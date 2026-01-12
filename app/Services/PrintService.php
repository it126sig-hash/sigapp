<?php

namespace App\Services;


use CodeIgniter\HTTP\Response;
use App\Models\ProfilePerusahaanModel;
use App\Repositories\TransaksiRepository;
use App\Models\ProyekModel;
use App\Models\KeuanganModel;
use App\Libraries\Mpdf_lib;
use App\Repositories\LogPembayaranRepository;
use App\Repositories\KonsumenRepository;
use App\Repositories\KavlingRepository;
use App\Repositories\KeuanganRepository;
use CodeIgniter\HTTP\ResponseInterface;
use App\Repositories\PosisiKonsumenRepository;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;


class PrintService
{
    protected $db;
    protected $comproModel;
    protected $mpdf;
    protected $transaksi;
    protected $proyek;
    protected $keuanganModel;
    protected $lpModel;
    protected $konsumen;
    protected $kavling;
    protected $keuRepo;
    protected $posisiKonsumen;
    public function __construct()
    {
        $this->comproModel = new ProfilePerusahaanModel();
        $this->db = db_connect();
        $this->mpdf = new Mpdf_lib();
        $this->proyek = new ProyekModel();
        $this->transaksi = new TransaksiRepository();
        $this->keuanganModel = new KeuanganModel();
        $this->lpModel = new LogPembayaranRepository();
        $this->konsumen = new KonsumenRepository();
        $this->kavling = new KavlingRepository();
        $this->keuRepo = new KeuanganRepository();
        $this->posisiKonsumen = new PosisiKonsumenRepository();
    }

