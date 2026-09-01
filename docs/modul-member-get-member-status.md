# Guide Status Modul Member Get Member

Dokumen ini merangkum alur dan status modul Member Get Member (MGM) setelah sinkronisasi referral, bonus Booking/Akad, pengajuan SPP Promosi, pencairan Keuangan, pembayaran ke member, riwayat, lampiran progres, dan modal detail dipisahkan.

## Entry Point Modul

- Halaman web: `GET /member-get-member`
- Web controller: `app/Controllers/Web/MemberGetMemberController.php`
- View utama: `app/Views/mgm/index.php`
- Modal detail: `app/Views/mgm/modal_detail_pencairan.php`
- JavaScript UI: `public/assets/js/mgm.js`
- CSS UI: `public/assets/css/mgm.css`
- API utama: `app/Controllers/Api/ReferralController.php`
- Service status: `app/Services/ReferralService.php`
- Query list/detail: `app/Repositories/ReferralRepository.php`
- Model bonus: `app/Models/ReferralBonusModel.php`
- Model riwayat: `app/Models/ReferralBonusHistoryModel.php`
- Repair bonus lama: `php spark mgm:repair-bonuses --dry-run`

`MemberGetMemberController` hanya merender halaman MGM. Aturan bisnis referral dan bonus berada di `ReferralService`; angka list/subrow dihitung dari query `ReferralRepository`; interaksi modal, timeline, filter, upload, dan preview lampiran dikendalikan oleh `public/assets/js/mgm.js`.

## Tabel Yang Terlibat

| Tabel | Fungsi |
|---|---|
| `konsumen.kode_referal` | Kode referral milik konsumen/referrer. Dibuat otomatis saat data konsumen disimpan jika belum ada. |
| `referrals` | Relasi pemilik kode referral dengan transaksi MKDT yang memakai kode tersebut. Field `status` menandai relasi `active` atau `inactive`. |
| `referral_bonus_stages` | Master tahapan bonus per proyek, misalnya Bonus Booking dan Bonus Akad. |
| `referral_bonuses` | Status, nominal, tanggal, lampiran, dan data penerima aktual per referral dan per tahapan. |
| `referral_bonus_histories` | Riwayat perubahan referral/bonus, perubahan nominal, pengajuan SPP, pencairan Keuangan, pembayaran Promosi, dan pembatalan. |

Field penting pada `referral_bonuses`:

| Kelompok | Field |
|---|---|
| Status dasar | `id_referral`, `id_stage`, `nominal_bonus`, `status`, `eligible_at`, `confirmed_at`, `confirmed_by` |
| Pengajuan SPP | `nominal_pengajuan_keuangan`, `tanggal_spp`, `bukti_pengajuan_keuangan`, `submitted_keuangan_at`, `submitted_keuangan_by` |
| Cair dari Keuangan | `nominal_cair_keuangan`, `tanggal_cair_keuangan`, `bukti_transfer_ke_promosi`, `cair_keuangan_at`, `cair_keuangan_by`, `cair_keuangan_penerima_nama`, `cair_keuangan_no_rekening`, `cair_keuangan_bank` |
| Cair ke Member | `paid_by_promosi`, `paid_promosi_at`, `paid_promosi_tanggal`, `paid_promosi_by`, `bukti_bayar_promosi`, `paid_promosi_penerima_nama`, `paid_promosi_no_rekening`, `paid_promosi_bank` |
| Catatan | `keterangan`, `add_by`, `edit_by`, `created_at`, `updated_at` |

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

Saat status MKDT disimpan atau diubah, `ReferralService::checkAndActivateBonuses()` mencari stage aktif yang `trigger_status_mkdt`-nya sama dengan status MKDT. Stage bernama Akad atau stage dengan trigger `Akad` hanya boleh aktif ketika status MKDT sudah `Akad`, sehingga Bonus Akad tidak muncul di subtable dan modal detail sebelum transaksi benar-benar akad.

Jika cocok:

