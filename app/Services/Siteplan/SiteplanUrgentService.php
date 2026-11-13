<?php

namespace App\Services\Siteplan;

use App\Repositories\Siteplan\SiteplanUrgentRepository;

class SiteplanUrgentService
{
    protected SiteplanUrgentRepository $repository;
    protected string $siteplanBaseUrl;

    protected array $sectionLabels = [
        'tagihan_overdue' => 'Tagihan lewat jatuh tempo',
        'tagihan_due' => 'Tagihan jatuh tempo 7 hari',
        'cashout_subkon' => 'Cashout subkon jatuh tempo',
        'sp3k_expire' => 'SP3K expire',
        'rencana_akad' => 'Rencana akad',
        'pembangunan_telat' => 'Terlambat selesai pembangunan',
        'perubahan_kavling' => 'Aktivitas perubahan',
    ];

    public function __construct(?SiteplanUrgentRepository $repository = null)
    {
        $this->repository = $repository ?? new SiteplanUrgentRepository();
        $this->siteplanBaseUrl = function_exists('base_url') ? rtrim(base_url('siteplan/view_siteplan'), '/') : '/siteplan/view_siteplan';
    }

    public function resolveGroupId(?int $sessionGroupId, int $userId): int
    {
        $groupId = (int) ($sessionGroupId ?? 0);
        if ($groupId > 0) {
            return $groupId;
        }

        return $this->repository->getUserGroupId($userId);
    }

    public function getSummary(int $idProyek, int $groupId, ?int $userId = null): array
    {
        $sections = $this->buildUrgentSections($idProyek, $groupId, $userId);

        if ($this->canViewActivity($groupId)) {
            $sections['perubahan_kavling'] = $this->newSection('perubahan_kavling');
            foreach ($this->repository->getUnreadNotifications($idProyek, $groupId) as $row) {
                $sections['perubahan_kavling']['items'][] = $this->formatNotificationItem($row);
            }
        }

        return $this->finalizeSections($sections);
    }

    public function getUrgentSummary(int $idProyek, int $groupId, ?int $userId = null): array
    {
        return $this->finalizeSections($this->buildUrgentSections($idProyek, $groupId, $userId));
    }

    /**
     * Unlimited, ungated counts for the dashboard alert card - every role sees the same
     * accurate numbers here, unlike the role-gated/50-capped urgent panel sections.
     */
    public function getDashboardAlertCounts(?int $idProyek = null): array
    {
        $today = date('Y-m-d');
        $limitDate = date('Y-m-d', strtotime('+7 days'));
        $id = ($idProyek !== null && $idProyek > 0) ? $idProyek : 0;

        return [
            'tagihan_overdue'   => $this->repository->countTagihanOverdue($id, $today),
            'tagihan_due'       => $this->repository->countTagihanDue($id, $today, $limitDate),
            'cashout_subkon'    => $this->repository->countCashoutSubkonUrgent($id, $limitDate),
            'pembangunan_telat' => $this->repository->countPembangunanTelat($id, $today),
            'sp3k_expire'       => $this->countMkdtDateAlerts('sp3k_tgl_exp', $idProyek, null, $limitDate),
            'rencana_akad'      => $this->countMkdtDateAlerts('rencana_akad_tgl', $idProyek, null, $limitDate),
        ];
    }

    public function countMkdtDateAlerts(string $field, ?int $idProyek = null, ?string $fromDate = null, ?string $toDate = null): int
    {
        return $this->repository->countMkdtDateAlerts($field, $idProyek, $fromDate, $toDate);
    }

    public function emptySummary(): array
    {
        return [
            'total' => 0,
            'sections' => [],
        ];
    }

    public function snoozeUrgentItem(int $userId, int $idProyek, string $itemKey, int $minutes): bool
    {
        if ($userId <= 0 || $idProyek <= 0 || $itemKey === '' || !$this->repository->hasSnoozeTable()) {
            return false;
        }

        $now = date('Y-m-d H:i:s');
        $snoozedUntil = date('Y-m-d H:i:s', strtotime('+' . $minutes . ' minutes'));
        $itemType = $this->extractItemType($itemKey);

        return $this->repository->upsertSnooze($userId, $idProyek, $itemKey, $itemType, $snoozedUntil, $now);
    }

