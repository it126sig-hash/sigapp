<?php

namespace App\Services\Bpb;

use App\Libraries\Mpdf_lib;
use App\Libraries\SimpleQrCode;
use App\Services\FileAccessService;
use DateTimeImmutable;
use DateTimeZone;
use IntlDateFormatter;
use Mpdf\Mpdf;
use RuntimeException;

final class BpbPdfService
{
    private FileAccessService $fileAccess;
    private BpbService $bpbService;

    public function __construct(?FileAccessService $fileAccess = null, ?BpbService $bpbService = null)
    {
        $this->fileAccess = $fileAccess ?? new FileAccessService();
        $this->bpbService = $bpbService ?? new BpbService();
    }

    public function render(array $detail, int $userId): string
    {
        $temporaryFiles = [];

        try {
            foreach ($detail['signatures'] as &$signature) {
                try {
                    $path = $this->bpbService->signaturePath((int) $signature['id'], $userId);
                    if (is_file($path)) {
                        $signature['data_uri'] = 'data:image/png;base64,' . base64_encode((string) file_get_contents($path));
                    } else {
                        $signature['data_uri'] = null;
                    }
                } catch (\Throwable) {
                    $signature['data_uri'] = null;
                }
            }
            unset($signature);

            $approved = in_array($detail['status'], [
                BpbWorkflow::APPROVED,
                BpbWorkflow::PROCESSED,
                BpbWorkflow::DISBURSED,
                BpbWorkflow::PENDING,
                BpbWorkflow::PURCHASED,
            ], true);
            $detail['approved_document'] = $approved;
            $detail['submitted_date_label'] = $this->formatIndonesianDate(
                (string) ($detail['submitted_at'] ?? $detail['created_at'] ?? '')
            );
            $detail['qr_data_uri'] = $approved
                ? 'data:image/png;base64,' . base64_encode(SimpleQrCode::encodeText(
                    site_url('bpb/verify/' . $detail['verification_token'])
                )->toPng(12, 4))
                : null;

            $attachments = $this->prepareAttachments($detail['files'] ?? [], $temporaryFiles);
            $html = view('bpb/pdf', ['bpb' => $detail]);

            return (new Mpdf_lib())->renderBinary(
                $html,
                '',
                [7, 7, 7, 7],
                'A5-L',
                '',
                static function (Mpdf $mpdf) use ($approved): void {
                    if (! $approved) {
                        $mpdf->SetWatermarkText('BELUM DISETUJUI', 0.2);
                        $mpdf->showWatermarkText = true;
                        $mpdf->watermark_size = 36;
                        $mpdf->watermarkAngle = -25;
                    }
                },
                fn (Mpdf $mpdf) => $this->appendAttachments($mpdf, $attachments)
            );
        } finally {
            foreach ($temporaryFiles as $path) {
                if (is_file($path)) {
                    @unlink($path);
                }
            }
        }
    }

    /** @return array{valid: bool, bpb: ?array, signatures: array} */
    public function verify(string $token): array
    {
        $db = db_connect();
        $request = $db->table('bpb_requests')
            ->select('id,nomor,status,current_document_hash,applicant_user_id,applicant_name,applicant_department,cc_user_id,approver_user_id')
            ->where('verification_token', $token)
            ->get()
            ->getRowArray();

        if (! $request) {
            return ['valid' => false, 'bpb' => null, 'signatures' => []];
        }

        $signatures = $db->table('bpb_signatures')
            ->select('role,signer_name,signed_at,document_hash')
            ->where(['bpb_id' => $request['id'], 'revoked_at' => null])
            ->orderBy('signed_at')
            ->get()
            ->getResultArray();
        $items = $db->table('bpb_items')
            ->where('bpb_id', $request['id'])
            ->orderBy('item_order')
            ->get()
            ->getResultArray();
        $files = $db->table('bpb_files')
            ->where('bpb_id', $request['id'])
            ->whereIn('category', ['request', 'purchase'])
            ->orderBy('id')
            ->get()
            ->getResultArray();
        $requestFiles = array_values(array_filter(
            $files,
            static fn (array $file): bool => $file['category'] === 'request'
        ));

        $valid = $signatures !== [] && hash_equals(
            (string) $request['current_document_hash'],
            BpbDocumentHasher::hash($request, $items, $requestFiles)
        );

        foreach ($files as $file) {
            try {
                $path = $this->fileAccess->privatePath((string) $file['logical_path']);
                $actualHash = is_file($path) ? hash_file('sha256', $path) : false;
            } catch (\Throwable) {
                $actualHash = false;
            }
            $valid = $valid && $actualHash !== false && hash_equals((string) $file['file_sha256'], $actualHash);
        }

        foreach ($signatures as $signature) {
            $valid = $valid && hash_equals(
                (string) $request['current_document_hash'],
                (string) $signature['document_hash']
            );
        }

        $request['status_label'] = BpbWorkflow::label((string) $request['status']);
        return ['valid' => $valid, 'bpb' => $request, 'signatures' => $signatures];
    }

