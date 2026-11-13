<?php

namespace App\Services\Keuangan;

use App\Repositories\Keuangan\CashOutRepository;
use App\Services\Mkdt\KonsumenService;
use App\Services\Notifikasi\NotifikasiService;
use CodeIgniter\Database\BaseConnection;

class CashOutService
{
    protected CashOutRepository $repo;
    protected KonsumenService $konsumenService;
    protected BaseConnection $db;
    protected NotifikasiService $notif;

    public function __construct(
        ?CashOutRepository $repo = null,
        ?KonsumenService $konsumenService = null,
        ?BaseConnection $db = null,
        ?NotifikasiService $notif = null
    ) {
        $this->repo = $repo ?? model(CashOutRepository::class);
        $this->konsumenService = $konsumenService ?? new KonsumenService();
        $this->db = $db ?? \Config\Database::connect();
        $this->notif = $notif ?? new NotifikasiService();
    }

    public function getListItem(string $search = ''): array
    {
        return $this->repo->getListItem($search);
    }

    public function getListItemSelect2(string $search = ''): array
    {
        return $this->repo->getListItemSelect2($search);
    }

    public function getMetaPayload(): array
    {
        $items = $this->repo->getListItemSelect2('');

        return [
            'list_item' => $items,
            'items' => $items,
        ];
    }

    public function getByIDKavling(int $idKavling): array
    {
        if ($idKavling <= 0) {
            return [
                'success' => false,
                'messages' => 'data tidak ditemukan',
            ];
        }

        $kavling = $this->repo->getKavlingDetail($idKavling);
        $mkdt = $kavling && ! empty($kavling->id_mkdt)
            ? $this->konsumenService->getKonsumenTransaksi((int) $kavling->id_mkdt)
            : null;

        return [
            'success' => true,
            'konsumen' => $mkdt ?: $this->konsumenService->getByIDKavling($idKavling),
            'biaya_mkdt' => $this->formatBiayaMkdt($mkdt),
            'riwayat_bayar' => $this->repo->getRiwayatBayarCashOutByIDKavling($idKavling),
        ];
    }

    public function konsumenExistsForKavling(int|string $idKavling): bool
    {
        $idKavlingInt = (int) $idKavling;
        if ($idKavlingInt <= 0) {
            return false;
        }

        $kavling = $this->repo->getKavlingDetail($idKavlingInt);
        if ($kavling && ! empty($kavling->id_mkdt) && $this->konsumenService->getKonsumenTransaksi((int) $kavling->id_mkdt)) {
            return true;
        }

        return (bool) $this->konsumenService->getByIDKavling($idKavlingInt);
    }

    public function formatBiayaMkdt(?object $mkdt): array
    {
        $num = static function ($value): float {
            return (float) ($value ?? 0);
        };

        if (! $mkdt) {
            return [];
        }

        $hargaUangMuka = $num($mkdt->harga_uang_muka ?? 0);
        $diskonUangMuka = $num($mkdt->harga_diskon_uang_muka ?? 0);
        $sbum = $num($mkdt->harga_sbum ?? 0);
        $administrasi = $num($mkdt->harga_administrasi ?? 0);
        $bphtb = $num($mkdt->harga_bphtb ?? 0);
        $biayaProses = $num($mkdt->harga_biaya_proses ?? 0);
        $ppn = $num($mkdt->harga_ppn ?? 0);
        $penambahanUm = $num($mkdt->harga_penambahan_um ?? 0);
        $penambahan = $num($mkdt->harga_penambahan ?? 0);
        $penambahanTanah = $num($mkdt->harga_penambahan_tanah ?? 0);

        $totalUm = $hargaUangMuka - $diskonUangMuka - $sbum;
        $totalBiayaLain = $administrasi + $bphtb + $biayaProses + $ppn + $penambahanUm + $penambahan + $penambahanTanah;

        return [
            'harga_jual' => $num($mkdt->harga_jual ?? 0),
            'harga_jual_net' => $num($mkdt->harga_jual_net ?? 0),
            'harga_kpr' => $num($mkdt->harga_kpr ?? 0),
            'harga_kpr_acc' => $num($mkdt->harga_kpr_acc ?? 0),
            'harga_uang_muka' => $hargaUangMuka,
            'harga_diskon_uang_muka' => $diskonUangMuka,
            'harga_sbum' => $sbum,
            'harga_administrasi' => $administrasi,
            'harga_bphtb' => $bphtb,
            'harga_biaya_proses' => $biayaProses,
            'harga_ppn' => $ppn,
            'harga_penambahan_um' => $penambahanUm,
            'harga_penambahan' => $penambahan,
            'harga_penambahan_tanah' => $penambahanTanah,
            'total_um' => $totalUm,
            'total_biaya_lain' => $totalBiayaLain,
            'total_tercatat' => $totalUm + $totalBiayaLain,
        ];
    }

