<?php

namespace App\Services;

use App\Models\KeuanganModel;
use CodeIgniter\Database\BaseConnection;
use App\Repositories\KeuanganRepository;
use App\Repositories\LogPembayaranRepository;
use App\Repositories\NotifRepository;
use App\Repositories\KavlingRepository;
use App\Repositories\HargaJualRepository;
use App\Repositories\TransaksiRepository;
use App\Repositories\SpptbRepository;
use App\Models\MkdtModel;
use Hermawan\DataTables\DataTable;


class KeuanganService
{
    protected $model;
    protected $keuRepo;
    protected $pembayaranRepo;
    protected $db;
    protected $mkdtModel;
    protected $notif;

    public function __construct()
    {
        $this->model = model(KeuanganModel::class);
        $this->db = \Config\Database::connect();
        $this->pembayaranRepo = new LogPembayaranRepository();
        // $this->mkdtService = new TransaksiService();
        $this->mkdtModel = new MkdtModel();
        $this->keuRepo = new KeuanganRepository();

        $this->notif = new NotifRepository();
    }

    public function simpanIsiTagihan($request, int $actorId): array
    {
        $response = [
            'token' => csrf_hash(),
            'success' => false,
            'messages' => 'Terjadi kesalahan saat melakukan perubahan data',
        ];

        $idMkdt = (int) $request->getPost('mk-id_mkdt');
        if ($idMkdt <= 0) {
            $response['messages'] = 'Data MKDT tidak valid';
            return $response;
        }

        $existing = $this->db->table('keuangan')
            ->where('id_mkdt', $idMkdt)
            ->get()
            ->getResult();

        if ($this->hasPaidTagihan($idMkdt, $existing)) {
            $response['messages'] = 'Sudah ada pembayaran. Tidak bisa merubah detail biaya/tagihan';
            return $response;
        }

        $db = $this->db;
        $db->transException(true);

        try {
            $db->transStart();

            if (! $this->mkdtModel->update($idMkdt, $this->buildMkdtTagihanPayload($request))) {
                throw new \RuntimeException('Gagal memperbarui detail biaya');
            }

            if ($this->hasRequestArray($request, 'berita_acara')) {
                $this->syncTagihanStatus($idMkdt, 'UM', $this->buildTagihanRows($request, '', 'UM'), $actorId);
            }

            if ($this->hasRequestArray($request, 'berita_acara_bb')) {
                $this->syncTagihanStatus($idMkdt, 'BB', $this->buildTagihanRows($request, '_bb', 'BB'), $actorId);
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Transaksi gagal');
            }

            return [
                'token' => csrf_hash(),
                'success' => true,
                'messages' => 'Data berhasil diperbaharui',
            ];
        } catch (\Throwable $e) {
            try {
                $db->transRollback();
            } catch (\Throwable $rollback) {
            }

            log_message('error', '[KeuanganService::simpanIsiTagihan] {message}', ['message' => $e->getMessage()]);
            $response['messages'] = 'Gagal menyimpan data: ' . $e->getMessage();
            return $response;
        }
    }

