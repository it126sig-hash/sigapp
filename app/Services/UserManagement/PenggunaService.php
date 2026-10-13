<?php

namespace App\Services\UserManagement;

use App\Repositories\UserManagement\KaryawanRepository;
use App\Repositories\UserManagement\PenggunaRepository;
use CodeIgniter\Database\BaseConnection;
use Myth\Auth\Password;

class PenggunaService
{
    protected PenggunaRepository $penggunaRepo;
    protected KaryawanRepository $karyawanRepo;
    protected BaseConnection $db;
    protected $validation;

    public function __construct()
    {
        $this->penggunaRepo = new PenggunaRepository();
        $this->karyawanRepo = new KaryawanRepository();
        $this->db           = \Config\Database::connect();
        $this->validation   = \Config\Services::validation();
    }

    /**
     * Data untuk view user/setuser.
     */
    public function getSetuserData(): array
    {
        return [
            'karyawan'   => $this->karyawanRepo->getUnlinkedKaryawan(),
            'controller' => 'pengguna',
            'title'      => 'Data Pengguna',
        ];
    }

    /**
     * Mengambil dan memformat data untuk DataTables.
     */
    public function getDatatable(): array
    {
        $users = $this->penggunaRepo->getAll();
        $data  = [];

        foreach ($users as $key => $value) {
            $st = ((int) $value->active === 1) ? 1 : 0;

            $ops = '<div class="btn-group">';
            $ops .= '<button type="button" class="btn btn-sm btn-warning" onclick="manageDevices(' . (int) $value->id . ')" title="Kelola sesi perangkat"><i class="fa fa-laptop"></i></button>';
            $ops .= ' <button type="button" class="btn btn-sm btn-info" onclick="edit(' . (int) $value->id . ')"><i class="fa fa-edit"></i></button>';

            if ($st === 1) {
                $status = '<span class="badge badge-pill badge-light-primary mr-1">Aktif</span>';
                $ops .= ' <button type="button" class="btn btn-sm btn-danger" onclick="remove(' . (int) $value->id . ', ' . $st . ', \'' . esc($value->username, 'js') . '\')"><i class="fa fa-times"></i></button>';
            } else {
                $status = '<span class="badge badge-pill badge-light-warning mr-1">Tidak Aktif</span>';
                $ops .= ' <button type="button" class="btn btn-sm btn-success" onclick="remove(' . (int) $value->id . ', ' . $st . ', \'' . esc($value->username, 'js') . '\')"><i class="fa fa-check"></i></button>';
            }
            $ops .= '</div>';

            $data[$key] = [
                $value->id,
                $value->username,
                $value->nik,
                $value->nama_karyawan,
                $value->divisi,
                $value->level,
                $status,
                $ops,
            ];
        }

        return [
            'data'  => $data,
            'token' => csrf_hash(),
        ];
    }

    /**
     * Mengambil detail single user.
     */
    public function getOne(int $id): ?object
    {
        $user = $this->penggunaRepo->findById($id);
        if ($user) {
            $user->token = csrf_hash();
        }
        return $user;
    }

