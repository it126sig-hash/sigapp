<?php

namespace App\Services\AuditTrail;

use App\Repositories\AuditTrail\HistoryRepository;
use CodeIgniter\HTTP\IncomingRequest;

class HistoryService
{
    public const MODULE_LABELS = [
        'mkdt'            => 'MKDT / Kavling',
        'produksi'        => 'Produksi',
        'keuangan'        => 'Keuangan',
        'dana_jaminan'    => 'Dana Jaminan',
        'pencairan_akad'  => 'Pencairan Akad',
        'target_siteplan' => 'Target Siteplan',
        'cashout_subkon'  => 'Cashout Subkon',
        'siteplan'        => 'Siteplan (Planning)',
        'master_proyek'   => 'Master Proyek',
        'master_cluster'  => 'Master Cluster',
        'master_jalan'    => 'Master Jalan',
        'master_tipe'     => 'Master Tipe',
        'fee_sales'       => 'Fee Sales External',
        'copilot'         => 'AI Copilot',
    ];

    protected HistoryRepository $repo;

    public function __construct(?HistoryRepository $repo = null)
    {
        $this->repo = $repo ?? new HistoryRepository();
    }

    public function hasTable(): bool
    {
        return $this->repo->hasTable();
    }

    public function log(string $module, array $fields): bool
    {
        try {
            if (! $this->repo->hasTable()) {
                return false;
            }

            $fields['module'] = $module;
            $fields['action'] = (string) ($fields['action'] ?? 'update');
            $fields['created_at'] = $fields['created_at'] ?? date('Y-m-d H:i:s');
            $fields['add_by'] = $fields['add_by'] ?? (function_exists('user_id') ? user_id() : null);

            $metadata = is_array($fields['metadata'] ?? null)
                ? ($fields['metadata'] ?? [])
                : $this->decodeJsonValue($fields['metadata'] ?? null);

            $debugInfo = $this->resolveDebugInfo();
            if (! empty($debugInfo)) {
                $metadata['_debug'] = array_merge($metadata['_debug'] ?? [], $debugInfo);
            }

            if (empty($fields['id_proyek'])) {
                $fields['id_proyek'] = $this->resolveProyekId($module, $fields);
            }

            if ($module === 'cashout_subkon') {
                $referenceId = (int) ($fields['reference_id'] ?? 0);
                if ($referenceId > 0) {
                    $projectIds = $this->repo->getCashoutProyekIds($referenceId);
                    if ($projectIds) {
                        $metadata['id_proyek_list'] = $projectIds;
                        $fields['id_proyek'] = $fields['id_proyek'] ?? $projectIds[0];
                    }
                }
            }

            $fields['metadata'] = $this->encodeJsonValue($metadata);

            foreach (['old_data', 'new_data'] as $key) {
                $fields[$key] = $this->encodeJsonValue($fields[$key] ?? null);
            }

            return $this->repo->insert($fields);
        } catch (\Throwable $e) {
            log_message('error', 'HistoryService::log error on module [' . $module . ']: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            return false;
        }
    }

    public function getList(array $filters, int $limit, int $offset): array
    {
        $limit = max(1, min(100, $limit));
        $offset = max(0, $offset);
        $rows = $this->repo->getList($filters, $limit, $offset);

        return [
            'data' => $this->decodeRows($rows),
            'total' => $this->repo->count($filters),
            'limit' => $limit,
            'offset' => $offset,
        ];
    }

    public function getByKavling(int $idKavling, array $modules, int $limit, int $offset): array
    {
        $limit = max(1, min(50, $limit));
        $offset = max(0, $offset);
        $rows = $this->decodeRows($this->repo->getByKavling($idKavling, $modules, $limit, $offset));
        $total = $this->repo->countByKavling($idKavling, $modules);

        return [
            'history' => array_map(static fn (array $row) => (object) $row, $rows),
            'history_total' => $total,
            'history_limit' => $limit,
            'history_offset' => $offset,
            'history_next_offset' => $offset + count($rows),
            'history_has_more' => ($offset + count($rows)) < $total,
        ];
    }

    public function getByReferenceAction(string $module, int $referenceId, string $action): array
    {
        return array_map(
            static fn (array $row) => (object) $row,
            $this->decodeRows($this->repo->getByReferenceAction($module, $referenceId, $action))
        );
    }

    public function moduleLabels(): array
    {
        return self::MODULE_LABELS;
    }

    private function decodeRows(array $rows): array
    {
        foreach ($rows as &$row) {
            $row['module_label'] = self::MODULE_LABELS[$row['module']] ?? $row['module'];
            $row['action_label'] = $this->formatActionLabel((string) ($row['action'] ?? ''));
            $row['old_data'] = $this->decodeJsonValue($row['old_data'] ?? null);
            $row['new_data'] = $this->decodeJsonValue($row['new_data'] ?? null);
            $row['metadata'] = $this->decodeJsonValue($row['metadata'] ?? null);
        }

        return $rows;
    }

    public function formatActionLabel(string $action): string
    {
        $labels = [
            'set_harga_jual'                => 'Set Harga Jual',
            'isi_data_konsumen'             => 'Isi Data Konsumen',
            'ubah_status_kavling'           => 'Ubah Status Kavling',
            'turun_pembangunan'             => 'Turun Pembangunan',
            'standing_instruction'          => 'Standing Instruction',
            'tarik_ke_nup'                  => 'Penarikan ke NUP',
            'aktifkan_nup'                  => 'Aktivasi NUP',
            'pindah_kavling'                => 'Pindah Kavling',
            'batal_booking'                 => 'Batal Booking',
            'batal_verifikasi_booking'      => 'Batal Verifikasi Booking Fee',
            'ganti_konsumen'                => 'Pindah Konsumen',
            'reconcile_financial_breakdown' => 'Rekonsiliasi Keuangan MKDT',
            'target_add_kavling'            => 'Tambah Kavling Target',
            'target_remove_kavling'         => 'Hapus Kavling Target',
            'target_update_tipe'            => 'Ubah Tipe Target Kavling',
            'target_approve_tipe'           => 'ACC Ubah Tipe Target',
            'update_progres'                => 'Update Progres Bangunan',
            'pengajuan'                     => 'Pengajuan Pencairan',
            'pencairan'                     => 'Pencairan Jaminan',
            'update'                        => 'Pembaruan Data',
        ];

        return $labels[$action] ?? ucwords(str_replace('_', ' ', $action));
    }

    private function encodeJsonValue($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_string($value)) {
            return $value;
        }

        return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    private function decodeJsonValue(?string $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        $decoded = json_decode($value, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function resolveProyekId(string $module, array $fields): ?int
    {
        $idKavling = (int) ($fields['id_kavling'] ?? 0);
        if ($idKavling > 0) {
            return $this->getProyekIdByKavling($idKavling);
        }

        $referenceId = (int) ($fields['reference_id'] ?? 0);
        if ($referenceId <= 0) {
            return null;
        }

        if ($module === 'target_siteplan') {
            return $this->repo->getTargetSiteplanProyekId($referenceId);
        }

        if ($module === 'cashout_subkon') {
            $ids = $this->repo->getCashoutProyekIds($referenceId);
            return $ids[0] ?? null;
        }

        return null;
    }

    public function getProyekIdByKavling(int $idKavling): ?int
    {
        return $this->repo->getProyekIdByKavling($idKavling);
    }

    private function resolveDebugInfo(): array
    {
        $debug = [];
        try {
            $request = service('request');
            if ($request instanceof IncomingRequest) {
                $ip = $request->getIPAddress();
                if ($ip) {
                    $debug['ip_address'] = $ip;
                }
                $userAgent = (string) $request->getUserAgent();
                if ($userAgent !== '') {
                    $debug['user_agent'] = $userAgent;
                }
                $uri = (string) $request->getUri();
                if ($uri !== '') {
                    $debug['uri'] = $uri;
                }
            }
        } catch (\Throwable $t) {
            // Abaikan error saat mengambil HTTP request debug context
        }

        return $debug;
    }
}
