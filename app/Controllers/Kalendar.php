<?php

namespace App\Controllers;

use App\Models\ProyekModel;
use App\Models\MkdtModel;
use App\Models\KeuanganModel;
use Myth\Auth\Authorization\GroupModel;
use App\Models\KavlingModel;

class Kalendar extends BaseController
{
    protected $db;
    protected $proyekModel;
    protected $keuanganModel;
    protected $kavlingModel;
    protected $mkdtModel;
    protected $authGroup;
    public function __construct()
    {
        $this->proyekModel = new ProyekModel();
        $this->keuanganModel = new KeuanganModel();
        $this->mkdtModel = new MkdtModel();
        $this->kavlingModel = new KavlingModel();
        $this->authGroup = new GroupModel();
        $this->db = db_connect();
    }

    function index()
    {
        $data['content'] = 'misc/kalender';

        //ambil data proyek masih static
        $data['data']['proyek'] = $this->proyekModel
            ->select("id_proyek, alamat_proyek, nama_proyek, siteplan")
            ->findAll();
        $data['data']['keuangan'] = $this->keuanganModel
            ->select("*")
            ->findAll();
        $data['data']['mkdt'] = $this->mkdtModel
            ->select("*")
            ->findAll();

        $data['data']['divisi'] = $this->authGroup
            ->select("id as id_divisi, name as divisi")
            ->findAll();

        return view('template', $data);
    }
    // function getDivisi($id)
    // {
    //     return $this->divisiModel
    //         ->where('id_divisi', $id)
    //         ->first();
    // }
    function getEvents()
    {
        $sdate = explode('T', $this->request->getVar('start'))[0];
        $edate = explode('T', $this->request->getVar('end'))[0];

        $id_proyek = $this->request->getVar('id_proyek');

        // $id_divisi = $this->request->getVar('id_role');
        // $divisi = $this->authGroup->where('id', $id_divisi)->findAll();

        // $divisi = strtolower($this->getDivisi($id_divisi)->divisi);

        // $tb = "kavling .id_" . $divisi;

        $data = [];
        // $data['token'] = csrf_hash();

        $x = 0;

        $lp = $this->db->table('log_pembayaran as lp')
            ->select("
                lp.tanggal_bayar,
                lp.nominal,
                lp.keterangan,
            ")
            ->join('mkdt', 'mkdt.id_mkdt = lp.id_mkdt')
            ->join('kavling', 'kavling.id_kavling = mkdt.id_kavling')
            ->join('jalan', 'jalan.id_jalan = kavling.id_jalan')
            ->join('cluster', 'cluster.id_cluster = jalan.id_cluster')
            ->join('proyek', 'proyek.id_proyek = cluster.id_proyek')

            ->where('proyek.id_proyek', $id_proyek)
            ->where("lp.tanggal_bayar between '$sdate' and '$edate'")
            ->get();
        foreach ($lp->getResult() as $rk) {
            $data[] = [
                "id" => $x + 1,
                "url" => '',
                "color" => '#ff40d2', // a non-ajax option
                "textColor" => 'white', // a non-ajax option
                "title" => "$rk->keterangan : (Rp. ".number_format($rk->nominal).")",
                "start" => "$rk->tanggal_bayar",
                "end" => "$rk->tanggal_bayar",
                "allDay" => true,
                "extendedProps" => array(
                    "calendar" => 'Pembayaran'
                )
            ];
            $x++;
        }

        $keuangan = $this->db->table("keuangan")
            ->select("
                keuangan.jatuh_tempo_tgl,
                konsumen.nama_konsumen,
                jalan.nama_jalan,
                kavling.no_kavling,
                tipe.tipe_rumah

            ")
            ->join('mkdt', 'mkdt.id_mkdt = keuangan.id_mkdt')
            ->join('konsumen', 'konsumen.id_konsumen = keuangan.id_mkdt')

            ->join('kavling', 'kavling.id_kavling = mkdt.id_kavling')
            ->join('jalan', 'jalan.id_jalan = kavling.id_jalan')
            ->join('cluster', 'cluster.id_cluster = jalan.id_cluster')
            ->join('proyek', 'proyek.id_proyek = cluster.id_proyek')
            ->join('tipe', 'kavling.id_tipe = tipe.id_tipe')

            ->where('proyek.id_proyek', $id_proyek)
            ->where("keuangan.jatuh_tempo_tgl between '$sdate' and '$edate'")
            ->get();


        foreach ($keuangan->getResult() as $rk) {
            $data[] = [
                "id" => $x + 1,
                "url" => '',
                "color" => 'red', // a non-ajax option
                "textColor" => 'white', // a non-ajax option
                "title" => "Jatuh Tempo \n $rk->nama_konsumen \n $rk->nama_jalan No. $rk->no_kavling ($rk->tipe_rumah)",
                "start" => "$rk->jatuh_tempo_tgl",
                "end" => "$rk->jatuh_tempo_tgl",
                "allDay" => true,
                "extendedProps" => array(
                    "calendar" => 'Jatuh Tempo'
                )
            ];
            $x++;
        }

        $mkdt = $this->db->table("mkdt")
            ->select("
            mkdt.akad,
            mkdt.rencana_akad_tgl,
            mkdt.akad_tgl,

            mkdt.perintah_bangun,
            mkdt.perintah_bangun_tgl,

            mkdt.wawancara,
            mkdt.wawancara_tgl,

            mkdt.booking_tgl,

            konsumen.nama_konsumen,

            jalan.nama_jalan,
            kavling.no_kavling,
            tipe.tipe_rumah

        ")
            ->join('konsumen', 'konsumen.id_konsumen = mkdt.id_mkdt')
            ->join('kavling', 'kavling.id_kavling = mkdt.id_kavling')
            ->join('jalan', 'jalan.id_jalan = kavling.id_jalan')
            ->join('cluster', 'cluster.id_cluster = jalan.id_cluster')
            ->join('proyek', 'proyek.id_proyek = cluster.id_proyek')
            ->join('tipe', 'kavling.id_tipe = tipe.id_tipe')

            ->where('proyek.id_proyek', $id_proyek)
            ->where("mkdt.akad_tgl between '$sdate' and '$edate'")
            ->orWhere('proyek.id_proyek', $id_proyek)
            ->where("mkdt.rencana_akad_tgl between '$sdate' and '$edate'")
            ->get();

        foreach ($mkdt->getResult() as $r) {
            if ($r->akad == 1) {
                $data[] = [
                    "id" => $x + 1,
                    "url" => '',
                    "color" => 'green', // a non-ajax option
                    "textColor" => 'white', // a non-ajax option
                    "title" => "Akad \n $r->nama_konsumen \n $r->nama_jalan No. $r->no_kavling ($r->tipe_rumah)",
                    "start" => "$r->akad_tgl",
                    "end" => "$r->akad_tgl",
                    "allDay" => true,
                    "extendedProps" => array(
                        "calendar" => 'Akad'
                    )
                ];
                $x++;
            }
            if ($r->rencana_akad_tgl) {
                $data[] = [
                    "id" => $x + 1,
                    "url" => '',
                    "color" => '#17ffff', // a non-ajax option
                    "textColor" => '#000', // a non-ajax option
                    "title" => "Rencana Akad \n $r->nama_konsumen \n $r->nama_jalan No. $r->no_kavling ($r->tipe_rumah)",
                    "start" => "$r->rencana_akad_tgl",
                    "end" => "$r->rencana_akad_tgl",
                    "allDay" => true,
                    "extendedProps" => array(
                        "calendar" => 'Rencana Akad'
                    )
                ];
                $x++;
            }
            if ($r->perintah_bangun == 1) {
                $data[] = [
                    "id" => $x + 1,
                    "url" => '',
                    "color" => '#fcba03', // a non-ajax option
                    "textColor" => '#000', // a non-ajax option
                    "title" => "Perintah Bangun \n $r->nama_jalan No. $r->no_kavling ($r->tipe_rumah)",
                    "start" => "$r->perintah_bangun_tgl",
                    "end" => "$r->perintah_bangun_tgl",
                    "allDay" => true,
                    "extendedProps" => array(
                        "calendar" => 'Perintah Bangun'
                    )
                ];
                $x++;
            }
        }

        return $this->response->setJSON($data);
    }
}
