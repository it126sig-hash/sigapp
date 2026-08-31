# Guide Status Modul Member Get Member

Dokumen ini merangkum alur dan status modul Member Get Member (MGM) setelah sinkronisasi referral, bonus Booking/Akad, pengajuan Promosi, dan pencairan Keuangan dipisahkan.

## Entry Point Modul

- Halaman web: `GET /member-get-member`
- Web controller: `app/Controllers/Web/MemberGetMemberController.php`
- View utama: `app/Views/mgm/index.php`
- JavaScript UI: `public/assets/js/mgm.js`
- API utama: `app/Controllers/Api/ReferralController.php`
- Service status: `app/Services/ReferralService.php`
- Query list/detail: `app/Repositories/ReferralRepository.php`
- Repair bonus lama: `php spark mgm:repair-bonuses --dry-run`

`MemberGetMemberController` hanya merender halaman MGM. Semua aturan bisnis referral dan bonus berada di `ReferralService`; angka list/subrow dihitung dari query `ReferralRepository`.

## Tabel Yang Terlibat

| Tabel | Fungsi |
|---|---|
| `konsumen.kode_referal` | Kode referral milik konsumen/referrer. Dibuat otomatis saat data konsumen disimpan jika belum ada. |
| `referrals` | Relasi pemilik kode referral dengan transaksi MKDT yang memakai kode tersebut. Field `status` menandai relasi `active` atau `inactive`. |
| `referral_bonus_stages` | Master tahapan bonus per proyek, misalnya Bonus Booking dan Bonus Akad. |
| `referral_bonuses` | Status dan nominal aktual per referral dan per tahapan. |
| `referral_bonus_histories` | Riwayat perubahan referral/bonus, perubahan nominal, pengajuan, pencairan Keuangan, pembayaran Promosi, dan pembatalan. |

## Sinkronisasi Kode Referal Dari MKDT

Saat MKDT menyimpan data booking konsumen, `TransaksiService::saveTransaksi()` memanggil `ReferralService::syncReferralForMkdt()`.

Perilakunya:

| Kondisi input kode referal | Perilaku sistem |
|---|---|
| Kode baru valid | Membuat referral aktif untuk transaksi MKDT tersebut, lalu membuat bonus yang trigger statusnya cocok dengan status MKDT saat itu. |
| Kode berubah | Memindahkan referral dan bonus transaksi ke pemilik kode baru selama referral belum terkunci. |
| Kode dikosongkan | Menonaktifkan referral dan membatalkan bonus yang belum terkunci. |
| Kode invalid / beda proyek | Penyimpanan MKDT ditolak. |
| Kode milik konsumen sendiri | Penyimpanan MKDT ditolak. |
| Referral sudah terkunci | Perubahan atau penghapusan kode referal ditolak. |

Referral terkunci ketika ada bonus pada referral tersebut yang sudah dibayar oleh Sales & Promotion ke member (`paid_by_promosi = 1`, status `dibayar_promosi`, atau status `selesai`).

## Trigger Tahapan Bonus

Tahapan bonus diatur dari modal "Pengaturan Tahapan Bonus Referral".

| Field | Arti |
|---|---|
| `nama_tahapan` | Nama tahapan bonus yang tampil di UI. |
| `trigger_status_mkdt` | Status MKDT yang memicu bonus, contoh `Booking` atau `Akad`. |
| `nominal_default` | Nominal awal bonus saat tahapan menjadi eligible. |
| `urutan` | Urutan tampil tahapan. |
| `is_active` | Hanya stage aktif yang dipakai. |

Saat status MKDT disimpan atau diubah, `ReferralService::checkAndActivateBonuses()` mencari stage aktif yang `trigger_status_mkdt`-nya sama dengan status MKDT. Stage bernama Akad hanya boleh aktif ketika status MKDT sudah `Akad`, sehingga Bonus Akad tidak muncul di modal detail sebelum transaksi benar-benar akad.

Jika cocok:

- Belum ada bonus pada stage itu: buat row `referral_bonuses` dengan status `eligible`.
- Bonus sebelumnya `batal`: aktifkan ulang menjadi `eligible`.
- Bonus sudah ada dengan status lain: tidak dibuat ulang.

