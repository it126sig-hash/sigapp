<?php

namespace App\Controllers\Web\Keuangan;

use App\Controllers\BaseController;
use App\Services\Keuangan\PrintService;
use CodeIgniter\HTTP\ResponseInterface;

class PrintController extends BaseController
{
    protected PrintService $printService;

    public function __construct(?PrintService $printService = null)
    {
        $this->printService = $printService ?? new PrintService();
    }

    public function printSpptb()
    {
        $idKavling = trim((string) $this->request->getVar('id_kavling'));
        $idMkdt = trim((string) $this->request->getVar('id_mkdt'));
        $idProyek = trim((string) $this->request->getVar('id_proyek'));

        if ($idMkdt === 'null' || $idMkdt === '') {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => 'Data konsumen belum tersimpan (id_mkdt kosong).',
            ]);
        }

        $validation = \Config\Services::validation();
        $rules = [
            'id_kavling' => 'required|is_natural_no_zero',
            'id_mkdt' => 'required|is_natural_no_zero',
            'id_proyek' => 'required|is_natural_no_zero',
        ];
        if (! $validation->setRules($rules)->run(['id_kavling' => $idKavling, 'id_mkdt' => $idMkdt, 'id_proyek' => $idProyek])) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => 'error',
                'message' => 'Validasi gagal.',
                'errors' => $validation->getErrors(),
            ]);
        }

        try {
            $this->printService->printSpptb((int) $idKavling, (int) $idMkdt, (int) $idProyek);
            exit();
        } catch (\Throwable $e) {
            log_message('error', 'SPPTB print error: {msg}', ['msg' => $e->getMessage()]);
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function printKuitansi(): ?ResponseInterface
    {
        return $this->handleKuitansiPrint(fn () => $this->printService->printKuitansi($this->request));
    }

    public function printPembayaran(): ?ResponseInterface
    {
        return $this->handleKuitansiPrint(fn () => $this->printService->printKuitansi($this->request));
    }

    public function printPembayaranUm(): ?ResponseInterface
    {
        return $this->handleKuitansiPrint(fn () => $this->printService->printKuitansiUm($this->request));
    }

    private function handleKuitansiPrint(callable $printer): ?ResponseInterface
    {
        try {
            $printer();
            exit;
        } catch (\DomainException $e) {
            $message = $e->getMessage();
            $status = (str_contains($message, 'tidak terkait') || str_contains($message, 'tidak dapat dicetak'))
                ? 403
                : 404;

            return $this->response->setStatusCode($status)->setJSON([
                'success' => false,
                'messages' => $message,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Print kuitansi error: {msg}', ['msg' => $e->getMessage()]);

            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'messages' => 'Gagal mencetak kuitansi pembayaran.',
            ]);
        }
    }

    public function printTagihan(): ?ResponseInterface
    {
        $id = trim((string) $this->request->getVar('id'));
        if ($id === '') {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'messages' => 'ID tagihan wajib diisi.',
            ]);
        }

        try {
            $this->printService->printTagihan($id, (int) user_id());
            exit;
        } catch (\DomainException $e) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'messages' => $e->getMessage(),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Print tagihan error: {msg}', ['msg' => $e->getMessage()]);
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'messages' => 'Gagal mencetak tagihan.',
            ]);
        }
    }
}