    /** @return array<int, array{path: string, mime: string, category: string, pages?: array<int, array{width: float, height: float}>}> */
    private function prepareAttachments(array $files, array &$temporaryFiles): array
    {
        usort($files, static function (array $left, array $right): int {
            $order = ['request' => 0, 'purchase' => 1];
            return ($order[$left['category'] ?? ''] ?? 2) <=> ($order[$right['category'] ?? ''] ?? 2)
                ?: ((int) $left['id'] <=> (int) $right['id']);
        });

        $attachments = [];
        foreach ($files as $file) {
            $path = $this->fileAccess->privatePath((string) ($file['logical_path'] ?? ''));
            if (! is_file($path)) {
                throw new RuntimeException('Lampiran BPB tidak ditemukan: ' . ($file['original_name'] ?? 'file'));
            }

            $mime = strtolower((string) (mime_content_type($path) ?: ($file['mime_type'] ?? '')));
            $category = (string) ($file['category'] ?? 'request');
            if ($mime === 'application/pdf') {
                try {
                    $inspector = new Mpdf();
                    $pageCount = $inspector->setSourceFile($path);
                    $pages = [];
                    for ($page = 1; $page <= $pageCount; $page++) {
                        $template = $inspector->importPage($page);
                        $size = $inspector->getTemplateSize($template);
                        if ($size === false) {
                            throw new RuntimeException('Ukuran halaman PDF lampiran tidak dapat dibaca.');
                        }
                        $pages[] = ['width' => (float) $size['width'], 'height' => (float) $size['height']];
                    }
                } catch (\Throwable $e) {
                    throw new RuntimeException('PDF lampiran BPB tidak dapat dibaca: ' . ($file['original_name'] ?? 'file'), 0, $e);
                }
                $attachments[] = ['path' => $path, 'mime' => $mime, 'category' => $category, 'pages' => $pages];
                continue;
            }
            if (! in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
                throw new RuntimeException('Format lampiran BPB tidak dapat dicetak: ' . ($file['original_name'] ?? 'file'));
            }

            $imageBytes = file_get_contents($path);
            $image = $imageBytes === false ? false : @imagecreatefromstring($imageBytes);
            if ($image === false) {
                throw new RuntimeException('Gambar lampiran BPB tidak dapat dibaca: ' . ($file['original_name'] ?? 'file'));
            }

            $tempPath = tempnam(WRITEPATH . 'cache', 'bpb_pdf_image_');
            if ($tempPath === false) {
                imagedestroy($image);
                throw new RuntimeException('Tidak dapat menyiapkan gambar lampiran BPB untuk PDF.');
            }

            $width = imagesx($image);
            $height = imagesy($image);
            $canvas = imagecreatetruecolor($width, $height);
            $white = imagecolorallocate($canvas, 255, 255, 255);
            imagefilledrectangle($canvas, 0, 0, $width, $height, $white);
            imagecopy($canvas, $image, 0, 0, 0, 0, $width, $height);
            $saved = imagepng($canvas, $tempPath);
            imagedestroy($canvas);
            imagedestroy($image);

            if (! $saved) {
                @unlink($tempPath);
                throw new RuntimeException('Gambar lampiran BPB gagal disiapkan untuk PDF.');
            }

            $temporaryFiles[] = $tempPath;
            $attachments[] = ['path' => $tempPath, 'mime' => 'image/png', 'category' => $category];
        }

        return $attachments;
    }

