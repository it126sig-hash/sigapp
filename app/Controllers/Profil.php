<?php

namespace App\Controllers;

use App\Services\FileAccessService;
use Myth\Auth\Password;
use RuntimeException;

class Profil extends BaseController
{
    protected $db;
    protected FileAccessService $fileAccessService;

    public function __construct()
    {
        $this->db = db_connect();
        $this->fileAccessService = new FileAccessService();
    }

    public function index()
    {
        $profile = $this->currentUserRow();
        $photoUrl = $this->profilePhotoUrl($profile->profile_photo ?? null);

        $data['content'] = 'user/profil';
        $data['data'] = [
            'title' => 'Ubah Profil',
            'profile' => $profile,
            'photoUrl' => $photoUrl,
            'defaultPhotoUrl' => base_url('app-assets/images/portrait/small/avatar-s-11.jpg'),
        ];

        return view('template', $data);
    }

    public function update()
    {
        $profile = $this->currentUserRow();
        $userId = (int) $profile->id;
        $rules = [
            'name' => [
                'label' => 'Nama',
                'rules' => 'required|min_length[3]|max_length[120]',
                'errors' => [
                    'required' => '{field} harus diisi',
                    'min_length' => '{field} minimal 3 karakter',
                    'max_length' => '{field} maksimal 120 karakter',
                ],
            ],
            'password' => [
                'label' => 'Password',
                'rules' => 'permit_empty|min_length[4]|max_length[50]',
                'errors' => [
                    'min_length' => '{field} minimal 4 karakter',
                    'max_length' => '{field} maksimal 50 karakter',
                ],
            ],
            'password_confirm' => [
                'label' => 'Konfirmasi password',
                'rules' => 'matches[password]',
                'errors' => [
                    'matches' => '{field} tidak sama dengan password baru',
                ],
            ],
        ];

        $photo = $this->request->getFile('profile_photo');
        if ($photo && $photo->getError() !== UPLOAD_ERR_NO_FILE) {
            $rules['profile_photo'] = [
                'label' => 'Foto profil',
                'rules' => 'uploaded[profile_photo]'
                    . '|mime_in[profile_photo,image/jpg,image/jpeg,image/png,image/webp]'
                    . '|max_size[profile_photo,2048]'
                    . '|max_dims[profile_photo,3000,3000]',
                'errors' => [
                    'uploaded' => '{field} gagal diupload',
                    'mime_in' => '{field} harus berupa JPG, PNG, atau WEBP',
                    'max_size' => '{field} maksimal 2MB',
                    'max_dims' => 'Dimensi {field} maksimal 3000x3000 px',
                ],
            ];
        }

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $fields = [
            'name' => trim((string) $this->request->getPost('name')),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $password = (string) $this->request->getPost('password');
        if ($password !== '') {
            $fields['password_hash'] = Password::hash($password);
            $fields['reset_hash'] = null;
            $fields['reset_at'] = null;
            $fields['reset_expires'] = null;
        }

        if ($photo && $photo->isValid() && ! $photo->hasMoved()) {
            try {
                $fields['profile_photo'] = $this->fileAccessService->store($photo, 'uploads/profile/' . date('Ymd'));
            } catch (RuntimeException $e) {
                return redirect()->back()->withInput()->with('error', 'Foto profil gagal disimpan.');
            }
        }

        $this->db->table('users')->where('id', $userId)->update($fields);

        return redirect()->to(base_url('profil'))->with('message', 'Profil berhasil diperbaharui.');
    }

    private function currentUserRow(): object
    {
        $row = $this->db->table('users')->where('id', user_id())->get()->getRow();

        if (! $row) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('User tidak ditemukan');
        }

        return $row;
    }

    private function profilePhotoUrl(?string $path): string
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
