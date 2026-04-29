---
name: ci4-refactor-audit
description: >
  Audit satu modul CodeIgniter 4 dan hasilkan laporan refactor TANPA mengubah kode.
  Gunakan skill ini SEBELUM melakukan refactor apapun di project CI4 yang sudah berjalan.
  Triggers: "audit modul X", "cek controller X", "analisa kode CI4", "apa yang perlu direfactor",
  "review controller", "periksa modul", atau kapanpun user ingin tahu kondisi kode CI4 sebelum mengubahnya.
  Selalu jalankan audit dulu sebelum ci4-refactor-execute. Jangan ubah kode apapun saat audit.
---

# CI4 Refactor Audit

Tugasmu adalah **membaca dan menganalisa** kode, lalu menghasilkan laporan audit.
**Jangan ubah satu baris kode pun** selama proses ini.

---

## Langkah Audit

### 1. Identifikasi File dalam Modul

Kumpulkan semua file terkait modul:
- `app/Controllers/**/*[Modul]*.php`
- `app/Models/**/*[Modul]*.php`
- `app/Services/**/*[Modul]*.php` (jika ada)
- Entry di `app/Config/Routes.php`
- File JS di `assets/js/[modul].js` (jika ada)

### 2. Analisa Setiap File

Periksa setiap controller untuk 6 kategori berikut:

---

#### A. 🔴 Security — Validasi Finansial Backend

Ini prioritas tertinggi. Cari semua method yang menyimpan data finansial:

- Apakah backend **menghitung ulang** nominal/total dari DB sebelum simpan?
- Apakah ada `$model->getTotalTagihan()` atau equivalent?
- Apakah ada pengecekan `nominal <= sisa_tagihan`?
- Apakah `is_lunas` / status lunas ditentukan backend atau percaya input user?
- Apakah nilai `turun_kpr`, `diskon`, `total_cicilan` divalidasi dari DB?

**Contoh temuan:**
```
simpanPembayaran() — tidak ada pengecekan nominal <= sisa tagihan
→ User bisa kirim nominal 0 atau nominal > total tagihan
→ Risiko: data keuangan corrupt, pembayaran fiktif
```

---

#### B. 🔴 Transaction — Multi-DB Operations

- Method mana yang melakukan 2+ INSERT/UPDATE/DELETE?
- Apakah sudah ada `$db->transStart()` / `$db->transComplete()`?
- Jika tidak ada: data apa yang bisa corrupt jika gagal di tengah?

---

#### C. 🔴 Controller Separation — Web vs API

- Apakah controller ini melayani browser (render view) sekaligus API (return JSON)?
- Apakah namespace sudah `App\Controllers\Web` atau `App\Controllers\Api`?
- Apakah ada `echo json_encode()` atau `return view()` di controller yang sama?

**Target struktur:**
```
app/Controllers/Web/  → render view, redirect
app/Controllers/Api/  → return JSON, extend BaseApiController
```

---

#### D. 🟡 Fat Controller — Logika Bisnis

- Apakah ada `if` / kalkulasi bisnis di controller?
- Apakah ada query database langsung di controller?
- Berapa baris rata-rata per method? (>30 baris = perlu dipecah)

---

#### E. 🟡 Response Tidak Konsisten

- Format apa yang dipakai? (`respond()`, `echo json_encode()`, `return view()`, dsb.)
- Apakah struktur JSON konsisten `{status, message, data}`?
- Apakah HTTP status code diset dengan benar?

---

#### F. 🟢 Dead Code & Kebersihan

- Apakah ada variabel/fungsi yang tidak dipakai? (misal: `text_um`, `text_bb`)
- Apakah ada kode yang dikomentari panjang yang sudah tidak relevan?
- Apakah ada kondisional load JS/View dengan `$k == X` yang bisa disederhanakan?

---

### 3. Cek File JS (Jika Ada)

Untuk setiap file JS yang terkait modul:

- Apakah ada **kalkulasi finansial** yang seharusnya ada di backend?
  - `total = harga * qty` yang di-POST langsung → 🔴
  - `sisa = total - sudah_bayar` untuk display saja → 🟢 aman
- Apakah ada **dead code** yang tidak dipakai? (variabel global, fungsi commented out)
- Apakah ada fungsi yang perlu dipindah ke shared utility?

---

### 4. Format Laporan Audit