    /**
     * Tambah user baru dengan transaksi atomik multi-tabel.
     */
    public function addUser(array $postData): array
    {
        $nik = trim((string) ($postData['nik'] ?? ''));
        if ($nik === '') {
            return [
                'success'  => false,
                'messages' => ['nik' => 'Pilih karyawan terlebih dahulu'],
                'token'    => csrf_hash(),
            ];
        }

        $karyawan = $this->karyawanRepo->findByNik($nik);
        if (!$karyawan) {
            return [
                'success'  => false,
                'messages' => 'Data karyawan tidak ditemukan',
                'token'    => csrf_hash(),
            ];
        }

        $rules = [
            'username' => [
                'rules'  => 'required|min_length[4]|max_length[20]|is_unique[users.username]',
                'errors' => [
                    'required'  => 'Username harus diisi',
                    'min_length'=> 'Username minimal 4 karakter',
                    'max_length'=> 'Username maksimal 20 karakter',
                    'is_unique' => 'Username sudah digunakan sebelumnya',
                ],
            ],
            'email' => [
                'rules'  => 'permit_empty|valid_email|is_unique[users.email]',
                'errors' => [
                    'valid_email' => 'Format email tidak valid',
                    'is_unique'   => 'Email sudah digunakan sebelumnya',
                ],
            ],
            'password' => [
                'rules'  => 'required|min_length[4]|max_length[50]',
                'errors' => [
                    'required'  => 'Password harus diisi',
                    'min_length'=> 'Password minimal 4 karakter',
                    'max_length'=> 'Password maksimal 50 karakter',
                ],
            ],
        ];

        $fields = [
            'username'   => trim((string) ($postData['username'] ?? '')),
            'email'      => trim((string) ($postData['email'] ?? '')),
            'password'   => (string) ($postData['password'] ?? ''),
            'name'       => $karyawan->nama_karyawan ?? '',
            'active'     => (int) ($postData['active'] ?? 1),
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $this->validation->setRules($rules);
        if (!$this->validation->run($fields)) {
            return [
                'success'  => false,
                'messages' => $this->validation->getErrors(),
                'token'    => csrf_hash(),
            ];
        }

        $fields['password_hash'] = Password::hash($fields['password']);
        unset($fields['password']);

        // Transaksi Atomik Multi-Tabel
        $this->db->transStart();
        try {
            $userId = $this->penggunaRepo->insertUser($fields);
            $this->karyawanRepo->linkUser($nik, $userId);

            if (!empty($karyawan->id_divisi)) {
                $this->penggunaRepo->syncGroup($userId, (int) $karyawan->id_divisi);
            }
            if (!empty($karyawan->id_level)) {
                $this->penggunaRepo->syncPermission($userId, (int) $karyawan->id_level);
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                return [
                    'success'  => false,
                    'messages' => 'Gagal menyimpan data pengguna (transaksi database gagal)',
                    'token'    => csrf_hash(),
                ];
            }

            return [
                'success'  => true,
                'messages' => 'Data pengguna berhasil ditambahkan',
                'token'    => csrf_hash(),
            ];
        } catch (\Throwable $e) {
            $this->db->transRollback();
            return [
                'success'  => false,
                'messages' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'token'    => csrf_hash(),
            ];
        }
    }

    /**
     * Edit user dengan transaksi atomik multi-tabel.
     */
    public function editUser(array $postData): array
    {
        $id = (int) ($postData['id'] ?? 0);
        if ($id <= 0) {
            return [
                'success'  => false,
                'messages' => 'ID pengguna tidak valid',
                'token'    => csrf_hash(),
            ];
        }

        $currentUser = $this->penggunaRepo->findRawUser($id);
        if (!$currentUser) {
            return [
                'success'  => false,
                'messages' => 'Pengguna tidak ditemukan',
                'token'    => csrf_hash(),
            ];
        }

        $username = trim((string) ($postData['username'] ?? ''));
        $email    = trim((string) ($postData['email'] ?? ''));
        $password = (string) ($postData['password'] ?? '');

        $fields = [
            'active'     => (int) ($postData['active'] ?? 1),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $rules = [
            'id' => ['rules' => 'required|integer'],
        ];

        if ($username !== '' && $username !== $currentUser->username) {
            $fields['username'] = $username;
            $rules['username']  = [
                'rules'  => "required|min_length[4]|max_length[20]|is_unique[users.username,id,{$id}]",
                'errors' => [
                    'required'  => 'Username harus diisi',
                    'min_length'=> 'Username minimal 4 karakter',
                    'max_length'=> 'Username maksimal 20 karakter',
                    'is_unique' => 'Username sudah digunakan sebelumnya',
                ],
            ];
        }

        if ($email !== '' && $email !== $currentUser->email) {
            $fields['email'] = $email;
            $rules['email']  = [
                'rules'  => "required|valid_email|is_unique[users.email,id,{$id}]",
                'errors' => [
                    'required'   => 'Email harus diisi',
                    'valid_email'=> 'Format email tidak valid',
                    'is_unique'  => 'Email sudah digunakan sebelumnya',
                ],
            ];
        }

        if ($password !== '') {
            $fields['password'] = $password;
            $rules['password']  = [
                'rules'  => 'min_length[4]|max_length[50]',
                'errors' => [
                    'min_length'=> 'Password minimal 4 karakter',
                    'max_length'=> 'Password maksimal 50 karakter',
                ],
            ];
        }

        $dataToValidate = array_merge(['id' => $id], $fields);
        $this->validation->setRules($rules);
        if (!$this->validation->run($dataToValidate)) {
            return [
                'success'  => false,
                'messages' => $this->validation->getErrors(),
                'token'    => csrf_hash(),
            ];
        }

        if ($password !== '') {
            $fields['password_hash'] = Password::hash($password);
            unset($fields['password']);
        }

        $nik = trim((string) ($postData['nik'] ?? ''));
        $karyawan = $nik !== '' ? $this->karyawanRepo->findByNik($nik) : null;
        if ($karyawan && !empty($karyawan->nama_karyawan)) {
            $fields['name'] = $karyawan->nama_karyawan;
        }

        $this->db->transStart();
        try {
            $this->penggunaRepo->updateUser($id, $fields);

            if ($karyawan) {
                if (!empty($karyawan->id_divisi)) {
                    $this->penggunaRepo->syncGroup($id, (int) $karyawan->id_divisi);
                }
                if (!empty($karyawan->id_level)) {
                    $this->penggunaRepo->syncPermission($id, (int) $karyawan->id_level);
                }
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                return [
                    'success'  => false,
                    'messages' => 'Gagal memperbarui pengguna (transaksi database gagal)',
                    'token'    => csrf_hash(),
                ];
            }

            return [
                'success'  => true,
                'messages' => 'Data pengguna berhasil diperbarui',
                'token'    => csrf_hash(),
            ];
        } catch (\Throwable $e) {
            $this->db->transRollback();
            return [
                'success'  => false,
                'messages' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'token'    => csrf_hash(),
            ];
        }
    }

    /**
     * Mengubah status aktif/nonaktif user.
     */
    public function toggleStatus(int $id, int $currentStatus): array
    {
        $newStatus = ($currentStatus === 1) ? 0 : 1;
        $updated   = $this->penggunaRepo->toggleActive($id, $newStatus);

        return [
            'success'  => $updated,
            'messages' => $updated ? 'Status pengguna berhasil diubah' : 'Gagal mengubah status pengguna',
            'token'    => csrf_hash(),
        ];
    }
}
