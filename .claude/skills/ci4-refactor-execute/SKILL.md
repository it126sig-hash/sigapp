---
name: ci4-refactor-execute
description: >
  Eksekusi refactor satu modul CodeIgniter 4 berdasarkan hasil audit yang sudah disetujui developer.
  Gunakan skill ini HANYA setelah ci4-refactor-audit selesai dan developer memberikan persetujuan.
  Triggers: "refactor modul X", "execute refactor", "lakukan refactor", "kerjakan refactor [modul]",
  "mulai refactor", "fix controller X", "pisahkan web dan api", atau setelah developer approve laporan audit CI4.
  JANGAN jalankan tanpa hasil audit terlebih dahulu.
---

# CI4 Refactor Execute

Eksekusi refactor **satu modul** sesuai laporan audit yang sudah disetujui.
Kerjakan **bertahap**, dan **laporkan + tunggu konfirmasi** setelah setiap tahap.

---

## Pra-kondisi (Wajib)

- [ ] Laporan audit sudah ada dan di-review developer
- [ ] Developer sudah konfirmasi scope (semua / KRITIS saja / dll)
- [ ] Tidak ada pertanyaan terbuka dari audit yang belum dijawab

Jika belum, minta developer jalankan `ci4-refactor-audit` dulu.

---

## Urutan Eksekusi

### TAHAP 1 — Security Fix: Validasi Finansial Backend

**Ini selalu dikerjakan PERTAMA** karena risikonya paling tinggi di production.

Untuk setiap method yang menyimpan data finansial, tambahkan validasi di Service:

```php
// app/Services/KeuanganService.php

public function simpanPembayaran(array $data): array
{
    $id_mkdt = (int) $data['id_mkdt'];
    $nominal = cleanNumeric($data['nominal']); // bersihkan format "1,500,000"

    // ── Hitung dari DB — jangan percaya angka dari frontend ──
    $totalTagihan = $this->keuanganModel->getTotalTagihan($id_mkdt);
    $sudahBayar   = $this->pembayaranModel->getTotalSudahBayar($id_mkdt);
    $sisa         = $totalTagihan - $sudahBayar;

    if ($nominal <= 0) {
        throw new ServiceException('Nominal harus lebih dari 0', 422);
    }
    if ($nominal > $sisa) {
        throw new ServiceException("Nominal melebihi sisa tagihan Rp.{$sisa}", 422);
    }

    // ... lanjut simpan
}
```

Tambahkan helper `cleanNumeric()` di `app/Helpers/numeric_helper.php`:
```php
function cleanNumeric(mixed $value): float
{
    if (is_null($value) || $value === '') return 0.0;
    return (float) preg_replace('/[^\d.\-]/', '', str_replace(',', '', (string) $value));
}
```

Setelah tahap ini, **laporkan dan minta konfirmasi:**
> "✅ Tahap 1 selesai: Validasi finansial sudah ditambahkan ke [daftar method].
> Silakan test endpoint: [daftar URL]
> Ketik 'lanjut' jika sudah OK."

**Tunggu konfirmasi sebelum lanjut.**

---

### TAHAP 2 — Transaction

Untuk semua method dengan 2+ operasi DB tanpa transaction:

```php
$db = \Config\Database::connect();
$db->transStart();

try {
    // semua operasi DB di sini

    $db->transComplete();

    if ($db->transStatus() === false) {
        throw new \RuntimeException('Transaksi gagal');
    }
} catch (\Throwable $e) {
    $db->transRollback();
    throw $e;
}
```

**Jika logika masih di controller:** tambahkan transaction di controller dulu.
Nanti dipindah ke Service di tahap berikutnya — jangan lakukan keduanya sekaligus.

Setelah tahap ini, **laporkan dan minta konfirmasi:**
> "✅ Tahap 2 selesai: Transaction ditambahkan ke [daftar method].
> Silakan test: [daftar endpoint]
> Ketik 'lanjut' jika sudah OK."

---

### TAHAP 3 — Pisahkan Web Controller dan API Controller

Buat file baru, jangan ubah file lama dulu:

**Web Controller** (`app/Controllers/Web/[Modul]Controller.php`):
```php
<?php

namespace App\Controllers\Web;

class SiteplanController extends BaseWebController
{
    protected string $activeMenu = 'siteplan';

    public function __construct(
        private readonly \App\Services\SiteplanService $service
    ) {}

    // Hanya render view — data di-load via API
    public function index(string $id_proyek): string
    {
        $proyek = $this->service->getProyek($id_proyek);
        return $this->view('siteplan/master', compact('proyek'));
    }
}
```

**API Controller** (`app/Controllers/Api/[Modul]Controller.php`):
```php
<?php

namespace App\Controllers\Api;

class TransaksiController extends BaseApiController
{
    public function __construct(
        private readonly \App\Services\TransaksiService $service
    ) {}

    public function ambilSatu(): ResponseInterface
    {
        if (! $this->validate(['id_kavling' => 'required|integer'])) {
            return $this->failValidationErrors($this->validator->getErrors());
        }
        return $this->success($this->service->getDetail($this->request->getPost()));
    }
}
```

