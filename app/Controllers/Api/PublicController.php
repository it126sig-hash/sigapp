<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Services\ReferralService;

/**
 * PublicController
 *
 * Endpoint-endpoint yang dapat diakses tanpa autentikasi.
 * Tidak menggunakan BaseApiController agar tidak menyertakan
 * csrf_hash() yang membutuhkan sesi login.
 */
class PublicController extends ResourceController
{
    protected $format = 'json';

    /**
     * Validasi kode referral.
     *
     * POST /api/public/check-referral
     * Body: kode (string)
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function checkReferral()
    {
        $kode = $this->request->getPost('kode');

        if (empty($kode)) {
            return $this->respond([
                'success' => false,
                'message' => 'Parameter "kode" diperlukan',
            ], 400);
        }

        $service = new ReferralService();
        $result  = $service->validateKodePublic((string) $kode);

        if (! $result['valid']) {
            return $this->respond([
                'success' => false,
                'message' => $result['message'],
            ], 404);
        }

        return $this->respond([
            'success' => true,
            'data'    => $result['data'],
        ], 200);
    }
}
