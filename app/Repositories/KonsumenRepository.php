<?php

namespace App\Repositories;

use CodeIgniter\Model;

class KonsumenRepository extends Model
{
    protected $table = 'konsumen';
    protected $primaryKey = 'id_keuangan';
    protected $returnType = 'object';

    public function getKonsumenTransaksi(int $idMkdt): ?object
    {
        return $this->select([
            'mkdt.*',

            'konsumen.file_ktp AS ktp_lok',
            'konsumen.file_npwp AS npwp_lok',
            'konsumen.file_data_diri AS data_diri_lok',

            'konsumen.no_spptb',
            'konsumen.nama_konsumen',
            'konsumen.nik AS nik_konsumen',
            'konsumen.alamat_konsumen',
            'konsumen.npwp AS npwp_konsumen',
            'konsumen.hp_konsumen',
            'konsumen.email_konsumen',

            'konsumen.nama_instansi',
            'konsumen.alamat_instansi',
            'konsumen.tel_instansi',
            'konsumen.email_instansi',
            'konsumen.alamat_surat',
            'konsumen.pekerjaan',
            'konsumen.lama_bekerja',
            'konsumen.bidang_pekerjaan',

            'konsumen.status_pernikahan',
            'konsumen.nama_pasangan',
            'konsumen.nik_pasangan',
            'konsumen.hp_pasangan',
            'konsumen.status_pekerjaan_pasangan',
            'konsumen.instansi_pasangan',

            'konsumen.sales',

            'konsumen.status_konsumen',
            'users.username AS perintah_bangun_user',
        ])
            ->join('mkdt', 'konsumen.id_konsumen = mkdt.id_konsumen')
            ->join('users', 'users.id = mkdt.edit_by', 'left')
            ->where('mkdt.id_mkdt', $idMkdt)
            ->first();
    }
}
