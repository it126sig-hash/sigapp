<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;

class LegalPageController extends BaseController
{
    public function privacyPolicy(): string
    {
        return view('public/privacy_policy', [
            'pageTitle'       => 'Kebijakan Privasi',
            'metaDescription' => 'Kebijakan Privasi SIGAPP mengenai pengumpulan, penggunaan, penyimpanan, dan perlindungan data pengguna.',
            'activePage'      => 'privacy',
        ]);
    }

    public function termsOfService(): string
    {
        return view('public/terms_of_service', [
            'pageTitle'       => 'Ketentuan Layanan',
            'metaDescription' => 'Ketentuan Layanan SIGAPP yang mengatur akses dan penggunaan platform manajemen proyek properti.',
            'activePage'      => 'terms',
        ]);
    }
}
