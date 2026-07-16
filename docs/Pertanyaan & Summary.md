# Pertanyaan & Perminatan
aku mau ubah alur pencairan dana dari bank dan dana jaminan. aku akan gabung keduanya di satu modul.

aku akan jelaskan alurnya dulu, kamu sanggar kalau ada yang terdengar rancu. kamu pastikan modul yang existing masih bisa digunakan dan di ubah atau harus buat modul baru.

## yang harus kamu tahu:
*dana hasil akad * : nominal acc kpr (field acc_harga_kpr di table mkdt)

## alur pembuatan list pencairan dana retensi dan dana hasil akad  
- ketika user mkdt  merubah status kavling booking KPR (is_kpr = 1) menjadi akad, akan kirim notif ke user keuangan. jika acc_harga_kpr =  0, user tidak bisa ubah status jadi akad. Tapi untuk saat ini, ada bug kecil. ada ketikda konsistenan data. saat ini status akad dilihat dari status_mkdt, tapi ada kolom akad yang beriisi bool dan mereka tidak saling terhubung. status_mkdt mucnul di isi_data_konsumen() dan open_mkdt(), sementara akad hanya muncul di open_mkdt(). tambahkan restriction pada isi_data_konsumen() tidak bisa isi status_mkdt = akad dan pada open_mkdt() ketika salah satu dari status_mkdt = akad maka akad = 1 dan sebaliknya. tambahkan restriction untuk ubah status jadi akad jika acc_harga_kpr = 0

- user keuangan akan pilih kavling dengan status akad dan buka modal untuk pencairan dana hasil akad
- jika dana hasil akad = 0, akan ada popup nominal acc_harga_kpr = 0 dan harus menghubungi MKDT untuk memastikan nominal tersebut
- jika sudah clear, user keuangan akan menentukan apakah ada dana retensi (dana jaminan) yang di tahan oleh bank (tambahkan kolom catatan untuk setiap item). jika ada, user akan memilih item dan memasukan nominal yang di tahan. kemudian dana hasil akad = dana hasil akad - dana retensi 
- sertelah itu user keuangan akan menentukan dalam berapa kali dana tersebut cair secara manual. bisa cair 100% atau 30, 30, 30, 10 (tambahkan kolom catatan untuk setiap tenor). yang penting total dari rencana cair tersebut = dana hasil akad.
- setelah selesai, akan ada 2 list table:
  - list dana retensi
  - list tenor pencairan dana hasil akad

## alur pengajuan & pencairan dana retensi dan dana hasil akad
- user keuangan akan membuat pengajuan pencairan (dana retensi dan dana hasil akad) terkait item, nominal, catatan, tanggal pengajuan, lampiran (file surat yang dikirim ke bank), tanggal rencana cair. bisa mengajukan lebih dari 1 item sekaligus dalam 1 pengajuan dan nominal akan otomatis kalkulasi.
- ketika sudah disimpan jadi list, list tersebut akan menjadi piutang yang muncul di dashboard atau keperluan reporting.
- user keuangan bisa mencairkan pengajuan tersebut dengan mengisi nominal dan tanggal pencairan. bisa cair sebagian. jika cair sebagian status pada list tersebut akan tetap aktif. status akan otomatis paid ketika nominal pencairan sudah sama dengan pengajuan. akan ada notifikasi jika dana belum cair sampai tanggal rencana cair, untuk pengingat ke user keuangan untuk menagih ke bank.



# Modul Gabungan Pencairan Retensi Dan Hasil Akad

**Summary**

Existing module masih berguna, tapi tidak cukup dipakai apa adanya. `DanaJaminanService` sudah punya item, pengajuan, lampiran, history, dan ledger saat cair; `BankKprDisbursementService` sudah punya pencairan bank KPR. Namun keduanya belum mendukung satu alur gabungan: rencana retensi + tenor hasil akad, piutang aktif, partial cair manual per item, dan reminder rencana cair.

Bangun modul baru finance-owned: **Pencairan Akad**. Modul ini menggantikan menu lama Dana Jaminan dan Pencairan Bank, memakai `mkdt.harga_kpr_acc` sebagai dana hasil akad, `list_dajam` sebagai master item retensi, tabel piutang operasional baru sebagai source of truth pengajuan, dan `finance_ledger` hanya saat uang benar-benar cair.

**Key Changes**

- Perbaiki guard MKDT di jalur aktif `TransaksiService`:
  - `isi_data_konsumen()` tidak boleh menyimpan `status_mkdt = Akad`.
  - `open_mkdt()` / status modal menyinkronkan dua arah: jika `status_mkdt = Akad` maka `akad = 1`; jika checkbox `akad = 1` maka `status_mkdt = Akad`.
  - Untuk KPR (`is_kpr = 1`), perubahan ke Akad ditolak jika `harga_kpr_acc <= 0`.
  - Saat status baru masuk Akad, kirim notifikasi ke role Keuangan.
  - Data lama yang Akad tapi `harga_kpr_acc = 0` dipertahankan, ditandai/di-warning, dan diblokir dari rencana pencairan baru sampai nominal diperbaiki.

