<?php

namespace App\Services\UserManagement;

use App\Repositories\UserManagement\ProfilRepository;
use App\Services\FileAccessService;
use App\Services\GoogleCalendarService;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\HTTP\Files\UploadedFile;
use Myth\Auth\Password;
use RuntimeException;

class ProfilService
{
    protected ProfilRepository $profilRepo;
    protected FileAccessService $fileAccessService;
    protected GoogleCalendarService $googleCalendarService;
    protected BaseConnection $db;
    protected $validation;

    public function __construct()
    {
        $this->profilRepo            = new ProfilRepository();
        $this->fileAccessService     = new FileAccessService();
        $this->googleCalendarService = new GoogleCalendarService();
        $this->db                    = \Config\Database::connect();
        $this->validation            = \Config\Services::validation();
    }

    /**
     * Mengambil seluruh data yang dibutuhkan untuk view profil pengguna.
     */
    public function getIndexData(int $userId): array
    {
        $profile = $this->profilRepo->getUserRow($userId);
        if (!$profile) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('User tidak ditemukan');
        }

        $photoUrl   = $this->profilePhotoUrl($profile->profile_photo ?? null);
        $department = $this->profilRepo->getDepartmentName($userId);

        return [
            'title'                => 'Ubah Profil',
            'profile'              => $profile,
            'department'           => $department,
            'photoUrl'             => $photoUrl,
            'defaultPhotoUrl'      => base_url('app-assets/images/portrait/small/avatar-s-11.jpg'),
            'googleCalendarStatus' => $this->googleCalendarService->getStatus($userId),
        ];
    }

    /**
     * Memproses update data profil pengguna dengan transaksi atomik dan kompresi foto.
     */
    public function updateProfile(int $userId, array $postData, ?UploadedFile $photo = null): array
    {
        $profile = $this->profilRepo->getUserRow($userId);
        if (!$profile) {
            return [
                'success'  => false,
                'messages' => ['user' => 'User tidak ditemukan'],
            ];
        }

        $rules = [
            'name' => [
                'label'  => 'Nama',
                'rules'  => 'required|min_length[3]|max_length[120]',
                'errors' => [
                    'required'   => '{field} harus diisi',
                    'min_length' => '{field} minimal 3 karakter',
                    'max_length' => '{field} maksimal 120 karakter',
                ],
            ],
            'email' => [
                'label'  => 'Email',
                'rules'  => "required|valid_email|is_unique[users.email,id,{$userId}]",
                'errors' => [
                    'required'   => '{field} harus diisi',
                    'valid_email'=> '{field} tidak valid',
                    'is_unique'  => '{field} sudah terdaftar',
                ],
            ],
            'username' => [
                'label'  => 'Username',
                'rules'  => "required|alpha_numeric_punct|min_length[3]|max_length[30]|is_unique[users.username,id,{$userId}]",
                'errors' => [
                    'required'           => '{field} harus diisi',
                    'alpha_numeric_punct'=> '{field} memuat karakter tidak valid',
                    'min_length'         => '{field} minimal 3 karakter',
                    'max_length'         => '{field} maksimal 30 karakter',
                    'is_unique'          => '{field} sudah terdaftar',
                ],
            ],
            'password' => [
                'label'  => 'Password',
                'rules'  => 'permit_empty|min_length[4]|max_length[50]',
                'errors' => [
                    'min_length' => '{field} minimal 4 karakter',
                    'max_length' => '{field} maksimal 50 karakter',
                ],
            ],
            'password_confirm' => [
                'label'  => 'Konfirmasi password',
                'rules'  => 'matches[password]',
                'errors' => [
                    'matches' => '{field} tidak sama dengan password baru',
                ],
            ],
        ];

        if ($photo && $photo->getError() !== UPLOAD_ERR_NO_FILE) {
            $rules['profile_photo'] = [
                'label'  => 'Foto profil',
                'rules'  => 'uploaded[profile_photo]'
                    . '|mime_in[profile_photo,image/jpg,image/jpeg,image/png,image/webp]'
                    . '|max_size[profile_photo,2048]'
                    . '|max_dims[profile_photo,3000,3000]',
                'errors' => [
                    'uploaded' => '{field} gagal diupload',
                    'mime_in'  => '{field} harus berupa JPG, PNG, atau WEBP',
                    'max_size' => '{field} maksimal 2MB',
                    'max_dims' => 'Dimensi {field} maksimal 3000x3000 px',
                ],
            ];
        }

        $this->validation->setRules($rules);
        $dataToValidate = $postData;
        if ($photo) {
            $dataToValidate['profile_photo'] = $photo;
        }

        if (!$this->validation->run($dataToValidate)) {
            return [
                'success'  => false,
                'messages' => $this->validation->getErrors(),
            ];
        }

        $fields = [
            'name'                => trim((string) ($postData['name'] ?? '')),
            'username'            => trim((string) ($postData['username'] ?? '')),
            'email'               => trim((string) ($postData['email'] ?? '')),
            'email_notif_enabled' => !empty($postData['email_notif_enabled']) ? 1 : 0,
            'updated_at'          => date('Y-m-d H:i:s'),
        ];

        $password = (string) ($postData['password'] ?? '');
        if ($password !== '') {
            $fields['password_hash'] = Password::hash($password);
            $fields['reset_hash']    = null;
            $fields['reset_at']      = null;
            $fields['reset_expires'] = null;
        }

        if ($photo && $photo->isValid() && !$photo->hasMoved()) {
            try {
                $storedPath = $this->fileAccessService->store($photo, 'uploads/profile/' . date('Ymd'));
                $fields['profile_photo'] = $storedPath;

                $absolutePath = $this->fileAccessService->privatePath($storedPath);
                if (is_file($absolutePath)) {
                    try {
                        $image = \Config\Services::image()->withFile($absolutePath);
                        $info  = $image->getFile()->getProperties(true);
                        $width = $info['width'] ?? 0;
                        $height= $info['height'] ?? 0;

                        if ($width > 400 || $height > 400) {
                            $image->resize(400, 400, true, 'auto');
                        }
                        $image->save($absolutePath, 85);
                    } catch (\Throwable $e) {
                        log_message('warning', 'Gagal kompres foto profil: ' . $e->getMessage());
                    }
                }
            } catch (RuntimeException $e) {
                return [
                    'success'  => false,
                    'messages' => ['profile_photo' => 'Foto profil gagal disimpan: ' . $e->getMessage()],
                ];
            }
        }

        $this->db->transStart();
        try {
            $this->profilRepo->updateUser($userId, $fields);
            $this->profilRepo->syncKaryawanName($userId, $fields['name']);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                return [
                    'success'  => false,
                    'messages' => ['db' => 'Gagal memperbarui profil pengguna (transaksi database gagal)'],
                ];
            }

            return [
                'success'  => true,
                'messages' => 'Profil berhasil diperbaharui.',
            ];
        } catch (\Throwable $e) {
            $this->db->transRollback();
            return [
                'success'  => false,
                'messages' => ['error' => 'Terjadi kesalahan: ' . $e->getMessage()],
            ];
        }
    }

    /**
     * Resolusi URL foto profil pengguna.
     */
    public function profilePhotoUrl(?string $path): string
    {
        if (empty($path)) {
            return base_url('app-assets/images/portrait/small/avatar-s-11.jpg');
        }

        try {
            return $this->fileAccessService->pathUrl('profile_photo', $path);
        } catch (RuntimeException) {
            return base_url('app-assets/images/portrait/small/avatar-s-11.jpg');
        }
    }
}
