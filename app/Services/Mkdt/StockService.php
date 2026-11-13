<?php

namespace App\Services\Mkdt;

use App\Repositories\Mkdt\StockRepository;
use App\Services\Proyek\ActiveProyekService;

class StockService
{
    private StockRepository $stockRepository;
    private ?ActiveProyekService $activeProyekService;

    public function __construct(
        ?StockRepository $stockRepository = null,
        ?ActiveProyekService $activeProyekService = null
    ) {
        $this->stockRepository     = $stockRepository ?? new StockRepository();
        $this->activeProyekService = $activeProyekService;
    }

    protected function getActiveProyekService(): ActiveProyekService
    {
        if ($this->activeProyekService === null) {
            $this->activeProyekService = new ActiveProyekService();
        }

        return $this->activeProyekService;
    }

    /**
     * Susun data halaman Web list_stock() dan metadata API.
     *
     * @return array{content: string, data: array<string, mixed>}
     */
    public function getIndexData(): array
    {
        $proyek = $this->getActiveProyekService()->resolveAndGet();

        return [
            'content' => 'kavling/list-stock',
            'data'    => [
                'controller' => 'Mkdt',
                'title'      => 'List Kavling Stock',
                'proyek'     => $proyek ?? (object) ['id_proyek' => null, 'nama_proyek' => ''],
            ],
        ];
    }

    /**
     * Dual-mode DataTables response untuk daftar kavling stock.
     * - $raw = true  -> RAW_JSON_READY tanpa HTML
     * - $raw = false -> Legacy payload untuk app/Views/kavling/list-stock.php
     *
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    public function getListStock(array $params, bool $raw = false): array
    {
        $draw   = (int) ($params['draw'] ?? 0);
        $start  = max(0, (int) ($params['start'] ?? 0));
        $result = $this->stockRepository->getStockListDataTables($params);

        $rows = [];
        $no   = $start;

        foreach ($result['rows'] as $row) {
            $no++;
            $tglPembangunanFormatted = $this->formatTanggal($row->tanggal_pembangunan ?? null);
            $tglSelesaiFormatted     = $this->formatTanggal($row->tanggal_selesai_pembangunan ?? null);
            $updatedAtFormatted      = $this->formatDateTime($row->updated_at ?? null);
            $updatedBy               = trim((string) ($row->uedit_by ?? ''));

            if ($raw) {
                $rows[] = [
                    'no_index'                              => $no,
                    'id_kavling'                            => isset($row->id_kavling) ? (int) $row->id_kavling : null,
                    'id_produksi'                           => isset($row->id_produksi) ? (int) $row->id_produksi : null,
                    'id_mkdt'                               => isset($row->id_mkdt) ? (int) $row->id_mkdt : null,
                    'id_keuangan'                           => isset($row->id_keuangan) ? (int) $row->id_keuangan : null,
                    'id_legal'                              => isset($row->id_legal) ? (int) $row->id_legal : null,
                    'id_komplain'                           => isset($row->id_komplain) ? (int) $row->id_komplain : null,
                    'id_jalan'                              => isset($row->id_jalan) ? (int) $row->id_jalan : null,
                    'nama_jalan'                            => $row->nama_jalan ?? '',
                    'no_kavling'                            => $row->no_kavling ?? '',
                    'id_cluster'                            => isset($row->id_cluster) ? (int) $row->id_cluster : null,
                    'nama_cluster'                          => $row->nama_cluster ?? '',
                    'id_proyek'                             => isset($row->id_proyek) ? (int) $row->id_proyek : null,
                    'nama_proyek'                           => $row->nama_proyek ?? '',
                    'tipe_rumah'                            => $row->tipe_rumah ?? '',
                    'no_tipe_rumah'                         => $row->no_tipe_rumah ?? '',
                    'progres_bangunan'                      => isset($row->progres_bangunan) ? (int) $row->progres_bangunan : null,
                    'status_mkdt'                           => $row->status_mkdt ?? null,
                    'nama_konsumen'                         => $row->nama_konsumen ?? null,
                    'hp_konsumen'                           => $row->hp_konsumen ?? null,
                    'keterangan_batal'                      => $row->keterangan_batal ?? null,
                    'tanggal_pembangunan'                   => $row->tanggal_pembangunan ?? null,
                    'tanggal_pembangunan_formatted'         => $tglPembangunanFormatted,
                    'tanggal_selesai_pembangunan'           => $row->tanggal_selesai_pembangunan ?? null,
                    'tanggal_selesai_pembangunan_formatted' => $tglSelesaiFormatted,
                    'created_by'                            => $row->uadd_by ?? null,
                    'updated_by'                            => $updatedBy !== '' ? $updatedBy : null,
                    'updated_at'                            => $row->updated_at ?? null,
                    'updated_at_formatted'                  => $updatedAtFormatted,
                ];
                continue;
            }

            $legacyRow                                          = (array) $row;
            $legacyRow['no_index']                              = $no;
            $legacyRow['tanggal_pembangunan_formatted']         = $tglPembangunanFormatted;
            $legacyRow['tanggal_selesai_pembangunan_formatted'] = $tglSelesaiFormatted;
            $legacyRow['terakhir_diperbarui']                   = ($updatedBy !== '' ? $updatedBy : '-') . '<br>' . $updatedAtFormatted;
            $rows[]                                             = $legacyRow;
        }

        if ($raw) {
            return [
                'draw'            => $draw,
                'recordsTotal'    => (int) ($result['recordsTotal'] ?? 0),
                'recordsFiltered' => (int) ($result['recordsFiltered'] ?? 0),
                'items'           => $rows,
                'data'            => $rows,
            ];
        }

        return [
            'draw'            => $draw,
            'recordsTotal'    => (int) ($result['recordsTotal'] ?? 0),
            'recordsFiltered' => (int) ($result['recordsFiltered'] ?? 0),
            'data'            => $rows,
        ];
    }

    public function formatTanggal(?string $tgl): string
    {
        $tgl = trim((string) $tgl);
        if ($tgl === '' || str_starts_with($tgl, '0000-00-00')) {
            return '-';
        }

        $datePart = substr($tgl, 0, 10);
        if ($datePart <= '1970-01-01') {
            return '-';
        }

        $timestamp = strtotime($tgl);
        if ($timestamp === false) {
            return '-';
        }

        return date('d-M-Y', $timestamp);
    }

    public function formatDateTime(?string $datetime): string
    {
        $datetime = trim((string) $datetime);
        if ($datetime === '' || str_starts_with($datetime, '0000-00-00')) {
            return '-';
        }

        $datePart = substr($datetime, 0, 10);
        if ($datePart <= '1970-01-01') {
            return '-';
        }

        $timestamp = strtotime($datetime);
        if ($timestamp === false) {
            return '-';
        }

        return date('d-M-Y H:i', $timestamp);
    }
}
