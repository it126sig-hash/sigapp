# Project: Laporan Booking & Akad Overhaul

## Architecture
Modul Laporan Booking & Akad mengikuti arsitektur baku SIGAPP (CodeIgniter 4) dengan pola:
**Thin Controller → Service → Repository** (Modul Bisnis MKDT).
- **Web Controller** (`app/Controllers/Web/BookingAkadReportController.php`): Menangani request tampilan HTML, pengecekan hak akses, inisialisasi filter proyek aktif dan tahun.
- **Api Controller** (`app/Controllers/Api/BookingAkadReportController.php`): Endpoint AJAX untuk ringkasan matriks (`/summary`) dan detail transaksi (`/detail`).
- **Service** (`app/Services/BookingAkadReportService.php`): Mengatur logika bisnis, agregasi data bulanan untuk 6 status, formula konversi riil berbasis kode transaksi unik per bulan dan total tahunan, serta pemformatan data baris modal detail.
- **Repository** (`app/Repositories/BookingAkadReportRepository.php`): Eksekusi query database ke tabel `mkdt`, `kavling`, `jalan`, `cluster`, `proyek`, `konsumen`, `tipe`, `hargajual` dengan penanganan safe zero-date (`> '1970-01-01'`) dan agregasi cohort `mkdt.uniq_id`.
- **View & Assets** (`app/Views/laporan/booking-akad.php`, `public/assets/js/laporan-booking-akad.js`, `public/assets/css/laporan-booking-akad.css`): Tampilan matriks (7 kolom per tahun), modal detail 7 kolom, multi-modal stacking untuk `#modal_detail` unit kavling, dan aturan perataan teks.
- **Dokumentasi** (`docs/modules/MKDT/MODULE.md`): Dokumentasi modul resmi SIGAPP disinkronisasi melalui `php spark module:sync MKDT`.

## Feature Inventory
| # | Feature | Description | Milestone | Source |
|---|---------|-------------|-----------|--------|
| 1 | Metrik 6 Status Transaksi (R1) | Tampilkan dan hitung 6 kategori: booking, wawancara, SP3K, batal, akad indent, akad AJB pada tabel matriks bulanan dan total tahunan | M1 | ORIGINAL_REQUEST §R1 |
| 2 | Konversi Riil Berbasis Kode Transaksi (R2) | Hitung persentase konversi cohort berbasis `mkdt.uniq_id` (booking bulan M yang mencapai akad indent/AJB dibagi total booking unik bulan M) pada baris bulanan dan total tahunan | M1 | ORIGINAL_REQUEST §R2 |
| 3 | Query Data Modal Detail 7 Kolom (R3) | Query SQL dengan relasi lengkap untuk menghasilkan 7 kolom modal detail beserta status terakhir dan catatan MKDT bertingkat | M1 | ORIGINAL_REQUEST §R3 |
| 4 | UI Matriks & Kolom Konversi (R1, R2, R4) | Header tabel matriks 7 kolom per tahun (`colspan="7"`), min-width responsif (1200px/2200px), perataan teks (bulan left, angka nominal/jumlah right, rasio center) | M2 | ORIGINAL_REQUEST §R1, §R2, §R4 |
| 5 | Tampilan Modal Detail 7 Kolom (R3, R4) | Tabel DataTables modal detail 7 kolom: No, Kavling (Jalan + No + sales muted), Konsumen (Nama + tipe pricelist + badge subsidi/komersil), Tanggal booking, Status Terakhir (badge + catatan mkdt), Tanggal status terakhir, Tombol aksi | M2 | ORIGINAL_REQUEST §R3, §R4 |
| 6 | Integrasi Tombol Aksi Modal Kavling (R3) | Tombol aksi membuka `siteplan/partials/modal_detail` tanpa break CSS/JS, menyertakan `modal_detail_styles`, inisialisasi `window.loaded`, dan pemuatan asset Select2 & Flatpickr | M2 | ORIGINAL_REQUEST §R3 |
| 7 | Perataan Teks Tampilan (R4) | Standar perataan teks: semua teks rata kiri (`text-left`), nominal rata kanan (`text-right`), tanggal dan nomor urut rata tengah (`text-center`) | M2 | ORIGINAL_REQUEST §R4 |
| 8 | Sinkronisasi Dokumentasi Modul (R5) | Pembuatan/pembaruan dokumentasi modul di `docs/modules/MKDT/MODULE.md` memuat rute, controller, service, repository, struktur data, dan formula konversi, divalidasi via `php spark module:sync MKDT` | M3 | ORIGINAL_REQUEST §R5 |
| 9 | E2E Testing & Audit Verifikasi | Verifikasi data riil pada proyek aktif "Sanggar Indah Palastri" (ID Proyek 13), pengujian kalkulasi, UI test, dan Forensic Integrity Audit | M4 | ORIGINAL_REQUEST §Acceptance Criteria |

