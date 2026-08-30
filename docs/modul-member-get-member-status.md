# Guide Status Modul Member Get Member

Dokumen ini merangkum status pada modul Member Get Member (MGM) berdasarkan implementasi saat ini.

## Entry Point Modul

- Halaman web: `GET /member-get-member`
- Web controller: `app/Controllers/Web/MemberGetMemberController.php`
- View utama: `app/Views/mgm/index.php`
- JavaScript UI: `public/assets/js/mgm.js`
- API utama: `app/Controllers/Api/ReferralController.php`
- Service status: `app/Services/ReferralService.php`
- Query list/detail: `app/Repositories/ReferralRepository.php`

`MemberGetMemberController` hanya merender halaman MGM. Semua status bonus berasal dari tabel `referral_bonuses` dan diproses lewat `ReferralService`.

## Tabel Yang Terlibat

| Tabel | Fungsi |
|---|---|
| `konsumen.kode_referal` | Kode referral milik konsumen/referrer. Dibuat otomatis saat data konsumen disimpan jika belum ada. |
| `referrals` | Relasi referrer dengan MKDT/konsumen yang direferensikan. Satu `id_mkdt_referred` hanya boleh punya satu referral. |
| `referral_bonus_stages` | Master tahapan bonus per proyek. Berisi nama tahapan, nominal default, urutan, status MKDT pemicu, dan aktif/tidak. |
| `referral_bonuses` | Status bonus aktual per referral dan per tahapan. Inilah sumber utama status MGM. |

## Trigger Tahapan Bonus

Tahapan bonus diatur dari modal "Pengaturan Tahapan Bonus Referral".

Field penting:

| Field | Arti |
|---|---|
| `nama_tahapan` | Nama tahapan bonus yang tampil di UI. |
| `trigger_status_mkdt` | Status MKDT yang memicu bonus, contoh `Booking`, `Akad`, `SP3K`, `Batal`. |
| `nominal_default` | Nominal awal bonus saat tahapan menjadi eligible. |
| `urutan` | Urutan tampil tahapan. |
| `is_active` | Hanya stage aktif yang dipakai untuk membuat/menampilkan bonus. |

Saat status MKDT disimpan atau diubah, `ReferralService::checkAndActivateBonuses()` akan mencari stage aktif dengan `trigger_status_mkdt` yang sama dengan status MKDT. Pencocokan tidak sensitif huruf besar/kecil.

Jika cocok:

- Belum ada bonus pada stage itu: buat row `referral_bonuses` dengan status `eligible`.
- Bonus sebelumnya `batal`: aktifkan ulang menjadi `eligible`.
- Bonus sudah ada dengan status lain: tidak dibuat ulang.

## Status Bonus Mentah

Status mentah disimpan di `referral_bonuses.status`.

| Status | Label umum di UI | Arti | Aksi berikutnya |
|---|---|---|---|
| `eligible` | `Pending` / `Belum diajukan` | Bonus sudah terbentuk otomatis karena status MKDT memenuhi trigger stage, tetapi belum dikonfirmasi Promosi. | Promosi konfirmasi bonus. |
| `dikonfirmasi` | `Diajukan Promosi` | Promosi sudah mengonfirmasi kelayakan bonus. Nominal dapat mengikuti default atau diubah saat konfirmasi. | Bayar dari Promosi atau ajukan ke Keuangan. |
| `dibayar_promosi` | `Diajukan Promosi` | Promosi sudah membayar dana talangan ke member. Bukti bayar tersimpan di `bukti_bayar_promosi`. | Ajukan reimburse/pencairan ke Keuangan. |
| `diajukan_keuangan` | `Menunggu Pencairan` | Bonus sudah diajukan ke Keuangan. | Tunggu sinkronisasi status dari proses Keuangan. |
| `cair` | `Cair Dari Keuangan` | Keuangan sudah mencairkan, tetapi belum ditandai selesai dari sisi pembayaran Promosi. | Dapat menjadi `selesai` setelah Promosi membayar/menutup proses. |
| `selesai` | `Completed` / `Cair Dari Keuangan` | Siklus bonus selesai. Umumnya terjadi jika Keuangan sudah cair dan Promosi sudah membayar member. | Tidak ada aksi lanjutan di UI saat ini. |
| `batal` | `Batal` | Bonus dibatalkan. | Dapat aktif ulang menjadi `eligible` jika status MKDT kembali memenuhi trigger stage. |

Catatan: stage yang aktif tetapi belum punya row `referral_bonuses` akan tampil seperti `Belum diajukan` pada detail subrow karena `bonus_status` bernilai kosong/null.

## Alur Transisi Status