- Belum ada bonus pada stage itu: buat row `referral_bonuses` dengan status `eligible`.
- Bonus sebelumnya `batal`: aktifkan ulang menjadi `eligible`.
- Bonus sudah ada dengan status lain: tidak dibuat ulang.

Alur umum: status `Booking` membuat Bonus Booking; saat status berubah menjadi `Akad`, Bonus Akad dibuat sebagai stage terpisah tanpa menghapus Bonus Booking.

## Status Bonus

Status mentah tetap disimpan di `referral_bonuses.status`, tetapi UI menampilkan status Promosi dan Keuangan secara terpisah.

| Status mentah | Label bonus | Arti |
|---|---|---|
| `eligible` | Belum diajukan | Bonus otomatis terbentuk, belum diajukan ke Keuangan dan belum dibayar ke member. |
| `dikonfirmasi` | Belum diajukan | Status lama/kompatibilitas untuk bonus yang sudah dikonfirmasi nominal/kelayakan, tetapi belum diajukan ke Keuangan dan belum dibayar ke member. |
| `dibayar_promosi` | Cair Dari Promosi | Promosi sudah membayar bonus ke member sebelum dana Keuangan cair. Referral terkunci. |
| `diajukan_keuangan` | Diajukan Keuangan | Promosi sudah mengajukan dana ke Keuangan dengan nominal, tanggal SPP, dan lampiran SPP. |
| `cair` | Cair Dari Keuangan | Keuangan sudah transfer dana ke Promosi, tetapi Promosi belum membayar member. |
| `selesai` | Cair Dari Keuangan | Keuangan sudah cair dan Promosi sudah membayar member. |
| `batal` | Batal | Bonus dibatalkan karena referral dihapus/dibatalkan atau aksi pembatalan. |

Label Promosi:

| Kondisi | Label |
|---|---|
| `paid_by_promosi = 1` | `Cair ke Member` |
| Selain itu | `Belum cair ke Member` |

Label Keuangan:

| Kondisi | Label |
|---|---|
| Status `cair` / `selesai` atau `cair_keuangan_at` terisi | `Sudah cair` |
| Status `diajukan_keuangan` | `Sedang diajukan` |
| Selain itu | `Belum cair` |

## Alur Promosi Dan Keuangan

| Dari | Aksi/pemicu | Ke | Endpoint/fungsi |
|---|---|---|---|
| Tidak ada bonus | Status MKDT cocok stage aktif | `eligible` | `ReferralService::checkAndActivateBonuses()` |
| `batal` | Status MKDT cocok ulang | `eligible` | `ReferralService::checkAndActivateBonuses()` |
| `eligible` | Promosi konfirmasi nominal | `dikonfirmasi` | `POST /api/mgm/confirm-bonus` |
| `eligible` / `dikonfirmasi` / `dibayar_promosi` | Promosi ajukan SPP ke Keuangan | `diajukan_keuangan` | `POST /api/mgm/submit-keuangan` |
| `eligible` / `dikonfirmasi` / `cair` / `dibayar_promosi` / `selesai` | Promosi ubah nominal bonus | Status tetap | `POST /api/mgm/update-nominal` |
| `dikonfirmasi` / `diajukan_keuangan` | Promosi bayar member sebelum dana Keuangan cair | `dibayar_promosi` atau tetap `diajukan_keuangan` | `POST /api/mgm/pay-promosi` |
| `diajukan_keuangan` | Keuangan transfer dana ke Promosi, member belum dibayar | `cair` | `POST /api/mgm/mark-cair-keuangan` |
| `diajukan_keuangan` | Keuangan transfer dana ke Promosi, member sudah dibayar | `selesai` | `POST /api/mgm/mark-cair-keuangan` |
| `cair` | Promosi bayar member | `selesai` | `POST /api/mgm/pay-promosi` |
| Status apa pun | Promosi/Admin batalkan bonus | `batal` | `POST /api/mgm/cancel-bonus` |
| Status apa pun | Promosi/Admin update catatan | Status tetap | `POST /api/mgm/update-keterangan` |

Catatan:

