<?php

namespace App\Services\Keuangan;

use App\Enums\NotificationEvent;
use App\Repositories\Keuangan\PencairanAkadListRepository;
use App\Services\AuditTrail\HistoryService;
use App\Services\FileAccessService;
use App\Services\Notifikasi\NotifikasiService;
use Hermawan\DataTables\DataTable;

class PencairanAkadService
{
    protected $db;
    protected FileAccessService $fileAccessService;
    protected FinanceLedgerService $ledgerService;
    protected HistoryService $historyService;
    protected NotifikasiService $notifikasiService;
    protected PencairanAkadListRepository $listRepository;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->fileAccessService = new FileAccessService();
        $this->ledgerService = new FinanceLedgerService();
        $this->historyService = new HistoryService();
        $this->notifikasiService = new NotifikasiService();
        $this->listRepository = new PencairanAkadListRepository($this->db);
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
                'bank' => $mkdt->bank ?? null,
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

            $existingTenorTotal = (float) $this->db->table('pencairan_akad_item')
                ->selectSum('nominal')
                ->where('id_plan', $idPlan)
                ->where('jenis', 'tenor')
                ->get()->getRow()->nominal;

            if ($totalRetensi + $existingTenorTotal > $hargaKprAcc + 0.01) {
                throw new \RuntimeException('Total retensi dan pencairan dana akad bersih melebihi total plafon ACC KPR (Rp ' . number_format($hargaKprAcc, 0, ',', '.') . ')');
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
        $file = $request->getFile('lampiran_surat');
        if ($file && $file->getError() !== UPLOAD_ERR_NO_FILE && ! $file->isValid()) {
            return $this->response(false, 'Lampiran surat tidak valid');
        }
        $lampiranSurat = $this->resolveLampiranSurat($file);

        return $this->storePengajuanCore([
            'id_plan' => (int) $request->getPost('id_plan'),
            'items' => $request->getPost('items'),
            'tanggal_pengajuan' => trim((string) $request->getPost('tanggal_pengajuan')),
            'tanggal_rencana_cair' => trim((string) $request->getPost('tanggal_rencana_cair')) ?: null,
            'catatan' => $request->getPost('catatan'),
        ], $lampiranSurat, $actorId, false);
    }

