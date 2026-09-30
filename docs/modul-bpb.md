# Modul Bon Permintaan Barang (BPB)

Dokumen ini adalah acuan teknis modul BPB SIGAPP. Modul menggunakan pola Thin Controller → Service → Repository, file privat, transaksi database, tanda tangan elektronik internal, audit trail, PDF, dan QR verifikasi.

Terakhir dicek: 2026-09-30

## Entry Point dan File Utama

| Area | File |
|---|---|
| Halaman | `GET /bpb` → `app/Controllers/Web/BpbController.php` |
| API | `app/Controllers/Api/BpbController.php` |
| Aturan bisnis | `app/Services/Bpb/BpbService.php` |
| State machine | `app/Services/Bpb/BpbWorkflow.php` |
| Query | `app/Repositories/BpbRepository.php` |
| File/kompresi | `app/Services/Bpb/BpbFileService.php` |
| Hash dokumen | `app/Services/Bpb/BpbDocumentHasher.php` |
| TTD profil | `ProfileSignatureService` dan `ProfileSignatureController` |
| UI | `app/Views/bpb/index.php`, `public/assets/js/bpb.js`, `public/assets/css/bpb.css` |
| PDF/verifikasi | `app/Views/bpb/pdf.php`, `app/Views/bpb/verify.php` |
| Migration | `2026-09-29-000002_CreateBpbModule.php` |

Controller hanya menerjemahkan request/response. Validasi status, otorisasi, password, hash, transaksi, penomoran, dan side effect berada di service. Query lintas tabel berada di repository.

## Data

| Tabel | Fungsi |
|---|---|
| `bpb_requests` | Header, snapshot pemohon, approver, status, hash, token verifikasi, data cair/beli |
| `bpb_items` | Item jamak dalam BPB |
| `bpb_files` | Metadata lampiran awal dan bukti pembelian |
| `bpb_signatures` | Salinan TTD per transaksi, identitas, hash, IP, user-agent, pencabutan |
| `bpb_history` | Timeline append-only perubahan BPB |
| `bpb_number_counters` | Counter nomor transaction-safe per tahun |
| `user_signature_profiles` | Lokasi privat TTD default user, satu baris per `user_id` |

Nomor dialokasikan ketika submit pertama dengan row lock counter tahunan. Format: `001/BPB/IX/2026`. Padding tiga digit adalah minimum sehingga urutan di atas 999 tidak dipotong.

## State Machine dan Hak Akses

```text
Draft
  → Menunggu CC (jika dipilih) → Menunggu Mengetahui → Disetujui
  → Diproses → Sudah Cair → Pending → Sudah Dibeli
                         └────────────→ Sudah Dibeli
```

- `Ditolak` dan `Dibatalkan` terminal. Tidak ada transisi mundur.
- Draft hanya dapat dilihat/diedit pembuat. Nomor yang sudah terbit tidak dipakai ulang.
- Sebelum CC/Mengetahui pertama menandatangani, pembuat boleh merevisi. Revisi mencabut TTD pemohon aktif dan mewajibkan password serta TTD baru.
- CC dan Mengetahui hanya dapat sign/reject saat tahapnya aktif.
- Pembuat dapat membatalkan sebelum tanda tangan approver pertama.
- Status operasional hanya dapat diubah pembuat atau user group `2` (Divisi Umum). Admin tidak mendapat hak mutasi otomatis.
- `Diproses` mewajibkan password dan TTD Verifikasi Pengeluaran.
- `Sudah Cair` mewajibkan nominal aktual, penerima, dan nomor rekening.
- `Pending` mewajibkan alasan. `Sudah Dibeli` mewajibkan tanggal dan 1–5 bukti.

## Tanda Tangan dan Integritas

V1 adalah TTE internal tidak tersertifikasi, bukan pengganti TTE PSrE tersertifikasi. Setiap signature event:

- memverifikasi password dengan `Myth\Auth\Password::verify()`;
- menyimpan snapshot nama/departemen/level, role, metode, waktu, IP, user-agent;
- menyalin PNG TTD ke lokasi transaksi sehingga perubahan TTD profil tidak mengubah dokumen lama;
- mengikat TTD ke hash SHA-256 dari representasi kanonis nomor, pemohon, approver, item, dan metadata lampiran;
- mempertahankan signature lama sebagai revoked ketika pemohon merevisi.

QR bukan tanda tangan. QR berisi URL token acak 256-bit untuk memeriksa nomor, status, hash, dan daftar penanda tangan tanpa mengekspos item, nominal, rekening, file, atau gambar TTD. Pemeriksaan turut memastikan hash file bukti pembelian cocok dengan file privat tersimpan; hash tanda tangan pengajuan tetap hanya mengikat isi serta lampiran yang ada pada saat pengajuan.

## File Privat

