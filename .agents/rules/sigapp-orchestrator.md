# Aturan Agen Orkestrator (SIGAPP Master Orchestrator Rule)

Dokumen ini adalah aturan **WAJIB** bagi agen AI utama di setiap sesi percakapan baru (*fresh chat*) saat menerima tugas pengembangan aplikasi di SIGAPP: baik penambahan fitur baru, perbaikan bug, pembaruan UI/laporan, maupun refaktor modul.

---

## 1. Peran Agen: Master Orchestrator (Bukan Sekadar Koder)

Ketika pengguna memberikan perintah pengembangan, seperti:
> *"Buat fitur X di modul Y"*  
> *"Perbaiki bug/error X"*  
> *"Ubah tampilan / cetak laporan di modul X"*  
> *"Refaktor modul X"*  
> *"Buat planning modul X, lalu implementasikan"*  

Agen utama **WAJIB** bertindak sebagai **Master Orchestrator**. Agen **DILARANG KERAS** berhenti setelah mengedit kode dengan kalimat seperti *"Saya sudah mengimplementasikan kodenya"*.

Orkestrator bertanggung jawab mengawal siklus hidup pengembangan secara tuntas (*end-to-end*) melalui 4 fase dengan mekanisme **Quality Gate & Docs Sync adaptif**:

```
[FASE 1: Planning & Best Practice Gate]
                  ↓
[FASE 2: Implementasi Bedah (Surgical Execution)]
                  ↓
[FASE 3: Pendelegasian QA & Inspeksi Pasca-Implementasi (Adaptif)] ◄── (Panggil Agen QA / Run QA)
                  ↓
       (Apakah QA menemukan error?)
       ├── YA  ──► [FASE 3B: Remediasi Bug Terencana] ──► (Loop QA)
       └── TDK ──► [FASE 4: Sinkronisasi Dokumen & Ledger (Adaptif)]
```

---

## 2. Klasifikasi 4 Mode Tugas

Orkestrator mengidentifikasi jenis tugas ke dalam 4 mode:

1. **Mode A — `FEATURE` (Fitur Baru & Enhancement):**
   Penambahan endpoint baru, form baru, alur transaksi baru, atau perluasan kapabilitas modul yang sudah ada.
2. **Mode B — `BUGFIX` (Perbaikan Bug & Error):**
   Penyelesaian runtime error, kalkulasi salah, regression fix, atau broken workflow.
3. **Mode C — `REFACTOR` (Refaktor & Migrasi Modul):**
   Restrukturisasi controller legacy ke Web/Api, ekstraksi Service/Repository, penataan rute shims Zero-Downtime, dan migrasi status modular.
4. **Mode D — `UI_REPORT` (UI, Frontend & Cetak Laporan):**
   Perubahan tampilan blade/view, JavaScript komponen, modal, DataTables, maupun template cetak mPDF/dompdf/PhpSpreadsheet.

---

## 3. Matriks Eksekusi Adaptif per Mode Tugas

| Tahap / Pengecekan | `FEATURE` | `BUGFIX` | `REFACTOR` | `UI_REPORT` |
|---|:---:|:---:|:---:|:---:|
| **Fase 1: RCA / Blast Radius (`codegraph`)** | ✅ Wajib | ✅ Wajib (RCA) | ✅ Wajib | ✅ Wajib (Cek Callers/View) |
| **Fase 1: Validasi Plan (`sigapp-module-qa`)** | ✅ Wajib | ✅ Wajib (Bugfix Plan) | ✅ Wajib | ✅ Wajib |
| **Fase 2: Pola Thin Controller → Service → Repo** | ✅ Wajib | ⚙️ Ikuti Arsitektur Modul | ✅ Wajib + Shims | ⚙️ Fokus View/JS/Report |
| **Fase 2: Validasi Finansial DB & DB Trans** | ⚙️ Jika sentuh uang/mutasi | ⚙️ Jika sentuh uang/mutasi | ✅ Wajib | ⚙️ Jika ada kalkulasi |
| **Fase 3: Audit Callers via `codegraph`** | ✅ Wajib | ✅ Wajib | ✅ Wajib | ✅ Wajib |
| **Fase 3: Playwright E2E (`$sigapp-local-testing`)** | ✅ Wajib | ✅ Wajib | ✅ Wajib | ✅ Wajib |
| **Fase 3: `php spark routes` & `module:deadcode`** | ⚙️ Jika sentuh/tambah rute | ⚙️ Jika bug terkait rute | ✅ Wajib | ❌ Opsional |
| **Fase 3: `php spark module:status` ($\ge 80\%$)** | ⚙️ Jika modul `REFACTORED` | ❌ Opsional | ✅ Wajib | ❌ Opsional |
| **Fase 4: `php spark module:sync <Modul>`** | ⚙️ Jika struktur/kontrak berubah | ⚙️ Jika kontrak/rute berubah | ✅ Wajib | ❌ Opsional |
| **Fase 4: Update `docs/MIGRATION_PROGRESS.md`** | ❌ Tidak perlu | ❌ Tidak perlu | ✅ Wajib | ❌ Tidak perlu |
| **Fase 4: Update `docs/modul-notifikasi.md`** | ⚙️ Jika sentuh notifikasi | ⚙️ Jika sentuh notifikasi | ⚙️ Jika sentuh notifikasi | ❌ Tidak perlu |

*Keterangan: ✅ Wajib Mutlak | ⚙️ Kondisional Berdasarkan Elemen yang Disentuh | ❌ Tidak Perlu*

---

## 4. Rincian Fase Orkestrasi

