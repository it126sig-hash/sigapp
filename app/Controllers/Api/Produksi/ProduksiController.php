<?php

namespace App\Controllers\Api\Produksi;

use App\Controllers\Api\BaseApiController;
use App\Libraries\Mpdf_lib;
use App\Repositories\Produksi\ProduksiRepository;
use App\Services\FileAccessService;
use App\Services\Produksi\ProduksiFileService;
use App\Services\Produksi\ProduksiService;
use App\Services\Proyek\ActiveProyekService;
use CodeIgniter\HTTP\ResponseInterface;

class ProduksiController extends BaseApiController
{
    protected ProduksiRepository   $repo;
    protected ProduksiService      $produksiService;
    protected ProduksiFileService  $fileService;
    protected FileAccessService    $fileAccessService;
    protected ActiveProyekService  $activeProyekService;
    protected Mpdf_lib             $mpdf;

    public function __construct(
        ?ProduksiRepository   $repo = null,
        ?ProduksiService      $produksiService = null,
        ?ProduksiFileService  $fileService = null,
        ?FileAccessService    $fileAccessService = null,
        ?ActiveProyekService  $activeProyekService = null,
        ?Mpdf_lib             $mpdf = null
    ) {
        $this->repo                = $repo ?? new ProduksiRepository();
        $this->produksiService     = $produksiService ?? new ProduksiService();
        $this->fileService         = $fileService ?? new ProduksiFileService();
        $this->fileAccessService   = $fileAccessService ?? new FileAccessService();
        $this->activeProyekService = $activeProyekService ?? new ActiveProyekService();
        $this->mpdf                = $mpdf ?? new Mpdf_lib();
    }

    public function meta(): ResponseInterface
    {
        $data    = $this->readRequestData();
        $userId  = (int) (user_id() ?? 0);
        $roleIds = $this->resolveUserRoleIds($userId);

        $canManage = in_array(1, $roleIds, true) || in_array(7, $roleIds, true) || in_array(8, $roleIds, true);
        $canPay    = in_array(1, $roleIds, true) || in_array(7, $roleIds, true);
        $canUpdateTanggalPembangunan = $canManage;

        $proyekList    = $this->activeProyekService->getAccessibleList($userId);
        $activeProyek  = $this->activeProyekService->getActive();
        $requestedId   = (int) ($data['id_proyek'] ?? $this->request->getVar('id_proyek') ?? 0);
        $idProyek      = $requestedId > 0 ? $requestedId : (int) ($activeProyek->id_proyek ?? $this->activeProyekService->getActiveId() ?? 0);
        $proyekDetail  = $idProyek > 0 ? $this->repo->getProyekDetailForSlf($idProyek) : $activeProyek;
        if ($activeProyek === null && $proyekDetail !== null) {
            $activeProyek = $proyekDetail;
        }
        $subkonList    = $this->repo->getSubkonList('');

        $metaData = [
            'active_proyek'           => $activeProyek,
            'proyek_detail'           => $proyekDetail,
            'accessible_proyek'       => $proyekList,
            'subkon_options'          => $subkonList,
            'checklist_subitems'      => $this->repo->getChecklistSubItemsWithGroup(),
            'st_stages'               => [
                ['key' => 'st_0',   'label' => 'ST 0% (Fondasi)'],
                ['key' => 'st_25',  'label' => 'ST 25% (Struktur)'],
                ['key' => 'st_50',  'label' => 'ST 50% (Dinding & Atap)'],
                ['key' => 'st_75',  'label' => 'ST 75% (Finishing)'],
                ['key' => 'st_100', 'label' => 'ST 100% (Serah Terima Fisik)'],
            ],
            'status_komplain_options' => [
                ['id' => 1, 'label' => 'Pending Sales'],
                ['id' => 2, 'label' => 'Ditangani Lapangan'],
                ['id' => 3, 'label' => 'Selesai Lapangan / Produksi'],
                ['id' => 4, 'label' => 'Selesai Konsumen / Sales'],
            ],
            'permissions'             => [
                'can_manage_progress'        => $canManage,
                'can_pay_subkon'             => $canPay,
                'can_handle_komplain'        => $canPay,
                'can_manage_slf'             => $canPay,
                'update_tanggal_pembangunan' => $canUpdateTanggalPembangunan,
            ],
        ];

        return $this->success($metaData, 'Metadata produksi berhasil dimuat');
    }

