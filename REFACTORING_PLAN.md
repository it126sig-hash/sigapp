# Panduan Refaktor Arsitektur SIGAPP (CI4)

Dokumen ini berisi panduan teknis untuk melakukan refaktorisasi Controller yang gemuk (fat controller) menjadi arsitektur yang lebih modular menggunakan **Repository Pattern** dan **Service Layer**.

## 1. Arsitektur Target

Setiap modul harus mengikuti pemisahan tanggung jawab sebagai berikut:

- **Web Controller (`App\Controllers\`)**: Hanya bertanggung jawab untuk merender View (`return view()`). Tidak boleh ada logika bisnis atau query database di sini.
- **Api Controller (`App\Controllers\Api\`)**: Mewarisi `BaseApiController`. Bertanggung jawab untuk menangani request AJAX/Restful, validasi input dasar, dan mengembalikan respon JSON melalui Service.
- **Service Layer (`App\Services\`)**: Tempat utama logika bisnis, perhitungan, validasi kompleks, dan pengelolaan file (upload/delete).
- **Repository Layer (`App\Repositories\`)**: Tempat khusus untuk query database (Query Builder / SQL). Tidak boleh ada logika bisnis atau manipulasi request di sini.
- **Common Helpers/Traits**: Fungsi pembantu yang digunakan berulang di banyak tempat.

---

## 2. Pemisahan Helper & Shared Logic

Banyak controller saat ini memiliki fungsi duplikat. Langkah pertama adalah memindahkan fungsi berikut ke **Trait** atau **Helper Service**:

| Fungsi | Tujuan | Lokasi Baru (Saran) |
| :--- | :--- | :--- |
| `num($d)` | Formatting angka/currency | `App\Helpers\FormatterHelper` |
| `format_tgl($tgl)` | Formatting tanggal | `App\Helpers\FormatterHelper` |
| `if_where()` | Abstraksi query kondisional | `App\Repositories\BaseRepository` |
| `is_active()` | Logika UI class active | `App\Helpers\UIHelper` |
| `uploadDocument()` | Logika upload file ke storage | `App\Services\StorageService` |

---

## 3. Prioritas & Mapping Modul

### A. Modul MKDT (`Mkdt.php` - 73KB)
**Target Utama:** Memindahkan logika transaksi konsumen.

- **Repository (`MkdtRepository`):**
    - `get_data_by_id`, `getListStock`, `get_kavling`, `get_kavling_konsumen`.
- **Service (`MkdtService`):**
    - `save`, `set_harga`, `simpan_batal`, `batal_mkdt`, `saveSI`.
    - Integrasi dengan `ExcelService` untuk `export_xlsx`.
- **API Controller (`Api\MkdtController`):**
    - Pindahkan semua fungsi yang saat ini dipanggil via AJAX ke sini.

### B. Modul Keuangan (`Keuangan.php` - 70KB)
**Target Utama:** Memisahkan manajemen tagihan dan pembayaran.

- **Repository (`KeuanganRepository`):**
    - `getDanaAkad`, `getJatuhTempo`, `getCashOut`, `get_riwayat_gantinama`, `riwayat_bayar`.
- **Service (`KeuanganService`):**
    - `saveCashOut`, `saveDanaAkad`, `isi_tagihan`, `save_inv`, `save_sb`, `save`.
    - Integrasi dengan `PrintService` untuk `doPrint` dan `print_tagihan`.

### C. Modul Siteplan (`Siteplan.php` - 49KB)
**Target Utama:** Memisahkan koordinat kavling dan metadata visual.

---

## 4. Alur Kerja Refaktor (Per Fungsi)

1.  **Identifikasi Query:** Pindahkan raw SQL atau Query Builder dari Controller ke method baru di Repository yang relevan.
2.  **Identifikasi Logika:** Pindahkan percabangan (`if-else`), perhitungan, dan pemrosesan file ke method di Service.
3.  **Update API Controller:**
    - Panggil Service method.
    - Gunakan `try-catch` block.
    - Kembalikan `$this->success()` atau `$this->error()` dari `BaseApiController`.
4.  **Update Web Controller:** Hapus semua logic, sisakan hanya parameter untuk view.

## 5. Standar Penamaan & Kode

- **Service Method:** Gunakan kata kerja aksi (e.g., `processPayment()`, `cancelTransaction()`, `uploadCertificate()`).
- **Repository Method:** Gunakan kata kerja pengambilan (e.g., `findById()`, `findActiveStock()`, `getRevenueReport()`).
- **Dependency Injection:** Gunakan constructor injection di Controller untuk memanggil Service/Repository.

---

**Catatan:** Selalu periksa `BaseApiController` untuk memastikan format JSON konsisten dengan kebutuhan frontend (termasuk CSRF Token).
