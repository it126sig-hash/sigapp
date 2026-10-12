# Playbook Penanganan Bug Terencana (Bug Remediation Playbook)

Playbook ini memandu agen AI saat menemukan error, regresi, atau kegagalan tes selama proses audit modul SIGAPP.

Ikuti aturan: **"Think Before Coding"** dan **"Surgical Changes"** (Karpathy Guidelines). Dilarang menyentuh kode tanpa perencanaan tertulis terlebih dahulu.

---

## Alur Penanganan Bug (5 Langkah)

```
[1. Tangkap Error & Reproduksi]
              ↓
[2. Root Cause Analysis via Codegraph + Grep]
              ↓
[3. Susun Bugfix Plan Artifact]
              ↓
[4. Eksekusi Perbaikan Bedah (Surgical Fix)]
              ↓
[5. Loop Verifikasi Hingga Hijau]
```

---

## Langkah 1: Tangkap Error & Reproduksi Konsisten

1. Catat detail kegagalan:
   - Pesan error persis dan status code (misal: HTTP 500 Internal Server Error, Call to undefined method, Syntax error, SQL error).
   - URL endpoint atau halaman UI yang bermasalah.
   - Peran (role) pengguna saat error terjadi.
2. Pastikan error dapat direproduksi secara konsisten (via curl, CLI, atau Playwright).

---

## Langkah 2: Root Cause Analysis (RCA) Menggunakan CodeGraph & Grep

Gunakan kapabilitas MCP `codegraph` dan pencarian teks untuk membedah akar masalah:

1. **Jelajahi Simbol Bermasalah:**
   Panggil MCP tool:
   ```json
   {
     "ServerName": "codegraph",
     "ToolName": "codegraph_explore",
     "Arguments": {
       "projectPath": "c:\\Users\\Salivs\\Data\\laragon\\www\\sigapp.dev",
       "query": "NamaClassAtauMethod"
     }
   }
   ```
2. **Telusuri Rantai Pemanggil (*Call Paths*):**
   Cari tahu siapa saja yang memanggil method/simbol tersebut. Apakah nama parameter berubah? Apakah tipe kembalian berubah (misal: Service me-return array tapi Controller mengharapkan objek)?
3. **Analisis Efek Samping (*Blast Radius*):**
   Pastikan perbaikan yang akan dilakukan tidak memicu regresi di modul lain yang mengandalkan simbol tersebut.

---

## Langkah 3: Susun Bugfix Plan Artifact

Tuliskan rencana perbaikan sebelum mengubah file kode. Rencana harus memuat struktur berikut:

```markdown
### 🐛 Bug Remediation Plan: [Deskripsi Singkat Bug]

1. **Gejala & Reproduksi:**
   - Error: [Pesan error lengkap]
   - File/Endpoint: [URL atau path file]
   - Langkah trigger: [Aksi yang memicu error]

2. **Akar Masalah (Root Cause):**
   - File: [path file:baris]
   - Penyebab: [Mengapa error ini terjadi? misal: parameter $id_kavling bertipe string kosong karena ...]

3. **Blast Radius (Analisis Codegraph):**
   - Pemanggil terkait: [daftar callers yang dicek via codegraph]
   - Modul terdampak: [hanya modul ini / ada modul lain]

4. **Rencana Perbaikan Bedah (Surgical Fix):**
   - File yang akan diubah: [hanya file yang esensial]
   - Snippet perubahan: [perubahan minimal tanpa merombak logika yang tidak rusak]

5. **Kriteria Keberhasilan & Verifikasi Ulang:**
   - [ ] [Tes spesifik yang membuktikan bug teratasi]
   - [ ] Tidak ada console error di Playwright
   - [ ] Seluruh rute kembali merespons 200 OK
```

---

## Langkah 4: Eksekusi Perbaikan Bedah (*Surgical Fix*)

Terapkan perbaikan kode sesuai rencana:
- **Hanya baris yang bermasalah**: Jangan merefaktor baris lain yang tidak rusak.
- **Ikuti gaya kode sekitar**: Pertahankan indentasi, konvensi penamaan, dan struktur yang ada.
- **Jangan tambahkan fitur baru**: Fokus murni menyelesaikan akar masalah.
- **Bersihkan sampah sendiri**: Jika perbaikan membuat variabel/import tertentu tidak terpakai, bersihkan variabel tersebut.

---

## Langkah 5: Loop Verifikasi Hingga Hijau

1. Jalankan langkah reproduksi di Langkah 1. Pastikan error sudah tidak muncul.
2. Jalankan kembali seluruh pengujian Playwright E2E.
3. Jalankan `php spark module:status` untuk memastikan skor modul tetap optimal.
4. Jika muncul bug baru, kembali ke Langkah 1 dan ulangi siklus. Jangan pernah menghentikan proses sebelum semua indikator hijau.
