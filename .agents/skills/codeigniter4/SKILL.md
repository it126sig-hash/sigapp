---
name: codeigniter4
description: >
  Best practice architecture for CodeIgniter 4 (CI4) projects using the Thin Controller → Service → Repository pattern,
  dengan pemisahan controller Web dan API. Gunakan skill ini SELALU ketika bekerja dengan CodeIgniter 4, CI4,
  atau membuat/modifikasi controllers, services, repositories, models, atau API endpoints di project CI4.
  Triggers: "buat controller CI4", "CodeIgniter 4 service", "CI4 repository", "buat API endpoint CI4",
  "buat CRUD CI4", "buat web controller", atau apapun yang menyebut CodeIgniter 4, struktur kode CI4,
  validation, query builder, atau REST API. Jangan andalkan memori — selalu baca skill ini.
---

# CodeIgniter 4 — Thin Controller + Service + Repository

## Prinsip Utama

| Layer | Tanggung Jawab |
|-------|----------------|
| **Web Controller** | Handle request dari browser (render view, redirect). Tidak ada logika bisnis. |
| **API Controller** | Handle request JSON/REST. Validasi format input, panggil Service, return JSON. Tidak ada logika bisnis. |
| **Service** | Semua logika bisnis, kalkulasi, validasi bisnis, **validasi ulang angka dari frontend**. |
| **Repository** | Query kompleks (JOIN, subquery, agregasi). Bukan untuk query sederhana. |
| **Model** | Konfigurasi tabel, allowedFields, validasi DB-level. |

> **Aturan emas:** Jika kamu menulis `if` bisnis di controller → pindah ke Service. Jika kamu menulis JOIN panjang di Service → pindah ke Repository. **Backend selalu hitung ulang semua angka finansial dari DB — jangan percaya angka dari frontend.**

---

## Struktur Direktori

```
app/
├── Controllers/
│   ├── Web/                          # Halaman browser
│   │   ├── BaseWebController.php
│   │   ├── SiteplanController.php
│   │   └── DashboardController.php
│   └── Api/                          # Endpoint JSON/REST
│       ├── BaseApiController.php
│       ├── TransaksiController.php
│       └── KeuanganController.php
├── Services/
│   ├── TransaksiService.php
│   └── KeuanganService.php
├── Repositories/
│   └── KeuanganRepository.php
├── Models/
│   └── TransaksiModel.php
└── Exceptions/
    └── ServiceException.php
```

---

## Routes — Pemisahan Web dan API

```php
// app/Config/Routes.php

// ── WEB ROUTES ──
$routes->group('', ['namespace' => 'App\Controllers\Web'], function ($routes) {
    $routes->get('siteplan/(:segment)', 'SiteplanController::index/$1');
    $routes->get('dashboard',           'DashboardController::index');
});

// ── API ROUTES ──
$routes->group('api', [
    'namespace' => 'App\Controllers\Api',
    'filter'    => 'apiAuth',
], function ($routes) {
    $routes->post('transaksi/simpan',        'TransaksiController::simpan');
    $routes->post('transaksi/ambilsatu',     'TransaksiController::ambilSatu');
    $routes->post('transaksi/status/simpan', 'TransaksiController::simpanStatus');
    $routes->post('pembayaran/simpan',       'KeuanganController::simpanPembayaran');
    $routes->post('tagihan/ambilsatu',       'KeuanganController::ambilTagihan');
});
```

---

## 1. BaseWebController

```php
<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;

class BaseWebController extends BaseController
{
    protected string $activeMenu = '';

    protected function view(string $template, array $data = []): string
    {
        return view($template, array_merge($data, [
            'activeMenu' => $this->activeMenu,
        ]));
    }
}
```

---

## 2. BaseApiController

