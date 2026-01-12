<?php

namespace App\Repositories;

use CodeIgniter\Model;

class KeuanganRepository extends Model
{
    protected $table = 'keuangan';
    protected $primaryKey = 'id_keuangan';
    protected $returnType = 'object';

    //ambil semua tagihan
    public function getTagihanById(int $idMkdt)
    {
        return $this->select(['keuangan.*', 'users.username'])
            ->join('users', 'users.id = keuangan.add_by')
            ->where('keuangan.id_mkdt', $idMkdt)
            ->findAll();
    }
    //hanya tagihan turun kpr
    public function getTagihanTurunKPRById(int $idMkdt)
    {
        //untuk
        return $this->select(['keuangan.*', 'users.username'])
            ->join('users', 'users.id = keuangan.add_by')
            ->where('keuangan.id_mkdt', $idMkdt)
            ->where('keuangan.berita_acara', 'Turun KPR')
            ->first();
    }

    public function getLIKeu()
    {
        //ambil list item keuangan
        return  $this->db->table('keuangan_item_list')
            ->select("*")->get()->getResult();
    }

    //tanpa tagihan turun kpr
    public function getTagihanOnlyByID(int $idMkdt)
    {
        //untuk 
        return $this->select(['keuangan.*', 'users.username'])
            ->join('users', 'users.id = keuangan.add_by')
            ->where('keuangan.id_mkdt', $idMkdt)
            ->where('berita_acara !=', 'Turun KPR')
            ->findAll();
    }
}
