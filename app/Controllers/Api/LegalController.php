<?php

namespace App\Controllers\Api;

use App\Services\LegalService;
use App\Services\FileAccessService;
use App\Repositories\LegalRepository;

class LegalController extends BaseApiController
{
    protected $legalService;
    protected $legalRepository;
    protected $fileAccessService;
    protected $validation;

    public function __construct()
    {
        $this->legalService = new LegalService();
        $this->legalRepository = new LegalRepository();
        $this->fileAccessService = new FileAccessService();
        $this->validation =  \Config\Services::validation();
    }

    public function save()
    {
        // Validasi wajib id_kavling
        $this->validation->setRules([
            'id_kavling' => [
                'label' => 'ID Kavling', 
                'rules' => 'required'
            ]
        ]);

        if ($this->validation->run($this->request->getPost()) == FALSE) {
            return $this->error($this->validation->listErrors());
        }

        $f['data'] = $this->request->getPost();

        try {
            $this->legalService->simpan($f['data']);
            return $this->success(null, 'Berhasil menginput/memperbaharui data');
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function getDataByID()
    {
        $id_legal = $this->request->getVar('id_legal');
        $id_kavling = $this->request->getVar('id_kavling');
        
        $r = (object)[];
        $r->token = csrf_hash();
        if ($id_legal) {
            $r = $this->legalRepository->getLegalDataById($id_legal);
            $r->token = csrf_hash();
        } 
        $r->data = $this->legalRepository->getKavlingLegalTaxes($id_kavling);

        return $this->respond($r);
    }

    public function removeDoc()
    {
        $id = $this->request->getVar('id');
        if ($id == '') {
            return $this->error("Tidak dapat melanjutkan perintah");
        }

        try {
            $this->legalService->deleteDocument($id);
            return $this->success(null, "Berhasil menghapus data");
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function getDoc()
    {
        $data = $this->legalRepository->getFileDocsByKavling($this->request->getVar('id_kavling'), 5);
        $data = $this->fileAccessService->addAccessUrlsToRows($data, 'file_upload');

        return $this->respond([
            'token' => csrf_hash(),
            'data' => $data
        ]);
    }

    public function upload()
    {
        $this->validation->setRules([
            'fl-file_name' => ['label' => 'Nama File', 'rules' => 'permit_empty|max_length[255]'],
            'fl-file' => [
                'label' => 'File',
                'rules' => 'uploaded[fl-file]'
                    . '|mime_in[fl-file,application/pdf]'
                    . '|max_size[fl-file,12000]',
            ],
        ]);

        if ($this->validation->run($this->request->getPost()) == FALSE) {
            return $this->error($this->validation->listErrors());
        }

        $data = [
            'id_group' => 5,
            'id_kavling' => $this->request->getPost('id_kavling'),
            'kategori' => $this->request->getPost('fl-kategori'),
            'file_name' => $this->request->getPost('fl-file_name'),
            'keterangan' => $this->request->getPost('fl-keterangan')
        ];

        try {
            $file = $this->request->getFile('fl-file');
            $this->legalService->uploadDocument($file, $data);
            return $this->success(null, 'Data has been inserted successfully');
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function edit_others()
    {
        $this->validation->setRules([
            'id_kavling' => [
                'label' => 'No Rumah', 
                'rules' => 'permit_empty|max_length[255]'
            ]
        ]);

        if ($this->validation->run($this->request->getPost()) == FALSE) {
            return $this->error($this->validation->listErrors());
        }

        $data = [
            'legal_luas' => $this->request->getPost('f_legal_luas'),
            'legal_keterangan' => $this->request->getPost('f_legal_keterangan')
        ];

        try {
            $this->legalService->editOthers($data, $this->request->getPost('id_kavling'));
            return $this->success(null, 'Data berhasil diperbaharui');
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function getListLegalitas()
    {
        $var = $this->request->getVar();
        $var['id_proyek'] = resolve_active_proyek_id($var['id_proyek'] ?? null);
        
        $results = $this->legalRepository->getDatatableLegalitasData($var);
        $recordsFiltered = $this->legalRepository->getDatatableLegalitasFilteredCount($var);
        $recordsTotal = $this->legalRepository->getDatatableLegalitasTotalCount();

        $data_array = [];
        $no = isset($var['start']) ? $var['start'] : 0;
        foreach ($results as $key => $v) {
            $no++;

            $ops = '<div class="btn-group">';
            $ops .= '	<button type="button" class="btn btn-sm btn-info" onclick="edit(' . $v->id_legal . ')"><i class="fa fa-edit"></i></button>';
            $ops .= '	<button type="button" class="btn btn-sm btn-danger" onclick="remove(' . $v->id_legal . ')"><i class="fa ' . $no . '"></i></button>';
            $ops .= '</div>';

            $data_array[$key] = array(
                $no,
                $v->nama_jalan,
                $v->no_kavling,
                $v->tipe_rumah,
                $v->sertifikat_split_no_hgb_induk,
                $v->sertifikat_split_no_hgb,
                $v->sertifikat_split_nib,
                $v->sertifikat_is_balik_nama,
                $v->pbb_pecah_nop,
                $this->format_tgl($v->bphtb_tanggal_verifikasi),
                $this->format_tgl($v->bphtb_tanggal_validasi),
                $v->pbg_no,
                $this->format_tgl($v->pph_tgl_permohonan),
                $v->ajb_no,
                $v->ppjb_no,
                $v->nama_konsumen,
                $v->uadd_by . "<br>" .$this->format_tgl($v->created_at),
                $v->uedit_by. "<br>" . $this->format_tgl($v->updated_at),
                $ops
            );
        }

        return $this->respond([
            'token' => csrf_hash(),
            'draw' => isset($var['draw']) ? $var['draw'] : 1,
            'data' => $data_array,
            'recordsFiltered' => $recordsFiltered,
            'recordsTotal' => $recordsTotal
        ]);
    }

    protected function format_tgl($tgl)
    {
        if ($tgl == "" || $tgl == "0000-00-00" || $tgl == null) return "-";
        return  date_format(date_create($tgl), "d-M-Y");
    }
}
