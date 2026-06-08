<?php

namespace App\Services\Keuangan\Cashout;

use App\Repositories\Keuangan\Cashout\CashoutSubkonRepo;
use App\Repositories\KavlingRepository;
use App\Services\FileAccessService;
use App\Services\StorageService;

class CashoutSubkonService
{
    protected $repo;
    protected $kavlingRepo;
    protected $db;
    protected $storageService;
    protected $fileAccessService;

    public function __construct()
    {
        $this->repo = new CashoutSubkonRepo();
        $this->kavlingRepo = new KavlingRepository();
        $this->db = \Config\Database::connect();
        $this->storageService = new StorageService();
        $this->fileAccessService = new FileAccessService();
    }

    public function getDataTables(array $var): array
    {
        $result = $this->repo->getDataTables($var);
        $rows = [];
        $no = (int) ($var['start'] ?? 0);

        foreach ($result['rows'] as $row) {
            $no++;
            $idKavlings = $this->decodeCsv($row->id_kavlings);
            $selectedKavlings = $this->decodeKavlings($row->kavling_options);

            $payload = htmlspecialchars(json_encode([
                'id_cashout_subkon' => $row->id_cashout_subkon,
                'id_proyek' => $row->id_proyek,
                'id_kavlings' => $idKavlings,
                'selected_kavlings' => $selectedKavlings,
            ]), ENT_QUOTES, 'UTF-8');

            $rows[] = [
                '<button type="button" class="btn btn-outline-primary btn-sm btn-edit-cashout-subkon" data-payload="' . $payload . '"><i class="fa fa-edit"></i> Isi</button>',
                $no,
                $row->nomor_surat ?: '-',
                $this->formatDate($row->tanggal_surat),
                $row->nama_subkon ?: '-',
                $row->nama_proyek ?: '-',
                $row->kavling_list ?: '-',
                number_format((float) $row->total_nominal, 0, '.', ','),
                $this->formatDateList($row->tanggal_jatuh_tempo_list),
                $this->formatDateList($row->waktu_cair_list),
                $this->statusBadge((int) ($row->max_detail_status ?? 0), (int) ($row->status ?? 0)),
                $this->formatDateTime($row->created_at),
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

    public function getCashoutSubkon(array $id_kavlings)
    {
        $id_kavlings = array_values(array_filter($id_kavlings));
        if (empty($id_kavlings)) {
            return $this->errorResponse('Data tidak ditemukan');
        }

        $id_cashout_subkon = $this->repo->getListCashoutKavling($id_kavlings);
        if (count($id_cashout_subkon) > 1) {
            return [
                'status' => 'error',
                'message' => 'Tidak bisa memanggil SPK Subkon lebih dari 1 SPK'
            ];
        }
        if (count($id_cashout_subkon) == 0) {
            return [
                'status' => 'success',
                'kavling_subkon' => [],
                'cashout_subkon_detail' => [],
                'detail_kavling' => [],
                'cashout_subkon' => [],
                'subkon' => [],
            ];
        }
        $kavling_subkon = $this->repo->getKavlingSubkonByID($id_cashout_subkon[0]->id_cashout_subkon);
        $detail_kavling = $this->kavlingRepo->getKavlingByIds($kavling_subkon);
        $cashout_subkon_detail = $this->repo->getListItemDetailByIDCashoutsubkon($id_cashout_subkon[0]->id_cashout_subkon);
        $cashout_subkon = $this->repo->getCashoutSubkonByID($id_cashout_subkon[0]->id_cashout_subkon);
        if (!empty($cashout_subkon['file_surat'])) {
            $idCashoutSubkon = (int) $cashout_subkon['id_cashout_subkon'];
            $cashout_subkon['file_surat_access_url'] = $this->fileAccessService->accessUrl('cashout_subkon', $idCashoutSubkon);
            $cashout_subkon['file_surat_download_url'] = $this->fileAccessService->accessUrl('cashout_subkon', $idCashoutSubkon, true);
        }
        $subkon = $this->repo->getSubkonByID($cashout_subkon['id_subkon']);
        // var_dump($cashout_subkon['id_subkon']);
        // die;

        return [
            'status' => 'success',
            'kavling_subkon' => $kavling_subkon,
            'cashout_subkon_detail' => $cashout_subkon_detail,
            'detail_kavling' => $detail_kavling,
            'cashout_subkon' => $cashout_subkon,
            'subkon' => $subkon,
        ];
    }
    public function saveCashoutSubkon($post)
    {

        $data = $post->getPost();
        $file = $post->getFile('file_surat');
        $validation = $this->validateCashoutSubkonData($data, $file);
        if ($validation['status'] === 'error') {
            return $validation;
        }

        $id_subkon = !empty($data['id_subkon']) ? $data['id_subkon'] : null;
        $totalNominal = $this->num($data['total_nominal']);

        $data_subkon = [
            'nama_subkon' => ucwords($data['nama_subkon']),
            'hp1_subkon' => $data['hp1_subkon'],
            'alamat_subkon' => $data['alamat_subkon'],
        ];

        //define data cashout subkon
        $id_cashout_subkon = !empty($data['id_cashout_subkon']) ? $data['id_cashout_subkon'] : null;

        $data_cashout_subkon = [
            'id_cashout_subkon' => $id_cashout_subkon,
            'id_subkon' => "", //diisi setelah berhasil insert ke table subkon
            'nomor_surat' => $data['nomor_surat'],
            'tanggal_surat' => $data['tanggal_surat'],
            'keterangan' => $data['keterangan_cashout_subkon'],
            'total_nominal' => $totalNominal,
            'status' => 0,

        ];

        if (empty($id_cashout_subkon)) {
            $data_cashout_subkon['created_at'] = date('Y-m-d H:i:s');
            $data_cashout_subkon['add_by'] = user_id();
        }

        try {
            $this->db->transStart();

            if ($file && $file->isValid() && !$file->hasMoved()) {
                $pathFile = $this->storageService->store($file, 'uploads/cashout/subkon/' . date('Ymd'));
                $data_cashout_subkon['file_surat'] = $pathFile;
            }

            //insert subkon
            $id_subkon = $this->repo->upsertSubkon($id_subkon, $data_subkon);
            $data_cashout_subkon['id_subkon'] = $id_subkon;

            //insert cashout 
            $id_cashout_subkon = $this->repo->saveCashoutSubkon($data_cashout_subkon);

            //insert cashout subkon detail
            $data_cashout_subkon_detail = [];
            $lastIndex = count($data['id_cashout_subkon_detail']) - 1;
            $accumulatedNominal = 0;
            foreach ($data['id_cashout_subkon_detail'] as $key => $value) {
                $percentage = $this->num($data['persentase'][$key]);
                $nominal = round(($totalNominal * $percentage) / 100, 2);
                if ($key === $lastIndex) {
                    $nominal = round($totalNominal - $accumulatedNominal, 2);
                }
                $accumulatedNominal += $nominal;

                $data_cashout_subkon_detail[] = [
                    'id_cashout_subkon' => $id_cashout_subkon,
                    'id_cashout_subkon_detail' => $value,
                    'berita_acara' => $data['berita_acara'][$key],
                    'persentase' => $percentage,
                    'nominal' => $nominal,
                ];
            }
            $this->repo->saveCashoutSubkonDetail($data_cashout_subkon_detail);

            //data cashout subkon kavling
            $data_cashout_subkon_kavling = [];
            foreach ($data['id_kavling'] as $key => $value) {
                $data_cashout_subkon_kavling[] = [
                    'id_kavling' => $value,
                    'id_cashout_subkon' => $id_cashout_subkon,
                ];
            }
            $this->repo->saveCashoutSubkonKavling($data_cashout_subkon_kavling);

            $this->db->transComplete();
            return [
                'status' => 'success',
                'message' => 'Data berhasil disimpan',
                'id_kavling' => $data['id_kavling'],
            ];
        } catch (\Throwable $th) {
            $this->db->transRollback();
            return [
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat menyimpan data',
                'error' => $th->getMessage(),
                'file' => $th->getFile(),
                'line' => $th->getLine(),
                'trace' => $th->getTraceAsString(),
            ];
        }
    }
    public function turunJatuhTempo($id_detail, $tanggal_jatuh_tempo, $berita_acara)
    {
        $validation = $this->validateRequired([
            'ID Detail tidak ditemukan' => $id_detail,
            'Tanggal Jatuh Tempo harus diisi' => $tanggal_jatuh_tempo,
            'Berita Acara harus diisi' => $berita_acara,
        ]);
        if ($validation['status'] === 'error') {
            return $validation;
        }

        try {
            $this->db->transStart();

            $detail = $this->repo->getDetailByID((int) $id_detail);
            if (!$detail) {
                $this->db->transRollback();
                return $this->errorResponse('Detail cashout subkon tidak ditemukan');
            }
            if ((int) $detail['status'] > 1) {
                $this->db->transRollback();
                return $this->errorResponse('Jatuh tempo tidak dapat diubah setelah SPP diajukan');
            }

            $this->repo->updateDetail((int) $id_detail, [
                'tanggal_jatuh_tempo' => $tanggal_jatuh_tempo,
                'status' => 1,
            ]);
            $this->repo->saveHistory(
                (int) $detail['id_cashout_subkon'],
                "Turun Jatuh Tempo untuk:  " . $berita_acara . " Pada Tanggal " . date('d F Y', strtotime($tanggal_jatuh_tempo)),
                1
            );

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                return [
                    'status' => 'error',
                    'message' => 'Gagal menyimpan data',
                ];
            }

            // Kirim notifikasi ke divisi Produksi (group_id=7)
            // setelah Jatuh Tempo berhasil diturunkan oleh Keuangan
            if ($detail && !empty($detail['id_cashout_subkon'])) {
                $id_kavlings = $this->repo->getKavlingBySubkonId((int) $detail['id_cashout_subkon']);
                if (!empty($id_kavlings)) {
                    $notif = new \App\Controllers\Notif();
                    foreach ($id_kavlings as $id_kavling) {
                        $notif->tambah_notif(
                            [7],   // Target: Divisi Produksi
                            'Jatuh Tempo Cashout Subkon telah diturunkan untuk: ' . $berita_acara,
                            user_id(),
                            $id_kavling,
                            null,
                            'cashout_subkon'
                        );
                    }
                }
            }

            return [
                'status' => 'success',
                'message' => 'Tanggal Jatuh Tempo berhasil disimpan',
            ];
        } catch (\Throwable $th) {
            $this->db->transRollback();
            return [
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat menyimpan data',
                'error' => $th->getMessage(),
            ];
        }
    }

    public function ajukanSPP($id_detail, $spp_no, $spp_tgl)
    {
        $validation = $this->validateRequired([
            'ID Detail tidak ditemukan' => $id_detail,
            'No SPP harus diisi' => $spp_no,
            'Tanggal SPP harus diisi' => $spp_tgl,
        ]);
        if ($validation['status'] === 'error') {
            return $validation;
        }

        try {
            $this->db->transStart();

            $detail = $this->repo->getDetailByID((int) $id_detail);
            if (!$detail) {
                $this->db->transRollback();
                return $this->errorResponse('Detail cashout subkon tidak ditemukan');
            }
            if ((int) $detail['status'] < 1 || (int) $detail['status'] > 2) {
                $this->db->transRollback();
                return $this->errorResponse('SPP hanya bisa diajukan setelah jatuh tempo turun');
            }

            $this->repo->updateDetail((int) $id_detail, [
                'spp_no' => $spp_no,
                'spp_tgl' => $spp_tgl,
                'spp_add_by' => user_id(),
                'spp_created_at' => date('Y-m-d H:i:s'),
                'status' => 2,
            ]);
            $this->repo->saveHistory(
                (int) $detail['id_cashout_subkon'],
                "Pengajuan SPP: No " . $spp_no . " Pada Tanggal " . date('d F Y', strtotime($spp_tgl)) . " untuk: " . ($detail['berita_acara'] ?? "-"),
                2
            );

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                return [
                    'status' => 'error',
                    'message' => 'Gagal menyimpan data',
                ];
            }

            return [
                'status' => 'success',
                'message' => 'Pengajuan SPP berhasil disimpan',
            ];
        } catch (\Throwable $th) {
            $this->db->transRollback();
            return [
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat menyimpan data',
                'error' => $th->getMessage(),
            ];
        }
    }

    public function ajukanPencairan($id_detail, $pencairan_tgl)
    {
        $validation = $this->validateRequired([
            'ID Detail tidak ditemukan' => $id_detail,
            'Tanggal Pengajuan Cair harus diisi' => $pencairan_tgl,
        ]);
        if ($validation['status'] === 'error') {
            return $validation;
        }

        try {
            $this->db->transStart();

            $detail = $this->repo->getDetailByID((int) $id_detail);
            if (!$detail) {
                $this->db->transRollback();
                return $this->errorResponse('Detail cashout subkon tidak ditemukan');
            }
            if ((int) $detail['status'] < 2 || (int) $detail['status'] > 3) {
                $this->db->transRollback();
                return $this->errorResponse('Pencairan hanya bisa diajukan setelah SPP');
            }

            $this->repo->updateDetail((int) $id_detail, [
                'pengajuan_cair_tgl' => $pencairan_tgl,
                'pengajuan_cari_add_by' => user_id(),
                'pengajuan_cair_created_at' => date('Y-m-d H:i:s'),
                'status' => 3,
            ]);
            $this->repo->saveHistory(
                (int) $detail['id_cashout_subkon'],
                "Pengajuan Pencairan: Pada Tanggal " . date('d F Y', strtotime($pencairan_tgl)) . " untuk: " . ($detail['berita_acara'] ?? "-"),
                3
            );

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                return [
                    'status' => 'error',
                    'message' => 'Gagal menyimpan data',
                ];
            }

            return [
                'status' => 'success',
                'message' => 'Pengajuan Pencairan berhasil disimpan',
            ];
        } catch (\Throwable $th) {
            $this->db->transRollback();
            return [
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat menyimpan data',
                'error' => $th->getMessage(),
            ];
        }
    }

