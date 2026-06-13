<?php

namespace App\Repositories;

use CodeIgniter\Model;

class TransaksiRepository extends Model
{
    protected $table = 'mkdt';
    protected $primaryKey = 'id_mkdt';
    protected $returnType = 'object';

    // catatan: kamu bisa set allowedFields kalau perlu insert/update.

    //ambil data transaksi dari toble mkdt dan konsumen
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
            'u_pb.username AS perintah_bangun_user',
            'u_ub.username AS edit_by_user',
            'list_bank.bank as nama_bank',
        ])
            ->join('konsumen', 'konsumen.id_konsumen = mkdt.id_konsumen')
            ->join('users u_pb', 'u_pb.id = mkdt.perintah_bangun', 'left') //user perintah bangun
            ->join('users u_ub', 'u_ub.id = mkdt.edit_by', 'left') //user edit by
            ->join('list_bank', 'list_bank.id = mkdt.id_bank', 'left')
            ->where('mkdt.id_mkdt', $idMkdt)
            ->first();
    }

    public function findNikUsage(string $nik, ?int $excludeMkdt = null, ?int $excludeKonsumen = null): array
    {
        $nik = trim($nik);
        if ($nik === '') {
            return [];
        }

        $builder = $this->db->table('mkdt')
            ->select('
                mkdt.id_mkdt,
                konsumen.id_konsumen,
                konsumen.nama_konsumen,
                konsumen.nik,
                kavling.id_kavling,
                kavling.no_kavling,
                jalan.nama_jalan,
                cluster.nama_cluster,
                proyek.nama_proyek
            ')
            ->join('konsumen', 'konsumen.id_konsumen = mkdt.id_konsumen')
            ->join('kavling', 'kavling.id_mkdt = mkdt.id_mkdt', 'left')
            ->join('jalan', 'jalan.id_jalan = kavling.id_jalan', 'left')
            ->join('cluster', 'cluster.id_cluster = jalan.id_cluster', 'left')
            ->join('proyek', 'proyek.id_proyek = cluster.id_proyek', 'left')
            ->where('konsumen.nik', $nik);

        if (!empty($excludeMkdt)) {
            $builder->where('mkdt.id_mkdt !=', $excludeMkdt);
        }

        if (!empty($excludeKonsumen)) {
            $builder->where('konsumen.id_konsumen !=', $excludeKonsumen);
        }

        return $builder
            ->orderBy('mkdt.updated_at', 'desc')
            ->limit(5)
            ->get()
            ->getResultArray();
    }

    public function getKonsumenByIdKavling($idKavling)
    {
        return $this->db->table('kavling')
            ->select('
            `proyek`.`nama_proyek`,
            `proyek`.`alamat_proyek`,
            `proyek`.`kelurahan`,
            `proyek`.`kecamatan`,
            `proyek`.`kota`,
            `proyek`.`provinsi`,
            `proyek`.`nama_pt`,
            `cluster`.`nama_cluster`,
            `jalan`.`nama_jalan`,
            `tipe`.`no_tipe_rumah`,
            `tipe`.`tipe_rumah`,
            tipe.lb,
            `kavling`.`no_kavling`,
            kavling.luas_tanah,
            mkdt.*,
            `konsumen`.`no_spptb`,
            `konsumen`.`nama_konsumen`,
            `konsumen`.`nik`,
            `konsumen`.`npwp`,
            `konsumen`.`file_npwp`,
            `konsumen`.`file_ktp`,
            `konsumen`.`hp_konsumen`,
            `konsumen`.`alamat_konsumen`,
            `konsumen`.`tel_instansi`,
            `konsumen`.`email_konsumen`,
            `konsumen`.`sales`,
            `konsumen`.`nama_instansi`,
            `konsumen`.`alamat_instansi`,
            `konsumen`.`tel_instansi`,
            `konsumen`.`email_instansi`,
            `konsumen`.`alamat_surat`,
            `konsumen`.`pekerjaan`,
            `konsumen`.`bidang_pekerjaan`,
            `konsumen`.`lama_bekerja`,
            `konsumen`.`status_pekerjaan_pasangan`,
            `konsumen`.`hp_pasangan`,
            `konsumen`.`nik_pasangan`,
            `konsumen`.`nama_pasangan`,
            `konsumen`.`instansi_pasangan`,
            
        ')
            ->join('jalan', 'jalan.id_jalan = kavling.id_jalan')
            ->join('cluster', 'cluster.id_cluster = jalan.id_cluster')
            ->join('proyek', 'cluster.id_proyek = proyek.id_proyek')
            ->join('tipe', 'tipe.id_tipe = kavling.id_tipe')
            ->join('mkdt', 'mkdt.id_mkdt = kavling.id_mkdt')
            ->join('konsumen', 'konsumen.id_konsumen = mkdt.id_konsumen', 'left')
            ->where('kavling.id_kavling', $idKavling)
            ->get()->getRow();
    }
}
