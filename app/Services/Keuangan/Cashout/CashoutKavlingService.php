<?php

namespace App\Services\Keuangan\Cashout;

use App\Models\ChecklistSubItemModel;
use App\Models\ProyekModel;
use App\Repositories\Keuangan\Cashout\CashoutKavlingRepo;
use CodeIgniter\Database\BaseConnection;

class CashoutKavlingService
{
    protected CashoutKavlingRepo $repo;
    protected ?BaseConnection $db = null;
    protected ?ProyekModel $proyekModel = null;
    protected ?ChecklistSubItemModel $siModel = null;

    public function __construct(
        ?CashoutKavlingRepo $repo = null,
        ?BaseConnection $db = null,
        ?ProyekModel $proyekModel = null,
        ?ChecklistSubItemModel $siModel = null
    ) {
        $this->repo = $repo ?? new CashoutKavlingRepo();
        $this->db = $db;
        $this->proyekModel = $proyekModel;
        $this->siModel = $siModel;
    }

    public function getIndexData(?int $idProyek): array
    {
        return [
            'title' => 'Rekap Cashout per Kavling',
            'proyek' => $idProyek ? $this->getProyekModel()->find($idProyek) : null,
            'pph' => $this->getDb()->table('pph')->where('deleted_at', null)->get()->getResult(),
            'ppn' => $this->getDb()->table('ppn')->where('deleted_at', null)->get()->getResult(),
            'list' => $this->getChecklistSubitemsForCurrentUser(),
        ];
    }

    public function getMetaPayload(?int $idProyek): array
    {
        helper('auth');
        $canKeuangan = function_exists('in_groups') && in_groups(['1', '3']);
        $canProduksi = function_exists('in_groups') && in_groups(['1', '7']);
        $canPajak = function_exists('in_groups') && in_groups(['1', '10']);

        $proyek = $idProyek ? $this->getProyekModel()->find($idProyek) : null;

        return [
            'active_project' => $proyek ? [
                'id' => (int) $proyek->id_proyek,
                'name' => (string) ($proyek->nama_proyek ?? ''),
            ] : null,
            'pph' => $this->getDb()->table('pph')->where('deleted_at', null)->get()->getResult(),
            'ppn' => $this->getDb()->table('ppn')->where('deleted_at', null)->get()->getResult(),
            'checklist_subitems' => $this->getChecklistSubitemsForCurrentUser(),
            'permissions' => [
                'can_view' => true,
                'can_keuangan' => $canKeuangan,
                'can_produksi' => $canProduksi,
                'can_pajak' => $canPajak,
            ],
        ];
    }

