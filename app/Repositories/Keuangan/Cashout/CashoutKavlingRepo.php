<?php

namespace App\Repositories\Keuangan\Cashout;

use CodeIgniter\Database\BaseConnection;

class CashoutKavlingRepo
{
    private const CASHOUT_LEDGER_SOURCES = ['bayar_produksi', 'cashout_subkon_allocation'];
    private const PAJAK_LEDGER_SOURCES = ['pajak_pph42', 'pajak_ppn'];

    protected BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? \Config\Database::connect();
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
                COALESCE(mgm.total, 0) AS total_mgm,
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
            ->join(
                '(SELECT kv.id_kavling, SUM(rb.nominal_cair_keuangan) AS total '
                . 'FROM referral_bonuses rb '
                . 'JOIN referrals r ON r.id = rb.id_referral '
                . 'JOIN kavling kv ON kv.id_mkdt = r.id_mkdt_referred '
                . 'WHERE rb.cair_keuangan_at IS NOT NULL AND rb.nominal_cair_keuangan > 0 '
                . 'GROUP BY kv.id_kavling) mgm',
                'mgm.id_kavling = k.id_kavling',
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
        $rows = array_merge($this->getCashoutRowsByKavling($idKavling), $this->getPajakRowsByKavling($idKavling));
        usort($rows, static fn ($a, $b) => strcmp((string) $b->tanggal_transaksi, (string) $a->tanggal_transaksi));

        return array_map(static function ($row) {
            return (object) [
                'tanggal' => $row->tanggal_transaksi,
                'nominal' => $row->nominal,
                'keterangan' => $row->keterangan,
                'departemen' => $row->departemen,
                'item' => $row->label,
            ];
        }, $rows);
    }

    public function getCashoutRowsByKavling(int $idKavling): array
    {
        $rows = [];
        $cashoutRows = $this->db->table('cashout c')
            ->select('c.id, c.nominal, c.tanggal_bayar, c.keterangan, lc.item')
            ->join('list_cashout lc', 'lc.id = c.id_item_cashout', 'left')
            ->where('c.id_kavling', $idKavling)
            ->where('c.is_deleted', 0)
            ->get()->getResult();

        foreach ($cashoutRows as $row) {
            $rows[] = (object) [
                'nominal' => $row->nominal,
                'tanggal_transaksi' => $row->tanggal_bayar,
                'tanggal_bayar' => $row->tanggal_bayar,
                'label' => $row->item ?: 'Cashout Keuangan',
                'item' => $row->item ?: 'Cashout Keuangan',
                'keterangan' => $row->keterangan,
                'source_type' => 'cashout_keuangan',
                'departemen' => 'Keuangan',
            ];
        }

        if ($this->db->tableExists('finance_ledger')) {
            $ledgerRows = $this->db->table('finance_ledger')
                ->select('nominal, tanggal_transaksi, label, keterangan, source_type')
                ->where('id_kavling', $idKavling)
                ->where('direction', 'expense')
                ->whereIn('source_type', self::CASHOUT_LEDGER_SOURCES)
                ->where('status', 'active')
                ->where('is_deleted', 0)
                ->get()->getResult();

            foreach ($ledgerRows as $row) {
                $row->item = $row->label;
                $row->departemen = $row->source_type === 'bayar_produksi' ? 'Produksi' : 'Subkon';
                $rows[] = $row;
            }
        }

        if ($this->db->tableExists('referral_bonuses')) {
            $bonusRows = $this->db->table('referral_bonuses rb')
                ->select('rb.nominal_cair_keuangan, rb.tanggal_cair_keuangan, rb.cair_keuangan_at, rb.keterangan, st.nama_tahapan')
                ->join('referrals r', 'r.id = rb.id_referral')
                ->join('kavling kv', 'kv.id_mkdt = r.id_mkdt_referred')
                ->join('referral_bonus_stages st', 'st.id = rb.id_stage', 'left')
                ->where('kv.id_kavling', $idKavling)
                ->where('rb.cair_keuangan_at IS NOT NULL', null, false)
                ->where('rb.nominal_cair_keuangan >', 0)
                ->get()->getResult();

            foreach ($bonusRows as $row) {
                $label = 'Member Get Member - ' . ($row->nama_tahapan ?: 'Bonus');
                $rows[] = (object) [
                    'nominal' => $row->nominal_cair_keuangan,
                    'tanggal_transaksi' => $row->tanggal_cair_keuangan ?: substr((string) $row->cair_keuangan_at, 0, 10),
                    'label' => $label,
                    'item' => $label,
                    'keterangan' => $row->keterangan,
                    'source_type' => 'member_get_member',
                    'departemen' => 'MGM',
                ];
            }
        }

        usort($rows, static fn ($a, $b) => strcmp((string) $b->tanggal_transaksi, (string) $a->tanggal_transaksi));
        return $rows;
    }

    private function getPajakRowsByKavling(int $idKavling): array
    {
        if (!$this->db->tableExists('finance_ledger')) {
            return [];
        }

        $rows = $this->db->table('finance_ledger')
            ->select('nominal, tanggal_transaksi, label, keterangan, source_type')
            ->where('id_kavling', $idKavling)
            ->where('direction', 'expense')
            ->whereIn('source_type', self::PAJAK_LEDGER_SOURCES)
            ->where('status', 'active')
            ->where('is_deleted', 0)
            ->get()->getResult();

        foreach ($rows as $row) {
            $row->departemen = 'Pajak';
        }

        return $rows;
    }
}
