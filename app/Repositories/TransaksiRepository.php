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
            'konsumen.kode_referal',
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
            'referrer.nama_konsumen as referred_by_nama',
            'referrer.kode_referal as referred_by_kode',
        ])
            ->join('konsumen', 'konsumen.id_konsumen = mkdt.id_konsumen')
            ->join('users u_pb', 'u_pb.id = mkdt.perintah_bangun', 'left') //user perintah bangun
            ->join('users u_ub', 'u_ub.id = mkdt.edit_by', 'left') //user edit by
            ->join('list_bank', 'list_bank.id = mkdt.id_bank', 'left')
            ->join('referrals', 'referrals.id_mkdt_referred = mkdt.id_mkdt', 'left')
            ->join('konsumen referrer', 'referrer.id_konsumen = referrals.id_konsumen_referrer', 'left')
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
        $kavling = $this->db->table('kavling')
            ->select('id_mkdt')
            ->where('id_kavling', $idKavling)
            ->get()->getRow();

        return $kavling && $kavling->id_mkdt
            ? $this->getSpptbData((int) $idKavling, (int) $kavling->id_mkdt)
            : null;
    }

    public function getSpptbData(int $idKavling, int $idMkdt): ?object
    {
        return $this->db->table('kavling')
            ->select('
            `proyek`.`nama_proyek`,
            `proyek`.`id_proyek`,
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
            hargajual.id_tipe AS tipe_pricelist,
            mkdt.*,
            `konsumen`.`no_spptb`,
            `konsumen`.`kode_referal`,
            referrer.nama_konsumen as referred_by_nama,
            referrer.kode_referal as referred_by_kode,
            `konsumen`.`nama_konsumen`,
            `konsumen`.`nik`,
            `konsumen`.`npwp`,
            `konsumen`.`file_npwp`,
            `konsumen`.`file_ktp`,
            `konsumen`.`file_data_diri`,
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
            ->join('mkdt', 'mkdt.id_kavling = kavling.id_kavling')
            ->join('konsumen', 'konsumen.id_konsumen = mkdt.id_konsumen', 'left')
            ->join('referrals', 'referrals.id_mkdt_referred = mkdt.id_mkdt', 'left')
            ->join('konsumen referrer', 'referrer.id_konsumen = referrals.id_konsumen_referrer', 'left')
            ->join('hargajual', 'hargajual.id = kavling.harga_akhir', 'left')
            ->where('kavling.id_kavling', $idKavling)
            ->where('mkdt.id_mkdt', $idMkdt)
            ->get()->getRow();
    }

    public function lockConsumerReplacementContext(int $idMkdt, int $idKavling): ?object
    {
        $sql = $this->db->table('mkdt')
            ->select('mkdt.*, kavling.id_mkdt AS kavling_id_mkdt, konsumen.no_spptb, konsumen.nama_konsumen, konsumen.status AS konsumen_status, konsumen.file_ktp, konsumen.file_npwp, konsumen.file_data_diri')
            ->join('kavling', 'kavling.id_kavling = mkdt.id_kavling')
            ->join('konsumen', 'konsumen.id_konsumen = mkdt.id_konsumen')
            ->where('mkdt.id_mkdt', $idMkdt)
            ->where('mkdt.id_kavling', $idKavling)
            ->getCompiledSelect();

        return $this->db->query($sql . ' FOR UPDATE')->getRow();
    }

    public function getLegacyReplacementSpptbData(int $idKavling, int $currentIdMkdt): array
    {
        $current = $this->db->table('mkdt')
            ->select('uniq_id')
            ->where('id_mkdt', $currentIdMkdt)
            ->get()->getRow();
        if (!$current || empty($current->uniq_id)) {
            return [];
        }

        $rows = $this->db->table('mkdt')
            ->select('id_mkdt')
            ->where('id_kavling', $idKavling)
            ->where('uniq_id', $current->uniq_id)
            ->where('is_ganti_nama', 'Ganti Nama')
            ->where('id_mkdt !=', $currentIdMkdt)
            ->orderBy('created_at', 'ASC')
            ->orderBy('id_mkdt', 'ASC')
            ->get()->getResult();

        $result = [];
        foreach ($rows as $row) {
            $data = $this->getSpptbData($idKavling, (int) $row->id_mkdt);
            if ($data) {
                $result[] = $data;
            }
        }
        return $result;
    }
}