    public function simpanDanaAkad($request, int $actorId): array
    {
        $response = [
            'token' => csrf_hash(),
            'success' => false,
            'messages' => 'Terjadi kesalahan saat melakukan perubahan data',
        ];

        $idMkdt = (int) $request->getVar('id_mkdt');
        $idKavling = (int) $request->getVar('id_kavling');
        $idDajam = $request->getVar('id_dajam');

        if ($idMkdt <= 0 || $idKavling <= 0 || ! is_array($idDajam)) {
            $response['messages'] = 'Data dana akad tidak lengkap';
            return $response;
        }

        $db = $this->db;
        $db->transException(true);

        try {
            $db->transStart();

            if (! $this->mkdtModel->update($idMkdt, ['dajam_selesai' => $request->getVar('dajam_selesai') ? 1 : 0])) {
                throw new \RuntimeException('Gagal memperbarui status dana akad');
            }

            foreach ($idDajam as $key => $row) {
                if (! is_array($row)) {
                    continue;
                }

                $payload = [
                    'id_kavling' => $idKavling,
                    'id_list_dajam' => $row['id_list_dajam'] ?? null,
                    'nominal' => $this->num($row['nominal'] ?? 0),
                ];

                if (! empty($row['sudah_cair'])) {
                    $payload += [
                        'sudah_cair' => 1,
                        'tgl_cair' => $row['tgl_cair'] ?? null,
                        'keterangan' => $row['keterangan'] ?? null,
                        'cair_oleh' => $actorId,
                        'cair_created_at' => date('Y-m-d H:i:s'),
                        'nominal_cair' => $this->num($row['nominal_cair'] ?? 0),
                    ];
                } else {
                    $payload += [
                        'sudah_cair' => 0,
                        'tgl_cair' => null,
                        'keterangan' => null,
                        'cair_oleh' => null,
                        'cair_created_at' => null,
                        'nominal_cair' => null,
                    ];
                }

                if (strpos((string) $key, 'n') === false) {
                    $payload['id'] = $key;
                    $payload['edit_by'] = $actorId;
                    $payload['updated_at'] = date('Y-m-d H:i:s');
                    $saved = $db->table('dana_akad')->where('id', $key)->update($payload);
                } else {
                    $payload['id'] = null;
                    $payload['add_by'] = $actorId;
                    $payload['created_at'] = date('Y-m-d H:i:s');
                    $saved = $db->table('dana_akad')->insert($payload);
                }

                if (! $saved) {
                    throw new \RuntimeException('Gagal menyimpan detail dana akad');
                }
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Transaksi gagal');
            }

            return [
                'token' => csrf_hash(),
                'success' => true,
                'messages' => 'Data berhasil diperbaharui',
            ];
        } catch (\Throwable $e) {
            try {
                $db->transRollback();
            } catch (\Throwable $rollback) {
            }

            log_message('error', '[KeuanganService::simpanDanaAkad] {message}', ['message' => $e->getMessage()]);
            $response['messages'] = 'Gagal menyimpan dana akad: ' . $e->getMessage();
            return $response;
        }
    }

    public function simpanInvoice($request, int $actorId): array
    {
        $response = [
            'token' => csrf_hash(),
            'success' => false,
            'messages' => 'Terjadi kesalahan saat menyimpan invoice',
        ];

        if (! $request->getVar('no_inv') || ! $request->getVar('id_mkdt') || ! $request->getVar('id_konsumen') || ! $request->getVar('id_kavling')) {
            $response['messages'] = 'Data invoice tidak lengkap';
            return $response;
        }

        $db = $this->db;
        $db->transException(true);

        try {
            $db->transStart();

            $saved = $db->table('invoice_log')->insert([
                'no_inv' => $request->getVar('no_inv'),
                'id_mkdt' => $request->getVar('id_mkdt'),
                'id_konsumen' => $request->getVar('id_konsumen'),
                'id_kavling' => $request->getVar('id_kavling'),
                'id_kopsurat' => $request->getVar('id_kopsurat'),
                'tanggal_invoice' => $request->getVar('tanggal_invoice'),
                'tanggal_jatuh_tempo' => $request->getVar('tanggal_jatuh_tempo'),
                'tagihan' => $request->getVar('tagihan'),
                'terms' => $request->getVar('terms'),
                'add_by' => $actorId,
                'date_add' => date('Y-m-d H:i:s'),
            ]);

            if (! $saved) {
                throw new \RuntimeException('Gagal menambahkan invoice');
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Transaksi gagal');
            }

            return [
                'token' => csrf_hash(),
                'success' => true,
                'messages' => 'Data berhasil ditambahkan',
            ];
        } catch (\Throwable $e) {
            try {
                $db->transRollback();
            } catch (\Throwable $rollback) {
            }

            log_message('error', '[KeuanganService::simpanInvoice] {message}', ['message' => $e->getMessage()]);
            $response['messages'] = 'Gagal menyimpan invoice: ' . $e->getMessage();
            return $response;
        }
    }

