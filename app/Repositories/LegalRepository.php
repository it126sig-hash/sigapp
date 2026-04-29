<?php

namespace App\Repositories;

use App\Models\LegalModel;
use App\Models\KavlingModel;

class LegalRepository
{
    protected $db;
    protected $legalModel;
    protected $kavlingModel;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->legalModel = new LegalModel();
        $this->kavlingModel = new KavlingModel();
    }

    public function getLegalDataById($id_legal)
    {
        if (!$id_legal) return null;
        return $this->legalModel->where('id_legal', $id_legal)->first();
    }

    public function getKavlingLegalTaxes($id_kavling)
    {
        return $this->db->table('kavling')
            ->select('
                konsumen.nama_konsumen,
                mkdt.harga_bphtb,
                pajak.pph42_id_billing,
                pajak.pph42_ntpn,
                pajak.pph42_nilai,
                pajak.pph42_tgl_bayar
            ')
            ->join('mkdt', 'mkdt.id_mkdt = kavling.id_mkdt', 'left')
            ->join('pajak', 'pajak.id_mkdt = mkdt.id_mkdt', 'left')
            ->join('konsumen', 'konsumen.id_konsumen = mkdt.id_konsumen', 'left')
            ->where('kavling.id_kavling', $id_kavling)
            ->get()->getRow();
    }

    public function getFileDocById($id)
    {
        return $this->db->table('file_upload')
            ->select('lokasi')
            ->where('id', $id)
            ->get()->getRow();
    }

    public function deleteFileDoc($id)
    {
        return $this->db->table('file_upload')->where('id', $id)->delete();
    }

    public function getFileDocsByKavling($id_kavling, $id_group = 5)
    {
        return $this->db->table('file_upload')
            ->select('
                file_upload.*,
                u.username as uadd_by
            ')
            ->join("users as u", "u.id = file_upload.upload_by", "left")
            ->where('id_kavling', $id_kavling)
            ->where('id_group', $id_group)
            ->get()->getResult();
    }

    public function insertFileDoc(array $data)
    {
        if ($this->db->table('file_upload')->insert($data)) {
            return $this->db->insertID();
        }
        return false;
    }

    public function updateOthers($id_kavling, array $data)
    {
        return $this->db->table('others')->where('id', $id_kavling)->update($data);
    }

    public function getDatatableLegalitasData($var)
    {
        $colum = ['nama_konsumen', 'nama_jalan', 'no_kavling'];
        $condition = [];

        $query = $this->baseDatatableQuery();

        if (!empty($var['id_jalan'])) {
            $condition["jalan.id_jalan"] = $var['id_jalan'];
        } elseif (!empty($var['id_cluster'])) {
            $condition["cluster.id_cluster"] = $var['id_cluster'];
        } elseif (!empty($var['id_proyek'])) {
            $condition["proyek.id_proyek"] = $var['id_proyek'];
        }

        $query = $this->applyIfWhere($var, $colum, $condition, $query);

        if (isset($var['start']) && isset($var['length'])) {
            $query->offset($var['start'])->limit($var['length']);
        }

        return $query->get()->getResult();
    }

    public function getDatatableLegalitasFilteredCount($var)
    {
        $colum = ['nama_konsumen', 'nama_jalan', 'no_kavling'];
        $condition = [];

        $query = $this->baseDatatableQuery();

        if (!empty($var['id_jalan'])) {
            $condition["jalan.id_jalan"] = $var['id_jalan'];
        } elseif (!empty($var['id_cluster'])) {
            $condition["cluster.id_cluster"] = $var['id_cluster'];
        } elseif (!empty($var['id_proyek'])) {
            $condition["proyek.id_proyek"] = $var['id_proyek'];
        }

         $query = $this->applyIfWhere($var, $colum, $condition, $query);
         return $query->countAllResults();
    }

    public function getDatatableLegalitasTotalCount()
    {
        $condition_total = [
            'kavling.id_legal !=' => null
        ];
        return $this->db->table("kavling")
            ->join('legal', "kavling.id_legal = legal.id_legal")
            ->where($condition_total)
            ->countAllResults();
    }

    protected function baseDatatableQuery()
    {
        return $this->db->table('kavling')
            ->select('
             legal.*,
             kavling.no_kavling,
             jalan.id_jalan,
             jalan.nama_jalan,
             cluster.id_cluster,
             cluster.nama_cluster,
             proyek.id_proyek,
             proyek.nama_proyek,
             tipe.tipe_rumah,
             konsumen.nama_konsumen,
             konsumen.hp_konsumen,
             mkdt.akad_tgl,
            c.username as uadd_by,
            d.username as uedit_by,
         ')
            ->join('legal', "kavling.id_legal = legal.id_legal")
            ->join('users c', "c.id = legal.add_by", 'left')
            ->join('users d', "c.id = legal.edit_by", 'left')
            ->join('produksi', "kavling.id_produksi = produksi.id_produksi", 'left')
            ->join('tipe', "tipe.id_tipe = kavling.id_tipe")
            ->join('mkdt', 'mkdt.id_mkdt = kavling.id_mkdt', 'left')
            ->join('konsumen', "konsumen.id_konsumen = mkdt.id_konsumen", 'left')
            ->join('jalan', "jalan.id_jalan = kavling.id_jalan")
            ->join('cluster', "jalan.id_cluster = cluster.id_cluster")
            ->join('proyek', "proyek.id_proyek = cluster.id_proyek");
    }

    protected function applyIfWhere($var, $column, $condition, $query)
    {
        $x = 0;
        if (isset($var['search']['value']) && !empty($var['search']['value'])) {
             $query->groupStart();
             foreach ($column as $i) {
                 if ($x === 0) {
                     $query->like($i, $var['search']['value']);
                 } else {
                     $query->orLike($i, $var['search']['value']);
                 }
                 $x++;
             }
             $query->groupEnd();
        }
        if (!empty($condition)) {
            $query->where($condition);
        }
        return $query;
    }
}
