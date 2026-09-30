<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Services\Bpb\BpbPdfService;
use App\Services\Bpb\BpbService;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;
use RuntimeException;

class BpbController extends BaseController
{
    public function index()
    {
        return view('template', ['content' => 'bpb/index', 'data' => ['title' => 'Bon Permintaan Barang']]);
    }

    public function signature(int $id)
    {
        try {
            $path = (new BpbService())->signaturePath($id, (int) user_id());
            if (! is_file($path)) {
                throw PageNotFoundException::forPageNotFound();
            }
            return $this->response->setHeader('Content-Type', 'image/png')->setHeader('Cache-Control', 'private, no-store')->setBody(file_get_contents($path));
        } catch (\Throwable) {
            throw PageNotFoundException::forPageNotFound();
        }
    }

    public function pdf(int $id): ResponseInterface
    {
        $service = new BpbService();
        try {
            $detail = $service->detail($id, (int) user_id());
        } catch (\Throwable) {
            throw PageNotFoundException::forPageNotFound();
        }

        try {
            $pdf = (new BpbPdfService())->render($detail, (int) user_id());
            if (! str_starts_with($pdf, '%PDF-')) {
                throw new RuntimeException('Renderer tidak menghasilkan dokumen PDF yang valid.');
            }

            $filename = 'BPB-' . preg_replace('/[^A-Za-z0-9-]+/', '-', (string) ($detail['nomor'] ?: $detail['id'])) . '.pdf';

            return $this->response
                ->setStatusCode(200)
                ->setContentType('application/pdf')
                ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
                ->setHeader('Cache-Control', 'private, no-store, max-age=0')
                ->setHeader('Pragma', 'no-cache')
                ->setHeader('X-Content-Type-Options', 'nosniff')
                ->setBody($pdf);
        } catch (\Throwable $e) {
            log_message('error', 'BPB PDF generation failed for request {id}: {message}', [
                'id' => $id,
                'message' => $e->getMessage(),
            ]);

            return $this->response
                ->setStatusCode(500)
                ->setContentType('text/plain')
                ->setHeader('Cache-Control', 'no-store')
                ->setBody('PDF BPB gagal dibuat. Silakan coba kembali atau hubungi administrator.');
        }
    }

    public function verify(string $token)
    {
        $verification = (new BpbPdfService())->verify($token);
        return view('bpb/verify', $verification);
    }
}
