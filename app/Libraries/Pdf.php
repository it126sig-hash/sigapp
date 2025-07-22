<?php

namespace App\Libraries;

use Dompdf\Dompdf;
use Dompdf\Options;

class Pdf {
    protected $dompdf;

    public function __construct() {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $this->dompdf = new Dompdf($options);
    }

    public function generate($html, $filename = '', $size = 'A4', $orientasi = "portrait", $stream = true) {
        $this->dompdf->loadHtml($html);
        $this->dompdf->setPaper($size, $orientasi);
        $this->dompdf->render();
        if ($stream) {
            $this->dompdf->stream($filename, array("Attachment" => 0));
        } else {
            file_put_contents($filename, $this->dompdf->output());
        }
    }
}