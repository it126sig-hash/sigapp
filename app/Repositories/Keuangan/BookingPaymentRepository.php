<?php

namespace App\Repositories\Keuangan;

use CodeIgniter\Database\BaseConnection;

class BookingPaymentRepository
{
    public function __construct(public readonly BaseConnection $db)
    {
    }

    public function findProjectIdByMkdt(int $idMkdt): ?int
    {
        if (! $this->db->tableExists('kavling')) {
            if (! $this->db->fieldExists('id_proyek', 'mkdt')) {
                return null;
            }

            $row = $this->db->table('mkdt m')
                ->select('m.id_proyek')
                ->where('m.id_mkdt', $idMkdt)
                ->get()
                ->getRowArray();

            return ! empty($row['id_proyek']) ? (int) $row['id_proyek'] : null;
        }

        $select = $this->db->fieldExists('id_proyek', 'mkdt')
            ? 'COALESCE(c.id_proyek, m.id_proyek) AS id_proyek'
            : 'c.id_proyek AS id_proyek';

        $row = $this->db->table('mkdt m')
            ->select($select, false)
            ->join('kavling k', 'k.id_kavling = m.id_kavling', 'left')
            ->join('jalan j', 'j.id_jalan = k.id_jalan', 'left')
            ->join('cluster c', 'c.id_cluster = j.id_cluster', 'left')
            ->where('m.id_mkdt', $idMkdt)
            ->get()
            ->getRowArray();

        return ! empty($row['id_proyek']) ? (int) $row['id_proyek'] : null;
    }

    public function clearVerification(int $idMkdt): void
    {
        $this->write('mkdt_booking_payment', [
            'verified_by' => null,
            'verified_at' => null,
            'updated_at'  => date('Y-m-d H:i:s'),
        ], ['id_mkdt' => $idMkdt]);
    }

    public function findUsernameById(?int $userId): ?string
    {
        if (! $userId || ! $this->db->tableExists('users')) {
            return null;
        }

        $row = $this->db->table('users')
            ->select('username')
            ->where('id', $userId)
            ->get()
            ->getRowArray();

        return $row['username'] ?? null;
    }

    public function getVerifiedList(int $idProyek, array $filters = []): array
    {
        $length = (int) ($filters['length'] ?? 25);
        $length = max(1, min(100, $length));
        $start  = max(0, (int) ($filters['start'] ?? 0));

        $recordsTotal = (int) $this->buildVerifiedQuery($idProyek)->countAllResults();

        $hasOptionalFilters = ((int) ($filters['id_cluster'] ?? 0) > 0)
            || ((int) ($filters['id_jalan'] ?? 0) > 0)
            || trim((string) ($filters['search'] ?? '')) !== '';

        $recordsFiltered = $hasOptionalFilters
            ? (int) $this->buildVerifiedQuery($idProyek, $filters)->countAllResults()
            : $recordsTotal;

        $rows = $this->buildVerifiedQuery($idProyek, $filters)
            ->select([
                'mbp.id_mkdt',
                'mbp.id_pembayaran',
                'mbp.verified_by',
                'mbp.verified_at',
                'u.username AS verified_by_name',
                'COALESCE(so.shared_owners_count, 1) AS shared_owners_count',
                'm.id_kavling',
                'COALESCE(cl.id_proyek, m.id_proyek) AS id_proyek',
                'p.nama_proyek',
                'kns.nama_konsumen',
                'j.nama_jalan',
                'k.no_kavling',
                't.no_tipe_rumah',
                "COALESCE(NULLIF(hj.id_tipe, ''), t.tipe_rumah) AS tipe_rumah",
                'CASE WHEN m.id_kavling IS NULL OR m.id_kavling = 0 THEN 1 ELSE 0 END AS is_nup',
                'm.no_nup',
                'm.status_mkdt',
                'm.is_batal',
                'm.refund_paid',
                'm.is_lunas',
                'm.booking_fee',
                'm.booking_tgl',
            ], false)
            ->orderBy('mbp.verified_at', 'DESC')
            ->orderBy('mbp.id_mkdt', 'DESC')
            ->limit($length, $start)
            ->get()
            ->getResultArray();

        return [
            'rows'            => $rows,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
        ];
    }