    public function updateSudahDibayar($request): array
    {
        $response = [
            'token' => csrf_hash(),
            'success' => false,
            'messages' => 'Terjadi kesalahan saat melakukan perubahan data',
        ];

        $idKeuangan = (int) $request->getVar('id_keuangan');
        if ($idKeuangan <= 0) {
            $response['messages'] = 'Data tagihan tidak valid';
            return $response;
        }

        $db = $this->db;
        $db->transException(true);

        try {
            $db->transStart();

            if (! $this->model->update($idKeuangan, ['sudah_dibayar' => $request->getVar('sb') ? 1 : 0])) {
                throw new \RuntimeException('Gagal memperbarui status tagihan');
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Transaksi gagal');
            }

            return [
                'token' => csrf_hash(),
                'success' => true,
                'messages' => 'Data berhasil diperbaharui',
            ];
        } catch (\Throwable $e) {
            try {
                $db->transRollback();
            } catch (\Throwable $rollback) {
            }

            log_message('error', '[KeuanganService::updateSudahDibayar] {message}', ['message' => $e->getMessage()]);
            $response['messages'] = 'Gagal memperbarui status tagihan: ' . $e->getMessage();
            return $response;
        }
    }

    /**
     * Sinkronisasi tagihan UM & BB:
     * - Update jika id_keuangan ada
     * - Insert jika id_keuangan kosong
     * - Hapus yang tidak lagi dikirim
     */
    public function syncTagihan(int $idMkdt, array $um, int $actorId): void
    {
        // Ambil existing ids di DB
        $existing = $this->db->table('keuangan')->select('id_keuangan')->where('id_mkdt', $idMkdt)->get()->getResultArray();
        $existingIds = array_map(fn($r) => (int) $r['id_keuangan'], $existing);

        $incomingIds = [];

        // UM
        $lenUM = max(count($um['berita_acara']), count($um['jatuh_tempo_tgl']), count($um['nominal']));
        $x = 1;
        for ($i = 0; $i < $lenUM; $i++) {
            $angsuran = $um['berita_acara'][$i];

            if ($angsuran === "Angsuran") {
                $angsuran = "Angsuran {$x}";
                $x++;
            }
            $payload = [
                'berita_acara' => $angsuran,
                'jatuh_tempo_tgl' => $um['jatuh_tempo_tgl'][$i] ?? null,
                'nominal' => $this->num($um['nominal'][$i] ?? null),
                'status' => 'UM',
                'id_mkdt' => $idMkdt,
            ];
            $id = (int) ($um['id_keuangan'][$i] ?? 0);

            // var_dump($payload);die();

            if ($id > 0) {
                $payload['edit_by'] = $actorId;
                $this->model->update($id, $payload);
                $incomingIds[] = $id;
            } else {
                $payload['add_by'] = $actorId;
                $payload['edit_by'] = $actorId;
                $this->model->insert($payload);
                $incomingIds[] = (int) $this->model->getInsertID();
            }
        }

        // BB
        // $lenBB = max(count($bb['berita_acara']), count($bb['jatuh_tempo_tgl']), count($bb['nominal']));
        // for ($i=0; $i<$lenBB; $i++) {
        //     $payload = [
        //         'berita_acara'   => $bb['berita_acara'][$i] ?? null,
        //         'jatuh_tempo_tgl'=> $bb['jatuh_tempo_tgl'][$i] ?? null,
        //         'nominal'        => $this->num($bb['nominal'][$i] ?? null),
        //         'status'         => 'BB',
        //         'id_mkdt'        => $idMkdt,
        //     ];
        //     $id = (int) ($bb['id_keuangan'][$i] ?? 0);

        //     if ($id > 0) {
        //         $payload['edit_by'] = $actorId;
        //         $this->model->update($id, $payload);
        //         $incomingIds[] = $id;
        //     } else {
        //         $payload['add_by']  = $actorId;
        //         $payload['edit_by'] = $actorId;
        //         $this->model->insert($payload);
        //         $incomingIds[] = (int) $this->model->getInsertID();
        //     }
        // }

        // Hapus yang tidak dikirim lagi
        $toDelete = array_diff($existingIds, $incomingIds);
        if (!empty($toDelete)) {
            $this->model->whereIn('id_keuangan', $toDelete)->delete();
        }
    }
    public function getListTagihan($request, $status = null)
    {
        $status = $status ?? "Booking";
        $builder = $this->keuRepo->getBelumLunasQuery($status);
        if ($request->getVar('id_proyek'))
            $builder->where('p.id_proyek', $request->getVar('id_proyek'));
        if ($request->getVar('id_cluster'))
            $builder->where('cl.id_cluster', $request->getVar('id_cluster'));
        if ($request->getVar('id_jalan'))
            $builder->where('j.id_jalan', $request->getVar('id_jalan'));


        return DataTable::of($builder)
            ->addSearchableColumns('nama_konsumen', 'no_kavling')
            ->add('Aksi', function ($value) {
                $sh = json_encode([
                    'data' => [
                        'id_mkdt' => $value->id_mkdt,
                        'nama_proyek' => $value->nama_proyek,
                        'nama_jalan' => $value->nama_jalan,
                        'no_kavling' => $value->no_kavling,
                    ],
                    'data2' => [
                        'no_tipe_rumah' => $value->no_tipe_rumah,
                        'tipe_rumah' => $value->id_tipe,
                    ]
                ]);
                return '<button class="btn btn-outline-primary btn-sm" onclick="open_keuangan(' .  htmlspecialchars($sh, ENT_QUOTES, 'UTF-8') . ', 3, 0)"><i class="fas fa-receipt"></i> Bayar</button>';
            }, 'first')
            ->addNumbering('no')
            ->edit('booking_tgl', function ($value) {
                return $this->format_tgl($value->booking_tgl);
            })
            ->edit('jatuh_tempo_tgl', function ($value) {
                return $this->format_tgl($value->jatuh_tempo_tgl);
            })
            ->edit('is_kpr', function ($value) {
                return $this->is_active($value->is_kpr, 'KPR', 'TUNAI');
            })
            ->edit('nominal', function ($v) {
                return number_format($v->nominal);
            })
            ->edit('total_tagihan', function ($v) {
                return number_format($v->um + $v->adm + $v->bb);
            })
            ->edit('sudah_bayar', function ($v) {
                return number_format($v->total_um + $v->total_adm + $v->total_bb);
            })
            ->edit('sisa_tagihan', function ($v) {
                $tot = $v->um + $v->adm + $v->bb;
                $sb = $v->total_um + $v->total_adm + $v->total_bb;
                return number_format($tot - $sb);
            })
            // ->edit('action', function ($value) {
            //     return '
            //     <div class="btn-group">
            //     <button class="btn btn-primary btn-sm" onclick="openDetail(' . $value->id_mkdt . ')"><i class="fa fa-eye"></i></button>
            //     <button class="btn btn-warning btn-sm" onclick="openEdit(' . $value->id_mkdt . ')"><i class="fa fa-edit"></i></button>
            //     </div>';
            // })
            ->toJson();
    }
    public function getListTagihanGrouped($request)
    {
        $builder = $this->keuRepo->getBelumLunasGroupedQuery();

        if ($request->getVar('id_proyek'))
            $builder->where('p.id_proyek', $request->getVar('id_proyek'));
        if ($request->getVar('id_cluster'))
            $builder->where('cl.id_cluster', $request->getVar('id_cluster'));
        if ($request->getVar('id_jalan'))
            $builder->where('j.id_jalan', $request->getVar('id_jalan'));

        return DataTable::of($builder)
            ->addSearchableColumns('nama_konsumen', 'no_kavling')
            ->add('Aksi', function ($value) {
                $sh = json_encode([
                    'data' => [
                        'id_mkdt'     => $value->id_mkdt,
                        'nama_proyek' => $value->nama_proyek,
                        'nama_jalan'  => $value->nama_jalan,
                        'no_kavling'  => $value->no_kavling,
                    ],
                    'data2' => [
                        'no_tipe_rumah' => $value->no_tipe_rumah,
                        'tipe_rumah'    => $value->id_tipe,
                    ]
                ]);
                return '<button class="btn btn-outline-primary btn-sm" onclick="open_keuangan(' . htmlspecialchars($sh, ENT_QUOTES, 'UTF-8') . ', 3, 0)"><i class="fas fa-receipt"></i> Bayar</button>';
            }, 'first')
            ->addNumbering('no')
            ->edit('booking_tgl', function ($value) {
                return $this->format_tgl($value->booking_tgl);
            })
            ->edit('jatuh_tempo_tgl', function ($value) {
                return $this->format_tgl($value->jatuh_tempo_tgl);
            })
            ->edit('is_kpr', function ($value) {
                return $this->is_active($value->is_kpr, 'KPR', 'TUNAI');
            })
            ->edit('total_tagihan', function ($v) {
                return number_format($v->um + $v->adm + $v->bb);
            })
            ->edit('sudah_bayar', function ($v) {
                return number_format($v->total_um + $v->total_adm + $v->total_bb);
            })
            ->edit('sisa_tagihan', function ($v) {
                $tot = $v->um + $v->adm + $v->bb;
                $sb  = $v->total_um + $v->total_adm + $v->total_bb;
                return number_format($tot - $sb);
            })
            ->toJson();
    }

