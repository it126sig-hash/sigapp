<?php

namespace App\Services;

use App\Models\KeuanganModel;
use CodeIgniter\Database\BaseConnection;
use App\Repositories\KeuanganRepository;
use App\Repositories\LogPembayaranRepository;
use App\Services\NotifikasiService;
use App\Repositories\KavlingRepository;
use App\Repositories\HargaJualRepository;
use App\Repositories\TransaksiRepository;
use App\Repositories\SpptbRepository;
use App\Models\MkdtModel;
use Hermawan\DataTables\DataTable;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use App\Services\Bpb\ProfileSignatureService;
use App\Services\Bpb\BpbFileService;
use Myth\Auth\Password;


class KeuanganService
{
    protected $model;
    protected $keuRepo;
    protected $pembayaranRepo;
    protected $db;
    protected $mkdtModel;
    protected $notif;
    protected MkdtSettlementService $settlementService;

    public function __construct()
    {
        $this->model = model(KeuanganModel::class);
        $this->db = \Config\Database::connect();
        $this->pembayaranRepo = new LogPembayaranRepository();
        // $this->mkdtService = new TransaksiService();
        $this->mkdtModel = new MkdtModel();
        $this->keuRepo = new KeuanganRepository();
        $this->settlementService = new MkdtSettlementService($this->db);

        $this->notif = new NotifikasiService();
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

            $this->settlementService->synchronize($idMkdt);

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
        // Ambil existing ids di DB (kecualikan yang sudah di-void supaya tidak ikut ter-hard-delete karena tidak dikirim ulang)
        $existing = $this->db->table('keuangan')->select('id_keuangan')->where('id_mkdt', $idMkdt)->where('is_void', 0)->get()->getResultArray();
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

        $this->settlementService->synchronize($idMkdt);
    }

