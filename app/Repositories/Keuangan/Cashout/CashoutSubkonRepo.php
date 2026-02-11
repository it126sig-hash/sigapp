<?php

namespace App\Repositories\Keuangan\Cashout;

use App\Models\CashoutSubkonDetailModel;
use App\Models\CashoutSubkonKavlingModel;
use App\Models\CashoutSubkonModel;
use App\Models\SubkonModel;
use App\Models\CashoutSubkonHistoryModel;

use CodeIgniter\Model;

class CashoutSubkonRepo extends Model
{
    protected $table = 'cashout_subkon';
    protected $primaryKey = 'id_cashout_subkon';
    protected $returnType = 'object';
    protected $useTimestamps = true;
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'id_subkon',
        'total_nominal',
        'spk_file',
        'spk_no',
        'spk_tgl',
        'keterangan',
        'status',
        'add_by',
        'edit_by',
    ];
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
    protected $cashoutSubkonKavlingModel;
    protected $cashoutSubkonModel;
    protected $subkonModel;
    protected $cashoutSubkonDetailModel;
    protected $cashoutSubkonHistoryModel;

    public function __construct()
    {
        parent::__construct();
        $this->cashoutSubkonKavlingModel = new CashoutSubkonKavlingModel();
        $this->cashoutSubkonModel = new CashoutSubkonModel();
        $this->subkonModel = new SubkonModel();
        $this->cashoutSubkonDetailModel = new CashoutSubkonDetailModel();
        $this->cashoutSubkonHistoryModel = new CashoutSubkonHistoryModel();
    }

    public function getSubkonByID(int $id_subkon)
    {
        return $this->subkonModel->where('id', $id_subkon)->first();
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

    public function softDelete($id)
    {
        return $this->db->table('cashout')
            ->where('id', $id)
            ->update(['is_deleted' => 1, 'deleted_at' => date('Y-m-d H:i:s')]);
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
            $this->cashoutSubkonHistoryModel->save([
                'id_cashout_subkon' => $id_cashout_subkon,
                'keterangan' => "Terbit SPK",
                'status' => 0,
                'add_by' => user_id(),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            return $id_cashout_subkon;
        } else {
            $this->cashoutSubkonHistoryModel->save([
                'id_cashout_subkon' => $data['id_cashout_subkon'],
                'keterangan' => "Melakukan Perubahan pada SPK",
                'status' => 0,
                'add_by' => user_id(),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
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

    public function updateJatuhTempo($id_detail, $tanggal_jatuh_tempo, $berita_acara)
    {
        // Get the detail to find the id_cashout_subkon
        $detail = $this->cashoutSubkonDetailModel->find($id_detail);

        $this->cashoutSubkonDetailModel->update($id_detail, [
            'tanggal_jatuh_tempo' => $tanggal_jatuh_tempo,
            'status' => 1,
        ]);

        // Log history
        if ($detail) {
            $this->cashoutSubkonHistoryModel->save([
                'id_cashout_subkon' => $detail['id_cashout_subkon'],
                'keterangan' => "Turun Jatuh Tempo untuk:  " . $berita_acara . " Pada Tanggal " . date('d F Y', strtotime($tanggal_jatuh_tempo)),
                'status' => 1,
                'add_by' => user_id(),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        return true;
    }

    public function updateSPP($id_detail, $spp_no, $spp_tgl)
    {
        // Get the detail to find the id_cashout_subkon
        $detail = $this->cashoutSubkonDetailModel->find($id_detail);

        $this->cashoutSubkonDetailModel->update($id_detail, [
            'spp_no' => $spp_no,
            'spp_tgl' => $spp_tgl,
            'spp_add_by' => user_id(),
            'spp_created_at' => date('Y-m-d H:i:s'),
            'status' => 2,
        ]);

        // Log history
        if ($detail) {
            $this->cashoutSubkonHistoryModel->save([
                'id_cashout_subkon' => $detail['id_cashout_subkon'],
                'keterangan' => "Pengajuan SPP: No " . $spp_no . " Pada Tanggal " . date('d F Y', strtotime($spp_tgl)) . " untuk: " . ($detail['berita_acara'] ?? "-"),
                'status' => 2,
                'add_by' => user_id(),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        return true;
    }

    public function updatePencairan($id_detail, $pencairan_tgl)
    {
        // Get the detail to find the id_cashout_subkon
        $detail = $this->cashoutSubkonDetailModel->find($id_detail);

        $this->cashoutSubkonDetailModel->update($id_detail, [
            'pencairan_tgl' => $pencairan_tgl,
            'pencairan_add_by' => user_id(),
            'pencairan_created_at' => date('Y-m-d H:i:s'),
            'status' => 3,
        ]);

        // Log history
        if ($detail) {
            $this->cashoutSubkonHistoryModel->save([
                'id_cashout_subkon' => $detail['id_cashout_subkon'],
                'keterangan' => "Pengajuan Pencairan: Pada Tanggal " . date('d F Y', strtotime($pencairan_tgl)) . " untuk: " . ($detail['berita_acara'] ?? "-"),
                'status' => 3,
                'add_by' => user_id(),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        return true;
    }

    public function updatePembayaran($id_detail, $cek_no, $cek_tgl)
    {
        // Get the detail to find the id_cashout_subkon
        $detail = $this->cashoutSubkonDetailModel->find($id_detail);

        $this->cashoutSubkonDetailModel->update($id_detail, [
            'cek_no' => $cek_no,
            'cek_tgl' => $cek_tgl,
            'bayar_add_by' => user_id(),
            'bayar_created_at' => date('Y-m-d H:i:s'),
            'status' => 4,
        ]);

        // Log history
        if ($detail) {
            $this->cashoutSubkonHistoryModel->save([
                'id_cashout_subkon' => $detail['id_cashout_subkon'],
                'keterangan' => "Pembayaran: No Cek " . $cek_no . " Pada Tanggal " . date('d F Y', strtotime($cek_tgl)) . " untuk: " . ($detail['berita_acara'] ?? "-"),
                'status' => 4,
                'add_by' => user_id(),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        // Check if all details are paid (status 4)
        $allDetails = $this->cashoutSubkonDetailModel->where('id_cashout_subkon', $detail['id_cashout_subkon'])->findAll();
        $allPaid = true;
        foreach ($allDetails as $d) {
            if ($d['status'] != 4) {
                $allPaid = false;
                break;
            }
        }

        if ($allPaid) {
            $this->cashoutSubkonModel->update($detail['id_cashout_subkon'], ['status' => 1]); // Set main status to 1 (Done/Paid)
        }

        return true;
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
        return $this->db->table('cashout_subkon_history csh')
            ->select('csh.*, u.username')
            ->join('users u', 'u.id = csh.add_by', 'left')
            ->where('csh.id_cashout_subkon', $id_cashout_subkon)
            ->orderBy('csh.created_at', 'DESC')
            ->get()->getResult();
    }
}
