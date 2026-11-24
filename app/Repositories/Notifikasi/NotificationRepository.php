<?php

namespace App\Repositories\Notifikasi;

use App\Support\NotificationTaxonomy;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\BaseConnection;

class NotificationRepository
{
    private BaseConnection $db;
    private ?bool $hasRecipientVisibilityColumn = null;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? db_connect();
    }

    public function listForUser(
        int $userId,
        int $groupId,
        ?int $idProyek = null,
        int $offset = 0,
        int $limit = 10,
        bool $all = false,
        ?string $intent = null,
        ?string $category = null
    ): array {
        $intentFilter   = $intent !== null && trim($intent) !== '' ? trim($intent) : null;
        $categoryFilter = $category !== null && trim($category) !== '' ? trim($category) : null;

        if ($intentFilter === null && $categoryFilter === null) {
            $rows = $this->buildListBuilder($userId, $groupId, $idProyek, $offset, $limit, $all)
                ->get()
                ->getResult();

            return $this->attachTaxonomy($rows);
        }

        $targetCount = max(1, $offset + $limit);
        $batchSize   = 100;
        $maxScan     = 1000;
        $scanOffset  = 0;
        $matched     = [];

        while ($scanOffset < $maxScan && count($matched) < $targetCount) {
            $batch = $this->buildListBuilder($userId, $groupId, $idProyek, $scanOffset, $batchSize, $all)
                ->get()
                ->getResult();

            if ($batch === []) {
                break;
            }

            foreach ($batch as $row) {
                $taxonomy = NotificationTaxonomy::resolve($row);
                if ($intentFilter !== null && $taxonomy['intent'] !== $intentFilter) {
                    continue;
                }
                if ($categoryFilter !== null && strcasecmp($taxonomy['category'], $categoryFilter) !== 0) {
                    continue;
                }

                $this->applyTaxonomyToRow($row, $taxonomy);
                $matched[] = $row;

                if (count($matched) >= $targetCount) {
                    break;
                }
            }

            if (count($batch) < $batchSize) {
                break;
            }

            $scanOffset += $batchSize;
        }

        return array_slice($matched, $offset, $limit);
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

            $this->applyRecipientVisibilityFilter($builder);

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

        if (! $this->canSeeLegacyNotification($notificationId, $userId, $this->getUserGroupId($userId))) {
            return false;
        }

        $this->db->table('notification')
            ->where('id', $notificationId)
            ->update(['is_read' => 1]);

        return $this->db->affectedRows() > 0;
    }

    public function markAllAsReadForUser(int $userId): bool
    {
        if ($this->usesRecipientReadPath()) {
            $builder = $this->db->table('notification_recipients');
            if ($this->hasRecipientVisibilityColumn()) {
                $builder->where('in_app_visible', 1);
            }

            $builder
                ->where('user_id', $userId)
                ->where('read_at IS NULL', null, false)
                ->update([
                    'read_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

            return $this->db->affectedRows() > 0;
        }

        // Legacy mark all as read is difficult to do perfectly without joining, 
        // but we'll update everything the user can see.
        $subquery = $this->db->table('notification')
            ->select('id')
            ->where('is_read', 0);
        $this->applyLegacyAudienceFilter($subquery, $userId, $this->getUserGroupId($userId));
        $ids = array_column($subquery->get()->getResultArray(), 'id');

        if (empty($ids)) {
            return false;
        }

        $this->db->table('notification')
            ->whereIn('id', $ids)
            ->update(['is_read' => 1]);

        return $this->db->affectedRows() > 0;
    }

    public function getUserGroupId(int $userId): int
    {
        $row = $this->db->table('auth_groups_users')
            ->select('group_id')
            ->where('user_id', $userId)
            ->get()
            ->getRow();

        return (int) ($row->group_id ?? 0);
    }

    public function getProyekName(int $idProyek): string
    {
        if ($idProyek <= 0) {
            return '';
        }

        $row = $this->db->table('proyek')
            ->select('nama_proyek')
            ->where('id_proyek', $idProyek)
            ->get()
            ->getRow();

        return (string) ($row->nama_proyek ?? '');
    }

    public function getActorDepartmentContext(int $userId): array
    {
        $departmentSubquery = $this->db->table('auth_groups_users agu')
            ->select('agu.user_id, MIN(ag.name) as department', false)
            ->join('auth_groups ag', 'ag.id = agu.group_id')
            ->groupBy('agu.user_id')
            ->getCompiledSelect();

        $row = $this->db->table('users')
            ->select('users.name, users.username, actor_department.department')
            ->join("({$departmentSubquery}) actor_department", 'actor_department.user_id = users.id', 'left')
            ->where('users.id', $userId)
            ->get()
            ->getRowArray();

        return $row ?: [];
    }

    private function buildListBuilder(int $userId, int $groupId, ?int $idProyek, int $offset, int $limit, bool $all): BaseBuilder
    {
        if ($this->usesRecipientReadPath()) {
            return $this->newListQuery($userId, $idProyek, $offset, $limit, $all);
        }

        $builder = $this->legacyBaseQuery();
        if ($idProyek) {
            $this->applyProjectFilter($builder, $idProyek);
        }
        $this->applyLegacyAudienceFilter($builder, $userId, $groupId);

        return $builder
            ->orderBy('notification.created_at', 'desc')
            ->limit($limit, $offset);
    }

    private function attachTaxonomy(array $rows): array
    {
        foreach ($rows as $row) {
            $this->applyTaxonomyToRow($row, NotificationTaxonomy::resolve($row));
        }

        return $rows;
    }

    private function applyTaxonomyToRow(array|object $row, array $taxonomy): void
    {
        if (is_object($row)) {
            $row->event_type    = $taxonomy['event_type'];
            $row->category      = $taxonomy['category'];
            $row->intent        = $taxonomy['intent'];
            $row->target_module = $taxonomy['target_module'];
            $row->ref_id        = $taxonomy['ref_id'];
            $row->spa_route     = $taxonomy['spa_route'];
        } elseif (is_array($row)) {
            $row['event_type']    = $taxonomy['event_type'];
            $row['category']      = $taxonomy['category'];
            $row['intent']        = $taxonomy['intent'];
            $row['target_module'] = $taxonomy['target_module'];
            $row['ref_id']        = $taxonomy['ref_id'];
            $row['spa_route']     = $taxonomy['spa_route'];
        }
    }

    private function newListQuery(int $userId, ?int $idProyek, int $offset, int $limit, bool $all): BaseBuilder
    {
        $builder = $this->newBaseQuery($userId);
        if ($idProyek) {
            $this->applyProjectFilter($builder, $idProyek);
        }

        return $builder
            ->orderBy('notification.created_at', 'desc')
            ->limit($limit, $offset);
    }

    private function newBaseQuery(int $userId): BaseBuilder
    {
        $builder = $this->db->table('notification_recipients nr')
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
            ->where('nr.user_id', $userId);

        $this->applyRecipientVisibilityFilter($builder);

        return $builder->groupBy('notification.id');
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

    private function applyRecipientVisibilityFilter(BaseBuilder $builder): void
    {
        if ($this->hasRecipientVisibilityColumn()) {
            $builder->where('nr.in_app_visible', 1);
        }
    }

    private function hasRecipientVisibilityColumn(): bool
    {
        if ($this->hasRecipientVisibilityColumn !== null) {
            return $this->hasRecipientVisibilityColumn;
        }

        return $this->hasRecipientVisibilityColumn = $this->db->fieldExists('in_app_visible', 'notification_recipients');
    }

    private function syncLegacyReadState(int $notificationId): void
    {
        $builder = $this->db->table('notification_recipients')
            ->where('notification_id', $notificationId)
            ->where('read_at IS NULL', null, false);

        if ($this->hasRecipientVisibilityColumn()) {
            $builder->where('in_app_visible', 1);
        }

        $unread = $builder->countAllResults();

        if ((int) $unread === 0) {
            $this->db->table('notification')
                ->where('id', $notificationId)
                ->update(['is_read' => 1]);
        }
    }

    private function canSeeLegacyNotification(int $notificationId, int $userId, int $groupId): bool
    {
        $builder = $this->db->table('notification')
            ->where('id', $notificationId);
        $this->applyLegacyAudienceFilter($builder, $userId, $groupId);

        return (bool) $builder->countAllResults();
    }
}