    public function pembayaran($id_detail, $cek_no, $cek_tgl)
    {
        $validation = $this->validateRequired([
            'ID Detail tidak ditemukan' => $id_detail,
            'No Cek harus diisi' => $cek_no,
            'Tanggal Cek harus diisi' => $cek_tgl,
        ]);
        if ($validation['status'] === 'error') {
            return $validation;
        }

        try {
            $this->db->transStart();

            $detail = $this->repo->getDetailByID((int) $id_detail);
            if (!$detail) {
                $this->db->transRollback();
                return $this->errorResponse('Detail cashout subkon tidak ditemukan');
            }
            if ((int) $detail['status'] < 3) {
                $this->db->transRollback();
                return $this->errorResponse('Pembayaran hanya bisa dilakukan setelah pengajuan pencairan');
            }

            $this->repo->updateDetail((int) $id_detail, [
                'cek_no' => $cek_no,
                'cek_tgl' => $cek_tgl,
                'cek_add_by' => user_id(),
                'cek_created_at' => date('Y-m-d H:i:s'),
                'status' => 4,
            ]);
            $this->repo->saveHistory(
                (int) $detail['id_cashout_subkon'],
                "Pembayaran: No Cek " . $cek_no . " Pada Tanggal " . date('d F Y', strtotime($cek_tgl)) . " untuk: " . ($detail['berita_acara'] ?? "-"),
                4
            );

            if ($this->allTerminPaid((int) $detail['id_cashout_subkon'])) {
                $this->repo->updateCashoutSubkonStatus((int) $detail['id_cashout_subkon'], 1);
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                return [
                    'status' => 'error',
                    'message' => 'Gagal menyimpan data',
                ];
            }

            return [
                'status' => 'success',
                'message' => 'Pembayaran berhasil disimpan',
            ];
        } catch (\Throwable $th) {
            $this->db->transRollback();
            return [
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat menyimpan data',
                'error' => $th->getMessage(),
            ];
        }
    }

