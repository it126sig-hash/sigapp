<?php

namespace App\Repositories\Copilot;

use CodeIgniter\Database\BaseConnection;

class MessageRepository
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? db_connect();
    }

    /**
     * Simpan satu pesan percakapan ke tabel `copilot_messages`.
     *
     * @param array<string, mixed>|null $metadata
     */
    public function insertMessage(
        int $userId,
        string $channel,
        string $role,
        string $content,
        ?int $telegramUpdateId = null,
        ?array $metadata = null
    ): int {
        if (! $this->db->tableExists('copilot_messages')) {
            return 0;
        }

        $metadataJson = null;
        if ($metadata !== null && $metadata !== []) {
            $encoded = json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $metadataJson = $encoded !== false ? $encoded : null;
        }

        $this->db->table('copilot_messages')->insert([
            'user_id'            => $userId,
            'channel'            => substr(trim($channel), 0, 20),
            'role'               => substr(trim($role), 0, 20),
            'content'            => $content,
            'telegram_update_id' => $telegramUpdateId,
            'metadata_json'      => $metadataJson,
            'created_at'         => date('Y-m-d H:i:s'),
        ]);

        return (int) $this->db->insertID();
    }

    /**
     * Ambil N pesan terbaru role `user`/`assistant`, dikembalikan berurutan kronologis (lama -> baru).
     *
     * @return array<int, array<string, mixed>>
     */
    public function getRecentHistory(int $userId, string $channel, int $limit = 10): array
    {
        if ($userId <= 0 || $limit <= 0 || ! $this->db->tableExists('copilot_messages')) {
            return [];
        }

        $rows = $this->db->table('copilot_messages')
            ->select('id, user_id, channel, role, content, telegram_update_id, metadata_json, created_at')
            ->where('user_id', $userId)
            ->where('channel', $channel)
            ->whereIn('role', ['user', 'assistant'])
            ->orderBy('id', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();

        $rows = array_reverse($rows);

        return array_map(static function (array $row): array {
            $metadata = null;
            if (! empty($row['metadata_json'])) {
                $decoded = json_decode((string) $row['metadata_json'], true);
                if (is_array($decoded)) {
                    $metadata = $decoded;
                }
            }

            return [
                'id'                 => (int) $row['id'],
                'user_id'            => (int) $row['user_id'],
                'channel'            => (string) $row['channel'],
                'role'               => (string) $row['role'],
                'content'            => (string) $row['content'],
                'telegram_update_id' => $row['telegram_update_id'] !== null ? (int) $row['telegram_update_id'] : null,
                'metadata'           => $metadata,
                'created_at'         => (string) $row['created_at'],
            ];
        }, $rows);
    }

    /**
     * Hitung pesan role `user` dalam N menit terakhir (untuk rate limiting lintas channel).
     */
    public function countRecentUserMessages(int $userId, int $windowMinutes = 10): int
    {
        if ($userId <= 0 || $windowMinutes <= 0 || ! $this->db->tableExists('copilot_messages')) {
            return 0;
        }

        $since = date('Y-m-d H:i:s', time() - ($windowMinutes * 60));

        return (int) $this->db->table('copilot_messages')
            ->where('user_id', $userId)
            ->where('role', 'user')
            ->where('created_at >=', $since)
            ->countAllResults();
    }

    /**
     * Cek apakah `telegram_update_id` sudah pernah diproses (deduplikasi webhook/polling).
     */
    public function hasTelegramUpdateId(int $updateId): bool
    {
        if ($updateId <= 0 || ! $this->db->tableExists('copilot_messages')) {
            return false;
        }

        return $this->db->table('copilot_messages')
            ->where('telegram_update_id', $updateId)
            ->countAllResults() > 0;
    }

    /**
     * Hapus riwayat pesan yang lebih tua dari N hari.
     */
    public function pruneOlderThanDays(int $days = 90): int
    {
        if ($days <= 0 || ! $this->db->tableExists('copilot_messages')) {
            return 0;
        }

        $cutoff = date('Y-m-d H:i:s', time() - ($days * 86400));

        $this->db->table('copilot_messages')
            ->where('created_at <', $cutoff)
            ->delete();

        return (int) $this->db->affectedRows();
    }
}
