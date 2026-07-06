<?php

namespace App\Services;

use Hermawan\DataTables\DataTable;

class PencairanAkadService
{
    protected $db;
    protected FileAccessService $fileAccessService;
    protected FinanceLedgerService $ledgerService;
    protected HistoryService $historyService;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->fileAccessService = new FileAccessService();
        $this->ledgerService = new FinanceLedgerService();
        $this->historyService = new HistoryService();
    }

    public function getData(int $idMkdt, int $idKavling): array
    {
        $mkdt = $this->getMkdtContext($idMkdt, $idKavling);
        if (! $mkdt) {
            return $this->response(false, 'Data kavling tidak ditemukan');
        }

        $plan = $this->getPlanByMkdt($idMkdt);
        $lockedIds = $plan ? $this->getLockedItemIds((int) $plan->id) : [];
        $items = $plan ? $this->getItems((int) $plan->id, $lockedIds) : [];
        $pengajuan = $plan ? $this->listPengajuan((int) $plan->id) : [];

        return $this->response(true, 'Data berhasil dimuat', [
            'id_mkdt' => $idMkdt,
            'id_kavling' => $idKavling,
            'mkdt' => (object) [
                'harga_kpr_acc' => (float) ($mkdt->harga_kpr_acc ?? 0),
                'status_mkdt' => $mkdt->status_mkdt,
                'is_kpr' => (int) ($mkdt->is_kpr ?? 0),
            ],
            'plan' => $plan,
            'list_dajam' => $this->getListDajam(),
            'items' => $items,
            'list_pengajuan' => $pengajuan,
        ]);
    }

    public function saveRetensi(array $post, int $actorId): array
    {
        $idMkdt = (int) ($post['id_mkdt'] ?? 0);
        $idKavling = (int) ($post['id_kavling'] ?? 0);
        $rows = $post['retensi'] ?? [];

        $mkdt = $this->assertAkadContext($idMkdt, $idKavling);
        if (is_array($mkdt)) {
            return $mkdt;
        }

        $hargaKprAcc = (float) ($mkdt->harga_kpr_acc ?? 0);

        $db = $this->db;
        $db->transException(true);

        try {
            $db->transStart();

            $idPlan = $this->ensurePlan($idMkdt, $idKavling, $hargaKprAcc, $actorId);
            $lockedIds = $this->getLockedItemIds($idPlan);
            $existing = $this->getItemsForPlanJenis($idPlan, 'retensi');
            $existingById = [];
            foreach ($existing as $row) {
                $existingById[(int) $row->id] = $row;
            }

            $submittedIds = [];
            $finalRows = [];

            foreach ($rows as $row) {
                $idItem = (int) ($row['id'] ?? 0);
                $idListDajam = (int) ($row['id_list_dajam'] ?? 0);
                $nominal = $this->num($row['nominal'] ?? 0);
                $catatan = $row['catatan'] ?? null;

                if ($idItem > 0) {
                    $submittedIds[] = $idItem;
                    $current = $existingById[$idItem] ?? null;
                    if (! $current) {
                        throw new \RuntimeException('Item retensi tidak ditemukan');
                    }
                    if (in_array($idItem, $lockedIds, true)) {
                        if ((float) $current->nominal !== $nominal || $idListDajam !== (int) $current->id_list_dajam) {
                            throw new \RuntimeException('Item retensi yang sudah diajukan ke bank tidak bisa diubah');
                        }
                        $finalRows[] = ['id' => $idItem, 'nominal' => (float) $current->nominal];
                        continue;
                    }
                    if ($idListDajam <= 0 || $nominal <= 0) {
                        $db->table('pencairan_akad_item')->where('id', $idItem)->delete();
                        continue;
                    }
                    $db->table('pencairan_akad_item')->where('id', $idItem)->update([
                        'id_list_dajam' => $idListDajam,
                        'nominal' => $nominal,
                        'catatan' => $catatan,
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                    $finalRows[] = ['id' => $idItem, 'nominal' => $nominal];
                } else {
                    if ($idListDajam <= 0 || $nominal <= 0) {
                        continue;
                    }
                    $db->table('pencairan_akad_item')->insert([
                        'id_plan' => $idPlan,
                        'jenis' => 'retensi',
                        'id_list_dajam' => $idListDajam,
                        'nominal' => $nominal,
                        'catatan' => $catatan,
                        'add_by' => $actorId,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                    $finalRows[] = ['id' => (int) $db->insertID(), 'nominal' => $nominal];
                }
            }

            foreach ($existingById as $idItem => $current) {
                if (in_array($idItem, $submittedIds, true)) {
                    continue;
                }
                if (in_array($idItem, $lockedIds, true)) {
                    throw new \RuntimeException('Item retensi yang sudah diajukan ke bank tidak bisa dihapus');
                }
                $db->table('pencairan_akad_item')->where('id', $idItem)->delete();
            }

            $totalRetensi = array_sum(array_column($finalRows, 'nominal'));
            if ($totalRetensi > $hargaKprAcc) {
                throw new \RuntimeException('Total retensi tidak boleh melebihi ACC KPR');
            }

            $db->table('pencairan_akad_plan')->where('id', $idPlan)->update([
                'total_retensi' => $totalRetensi,
                'total_hasil_akad' => $hargaKprAcc - $totalRetensi,
                'edit_by' => $actorId,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            $this->saveHistory($idKavling, $idMkdt, $idPlan, null, 'simpan_retensi', 'Item retensi diperbarui', $finalRows, $actorId);

            $db->transComplete();
            if ($db->transStatus() === false) {
                throw new \RuntimeException('Transaksi gagal');
            }

            return $this->response(true, 'Retensi berhasil disimpan', ['id_plan' => $idPlan]);
        } catch (\Throwable $e) {
            try {
                $db->transRollback();
            } catch (\Throwable $rollback) {
            }
            log_message('error', '[PencairanAkadService::saveRetensi] {message}', ['message' => $e->getMessage()]);
            return $this->response(false, 'Gagal menyimpan retensi: ' . $e->getMessage());
        }
    }

    public function saveTenor(array $post, int $actorId): array
    {
        $idMkdt = (int) ($post['id_mkdt'] ?? 0);
        $idKavling = (int) ($post['id_kavling'] ?? 0);
        $rows = $post['tenor'] ?? [];

        $mkdt = $this->assertAkadContext($idMkdt, $idKavling);
        if (is_array($mkdt)) {
            return $mkdt;
        }

        $hargaKprAcc = (float) ($mkdt->harga_kpr_acc ?? 0);

        $db = $this->db;
        $db->transException(true);

        try {
            $db->transStart();

            $idPlan = $this->ensurePlan($idMkdt, $idKavling, $hargaKprAcc, $actorId);
            $plan = $this->getPlanById($idPlan);
            $hasilAkad = (float) $plan->total_hasil_akad;

            $lockedIds = $this->getLockedItemIds($idPlan);
            $existing = $this->getItemsForPlanJenis($idPlan, 'tenor');
            $existingById = [];
            foreach ($existing as $row) {
                $existingById[(int) $row->id] = $row;
            }

            $submittedIds = [];
            $finalRows = [];
            $urutan = 1;

            foreach ($rows as $row) {
                $idItem = (int) ($row['id'] ?? 0);
                $nominal = $this->num($row['nominal'] ?? 0);
                $catatan = $row['catatan'] ?? null;

                if ($idItem > 0) {
                    $submittedIds[] = $idItem;
                    $current = $existingById[$idItem] ?? null;
                    if (! $current) {
                        throw new \RuntimeException('Item tenor tidak ditemukan');
                    }
                    if (in_array($idItem, $lockedIds, true)) {
                        if ((float) $current->nominal !== $nominal) {
                            throw new \RuntimeException('Item tenor yang sudah diajukan ke bank tidak bisa diubah');
                        }
                        $finalRows[] = ['id' => $idItem, 'nominal' => (float) $current->nominal];
                        $urutan++;
                        continue;
                    }
                    if ($nominal <= 0) {
                        $db->table('pencairan_akad_item')->where('id', $idItem)->delete();
                        continue;
                    }
                    $db->table('pencairan_akad_item')->where('id', $idItem)->update([
                        'nominal' => $nominal,
                        'catatan' => $catatan,
                        'urutan_tenor' => $urutan,
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                    $finalRows[] = ['id' => $idItem, 'nominal' => $nominal];
                    $urutan++;
                } else {
                    if ($nominal <= 0) {
                        continue;
                    }
                    $db->table('pencairan_akad_item')->insert([
                        'id_plan' => $idPlan,
                        'jenis' => 'tenor',
                        'urutan_tenor' => $urutan,
                        'nominal' => $nominal,
                        'catatan' => $catatan,
                        'add_by' => $actorId,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                    $finalRows[] = ['id' => (int) $db->insertID(), 'nominal' => $nominal];
                    $urutan++;
                }
            }

            foreach ($existingById as $idItem => $current) {
                if (in_array($idItem, $submittedIds, true)) {
                    continue;
                }
                if (in_array($idItem, $lockedIds, true)) {
                    throw new \RuntimeException('Item tenor yang sudah diajukan ke bank tidak bisa dihapus');
                }
                $db->table('pencairan_akad_item')->where('id', $idItem)->delete();
            }

            $totalTenor = array_sum(array_column($finalRows, 'nominal'));
            if ($totalTenor > $hasilAkad + 0.01) {
                throw new \RuntimeException('Total tenor tidak boleh melebihi hasil akad (Rp ' . number_format($hasilAkad, 0, ',', '.') . ')');
            }

            $db->table('pencairan_akad_plan')->where('id', $idPlan)->update([
                'edit_by' => $actorId,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            $this->saveHistory($idKavling, $idMkdt, $idPlan, null, 'simpan_tenor', 'Item tenor hasil akad diperbarui', $finalRows, $actorId);

            $db->transComplete();
            if ($db->transStatus() === false) {
                throw new \RuntimeException('Transaksi gagal');
            }

            return $this->response(true, 'Tenor berhasil disimpan', ['id_plan' => $idPlan]);
        } catch (\Throwable $e) {
            try {
                $db->transRollback();
            } catch (\Throwable $rollback) {
            }
            log_message('error', '[PencairanAkadService::saveTenor] {message}', ['message' => $e->getMessage()]);
            return $this->response(false, 'Gagal menyimpan tenor: ' . $e->getMessage());
        }
    }

    public function storePengajuan($request, int $actorId): array
    {
        $idPlan = (int) $request->getPost('id_plan');
        $items = $request->getPost('items');
        $tanggalPengajuan = trim((string) $request->getPost('tanggal_pengajuan'));
        $tanggalRencanaCair = trim((string) $request->getPost('tanggal_rencana_cair'));

        if ($idPlan <= 0 || ! is_array($items) || empty($items)) {
            return $this->response(false, 'Pilih minimal satu item untuk diajukan');
        }
        if ($tanggalPengajuan === '') {
            return $this->response(false, 'Tanggal pengajuan harus diisi');
        }

        $plan = $this->getPlanById($idPlan);
        if (! $plan) {
            return $this->response(false, 'Plan tidak ditemukan');
        }

        $db = $this->db;
        $db->transException(true);

        try {
            $db->transStart();

            $lampiranSurat = null;
            $file = $request->getFile('lampiran_surat');
            if ($file && $file->getError() !== UPLOAD_ERR_NO_FILE) {
                if (! $file->isValid()) {
                    throw new \RuntimeException('Lampiran surat tidak valid');
                }
                if (! $file->hasMoved()) {
                    $lampiranSurat = $this->fileAccessService->store($file, 'uploads/pencairan_akad/' . date('Ymd'));
                }
            }
            if (! $lampiranSurat) {
                throw new \RuntimeException('Lampiran surat wajib diisi untuk pengajuan');
            }

            $total = 0;
            $rows = [];
            foreach ($items as $idItem) {
                $item = $this->getItemById((int) $idItem, $idPlan);
                if (! $item) {
                    throw new \RuntimeException('Item tidak ditemukan');
                }

                $sisa = $this->getSisaItem((int) $item->id, $this->num($item->nominal));
                if ($sisa <= 0) {
                    throw new \RuntimeException('Item sudah diajukan penuh: ' . ($item->jenis ?? ''));
                }

                $rows[] = ['item' => $item, 'nominal' => $sisa];
                $total += $sisa;
            }

            $db->table('pencairan_akad_pengajuan')->insert([
                'id_plan' => $idPlan,
                'tanggal_pengajuan' => $tanggalPengajuan,
                'tanggal_rencana_cair' => $tanggalRencanaCair ?: null,
                'catatan' => $request->getPost('catatan'),
                'lampiran_surat' => $lampiranSurat,
                'total_pengajuan' => $total,
                'status' => 'active',
                'add_by' => $actorId,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            $idPengajuan = (int) $db->insertID();

            foreach ($rows as $row) {
                $db->table('pencairan_akad_pengajuan_detail')->insert([
                    'id_pengajuan' => $idPengajuan,
                    'id_item' => (int) $row['item']->id,
                    'nominal_pengajuan' => $row['nominal'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }

            $db->table('pencairan_akad_plan')->where('id', $idPlan)->update([
                'status' => 'diajukan',
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            $this->saveHistory((int) $plan->id_kavling, (int) $plan->id_mkdt, $idPlan, $idPengajuan, 'pengajuan', 'Pengajuan pencairan akad dibuat', $rows, $actorId);

            $db->transComplete();
            if ($db->transStatus() === false) {
                throw new \RuntimeException('Transaksi gagal');
            }

            return $this->response(true, 'Pengajuan berhasil disimpan', ['id_pengajuan' => $idPengajuan]);
        } catch (\Throwable $e) {
            try {
                $db->transRollback();
            } catch (\Throwable $rollback) {
            }
            log_message('error', '[PencairanAkadService::storePengajuan] {message}', ['message' => $e->getMessage()]);
            return $this->response(false, 'Gagal menyimpan pengajuan: ' . $e->getMessage());
        }
    }

    public function cairkan($request, int $actorId): array
    {
        $idPengajuan = (int) $request->getPost('id_pengajuan');
        $tanggalCair = trim((string) $request->getPost('tanggal_cair'));
        $details = $request->getPost('details');

        if ($idPengajuan <= 0 || ! is_array($details) || empty($details)) {
            return $this->response(false, 'Data pencairan tidak lengkap');
        }
        if ($tanggalCair === '') {
            return $this->response(false, 'Tanggal cair harus diisi');
        }

        $pengajuan = $this->db->table('pencairan_akad_pengajuan')->where('id', $idPengajuan)->get()->getRow();
        if (! $pengajuan) {
            return $this->response(false, 'Pengajuan tidak ditemukan');
        }
        if (in_array($pengajuan->status, ['paid', 'void'], true)) {
            return $this->response(false, 'Pengajuan ini sudah ' . $pengajuan->status);
        }

        $db = $this->db;
        $db->transException(true);

        try {
            $db->transStart();

            $totalCairBaru = 0;
            $db->table('pencairan_akad_payment')->insert([
                'id_pengajuan' => $idPengajuan,
                'tanggal_cair' => $tanggalCair,
                'catatan' => $request->getPost('catatan'),
                'total_cair' => 0,
                'add_by' => $actorId,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            $idPayment = (int) $db->insertID();

            foreach ($details as $idDetail => $row) {
                if (! is_array($row)) {
                    continue;
                }
                $nominalCair = $this->num($row['nominal_cair'] ?? 0);
                if ($nominalCair <= 0) {
                    continue;
                }

                $detail = $db->table('pencairan_akad_pengajuan_detail')
                    ->where('id', (int) $idDetail)
                    ->where('id_pengajuan', $idPengajuan)
                    ->get()
                    ->getRow();
                if (! $detail) {
                    throw new \RuntimeException('Detail pengajuan tidak ditemukan');
                }

                $sisaDetail = $this->num($detail->nominal_pengajuan) - $this->num($detail->nominal_cair);
                if ($nominalCair > $sisaDetail + 0.01) {
                    throw new \RuntimeException('Nominal cair melebihi sisa pengajuan item');
                }

                $db->table('pencairan_akad_pengajuan_detail')->where('id', (int) $detail->id)->update([
                    'nominal_cair' => $this->num($detail->nominal_cair) + $nominalCair,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

                $db->table('pencairan_akad_payment_detail')->insert([
                    'id_payment' => $idPayment,
                    'id_pengajuan_detail' => (int) $detail->id,
                    'nominal_cair' => $nominalCair,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
                $idPaymentDetail = (int) $db->insertID();

                $this->ledgerService->recordIncomeFromPencairanAkadPaymentDetail($idPaymentDetail, $actorId);
                $totalCairBaru += $nominalCair;
            }

            if ($totalCairBaru <= 0) {
                throw new \RuntimeException('Tidak ada nominal cair yang diisi');
            }

            $db->table('pencairan_akad_payment')->where('id', $idPayment)->update(['total_cair' => $totalCairBaru]);

            $totalCairPengajuan = (float) $this->db->table('pencairan_akad_pengajuan_detail')
                ->selectSum('nominal_cair')
                ->where('id_pengajuan', $idPengajuan)
                ->get()
                ->getRow()->nominal_cair;

            $statusBaru = $totalCairPengajuan >= (float) $pengajuan->total_pengajuan - 0.01 ? 'paid' : 'partial';

            $db->table('pencairan_akad_pengajuan')->where('id', $idPengajuan)->update([
                'total_cair' => $totalCairPengajuan,
                'status' => $statusBaru,
                'edit_by' => $actorId,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            $plan = $this->getPlanById((int) $pengajuan->id_plan);
            $this->saveHistory((int) $plan->id_kavling, (int) $plan->id_mkdt, (int) $plan->id, $idPengajuan, 'pencairan', 'Pencairan akad dicatat', $details, $actorId);

            $db->transComplete();
            if ($db->transStatus() === false) {
                throw new \RuntimeException('Transaksi gagal');
            }

            return $this->response(true, 'Pencairan berhasil disimpan', ['id_pengajuan' => $idPengajuan, 'status' => $statusBaru]);
        } catch (\Throwable $e) {
            try {
                $db->transRollback();
            } catch (\Throwable $rollback) {
            }
            log_message('error', '[PencairanAkadService::cairkan] {message}', ['message' => $e->getMessage()]);
            return $this->response(false, 'Gagal menyimpan pencairan: ' . $e->getMessage());
        }
    }

    public function void(int $idPengajuan, string $reason, int $actorId): array
    {
        $pengajuan = $this->db->table('pencairan_akad_pengajuan')->where('id', $idPengajuan)->get()->getRow();
        if (! $pengajuan) {
            return $this->response(false, 'Pengajuan tidak ditemukan');
        }
        if ((float) $pengajuan->total_cair > 0) {
            return $this->response(false, 'Pengajuan yang sudah ada pencairan tidak bisa di-void');
        }

        $this->db->table('pencairan_akad_pengajuan')->where('id', $idPengajuan)->update([
            'status' => 'void',
            'void_reason' => $reason,
            'edit_by' => $actorId,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $plan = $this->getPlanById((int) $pengajuan->id_plan);
        if ($plan) {
            $this->saveHistory((int) $plan->id_kavling, (int) $plan->id_mkdt, (int) $plan->id, $idPengajuan, 'void', 'Pengajuan dibatalkan: ' . $reason, [], $actorId);
        }

        return $this->response(true, 'Pengajuan berhasil dibatalkan');
    }

    public function getListGrouped($request)
    {
        $pengajuanAgg = $this->db->table('pencairan_akad_pengajuan')
            ->select("id_plan,
                      SUM(CASE WHEN status IN ('active','partial') THEN total_pengajuan - total_cair ELSE 0 END) AS outstanding,
                      SUM(CASE WHEN status != 'void' THEN total_cair ELSE 0 END) AS total_cair")
            ->groupBy('id_plan')
            ->getCompiledSelect();

        $builder = $this->db->table('mkdt m')
            ->select('
                m.id_mkdt, m.id_kavling, m.akad_tgl, m.harga_kpr_acc, m.is_kpr,
                c.nama_konsumen, j.nama_jalan, k.no_kavling, tipe.tipe_rumah,
                hj.hargajual, p.nama_proyek,
                pap.id AS id_plan,
                COALESCE(pap.total_hasil_akad, m.harga_kpr_acc) AS total_hasil_akad,
                COALESCE(pg.outstanding, 0) AS pengajuan_outstanding,
                COALESCE(pg.total_cair, 0) AS sudah_cair
            ')
            ->join('kavling k', 'k.id_mkdt = m.id_mkdt')
            ->join('jalan j', 'j.id_jalan = k.id_jalan')
            ->join('cluster cl', 'cl.id_cluster = j.id_cluster')
            ->join('proyek p', 'p.id_proyek = cl.id_proyek')
            ->join('konsumen c', 'c.id_konsumen = m.id_konsumen')
            ->join('hargajual hj', 'hj.id = k.harga_akhir', 'left')
            ->join('tipe', 'tipe.id_tipe = k.id_tipe', 'left')
            ->join('pencairan_akad_plan pap', 'pap.id_mkdt = m.id_mkdt', 'left')
            ->join("({$pengajuanAgg}) pg", 'pg.id_plan = pap.id', 'left')
            ->where('m.status_mkdt', 'Akad')
            ->where('m.is_kpr', 1);

        $idProyek = resolve_active_proyek_id($request->getVar('id_proyek'));
        if ($idProyek) {
            $builder->where('p.id_proyek', $idProyek);
        }
        if ($request->getVar('id_cluster')) {
            $builder->where('cl.id_cluster', $request->getVar('id_cluster'));
        }
        if ($request->getVar('id_jalan')) {
            $builder->where('j.id_jalan', $request->getVar('id_jalan'));
        }

        $statusCair = $request->getVar('status_cair');
        if ($statusCair === 'belum_cair') {
            $builder->where("m.harga_kpr_acc - COALESCE(pg.total_cair, 0) > 0.01", null, false);
        } elseif ($statusCair === 'sudah_cair') {
            $builder->where("m.harga_kpr_acc - COALESCE(pg.total_cair, 0) <= 0.01", null, false);
        }

        return DataTable::of($builder)
            ->setSearchableColumns(['c.nama_konsumen', 'k.no_kavling', 'j.nama_jalan'])
            ->add('Aksi', function ($v) {
                $sh = htmlspecialchars(json_encode([
                    'id_kavling' => $v->id_kavling,
                    'id_mkdt' => $v->id_mkdt,
                    'nama_proyek' => $v->nama_proyek,
                    'nama_jalan' => $v->nama_jalan,
                    'no_kavling' => $v->no_kavling,
                ]), ENT_QUOTES, 'UTF-8');

                return '<button type="button" class="btn btn-primary btn-sm" onclick="openPencairanAkadModal(' . $sh . ')"><i class="fas fa-hand-holding-usd mr-25"></i> Kelola</button>';
            }, 'first')
            ->addNumbering('no')
            ->edit('akad_tgl', fn ($v) => $this->format_tgl($v->akad_tgl))
            ->edit('hargajual', fn ($v) => number_format((float) $v->hargajual))
            ->edit('harga_kpr_acc', fn ($v) => (float) $v->harga_kpr_acc > 0
                ? 'Rp ' . number_format((float) $v->harga_kpr_acc)
                : '<span class="badge badge-warning">ACC KPR Belum Diisi</span>')
            ->edit('pengajuan_outstanding', fn ($v) => '<span class="text-warning font-weight-bold">Rp ' . number_format((float) $v->pengajuan_outstanding) . '</span>')
            ->edit('sudah_cair', fn ($v) => '<span class="text-success font-weight-bold">Rp ' . number_format((float) $v->sudah_cair) . '</span>')
            ->add('sisa', fn ($v) => '<span class="text-danger font-weight-bold">Rp ' . number_format((float) $v->harga_kpr_acc - (float) $v->sudah_cair) . '</span>')
            ->toJson(true);
    }

    public function getListDetail(int $idMkdt): array
    {
        $plan = $this->getPlanByMkdt($idMkdt);
        if (! $plan) {
            return ['token' => csrf_hash(), 'success' => true, 'items' => [], 'pengajuan' => []];
        }

        $lockedIds = $this->getLockedItemIds((int) $plan->id);

        return [
            'token' => csrf_hash(),
            'success' => true,
            'items' => $this->getItems((int) $plan->id, $lockedIds),
            'pengajuan' => $this->listPengajuan((int) $plan->id),
        ];
    }

    protected function format_tgl($tgl)
    {
        if ($tgl == '' || $tgl == '0000-00-00' || $tgl == null) {
            return '-';
        }

        return date_format(date_create($tgl), 'd-M-Y');
    }

    public function getHistory(int $idKavling): array
    {
        $result = $this->historyService->getList([
            'module' => 'pencairan_akad',
            'id_kavling' => $idKavling,
        ], 1000, 0);

        return [
            'token' => csrf_hash(),
            'success' => true,
            'data' => $result['data'],
        ];
    }

    protected function assertAkadContext(int $idMkdt, int $idKavling)
    {
        if ($idMkdt <= 0 || $idKavling <= 0) {
            return $this->response(false, 'Data kavling tidak lengkap');
        }

        $mkdt = $this->getMkdtContext($idMkdt, $idKavling);
        if (! $mkdt || ($mkdt->status_mkdt ?? null) !== 'Akad') {
            return $this->response(false, 'Plan pencairan hanya bisa dibuat untuk kavling dengan status Akad');
        }

        $hargaKprAcc = (float) ($mkdt->harga_kpr_acc ?? 0);
        if ((int) ($mkdt->is_kpr ?? 0) === 1 && $hargaKprAcc <= 0) {
            return $this->response(false, 'Nominal ACC KPR masih 0, hubungi MKDT untuk memastikan nominal tersebut');
        }

        return $mkdt;
    }

    protected function ensurePlan(int $idMkdt, int $idKavling, float $hargaKprAcc, int $actorId): int
    {
        $plan = $this->getPlanByMkdt($idMkdt);
        if ($plan) {
            $this->db->table('pencairan_akad_plan')->where('id', (int) $plan->id)->update([
                'harga_kpr_acc' => $hargaKprAcc,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            return (int) $plan->id;
        }

        $this->db->table('pencairan_akad_plan')->insert([
            'id_mkdt' => $idMkdt,
            'id_kavling' => $idKavling,
            'harga_kpr_acc' => $hargaKprAcc,
            'total_retensi' => 0,
            'total_hasil_akad' => $hargaKprAcc,
            'status' => 'draft',
            'add_by' => $actorId,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return (int) $this->db->insertID();
    }

    protected function getLockedItemIds(int $idPlan): array
    {
        $rows = $this->db->table('pencairan_akad_item pi')
            ->select('DISTINCT pi.id', false)
            ->join('pencairan_akad_pengajuan_detail pgd', 'pgd.id_item = pi.id')
            ->join('pencairan_akad_pengajuan pg', 'pg.id = pgd.id_pengajuan')
            ->where('pi.id_plan', $idPlan)
            ->where('pg.status !=', 'void')
            ->get()
            ->getResultArray();

        return array_map('intval', array_column($rows, 'id'));
    }

    protected function getItemsForPlanJenis(int $idPlan, string $jenis): array
    {
        return $this->db->table('pencairan_akad_item')
            ->where('id_plan', $idPlan)
            ->where('jenis', $jenis)
            ->get()
            ->getResult();
    }

    protected function getMkdtContext(int $idMkdt, int $idKavling): ?object
    {
        return $this->db->table('mkdt')
            ->select('id_mkdt, id_konsumen, status_mkdt, harga_kpr_acc, is_kpr')
            ->where('id_mkdt', $idMkdt)
            ->get()
            ->getRow();
    }

    protected function getPlanByMkdt(int $idMkdt): ?object
    {
        return $this->db->table('pencairan_akad_plan')->where('id_mkdt', $idMkdt)->get()->getRow();
    }

    protected function getPlanById(int $idPlan): ?object
    {
        return $this->db->table('pencairan_akad_plan')->where('id', $idPlan)->get()->getRow();
    }

    protected function getItems(int $idPlan, array $lockedIds = []): array
    {
        $items = $this->db->table('pencairan_akad_item pi')
            ->select('pi.*, ld.nama_jaminan')
            ->join('list_dajam ld', 'ld.id = pi.id_list_dajam', 'left')
            ->where('pi.id_plan', $idPlan)
            ->orderBy('pi.jenis', 'ASC')
            ->orderBy('pi.urutan_tenor', 'ASC')
            ->get()
            ->getResult();

        foreach ($items as $item) {
            $item->is_locked = in_array((int) $item->id, $lockedIds, true);
            $item->sisa = $this->getSisaItem((int) $item->id, $this->num($item->nominal));
        }

        return $items;
    }

    protected function getItemById(int $idItem, int $idPlan): ?object
    {
        return $this->db->table('pencairan_akad_item')
            ->where('id', $idItem)
            ->where('id_plan', $idPlan)
            ->get()
            ->getRow();
    }

    protected function getSisaItem(int $idItem, float $nominalItem): float
    {
        $sudahDiajukan = (float) $this->db->table('pencairan_akad_pengajuan_detail pgd')
            ->selectSum('pgd.nominal_pengajuan')
            ->join('pencairan_akad_pengajuan pg', 'pg.id = pgd.id_pengajuan', 'left')
            ->where('pgd.id_item', $idItem)
            ->where('pg.status !=', 'void')
            ->get()
            ->getRow()->nominal_pengajuan;

        return $nominalItem - $sudahDiajukan;
    }

    protected function getListDajam(): array
    {
        return $this->db->table('list_dajam')
            ->where('deleted_at', null)
            ->orderBy('id', 'ASC')
            ->get()
            ->getResult();
    }

    protected function listPengajuan(int $idPlan): array
    {
        $rows = $this->db->table('pencairan_akad_pengajuan pg')
            ->select('pg.*, u.username as add_by_name')
            ->join('users u', 'u.id = pg.add_by', 'left')
            ->where('pg.id_plan', $idPlan)
            ->orderBy('pg.created_at', 'DESC')
            ->get()
            ->getResultArray();

        foreach ($rows as &$row) {
            $id = (int) $row['id'];
            if (! empty($row['lampiran_surat'])) {
                $row['access_url'] = $this->fileAccessService->accessUrl('pencairan_akad', $id);
                $row['download_url'] = $this->fileAccessService->accessUrl('pencairan_akad', $id, true);
            }
            $row['details'] = $this->db->table('pencairan_akad_pengajuan_detail pgd')
                ->select('pgd.*, pi.jenis, pi.urutan_tenor, pi.catatan as item_catatan, ld.nama_jaminan')
                ->join('pencairan_akad_item pi', 'pi.id = pgd.id_item', 'left')
                ->join('list_dajam ld', 'ld.id = pi.id_list_dajam', 'left')
                ->where('pgd.id_pengajuan', $id)
                ->get()
                ->getResultArray();
        }

        return $rows;
    }

    protected function saveHistory(int $idKavling, int $idMkdt, ?int $idPlan, ?int $idPengajuan, string $aksi, string $deskripsi, $snapshot, int $actorId): void
    {
        $this->historyService->log('pencairan_akad', [
            'reference_type' => $idPengajuan ? 'pengajuan_akad' : 'plan_akad',
            'reference_id' => $idPengajuan ?: $idPlan,
            'id_kavling' => $idKavling,
            'action' => $aksi,
            'summary' => $deskripsi,
            'new_data' => $snapshot,
            'metadata' => [
                'id_mkdt' => $idMkdt,
                'id_plan' => $idPlan,
                'id_pengajuan' => $idPengajuan,
            ],
            'add_by' => $actorId,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    protected function response(bool $success, string $message, array $extra = []): array
    {
        return array_merge([
            'token' => csrf_hash(),
            'success' => $success,
            'message' => $message,
            'messages' => $message,
        ], $extra);
    }

    protected function num($value): float
    {
        if ($value === null || $value === '') {
            return 0;
        }

        return (float) str_replace(',', '', (string) $value);
    }
}