| Dari | Aksi/pemicu | Ke | Method/endpoint |
|---|---|---|---|
| Tidak ada bonus | Status MKDT cocok dengan stage aktif | `eligible` | `ReferralService::checkAndActivateBonuses()` |
| `batal` | Status MKDT cocok ulang dengan stage aktif | `eligible` | `ReferralService::checkAndActivateBonuses()` |
| `eligible` | Konfirmasi Promosi | `dikonfirmasi` | `POST /api/mgm/confirm-bonus` |
| `dikonfirmasi` | Bayar dari Promosi dengan bukti bayar | `dibayar_promosi` | `POST /api/mgm/pay-promosi` |
| `dikonfirmasi` | Ajukan ke Keuangan | `diajukan_keuangan` | `POST /api/mgm/submit-keuangan` |
| `dibayar_promosi` | Ajukan ke Keuangan | `diajukan_keuangan` | `POST /api/mgm/submit-keuangan` |
| `diajukan_keuangan` | Keuangan cair, belum dibayar Promosi | `cair` | `ReferralService::syncFromKeuangan()` |
| `diajukan_keuangan` | Keuangan cair, sudah dibayar Promosi | `selesai` | `ReferralService::syncFromKeuangan()` |
| `diajukan_keuangan` | Keuangan menolak, belum dibayar Promosi | `dikonfirmasi` | `ReferralService::syncFromKeuangan()` |
| `diajukan_keuangan` | Keuangan menolak, sudah dibayar Promosi | `dibayar_promosi` | `ReferralService::syncFromKeuangan()` |
| `cair` | Promosi membayar/menutup pembayaran | `selesai` | `ReferralService::payByPromosi()` |
| Status apa pun | Pembatalan bonus | `batal` | `POST /api/mgm/cancel-bonus` |
| Status apa pun | Tandai selesai manual dari service | `selesai` | `ReferralService::markSelesai()` |

## Timeline Di Modal Detail

Timeline pada modal detail menampilkan tiga langkah:

| Langkah | Status yang dianggap aktif | Status yang dianggap selesai |
|---|---|---|
| Pengajuan Dibuat | `eligible` | Semua status selain `eligible` dan `batal` |
| Verifikasi Dept. Promosi | `dikonfirmasi`, `dibayar_promosi` | `diajukan_keuangan`, `cair`, `selesai` |
| Menunggu Keuangan | `diajukan_keuangan` | `cair`, `selesai` |

Status `batal` tidak menampilkan aksi form pada modal detail.

## Label Ringkasan Per Referred

Pada subrow "Daftar Member yang Diajak", beberapa stage milik satu referred digabung menjadi satu label status.

Urutan prioritas label:

| Kondisi stage | Label |
|---|---|
| Ada minimal satu `diajukan_keuangan` | `Menunggu Pencairan` |
| Ada minimal satu `dikonfirmasi` atau `dibayar_promosi` | `Diajukan Promosi` |
| Ada minimal satu `eligible` atau belum ada row bonus | `Belum diajukan` |
| Ada minimal satu `cair` atau `selesai` | `Cair Dari Keuangan` |

Karena prioritas ini, jika satu referred punya stage yang sudah cair tetapi masih ada stage lain yang eligible/null, label gabungannya dapat tetap tampil `Belum diajukan`.

## Ringkasan Angka Di List Utama

List utama diambil dari `ReferralRepository::getListMGM()`.

| Kolom UI | Rumus saat ini |
|---|---|
| Total Penghasilan | `SUM(referral_bonuses.nominal_bonus)` |
| Cair ke Member (Promosi) | Sum status `dibayar_promosi` dan `selesai` |
| Sudah Cair (Keuangan) | Sum status `cair` dan `selesai` |
| Sisa Belum Cair | Sum status selain `cair`, `selesai`, `dibayar_promosi`, dan `batal` |

## Endpoint MGM

| Endpoint | Fungsi |
|---|---|
| `POST /api/mgm/list` | List referrer untuk tabel utama. |
| `POST /api/mgm/subrows` | Detail referred dan stage bonus per referrer. |
| `POST /api/mgm/confirm-bonus` | Mengubah `eligible` menjadi `dikonfirmasi`. |
| `POST /api/mgm/pay-promosi` | Mencatat pembayaran Promosi dan bukti bayar. |
| `POST /api/mgm/submit-keuangan` | Mengubah status menjadi `diajukan_keuangan`. |
| `POST /api/mgm/cancel-bonus` | Membatalkan bonus menjadi `batal`. |
| `POST /api/mgm/update-keterangan` | Update catatan bonus. |
| `POST /api/mgm/search-options` | Select2 pilihan kode referral pada form MKDT. |
| `POST /api/mgm/stages/list` | List master tahapan bonus. |
| `POST /api/mgm/stages/save` | Tambah/edit master tahapan bonus. |
| `POST /api/mgm/stages/delete` | Hapus master tahapan bonus. |

## Catatan Implementasi Saat Ini

- `submitToKeuangan()` saat ini hanya mengubah status bonus menjadi `diajukan_keuangan`. Belum terlihat proses membuat row `pengajuan_pencairan` atau mengisi `referral_bonuses.id_pengajuan_pencairan`.
- `syncFromKeuangan()` mencari bonus berdasarkan `id_pengajuan_pencairan`. Agar callback ini bekerja, integrasi Keuangan harus memastikan `id_pengajuan_pencairan` terisi.
- `submitToKeuangan()` mencoba menyimpan `bukti_pengajuan_keuangan`, tetapi field ini belum ada di migration `referral_bonuses` dan belum masuk `ReferralBonusModel::$allowedFields`.
- `cancelBonus()` dan `markSelesai()` di service tidak membatasi status asal. Kalau dipakai dari endpoint/tool lain, validasi bisnis sebaiknya ditambahkan sesuai kebutuhan.
- Tombol/aksi UI yang terlihat saat ini mencakup konfirmasi, bayar Promosi, dan ajukan Keuangan. Endpoint cancel dan update keterangan sudah ada, tetapi belum terlihat sebagai tombol utama di modal detail.
