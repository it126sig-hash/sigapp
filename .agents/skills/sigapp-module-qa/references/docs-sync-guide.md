# Panduan Sinkronisasi Dokumentasi & Ledger (Docs Sync Guide)

Dokumentasi dalam SIGAPP bukan sekadar catatan pasif, melainkan **kontrak batas wilayah yang dibaca oleh sistem dan agen AI lain**.

Panduan ini mengatur cara memperbarui dokumentasi setelah suatu modul selesai diverifikasi dan dinyatakan bebas dari bug.

---

## 1. Sinkronisasi Dokumen Spesifik Modul (`MODULE.md`)

Setiap modul memiliki dokumen spesifik di `docs/modules/<NamaModul>/MODULE.md`.

### Perintah Sinkronisasi:
```bash
php spark module:sync <NamaModul>
```

**Perilaku Perintah:**
- Memindai file controller, service, repository, model, view, dan aset JS aktual.
- Memetakan ulang rute eksplisit maupun AutoRoutes.
- Menghitung ulang skor kesiapan (*Readiness Score*).
- Memperbarui daftar *Provides (Contracts)* dan *Owner Tables*.

### Opsi `--check-only`:
Gunakan opsi ini jika hanya ingin memastikan apakah dokumentasi sudah sinkron atau belum tanpa menimpa file:
```bash
php spark module:sync <NamaModul> --check-only
```

---

## 2. Pembaruan Global Migration Ledger (`docs/MIGRATION_PROGRESS.md`)

File [docs/MIGRATION_PROGRESS.md](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/docs/MIGRATION_PROGRESS.md) adalah sumber kebenaran tunggal (*Single Source of Truth*) untuk melacak progres migrasi modular lintas percakapan (*fresh chat*).

Lakukan dua pembaruan pada file ini:

### A. Perbarui Tabel Papan Status Modul
Cari baris modul yang bersangkutan pada tabel Bagian 2, lalu perbarui kolom:
- **Status**: Ubah menjadi `COMPLETED`.
- **Sesi / Commit Hash**: Isi dengan commit hash terbaru (misal: `c717d6c`) atau penanda sesi.
- **Terakhir Diverifikasi**: Isi dengan tanggal hari verifikasi dilakukan (`YYYY-MM-DD`).

*Contoh:*
```markdown
| 9 | **Proyek** | Core Master Data | Sedang | `COMPLETED` | `c717d6c` (100% Score) | 2026-10-12 |
```

### B. Perbarui Rangkuman Progres Global
Sesuaikan angka di Bagian 1:
```markdown
* **Total Modul:** 16
* **Status:** `X Selesai` | `0 Sedang Dikerjakan` | `Y Antre (Pending)`
```

---

## 3. Pembaruan Dokumen Notifikasi (Jika Ada Perubahan Notifikasi)

Sesuai aturan `sigapp-general-rules.md` dan skill `sigapp-notifikasi`:
- Jika modul menambahkan `tambah_notif()` baru, mengubah outbox, email digest, atau web push:
- Agen **WAJIB** membuka dan memperbarui file [docs/modul-notifikasi.md](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/docs/modul-notifikasi.md) dengan mencatat:
  1. Trigger event baru
  2. Penerima / role target
  3. Pesan template & level prioritas

---

## 4. Pemeriksaan Git & Komitmen Terisolasi

1. Periksa file yang termodifikasi:
   ```bash
   git status
   ```
2. Pastikan file yang berubah **hanya** milik inventori modul target dan dokumen terkait (`MODULE.md`, `MIGRATION_PROGRESS.md`).
3. Dilarang mencampuradukkan perubahan kode modul lain dalam satu commit yang sama.