    public function getHistory($id_cashout_subkon): array
    {
        if (empty($id_cashout_subkon)) {
            return $this->errorResponse('ID tidak ditemukan');
        }

        return [
            'status' => 'success',
            'data' => $this->repo->getHistoryByIDCashoutSubkon($id_cashout_subkon),
        ];
    }

    private function allTerminPaid(int $id_cashout_subkon): bool
    {
        $details = $this->repo->getDetailByCashoutSubkonID($id_cashout_subkon);
        if (empty($details)) {
            return false;
        }

        foreach ($details as $detail) {
            if ((int) $detail['status'] !== 4) {
                return false;
            }
        }

        return true;
    }

    private function hasProgressedTermin(int $id_cashout_subkon): bool
    {
        foreach ($this->repo->getDetailByCashoutSubkonID($id_cashout_subkon) as $detail) {
            if ((int) $detail['status'] !== 0) {
                return true;
            }
        }

        return false;
    }

    private function validateCashoutSubkonData(array $data, $file): array
    {
        if (empty($data['nama_subkon']) || empty($data['hp1_subkon']) || empty($data['alamat_subkon'])) {
            return $this->errorResponse('Subkon Harus diisi');
        }

        if (empty($data['id_kavling'])) {
            return $this->errorResponse('Kavling Harus diisi');
        }

        if (empty($data['tanggal_surat'])) {
            return $this->errorResponse('Tanggal Surat Harus diisi');
        }

        if (empty($data['nomor_surat'])) {
            return $this->errorResponse('No. Surat Harus diisi');
        }

        $totalNominal = $this->num($data['total_nominal'] ?? 0);
        if ($totalNominal <= 0) {
            return $this->errorResponse('Total Nominal harus diisi');
        }

        if (empty($data['id_cashout_subkon']) && (!$file || !$file->isValid())) {
            return $this->errorResponse('Soft file SPK harus diupload');
        }

        if (!empty($data['id_cashout_subkon']) && $this->hasProgressedTermin((int) $data['id_cashout_subkon'])) {
            return $this->errorResponse('SPK tidak dapat diubah karena termin sudah berjalan');
        }

        $detailIds = $data['id_cashout_subkon_detail'] ?? [];
        $beritaAcara = $data['berita_acara'] ?? [];
        $persentase = $data['persentase'] ?? [];
        if (empty($detailIds) || count($detailIds) !== count($beritaAcara) || count($detailIds) !== count($persentase)) {
            return $this->errorResponse('Termin pembayaran belum lengkap');
        }

        $totalPercentage = 0;
        foreach ($persentase as $p) {
            $percentage = $this->num($p);
            if ($percentage <= 0) {
                return $this->errorResponse('Persentase termin harus lebih dari 0');
            }
            $totalPercentage += $percentage;
        }

        if (abs($totalPercentage - 100) > 0.001) {
            return $this->errorResponse('Total Persentase harus 100%');
        }

        return ['status' => 'success'];
    }

