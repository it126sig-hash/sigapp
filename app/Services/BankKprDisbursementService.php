<?php

namespace App\Services;

class BankKprDisbursementService
{
    protected $db;
    protected FileAccessService $fileAccessService;
    protected FinanceLedgerService $ledgerService;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->fileAccessService = new FileAccessService();
        $this->ledgerService = new FinanceLedgerService();
    }

    public function getData(int $idMkdt, int $idKavling): array
    {
        $context = $this->getAkadContext($idMkdt, $idKavling, false);
        if (! $context) {
            return $this->response(false, 'Data akad kavling tidak ditemukan', [
                'id_mkdt' => $idMkdt,
                'id_kavling' => $idKavling,
                'mkdt' => null,
                'banks' => [],
                'rows' => [],
                'summary' => $this->emptySummary(),
            ]);
        }

        $rows = $this->getRows($idMkdt, $idKavling);

        return $this->response(true, 'Data pencairan bank berhasil dimuat', [
            'id_mkdt' => $idMkdt,
            'id_kavling' => $idKavling,
            'mkdt' => (object) [
                'id_mkdt' => (int) $context->id_mkdt,
                'id_kavling' => (int) $idKavling,
                'status_mkdt' => $context->status_mkdt,
                'akad_tgl' => $context->akad_tgl ?? null,
                'harga_kpr' => $context->harga_kpr ?? 0,
                'harga_kpr_acc' => $context->harga_kpr_acc ?? 0,
                'id_bank' => $context->id_bank ?? null,
                'bank' => $context->bank ?? null,
            ],
            'banks' => $this->getBanks(),
            'rows' => $rows,
            'summary' => $this->buildSummary($rows, $context, $idKavling),
        ]);
    }

    public function save($request, int $actorId): array
    {
        if (! $this->canManage()) {
            return $this->response(false, 'Akses ditolak. Pencairan bank hanya untuk Finance/Admin.');
        }

        $id = (int) $request->getPost('id');
        $idMkdt = (int) $request->getPost('id_mkdt');
        $idKavling = (int) $request->getPost('id_kavling');

        if ($idMkdt <= 0 || $idKavling <= 0) {
            return $this->response(false, 'Data akad kavling tidak lengkap');
        }

        $context = $this->getAkadContext($idMkdt, $idKavling);
        if (! $context) {
            return $this->response(false, 'Pencairan hanya boleh dibuat untuk kavling dengan status Akad');
        }

        $existing = null;
        if ($id > 0) {
            $existing = $this->db->table('bank_kpr_disbursement')
                ->where('id', $id)
                ->where('id_mkdt', $idMkdt)
                ->where('id_kavling', $idKavling)
                ->where('deleted_at', null)
                ->get()
                ->getRow();

            if (! $existing || (string) ($existing->status ?? '') === 'void') {
                return $this->response(false, 'Data pencairan bank tidak ditemukan atau sudah void');
            }
        }

        $status = strtolower(trim((string) ($request->getPost('status') ?: 'draft')));
        if (! in_array($status, ['draft', 'cair'], true)) {
            return $this->response(false, 'Status pencairan tidak valid');
        }

        $nominalCair = $this->num($request->getPost('nominal_cair'));
        $tanggalCair = trim((string) $request->getPost('tanggal_cair'));
        if ($status === 'cair') {
            if ($nominalCair <= 0) {
                return $this->response(false, 'Nominal cair harus lebih dari 0');
            }
            if (! $this->isValidDate($tanggalCair)) {
                return $this->response(false, 'Tanggal cair harus diisi dengan format tanggal yang valid');
            }
        } elseif (! $this->isValidDate($tanggalCair)) {
            $tanggalCair = null;
        }

        $db = $this->db;
        $db->transException(true);

        try {
            $db->transStart();

            $filePath = $existing->file_bukti ?? null;
            $file = $request->getFile('file_bukti');
            if ($file && $file->getError() !== UPLOAD_ERR_NO_FILE) {
                $filePath = $this->storeBukti($file);
            }

            $nominalRetensiInput = $request->getPost('nominal_retensi');
            $nominalRetensi = $this->num($nominalRetensiInput);
            if (($nominalRetensiInput === null || $nominalRetensiInput === '') && $nominalRetensi <= 0) {
                $nominalRetensi = $this->getDanaJaminanTotal($idKavling);
            }

            $payload = [
                'id_mkdt' => $idMkdt,
                'id_kavling' => $idKavling,
                'id_bank' => ((int) $request->getPost('id_bank')) ?: null,
                'nominal_plafon' => $this->num($request->getPost('nominal_plafon')),
                'nominal_cair' => $nominalCair,
                'nominal_retensi' => $nominalRetensi,
                'tanggal_cair' => $tanggalCair,
                'rekening_tujuan' => trim((string) $request->getPost('rekening_tujuan')) ?: null,
                'no_referensi' => trim((string) $request->getPost('no_referensi')) ?: null,
                'file_bukti' => $filePath,
                'keterangan' => trim((string) $request->getPost('keterangan')) ?: null,
                'status' => $status,
                'void_reason' => null,
                'edit_by' => $actorId,
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            if ($existing) {
                $db->table('bank_kpr_disbursement')->where('id', (int) $existing->id)->update($payload);
                $idDisbursement = (int) $existing->id;
            } else {
                $payload['add_by'] = $actorId;
                $payload['created_at'] = date('Y-m-d H:i:s');
                $db->table('bank_kpr_disbursement')->insert($payload);
                $idDisbursement = (int) $db->insertID();
            }

            if ($status === 'cair') {
                $this->ledgerService->recordIncomeFromBankKprDisbursement($idDisbursement, $actorId);
            } else {
                $this->ledgerService->voidByBankKprDisbursement($idDisbursement, $actorId);
            }

            $db->transComplete();
            if ($db->transStatus() === false) {
                throw new \RuntimeException('Transaksi gagal');
            }

            return $this->response(true, 'Pencairan bank berhasil disimpan', [
                'id' => $idDisbursement,
                'id_mkdt' => $idMkdt,
                'id_kavling' => $idKavling,
            ]);
        } catch (\Throwable $e) {
            try {
                $db->transRollback();
            } catch (\Throwable $rollback) {
            }

            log_message('error', '[BankKprDisbursementService::save] {message}', ['message' => $e->getMessage()]);
            return $this->response(false, 'Gagal menyimpan pencairan bank: ' . $e->getMessage());
        }
    }

    public function void(int $id, $request, int $actorId): array
    {
        if (! $this->canManage()) {
            return $this->response(false, 'Akses ditolak. Pencairan bank hanya untuk Finance/Admin.');
        }

        if ($id <= 0) {
            return $this->response(false, 'Data pencairan bank tidak valid');
        }

        $row = $this->db->table('bank_kpr_disbursement')
            ->where('id', $id)
            ->where('deleted_at', null)
            ->get()
            ->getRow();

        if (! $row || (string) ($row->status ?? '') === 'void') {
            return $this->response(false, 'Data pencairan bank tidak ditemukan atau sudah void');
        }

        $db = $this->db;
        $db->transException(true);

        try {
            $db->transStart();

            $db->table('bank_kpr_disbursement')->where('id', $id)->update([
                'status' => 'void',
                'void_reason' => trim((string) $request->getPost('void_reason')) ?: null,
                'deleted_by' => $actorId,
                'edit_by' => $actorId,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            $this->ledgerService->voidByBankKprDisbursement($id, $actorId);

            $db->transComplete();
            if ($db->transStatus() === false) {
                throw new \RuntimeException('Transaksi gagal');
            }

            return $this->response(true, 'Pencairan bank berhasil di-void', [
                'id' => $id,
                'id_mkdt' => (int) $row->id_mkdt,
                'id_kavling' => (int) $row->id_kavling,
            ]);
        } catch (\Throwable $e) {
            try {
                $db->transRollback();
            } catch (\Throwable $rollback) {
            }

            log_message('error', '[BankKprDisbursementService::void] {message}', ['message' => $e->getMessage()]);
            return $this->response(false, 'Gagal void pencairan bank: ' . $e->getMessage());
        }
    }

    protected function getRows(int $idMkdt, int $idKavling): array
    {
        $rows = $this->db->table('bank_kpr_disbursement bkd')
            ->select('bkd.*, lb.bank, u.username as add_by_name, e.username as edit_by_name')
            ->join('list_bank lb', 'lb.id = bkd.id_bank', 'left')
            ->join('users u', 'u.id = bkd.add_by', 'left')
            ->join('users e', 'e.id = bkd.edit_by', 'left')
            ->where('bkd.id_mkdt', $idMkdt)
            ->where('bkd.id_kavling', $idKavling)
            ->where('bkd.deleted_at', null)
            ->orderBy('bkd.id', 'DESC')
            ->get()
            ->getResultArray();

        foreach ($rows as &$row) {
            if (! empty($row['file_bukti'])) {
                $row['access_url'] = $this->fileAccessService->accessUrl('bank_kpr_disbursement', (int) $row['id']);
                $row['download_url'] = $this->fileAccessService->accessUrl('bank_kpr_disbursement', (int) $row['id'], true);
            }
        }

        return $rows;
    }

    protected function getBanks(): array
    {
        if (! $this->db->tableExists('list_bank')) {
            return [];
        }

        return $this->db->table('list_bank')
            ->select('id, bank, keterangan')
            ->where('deleted_at', null)
            ->orderBy('bank', 'ASC')
            ->get()
            ->getResultArray();
    }

    protected function getAkadContext(int $idMkdt, int $idKavling, bool $akadOnly = true): ?object
    {
        $builder = $this->db->table('mkdt m')
            ->select('m.*, k.id_kavling as kavling_id, lb.bank')
            ->join('kavling k', 'k.id_mkdt = m.id_mkdt', 'left')
            ->join('list_bank lb', 'lb.id = m.id_bank', 'left')
            ->where('m.id_mkdt', $idMkdt)
            ->groupStart()
                ->where('m.id_kavling', $idKavling)
                ->orWhere('k.id_kavling', $idKavling)
            ->groupEnd();

        if ($akadOnly) {
            $builder->where('m.status_mkdt', 'Akad');
        }

        return $builder->get()->getRow();
    }

    protected function buildSummary(array $rows, object $context, int $idKavling): array
    {
        $summary = $this->emptySummary();
        $summary['nominal_plafon'] = (float) ($context->harga_kpr_acc ?? 0);
        $summary['total_dana_jaminan'] = $this->getDanaJaminanTotal($idKavling);
        $summary['has_dana_jaminan_nominal'] = $summary['total_dana_jaminan'] > 0;

        foreach ($rows as $row) {
            if (($row['status'] ?? '') === 'void') {
                $summary['void_count']++;
                continue;
            }

            $summary['total_retensi'] += $this->num($row['nominal_retensi'] ?? 0);
            if (($row['status'] ?? '') === 'cair') {
                $summary['total_cair'] += $this->num($row['nominal_cair'] ?? 0);
                $summary['cair_count']++;
            } else {
                $summary['draft_count']++;
            }
        }

        $summary['sisa_plafon'] = max(0, $summary['nominal_plafon'] - $summary['total_cair'] - $summary['total_retensi']);

        return $summary;
    }

    protected function emptySummary(): array
    {
        return [
            'nominal_plafon' => 0,
            'total_cair' => 0,
            'total_retensi' => 0,
            'total_dana_jaminan' => 0,
            'has_dana_jaminan_nominal' => false,
            'sisa_plafon' => 0,
            'draft_count' => 0,
            'cair_count' => 0,
            'void_count' => 0,
        ];
    }

    protected function getDanaJaminanTotal(int $idKavling): float
    {
        if (! $this->db->tableExists('dana_akad')) {
            return 0;
        }

        $row = $this->db->table('dana_akad')
            ->select('COALESCE(SUM(nominal), 0) as total', false)
            ->where('id_kavling', $idKavling)
            ->get()
            ->getRow();

        return (float) ($row->total ?? 0);
    }

    protected function storeBukti($file): string
    {
        if (! $file->isValid()) {
            throw new \RuntimeException('File bukti transfer tidak valid');
        }

        $extension = strtolower($file->getClientExtension());
        if (! in_array($extension, ['pdf', 'jpg', 'jpeg', 'png'], true)) {
            throw new \RuntimeException('File bukti transfer harus PDF/JPG/PNG');
        }

        if ((int) $file->getSizeByUnit('kb') > 4096) {
            throw new \RuntimeException('File bukti transfer maksimum 4 MB');
        }

        return $this->fileAccessService->store($file, 'uploads/pencairan-bank/' . date('Ymd'));
    }

    protected function canManage(): bool
    {
        if (! function_exists('logged_in') || ! logged_in() || ! user()) {
            return false;
        }

        $roles = array_map('intval', array_keys(user()->getRoles()));
        return count(array_intersect($roles, [1, 3])) > 0;
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

    protected function isValidDate(?string $value): bool
    {
        $value = trim((string) $value);
        if ($value === '' || $value === '0000-00-00') {
            return false;
        }

        $date = \DateTime::createFromFormat('Y-m-d', $value);
        return $date && $date->format('Y-m-d') === $value;
    }

    protected function num($value): float
    {
        if ($value === null || $value === '') {
            return 0;
        }

        return (float) str_replace(',', '', (string) $value);
    }
}