    private function buildVerifiedQuery(int $idProyek, array $filters = [])
    {
        $mbpTable       = $this->db->prefixTable('mkdt_booking_payment');
        $sharedSubQuery = "SELECT id_pembayaran, COUNT(*) AS shared_owners_count FROM {$mbpTable} WHERE id_pembayaran IS NOT NULL GROUP BY id_pembayaran";

        $builder = $this->db->table('mkdt_booking_payment mbp')
            ->join('mkdt m', 'm.id_mkdt = mbp.id_mkdt')
            ->join('konsumen kns', 'kns.id_konsumen = m.id_konsumen', 'left')
            ->join('kavling k', 'k.id_kavling = m.id_kavling', 'left')
            ->join('jalan j', 'j.id_jalan = k.id_jalan', 'left')
            ->join('cluster cl', 'cl.id_cluster = j.id_cluster', 'left')
            ->join('proyek p', 'p.id_proyek = COALESCE(cl.id_proyek, m.id_proyek)', 'left')
            ->join('hargajual hj', 'hj.id = COALESCE(NULLIF(m.id_hargajual, 0), k.harga_akhir)', 'left')
            ->join('tipe t', 't.id_tipe = k.id_tipe', 'left')
            ->join('users u', 'u.id = mbp.verified_by', 'left')
            ->join("({$sharedSubQuery}) so", 'so.id_pembayaran = mbp.id_pembayaran', 'left')
            ->where('mbp.verified_at IS NOT NULL', null, false)
            ->where('COALESCE(cl.id_proyek, m.id_proyek) =', $idProyek, false);

        $idCluster = (int) ($filters['id_cluster'] ?? 0);
        if ($idCluster > 0) {
            $builder->where('cl.id_cluster', $idCluster);
        }

        $idJalan = (int) ($filters['id_jalan'] ?? 0);
        if ($idJalan > 0) {
            $builder->where('j.id_jalan', $idJalan);
        }

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $builder->groupStart()
                ->like('kns.nama_konsumen', $search)
                ->orLike('k.no_kavling', $search)
                ->orLike('j.nama_jalan', $search)
                ->orLike('m.no_nup', $search)
                ->orLike('u.username', $search)
                ->orLike('m.status_mkdt', $search)
                ->groupEnd();
        }