    protected function buildUrgentSections(int $idProyek, int $groupId, ?int $userId): array
    {
        $today = date('Y-m-d');
        $limitDate = date('Y-m-d', strtotime('+7 days'));
        $sections = [];

        foreach ($this->visibleUrgentSectionKeys($groupId) as $key) {
            $sections[$key] = $this->newSection($key);
        }

        if (isset($sections['tagihan_overdue']) || isset($sections['tagihan_due'])) {
            foreach ($this->repository->getTagihanUrgent($idProyek, $limitDate) as $row) {
                $key = $row->jatuh_tempo_tgl < $today ? 'tagihan_overdue' : 'tagihan_due';
                if (isset($sections[$key])) {
                    $sections[$key]['items'][] = $this->formatTagihanItem($row, $key === 'tagihan_overdue');
                }
            }
        }

        if (isset($sections['cashout_subkon'])) {
            foreach ($this->repository->getCashoutSubkonUrgent($idProyek, $limitDate, $groupId) as $row) {
                $isOverdue = !empty($row->tanggal_jatuh_tempo) && $row->tanggal_jatuh_tempo < $today;
                $sections['cashout_subkon']['items'][] = $this->formatCashoutSubkonItem($row, $isOverdue);
            }
        }

        if (isset($sections['sp3k_expire'])) {
            foreach ($this->repository->getMkdtDateUrgent($idProyek, 'sp3k_tgl_exp', $limitDate) as $row) {
                $sections['sp3k_expire']['items'][] = $this->formatMkdtDateItem($row, 'sp3k_expire', 'SP3K expire', $row->sp3k_tgl_exp < $today);
            }
        }

        if (isset($sections['rencana_akad'])) {
            foreach ($this->repository->getMkdtDateUrgent($idProyek, 'rencana_akad_tgl', $limitDate) as $row) {
                $sections['rencana_akad']['items'][] = $this->formatMkdtDateItem($row, 'rencana_akad', 'Rencana akad', $row->rencana_akad_tgl < $today);
            }
        }

        if (isset($sections['pembangunan_telat'])) {
            foreach ($this->repository->getPembangunanTelat($idProyek, $today) as $row) {
                $sections['pembangunan_telat']['items'][] = $this->formatPembangunanTelatItem($row, $today);
            }
        }

        if ($userId) {
            $snoozedKeys = $this->repository->getActiveSnoozeKeys($userId, $idProyek, date('Y-m-d H:i:s'));
            $sections = $this->filterSnoozedSections($sections, $snoozedKeys);
        }

        return $sections;
    }

    protected function visibleUrgentSectionKeys(int $groupId): array
    {
        if ($groupId === 1) {
            return [
                'tagihan_overdue',
                'tagihan_due',
                'cashout_subkon',
                'sp3k_expire',
                'rencana_akad',
                'pembangunan_telat',
            ];
        }

        if ($groupId === 3) {
            return ['tagihan_overdue', 'tagihan_due', 'cashout_subkon'];
        }

        if ($groupId === 4) {
            return ['sp3k_expire', 'rencana_akad'];
        }

        if ($groupId === 7) {
            return ['cashout_subkon', 'pembangunan_telat'];
        }

        return [];
    }

    protected function canViewActivity(int $groupId): bool
    {
        return $groupId > 0;
    }

    protected function newSection(string $key): array
    {
        return [
            'label' => $this->sectionLabels[$key] ?? $key,
            'count' => 0,
            'items' => [],
        ];
    }

    protected function finalizeSections(array $sections): array
    {
        $total = 0;
        foreach ($sections as $key => $section) {
            $sections[$key]['count'] = count($section['items'] ?? []);
            $total += $sections[$key]['count'];
        }

        return [
            'total' => $total,
            'sections' => $sections,
        ];
    }

    protected function filterSnoozedSections(array $sections, array $snoozedKeys): array
    {
        if (!$snoozedKeys) {
            return $sections;
        }

        foreach ($sections as $key => $section) {
            $sections[$key]['items'] = array_values(array_filter($section['items'], function ($item) use ($snoozedKeys) {
                return empty($item['item_key']) || !in_array($item['item_key'], $snoozedKeys, true);
            }));
        }

        return $sections;
    }

