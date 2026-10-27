<?php

namespace App\Controllers\Api\MGM;

use CodeIgniter\RESTful\ResourceController;
use App\Services\MGM\ReferralService;

/**
 * PublicReferralController
 *
 * Endpoint-endpoint MGM yang dapat diakses tanpa autentikasi.
 * Tidak menggunakan BaseApiController agar tidak menyertakan
 * csrf_hash() yang membutuhkan sesi login.
 */
class PublicReferralController extends ResourceController
{
    protected $format = 'json';

    /**
     * Validasi kode referral publik.
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