        return $builder;
    }

    public function mkdt(int $id, bool $lock = false): array
    {
        $sql = 'SELECT * FROM ' . $this->db->prefixTable('mkdt') . ' WHERE id_mkdt = ?';
        if ($lock && $this->db->DBDriver !== 'SQLite3') $sql .= ' FOR UPDATE';
        $row = $this->db->query($sql, [$id])->getRowArray();
        if (! $row) throw new \DomainException('Transaksi MKDT tidak ditemukan');
        return $row;
    }

    public function link(int $id, bool $lock = false): ?array
    {
        $sql = 'SELECT * FROM ' . $this->db->prefixTable('mkdt_booking_payment') . ' WHERE id_mkdt = ?';
        if ($lock && $this->db->DBDriver !== 'SQLite3') $sql .= ' FOR UPDATE';
        return $this->db->query($sql, [$id])->getRowArray() ?: null;
    }

    public function owners(int $payment): array
    {
        return $this->db->table('mkdt_booking_payment')->where('id_pembayaran', $payment)->get()->getResultArray();
    }

    public function payment(int $id): ?array
    {
        return $this->db->table('log_pembayaran')->where('id_pembayaran', $id)->get()->getRowArray();
    }

    public function details(int $payment): array
    {
        return $this->db->table('log_pembayaran_detail d')->select('d.*, i.item, i.kategori')
            ->join('keuangan_item_list i', 'i.id_keuangan_item_list = d.id_keuangan_item_list')
            ->where('d.id_pembayaran', $payment)->get()->getResultArray();
    }

    public function bookingRows(int $id, bool $deleted = false): array
    {
        return $this->db->table('log_pembayaran lp')->select('DISTINCT lp.*', false)
            ->join('log_pembayaran_detail d', 'd.id_pembayaran = lp.id_pembayaran', 'left')
            ->join('keuangan_item_list i', 'i.id_keuangan_item_list = d.id_keuangan_item_list', 'left')
            ->where('lp.id_mkdt', $id)->where('lp.is_deleted', $deleted ? 1 : 0)
            ->groupStart()->where('i.kategori', 'BO')->orWhere("LOWER(REPLACE(TRIM(COALESCE(lp.payment_type,'')), ';', '')) = 'booking'", null, false)->groupEnd()
            ->orderBy('lp.id_pembayaran')->get()->getResultArray();
    }

    public function item(string $category, ?string $name = null): array
    {
        $q = $this->db->table('keuangan_item_list')->where('kategori', $category)->where('deleted_at', null);
        if ($name !== null) $q->where('item', $name);
        $row = $q->orderBy('id_keuangan_item_list')->get()->getRowArray();
        if (! $row) throw new \DomainException('Master item keuangan tidak ditemukan: ' . ($name ?? $category));
        return $row;
    }

    public function write(string $table, array $data, array $where = []): int
    {
        $builder = $this->db->table($table);
        $ok = $where ? $builder->where($where)->update($data) : $builder->insert($data);
        if (! $ok) throw new \RuntimeException('Gagal menyimpan ' . $table);
        return $where ? 0 : (int) $this->db->insertID();
    }

    public function saveLink(int $id, ?int $payment, array $extra = []): void
    {
        $existing = $this->link($id);
        $changes = ['id_pembayaran' => $payment] + $extra;
        if ($existing) {
            $dirty = false;
            foreach ($changes as $key => $value) {
                if (($existing[$key] ?? null) != $value) { $dirty = true; break; }
            }
            if ($dirty) $this->write('mkdt_booking_payment', $changes + ['updated_at' => date('Y-m-d H:i:s')], ['id_mkdt' => $id]);
            return;
        }
        $this->write('mkdt_booking_payment', ['id_mkdt' => $id, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')] + $changes);
    }

    public static function separateBookingSql(string $detail = 'lpd', string $item = 'kl'): string
    {
        return "($item.kategori = 'BO' AND COALESCE($detail.booking_is_installment, 0) = 0)";
    }

    public static function effectiveCategorySql(string $detail = 'lpd', string $item = 'kl'): string
    {
        return "CASE WHEN $item.kategori = 'BO' AND $detail.booking_is_installment = 1 THEN 'UM' ELSE $item.kategori END";
    }

    public function installmentSummary(int $id): array
    {
        $category = self::effectiveCategorySql();
        $separate = self::separateBookingSql();
        $items = $this->db->table('log_pembayaran_detail lpd')
            ->select("CASE WHEN kl.kategori = 'BO' THEN um.id_keuangan_item_list ELSE kl.id_keuangan_item_list END AS id_keuangan_item_list, CASE WHEN kl.kategori = 'BO' THEN 'Uang Muka' ELSE kl.item END AS item, $category AS kategori, SUM(lpd.nominal) AS total_nominal", false)
            ->join('log_pembayaran lp', 'lp.id_pembayaran = lpd.id_pembayaran')
            ->join('keuangan_item_list kl', 'kl.id_keuangan_item_list = lpd.id_keuangan_item_list')
            ->join('(SELECT MIN(id_keuangan_item_list) AS id_keuangan_item_list FROM ' . $this->db->prefixTable('keuangan_item_list') . " WHERE kategori = 'UM' AND deleted_at IS NULL) um", '1 = 1', 'left', false)
            ->where('lp.id_mkdt', $id)->where('lp.is_deleted', 0)->where("NOT $separate", null, false)
            ->where("LOWER(REPLACE(TRIM(COALESCE(lp.payment_type,'')), ';', '')) != 'refund'", null, false)
            ->groupBy('1, 2, 3', false)->get()->getResultArray();
        // Legacy headers without any allocation remain part of installments unless they are Booking/Refund.
        $legacy = $this->db->table('log_pembayaran lp')->select('COALESCE(SUM(lp.nominal),0) AS total', false)
            ->join('log_pembayaran_detail lpd', 'lpd.id_pembayaran = lp.id_pembayaran', 'left')
            ->where('lp.id_mkdt', $id)->where('lp.is_deleted', 0)->where('lpd.id_pembayaran', null)
            ->where("LOWER(REPLACE(TRIM(COALESCE(lp.payment_type,'')), ';', '')) NOT IN ('booking','refund')", null, false)
            ->get()->getRowArray();
        $total = array_sum(array_column($items, 'total_nominal')) + (float) ($legacy['total'] ?? 0);
        $tagihan = $this->db->table('keuangan')->select('COALESCE(SUM(nominal),0) AS total', false)
            ->where('id_mkdt', $id)->where('is_void', 0)->get()->getRowArray();
        $mk = $this->mkdt($id);
        $totalTagihan = (float) $tagihan['total'];
        return ['total_tagihan' => $totalTagihan, 'sudah_bayar' => $total,
            'sisa_tagihan' => max(0, (float) $tagihan['total'] - $total), 'items' => $items,
            'perlu_rekonsiliasi' => (int) $mk['is_lunas'] === 1 && $totalTagihan > $total];
    }

    public function recalculate(int $id): void
    {
        $cat = self::effectiveCategorySql();
        $rows = $this->db->table('log_pembayaran_detail lpd')->select("$cat AS kategori, SUM(lpd.nominal) AS nominal", false)
            ->join('log_pembayaran lp', 'lp.id_pembayaran=lpd.id_pembayaran')
            ->join('keuangan_item_list kl', 'kl.id_keuangan_item_list=lpd.id_keuangan_item_list')
            ->where('lp.id_mkdt', $id)->where('lp.is_deleted', 0)->groupBy($cat, false)->get()->getResultArray();
        $data = ['total_um' => 0, 'total_bb' => 0, 'total_adm' => 0, 'total_booking' => 0, 'updated_at' => date('Y-m-d H:i:s')];
        $fields = ['UM' => 'total_um', 'BB' => 'total_bb', 'ADM' => 'total_adm', 'BO' => 'total_booking'];
        foreach ($rows as $r) {
            if (! isset($fields[$r['kategori']])) throw new \DomainException('Kategori pembayaran tidak valid');
            $data[$fields[$r['kategori']]] = (float) $r['nominal'];
        }
        $existing = $this->db->table('mkdt_payment_summary')->where('id_mkdt', $id)->get()->getRowArray();
        if ($existing) {
            foreach (['total_um','total_bb','total_adm','total_booking'] as $field) {
                if (round((float)($existing[$field] ?? 0),2) !== round((float)$data[$field],2)) {
                    $this->write('mkdt_payment_summary', $data, ['id_mkdt' => $id]);
                    return;
                }
            }
            return;
        }
        $this->write('mkdt_payment_summary', ['id_mkdt' => $id] + $data);
    }
}
