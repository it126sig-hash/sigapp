<?php

namespace App\Models;

use CodeIgniter\Model;

class ListBankModel extends Model
{
    protected $table = 'list_bank';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $protectFields = true;
    protected $allowedFields = ['bank', 'keterangan', 'exp_days', 'deleted_at'];

    // Validation rules
    protected $validationRules = [
        'bank' => 'permit_empty|max_length[255]',
        'keterangan' => 'permit_empty|max_length[255]',
        'exp_days' => 'permit_empty|integer'
    ];

    protected $validationMessages = [
        'bank' => [
            'max_length' => 'Nama bank maksimal 255 karakter'
        ],
        'keterangan' => [
            'max_length' => 'Keterangan maksimal 255 karakter'
        ],
        'exp_days' => [
            'integer' => 'Exp days harus berupa angka'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Dates
    protected $useTimestamps = false;
    protected $deletedField = 'deleted_at';

    // Custom methods
    public function getAllBanks($search = null)
    {
        if ($search != null) {
            return $this->like('bank', $search)
                       ->orLike('keterangan', $search)
                       ->findAll();
        }
        return $this->findAll();
    }

    public function getBankById($id)
    {
        return $this->find($id);
    }

    public function getBankByName($bankName)
    {
        return $this->where('bank', $bankName)->first();
    }

    public function searchBanks($keyword)
    {
        return $this->like('bank', $keyword)
                   ->orLike('keterangan', $keyword)
                   ->findAll();
    }

    public function getActiveBanks()
    {
        // Assuming active banks are those with exp_days > 0
        return $this->where('exp_days >', 0)->findAll();
    }

    public function insertBank($data)
    {
        return $this->insert($data);
    }

    public function updateBank($id, $data)
    {
        return $this->update($id, $data);
    }

    public function deleteBank($id)
    {
        return $this->delete($id);
    }
}
