<?php

namespace App\Services\Keuangan\Cashout;

use App\Repositories\Keuangan\Cashout\CashoutKavlingRepo;

class CashoutKavlingService
{
    protected CashoutKavlingRepo $repo;

    public function __construct()
    {
        $this->repo = new CashoutKavlingRepo();
    }

    public function getDataTables(array $var): array
    {
        $result = $this->repo->getDataTables($var);
        $rows = [];
        $no = (int) ($var['start'] ?? 0);

        foreach ($result['rows'] as $row) {
            $no++;

            $payload = htmlspecialchars(json_encode([
                'id_kavling' => (int) $row->id_kavling,
                'id_mkdt' => $row->id_mkdt ? (int) $row->id_mkdt : null,
                'nama_jalan' => $row->nama_jalan,
                'no_kavling' => $row->no_kavling,
                'no_tipe_rumah' => $row->no_tipe_rumah,
                'tipe_rumah' => $row->tipe_rumah,
                'status_mkdt' => $row->status_mkdt,
            ]), ENT_QUOTES, 'UTF-8');

            $totalCashout = (float) $row->total_cashout_keu;
            $totalProduksi = (float) $row->total_produksi;
            $totalSubkon = (float) $row->total_subkon;
            $totalPajak = (float) $row->total_pajak;
            $grandTotal = $totalCashout + $totalProduksi + $totalSubkon + $totalPajak;

            $rows[] = [
                '<button type="button" class="btn btn-sm btn-outline-secondary btn-ck-detail" data-id-kavling="' . (int) $row->id_kavling . '"><i class="fa fa-chevron-down"></i></button>',
                $no,
                '<strong>' . $this->escape($row->nama_jalan) . ' No ' . $this->escape($row->no_kavling) . '</strong>',
                $this->escape($row->nama_konsumen ?: '-'),
                $this->nominalButton($totalCashout, 'ck-open-cashout', $payload),
                $this->nominalButton($totalProduksi, 'ck-open-produksi', $payload, empty($row->id_mkdt)),
                $this->formatNumber($totalSubkon) . ' <a href="' . base_url('cashout/subkon') . '" target="_blank" class="d-block small">Lihat SPK &rarr;</a>',
                $this->nominalButton($totalPajak, 'ck-open-pajak', $payload, empty($row->id_mkdt)),
                '<strong>' . $this->formatNumber($grandTotal) . '</strong>',
            ];
        }

        return [
            'token' => csrf_hash(),
            'draw' => (int) ($var['draw'] ?? 1),
            'recordsTotal' => $result['recordsTotal'],
            'recordsFiltered' => $result['recordsFiltered'],
            'data' => $rows,
        ];
    }

    public function getDetailList(int $idKavling): array
    {
        if ($idKavling <= 0) {
            return [
                'status' => 'error',
                'message' => 'ID kavling tidak valid',
            ];
        }

        $rows = $this->repo->getDetailList($idKavling);

        return [
            'status' => 'success',
            'data' => array_map(function ($row) {
                return [
                    'tanggal' => $row->tanggal,
                    'nominal' => (float) $row->nominal,
                    'keterangan' => (string) ($row->keterangan ?? ''),
                    'departemen' => $row->departemen,
                ];
            }, $rows),
        ];
    }

    private function nominalButton(float $value, string $class, string $payload, bool $disabled = false): string
    {
        if ($disabled) {
            return $this->formatNumber($value) . '<br><small class="text-muted">Belum ada konsumen</small>';
        }

        return '<button type="button" class="btn btn-link p-0 ' . $class . '" data-payload="' . $payload . '">'
            . $this->formatNumber($value)
            . '</button>';
    }

    private function formatNumber(float $value): string
    {
        return number_format($value, 0, '.', ',');
    }

    private function escape(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}
