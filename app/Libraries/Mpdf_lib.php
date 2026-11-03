<?php

namespace App\Libraries;

use \Mpdf\Mpdf;

class Mpdf_lib
{
    protected $mpdf;
    protected ?bool $watermarkFooter = null;

    public function __construct() {}

    public function setWatermarkFooter(?bool $enable): self
    {
        $this->watermarkFooter = $enable;
        return $this;
    }

    public function isWatermarkFooterEnabled(?bool $explicit = null): bool
    {
        if ($explicit !== null) {
            return $explicit;
        }
        if ($this->watermarkFooter !== null) {
            return $this->watermarkFooter;
        }
        // Cek URL query parameter ?watermark=0 atau ?watermark=false
        if (function_exists('service')) {
            try {
                $request = service('request');
                if ($request && method_exists($request, 'getGet')) {
                    $val = $request->getGet('watermark');
                    if ($val !== null && $val !== '') {
                        return filter_var($val, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? ($val === '1');
                    }
                }
            } catch (\Throwable $e) {
                // Abaikan jika di context CLI tanpa request
            }
        }
        // Fallback ke config
        if (function_exists('config')) {
            try {
                $config = config('Pdf');
                if ($config && isset($config->enableWatermarkFooter)) {
                    return (bool) $config->enableWatermarkFooter;
                }
            } catch (\Throwable $e) {
            }
        }
        return true;
    }

    public function buildWatermarkFooterHtml(): string
    {
        $config = function_exists('config') ? config('Pdf') : null;
        $appName = $config->appName ?? 'SIGAPP';
        $includeUser = $config->includeUsername ?? true;
        $includePageNumbers = $config->includePageNumbers ?? true;

        $now = date('d/m/Y H:i') . ' WIB';
        $userLabel = '';
        if ($includeUser && function_exists('user') && function_exists('logged_in') && logged_in()) {
            $username = user()->username ?? null;
            if (!empty($username)) {
                $userLabel = ' oleh ' . (function_exists('esc') ? esc($username) : htmlspecialchars($username, ENT_QUOTES, 'UTF-8'));
            }
        }

        $pageNumberHtml = $includePageNumbers ? '<td style="text-align: right; vertical-align: middle; border: none; padding: 0; width: 30%;">Hal. {PAGENO} / {nbpg}</td>' : '';
        $escapedAppName = function_exists('esc') ? esc($appName) : htmlspecialchars($appName, ENT_QUOTES, 'UTF-8');

        return '<div class="sigapp-pdf-watermark-footer" style="border-top: 0.5px solid #dcdcdc; padding-top: 2px; margin-top: 2px; font-family: sans-serif; font-size: 6.5pt; color: #777777;">'
            . '<table width="100%" style="font-family: sans-serif; font-size: 6.5pt; color: #777777; border: none; border-collapse: collapse; margin: 0; padding: 0; width: 100%;">'
            . '<tr>'
            . '<td style="text-align: left; vertical-align: middle; border: none; padding: 0; width: 70%;">'
            . 'Dokumen ini dicetak otomatis melalui ' . $escapedAppName . ' pada ' . $now . $userLabel
            . '</td>'
            . $pageNumberHtml
            . '</tr>'
            . '</table>'
            . '</div>';
    }

    public function generate($html, $filename = '', $header = '', $mg = [15, 15, 25, 45], $format = 'A4', $stream = true, $footer = "", ?bool $watermarkFooter = null)
    {
        $mpdf = $this->createDocument($html, $header, $mg, $format, $footer, null, null, $watermarkFooter);

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
        ?callable $beforeOutput = null,
        ?bool $watermarkFooter = null
    ): string {
        $mpdf = $this->createDocument($html, $header, $mg, $format, $footer, $configure, $beforeOutput, $watermarkFooter);

        return $mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN);
    }

