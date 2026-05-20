<?php

namespace Tests\Support\Fixtures\Controllers;

use App\Models\TransaksiModel;
use App\Models\KeuanganModel;
use App\Models\KavlingModel;
use CodeIgniter\Database\Database;

/**
 * Complex test controller with business logic and database queries
 */
class ComplexController
{
    private TransaksiModel $transaksiModel;
    private KeuanganModel $keuanganModel;
    private $db;

    public function __construct()
    {
        $this->transaksiModel = new TransaksiModel();
        $this->keuanganModel = new KeuanganModel();
        $this->db = \Config\Database::connect();
    }

    public function processTransaction($id)
    {
        // Business logic: Complex conditional
        if ($id > 0 && $id < 1000 && $this->isValidTransaction($id)) {
            // Business logic: Calculation
            $amount = $this->calculateAmount($id);
            $tax = $amount * 0.1;
            $total = $amount + $tax;

            // Database query: Query Builder
            $result = $this->db->table('transaksi')
                ->where('id', $id)
                ->where('status', 'pending')
                ->get()
                ->getRowArray();

            // Business logic: Data transformation
            $processed = array_map(function($item) {
                return [
                    'id' => $item['id'],
                    'amount' => $item['amount'] * 1.1
                ];
            }, $result);

            // Business logic: Validation
            if (empty($processed) || !isset($processed['id'])) {
                return false;
            }

            // Database query: Raw SQL
            $query = "SELECT * FROM keuangan WHERE transaksi_id = " . $id;
            $keuangan = $this->db->query($query)->getResultArray();

            // Business logic: Loop processing
            foreach ($keuangan as &$item) {
                $item['processed'] = true;
                $item['total'] = $item['amount'] + $tax;
            }

            // Database query: Model method
            $this->transaksiModel->where('id', $id)->update(['status' => 'completed']);

            return $total;
        }

        return false;
    }

    private function calculateAmount($id)
    {
        // More business logic
        $base = 1000;
        $multiplier = 1.5;
        return $base * $multiplier;
    }

    private function isValidTransaction($id)
    {
        // Validation logic
        return !empty($id) && is_numeric($id);
    }

    public function bulkUpdate()
    {
        // Direct database connection usage
        $builder = $this->db->table('transaksi');
        $builder->where('status', 'pending');
        $builder->update(['status' => 'processing']);

        return true;
    }

    public function getReport()
    {
        // Multiple database queries
        $transactions = $this->transaksiModel->findAll();
        $keuangan = $this->keuanganModel->where('status', 'active')->findAll();

        // Data processing
        $total = 0;
        foreach ($transactions as $t) {
            $total += $t['amount'];
        }

        return [
            'transactions' => $transactions,
            'keuangan' => $keuangan,
            'total' => $total
        ];
    }
}
