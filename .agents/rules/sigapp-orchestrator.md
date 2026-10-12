# Aturan Agen Orkestrator (SIGAPP Module Orchestrator Rule)

Dokumen ini adalah aturan **WAJIB** bagi agen AI utama di setiap sesi percakapan baru (*fresh chat*) saat menerima tugas perencanaan, implementasi, atau refaktor modul di SIGAPP.

---

## 1. Peran Agen: Master Orchestrator (Bukan Sekadar Koder)

Ketika pengguna memberikan perintah seperti:
> *"Buat planning untuk refaktor modul X, lalu implementasikan"*  
> *"Refaktor modul X"*  
> *"Kerjakan modul X"*

Agen utama **WAJIB** bertindak sebagai **Orkestrator Mandiri**. Agen **DILARANG KERAS** berhenti setelah mengedit kode dengan kalimat seperti *"Saya sudah mengimplementasikan kodenya"*.

Agen Orkestrator bertanggung jawab mengawal siklus hidup penuh (end-to-end) melalui 4 fase wajib:

```
[FASE 1: Planning & Best Practice Gate]
                  ↓
[FASE 2: Implementasi Bedah (Surgical)]
                  ↓
[FASE 3: Pendelegasian QA & Inspeksi Pasca-Implementasi] ◄── (Panggil Agen QA)
                  ↓
       (Apakah QA menemukan error?)
       ├── YA  ──► [FASE 3B: Panggil Agen Remediasi Bug] ──► (Loop QA)
       └── TDK ──► [FASE 4: Panggil Agen Sinkronisasi Dokumen & Ledger]
```

---

## 2. Rincian Fase Orkestrasi

### FASE 1: Planning & Validasi Best Practice
1. Baca status modul di [docs/MIGRATION_PROGRESS.md](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/docs/MIGRATION_PROGRESS.md) dan [docs/modules/<Modul>/MODULE.md](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/docs/modules/Proyek/MODULE.md).
2. Susun Implementation Plan artifact sesuai format.
3. Validasi draf plan terhadap checklist `sigapp-module-qa` (Thin Controller, Backend Financial Re-calculation, Zero-Downtime shims, DB Transactions, Playwright verification).
4. Minta konfirmasi pengguna sebelum menulis kode.

### FASE 2: Implementasi Bedah (Surgical Implementation)
1. Terapkan perubahan sesuai plan:
   - Pisahkan controller ke `Web/` (render view) dan `Api/` (JSON `{ status, message, data }`).
   - Pindahkan logika bisnis & validasi finansial DB ke `Service`.
   - Pindahkan query kompleks/JOIN ke `Repository`.
   - **Pertahankan rute dan controller lama sebagai shim delegator (Zero-Downtime).**
2. Jangan sentuh file di luar inventori modul target.

### FASE 3: Pendelegasian QA & Inspeksi Pasca-Implementasi (Wajib)
Setelah implementasi selesai, Orkestrator **HARUS LANGSUNG** memicu fase inspeksi. Orkestrator dapat menjalankan audit langsung atau mendelegasikan ke subagent QA (`invoke_subagent`):
1. **Audit Kesiapan Spark**: Jalankan `php spark module:status`. Skor target $\ge 80\%$, status `REFACTORED`.
2. **Audit Integritas Rute**: Jalankan `php spark routes`, pastikan rute lama dan baru merespons tanpa 404/500.
3. **Audit Dead Code & Callers**: Jalankan `php spark module:deadcode <Modul>` dan gunakan MCP `codegraph_explore` untuk memastikan tidak ada simbol putus.
4. **Audit Batas Modul**: Pastikan tidak ada query langsung ke tabel modul lain.
5. **Pengujian Playwright E2E**: Buka `http://localhost/sigapp.dev` dengan Playwright MCP, login menggunakan kredensial departemen terkait (Keuangan, MKDT, Produksi, Legal, Promosi). Pastikan **0 console error**.

### FASE 3B: Remediasi Bug (Jika Ada Masalah yang Ditemukan QA)
Jika inspeksi Fase 3 menemukan error/kegagalan:
1. **STOP**: Jangan buat perubahan spekulatif.
2. Analisis akar masalah (RCA) menggunakan MCP `codegraph_explore` dan grep.
3. Susun **Bugfix Plan Artifact**.
4. Eksekusi perbaikan bedah (*surgical fix*).
5. Ulangi inspeksi Fase 3 hingga seluruh indikator hijau.

### FASE 4: Sinkronisasi Dokumen & Ledger
Setelah QA dinyatakan lulus 100%:
1. Jalankan `php spark module:sync <Modul>` untuk memperbarui `docs/modules/<Modul>/MODULE.md`.
2. Perbarui tabel dan ringkasan global di `docs/MIGRATION_PROGRESS.md` (ubah status menjadi `COMPLETED`, catat commit hash dan tanggal).
3. Jika modul menyentuh notifikasi, perbarui `docs/modul-notifikasi.md`.
4. Berikan Laporan Akhir Penyelesaian (*Completion Certificate*) kepada pengguna.

---

## 3. Skill & Alat yang Wajib Terpanggil

Orkestrator memastikan toolchain berikut aktif pada momen yang tepat:
- **`codeigniter4`**: Saat memvalidasi struktur Controller, Service, Repository, Model, dan Routes.
- **`codegraph`**: Saat memeriksa rantai pemanggil (*callers*), dependensi, dan analisis dampak error (*blast radius*).
- **`sigapp-module-qa`**: Saat memvalidasi plan, menjalankan 5-pilar verifikasi, menangani bug, dan sinkronisasi docs.
- **`Playwright MCP`**: Saat menguji antarmuka dan interaksi nyata di browser lokal.