```
# Audit Modul: [Nama Modul]
Tanggal: [tanggal]
File yang diaudit: [daftar file]

## Ringkasan Risiko
🔴 KRITIS  : X isu
🟡 SEDANG  : X isu
🟢 RENDAH  : X isu

---

## Temuan

### 🔴 KRITIS — Validasi finansial tidak ada di backend
- File: app/Controllers/[...].php → method simpanPembayaran()
- Masalah: nominal diterima dari POST tanpa dicek ke DB
- Risiko: user bisa kirim nominal sembarang, data keuangan corrupt
- Fix: tambahkan getTotalTagihan() + cek nominal <= sisa di Service
- Estimasi: 30 menit

### 🔴 KRITIS — Tidak ada transaction
- File: app/Controllers/[...].php → method simpan()
- Operasi: INSERT mkdt + INSERT keuangan + UPDATE kavling_status
- Risiko: jika INSERT keuangan gagal, mkdt sudah tersimpan → data tidak sinkron
- Estimasi: 20 menit

### 🔴 KRITIS — Controller belum dipisah Web/API
- File: app/Controllers/SiteplanController.php
- Masalah: method index() render view, method getKavling() return JSON — di controller yang sama
- Fix: pisah ke Web/SiteplanController.php dan Api/SiteplanController.php
- Estimasi: 45 menit

### 🟡 SEDANG — Fat controller
- File: app/Controllers/[...].php → method store() (67 baris)
- Logika bisnis yang perlu dipindah ke Service: kalkulasi harga_net, penentuan status_mkdt
- Estimasi: 60 menit

### 🟡 SEDANG — Response tidak konsisten
- method index() return array langsung
- method show() return {success: true, data: ...}
- method store() return {messages: "OK"}
- Estimasi: 20 menit

### 🟢 RENDAH — Dead code JS
- File: assets/js/keuangan.js
- text_um dan text_bb di-build tapi tidak dipakai di backend
- Estimasi: 5 menit

---

## File Baru yang Perlu Dibuat
- [ ] app/Controllers/Web/[Modul]Controller.php
- [ ] app/Controllers/Api/[Modul]Controller.php
- [ ] app/Services/[Modul]Service.php
- [ ] app/Repositories/[Modul]Repository.php (jika ada query JOIN)

## File yang Dimodifikasi
- [ ] app/Controllers/[Modul]Controller.php → dipecah ke Web/ dan Api/
- [ ] app/Config/Routes.php → tambah group web dan api
- [ ] assets/js/[modul].js → hapus dead code, update URL ke /api/...

## Estimasi Waktu
- KRITIS saja    : ~[X] menit
- KRITIS + SEDANG: ~[X] menit
- Full refactor  : ~[X] menit

## Pertanyaan untuk Developer
- [Pertanyaan yang perlu dijawab sebelum execute, misal: "Apakah endpoint /transaksi/simpan dipanggil dari JS lain selain mkdt.js?"]
- [Misal: "Ada berapa departemen yang pakai controller ini via conditional $k?"]
```

### 5. Tunggu Persetujuan

Setelah laporan selesai, **berhenti dan tanyakan:**

> "Laporan audit modul **[X]** selesai. Mau lanjut ke mana:
> 1. Execute refactor modul ini (mulai dari yang KRITIS dulu)?
> 2. Audit modul lain dulu?
> 3. Ubah scope (misal: security fix dulu saja, arsitektur belakangan)?"

**Jangan lanjutkan tanpa konfirmasi eksplisit.**

---

## Tabel Prioritas Risiko

| Kondisi | Level | Alasan |
|---------|-------|--------|
| Angka finansial tidak divalidasi di backend | 🔴 KRITIS | Manipulasi data keuangan |
| Multi-DB tanpa transaction | 🔴 KRITIS | Data corrupt di production |
| Controller belum pisah Web/API | 🔴 KRITIS | Tidak scalable, susah maintain |
| Query langsung di controller | 🔴 KRITIS | Tidak reusable, tidak testable |
| Logika bisnis kompleks di controller | 🟡 SEDANG | Sulit maintain |
| Response tidak konsisten | 🟡 SEDANG | Breaking change untuk frontend |
| Dead code JS | 🟢 RENDAH | Kebersihan kode |
| Conditional load `$k == X` | 🟡 SEDANG | Tidak scalable |
