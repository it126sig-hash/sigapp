<?php

namespace App\Controllers;

use App\Controllers\Notif;
use App\Services\FileAccessService;
use App\Services\Bpb\ProfileSignatureService;
use Myth\Auth\Password;

class Direksi extends BaseController
{
    protected $db;
    protected $notif;
    protected $fileAccessService;

    public function __construct()
    {
        $this->notif = new Notif();
        $this->db = db_connect();
        $this->fileAccessService = new FileAccessService();
    }
    function get_data_by_id($st = null)
    {

        $r['token'] = csrf_hash();

        $id_kavling = $this->request->getVar('id_kavling');

        $q = $this->db->table('kavling')
        ->select('harga_akhir, diskresi_harga, diskresi_memo, diskresi_oleh, diskresi_at, username')
        ->join('users', 'users.id = kavling.diskresi_oleh', 'left')
        ->where('id_kavling', $id_kavling)->get()->getResult()[0];

        if ($q) {
            $hj = $this->db->table('hargajual')
            ->select('hargajual.*,
            tipe_rumah,
            lokasi,
            file_name
            ')
            ->join('file_hargajual', 'file_hargajual.id_filehj = hargajual.id_filehj')
            ->join('tipe', 'tipe.id_tipe = hargajual.id_tipe')
            ->where('id', $q->harga_akhir)->get()->getResult();
        }
        if (!empty($hj)) {
            foreach ($hj as $row) {
                if (!empty($row->id_filehj)) {
                    $row->access_url = $this->fileAccessService->accessUrl('file_hargajual', (int) $row->id_filehj);
                }
            }
        }
        $r['data'] = $q;
        $r['harga_akhir'] = $hj;

        return $this->response->setJSON($r);
    }



    function save()
    {
        $response['token'] = csrf_hash();
        $id_kavling = $this->request->getVar('id_kavling');


        $f['diskresi_harga'] = $this->num($this->request->getPost('dir-diskresi_harga'));
        $f['diskresi_memo'] = $this->request->getPost('dir-diskresi_memo');
        $f['diskresi_at'] = date('Y-m-d H:i:s');
        $f['diskresi_oleh'] = user_id();
        
        $q = $this->db->table('kavling')->update($f, ['id_kavling' => $id_kavling]);

        if($q){
            $notif = 'Diskresi Harga Jual : Rp. '. number_format($f['diskresi_harga'], 0, ',', '.') .' - '. $f['diskresi_memo'];
            $this->notif->tambah_notif("3;4;9", $notif, user_id(), $id_kavling, null, \App\Enums\NotificationEvent::DISKRESI_HARGA, null, "siteplan/view?id_kavling=" . $id_kavling . "&tab=konsumen"); 

            $response['success'] = true;
            $response['messages'] = 'Berhasil melakukan perubahan data';
        }else{
            $response['success'] = false;
            $response['messages'] = 'Terjadi kesaahan saat melakukan perubahan data';
        }

        return $this->response->setJSON($response);
        
    }
   
    function if_where($var, $column, $condition, $query)
    {
        $x = 0;
        foreach ($column as $i) {
            if ($x === 0) {
                $query->like($i, $var['search']['value']);
            } else {
                $query->orLike($i, $var['search']['value']);
            }
            $query->where($condition);
            $x++;
        }
        return $query;
    }
    protected function num($d)
    {
        $d = str_replace('.', "", $d);
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

    /******************************** export *******************************/
    public function export_xlsx()
    {
        $table = $this->request->getVar('table');

        // $spreadsheet = new Spreadsheet();

        $firstHtmlString = '<table>' . $table . '</table>';

        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Html();
        $spreadsheet = $reader->loadFromString($firstHtmlString);
        $reader->setSheetIndex(1);
        // $spreadhseet = $reader->loadFromString($secondHtmlString, $spreadsheet);

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xls');
        // $writer->save('write.xls');

        // $sheet = $spreadsheet->getActiveSheet();
        // $sheet->setCellValue('A1', 'Employee Name');
        // $sheet->setCellValue('B1', 'Email Address');
        // $sheet->setCellValue('C1', 'Mobile No.');
        // $sheet->setCellValue('D1', 'Department');

        // $count = 2;

        // foreach ($data as $row) {
        //     $sheet->setCellValue('A' . $count, $row['employee_name']);

        //     $sheet->setCellValue('B' . $count, $row['employee_email']);

        //     $sheet->setCellValue('C' . $count, $row['employee_mobile']);

        //     $sheet->setCellValue('D' . $count, $row['employee_department']);

        //     $count++;
        // }
        ob_start();
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        $xlsData = ob_get_contents();
        ob_end_clean();
        $response = array(
            'status' => TRUE,
            'file' => "data:application/vnd.ms-excel;base64," . base64_encode($xlsData)
        );

        return $this->response->setJSON($response);
    }
    
    // ---- Tanda Tangan Surat Tagihan ----
    
    public function tagihan()
    {
        $data['content'] = 'direksi/list-tagihan-pending';
        $data['data']['title'] = 'Persetujuan Surat Tagihan';

        return view('template', $data);
    }

    public function get_pending_tagihan()
    {
        $list = $this->db->table('invoice_log')
            ->select('
                invoice_log.*, 
                konsumen.nama_konsumen, 
                proyek.nama_proyek, 
                jalan.nama_jalan, 
                kavling.no_kavling,
                COALESCE(usr.name, usr.username, "-") as pembuat
            ')
            ->join('konsumen', 'konsumen.id_konsumen = invoice_log.id_konsumen', 'left')
            ->join('kavling', 'kavling.id_kavling = invoice_log.id_kavling', 'left')
            ->join('jalan', 'jalan.id_jalan = kavling.id_jalan', 'left')
            ->join('cluster', 'cluster.id_cluster = jalan.id_cluster', 'left')
            ->join('proyek', 'cluster.id_proyek = proyek.id_proyek', 'left')
            ->join('users usr', 'usr.id = invoice_log.add_by', 'left')
            ->where('invoice_log.is_signed_direktur', 0)
            ->where('invoice_log.nomor_surat IS NOT NULL')
            ->orderBy('invoice_log.date_add', 'DESC')
            ->get()->getResult();

        foreach ($list as $item) {
            $totalNominal = 0;
            if (!empty($item->tagihan)) {
                $raw = $item->tagihan;
                if (is_string($raw)) {
                    $raw = json_decode(str_replace('&quot;', '"', $raw), true);
                }
                if (is_array($raw)) {
                    foreach ($raw as $t) {
                        $totalNominal += (float) ($t['nominal'] ?? 0);
                    }
                }
            }
            $item->total_nominal = $totalNominal;
        }

        return $this->response->setJSON([
            'data' => $list
        ]);
    }

    public function get_my_signature()
    {
        $userId = (int) user_id();
        $profileService = new ProfileSignatureService();
        $profile = $profileService->get($userId);

        return $this->response->setJSON([
            'success' => true,
            'data'    => $profile,
            'token'   => csrf_hash(),
        ]);
    }

    public function sign_tagihan()
    {
        $no_inv = $this->request->getPost('no_inv');
        $password = (string) $this->request->getPost('password');
        $signatureMethod = (string) ($this->request->getPost('signature_method') ?? 'profile');
        $signatureData = (string) $this->request->getPost('signature_data');
        
        if (empty($no_inv)) {
            return $this->response->setJSON(['success' => false, 'message' => 'ID Surat tidak ditemukan.', 'token' => csrf_hash()]);
        }

        if (empty($password)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Password wajib diisi untuk menandatangani surat.', 'token' => csrf_hash()]);
        }

        $userId = (int) user_id();
        $user = $this->db->table('users')->where('id', $userId)->get()->getRow();
        if (!$user || ! Password::verify($password, (string) $user->password_hash)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Password yang Anda masukkan salah.', 'token' => csrf_hash()]);
        }

        $profileService = new ProfileSignatureService();

        if ($signatureMethod === 'canvas') {
            if (empty($signatureData) || !str_starts_with($signatureData, 'data:image/')) {
                return $this->response->setJSON(['success' => false, 'message' => 'Silakan buat tanda tangan pada kanvas terlebih dahulu.', 'token' => csrf_hash()]);
            }

            try {
                // Simpan tanda tangan canvas ke profil user
                $profileService->save($userId, $password, $signatureData);
            } catch (\Throwable $e) {
                return $this->response->setJSON(['success' => false, 'message' => 'Gagal menyimpan tanda tangan kanvas: ' . $e->getMessage(), 'token' => csrf_hash()]);
            }
        } else {
            // Gunakan tanda tangan dari profil
            $profile = $profileService->get($userId);
            if (empty($profile['has_signature'])) {
                return $this->response->setJSON(['success' => false, 'message' => 'Anda belum memiliki tanda tangan di profil. Silakan pilih opsi Gambar di Kanvas.', 'token' => csrf_hash()]);
            }
        }

        $update = $this->db->table('invoice_log')
            ->where('no_inv', $no_inv)
            ->update([
                'is_signed_direktur' => 1,
                'signed_at'          => date('Y-m-d H:i:s'),
                'signed_by'          => $userId
            ]);

        if ($update) {
            return $this->response->setJSON(['success' => true, 'message' => 'Surat tagihan berhasil ditandatangani.', 'token' => csrf_hash()]);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Gagal menandatangani surat.', 'token' => csrf_hash()]);
    }

    public function get_history_tagihan()
    {
        $list = $this->db->table('invoice_log')
            ->select('
                invoice_log.*, 
                konsumen.nama_konsumen, 
                proyek.nama_proyek, 
                jalan.nama_jalan, 
                kavling.no_kavling,
                COALESCE(usr_add.name, usr_add.username, "-") as pembuat,
                COALESCE(usr_sign.name, usr_sign.username, "-") as diproses_oleh
            ')
            ->join('konsumen', 'konsumen.id_konsumen = invoice_log.id_konsumen', 'left')
            ->join('kavling', 'kavling.id_kavling = invoice_log.id_kavling', 'left')
            ->join('jalan', 'jalan.id_jalan = kavling.id_jalan', 'left')
            ->join('cluster', 'cluster.id_cluster = jalan.id_cluster', 'left')
            ->join('proyek', 'cluster.id_proyek = proyek.id_proyek', 'left')
            ->join('users usr_add', 'usr_add.id = invoice_log.add_by', 'left')
            ->join('users usr_sign', 'usr_sign.id = invoice_log.signed_by', 'left')
            ->whereIn('invoice_log.is_signed_direktur', [1, 2])
            ->where('invoice_log.nomor_surat IS NOT NULL')
            ->orderBy('invoice_log.signed_at', 'DESC')
            ->get()->getResult();

        foreach ($list as $item) {
            $totalNominal = 0;
            if (!empty($item->tagihan)) {
                $raw = $item->tagihan;
                if (is_string($raw)) {
                    $raw = json_decode(str_replace('&quot;', '"', $raw), true);
                }
                if (is_array($raw)) {
                    foreach ($raw as $t) {
                        $totalNominal += (float) ($t['nominal'] ?? 0);
                    }
                }
            }
            $item->total_nominal = $totalNominal;
        }

        return $this->response->setJSON([
            'data' => $list
        ]);
    }

    public function reject_tagihan()
    {
        $no_inv = $this->request->getPost('no_inv');
        $alasan = trim((string) $this->request->getPost('alasan'));
        $password = (string) $this->request->getPost('password');

        if (empty($no_inv)) {
            return $this->response->setJSON(['success' => false, 'message' => 'ID Surat tidak ditemukan.', 'token' => csrf_hash()]);
        }

        if (empty($alasan)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Alasan penolakan wajib diisi.', 'token' => csrf_hash()]);
        }

        if (empty($password)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Password wajib diisi untuk konfirmasi penolakan.', 'token' => csrf_hash()]);
        }

        $userId = (int) user_id();
        $user = $this->db->table('users')->where('id', $userId)->get()->getRow();
        if (!$user || ! Password::verify($password, (string) $user->password_hash)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Password yang Anda masukkan salah.', 'token' => csrf_hash()]);
        }

        $now = date('Y-m-d H:i:s');
        $update = $this->db->table('invoice_log')
            ->where('no_inv', $no_inv)
            ->update([
                'is_signed_direktur' => 2, // 2 = Ditolak
                'signed_at'          => $now,
                'signed_by'          => $userId,
                'keterangan_status'  => $alasan,
            ]);

        if ($update) {
            // Catat di invoice_status_log
            $this->db->table('invoice_status_log')->insert([
                'no_inv'     => $no_inv,
                'status'     => 'ditolak',
                'tanggal'    => date('Y-m-d'),
                'keterangan' => 'Tanda tangan ditolak oleh Direksi: ' . $alasan,
                'add_by'     => $userId,
                'date_add'   => $now,
            ]);

            // Kirim notifikasi jika pembuat ada
            try {
                $inv = $this->db->table('invoice_log')->where('no_inv', $no_inv)->get()->getRow();
                if ($inv && !empty($inv->add_by)) {
                    $msg = "Tanda tangan surat tagihan {$inv->nomor_surat} ditolak oleh Direksi. Alasan: {$alasan}";
                    $this->notif->tambah_notif(
                        "3", // Keuangan
                        $msg,
                        $userId,
                        $inv->id_kavling ?? null,
                        null,
                        null,
                        null,
                        "tagihan/list"
                    );
                }
            } catch (\Throwable $e) {
                // Ignore notif error
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Surat tagihan berhasil ditolak.',
                'token'   => csrf_hash(),
            ]);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Gagal menolak surat tagihan.', 'token' => csrf_hash()]);
    }
}
