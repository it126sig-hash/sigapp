<?php

namespace App\Repositories\Copilot;

use CodeIgniter\Database\BaseConnection;

class TelegramLinkRepository
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? db_connect();
    }

    /**
     * Ambil baris tautan Telegram berdasarkan `user_id` SIGAPP.
     *
     * @return array<string, mixed>|null
     */
    public function findByUserId(int $userId): ?array
    {
        if ($userId <= 0 || ! $this->db->tableExists('copilot_telegram_links')) {
            return null;
        }

        $builder = $this->db->table('copilot_telegram_links l')
            ->select('l.id, l.user_id, l.telegram_user_id, l.chat_id, l.telegram_username, l.code_hash, l.code_expires_at, l.verified_at, l.created_at, l.updated_at');

        if ($this->db->tableExists('users')) {
            $builder->select('u.username AS sigapp_username, u.name AS sigapp_name, u.active AS sigapp_active, u.deleted_at AS sigapp_deleted_at')
                ->join('users u', 'u.id = l.user_id', 'left');
        }

        $row = $builder->where('l.user_id', $userId)
            ->get()
            ->getRowArray();

        if (! is_array($row)) {
            return null;
        }

        return $this->normalizeRow($row);
    }

    /**
     * Ambil tautan terverifikasi berdasarkan `telegram_user_id`.
     * Jika user di tabel `users` ditemukan namun nonaktif/soft-deleted, kembalikan null.
     *
     * @return array<string, mixed>|null
     */
    public function findVerifiedByTelegramUserId(int $telegramUserId): ?array
    {
        if ($telegramUserId <= 0 || ! $this->db->tableExists('copilot_telegram_links')) {
            return null;
        }

        $builder = $this->db->table('copilot_telegram_links l')
            ->select('l.id, l.user_id, l.telegram_user_id, l.chat_id, l.telegram_username, l.code_hash, l.code_expires_at, l.verified_at, l.created_at, l.updated_at');

        $hasUsersTable = $this->db->tableExists('users');
        if ($hasUsersTable) {
            $builder->select('u.username AS sigapp_username, u.name AS sigapp_name, u.active AS sigapp_active, u.deleted_at AS sigapp_deleted_at')
                ->join('users u', 'u.id = l.user_id', 'left');
        }

        $row = $builder->where('l.telegram_user_id', $telegramUserId)
            ->where('l.verified_at IS NOT NULL', null, false)
            ->get()
            ->getRowArray();

        if (! is_array($row)) {
            return null;
        }

        if ($hasUsersTable && array_key_exists('sigapp_active', $row) && $row['sigapp_active'] !== null) {
            if ((int) $row['sigapp_active'] !== 1 || ! empty($row['sigapp_deleted_at'])) {
                return null;
            }
        }

        return $this->normalizeRow($row);
    }

    /**
     * Simpan atau perbarui kode pairing (upsert berdasarkan `user_id`).
     */
    public function savePairingCode(int $userId, string $codeHash, string $expiresAt): void
    {
        if ($userId <= 0 || ! $this->db->tableExists('copilot_telegram_links')) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $existing = $this->db->table('copilot_telegram_links')
            ->select('id')
            ->where('user_id', $userId)
            ->get()
            ->getRow();

        if ($existing) {
            $this->db->table('copilot_telegram_links')
                ->where('id', (int) $existing->id)
                ->update([
                    'code_hash'       => $codeHash,
                    'code_expires_at' => $expiresAt,
                    'updated_at'      => $now,
                ]);

            return;
        }

        $this->db->table('copilot_telegram_links')->insert([
            'user_id'         => $userId,
            'code_hash'       => $codeHash,
            'code_expires_at' => $expiresAt,
            'created_at'      => $now,
            'updated_at'      => $now,
        ]);
    }

    /**
     * Verifikasi hash kode pairing dan tautkan akun Telegram dalam transaksi DB.
     * Jika `telegram_user_id` sebelumnya tertaut ke user lain, tautan lama dilepas terlebih dahulu
     * agar tidak melanggar unique constraint `telegram_user_id`.
     *
     * @return array<string, mixed>|null
     */
    public function verifyAndBindByCodeHash(
        string $codeHash,
        int $telegramUserId,
        int $chatId,
        ?string $telegramUsername,
        string $now
    ): ?array {
        if ($codeHash === '' || $telegramUserId <= 0 || $chatId === 0 || ! $this->db->tableExists('copilot_telegram_links')) {
            return null;
        }

        $this->db->transStart();

        try {
            $row = $this->db->table('copilot_telegram_links')
                ->where('code_hash', $codeHash)
                ->where('code_expires_at >=', $now)
                ->get()
                ->getRowArray();

            if (! is_array($row)) {
                $this->db->transComplete();
                return null;
            }

            $targetUserId = (int) $row['user_id'];

            // Lepaskan `telegram_user_id` dari baris lain jika pernah tertaut ke akun berbeda
            $this->db->table('copilot_telegram_links')
                ->where('telegram_user_id', $telegramUserId)
                ->where('user_id !=', $targetUserId)
                ->update([
                    'telegram_user_id'  => null,
                    'chat_id'           => null,
                    'telegram_username' => null,
                    'verified_at'       => null,
                    'updated_at'        => $now,
                ]);

            $cleanUsername = null;
            if ($telegramUsername !== null && trim($telegramUsername) !== '') {
                $cleanUsername = substr(trim($telegramUsername), 0, 100);
            }

            $this->db->table('copilot_telegram_links')
                ->where('id', (int) $row['id'])
                ->update([
                    'telegram_user_id'  => $telegramUserId,
                    'chat_id'           => $chatId,
                    'telegram_username' => $cleanUsername,
                    'verified_at'       => $now,
                    'code_hash'         => null,
                    'code_expires_at'   => null,
                    'updated_at'        => $now,
                ]);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                return null;
            }

            return $this->findByUserId($targetUserId);
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }
    }

    /**
     * Putuskan tautan Telegram untuk `user_id` tertentu.
     */
    public function unlinkByUserId(int $userId): bool
    {
        if ($userId <= 0 || ! $this->db->tableExists('copilot_telegram_links')) {
            return false;
        }

        $existing = $this->db->table('copilot_telegram_links')
            ->select('id')
            ->where('user_id', $userId)
            ->get()
            ->getRow();

        if (! $existing) {
            return false;
        }

        $this->db->table('copilot_telegram_links')
            ->where('user_id', $userId)
            ->update([
                'telegram_user_id'  => null,
                'chat_id'           => null,
                'telegram_username' => null,
                'verified_at'       => null,
                'code_hash'         => null,
                'code_expires_at'   => null,
                'updated_at'        => date('Y-m-d H:i:s'),
            ]);

        return true;
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function normalizeRow(array $row): array
    {
        return [
            'id'                => (int) $row['id'],
            'user_id'           => (int) $row['user_id'],
            'telegram_user_id'  => $row['telegram_user_id'] !== null ? (int) $row['telegram_user_id'] : null,
            'chat_id'           => $row['chat_id'] !== null ? (int) $row['chat_id'] : null,
            'telegram_username' => ($row['telegram_username'] !== null && $row['telegram_username'] !== '') ? (string) $row['telegram_username'] : null,
            'code_hash'         => $row['code_hash'] !== null ? (string) $row['code_hash'] : null,
            'code_expires_at'   => $row['code_expires_at'] !== null ? (string) $row['code_expires_at'] : null,
            'verified_at'       => $row['verified_at'] !== null ? (string) $row['verified_at'] : null,
            'created_at'        => $row['created_at'] !== null ? (string) $row['created_at'] : null,
            'updated_at'        => $row['updated_at'] !== null ? (string) $row['updated_at'] : null,
            'sigapp_username'   => isset($row['sigapp_username']) && $row['sigapp_username'] !== '' ? (string) $row['sigapp_username'] : null,
            'sigapp_name'       => isset($row['sigapp_name']) && $row['sigapp_name'] !== '' ? (string) $row['sigapp_name'] : null,
        ];
    }
}
