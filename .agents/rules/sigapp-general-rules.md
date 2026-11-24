# Aturan Pengembangan SIGAPP

Kamu WAJIB mengikuti aturan-aturan berikut dalam setiap interaksi dan modifikasi kode di repository ini:

## 1. Prioritas Utama
Dalam setiap penyelesaian masalah atau penambahan fitur, selalu utamakan hal-hal berikut (sesuai urutan):
1. **Existing architecture**: Ikuti dan hormati struktur arsitektur sistem yang sudah ada (`Thin Web/Api Controller -> Service -> Repository`).
2. **Existing library**: Gunakan library yang sudah terinstall di dalam project sebelum mengusulkan penambahan package baru.
3. **Existing helper/component**: Manfaatkan fungsi helper (`cleanNumeric()`), utility JS, atau komponen UI (modal, datatable) yang sudah tersedia.
4. **Existing query/service/repository**: Gunakan kembali (*reuse*) query, method service, atau fungsi repository yang sudah ada.
5. **Minimal perubahan**: Lakukan perubahan seminimal mungkin secara presisi (*surgical changes*).
6. **Maintainability & Performance**: Tulis kode yang rapi, mudah di-maintain, dan efisien dalam eksekusi query database.

## 2. Penggunaan Skills Wajib Berdasarkan Peran Agen
- **`sigapp-orchestrator`**: Wajib dibaca oleh agen utama (`Master Orchestrator`) untuk mengoordinasikan 5 subagent spesialis (`sigapp-explorer`, `sigapp-engineer`, `sigapp-frontend`, `sigapp-qa`, `sigapp-docs`).
- **`codeigniter4`**: Wajib digunakan oleh **`sigapp-engineer`** setiap kali membuat/memodifikasi controller, service, repository, model, atau endpoint CI4.
- **`sigapp-ui-acuan`**: Wajib digunakan oleh **`sigapp-frontend`** saat membuat/memodifikasi halaman, view CI4, file CSS (`public/assets/css/<modul>.css`), JS (`public/assets/js/<modul>.js`), modal Bootstrap, tab, divider, DataTables, laporan matriks, atau menampilkan file dari `FileAccessService`.
- **`codegraph`**: Wajib digunakan oleh **`sigapp-explorer`** dan **`sigapp-qa`** untuk memetakan *callers*, *blast radius*, dan dependensi simbol.
- **`sigapp-module-qa` & `sigapp-local-testing`**: Wajib digunakan oleh **`sigapp-qa`** saat melakukan verifikasi pasca-implementasi dan pengujian browser Playwright.

## 3. Aturan Pengujian (Testing) & Kredensial Lokal
- Setiap perubahan kode WAJIB diuji secara independen oleh `sigapp-qa` menggunakan Playwright MCP setelah membaca `.agents/skills/sigapp-local-testing/SKILL.md`.
- **Target URL**: `http://localhost/sigapp.dev`
- **Kredensial Departemen**:
  - **Keuangan**: `nisalis` / `nisalis123`
  - **MKDT**: `citra` / `citra123`
  - **Planning**: `purnama` / `purnama123`
  - **Legal**: `marlina` / `marlina123`
  - **Promosi**: `benny` / `benny123`
  - **Produksi**: `alika` / `alikas123`

## 4. Aturan Notifikasi
Jika perubahan menyentuh notifikasi, `sigapp-engineer` WAJIB mengikuti skill `sigapp-notifikasi`, dan `sigapp-docs` WAJIB memperbarui `docs/modul-notifikasi.md` pada Fase 4.
