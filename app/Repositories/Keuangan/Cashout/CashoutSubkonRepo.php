<?php

namespace App\Repositories\Keuangan\Cashout;

use App\Models\CashoutSubkonDetailModel;
use App\Models\CashoutSubkonDetailAllocationModel;
use App\Models\CashoutSubkonKavlingModel;
use App\Models\CashoutSubkonModel;
use App\Models\SubkonModel;
use App\Models\CashoutSubkonHistoryModel;
use App\Services\HistoryService;

use CodeIgniter\Model;

class CashoutSubkonRepo extends Model
{
    protected $table = 'cashout_subkon';
    protected $primaryKey = 'id_cashout_subkon';
    protected $returnType = 'object';
    protected $useTimestamps = true;
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'id_subkon',
        'total_nominal',
        'nomor_surat',
        'file_surat',
        'tanggal_surat',
        'keterangan',
        'status',
        'keuangan_diperiksa_by',
        'keuangan_diperiksa_at',
        'add_by',
        'edit_by',
    ];
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $cashoutSubkonKavlingModel;
    protected $cashoutSubkonModel;
    protected $subkonModel;
    protected $cashoutSubkonDetailModel;
    protected $cashoutSubkonDetailAllocationModel;
    protected $cashoutSubkonHistoryModel;
    protected HistoryService $historyService;

    public function __construct()
    {
        parent::__construct();
        $this->cashoutSubkonKavlingModel = new CashoutSubkonKavlingModel();
        $this->cashoutSubkonModel = new CashoutSubkonModel();
        $this->subkonModel = new SubkonModel();
        $this->cashoutSubkonDetailModel = new CashoutSubkonDetailModel();
        $this->cashoutSubkonDetailAllocationModel = new CashoutSubkonDetailAllocationModel();
        $this->cashoutSubkonHistoryModel = new CashoutSubkonHistoryModel();
        $this->historyService = new HistoryService();
    }

    public function getSubkonByID(int $id_subkon)
    {
        return $this->subkonModel->where('id', $id_subkon)->first();
    }

    public function getDataTables(array $var): array
    {
        $search = $var['search']['value'] ?? '';
        $idProyek = $var['id_proyek'] ?? null;
        $status = $var['status'] ?? null;

        $recordsTotal = $this->countDataTables('', $idProyek, $status);
        $recordsFiltered = $this->countDataTables($search, $idProyek, $status);

        $builder = $this->dataTablesBaseQuery($search, $idProyek, $status);
        $builder->orderBy('cs.created_at', 'DESC');

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

    private function countDataTables(string $search = '', $idProyek = null, $status = null): int
    {
        return count($this->dataTablesBaseQuery($search, $idProyek, $status)->get()->getResult());
    }

    private function dataTablesBaseQuery(string $search = '', $idProyek = null, $status = null)
    {
        $builder = $this->db->table('cashout_subkon cs')
            ->select("
                cs.id_cashout_subkon,
                cs.nomor_surat,
                cs.tanggal_surat,
                cs.total_nominal,
                cs.status,
                cs.created_at,
                s.nama_subkon,
                MIN(p.id_proyek) AS id_proyek,
                GROUP_CONCAT(DISTINCT p.nama_proyek ORDER BY p.nama_proyek SEPARATOR ', ') AS nama_proyek,
                GROUP_CONCAT(DISTINCT CONCAT(j.nama_jalan, ' No ', k.no_kavling) ORDER BY j.nama_jalan, ABS(k.no_kavling), k.no_kavling SEPARATOR ', ') AS kavling_list,
                GROUP_CONCAT(DISTINCT k.id_kavling ORDER BY j.nama_jalan, ABS(k.no_kavling), k.no_kavling SEPARATOR ',') AS id_kavlings,
                GROUP_CONCAT(DISTINCT CONCAT(k.id_kavling, '|', j.nama_jalan, '|', k.no_kavling) ORDER BY j.nama_jalan, ABS(k.no_kavling), k.no_kavling SEPARATOR ',') AS kavling_options,
                csds.tanggal_jatuh_tempo_list,
                csds.tanggal_hutang_list,
                COALESCE(csds.total_sudah_cair, 0) AS total_sudah_cair,
                COALESCE(csds.total_hutang, 0) AS total_hutang,
                csds.max_detail_status
            ", false)
            ->join('subkon s', 's.id = cs.id_subkon', 'left')
            ->join('cashout_subkon_kavling csk', 'csk.id_cashout_subkon = cs.id_cashout_subkon', 'left')
            ->join('kavling k', 'k.id_kavling = csk.id_kavling', 'left')
            ->join('jalan j', 'j.id_jalan = k.id_jalan', 'left')
            ->join('cluster cl', 'cl.id_cluster = j.id_cluster', 'left')
            ->join('proyek p', 'p.id_proyek = cl.id_proyek', 'left')
            ->join(
                "(
                    SELECT
                        id_cashout_subkon,
                        GROUP_CONCAT(DISTINCT tanggal_jatuh_tempo ORDER BY tanggal_jatuh_tempo SEPARATOR ',') AS tanggal_jatuh_tempo_list,
                        GROUP_CONCAT(DISTINCT CASE WHEN status >= 2 AND (is_paid IS NULL OR is_paid = 0) THEN tanggal_jatuh_tempo END ORDER BY tanggal_jatuh_tempo SEPARATOR ',') AS tanggal_hutang_list,
                        COALESCE(SUM(CASE WHEN status = 4 OR is_paid = 1 THEN nominal ELSE 0 END), 0) AS total_sudah_cair,
                        COALESCE(SUM(CASE WHEN status >= 2 AND (is_paid IS NULL OR is_paid = 0) THEN nominal ELSE 0 END), 0) AS total_hutang,
                        MAX(status) AS max_detail_status
                    FROM cashout_subkon_detail
                    GROUP BY id_cashout_subkon
                ) csds",
                'csds.id_cashout_subkon = cs.id_cashout_subkon',
                'left',
                false
            )
            ->groupBy('cs.id_cashout_subkon');

        if (!empty($idProyek)) {
            $builder->where('p.id_proyek', $idProyek);
        }

        if ($status !== null && $status !== '') {
            $builder->where('cs.status', $status);
        }

        if ($search !== '') {
            $builder->groupStart()
                ->like('cs.nomor_surat', $search)
                ->orLike('s.nama_subkon', $search)
                ->orLike('p.nama_proyek', $search)
                ->orLike('j.nama_jalan', $search)
                ->orLike('k.no_kavling', $search)
                ->groupEnd();
        }

        return $builder;
    }

    public function getDetailsByCashoutSubkonIds(array $ids): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if (empty($ids)) {
            return [];
        }

        $rows = $this->db->table('cashout_subkon_detail csd')
            ->select('
                csd.id_cashout_subkon,
                csd.id_cashout_subkon_detail,
                csd.berita_acara,
                csd.persentase,
                csd.nominal,
                csd.tanggal_jatuh_tempo,
                csd.status,
                csd.spp_no,
                csd.spp_tgl,
                csd.pengajuan_cair_tgl,
                csd.cek_no,
                csd.cek_tgl,
                csd.is_paid,
                csd.keterangan
            ')
            ->whereIn('csd.id_cashout_subkon', $ids)
            ->orderBy('csd.id_cashout_subkon', 'ASC')
            ->orderBy('csd.id_cashout_subkon_detail', 'ASC')
            ->get()
            ->getResultArray();

        $grouped = [];
        foreach ($rows as $row) {
            $grouped[(int) $row['id_cashout_subkon']][] = $row;
        }

        return $grouped;
    }

    public  function getListCashoutKavling(array $id_kavlings)
    {
        return $this->db->table('cashout_subkon_kavling csk')
            ->select('distinct(id_cashout_subkon) as id_cashout_subkon')
            ->whereIn('csk.id_kavling', $id_kavlings)
            ->get()->getResult();
    }

    public function getCashoutSubkonByID(int $id_cashout_subkon)
    {
        return $this->cashoutSubkonModel->where('id_cashout_subkon', $id_cashout_subkon)->first();
    }

    function getKavlingSubkonByID(int $id_cashout_subkon)
    {
        $result = $this->cashoutSubkonKavlingModel->select('distinct(id_kavling) as id_kavling')->where('id_cashout_subkon', $id_cashout_subkon)->get()->getResultArray();
        return array_column($result, 'id_kavling');
    }
    public function getListItemDetailByIDCashoutsubkon(int $id_cashout_subkon)
    {
        return $this->db->table('cashout_subkon_detail csd')
            ->where('csd.id_cashout_subkon', $id_cashout_subkon)
            ->get()->getResult();
    }

    public function upsertSubkon($id_subkon, array $data)
    {
        if (empty($id_subkon)) {
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['add_by'] = user_id();
            $this->subkonModel->insert($data);
            return $this->subkonModel->getInsertID();
        } else {
            $data['updated_at'] = date('Y-m-d H:i:s');
            $data['edit_by'] = user_id();
            $this->subkonModel->update($id_subkon, $data);
            return $id_subkon;
        }
    }

    public function saveCashoutSubkon(array $data)
    {
        if (empty($data['id_cashout_subkon'])) {
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['add_by'] = user_id();
            $this->cashoutSubkonModel->insert($data);
            $id_cashout_subkon = $this->cashoutSubkonModel->getInsertID();
            return $id_cashout_subkon;
        } else {
            $data['updated_at'] = date('Y-m-d H:i:s');
            $data['edit_by'] = user_id();
            $this->cashoutSubkonModel->update($data['id_cashout_subkon'], $data);
            return $data['id_cashout_subkon'];
        }
    }

    public function saveCashoutSubkonDetail(array $data)
    {
        $inserts = [];
        $updates = [];

        foreach ($data as $val) {
            if ($val['id_cashout_subkon_detail'] == "") {
                unset($val['id_cashout_subkon_detail']);
                $inserts[] = $val;
            } else {
                $updates[] = $val;
            }
        }

        if (!empty($updates)) {
            $this->cashoutSubkonDetailModel->updateBatch($updates, 'id_cashout_subkon_detail');
        }

        if (!empty($inserts)) {
            $this->cashoutSubkonDetailModel->insertBatch($inserts);
        }

        return true;
    }

    /**
     * Ambil daftar id_kavling yang terkait dengan sebuah id_cashout_subkon
     */
    public function getKavlingBySubkonId(int $id_cashout_subkon): array
    {
        $result = $this->cashoutSubkonKavlingModel
            ->select('id_kavling')
            ->where('id_cashout_subkon', $id_cashout_subkon)
            ->get()->getResultArray();
        return array_column($result, 'id_kavling');
    }

    public function getDetailByID(int $id_detail): ?array
    {
        return $this->cashoutSubkonDetailModel->find($id_detail);
    }

    public function updateDetail(int $id_detail, array $data): bool
    {
        return (bool) $this->cashoutSubkonDetailModel->update($id_detail, $data);
    }

    public function saveHistory(int $id_cashout_subkon, string $keterangan, int $status): bool
    {
        return $this->historyService->log('cashout_subkon', [
            'reference_type' => 'cashout_subkon',
            'reference_id' => $id_cashout_subkon,
            'action' => $this->cashoutActionFromStatus($status),
            'summary' => $keterangan,
            'metadata' => [
                'status' => $status,
            ],
            'add_by' => user_id(),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function getDetailByCashoutSubkonID(int $id_cashout_subkon): array
    {
        return $this->cashoutSubkonDetailModel
            ->where('id_cashout_subkon', $id_cashout_subkon)
            ->findAll();
    }

    public function syncAutomaticDetailAllocations(int $id_cashout_subkon, array $id_kavlings): bool
    {
        if (!$this->db->tableExists('cashout_subkon_detail_allocation')) {
            return true;
        }

        $id_kavlings = array_values(array_unique(array_map('intval', array_filter($id_kavlings))));
        sort($id_kavlings, SORT_NUMERIC);
        if (empty($id_kavlings)) {
            return false;
        }

        $details = $this->getDetailByCashoutSubkonID($id_cashout_subkon);
        if (empty($details)) {
            return true;
        }

        $this->cashoutSubkonDetailAllocationModel
            ->where('id_cashout_subkon', $id_cashout_subkon)
            ->delete();

        $now = date('Y-m-d H:i:s');
        $rows = [];
        foreach ($details as $detail) {
            $rows = array_merge($rows, $this->buildEqualAllocationRows($detail, $id_kavlings, $now));
        }

        if (empty($rows)) {
            return true;
        }

        return (bool) $this->cashoutSubkonDetailAllocationModel->insertBatch($rows);
    }

    public function getAllocationsByDetailID(int $id_cashout_subkon_detail): array
    {
        if (!$this->db->tableExists('cashout_subkon_detail_allocation')) {
            return [];
        }

        return $this->cashoutSubkonDetailAllocationModel
            ->where('id_cashout_subkon_detail', $id_cashout_subkon_detail)
            ->orderBy('id_kavling', 'ASC')
            ->findAll();
    }

    public function updateCashoutSubkonStatus(int $id_cashout_subkon, int $status): bool
    {
        return (bool) $this->cashoutSubkonModel->update($id_cashout_subkon, ['status' => $status]);
    }

    public function saveCashoutSubkonKavling(array $data)
    {
        if (empty($data)) {
            return false;
        }
        $id_cashout_subkon = $data[0]['id_cashout_subkon'];
        $this->cashoutSubkonKavlingModel->where('id_cashout_subkon', $id_cashout_subkon)->delete();
        return $this->cashoutSubkonKavlingModel->insertBatch($data);
    }

    public function getHistoryByIDCashoutSubkon($id_cashout_subkon)
    {
        $result = $this->historyService->getList([
            'module' => 'cashout_subkon',
            'reference_type' => 'cashout_subkon',
            'reference_id' => $id_cashout_subkon,
        ], 1000, 0);

        $rows = [];
        foreach ($result['data'] as $row) {
            $metadata = $row['metadata'] ?? [];
            $rows[] = (object) [
                'id_cashout_subkon_history' => $row['id'],
                'id_cashout_subkon' => $row['reference_id'],
                'status' => (int) ($metadata['status'] ?? 0),
                'keterangan' => $row['summary'],
                'created_at' => $row['created_at'],
                'add_by' => $row['add_by'],
                'updated_at' => null,
                'edit_by' => null,
                'deleted_at' => null,
                'deleted_by' => null,
                'username' => $row['username'] ?? null,
            ];
        }

        return $rows;
    }

    private function cashoutActionFromStatus(int $status): string
    {
        return match ($status) {
            0 => 'update_spk',
            1 => 'turun_jatuh_tempo',
            2 => 'pengajuan_spp',
            3 => 'pengajuan_pencairan',
            4 => 'pembayaran',
            default => 'status_update',
        };
    }

    private function buildEqualAllocationRows(array $detail, array $id_kavlings, string $now): array
    {
        $rows = [];
        $count = count($id_kavlings);
        $total = round((float) ($detail['nominal'] ?? 0), 2);
        $allocated = 0.0;
        $lastIndex = $count - 1;

        foreach ($id_kavlings as $index => $id_kavling) {
            $nominal = round($total / $count, 2);
            if ($index === $lastIndex) {
                $nominal = round($total - $allocated, 2);
            }
            $allocated = round($allocated + $nominal, 2);

            $rows[] = [
                'id_cashout_subkon_detail' => (int) $detail['id_cashout_subkon_detail'],
                'id_cashout_subkon' => (int) $detail['id_cashout_subkon'],
                'id_kavling' => (int) $id_kavling,
                'nominal' => $nominal,
                'allocation_type' => 'auto_equal',
                'created_at' => $now,
                'add_by' => user_id(),
                'updated_at' => $now,
                'edit_by' => user_id(),
            ];
        }

        return $rows;
    }
}