    private function validateRequired(array $fields): array
    {
        foreach ($fields as $message => $value) {
            if (empty($value)) {
                return $this->errorResponse($message);
            }
        }

        return ['status' => 'success'];
    }

    private function errorResponse(string $message): array
    {
        return [
            'status' => 'error',
            'message' => $message,
        ];
    }

    private function decodeCsv(?string $csv): array
    {
        if (empty($csv)) {
            return [];
        }

        return array_values(array_filter(explode(',', $csv), static fn ($value) => $value !== ''));
    }

    private function decodeKavlings(?string $value): array
    {
        $items = [];
        foreach ($this->decodeCsv($value) as $item) {
            $parts = explode('|', $item);
            if (count($parts) < 3) {
                continue;
            }

            $items[] = [
                'id_kavling' => $parts[0],
                'nama_jalan' => $parts[1],
                'no_kavling' => $parts[2],
            ];
        }

        return $items;
    }

    private function formatDateList(?string $csv): string
    {
        $dates = $this->decodeCsv($csv);
        if (empty($dates)) {
            return '-';
        }

        return implode('<br>', array_map(fn ($date) => $this->formatDate($date), $dates));
    }

    private function formatDate(?string $date): string
    {
        if (empty($date) || $date === '0000-00-00') {
            return '-';
        }

        return date('d M Y', strtotime($date));
    }

    private function formatDateTime(?string $datetime): string
    {
        if (empty($datetime) || $datetime === '0000-00-00 00:00:00') {
            return '-';
        }

        return date('d M Y H:i', strtotime($datetime));
    }

    private function statusBadge(int $detailStatus, int $mainStatus): string
    {
        if ($mainStatus === 1 || $detailStatus === 4) {
            return '<span class="badge badge-success">Selesai / Dibayar</span>';
        }

        $labels = [
            0 => ['badge-primary', 'Terbit SPK'],
            1 => ['badge-secondary', 'Turun Jatuh Tempo'],
            2 => ['badge-info', 'Pengajuan SPP'],
            3 => ['badge-warning', 'Pengajuan Pencairan'],
        ];

        [$class, $label] = $labels[$detailStatus] ?? ['badge-light', '-'];
        return '<span class="badge ' . $class . '">' . $label . '</span>';
    }

    function num($number)
    {
        return (float) str_replace(',', "", (string) $number);
    }
}