```php
<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class BaseApiController extends ResourceController
{
    protected $format = 'json';

    protected function success(mixed $data = null, string $message = 'Success', int $code = 200)
    {
        $body = ['status' => true, 'message' => $message];
        if ($data !== null) $body['data'] = $data;
        return $this->respond($body, $code);
    }

    protected function successCreated(mixed $data, string $message = 'Created')
    {
        return $this->respond([
            'status'  => true,
            'message' => $message,
            'data'    => $data,
        ], 201);
    }

    protected function error(string $message, int $code = 400, mixed $errors = null)
    {
        $body = ['status' => false, 'message' => $message];
        if ($errors !== null) $body['errors'] = $errors;
        return $this->respond($body, $code);
    }
}
```

---

## 3. Web Controller

```php
<?php

namespace App\Controllers\Web;

use App\Services\SiteplanService;

class SiteplanController extends BaseWebController
{
    protected string $activeMenu = 'siteplan';

    public function __construct(
        private readonly SiteplanService $siteplanService
    ) {}

    // Hanya render view — data berat di-load via API call (jQuery/fetch)
    public function index(string $id_proyek): string
    {
        $proyek = $this->siteplanService->getProyek($id_proyek);
        return $this->view('siteplan/master', ['proyek' => $proyek]);
    }
}
```

---

## 4. API Controller (Tipis)

```php
<?php

namespace App\Controllers\Api;

use App\Services\TransaksiService;
use CodeIgniter\HTTP\ResponseInterface;

class TransaksiController extends BaseApiController
{
    public function __construct(
        private readonly TransaksiService $transaksiService
    ) {}

    public function ambilSatu(): ResponseInterface
    {
        $rules = [
            'id_kavling'   => 'required|integer',
            'id_mkdt'      => 'permit_empty|integer',
            'id_hargajual' => 'permit_empty|integer',
        ];

        if (! $this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $result = $this->transaksiService->getDetail($this->request->getPost());
        return $this->success($result);
    }

    public function simpan(): ResponseInterface
    {
        $rules = [
            'id_kavling'  => 'required|integer',
            'id_konsumen' => 'permit_empty|integer',
        ];

        if (! $this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $result = $this->transaksiService->simpan($this->request->getPost());
        return $this->successCreated($result);
    }
}
```

---

## 5. Service — Validasi Finansial WAJIB di Sini

```php
<?php

namespace App\Services;

use App\Models\KeuanganModel;
use App\Models\PembayaranModel;
use App\Exceptions\ServiceException;

class KeuanganService
{
    public function __construct(
        private readonly KeuanganModel   $keuanganModel,
        private readonly PembayaranModel $pembayaranModel,
    ) {}

    public function simpanPembayaran(array $data): array
    {
        $id_mkdt = (int) $data['id_mkdt'];
        $nominal = (float) str_replace(',', '', $data['nominal']);

        // ── WAJIB: Hitung dari DB, jangan percaya angka dari frontend ──
        $totalTagihan = $this->keuanganModel->getTotalTagihan($id_mkdt);
        $sudahBayar   = $this->pembayaranModel->getTotalSudahBayar($id_mkdt);
        $sisa         = $totalTagihan - $sudahBayar;

        if ($nominal <= 0) {
            throw new ServiceException('Nominal pembayaran harus lebih dari 0', 422);
        }
        if ($nominal > $sisa) {
            throw new ServiceException(
                "Nominal Rp.{$nominal} melebihi sisa tagihan Rp.{$sisa}", 422
            );
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $id = $this->pembayaranModel->insert([
                'id_mkdt'       => $id_mkdt,
                'nominal'       => $nominal,
                'tanggal_bayar' => $data['tanggal_bayar'],
                'payment_type'  => $data['payment_type'],
            ], true);

            // Set lunas jika sudah lunas — ditentukan backend, bukan input user
            if (($sudahBayar + $nominal) >= $totalTagihan) {
                $this->keuanganModel->setLunas($id_mkdt);
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new ServiceException('Gagal menyimpan pembayaran');
            }

            return $this->pembayaranModel->find($id);

        } catch (\Throwable $e) {
            $db->transRollback();
            throw $e;
        }
    }

    public function simpanTagihanTurunKpr(array $data): array
    {
        $id_mkdt = (int) $data['id_mkdt'];

        // Hitung turun KPR dari DB — abaikan nilai dari frontend
        $mkdt        = $this->keuanganModel->getMkdtById($id_mkdt);
        $turunKpr    = (float) $mkdt['harga_kpr'] - (float) $mkdt['harga_kpr_acc'];

        if ($turunKpr <= 0) {
            throw new ServiceException('Tidak ada selisih KPR untuk dibuat tagihan', 422);
        }

        if ($this->keuanganModel->getTagihanTurunKpr($id_mkdt)) {
            throw new ServiceException('Tagihan turun KPR sudah ada', 422);
        }

        return $this->keuanganModel->insert([
            'id_mkdt'         => $id_mkdt,
            'berita_acara'    => 'Turun KPR',
            'nominal'         => $turunKpr,     // dari DB, bukan dari request
            'jatuh_tempo_tgl' => $data['jatuh_tempo'],
            'status'          => 'UM',
        ], true);
    }
}
```