    public function get_data_by_id(): ResponseInterface
    {
        $data       = $this->readRequestData();
        $idKavling  = (int) ($data['id_kavling'] ?? $this->request->getVar('id_kavling') ?? 0);
        $rawIdProd  = $data['id_produksi'] ?? $this->request->getVar('id_produksi') ?? null;
        $idProduksi = ($rawIdProd !== null && $rawIdProd !== '')
            ? (int) $rawIdProd
            : null;

        $result = $this->produksiService->getDetailByKavling($idKavling, $idProduksi);
        $result['token'] = csrf_hash();

        return $this->response->setJSON($result);
    }

    public function history(): ResponseInterface
    {
        $data      = $this->readRequestData();
        $idKavling = (int) ($data['id_kavling'] ?? $this->request->getVar('id_kavling') ?? 0);
        $limit     = (int) ($data['history_limit'] ?? $this->request->getVar('history_limit') ?? 10);
        $offset    = (int) ($data['history_offset'] ?? $this->request->getVar('history_offset') ?? 0);

        $result = $this->produksiService->getHistory($idKavling, $limit, $offset);
        $result['token'] = csrf_hash();

        return $this->response->setJSON($result);
    }

    public function getBayarProduksiListItem(): ResponseInterface
    {
        $data   = $this->readRequestData();
        $search = trim((string) ($data['search'] ?? $this->request->getVar('search') ?? ''));

        return $this->response->setJSON([
            'token'     => csrf_hash(),
            'list_item' => $this->repo->getBayarItemList($search),
        ]);
    }

    public function getBayarProduksi(): ResponseInterface
    {
        $data      = $this->readRequestData();
        $idKavling = (int) ($data['id_kavling'] ?? $this->request->getVar('id_kavling') ?? 0);
        $result = $this->produksiService->getBayarProduksiData($idKavling);
        $result['token'] = csrf_hash();

        return $this->response->setJSON($result);
    }

    public function saveBayarProduksi(): ResponseInterface
    {
        $data           = $this->readRequestData();
        $idKavling      = (int) ($data['id_kavling'] ?? $this->request->getVar('id_kavling') ?? 0);
        $idItemProduksi = (int) ($data['bp-untuk_pembayaran'] ?? $data['id_item_produksi'] ?? $this->request->getVar('bp-untuk_pembayaran') ?? 0);
        $tanggalBayar   = trim((string) ($data['bp-tanggal_bayar'] ?? $data['tanggal_bayar'] ?? $this->request->getVar('bp-tanggal_bayar') ?? ''));
        $nominal        = $data['bp-nominal'] ?? $data['nominal'] ?? $this->request->getVar('bp-nominal');
        $keterangan     = (string) ($data['bp-keterangan'] ?? $data['keterangan'] ?? $this->request->getVar('bp-keterangan') ?? '');
        $userId         = (int) user_id();

        $result = $this->produksiService->saveBayarProduksi(
            $idKavling,
            $idItemProduksi,
            $nominal,
            $tanggalBayar,
            $keterangan,
            $userId
        );
        $result['token'] = csrf_hash();

        return $this->response->setJSON($result);
    }

    public function deleteBayarProduksi(): ResponseInterface
    {
        $data   = $this->readRequestData();
        $id     = (int) ($data['id'] ?? $this->request->getVar('id') ?? 0);
        $userId = (int) user_id();

        $result = $this->produksiService->deleteBayarProduksi($id, $userId);
        $result['token'] = csrf_hash();

        return $this->response->setJSON($result);
    }

    public function get_data_komplain_by_id(): ResponseInterface
    {
        $data      = $this->readRequestData();
        $idKavling = (int) ($data['id_kavling'] ?? $this->request->getVar('id_kavling') ?? 0);
        $result    = $this->produksiService->getKomplainDetail($idKavling);
        $result['token'] = csrf_hash();

        return $this->response->setJSON($result);
    }

    public function save_komplain_produksi(): ResponseInterface
    {
        $data          = array_merge((array) $this->request->getVar(), $this->readRequestData());
        $uploadedFiles = (array) $this->request->getFiles();
        $userId        = (int) user_id();

        $result = $this->produksiService->saveKomplainProduksi($data, $uploadedFiles, $userId);
        $result['token'] = csrf_hash();

        return $this->response->setJSON($result);
    }

    public function save(): ResponseInterface
    {
        $userId = (int) user_id();
        $vars   = array_merge((array) $this->request->getVar(), $this->readRequestData());
        $result = $this->produksiService->save(
            $vars,
            (array) $this->request->getFiles(),
            $userId
        );

        $result['token'] = csrf_hash();
        return $this->response->setJSON($result);
    }