    public function getTagihanById($id_mkdt, $isTurunKPR = false)
    {
        return $this->keuRepo->getTagihanById($id_mkdt, $isTurunKPR);
    }
    public function getRiwayatBayarById($id_mkdt)
    {
        return $this->pembayaranRepo->getRiwayatBayarById($id_mkdt);
    }
    function getRiwayatBayarWithDetailById($id_mkdt)
    {
        $log = $this->pembayaranRepo->getRiwayatBayarById($id_mkdt);
        foreach ($log as $key => $value) {
            $log[$key]->detail = $this->pembayaranRepo->getDetailRiwayatBayarById($value->id_pembayaran);
        }
        return $log;
    }
    function getRiwayatBayar($request)
    {
        $id_proyek = $request->getVar('id_proyek');
        $id_cluster = $request->getVar('id_cluster');
        $id_jalan = $request->getVar('id_jalan');
        if ($id_proyek == null) {
            return [];
        }
        $builder = $this->pembayaranRepo->getRiwayatBayarQuery($id_proyek, $id_cluster, $id_jalan);
        return DataTable::of($builder)
            ->toJson();
    }

    function getAllJatuhTempo($id_proyek)
    {
        $q = $this->keuRepo->getAllJatuhTempo($id_proyek);

        return $q;
    }