    protected function extractItemType(string $itemKey): string
    {
        $parts = explode(':', $itemKey);

        return $parts[1] ?? 'urgent';
    }

    protected function formatTagihanItem(object $row, bool $isOverdue): array
    {
        return $this->withAction([
            'item_key' => 'urgent:tagihan:' . (int) $row->id_keuangan,
            'type' => 'tagihan',
            'severity' => $isOverdue ? 'danger' : 'warning',
            'title' => ($row->nama_konsumen ?: 'Konsumen') . ' - ' . ($row->berita_acara ?: 'Tagihan'),
            'description' => 'Tempo ' . $this->formatDate($row->jatuh_tempo_tgl) . ' - Rp ' . number_format((float) $row->nominal, 0, ',', '.'),
            'meta' => trim(($row->nama_jalan ?: '-') . ' No. ' . ($row->no_kavling ?: '-')),
            'id_proyek' => (int) $row->id_proyek,
            'id_kavling' => (int) $row->id_kavling,
            'id_mkdt' => (int) $row->id_mkdt,
            'id_keuangan' => (int) $row->id_keuangan,
            'nama_jalan' => $row->nama_jalan,
            'no_kavling' => $row->no_kavling,
            'nama_proyek' => $row->nama_proyek,
            'id_tipe' => $row->id_tipe,
            'due_date' => $row->jatuh_tempo_tgl,
        ], 'tagihan');
    }

    protected function formatCashoutSubkonItem(object $row, bool $isOverdue): array
    {
        return $this->withAction([
            'item_key' => 'urgent:cashout_subkon:' . (int) $row->id_cashout_subkon_detail,
            'type' => 'cashout_subkon',
            'severity' => $isOverdue ? 'danger' : 'warning',
            'title' => ($row->nama_subkon ?: 'Subkon') . ' - ' . ($row->berita_acara ?: 'Termin'),
            'description' => $this->cashoutSubkonDescription($row),
            'meta' => $row->daftar_kavling ?: trim(($row->nama_jalan ?: '-') . ' No. ' . ($row->no_kavling ?: '-')),
            'id_proyek' => (int) $row->id_proyek,
            'id_kavling' => (int) $row->id_kavling,
            'id_cashout_subkon' => (int) $row->id_cashout_subkon,
            'id_cashout_subkon_detail' => (int) $row->id_cashout_subkon_detail,
            'nama_jalan' => $row->nama_jalan,
            'no_kavling' => $row->no_kavling,
            'daftar_kavling' => $row->daftar_kavling,
            'nama_proyek' => $row->nama_proyek,
            'due_date' => $row->tanggal_jatuh_tempo,
        ], 'cashout_subkon');
    }

    protected function formatMkdtDateItem(object $row, string $type, string $label, bool $isOverdue): array
    {
        $date = $type === 'sp3k_expire' ? $row->sp3k_tgl_exp : $row->rencana_akad_tgl;

        return $this->withAction([
            'item_key' => 'urgent:' . $type . ':' . (int) $row->id_mkdt,
            'type' => $type,
            'severity' => $isOverdue ? 'danger' : 'warning',
            'title' => ($row->nama_konsumen ?: 'Konsumen') . ' - ' . $label,
            'description' => 'Tanggal ' . $this->formatDate($date),
            'meta' => trim(($row->nama_jalan ?: '-') . ' No. ' . ($row->no_kavling ?: '-')),
            'id_proyek' => (int) $row->id_proyek,
            'id_kavling' => (int) $row->id_kavling,
            'id_mkdt' => (int) $row->id_mkdt,
            'id_konsumen' => (int) ($row->id_konsumen ?? 0),
            'id_tipe' => $row->id_tipe,
            'nama_jalan' => $row->nama_jalan,
            'no_kavling' => $row->no_kavling,
            'nama_proyek' => $row->nama_proyek,
            'due_date' => $date,
        ], 'detail');
    }