    public function uploadMobile(): ResponseInterface
    {
        $vars       = array_merge((array) $this->request->getVar(), $this->readRequestData());
        $idKavling  = (int) ($vars['id_kavling'] ?? $this->request->getVar('id_kavling') ?? 0);
        $rawIdProd  = $vars['id_produksi'] ?? $this->request->getVar('id_produksi') ?? null;
        $idProduksi = ($rawIdProd !== null && $rawIdProd !== '')
            ? (int) $rawIdProd
            : null;
        $files      = (array) $this->request->getFiles();
        $userId     = (int) user_id();

        $result = $this->produksiService->saveMobileUpload($idKavling, $idProduksi, $files, $vars, $userId);
        $result['token'] = csrf_hash();

        return $this->response->setJSON($result);
    }

    public function saveSLf(): ResponseInterface
    {
        $userId = (int) user_id();
        $vars   = array_merge((array) $this->request->getVar(), $this->readRequestData());
        if (empty($vars['id_proyek'])) {
            $vars['id_proyek'] = $this->activeProyekService->getActiveId();
        }
        $success = $this->produksiService->saveSlf($vars, $userId);

        return $this->response->setJSON([
            'token'    => csrf_hash(),
            'success'  => $success,
            'messages' => $success ? 'Berhasil menyimpan data' : 'Gagal menyimpan data',
        ]);
    }

    public function getSlf(): ResponseInterface
    {
        $data     = $this->readRequestData();
        $idProyek = (int) ($data['id_proyek'] ?? $this->request->getVar('id_proyek') ?? $this->activeProyekService->getActiveId() ?? 0);

        return $this->response->setJSON([
            'success'  => true,
            'messages' => 'Daftar SLF berhasil dimuat',
            'data'     => $this->repo->getSlfList($idProyek > 0 ? $idProyek : null),
            'token'    => csrf_hash(),
        ]);
    }

    public function hapusSLF(): ResponseInterface
    {
        $data = $this->readRequestData();
        $id   = (int) ($data['id'] ?? $data['id_slf'] ?? $this->request->getVar('id') ?? 0);

        if ($id <= 0) {
            return $this->response->setJSON([
                'token'    => csrf_hash(),
                'success'  => false,
                'status'   => 'error',
                'messages' => 'ID SLF tidak ditemukan',
                'message'  => 'ID SLF tidak ditemukan',
            ]);
        }

        $success = $this->produksiService->deleteSlf($id);

        return $this->response->setJSON([
            'token'    => csrf_hash(),
            'success'  => $success,
            'status'   => $success ? 'success' : 'error',
            'messages' => $success ? 'Data SLF berhasil dihapus' : 'Gagal menghapus data SLF',
            'message'  => $success ? 'Data SLF berhasil dihapus' : 'Gagal menghapus data SLF',
        ]);
    }

