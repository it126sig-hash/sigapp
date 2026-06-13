<?php
// ADEL CODEIGNITER 4 CRUD GENERATOR

namespace App\Controllers;

use App\Controllers\BaseController;

use App\Models\ClusterModel;
use App\Models\ProyekModel;

class Promo extends BaseController
{

	protected $clusterModel;
	protected $proyekModel;
	protected $validation;
	protected $db;

	public function __construct()
	{
		$this->clusterModel = new ClusterModel();
		$this->proyekModel = new ProyekModel();
		$this->validation =  \Config\Services::validation();
		$this->db = db_connect();
	}

	public function index()
	{
		$data['data']['proyek'] = $this->proyekModel->select('id_proyek, nama_proyek')->findAll();

		$data['content'] = 'promo/index';
		$data['data']['controller'] = 'promo';
		$data['data']['title'] = 'Promo';
		return view('template', $data);
	}

	public function getDataTable()
	{
		// if(!$this->request->isAJAX()){
		// 	echo "ewean kuda";
		// 	return;
		// }
		$response = array();
		$data['token'] = csrf_hash();
		$data['data'] = array();

		$var = [];

		$var = array_merge($var, ["search" => ["value" => "", "regex"=>false]]);
		$data['test2'] = $var;		
		
		$var = $this->request->getVar();
		$id_proyek = resolve_active_proyek_id($var['id_proyek'] ?? null);
		
		$data['test'] = $var;
		// if(!$var['search'])
		// 	$var['search']['value'] = "";

		$colum = ['promo.nama_promo'];
		$condition = [];

		$query = $this->db->table('promo')
			->select('
				promo.*, 
				users.username as uadd_by, 
				c.username as uedit_by, 
				')
			->join('proyek', 'proyek.id_proyek = promo.id_proyek')
			->join('users', 'users.id = promo.add_by', 'left')
			->join('users as c', 'c.id = promo.edit_by', 'left');

		if ($id_proyek)
			$condition = array_merge($condition, ["promo.id_proyek" => $id_proyek]);

		$result = $this->if_where($var, $colum, $condition, $query);

		$result
			->offset($var['start'])
			->limit($var['length']);

		$x = $result->get();
		$data['draw'] = $var['draw'];

		//count filtered
		$countfiltered = $this->db->table('promo')
			->select('promo.*')
			->join('proyek', 'proyek.id_proyek = promo.id_proyek');

		$countfiltered = $this->if_where($var, $colum, $condition, $countfiltered);
		$data['recordsFiltered'] = count($countfiltered->get()->getResult());

		$condition = [];
		$countTotal =  $this->db->table('promo')
			->select('count(promo.id) as count')
			->join('proyek', 'proyek.id_proyek = promo.id_proyek')
			->where($condition);

		$data['recordsTotal'] =  $countTotal->get()->getResult()[0]->count;
		$no = $var['start'];
        foreach ($x->getResult() as $key => $value) {
			$ops = '<div class="btn-group">';
			$ops .= '	<button type="button" class="btn btn-outline-primary waves-effect btn-sm" onclick="edit(' . $value->id . ')"><i class="fa fa-edit"></i></button>';
			$ops .= '	<button type="button" class="btn btn-outline-danger waves-effect btn-sm" onclick="remove(' . $value->id . ')"><i class="fa fa-trash"></i></button>';
			$ops .= '</div>';

			$data['data'][$key] = array(
				$value->id,
				$value->nama_promo,
				number_format($value->diskon),
				$value->insentif,
				$this->format_tgl($value->tgl_berlaku_start). " s/d <br>". $this->format_tgl($value->tgl_berlaku_end),
				$this->format_tgl($value->tgl_booking_start). " s/d <br>". $this->format_tgl($value->tgl_booking_end),
				$value->keterangan,
				$this->is_active($value->status, "Aktif", "Tidak"),
				$value->uadd_by,
				$this->format_tgl($value->created_at),
				$value->uedit_by,
				$this->format_tgl($value->updated_at),
				$ops,
			);
		}
        return $this->response->setJSON($data);
	}

	public function getAll()
	{
		$response = array();
		$data['token'] = csrf_hash();
		$data['data'] = array();

		$id_proyek = resolve_active_proyek_id($this->request->getVar('id_proyek'));
		$search = $this->request->getVar('search');
		if(!$search)
			$search = "";

		$result = $this->clusterModel
			->select('id_cluster, cluster.id_proyek, nama_proyek, nama_cluster, is_active')
			->join('proyek', 'proyek.id_proyek = cluster.id_proyek');

		if($id_proyek) {
			$result
				->like('cluster.nama_cluster', $search)
				->where('cluster.id_proyek', $id_proyek);
		}

		foreach ($result->findAll() as $key => $value) {

			$ops = '<div class="btn-group">';
			$ops .= '	<button type="button" class="btn btn-outline-primary waves-effect btn-sm" onclick="edit(' . $value->id_cluster . ')"><i class="fa fa-edit"></i></button>';
			$ops .= '	<button type="button" class="btn btn-outline-danger waves-effect btn-sm" onclick="remove(' . $value->id_cluster . ')"><i class="fa fa-trash"></i></button>';
			$ops .= '</div>';

			$data['data'][$key] = array(
				$value->id_cluster,
				$value->id_proyek,
				$value->nama_proyek,
				$value->nama_cluster,
				$value->is_active,

				$ops,
			);
		}
		return $this->response->setJSON($data);
	}

	function getOne()
	{
		$response = array();

		$id = $this->request->getPost('id_cluster');

		if ($this->validation->check($id, 'required|numeric')) {

			$data = $this->db->table('promo')
				->where('id', $id)->get()->getRow(0);
			$data->token = csrf_hash();
			// $data['token'] = csrf_hash();
			return $this->response->setJSON($data);
		} else {

			throw new \CodeIgniter\Exceptions\PageNotFoundException();
		}
	}

	public function add()
	{

		$response = array();
		$response['token'] = csrf_hash();

		$fields['id_proyek'] = $this->request->getPost('idProyek');
		$fields['nama_promo'] = $this->request->getPost('nama_promo');
		$fields['insentif'] = $this->request->getPost('insentif');
		$fields['diskon'] = $this->num($this->request->getPost('diskon'));
		$fields['tgl_berlaku_start'] = $this->request->getPost('tgl_berlaku_start');
		$fields['tgl_berlaku_end'] = $this->request->getPost('tgl_berlaku_end');
		$fields['tgl_booking_start'] = $this->request->getPost('tgl_booking_start');
		$fields['tgl_booking_end'] = $this->request->getPost('tgl_booking_end');
		$fields['keterangan'] = $this->request->getPost('keterangan');
		$fields['status'] = $this->request->getPost('isActive');

		$fields['add_by'] = user_id();
		$fields['created_at'] = date("Y-m-d H:i:s");
		$fields['edit_by'] = user_id();
		$fields['updated_at'] = date("Y-m-d H:i:s");




		$this->validation->setRules([
			'id_proyek' => ['label' => 'Id proyek', 'rules' => 'permit_empty|max_length[255]'],
			'nama_promo' => ['label' => 'Nama Promo', 'rules' => 'permit_empty|max_length[255]'],
		]);

		if ($this->validation->run($fields) == FALSE) {
			$response['success'] = false;
			$response['messages'] = $this->validation->listErrors();
		} else {

			if ($this->db->table("promo")->insert($fields)) {

				$response['success'] = true;
				$response['messages'] = 'Data berhasil diinput';
			} else {

				$response['success'] = false;
				$response['messages'] = 'Insertion error!';
			}
		}

		return $this->response->setJSON($response);
	}

	public function edit()
	{

		$response = array();
		$response['token'] = csrf_hash();

		$fields['id_proyek'] = $this->request->getPost('idProyek');
		$fields['nama_promo'] = $this->request->getPost('nama_promo');
		$fields['insentif'] = $this->request->getPost('insentif');
		$fields['diskon'] = $this->num($this->request->getPost('diskon'));
		$fields['tgl_berlaku_start'] = $this->request->getPost('tgl_berlaku_start');
		$fields['tgl_berlaku_end'] = $this->request->getPost('tgl_berlaku_end');
		$fields['tgl_booking_start'] = $this->request->getPost('tgl_booking_start');
		$fields['tgl_booking_end'] = $this->request->getPost('tgl_booking_end');
		$fields['keterangan'] = $this->request->getPost('keterangan');
		$fields['status'] = $this->request->getPost('isActive');

		$fields['edit_by'] = user_id();
		$fields['updated_at'] = date("Y-m-d H:i:s");

		$id = $this->request->getPost('id');

		$this->validation->setRules([
			'id_proyek' => ['label' => 'Id proyek', 'rules' => 'permit_empty|max_length[255]'],
			'nama_promo' => ['label' => 'Nama Promo', 'rules' => 'permit_empty|max_length[255]'],
		]);

		if ($this->validation->run($fields) == FALSE) {

			$response['success'] = false;
			$response['messages'] = $this->validation->listErrors();
		} else {

			if ($this->db->table('promo')->where('id', $id)->update($fields)) {
				$response['success'] = true;
				$response['messages'] = 'Data berhasil diperbaharui';
			} else {

				$response['success'] = false;
				$response['messages'] = 'Opps!! Terjadi kesalahan';
			}
		}

		return $this->response->setJSON($response);
	}

	public function remove()
	{
		$response = array();
		$response['token'] = csrf_hash();

		$fields['status'] = 0;

		$fields['edit_by'] = user_id();
		$fields['updated_at'] = date("Y-m-d H:i:s");

		$id = $this->request->getPost('id');

		$this->validation->setRules([
			'id_proyek' => ['label' => 'Id proyek', 'rules' => 'permit_empty|max_length[255]'],
		]);

		if ($this->validation->run($fields) == FALSE) {

			$response['success'] = false;
			$response['messages'] = $this->validation->listErrors();
		} else {

			if ($this->db->table('promo')->where('id', $id)->update($fields)) {
				$response['success'] = true;
				$response['messages'] = 'Promo berhasil dinonaktifkan';
			} else {

				$response['success'] = false;
				$response['messages'] = 'Opps!! Terjadi kesalahan';
			}
		}

		return $this->response->setJSON($response);
	}
	function if_where($var, $column, $condition, $query)
	{
		$x = 0;
		foreach ($column as $i) {
			if ($x === 0) {
				$query->like($i, $var['search']['value']);
			} else {
				$query->orLike($i, $var['search']['value']);
			}
			$query->where($condition);
			$x++;
		}
		return $query;
	}
	function is_active($id, $texts, $textf)
    {
        $r = '<span class="badge badge-pill badge-light-danger" text-capitalized="">' . $textf . '</span>';
        if ($id == "1") $r = '<span class="badge badge-pill badge-light-success" text-capitalized="">' . $texts . '</span>';
        return $r;
    }
	function format_tgl($tgl)
    {
		$v = date_parse($tgl);

        if ($tgl == "" || $tgl == "0000-00-00" || $tgl == null) return "-";
		if($v['hour'])
        	return date_format(date_create($tgl), "d-M-Y h:i:s");
		return date_format(date_create($tgl), "d-M-Y");
    }
	protected function num($d)
    {
        $d = str_replace('.', "", $d);
        $d = str_replace(',', "", $d);

        return $d;
    }
}