Alur umum: status `Booking` membuat Bonus Booking; saat status berubah menjadi `Akad`, Bonus Akad dibuat sebagai stage terpisah tanpa menghapus Bonus Booking.

## Status Bonus

Status mentah tetap disimpan di `referral_bonuses.status`, tetapi UI menampilkan status Promosi dan Keuangan secara terpisah.

| Status mentah | Label bonus | Arti |
|---|---|---|
| `eligible` | Belum diajukan | Bonus otomatis terbentuk, belum dikonfirmasi/diajukan. |
| `dikonfirmasi` | Belum diajukan | Promosi sudah mengonfirmasi nominal/kelayakan, tetapi belum mengajukan ke Keuangan dan belum membayar member. |
| `dibayar_promosi` | Cair Dari Promosi | Promosi sudah membayar bonus ke member. Referral terkunci. |
| `diajukan_keuangan` | Diajukan Keuangan | Promosi sudah mengajukan dana ke Keuangan dengan nominal, tanggal SPP, dan lampiran SPP. |
| `cair` | Cair Dari Keuangan | Keuangan sudah transfer dana ke Promosi, tetapi Promosi belum membayar member. |
| `selesai` | Cair Dari Keuangan | Keuangan sudah cair dan Promosi sudah membayar member. |
| `batal` | Batal | Bonus dibatalkan karena referral dihapus/dibatalkan atau aksi pembatalan. |

## Alur Promosi Dan Keuangan

| Dari | Aksi/pemicu | Ke | Endpoint |
|---|---|---|---|
| Tidak ada bonus | Status MKDT cocok stage aktif | `eligible` | `ReferralService::checkAndActivateBonuses()` |
| `batal` | Status MKDT cocok ulang | `eligible` | `ReferralService::checkAndActivateBonuses()` |
| `eligible` | Promosi konfirmasi nominal | `dikonfirmasi` | `POST /api/mgm/confirm-bonus` |
| `dikonfirmasi` | Promosi bayar member | `dibayar_promosi` | `POST /api/mgm/pay-promosi` |
| `dikonfirmasi` / `dibayar_promosi` | Promosi ajukan SPP ke Keuangan | `diajukan_keuangan` | `POST /api/mgm/submit-keuangan` |
| `diajukan_keuangan` | Keuangan transfer dana ke Promosi, member belum dibayar | `cair` | `POST /api/mgm/mark-cair-keuangan` |
| `diajukan_keuangan` | Keuangan transfer dana ke Promosi, member sudah dibayar | `selesai` | `POST /api/mgm/mark-cair-keuangan` |
| `cair` | Promosi bayar member | `selesai` | `POST /api/mgm/pay-promosi` |
| Status apa pun | Promosi/Admin batalkan bonus | `batal` | `POST /api/mgm/cancel-bonus` |

Role aksi:

- Admin (`1`) bisa menjalankan aksi Promosi dan Keuangan.
- Keuangan (`3`) bisa menjalankan `Cair Keuangan`.
- Sales & Promotion (`8`) bisa konfirmasi bonus, ajukan ke Keuangan, bayar member, batal, dan update catatan.

## Field Pengajuan Dan Pencairan

| Field | Diisi oleh | Fungsi |
|---|---|---|
| `nominal_pengajuan_keuangan` | Promosi | Nominal yang diajukan ke Keuangan. Tidak boleh melebihi nominal bonus. |
| `tanggal_spp` | Promosi | Tanggal SPP saat pengajuan. |
| `bukti_pengajuan_keuangan` | Promosi | Lampiran SPP foto/PDF. |
| `submitted_keuangan_at` / `submitted_keuangan_by` | Sistem | Waktu dan user pengaju. |
| `nominal_cair_keuangan` | Keuangan | Nominal cair, otomatis terisi dari nominal pengajuan di UI dan divalidasi di backend. |
| `tanggal_cair_keuangan` | Keuangan | Tanggal dana cair/transfer ke Promosi. |
| `bukti_transfer_ke_promosi` | Keuangan | Bukti transfer dana dari Keuangan ke Promosi. |
| `cair_keuangan_at` / `cair_keuangan_by` | Sistem | Waktu dan user yang mencatat pencairan. |