    public function printKuitansi($var)
    {
        $id = trim((string) $var->getVar('e'));
        $id_mkdt = trim((string) $var->getVar('e2'));
        $id_poryek = trim((string) $var->getVar('e3'));

        $data['pembayaran'] = $this->lpModel->getRiwayatBayarByIdPembayran($id);
        $data['detail'] = $this->lpModel->getDetailRiwayatBayarById($id);
        $data['list'] = $this->keuRepo->getLIKeu();
        $data['konsumen'] = $this->konsumen->getKonsumenTransaksi($id_mkdt);
        $data['proyek'] = $this->proyek->find($id_poryek);
        $data['kavling'] = $this->kavling->getKavlingByIdMkdt($id_mkdt);

        $filename = $data['konsumen']->nama_konsumen . '-Kuitansi Pembayaran';

        $html[] = view('pdf/kuitansi_pembayaran', $data);

        $mg = [2, 2, 2, 2];

        // $mg = [$kop->pml, $kop->pmr, $kop->pmt, $kop->pmb];

        $this->mpdf->generate($html, $filename, $header = '', $mg, [210, 148]);

        exit();
    }
    public function printKuitansiUm($var)
    {
        $id = trim((string) $var->getVar('e'));
        $id_mkdt = trim((string) $var->getVar('e2'));
        $id_poryek = trim((string) $var->getVar('e3'));

        $data['pembayaran'] = $this->lpModel->getRiwayatBayarByIdPembayran($id);
        $data['list'] = $this->keuRepo->getLIKeu();
        $data['konsumen'] = $this->konsumen->getKonsumenTransaksi($id_mkdt);
        $data['proyek'] = $this->proyek->find($id_poryek);
        $data['kavling'] = $this->kavling->getKavlingByIdMkdt($id_mkdt);

        $filename = $data['konsumen']->nama_konsumen . '- Kuitansi Pembayaran';

        $html[] = view('pdf/kuitansi_pembayaran-uangmuka', $data);

        $mg = [2, 2, 2, 2];

        // $mg = [$kop->pml, $kop->pmr, $kop->pmt, $kop->pmb];

        $this->mpdf->generate($html, $filename, $header = '', $mg, [210, 148]);

        exit();
    }
    function exportPBataloskonPdf($id_proyek, $id_cluster, $id_jalan)
    {
        $st = "Batal";
        $dataRumah = $this->posisiKonsumen->getQueryBatal();
        if ($id_proyek != null) {
            $dataRumah->where('proyek.id_proyek', $id_proyek);
        }
        if ($id_cluster != null) {
            $dataRumah->where('cluster.id_cluster', $id_cluster);
        }
        if ($id_jalan != null) {
            $dataRumah->where('jalan.id_jalan', $id_jalan);
        }

        $data['poskon'] = $dataRumah->get()->getResult();
        $data['status'] = $st;


        // Persiapan Folder Penyimpanan
        $subFolder = date('Ym'); // Folder berdasarkan tanggal agar rapi
        $targetDir = 'upload/poskon/' . $subFolder . '/';

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        // Penamaan File
        $randomName = bin2hex(random_bytes(5)) . '.pdf';
        $filename = 'Poskon_' . $st . '_' . date('His') . '.pdf';
        $fullPath = $targetDir . $randomName;

        // Generate HTML View
        $html[] = view('pdf/poskon-batal', $data);
        $mg = [2, 2, 2, 2];

        $this->mpdf->generate($html, $fullPath, $header = '', $mg, "A3-L", false);

        // Response JSON
        $response = array(
            'status'     => TRUE,
            'message'    => 'File berhasil disimpan di server',
            'tipe'       => 'pdf',
            'randomName' => $randomName,
            'filename'   => $filename,
            'path'       => $targetDir,
            'file'       => base_url($fullPath) // URL lengkap untuk akses file
        );

        return $response;
    }
    public function exportPoskonPdf($id_proyek, $id_cluster, $id_jalan, $status)
    {
        if ($status == "batal") {
            $response = $this->exportPBataloskonPdf($id_proyek, $id_cluster, $id_jalan);
            return $response;
        }
        // var_dump($status);
        // die;
        $st = $status == "Aktif" ? "Booking" : $status;
        $dataRumah = $this->posisiKonsumen->getBaseQuery($st);
        if ($id_proyek != null) {
            $dataRumah->where('proyek.id_proyek', $id_proyek);
        }
        if ($id_cluster != null) {
            $dataRumah->where('cluster.id_cluster', $id_cluster);
        }
        if ($id_jalan != null) {
            $dataRumah->where('jalan.id_jalan', $id_jalan);
        }

        $data['poskon'] = $dataRumah->get()->getResult();
        $data['status'] = $status;

        // Persiapan Folder Penyimpanan
        $subFolder = date('Ym'); // Folder berdasarkan tanggal agar rapi
        $targetDir = 'upload/poskon/' . $subFolder . '/';

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        // Penamaan File
        $randomName = bin2hex(random_bytes(5)) . '.pdf';
        $filename = 'Poskon_' . $status . '_' . date('His') . '.pdf';
        $fullPath = $targetDir . $randomName;

        // Generate HTML View
        $html[] = view('pdf/poskon', $data);
        $mg = [2, 2, 2, 2];

        $this->mpdf->generate($html, $fullPath, $header = '', $mg, "A3-L", false);

        // Response JSON
        $response = array(
            'status'     => TRUE,
            'message'    => 'File berhasil disimpan di server',
            'tipe'       => 'pdf',
            'randomName' => $randomName,
            'filename'   => $filename,
            'path'       => $targetDir,
            'file'       => base_url($fullPath) // URL lengkap untuk akses file
        );

        return $response;
    }
    public function exportPoskonXlsx($id_proyek, $id_cluster, $id_jalan, $status)
    {
        $st = $status == "Aktif" ? "Booking" : $status;

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // --- 1. SETTING HEADER (MERGING) ---
        // Row 1 & 2 & 3 (Vertical Merges untuk yang single)
        $sheet->mergeCells('A1:A3')->setCellValue('A1', 'NO');
        $sheet->mergeCells('B1:C1')->setCellValue('B1', 'KAVLING');
        $sheet->mergeCells('B2:B3')->setCellValue('B2', 'BLOK');
        $sheet->mergeCells('C2:C3')->setCellValue('C2', 'NO');

        $sheet->mergeCells('D1:D3')->setCellValue('D1', 'TYPE');
        $sheet->mergeCells('E1:E3')->setCellValue('E1', 'NAMA KONSUMEN');
        $sheet->mergeCells('F1:F3')->setCellValue('F1', 'SALES');
        $sheet->mergeCells('G1:G3')->setCellValue('G1', 'TGL BOOKING');
        $sheet->mergeCells('H1:H3')->setCellValue('H1', 'TGL WAWANCARA');

        // Marketing Data
        $sheet->mergeCells('I1:N1')->setCellValue('I1', 'MARKETING DATA');
        $sheet->mergeCells('I2:J2')->setCellValue('I2', 'PENGAJUAN');
        $sheet->setCellValue('I3', 'TUNAI/KPR');
        $sheet->setCellValue('J3', 'BANK');
        $sheet->mergeCells('K2:K3')->setCellValue('K2', 'STATUS');
        $sheet->mergeCells('L2:M2')->setCellValue('L2', 'SP3K');
        $sheet->setCellValue('L3', 'TERBIT');
        $sheet->setCellValue('M3', 'EXPIRED');
        $sheet->mergeCells('N2:N3')->setCellValue('N2', 'SIKASEP');

        // Keuangan
        $sheet->mergeCells('O1:R1')->setCellValue('O1', 'KEUANGAN');
        $sheet->mergeCells('O2:O3')->setCellValue('O2', 'TUNAI');
        $sheet->mergeCells('P2:P3')->setCellValue('P2', 'UM');
        $sheet->mergeCells('Q2:Q3')->setCellValue('Q2', 'B. ADM');
        $sheet->mergeCells('R2:R3')->setCellValue('R2', 'BIAYA-BIAYA');

        // Produksi
        $sheet->mergeCells('S1:U1')->setCellValue('S1', 'PRODUKSI');
        $sheet->mergeCells('S2:T2')->setCellValue('S2', 'BANGUNAN');
        $sheet->setCellValue('S3', '%');
        $sheet->setCellValue('T3', 'LPA');
        $sheet->mergeCells('U2:U3')->setCellValue('U2', 'LISTRIK');

        // Legal & GA
        $sheet->mergeCells('V1:X1')->setCellValue('V1', 'LEGAL');
        $sheet->mergeCells('V2:V3')->setCellValue('V2', 'HGB');
        $sheet->mergeCells('W2:W3')->setCellValue('W2', 'IMB');
        $sheet->mergeCells('X2:X3')->setCellValue('X2', 'PBB');

        $sheet->mergeCells('Y1:Y1')->setCellValue('Y1', 'GA');
        $sheet->mergeCells('Y2:Y3')->setCellValue('Y2', 'SIKUMBANG');

        // --- 2. STYLING HEADER ---
        $headerStyle = [
            'font' => ['bold' => true],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F2F2F2'],
            ],
        ];
        $sheet->getStyle('A1:Y3')->applyFromArray($headerStyle);

