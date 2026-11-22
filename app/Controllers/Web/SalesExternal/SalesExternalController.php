<?php

namespace App\Controllers\Web\SalesExternal;

use App\Controllers\BaseController;
use App\Services\FileAccessService;
use App\Services\Proyek\ProyekService;
use App\Services\SalesExternal\FeeSalesPdfService;
use App\Services\SalesExternal\FeeSalesSettingService;
use App\Services\SalesExternal\SalesExternalService;

class SalesExternalController extends BaseController
{
    public function index()
    {
        $proyekService   = new ProyekService();
        $data['content'] = 'sales/master_sales';
        $data['data']    = [
            'controller' => 'SalesExternal',
            'title'      => 'Master Sales External',
            'proyekList' => $proyekService->getAll(),
        ];

        return view('template', $data);
    }

    public function feeSales()
    {
        $settingService = new FeeSalesSettingService();
        $permissions    = $settingService->getUserPermissions((int) user_id());

        if (empty($permissions['can_view'])) {
            return redirect()->to(base_url('dashboard'))->with('error', 'Anda tidak memiliki hak akses untuk membuka modul Fee Sales.');
        }

        $proyekService   = new ProyekService();
        $data['content'] = 'sales/fee_sales';
        $data['data']    = [
            'controller'  => 'SalesExternal',
            'title'       => 'Fee Sales External',
            'proyekList'  => $proyekService->getAll(),
            'permissions' => [
                'current_user_id'   => (int) user_id(),
                'can_manage'        => (int) $permissions['can_manage'],
                'can_submit_spp'    => (int) $permissions['can_submit_spp'],
                'can_cairkan'       => (int) $permissions['can_cairkan'],
                'can_sign_mkdt'     => (int) ($permissions['can_sign_mkdt'] ?? 0),
                'can_sign_keuangan' => (int) ($permissions['can_sign_keuangan'] ?? 0),
            ],
        ];

        return view('template', $data);
    }

    /**
     * Cetak PDF Pengajuan Pencairan Fee Sales
     */
    public function pengajuanPdf(int $id)
    {
        $settingService = new FeeSalesSettingService();
        $permissions    = $settingService->getUserPermissions((int) user_id());
        if (empty($permissions['can_view'])) {
            return $this->response->setStatusCode(403)->setBody('Anda tidak memiliki hak akses untuk dokumen ini.');
        }

        try {
            $pdfService = new FeeSalesPdfService();
            $binary     = $pdfService->render($id);

            $filename = 'Pengajuan-Fee-Sales-' . $id . '.pdf';

            return $this->response
                ->setStatusCode(200)
                ->setContentType('application/pdf')
                ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
                ->setHeader('Cache-Control', 'private, no-store, max-age=0')
                ->setBody($binary);
        } catch (\Throwable $e) {
            log_message('error', 'Error generate PDF pengajuan fee: ' . $e->getMessage());
            return $this->response->setStatusCode(404)->setBody('Gagal memuat PDF pengajuan fee: ' . $e->getMessage());
        }
    }

    /**
     * Tampilkan gambar tanda tangan pengajuan fee sales
     */
    public function pengajuanSignature(int $id, string $role)
    {
        $settingService = new FeeSalesSettingService();
        $permissions    = $settingService->getUserPermissions((int) user_id());
        if (empty($permissions['can_view'])) {
            return $this->response->setStatusCode(403)->setBody('Akses ditolak.');
        }

        $salesService = new SalesExternalService();
        $group        = $salesService->getPengajuanGroupDetail($id);
        if (! $group) {
            return $this->response->setStatusCode(404)->setBody('Pengajuan tidak ditemukan.');
        }

        $role  = strtolower($role);
        $field = match ($role) {
            'pembuat'  => 'ttd_pembuat_path',
            'mkdt'     => 'ttd_mkdt_path',
            'keuangan' => 'ttd_keuangan_path',
            default    => null,
        };

        if (! $field || empty($group->$field)) {
            return $this->response->setStatusCode(404)->setBody('Tanda tangan belum tersedia.');
        }

        $fileAccess = new FileAccessService();
        $path       = $fileAccess->existingPath($group->$field) ?: $fileAccess->privatePath($group->$field);

        if (! is_file($path)) {
            // Fallback gambar transparan 1x1 px jika file fisik tidak ada di disk (misal dev/staging/dummy)
            $transparentPng = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAA=');
            return $this->response
                ->setStatusCode(200)
                ->setContentType('image/png')
                ->setHeader('Cache-Control', 'no-cache')
                ->setBody($transparentPng);
        }

        $mime = mime_content_type($path) ?: 'image/png';
        return $this->response
            ->setStatusCode(200)
            ->setContentType($mime)
            ->setHeader('Cache-Control', 'private, max-age=86400')
            ->setBody(file_get_contents($path));
    }

    /**
     * Halaman shareable pengajuan fee sales (mobile-friendly)
     */
    public function pengajuanShared(string $token)
    {
        $settingService = new FeeSalesSettingService();
        $permissions    = $settingService->getUserPermissions((int) user_id());
        if (empty($permissions['can_view'])) {
            return redirect()->to(base_url('dashboard'))->with('error', 'Anda tidak memiliki hak akses untuk melihat pengajuan ini.');
        }

        $salesService = new SalesExternalService();
        $group        = $salesService->getPengajuanGroupByToken($token);
        if (! $group) {
            return redirect()->to(base_url('fee-sales'))->with('error', 'Pengajuan tidak ditemukan atau link sudah tidak berlaku.');
        }

        $salesGroups = [];
        foreach ($group->items ?? [] as $item) {
            $salesKey = ($item->sales_nama ?? '') ?: (($item->sales_keterangan ?? '') ?: 'Sales Tanpa Nama');
            $salesGroups[$salesKey][] = $item;
        }

        $data['content'] = 'sales/pengajuan_fee_shared';
        $data['data']    = [
            'controller'  => 'SalesExternal',
            'title'       => 'Detail Pengajuan Fee - ' . (($group->nomor_pengajuan ?? '') !== '' ? $group->nomor_pengajuan : ('#' . ($group->id ?? ''))),
            'group'       => $group,
            'salesGroups' => $salesGroups,
            'permissions' => [
                'current_user_id'   => (int) user_id(),
                'can_manage'        => (int) ($permissions['can_manage'] ?? 0),
                'can_submit_spp'    => (int) ($permissions['can_submit_spp'] ?? 0),
                'can_cairkan'       => (int) ($permissions['can_cairkan'] ?? 0),
                'can_sign_mkdt'     => (int) ($permissions['can_sign_mkdt'] ?? 0),
                'can_sign_keuangan' => (int) ($permissions['can_sign_keuangan'] ?? 0),
            ],
        ];

        return view('template', $data);
    }
}
