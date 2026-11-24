# DISPATCH

## 2026-11-04T05:10:14Z

/plan /codegraph /ponytail /sigapp-orchestrator
Perbarui modul Laporan Booking & Akad (antarmuka view `app/Views/laporan/booking-akad.php`, backend controller, service, dan repository terkait) serta dokumentasi modul di SIGAPP.

Integrity mode: development

## Requirements

### R1. Metrik Status Laporan
Tampilkan dan hitung 6 kategori status transaksi pada laporan: booking, wawancara, SP3K, batal, akad indent, dan akad AJB.

### R2. Konversi Real Berbasis Kode Transaksi
Tampilkan konversi dari booking ke akad secara riil berbasis kode transaksi (bukan perbandingan kuantitas per bulan). Transaksi yang terkonversi ke akad mencakup transaksi yang mencapai status "akad indent" maupun "akad AJB". Tampilkan persentase konversi ini pada tabel matriks bulanan (mengukur berapa persen transaksi booking pada bulan bersangkutan yang akhirnya mencapai akad) serta pada baris total tahunan.

### R3. Kolom dan Fungsi Modal Detail
Ketika modal detail dibuka dari setiap angka laporan, tampilkan kolom-kolom berikut:
- **No**: nomor urut.
- **Kavling**: Nama Jalan + No Kavling + nama sales dengan style muted di baris baru.
- **Konsumen**: Nama konsumen + tipe rumah (nama tipe yang ada di pricelist) + badge status subsidi/komersil.
- **Tanggal booking**: tanggal transaksi booking.
- **Status Terakhir**: badge status (booking, wawancara, SP3K, batal, akad indent, akad AJB) disertai isi keterangan/catatan dari tabel `mkdt`.
- **Tanggal status terakhir**: tanggal dari status terakhir (wawancara / SP3K / batal / akad indent / akad AJB).
- **Tombol aksi**: tombol untuk membuka modal detail unit kavling/siteplan (`siteplan/partials/modal_detail`) dengan memastikan seluruh dependensi dan resource JavaScript/CSS terpanggil agar tidak break saat dibuka.

### R4. Perataan Teks Tampilan
Semua teks pada tabel laporan dan tabel modal detail dibuat rata kiri, kecuali kolom nominal (rata kanan) dan kolom tanggal.

### R5. Dokumentasi Modul
Buat dokumentasi lengkap modul Laporan Booking & Akad pada `docs/modules/` sesuai standar dokumentasi SIGAPP, mendokumentasikan rute, kontroler, service, repositori, struktur data, dan perubahan logika konversi.

## Verification Resources
Gunakan data transaksi pada proyek aktif "Sanggar Indah Palastri" di database lokal untuk memverifikasi kalkulasi matriks, rasio konversi per kode transaksi, pembukaan modal detail, serta integrasi tombol aksi.

## Acceptance Criteria

### Metrik & Kalkulasi
- [ ] Tabel laporan menampilkan dan menghitung seluruh 6 status: booking, wawancara, SP3K, batal, akad indent, dan akad AJB.
- [ ] Rasio konversi dihitung dari perbandingan transaksi unik (kode transaksi) yang booking pada periode tersebut dan telah mencapai akad (akad indent atau akad AJB).
- [ ] Rasio konversi ditampilkan pada baris tabel bulanan dan baris total tahunan.

### Modal Detail & UI
- [ ] Modal detail menampilkan kolom: No, Kavling (Jalan + No Kavling + sales muted), Konsumen (Nama + tipe pricelist + badge subsidi/komersil), Tanggal booking, Status Terakhir (badge status + catatan mkdt), Tanggal status terakhir, dan Tombol aksi.
- [ ] Klik pada tombol aksi di baris modal detail berhasil menampilkan modal detail kavling/siteplan tanpa JavaScript runtime error atau resource missing.
- [ ] Format teks di tabel laporan dan modal detail rata kiri, kecuali kolom nominal rata kanan dan kolom tanggal.

### Dokumentasi
- [ ] File dokumentasi modul dibuat/diperbarui di direktori `docs/` dan memuat alur lengkap modul Laporan Booking & Akad.