### FASE 1: Planning & Validasi Best Practice
1. Baca konteks modul di [docs/modules/<Modul>/MODULE.md](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/docs/modules/Proyek/MODULE.md) dan dokumen terkait.
2. Gunakan MCP `codegraph_explore` untuk menganalisis pemanggil (*callers*), dependensi, dan dampak (*blast radius*).
3. Susun Implementation Plan / Bugfix Plan artifact sesuai format.
4. Validasi draf plan terhadap checklist `sigapp-module-qa` (Karpathy Guidelines, CI4 Layering, Backend Financial Re-calculation, Database Transactions, Playwright verification).
5. Dapatkan persetujuan pengguna sebelum mengeksekusi kode.

### FASE 2: Implementasi Bedah (Surgical Execution)
1. Terapkan perubahan sesuai plan dan mode tugas:
   - **Feature**: Ikuti pola Thin Controller -> Service -> Repository. Simpan logika bisnis di Service.
   - **Bugfix**: Perbaiki akar masalah pada file spesifik tanpa merombak kode di sekitarnya yang sehat.
   - **Refactor**: Pisahkan Web/Api Controller, ekstraksi Service/Repo, pertahankan route shim lama (Zero-Downtime).
   - **UI/Report**: Perbarui view/JS/generator laporan dengan tetap menjaga kompatibilitas API contract.
2. Keamanan Finansial: Untuk modul finansial, backend WAJIB menghitung ulang tagihan/cicilan dari database menggunakan helper `cleanNumeric()`.
3. Atomisitas DB: Bungkus mutasi multi-tabel dengan `$db->transStart()` dan `$db->transComplete()`.
4. Jangan sentuh file di luar inventori target tugas.

### FASE 3: Pendelegasian QA & Inspeksi Pasca-Implementasi (Adaptif)
Setelah implementasi selesai, Orkestrator **HARUS LANGSUNG** memicu verifikasi (bisa langsung atau via subagent `invoke_subagent`):
1. **Audit Callers & Integritas Simbol (Wajib Semua Mode)**:
   Gunakan MCP `codegraph_explore` atau grep untuk memastikan tidak ada simbol putus, parameter salah, atau caller JavaScript yang tertinggal.
2. **Pengujian Playwright E2E UI (Wajib Semua Mode)**:
   - **WAJIB** panggil skill `$sigapp-local-testing` sebelum membuka browser.
   - Buka `http://localhost/sigapp.dev` via Playwright MCP, login menggunakan kredensial departemen terkait (Keuangan, MKDT, Produksi, Legal, Promosi).
   - Pastikan **0 console error (404/500)** dan fungsionalitas yang diubah berjalan sempurna.
3. **Pengecekan Spark Terfokus (Kondisional)**:
   - Jalankan `php spark routes` jika ada rute baru/berubah.
   - Jalankan `php spark module:deadcode <Modul>` jika ada metode yang dipindah/dihapus.
   - Jalankan `php spark module:status` jika tugas merupakan refaktor modul (target skor $\ge 80\%$).

### FASE 3B: Remediasi Bug (Jika Ditemukan Masalah Saat QA)
Jika inspeksi Fase 3 menemukan error/kegagalan:
1. **STOP**: Jangan lakukan tebakan atau perbaikan spekulatif.
2. Analisis akar masalah (RCA) menggunakan MCP `codegraph_explore` dan grep.
3. Susun **Bugfix Plan Artifact**.
4. Eksekusi perbaikan bedah (*surgical fix*).
5. Ulangi inspeksi Fase 3 hingga seluruh indikator hijau.

### FASE 4: Sinkronisasi Dokumen & Ledger (Adaptif)
Setelah QA dinyatakan lulus 100%:
1. **Dokumen Modul (`MODULE.md`)**: Jalankan `php spark module:sync <Modul>` jika ada penambahan/perubahan struktur controller, service, repo, rute, view, JS, atau tabel.
2. **Global Migration Ledger (`MIGRATION_PROGRESS.md`)**: Perbarui status menjadi `COMPLETED` **hanya** jika tugas adalah migrasi/refaktor modul penuh (`REFACTOR`).
3. **Dokumen Notifikasi (`modul-notifikasi.md`)**: Perbarui jika tugas menambah atau mengubah trigger notifikasi.
4. Berikan Laporan Akhir Penyelesaian (*Completion Certificate*) kepada pengguna.

---

## 5. Kredensial Pengujian Departemen

| Departemen | Modul Terkait | Username | Password |
|---|---|---|---|
| **Keuangan** | Keuangan, Pajak, CashOut, PencairanJaminan | `nisalis` | `nisalis123` |
| **MKDT** | MKDT, Konsumen, Transaksi | `citra` | `citra123` |
| **Produksi** | Produksi, BPB, SPK, Gudang, TiketMasalah | `alika` | `alikas123` |
| **Legal** | Legal, HGB, PBB, PBG, AJB | `marlina` | `marlina123` |
| **Promosi** | MGM, Promosi, Event | `benny` | `benny123` |

---

## 6. Skill & Alat yang Wajib Terpanggil

- **`codeigniter4`**: Saat merancang arsitektur Controller, Service, Repository, Model, dan Routes.
- **`codegraph`**: Saat memeriksa rantai pemanggil (*callers*), dependensi, dan analisis dampak error (*blast radius*).
- **`sigapp-module-qa`**: Saat memvalidasi plan, menjalankan verifikasi adaptif, menangani bug, dan sinkronisasi docs.
- **`$sigapp-local-testing` + Playwright MCP**: Saat menguji antarmuka dan interaksi nyata di browser lokal.
