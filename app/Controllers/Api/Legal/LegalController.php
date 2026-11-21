<?php

namespace App\Controllers\Api\Legal;

use App\Controllers\Api\BaseApiController;
use App\Repositories\Legal\LegalRepository;
use App\Services\FileAccessService;
use App\Services\Legal\LegalService;

class LegalController extends BaseApiController
{
    protected LegalService $legalService;
    protected LegalRepository $legalRepository;
    protected FileAccessService $fileAccessService;
    protected $validation;

    public function __construct(
        ?LegalService $legalService = null,
        ?LegalRepository $legalRepository = null,
        ?FileAccessService $fileAccessService = null
    ) {
        $this->legalService      = $legalService ?? new LegalService();
        $this->legalRepository   = $legalRepository ?? new LegalRepository();
        $this->fileAccessService = $fileAccessService ?? new FileAccessService();
        $this->validation        = \Config\Services::validation();
    }

    public function save()
    {
        $this->validation->setRules([
            'id_kavling' => [
                'label' => 'ID Kavling',
                'rules' => 'required',
            ],
        ]);

        if ($this->validation->run($this->request->getPost()) === false) {
            return $this->error($this->validation->listErrors(), 422);
        }

        $postData = (array) $this->request->getPost();

        try {
            $this->legalService->simpan($postData);

            return $this->success(null, 'Berhasil menginput/memperbaharui data');
        } catch (\Throwable $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function getDataByID()
    {
        $idLegal   = (int) $this->request->getVar('id_legal');
        $idKavling = (int) $this->request->getVar('id_kavling');

        $legal = $idLegal > 0 ? $this->legalRepository->getLegalDataById($idLegal) : null;
        $taxes = $idKavling > 0 ? $this->legalRepository->getKavlingLegalTaxes($idKavling) : null;

        // Siapkan struktur array pajak/kavling
        $taxesArray = $taxes ? (array) $taxes : [];
        $legalArray = $legal ? (array) $legal : [];

        // Gabungkan objek data: mempertahankan properti langsung untuk jQuery lama (misal r.data.nama_konsumen)
        // sekaligus menyediakan sub-key legal & taxes untuk SPA/Vue
        $dataPayload = (object) array_merge($taxesArray, [
            'legal' => $legal,
            'taxes' => $taxes,
        ]);

        $response = [
            'success'  => true,
            'messages' => 'Data legal berhasil dimuat',
            'token'    => csrf_hash(),
            'data'     => $dataPayload,
            'legal'    => $legal,
        ];

        // Root properties legal untuk loop `for (let i in r)` di jQuery lama di siteplan/legal.js
        if (!empty($legalArray)) {
            foreach ($legalArray as $key => $val) {
                if (!array_key_exists($key, $response)) {
                    $response[$key] = $val;
                }
            }
        }

        return $this->respond($response);
    }

    public function removeDoc()
    {
        $id = $this->request->getVar('id');
        if (empty($id)) {
            return $this->error('Tidak dapat melanjutkan perintah', 400);
        }

        try {
            $this->legalService->deleteDocument($id);

            return $this->success(null, 'Berhasil menghapus data');
        } catch (\Throwable $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function getDoc()
    {
        $idKavling = (int) $this->request->getVar('id_kavling');
        $data      = $this->legalRepository->getFileDocsByKavling($idKavling, 5);
        $data      = $this->fileAccessService->addAccessUrlsToRows($data, 'file_upload');

        return $this->respond([
            'success'  => true,
            'messages' => 'Data dokumen berhasil dimuat',
            'token'    => csrf_hash(),
            'data'     => $data,
        ]);
    }

    public function upload()
    {
        $this->validation->setRules([
            'fl-file_name' => ['label' => 'Nama File', 'rules' => 'permit_empty|max_length[255]'],
            'fl-file'      => [
                'label' => 'File',
                'rules' => 'uploaded[fl-file]'
                    . '|mime_in[fl-file,application/pdf]'
                    . '|max_size[fl-file,12000]',
            ],
        ]);

        if ($this->validation->run($this->request->getPost()) === false) {
            return $this->error($this->validation->listErrors(), 422);
        }

        $data = [
            'id_group'   => 5,
            'id_kavling' => $this->request->getPost('id_kavling'),
            'kategori'   => $this->request->getPost('fl-kategori'),
            'file_name'  => $this->request->getPost('fl-file_name'),
            'keterangan' => $this->request->getPost('fl-keterangan'),
        ];

        try {
            $file = $this->request->getFile('fl-file');
            $this->legalService->uploadDocument($file, $data);

            return $this->success(null, 'Data has been inserted successfully');
        } catch (\Throwable $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function edit_others()
    {
        $this->validation->setRules([
            'id_kavling' => [
                'label' => 'No Rumah',
                'rules' => 'permit_empty|max_length[255]',
            ],
        ]);

        if ($this->validation->run($this->request->getPost()) === false) {
            return $this->error($this->validation->listErrors(), 422);
        }

        $data = [
            'legal_luas'       => $this->request->getPost('f_legal_luas'),
            'legal_keterangan' => $this->request->getPost('f_legal_keterangan'),
        ];

        try {
            $idKavling = (int) $this->request->getPost('id_kavling');
            $this->legalService->editOthers($data, $idKavling);

            return $this->success(null, 'Data berhasil diperbaharui');
        } catch (\Throwable $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function getListLegalitas()
    {
        $var = (array) $this->request->getVar();
        $var['id_proyek'] = resolve_active_proyek_id($var['id_proyek'] ?? null);

        $results         = $this->legalRepository->getDatatableLegalitasData($var);
        $recordsFiltered = $this->legalRepository->getDatatableLegalitasFilteredCount($var);
        $recordsTotal    = $this->legalRepository->getDatatableLegalitasTotalCount();

        $format = $this->request->getVar('format');
        if ($format === 'raw') {
            return $this->respond([
                'success'         => true,
                'messages'        => 'Data legalitas berhasil dimuat',
                'token'           => csrf_hash(),
                'draw'            => isset($var['draw']) ? (int) $var['draw'] : 1,
                'recordsFiltered' => $recordsFiltered,
                'recordsTotal'    => $recordsTotal,
                'data'            => $results,
            ]);
        }

        $dataArray = [];
        $no        = isset($var['start']) ? (int) $var['start'] : 0;

        foreach ($results as $key => $v) {
            $no++;

            $ops  = '<div class="btn-group">';
            $ops .= '	<button type="button" class="btn btn-sm btn-info" onclick="edit(' . $v->id_legal . ')"><i class="fa fa-edit"></i></button>';
            $ops .= '	<button type="button" class="btn btn-sm btn-danger" onclick="remove(' . $v->id_legal . ')"><i class="fa fa-trash"></i></button>';
            $ops .= '</div>';

            $dataArray[$key] = [
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
                $v->uadd_by . '<br>' . $this->format_tgl($v->created_at),
                $v->uedit_by . '<br>' . $this->format_tgl($v->updated_at),
                $ops,
            ];
        }

        return $this->respond([
            'success'         => true,
            'messages'        => 'Data legalitas berhasil dimuat',
            'token'           => csrf_hash(),
            'draw'            => isset($var['draw']) ? (int) $var['draw'] : 1,
            'recordsFiltered' => $recordsFiltered,
            'recordsTotal'    => $recordsTotal,
            'data'            => $dataArray,
        ]);
    }

    public function meta()
    {
        $activeProyekId = resolve_active_proyek_id($this->request->getVar('id_proyek'));
        $db             = \Config\Database::connect();

        $proyekList = $db->table('proyek')
            ->select('id_proyek, nama_proyek, kode_keuangan as alias')
            ->orderBy('nama_proyek', 'ASC')
            ->get()->getResult();

        $clusterList = [];
        $jalanList   = [];

        if ($activeProyekId) {
            $clusterList = $db->table('cluster')
                ->select('id_cluster, nama_cluster, id_proyek')
                ->where('id_proyek', $activeProyekId)
                ->orderBy('nama_cluster', 'ASC')
                ->get()->getResult();

            $clusterIds = array_column($clusterList, 'id_cluster');
            if (!empty($clusterIds)) {
                $jalanList = $db->table('jalan')
                    ->select('id_jalan, nama_jalan, id_cluster')
                    ->whereIn('id_cluster', $clusterIds)
                    ->orderBy('nama_jalan', 'ASC')
                    ->get()->getResult();
            }
        }

        $documentCategories = [
            'Sertifikat',
            'PBB',
            'PBG/IMB',
            'BPHTB',
            'PPH',
            'AJB/PPJB',
            'Lain-lain',
        ];

        $permissions = [
            'can_view'   => true,
            'can_manage' => in_groups(['Admin', 'Legal', 'Direksi', 1, 5, 9]),
            'can_edit'   => in_groups(['Admin', 'Legal', 1, 5]),
            'can_delete' => in_groups(['Admin', 'Legal', 1, 5]),
            'can_upload' => in_groups(['Admin', 'Legal', 1, 5]),
            'is_admin'   => in_groups(['Admin', 1]),
        ];

        return $this->success([
            'active_proyek_id'    => $activeProyekId,
            'proyek_options'      => $proyekList,
            'cluster_options'     => $clusterList,
            'jalan_options'       => $jalanList,
            'document_categories' => $documentCategories,
            'permissions'         => $permissions,
        ], 'Meta data legalitas berhasil dimuat');
    }

    protected function format_tgl($tgl)
    {
        if ($tgl == '' || $tgl == '0000-00-00' || $tgl == null) {
            return '-';
        }

        return date_format(date_create($tgl), 'd-M-Y');
    }
}