Lampiran awal dan bukti pembelian menerima JPG/JPEG/PNG/WEBP/PDF, maksimal 5 file per kategori dan 5 MB per file. Gambar disimpan privat lalu diproses CI4 Image Service dengan sisi maksimum 1920 px dan kualitas 78; PDF tidak dikompres. Akses file login memakai `FileAccessService` source `bpb_file`. TTD transaksi memakai endpoint terautorisasi `/bpb/signature/{id}`; TTD profil tidak memiliki URL publik.

Form lampiran awal juga menerima drag-and-drop dan gambar yang ditempel dari clipboard saat form terlihat. File terpilih tampil sebagai thumbnail (gambar) atau tile dokumen (PDF), dapat dihapus sebelum upload, dan mengikuti batas maksimal lima file termasuk file lama yang dipertahankan. Request multipart Draft/Ajukan/Revisi memakai XHR untuk menampilkan persentase unggah aktual; setelah transfer selesai indikator beralih ke status penyimpanan server.

## API

| Method | Endpoint | Fungsi |
|---|---|---|
| POST | `/api/bpb/list` | DataTables server-side, cakupan/status, tanggal status, divisi, pembuat, dan pencarian |
| GET | `/api/bpb/options` | Kandidat user aktif, label status, serta opsi divisi/pembuat yang pernah mengajukan BPB non-draft |
| GET | `/api/bpb/detail/{id}` | Detail, timeline, dan action berdasarkan otorisasi |
| POST | `/api/bpb/draft` | Buat/perbarui draft |
| POST | `/api/bpb/submit` | Submit pertama dan TTD pemohon |
| POST | `/api/bpb/{id}/update` | Revisi pra-approval dan TTD ulang |
| POST | `/api/bpb/{id}/sign` | Sign CC/Mengetahui |
| POST | `/api/bpb/{id}/reject` | Tolak tahap aktif |
| POST | `/api/bpb/{id}/cancel` | Batalkan oleh pembuat |
| POST | `/api/bpb/{id}/status` | Transisi operasional |
| GET/PUT/DELETE | `/api/profile/signature` | TTD profil; PUT/DELETE wajib password |
| GET | `/bpb/{id}/pdf` | PDF A5 landscape untuk user login |
| GET | `/bpb/verify/{token}` | Verifikasi publik yang di-throttle |

Payload `items` berupa JSON array `{nama_barang, jumlah, satuan, keterangan}`. `jumlah` tetap numerik desimal; `satuan` adalah teks bebas maksimal 50 karakter dan wajib untuk submit. Submit baru tanpa ID draft menyertakan `submission_key` 64 karakter heksadesimal; key digunakan untuk replay protection dan tidak dikembalikan pada respons detail.

Endpoint mutasi memakai CSRF. Endpoint password/signature juga memakai throttle. Password tidak dimasukkan ke metadata history atau log.

Respons list juga membawa `applicant_department`, `display_date`, dan `status_changed_at`: `display_date` memakai `submitted_at` untuk BPB diajukan atau `created_at` untuk draft, sedangkan `status_changed_at` berasal dari history terakhir untuk status aktif. Filter `history_status` dengan `date_from`/`date_to` mencari waktu BPB masuk ke status pilihan; `department` dan `applicant_user_id` membatasi divisi serta pembuat berdasarkan snapshot pengajuan.

## UI, PDF, dan Notifikasi

- Daftar desktop menampilkan enam kolom: Nomor, Nama Item, Pembuat BPB, Divisi, Tanggal Pengajuan, dan Status Terakhir. Item pertama diberi label `+N item`; tanggal perubahan status tampil di bawah status.
- Filter berada di side modal yang dibuka dari tombol Filter. Filter aktif muncul sebagai chip pada header card, tiap chip bisa dihapus atau seluruh filter dibersihkan; tombol Refresh memuat ulang DataTable. Mobile memakai card dengan chevron pada baris atas tanpa badge status; card juga menampilkan Divisi dan status berwarna bersama tanggal perubahannya. Pagination, pencarian, dan filter memakai DataTable server-side yang sama.
- Canvas memakai Pointer Events, mencegah scroll saat menggambar, dan untuk modal aksi baru di-resize setelah `shown.bs.modal` agar area gambar tidak pernah dibuat saat lebar modal nol.
- Detail dan aksi memiliki Tutup. X, Tutup, backdrop, serta Escape meminta konfirmasi melalui `initModalListener()`; penutupan setelah aksi sukses melewati konfirmasi.
- Modal detail menggunakan body scrollable dan footer aksi tetap terlihat. Kontennya menampilkan ringkasan BPB, item/lampiran, rantai tanda tangan, data pencairan/pembelian, dan timeline audit.
- Lampiran gambar pengajuan dan bukti pembelian dirender sebagai thumbnail. Thumbnail memakai URL privat dari `FileAccessService`, klik membuka lightbox yang dapat digeser dalam kelompok tahapnya; PDF tetap berupa tautan file.
- Warna dot, aksen, dan badge entri timeline mengikuti `to_status` dan palet badge status BPB.
- SweetAlert BPB berada di atas modal bertumpuk, termasuk ketika modal aksi terbuka di atas detail.
- Penyimpanan TTD profil memakai insert pada penggunaan pertama dan update pada penggantian; file lama dibersihkan hanya setelah transaksi database berhasil.

