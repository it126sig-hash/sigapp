<?php

namespace App\Libraries;

use \Mpdf\Mpdf;

class Mpdf_lib
{
    protected $mpdf;

    public function __construct() {}

    public function generate($html, $filename = '', $header = '', $mg = [15, 15, 25, 45], $format = 'A4', $stream = true, $footer = "")
    {
        $mpdf = $this->createDocument($html, $header, $mg, $format, $footer);

        if ($stream) {
            $mpdf->Output($filename, \Mpdf\Output\Destination::INLINE);
        } else {
            $mpdf->Output($filename, \Mpdf\Output\Destination::FILE);
        }
    }

    /**
     * Render a PDF to bytes without sending headers or writing to the output buffer.
     *
     * @param mixed $html
     * @param string $header
     * @param array<int, int|float> $mg
     * @param string|array<int, int|float> $format
     * @param string $footer
     */
    public function renderBinary(
        $html,
        $header = '',
        $mg = [15, 15, 25, 45],
        $format = 'A4',
        $footer = '',
        ?callable $configure = null,
        ?callable $beforeOutput = null
    ): string
    {
        $mpdf = $this->createDocument($html, $header, $mg, $format, $footer, $configure, $beforeOutput);

        return $mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN);
    }

    private function createDocument($html, $header, $mg, $format, $footer, ?callable $configure = null, ?callable $beforeOutput = null): Mpdf
    {
        $paper = $format;
        if ($format == 'F4' || $format == 'Folio') {
            $paper = [210, 330];
        }
        $mpdf = new Mpdf(
            [
                'margin_left' => $mg[0],
                'margin_right' => $mg[1],
                'margin_top' => $mg[2],
                'margin_bottom' => $mg[3],
                'format' => $paper
            ]
        );
        $mpdf->SetHTMLHeader($header);

        if ($configure !== null) {
            $configure($mpdf);
        }


        if (is_array($html)) {
            for ($i = 0; $i < count($html); $i++) {
                $page = $html[$i];
                $pageHtml = is_array($page) ? ($page['html'] ?? '') : $page;
                if (is_array($page)) {
                    $pageFooter = (string) ($page['footer'] ?? '');
                    $pageFooter !== '' ? $mpdf->setHTMLFooter($pageFooter) : $mpdf->setFooter();
                } elseif ($i == 0) {
                    $mpdf->setHTMLFooter($footer);
                } else {
                    // Halaman lainnya → matikan footer
                    $mpdf->setFooter(); // <----- penting
                }

                $mpdf->WriteHTML($pageHtml);
                if ($i < count($html) - 1) {
                    $mpdf->AddPage();
                }
            }
        } else {
            $mpdf->WriteHTML($html);
        }

        if ($beforeOutput !== null) {
            $beforeOutput($mpdf);
        }

        return $mpdf;
    }
}
