<?php

namespace App\Repositories\Keuangan\Cashout;

use CodeIgniter\Database\BaseConnection;

class CashoutKavlingRepo
{
    protected BaseConnection $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function getDataTables(array $var): array
    {
        $search = $var['search']['value'] ?? '';
        $idProyek = $var['id_proyek'] ?? null;

        $recordsTotal = $this->countDataTables('', $idProyek);
        $recordsFiltered = $this->countDataTables($search, $idProyek);

        $builder = $this->dataTablesBaseQuery($search, $idProyek);
        $builder->orderBy('j.nama_jalan', 'ASC')->orderBy('ABS(k.no_kavling)', 'ASC')->orderBy('k.no_kavling', 'ASC');

        if (isset($var['start'], $var['length']) && (int) $var['length'] > 0) {
            $builder->limit((int) $var['length'], (int) $var['start']);
        }

        $rows = $builder->get()->getResult();

        return [
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'rows' => $rows,
        ];
    }

    private function countDataTables(string $search = '', $idProyek = null): int
    {
        $countBuilder = $this->db->table('(' . $this->dataTablesBaseQuery($search, $idProyek)->getCompiledSelect() . ') t');
        return (int) ($countBuilder->select('COUNT(*) as total')->get()->getRow()->total ?? 0);
    }

    private function dataTablesBaseQuery(string $search = '', $idProyek = null)
    {
        $builder = $this->db->table('kavling k')
            ->select("
                k.id_kavling,
                j.nama_jalan,
                k.no_kavling,
                t.tipe_rumah,
                t.no_tipe_rumah,
                m.id_mkdt,
                m.status_mkdt,
                kon.nama_konsumen,
                COALESCE(co.total, 0) AS total_cashout_keu,
                COALESCE(pr.total, 0) AS total_produksi,
                COALESCE(sk.total, 0) AS total_subkon,
                COALESCE(pj.total, 0) AS total_pajak
            ", false)
            ->join('jalan j', 'j.id_jalan = k.id_jalan')
            ->join('cluster cl', 'cl.id_cluster = j.id_cluster')
            ->join('proyek p', 'p.id_proyek = cl.id_proyek')
            ->join('tipe t', 't.id_tipe = k.id_tipe', 'left')
            ->join('mkdt m', 'm.id_mkdt = k.id_mkdt', 'left')
            ->join('konsumen kon', 'kon.id_konsumen = m.id_konsumen', 'left')
            ->join(
                '(SELECT id_kavling, SUM(nominal) AS total FROM cashout WHERE is_deleted = 0 GROUP BY id_kavling) co',
                'co.id_kavling = k.id_kavling',
                'left',
                false
            )
            ->join(
                "(SELECT id_kavling, SUM(nominal) AS total FROM finance_ledger WHERE direction = 'expense' AND source_type = 'bayar_produksi' AND status = 'active' AND is_deleted = 0 GROUP BY id_kavling) pr",
                'pr.id_kavling = k.id_kavling',
                'left',
                false
            )
            ->join(
                "(SELECT id_kavling, SUM(nominal) AS total FROM finance_ledger WHERE direction = 'expense' AND source_type = 'cashout_subkon_allocation' AND status = 'active' AND is_deleted = 0 GROUP BY id_kavling) sk",
                'sk.id_kavling = k.id_kavling',
                'left',
                false
            )
            ->join(
                "(SELECT id_kavling, SUM(nominal) AS total FROM finance_ledger WHERE direction = 'expense' AND source_type IN ('pajak_pph42', 'pajak_ppn') AND status = 'active' AND is_deleted = 0 GROUP BY id_kavling) pj",
                'pj.id_kavling = k.id_kavling',
                'left',
                false
            )
            ->where('p.id_proyek', $idProyek);

        if ($search !== '') {
            $builder->groupStart()
                ->like('j.nama_jalan', $search)
                ->orLike('k.no_kavling', $search)
                ->orLike('kon.nama_konsumen', $search)
                ->groupEnd();
        }

        return $builder;
    }

    public function getDetailList(int $idKavling): array
    {
        $sql = "
            SELECT tanggal_bayar AS tanggal, cashout.nominal, cashout.keterangan COLLATE utf8mb4_general_ci AS keterangan, 'Keuangan' AS departemen, lc.item COLLATE utf8mb4_general_ci AS item
                FROM cashout
                LEFT JOIN list_cashout lc ON lc.id = cashout.id_item_cashout
                WHERE cashout.id_kavling = ? AND cashout.is_deleted = 0
            UNION ALL
            SELECT tanggal_transaksi, nominal, keterangan COLLATE utf8mb4_general_ci, 'Produksi', label COLLATE utf8mb4_general_ci
                FROM finance_ledger WHERE id_kavling = ? AND direction = 'expense' AND source_type = 'bayar_produksi' AND status = 'active' AND is_deleted = 0
            UNION ALL
            SELECT tanggal_transaksi, nominal, keterangan COLLATE utf8mb4_general_ci, 'Subkon', label COLLATE utf8mb4_general_ci
                FROM finance_ledger WHERE id_kavling = ? AND direction = 'expense' AND source_type = 'cashout_subkon_allocation' AND status = 'active' AND is_deleted = 0
            UNION ALL
            SELECT tanggal_transaksi, nominal, keterangan COLLATE utf8mb4_general_ci, 'Pajak', label COLLATE utf8mb4_general_ci
                FROM finance_ledger WHERE id_kavling = ? AND direction = 'expense' AND source_type IN ('pajak_pph42', 'pajak_ppn') AND status = 'active' AND is_deleted = 0
            ORDER BY tanggal DESC
        ";

        return $this->db->query($sql, [$idKavling, $idKavling, $idKavling, $idKavling])->getResult();
    }
}
