<?php

namespace App\Services\Keuangan;

use App\Enums\NotificationEvent;
use App\Repositories\Keuangan\PajakRepository;
use App\Services\FileAccessService;
use App\Services\Notifikasi\NotifikasiService;
use App\Services\Proyek\ActiveProyekService;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\HTTP\Files\UploadedFile;

class PajakService
{
    protected PajakRepository $repo;
    protected FileAccessService $fileAccessService;
    protected FinanceLedgerService $financeLedgerService;
    protected NotifikasiService $notif;
    protected ActiveProyekService $activeProyekService;
    protected BaseConnection $db;

    public function __construct(
        ?PajakRepository $repo = null,
        ?FileAccessService $fileAccessService = null,
        ?FinanceLedgerService $financeLedgerService = null,
        ?NotifikasiService $notif = null,
        ?ActiveProyekService $activeProyekService = null,
        ?BaseConnection $db = null
    ) {
        $this->repo = $repo ?? new PajakRepository();
        $this->fileAccessService = $fileAccessService ?? new FileAccessService();
        $this->financeLedgerService = $financeLedgerService ?? new FinanceLedgerService();
        $this->notif = $notif ?? new NotifikasiService();
        $this->activeProyekService = $activeProyekService ?? new ActiveProyekService();
        $this->db = $db ?? db_connect();
    }

    public function getMetaPayload(?int $activeProjectId = null): array
    {
        $activeProyek = $this->activeProyekService->resolveAndGet($activeProjectId);
        $canManage = ! function_exists('in_groups') || in_groups(['1', '3', '10']);

        return [
            'active_project' => $activeProyek ? [
                'id'   => (int) $activeProyek->id_proyek,
                'name' => (string) ($activeProyek->nama_proyek ?? ''),
            ] : null,
            'tarif_pph' => $this->repo->getTarifPph(),
            'tarif_ppn' => $this->repo->getTarifPpn(),
            'file_categories' => [
                ['key' => 'pph42_ebilling', 'id_group' => 10, 'kategori' => 9,  'label' => 'E-billing PPh 4(2)'],
                ['key' => 'pph42_bpn',      'id_group' => 10, 'kategori' => 10, 'label' => 'Bukti Penerimaan Negara (BPN) PPh 4(2)'],
                ['key' => 'ppn_ebilling',   'id_group' => 10, 'kategori' => 11, 'label' => 'E-billing PPN'],
                ['key' => 'ppn_bpn',        'id_group' => 10, 'kategori' => 12, 'label' => 'Bukti Penerimaan Negara (BPN) PPN'],
                ['key' => 'ppn_faktur',     'id_group' => 10, 'kategori' => 13, 'label' => 'Faktur Pajak PPN'],
            ],
            'permissions' => [
                'can_view'   => true,
                'can_manage' => $canManage,
            ],
        ];
    }

    public function getOneBundle(int $idKavling, ?int $actorId = null): ?object
    {
        if ($idKavling <= 0) {
            return null;
        }

        $bundle = $this->repo->getPajakBundleByKavling($idKavling);
        $data = $bundle['pajak'] ?? null;

        if (! $data) {
            return null;
        }

        $uid = $actorId ?: (function_exists('user_id') && user_id() ? (int) user_id() : 0);

        $data->file_ajb = $this->mapFileUrls($bundle['file_ajb'] ?? [], $uid);
        $data->file = $this->mapFileUrls($bundle['file'] ?? [], $uid);
        $data->file_ppn = $this->mapFileUrls($bundle['file_ppn'] ?? [], $uid);

        return $data;
    }