## Konsistensi Submit

Submit baru tanpa draft berjalan dalam satu transaksi: header sementara, item, file, nomor, hash, signature, dan history. Jika password, TTD, validasi, atau file gagal, seluruh data baru di-rollback beserta file yang baru dibuat. Form menghasilkan `submission_key` acak 256-bit untuk satu form baru; key tetap sama pada retry selama form itu masih terbuka, lalu berubah saat pengguna memulai BPB baru. Unique index di `bpb_requests` dan pemeriksaan service membuat retry/request paralel dengan key yang sama mengembalikan BPB yang sudah tersimpan tanpa mengulang nomor, history, atau notifikasi. Key tidak dikirim kembali pada respons detail. Tombol Simpan Draft, Ajukan, dan Buat BPB dinonaktifkan/ditutup selama form request aktif; draft yang sengaja disimpan tetap diajukan menggunakan ID yang sama.

`bpb_items.satuan` nullable untuk kompatibilitas record lama. Item lama tanpa satuan harus dilengkapi sebelum submit/revisi. Satuan nonkosong disertakan dalam hash dokumen; satuan kosong dihilangkan dari representasi kanonis agar hash signature BPB lama tetap valid. Detail dan PDF menampilkan jumlah beserta satuannya.

Form lampiran awal menerima pemilih file, drag-and-drop, dan gambar yang ditempel dari clipboard ketika form terlihat. File terpilih tampil sebagai thumbnail (gambar) atau tile dokumen (PDF), dapat dihapus sebelum upload, dan mengikuti batas maksimal lima file total termasuk file tersimpan yang dipertahankan, 5 MB per file. Request multipart Draft/Ajukan/Revisi memakai XHR untuk menampilkan persentase unggah; setelah transfer selesai indikator beralih ke status penyimpanan server.
- Detail memisahkan ringkasan, item/lampiran, rantai TTD, pencairan/pembelian, aksi, dan timeline.
  - Formulir BPB memakai halaman A5 landscape. Nomor, nama, dan departemen pemohon rata kiri di header; QR verifikasi berada di kanan atas dalam bidang putih dengan quiet zone. Lampiran gambar dicetak satu per halaman dengan rasio sumber dipertahankan dan ukuran maksimum setara A4; setiap halaman lampiran PDF diimpor dengan ukuran dan orientasi sumber. Urutan lampiran adalah pengajuan lalu bukti pembelian.
  - Area tanda tangan tetap memiliki tinggi dan baseline nama yang konsisten walau belum ditandatangani. Gambar TTD transparan sedikit menimpa area nama; tanggal pengajuan ada di atas label Pemohon dan paraf CC tampil kecil di samping label Mengetahui.
  - PDF belum disetujui memakai watermark teks mPDF dengan transparansi 20%. Hasil cetak tidak menyertakan timeline riwayat; status dan hash tetap dicetak.
  - QR verifikasi ditempatkan di kanan atas dengan quiet zone putih dan dicetak pada ukuran fisik yang diuji untuk keterbacaan. URL QR tetap memakai `/bpb/verify/{token}`.
- Event personal BPB: `bpb_signature_requested` (mandatory), `bpb_signature_completed`, `bpb_rejected`, `bpb_approved`, dan `bpb_status_changed`. Deep-link memakai `/bpb?open={id}`.

## Verifikasi

Perintah minimum:

```bash
php spark migrate
vendor/bin/phpunit --no-coverage tests/unit/BpbWorkflowTest.php tests/unit/BpbDocumentHasherTest.php tests/unit/BpbNumberFormatterTest.php
cd tests/js && npm test -- --runTestsByPath bpb-interface.test.js
php -l app/Services/Bpb/BpbService.php
git diff --check
```

Untuk browser/E2E lokal, wajib ikuti skill `sigapp-local-testing`. QR dinyatakan lolos hanya setelah PNG dari PDF benar-benar di-decode ke URL `/bpb/verify/{token}` yang sama.

## Batasan V1

- Belum terintegrasi PSrE tersertifikasi. Tinjauan legal tetap diperlukan untuk penggunaan eksternal/sengketa.
- Hash melindungi isi pengajuan yang ditandatangani; audit status operasional dicatat terpisah di timeline.
- Keuangan tidak memiliki hak khusus selain ketika menjadi pembuat. Divisi Umum adalah group `2`.
