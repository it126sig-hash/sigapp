<?php

namespace App\Controllers;

use App\Models\ProduksiModel;
use App\Models\GambarkerjaModel;
use App\Models\ChecklistSubItemModel;
use CodeIgniter\HTTP\Response;
use App\Models\KavlingModel;
use App\Models\KomplainModel;
use App\Models\SerahTerimaModel;
use App\Models\ChecklistWorkModel;
use App\Services\FileAccessService;

class Sales extends BaseController
{
    protected $fileAccessService;

    public function __construct()
    {
        $this->produksiModel = new ProduksiModel();
        $this->gambarkerjaModel = new GambarkerjaModel();
        $this->kavlingModel = new KavlingModel();
        $this->siModel = new ChecklistSubItemModel();
        $this->cwModel = new ChecklistWorkModel();
        $this->stModel = new SerahTerimaModel();
        $this->validation =  \Config\Services::validation();
        $this->komplainModel = new KomplainModel();
        $this->fileAccessService = new FileAccessService();
    }
    function get_data_by_id()
    {
        if ($this->request->getVar('id_kavling')) {
            $r['kav'] = $this->kavlingModel
                ->select('is_checked')
                ->where('id_kavling', $this->request->getVar('id_kavling'))
                ->first();
            $r['cl'] = $this->cwModel
                ->select("checklist_work.*, users.username as username_prod, c.username as username_sales")
                ->where('id_kavling', $this->request->getVar('id_kavling'))
                ->join('users', 'checklist_work.produksi_cek = users.id', 'left')
                ->join('users as c', 'checklist_work.sales_cek = c.id', 'left')
                ->findAll();

            $r['token'] = csrf_hash();
        } else {
            $r['token'] = csrf_hash();
        }
        return $this->response->setJSON($r);
    }