    public function getDataTables(array $var): array
    {
        helper('auth');
        $var = $this->normalizeDataTablesParams($var);
        $canKeuangan = in_groups(['1', '3']);
        $canProduksi = in_groups(['1', '7']);
        $canPajak = in_groups(['1', '10']);

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
            $totalMgm = (float) $row->total_mgm;
            $totalPajak = (float) $row->total_pajak;
            $grandTotal = $totalCashout + $totalProduksi + $totalSubkon + $totalMgm;

            $detailCell = '<button type="button" class="btn btn-sm btn-outline-secondary btn-ck-detail" data-id-kavling="' . (int) $row->id_kavling . '"><i class="fa fa-chevron-down"></i></button>';
            if ($canKeuangan) {
                $detailCell .= $this->actionButton('ck-open-cashout', $payload, 'fa-money-bill-wave', 'primary', 'Bayar Keuangan');
            }
            if ($canProduksi && !empty($row->id_mkdt)) {
                $detailCell .= $this->actionButton('ck-open-produksi', $payload, 'fa-industry', 'warning', 'Bayar Produksi');
            }
            if ($canPajak && !empty($row->id_mkdt)) {
                $detailCell .= $this->actionButton('ck-open-pajak', $payload, 'fa-file-invoice-dollar', 'info', 'Bayar Pajak');
            }

            $rows[] = [
                $detailCell,
                $no,
                '<strong>' . $this->escape($row->nama_jalan) . ' No ' . $this->escape($row->no_kavling) . '</strong>',
                $this->escape($row->nama_konsumen ?: '-'),
                $this->nominalButton($totalCashout, 'ck-open-cashout', $payload),
                $this->nominalButton($totalProduksi, 'ck-open-produksi', $payload, empty($row->id_mkdt)),
                $this->formatNumber($totalSubkon) . ' <a href="' . base_url('cashout/subkon') . '" target="_blank" class="d-block small">Lihat SPK &rarr;</a>',
                $this->formatNumber($totalMgm),
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

    public function getDataTablesRaw(array $var): array
    {
        helper('auth');
        $var = $this->normalizeDataTablesParams($var);
        $canKeuangan = function_exists('in_groups') && in_groups(['1', '3']);
        $canProduksi = function_exists('in_groups') && in_groups(['1', '7']);
        $canPajak = function_exists('in_groups') && in_groups(['1', '10']);

        $result = $this->repo->getDataTables($var);
        $items = [];

        foreach ($result['rows'] as $row) {
            $totalCashout = (float) $row->total_cashout_keu;
            $totalProduksi = (float) $row->total_produksi;
            $totalSubkon = (float) $row->total_subkon;
            $totalMgm = (float) $row->total_mgm;
            $totalPajak = (float) $row->total_pajak;
            $grandTotal = $totalCashout + $totalProduksi + $totalSubkon + $totalMgm;

            $items[] = [
                'id_kavling' => (int) $row->id_kavling,
                'id_mkdt' => $row->id_mkdt ? (int) $row->id_mkdt : null,
                'nama_jalan' => (string) ($row->nama_jalan ?? ''),
                'no_kavling' => (string) ($row->no_kavling ?? ''),
                'no_tipe_rumah' => (string) ($row->no_tipe_rumah ?? ''),
                'tipe_rumah' => (string) ($row->tipe_rumah ?? ''),
                'status_mkdt' => (string) ($row->status_mkdt ?? ''),
                'nama_konsumen' => (string) ($row->nama_konsumen ?? ''),
                'total_cashout_keu' => $totalCashout,
                'total_produksi' => $totalProduksi,
                'total_subkon' => $totalSubkon,
                'total_mgm' => $totalMgm,
                'total_pajak' => $totalPajak,
                'grand_total' => $grandTotal,
                'permissions' => [
                    'can_keuangan' => $canKeuangan,
                    'can_produksi' => $canProduksi && !empty($row->id_mkdt),
                    'can_pajak' => $canPajak && !empty($row->id_mkdt),
                ],
            ];
        }

        return [
            'items' => $items,
            'meta' => [
                'draw' => (int) ($var['draw'] ?? 1),
                'recordsTotal' => $result['recordsTotal'],
                'recordsFiltered' => $result['recordsFiltered'],
                'total' => $result['recordsFiltered'],
            ],
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
                    'item' => (string) ($row->item ?? ''),
                ];
            }, $rows),
        ];
    }

    private function normalizeDataTablesParams(array $var): array
    {
        $search = $var['search'] ?? '';
        if (! is_array($search)) {
            $search = ['value' => (string) $search];
        } elseif (! array_key_exists('value', $search)) {
            $search['value'] = '';
        }

        return array_merge($var, [
            'draw' => (int) ($var['draw'] ?? 1),
            'start' => max(0, (int) ($var['start'] ?? 0)),
            'length' => isset($var['length']) ? (int) $var['length'] : 25,
            'search' => $search,
            'order' => is_array($var['order'] ?? null) ? $var['order'] : [],
            'columns' => is_array($var['columns'] ?? null) ? $var['columns'] : [],
        ]);
    }

    private function getChecklistSubitemsForCurrentUser(): array
    {
        helper('auth');
        if (! function_exists('in_groups') || ! in_groups(['1', '7', '8'])) {
            return [];
        }

        return $this->getSiModel()
            ->select('
                checklist_group.nama_group,
                checklist_item.nama_item,
                checklist_subitem.id_subitem,
                checklist_subitem.nama_subitem,
            ')
            ->join('checklist_item', 'checklist_item.id_item = checklist_subitem.id_item')
            ->join('checklist_group', 'checklist_item.id_group = checklist_group.id_group')
            ->where('checklist_item.is_active = 1')
            ->where('checklist_group.is_active = 1')
            ->where('checklist_subitem.is_active = 1')
            ->orderBy('checklist_item.id_group', 'asc')
            ->orderBy('checklist_item.id_item', 'asc')
            ->findAll();
    }

    private function getDb(): BaseConnection
    {
        return $this->db ??= \Config\Database::connect();
    }

    private function getProyekModel(): ProyekModel
    {
        return $this->proyekModel ??= new ProyekModel();
    }

    private function getSiModel(): ChecklistSubItemModel
    {
        return $this->siModel ??= new ChecklistSubItemModel();
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

    private function actionButton(string $class, string $payload, string $icon, string $color, string $title): string
    {
        return '<button type="button" class="btn btn-sm btn-outline-' . $color . ' ' . $class . ' ml-1" data-payload="' . $payload . '" title="' . $title . '"><i class="fa ' . $icon . '"></i></button>';
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