    private function buildMkdtTagihanPayload($request): array
    {
        $tglHarga = $request->getVar('mk-tgl_harga');

        return [
            'id_hargajual' => $request->getVar('mk-id'),
            'tgl_harga' => $tglHarga ? date('Y-m-d', strtotime($tglHarga)) : null,
            'harga_jual' => $this->num($request->getVar('mk-hargajual')),
            'harga_kpr' => $this->num($request->getVar('mk-kpr')),
            'harga_diskon_harga_jual' => $this->num($request->getVar('mk-diskon_harga_jual')),
            'harga_diskon_uang_muka' => $this->num($request->getVar('mk-diskon_uang_muka')),
            'harga_administrasi' => $this->num($request->getVar('mk-biaya_adm')),
            'harga_ppn' => $this->num($request->getVar('mk-harga_ppn')),
            'harga_bphtb' => $this->num($request->getVar('mk-bphtb')),
            'harga_biaya_proses' => $this->num($request->getVar('mk-biaya_proses')),
            'harga_penambahan' => $this->num($request->getVar('mk-harga_penambahan')),
            'harga_penambahan_tanah' => $this->num($request->getVar('mk-harga_penambahan_tanah')),
            'keterangan_penambahan_biaya' => $this->num($request->getVar('mk-keterangan_harga_penambahan')),
        ];
    }

