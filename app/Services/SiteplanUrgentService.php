<?php

namespace App\Services;

use Config\Database;

class SiteplanUrgentService
{
    protected $db;

    protected array $sectionLabels = [
        'tagihan_overdue' => 'Tagihan lewat jatuh tempo',
        'tagihan_due' => 'Tagihan jatuh tempo 7 hari',
        'cashout_subkon' => 'Cashout subkon jatuh tempo',
        'sp3k_expire' => 'SP3K expire',
        'rencana_akad' => 'Rencana akad',
        'pembangunan_telat' => 'Terlambat selesai pembangunan',
        'perubahan_kavling' => 'Aktivitas perubahan',
    ];

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function getSummary(int $idProyek, int $groupId, ?int $userId = null): array
    {
        $sections = $this->buildUrgentSections($idProyek, $groupId, $userId);

        if ($this->canViewActivity($groupId)) {
            $sections['perubahan_kavling'] = $this->newSection('perubahan_kavling');
            foreach ($this->getUnreadNotifications($idProyek, $groupId) as $row) {
                $sections['perubahan_kavling']['items'][] = $this->formatNotificationItem($row);
            }
        }

        return $this->finalizeSections($sections);
    }

    public function getUrgentSummary(int $idProyek, int $groupId, ?int $userId = null): array
    {
        return $this->finalizeSections($this->buildUrgentSections($idProyek, $groupId, $userId));
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
        if ($userId <= 0 || $idProyek <= 0 || $itemKey === '' || !$this->hasSnoozeTable()) {
            return false;
        }

        $now = date('Y-m-d H:i:s');
        $snoozedUntil = date('Y-m-d H:i:s', strtotime('+' . $minutes . ' minutes'));
        $itemType = $this->extractItemType($itemKey);
        $existing = $this->db->table('notification_snoozes')
            ->select('id')
            ->where('user_id', $userId)
            ->where('id_proyek', $idProyek)
            ->where('item_key', $itemKey)
            ->get()
            ->getRow();

        $data = [
            'user_id' => $userId,
            'item_key' => $itemKey,
            'item_type' => $itemType,
            'id_proyek' => $idProyek,
            'snoozed_until' => $snoozedUntil,
            'updated_at' => $now,
        ];

        if ($existing) {
            return (bool) $this->db->table('notification_snoozes')
                ->where('id', (int) $existing->id)
                ->update($data);
        }

        $data['created_at'] = $now;

        return (bool) $this->db->table('notification_snoozes')->insert($data);
    }

