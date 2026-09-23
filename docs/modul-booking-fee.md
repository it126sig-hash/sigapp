# Modul Booking Fee MKDT

## Tujuan dan aturan bisnis

Penyimpanan data konsumen oleh MKDT sekaligus menyatakan booking sudah dibayar pada nominal `booking_fee` dan tanggal `booking_tgl`. Verifikasi Keuangan hanya mengesahkan kecocokan data dan tidak membuat penerimaan baru.

- Booking MKDT lebih dari Rp0 dicatat sebagai pembayaran `Booking` dengan satu detail kategori `BO`, satu ledger income, dan tidak dihitung sebagai angsuran.
- Booking MKDT Rp0 adalah promo. Jika Keuangan pernah mencatat alokasi `BO`, detail itu diberi `booking_is_installment = 1`, diperlakukan sebagai `UM`, dan tetap menjadi bagian angsuran serta kuitansi UM.
- Promo tanpa penerimaan Keuangan hanya memiliki status bisnis “sudah dibayar”; sistem tidak membuat pembayaran atau ledger nol.
- Setelah `verified_at` terisi, perubahan nominal atau tanggal ditolak oleh backend dan input MKDT dikunci pada frontend.
- `mkdt.is_lunas` historis tidak diubah. Jika total angsuran hasil pemisahan lebih kecil dari tagihan, daftar tagihan menampilkan **Perlu rekonsiliasi**.

## Penyimpanan data

Migrasi `2026-09-16-000001_CreateMkdtBookingPayment` menambahkan:

- `mkdt_booking_payment`: satu baris per ID MKDT, referensi ke penerimaan booking, `verified_by`, `verified_at`, dan referensi pembayaran yang diganti. `id_pembayaran` sengaja tidak unik agar riwayat MKDT pengganti dapat menunjuk satu penerimaan yang sama.
- `log_pembayaran_detail.booking_is_installment`: provenance permanen untuk membedakan booking positif dari alokasi promo yang tetap menjadi UM.
- `booking_fee_migration_log`: audit tindakan migrasi dengan nilai sebelum dan sesudah. `action_key` unik membuat pencatatan dapat dijalankan ulang.

`BookingPaymentService::synchronize()` dipanggil dalam transaksi database yang sama dengan penyimpanan MKDT. Kegagalan pembuatan pembayaran, detail, ringkasan, atau ledger membatalkan seluruh penyimpanan.

## Verifikasi Keuangan

Endpoint `POST /api/tagihan/booking/verifikasi` menerima `id_mkdt`, `booking_fee`, dan `booking_tgl`. Akses dibatasi untuk grup administrator/Keuangan dan diperiksa lagi terhadap proyek transaksi.

Tombol verifikasi membuka form berisi nominal dan tanggal booking dari MKDT. Keuangan dapat mengoreksi keduanya sebelum verifikasi. Server memperbarui MKDT lalu menyinkronkan pembayaran, detail BO, ringkasan, dan ledger dalam satu transaksi sebelum mengisi `verified_by` serta `verified_at`. Kegagalan salah satu tahap membatalkan seluruh perubahan. Setelah berhasil, nominal dan tanggal terkunci. Permintaan ulang dengan nilai yang sama aman dan mengembalikan verifikasi pertama; nilai yang berbeda ditolak. Semua data hasil migrasi sengaja berstatus belum diverifikasi.

Pembayaran booking otomatis tidak dapat dihapus lewat riwayat pembayaran umum. Untuk MKDT dengan booking positif, server juga menolak alokasi BO tambahan meskipun permintaan dibentuk di luar frontend.

## Tampilan dan perhitungan

Modal pembayaran menampilkan kartu Booking Fee di atas list tagihan. Kartu memuat nominal, tanggal, status pembayaran, status verifikasi, riwayat booking, dan kuitansi booking tersendiri. Riwayat tersebut tidak muncul lagi di riwayat angsuran.

Objek detail tagihan menyediakan:

- `booking`: sumber MKDT, nominal efektif, tanggal, pembayaran canonical, status promo/verifikasi, dan riwayat.
- `angsuran`: total tagihan, sudah bayar, sisa, breakdown kategori efektif, dan flag rekonsiliasi.

Field respons lama tetap tersedia untuk kompatibilitas. Pada modal tagihan, nilainya mengikuti perhitungan angsuran. Laporan Cash In memakai aturan kategori efektif yang sama: BO dengan `booking_is_installment = 0` masuk Booking Fee; BO dengan flag `1` masuk Uang Muka.

## Migrasi historis

Command berikut wajib diarahkan dahulu ke database salinan:

```powershell
php spark booking:migrate --database nama_database_clone --install-schema --report "C:\path\clone-run-1.json"
php spark booking:migrate --database nama_database_clone --report "C:\path\clone-run-2.json"
```

Bandingkan hasil keuangan kedua laporan. Jumlah pembayaran, detail, ledger, mapping, nominal, tanggal, kategori, dan sisa tagihan harus identik. Setelah itu jalankan migrasi schema biasa dan command tanpa `--database` pada database tujuan.

Migrator menangani koreksi 163/170/199, mempertahankan 154 dan 576 sebagai booking, memindahkan 987 ke Biaya Proses dan 863 ke UM, menormalkan 1002, serta memisahkan 1001 menjadi UM Rp1.000.000 tanggal 1 September 2026 dan booking Rp1.500.000 tanggal 9 Juli 2025. Sebelas pembayaran terhapus dibuat ulang dan ID lama disimpan pada `replaces_payment_ids`. Pasangan 71→72, 74→75, dan 318→319 berbagi satu penerimaan.

Relasi 63→304 dan riwayat 293 tidak digabung otomatis. MKDT 142 tetap ditandai karena identitas/kavling belum terhubung. Status batal dan refund tidak diubah.

## Penerapan workspace 16 September 2026

Backup pra-migrasi:

`writable/backups/booking-fee/sigapp_before_20260916_032422.sql`

Salinan rehearsal: `sigapp_booking_clone_20260916_032422`. Hasil run pertama, run kedua, dan database workspace memiliki hash keuangan yang sama: `10e21f9f22984c1e21adf48567533628b361236018c1c366d1178d5b9f7922ca`.

Hasil penerapan:

- 310 sumber MKDT memiliki mapping; 296 memiliki penerimaan booking dan 14 promo Rp0 tidak membuat penerimaan baru.
- 11 pembayaran terhapus diganti senilai Rp13.000.000.
- 120 booking lain yang belum tercatat dibuat senilai Rp197.500.000.
- Koreksi 163 dan 199 menambah Rp1.500.000 pada penerimaan lama. Total kenaikan pembayaran aktif dan ledger adalah Rp212.000.000; keduanya tetap seimbang.
- Tiga pasangan riwayat berbagi penerimaan; tidak ada duplikasi mapping per MKDT.
- Semua 310 mapping masih belum diverifikasi oleh Keuangan.
- Flag lunas yang membutuhkan rekonsiliasi setelah pemisahan: 104, 110, 161, 187, dan 208. MKDT 187 tetap lunas dengan tagihan Rp24.100.000, angsuran Rp23.100.000, dan sisa Rp1.000.000.

Laporan lengkap sebelum/sesudah serta daftar anomali tersedia di `writable/backups/booking-fee/clone_explicit_run1_20260916_032422.json`, `clone_explicit_run2_20260916_032422.json`, dan `workspace_verification_20260916_032422.json`. Daftar ringkas dapat dibuka melalui `anomali_booking_fee_20260916.csv`.