    private function buildTagihanRows($request, string $suffix, string $status): array
    {
        $beritaAcara = $this->requestArray($request, 'berita_acara' . $suffix);
        $jatuhTempo = $this->requestArray($request, 'jatuh_tempo_tgl' . $suffix);
        $nominal = $this->requestArray($request, 'nominal' . $suffix);
        $idKeuangan = $this->requestArray($request, $suffix === '_bb' ? 'id_keuangan_bb' : 'id_keuangan');

        $rows = [];
        $length = max(count($beritaAcara), count($jatuhTempo), count($nominal), count($idKeuangan));

        for ($i = 0; $i < $length; $i++) {
            $ba = $beritaAcara[$i] ?? null;
            $jt = $jatuhTempo[$i] ?? null;
            $nm = $nominal[$i] ?? null;
            $id = $idKeuangan[$i] ?? null;

            if ($ba === null && $jt === null && $nm === null && $id === null) {
                continue;
            }

            $rows[] = [
                'id_keuangan' => $id,
                'berita_acara' => $ba,
                'jatuh_tempo_tgl' => $jt,
                'nominal' => $this->num($nm),
                'status' => $status,
            ];
        }

        return $rows;
    }

    private function syncTagihanStatus(int $idMkdt, string $status, array $rows, int $actorId): void
    {
        $incomingIds = [];

        foreach ($rows as $row) {
            $payload = [
                'berita_acara' => $row['berita_acara'],
                'jatuh_tempo_tgl' => $row['jatuh_tempo_tgl'],
                'nominal' => $row['nominal'],
                'status' => $status,
                'id_mkdt' => $idMkdt,
                'edit_by' => $actorId,
            ];

            $idKeuangan = (int) ($row['id_keuangan'] ?? 0);
            if ($idKeuangan > 0) {
                if (! $this->model->update($idKeuangan, $payload)) {
                    throw new \RuntimeException('Gagal memperbarui tagihan');
                }

                $incomingIds[] = $idKeuangan;
                continue;
            }

            $payload['add_by'] = $actorId;
            $insertId = $this->model->insert($payload);
            if (! $insertId) {
                throw new \RuntimeException('Gagal menambahkan tagihan');
            }

            $incomingIds[] = (int) $insertId;
        }

        $existingIds = array_map(
            static fn($row) => (int) $row['id_keuangan'],
            $this->db->table('keuangan')
                ->select('id_keuangan')
                ->where('id_mkdt', $idMkdt)
                ->where('status', $status)
                ->get()
                ->getResultArray()
        );

        $deleteIds = array_diff($existingIds, $incomingIds);
        if ($deleteIds !== []) {
            if (! $this->model->whereIn('id_keuangan', $deleteIds)->delete()) {
                throw new \RuntimeException('Gagal menghapus tagihan lama');
            }
        }
    }

