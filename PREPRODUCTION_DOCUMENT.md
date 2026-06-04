# Dokumen Pre-Production: Rebuild SigApp (Sistem Informasi Manajemen Properti)

## 1. Ringkasan Sistem & Alur Bisnis (Berdasarkan Repositori Saat Ini)

SigApp adalah sistem ERP/Sistem Informasi Manajemen untuk developer properti/real estate. Sistem ini mencakup siklus penuh pengembangan properti, mulai dari perencanaan proyek, penjualan, konstruksi, hingga keuangan dan legalitas.

Berikut adalah alur bisnis utama yang dapat ditangkap dari sistem ini:

### A. Master Data Proyek & Properti
- **Manajemen Proyek & Cluster:** Mendefinisikan proyek perumahan, cluster di dalam proyek, jalan, dan *siteplan*.
- **Manajemen Kavling & Tipe:** Mengatur data kavling/unit yang tersedia, tipe rumah, spesifikasi, dan harga jual.
- **Harga & Promo:** Mengatur daftar harga (*pricelist*), diskon, dan program promo yang berlaku.

### B. Penjualan (Sales) & Konsumen
- **Manajemen Leads & Konsumen:** Pendataan calon pembeli dan posisi konsumen dalam *funnel* penjualan.
- **Transaksi & Pembayaran:** Mengelola transaksi pembelian unit (KPR, Cash Keras, Cash Bertahap). Pencatatan tagihan, jadwal pembayaran, dan riwayat pembayaran cicilan konsumen.
- **Sales & Komisi:** Melacak performa *sales* dan perhitungan komisi/bonus.

### C. Konstruksi & Produksi
- **Manajemen Subkon (Subkontraktor):** Pendataan pihak ketiga yang mengerjakan proyek.
- **Monitoring Produksi (MKDT & Progress Pekerjaan):** Melacak progres pembangunan unit fisik (*Checklist Work*, *Group*, *Item*, *SubItem*).
- **Serah Terima:** Proses *handover* bangunan ke konsumen setelah pembangunan selesai.

### D. Keuangan (Finance)
- **Cash Out & Tagihan Subkon:** Manajemen pembayaran ke subkontraktor berdasarkan progres pekerjaan.
- **Dana Akad & Pencairan Jaminan:** Pencatatan aliran dana dari bank (jika KPR) dan pencairan retensi/jaminan subkon.
- **Manajemen Pajak & Modal:** Mengelola pajak (PPN, BPHTB, dll), modal proyek, dan laporan keuangan terkait proyek.

### E. Legal & Administrasi
- **Dokumen Legalitas:** Pengelolaan dokumen legal per unit/konsumen (Sertifikat, IMB, AJB, dll).
- **Akses Proyek:** Pengaturan siapa saja (karyawan/departemen) yang memiliki akses atau *assigned* ke proyek tertentu.

---

## 2. Rekomendasi Teknologi

Karena sistem ini ditargetkan untuk *mobile-ready* (menggunakan Capacitor sebagai *wrapper*) dan memiliki pemisahan antara Backend API dan Frontend, berikut adalah *stack* teknologi yang direkomendasikan:

### Backend: Laravel 11 (REST API)
- **Framework:** Laravel 11 (versi terbaru, ringan, dan modern).
- **Authentication & API Security:** **Laravel Sanctum**. Sangat cocok untuk mengamankan REST API yang dikonsumsi oleh SPA (Vue.js) maupun aplikasi mobile.
- **Role & Permission Management:** **Spatie Laravel-Permission**. Standar industri di Laravel untuk mengatur *role* dan *permission* secara dinamis.
- **Database:** MySQL atau PostgreSQL.
- **File Storage:** Local Storage (`storage/app`) dengan proteksi rute, atau Cloud Storage (AWS S3 / MinIO) jika skala file besar.
- **Format API:** Standar JSON (bisa menggunakan *API Resources* milik Laravel untuk format data yang rapi).

