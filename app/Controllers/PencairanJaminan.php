<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\RiwayatPencairanJaminanModel;
use App\Services\FileAccessService;

class PencairanJaminan extends BaseController
{
    protected $model;
    protected $fileAccessService;
    public function __construct()
    {
        $this->model = new RiwayatPencairanJaminanModel();
        $this->fileAccessService = new FileAccessService();
        helper(['form']);
    }

    public function index()
    {
        return view('pencairan_jaminan/index');
    }

    public function list($id_kavling)
    {
        $rows = $this->model->where('id_kavling', $id_kavling)
                            ->orderBy('created_at','DESC')
                            ->findAll();
        return $this->response->setJSON(['data'=>$rows]);
    }

    public function store()
    {
        $rules = [
            'id_kavling'        => 'required|integer',
            'tanggal_pengajuan' => 'required|valid_date[Y-m-d]',
            'keterangan'        => 'permit_empty|string',
            'status_cair'       => 'required|in_list[0,1]',
            'surat'             => 'uploaded[surat]|max_size[surat,4096]|ext_in[surat,pdf]|mime_in[surat,application/pdf,application/x-pdf,application/acrobat,applications/vnd.pdf,text/pdf,application/octet-stream]'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success'=>false,
                'errors'=>$this->validator->getErrors()
            ], 422);
        }

        // Simpan file PDF
        $file = $this->request->getFile('surat');
        $newName = $file->getRandomName();
        $suratPath = $this->fileAccessService->storeAs($file, 'uploads/pencairan', $newName);

        $data = [
            'id_kavling'        => (int)$this->request->getPost('id_kavling'),
            'tanggal_pengajuan' => $this->request->getPost('tanggal_pengajuan'),
            'keterangan'        => $this->request->getPost('keterangan'),
            'status_cair'       => (int)$this->request->getPost('status_cair'),
            'surat_path'        => $suratPath,
            'created_by'        => user_id() ?? null,
        ];
        $this->model->insert($data);

        return $this->response->setJSON(['success'=>true,'message'=>'Data tersimpan']);
    }

    public function toggleStatus($id)
    {
        $row = $this->model->find($id);
        if(!$row){
            return $this->response->setJSON(['success'=>false,'message'=>'Data tidak ditemukan'],404);
        }
        $new = $row['status_cair'] ? 0 : 1;
        $this->model->update($id, ['status_cair'=>$new,'updated_by'=>user_id() ?? null]);

        return $this->response->setJSON(['success'=>true,'status_cair'=>$new,'message'=>'Status diperbarui']);
    }

    public function download($id)
    {
        $row = $this->model->find($id);
        if (!$row) {
            return $this->response->setBody('Lampiran tidak ditemukan')->setStatusCode(404);
        }

        return redirect()->to(site_url('files/pencairan_jaminan/' . $id . '?download=1'));
    }
}
