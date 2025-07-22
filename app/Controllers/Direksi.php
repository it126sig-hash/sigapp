<?php

namespace App\Controllers;

use App\Controllers\Notif;

class Direksi extends BaseController
{
    protected $db;
    protected $notif;

    public function __construct()
    {
        $this->notif = new Notif();
        $this->db = db_connect();
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
            ->where('id', $q->harga_akhir)->get()->getResult()[0];
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
            $this->notif->tambah_notif("3;4;9", $notif, user_id(), $id_kavling, null); 

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
}
