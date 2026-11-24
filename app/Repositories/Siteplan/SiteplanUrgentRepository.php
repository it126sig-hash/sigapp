<?php

namespace App\Repositories\Siteplan;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\BaseConnection;
use Config\Database;

class SiteplanUrgentRepository
{
    protected BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? Database::connect();
    }

    public function getUserGroupId(int $userId): int
    {
        if ($userId <= 0) {
            return 0;
        }

        $group = $this->db->table('auth_groups_users')
            ->select('group_id')
            ->where('user_id', $userId)
            ->get()
            ->getRow();

        return (int) ($group->group_id ?? 0);
    }

    public function countTagihanOverdue(int $idProyek, string $today): int
    {
        return (int) $this->tagihanBaseBuilder($idProyek)
            ->where('keuangan.jatuh_tempo_tgl <', $today)
            ->countAllResults();
    }

    public function countTagihanDue(int $idProyek, string $today, string $limitDate): int
    {
        return (int) $this->tagihanBaseBuilder($idProyek)
            ->where('keuangan.jatuh_tempo_tgl >=', $today)
            ->where('keuangan.jatuh_tempo_tgl <=', $limitDate)
            ->countAllResults();
    }

    public function countCashoutSubkonUrgent(int $idProyek, string $limitDate): int
    {
        return (int) ($this->cashoutSubkonBaseBuilder($idProyek, $limitDate)
            ->select('COUNT(DISTINCT csd.id_cashout_subkon_detail) AS cnt', false)
            ->get()
            ->getRow()
            ->cnt ?? 0);
    }

    public function countPembangunanTelat(int $idProyek, string $today): int
    {
        return (int) ($this->pembangunanTelatBaseBuilder($idProyek, $today)
            ->select('COUNT(DISTINCT pr.id_produksi) AS cnt', false)
            ->get()
            ->getRow()
            ->cnt ?? 0);
    }

    public function countMkdtDateAlerts(string $field, ?int $idProyek = null, ?string $fromDate = null, ?string $toDate = null): int
    {
        if (!in_array($field, ['sp3k_tgl_exp', 'rencana_akad_tgl'], true)) {
            return 0;
        }

        $builder = $this->db->table('mkdt m')
            ->join('kavling k', 'k.id_kavling = m.id_kavling')
            ->join('jalan j', 'j.id_jalan = k.id_jalan')
            ->join('cluster cl', 'cl.id_cluster = j.id_cluster')
            ->join('proyek p', 'p.id_proyek = cl.id_proyek');

        if ($idProyek !== null && $idProyek > 0) {
            $builder->where('p.id_proyek', $idProyek);
        }

        $builder->where("m.{$field} IS NOT NULL", null, false)
            ->where("m.{$field} >", '1000-01-01');

        if ($fromDate !== null) {
            $builder->where("m.{$field} >=", $fromDate);
        }
        if ($toDate !== null) {
            $builder->where("m.{$field} <=", $toDate);
        }

        return (int) $builder
            ->groupStart()
                ->where('m.akad_tgl IS NULL', null, false)
                ->orWhere('m.akad_tgl <=', '1000-01-01')
            ->groupEnd()
            ->groupStart()
                ->where('m.is_batal', 0)
                ->orWhere('m.is_batal IS NULL', null, false)
            ->groupEnd()
            ->countAllResults();
    }

    public function hasSnoozeTable(): bool
    {
        return $this->db->tableExists('notification_snoozes');
    }

    public function upsertSnooze(int $userId, int $idProyek, string $itemKey, string $itemType, string $snoozedUntil, string $now): bool
    {
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

    public function getActiveSnoozeKeys(int $userId, int $idProyek, string $now): array
    {
        if (!$this->hasSnoozeTable()) {
            return [];
        }

        $rows = $this->db->table('notification_snoozes')
            ->select('item_key')
            ->where('user_id', $userId)
            ->where('id_proyek', $idProyek)
            ->where('snoozed_until >', $now)
            ->get()
            ->getResult();

        return array_values(array_map(static function ($row) {
            return (string) $row->item_key;
        }, $rows));
    }

    public function getTagihanUrgent(int $idProyek, string $limitDate): array
    {
        return $this->tagihanBaseBuilder($idProyek)
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
            ->join('konsumen c', 'c.id_konsumen = m.id_konsumen', 'left')
            ->join('hargajual hj', 'hj.id = k.harga_akhir', 'left')
            ->where('keuangan.jatuh_tempo_tgl <=', $limitDate)
            ->orderBy('keuangan.jatuh_tempo_tgl', 'ASC')
            ->limit(50)
            ->get()
            ->getResult();
    }

    public function getCashoutSubkonUrgent(int $idProyek, string $limitDate, int $groupId): array
    {
        $builder = $this->cashoutSubkonBaseBuilder($idProyek, $limitDate)
            ->select("
                csd.id_cashout_subkon_detail,
                csd.id_cashout_subkon,
                csd.berita_acara,
                csd.nominal,
                csd.tanggal_jatuh_tempo,
                csd.status,
                cs.nomor_surat,
                s.nama_subkon,
                MIN(k.id_kavling) AS id_kavling,
                MIN(k.no_kavling) AS no_kavling,
                MIN(j.nama_jalan) AS nama_jalan,
                GROUP_CONCAT(CONCAT(j.nama_jalan, ' No. ', k.no_kavling) ORDER BY j.nama_jalan, k.no_kavling SEPARATOR ', ') AS daftar_kavling,
                cl.nama_cluster,
                p.id_proyek,
                p.nama_proyek
            ", false)
            ->join('subkon s', 's.id = cs.id_subkon', 'left');

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
            ->groupBy([
                'csd.id_cashout_subkon_detail',
                'csd.id_cashout_subkon',
                'csd.berita_acara',
                'csd.nominal',
                'csd.tanggal_jatuh_tempo',
                'csd.status',
                'cs.nomor_surat',
                's.nama_subkon',
                'cl.nama_cluster',
                'p.id_proyek',
                'p.nama_proyek',
            ])
            ->orderBy('csd.tanggal_jatuh_tempo IS NULL', 'ASC', false)
            ->orderBy('csd.tanggal_jatuh_tempo', 'ASC')
            ->orderBy('csd.id_cashout_subkon_detail', 'DESC')
            ->limit(50)
            ->get()
            ->getResult();
    }

    public function getMkdtDateUrgent(int $idProyek, string $field, string $limitDate): array
    {
        if (!in_array($field, ['sp3k_tgl_exp', 'rencana_akad_tgl'], true)) {
            return [];
        }

        $builder = $this->db->table('mkdt m')
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
            ->join('tipe t', 't.id_tipe = k.id_tipe', 'left');

        if ($idProyek > 0) {
            $builder->where('p.id_proyek', $idProyek);
        }

        return $builder
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

    public function getPembangunanTelat(int $idProyek, string $today): array
    {
        return $this->pembangunanTelatBaseBuilder($idProyek, $today)
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
            ->join('konsumen c', 'c.id_konsumen = m.id_konsumen', 'left')
            ->join('tipe t', 't.id_tipe = k.id_tipe', 'left')
            ->orderBy('pr.tanggal_rencana_selesai_pembangunan', 'ASC')
            ->limit(50)
            ->get()
            ->getResult();
    }

    public function getUnreadNotifications(int $idProyek, int $groupId): array
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

    protected function tagihanBaseBuilder(int $idProyek): BaseBuilder
    {
        $builder = $this->db->table('keuangan')
            ->join('mkdt m', 'm.id_mkdt = keuangan.id_mkdt')
            ->join('kavling k', 'k.id_kavling = m.id_kavling')
            ->join('jalan j', 'j.id_jalan = k.id_jalan')
            ->join('cluster cl', 'cl.id_cluster = j.id_cluster')
            ->join('proyek p', 'p.id_proyek = cl.id_proyek');

        if ($idProyek > 0) {
            $builder->where('p.id_proyek', $idProyek);
        }

        return $builder
            ->where('keuangan.sudah_dibayar', 0)
            ->where('keuangan.is_void', 0)
            ->where('keuangan.jatuh_tempo_tgl IS NOT NULL', null, false)
            ->where('keuangan.jatuh_tempo_tgl >', '1000-01-01')
            ->groupStart()
            ->where('m.is_batal', 0)
            ->orWhere('m.is_batal IS NULL', null, false)
            ->groupEnd();
    }

    protected function cashoutSubkonBaseBuilder(int $idProyek, string $limitDate): BaseBuilder
    {
        $builder = $this->db->table('cashout_subkon_detail csd')
            ->join('cashout_subkon cs', 'cs.id_cashout_subkon = csd.id_cashout_subkon')
            ->join('cashout_subkon_kavling csk', 'csk.id_cashout_subkon = cs.id_cashout_subkon')
            ->join('kavling k', 'k.id_kavling = csk.id_kavling')
            ->join('jalan j', 'j.id_jalan = k.id_jalan')
            ->join('cluster cl', 'cl.id_cluster = j.id_cluster')
            ->join('proyek p', 'p.id_proyek = cl.id_proyek');

        if ($idProyek > 0) {
            $builder->where('p.id_proyek', $idProyek);
        }

        return $builder
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
    }

    protected function pembangunanTelatBaseBuilder(int $idProyek, string $today): BaseBuilder
    {
        $builder = $this->db->table('produksi pr')
            ->join('kavling k', 'k.id_produksi = pr.id_produksi')
            ->join('jalan j', 'j.id_jalan = k.id_jalan')
            ->join('cluster cl', 'cl.id_cluster = j.id_cluster')
            ->join('proyek p', 'p.id_proyek = cl.id_proyek')
            ->join('mkdt m', 'm.id_kavling = k.id_kavling', 'left');

        if ($idProyek > 0) {
            $builder->where('p.id_proyek', $idProyek);
        }

        return $builder
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
            ->groupEnd();
    }

    protected function applyGroupTargetFilter(BaseBuilder $builder, int $groupId): void
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
}