- Tambahkan data model baru:
  - `pencairan_akad_plan`: satu plan per kavling/MKDT Akad, snapshot ACC KPR, total retensi, total hasil akad, status, dan legacy reference.
  - `pencairan_akad_item`: item retensi atau tenor hasil akad, nominal, catatan, urutan tenor, dan link `id_list_dajam` untuk retensi.
  - `pencairan_akad_pengajuan`: header pengajuan dengan tanggal pengajuan, tanggal rencana cair, catatan, lampiran surat, total, status `active|partial|paid|void`.
  - `pencairan_akad_pengajuan_detail`: item yang diajukan dan nominal pengajuan per item.
  - `pencairan_akad_payment` + `pencairan_akad_payment_detail`: pencairan partial manual per item, tanggal cair, nominal cair, catatan.
  - Tambahkan source ledger baru, misalnya `pencairan_akad_payment_detail`, agar satu payment detail menghasilkan satu income ledger row.

- Buat API/flow baru di Keuangan:
  - `POST keuangan/pencairan-akad/get`
  - `POST keuangan/pencairan-akad/plan/save`
  - `POST keuangan/pencairan-akad/pengajuan/store`
  - `POST keuangan/pencairan-akad/pencairan/store`
  - `POST keuangan/pencairan-akad/void`
  - Semua file lampiran lewat `FileAccessService`, bukan direct `uploads/...`.

- Rework UI di `app/Views/siteplan/keuangan.php`:
  - Satu modal cashout-style untuk Pencairan Akad.
  - Tab/ringkasan: Retensi, Tenor Hasil Akad, Pengajuan, Pencairan, History.
  - Jika ACC KPR 0, tampilkan popup “nominal ACC KPR 0, hubungi MKDT” dan blok pembuatan plan.
  - Retensi dipilih dari `list_dajam`, dengan nominal dan catatan per item.
  - Hasil akad otomatis: `harga_kpr_acc - total_retensi`.
  - Tenor hasil akad dibuat manual; total tenor wajib sama dengan hasil akad.
  - Pengajuan bisa memilih lebih dari satu item, campur retensi dan hasil akad.
  - Pencairan partial diisi manual per detail item; status pengajuan tetap aktif/partial sampai total cair = total pengajuan.

- Backfill dan transisi:
  - Backfill `dana_akad`, `riwayat_pencairan_jaminan`, dan detailnya ke tabel baru sebagai item/pengajuan/payment retensi.
  - Backfill `bank_kpr_disbursement` sebagai legacy pencairan hasil akad; row `void` tetap void.
  - Simpan `legacy_source_type` dan `legacy_source_id` agar audit/rollback jelas.
  - Disable atau redirect menu lama `keuangan_dana_jaminan` dan `keuangan_pencairan_bank` ke modul baru supaya user tidak input di dua alur.

**Reporting**

- Dashboard/piutang membaca tabel pengajuan baru, bukan `finance_ledger`.
- Piutang aktif = total pengajuan - total cair untuk status `active|partial`.
- Reminder Keuangan muncul jika `tanggal_rencana_cair < hari ini` dan masih ada sisa piutang.
- `finance_ledger` tetap cash basis: hanya payment detail yang sudah cair masuk sebagai `income`.

**Test Plan**

- Schema/backfill:
  - `php spark migrate:status`
  - `php spark db:table pencairan_akad_plan`
  - `php spark db:table pencairan_akad_pengajuan`
  - Jalankan backfill dua kali dan pastikan idempotent, tidak menggandakan data.
  - Query jumlah legacy yang termigrasi dari `dana_akad`, `riwayat_pencairan_jaminan`, dan `bank_kpr_disbursement`.

- Business rules:
  - `isi_data_konsumen()` dengan status Akad ditolak.
  - Status modal: checkbox Akad dan `status_mkdt` sinkron dua arah.
  - KPR dengan ACC KPR 0 ditolak saat masuk Akad.
  - Existing Akad ACC 0 tetap ada tapi tidak bisa dibuatkan plan.
  - Retensi tidak boleh melebihi ACC KPR.
  - Total tenor wajib sama dengan `ACC KPR - retensi`.
  - Pengajuan tidak boleh melebihi sisa item.
  - Partial cair manual memperbarui status `active -> partial -> paid`.
  - Ledger hanya bertambah saat pencairan, bukan saat pengajuan.

- Verification:
  - `php -l` untuk controller/service/model/migration yang disentuh.
  - `node --check` untuk JS inline yang diekstrak dari `keuangan.php` dan `mkdt.php`.
  - `git diff --check`, dengan CRLF warning diperlakukan sebagai informasi bila tidak ada whitespace error nyata.
  - Manual browser check bila login lokal tersedia: Akad guard, buka modul, simpan plan, pengajuan multi-item, partial cair, dashboard reminder.

**Assumptions**

- Piutang memakai tabel operasional baru.
- Data lama dibackfill ke modul baru.
- Retensi wajib memakai master `list_dajam`.
- Partial cair dialokasikan manual per item.
- Menu lama diganti oleh satu menu gabungan.
- Lampiran surat pengajuan dibuat wajib untuk submit pengajuan final.

