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
        helper(['auth', 'active_proyek']);

        $this->legalService      = $legalService ?? new LegalService();
        $this->legalRepository   = $legalRepository ?? new LegalRepository();
        $this->fileAccessService = $fileAccessService ?? new FileAccessService();
        $this->validation        = \Config\Services::validation();
    }

    public function save()
    {
        if ($denied = $this->ensureMenuAccess(['/legalitas', '/siteplan'])) {
            return $denied;
        }

        $postData = $this->readRequestData();

        $this->validation->reset();
        $this->validation->setRules([
            'id_kavling' => [
                'label' => 'ID Kavling',
                'rules' => 'required|is_natural_no_zero',
            ],
        ]);

        if ($this->validation->run($postData) === false) {
            $errors  = $this->validation->getErrors();
            $message = implode(' ', $errors) ?: 'Validasi gagal.';

            return $this->error($message, 422, $errors);
        }

        try {
            $idLegal = $this->legalService->simpan($postData);

            return $this->success([
                'id_legal'   => $idLegal,
                'id_kavling' => (int) ($postData['id_kavling'] ?? 0),
            ], 'Berhasil menginput/memperbaharui data');
        } catch (\Throwable $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function getDataByID()
    {
        if ($denied = $this->ensureMenuAccess(['/legalitas', '/siteplan'])) {
            return $denied;
        }

        $input     = $this->readRequestData();
        $idLegal   = (int) ($input['id_legal'] ?? 0);
        $idKavling = (int) ($input['id_kavling'] ?? 0);

        if ($idLegal <= 0 && $idKavling > 0) {
            $idLegal = $this->legalRepository->getKavlingLegalId($idKavling) ?? 0;
        }

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
        if ($denied = $this->ensureMenuAccess(['/legalitas', '/siteplan'])) {
            return $denied;
        }

        $input = $this->readRequestData();
        $id    = $input['id'] ?? null;
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
        if ($denied = $this->ensureMenuAccess(['/legalitas', '/siteplan'])) {
            return $denied;
        }

        $input     = $this->readRequestData();
        $idKavling = (int) ($input['id_kavling'] ?? 0);
        $data      = $this->legalRepository->getFileDocsByKavling($idKavling, 5);
        $data      = $this->fileAccessService->addAccessUrlsToRows($data, 'file_upload');

        foreach ($data as $row) {
            $kategoriCode        = isset($row->kategori) && is_numeric($row->kategori) ? (int) $row->kategori : 0;
            $row->kategori       = $kategoriCode;
            $row->kategori_label = $this->legalService->getDocumentCategoryLabel($kategoriCode);
        }

        return $this->respond([
            'success'  => true,
            'messages' => 'Data dokumen berhasil dimuat',
            'token'    => csrf_hash(),
            'data'     => $data,
        ]);
    }

    public function upload()
    {
        if ($denied = $this->ensureMenuAccess(['/legalitas', '/siteplan'])) {
            return $denied;
        }

        $postData      = $this->readRequestData();
        $uploadedFiles = $this->request->getFiles();
        $fileField     = isset($uploadedFiles['fl-file'])
            ? 'fl-file'
            : (isset($uploadedFiles['file']) ? 'file' : 'fl-file');

        $this->validation->reset();
        $this->validation->setRules([
            'id_kavling'   => ['label' => 'ID Kavling', 'rules' => 'required|is_natural_no_zero'],
            'fl-file_name' => ['label' => 'Nama File', 'rules' => 'permit_empty|max_length[255]'],
            'file_name'    => ['label' => 'Nama File', 'rules' => 'permit_empty|max_length[255]'],
            $fileField     => [
                'label' => 'File',
                'rules' => "uploaded[{$fileField}]"
                    . "|mime_in[{$fileField},application/pdf,image/jpeg,image/png,image/webp]"
                    . "|max_size[{$fileField},12000]",
            ],
        ]);

        if ($this->validation->run($postData) === false) {
            $errors  = $this->validation->getErrors();
            $message = implode(' ', $errors) ?: 'Validasi gagal.';

            return $this->error($message, 422, $errors);
        }

        $kategori   = $postData['kategori'] ?? $postData['fl-kategori'] ?? 0;
        $fileName   = (string) ($postData['file_name'] ?? $postData['fl-file_name'] ?? '');
        $keterangan = (string) ($postData['keterangan'] ?? $postData['fl-keterangan'] ?? '');

        $data = [
            'id_group'   => 5,
            'id_kavling' => (int) ($postData['id_kavling'] ?? 0),
            'kategori'   => is_numeric($kategori) ? (int) $kategori : 0,
            'file_name'  => $fileName,
            'keterangan' => $keterangan,
        ];

        try {
            $file  = $this->request->getFile($fileField);
            $docId = $this->legalService->uploadDocument($file, $data);

            return $this->success([
                'id'             => $docId,
                'id_kavling'     => $data['id_kavling'],
                'kategori'       => $data['kategori'],
                'kategori_label' => $this->legalService->getDocumentCategoryLabel($data['kategori']),
            ], 'Data has been inserted successfully');
        } catch (\Throwable $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function edit_others()
    {
        if ($denied = $this->ensureMenuAccess(['/legalitas', '/siteplan'])) {
            return $denied;
        }

        $postData = $this->readRequestData();

        $this->validation->reset();
        $this->validation->setRules([
            'id_kavling' => [
                'label' => 'No Rumah',
                'rules' => 'required|is_natural_no_zero',
            ],
        ]);

        if ($this->validation->run($postData) === false) {
            $errors  = $this->validation->getErrors();
            $message = implode(' ', $errors) ?: 'Validasi gagal.';

            return $this->error($message, 422, $errors);
        }

        $data = [
            'legal_luas'       => $postData['f_legal_luas'] ?? $postData['legal_luas'] ?? null,
            'legal_keterangan' => $postData['f_legal_keterangan'] ?? $postData['legal_keterangan'] ?? null,
        ];

        try {
            $idKavling = (int) ($postData['id_kavling'] ?? 0);
            $this->legalService->editOthers($data, $idKavling);

            return $this->success(null, 'Data berhasil diperbaharui');
        } catch (\Throwable $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function getListLegalitas()
    {
        if ($denied = $this->ensureMenuAccess(['/legalitas', '/siteplan'])) {
            return $denied;
        }

        $var              = $this->readRequestData();
        $var['id_proyek'] = resolve_active_proyek_id($var['id_proyek'] ?? null);
        $idProyek         = !empty($var['id_proyek']) ? (int) $var['id_proyek'] : null;

        $statusLegal = (string) ($var['status_legal'] ?? 'has_legal');
        if (!in_array($statusLegal, ['has_legal', 'empty_legal', 'all'], true)) {
            $statusLegal = 'has_legal';
        }
        $var['status_legal'] = $statusLegal;

        if ($this->isRawFormat()) {
            $page          = max(1, (int) ($var['page'] ?? 1));
            $perPage       = max(1, min(100, (int) ($var['per_page'] ?? 15)));
            $var['start']  = ($page - 1) * $perPage;
            $var['length'] = $perPage;

            $results         = $this->legalRepository->getDatatableLegalitasData($var);
            $recordsFiltered = $this->legalRepository->getDatatableLegalitasFilteredCount($var);
            $recordsTotal    = $this->legalRepository->getDatatableLegalitasTotalCount($idProyek, $statusLegal);
            $lastPage        = max(1, (int) ceil($recordsFiltered / $perPage));

            return $this->success([
                'items' => $results,
                'meta'  => [
                    'page'             => $page,
                    'per_page'         => $perPage,
                    'total'            => $recordsFiltered,
                    'last_page'        => $lastPage,
                    'records_total'    => $recordsTotal,
                    'records_filtered' => $recordsFiltered,
                ],
            ], 'Data legalitas berhasil dimuat');
        }

        $results         = $this->legalRepository->getDatatableLegalitasData($var);
        $recordsFiltered = $this->legalRepository->getDatatableLegalitasFilteredCount($var);
        $recordsTotal    = $this->legalRepository->getDatatableLegalitasTotalCount($idProyek, $statusLegal);

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
        if ($denied = $this->ensureMenuAccess(['/legalitas', '/siteplan'])) {
            return $denied;
        }

        $input          = $this->readRequestData();
        $activeProyekId = resolve_active_proyek_id($input['id_proyek'] ?? null);

        $proyekList  = $this->legalRepository->getProyekOptions();
        $clusterList = $this->legalRepository->getClusterOptionsByProyek($activeProyekId);
        $clusterIds  = array_map(static fn (object $c): int => (int) $c->id_cluster, $clusterList);
        $jalanList   = $this->legalRepository->getJalanOptionsByClusterIds($clusterIds);

        $documentCategories = $this->legalService->getDocumentCategories();

        $permissions = [
            'can_view'   => true,
            'can_manage' => in_groups(['Admin', 'Legal', 'Direksi', 1, 5, 9, '1', '5', '9']),
            'can_edit'   => in_groups(['Admin', 'Legal', 1, 5, '1', '5']),
            'can_delete' => in_groups(['Admin', 'Legal', 1, 5, '1', '5']),
            'can_upload' => in_groups(['Admin', 'Legal', 1, 5, '1', '5']),
            'is_admin'   => in_groups(['Admin', 1, '1']),
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