    protected function buildUrgentSections(int $idProyek, int $groupId, ?int $userId): array
    {
        if ($idProyek <= 0) {
            return [];
        }

        $today = date('Y-m-d');
        $limitDate = date('Y-m-d', strtotime('+7 days'));
        $sections = [];

        foreach ($this->visibleUrgentSectionKeys($groupId) as $key) {
            $sections[$key] = $this->newSection($key);
        }

        if (isset($sections['tagihan_overdue']) || isset($sections['tagihan_due'])) {
            foreach ($this->getTagihanUrgent($idProyek, $limitDate) as $row) {
                $key = $row->jatuh_tempo_tgl < $today ? 'tagihan_overdue' : 'tagihan_due';
                if (isset($sections[$key])) {
                    $sections[$key]['items'][] = $this->formatTagihanItem($row, $key === 'tagihan_overdue');
                }
            }
        }

        if (isset($sections['cashout_subkon'])) {
            foreach ($this->getCashoutSubkonUrgent($idProyek, $limitDate, $groupId) as $row) {
                $isOverdue = !empty($row->tanggal_jatuh_tempo) && $row->tanggal_jatuh_tempo < $today;
                $sections['cashout_subkon']['items'][] = $this->formatCashoutSubkonItem($row, $isOverdue);
            }
        }

        if (isset($sections['sp3k_expire'])) {
            foreach ($this->getMkdtDateUrgent($idProyek, 'sp3k_tgl_exp', $limitDate) as $row) {
                $sections['sp3k_expire']['items'][] = $this->formatMkdtDateItem($row, 'sp3k_expire', 'SP3K expire', $row->sp3k_tgl_exp < $today);
            }
        }

        if (isset($sections['rencana_akad'])) {
            foreach ($this->getMkdtDateUrgent($idProyek, 'rencana_akad_tgl', $limitDate) as $row) {
                $sections['rencana_akad']['items'][] = $this->formatMkdtDateItem($row, 'rencana_akad', 'Rencana akad', $row->rencana_akad_tgl < $today);
            }
        }

        if (isset($sections['pembangunan_telat'])) {
            foreach ($this->getPembangunanTelat($idProyek, $today) as $row) {
                $sections['pembangunan_telat']['items'][] = $this->formatPembangunanTelatItem($row, $today);
            }
        }

        if ($userId) {
            $sections = $this->filterSnoozedSections($sections, $this->getActiveSnoozeKeys($userId, $idProyek));
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

    protected function getActiveSnoozeKeys(int $userId, int $idProyek): array
    {
        if (!$this->hasSnoozeTable()) {
            return [];
        }

        $rows = $this->db->table('notification_snoozes')
            ->select('item_key')
            ->where('user_id', $userId)
            ->where('id_proyek', $idProyek)
            ->where('snoozed_until >', date('Y-m-d H:i:s'))
            ->get()
            ->getResult();

        return array_values(array_map(static function ($row) {
            return (string) $row->item_key;
        }, $rows));
    }

    protected function hasSnoozeTable(): bool
    {
        return $this->db->tableExists('notification_snoozes');
    }

    protected function extractItemType(string $itemKey): string
    {
        $parts = explode(':', $itemKey);

        return $parts[1] ?? 'urgent';
    }

    protected function getTagihanUrgent(int $idProyek, string $limitDate): array
    {
        return $this->db->table('keuangan')
            ->select("
                keuangan.id_keuangan,
                keuangan.berita_acara,
                keuangan.jatuh_tempo_tgl,
                keuangan.nominal,
                keuangan.status,
                m.id_mkdt,
                c.id_konsumen,
                c.nama_konsumen,
                k.id_kavling,
                k.no_kavling,
                j.nama_jalan,
                cl.nama_cluster,
                p.id_proyek,
                p.nama_proyek,
                hj.id_tipe
            ", false)
            ->join('mkdt m', 'm.id_mkdt = keuangan.id_mkdt')
            ->join('konsumen c', 'c.id_konsumen = m.id_konsumen', 'left')
            ->join('kavling k', 'k.id_kavling = m.id_kavling')
            ->join('jalan j', 'j.id_jalan = k.id_jalan')
            ->join('cluster cl', 'cl.id_cluster = j.id_cluster')
            ->join('proyek p', 'p.id_proyek = cl.id_proyek')
            ->join('hargajual hj', 'hj.id = k.harga_akhir', 'left')
            ->where('p.id_proyek', $idProyek)
            ->where('keuangan.sudah_dibayar', 0)
            ->where('keuangan.jatuh_tempo_tgl IS NOT NULL', null, false)
            ->where('keuangan.jatuh_tempo_tgl >', '1000-01-01')
            ->where('keuangan.jatuh_tempo_tgl <=', $limitDate)
            ->groupStart()
            ->where('m.is_batal', 0)
            ->orWhere('m.is_batal IS NULL', null, false)
            ->groupEnd()
            ->orderBy('keuangan.jatuh_tempo_tgl', 'ASC')
            ->limit(50)
            ->get()
            ->getResult();
    }

    protected function getCashoutSubkonUrgent(int $idProyek, string $limitDate, int $groupId): array
    {
        $builder = $this->db->table('cashout_subkon_detail csd')
            ->select("
                csd.id_cashout_subkon_detail,
                csd.id_cashout_subkon,
                csd.berita_acara,
                csd.nominal,
                csd.tanggal_jatuh_tempo,
                csd.status,
                cs.nomor_surat,
                s.nama_subkon,
                k.id_kavling,
                k.no_kavling,
                j.nama_jalan,
                cl.nama_cluster,
                p.id_proyek,
                p.nama_proyek
            ", false)
            ->join('cashout_subkon cs', 'cs.id_cashout_subkon = csd.id_cashout_subkon')
            ->join('subkon s', 's.id = cs.id_subkon', 'left')
            ->join('cashout_subkon_kavling csk', 'csk.id_cashout_subkon = cs.id_cashout_subkon')
            ->join('kavling k', 'k.id_kavling = csk.id_kavling')
            ->join('jalan j', 'j.id_jalan = k.id_jalan')
            ->join('cluster cl', 'cl.id_cluster = j.id_cluster')
            ->join('proyek p', 'p.id_proyek = cl.id_proyek')
            ->where('p.id_proyek', $idProyek)
            ->where('COALESCE(csd.status, 0) < 4', null, false)
            ->groupStart()
            ->where('csd.is_paid', 0)
            ->orWhere('csd.is_paid IS NULL', null, false)
            ->groupEnd()
            ->groupStart()
            ->where('COALESCE(csd.status, 0)', 0, false)
            ->orGroupStart()
            ->where('csd.tanggal_jatuh_tempo IS NOT NULL', null, false)
            ->where('csd.tanggal_jatuh_tempo >', '1000-01-01')
            ->where('csd.tanggal_jatuh_tempo <=', $limitDate)
            ->groupEnd()
            ->groupEnd();

        if ($groupId === 3) {
            $builder->groupStart()
                ->where('COALESCE(csd.status, 0)', 0, false)
                ->orWhere('COALESCE(csd.status, 0)', 2, false)
                ->orWhere('COALESCE(csd.status, 0)', 3, false)
                ->groupEnd();
        } elseif ($groupId === 7) {
            $builder->where('COALESCE(csd.status, 0)', 1, false);
        } elseif ($groupId !== 1) {
            $builder->where('COALESCE(csd.status, 0)', -1, false);
        }

        return $builder
            ->orderBy('csd.tanggal_jatuh_tempo IS NULL', 'ASC', false)
            ->orderBy('csd.tanggal_jatuh_tempo', 'ASC')
            ->orderBy('csd.id_cashout_subkon_detail', 'DESC')
            ->limit(50)
            ->get()
            ->getResult();
    }

    protected function getMkdtDateUrgent(int $idProyek, string $field, string $limitDate): array
    {
        if (!in_array($field, ['sp3k_tgl_exp', 'rencana_akad_tgl'], true)) {
            return [];
        }

        return $this->db->table('mkdt m')
            ->select("
                m.id_mkdt,
                m.id_konsumen,
                m.{$field},
                m.sp3k_tgl_exp,
                m.rencana_akad_tgl,
                c.nama_konsumen,
                k.id_kavling,
                k.no_kavling,
                j.nama_jalan,
                cl.nama_cluster,
                p.id_proyek,
                p.nama_proyek,
                t.id_tipe,
                t.no_tipe_rumah,
                t.tipe_rumah
            ", false)
            ->join('konsumen c', 'c.id_konsumen = m.id_konsumen', 'left')
            ->join('kavling k', 'k.id_kavling = m.id_kavling')
            ->join('jalan j', 'j.id_jalan = k.id_jalan')
            ->join('cluster cl', 'cl.id_cluster = j.id_cluster')
            ->join('proyek p', 'p.id_proyek = cl.id_proyek')
            ->join('tipe t', 't.id_tipe = k.id_tipe', 'left')
            ->where('p.id_proyek', $idProyek)
            ->where("m.{$field} IS NOT NULL", null, false)
            ->where("m.{$field} >", '1000-01-01')
            ->where("m.{$field} <=", $limitDate)
            ->groupStart()
            ->where('m.akad_tgl IS NULL', null, false)
            ->orWhere('m.akad_tgl <=', '1000-01-01')
            ->groupEnd()
            ->groupStart()
            ->where('m.is_batal', 0)
            ->orWhere('m.is_batal IS NULL', null, false)
            ->groupEnd()
            ->orderBy("m.{$field}", 'ASC')
            ->limit(50)
            ->get()
            ->getResult();
    }

    protected function getPembangunanTelat(int $idProyek, string $today): array
    {
        return $this->db->table('produksi pr')
            ->select("
                pr.id_produksi,
                k.id_kavling,
                pr.progres_bangunan,
                pr.tanggal_rencana_selesai_pembangunan,
                pr.tanggal_selesai_pembangunan,
                m.id_mkdt,
                m.id_konsumen,
                c.nama_konsumen,
                k.no_kavling,
                j.nama_jalan,
                cl.nama_cluster,
                p.id_proyek,
                p.nama_proyek,
                t.id_tipe,
                t.no_tipe_rumah,
                t.tipe_rumah
            ", false)
            ->join('kavling k', 'k.id_produksi = pr.id_produksi')
            ->join('jalan j', 'j.id_jalan = k.id_jalan')
            ->join('cluster cl', 'cl.id_cluster = j.id_cluster')
            ->join('proyek p', 'p.id_proyek = cl.id_proyek')
            ->join('mkdt m', 'm.id_kavling = k.id_kavling', 'left')
            ->join('konsumen c', 'c.id_konsumen = m.id_konsumen', 'left')
            ->join('tipe t', 't.id_tipe = k.id_tipe', 'left')
            ->where('p.id_proyek', $idProyek)
            ->where('pr.tanggal_rencana_selesai_pembangunan IS NOT NULL', null, false)
            ->where('pr.tanggal_rencana_selesai_pembangunan >', '1000-01-01')
            ->where('pr.tanggal_rencana_selesai_pembangunan <', $today)
            ->groupStart()
            ->where('pr.tanggal_selesai_pembangunan IS NULL', null, false)
            ->orWhere('pr.tanggal_selesai_pembangunan <=', '1000-01-01')
            ->groupEnd()
            ->where('COALESCE(pr.progres_bangunan, 0) <', 100, false)
            ->groupStart()
            ->where('m.is_batal', 0)
            ->orWhere('m.is_batal IS NULL', null, false)
            ->groupEnd()
            ->orderBy('pr.tanggal_rencana_selesai_pembangunan', 'ASC')
            ->limit(50)
            ->get()
            ->getResult();
    }

    protected function getUnreadNotifications(int $idProyek, int $groupId): array
    {
        $builder = $this->db->table('notification')
            ->select('
                notification.*,
                users.username,
                k.id_kavling,
                k.no_kavling,
                j.nama_jalan,
                cl.nama_cluster,
                p.id_proyek,
                p.nama_proyek
            ')
            ->join('users', 'users.id = notification.add_by', 'left')
            ->join('kavling k', 'k.id_kavling = notification.id_kavling')
            ->join('jalan j', 'j.id_jalan = k.id_jalan')
            ->join('cluster cl', 'cl.id_cluster = j.id_cluster')
            ->join('proyek p', 'p.id_proyek = cl.id_proyek')
            ->where('p.id_proyek', $idProyek)
            ->where('notification.is_read', 0);

        $this->applyGroupTargetFilter($builder, $groupId);

        return $builder
            ->orderBy('notification.created_at', 'DESC')
            ->limit(20)
            ->get()
            ->getResult();
    }

    protected function applyGroupTargetFilter($builder, int $groupId): void
    {
        if ($groupId === 1) {
            return;
        }

        $role = (string) $groupId;
        $builder->groupStart()
            ->where('notification.group_target', $role)
            ->orLike('notification.group_target', $role . ';', 'after')
            ->orLike('notification.group_target', ';' . $role . ';', 'both')
            ->orLike('notification.group_target', ';' . $role, 'before')
            ->orWhere('notification.group_target', '0')
            ->groupEnd();
    }

    protected function formatTagihanItem(object $row, bool $isOverdue): array
    {
        return [
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
        ];
    }

    protected function formatCashoutSubkonItem(object $row, bool $isOverdue): array
    {
        return [
            'item_key' => 'urgent:cashout_subkon:' . (int) $row->id_cashout_subkon_detail,
            'type' => 'cashout_subkon',
            'severity' => $isOverdue ? 'danger' : 'warning',
            'title' => ($row->nama_subkon ?: 'Subkon') . ' - ' . ($row->berita_acara ?: 'Termin'),
            'description' => $this->cashoutSubkonDescription($row),
            'meta' => trim(($row->nama_jalan ?: '-') . ' No. ' . ($row->no_kavling ?: '-')),
            'id_proyek' => (int) $row->id_proyek,
            'id_kavling' => (int) $row->id_kavling,
            'id_cashout_subkon' => (int) $row->id_cashout_subkon,
            'id_cashout_subkon_detail' => (int) $row->id_cashout_subkon_detail,
            'nama_jalan' => $row->nama_jalan,
            'no_kavling' => $row->no_kavling,
            'nama_proyek' => $row->nama_proyek,
            'due_date' => $row->tanggal_jatuh_tempo,
        ];
    }

    protected function formatMkdtDateItem(object $row, string $type, string $label, bool $isOverdue): array
    {
        $date = $type === 'sp3k_expire' ? $row->sp3k_tgl_exp : $row->rencana_akad_tgl;

        return [
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
        ];
    }

    protected function formatPembangunanTelatItem(object $row, string $today): array
    {
        $daysLate = max(1, (int) floor((strtotime($today) - strtotime($row->tanggal_rencana_selesai_pembangunan)) / 86400));

        return [
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
        ];
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

        return [
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
        ];
    }

    protected function formatDate(?string $date): string
    {
        if (!$date || $date === '0000-00-00') {
            return '-';
        }

        return date('d-m-Y', strtotime($date));
    }
}