Lampiran MGM disimpan sebagai protected upload dan ditampilkan melalui gateway `FileAccessService` source `mgm_bonus_file`, bukan direct URL `uploads`.

## Ringkasan Angka Di List Utama

List utama diambil dari `ReferralRepository::getListMGM()` dan hanya menampilkan referral aktif.

| Kolom UI | Rumus |
|---|---|
| Jumlah referal | `COUNT(DISTINCT referrals.id)` untuk referral aktif pemilik kode tersebut. |
| Total Penghasilan | Total `nominal_bonus` semua bonus aktif non-`batal`. |
| Cair ke Member | Total bonus yang `paid_by_promosi = 1`, status `dibayar_promosi`, atau status `selesai`. |
| Sudah Cair dari Keuangan | Total `nominal_cair_keuangan` untuk status `cair`/`selesai` atau bonus yang punya `cair_keuangan_at`. |
| Sedang Diajukan | Total `nominal_pengajuan_keuangan` untuk status `diajukan_keuangan`. |
| Sisa Belum Cair | `total_penghasilan - sudah_cair_keuangan`. Nominal yang sedang diajukan tetap masuk sisa sampai benar-benar cair dari Keuangan. |

## Subtable Dan Modal Detail

Subtable per referrer mengelompokkan transaksi yang memakai kode referal, lalu menjumlahkan semua stage bonus transaksi tersebut.

Kolom subtable:

- Aksi untuk membuka modal detail.
- Nama konsumen referred dan kavling.
- Status MKDT.
- Status bonus gabungan dari semua stage.
- Nominal bonus.
- Dibayar Promosi.
- Cair Keuangan.

Modal detail menampilkan:

- Stage bonus, misalnya Booking dan Akad.
- Nominal bonus per stage.
- Status Promosi dan status Keuangan secara terpisah.
- Aksi Promosi: konfirmasi nominal, ajukan SPP ke Keuangan, bayar member.
- Aksi Keuangan: cairkan dana ke Promosi dengan tanggal cair, nominal cair otomatis dari pengajuan, dan bukti transfer.
- Timeline per stage di bawah masing-masing kartu bonus; disembunyikan saat modal pertama dibuka dan muncul setelah user klik `Lihat Detail`.
- Icon timeline menjadi aktif hanya untuk step yang sudah tercapai: bonus dibentuk, pengajuan ke Keuangan, cair dari Keuangan, atau cair ke member.
- Tab `Riwayat Bonus` yang menampilkan gabungan seluruh perubahan dari `referral_bonus_histories` untuk semua stage bonus pada referral tersebut.

## Endpoint MGM

| Endpoint | Fungsi |
|---|---|
| `POST /api/mgm/list` | List referrer untuk tabel utama. |
| `POST /api/mgm/subrows` | Detail referred dan stage bonus per referrer. |
| `POST /api/mgm/confirm-bonus` | Mengubah `eligible` menjadi `dikonfirmasi` dan dapat menyesuaikan nominal bonus. |
| `POST /api/mgm/pay-promosi` | Mencatat pembayaran Promosi ke member dan bukti bayar. |
| `POST /api/mgm/submit-keuangan` | Mencatat pengajuan SPP Promosi ke Keuangan. |
| `POST /api/mgm/mark-cair-keuangan` | Mencatat pencairan Keuangan ke Promosi. |
| `POST /api/mgm/cancel-bonus` | Membatalkan bonus menjadi `batal`. |
| `POST /api/mgm/update-keterangan` | Update catatan bonus. |
| `POST /api/mgm/search-options` | Select2 pilihan kode referral pada form MKDT. |
| `POST /api/mgm/stages/list` | List master tahapan bonus. |
| `POST /api/mgm/stages/save` | Tambah/edit master tahapan bonus. |
| `POST /api/mgm/stages/delete` | Hapus master tahapan bonus. |

## Repair Data Lama

Gunakan command berikut setelah migration:

```bash
php spark mgm:repair-bonuses --dry-run
php spark mgm:repair-bonuses
```

Command ini mencari referral aktif yang status MKDT-nya sudah cocok dengan stage aktif, tetapi belum punya row `referral_bonuses`, lalu membuat bonus missing melalui `ReferralService::checkAndActivateBonuses()`.