- Tombol Promosi pertama kali langsung membuka form `Ajukan SPP Bonus`, bukan form edit nominal.
- Edit nominal dipisahkan ke tombol `Edit Nominal`.
- Setelah SPP diajukan, tombol utama Promosi berubah menjadi `Cairkan Bonus ke Member`.
- Jika Promosi mencairkan ke member ketika status masih `diajukan_keuangan` dan dana belum cair, UI menampilkan warning: `Dana belum cair, apakah kamu yakin akan mencairkan dana ke member?`.
- Keuangan hanya dapat mencairkan bonus berstatus `diajukan_keuangan`; tombol `Cair Dari Keuangan` dibuat disabled dan secondary ketika status belum bisa dicairkan.

## Notifikasi MGM

Notifikasi MGM dikirim melalui `App\Services\NotifikasiService`, sehingga activity navbar, email queue, dan web push mengikuti perilaku modul notifikasi utama.

| Event | Target group | Notification type | Pesan utama |
|---|---|---|---|
| Bonus Booking/Akad menjadi `eligible` dari input konsumen atau perubahan status Akad dengan kode referal valid | Promosi (`8`) | `mgm_referral_created` | Nama konsumen referred, kavling, booking/akad, dan kode referal. |
| Promosi mengajukan SPP bonus | Keuangan (`3`) | `mgm_spp_submitted` | User Promosi, tahapan bonus, nama konsumen, dan kavling. |
| Keuangan mencairkan SPP bonus | Promosi (`8`) | `mgm_spp_cair` | User Keuangan, tahapan bonus, nama penerima, nomor rekening, dan bank jika ada. |

Setiap notifikasi mengisi `id_kavling`, `id_konsumen`, dan `id_proyek` dari context referral/bonus.

## Hak Akses Role

| Role/group | Hak akses |
|---|---|
| Admin (`1`) | Dapat menjalankan aksi Promosi dan Keuangan. |
| Keuangan (`3`) | Dapat menjalankan `Cair Dari Keuangan`. Form Keuangan disabled sampai status bonus `diajukan_keuangan`. |
| Sales & Promotion (`8`) | Dapat ajukan SPP Bonus, cair/bayar ke member, ubah nominal pada status valid, batal, dan update catatan. |
| Departemen lain | Tombol pencarian/detail dibuat `secondary disabled` dan DataTables search disembunyikan di UI. |

Catatan backend: `ReferralController::searchOptions()` masih mengembalikan opsi referrer dari repository; fungsi helper `canMgmSearch()` sudah tersedia tetapi belum dipakai di endpoint tersebut. Jika role non-MGM tidak boleh memakai endpoint pencarian langsung, guard backend perlu ditambahkan.

## Field Pengajuan Dan Pencairan

| Field | Diisi oleh | Fungsi |
|---|---|---|
| `nominal_pengajuan_keuangan` | Promosi | Nominal yang diajukan ke Keuangan. Tidak boleh melebihi nominal bonus. |
| `tanggal_spp` | Promosi | Tanggal SPP saat pengajuan. |
| `bukti_pengajuan_keuangan` | Promosi | Lampiran SPP foto/PDF. |
| `submitted_keuangan_at` / `submitted_keuangan_by` | Sistem | Waktu dan user pengaju. |
| `nominal_cair_keuangan` | Keuangan | Nominal cair. Tidak boleh melebihi nominal pengajuan. |
| `tanggal_cair_keuangan` | Keuangan | Tanggal dana cair/transfer ke Promosi. |
| `bukti_transfer_ke_promosi` | Keuangan | Bukti transfer dana dari Keuangan ke Promosi. |
| `cair_keuangan_at` / `cair_keuangan_by` | Sistem | Waktu dan user yang mencatat pencairan. |
| `cair_keuangan_penerima_nama` | Keuangan | Nama penerima dana pencairan Keuangan. |
| `cair_keuangan_no_rekening` | Keuangan | Nomor rekening tujuan pencairan Keuangan. |
| `cair_keuangan_bank` | Keuangan | Bank tujuan pencairan Keuangan. |
| `paid_promosi_tanggal` | Promosi | Tanggal pembayaran/cair ke member. |
| `paid_promosi_penerima_nama` | Promosi | Nama penerima bonus member. |
| `paid_promosi_no_rekening` | Promosi | Nomor rekening penerima bonus member. |
| `paid_promosi_bank` | Promosi | Bank penerima bonus member. |
| `bukti_bayar_promosi` | Promosi | Lampiran pembayaran ke member. |
| `keterangan` | Promosi/Keuangan | Keterangan atau catatan proses terakhir. |