        //query data
        $dataRumah = $this->posisiKonsumen->getBaseQuery($st);
        if ($id_proyek != null) {
            $dataRumah->where('proyek.id_proyek', $id_proyek);
        }
        if ($id_cluster != null) {
            $dataRumah->where('cluster.id_cluster', $id_cluster);
        }
        if ($id_jalan != null) {
            $dataRumah->where('jalan.id_jalan', $id_jalan);
        }

        $dataRumah = $dataRumah->get()->getResult();
        $column = 4; // Data dimulai dari baris ke-4
        $no = 1;

        foreach ($dataRumah as $row) {
            $total = $row->um + $row->adm + $row->bb;
            $bayar = $row->total_um + $row->total_adm + $row->total_bb;
            $persen_tunai = "-";

            $um = $row->um;
            $adm = $row->adm;
            $bb = $row->bb;

            $kpr = "KPR";
            if ($row->is_kpr == 0) {
                $kpr = 'TUNAI';
                $um = "-";
                $adm = "-";
                $bb = "-";
                if ($bayar <= 0) {
                    $persen_tunai = '0%';
                } else {
                    $persen_tunai = round(($bayar / $total) * 100) . "%";
                }
            } else {
                $um = $row->total_um <= 0 ? "0%" : round(($row->total_um / $row->um) * 100) . "%";
                $adm = $row->total_adm <= 0 ? "0%" : round(($row->total_adm / $row->adm) * 100) . "%";
                $bb = $row->total_bb <= 0 ? "0%" : round(($row->total_bb / $row->bb) * 100) . "%";
            }

            $sheet->setCellValue('A' . $column, $no++);
            $sheet->setCellValue('B' . $column, $row->nama_jalan);
            $sheet->setCellValue('C' . $column, $row->no_kavling);
            $sheet->setCellValue('D' . $column, $row->tipe_rumah);
            $sheet->setCellValue('E' . $column, $row->nama_konsumen);
            $sheet->setCellValue('F' . $column, $row->sales);
            $sheet->setCellValue('G' . $column, $row->booking_tgl == "0000-00-00" ? "" : $row->booking_tgl);
            $sheet->setCellValue('H' . $column, $row->wawancara_tgl == "0000-00-00" ? "" : $row->wawancara_tgl);
            $sheet->setCellValue('I' . $column, $kpr);
            $sheet->setCellValue('J' . $column, $row->bank);
            $sheet->setCellValue('K' . $column, $row->keterangan);
            $sheet->setCellValue('L' . $column, $row->sp3k_tgl == "0000-00-00" ? "" : $row->sp3k_tgl);
            $sheet->setCellValue('M' . $column, $row->sp3k_tgl_exp == "0000-00-00" ? "" : $row->sp3k_tgl_exp);
            $sheet->setCellValue('N' . $column, $row->sikasep);
            $sheet->setCellValue('O' . $column, $persen_tunai);
            $sheet->setCellValue('P' . $column, $um);
            $sheet->setCellValue('Q' . $column, $adm);
            $sheet->setCellValue('R' . $column, $bb);
            $sheet->setCellValue('S' . $column, $row->progres_bangunan);
            $sheet->setCellValue('T' . $column, $row->lpa ? '✓' : '');
            $sheet->setCellValue('U' . $column, $row->st_listrik ? '✓' : '');
            $sheet->setCellValue('V' . $column, $row->sertifikat_split_no_hgb);
            $sheet->setCellValue('W' . $column, $row->pbg_no);
            $sheet->setCellValue('X' . $column, $row->pbb_pecah_nop);
            $sheet->setCellValue('Y' . $column, $row->sikumbang);

            // Beri border untuk baris data
            $sheet->getStyle('A' . $column . ':Y' . $column)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $column++;
        }