    protected function formatPembangunanTelatItem(object $row, string $today): array
    {
        $daysLate = max(1, (int) floor((strtotime($today) - strtotime($row->tanggal_rencana_selesai_pembangunan)) / 86400));

        return $this->withAction([
            'item_key' => 'urgent:pembangunan_telat:' . (int) $row->id_produksi,
            'type' => 'pembangunan_telat',
            'severity' => 'danger',
            'title' => 'Pembangunan telat - ' . ($row->nama_konsumen ?: 'Kavling'),
            'description' => 'Rencana selesai ' . $this->formatDate($row->tanggal_rencana_selesai_pembangunan) . ' - telat ' . $daysLate . ' hari',
            'meta' => trim(($row->nama_jalan ?: '-') . ' No. ' . ($row->no_kavling ?: '-') . ' - Progres ' . (float) ($row->progres_bangunan ?? 0) . '%'),
            'id_proyek' => (int) $row->id_proyek,
            'id_kavling' => (int) $row->id_kavling,
            'id_mkdt' => (int) ($row->id_mkdt ?? 0),
            'id_konsumen' => (int) ($row->id_konsumen ?? 0),
            'id_produksi' => (int) $row->id_produksi,
            'id_tipe' => $row->id_tipe,
            'nama_jalan' => $row->nama_jalan,
            'no_kavling' => $row->no_kavling,
            'nama_proyek' => $row->nama_proyek,
            'due_date' => $row->tanggal_rencana_selesai_pembangunan,
        ], 'detail');
    }

    protected function cashoutSubkonDescription(object $row): string
    {
        $status = (int) ($row->status ?? 0);
        $prefix = 'Tempo ' . $this->formatDate($row->tanggal_jatuh_tempo);
        if ($status === 0 || empty($row->tanggal_jatuh_tempo)) {
            $prefix = 'Perlu set jatuh tempo';
        } elseif ($status === 2) {
            $prefix = 'Perlu pengajuan pencairan';
        } elseif ($status === 3) {
            $prefix = 'Perlu pencairan';
        }

        return $prefix . ' - Rp ' . number_format((float) $row->nominal, 0, ',', '.');
    }

    protected function formatNotificationItem(object $row): array
    {
        $type = $row->type ?: 'kavling';

        return $this->withAction([
            'type' => $type,
            'severity' => $type === 'mkdt_konsumen' ? 'info' : 'primary',
            'title' => $type === 'mkdt_konsumen' ? 'Perubahan data konsumen' : 'Perubahan kavling',
            'description' => trim(($row->username ?: 'User') . ': ' . ($row->notif ?: '-')),
            'meta' => trim(($row->nama_jalan ?: '-') . ' No. ' . ($row->no_kavling ?: '-')),
            'id_notif' => (int) $row->id,
            'id_proyek' => (int) $row->id_proyek,
            'id_kavling' => (int) $row->id_kavling,
            'id_konsumen' => (int) ($row->id_konsumen ?? 0),
            'nama_jalan' => $row->nama_jalan,
            'no_kavling' => $row->no_kavling,
            'nama_proyek' => $row->nama_proyek,
            'created_at' => $row->created_at,
        ], $type === 'tagihan' ? 'tagihan' : 'detail');
    }

    protected function withAction(array $item, string $target): array
    {
        $idProyek = (int) ($item['id_proyek'] ?? 0);
        $params = [
            'urgent_action' => $target,
            'id_kavling' => (int) ($item['id_kavling'] ?? 0),
        ];

        if (!empty($item['id_mkdt'])) {
            $params['id_mkdt'] = (int) $item['id_mkdt'];
        }
        if (!empty($item['id_keuangan'])) {
            $params['id_keuangan'] = (int) $item['id_keuangan'];
        }
        if (!empty($item['id_cashout_subkon'])) {
            $params['id_cashout_subkon'] = (int) $item['id_cashout_subkon'];
        }
        if (!empty($item['id_cashout_subkon_detail'])) {
            $params['id_cashout_subkon_detail'] = (int) $item['id_cashout_subkon_detail'];
        }

        $item['action_target'] = $target;
        $item['action_url'] = $idProyek > 0
            ? $this->siteplanBaseUrl . '/' . $idProyek . '?' . http_build_query($params)
            : '';

        return $item;
    }

    protected function formatDate(?string $date): string
    {
        if (!$date || $date === '0000-00-00') {
            return '-';
        }

        return date('d-m-Y', strtotime($date));
    }
}