Lampiran MGM diterima sebagai gambar atau PDF. UI mendukung klik file, drag and drop, dan paste dari clipboard; gambar dikompres di browser sebelum dikirim. Controller menyimpan file melalui `StorageService::store()`, lalu service menampilkan URL akses melalui `FileAccessService::pathUrl('mgm_bonus_file', ...)`, bukan direct URL `uploads`.

`FileAccessService` source `mgm_bonus_file` mengizinkan akses untuk group `1`, `3`, `8`, dan `9`.

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

## Filter List MGM

Filter tersedia di modal `Filter Data MGM`.

| Filter | Perilaku |
|---|---|
| Pemilik kode referal | Select2 dari `POST /api/mgm/search-options`, berdasarkan kode/nama pemilik referral aktif. |
| Status acuan `booking` | Memakai stage non-Akad dan range `mk.booking_tgl`. |
| Status acuan `akad` | Memakai stage Akad, status MKDT `Akad`, dan range `mk.akad_tgl`. |
| Status acuan `cair_bonus_booking` | Memakai stage non-Akad dan range tanggal cair dari `rb.tanggal_cair_keuangan` atau `rb.paid_promosi_tanggal`. |
| Status acuan `cair_bonus_akad` | Memakai stage Akad dan range tanggal cair dari `rb.tanggal_cair_keuangan` atau `rb.paid_promosi_tanggal`. |

Jika tanggal mulai lebih besar dari tanggal selesai, controller menukar urutan tanggal sebelum query dijalankan.

## Subtable Dan Modal Detail

Subtable per referrer mengelompokkan transaksi yang memakai kode referal, lalu menjumlahkan semua stage bonus transaksi tersebut.

Kolom subtable:

- Aksi untuk membuka modal detail.
- Nama konsumen referred dan kavling.
- Status MKDT.
- Status bonus gabungan dari semua stage.
- Nominal bonus.
- Dibayar Promosi.
- Cair Keuangan, dibuat rata kanan di kolom UI.

Modal detail menampilkan:

- Header referrer dan referred.
- Ringkasan Potensi, Cair, dan Sisa.
- Tab `Ringkasan Bonus` dan `Riwayat Bonus`.
- Stage bonus, misalnya Booking dan Akad.
- Nominal bonus per stage.
- Informasi tanggal Booking dan tanggal Akad pada setiap kartu bonus.
- Status Promosi dan status Keuangan secara terpisah.
- Aksi Promosi utama: `Ajukan SPP Bonus` untuk membuka form pengajuan SPP. Setelah SPP diajukan, tombol utama berubah menjadi `Cairkan Bonus ke Member`.
- Aksi Promosi tambahan: `Edit Nominal`, tombol terpisah dari pengajuan SPP. Tombol ini hanya muncul jika status bonus mengizinkan perubahan nominal.
- Aksi Keuangan: `Cair Dari Keuangan`; tombol dibuat `secondary disabled` ketika status belum `diajukan_keuangan`.
- Tombol aksi dan `Lihat Detail` bersifat toggle: klik pertama membuka form/timeline dengan animasi, icon berubah, dan tombol diberi state aktif; klik ulang menutupnya.
- Form tidak auto-close setelah simpan. Setelah sukses, modal tetap terbuka dan data modal di-refresh dari `POST /api/mgm/subrows`.
- Modal detail memakai `initModalListener('#modalDetailPencairan')`, sehingga close modal meminta konfirmasi terlebih dahulu.
- Timeline per stage berada di bawah masing-masing kartu bonus; disembunyikan saat modal pertama dibuka dan muncul setelah user klik `Lihat Detail`.
- Tombol `Lihat Detail` memiliki icon, berubah menjadi `Tutup Detail` saat aktif, dan state aktif menjaga teks/icon tetap putih.
- Icon timeline menjadi aktif hanya untuk step yang sudah tercapai. Step yang belum tercapai memakai icon putih/muted.
- Tab `Riwayat Bonus` menampilkan gabungan seluruh perubahan dari `referral_bonus_histories` untuk semua stage bonus pada referral tersebut.
- Modal `Lampiran Progres Bonus` muncul ketika user klik `Lihat lampiran` pada timeline. Modal ini menampilkan preview gambar/PDF, tombol buka lampiran, dan informasi progres sesuai tahap lampiran.

