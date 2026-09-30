<?php

namespace App\Services\Bpb;

use App\Models\UserSignatureProfileModel;
use App\Repositories\BpbRepository;
use App\Services\FileAccessService;
use Myth\Auth\Password;
use RuntimeException;

class ProfileSignatureService
{
    private UserSignatureProfileModel $model;
    private BpbFileService $files;
    private BpbRepository $repository;

    public function __construct()
    {
        $this->model = new UserSignatureProfileModel();
        $this->files = new BpbFileService();
        $this->repository = new BpbRepository();
    }

    public function get(int $userId): array
    {
        $row = $this->model->find($userId);
        return ['has_signature' => (bool) $row, 'updated_at' => $row['updated_at'] ?? null];
    }

    public function save(int $userId, string $password, string $dataUrl): array
    {
        $this->verifyPassword($userId, $password);
        $old = $this->model->find($userId);
        $path = $this->files->storeCanvas($dataUrl, "profile-signatures/{$userId}");
        $db = db_connect();
        $db->transBegin();
        try {
            $record = ['user_id' => $userId, 'signature_path' => $path, 'updated_at' => date('Y-m-d H:i:s')];
            $saved = $old
                ? $this->model->update($userId, $record)
                : $this->model->insert($record, false);
            $stored = $this->model->find($userId);
            if ($saved === false || ! $stored || ! hash_equals((string) $path, (string) $stored['signature_path']) || ! $db->transStatus()) {
                throw new RuntimeException('Gagal menyimpan tanda tangan profil.');
            }
            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();
            $this->files->cleanup([$path]);
            throw $e;
        }
        if ($old) {
            $this->files->cleanup([$old['signature_path']]);
        }
        return $this->get($userId);
    }

    public function delete(int $userId, string $password): void
    {
        $this->verifyPassword($userId, $password);
        $old = $this->model->find($userId);
        if ($old) {
            $this->model->delete($userId);
            $this->files->cleanup([$old['signature_path']]);
        }
    }

    public function absolutePath(int $userId): string
    {
        $row = $this->model->find($userId);
        if (! $row) {
            throw new RuntimeException('Tanda tangan profil belum tersedia.');
        }
        $path = (new FileAccessService())->privatePath($row['signature_path']);
        if (! is_file($path)) {
            throw new RuntimeException('File tanda tangan profil tidak ditemukan.');
        }
        return $path;
    }

    private function verifyPassword(int $userId, string $password): void
    {
        $user = $this->repository->userSnapshot($userId);
        if ($password === '' || ! Password::verify($password, (string) $user['password_hash'])) {
            throw new RuntimeException('Password saat ini tidak sesuai.');
        }
    }
}
