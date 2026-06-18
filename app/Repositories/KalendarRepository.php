<?php

namespace App\Repositories;

use CodeIgniter\Database\BaseConnection;

class KalendarRepository
{
    protected BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? db_connect();
    }

    public function getPembayaranEvents(int $idProyek, string $start, string $end): array
    {
        return $this->db->table('log_pembayaran as lp')
            ->select('lp.tanggal_bayar, lp.nominal, lp.keterangan')
            ->join('mkdt', 'mkdt.id_mkdt = lp.id_mkdt')
            ->join('kavling', 'kavling.id_kavling = mkdt.id_kavling')
            ->join('jalan', 'jalan.id_jalan = kavling.id_jalan')
            ->join('cluster', 'cluster.id_cluster = jalan.id_cluster')
            ->join('proyek', 'proyek.id_proyek = cluster.id_proyek')
            ->where('proyek.id_proyek', $idProyek)
            ->where('lp.tanggal_bayar >=', $start)
            ->where('lp.tanggal_bayar <=', $end)
            ->get()
            ->getResult();
    }

    public function getJatuhTempoEvents(int $idProyek, string $start, string $end): array
    {
        return $this->db->table('keuangan')
            ->select('keuangan.jatuh_tempo_tgl, konsumen.nama_konsumen, jalan.nama_jalan, kavling.no_kavling, tipe.tipe_rumah')
            ->join('mkdt', 'mkdt.id_mkdt = keuangan.id_mkdt')
            ->join('konsumen', 'konsumen.id_konsumen = keuangan.id_mkdt')
            ->join('kavling', 'kavling.id_kavling = mkdt.id_kavling')
            ->join('jalan', 'jalan.id_jalan = kavling.id_jalan')
            ->join('cluster', 'cluster.id_cluster = jalan.id_cluster')
            ->join('proyek', 'proyek.id_proyek = cluster.id_proyek')
            ->join('tipe', 'kavling.id_tipe = tipe.id_tipe')
            ->where('proyek.id_proyek', $idProyek)
            ->where('keuangan.jatuh_tempo_tgl >=', $start)
            ->where('keuangan.jatuh_tempo_tgl <=', $end)
            ->get()
            ->getResult();
    }

    public function getMkdtEvents(int $idProyek, string $start, string $end): array
    {
        return $this->db->table('mkdt')
            ->select('
                mkdt.akad,
                mkdt.rencana_akad_tgl,
                mkdt.akad_tgl,
                mkdt.perintah_bangun,
                mkdt.perintah_bangun_tgl,
                konsumen.nama_konsumen,
                jalan.nama_jalan,
                kavling.no_kavling,
                tipe.tipe_rumah
            ')
            ->join('konsumen', 'konsumen.id_konsumen = mkdt.id_mkdt')
            ->join('kavling', 'kavling.id_kavling = mkdt.id_kavling')
            ->join('jalan', 'jalan.id_jalan = kavling.id_jalan')
            ->join('cluster', 'cluster.id_cluster = jalan.id_cluster')
            ->join('proyek', 'proyek.id_proyek = cluster.id_proyek')
            ->join('tipe', 'kavling.id_tipe = tipe.id_tipe')
            ->where('proyek.id_proyek', $idProyek)
            ->groupStart()
                ->where('mkdt.akad_tgl >=', $start)
                ->where('mkdt.akad_tgl <=', $end)
                ->orGroupStart()
                    ->where('mkdt.rencana_akad_tgl >=', $start)
                    ->where('mkdt.rencana_akad_tgl <=', $end)
                ->groupEnd()
                ->orGroupStart()
                    ->where('mkdt.perintah_bangun_tgl >=', $start)
                    ->where('mkdt.perintah_bangun_tgl <=', $end)
                ->groupEnd()
            ->groupEnd()
            ->get()
            ->getResult();
    }
}
