<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

class NotificationPreferenceService
{
    protected BaseConnection $db;

    public function __construct()
    {
        $this->db = db_connect();
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
    public function getUserPreferences(int $userId, ?int $groupId = null): array
    {
        // Auto-seed if empty (berguna untuk shared hosting yang sulit akses CLI)
        if ($this->db->table('notification_event_types')->countAllResults() === 0) {
            $seeder = \Config\Database::seeder();
            $seeder->call('NotificationEventTypeSeeder');
        }

        $builder = $this->db->table('notification_event_types e')
            ->select('e.*, p.in_app, p.email, p.web_push, p.is_locked')
            ->join('user_notification_preferences p', "p.event_type = e.event_type AND p.user_id = $userId", 'left')
            ->orderBy('e.category', 'asc')
            ->orderBy('e.sort_order', 'asc');

        $rows = $builder->get()->getResult();

        $result = [];
        foreach ($rows as $row) {
            $isMandatory = (int) $row->is_mandatory === 1;
            $result[] = [
                'event_type' => $row->event_type,
                'category' => $row->category,
                'label' => $row->label,
                'description' => $row->description,
                'is_mandatory' => $isMandatory,
                'is_locked' => $isMandatory || (bool)$row->is_locked,
                'in_app' => $isMandatory ? true : ($row->in_app !== null ? (bool)$row->in_app : (bool)$row->default_in_app),
                'email' => $isMandatory ? true : ($row->email !== null ? (bool)$row->email : (bool)$row->default_email),
                'web_push' => $isMandatory ? true : ($row->web_push !== null ? (bool)$row->web_push : (bool)$row->default_web_push),
            ];
        }

        return $result;
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

        $rows = $this->db->table('notification_recipients nr')
            ->select('nr.id, e.is_mandatory, e.default_in_app, p.in_app')
            ->join('notification n', 'n.id = nr.notification_id')
            ->join('notification_event_types e', 'e.event_type = n.type')
            ->join('user_notification_preferences p', 'p.user_id = nr.user_id AND p.event_type = e.event_type', 'left')
            ->where('nr.user_id', $userId)
            ->get()
            ->getResult();

        $updates = [];
        foreach ($rows as $row) {
            if ((int) $row->is_mandatory === 1) {
                $visible = 1;
            } elseif ($row->in_app !== null) {
                $visible = (int) $row->in_app === 1 ? 1 : 0;
            } else {
                $visible = (int) $row->default_in_app === 1 ? 1 : 0;
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
}