## Milestones
| # | Name | Scope | Dependencies | Status |
|---|------|-------|-------------|--------|
| M1 | Backend Calculation & Data Layer | Service & Repository: implementasi 6 status transaksi (R1), kalkulasi konversi riil berbasis `mkdt.uniq_id` (R2), dan query data modal detail (R3) | none | DONE |
| M2 | Frontend UI & Modal Integration | View, JS, CSS: header matriks 7 kolom, tabel modal detail 7 kolom (R3), pemanggilan `#modal_detail` kavling bebas error (R3), dan perataan teks (R4) | M1 | DONE |
| M3 | Module Documentation | Dokumentasi lengkap di `docs/modules/MKDT/MODULE.md` sesuai standar SIGAPP dan verifikasi via `php spark module:sync MKDT` (R5) | M1, M2 | DONE |
| M4 | E2E Testing & Quality Gate | Verifikasi menyeluruh menggunakan dataset Proyek 13 (Sanggar Indah Palastri), pengujian 100% acceptance criteria, dan forensic integrity audit | M1, M2, M3 | DONE |

## Interface Contracts

### BookingAkadReportRepository ↔ BookingAkadReportService
- `getSummary(int $idProyek, int $year): array`:
  Mengembalikan array dengan key per bulan (1..12) yang berisi:
  `['booking' => int, 'wawancara' => int, 'sp3k' => int, 'batal' => int, 'akad_indent' => int, 'akad_ajb' => int, 'booking_converted_akad' => int, 'total_booking_cohort' => int]`
- `getDetailRows(int $idProyek, int $year, int $month, string $category): array`:
  Mengembalikan daftar transaksi dengan kolom:
  `id_mkdt`, `uniq_id`, `id_kavling`, `nama_jalan`, `no_kavling`, `sales`, `nama_konsumen`, `nama_tipe`, `is_subsidi`, `booking_tgl`, `status_terakhir`, `status_terakhir_badge`, `tanggal_status_terakhir`, `catatan_mkdt`

### BookingAkadReportService ↔ Api\BookingAkadReportController
- Response Summary JSON:
  ```json
  {
    "status": "success",
    "data": {
      "years": [2026],
      "summary": {
        "2026": {
          "1": { "booking": 0, "wawancara": 0, "sp3k": 0, "batal": 0, "akad_indent": 0, "akad_ajb": 0, "konversi": 0.0 },
          ...
          "total": { "booking": 69, "wawancara": 11, "sp3k": 15, "batal": 9, "akad_indent": 18, "akad_ajb": 23, "konversi": 52.2 }
        }
      }
    },
    "csrfName": "...",
    "csrfHash": "..."
  }
  ```
- Response Detail JSON:
  ```json
  {
    "status": "success",
    "data": [
      {
        "id_mkdt": 147,
        "id_kavling": 52,
        "kavling": {
          "nama_jalan": "Jalan Mawar",
          "no_kavling": "A-12",
          "sales": "Budi Santoso"
        },
        "konsumen": {
          "nama_konsumen": "Ahmad Supardi",
          "tipe_rumah": "36/60",
          "is_subsidi": true
        },
        "tanggal_booking": "09-10-2024",
        "status_terakhir": "Akad AJB",
        "status_terakhir_badge": "success",
        "catatan_mkdt": "Berkas lengkap dan acc bank",
        "tanggal_status_terakhir": "14-04-2025"
      }
    ],
    "csrfName": "...",
    "csrfHash": "..."
  }
  ```

## Code Layout
- `app/Repositories/BookingAkadReportRepository.php` — Query agregasi matriks 6 status & cohort `uniq_id`, query detail rows
- `app/Services/BookingAkadReportService.php` — Logika kalkulasi rasio konversi riil bulanan & tahunan, pemformatan baris modal detail
- `app/Views/laporan/booking-akad.php` — View utama laporan, style modal detail include, aset vendor Flatpickr & Select2, globals guard
- `public/assets/js/laporan-booking-akad.js` — Logika render tabel matriks 7 kolom, inisialisasi DataTables 7 kolom, trigger modal unit kavling
- `public/assets/css/laporan-booking-akad.css` — Responsivitas tabel (1200px/2200px), styling badge status semantik, kelas perataan teks
- `docs/modules/MKDT/MODULE.md` — Dokumentasi modul resmi MKDT
- `tests/unit/BookingAkadReportServiceTest.php` — Unit test perhitungan matriks dan konversi cohort riil
