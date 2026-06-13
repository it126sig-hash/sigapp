<?php

namespace App\Services;

class DanaJaminanService
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
            return $this->response(false, 'Data kavling tidak ditemukan', [
                'id_mkdt' => $idMkdt,
                'id_kavling' => $idKavling,
                'mkdt' => null,
                'list_dajam' => [],
                'list_pengajuan' => [],
            ]);
        }

        return $this->response(true, 'Data berhasil dimuat', [
            'id_mkdt' => $idMkdt,
            'id_kavling' => $idKavling,
            'mkdt' => (object) [
                'harga_kpr_acc' => $context->harga_kpr_acc,
                'dajam_selesai' => $context->dajam_selesai,
                'status_mkdt' => $context->status_mkdt,
            ],
            'list_dajam' => $this->getDanaJaminanRows($idKavling),
            'list_pengajuan' => $this->listPengajuan($idKavling)['data'],
        ]);
    }

    public function saveDanaAkad($request, int $actorId): array
    {
        $idMkdt = (int) $request->getVar('id_mkdt');
        $idKavling = (int) $request->getVar('id_kavling');
        $idDajam = $request->getVar('id_dajam');

        if ($idMkdt <= 0 || $idKavling <= 0 || ! is_array($idDajam)) {
            return $this->response(false, 'Data dana jaminan tidak lengkap');
        }

        $context = $this->getAkadContext($idMkdt, $idKavling);
        if (! $context) {
            return $this->response(false, 'Dana jaminan hanya bisa diisi untuk kavling dengan status Akad');
        }

        $db = $this->db;
        $db->transException(true);

        try {
            $db->transStart();

            $db->table('mkdt')->where('id_mkdt', $idMkdt)->update([
                'dajam_selesai' => $request->getVar('dajam_selesai') ? 1 : 0,
                'edit_by' => $actorId,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            foreach ($idDajam as $key => $row) {
                if (! is_array($row)) {
                    continue;
                }

                $idListDajam = (int) ($row['id_list_dajam'] ?? 0);
                $nominal = $this->num($row['nominal'] ?? 0);
                if ($idListDajam <= 0) {
                    continue;
                }

                $existing = $this->findDanaAkad((string) $key, $idKavling, $idListDajam);
                if ($existing && (int) ($existing->sudah_cair ?? 0) === 1) {
                    if ($this->num($existing->nominal ?? 0) !== $nominal) {
                        throw new \RuntimeException('Item dana jaminan yang sudah cair tidak bisa diubah');
                    }
                    continue;
                }

                $payload = [
                    'id_kavling' => $idKavling,
                    'id_list_dajam' => $idListDajam,
                    'nominal' => $nominal,
                ];

                $shouldCair = ! empty($row['sudah_cair']);
                if ($shouldCair) {
                    $payload += $this->buildCairPayload($row, $nominal, $actorId);
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

                if ($existing) {
                    $payload['edit_by'] = $actorId;
                    $payload['updated_at'] = date('Y-m-d H:i:s');
                    $db->table('dana_akad')->where('id', (int) $existing->id)->update($payload);
                    $idDanaAkad = (int) $existing->id;
                    if ($this->num($existing->nominal ?? 0) !== $nominal) {
                        $this->saveHistory($idKavling, $idMkdt, $idDanaAkad, null, 'ubah_nominal', 'Nominal dana jaminan diperbarui', [
                            'before' => $existing,
                            'after' => $payload,
                        ], $actorId);
                    }
                } else {
                    $payload['add_by'] = $actorId;
                    $payload['created_at'] = date('Y-m-d H:i:s');
                    $db->table('dana_akad')->insert($payload);
                    $idDanaAkad = (int) $db->insertID();
                    $this->saveHistory($idKavling, $idMkdt, $idDanaAkad, null, 'isi_nominal', 'Nominal dana jaminan ditambahkan', $payload, $actorId);
                }

                if ($shouldCair) {
                    $this->ledgerService->recordIncomeFromDanaJaminan($idDanaAkad, $idMkdt, $actorId);
                    $this->saveHistory($idKavling, $idMkdt, $idDanaAkad, null, 'pencairan', 'Dana jaminan dicairkan langsung', $payload, $actorId);
                }
            }

            $db->transComplete();
            if ($db->transStatus() === false) {
                throw new \RuntimeException('Transaksi gagal');
            }

            return $this->response(true, 'Data dana jaminan berhasil disimpan', [
                'id_kavling' => $idKavling,
                'id_mkdt' => $idMkdt,
            ]);
        } catch (\Throwable $e) {
            try {
                $db->transRollback();
            } catch (\Throwable $rollback) {
            }

            log_message('error', '[DanaJaminanService::saveDanaAkad] {message}', ['message' => $e->getMessage()]);
            return $this->response(false, 'Gagal menyimpan dana jaminan: ' . $e->getMessage());
        }
    }

    public function listPengajuan(int $idKavling): array
    {
        $rows = $this->db->table('riwayat_pencairan_jaminan rpj')
            ->select('rpj.*, u.username as created_by_name, e.username as updated_by_name')
            ->join('users u', 'u.id = rpj.created_by', 'left')
            ->join('users e', 'e.id = rpj.updated_by', 'left')
            ->where('rpj.id_kavling', $idKavling)
            ->orderBy('rpj.created_at', 'DESC')
            ->get()
            ->getResultArray();

        $detailMap = $this->getPengajuanDetailMap(array_column($rows, 'id'));

        foreach ($rows as &$row) {
            $id = (int) $row['id'];
            $suratPath = $row['surat_path'] ?? ($row['file_path'] ?? null);
            if (! empty($suratPath)) {
                $row['access_url'] = $this->fileAccessService->accessUrl('pencairan_jaminan', $id);
                $row['download_url'] = $this->fileAccessService->accessUrl('pencairan_jaminan', $id, true);
            }
            $row['details'] = $detailMap[$id] ?? [];
        }

        return [
            'token' => csrf_hash(),
            'success' => true,
            'data' => $rows,
        ];
    }

    public function storePengajuan($request, int $actorId): array
    {
        $idKavling = (int) $request->getPost('id_kavling');
        $idMkdt = (int) $request->getPost('id_mkdt');
        $items = $request->getPost('items');

        if ($idKavling <= 0 || $idMkdt <= 0 || ! is_array($items) || empty($items)) {
            return $this->response(false, 'Pilih minimal satu item dana jaminan');
        }

        $context = $this->getAkadContext($idMkdt, $idKavling);
        if (! $context) {
            return $this->response(false, 'Pengajuan hanya bisa dibuat untuk kavling dengan status Akad');
        }

        $tanggalPengajuan = trim((string) $request->getPost('tanggal_pengajuan'));
        if ($tanggalPengajuan === '') {
            return $this->response(false, 'Tanggal pengajuan harus diisi');
        }

        $db = $this->db;
        $db->transException(true);

        try {
            $db->transStart();

            $suratPath = null;
            $file = $request->getFile('surat');
            if ($file && $file->getError() !== UPLOAD_ERR_NO_FILE) {
                if (! $file->isValid()) {
                    throw new \RuntimeException('Lampiran surat tidak valid');
                }
                if (strtolower($file->getClientExtension()) !== 'pdf') {
                    throw new \RuntimeException('Lampiran surat harus PDF');
                }
                if ((int) $file->getSizeByUnit('kb') > 4096) {
                    throw new \RuntimeException('Lampiran surat maksimum 4 MB');
                }
                if (! $file->hasMoved()) {
                    $suratPath = $this->fileAccessService->store($file, 'uploads/pencairan/' . date('Ymd'));
                }
            }

            $header = [
                'id_kavling' => $idKavling,
                'tanggal_pengajuan' => $tanggalPengajuan,
                'tanggal_cair' => null,
                'keterangan' => $request->getPost('keterangan'),
                'status_cair' => 0,
                'created_by' => $actorId,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            if ($this->db->fieldExists('id_mkdt', 'riwayat_pencairan_jaminan')) {
                $header['id_mkdt'] = $idMkdt;
            }
            if ($this->db->fieldExists('surat_path', 'riwayat_pencairan_jaminan')) {
                $header['surat_path'] = $suratPath;
            }
            if ($this->db->fieldExists('file_path', 'riwayat_pencairan_jaminan')) {
                $header['file_path'] = $suratPath;
            }

            $db->table('riwayat_pencairan_jaminan')->insert($header);
            $idPengajuan = (int) $db->insertID();

            foreach ($items as $idDanaAkad) {
                $item = $this->getDanaAkadById((int) $idDanaAkad, $idKavling);
                if (! $item) {
                    throw new \RuntimeException('Item dana jaminan tidak ditemukan');
                }
                if ((int) ($item->sudah_cair ?? 0) === 1) {
                    throw new \RuntimeException('Item dana jaminan yang sudah cair tidak bisa diajukan ulang');
                }
                if ($this->num($item->nominal ?? 0) <= 0) {
                    throw new \RuntimeException('Nominal dana jaminan harus diisi sebelum pengajuan');
                }

                $db->table('riwayat_pencairan_jaminan_detail')->insert([
                    'id_pengajuan' => $idPengajuan,
                    'id_dana_akad' => (int) $item->id,
                    'id_list_dajam' => (int) $item->id_list_dajam,
                    'nominal_pengajuan' => $this->num($item->nominal ?? 0),
                    'status_cair' => 0,
                    'add_by' => $actorId,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }

            $this->saveHistory($idKavling, $idMkdt, null, $idPengajuan, 'pengajuan', 'Pengajuan dana jaminan ke bank dibuat', [
                'header' => $header,
                'items' => array_values($items),
            ], $actorId);

            $db->transComplete();
            if ($db->transStatus() === false) {
                throw new \RuntimeException('Transaksi gagal');
            }

            return $this->response(true, 'Pengajuan dana jaminan berhasil disimpan', [
                'id_pengajuan' => $idPengajuan,
                'id_kavling' => $idKavling,
            ]);
        } catch (\Throwable $e) {
            try {
                $db->transRollback();
            } catch (\Throwable $rollback) {
            }

            log_message('error', '[DanaJaminanService::storePengajuan] {message}', ['message' => $e->getMessage()]);
            return $this->response(false, 'Gagal menyimpan pengajuan: ' . $e->getMessage());
        }
    }

    public function cairkanPengajuan(int $idPengajuan, $request, int $actorId): array
    {
        $header = $this->db->table('riwayat_pencairan_jaminan')->where('id', $idPengajuan)->get()->getRow();
        if (! $header) {
            return $this->response(false, 'Pengajuan tidak ditemukan');
        }

        $idKavling = (int) $header->id_kavling;
        $idMkdt = (int) ($header->id_mkdt ?? $request->getPost('id_mkdt') ?? 0);
        if ($idMkdt <= 0) {
            $context = $this->getContextByKavling($idKavling);
            $idMkdt = $context ? (int) $context->id_mkdt : 0;
        }

        if (! $this->getAkadContext($idMkdt, $idKavling)) {
            return $this->response(false, 'Pencairan hanya bisa dilakukan untuk kavling dengan status Akad');
        }

        $items = $request->getPost('items');
        if (! is_array($items) || empty($items)) {
            return $this->response(false, 'Data pencairan item tidak lengkap');
        }

        $db = $this->db;
        $db->transException(true);

        try {
            $db->transStart();
            $lastTanggalCair = null;

            foreach ($items as $idDetail => $row) {
                if (! is_array($row)) {
                    continue;
                }

                $detail = $this->getPengajuanDetail((int) $idDetail, $idPengajuan);
                if (! $detail) {
                    throw new \RuntimeException('Detail pengajuan tidak ditemukan');
                }
                if ((int) ($detail->status_cair ?? 0) === 1) {
                    continue;
                }

                $danaAkad = $this->getDanaAkadById((int) $detail->id_dana_akad, $idKavling);
                if (! $danaAkad) {
                    throw new \RuntimeException('Item dana jaminan tidak ditemukan');
                }
                if ((int) ($danaAkad->sudah_cair ?? 0) === 1) {
                    throw new \RuntimeException('Item dana jaminan sudah cair');
                }

                $nominalCair = $this->num($row['nominal_cair'] ?? 0);
                $tanggalCair = trim((string) ($row['tanggal_cair'] ?? $request->getPost('tanggal_cair') ?? ''));
                $keteranganCair = $row['keterangan_cair'] ?? null;
                $nominalJaminan = $this->num($danaAkad->nominal ?? 0);

                if ($nominalCair <= 0) {
                    throw new \RuntimeException('Nominal cair harus lebih dari 0');
                }
                if ($nominalCair > $nominalJaminan) {
                    throw new \RuntimeException('Nominal cair tidak boleh melebihi nominal dana jaminan');
                }
                if ($tanggalCair === '') {
                    throw new \RuntimeException('Tanggal cair harus diisi');
                }
                $lastTanggalCair = $tanggalCair;

                $db->table('dana_akad')->where('id', (int) $danaAkad->id)->update([
                    'sudah_cair' => 1,
                    'tgl_cair' => $tanggalCair,
                    'keterangan' => $keteranganCair,
                    'cair_oleh' => $actorId,
                    'cair_created_at' => date('Y-m-d H:i:s'),
                    'nominal_cair' => $nominalCair,
                    'edit_by' => $actorId,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

                $db->table('riwayat_pencairan_jaminan_detail')->where('id', (int) $detail->id)->update([
                    'nominal_cair' => $nominalCair,
                    'tanggal_cair' => $tanggalCair,
                    'keterangan_cair' => $keteranganCair,
                    'status_cair' => 1,
                    'edit_by' => $actorId,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

                $this->ledgerService->recordIncomeFromDanaJaminan((int) $danaAkad->id, $idMkdt, $actorId);
                $this->saveHistory($idKavling, $idMkdt, (int) $danaAkad->id, $idPengajuan, 'pencairan_pengajuan', 'Pengajuan dana jaminan dicairkan', [
                    'detail' => $detail,
                    'nominal_cair' => $nominalCair,
                    'tanggal_cair' => $tanggalCair,
                    'keterangan_cair' => $keteranganCair,
                ], $actorId);
            }

            $remaining = $db->table('riwayat_pencairan_jaminan_detail')
                ->where('id_pengajuan', $idPengajuan)
                ->where('status_cair', 0)
                ->countAllResults();

            if ($remaining === 0) {
                $db->table('riwayat_pencairan_jaminan')->where('id', $idPengajuan)->update([
                    'status_cair' => 1,
                    'tanggal_cair' => $request->getPost('tanggal_cair') ?: ($lastTanggalCair ?: date('Y-m-d')),
                    'updated_by' => $actorId,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }

            $db->transComplete();
            if ($db->transStatus() === false) {
                throw new \RuntimeException('Transaksi gagal');
            }

            return $this->response(true, 'Pencairan dana jaminan berhasil disimpan', [
                'id_pengajuan' => $idPengajuan,
                'id_kavling' => $idKavling,
            ]);
        } catch (\Throwable $e) {
            try {
                $db->transRollback();
            } catch (\Throwable $rollback) {
            }

            log_message('error', '[DanaJaminanService::cairkanPengajuan] {message}', ['message' => $e->getMessage()]);
            return $this->response(false, 'Gagal menyimpan pencairan: ' . $e->getMessage());
        }
    }

    public function getHistory(int $idKavling): array
    {
        $rows = $this->db->table('dana_jaminan_history h')
            ->select('h.*, u.username')
            ->join('users u', 'u.id = h.add_by', 'left')
            ->where('h.id_kavling', $idKavling)
            ->orderBy('h.created_at', 'DESC')
            ->orderBy('h.id', 'DESC')
            ->get()
            ->getResultArray();

        return [
            'token' => csrf_hash(),
            'success' => true,
            'data' => $rows,
        ];
    }

    public function rejectLegacyToggle(): array
    {
        return $this->response(false, 'Gunakan form pencairan agar nominal cair dan ledger tercatat lengkap');
    }

    protected function getDanaJaminanRows(int $idKavling): array
    {
        return $this->db->table('list_dajam')
            ->select('list_dajam.nama_jaminan, list_dajam.id as id_list_dajam_ori, dana_akad.*')
            ->join('dana_akad', 'dana_akad.id_list_dajam = list_dajam.id and dana_akad.id_kavling = ' . $this->db->escape($idKavling), 'left')
            ->where('list_dajam.deleted_at', null)
            ->orderBy('list_dajam.id', 'ASC')
            ->get()
            ->getResult();
    }

    protected function getPengajuanDetailMap(array $ids): array
    {
        $ids = array_values(array_filter(array_map('intval', $ids)));
        if (empty($ids)) {
            return [];
        }

        $rows = $this->db->table('riwayat_pencairan_jaminan_detail d')
            ->select('d.*, ld.nama_jaminan')
            ->join('list_dajam ld', 'ld.id = d.id_list_dajam', 'left')
            ->whereIn('d.id_pengajuan', $ids)
            ->orderBy('d.id', 'ASC')
            ->get()
            ->getResultArray();

        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row['id_pengajuan']][] = $row;
        }

        return $map;
    }

    protected function getPengajuanDetail(int $idDetail, int $idPengajuan): ?object
    {
        return $this->db->table('riwayat_pencairan_jaminan_detail')
            ->where('id', $idDetail)
            ->where('id_pengajuan', $idPengajuan)
            ->get()
            ->getRow();
    }

    protected function findDanaAkad(string $key, int $idKavling, int $idListDajam): ?object
    {
        if (strpos($key, 'n') === false && (int) $key > 0) {
            return $this->getDanaAkadById((int) $key, $idKavling);
        }

        return $this->db->table('dana_akad')
            ->where('id_kavling', $idKavling)
            ->where('id_list_dajam', $idListDajam)
            ->get()
            ->getRow();
    }

    protected function getDanaAkadById(int $idDanaAkad, int $idKavling): ?object
    {
        if ($idDanaAkad <= 0) {
            return null;
        }

        return $this->db->table('dana_akad')
            ->where('id', $idDanaAkad)
            ->where('id_kavling', $idKavling)
            ->get()
            ->getRow();
    }

    protected function buildCairPayload(array $row, float $nominal, int $actorId): array
    {
        $nominalCair = $this->num($row['nominal_cair'] ?? 0);
        $tanggalCair = trim((string) ($row['tgl_cair'] ?? ''));

        if ($nominalCair <= 0) {
            throw new \RuntimeException('Nominal cair harus lebih dari 0');
        }
        if ($nominalCair > $nominal) {
            throw new \RuntimeException('Nominal cair tidak boleh melebihi nominal dana jaminan');
        }
        if ($tanggalCair === '') {
            throw new \RuntimeException('Tanggal cair harus diisi');
        }

        return [
            'sudah_cair' => 1,
            'tgl_cair' => $tanggalCair,
            'keterangan' => $row['keterangan'] ?? null,
            'cair_oleh' => $actorId,
            'cair_created_at' => date('Y-m-d H:i:s'),
            'nominal_cair' => $nominalCair,
        ];
    }

    protected function getAkadContext(int $idMkdt, int $idKavling, bool $akadOnly = true): ?object
    {
        $builder = $this->db->table('mkdt m')
            ->select('m.id_mkdt, m.id_kavling, m.status_mkdt, m.harga_kpr_acc, m.dajam_selesai, k.id_kavling as kavling_id')
            ->join('kavling k', 'k.id_mkdt = m.id_mkdt', 'left')
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

    protected function getContextByKavling(int $idKavling): ?object
    {
        return $this->db->table('kavling k')
            ->select('m.id_mkdt, m.status_mkdt')
            ->join('mkdt m', 'm.id_mkdt = k.id_mkdt', 'left')
            ->where('k.id_kavling', $idKavling)
            ->get()
            ->getRow();
    }

    protected function saveHistory(int $idKavling, int $idMkdt, ?int $idDanaAkad, ?int $idPengajuan, string $aksi, string $deskripsi, $snapshot, int $actorId): void
    {
        $this->db->table('dana_jaminan_history')->insert([
            'id_kavling' => $idKavling,
            'id_mkdt' => $idMkdt,
            'id_dana_akad' => $idDanaAkad,
            'id_pengajuan' => $idPengajuan,
            'aksi' => $aksi,
            'deskripsi' => $deskripsi,
            'snapshot' => json_encode($snapshot, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
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