### Frontend: Vue.js 3 + Capacitor
- **Core Framework:** **Vue 3** (Composition API, `<script setup>`) menggunakan *build tool* **Vite** untuk kompilasi yang sangat cepat.
- **Mobile Wrapper:** **Capacitor** (dari Ionic). Mengubah aplikasi web Vue menjadi aplikasi native (Android & iOS) dengan mudah, serta memberikan akses ke *hardware* device (kamera, lokasi, dll).
- **UI Framework / CSS:**
  - **Tailwind CSS:** Untuk *styling* yang cepat dan sangat responsif (*mobile-first*).
  - *Opsi tambahan:* **Ionic Vue** (Jika Anda ingin *feel* komponen UI yang benar-benar mirip aplikasi *native* seperti *tab bar*, *swipe-to-go-back*, dll) atau **PrimeVue** (Jika butuh komponen data table & form yang kompleks untuk dashboard admin).
- **State Management:** **Pinia** (Pengganti Vuex, lebih ringan dan dukungan TypeScript yang sangat baik).
- **HTTP Client:** **Axios** (Untuk komunikasi ke REST API Laravel).
- **Routing:** **Vue Router**.
- **Interactive Map/Siteplan:**
  - **Konva.js:** Library canvas 2D untuk memuat gambar siteplan dan membuat *layer* untuk *shapes* interaktif di atas gambar tersebut.
  - **Magic Wand JS:** Digunakan untuk memudahkan pemilihan (selection) area kavling dari gambar siteplan. Koordinat hasil dari *selection* akan digunakan untuk menggambar *polygon* atau *line shape* di Konva.js pada *layer* baru. Hal ini akan membuat tiap unit/kavling menjadi elemen interaktif (bisa diklik, berubah warna sesuai status penjualan, dll).

---

## 3. Implementasi Fitur Khusus

### A. Role Management (Pre-defined & Admin Override)
- **Strategi:**
  - Kita akan membuat *pre-defined roles* (contoh: `SuperAdmin`, `Direksi`, `Keuangan`, `Sales`, `Subkon`, `Legal`).
  - Setiap peran akan memiliki *default permissions* yang ditanamkan saat awal sistem dibuat (via *Database Seeder*).
  - Untuk mengakomodasi kebutuhan *override*, Spatie Permission memungkinkan kita untuk memberikan *Direct Permission* kepada spesifik *User*.
  - **Contoh:** Budi memiliki role `Sales` (tidak bisa melihat dokumen keuangan). Namun, Admin dapat secara eksplisit memberikan permission `view-finance-report` langsung ke *User* Budi tanpa harus mengubah *default* role `Sales`.

### B. Keamanan Akses Dokumen (Secure Document Storage)
- **Strategi Penyimpanan:** Dokumen **TIDAK** akan disimpan di folder `public` Laravel. Dokumen akan disimpan di folder `storage/app/private` (atau sub-direktori spesifik).
- **Alur Akses:**
  1. Frontend (Vue.js) meminta file melalui URL API khusus (misal: `GET /api/documents/{id}/download`).
  2. Laravel Route akan menangkap *request* ini dan meneruskannya ke Middleware/Controller.
  3. Controller akan mengecek:
     - Apakah *User* sudah login?
     - Apakah *User* memiliki *role* atau *permission* (misal: departemen `Legal` atau Admin) untuk melihat dokumen tersebut?
     - Apakah dokumen ini merupakan milik *User* tersebut (jika *User* adalah Konsumen)?
  4. Jika semua syarat terpenuhi, Laravel akan merespons file tersebut menggunakan `response()->file()` atau `response()->download()`. Jika tidak, akan mengembalikan pesan *403 Forbidden*.
- **Keamanan Ekstra:** File akan disajikan langsung oleh backend via *stream* tanpa mengekspos *path* asli di server, sehingga *link direct* tidak akan pernah bisa ditebak atau diakses publik.

---

## 4. Rencana Langkah Selanjutnya (Next Steps)
Setelah dokumen ini disetujui, Anda dapat membuat repositori baru untuk proyek ini. Langkah teknis inisialisasi yang disarankan:
1. **Setup Repository Backend:** Inisialisasi *project* Laravel baru, setup struktur *database/migrations* berdasarkan alur di atas, setup Sanctum, dan setup Spatie Permission.
2. **Setup Repository Frontend:** Inisialisasi Vite + Vue 3, konfigurasi Tailwind CSS, Pinia, Vue Router, dan instalasi Capacitor.
3. **Membangun Modul Bertahap:**
   - Tahap 1: Auth, Role Management, & Setup Base API.
   - Tahap 2: Master Data (Proyek, Cluster, dll).
   - Tahap 3: Modul Inti (Sales, Produksi, Keuangan).
   - Tahap 4: Sistem Dokumen Tertutup.