    /** @param array<int, array{path: string, mime: string, category: string, pages?: array<int, array{width: float, height: float}>}> $attachments */
    private function appendAttachments(Mpdf $mpdf, array $attachments): void
    {
        foreach ($attachments as $attachment) {
            if ($attachment['mime'] === 'application/pdf') {
                try {
                    $pageCount = $mpdf->setSourceFile($attachment['path']);
                    for ($page = 1; $page <= $pageCount; $page++) {
                        $template = $mpdf->importPage($page);
                        $size = $attachment['pages'][$page - 1] ?? null;
                        if ($size === null) {
                            throw new RuntimeException('Ukuran halaman PDF lampiran tidak dapat dibaca.');
                        }
                        $this->addImportedPage($mpdf, $template, (float) $size['width'], (float) $size['height']);
                    }
                } catch (\Throwable $e) {
                    throw new RuntimeException('PDF lampiran BPB tidak dapat digabungkan.', 0, $e);
                }
                continue;
            }

            $dimensions = @getimagesize($attachment['path']);
            if (! $dimensions || $dimensions[0] < 1 || $dimensions[1] < 1) {
                throw new RuntimeException('Ukuran gambar lampiran BPB tidak valid.');
            }
            $width = (float) $dimensions[0];
            $height = (float) $dimensions[1];
            $landscape = $width >= $height;
            $maxPageWidth = $landscape ? 297.0 : 210.0;
            $maxPageHeight = $landscape ? 210.0 : 297.0;
            $pageScale = min($maxPageWidth / $width, $maxPageHeight / $height);
            $pageWidth = $width * $pageScale;
            $pageHeight = $height * $pageScale;
            $imageScale = min(($pageWidth * 0.96) / $width, ($pageHeight * 0.96) / $height);
            $drawWidth = $width * $imageScale;
            $drawHeight = $height * $imageScale;
            $portraitSheet = $landscape ? [$pageHeight, $pageWidth] : [$pageWidth, $pageHeight];

            $mpdf->AddPageByArray([
                'orientation' => $landscape ? 'L' : 'P',
                'sheet-size' => $portraitSheet,
                'mgl' => 0,
                'mgr' => 0,
                'mgt' => 0,
                'mgb' => 0,
            ]);
            $mpdf->Image(
                $attachment['path'],
                ($pageWidth - $drawWidth) / 2,
                ($pageHeight - $drawHeight) / 2,
                $drawWidth,
                $drawHeight,
                'png',
                '',
                true,
                false
            );
        }
    }

    private function addImportedPage(Mpdf $mpdf, string $template, float $sourceWidth, float $sourceHeight): void
    {
        $landscape = $sourceWidth >= $sourceHeight;
        $pageWidth = $sourceWidth;
        $pageHeight = $sourceHeight;
        $portraitSheet = $landscape ? [$sourceHeight, $sourceWidth] : [$sourceWidth, $sourceHeight];

        $mpdf->AddPageByArray([
            'orientation' => $landscape ? 'L' : 'P',
            'sheet-size' => $portraitSheet,
            'mgl' => 0,
            'mgr' => 0,
            'mgt' => 0,
            'mgb' => 0,
        ]);
        $mpdf->useTemplate(
            $template,
            0,
            0,
            $pageWidth,
            $pageHeight
        );
    }

    private function formatIndonesianDate(string $date): string
    {
        if ($date === '') {
            return 'Bandung, -';
        }

        $value = new DateTimeImmutable($date, new DateTimeZone('Asia/Jakarta'));
        $formatter = new IntlDateFormatter(
            'id_ID',
            IntlDateFormatter::LONG,
            IntlDateFormatter::NONE,
            'Asia/Jakarta',
            IntlDateFormatter::GREGORIAN,
            'd MMMM yyyy'
        );
        return 'Bandung, ' . ($formatter->format($value) ?: $value->format('d-m-Y'));
    }
}