    public function getSLFPDF($id): ResponseInterface
    {
        if (!$id) {
            return $this->response->setStatusCode(400)->setJSON([
                'success'  => false,
                'messages' => 'ID SLF tidak valid',
            ]);
        }

        $listSlf = $this->repo->getSlfById((int) $id);
        if (!$listSlf) {
            return $this->response->setStatusCode(404)->setJSON([
                'success'  => false,
                'messages' => 'Data SLF tidak ditemukan',
            ]);
        }

        $idKavlingList = explode(',', $listSlf->id_kavling);

        $data = [
            'list_slf' => $listSlf,
            'kavling'  => $this->repo->getSlfKavlingData($idKavlingList),
            'mpdf'     => $this->mpdf,
        ];

        $filename = date('y-m-d-H-i-s') . '- SLF.pdf';
        $html     = [view('pdf/slf', $data), view('pdf/slf_page3', $data)];
        $pdfBytes = $this->mpdf->renderBinary($html, '');

        return $this->response
            ->setStatusCode(200)
            ->setContentType('application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
            ->setBody($pdfBytes);
    }

    public function getKavling(): ResponseInterface
    {
        $data      = $this->readRequestData();
        $idProyek  = (int) ($data['id_proyek'] ?? $this->request->getVar('id_proyek') ?? $this->activeProyekService->getActiveId() ?? 0);
        $search    = (string) ($data['search'] ?? $this->request->getVar('search') ?? '');
        $idKavling = (int) ($data['id_kavling'] ?? $this->request->getVar('id_kavling') ?? 0);

        return $this->response->setJSON([
            'token' => csrf_hash(),
            'data'  => $this->repo->getKavlingByProyek($idProyek, $search, $idKavling > 0 ? $idKavling : null),
        ]);
    }

    public function hapus_foto(): ResponseInterface
    {
        $userId = (int) (user_id() ?? 0);
        if ($userId <= 0) {
            return $this->error('Silakan login terlebih dahulu', 401);
        }

        $roleIds = $this->resolveUserRoleIds($userId);
        if (! in_array(1, $roleIds, true) && ! in_array(7, $roleIds, true)) {
            return $this->error('Anda tidak memiliki akses untuk menghapus foto produksi', 403);
        }

        $data  = $this->readRequestData();
        $rawId = $data['id'] ?? $this->request->getVar('id') ?? null;
        if ($rawId === null || $rawId === '' || filter_var($rawId, FILTER_VALIDATE_INT) === false || (int) $rawId <= 0) {
            return $this->error('ID foto tidak valid', 400, ['id' => 'ID foto harus berupa bilangan bulat positif']);
        }

        $id     = (int) $rawId;
        $result = $this->produksiService->deleteFoto($id, $userId);
        $status = (int) ($result['code'] ?? (($result['success'] ?? false) ? 200 : 400));

        if (! ($result['success'] ?? false)) {
            return $this->error((string) ($result['messages'] ?? 'Gagal menghapus foto'), $status);
        }

        return $this->success($result['data'] ?? ['id' => $id], (string) ($result['messages'] ?? 'File berhasil dihapus'), 200);
    }

    public function get_gambarkerja(): ResponseInterface
    {
        $data = $this->readRequestData();
        $id   = (int) ($data['id_gambar_kerja'] ?? $this->request->getVar('id_gambar_kerja') ?? 0);
        $r    = $this->repo->getGambarKerjaById($id);

        if (!$r) {
            return $this->response->setStatusCode(404)->setJSON([
                'success'  => false,
                'messages' => 'File tidak ditemukan',
                'token'    => csrf_hash(),
            ]);
        }

        return $this->response->setJSON([
            'success'             => true,
            'lokasi'              => $this->fileAccessService->accessUrl('gambar_kerja', $id, true),
            'signed_url'          => $this->fileAccessService->signedUrl('gambar_kerja', $id, false),
            'signed_download_url' => $this->fileAccessService->signedUrl('gambar_kerja', $id, true),
            'file_name'           => $r->default_filename ?? $r->file ?? $r->lokasi ?? null,
            'token'               => csrf_hash(),
        ]);
    }

    public function add_jalan(): ResponseInterface
    {
        $userId  = (int) user_id();
        $roleIds = function_exists('user') && user()
            ? array_map('intval', array_keys(user()->getRoles()))
            : [];
        if ($roleIds === [] && $userId > 0) {
            $roleIds = $this->fileAccessService->getUserRoles($userId);
        }

        if (!in_array(1, $roleIds, true) && !in_array(7, $roleIds, true)) {
            return $this->response->setJSON([
                'token'    => csrf_hash(),
                'success'  => false,
                'messages' => 'Kamu tidak memiliki akses untuk menambahkan jalan produksi',
            ]);
        }

        $data   = array_merge((array) $this->request->getVar(), $this->readRequestData());
        $result = $this->produksiService->addJalan($data, $userId);
        $result['token'] = csrf_hash();

        return $this->response->setJSON($result);
    }

    public function edit_others(): ResponseInterface
    {
        $postVars      = array_merge((array) $this->request->getVar(), $this->readRequestData());
        $id            = (int) ($postVars['id_kavling'] ?? $postVars['id'] ?? $this->request->getVar('id_kavling') ?? 0);
        $uploadedFiles = (array) $this->request->getFiles();
        $userId        = (int) user_id();

        $result = $this->produksiService->editOthers($id, $postVars, $uploadedFiles, $userId);
        $result['token'] = csrf_hash();

        return $this->response->setJSON($result);
    }

    private function resolveUserRoleIds(int $userId): array
    {
        $roleIds = function_exists('user') && user()
            ? array_map('intval', array_keys(user()->getRoles()))
            : [];

        if ($roleIds === [] && $userId > 0) {
            $roleIds = $this->fileAccessService->getUserRoles($userId);
        }

        return array_map('intval', $roleIds);
    }
}