---

## 6. Repository (Query Kompleks Saja)

```php
<?php

namespace App\Repositories;

use App\Models\KeuanganModel;

class KeuanganRepository
{
    public function __construct(
        private readonly KeuanganModel $keuanganModel
    ) {}

    public function getRingkasanPerKavling(int $id_proyek): array
    {
        return $this->keuanganModel
            ->select('kavling.no_kavling, kavling.nama_jalan')
            ->select('mkdt.nama_konsumen, mkdt.status_mkdt')
            ->select('SUM(keuangan.nominal) as total_tagihan')
            ->select('COALESCE(SUM(pembayaran.nominal), 0) as total_bayar')
            ->select('SUM(keuangan.nominal) - COALESCE(SUM(pembayaran.nominal), 0) as sisa')
            ->join('mkdt',       'mkdt.id_mkdt = keuangan.id_mkdt')
            ->join('kavling',    'kavling.id_kavling = mkdt.id_kavling')
            ->join('pembayaran', 'pembayaran.id_mkdt = mkdt.id_mkdt', 'left')
            ->where('kavling.id_proyek', $id_proyek)
            ->groupBy('keuangan.id_mkdt')
            ->findAll();
    }
}
```

---

## 7. Tabel Validasi Finansial (Aturan Tetap)

| Data dari Frontend | Yang Harus Dilakukan di Backend (Service) |
|---|---|
| `nominal` pembayaran | Hitung `sisa_tagihan` dari DB, tolak jika `nominal > sisa` |
| `total_tagihan` | Abaikan — SUM dari tabel `keuangan` di DB |
| `turun_kpr` | Abaikan — hitung `harga_kpr - harga_kpr_acc` dari DB |
| `is_lunas` | Abaikan — tentukan dari kalkulasi backend |
| `harga_diskon` | Validasi tidak melebihi batas yang diizinkan |
| `text_um`, `text_bb` | Abaikan / hapus — dead code |

---

## 8. Checklist Best Practice

- [ ] Web Controller hanya render view / redirect — tidak ada logika bisnis
- [ ] API Controller hanya validasi format input + panggil Service
- [ ] Service memvalidasi ulang semua angka finansial dari DB
- [ ] Transaction untuk semua operasi multi-tabel
- [ ] Repository hanya untuk query JOIN/subquery/agregasi
- [ ] Route Web (`app/Controllers/Web`) dan API (`app/Controllers/Api`) dipisah
- [ ] `BaseWebController` dan `BaseApiController` digunakan konsisten
- [ ] Dead code JS (`text_um`, `text_bb`, dll) dibersihkan

---

## Referensi Detail

- `references/pagination-filter.md` — Pagination + filter di repository
- `references/transaction.md` — Database transaction di service
- `references/response-format.md` — Standar format JSON response API
- `references/financial-validation.md` — Pola validasi finansial lengkap
