<?php

namespace App\Repositories;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\BaseConnection;

class NotificationRepository
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? db_connect();
    }

    public function listForUser(int $userId, int $groupId, ?int $idProyek = null, int $offset = 0, int $limit = 10, bool $all = false): array
    {
        if ($this->usesRecipientReadPath()) {
            return $this->newListQuery($userId, $idProyek, $offset, $limit, $all)->get()->getResult();
        }

        $builder = $this->legacyBaseQuery();
        if ($idProyek) {
            $this->applyProjectFilter($builder, $idProyek);
        }
        $this->applyLegacyAudienceFilter($builder, $userId, $groupId);

        if (! $all) {
            $builder->orderBy('notification.is_read', 'asc');
        }

        return $builder
            ->orderBy('notification.created_at', 'desc')
            ->limit($limit, $offset)
            ->get()
            ->getResult();
    }

    public function unreadCountForUser(int $userId, int $groupId, ?int $idProyek = null): int
    {
        if ($this->usesRecipientReadPath()) {
            $builder = $this->db->table('notification_recipients nr')
                ->join('notification', 'notification.id = nr.notification_id')
                ->join('kavling', 'kavling.id_kavling = notification.id_kavling', 'left')
                ->join('jalan', 'jalan.id_jalan = kavling.id_jalan', 'left')
                ->join('cluster', 'jalan.id_cluster = cluster.id_cluster', 'left')
                ->join('proyek', 'proyek.id_proyek = cluster.id_proyek', 'left')
                ->where('nr.user_id', $userId)
                ->where('nr.read_at IS NULL', null, false);

            if ($idProyek) {
                $this->applyProjectFilter($builder, $idProyek);
            }

            return (int) $builder->countAllResults();
        }

        $builder = $this->db->table('notification')
            ->join('kavling', 'kavling.id_kavling = notification.id_kavling', 'left')
            ->join('jalan', 'jalan.id_jalan = kavling.id_jalan', 'left')
            ->join('cluster', 'jalan.id_cluster = cluster.id_cluster', 'left')
            ->join('proyek', 'proyek.id_proyek = cluster.id_proyek', 'left')
            ->where('notification.is_read', 0);

        if ($idProyek) {
            $this->applyProjectFilter($builder, $idProyek);
        }
        $this->applyLegacyAudienceFilter($builder, $userId, $groupId);

        return (int) $builder->countAllResults();
    }

    public function markAsReadForUser(int $notificationId, int $userId): bool
    {
        if ($this->usesRecipientReadPath()) {
            $this->db->table('notification_recipients')
                ->where('notification_id', $notificationId)
                ->where('user_id', $userId)
                ->where('read_at IS NULL', null, false)
                ->update([
                    'read_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

            $affected = $this->db->affectedRows() > 0;
            if ($affected) {
                $this->syncLegacyReadState($notificationId);
            }

            return $affected;
        }

        if (! $this->canSeeLegacyNotification($notificationId, $userId, $this->currentGroupId($userId))) {
            return false;
        }

        $this->db->table('notification')
            ->where('id', $notificationId)
            ->update(['is_read' => 1]);

        return $this->db->affectedRows() > 0;
    }

    private function newListQuery(int $userId, ?int $idProyek, int $offset, int $limit, bool $all): BaseBuilder
    {
        $builder = $this->newBaseQuery($userId);
        if ($idProyek) {
            $this->applyProjectFilter($builder, $idProyek);
        }

        if (! $all) {
            $builder->orderBy('is_read', 'asc', false);
        }

        return $builder
            ->orderBy('notification.created_at', 'desc')
            ->limit($limit, $offset);
    }

    private function newBaseQuery(int $userId): BaseBuilder
    {
        return $this->db->table('notification_recipients nr')
            ->select('notification.*, users.username, nama_jalan, no_kavling, COALESCE(notification.id_proyek, proyek.id_proyek) as id_proyek')
            ->select('MIN(auth_groups.id) as divisi_id, MIN(auth_groups.name) as divisi', false)
            ->select('CASE WHEN nr.read_at IS NULL THEN 0 ELSE 1 END as is_read', false)
            ->join('notification', 'notification.id = nr.notification_id')
            ->join('users', 'users.id = notification.add_by', 'left')
            ->join('kavling', 'kavling.id_kavling = notification.id_kavling', 'left')
            ->join('jalan', 'jalan.id_jalan = kavling.id_jalan', 'left')
            ->join('cluster', 'jalan.id_cluster = cluster.id_cluster', 'left')
            ->join('proyek', 'proyek.id_proyek = cluster.id_proyek', 'left')
            ->join('auth_groups_users', 'auth_groups_users.user_id = notification.add_by', 'left')
            ->join('auth_groups', 'auth_groups.id = auth_groups_users.group_id', 'left')
            ->where('nr.user_id', $userId)
            ->groupBy('notification.id');
    }

    private function legacyBaseQuery(): BaseBuilder
    {
        return $this->db->table('notification')
            ->select('notification.*, users.username, nama_jalan, no_kavling, COALESCE(notification.id_proyek, proyek.id_proyek) as id_proyek')
            ->select('MIN(auth_groups.id) as divisi_id, MIN(auth_groups.name) as divisi', false)
            ->join('users', 'users.id = notification.add_by', 'left')
            ->join('kavling', 'kavling.id_kavling = notification.id_kavling', 'left')
            ->join('jalan', 'jalan.id_jalan = kavling.id_jalan', 'left')
            ->join('cluster', 'jalan.id_cluster = cluster.id_cluster', 'left')
            ->join('proyek', 'proyek.id_proyek = cluster.id_proyek', 'left')
            ->join('auth_groups_users', 'auth_groups_users.user_id = notification.add_by', 'left')
            ->join('auth_groups', 'auth_groups.id = auth_groups_users.group_id', 'left')
            ->groupBy('notification.id');
    }

    private function applyProjectFilter(BaseBuilder $builder, int $idProyek): void
    {
        $builder->where('COALESCE(notification.id_proyek, proyek.id_proyek)', $idProyek);
    }

    private function applyLegacyAudienceFilter(BaseBuilder $builder, int $userId, int $groupId): void
    {
        if ($groupId === 1) {
            return;
        }

        $role = (string) $groupId;
        $builder->groupStart();
        if ($role !== '0') {
            $builder->where('notification.group_target', $role)
                ->orLike('notification.group_target', $role . ';', 'after')
                ->orLike('notification.group_target', ';' . $role . ';', 'both')
                ->orLike('notification.group_target', ';' . $role, 'before');
        }

        $builder->orWhere('notification.group_target', '0')
            ->orWhere('notification.user_id', $userId)
            ->orWhere('notification.add_by', $userId)
            ->groupEnd();
    }

    private function hasRecipientTable(): bool
    {
        return $this->db->tableExists('notification_recipients');
    }

    private function usesRecipientReadPath(): bool
    {
        if (! $this->hasRecipientTable()) {
            return false;
        }

        $value = getenv('NOTIF_USE_RECIPIENT_READ_PATH');
        if ($value === false || $value === '') {
            return true;
        }

        return in_array(strtolower((string) $value), ['1', 'true', 'yes', 'on'], true);
    }

    private function syncLegacyReadState(int $notificationId): void
    {
        $unread = $this->db->table('notification_recipients')
            ->where('notification_id', $notificationId)
            ->where('read_at IS NULL', null, false)
            ->countAllResults();

        if ((int) $unread === 0) {
            $this->db->table('notification')
                ->where('id', $notificationId)
                ->update(['is_read' => 1]);
        }
    }

    private function currentGroupId(int $userId): int
    {
        $row = $this->db->table('auth_groups_users')
            ->select('group_id')
            ->where('user_id', $userId)
            ->get()
            ->getRow();

        return (int) ($row->group_id ?? 0);
    }

    private function canSeeLegacyNotification(int $notificationId, int $userId, int $groupId): bool
    {
        $builder = $this->db->table('notification')
            ->where('id', $notificationId);
        $this->applyLegacyAudienceFilter($builder, $userId, $groupId);

        return (bool) $builder->countAllResults();
    }
}
