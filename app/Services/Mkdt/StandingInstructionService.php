<?php

namespace App\Services\Mkdt;

use App\Repositories\Mkdt\StandingInstructionRepository;
use App\Services\FileAccessService;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\HTTP\Files\UploadedFile;

class StandingInstructionService
{
    private StandingInstructionRepository $siRepository;
    private FileAccessService $fileAccessService;
    private MkdtHistoryService $mkdtHistoryService;
    private ?BaseConnection $db;

    public function __construct(
        ?StandingInstructionRepository $siRepository = null,
        ?FileAccessService $fileAccessService = null,
        ?MkdtHistoryService $mkdtHistoryService = null,
        ?BaseConnection $db = null
    ) {
        $this->siRepository       = $siRepository ?? new StandingInstructionRepository();
        $this->fileAccessService  = $fileAccessService ?? new FileAccessService();
        $this->mkdtHistoryService = $mkdtHistoryService ?? new MkdtHistoryService();
        $this->db                 = $db;
    }

    protected function getDb(): BaseConnection
    {
        if ($this->db === null) {
            $this->db = \Config\Database::connect();
        }

        return $this->db;
    }

    /**
     * Ambil daftar Standing Instruction untuk satu kavling beserta Signed/Access URL.
     *
     * @return array<int, object>
     */
    public function getByKavling(int $idKavling): array
    {
        if ($idKavling <= 0) {
            return [];
        }

        $rows = $this->siRepository->getByKavling($idKavling);

        return $this->fileAccessService->addAccessUrlsToRows($rows, 'si');
    }

    /**
     * Simpan perubahan multi-row Standing Instruction dalam transaksi DB dan catat audit trail.
     *
     * @param array<string|int, array<string, mixed>> $items
     * @param array<string, mixed>                    $uploadedFiles
     * @return array{success: bool, messages: string, saved_items: array<int, array<string, mixed>>, errors?: array<string, string>}
     */
    public function save(int $idKavling, array $items, array $uploadedFiles, int $actorId): array
    {
        if ($idKavling <= 0) {
            return [
                'success'     => false,
                'messages'    => 'ID kavling tidak valid',
                'errors'      => ['id_kavling' => 'ID kavling tidak valid'],
                'saved_items' => [],
            ];
        }

        if (empty($items)) {
            return [
                'success'     => false,
                'messages'    => 'Data Standing Instruction tidak boleh kosong',
                'errors'      => ['id-si' => 'Data Standing Instruction tidak boleh kosong'],
                'saved_items' => [],
            ];
        }

        $pendingItems = [];
        foreach ($items as $key => $item) {
            if (!is_array($item)) {
                continue;
            }

            $tanggalSi = trim((string) ($item['tanggal_si'] ?? ''));
            if ($tanggalSi === '') {
                continue;
            }

            $keyStr     = (string) $key;
            $keterangan = trim((string) ($item['keterangan'] ?? ''));
            $data       = [
                'id_kavling' => $idKavling,
                'tanggal_si' => $tanggalSi,
                'keterangan' => $keterangan,
            ];

            $file = $uploadedFiles['id-si-file-' . $keyStr] ?? $uploadedFiles[$keyStr] ?? null;
            if ($file instanceof UploadedFile && $file->isValid() && !$file->hasMoved() && $file->getSize() > 0) {
                $name         = $file->getRandomName();
                $lok          = 'uploads/si/' . date('Ymd') . '/';
                $storedPath   = $this->fileAccessService->storeAs($file, $lok, $name);
                $data['file'] = $storedPath;
            }

            $pendingItems[] = [
                'key'        => $keyStr,
                'data'       => $data,
                'tanggal_si' => $tanggalSi,
                'keterangan' => $keterangan,
            ];
        }

        if (empty($pendingItems)) {
            return [
                'success'     => false,
                'messages'    => 'Tanggal Standing Instruction harus diisi',
                'errors'      => ['tanggal_si' => 'Tanggal Standing Instruction harus diisi'],
                'saved_items' => [],
            ];
        }

        $db         = $this->getDb();
        $savedItems = [];
        $now        = date('Y-m-d H:i:s');

        $db->transStart();

        foreach ($pendingItems as $entry) {
            $keyStr = $entry['key'];
            $data   = $entry['data'];

            if (!str_contains($keyStr, 'n')) {
                $siId               = (int) $keyStr;
                $data['edit_by']    = $actorId;
                $data['updated_at'] = $now;

                $this->siRepository->updateSi($siId, $data);
                $listSiId = $this->siRepository->getSiListIdBySiId($siId);
            } else {
                $listSiId           = (int) substr($keyStr, 1);
                $data['id_list_si'] = $listSiId;
                $data['add_by']     = $actorId;
                $data['created_at'] = $now;

                $this->siRepository->insertSi($data);
            }

            $namaListSi = $listSiId ? ($this->siRepository->getListSiNameById($listSiId) ?? 'SI') : 'SI';

            $savedItems[] = [
                'nama'       => $namaListSi,
                'tanggal_si' => $entry['tanggal_si'],
                'keterangan' => $entry['keterangan'],
            ];
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return [
                'success'     => false,
                'messages'    => 'Gagal menyimpan Standing Instruction',
                'saved_items' => [],
            ];
        }

        if (!empty($savedItems)) {
            $idMkdt = $this->siRepository->getKavlingMkdtId($idKavling);
            $this->mkdtHistoryService->log(
                $idKavling,
                $idMkdt,
                MkdtHistoryService::ACTION_STANDING_INSTRUCTION,
                $this->mkdtHistoryService->buildStandingInstructionSummary($savedItems),
                null,
                $savedItems,
                $actorId
            );
        }

        return [
            'success'     => true,
            'messages'    => 'Data berhasil diperbaharui',
            'saved_items' => $savedItems,
        ];
    }
}
