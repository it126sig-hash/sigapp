<?php

namespace App\Services;

use App\Repositories\PosisiKonsumenRepository;

use Hermawan\DataTables\DataTable;

class PosisiKonsumenService
{
    protected $db;
    protected $posisiKonsumenRepo;
    protected FileAccessService $fileAccessService;
    protected SiteplanMenuService $siteplanMenuService;
    protected ?array $actionMenuItems = null;
    protected int $actionMenuRoleId = 0;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->posisiKonsumenRepo = new PosisiKonsumenRepository();
        $this->fileAccessService = new FileAccessService();
        $this->siteplanMenuService = new SiteplanMenuService();
    }

    private function resolveCurrentRoleId(): int
    {
        if (!function_exists('user') || !user()) {
            return 0;
        }

        foreach (user()->getRoles() as $roleId => $roleName) {
            return (int) $roleId;
        }

        return 0;
    }

    private function getActionMenuItems(): array
    {
        if ($this->actionMenuItems === null) {
            $this->actionMenuRoleId = $this->resolveCurrentRoleId();
            $this->actionMenuItems = $this->siteplanMenuService->getActionItemsForList($this->actionMenuRoleId);
        }

        return $this->actionMenuItems;
    }

    private function buildPoskonRowPayload(object $row): array
    {
        return [
            'id_kavling'   => $row->id_kavling ?? null,
            'id_mkdt'      => $row->id_mkdt ?? null,
            'id_keuangan'  => $row->id_keuangan ?? null,
            'id_legal'     => $row->id_legal ?? null,
            'id_produksi'  => $row->id_produksi ?? null,
            'id_hargajual' => $row->id_hargajual ?? null,
            'id_komplain'  => $row->id_komplain ?? null,
            'nama_jalan'   => $row->nama_jalan ?? '',
            'no_kavling'   => $row->no_kavling ?? '',
            'no_tipe_rumah'=> $row->no_tipe_rumah ?? '',
            'tipe_rumah'   => $row->tipe_rumah ?? '',
            'harga_akhir'  => $row->harga_akhir ?? null,
            'kode_referal' => $row->kode_referal ?? '',
            'id_proyek'    => $row->id_proyek ?? null,
            'nama_proyek'  => $row->nama_proyek ?? '',
            'uadd_by'      => $row->uadd_by ?? '',
        ];
    }

    private function renderPoskonActionHtml(object $row): string
    {
        $idKavling = (int) ($row->id_kavling ?? 0);
        if ($idKavling <= 0) {
            return '';
        }

        $rowEncoded = rawurlencode(json_encode($this->buildPoskonRowPayload($row), JSON_UNESCAPED_UNICODE));
        $menuItems = $this->getActionMenuItems();
        $menuHtml = '';
        $lastGroup = null;

        foreach ($menuItems as $item) {
            if ($this->actionMenuRoleId === 1 && !empty($item['group_label']) && $item['group_label'] !== $lastGroup) {
                $lastGroup = $item['group_label'];
                $menuHtml .= '<div class="dropdown-header">' . esc($lastGroup) . '</div>';
            }

            $icon = !empty($item['icon'])
                ? '<i class="' . esc($item['icon']) . '"></i> '
                : '';
            $onclick = rawurlencode((string) ($item['onclick'] ?? ''));
            $menuHtml .= '<button type="button" class="dropdown-item poskon-menu-action" data-onclick="' . esc($onclick) . '" data-row="' . esc($rowEncoded) . '" data-group="' . esc((string) ($item['id_group'] ?? '')) . '">'
                . $icon . esc($item['label'] ?? '')
                . '</button>';
        }

        $dropdown = $menuHtml !== ''
            ? '<div class="btn-group ml-50">'
                . '<button type="button" class="btn btn-outline-primary btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Aksi</button>'
                . '<div class="dropdown-menu dropdown-menu-right">' . $menuHtml . '</div>'
                . '</div>'
            : '';

        return '<div class="btn-group poskon-action-cell" style="white-space:nowrap">'
            . '<button type="button" class="btn btn-info btn-sm poskon-detail-btn" data-row="' . esc($rowEncoded) . '" title="Lihat Detail">'
            . '<i class="fa fa-eye"></i></button>'
            . $dropdown
            . '</div>';
    }

    private function renderReferralQrHtml(object $row): string
    {
        $kodeReferal = trim((string) ($row->kode_referal ?? ''));
        if ($kodeReferal === '') {
            return '-';
        }

        return '<a href="javascript:void(0)" class="text-primary font-weight-bold js-referral-qr"'
            . ' data-kode-referal="' . esc($kodeReferal) . '"'
            . ' data-id-mkdt="' . esc((string) ($row->id_mkdt ?? '')) . '"'
            . ' data-id-proyek="' . esc((string) ($row->id_proyek ?? '')) . '"'
            . ' title="Generate QR Referal">'
            . '<i class="fas fa-qrcode mr-50"></i>' . esc($kodeReferal)
            . '</a>';
    }

    public function getDataTable($request, $status = null)
    {
        $status = $status ?? "Booking";
        $builder = $this->posisiKonsumenRepo->getBaseQuery($status);
        $rowNumber = (int) $request->getVar('start');
        if ($request->getVar('id_proyek'))
            $builder->where('proyek.id_proyek', $request->getVar('id_proyek'));
        if ($request->getVar('id_cluster'))
            $builder->where('cluster.id_cluster', $request->getVar('id_cluster'));
        if ($request->getVar('id_jalan'))
            $builder->where('jalan.id_jalan', $request->getVar('id_jalan'));

        if ($request->getVar('sp3k') != "")
            $builder->where('mkdt.sp3k', $request->getVar('sp3k'));
        if ($request->getVar('wawancara') != "")
            $builder->where('mkdt.wawancara', $request->getVar('wawancara'));
        if ($request->getVar('akad') != "")
            $builder->where('mkdt.akad', $request->getVar('akad'));
        if ($request->getVar('akad_indent') != "")
            $builder->where('mkdt.akad_indent', $request->getVar('akad_indent'));

        $bulan = $request->getVar('bulan');
        $tahun = $request->getVar('tahun');
        if ($bulan != "" || $tahun != "") {
            $col = $request->getVar('bulan_field') === 'akad' ? 'mkdt.akad_tgl' : 'mkdt.booking_tgl';
            if ($bulan != "")
                $builder->where("MONTH($col)", $bulan);
            if ($tahun != "")
                $builder->where("YEAR($col)", $tahun);
        }

        return DataTable::of($builder)
            ->setSearchableColumns([
                'kavling.id_kavling',
                'jalan.nama_jalan',
                'kavling.no_kavling',
                'hargajual.id_tipe',
                'konsumen.nama_konsumen',
                'konsumen.kode_referal',
                'konsumen.sales',
                'mkdt.booking_tgl',
                'mkdt.wawancara_tgl',
                'mkdt.akad_tgl',
                'mkdt.is_kpr',
                'list_bank.bank',
                'mkdt.keterangan',
                'mkdt.sp3k_tgl',
                'mkdt.sp3k_tgl_exp',
                'produksi.progres_bangunan',
                'produksi.lpa',
                'produksi.st_jalan',
                'legal.sertifikat_split_no_hgb',
                'legal.pbg_no',
                'legal.pbb_pecah_nop',
                'mkdt.keterangan_status',
            ])
            ->edit('id_kavling', function ($v) use (&$rowNumber) {
                return ++$rowNumber;
            })
            ->edit('nama_jalan', function ($v) {
                $html = esc($v->nama_jalan);
                if ((int) ($v->akad_indent ?? 0) === 1) {
                    $html .= ' <span class="badge badge-info">Akad Indent</span>';
                }
                return $html;
            })
            ->edit('booking_tgl', function ($value) {
                return $this->format_tgl($value->booking_tgl);
            })
            ->edit('wawancara_tgl', function ($value) {
                return $this->format_tgl($value->wawancara_tgl);
            })
            ->edit('akad_tgl', function ($value) {
                return $this->format_tgl($value->akad_tgl);
            })
            ->edit('sp3k_tgl', function ($value) {
                return $this->format_tgl($value->sp3k_tgl);
            })
            ->edit('sp3k_tgl_exp', function ($value) {
                return $this->format_tgl($value->sp3k_tgl_exp);
            })
            ->edit('progres_bangunan', function ($v) {
                return $v->progres_bangunan ?? 0 . "%";
            })
            ->edit('is_kpr', function ($value) {
                return $this->is_active($value->is_kpr, 'KPR', 'TUNAI');
            })
            ->edit('lpa', function ($v) {
                if ($v->lpa == 1) {
                    return '<i class="fa fa-solid fa-check"></i><br>';
                }
                return '';
            })
            ->edit('st_listrik', function ($v) {
                if ($v->st_listrik == 1) {
                    return '<i class="fa fa-solid fa-check"></i><br>';
                }
                return '';
            })
            ->edit('tunai', function ($v) {
                if ($v->is_kpr == 1) {
                    return '-';
                }

                $total = $v->um + $v->adm + $v->bb;
                $bayar = $v->total_um + $v->total_adm + $v->total_bb;

                if ($bayar <= 0) {
                    return '0%';
                }

                $persen = ($bayar / $total) * 100;

                return round($persen) . '%'; // tanpa desimal
            })
            ->edit('um', function ($v) {
                if ($v->is_kpr == 0) {
                    return '-';
                }
                if ($v->total_um <= 0) {
                    return '0%';
                }
                $persen = ($v->total_um / $v->um) * 100;
                return round($persen) . '%'; // tanpa desimal
            })
            ->edit('adm', function ($v) {
                if ($v->is_kpr == 0) {
                    return '-';
                }
                if ($v->total_adm <= 0) {
                    return '0%';
                }
                $persen = ($v->total_adm / $v->adm) * 100;
                return round($persen) . '%'; // tanpa desimal
            })
            ->edit('bb', function ($v) {
                if ($v->is_kpr == 0) {
                    return '-';
                }
                if ($v->total_bb <= 0) {
                    return '0%';
                }
                $persen = ($v->total_bb / $v->bb) * 100;
                return round($persen) . '%'; // tanpa desimal
            })
            ->edit('action', function ($value) {
                return $this->renderPoskonActionHtml($value);
            })
            ->edit('kode_referal', function ($value) {
                return $this->renderReferralQrHtml($value);
            })
            ->edit('keterangan_status', function ($v) {
                return $v->keterangan_status ?: '-';
            })
            ->toJson();
    }
    function getDataTablesBatal($request)
    {
        $status = "Batal";
        $builder = $this->posisiKonsumenRepo->getQueryBatal();
        if ($request->getVar('id_proyek'))
            $builder->where('proyek.id_proyek', $request->getVar('id_proyek'));
        if ($request->getVar('id_cluster'))
            $builder->where('cluster.id_cluster', $request->getVar('id_cluster'));
        if ($request->getVar('id_jalan'))
            $builder->where('jalan.id_jalan', $request->getVar('id_jalan'));

        return DataTable::of($builder)
            ->setSearchableColumns([
                'jalan.nama_jalan',
                'kavling.no_kavling',
                'tipe.tipe_rumah',
                'mkdt.keterangan_batal',
                'konsumen.nama_konsumen',
                'mkdt.booking_tgl',
                'mkdt.is_kpr',
            ])
            ->addNumbering('no')
            ->edit('booking_tgl', function ($value) {
                return $this->format_tgl($value->booking_tgl);
            })
            ->edit('is_kpr', function ($value) {
                return $this->is_active($value->is_kpr, 'KPR', 'TUNAI');
            })
            ->edit('keterangan_batal', function ($value) {
                $tanggal_batal = $this->format_tgl($value->mkdt_batal_tgl);
                $keterangan_batal = $value->keterangan_batal;
                return $keterangan_batal . "<br> <span class='text-muted'>Dibatalkan pada: " . $tanggal_batal . "</span>";
            })
            ->edit('perlu_refund', function ($value) {
                if ((int) ($value->perlu_refund ?? 0) === 1) {
                    return '<span class="badge badge-warning">Perlu Refund</span>';
                }
                return '<span class="badge badge-secondary">Tidak Perlu Refund</span>';
            })
            ->edit('total_tagihan', function ($v) {
                return number_format($v->um + $v->adm + $v->bb);
            })
            ->edit('sudah_bayar', function ($v) {
                return number_format($v->total_um + $v->total_adm + $v->total_bb);
            })
            ->edit('sisa_tagihan', function ($v) {
                $tot = $v->um + $v->adm + $v->bb;
                $sb = $v->total_um + $v->total_adm + $v->total_bb;
                return number_format($tot - $sb);
            })
            ->edit('action', function ($value) {
                return $this->renderPoskonActionHtml($value);
            })
            ->toJson();
    }

    function getRiwayatExport($id_proyek, $status)
    {
        $rows = $this->posisiKonsumenRepo->getRiwayatExport($id_proyek, $status);

        foreach ($rows as $row) {
            $randomName = $row->randomName ?? $row->randomname ?? '';
            if ($randomName === '' || empty($row->path)) {
                continue;
            }

            $logicalPath = rtrim((string) $row->path, '/') . '/' . $randomName;

            try {
                $row->download_url = $this->fileAccessService->pathUrl('poskon_export', $logicalPath, true);
                $row->access_url = $this->fileAccessService->pathUrl('poskon_export', $logicalPath, false);
            } catch (\Throwable $e) {
                $row->download_url = '';
                $row->access_url = '';
            }
        }

        return $rows;
    }
    function insertRiwayatExport($data)
    {
        return $this->posisiKonsumenRepo->insertRiwayatExport($data);
    }
    protected function num($d)
    {
        $d = str_replace(',', "", $d);
        return $d;
    }
    function format_tgl($tgl)
    {
        if ($tgl == "" || $tgl == "0000-00-00" || $tgl == null)
            return "-";
        return date_format(date_create($tgl), "d-M-Y");
    }
    function is_active($id, $texts, $textf)
    {
        $r = '<span class="btn btn-primary btn-sm" text-capitalized="">' . $textf . '</span>';
        if ($id == "1")
            $r = '<span class="btn btn-success btn-sm" text-capitalized="">' . $texts . '</span>';
        return $r;
    }
}
