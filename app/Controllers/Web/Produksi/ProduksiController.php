<?php

namespace App\Controllers\Web\Produksi;

use App\Controllers\BaseController;
use App\Libraries\Mpdf_lib;
use App\Repositories\Produksi\ProduksiRepository;
use App\Services\Proyek\ActiveProyekService;

class ProduksiController extends BaseController
{
    protected ProduksiRepository $repo;
    protected ActiveProyekService $activeProyekService;
    protected Mpdf_lib $mpdf;

    public function __construct(
        ?ProduksiRepository $repo = null,
        ?ActiveProyekService $activeProyekService = null,
        ?Mpdf_lib $mpdf = null
    ) {
        $this->repo                = $repo ?? new ProduksiRepository();
        $this->activeProyekService = $activeProyekService ?? new ActiveProyekService();
        $this->mpdf                = $mpdf ?? new Mpdf_lib();
    }

    public function index()
    {
        return redirect()->to(base_url('siteplan'));
    }

    public function produksiMobile()
    {
        $idProyek = $this->activeProyekService->getActiveId();

        if ($idProyek === null) {
            return redirect()->to(base_url('dashboard'))->with('error', 'Silahkan pilih proyek terlebih dahulu');
        }

        $proyek = $this->activeProyekService->getActive();
        $userId = (int) user_id();

        $hasAkses = [
            'proyek'                     => true,
            'update_tanggal_pembangunan' => false,
        ];

        if (function_exists('in_groups') && in_groups(['1', '7', '8'])) {
            $hakAksesModel = new \App\Models\HakaksesModel();
            $tanggalPembangunanAkses = $hakAksesModel->getHak($userId);
            $hasUpdateAccess = array_values(array_filter($tanggalPembangunanAkses, static function ($item) {
                return ($item->nama_akses ?? '') === 'update_tanggal_pembangunan';
            }));
            $hasAkses['update_tanggal_pembangunan'] = count($hasUpdateAccess) > 0;
        }

        return view('template', [
            'content' => 'siteplan/produksi_mobile',
            'data'    => [
                'proyek'             => $proyek,
                'has_akses'          => $hasAkses,
                'initial_id_kavling' => (int) ($this->request->getGet('id_kavling') ?? 0),
            ],
        ]);
    }

    public function getSLFPDF($id)
    {
        if (!$id) {
            return false;
        }

        $listSlf = $this->repo->getSlfById((int) $id);
        if (!$listSlf) {
            return false;
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

    public function direksi()
    {
        $data['content']            = 'direksi/list-tagihan-pending';
        $data['data']['controller'] = 'Direksi';
        $data['data']['title']      = 'Monitoring Direksi';

        return view('template', $data);
    }
}
