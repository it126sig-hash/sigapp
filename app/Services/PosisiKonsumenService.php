<?php

namespace App\Services;

use App\Repositories\PosisiKonsumenRepository;

use Hermawan\DataTables\DataTable;

class PosisiKonsumenService
{
    protected $db;
    protected $posisiKonsumenRepo;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->posisiKonsumenRepo = new PosisiKonsumenRepository();
    }

    public function getDataTable($request, $status = null)
    {
        $status = $status ?? "Booking";
        $builder = $this->posisiKonsumenRepo->getBaseQuery($status);
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

        return DataTable::of($builder)
            ->edit('booking_tgl', function ($value) {
                return $this->format_tgl($value->booking_tgl);
            })
            ->edit('wawancara_tgl', function ($value) {
                return $this->format_tgl($value->wawancara_tgl);
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
                $jsonData = htmlspecialchars(json_encode($value), ENT_QUOTES, 'UTF-8');
                return '
                <div class="btn-group">
                <button class="btn btn-primary btn-sm" onclick="openDetail(' . $value->id_mkdt . ')"><i class="fa fa-eye"></i></button>
                <button class="btn btn-warning btn-sm" data-kavling="' . $jsonData . '" onclick="openEdit(this)"><i class="fa fa-edit"></i></button>
                </div>';
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
                $jsonData = htmlspecialchars(json_encode($value), ENT_QUOTES, 'UTF-8');
                return '
                <div class="btn-group">
                <button class="btn btn-primary btn-sm" onclick="openDetail(' . $value->id_mkdt . ')"><i class="fa fa-eye"></i></button>
                <button class="btn btn-warning btn-sm" data-kavling="' . $jsonData . '" onclick="openEdit(this)"><i class="fa fa-edit"></i></button>
                </div>';
            })
            ->toJson();
    }

    function getRiwayatExport($id_proyek, $status)
    {
        return $this->posisiKonsumenRepo->getRiwayatExport($id_proyek, $status);
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
