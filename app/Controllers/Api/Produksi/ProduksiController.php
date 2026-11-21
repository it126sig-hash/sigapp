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
        $userId  = (int) user_id();
        $roleIds = function_exists('user') && user()
            ? array_map('intval', array_keys(user()->getRoles()))
            : [];

        $canManage = in_array(1, $roleIds, true) || in_array(7, $roleIds, true) || in_array(8, $roleIds, true);
        $canPay    = in_array(1, $roleIds, true) || in_array(7, $roleIds, true);

        $proyekList = $this->activeProyekService->getAccessibleList($userId);
        $activeProyek = $this->activeProyekService->getActive();
        $subkonList = $this->repo->getSubkonList('');

        $metaData = [
            'active_proyek'           => $activeProyek,
            'accessible_proyek'       => $proyekList,
            'subkon_options'          => $subkonList,
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
                'can_manage_progress' => $canManage,
                'can_pay_subkon'      => $canPay,
                'can_handle_komplain' => $canPay,
                'can_manage_slf'      => $canPay,
            ],
        ];

        return $this->success($metaData, 'Metadata produksi berhasil dimuat');
    }

    public function get_data_by_id(): ResponseInterface
    {
        $idKavling  = (int) ($this->request->getVar('id_kavling') ?? 0);
        $idProduksi = $this->request->getVar('id_produksi') !== null
            ? (int) $this->request->getVar('id_produksi')
            : null;

        $result = $this->produksiService->getDetailByKavling($idKavling, $idProduksi);
        $result['token'] = csrf_hash();

        return $this->response->setJSON($result);
    }

    public function history(): ResponseInterface
    {
        $idKavling = (int) ($this->request->getVar('id_kavling') ?? 0);
        $limit     = (int) ($this->request->getVar('history_limit') ?? 10);
        $offset    = (int) ($this->request->getVar('history_offset') ?? 0);

        $result = $this->produksiService->getHistory($idKavling, $limit, $offset);
        $result['token'] = csrf_hash();

        return $this->response->setJSON($result);
    }

    public function getBayarProduksiListItem(): ResponseInterface
    {
        $search = trim((string) ($this->request->getVar('search') ?? ''));

        return $this->response->setJSON([
            'token'     => csrf_hash(),
            'list_item' => $this->repo->getBayarItemList($search),
        ]);
    }

    public function getBayarProduksi(): ResponseInterface
    {
        $idKavling = (int) ($this->request->getVar('id_kavling') ?? 0);
        $result = $this->produksiService->getBayarProduksiData($idKavling);
        $result['token'] = csrf_hash();

        return $this->response->setJSON($result);
    }

    public function saveBayarProduksi(): ResponseInterface
    {
        $idKavling      = (int) ($this->request->getVar('id_kavling') ?? 0);
        $idItemProduksi = (int) ($this->request->getVar('bp-untuk_pembayaran') ?? 0);
        $tanggalBayar   = trim((string) ($this->request->getVar('bp-tanggal_bayar') ?? ''));
        $nominal        = $this->request->getVar('bp-nominal');
        $keterangan     = (string) ($this->request->getVar('bp-keterangan') ?? '');
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
        $id     = (int) ($this->request->getVar('id') ?? 0);
        $userId = (int) user_id();

        $result = $this->produksiService->deleteBayarProduksi($id, $userId);
        $result['token'] = csrf_hash();

        return $this->response->setJSON($result);
    }

    public function get_data_komplain_by_id(): ResponseInterface
    {
        $idKavling = (int) ($this->request->getVar('id_kavling') ?? 0);
        $result    = $this->produksiService->getKomplainDetail($idKavling);
        $result['token'] = csrf_hash();

        return $this->response->setJSON($result);
    }

    public function save_komplain_produksi(): ResponseInterface
    {
        $data          = (array) $this->request->getVar();
        $uploadedFiles = (array) $this->request->getFiles();
        $userId        = (int) user_id();

        $result = $this->produksiService->saveKomplainProduksi($data, $uploadedFiles, $userId);
        $result['token'] = csrf_hash();

        return $this->response->setJSON($result);
    }

    public function save(): ResponseInterface
    {
        $userId = (int) user_id();
        $result = $this->produksiService->save(
            (array) $this->request->getVar(),
            (array) $this->request->getFiles(),
            $userId
        );

        $result['token'] = csrf_hash();
        return $this->response->setJSON($result);
    }

    public function uploadMobile(): ResponseInterface
    {
        $idKavling  = (int) ($this->request->getVar('id_kavling') ?? 0);
        $idProduksi = $this->request->getVar('id_produksi') !== null
            ? (int) $this->request->getVar('id_produksi')
            : null;
        $files      = (array) $this->request->getFiles();
        $vars       = (array) $this->request->getVar();
        $userId     = (int) user_id();

        $result = $this->produksiService->saveMobileUpload($idKavling, $idProduksi, $files, $vars, $userId);
        $result['token'] = csrf_hash();

        return $this->response->setJSON($result);
    }

    public function saveSLf(): ResponseInterface
    {
        $userId  = (int) user_id();
        $success = $this->produksiService->saveSlf((array) $this->request->getVar(), $userId);

        return $this->response->setJSON([
            'token'    => csrf_hash(),
            'success'  => $success,
            'messages' => $success ? 'Berhasil menyimpan data' : 'Gagal menyimpan data',
        ]);
    }

    public function getSlf(): ResponseInterface
    {
        return $this->response->setJSON(['data' => $this->repo->getSlfList()]);
    }

    public function hapusSLF(): ResponseInterface
    {
        $id = (int) ($this->request->getPost('id') ?? 0);

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

    public function getSLFPDF($id)
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

        $filename = date('y-m-d-H-i-s') . '- SLF';
        $html     = [view('pdf/slf', $data), view('pdf/slf_page3', $data)];

        $this->mpdf->generate($html, $filename, '');
        exit();
    }

    public function getKavling(): ResponseInterface
    {
        $idProyek  = (int) ($this->request->getVar('id_proyek') ?? 0);
        $search    = (string) ($this->request->getVar('search') ?? '');
        $idKavling = (int) ($this->request->getVar('id_kavling') ?? 0);

        return $this->response->setJSON([
            'token' => csrf_hash(),
            'data'  => $this->repo->getKavlingByProyek($idProyek, $search, $idKavling > 0 ? $idKavling : null),
        ]);
    }

    public function hapus_foto(): ResponseInterface
    {
        $id     = (int) ($this->request->getPost('id') ?? 0);
        $userId = (int) user_id();

        $result = $this->produksiService->deleteFoto($id, $userId);
        $result['token'] = csrf_hash();

        return $this->response->setJSON($result);
    }

    public function get_gambarkerja(): ResponseInterface
    {
        $id = (int) ($this->request->getVar('id_gambar_kerja') ?? 0);
        $r  = $this->repo->getGambarKerjaById($id);

        if (!$r) {
            return $this->response->setStatusCode(404)->setJSON([
                'success'  => false,
                'messages' => 'File tidak ditemukan',
                'token'    => csrf_hash(),
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'lokasi'  => $this->fileAccessService->accessUrl('gambar_kerja', $id, true),
            'token'   => csrf_hash(),
        ]);
    }

    public function add_jalan(): ResponseInterface
    {
        $roleIds = function_exists('user') && user()
            ? array_map('intval', array_keys(user()->getRoles()))
            : [];

        if (!in_array(1, $roleIds, true) && !in_array(7, $roleIds, true)) {
            return $this->response->setJSON([
                'token'    => csrf_hash(),
                'success'  => false,
                'messages' => 'Kamu tidak memiliki akses untuk menambahkan jalan produksi',
            ]);
        }

        $userId = (int) user_id();
        $result = $this->produksiService->addJalan((array) $this->request->getPost(), $userId);
        $result['token'] = csrf_hash();

        return $this->response->setJSON($result);
    }

    public function edit_others(): ResponseInterface
    {
        $id            = (int) ($this->request->getPost('id_kavling') ?? 0);
        $postVars      = (array) $this->request->getPost();
        $uploadedFiles = (array) $this->request->getFiles();
        $userId        = (int) user_id();

        $result = $this->produksiService->editOthers($id, $postVars, $uploadedFiles, $userId);
        $result['token'] = csrf_hash();

        return $this->response->setJSON($result);
    }
}