    function save_checklist()
    {
        $response['token'] = csrf_hash();

        $f['is_checked'] = $this->request->getVar('is_checked');

        //get data ceklist yang sudah ada di tb cheklist_work
        $get_cw = $this->cwModel->select('id_subitem')->where('id_kavling', $this->request->getVar('id_kavling'))->findAll();

        $sub = $this->siModel->select('id_subitem')->findAll();
        foreach ($sub as $s) {

            //cek apakah subitem cheklist sudah ada di tb checklist_work
            $a = $this->findObjectById($s->id_subitem, $get_cw);

            $hct = isset($this->request->getVar('hasil_cek_t_s')[$s->id_subitem]) ? 1 : 0;
            $hcf = isset($this->request->getVar('hasil_cek_f_s')[$s->id_subitem]) ? 1 : 0;
            $hcv = isset($this->request->getVar('hasil_cek_v_s')[$s->id_subitem]) ? 1 : 0;
            $kcp = $this->request->getVar('keterangan_cek_sales')[$s->id_subitem];

            $f2 = array();

            if ($hct != "" || $hcf != "" || $hcv != "") {

                $f2['sales_cek'] = user_id();
                $f2['sales_cek_tgl'] = date('Y-m-d');
                $f2['keterangan_cek_sales'] = $kcp;
                $f2['hasil_cek_t_s'] = $hct;
                $f2['hasil_cek_f_s'] = $hcf;
                $f2['hasil_cek_v_s'] = $hcv;

                if ($a) {
                    $u = $this->cwModel
                        ->set($f2)
                        ->where('id_kavling', $this->request->getVar('id_kavling'))
                        ->where('id_subitem', $s->id_subitem);
                    $u->update();
                } else {
                    $f2['id_kavling'] = $this->request->getVar('id_kavling');
                    $f2['id_subitem'] = $s->id_subitem;

                    $this->cwModel->insert($f2);
                }
            }
        }
        if ($this->kavlingModel->set($f)->where('id_kavling', $this->request->getVar('id_kavling'))->update()) {
            $response['success'] = true;
            $response['messages'] = 'Successfully updated';
        } else {
            $response['success'] = false;
            $response['messages'] = 'Terjadi Kesalahan';
        }


        return $this->response->setJSON($response);
    }
    function get_data_komplain_by_id()
    {
        if ($this->request->getVar('id_kavling')) {
            $r['komplain'] = $this->kavlingModel
                ->select('
                    status_komplain,
                    kavling.id_kavling, 
                    komplain.*,
                    s.username as username_komplain_oleh,
                    p.username as username_ditangani_oleh,
                    ss.username as username_selesai_oleh_sales,
                    sp.username as username_selesai_oleh_produksi,
                    lu.username as username_last_update
                ')
                ->join('komplain', 'komplain.id_komplain = kavling.id_komplain')
                ->join('users as s', 's.id = komplain.komplain_oleh', 'left')
                ->join('users as p', 'p.id = komplain.ditangani_oleh', 'left')
                ->join('users as ss', 'ss.id = komplain.selesai_oleh_sales', 'left')
                ->join('users as sp', 'sp.id = komplain.selesai_oleh_produksi', 'left')
                ->join('users as lu', 'lu.id = komplain.edit_by', 'left')
                ->where('id_kavling', $this->request->getVar('id_kavling'))
                ->first();
            if ($r['komplain']) {
                $r['komplain']->upload_komplain_sales_urls = $this->fileAccessService->pathUrlsFromDelimitedString($r['komplain']->upload_komplain_sales, 'komplain_sales');
                $r['komplain']->upload_komplain_produksi_urls = $this->fileAccessService->pathUrlsFromDelimitedString($r['komplain']->upload_komplain_produksi, 'komplain_produksi');
            }
            $r['token'] = csrf_hash();
        } else {
            $r['token'] = csrf_hash();
        }
        return $this->response->setJSON($r);
    }
    function batalkan_komplain()
    {
        $response['token'] = csrf_hash();
        $id = $this->request->getVar('id_kavling');
        $id_komplain = $this->request->getVar('id_komplain');
        if ($id) {
            $this->kavlingModel->update($id, [
                'id_komplain' => null
            ]);
            $this->komplainModel->delete($id_komplain);
            $response['success'] = true;
            $response['messages'] = "Komplain berhasil dibatalkan";
        } else {
            $response['success'] = false;
            $response['messages'] = "terjadi kesalahan";
        }
        return $this->response->setJSON($response);
    }
    function save_komplain_sales()
    {
        $response['token'] = csrf_hash();
        $id_kavling = $this->request->getVar('id_kavling');
        $is_selesai_sales = $this->request->getVar('is_selesai_sales');
        $f['id_komplain'] = $this->request->getVar('id_komplain');
        $f['edit_by'] = user_id();


        if ($is_selesai_sales == 1) {
            $f['selesai_keterangan_sales'] = $this->request->getVar('selesai_keterangan_sales');

            $this->validation->setRules([
                'selesai_keterangan_sales' => [
                    'label' => 'Keterangan',
                    'rules' => 'required|max_length[255]',
                    'errors' => [
                        'required' => 'Keterangan harus diisi'
                    ]
                ]
            ]);

            if ($this->validation->run($f) == false) {
                $response['success'] = false;
                $response['messages'] = $this->validation->listErrors();
            } else {

                $f['is_selesai_sales'] = 1;
                $f['selesai_oleh_sales'] = user_id();
                $f['selesai_tgl_sales'] = date("Y-m-d");

                if ($this->komplainModel->update($f['id_komplain'], $f)) {
                    $response['success'] = true;
                    $response['messages'] = 'Successfully updated';
                } else {
                    $response['success'] = false;
                    $response['messages'] = 'Terjadi kesalahan';
                }

                // update id_komplain di tbl kavling
                $this->kavlingModel->update(
                    $id_kavling,
                    [
                        "status_komplain" => 4
                    ]
                );
            }
        } else {

            $f['keterangan_komplain'] = $this->request->getVar('keterangan_komplain');

            $this->validation->setRules([
                'keterangan_komplain' => [
                    'label' => 'Keterangan',
                    'rules' => 'required|max_length[255]',
                    'errors' => [
                        'required' => 'Keterangan harus diisi'
                    ]
                ],
                'upload_komplain_sales' => [
                    'label' => 'File',
                    'rules' => 'uploaded[upload_komplain_sales]'
                        . '|mime_in[upload_komplain_sales,image/jpg,image/jpeg,image/gif,image/png,image/webp]'
                        . '|max_size[upload_komplain_sales,12000]'
                        . '|max_dims[upload_komplain_sales,6000,6000]',
                ],
            ]);

            if ($this->validation->run($f) == false) {
                $response['success'] = false;
                $response['messages'] = $this->validation->listErrors();
            } else {
                if (!$f['id_komplain']) {
                    //jika id_serah terima tidak ada maka tambah data ke tbl komplain
                    $f['komplain_oleh'] = user_id();
                    $f['komplain_tgl'] = date("Y-m-d");
                    $f['add_by'] = user_id();

                    $f['upload_komplain_sales'] = "";

                    //upload foto komplain
                    // if ($this->request->getFileMultiple('upload_komplain_sales')) {
 
                    foreach($this->request->getFileMultiple('upload_komplain_sales') as $img)
                    {   
                        $name = $img->getRandomName();
        
                        $lok = 'uploads/komplain_sales/' . date('Ymd') . '/';
                        
                        $f['upload_komplain_sales'] .= $lok.$name.";";
                
                        $this->fileAccessService->storeAs($img, $lok, $name);
                    }
                //    }

                    if ($this->komplainModel->insert($f)) {
                        $f['id_komplain'] = $this->komplainModel->getInsertID();
                        $response['success'] = true;
                        $response['messages'] = 'Successfully updated';
                    } else {
                        $response['success'] = false;
                        $response['messages'] = 'Terjadi kesalahan';
                    }
                } else {


                    if ($this->komplainModel->update($f['id_komplain'], $f)) {
                        $response['success'] = true;
                        $response['messages'] = 'Successfully updated';
                    } else {
                        $response['success'] = false;
                        $response['messages'] = 'Terjadi kesalahan';
                    }
                }

                // update id_komplain di tbl kavling
                $this->kavlingModel->update(
                    $id_kavling,
                    [
                        "id_komplain" => $f['id_komplain'],
                        "status_komplain" => 1
                    ]
                );
            }
        }

        return $this->response->setJSON($response);
    }
    function get_data_serah_terima_by_id()
    {
        if ($this->request->getVar('id_kavling')) {
            $r['serah_terima'] = $this->kavlingModel
                ->select('
                    is_serah_terima, 
                    kavling.id_serah_terima, 
                    kavling.id_kavling, 
                    serah_terima.*,
                    username')
                ->join('serah_terima', 'serah_terima.id_serah_terima = kavling.id_serah_terima')
                ->join('users', 'users.id = serah_terima.edit_by', 'left')
                ->where('id_kavling', $this->request->getVar('id_kavling'))
                ->first();
            $r['token'] = csrf_hash();
        } else {
            $r['token'] = csrf_hash();
        }
        return $this->response->setJSON($r);
    }
    function save_serah_terima()
    {
        $response['token'] = csrf_hash();
        $id_kavling = $this->request->getVar('id_kavling');

        $f['is_serah_terima'] = ($this->request->getVar('is_serah_terima')) ? 1 : 0;
        $f['serah_terima_oleh'] = $this->request->getVar('serah_terima_oleh');
        $f['serah_terima_ke'] = $this->request->getVar('serah_terima_ke');
        $f['serah_terima_tgl'] = $this->request->getVar('serah_terima_tgl');
        $f['serah_terima_keterangan'] = $this->request->getVar('serah_terima_keterangan');

        $f['edit_by'] = user_id();

        $f['id_serah_terima'] = $this->request->getVar('id_serah_terima');

        $this->validation->setRules([
            'serah_terima_oleh' => [
                'label' => 'Oleh',
                'rules' => 'required|max_length[255]',
                'errors' => [
                    'required' => 'Form oleh (yang melakukan serah terima) harus diisi'
                ]
            ],
            'serah_terima_ke' => ['label' => 'Ke', 'rules' => 'required|max_length[255]', 'errors' => [
                'required' => 'Form Ke (yang melakukan serah terima) harus diisi'
            ]],
            'serah_terima_tgl' => ['label' => 'Tanggal Serah Terima', 'rules' => 'required|date', 'errors' => [
                'required' => 'Taggal serah terima harus diisi'
            ]]
        ]);

        if ($this->validation->run($f) == false) {
            $response['success'] = false;
            $response['messages'] = $this->validation->listErrors();
        } else {
            if (!$f['id_serah_terima']) {
                //jika id_serah terima tidak ada maka tambah data ke tbl serah terima
                $f['add_by'] = user_id();
                if ($this->stModel->insert($f)) {
                    $f['id_serah_terima'] = $this->stModel->getInsertID();
                    $response['success'] = true;
                    $response['messages'] = 'Successfully updated';
                } else {
                    $response['success'] = false;
                    $response['messages'] = 'Terjadi kesalahan';
                }
            } else {

                if ($this->stModel->update($f['id_serah_terima'], $f)) {
                    $response['success'] = true;
                    $response['messages'] = 'Successfully updated';
                } else {
                    $response['success'] = false;
                    $response['messages'] = 'Terjadi kesalahan';
                }
            }
            //update id_serah terima kavling
            $this->kavlingModel->update(
                $id_kavling,
                array(
                    "is_serah_terima" => $f['is_serah_terima'],
                    'id_serah_terima' => $f['id_serah_terima']
                )
            );
        }



        return $this->response->setJSON($response);
    }
    protected function findObjectById($id, $array = array())
    {

        foreach ($array as $element) {
            if ($id == $element->id_subitem) {
                return true;
            }
        }

        return false;
    }
}