    private function createDocument(
        $html,
        $header,
        $mg,
        $format,
        $footer,
        ?callable $configure = null,
        ?callable $beforeOutput = null,
        ?bool $watermarkFooter = null
    ): Mpdf {
        $isWatermark = $this->isWatermarkFooterEnabled($watermarkFooter);
        $wmHtml = $isWatermark ? $this->buildWatermarkFooterHtml() : '';

        if ($isWatermark && isset($mg[3]) && $mg[3] < 8) {
            $mg[3] = 8;
        }

        $paper = $format;
        if ($format == 'F4' || $format == 'Folio') {
            $paper = [215.9, 330];
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
                    if ($pageFooter !== '') {
                        $mpdf->setHTMLFooter($isWatermark ? ($pageFooter . $wmHtml) : $pageFooter);
                    } else {
                        $isWatermark ? $mpdf->setHTMLFooter($wmHtml) : $mpdf->setFooter();
                    }
                } elseif ($i === 0) {
                    $firstFooter = $footer !== '' ? ($footer . ($isWatermark ? $wmHtml : '')) : ($isWatermark ? $wmHtml : '');
                    $firstFooter !== '' ? $mpdf->setHTMLFooter($firstFooter) : $mpdf->setFooter();
                } else {
                    if ($isWatermark) {
                        $mpdf->setHTMLFooter($wmHtml);
                    } else {
                        // Halaman lainnya → matikan footer
                        $mpdf->setFooter(); // <----- penting
                    }
                }

                $mpdf->WriteHTML($pageHtml);
                if ($i < count($html) - 1) {
                    $mpdf->AddPage();
                }
            }
        } else {
            if (preg_match('/<\/htmlpagefooter>/i', $html)) {
                if ($isWatermark) {
                    $html = (string) preg_replace('/<\/htmlpagefooter>/i', $wmHtml . '</htmlpagefooter>', $html, 1);
                }
            } else {
                $finalFooter = $footer !== '' ? ($footer . ($isWatermark ? $wmHtml : '')) : ($isWatermark ? $wmHtml : '');
                if ($finalFooter !== '') {
                    $mpdf->setHTMLFooter($finalFooter);
                }
            }

            $this->writeHtmlChunked($mpdf, $html);
        }

        if ($beforeOutput !== null) {
            $beforeOutput($mpdf);
        }

        return $mpdf;
    }

    /**
     * Pisahkan CSS dan body HTML lalu kirim via WriteHTML() terpisah
     * agar masing-masing chunk berada di bawah pcre.backtrack_limit.
     */
    private function writeHtmlChunked(Mpdf $mpdf, string $html): void
    {
        // 1. Ekstrak semua blok <style>...</style>
        $css = '';
        $html = (string) preg_replace_callback(
            '/<style[^>]*>(.*?)<\/style>/si',
            function (array $m) use (&$css): string {
                $css .= $m[1];
                return '';
            },
            $html
        );

        // 2. Kirim CSS terpisah (mode HEADER_CSS = 2)
        if ($css !== '') {
            $mpdf->WriteHTML($css, \Mpdf\HTMLParserMode::HEADER_CSS);
        }

        // 3. Kirim body — jika masih besar, pecah di batas tag
        $chunkSize = 500_000; // 500 KB per chunk
        if (strlen($html) <= $chunkSize) {
            $mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);
        } else {
            $offset = 0;
            $len    = strlen($html);
            while ($offset < $len) {
                $chunk = substr($html, $offset, $chunkSize);
                // Mundur ke batas penutup tag agar tidak memotong di tengah tag
                if ($offset + $chunkSize < $len) {
                    $lastClose = strrpos($chunk, '>');
                    if ($lastClose !== false) {
                        $chunk = substr($chunk, 0, $lastClose + 1);
                    }
                }
                $mpdf->WriteHTML($chunk, \Mpdf\HTMLParserMode::HTML_BODY);
                $offset += strlen($chunk);
            }
        }
    }
}
