<?php

namespace App\Services\UserManagement;

use App\Repositories\UserManagement\KaryawanRepository;
use App\Repositories\UserManagement\PenggunaRepository;
use CodeIgniter\Database\BaseConnection;

class KaryawanService
{
    protected KaryawanRepository $karyawanRepo;
    protected PenggunaRepository $penggunaRepo;
    protected BaseConnection $db;
    protected $validation;

    public function __construct()
    {
        $this->karyawanRepo = new KaryawanRepository();
        $this->penggunaRepo = new PenggunaRepository();
        $this->db           = \Config\Database::connect();
        $this->validation   = \Config\Services::validation();
    }

    /**
     * Data untuk view master/karyawan.
     */
    public function getIndexData(): array
    {
        return [
            'divisi'     => $this->karyawanRepo->getDivisiList(),
            'level'      => $this->karyawanRepo->getLevelList(),
            'controller' => 'karyawan',
            'title'      => 'Karyawan',
        ];
    }

    /**
     * Mengambil dan memformat data untuk DataTables karyawan.
     */
    public function getDatatable(): array
    {
        $result = $this->karyawanRepo->getAll();
        $data   = [];

        foreach ($result as $key => $value) {
            $st = ((int) $value->status === 1) ? 1 : 0;

            $ops = '<div class="btn-group">';
            $ops .= ' <button type="button" class="btn btn-sm btn-info" onclick="edit(\'' . esc($value->nik, 'js') . '\')"><i class="fa fa-edit"></i></button>';

            if ($st === 1) {
                $status = '<span class="badge badge-pill badge-light-primary mr-1">Aktif</span>';
                $ops .= ' <button type="button" class="btn btn-sm btn-danger" onclick="remove(\'' . esc($value->nik, 'js') . '\', ' . $st . ')"><i class="fa fa-times"></i></button>';
            } else {
                $status = '<span class="badge badge-pill badge-light-warning mr-1">Tidak Aktif</span>';
                $ops .= ' <button type="button" class="btn btn-sm btn-success" onclick="remove(\'' . esc($value->nik, 'js') . '\', ' . $st . ')"><i class="fa fa-check"></i></button>';
            }
            $ops .= '</div>';

            $data[$key] = [
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
     * Mengambil data single karyawan by NIK.
     */
    public function getOne(string $nik): ?object
    {
        $karyawan = $this->karyawanRepo->findByNik($nik);
        if ($karyawan) {
            $karyawan->token = csrf_hash();
        }
        return $karyawan;
    }

    /**
     * Tambah karyawan baru.
     */
    public function addKaryawan(array $postData): array
    {
        $fields = [
            'nik'           => trim((string) ($postData['nik'] ?? '')),
            'nama_karyawan' => trim((string) ($postData['nama_karyawan'] ?? '')),
            'id_divisi'     => $postData['id_divisi'] ?? null,
            'id_level'      => $postData['id_level'] ?? null,
            'status'        => $postData['status'] ?? 1,
        ];

        $this->validation->setRules([
            'nik'           => ['label' => 'NIK', 'rules' => 'required|max_length[255]'],
            'nama_karyawan' => ['label' => 'Nama karyawan', 'rules' => 'required|max_length[255]'],
            'id_divisi'     => ['label' => 'Divisi', 'rules' => 'permit_empty|max_length[255]'],
            'id_level'      => ['label' => 'Level', 'rules' => 'permit_empty|max_length[255]'],
            'status'        => ['label' => 'Status', 'rules' => 'permit_empty|max_length[255]'],
        ]);

        if (!$this->validation->run($fields)) {
            return [
                'success'  => false,
                'messages' => $this->validation->getErrors(),
                'token'    => csrf_hash(),
            ];
        }

        $existing = $this->karyawanRepo->findByNik($fields['nik']);
        if ($existing) {
            return [
                'success'  => false,
                'messages' => ['nik' => 'NIK sudah digunakan'],
                'token'    => csrf_hash(),
            ];
        }

        $inserted = $this->karyawanRepo->insert($fields);

        return [
            'success'  => $inserted,
            'messages' => $inserted ? 'Data karyawan berhasil ditambahkan' : 'Gagal menambahkan data karyawan',
            'token'    => csrf_hash(),
        ];
    }

    /**
     * Edit karyawan dengan sinkronisasi atomik ke akun user (jika tertaut).
     */
    public function editKaryawan(array $postData): array
    {
        $nik = trim((string) ($postData['nik'] ?? ''));
        if ($nik === '') {
            return [
                'success'  => false,
                'messages' => 'NIK tidak valid',
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

        $fields = [
            'nik'           => $nik,
            'nama_karyawan' => trim((string) ($postData['nama_karyawan'] ?? '')),
            'id_divisi'     => $postData['id_divisi'] ?? null,
            'id_level'      => $postData['id_level'] ?? null,
            'status'        => $postData['status'] ?? 1,
        ];

        $this->validation->setRules([
            'nik'           => ['label' => 'NIK', 'rules' => 'required|max_length[255]'],
            'nama_karyawan' => ['label' => 'Nama karyawan', 'rules' => 'required|max_length[255]'],
            'id_divisi'     => ['label' => 'Divisi', 'rules' => 'permit_empty|max_length[255]'],
            'id_level'      => ['label' => 'Level', 'rules' => 'permit_empty|max_length[255]'],
            'status'        => ['label' => 'Status', 'rules' => 'permit_empty|max_length[255]'],
        ]);

        if (!$this->validation->run($fields)) {
            return [
                'success'  => false,
                'messages' => $this->validation->getErrors(),
                'token'    => csrf_hash(),
            ];
        }

        $this->db->transStart();
        try {
            $this->karyawanRepo->update($nik, $fields);

            // Sinkronisasi ke akun user jika karyawan sudah memiliki id_user
            if (!empty($karyawan->id_user)) {
                $userId = (int) $karyawan->id_user;
                $this->penggunaRepo->updateUser($userId, [
                    'name'       => $fields['nama_karyawan'],
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

                if (!empty($fields['id_divisi'])) {
                    $this->penggunaRepo->syncGroup($userId, (int) $fields['id_divisi']);
                }
                if (!empty($fields['id_level'])) {
                    $this->penggunaRepo->syncPermission($userId, (int) $fields['id_level']);
                }
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                return [
                    'success'  => false,
                    'messages' => 'Gagal memperbarui karyawan (transaksi database gagal)',
                    'token'    => csrf_hash(),
                ];
            }

            return [
                'success'  => true,
                'messages' => 'Data karyawan berhasil diperbarui',
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
     * Toggle status aktif/nonaktif karyawan.
     */
    public function toggleStatus(string $nik, int $currentStatus): array
    {
        $newStatus = ($currentStatus === 1) ? 0 : 1;
        $updated   = $this->karyawanRepo->toggleStatus($nik, $newStatus);

        return [
            'success'  => $updated,
            'messages' => $updated ? 'Status karyawan berhasil diubah' : 'Gagal mengubah status karyawan',
            'token'    => csrf_hash(),
        ];
    }
}