    protected function storePengajuanCore(array $data, ?string $lampiranSurat, int $actorId, bool $requireLampiran): array
    {
        $idPlan = (int) ($data['id_plan'] ?? 0);
        $items = $data['items'] ?? [];
        $tanggalPengajuan = trim((string) ($data['tanggal_pengajuan'] ?? ''));
        $tanggalRencanaCair = $data['tanggal_rencana_cair'] ?? null;

        if ($idPlan <= 0 || ! is_array($items) || empty($items)) {
            return $this->response(false, 'Pilih minimal satu item untuk diajukan');
        }
        if ($tanggalPengajuan === '') {
            return $this->response(false, 'Tanggal pengajuan harus diisi');
        }
        if ($requireLampiran && ! $lampiranSurat) {
            return $this->response(false, 'Lampiran surat wajib diisi untuk pengajuan');
        }

        $plan = $this->getPlanById($idPlan);
        if (! $plan) {
            return $this->response(false, 'Plan tidak ditemukan');
        }

        $db = $this->db;
        $db->transException(true);

        try {
            $db->transStart();

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

            $idBatch = (int) ($data['id_batch'] ?? 0);
            if ($idBatch <= 0) {
                $nomorBatch = $this->generateBatchNumber();
                $db->table('pencairan_akad_batch')->insert([
                    'nomor_batch'          => $nomorBatch,
                    'tanggal_pengajuan'    => $tanggalPengajuan,
                    'tanggal_rencana_cair' => $tanggalRencanaCair ?: null,
                    'catatan'              => $data['catatan'] ?? null,
                    'lampiran_surat'       => $lampiranSurat,
                    'total_pengajuan'      => $total,
                    'total_cair'           => 0,
                    'status'               => 'active',
                    'add_by'               => $actorId,
                    'created_at'           => date('Y-m-d H:i:s'),
                    'updated_at'           => date('Y-m-d H:i:s'),
                ]);
                $idBatch = (int) $db->insertID();

                $this->saveBatchHistory($idBatch, 'buat_pengajuan_single', 'Pengajuan pencairan ' . $nomorBatch . ' dibuat sebesar Rp ' . number_format($total, 0, ',', '.'), [
                    'nomor_batch'     => $nomorBatch,
                    'total_pengajuan' => $total,
                    'id_plan'         => $idPlan,
                ], $actorId);
            }

            $db->table('pencairan_akad_pengajuan')->insert([
                'id_batch' => $idBatch,
                'id_plan' => $idPlan,
                'tanggal_pengajuan' => $tanggalPengajuan,
                'tanggal_rencana_cair' => $tanggalRencanaCair ?: null,
                'catatan' => $data['catatan'] ?? null,
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
        return $this->cairkanCore(
            (int) $request->getPost('id_pengajuan'),
            trim((string) $request->getPost('tanggal_cair')),
            $request->getPost('details') ?: [],
            $request->getPost('catatan'),
            $actorId
        );
    }

    protected function cairkanCore(int $idPengajuan, string $tanggalCair, array $details, ?string $catatan, int $actorId): array
    {
        if (function_exists('in_groups') && !in_groups(['1', '3'])) {
            return $this->response(false, 'Akses ditolak: Hanya user Keuangan atau Administrator yang dapat memproses pencairan.');
        }

        if ($idPengajuan <= 0 || empty($details)) {
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
                'catatan' => $catatan,
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

            $this->syncBatchStatus((int) ($pengajuan->id_batch ?? 0), $actorId);

            $plan = $this->getPlanById((int) $pengajuan->id_plan);
            $this->saveHistory((int) $plan->id_kavling, (int) $plan->id_mkdt, (int) $plan->id, $idPengajuan, 'pencairan', 'Pencairan akad dicatat', $details, $actorId);

            $kavData = $db->table('kavling k')
                ->select('k.no_kavling, cl.id_proyek, m.id_konsumen')
                ->join('jalan j', 'j.id_jalan = k.id_jalan')
                ->join('cluster cl', 'cl.id_cluster = j.id_cluster')
                ->join('mkdt m', 'm.id_mkdt = k.id_mkdt', 'left')
                ->where('k.id_kavling', (int) $plan->id_kavling)
                ->get()
                ->getRow();

            if ($kavData) {
                $notifText = 'Pencairan hasil akad untuk kavling ' . $kavData->no_kavling . ' telah berhasil diproses sebesar Rp ' . number_format($totalCairBaru, 0, ',', '.');
                $this->notifikasiService->tambah_notif(
                    '3;4;9',
                    $notifText,
                    $actorId,
                    (int) $plan->id_kavling,
                    (int) ($kavData->id_konsumen ?? 0),
                    NotificationEvent::PENCAIRAN_HASIL_AKAD,
                    (int) $kavData->id_proyek,
                    'keuangan/hasil-akad/list',
                    NotificationEvent::PENCAIRAN_HASIL_AKAD
                );
            }

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

    public function listPayment(int $idPengajuan): array
    {
        $payments = $this->db->table('pencairan_akad_payment')
            ->where('id_pengajuan', $idPengajuan)
            ->where('total_cair >', 0)
            ->orderBy('id', 'DESC')
            ->get()
            ->getResult();

        return $this->response(true, 'Daftar pembayaran', ['payments' => $payments]);
    }

    public function voidPayment(int $idPayment, string $reason, int $actorId): array
    {
        if (function_exists('in_groups') && !in_groups(['1', '3'])) {
            return $this->response(false, 'Akses ditolak: Hanya user Keuangan atau Administrator yang dapat membatalkan pencairan.');
        }

        $payment = $this->db->table('pencairan_akad_payment')->where('id', $idPayment)->get()->getRow();
        if (! $payment) {
            return $this->response(false, 'Data pencairan tidak ditemukan');
        }
        if ((float) $payment->total_cair <= 0) {
            return $this->response(false, 'Pencairan ini sudah dibatalkan atau bernilai 0');
        }

        $pengajuan = $this->db->table('pencairan_akad_pengajuan')->where('id', $payment->id_pengajuan)->get()->getRow();
        if (! $pengajuan) {
            return $this->response(false, 'Pengajuan tidak ditemukan');
        }

        $db = $this->db;
        $db->transException(true);

        try {
            $db->transStart();

            $details = $db->table('pencairan_akad_payment_detail')->where('id_payment', $idPayment)->get()->getResult();

            foreach ($details as $detail) {
                // Rollback Ledger
                $this->ledgerService->voidByPencairanAkadPaymentDetail((int) $detail->id, $actorId);

                // Reduce nominal_cair on pengajuan_detail
                $pd = $db->table('pencairan_akad_pengajuan_detail')->where('id', $detail->id_pengajuan_detail)->get()->getRow();
                if ($pd) {
                    $newCair = $this->num($pd->nominal_cair) - $this->num($detail->nominal_cair);
                    $db->table('pencairan_akad_pengajuan_detail')
                        ->where('id', $detail->id_pengajuan_detail)
                        ->update(['nominal_cair' => max(0, $newCair), 'updated_at' => date('Y-m-d H:i:s')]);
                }
            }

            // Set payment to 0 and add note
            $db->table('pencairan_akad_payment')->where('id', $idPayment)->update([
                'total_cair' => 0,
                'catatan' => trim($payment->catatan . ' (Void: ' . $reason . ')')
            ]);

            // Recalculate total_cair on pengajuan
            $totalCairPengajuan = (float) $this->db->table('pencairan_akad_pengajuan_detail')
                ->selectSum('nominal_cair')
                ->where('id_pengajuan', $pengajuan->id)
                ->get()
                ->getRow()->nominal_cair;

            $statusBaru = $totalCairPengajuan >= (float) $pengajuan->total_pengajuan - 0.01 ? 'paid' : ($totalCairPengajuan > 0.01 ? 'partial' : 'active');

            $db->table('pencairan_akad_pengajuan')->where('id', $pengajuan->id)->update([
                'total_cair' => $totalCairPengajuan,
                'status' => $statusBaru,
                'edit_by' => $actorId,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            $this->syncBatchStatus((int) ($pengajuan->id_batch ?? 0), $actorId);

            $plan = $this->getPlanById((int) $pengajuan->id_plan);
            if ($plan) {
                $this->saveHistory((int) $plan->id_kavling, (int) $plan->id_mkdt, (int) $plan->id, $pengajuan->id, 'void', 'Pencairan dibatalkan: ' . $reason . ' (-Rp ' . number_format($payment->total_cair, 0, ',', '.') . ')', [], $actorId);
            }

            $db->transComplete();
            if ($db->transStatus() === false) {
                throw new \RuntimeException('Transaksi gagal');
            }

            return $this->response(true, 'Pencairan berhasil dibatalkan', ['id_pengajuan' => $pengajuan->id]);
        } catch (\Throwable $e) {
            try {
                $db->transRollback();
            } catch (\Throwable $rollback) {
            }
            log_message('error', '[PencairanAkadService::voidPayment] {message}', ['message' => $e->getMessage()]);
            return $this->response(false, 'Gagal membatalkan pencairan: ' . $e->getMessage());
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

        $this->syncBatchStatus((int) ($pengajuan->id_batch ?? 0), $actorId);

        $plan = $this->getPlanById((int) $pengajuan->id_plan);
        if ($plan) {
            $this->saveHistory((int) $plan->id_kavling, (int) $plan->id_mkdt, (int) $plan->id, $idPengajuan, 'void', 'Pengajuan dibatalkan: ' . $reason, [], $actorId);
        }

        return $this->response(true, 'Pengajuan berhasil dibatalkan');
    }

    /**
     * Baris item retensi/tenor yang masih ada sisa (belum diajukan penuh),
     * lengkap dengan kolom identitas terkunci, untuk diexport lalu diisi tanggal cair.
     */
    public function exportTemplate(array $filter): array
    {
        $builder = $this->db->table('pencairan_akad_item pi')
            ->select('
                pi.id AS id_item, pi.jenis, pi.urutan_tenor, pi.nominal,
                ld.nama_jaminan,
                pap.id_kavling, pap.harga_kpr_acc AS acc_kpr,
                pap.total_retensi, pap.total_hasil_akad AS rencana_hasil_akad,
                j.nama_jalan AS proyek, k.no_kavling, c.nama_konsumen
            ')
            ->join('pencairan_akad_plan pap', 'pap.id = pi.id_plan')
            ->join('kavling k', 'k.id_kavling = pap.id_kavling')
            ->join('jalan j', 'j.id_jalan = k.id_jalan')
            ->join('mkdt m', 'm.id_mkdt = pap.id_mkdt')
            ->join('konsumen c', 'c.id_konsumen = m.id_konsumen');

        if (! empty($filter['id_kavling'])) {
            $builder->where('pap.id_kavling', (int) $filter['id_kavling']);
        }
        if (! empty($filter['id_proyek'])) {
            $builder->join('cluster cl', 'cl.id_cluster = j.id_cluster')
                ->where('cl.id_proyek', (int) $filter['id_proyek']);
        }
        $builder->join('list_dajam ld', 'ld.id = pi.id_list_dajam', 'left');

        $rows = $builder->orderBy('pap.id_kavling')->orderBy('pi.jenis')->orderBy('pi.urutan_tenor')->get()->getResult();

        $out = [];
        foreach ($rows as $row) {
            if ($this->getSisaItem((int) $row->id_item, (float) $row->nominal) <= 0.01) {
                continue;
            }

            $out[] = [
                'id_item' => (int) $row->id_item,
                'id_kavling' => (int) $row->id_kavling,
                'proyek' => $row->proyek,
                'no_kavling' => $row->no_kavling,
                'nama_konsumen' => $row->nama_konsumen,
                'acc_kpr' => (float) $row->acc_kpr,
                'total_retensi' => (float) $row->total_retensi,
                'rencana_hasil_akad' => (float) $row->rencana_hasil_akad,
                'jenis' => $row->jenis,
                'nama_item' => $row->jenis === 'tenor' ? ('Termin ' . $row->urutan_tenor) : (string) $row->nama_jaminan,
                'nominal' => (float) $row->nominal,
                'no_pengajuan' => '',
                'tanggal_pengajuan' => '',
                'tanggal_cair' => '',
                'nominal_cair' => '',
                'keterangan' => '',
            ];
        }

        return $out;
    }

    public static function templateColumns(): array
    {
        return [
            'id_item', 'id_kavling', 'proyek', 'no_kavling', 'nama_konsumen',
            'acc_kpr', 'total_retensi', 'rencana_hasil_akad', 'jenis', 'nama_item', 'nominal',
            'no_pengajuan', 'tanggal_pengajuan', 'tanggal_cair', 'nominal_cair', 'keterangan',
        ];
    }

    /**
     * Import massal tanggal cair dari CSV hasil exportTemplate().
     * Baris dengan tanggal_cair kosong dilewati. Baris dengan id_item tak dikenal atau
     * kolom terkunci tidak cocok dengan data sistem ditolak (dilaporkan, bukan diproses).
     * Baris valid dikelompokkan per (id_kavling, no_pengajuan) menjadi satu pengajuan+payment,
     * dieksekusi lewat storePengajuanCore()+cairkanCore() yang sama dengan alur UI manual.
     */
    public function importTanggalCair(string $csvPath, $lampiranFile, int $actorId): array
    {
        if (function_exists('in_groups') && !in_groups(['1', '3'])) {
            return $this->response(false, 'Akses ditolak: Hanya user Keuangan atau Administrator yang dapat mengimpor tanggal pencairan.');
        }

        if (! is_file($csvPath)) {
            return $this->response(false, 'File CSV tidak ditemukan');
        }

        $handle = fopen($csvPath, 'r');
        if (! $handle) {
            return $this->response(false, 'File CSV tidak bisa dibaca');
        }

        $header = fgetcsv($handle);
        if (! $header) {
            fclose($handle);

            return $this->response(false, 'File CSV kosong');
        }
        $header = array_map(fn ($h) => trim((string) $h), $header);

        foreach (['id_item', 'id_kavling', 'jenis', 'nominal', 'acc_kpr', 'total_retensi', 'rencana_hasil_akad'] as $col) {
            if (! in_array($col, $header, true)) {
                fclose($handle);

                return $this->response(false, "Kolom wajib '{$col}' tidak ditemukan di CSV");
            }
        }

        $rows = [];
        $line = 1;
        while (($cols = fgetcsv($handle)) !== false) {
            $line++;
            if (count($cols) === 1 && trim((string) $cols[0]) === '') {
                continue;
            }
            $assoc = ['_line' => $line];
            foreach ($header as $i => $key) {
                $assoc[$key] = trim((string) ($cols[$i] ?? ''));
            }
            $rows[] = $assoc;
        }
        fclose($handle);

        $idItems = array_unique(array_map(fn ($r) => (int) $r['id_item'], $rows));
        $dbItems = $this->getItemsWithPlanByIds($idItems);

        $errors = [];
        $groups = [];

        foreach ($rows as $row) {
            $tanggalCair = $row['tanggal_cair'] ?? '';
            if ($tanggalCair === '') {
                continue;
            }

            $idItem = (int) $row['id_item'];
            $item = $dbItems[$idItem] ?? null;
            if (! $item) {
                $errors[] = ['baris' => $row['_line'], 'id_item' => $idItem, 'alasan' => 'id_item tidak ditemukan'];
                continue;
            }

            $mismatch = self::findLockedMismatch($row, $item);
            if ($mismatch) {
                $errors[] = ['baris' => $row['_line'], 'id_item' => $idItem, 'alasan' => $mismatch];
                continue;
            }

            if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggalCair)) {
                $errors[] = ['baris' => $row['_line'], 'id_item' => $idItem, 'alasan' => 'Format tanggal_cair harus YYYY-MM-DD'];
                continue;
            }

            $noPengajuan = trim((string) ($row['no_pengajuan'] ?? ''));
            if ($noPengajuan === '') {
                $errors[] = ['baris' => $row['_line'], 'id_item' => $idItem, 'alasan' => 'no_pengajuan wajib diisi kalau tanggal_cair diisi'];
                continue;
            }

            $tanggalPengajuan = trim((string) ($row['tanggal_pengajuan'] ?? '')) ?: $tanggalCair;
            $nominalCair = self::num($row['nominal_cair'] ?? '') ?: (float) $item->nominal;

            $groupKey = $item->id_kavling . '|' . $noPengajuan;
            if (isset($groups[$groupKey])) {
                if ($groups[$groupKey]['tanggal_cair'] !== $tanggalCair) {
                    $errors[] = ['baris' => $row['_line'], 'id_item' => $idItem, 'alasan' => 'tanggal_cair berbeda dari baris lain dengan no_pengajuan yang sama'];
                    continue;
                }
                if ($groups[$groupKey]['tanggal_pengajuan'] !== $tanggalPengajuan) {
                    $errors[] = ['baris' => $row['_line'], 'id_item' => $idItem, 'alasan' => 'tanggal_pengajuan berbeda dari baris lain dengan no_pengajuan yang sama'];
                    continue;
                }
            } else {
                $groups[$groupKey] = [
                    'id_plan' => (int) $item->id_plan,
                    'tanggal_cair' => $tanggalCair,
                    'tanggal_pengajuan' => $tanggalPengajuan,
                    'catatan' => 'Import CSV (no_pengajuan: ' . $noPengajuan . ')',
                    'items' => [],
                ];
            }
            $groups[$groupKey]['items'][] = ['id_item' => $idItem, 'nominal_cair' => $nominalCair, 'baris' => $row['_line']];
        }

        $lampiranSurat = $this->resolveLampiranSurat($lampiranFile);

        $imported = 0;
        $results = [];

        foreach ($groups as $groupKey => $group) {
            $pengajuanResult = $this->storePengajuanCore([
                'id_plan' => $group['id_plan'],
                'items' => array_column($group['items'], 'id_item'),
                'tanggal_pengajuan' => $group['tanggal_pengajuan'],
                'tanggal_rencana_cair' => null,
                'catatan' => $group['catatan'],
            ], $lampiranSurat, $actorId, false);

            if (! $pengajuanResult['success']) {
                foreach ($group['items'] as $it) {
                    $errors[] = ['baris' => $it['baris'], 'id_item' => $it['id_item'], 'alasan' => $pengajuanResult['message']];
                }
                continue;
            }

            $idPengajuan = (int) $pengajuanResult['id_pengajuan'];
            $detailMap = array_column(
                $this->db->table('pencairan_akad_pengajuan_detail')
                    ->select('id, id_item')
                    ->where('id_pengajuan', $idPengajuan)
                    ->get()
                    ->getResultArray(),
                'id',
                'id_item'
            );

            $details = [];
            foreach ($group['items'] as $it) {
                if (isset($detailMap[$it['id_item']])) {
                    $details[$detailMap[$it['id_item']]] = ['nominal_cair' => $it['nominal_cair']];
                }
            }

            $cairResult = $this->cairkanCore($idPengajuan, $group['tanggal_cair'], $details, $group['catatan'], $actorId);
            if (! $cairResult['success']) {
                foreach ($group['items'] as $it) {
                    $errors[] = ['baris' => $it['baris'], 'id_item' => $it['id_item'], 'alasan' => $cairResult['message']];
                }
                continue;
            }

            $imported++;
            $results[] = [
                'no_pengajuan' => explode('|', $groupKey)[1],
                'id_pengajuan' => $idPengajuan,
                'jumlah_item' => count($group['items']),
            ];
        }

        return $this->response(
            true,
            "Import selesai: {$imported} pengajuan berhasil dibuat, " . count($errors) . ' baris ditolak',
            ['imported' => $imported, 'results' => $results, 'errors' => $errors]
        );
    }

    public function getListGrouped($request)
    {
        $filters = $this->listFilters($request);
        $builder = $this->listRepository->buildListQuery($filters);

        $response = DataTable::of($builder)
            ->setSearchableColumns(['c.nama_konsumen', 'k.no_kavling', 'j.nama_jalan'])
            ->add('Aksi', function ($v) {
                if (!function_exists('in_groups') || !in_groups(['1', '3'])) {
                    return '-';
                }

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
            ->add('raw_sisa', fn ($v) => (float) $v->harga_kpr_acc - (float) $v->sudah_cair)
            ->add('can_batch', fn ($v) => ((float) $v->harga_kpr_acc - (float) $v->sudah_cair - (float) $v->pengajuan_outstanding) > 0.01 ? 1 : 0)
            ->toJson(true);

        $payload = json_decode($response->getBody(), true);
        if (!is_array($payload)) {
            return $response;
        }

        $search = $request->getVar('search');
        $searchValue = is_array($search) ? trim((string) ($search['value'] ?? '')) : '';
        $payload['summary'] = $this->listRepository->getSummary($filters, $searchValue);
        $payload['token'] = csrf_hash();

        return $response->setJSON($payload);
    }

    protected function listFilters($request): array
    {
        $jenisTanggal = (string) $request->getVar('jenis_tanggal');
        $periodeTanggal = trim((string) $request->getVar('periode_tanggal'));
        $legacyTanggalAkad = trim((string) $request->getVar('tanggal_akad'));

        if ($periodeTanggal === '' && $legacyTanggalAkad !== '') {
            $periodeTanggal = $legacyTanggalAkad;
            $jenisTanggal = 'tanggal_akad';
        }

        $allowedJenisTanggal = ['tanggal_pengajuan', 'tanggal_akad', 'tanggal_pencairan'];
        if (!in_array($jenisTanggal, $allowedJenisTanggal, true)) {
            $jenisTanggal = 'tanggal_akad';
        }

        [$tanggalMulai, $tanggalSelesai] = $this->normalizeListDateRange($periodeTanggal);
        $statusCair = (string) $request->getVar('status_cair');
        if (!in_array($statusCair, ['belum_cair', 'sudah_cair'], true)) {
            $statusCair = '';
        }

        return [
            'id_proyek' => resolve_active_proyek_id($request->getVar('id_proyek')),
            'id_cluster' => (int) $request->getVar('id_cluster'),
            'id_jalan' => (int) $request->getVar('id_jalan'),
            'status_cair' => $statusCair,
            'jenis_tanggal' => $jenisTanggal,
            'tanggal_mulai' => $tanggalMulai,
            'tanggal_selesai' => $tanggalSelesai,
        ];
    }

    protected function normalizeListDateRange(string $dateRange): array
    {
        if ($dateRange === '') {
            return [null, null];
        }

        $parts = preg_split('/\s+to\s+/i', $dateRange, 2) ?: [];
        $start = trim((string) ($parts[0] ?? ''));
        $end = trim((string) ($parts[1] ?? $start));

        if (!$this->isListDate($start) || !$this->isListDate($end)) {
            return [null, null];
        }

        if ($start > $end) {
            [$start, $end] = [$end, $start];
        }

        return [$start, $end];
    }

    protected function isListDate(string $date): bool
    {
        $parsed = \DateTimeImmutable::createFromFormat('!Y-m-d', $date);

        return $parsed !== false && $parsed->format('Y-m-d') === $date;
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
        return $this->db->table('mkdt m')
            ->select('m.id_mkdt, m.id_konsumen, m.status_mkdt, m.harga_kpr_acc, m.is_kpr, lb.bank')
            ->join('list_bank lb', 'lb.id = m.id_bank', 'left')
            ->where('m.id_mkdt', $idMkdt)
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
            $item->sudah_cair = $this->getCairItem((int) $item->id);
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

    /** @return array<int, object> id_item => item beserta data plan (id_plan, id_kavling, harga_kpr_acc, total_retensi, total_hasil_akad) */
    protected function getItemsWithPlanByIds(array $ids): array
    {
        $ids = array_values(array_filter($ids, fn ($id) => $id > 0));
        if (empty($ids)) {
            return [];
        }

        $rows = $this->db->table('pencairan_akad_item pi')
            ->select('pi.id, pi.id_plan, pi.jenis, pi.nominal, pap.id_kavling, pap.harga_kpr_acc, pap.total_retensi, pap.total_hasil_akad')
            ->join('pencairan_akad_plan pap', 'pap.id = pi.id_plan')
            ->whereIn('pi.id', $ids)
            ->get()
            ->getResult();

        $out = [];
        foreach ($rows as $row) {
            $out[(int) $row->id] = $row;
        }

        return $out;
    }

    /**
     * Bandingkan kolom "terkunci" di baris CSV terhadap data pencairan_akad_item/plan sebenarnya.
     * Return null kalau cocok, atau alasan penolakan kalau ada kolom yang berubah.
     */
    public static function findLockedMismatch(array $row, object $item): ?string
    {
        if ((int) ($row['id_kavling'] ?? 0) !== (int) $item->id_kavling) {
            return 'id_kavling tidak cocok dengan data sistem';
        }
        if (trim((string) ($row['jenis'] ?? '')) !== $item->jenis) {
            return 'jenis tidak cocok dengan data sistem';
        }
        if (abs(self::num($row['nominal'] ?? '') - (float) $item->nominal) > 0.01) {
            return 'nominal tidak cocok dengan data sistem';
        }
        if (abs(self::num($row['acc_kpr'] ?? '') - (float) $item->harga_kpr_acc) > 0.01) {
            return 'acc_kpr tidak cocok dengan data sistem';
        }
        if (abs(self::num($row['total_retensi'] ?? '') - (float) $item->total_retensi) > 0.01) {
            return 'total_retensi tidak cocok dengan data sistem';
        }
        if (abs(self::num($row['rencana_hasil_akad'] ?? '') - (float) $item->total_hasil_akad) > 0.01) {
            return 'rencana_hasil_akad tidak cocok dengan data sistem';
        }

        return null;
    }

    protected function resolveLampiranSurat($file): ?string
    {
        if (! $file || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if (! $file->isValid() || $file->hasMoved()) {
            return null;
        }

        return $this->fileAccessService->store($file, 'uploads/pencairan_akad/' . date('Ymd'));
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

    protected function getCairItem(int $idItem): float
    {
        return (float) $this->db->table('pencairan_akad_pengajuan_detail pgd')
            ->selectSum('pgd.nominal_cair')
            ->join('pencairan_akad_pengajuan pg', 'pg.id = pgd.id_pengajuan', 'left')
            ->where('pgd.id_item', $idItem)
            ->where('pg.status !=', 'void')
            ->get()
            ->getRow()->nominal_cair;
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

    protected static function num($value): float
    {
        if ($value === null || $value === '') {
            return 0;
        }

        return (float) str_replace(',', '', (string) $value);
    }

    public function generateBatchNumber(): string
    {
        $prefix = 'PA-' . date('Ym') . '-';
        $last = $this->db->table('pencairan_akad_batch')
            ->like('nomor_batch', $prefix, 'after')
            ->orderBy('id', 'DESC')
            ->get()
            ->getRow();

        $seq = 1;
        if ($last && !empty($last->nomor_batch)) {
            $parts = explode('-', $last->nomor_batch);
            $lastNum = (int) end($parts);
            if ($lastNum > 0) {
                $seq = $lastNum + 1;
            }
        }

        return $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    public function syncBatchStatus(?int $idBatch, int $actorId): void
    {
        if (!$idBatch || $idBatch <= 0) {
            return;
        }

        $summary = $this->db->table('pencairan_akad_pengajuan')
            ->selectSum('total_pengajuan')
            ->selectSum('total_cair')
            ->where('id_batch', $idBatch)
            ->where('status !=', 'void')
            ->get()
            ->getRow();

        $totalPengajuan = (float) ($summary->total_pengajuan ?? 0);
        $totalCair = (float) ($summary->total_cair ?? 0);

        $countTotal = $this->db->table('pencairan_akad_pengajuan')->where('id_batch', $idBatch)->countAllResults();
        $countVoid = $this->db->table('pencairan_akad_pengajuan')->where('id_batch', $idBatch)->where('status', 'void')->countAllResults();

        if ($countTotal > 0 && $countTotal === $countVoid) {
            $statusBaru = 'void';
        } elseif ($totalCair >= $totalPengajuan - 0.01 && $totalPengajuan > 0) {
            $statusBaru = 'paid';
        } elseif ($totalCair > 0) {
            $statusBaru = 'partial';
        } else {
            $statusBaru = 'active';
        }

        $this->db->table('pencairan_akad_batch')->where('id', $idBatch)->update([
            'total_pengajuan' => $totalPengajuan,
            'total_cair'      => $totalCair,
            'status'          => $statusBaru,
            'edit_by'         => $actorId,
            'updated_at'      => date('Y-m-d H:i:s'),
        ]);
    }

    public function getEligibleBatchKavling(array $idKavlings = [], int $idProyek = 0): array
    {
        $idKavlings = array_values(array_unique(array_filter(array_map('intval', $idKavlings))));

        $builder = $this->db->table('kavling k')
            ->select('
                k.id_kavling, k.no_kavling, k.id_mkdt,
                m.status_mkdt, m.is_kpr, m.harga_kpr_acc, m.bank,
                c.nama_konsumen, j.nama_jalan, p.nama_proyek
            ')
            ->join('mkdt m', 'm.id_mkdt = k.id_mkdt')
            ->join('konsumen c', 'c.id_konsumen = m.id_konsumen')
            ->join('jalan j', 'j.id_jalan = k.id_jalan')
            ->join('cluster cl', 'cl.id_cluster = j.id_cluster')
            ->join('proyek p', 'p.id_proyek = cl.id_proyek')
            ->where('m.status_mkdt', 'Akad')
            ->where('m.is_kpr', 1);

        if (!empty($idKavlings)) {
            $builder->whereIn('k.id_kavling', $idKavlings);
        }

        if ($idProyek > 0) {
            $builder->where('p.id_proyek', $idProyek);
        }

        $kavlings = $builder->get()->getResult();

        if (empty($kavlings)) {
            return $this->response(false, 'Tidak ada kavling yang memenuhi syarat Akad KPR');
        }

        $result = [];
        $grandTotalAvailable = 0;

        foreach ($kavlings as $kav) {
            $idMkdt = (int) $kav->id_mkdt;
            $idKavling = (int) $kav->id_kavling;
            $hargaKprAcc = (float) $kav->harga_kpr_acc;

            // Pastikan plan ada
            $idPlan = $this->ensurePlan($idMkdt, $idKavling, $hargaKprAcc, (int) (function_exists('user_id') ? user_id() : 0));

            $lockedIds = $this->getLockedItemIds($idPlan);
            $items = $this->getItems($idPlan, $lockedIds);

            // Cek apakah ada item tenor (dana akad bersih)
            $hasTenor = false;
            foreach ($items as $it) {
                if ($it->jenis === 'tenor') {
                    $hasTenor = true;
                    break;
                }
            }

            // Jika belum ada item tenor sama sekali, inisialisasi default Termin 1 dari sisa hasil akad
            if (!$hasTenor) {
                $plan = $this->getPlanById($idPlan);
                $totalRetensi = (float) ($plan->total_retensi ?? 0);
                $sisaHasilAkad = max(0, $hargaKprAcc - $totalRetensi);
                if ($sisaHasilAkad > 0.01) {
                    $this->db->table('pencairan_akad_item')->insert([
                        'id_plan'      => $idPlan,
                        'jenis'        => 'tenor',
                        'urutan_tenor' => 1,
                        'nominal'      => $sisaHasilAkad,
                        'catatan'      => 'Pencairan Dana Akad Bersih',
                        'add_by'       => (int) (function_exists('user_id') ? user_id() : 0),
                        'created_at'   => date('Y-m-d H:i:s'),
                        'updated_at'   => date('Y-m-d H:i:s'),
                    ]);
                    $items = $this->getItems($idPlan, $lockedIds);
                }
            }

            // Filter item yang masih bisa diajukan (sisa > 0)
            $availableItems = [];
            $totalKavlingAvailable = 0;
            foreach ($items as $item) {
                $sisa = (float) $item->sisa;
                if ($sisa > 0.01) {
                    $label = $item->jenis === 'retensi'
                        ? ('Retensi - ' . ($item->nama_jaminan ?: '-'))
                        : ('Hasil Akad' . ($item->urutan_tenor > 1 ? ' (Termin #' . $item->urutan_tenor . ')' : ''));

                    $availableItems[] = [
                        'id'           => (int) $item->id,
                        'id_item'      => (int) $item->id,
                        'jenis'        => $item->jenis,
                        'urutan_tenor' => $item->urutan_tenor,
                        'nama_jaminan' => $item->nama_jaminan,
                        'label'        => $label,
                        'catatan'      => (string) ($item->catatan ?: ''),
                        'nominal'      => (float) $item->nominal,
                        'sisa'         => $sisa,
                    ];
                    $totalKavlingAvailable += $sisa;
                }
            }

            if ($totalKavlingAvailable > 0.01) {
                $result[] = [
                    'id_kavling'      => $idKavling,
                    'id_mkdt'         => $idMkdt,
                    'id_plan'         => $idPlan,
                    'no_kavling'      => $kav->no_kavling,
                    'nama_konsumen'   => $kav->nama_konsumen,
                    'nama_jalan'      => $kav->nama_jalan,
                    'nama_proyek'     => $kav->nama_proyek,
                    'bank'            => $kav->bank ?: '-',
                    'harga_kpr_acc'   => $hargaKprAcc,
                    'total_available' => $totalKavlingAvailable,
                    'items'           => $availableItems,
                ];
                $grandTotalAvailable += $totalKavlingAvailable;
            }
        }

        if (empty($result)) {
            return $this->response(false, 'Semua kavling yang dipilih sudah pernah diajukan penuh');
        }

        return $this->response(true, 'Data kavling eligible', [
            'kavlings'        => $result,
            'total_nominal'   => $grandTotalAvailable,
            'total_kavling'   => count($result),
            'nomor_pengajuan' => $this->generateBatchNumber(),
        ]);
    }

    public function storeBatchPengajuan(array $data, $lampiranFile, int $actorId): array
    {
        $kavlingsData = $data['kavlings'] ?? [];
        if (!is_array($kavlingsData) || empty($kavlingsData)) {
            return $this->response(false, 'Pilih minimal satu kavling untuk diajukan');
        }

        $tanggalPengajuan = trim((string) ($data['tanggal_pengajuan'] ?? ''));
        if ($tanggalPengajuan === '') {
            return $this->response(false, 'Tanggal pengajuan harus diisi');
        }

        $tanggalRencanaCair = trim((string) ($data['tanggal_rencana_cair'] ?? '')) ?: null;
        $catatan = trim((string) ($data['catatan'] ?? '')) ?: null;

        $lampiranSurat = null;
        if ($lampiranFile && $lampiranFile->getError() !== UPLOAD_ERR_NO_FILE) {
            if (!$lampiranFile->isValid()) {
                return $this->response(false, 'Lampiran surat tidak valid');
            }
            $lampiranSurat = $this->resolveLampiranSurat($lampiranFile);
        }

        $nomorBatch = !empty($data['nomor_batch']) ? trim((string) $data['nomor_batch']) : $this->generateBatchNumber();

        $db = $this->db;
        $db->transException(true);

        try {
            $db->transStart();

            // Insert Batch Header
            $db->table('pencairan_akad_batch')->insert([
                'nomor_batch'          => $nomorBatch,
                'tanggal_pengajuan'    => $tanggalPengajuan,
                'tanggal_rencana_cair' => $tanggalRencanaCair,
                'catatan'              => $catatan,
                'lampiran_surat'       => $lampiranSurat,
                'total_pengajuan'      => 0,
                'total_cair'           => 0,
                'status'               => 'active',
                'add_by'               => $actorId,
                'created_at'           => date('Y-m-d H:i:s'),
                'updated_at'           => date('Y-m-d H:i:s'),
            ]);
            $idBatch = (int) $db->insertID();

            $grandTotalPengajuan = 0;
            $processedKavlings = [];

            foreach ($kavlingsData as $itemData) {
                $idPlan = (int) ($itemData['id_plan'] ?? 0);
                $selectedItemIds = $itemData['items'] ?? [];
                if ($idPlan <= 0 || !is_array($selectedItemIds) || empty($selectedItemIds)) {
                    continue;
                }

                $plan = $this->getPlanById($idPlan);
                if (!$plan) {
                    continue;
                }

                $kavTotal = 0;
                $detailRows = [];

                foreach ($selectedItemIds as $idItem) {
                    $item = $this->getItemById((int) $idItem, $idPlan);
                    if (!$item) {
                        continue;
                    }
                    $sisa = $this->getSisaItem((int) $item->id, $this->num($item->nominal));
                    if ($sisa <= 0) {
                        continue;
                    }

                    $detailRows[] = [
                        'id_item' => (int) $item->id,
                        'nominal' => $sisa,
                    ];
                    $kavTotal += $sisa;
                }

                if ($kavTotal <= 0) {
                    continue;
                }

                // Insert pencairan_akad_pengajuan
                $db->table('pencairan_akad_pengajuan')->insert([
                    'id_batch'             => $idBatch,
                    'id_plan'              => $idPlan,
                    'tanggal_pengajuan'    => $tanggalPengajuan,
                    'tanggal_rencana_cair' => $tanggalRencanaCair,
                    'catatan'              => $catatan,
                    'lampiran_surat'       => $lampiranSurat,
                    'total_pengajuan'      => $kavTotal,
                    'total_cair'           => 0,
                    'status'               => 'active',
                    'add_by'               => $actorId,
                    'created_at'           => date('Y-m-d H:i:s'),
                    'updated_at'           => date('Y-m-d H:i:s'),
                ]);
                $idPengajuan = (int) $db->insertID();

                foreach ($detailRows as $dr) {
                    $db->table('pencairan_akad_pengajuan_detail')->insert([
                        'id_pengajuan'      => $idPengajuan,
                        'id_item'           => $dr['id_item'],
                        'nominal_pengajuan' => $dr['nominal'],
                        'nominal_cair'      => 0,
                        'created_at'        => date('Y-m-d H:i:s'),
                        'updated_at'        => date('Y-m-d H:i:s'),
                    ]);
                }

                $db->table('pencairan_akad_plan')->where('id', $idPlan)->update([
                    'status'     => 'diajukan',
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

                $this->saveHistory((int) $plan->id_kavling, (int) $plan->id_mkdt, $idPlan, $idPengajuan, 'pengajuan_batch', 'Pengajuan batch pencairan dibuat (' . $nomorBatch . ')', $detailRows, $actorId);

                $grandTotalPengajuan += $kavTotal;
                $processedKavlings[] = (int) $plan->id_kavling;
            }

            if ($grandTotalPengajuan <= 0) {
                throw new \RuntimeException('Tidak ada nominal pengajuan yang valid');
            }

            // Update total batch
            $db->table('pencairan_akad_batch')->where('id', $idBatch)->update([
                'total_pengajuan' => $grandTotalPengajuan,
            ]);

            // Save batch history
            $this->saveBatchHistory($idBatch, 'buat_pengajuan_batch', 'Pengajuan pencairan batch ' . $nomorBatch . ' dibuat untuk ' . count($processedKavlings) . ' kavling sebesar Rp ' . number_format($grandTotalPengajuan, 0, ',', '.'), [
                'nomor_batch'     => $nomorBatch,
                'total_pengajuan' => $grandTotalPengajuan,
                'jumlah_kavling'  => count($processedKavlings),
            ], $actorId);

            $db->transComplete();
            if ($db->transStatus() === false) {
                throw new \RuntimeException('Transaksi gagal');
            }

            return $this->response(true, 'Pengajuan batch berhasil dibuat', [
                'id_batch'    => $idBatch,
                'nomor_batch' => $nomorBatch,
            ]);
        } catch (\Throwable $e) {
            try {
                $db->transRollback();
            } catch (\Throwable $rb) {}
            log_message('error', '[PencairanAkadService::storeBatchPengajuan] {message}', ['message' => $e->getMessage()]);
            return $this->response(false, 'Gagal menyimpan pengajuan batch: ' . $e->getMessage());
        }
    }

    public function getBatchList($request)
    {
        $builder = $this->db->table('pencairan_akad_batch b')
            ->select('
                b.id,
                b.nomor_batch,
                b.tanggal_pengajuan,
                b.tanggal_rencana_cair,
                b.total_pengajuan,
                b.total_cair,
                (b.total_pengajuan - b.total_cair) AS sisa,
                b.status,
                b.catatan,
                b.lampiran_surat,
                b.created_at,
                COALESCE(u.name, u.username, "-") AS pic_nama,
                COUNT(DISTINCT pap.id_kavling) AS jumlah_kavling,
                GROUP_CONCAT(
                    DISTINCT CONCAT(
                        j.nama_jalan, \', No \', k.no_kavling, \' - \',
                        IF(pai.jenis = \'tenor\', 
                            IF(pai.urutan_tenor = 1, \'Hasil Akad\', CONCAT(\'Hasil Akad (Termin \', pai.urutan_tenor, \')\')), 
                            CONCAT(\'Retensi - \', COALESCE(pld.nama_jaminan, \'Retensi\'))
                        ),
                        IF(pai.catatan IS NOT NULL AND pai.catatan != \'\', CONCAT(\' (\', pai.catatan, \')\'), \'\')
                    )
                    ORDER BY j.nama_jalan, k.no_kavling
                    SEPARATOR \'<br>\'
                ) AS daftar_kavling_item
            ')
            ->join('users u', 'u.id = b.add_by', 'left')
            ->join('pencairan_akad_pengajuan papg', 'papg.id_batch = b.id', 'left')
            ->join('pencairan_akad_pengajuan_detail pgd', 'pgd.id_pengajuan = papg.id', 'left')
            ->join('pencairan_akad_item pai', 'pai.id = pgd.id_item', 'left')
            ->join('list_dajam pld', 'pld.id = pai.id_list_dajam', 'left')
            ->join('pencairan_akad_plan pap', 'pap.id = papg.id_plan', 'left')
            ->join('kavling k', 'k.id_kavling = pap.id_kavling', 'left')
            ->join('jalan j', 'j.id_jalan = k.id_jalan', 'left')
            ->join('cluster cl', 'cl.id_cluster = j.id_cluster', 'left')
            ->groupBy('b.id');

        $idProyek = (int) $request->getVar('id_proyek');
        if ($idProyek > 0) {
            $builder->where('cl.id_proyek', $idProyek);
        }

        $statusFilter = (string) $request->getVar('status');
        if ($statusFilter !== '') {
            $builder->where('b.status', $statusFilter);
        }

        $periodeTanggal = trim((string) $request->getVar('periode_tanggal'));
        if ($periodeTanggal !== '') {
            $dates = explode(' to ', $periodeTanggal);
            if (count($dates) === 2) {
                $builder->where('b.tanggal_pengajuan >=', trim($dates[0]));
                $builder->where('b.tanggal_pengajuan <=', trim($dates[1]));
            } elseif (count($dates) === 1) {
                $builder->where('b.tanggal_pengajuan', trim($dates[0]));
            }
        }

        return DataTable::of($builder)
            ->setSearchableColumns(['b.nomor_batch', 'b.catatan', 'k.no_kavling'])
            ->addNumbering('no')
            ->edit('nomor_batch', fn ($v) => '<div class="text-left font-weight-bold font-small-3 text-dark">' . htmlspecialchars($v->nomor_batch) . '</div><div class="text-left small text-muted mt-25"><i class="far fa-calendar-alt mr-25"></i>' . $this->format_tgl($v->tanggal_pengajuan) . ' &bull; <i class="far fa-user mr-25"></i>' . htmlspecialchars($v->pic_nama) . '</div>')
            ->edit('tanggal_pengajuan', fn ($v) => $this->format_tgl($v->tanggal_pengajuan))
            ->edit('total_pengajuan', fn ($v) => 'Rp ' . number_format((float) $v->total_pengajuan))
            ->edit('total_cair', fn ($v) => '<span class="text-success font-weight-bold">Rp ' . number_format((float) $v->total_cair) . '</span>')
            ->edit('sisa', fn ($v) => '<span class="text-danger font-weight-bold">Rp ' . number_format((float) $v->sisa) . '</span>')
            ->edit('jumlah_kavling', fn ($v) => '<div class="text-left font-small-2">' . str_replace('`', '', $v->daftar_kavling_item ?: '-') . '</div>')
            ->edit('status', function ($v) {
                $badges = [
                    'active'  => '<span class="badge badge-warning">Aktif / Belum Cair</span>',
                    'partial' => '<span class="badge badge-info">Partial</span>',
                    'paid'    => '<span class="badge badge-success">Lunas</span>',
                    'void'    => '<span class="badge badge-danger">Void</span>',
                ];
                return $badges[$v->status] ?? $v->status;
            })
            ->add('Aksi', function ($v) {
                $isKeuangan = (!function_exists('in_groups') || in_groups(['1', '3']));
                $canCair = ($isKeuangan && in_array($v->status, ['active', 'partial']) && (float) $v->sisa > 0);

                $btnCair = $canCair
                    ? '<button type="button" class="btn btn-success btn-sm mr-25" title="Pencairan Dana" onclick="openBatchPencairanModal(' . $v->id . ')"><i class="fas fa-hand-holding-usd mr-25"></i> Cairkan</button>'
                    : '';
                $btnDetail = '<button type="button" class="btn btn-outline-primary btn-sm" title="Detail Pengajuan" onclick="openBatchDetailModal(' . $v->id . ')"><i class="fas fa-eye mr-25"></i> Detail</button>';

                return '<div class="d-inline-flex align-items-center justify-content-center text-nowrap">' . $btnCair . $btnDetail . '</div>';
            }, 'last')
            ->toJson(true);
    }

    public function getBatchDetail(int $idBatch): array
    {
        $batch = $this->db->table('pencairan_akad_batch b')
            ->select('b.*, COALESCE(u.name, u.username, "-") AS pic_nama')
            ->join('users u', 'u.id = b.add_by', 'left')
            ->where('b.id', $idBatch)
            ->get()
            ->getRow();

        if (!$batch) {
            return $this->response(false, 'Batch pengajuan tidak ditemukan');
        }

        $pengajuans = $this->db->table('pencairan_akad_pengajuan papg')
            ->select('
                papg.id AS id_pengajuan, papg.total_pengajuan, papg.total_cair, papg.status,
                pap.id AS id_plan, pap.id_kavling, pap.harga_kpr_acc,
                k.no_kavling, j.nama_jalan, c.nama_konsumen, m.bank
            ')
            ->join('pencairan_akad_plan pap', 'pap.id = papg.id_plan')
            ->join('kavling k', 'k.id_kavling = pap.id_kavling')
            ->join('jalan j', 'j.id_jalan = k.id_jalan')
            ->join('mkdt m', 'm.id_mkdt = pap.id_mkdt')
            ->join('konsumen c', 'c.id_konsumen = m.id_konsumen')
            ->where('papg.id_batch', $idBatch)
            ->get()
            ->getResult();

        $kavlingDetails = [];
        foreach ($pengajuans as $pg) {
            $details = $this->db->table('pencairan_akad_pengajuan_detail pgd')
                ->select('
                    pgd.id AS id_pengajuan_detail, pgd.id_item, pgd.nominal_pengajuan, pgd.nominal_cair,
                    (pgd.nominal_pengajuan - pgd.nominal_cair) AS sisa,
                    pi.jenis, pi.urutan_tenor, pi.catatan AS item_catatan,
                    ld.nama_jaminan
                ')
                ->join('pencairan_akad_item pi', 'pi.id = pgd.id_item')
                ->join('list_dajam ld', 'ld.id = pi.id_list_dajam', 'left')
                ->where('pgd.id_pengajuan', (int) $pg->id_pengajuan)
                ->get()
                ->getResult();

            $kavlingDetails[] = [
                'id_pengajuan'    => (int) $pg->id_pengajuan,
                'id_plan'         => (int) $pg->id_plan,
                'id_kavling'      => (int) $pg->id_kavling,
                'no_kavling'      => $pg->no_kavling,
                'nama_jalan'      => $pg->nama_jalan,
                'nama_konsumen'   => $pg->nama_konsumen,
                'bank'            => $pg->bank ?: '-',
                'harga_kpr_acc'   => (float) $pg->harga_kpr_acc,
                'total_pengajuan' => (float) $pg->total_pengajuan,
                'total_cair'      => (float) $pg->total_cair,
                'sisa'            => (float) ($pg->total_pengajuan - $pg->total_cair),
                'status'          => $pg->status,
                'items'           => array_map(function ($d) {
                    return [
                        'id_pengajuan_detail' => (int) $d->id_pengajuan_detail,
                        'id_item'             => (int) $d->id_item,
                        'jenis'               => $d->jenis,
                        'label'               => $d->jenis === 'retensi' ? ('Retensi - ' . ($d->nama_jaminan ?: '-')) : ('Termin #' . $d->urutan_tenor),
                        'nominal_pengajuan'   => (float) $d->nominal_pengajuan,
                        'nominal_cair'        => (float) $d->nominal_cair,
                        'sisa'                => (float) $d->sisa,
                        'catatan'             => $d->item_catatan,
                    ];
                }, $details),
            ];
        }

        $batch->sisa = (float) $batch->total_pengajuan - (float) $batch->total_cair;

        return $this->response(true, 'Detail batch', [
            'batch'    => $batch,
            'kavlings' => $kavlingDetails,
        ]);
    }

    public function cairkanBatch(array $post, int $actorId): array
    {
        if (function_exists('in_groups') && !in_groups(['1', '3'])) {
            return $this->response(false, 'Akses ditolak: Hanya user Keuangan atau Administrator yang dapat memproses pencairan batch.');
        }

        $idBatch = (int) ($post['id_batch'] ?? 0);
        $tanggalCair = trim((string) ($post['tanggal_cair'] ?? ''));
        $catatan = trim((string) ($post['catatan'] ?? '')) ?: null;
        $mode = $post['mode'] ?? 'all'; // 'all' | 'partial'

        if ($idBatch <= 0) {
            return $this->response(false, 'Batch pengajuan tidak valid');
        }
        if ($tanggalCair === '') {
            return $this->response(false, 'Tanggal cair harus diisi');
        }

        $batch = $this->db->table('pencairan_akad_batch')->where('id', $idBatch)->get()->getRow();
        if (!$batch) {
            return $this->response(false, 'Batch pengajuan tidak ditemukan');
        }
        if (in_array($batch->status, ['paid', 'void'], true)) {
            return $this->response(false, 'Batch pengajuan ini sudah ' . $batch->status);
        }

        $pengajuans = $this->db->table('pencairan_akad_pengajuan')
            ->where('id_batch', $idBatch)
            ->where('status !=', 'void')
            ->where('total_pengajuan > total_cair', null, false)
            ->get()
            ->getResult();

        if (empty($pengajuans)) {
            return $this->response(false, 'Tidak ada pengajuan yang dapat dicairkan dalam batch ini');
        }

        $db = $this->db;
        $db->transException(true);

        try {
            $db->transStart();

            $grandTotalCairBaru = 0;
            $cairPerKavlingCount = 0;

            foreach ($pengajuans as $pg) {
                $idPengajuan = (int) $pg->id;
                $details = $db->table('pencairan_akad_pengajuan_detail')
                    ->where('id_pengajuan', $idPengajuan)
                    ->where('nominal_pengajuan > nominal_cair', null, false)
                    ->get()
                    ->getResult();

                $detailsToProcess = [];

                if ($mode === 'all') {
                    foreach ($details as $d) {
                        $sisa = (float) $d->nominal_pengajuan - (float) $d->nominal_cair;
                        if ($sisa > 0.01) {
                            $detailsToProcess[$d->id] = ['nominal_cair' => $sisa];
                        }
                    }
                } else {
                    $submittedDetails = $post['details'] ?? [];
                    foreach ($details as $d) {
                        if (isset($submittedDetails[$d->id])) {
                            $nom = $this->num($submittedDetails[$d->id]['nominal_cair'] ?? 0);
                            if ($nom > 0.01) {
                                $detailsToProcess[$d->id] = ['nominal_cair' => $nom];
                            }
                        }
                    }
                }

                if (!empty($detailsToProcess)) {
                    $res = $this->cairkanCore($idPengajuan, $tanggalCair, $detailsToProcess, $catatan, $actorId);
                    if (!$res['success']) {
                        throw new \RuntimeException($res['message'] ?? 'Gagal mencairkan pengajuan kavling #' . $idPengajuan);
                    }
                    $cairPerKavlingCount++;

                    $kavCair = array_sum(array_column($detailsToProcess, 'nominal_cair'));
                    $grandTotalCairBaru += $kavCair;
                }
            }

            if ($grandTotalCairBaru <= 0) {
                throw new \RuntimeException('Tidak ada nominal pencairan yang diproses');
            }

            $this->syncBatchStatus($idBatch, $actorId);

            $this->saveBatchHistory($idBatch, 'pencairan_batch', 'Pencairan batch dicatat sebesar Rp ' . number_format($grandTotalCairBaru, 0, ',', '.') . ' (' . ($mode === 'all' ? 'Semua Kavling Sekaligus' : $cairPerKavlingCount . ' Kavling') . ')', [
                'tanggal_cair' => $tanggalCair,
                'total_cair'   => $grandTotalCairBaru,
                'mode'         => $mode,
            ], $actorId);

            $db->transComplete();
            if ($db->transStatus() === false) {
                throw new \RuntimeException('Transaksi gagal');
            }

            return $this->response(true, 'Pencairan batch berhasil disimpan sebesar Rp ' . number_format($grandTotalCairBaru, 0, ',', '.'), [
                'id_batch'   => $idBatch,
                'total_cair' => $grandTotalCairBaru,
            ]);
        } catch (\Throwable $e) {
            try {
                $db->transRollback();
            } catch (\Throwable $rb) {}
            log_message('error', '[PencairanAkadService::cairkanBatch] {message}', ['message' => $e->getMessage()]);
            return $this->response(false, 'Gagal mencairkan batch: ' . $e->getMessage());
        }
    }

    public function voidBatch(int $idBatch, string $reason, int $actorId): array
    {
        if (function_exists('in_groups') && !in_groups(['1', '3'])) {
            return $this->response(false, 'Akses ditolak: Hanya user Keuangan atau Administrator yang dapat membatalkan batch pengajuan.');
        }

        $batch = $this->db->table('pencairan_akad_batch')->where('id', $idBatch)->get()->getRow();
        if (!$batch) {
            return $this->response(false, 'Batch pengajuan tidak ditemukan');
        }
        if ((float) $batch->total_cair > 0) {
            return $this->response(false, 'Batch yang sudah memiliki dana cair tidak bisa dibatalkan');
        }
        if ($batch->status === 'void') {
            return $this->response(false, 'Batch ini sudah dibatalkan sebelumnya');
        }

        $pengajuans = $this->db->table('pencairan_akad_pengajuan')->where('id_batch', $idBatch)->get()->getResult();

        $db = $this->db;
        $db->transException(true);

        try {
            $db->transStart();

            foreach ($pengajuans as $pg) {
                $res = $this->void((int) $pg->id, $reason, $actorId);
                if (!$res['success']) {
                    throw new \RuntimeException($res['message'] ?? 'Gagal membatalkan pengajuan');
                }
            }

            $db->table('pencairan_akad_batch')->where('id', $idBatch)->update([
                'status'      => 'void',
                'void_reason' => $reason,
                'edit_by'     => $actorId,
                'updated_at'  => date('Y-m-d H:i:s'),
            ]);

            $this->saveBatchHistory($idBatch, 'void_batch', 'Pengajuan batch dibatalkan: ' . $reason, [
                'reason' => $reason,
            ], $actorId);

            $db->transComplete();
            if ($db->transStatus() === false) {
                throw new \RuntimeException('Transaksi gagal');
            }

            return $this->response(true, 'Pengajuan batch berhasil dibatalkan');
        } catch (\Throwable $e) {
            try {
                $db->transRollback();
            } catch (\Throwable $rb) {}
            log_message('error', '[PencairanAkadService::voidBatch] {message}', ['message' => $e->getMessage()]);
            return $this->response(false, 'Gagal membatalkan batch: ' . $e->getMessage());
        }
    }

    protected function saveBatchHistory(int $idBatch, string $aksi, string $deskripsi, $snapshot, int $actorId): void
    {
        $this->historyService->log('pencairan_akad', [
            'reference_type' => 'batch_pengajuan',
            'reference_id'   => $idBatch,
            'id_kavling'     => 0,
            'action'         => $aksi,
            'summary'        => $deskripsi,
            'new_data'       => $snapshot,
            'metadata'       => [
                'id_batch' => $idBatch,
            ],
            'add_by'         => $actorId,
            'created_at'     => date('Y-m-d H:i:s'),
        ]);
    }

    public function getBatchHistory(int $idBatch): array
    {
        $result = $this->historyService->getList([
            'module'         => 'pencairan_akad',
            'reference_type' => 'batch_pengajuan',
            'reference_id'   => $idBatch,
        ], 1000, 0);

        return [
            'token'   => csrf_hash(),
            'success' => true,
            'data'    => $result['data'],
        ];
    }
}