## Timeline Bonus

Isi timeline per stage:

| Step timeline | Isi saat sudah tercapai | Isi saat belum tercapai |
|---|---|---|
| Bonus Tercatat | Tanggal/waktu bonus terbentuk dari `eligible_at` atau `created_at`. | Tidak muncul untuk bonus yang belum terbentuk; timeline muted menjelaskan trigger belum tercapai. |
| Pengajuan SPP | User pengaju dari `submitted_keuangan_username` dan link lampiran `bukti_pengajuan_keuangan_url`. | `Belum diajukan`. |
| Cair Dari Keuangan | User pencatat dari `cair_keuangan_username` dan link lampiran `bukti_transfer_ke_promosi_url`. | `Belum cair`. |
| Cair Ke Member | User pencatat dari `paid_promosi_username` dan link lampiran `bukti_bayar_promosi_url`. | `Belum cair ke member`. |

Urutan timeline adalah `Bonus Tercatat` -> `Pengajuan SPP` -> `Cair Dari Keuangan` -> `Cair Ke Member`.

## Modal Lampiran Progres

Modal lampiran dipakai untuk semua lampiran progres: pengajuan SPP, cair dari Keuangan, dan cair ke member.

| Jenis lampiran | Informasi yang tampil |
|---|---|
| Pengajuan SPP | Tahapan bonus, tanggal pengajuan, waktu dicatat, diajukan oleh, nominal pengajuan, keterangan/catatan, dan lampiran SPP. |
| Cair Dari Keuangan | Tahapan bonus, tanggal cair, waktu dicatat, dicatat oleh, nominal cair, nama penerima, nomor rekening, bank pencairan, keterangan/catatan, dan bukti transfer Keuangan. |
| Cair Ke Member | Tahapan bonus, tanggal pembayaran, waktu dicatat, dibayar oleh, nominal bonus, nama penerima, nomor rekening, bank penerima, keterangan/catatan, dan bukti bayar Promosi. |

Preview lampiran:

- PDF ditampilkan dalam `iframe`.
- Gambar ditampilkan sebagai `img`.
- Jika lampiran belum ada, modal menampilkan placeholder `Belum ada lampiran`.
- Tombol `Buka Lampiran` hanya muncul jika URL lampiran tersedia.

## Form Promosi

| Aksi | Field utama | Validasi penting |
|---|---|---|
| Ajukan SPP Bonus | Nominal pengajuan, tanggal pengajuan, lampiran pengajuan, keterangan/catatan. | Lampiran wajib gambar/PDF; nominal wajib lebih dari 0 dan tidak boleh melebihi nominal bonus. |
| Edit Nominal | Nominal bonus dan keterangan/catatan. | Nominal wajib lebih dari 0; status harus valid untuk perubahan nominal. |
| Cairkan Bonus ke Member | Nominal bayar member, tanggal pembayaran, nama penerima, nomor rekening penerima, bank penerima, lampiran pembayaran, keterangan/catatan. | Lampiran wajib gambar/PDF; tanggal dan data penerima wajib. |

Status valid untuk edit nominal di service: `eligible`, `dikonfirmasi`, `cair`, `dibayar_promosi`, `selesai`, atau bonus yang sudah `paid_by_promosi = 1`.

## Form Keuangan