    public function savePajakData(array $postData, array $uploadedFiles, int $actorId): array
    {
        $idKavling = (int) ($postData['id_kavling'] ?? 0);
        if ($idKavling <= 0) {
            return [
                'success'  => false,
                'messages' => 'ID kavling tidak valid.',
            ];
        }

        $pph42Nilai = $this->cleanNumeric($postData['pph42_nilai'] ?? 0);
        $ppnNilai = $this->cleanNumeric($postData['ppn_nilai'] ?? 0);
        $ppnTglBayar = $postData['ppn_tgl_bayar'] ?? null;

        $fields = [
            'id'               => $postData['id'] ?? null,
            'id_mkdt'          => $postData['id_mkdt'] ?? null,
            'pph42_tarif'      => $postData['pph42_tarif'] ?? null,
            'pph42_nilai'      => $pph42Nilai,
            'pph42_id_billing' => $postData['pph42_id_billing'] ?? null,
            'pph42_ntpn'       => $postData['pph42_ntpn'] ?? null,
            'pph42_tgl_bayar'  => $postData['pph42_tgl_bayar'] ?? null,
            'pph42_keterangan' => $postData['pph42_keterangan'] ?? null,
            'ppn_tarif'        => $postData['ppn_tarif'] ?? null,
            'ppn_nilai'        => $ppnNilai,
            'ppn_id_billing'   => $postData['ppn_id_billing'] ?? null,
            'ppn_ntpn'         => $postData['ppn_ntpn'] ?? null,
            'ppn_tgl_bayar'    => $ppnTglBayar,
            'ppn_keterangan'   => $postData['ppn_keterangan'] ?? null,
            'ppn_no_faktur'    => $postData['ppn_no_faktur'] ?? null,
        ];

        $isNew = empty($fields['id']);
        $idPajak = (int) ($fields['id'] ?? 0);

        $fileConfigs = [
            ['input' => 'pph42_file-bpn',      'kategori' => 10, 'default_key' => 'pph42_kategori-bpn',      'ket_key' => 'pph42_file_keterangan-bpn'],
            ['input' => 'pph42_file-ebilling', 'kategori' => 9,  'default_key' => 'pph42_kategori-ebilling', 'ket_key' => 'pph42_file_keterangan-ebilling'],
            ['input' => 'ppn_file-bpn',        'kategori' => 12, 'default_key' => 'ppn_kategori-bpn',        'ket_key' => 'ppn_file_keterangan-bpn'],
            ['input' => 'ppn_file-faktur',     'kategori' => 13, 'default_key' => 'ppn_kategori-faktur',     'ket_key' => 'ppn_file_keterangan-faktur'],
            ['input' => 'ppn_file-ebilling',   'kategori' => 11, 'default_key' => 'ppn_kategori-ebilling',   'ket_key' => 'ppn_file_keterangan-ebilling'],
        ];

        $this->db->transBegin();

        try {
            foreach ($fileConfigs as $cfg) {
                $file = $uploadedFiles[$cfg['input']] ?? null;
                if ($file instanceof UploadedFile) {
                    $this->storePajakFile(
                        $file,
                        $idKavling,
                        $cfg['kategori'],
                        $postData[$cfg['default_key']] ?? null,
                        $postData[$cfg['ket_key']] ?? null,
                        $actorId
                    );
                }
            }

            unset($fields['id']);

            if ($isNew) {
                $idPajak = $this->insertPajak($fields, $idKavling, $actorId);
            } else {
                $this->updatePajak($idPajak, $fields, $actorId);
            }

            $this->financeLedgerService->syncExpensesFromPajak($idPajak, $actorId);

            if ($pph42Nilai > 0) {
                $notif = $isNew ? 'Melakukan pembayaran PPH42' : 'Melakukan perubahan pembayaran/detail pada PPH42';
                $this->notif->tambah_notif(
                    '3;5',
                    $notif,
                    $actorId,
                    $idKavling,
                    0,
                    NotificationEvent::PAJAK_PPH,
                    null,
                    'siteplan/view?id_kavling=' . $idKavling . '&tab=legal'
                );
            }

            if (! empty($ppnTglBayar)) {
                $notif = $isNew ? 'Melakukan pembayaran PPN' : 'Melakukan perubahan pembayaran/detail pada PPN';
                $this->notif->tambah_notif(
                    '3;5',
                    $notif,
                    $actorId,
                    $idKavling,
                    0,
                    NotificationEvent::PAJAK_PPN,
                    null,
                    'siteplan/view?id_kavling=' . $idKavling . '&tab=legal'
                );
            }

            if ($this->db->transStatus() === false) {
                throw new \RuntimeException('Transaksi pajak gagal disimpan!');
            }

            $this->db->transCommit();

            return [
                'success'  => true,
                'messages' => $isNew ? 'Data berhasil diinput' : 'Data berhasil diubah',
                'data'     => [
                    'id'         => $idPajak,
                    'id_kavling' => $idKavling,
                    'is_new'     => $isNew,
                ],
            ];
        } catch (\Throwable $e) {
            $this->db->transRollback();
            log_message('error', 'Gagal menyimpan pajak: ' . $e->getMessage());

            return [
                'success'  => false,
                'messages' => $e->getMessage(),
            ];
        }
    }

    public function insertPajak(array $fields, int $idKavling, int $actorId): int
    {
        $fields['add_by'] = $actorId;
        $fields['created_at'] = date('Y-m-d H:i:s');

        $idPajak = $this->repo->insert($fields);
        if (! $idPajak) {
            throw new \RuntimeException('Kesalahan saat mengisi data!');
        }

        if (! $this->repo->linkKavling($idKavling, (int) $idPajak)) {
            throw new \RuntimeException('Kesalahan saat menghubungkan data pajak ke kavling!');
        }

        return (int) $idPajak;
    }

    public function updatePajak(int $idPajak, array $fields, int $actorId): bool
    {
        $fields['edit_by'] = $actorId;
        $fields['updated_at'] = date('Y-m-d H:i:s');

        if (! $this->repo->update($idPajak, $fields)) {
            throw new \RuntimeException('Kesalahan saat merubah data!');
        }

        return true;
    }

    protected function storePajakFile(
        UploadedFile $file,
        int $idKavling,
        int $kategori,
        ?string $defaultFilename,
        ?string $keterangan,
        int $actorId
    ): void {
        if (! $file->isValid() || $file->hasMoved() || $file->getSize() <= 0) {
            return;
        }

        $name = $file->getRandomName();
        $lok = 'uploads/l/' . date('Ymd') . '/';

        $this->fileAccessService->storeAs($file, $lok, $name);

        $this->repo->insertFileUpload([
            'id_kavling'       => $idKavling,
            'id_group'         => 10,
            'lokasi'           => $lok . $name,
            'file_name'        => $file->getClientName(),
            'kategori'         => $kategori,
            'default_filename' => $defaultFilename,
            'keterangan'       => $keterangan,
            'upload_at'        => date('Y-m-d H:i:s'),
            'upload_by'        => $actorId,
        ]);
    }

    protected function mapFileUrls(array $rows, int $uid): array
    {
        if ($uid > 0) {
            return $this->fileAccessService->addSignedAccessUrlsToRows($rows, 'file_upload', 'id', 3600, $uid);
        }

        return $this->fileAccessService->addAccessUrlsToRows($rows, 'file_upload');
    }

    protected function cleanNumeric(mixed $value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }

        $cleaned = str_replace(['.', ','], '', (string) $value);

        return (float) $cleaned;
    }
}