    public function saveCashOut(array $input, ?int $userId = null): array
    {
        $idKavling = trim((string) ($input['id_kavling'] ?? ''));
        $idItemCashout = trim((string) ($input['co-untuk_pembayaran'] ?? $input['id_item_cashout'] ?? ''));
        $tanggalBayar = trim((string) ($input['co-tanggal_bayar'] ?? $input['tanggal_bayar'] ?? ''));
        $nominalRaw = $input['co-nominal'] ?? $input['nominal'] ?? null;
        $nominal = $this->cleanNumeric($nominalRaw);
        $keterangan = $input['co-keterangan'] ?? $input['keterangan'] ?? null;

        if ($idKavling === '' || $idItemCashout === '' || $tanggalBayar === '' || $nominal <= 0) {
            return [
                'success' => false,
                'messages' => 'data tidak lengkap',
                'status_code' => 422,
            ];
        }

        if (! $this->konsumenExistsForKavling($idKavling)) {
            return [
                'success' => false,
                'messages' => 'Kavling belum memiliki data konsumen',
                'status_code' => 422,
            ];
        }

        $payload = [
            'id_kavling' => (int) $idKavling,
            'id_item_cashout' => (int) $idItemCashout,
            'tanggal_bayar' => $tanggalBayar,
            'nominal' => $nominal,
            'keterangan' => $keterangan !== null ? trim((string) $keterangan) : null,
        ];
        if ($userId !== null && $userId > 0) {
            $payload['add_by'] = $userId;
        }

        if ($this->insert($payload)) {
            return [
                'success' => true,
                'id_kavling' => $idKavling,
                'messages' => 'data berhasil disimpan',
            ];
        }

        return [
            'success' => false,
            'messages' => 'data gagal disimpan',
            'status_code' => 500,
        ];
    }

    public function deleteCashOut(int $id): array
    {
        if ($id <= 0) {
            return [
                'success' => false,
                'messages' => 'data tidak ditemukan',
                'status_code' => 404,
            ];
        }

        $idKavling = $this->delete($id);
        if ($idKavling) {
            return [
                'success' => true,
                'id_kavling' => $idKavling,
                'messages' => 'data berhasil dihapus',
            ];
        }

        return [
            'success' => false,
            'messages' => 'data gagal dihapus',
            'status_code' => 422,
        ];
    }

    function insert($data)
    {
        $this->db->transStart();
        $this->repo->insert($data);
        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return false;
        }
        return true;
    }

    function delete($id)
    {
        $this->db->transStart();

        $dataRow = $this->repo->find($id);
        $idKavling = $dataRow ? $dataRow->id_kavling : null;

        if ($dataRow) {
            $this->repo->softDelete($id);
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return false;
        }

        return $idKavling;
    }

    private function cleanNumeric(mixed $value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }

        return (float) str_replace(',', '', trim((string) $value));
    }

    private function num($v)
    {
        // if ($v === null || $v === '')
        //     return null;
        // $v = (string) $v;
        // $v = str_replace(',', "", $v);
        // return (int) round((float) $v);
        $v = str_replace(',', "", $v);
        return $v;
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