| Aksi | Field utama | Validasi penting |
|---|---|---|
| Cair Dari Keuangan | Nominal cair, tanggal cair, nama penerima, nomor rekening, bank pencairan, lampiran cair Keuangan, keterangan/catatan. | Hanya valid untuk status `diajukan_keuangan`; lampiran wajib gambar/PDF; nominal cair wajib lebih dari 0 dan tidak boleh melebihi nominal pengajuan. |

## Riwayat Bonus

`ReferralService::logHistory()` menulis riwayat ke `referral_bonus_histories` pada aksi berikut:

| Action | Label UI |
|---|---|
| `referral_created` | Referral dibuat |
| `referral_sync` | Referral disinkronkan |
| `referral_deactivated` | Referral dinonaktifkan |
| `bonus_eligible` | Bonus terbentuk |
| `bonus_reactivated` | Bonus diaktifkan ulang |
| `bonus_confirmed` | Bonus dikonfirmasi |
| `bonus_nominal_updated` | Nominal bonus diperbarui |
| `paid_by_promosi` | Dibayar Promosi |
| `submitted_to_keuangan` | Diajukan ke Keuangan |
| `cair_from_keuangan` | Cair dari Keuangan |
| `bonus_canceled` | Bonus dibatalkan |
| `bonus_canceled_by_referral_sync` | Bonus dibatalkan otomatis |
| `marked_selesai` | Ditandai selesai |
| `keterangan_updated` | Catatan diperbarui |

Repository mengambil histori per `id_bonus` dan histori referral tanpa `id_bonus`, lalu UI menggabungkan seluruh histori stage pada tab `Riwayat Bonus`.

## Endpoint MGM

| Endpoint | Fungsi |
|---|---|
| `POST /api/mgm/list` | List referrer untuk tabel utama. |
| `POST /api/mgm/subrows` | Detail referred dan stage bonus per referrer. |
| `POST /api/mgm/confirm-bonus` | Mengubah `eligible` menjadi `dikonfirmasi` dan dapat menyesuaikan nominal bonus. |
| `POST /api/mgm/update-nominal` | Mengubah nominal bonus dan mencatat histori `bonus_nominal_updated`. |
| `POST /api/mgm/pay-promosi` | Mencatat pembayaran Promosi ke member dan bukti bayar. |
| `POST /api/mgm/submit-keuangan` | Mencatat pengajuan SPP Promosi ke Keuangan. |
| `POST /api/mgm/mark-cair-keuangan` | Mencatat pencairan Keuangan ke Promosi. |
| `POST /api/mgm/cancel-bonus` | Membatalkan bonus menjadi `batal`. |
| `POST /api/mgm/update-keterangan` | Update catatan bonus. |
| `POST /api/mgm/search-options` | Select2 pilihan kode referral pada form/filter MGM. |
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

## Checklist Validasi Manual

- Bonus Booking muncul ketika status MKDT sudah cocok dengan trigger Booking.
- Bonus Akad tidak muncul sebelum status MKDT `Akad`.
- Timeline setiap bonus tersembunyi saat modal pertama dibuka.
- Tombol `Lihat Detail` membuka/menutup timeline dengan animasi, icon berubah, dan state aktif jelas.
- Tombol `Ajukan SPP Bonus` membuka form pengajuan SPP, bukan form edit nominal.
- Setelah simpan form, modal tetap terbuka dan data di-refresh.
- Role Keuangan melihat tombol `Cair Dari Keuangan` disabled secondary sebelum status `diajukan_keuangan`.
- Lampiran pengajuan SPP, cair Keuangan, dan cair member dapat dibuka dari timeline dan tampil di modal lampiran dengan informasi progres yang sesuai.
- Input konsumen Booking/Akad dengan kode referal valid membuat notifikasi `mgm_referral_created` untuk group Promosi (`8`).
- Submit SPP membuat notifikasi `mgm_spp_submitted` untuk group Keuangan (`3`).
- Cair Dari Keuangan membuat notifikasi `mgm_spp_cair` untuk group Promosi (`8`).
- Close modal detail memunculkan konfirmasi dari `initModalListener()`.
