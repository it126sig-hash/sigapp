<?php

namespace App\Controllers;

use App\Models\LegalModel;
use App\Models\KavlingModel;
use CodeIgniter\HTTP\Response;

class Legal extends BaseController
{
    protected $db;
    protected $legalModel;
    protected $kavlingModel;
    protected $validation;
    public function __construct()
    {
        $this->legalModel = new LegalModel();
        $this->kavlingModel = new KavlingModel();
        $this->validation =  \Config\Services::validation();
        $this->db = db_connect();
    }
    function get_data_by_id()
    {
        $id_legal = $this->request->getVar('id_legal');
        $id_kavling = $this->request->getVar('id_kavling');
        if ($id_legal) {
            $r = $this->legalModel
                ->where('id_legal', $id_legal)
                ->first();

            $r->data = $this->db->table('kavling')
                    ->select('
                        konsumen.nama_konsumen,
                        mkdt.harga_bphtb
                    ')
                    ->join('mkdt', 'mkdt.id_mkdt = kavling.id_mkdt', 'left')
                    ->join('konsumen', 'konsumen.id_konsumen = mkdt.id_konsumen', 'left')
                    ->where('kavling.id_kavling', $id_kavling)
                    ->get()->getRow()
                    ;
            $r->token = csrf_hash();
        } else {
            $r['token'] = csrf_hash();
        }
        return $this->response->setJSON($r);
    }

    function save()
    {
        $response['token'] = csrf_hash();

        $f['data'] = $this->request->getPost();

        // $f['id_legal'] = $this->request->getPost('id_legal');
        // $f['sertifikat_tgl'] = $this->request->getPost('sertifikat_tgl');
        // $f['sertifikat_no_hgb'] = $this->request->getPost('sertifikat_no_hgb');
        // $f['sertifikat_no_split'] = $this->request->getPost('sertifikat_no_split');
        // $f['sertifikat_masa_berlaku'] = $this->request->getPost('sertifikat_masa_berlaku');
        // $f['sertifikat_luas'] = $this->request->getPost('sertifikat_luas');
        // $f['pbb'] = $this->request->getPost('pbb');
        // $f['pbg'] = $this->request->getPost('pbg');
        // $f['imb_tgl'] = $this->request->getPost('imb_tgl');
        // $f['imb_no_induk'] = $this->request->getPost('imb_no_induk');
        // $f['imb_no_split'] = $this->request->getPost('imb_no_split');
        // $f['nop_pbb'] = $this->request->getPost('nop_pbb');
        // $f['bphtb_tgl'] = $this->request->getPost('bphtb_tgl');
        // $f['bphtb_masa_berlaku'] = $this->request->getPost('bphtb_masa_berlaku');
        // $f['bphtb_validasi'] = $this->request->getPost('bphtb_validasi');
        // $f['pph'] = $this->request->getPost('pph');
        // $f['akad_tgl'] = $this->request->getPost('legal_akad_tgl');
        // $f['keterangan'] = $this->request->getPost('legal_keterangan');

        if ($f['data']['id_legal'] == null) {
            $f['data']['add_by'] = user_id();
            $f['data']['created_at'] = date('Y-m-d H:i:s');

            if ($this->legalModel->insert($f['data'])) {
                $this->kavlingModel->update($f['data']['id_kavling'], array('id_legal' => $this->legalModel->getInsertID()));
                $response['success'] = true;
                $response['messages'] = 'Berhasil menginput data';
            } else {
                $response['success'] = false;
                $response['messages'] = 'Terjadi Kesalahan';
            }
        } else {
            $f['data']['edit_by'] = user_id();
            
            $f['data']['updated_at'] = date('Y-m-d H:i:s');

            if ($this->legalModel->update($f['data']['id_legal'], $f['data'])) {
                $response['success'] = true;
                $response['messages'] = 'Berhasil memperbaharui data';
            } else {
                $response['success'] = false;
                $response['messages'] = 'Terjadi Kesalahan';
            }
        }
        // $response['sucess'] = false;
        // $response['post'] = $f['data'];
        return $this->response->setJSON($response);
    }
    function removeDoc(){
        $r['token'] = csrf_hash();
        $id = $this->request->getVar('id') ;
        if($id == ''){
            $r['success'] = false;
            $r['messages'] = "Tidak dapat melanjutkan perintah";
            return $this->response->setJSON($r);
        }
        $l = $this->db->table('file_upload')->select('lokasi')->where('id', $id)->get()->getResult()[0];
        

        if($this->db->table('file_upload')->where('id', $id)->delete()){
            $str ='';
            if(isset($l->lokasi)){
                $str = $l->lokasi;
                $str = FCPATH . str_replace('/', '\\', $str);
                unlink($str);
            }

            $r['success'] = true;
            $r['messages'] = "Berhasil menghapus data";
            return $this->response->setJSON($r);
        }
    }
    function getDoc(){
        $r['token'] = csrf_hash();
        $r['data'] = $this->db->table('file_upload')
            ->select('
                file_upload.*,
                u.username as uadd_by
            ')
            ->join("users as u", "u.id = file_upload.upload_by", "left")
            ->where('id_kavling', $this->request->getVar('id_kavling'))
            ->get()->getResult();
        return $this->response->setJSON($r);
    }
    function upload(){
        $r = array();
		$r['token'] = csrf_hash();

        $f['id_kavling'] = $this->request->getPost('id_kavling');
        $f['kategori'] = $this->request->getPost('kategori');
        $f['file_name'] = $this->request->getPost('fl-file_name');
        $f['keterangan'] = $this->request->getPost('fl-keterangan');

        $this->validation->setRules([
			'file_name' => ['label' => 'Nama File', 'rules' => 'permit_empty|max_length[255]'],
			'file' => [
				'label' => 'File',
				'rules' => 'uploaded[fl-file]'
					. '|mime_in[fl-file,application/pdf]'
					. '|max_size[fl-file,12000]',
			],

		]);
        if ($this->validation->run($f) == FALSE) {
			$r['success'] = false;
			$r['messages'] = $this->validation->listErrors();
            return $this->response->setJSON($r);
		} else {
            $file = $this->request->getFile('fl-file');
            $originalname = $file->getClientName();
            $name = $file->getRandomName();

            $lok = 'uploads/file/' . date('Ymd') . '/';
            $file->move($lok, $name);
            $f['id'] = '';

            $f['lokasi'] = $lok . $name;
            $f['default_filename'] = $originalname;

            $f['upload_at'] = date('Y-m-d H:i:s');
			$f['upload_by'] = user_id();
        }
        $q = $this->db->table('file_upload')
            ->insert($f);

        if($q){
            $r['success'] = true;
				$r['messages'] = 'Data has been inserted successfully';
        }else{
            $r['success'] = false;
            $r['messages'] = 'Insertion error!';
        }
        return $this->response->setJSON($r);
	}
    
    function list_legalitas()
    {
        $data['content'] = 'legal/list-legalitas';
        $data['data']['controller'] = 'Legal';
        $data['data']['title'] = 'List Legalitas';

        return view('template', $data);
    }
    function getListLegalitas()
    {
        $data['token'] = csrf_hash();
        $data['data'] = array();

        $var = $this->request->getVar();

        $colum = ['nama_konsumen', 'nama_jalan',  'no_kavling'];
        $condition = [];
        //get legalitas 
        $query = $this->db->table('kavling')
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
             mkdt.akad_tgl
         ')
            ->join('legal', "kavling.id_legal = legal.id_legal")
            ->join('produksi', "kavling.id_produksi = produksi.id_produksi", 'left')
            ->join('tipe', "tipe.id_tipe = kavling.id_tipe")
            ->join('mkdt', 'mkdt.id_mkdt = kavling.id_mkdt', 'left')
            ->join('konsumen', "konsumen.id_konsumen = mkdt.id_konsumen", 'left')
            ->join('jalan', "jalan.id_jalan = kavling.id_jalan")
            ->join('cluster', "jalan.id_cluster = cluster.id_cluster")
            ->join('proyek', "proyek.id_proyek = cluster.id_proyek");

        if ($var['id_jalan'])
            $condition = array_merge($condition, ["jalan.id_jalan" => $var['id_jalan']]);
        elseif ($var['id_cluster'])
            $condition = array_merge($condition, ["cluster.id_cluster" => $var['id_cluster']]);
        else
            $condition = array_merge($condition, ["proyek.id_proyek" => $var['id_proyek']]);
        $result = $this->if_where($var, $colum, $condition, $query);

        $result
            ->offset($var['start'])
            ->limit($var['length']);

        $x = $result->get();

        $data['draw'] = $var['draw'];

        //count filtered
        $countfiltered = $this->db->table("kavling")
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
                mkdt.akad_tgl
            ')
            ->join('legal', "kavling.id_legal = legal.id_legal")
            ->join('produksi', "kavling.id_produksi = produksi.id_produksi", 'left')
            ->join('tipe', "tipe.id_tipe = kavling.id_tipe")
            ->join('mkdt', 'mkdt.id_mkdt = kavling.id_mkdt', 'left')
            ->join('konsumen', "konsumen.id_konsumen = mkdt.id_konsumen", 'left')
            ->join('jalan', "jalan.id_jalan = kavling.id_jalan")
            ->join('cluster', "jalan.id_cluster = cluster.id_cluster")
            ->join('proyek', "proyek.id_proyek = cluster.id_proyek");

        // $countTotal = $countfiltered;

        $countfiltered = $this->if_where($var, $colum, $condition, $countfiltered);
        $data['recordsFiltered'] = count($countfiltered->get()->getResult());
        //count total
        $condition = [
            'kavling.id_legal !=' => null
        ];
        $countTotal =  $this->db->table("kavling")
            ->select("count(mkdt.id_mkdt) as count")
            ->join('legal', "kavling.id_legal = legal.id_legal")
            ->join('produksi', "kavling.id_produksi = produksi.id_produksi", 'left')
            ->join('tipe', "tipe.id_tipe = kavling.id_tipe")
            ->join('mkdt', 'mkdt.id_mkdt = kavling.id_mkdt', 'left')
            ->join('konsumen', "konsumen.id_konsumen = mkdt.id_konsumen", 'left')
            ->join('jalan', "jalan.id_jalan = kavling.id_jalan")
            ->join('cluster', "jalan.id_cluster = cluster.id_cluster")
            ->join('proyek', "proyek.id_proyek = cluster.id_proyek")
            ->where($condition);

        $data['recordsTotal'] =  $countTotal->get()->getResult()[0]->count;

        //looping data untuk datatable
        $no = $var['start'];
        foreach ($x->getResult() as $key => $v) {
            $no++;

            $ops = '<div class="btn-group">';
            $ops .= '	<button type="button" class="btn btn-sm btn-info" onclick="edit(' . $v->id_legal . ')"><i class="fa fa-edit"></i></button>';
            $ops .= '	<button type="button" class="btn btn-sm btn-danger" onclick="remove(' . $v->id_legal . ')"><i class="fa ' . $no . '"></i></button>';
            $ops .= '</div>';

            $data['data'][$key] = array(

                $no,
                $v->nama_jalan,
                $v->no_kavling,
                $v->tipe_rumah,
                $v->nama_konsumen,
                // $v->hp_konsumen,

                $v->sertifikat_no_hgb,
                $v->sertifikat_no_split,
                $this->format_tgl($v->sertifikat_tgl),
                $this->format_tgl($v->sertifikat_masa_berlaku),

                $this->format_tgl($v->imb_tgl),
                $v->imb_no_induk,
                $v->imb_no_split,

                $v->nop_pbb,
                $this->format_tgl($v->bphtb_tgl),
                $this->format_tgl($v->bphtb_masa_berlaku),
                $this->format_tgl($v->bphtb_validasi),
                
                number_format($v->pph),

                $this->format_tgl($v->akad_tgl),

                $v->keterangan,

                
                // number_format($v->harga_diskon),


                // $this->is_active($v->wawancara, "Sudah", "Belum"),
                // $this->format_tgl($v->wawancara_tgl),
                // $this->is_active($v->sp3k, "Sudah", "Belum"),
                // $this->format_tgl($v->sp3k_tgl),
                // $this->format_tgl($v->rencana_akad_tgl),
                // $this->is_active($v->akad, "Sudah", "Belum"),
                $v->add_by,
                $this->format_tgl($v->created_at),
                $v->edit_by,
                $this->format_tgl($v->updated_at),
                $ops
            );
        }
        return $this->response->setJSON($data);
    }
    function edit_others()
    {
        $response = array();
        $response['token'] = csrf_hash();

        $builder = $this->db->table("others");

        $fields['legal_luas'] = $this->request->getPost('f_legal_luas');
        $fields['legal_keterangan'] = $this->request->getPost('f_legal_keterangan');
        
        $fields['legal_edit_by'] = user_id();
        $fields['legal_updated_at'] = date('Y-m-d H:i:s');

        $id = $this->request->getPost('id_kavling');

        $this->validation->setRules([
            'no_kavling' => [
                'label' => 'No Rumah', 
                'rules' => 'permit_empty|max_length[255]'
            ]
        ]);

        if ($this->validation->run($fields) == FALSE) {
            $response['success'] = false;
            $response['messages'] = $this->validation->listErrors();
        } else {
            $builder->where('id', $id);
            if ($builder->update($fields)) {
                $response['success'] = true;
                $response['messages'] = 'Data berhasil diperbaharui';
            } else {
                $response['success'] = false;
                $response['messages'] = 'Data gagal diperbaharui!';
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
    function format_tgl($tgl)
    {
        if ($tgl == "" || $tgl == "0000-00-00" || $tgl == null) return "-";
        return  date_format(date_create($tgl), "d-M-Y");
    }
    function is_active($id, $texts, $textf)
    {
        $r = '<span class="badge badge-pill badge-light-danger" text-capitalized="">' . $textf . '</span>';
        if ($id == "1") $r = '<span class="badge badge-pill badge-light-success" text-capitalized="">' . $texts . '</span>';
        return $r;
    }
}
