<?php

namespace App\Repositories\Copilot;

use CodeIgniter\Database\BaseConnection;

class SettingRepository
{
    public const ALLOWED_KEYS = ['provider', 'model', 'base_url'];

    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? db_connect();
    }

    /**
     * Return whitelisted settings as key => value map.
     *
     * @return array<string, ?string>
     */
    public function getAll(): array
    {
        if (! $this->db->tableExists('copilot_settings')) {
            return [];
        }

        $rows = $this->db->table('copilot_settings')
            ->whereIn('setting_key', self::ALLOWED_KEYS)
            ->get()
            ->getResult();

        $result = [];
        foreach ($rows as $row) {
            $result[(string) $row->setting_key] = $row->setting_value !== null ? (string) $row->setting_value : null;
        }

        return $result;
    }

    public function get(string $key, ?string $default = null): ?string
    {
        if (! in_array($key, self::ALLOWED_KEYS, true)) {
            return $default;
        }

        if (! $this->db->tableExists('copilot_settings')) {
            return $default;
        }

        $row = $this->db->table('copilot_settings')
            ->where('setting_key', $key)
            ->get()
            ->getRow();

        if (! $row || $row->setting_value === null) {
            return $default;
        }

        return (string) $row->setting_value;
    }

    /**
     * Upsert whitelisted setting pairs. Unknown keys (including secrets) are ignored.
     *
     * @param array<string, ?string> $pairs
     */
    public function upsertMany(array $pairs, ?int $userId = null): void
    {
        $filtered = [];
        foreach ($pairs as $key => $value) {
            if (! in_array($key, self::ALLOWED_KEYS, true)) {
                continue;
            }
            $filtered[$key] = $value === null ? null : trim((string) $value);
        }

        if ($filtered === []) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $this->db->transStart();

        foreach ($filtered as $key => $value) {
            $existing = $this->db->table('copilot_settings')
                ->where('setting_key', $key)
                ->get()
                ->getRow();

            $payload = [
                'setting_value' => $value === '' ? null : $value,
                'updated_by'    => $userId,
                'updated_at'    => $now,
            ];

            if ($existing) {
                $this->db->table('copilot_settings')
                    ->where('id', (int) $existing->id)
                    ->update($payload);
            } else {
                $payload['setting_key'] = $key;
                $this->db->table('copilot_settings')->insert($payload);
            }
        }

        $this->db->transComplete();
    }
}
