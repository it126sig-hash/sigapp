<?php

namespace App\Services\SalesExternal;

use App\Libraries\Mpdf_lib;
use App\Repositories\SalesExternal\SalesExternalRepository;
use App\Services\FileAccessService;
use RuntimeException;

class FeeSalesPdfService
{
    protected SalesExternalRepository $salesRepo;

    public function __construct()
    {
        $this->salesRepo = new SalesExternalRepository();
    }

    /**
     * Render PDF Pengajuan Pencairan Fee Sales Landscape A4
     */
    public function render(int $idPengajuan): string
    {
        $group = $this->salesRepo->getPengajuanGroupDetail($idPengajuan);
        if (! $group) {
            throw new RuntimeException('Data pengajuan tidak ditemukan.');
        }

        // Kelompokkan item berdasarkan Sales External
        $salesGroups = [];
        foreach ($group->items as $item) {
            $salesKey = $item->sales_nama ?: ($item->sales_keterangan ?: 'Sales Tanpa Nama');
            $salesGroups[$salesKey][] = $item;
        }

        $resolveAkadTime = static function ($item): int {
            if (! empty($item->akad_indent) && (int) $item->akad_indent === 1 && ! empty($item->akad_indent_tgl) && $item->akad_indent_tgl !== '0000-00-00') {
                $ts = strtotime($item->akad_indent_tgl);
                if ($ts !== false && $ts > 0) {
                    return $ts;
                }
            }

            if (! empty($item->akad_tgl) && $item->akad_tgl !== '0000-00-00') {
                $ts = strtotime($item->akad_tgl);
                if ($ts !== false && $ts > 0) {
                    return $ts;
                }
            }

            return PHP_INT_MAX;
        };

        foreach ($salesGroups as &$sItems) {
            usort($sItems, function ($a, $b) use ($resolveAkadTime) {
                $tglA = $resolveAkadTime($a);
                $tglB = $resolveAkadTime($b);
                if ($tglA === $tglB) {
                    return ($a->id_mkdt ?? 0) <=> ($b->id_mkdt ?? 0);
                }
                return $tglA <=> $tglB;
            });
        }
        unset($sItems);

        $fileAccess = new FileAccessService();
        $signatures = [
            'pembuat' => [
                'nama' => $group->ttd_pembuat_nama ?: ($group->creator_username ?: 'Pembuat Pengajuan'),
                'at'   => $group->ttd_pembuat_at,
                'data' => ! empty($group->ttd_pembuat_path) ? $this->signatureDataUri($fileAccess, $group->ttd_pembuat_path) : null,
            ],
            'mkdt' => [
                'nama' => $group->ttd_mkdt_nama,
                'at'   => $group->ttd_mkdt_at,
                'data' => ! empty($group->ttd_mkdt_path) ? $this->signatureDataUri($fileAccess, $group->ttd_mkdt_path) : null,
            ],
            'keuangan' => [
                'nama' => $group->ttd_keuangan_nama,
                'at'   => $group->ttd_keuangan_at,
                'data' => ! empty($group->ttd_keuangan_path) ? $this->signatureDataUri($fileAccess, $group->ttd_keuangan_path) : null,
            ],
        ];

        $html = view('sales/pdf_pengajuan_fee', [
            'group'       => $group,
            'salesGroups' => $salesGroups,
            'signatures'  => $signatures,
        ]);

        $mpdfLib = new Mpdf_lib();
        return $mpdfLib->renderBinary(
            $html,
            '',
            [8, 8, 10, 10], // margin left, right, top, bottom
            'A4-L'          // Landscape A4
        );
    }

    private function signatureDataUri(FileAccessService $fileAccess, ?string $logicalPath): ?string
    {
        if (empty($logicalPath)) {
            return null;
        }
        $fullPath = $fileAccess->existingPath($logicalPath) ?: $fileAccess->privatePath($logicalPath);
        if (! is_file($fullPath)) {
            return null;
        }
        $binary = file_get_contents($fullPath);
        if ($binary === false) {
            return null;
        }
        return 'data:image/png;base64,' . base64_encode($binary);
    }
}