        // Auto size kolom agar rapi
        foreach (range('A', 'Y') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }



        $filename = "Poskon " . $status . " Per " . date('d-m-Y') . " " . $dataRumah[0]->nama_proyek . ".xlsx";
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        // 1. Tentukan folder tujuan (Public path biasanya digunakan agar bisa diakses via URL)
        $subFolder = date('Ym'); // Hasil: 202512
        $directory = ROOTPATH . 'upload/poskon/' . $subFolder;

        // 2. Cek apakah folder ada, jika tidak, buat folder secara recursive
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
        // var_dump($directory);
        // die();

        // 3. Generate nama file random
        $randomName = bin2hex(random_bytes(10)) . '.xlsx'; // Nama random seperti: a1b2c3d4e5...xlsx
        $fullPath = $directory . '/' . $randomName;

        // 4. Proses Simpan ke Server (Bukan ke browser)
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($fullPath);

        // 5. Jika Anda masih butuh data base64 untuk response (opsional)
        // Anda bisa membaca file yang baru saja disimpan
        $xlsData = file_get_contents($fullPath);

        $response = array(
            'status'   => TRUE,
            'message'  => 'File berhasil disimpan di server',
            'tipe'  => 'xlsx',
            'randomName' => $randomName,
            'filename' => $filename,
            'path'     => 'upload/poskon/' . $subFolder . "/", // Path untuk akses URL
            'file'     => "data:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;base64," . base64_encode($xlsData)
        );

        return $response;
    }
}
