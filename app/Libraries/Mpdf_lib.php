<?php

namespace App\Libraries;

use \Mpdf\Mpdf;

class Mpdf_lib {
    protected $mpdf;

    public function __construct() {
        
    }

    public function generate($html, $filename = '', $header='', $mg = [15, 15, 25, 45], $format= 'A4', $stream = true) {
        $mpdf = new Mpdf(
            [
            'margin_left' => $mg[0],
            'margin_right' => $mg[1],
            'margin_top' => $mg[2],
            'margin_bottom' => $mg[3],
            'format' => $format
            ]
        );
        $mpdf->SetHTMLHeader($header);

        if( is_array($html) ) {
            for($i = 0; $i < count($html); $i++) {
                $mpdf->WriteHTML($html[$i]);
                $mpdf->AddPage();
            }
        }else{
            $mpdf->WriteHTML($html);
        }
        
        if ($stream) {
            $mpdf->Output($filename, \Mpdf\Output\Destination::INLINE);
        } else {
            $mpdf->Output($filename, \Mpdf\Output\Destination::FILE);
        }
    }
}
