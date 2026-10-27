<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

class NotificationPreferenceService
{
    protected BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? db_connect();
    }

    /**
     * Cek apakah notifikasi diizinkan untuk user, event, dan channel tertentu.
     */
    public function isAllowed(int $userId, string $eventType, string $channel): bool
    {
        // channel di-map ke kolom tabel preferences: in_app, email, web_push
        if (!in_array($channel, ['in_app', 'email', 'web_push'])) {
            return true; // Default fallback if unknown channel
        }

        $eventType = \App\Enums\NotificationEvent::fromLegacyType($eventType) ?? $eventType;

        // Ambil data event dari registry
        $eventDef = $this->db->table('notification_event_types')
            ->where('event_type', $eventType)
            ->get()
            ->getRow();

        // Jika event tidak ada di registry, by default allow
        if (!$eventDef) {
            return true;
        }

        // Cek mandatory flag dari event
        if ((int)$eventDef->is_mandatory === 1) {
            return true;
        }

        // Ambil preferensi user
        $pref = $this->db->table('user_notification_preferences')
            ->where('user_id', $userId)
            ->where('event_type', $eventType)
            ->get()
            ->getRow();

        if ($pref) {
            // Jika ada override admin (locked), dan admin force mandatory, logic bisa ditambah di sini.
            // Tapi yang penting adalah membaca nilai preference user.
            return (int)$pref->{$channel} === 1;
        }

        // Fallback ke default dari registry
        $defaultCol = 'default_' . $channel;
        return (int)$eventDef->{$defaultCol} === 1;
    }

    /**
     * Ambil seluruh preferensi user digabung dengan nilai default event registry.
     */
    public function getUserPreferences(int $userId, ?int $userGroupId = null): array
    {
        // Auto-seed if empty (berguna untuk shared hosting yang sulit akses CLI)
        if ($this->db->table('notification_event_types')->countAllResults() === 0) {
            (new \App\Database\Seeds\NotificationEventTypeSeeder(new \Config\Database(), $this->db))->run();
        }

        $userGroups = $this->resolveUserGroups($userId, $userGroupId);

        $builder = $this->db->table('notification_event_types e')
            ->select('e.*, p.in_app, p.email, p.web_push, p.is_locked')
            ->join('user_notification_preferences p', "p.event_type = e.event_type AND p.user_id = $userId", 'left')
            ->orderBy('e.category', 'asc')
            ->orderBy('e.sort_order', 'asc');

        $rows = $builder->get()->getResult();

        $result = [];
        foreach ($rows as $row) {
            $isMandatory = (int) $row->is_mandatory === 1;
            $isRelevantGroup = $this->isRelevantForGroups($row->relevant_groups ?? null, $userGroups);

            $defaultInApp   = $isRelevantGroup && (bool) $row->default_in_app;
            $defaultEmail   = $isRelevantGroup && (bool) $row->default_email;
            $defaultWebPush = $isRelevantGroup && (bool) $row->default_web_push;

            $result[] = [
                'event_type' => $row->event_type,
                'category' => $row->category,
                'label' => $row->label,
                'description' => $row->description,
                'relevant_groups' => $row->relevant_groups ?? null,
                'is_mandatory' => $isMandatory,
                'is_locked' => $isMandatory || (bool)$row->is_locked,
                'in_app' => $isMandatory ? true : ($row->in_app !== null ? (bool)$row->in_app : $defaultInApp),
                'email' => $isMandatory ? true : ($row->email !== null ? (bool)$row->email : $defaultEmail),
                'web_push' => $isMandatory ? true : ($row->web_push !== null ? (bool)$row->web_push : $defaultWebPush),
            ];
        }

        return $result;
    }

    /**
     * @return list<int>
     */
    private function resolveUserGroups(int $userId, ?int $userGroupId = null): array
    {
        $userGroups = [];
        if ($userGroupId !== null && $userGroupId > 0) {
            $userGroups[] = $userGroupId;
        }
        if ($userId > 0 && $this->db->tableExists('auth_groups_users')) {
            $groupRows = $this->db->table('auth_groups_users')
                ->select('group_id')
                ->where('user_id', $userId)
                ->get()
                ->getResult();
            foreach ($groupRows as $gr) {
                $gid = (int) $gr->group_id;
                if ($gid > 0) {
                    $userGroups[] = $gid;
                }
            }
        }

        return array_values(array_unique($userGroups));
    }

    private function isRelevantForGroups(?string $relevantGroups, array $userGroups): bool
    {
        $raw = trim((string) ($relevantGroups ?? ''));
        if ($raw === '' || $raw === '0') {
            return true;
        }

        $allowedGroups = array_values(array_filter(array_map('intval', preg_split('/[;,]+/', $raw))));
        if ($allowedGroups === []) {
            return true;
        }

        if ($userGroups === []) {
            return false;
        }

        return count(array_intersect($userGroups, $allowedGroups)) > 0;
    }

    /**
     * Simpan preferensi dari form.
     */
    public function savePreferences(int $userId, array $preferences): void
    {
        $now = date('Y-m-d H:i:s');
        
        $this->db->transStart();

        foreach ($preferences as $eventType => $channels) {
            $eventDef = $this->db->table('notification_event_types')
                ->select('is_mandatory')
                ->where('event_type', $eventType)
                ->get()
                ->getRow();

            if ($eventDef && (int) $eventDef->is_mandatory === 1) {
                continue;
            }

            $existing = $this->db->table('user_notification_preferences')
                ->where('user_id', $userId)
                ->where('event_type', $eventType)
                ->get()
                ->getRow();
                
            // Jika dikunci oleh admin, skip update
            if ($existing && (int)$existing->is_locked === 1) {
                continue;
            }

            if ($existing) {
                $this->db->table('user_notification_preferences')
                    ->where('id', $existing->id)
                    ->update([
                        'in_app' => $channels['in_app'] ? 1 : 0,
                        'email' => $channels['email'] ? 1 : 0,
                        'web_push' => $channels['web_push'] ? 1 : 0,
                        'updated_at' => $now,
                    ]);
            } else {
                $this->db->table('user_notification_preferences')->insert([
                    'user_id' => $userId,
                    'event_type' => $eventType,
                    'in_app' => $channels['in_app'] ? 1 : 0,
                    'email' => $channels['email'] ? 1 : 0,
                    'web_push' => $channels['web_push'] ? 1 : 0,
                    'is_locked' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        $this->syncInAppVisibility($userId, $now);

        $this->db->transComplete();
    }

    /**
     * Reset preferensi ke default (menghapus setting yang tidak dikunci).
     */
    public function resetToDefaults(int $userId): void
    {
        $this->db->table('user_notification_preferences')
            ->where('user_id', $userId)
            ->where('is_locked', 0) // Jangan hapus yang dikunci admin
            ->delete();

        $this->syncInAppVisibility($userId);
    }

    private function syncInAppVisibility(int $userId, ?string $now = null): void
    {
        if (! $this->db->tableExists('notification_recipients')
            || ! $this->db->fieldExists('in_app_visible', 'notification_recipients')
            || ! $this->db->tableExists('notification_event_types')
        ) {
            return;
        }

        $now ??= date('Y-m-d H:i:s');

        $events = [];
        foreach ($this->db->table('notification_event_types')->get()->getResult() as $eventRow) {
            $events[$eventRow->event_type] = $eventRow;
        }
        if ($events === []) {
            return;
        }

        $prefs = [];
        foreach ($this->db->table('user_notification_preferences')->where('user_id', $userId)->get()->getResult() as $prefRow) {
            $prefs[$prefRow->event_type] = (int) $prefRow->in_app;
        }

        $userGroups = $this->resolveUserGroups($userId);

        $rows = $this->db->table('notification_recipients nr')
            ->select('nr.id, n.type, n.notif')
            ->join('notification n', 'n.id = nr.notification_id')
            ->where('nr.user_id', $userId)
            ->get()
            ->getResult();

        $updates = [];
        foreach ($rows as $row) {
            $eventType = $this->resolveEventTypeFromNotification((string) ($row->type ?? ''), (string) ($row->notif ?? ''));
            if ($eventType === null || ! isset($events[$eventType])) {
                continue;
            }

            $eventDef = $events[$eventType];
            if ((int) $eventDef->is_mandatory === 1) {
                $visible = 1;
            } elseif (array_key_exists($eventType, $prefs)) {
                $visible = $prefs[$eventType] === 1 ? 1 : 0;
            } else {
                $isRelevant = $userGroups === []
                    || in_array(1, $userGroups, true)
                    || $this->isRelevantForGroups($eventDef->relevant_groups ?? null, $userGroups);
                $visible = ($isRelevant && (int) $eventDef->default_in_app === 1) ? 1 : 0;
            }

            $updates[] = [
                'id' => (int) $row->id,
                'in_app_visible' => $visible,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($updates, 100) as $chunk) {
            $this->db->table('notification_recipients')->updateBatch($chunk, 'id');
        }
    }

    private function resolveEventTypeFromNotification(string $rawType, string $notifText): ?string
    {
        $rawType = trim($rawType);
        if ($rawType === '') {
            return null;
        }

        if ($rawType === 'keuangan') {
            return 'pencairan_hasil_akad';
        }

        if (str_starts_with($rawType, 'tiket_masalah|')) {
            $parts = explode('|', $rawType);
            if (! empty($parts[4])) {
                return $parts[4];
            }
            if (str_starts_with($notifText, 'Tiket masalah baru ditugaskan')) {
                return 'tiket_masalah_assign';
            }
            if (str_starts_with($notifText, 'Tiket masalah baru:')) {
                return 'tiket_masalah_baru';
            }

            return 'tiket_masalah_update';
        }

        return \App\Enums\NotificationEvent::fromLegacyType($rawType) ?? $rawType;
    }
}