    public function synchronizeLunasStatus(int $idMkdt): array
    {
        return $this->settlementService->synchronize($idMkdt);
    }
    public function getListTagihan($request, $status = null)
    {
        $status = $status ?? "Booking";
        $builder = $this->keuRepo->getBelumLunasQuery($status);
        $id_proyek = resolve_active_proyek_id($request->getVar('id_proyek'));
        if ($id_proyek)
            $builder->where('p.id_proyek', $id_proyek);
        if ($request->getVar('id_cluster'))
            $builder->where('cl.id_cluster', $request->getVar('id_cluster'));
        if ($request->getVar('id_jalan'))
            $builder->where('j.id_jalan', $request->getVar('id_jalan'));

        $isKpr = $request->getVar('is_kpr');
        if ($isKpr !== null && $isKpr !== '') {
            $builder->where('m.is_kpr', $isKpr);
        }

        $bookingRange = $request->getVar('booking_tgl_range');
        if (!empty($bookingRange)) {
            $dates = explode(' to ', $bookingRange);
            if (count($dates) === 2) {
                $builder->where('m.booking_tgl >=', $dates[0]);
                $builder->where('m.booking_tgl <=', $dates[1]);
            } else if (count($dates) === 1) {
                $builder->where('m.booking_tgl', $dates[0]);
            }
        }

        $jatuhTempoRange = $request->getVar('jatuh_tempo_tgl_range');
        if (!empty($jatuhTempoRange)) {
            $dates = explode(' to ', $jatuhTempoRange);
            if (count($dates) === 2) {
                $builder->where('keuangan.jatuh_tempo_tgl >=', $dates[0]);
                $builder->where('keuangan.jatuh_tempo_tgl <=', $dates[1]);
            } else if (count($dates) === 1) {
                $builder->where('keuangan.jatuh_tempo_tgl', $dates[0]);
            }
        }


        return DataTable::of($builder)
            ->addSearchableColumns('nama_konsumen', 'no_kavling')
            ->add('Aksi', function ($value) {
                if (function_exists('in_groups') && !in_groups(['1', '3'])) {
                    return '-';
                }
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
    
    public function getListTagihanJatuhTempoGrouped($request)
    {
        $builder = $this->keuRepo->getBelumLunasGroupedQuery();
        
        $today = date('Y-m-d');
        $builder->where('keu_agg.jatuh_tempo_tgl <=', $today);

        $id_proyek = resolve_active_proyek_id($request->getVar('id_proyek'));
        if ($id_proyek)
            $builder->where('p.id_proyek', $id_proyek);
        if ($request->getVar('id_cluster'))
            $builder->where('cl.id_cluster', $request->getVar('id_cluster'));
        if ($request->getVar('id_jalan'))
            $builder->where('j.id_jalan', $request->getVar('id_jalan'));

        $isKpr = $request->getVar('is_kpr');
        if ($isKpr !== null && $isKpr !== '') {
            $builder->where('m.is_kpr', $isKpr);
        }

        $bookingRange = $request->getVar('booking_tgl_range');
        if (!empty($bookingRange)) {
            $dates = explode(' to ', $bookingRange);
            if (count($dates) === 2) {
                $builder->where('m.booking_tgl >=', $dates[0]);
                $builder->where('m.booking_tgl <=', $dates[1]);
            } else if (count($dates) === 1) {
                $builder->where('m.booking_tgl', $dates[0]);
            }
        }

        $jatuhTempoRange = $request->getVar('jatuh_tempo_tgl_range');
        if (!empty($jatuhTempoRange)) {
            $dates = explode(' to ', $jatuhTempoRange);
            if (count($dates) === 2) {
                $builder->where('keu_agg.jatuh_tempo_tgl >=', $dates[0]);
                $builder->where('keu_agg.jatuh_tempo_tgl <=', $dates[1]);
            } else if (count($dates) === 1) {
                $builder->where('keu_agg.jatuh_tempo_tgl', $dates[0]);
            }
        }

        return DataTable::of($builder)
            ->setSearchableColumns(['c.nama_konsumen', 'k.no_kavling', 'j.nama_jalan'])
            ->add('Aksi', function ($value) {
                if (function_exists('in_groups') && !in_groups(['1', '3'])) {
                    return '-';
                }
                $sh = json_encode([
                    'data' => [
                        'id_mkdt'     => $value->id_mkdt,
                        'nama_proyek' => $value->nama_proyek,
                        'nama_jalan'  => $value->nama_jalan,
                        'no_kavling'  => $value->no_kavling,
                    ],
                    'data2' => [
                        'no_tipe_rumah' => $value->no_tipe_rumah,
                        'tipe_rumah'    => $value->tipe_pricelist,
                    ]
                ]);
                return '<button type="button" class="btn btn-primary btn-sm" onclick="open_keuangan(' . htmlspecialchars($sh, ENT_QUOTES, 'UTF-8') . ', 3, 0)"><i class="fas fa-receipt mr-25"></i> Bayar</button>';
            }, 'first')
            ->addNumbering('no')
            ->edit('booking_tgl', function ($value) {
                return $this->format_tgl($value->booking_tgl);
            })
            // NOTE: Do NOT edit jatuh_tempo_tgl here so the frontend can parse Y-m-d
            ->edit('is_kpr', function ($value) {
                return $this->is_active($value->is_kpr, 'KPR', 'TUNAI');
            })
            ->edit('total_tagihan', function ($v) {
                return number_format((float) $v->total_tagihan);
            })
            ->edit('sudah_bayar', function ($v) {
                return number_format((float) $v->sudah_bayar);
            })
            ->edit('sisa_tagihan', function ($v) {
                $value = number_format((float) $v->sisa_tagihan);
                if ((int) ($v->perlu_rekonsiliasi ?? 0) === 1) {
                    $value .= ' <span class="badge badge-warning">Perlu rekonsiliasi</span>';
                }
                return $value;
            })
            ->toJson(true);
    }

    public function getListTagihanGrouped($request)
    {
        $status_lunas = $request->getVar('status_lunas');
        
        if ($status_lunas === 'all') {
            $builder = $this->keuRepo->getAllGroupedQuery();
        } else if ($status_lunas === 'jatuh_tempo') {
            $builder = $this->keuRepo->getBelumLunasGroupedQuery();
            $builder->where('keu_agg.jatuh_tempo_tgl <=', date('Y-m-d'));
        } else {
            // default to belum lunas (0)
            $builder = $this->keuRepo->getBelumLunasGroupedQuery();
        }

        $id_proyek = resolve_active_proyek_id($request->getVar('id_proyek'));
        if ($id_proyek)
            $builder->where('p.id_proyek', $id_proyek);
        if ($request->getVar('id_cluster'))
            $builder->where('cl.id_cluster', $request->getVar('id_cluster'));
        if ($request->getVar('id_jalan'))
            $builder->where('j.id_jalan', $request->getVar('id_jalan'));

        $isKpr = $request->getVar('is_kpr');
        if ($isKpr !== null && $isKpr !== '') {
            $builder->where('m.is_kpr', $isKpr);
        }

        $bookingRange = $request->getVar('booking_tgl_range');
        if (!empty($bookingRange)) {
            $dates = explode(' to ', $bookingRange);
            if (count($dates) === 2) {
                $builder->where('m.booking_tgl >=', $dates[0]);
                $builder->where('m.booking_tgl <=', $dates[1]);
            } else if (count($dates) === 1) {
                $builder->where('m.booking_tgl', $dates[0]);
            }
        }

        $jatuhTempoRange = $request->getVar('jatuh_tempo_tgl_range');
        if (!empty($jatuhTempoRange)) {
            $dates = explode(' to ', $jatuhTempoRange);
            if (count($dates) === 2) {
                $builder->where('keu_agg.jatuh_tempo_tgl >=', $dates[0]);
                $builder->where('keu_agg.jatuh_tempo_tgl <=', $dates[1]);
            } else if (count($dates) === 1) {
                $builder->where('keu_agg.jatuh_tempo_tgl', $dates[0]);
            }
        }

        return DataTable::of($builder)
            ->setSearchableColumns(['c.nama_konsumen', 'k.no_kavling', 'j.nama_jalan'])
            ->add('Aksi', function ($value) {
                if (function_exists('in_groups') && !in_groups(['1', '3'])) {
                    return '-';
                }
                $sh = json_encode([
                    'data' => [
                        'id_mkdt'     => $value->id_mkdt,
                        'nama_proyek' => $value->nama_proyek,
                        'nama_jalan'  => $value->nama_jalan,
                        'no_kavling'  => $value->no_kavling,
                    ],
                    'data2' => [
                        'no_tipe_rumah' => $value->no_tipe_rumah,
                        'tipe_rumah'    => $value->tipe_pricelist,
                    ]
                ]);
                return '<button type="button" class="btn btn-primary btn-sm" onclick="open_keuangan(' . htmlspecialchars($sh, ENT_QUOTES, 'UTF-8') . ', 3, 0)"><i class="fas fa-receipt mr-25"></i> Bayar</button>';
            }, 'first')
            ->addNumbering('no')
            ->edit('booking_tgl', function ($value) {
                return $this->format_tgl($value->booking_tgl);
            })
            ->add('jatuh_tempo_tgl_raw', function ($value) {
                return $value->jatuh_tempo_tgl;
            })
            ->edit('jatuh_tempo_tgl', function ($value) {
                if (!$value->jatuh_tempo_tgl) return '-';
                
                $months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                $ts = strtotime($value->jatuh_tempo_tgl);
                $dateStr = date('d', $ts) . ' ' . $months[date('n', $ts) - 1] . ' ' . date('Y', $ts);
                
                $today = new \DateTime(date('Y-m-d'));
                $jtDate = new \DateTime($value->jatuh_tempo_tgl);
                
                $diff = $today->diff($jtDate);
                $diffDays = (int) $diff->format('%r%a'); // positive if jtDate > today, negative if today > jtDate
                
                $sisa = $value->jumlah_tagihan ?? 0;
                
                if ($diffDays >= 0) {
                    return '<div class="d-flex flex-column">
                                <span class="">' . $dateStr . '</span>
                                <span class="text-muted font-small-2">Sisa ' . $sisa . ' Tagihan</span>
                            </div>';
                } else {
                    $telat = abs($diffDays);
                    return '<div class="d-flex flex-column">
                                <span class="">' . $dateStr . '</span>
                                <span class="text-danger font-small-2 font-weight-bold">Sisa ' . $sisa . ' Tagihan terlambat ' . $telat . ' hari</span>
                            </div>';
                }
            })
            ->edit('is_kpr', function ($value) {
                return $this->is_active($value->is_kpr, 'KPR', 'TUNAI');
            })
            ->edit('total_tagihan', function ($v) {
                return number_format((float) $v->total_tagihan);
            })
            ->edit('sudah_bayar', function ($v) {
                return number_format((float) $v->sudah_bayar);
            })
            ->edit('sisa_tagihan', function ($v) {
                $value = number_format((float) $v->sisa_tagihan);
                if ((int) ($v->perlu_rekonsiliasi ?? 0) === 1) {
                    $value .= ' <span class="badge badge-warning">Perlu rekonsiliasi</span>';
                }
                return $value;
            })
            ->toJson(true);
    }

    
    public function getListTagihanJatuhTempoDetail(int $idMkdt): array
    {
        return array_map(static function ($row) {
            return [
                'berita_acara'  => $row->berita_acara ?? '',
                'jatuh_tempo_tgl' => $row->jatuh_tempo_tgl ?? null,
                'nominal'       => (float) ($row->nominal ?? 0),
                'sudah_dibayar' => (int) ($row->sudah_dibayar ?? 0),
                'status'        => $row->status ?? '',
                'is_void'       => (int) ($row->is_void ?? 0),
                'void_reason'   => $row->void_reason ?? '',
            ];
        }, $this->keuRepo->getListTagihanJatuhTempoDetailById($idMkdt));
    }

    public function getListTagihanDetail(int $idMkdt, $statusLunas = null): array
    {
        $data = $statusLunas === 'jatuh_tempo' 
            ? $this->keuRepo->getListTagihanJatuhTempoDetailById($idMkdt)
            : $this->keuRepo->getListTagihanDetailById($idMkdt);

        return array_map(static function ($row) {
            return [
                'berita_acara'  => $row->berita_acara ?? '',
                'jatuh_tempo_tgl' => $row->jatuh_tempo_tgl ?? null,
                'nominal'       => (float) ($row->nominal ?? 0),
                'sudah_dibayar' => (int) ($row->sudah_dibayar ?? 0),
                'status'        => $row->status ?? '',
                'is_void'       => (int) ($row->is_void ?? 0),
                'void_reason'   => $row->void_reason ?? '',
            ];
        }, $data);
    }

    public function getTagihanById($id_mkdt, $isTurunKPR = false)
    {
        return $this->keuRepo->getTagihanById($id_mkdt, $isTurunKPR);
    }
    public function getRiwayatBayarById($id_mkdt)
    {
        return $this->pembayaranRepo->getRiwayatBayarById($id_mkdt);
    }
    public function getTotalBayarById($id_mkdt): float
    {
        return $this->pembayaranRepo->getTotalBayarByIdMkdt($id_mkdt);
    }
    public function getPaidItemSummaryById($id_mkdt): array
    {
        return $this->pembayaranRepo->getPaidItemSummaryByIdMkdt($id_mkdt);
    }
    function getRiwayatBayarWithDetailById($id_mkdt)
    {
        $log = $this->pembayaranRepo->getRiwayatBayarById($id_mkdt);
        $detailRows = $this->pembayaranRepo->getDetailRiwayatBayarByIdMkdt($id_mkdt);
        $detailByPayment = [];

        foreach ($detailRows as $detail) {
            $detailByPayment[$detail['id_pembayaran']][] = $detail;
        }

        foreach ($log as $key => $value) {
            $log[$key]->detail = $detailByPayment[$value->id_pembayaran] ?? [];
        }
        return $log;
    }
    function getRiwayatBayar($request)
    {
        $id_proyek = resolve_active_proyek_id($request->getVar('id_proyek'));
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

    /**
     * Hapus permanen satu tagihan.
     */
    public function deleteTagihan(int $idKeuangan, int $actorId): array
    {
        $row = $this->model->find($idKeuangan);
        if (! $row) {
            return ['success' => false, 'message' => 'Tagihan tidak ditemukan'];
        }
        if ((int) $row->is_void === 1) {
            return ['success' => false, 'message' => 'Tagihan sudah di-void'];
        }

        $this->db->transException(true)->transBegin();
        try {
            if (! $this->model->delete($idKeuangan)) {
                throw new \RuntimeException('Gagal menghapus tagihan');
            }
            $this->settlementService->synchronize((int) $row->id_mkdt);
            $this->db->transCommit();

            return ['success' => true, 'message' => 'Tagihan berhasil dihapus'];
        } catch (\Throwable $e) {
            $this->db->transRollback();
            log_message('error', '[KeuanganService::deleteTagihan] {message}', ['message' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Gagal menghapus tagihan'];
        }
    }

    /**
     * Void satu tagihan: data tetap ada untuk riwayat/audit, tapi ditandai
     * is_void sehingga dikecualikan dari semua perhitungan total tagihan.
     */
    public function voidTagihan(int $idKeuangan, string $reason, int $actorId): array
    {
        $row = $this->model->find($idKeuangan);
        if (! $row) {
            return ['success' => false, 'message' => 'Tagihan tidak ditemukan'];
        }
        if ((int) $row->is_void === 1) {
            return ['success' => false, 'message' => 'Tagihan sudah di-void'];
        }
        if (trim($reason) === '') {
            return ['success' => false, 'message' => 'Alasan void wajib diisi'];
        }

        $this->db->transException(true)->transBegin();
        try {
            if (! $this->model->update($idKeuangan, [
                'is_void'     => 1,
                'void_reason' => $reason,
                'edit_by'     => $actorId,
            ])) {
                throw new \RuntimeException('Gagal melakukan void tagihan');
            }
            $this->settlementService->synchronize((int) $row->id_mkdt);
            $this->db->transCommit();

            return ['success' => true, 'message' => 'Tagihan berhasil di-void'];
        } catch (\Throwable $e) {
            $this->db->transRollback();
            log_message('error', '[KeuanganService::voidTagihan] {message}', ['message' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Gagal melakukan void tagihan'];
        }
    }

    /**
     * Batalkan void: tagihan aktif kembali dan dihitung lagi di total.
     */
    public function unvoidTagihan(int $idKeuangan, int $actorId): array
    {
        $row = $this->model->find($idKeuangan);
        if (! $row) {
            return ['success' => false, 'message' => 'Tagihan tidak ditemukan'];
        }
        if ((int) $row->is_void !== 1) {
            return ['success' => false, 'message' => 'Tagihan tidak dalam status void'];
        }

        $this->db->transException(true)->transBegin();
        try {
            if (! $this->model->update($idKeuangan, [
                'is_void'     => 0,
                'void_reason' => null,
                'edit_by'     => $actorId,
            ])) {
                throw new \RuntimeException('Gagal membatalkan void tagihan');
            }
            $this->settlementService->synchronize((int) $row->id_mkdt);
            $this->db->transCommit();

            return ['success' => true, 'message' => 'Void tagihan berhasil dibatalkan'];
        } catch (\Throwable $e) {
            $this->db->transRollback();
            log_message('error', '[KeuanganService::unvoidTagihan] {message}', ['message' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Gagal membatalkan void tagihan'];
        }
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
        $row = $this->model->find($id);
        if (! $row) {
            return ['success' => false, 'message' => 'Tagihan Turun KPR tidak ditemukan.', 'deletedId' => $id];
        }

        try {
            $db->transException(true)->transBegin();

            $deleted = $this->delete($id);
            if (!$deleted) {
                throw new \RuntimeException('Gagal menghapus tagihan Turun KPR.');
            }

            $this->settlementService->synchronize((int) $row->id_mkdt);
            $db->transCommit();

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
            if ($this->hasTurunKPR($id_mkdt)) {
                return [
                    'token' => csrf_hash(),
                    'success' => false,
                    'messages' => 'Tagihan Turun KPR sudah ada ',
                ];
            }

            $db->transBegin();

            $insert = $this->insert($kpr);
            if (! $insert) {
                throw new \RuntimeException('Gagal menambahkan tagihan Turun KPR');
            }

            $pesanNotif = "Menabahkan tagihan untuk Turun KPR";
            $data = [
                'harga_kpr' => $harga_kpr,
                'harga_kpr_acc' => $harga_kpr_acc,
                'harga_penambahan_um' => $nominal,
            ];
            if (! $this->mkdtModel->update(['id_mkdt' => $id_mkdt], $data)) {
                throw new \RuntimeException('Gagal memperbarui nilai Turun KPR');
            }

            $this->settlementService->synchronize((int) $id_mkdt);

            $this->notif->tambah_notif("3;4;9", $pesanNotif, user_id(), $id_kavling, $id_konsumen, \App\Enums\NotificationEvent::TAGIHAN_KPR, null, "siteplan/view?id_kavling=" . $id_kavling . "&tab=keuangan");

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Transaksi gagal');
            }
            $db->transCommit();

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
            try {
                $db->transRollback();
            } catch (\Throwable $rollback) {
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

    public function exportExcelTagihan($request)
    {
        $status_lunas = $request->getVar('status_lunas');
        $today = date('Y-m-d');
        
        if ($status_lunas === 'all') {
            $builder = $this->keuRepo->getAllGroupedQuery();
        } else if ($status_lunas === 'jatuh_tempo') {
            $builder = $this->keuRepo->getBelumLunasGroupedQuery();
            $builder->where('keu_agg.jatuh_tempo_tgl <=', date('Y-m-d'));
        } else {
            // default to belum lunas (0)
            $builder = $this->keuRepo->getBelumLunasGroupedQuery();
        }

        $id_proyek = resolve_active_proyek_id($request->getVar('id_proyek'));
        if ($id_proyek)
            $builder->where('p.id_proyek', $id_proyek);
        if ($request->getVar('id_cluster'))
            $builder->where('cl.id_cluster', $request->getVar('id_cluster'));
        if ($request->getVar('id_jalan'))
            $builder->where('j.id_jalan', $request->getVar('id_jalan'));

        $isKpr = $request->getVar('is_kpr');
        if ($isKpr !== null && $isKpr !== '') {
            $builder->where('m.is_kpr', $isKpr);
        }

        $bookingRange = $request->getVar('booking_tgl_range');
        if (!empty($bookingRange)) {
            $dates = explode(' to ', $bookingRange);
            if (count($dates) === 2) {
                $builder->where('m.booking_tgl >=', $dates[0]);
                $builder->where('m.booking_tgl <=', $dates[1]);
            } else if (count($dates) === 1) {
                $builder->where('m.booking_tgl', $dates[0]);
            }
        }

        $jatuhTempoRange = $request->getVar('jatuh_tempo_tgl_range');
        if (!empty($jatuhTempoRange)) {
            $dates = explode(' to ', $jatuhTempoRange);
            if (count($dates) === 2) {
                $builder->where('keu_agg.jatuh_tempo_tgl >=', $dates[0]);
                $builder->where('keu_agg.jatuh_tempo_tgl <=', $dates[1]);
            } else if (count($dates) === 1) {
                $builder->where('keu_agg.jatuh_tempo_tgl', $dates[0]);
            }
        }

        $searchValue = $request->getVar('search_value');
        if (!empty($searchValue)) {
            $builder->groupStart()
                ->like('c.nama_konsumen', $searchValue)
                ->orLike('k.no_kavling', $searchValue)
                ->orLike('j.nama_jalan', $searchValue)
                ->groupEnd();
        }

        $builder->orderBy('keu_agg.jatuh_tempo_tgl', 'ASC');
        $rekapData = $builder->get()->getResult();

        $spreadsheet = new Spreadsheet();
        
        // =====================================
        // SHEET 1: REKAP
        // =====================================
        $sheetRekap = $spreadsheet->getActiveSheet();
        $sheetRekap->setTitle('REKAP');

        $headersRekap = ['NO', 'KONSUMEN', 'BLOK / UNIT', 'NO KAVLING', 'TYPE', 'TUNAI/KPR', 'JATUH TEMPO TERDEKAT', 'KETERLAMBATAN (HARI)', 'TOTAL TAGIHAN', 'SUDAH BAYAR', 'SISA TAGIHAN', 'JUMLAH ITEM'];
        
        // Add headers
        $col = 'A';
        foreach ($headersRekap as $h) {
            $sheetRekap->setCellValue($col . '1', $h);
            $col++;
        }

        // Add Data
        $rowNum = 2;
        $no = 1;
        $mkdtIds = [];
        foreach ($rekapData as $row) {
            $mkdtIds[] = $row->id_mkdt;

            $diffTime = strtotime($today) - strtotime($row->jatuh_tempo_tgl);
            $diffDays = floor($diffTime / (60 * 60 * 24));
            
            $sheetRekap->setCellValue('A' . $rowNum, $no++);
            $sheetRekap->setCellValue('B' . $rowNum, $row->nama_konsumen);
            $sheetRekap->setCellValue('C' . $rowNum, $row->nama_jalan);
            $sheetRekap->setCellValueExplicit('D' . $rowNum, $row->no_kavling, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheetRekap->setCellValueExplicit('E' . $rowNum, $row->tipe_pricelist, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheetRekap->setCellValue('F' . $rowNum, $row->is_kpr === '1' || $row->is_kpr === 'KPR' ? 'KPR' : 'TUNAI');
            
            // Excel date formatting
            $excelDate = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel(strtotime($row->jatuh_tempo_tgl));
            $sheetRekap->setCellValue('G' . $rowNum, $excelDate);
            $sheetRekap->getStyle('G' . $rowNum)->getNumberFormat()->setFormatCode('dd-mmm-yyyy');

            $sheetRekap->setCellValue('H' . $rowNum, $diffDays);
            
            $sheetRekap->setCellValue('I' . $rowNum, (float)$row->total_tagihan);
            $sheetRekap->setCellValue('J' . $rowNum, (float)$row->sudah_bayar);
            $sheetRekap->setCellValue('K' . $rowNum, (float)$row->sisa_tagihan);
            
            $sheetRekap->getStyle('I'.$rowNum.':K'.$rowNum)->getNumberFormat()->setFormatCode('#,##0');
            
            $sheetRekap->setCellValue('L' . $rowNum, (int)$row->jumlah_tagihan);
            
            $rowNum++;
        }
        
        // Add Total Row
        if ($rowNum > 2) {
            $sheetRekap->setCellValue('A' . $rowNum, 'TOTAL');
            $sheetRekap->mergeCells('A' . $rowNum . ':H' . $rowNum);
            $sheetRekap->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheetRekap->setCellValue('I' . $rowNum, '=SUM(I2:I' . ($rowNum - 1) . ')');
            $sheetRekap->setCellValue('J' . $rowNum, '=SUM(J2:J' . ($rowNum - 1) . ')');
            $sheetRekap->setCellValue('K' . $rowNum, '=SUM(K2:K' . ($rowNum - 1) . ')');
            $sheetRekap->getStyle('A' . $rowNum . ':L' . $rowNum)->getFont()->setBold(true);
            $sheetRekap->getStyle('I' . $rowNum . ':K' . $rowNum)->getNumberFormat()->setFormatCode('#,##0');
        }
        
        // Header styling
        $lastColRekap = chr(ord('A') + count($headersRekap) - 1);
        $sheetRekap->getStyle('A1:' . $lastColRekap . '1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2057a3']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        
        foreach (range('A', $lastColRekap) as $c) {
            $sheetRekap->getColumnDimension($c)->setAutoSize(true);
        }
        $sheetRekap->freezePane('A2');

        // =====================================
        // SHEET 2: DETAIL
        // =====================================
        $sheetDetail = $spreadsheet->createSheet();
        $sheetDetail->setTitle('DETAIL');

        $headersDetail = ['NO', 'KONSUMEN', 'BLOK / UNIT', 'NO KAVLING', 'TYPE', 'TUNAI/KPR', 'BERITA ACARA', 'JATUH TEMPO', 'KETERLAMBATAN (HARI)', 'STATUS', 'NOMINAL', 'PEMBAYARAN', 'STATUS PEMBAYARAN'];
        
        $col = 'A';
        foreach ($headersDetail as $h) {
            $sheetDetail->setCellValue($col . '1', $h);
            $col++;
        }

        if (!empty($mkdtIds)) {
            $detailData = $this->keuRepo->select([
                'keuangan.id_mkdt', 'keuangan.berita_acara', 'keuangan.jatuh_tempo_tgl',
                'keuangan.nominal', 'keuangan.sudah_dibayar', 'keuangan.status', 'keuangan.is_void'
            ])
            ->whereIn('keuangan.id_mkdt', $mkdtIds)
            ->where('keuangan.sudah_dibayar', 0)
            ->where('keuangan.jatuh_tempo_tgl <=', $today)
            ->orderBy('keuangan.jatuh_tempo_tgl', 'ASC')
            ->findAll();

            // Group detail by mkdt
            $groupedDetail = [];
            foreach ($detailData as $d) {
                $groupedDetail[$d->id_mkdt][] = $d;
            }

            $rowNumD = 2;
            $noD = 1;
            foreach ($rekapData as $parent) {
                if (isset($groupedDetail[$parent->id_mkdt])) {
                    foreach ($groupedDetail[$parent->id_mkdt] as $child) {
                        $diffTimeD = strtotime($today) - strtotime($child->jatuh_tempo_tgl);
                        $diffDaysD = floor($diffTimeD / (60 * 60 * 24));
                        
                        $statusPembayaran = 'BELUM LUNAS';
                        if ($child->is_void == 1) $statusPembayaran = 'VOID';
                        else if ($child->sudah_dibayar == 1) $statusPembayaran = 'LUNAS';

                        $sheetDetail->setCellValue('A' . $rowNumD, $noD++);
                        $sheetDetail->setCellValue('B' . $rowNumD, $parent->nama_konsumen);
                        $sheetDetail->setCellValue('C' . $rowNumD, $parent->nama_jalan);
                        $sheetDetail->setCellValueExplicit('D' . $rowNumD, $parent->no_kavling, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                        $sheetDetail->setCellValueExplicit('E' . $rowNumD, $parent->tipe_pricelist, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                        $sheetDetail->setCellValue('F' . $rowNumD, $parent->is_kpr === '1' || $parent->is_kpr === 'KPR' ? 'KPR' : 'TUNAI');
                        
                        $sheetDetail->setCellValue('G' . $rowNumD, $child->berita_acara);
                        
                        $excelDateD = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel(strtotime($child->jatuh_tempo_tgl));
                        $sheetDetail->setCellValue('H' . $rowNumD, $excelDateD);
                        $sheetDetail->getStyle('H' . $rowNumD)->getNumberFormat()->setFormatCode('dd-mmm-yyyy');
                        
                        $sheetDetail->setCellValue('I' . $rowNumD, $diffDaysD);
                        $sheetDetail->setCellValue('J' . $rowNumD, $child->status);
                        
                        $sheetDetail->setCellValue('K' . $rowNumD, (float)$child->nominal);
                        // Note: The detail query doesn't have partial payment amount historically on child row unless mapped. We use 0 as specified in example if not available
                        $sheetDetail->setCellValue('L' . $rowNumD, (float)($child->sudah_dibayar == 1 ? $child->nominal : 0));
                        
                        $sheetDetail->getStyle('K'.$rowNumD.':L'.$rowNumD)->getNumberFormat()->setFormatCode('#,##0');
                        
                        $sheetDetail->setCellValue('M' . $rowNumD, $statusPembayaran);
                        
                        $rowNumD++;
                    }
                }
            }
        }

        $lastColDetail = chr(ord('A') + count($headersDetail) - 1);
        $sheetDetail->getStyle('A1:' . $lastColDetail . '1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2057a3']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        foreach (range('A', $lastColDetail) as $c) {
            $sheetDetail->getColumnDimension($c)->setAutoSize(true);
        }
        $sheetDetail->freezePane('A2');
        
        $spreadsheet->setActiveSheetIndex(0);

        ob_start();
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        $xlsData = ob_get_contents();
        ob_end_clean();

        return [
            'status' => true,
            'file'   => "data:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;base64," . base64_encode($xlsData)
        ];
    }


    // ---- Fitur Penagihan Baru ----
    
    public function getRiwayatTagihan($request): array
    {
        $id_mkdt = $request->getVar('id_mkdt');
        
        $riwayat = $this->db->table('invoice_log i')
            ->select('i.*, u.nama_karyawan as pembuat')
            ->join('karyawan u', 'u.id_user = i.add_by', 'left')
            ->where('i.id_mkdt', $id_mkdt)
            ->orderBy('i.date_add', 'DESC')
            ->get()->getResultArray();
            
        foreach ($riwayat as &$r) {
            $logs = $this->db->table('invoice_status_log l')
                ->select('l.*, k.nama_karyawan as pembuat')
                ->join('karyawan k', 'k.id_user = l.add_by', 'left')
                ->where('l.no_inv', $r['no_inv'])
                ->orderBy('l.date_add', 'ASC')
                ->orderBy('l.id', 'ASC')
                ->get()->getResultArray();

            $hasDibuat = false;
            foreach ($logs as $lg) {
                if (strtolower($lg['status']) === 'dibuat') {
                    $hasDibuat = true;
                    break;
                }
            }

            if (! $hasDibuat && ! empty($r['no_inv'])) {
                array_unshift($logs, [
                    'id' => 0,
                    'no_inv' => $r['no_inv'],
                    'status' => 'dibuat',
                    'tanggal' => $r['tanggal_invoice'] ?: date('Y-m-d', strtotime($r['date_add'])),
                    'keterangan' => 'Surat penagihan berhasil dibuat.',
                    'add_by' => $r['add_by'],
                    'date_add' => $r['date_add'],
                    'pembuat' => $r['pembuat'] ?: '-'
                ]);
            }

            $r['lifecycle'] = $logs;
        }
            
        return [
            'token' => csrf_hash(),
            'success' => true,
            'data' => $riwayat
        ];
    }
    
    public function simpanPenagihan($request, int $actorId): array
    {
        $response = [
            'token' => csrf_hash(),
            'success' => false,
            'messages' => 'Terjadi kesalahan saat menyimpan invoice',
        ];

        $id_mkdt = $request->getVar('id_mkdt');
        $id_konsumen = $request->getVar('id_konsumen');
        $id_kavling = $request->getVar('id_kavling');
        $id_kopsurat = $request->getVar('id_kopsurat');
        $tanggal_invoice = $request->getVar('tanggal_invoice');
        $tanggal_jatuh_tempo = $request->getVar('tanggal_jatuh_tempo');
        $terms = $request->getVar('terms');
        $tagihan = $request->getVar('tagihan'); // JSON array of items

        // Pastikan id_konsumen dan id_kavling selalu valid dengan auto-lookup dari mkdt jika kosong
        if (empty($id_konsumen) || empty($id_kavling)) {
            $mkdtRow = $this->db->table('mkdt')->where('id_mkdt', $id_mkdt)->get()->getRow();
            if ($mkdtRow) {
                if (empty($id_konsumen)) $id_konsumen = $mkdtRow->id_konsumen;
                if (empty($id_kavling)) $id_kavling = $mkdtRow->id_kavling;
            }
        }
        
        // Signature & input verification are removed
        $nomor_surat = $request->getVar('nomor_surat');
        
        if (empty($id_mkdt)) {
            $response['messages'] = 'Data transaksi/konsumen (MKDT) tidak ditemukan.';
            return $response;
        }

        // 1. Validasi Kop Surat
        if (empty($id_kopsurat)) {
            $response['messages'] = 'Silakan pilih Kop Surat terlebih dahulu.';
            return $response;
        }

        $kop = $this->db->table('kopsurat')->where('id', $id_kopsurat)->get()->getRow();
        if (!$kop) {
            $response['messages'] = 'Kop Surat yang dipilih tidak valid atau sudah dihapus.';
            return $response;
        }

        // 2. Validasi Item Tagihan
        if (empty($tagihan) || $tagihan === '[]') {
            $response['messages'] = 'Tidak ada item tagihan untuk dibuatkan invoice.';
            return $response;
        }
        
        // 3. Validasi Nomor Surat
        if (empty($nomor_surat) || trim((string)$nomor_surat) === '') {
            $response['messages'] = 'Nomor Surat wajib diisi.';
            return $response;
        }
        
        // Cek duplikat Nomor Surat
        $cekDuplikat = $this->db->table('invoice_log')
            ->where('nomor_surat', trim((string)$nomor_surat))
            ->get()->getRow();
            
        if ($cekDuplikat) {
            $response['messages'] = 'Nomor Surat ini sudah digunakan. Silakan masukkan nomor surat yang lain.';
            return $response;
        }
        

        // Generate No Invoice auto
        // e.g., INV/2026/10/0001
        $y = date('Y');
        $m = date('m');
        $prefix = "INV/$y/$m/";
        
        $lastInv = $this->db->table('invoice_log')
            ->where("no_inv LIKE '$prefix%'")
            ->orderBy('no_inv', 'DESC')
            ->get()->getRow();
            
        if ($lastInv) {
            $lastNum = (int) substr($lastInv->no_inv, -4);
            $newNum = str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNum = '0001';
        }
        $no_inv = $prefix . $newNum;

        $db = $this->db;
        $db->transException(true);

        try {
            $db->transStart();

            $saved = $db->table('invoice_log')->insert([
                'no_inv' => $no_inv,
                'nomor_surat' => trim((string)$nomor_surat),
                'id_mkdt' => $id_mkdt,
                'id_konsumen' => $id_konsumen,
                'id_kavling' => $id_kavling,
                'id_kopsurat' => $id_kopsurat,
                'tanggal_invoice' => $tanggal_invoice,
                'tanggal_jatuh_tempo' => $tanggal_jatuh_tempo,
                'tagihan' => $tagihan,
                'terms' => $terms,
                'status_tagihan' => 'dibuat',
                'add_by' => $actorId,
                'date_add' => date('Y-m-d H:i:s'),
            ]);

            if (! $saved) {
                throw new \RuntimeException('Gagal menambahkan invoice');
            }
            
            $db->table('invoice_status_log')->insert([
                'no_inv' => $no_inv,
                'status' => 'dibuat',
                'tanggal' => date('Y-m-d'),
                'keterangan' => 'Surat penagihan berhasil dibuat.',
                'add_by' => $actorId,
                'date_add' => date('Y-m-d H:i:s')
            ]);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Transaksi gagal');
            }

            return [
                'token' => csrf_hash(),
                'success' => true,
                'messages' => 'Tagihan berhasil dibuat',
            ];
        } catch (\Throwable $e) {
            try {
                $db->transRollback();
            } catch (\Throwable $rollbackError) {
                // Ignore rollback error
            }
            $response['messages'] = $e->getMessage();
            return $response;
        }
    }
    
    public function updateStatusPenagihan($request, int $actorId): array
    {
        $response = [
            'token' => csrf_hash(),
            'success' => false,
            'messages' => 'Terjadi kesalahan',
        ];

        $no_inv = $request->getVar('no_inv');
        $status_tagihan = $request->getVar('status_tagihan');
        $tanggal_ubah_status = $request->getVar('tanggal_ubah_status');
        $keterangan_status = $request->getVar('keterangan_status');
        
        if (empty($no_inv) || empty($status_tagihan)) {
            $response['messages'] = 'Data tidak lengkap';
            return $response;
        }
        
        $this->db->table('invoice_log')
            ->where('no_inv', $no_inv)
            ->update([
                'status_tagihan' => $status_tagihan,
                'tanggal_ubah_status' => $tanggal_ubah_status,
                'keterangan_status' => $keterangan_status,
                'date_edit' => date('Y-m-d H:i:s'),
                'edit_by' => $actorId
            ]);
            
        $this->db->table('invoice_status_log')->insert([
            'no_inv' => $no_inv,
            'status' => $status_tagihan,
            'tanggal' => $tanggal_ubah_status,
            'keterangan' => $keterangan_status,
            'add_by' => $actorId,
            'date_add' => date('Y-m-d H:i:s')
        ]);
            
        return [
            'token' => csrf_hash(),
            'success' => true,
            'messages' => 'Status berhasil diubah',
        ];
    }
    
    public function setTanggalKirim($no_inv): void
    {
        $inv = $this->db->table('invoice_log')->select('tanggal_kirim')->where('no_inv', $no_inv)->get()->getRow();
        if ($inv && empty($inv->tanggal_kirim)) {
            $this->db->table('invoice_log')->where('no_inv', $no_inv)->update(['tanggal_kirim' => date('Y-m-d H:i:s')]);
        }
    }
}