    private function hasPaidTagihan(int $idMkdt, array $existingTagihan): bool
    {
        $existingIds = array_map(
            static fn($row) => (string) $row->id_keuangan,
            $existingTagihan
        );

        if ($existingIds === []) {
            return false;
        }

        $logs = $this->db->table('log_pembayaran')
            ->select('id_keuangan')
            ->where('id_mkdt', $idMkdt)
            ->get()
            ->getResult();

        foreach ($logs as $log) {
            $paidIds = preg_split('/[;,]+/', (string) $log->id_keuangan, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($paidIds as $paidId) {
                if (in_array(trim($paidId), $existingIds, true)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function hasRequestArray($request, string $name): bool
    {
        return $request->getVar($name . '[]') !== null || $request->getVar($name) !== null;
    }

    private function requestArray($request, string $name): array
    {
        $value = $request->getVar($name . '[]');
        if ($value === null) {
            $value = $request->getVar($name);
        }

        if ($value === null) {
            return [];
        }

        return is_array($value) ? array_values($value) : [$value];
    }

    private function num($v)
    {
        // if ($v === null || $v === '')
        //     return null;
        // $v = (string) $v;
        // $v = str_replace(',', "", $v);
        // return (int) round((float) $v);
        if ($v === null || $v === '') {
            return 0;
        }

        $v = str_replace(',', "", $v);
        return $v;
    }
    function insert($data)
    {
        return $this->model->insert($data);
    }
    function delete($id)
    {
        return $this->model->delete($id);
    }

    function hapusTurunKPR($id)
    {
        $db = $this->db;
        $db->transStart();

        try {
            // (Opsional) cek eksistensi untuk 404 yang lebih informatif
            // $exists = $this->keuanganService->exists($id); // buat method exists() di service
            // if (!$exists) {
            //     $db->transRollback();
            //     return $this->failNotFound('Data keuangan tidak ditemukan.');
            // }

            $deleted = $this->delete($id); // pastikan return bool
            if (!$deleted) {
                // Kalau service mengembalikan false, anggap kegagalan domain (mis. constraint)
                $db->transRollback();
                $resp = ['success' => false, 'message' => 'Gagal menghapus tagihan Turun KPR.', 'deletedId' => $id];
                return $resp;
            }

            $db->transCommit();

            // Gunakan response API trait agar seragam
            $resp = ['success' => true, 'message' => 'Berhasil menghapus tagihan Turun KPR.', 'deletedId' => $id];
            return $resp;
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', '[haputurunkpr] Exception: {msg}', ['msg' => $e->getMessage()]);
            $resp = ['success' => false, 'message' => 'Terjadi kesalahan tak terduga.', 'deletedId' => $id];
            return $resp;
        }
    }
    function tambahTurunKPR($data)
    {
        $data = (object) $data;
        $resp = ['token' => csrf_hash()];
        $id_mkdt = $data->id_mkdt;
        $id_kavling = $data->id_kavling;
        $id_konsumen = $data->id_konsumen;
        $harga_kpr_acc = $this->num($data->acc_harga_kpr);
        $harga_kpr = $this->num($data->harga_kpr);
        $nominal = $this->num($data->nominal);
        $jatuh_tempo_tgl = $data->jatuh_tempo;
        $berita_acara = $data->berita_acara;
        $kpr = [
            'berita_acara' => $berita_acara,
            'jatuh_tempo_tgl' => $jatuh_tempo_tgl,
            'nominal' => $nominal,
            'status' => "BB",
            'add_by' => user_id(),
            'id_mkdt' => $id_mkdt,
        ];
        $db = $this->db;

        $db->transException(true);

        try {
            $db->transStart();

            $cek = $this->hasTurunKPR($id_mkdt);
            if ($cek) {
                return [
                    'token' => csrf_hash(),
                    'success' => false,
                    'messages' => 'Tagihan Turun KPR sudah ada ',
                ];
            }

            $insert = $this->insert($kpr);

            if ($insert) {
                $pesanNotif = "Menabahkan tagihan untuk Turun KPR";

                $data = [
                    'harga_kpr' => $harga_kpr,
                    'harga_kpr_acc' => $harga_kpr_acc,
                    'harga_penambahan_um' => $nominal,

                ];
                $this->mkdtModel->update(['id_mkdt' => $id_mkdt], $data);
            }

            $this->notif->tambah_notif("3;4;9", $pesanNotif, user_id(), $id_kavling, $id_konsumen);

            $db->transComplete();

            $resp['data'] = [
                'add_by' => user_id(),
                'berita_acara' => $berita_acara,
                "jatuh_tempo_tgl" => $jatuh_tempo_tgl,
                "id_keuangan" => $this->model->getInsertID()
            ];
            $resp['success'] = true;
            $resp['messages'] = "Taihan KPR Berhasil ditambahkan";

            return $resp;
        } catch (\Throwable $e) {
            if ($db->transStatus() !== false) {
                $db->transRollback();
            }
            return [
                'token' => csrf_hash(),
                'success' => false,
                'messages' => 'Gagal menyimpan data: ' . $e->getMessage(),
            ];
        }
    }
    public function hasTurunKPR(int $id_mkdt): bool
    {
        return $this->model
            ->where('id_mkdt', $id_mkdt)
            ->where('berita_acara', 'Turun KPR')
            ->countAllResults() > 0;
    }
    function format_tgl($tgl)
    {
        if ($tgl == "" || $tgl == "0000-00-00" || $tgl == null)
            return "-";
        return date_format(date_create($tgl), "d-M-Y");
    }
    function is_active($id, $texts, $textf)
    {
        $r = '<span class="btn btn-primary btn-sm" text-capitalized="">' . $textf . '</span>';
        if ($id == "1")
            $r = '<span class="btn btn-success btn-sm" text-capitalized="">' . $texts . '</span>';
        return $r;
    }
}
