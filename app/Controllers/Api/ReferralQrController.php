<?php

namespace App\Controllers\Api;

use App\Services\ReferralQrService;
use CodeIgniter\HTTP\ResponseInterface;
use RuntimeException;
use Throwable;

class ReferralQrController extends BaseApiController
{
    private ReferralQrService $service;

    public function __construct()
    {
        $this->service = new ReferralQrService();
    }

    public function preview(): ResponseInterface
    {
        $rules = [
            'kode_referal' => 'permit_empty|max_length[100]',
            'id_mkdt' => 'permit_empty|integer',
            'id_proyek' => 'permit_empty|integer',
        ];

        if (! $this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        try {
            $qr = $this->service->generate(
                $this->request->getPost('kode_referal'),
                $this->request->getPost('id_mkdt') ? (int) $this->request->getPost('id_mkdt') : null,
                resolve_active_proyek_id($this->request->getPost('id_proyek'))
            );

            return $this->success([
                'kode_referal' => $qr['kode_referal'],
                'nama_konsumen' => $qr['nama_konsumen'],
                'nama_proyek' => $qr['nama_proyek'],
                'target_url' => $qr['target_url'],
                'filename' => $qr['filename'],
                'download_url' => site_url('api/referral-qr/download') . '?' . http_build_query([
                    'kode_referal' => $qr['kode_referal'],
                    'id_mkdt' => $this->request->getPost('id_mkdt'),
                    'id_proyek' => resolve_active_proyek_id($this->request->getPost('id_proyek')),
                ]),
                'image_data_url' => 'data:image/png;base64,' . base64_encode($qr['png']),
            ]);
        } catch (RuntimeException $e) {
            return $this->failValidationErrors($e->getMessage());
        } catch (Throwable $e) {
            log_message('error', 'Gagal generate QR referal: ' . $e->getMessage());
            return $this->failServerError('Gagal membuat QR referal.');
        }
    }

    public function download(): ResponseInterface
    {
        try {
            $qr = $this->service->generate(
                $this->request->getGet('kode_referal'),
                $this->request->getGet('id_mkdt') ? (int) $this->request->getGet('id_mkdt') : null,
                resolve_active_proyek_id($this->request->getGet('id_proyek'))
            );

            return $this->response
                ->setHeader('Content-Type', 'image/png')
                ->setHeader('Content-Disposition', 'attachment; filename="' . $qr['filename'] . '"')
                ->setBody($qr['png']);
        } catch (RuntimeException $e) {
            return $this->failValidationErrors($e->getMessage());
        } catch (Throwable $e) {
            log_message('error', 'Gagal download QR referal: ' . $e->getMessage());
            return $this->failServerError('Gagal membuat QR referal.');
        }
    }
}