Update `app/Config/Routes.php` — tambahkan route baru, **jangan hapus yang lama dulu**:
```php
// Route baru (Web)
$routes->group('', ['namespace' => 'App\Controllers\Web'], function ($routes) {
    $routes->get('siteplan/(:segment)', 'SiteplanController::index/$1');
});

// Route baru (API)
$routes->group('api', ['namespace' => 'App\Controllers\Api'], function ($routes) {
    $routes->post('transaksi/ambilsatu', 'TransaksiController::ambilSatu');
});

// Route lama — tandai untuk dihapus setelah konfirmasi
// $routes->post('transaksi/ambilsatu', 'Transaksi::ambilsatu'); // TODO: hapus setelah konfirmasi
```

Update URL di JS — ganti `base_url + "transaksi/ambilsatu"` menjadi `base_url + "api/transaksi/ambilsatu"`.

Setelah tahap ini, **laporkan dan minta konfirmasi:**
> "✅ Tahap 3 selesai: Controller sudah dipisah ke Web/ dan Api/.
> URL yang berubah di JS: [daftar URL lama → baru]
> Silakan test semua fungsi di modul ini.
> Ketik 'lanjut' jika sudah OK."

---

### TAHAP 4 — Pindahkan Logika ke Service

Setelah controller terpisah, pindahkan logika bisnis dari controller ke Service:

**Sebelum:**
```php
// Api/TransaksiController.php — masih ada logika ❌
public function simpan(): ResponseInterface
{
    $data = $this->request->getPost();
    $data['harga_net'] = $data['harga_jual'] - $data['diskon']; // kalkulasi di controller
    if ($data['harga_net'] < 0) { ... }                         // kondisi di controller
    $this->transaksiModel->insert($data);
    return $this->respond(['success' => true]);
}
```

**Sesudah:**
```php
// Api/TransaksiController.php — tipis ✅
public function simpan(): ResponseInterface
{
    if (! $this->validate($rules)) {
        return $this->failValidationErrors($this->validator->getErrors());
    }
    $result = $this->transaksiService->simpan($this->request->getPost());
    return $this->successCreated($result);
}

// Services/TransaksiService.php — logika di sini ✅
public function simpan(array $data): array
{
    $hargaNet = cleanNumeric($data['harga_jual']) - cleanNumeric($data['diskon']);
    if ($hargaNet < 0) {
        throw new ServiceException('Harga net tidak boleh negatif', 422);
    }
    // ... lanjut simpan
}
```

Setelah tahap ini, **laporkan dan minta konfirmasi:**
> "✅ Tahap 4 selesai: Logika dipindah ke [NamaService].
> Ketik 'lanjut' jika sudah OK."

---

### TAHAP 5 — Standarisasi Response + Hapus Dead Code

Standarisasi semua response API menggunakan `BaseApiController`:

| Sebelum | Sesudah |
|---------|---------|
| `echo json_encode(['success' => true, 'data' => $d])` | `return $this->success($d)` |
| `return $this->respond($data)` | `return $this->success($data)` |
| `return $this->respond(['messages' => 'OK'], 201)` | `return $this->successCreated($data)` |
| `return json_encode(['error' => $msg])` | `return $this->error($msg, 400)` |

Hapus dead code JS (konfirmasi dulu dengan developer):
```javascript
// Hapus jika tidak dipakai:
// let text_um = [], text_bb = [];
// text_um = text_um.join(";");
// data: "..." + "&text_um=" + text_um + "&text_bb=" + text_bb
```

---

### TAHAP 6 — Laporan Akhir Modul

```
# Refactor Selesai: Modul [Nama]
Tanggal: [tanggal]

## Yang Dikerjakan
- [x] Validasi finansial ditambahkan di Service
- [x] Transaction ditambahkan di method: [daftar]
- [x] Controller dipisah: Web/[Nama]Controller + Api/[Nama]Controller
- [x] Service dibuat: app/Services/[Nama]Service.php
- [x] Response distandarisasi
- [x] Dead code dihapus

## URL yang Berubah (perlu update di JS)
- /transaksi/simpan → /api/transaksi/simpan
- /keuangan/ambilsatu → /api/keuangan/ambilsatu

## ⚠️ Breaking Changes
- Response format berubah: { status, message, data } — pastikan JS sudah handle

## Route Lama (siap dihapus)
- // $routes->post('transaksi/simpan', ...) — sudah bisa dihapus

## Modul Berikutnya?
```

---

## Aturan yang Tidak Boleh Dilanggar

1. Kerjakan **satu tahap sekaligus** — jangan loncat
2. **Tunggu konfirmasi developer** setelah Tahap 1, 2, dan 3
3. **Jangan hapus controller lama** sampai developer konfirmasi route baru berjalan
4. **Jangan ubah URL di JS** sebelum route baru aktif
5. **Catat semua breaking changes** di laporan akhir
6. Validasi finansial di Tahap 1 **tidak boleh dilewati** meski developer bilang "skip dulu"
