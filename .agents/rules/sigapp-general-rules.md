---
name: sigapp-general-rules
description: >
  Aturan umum dan prinsip pengembangan yang WAJIB diikuti saat melakukan modifikasi kode, penambahan fitur, atau refactor di project SIGAPP.
  Triggers: "selalu aktif", "aturan umum", "saat ngoding", "development rules".
---

# Aturan Pengembangan SIGAPP

Kamu WAJIB mengikuti aturan-aturan berikut dalam setiap interaksi dan modifikasi kode di repository ini:

## 1. Prioritas Utama
Dalam setiap penyelesaian masalah atau penambahan fitur, selalu utamakan hal-hal berikut (sesuai urutan):
1. **Existing architecture**: Ikuti dan hormati struktur arsitektur sistem yang sudah ada (misal pola Controller -> Service -> Repository).
2. **Existing library**: Gunakan library yang sudah terinstall di dalam project sebelum mengusulkan penambahan package baru.
3. **Existing helper/component**: Manfaatkan fungsi helper, utility JS, atau komponen UI (modal, datatable) yang sudah tersedia.
4. **Existing query/service/repository**: Gunakan kembali (reuse) query, method service, atau fungsi repository yang sudah ada daripada membuat fungsi baru dengan tujuan yang sama.
5. **Minimal perubahan**: Lakukan perubahan seminimal mungkin secara presisi untuk menyelesaikan masalah (surgical changes).
6. **Maintainability**: Tulis kode yang mudah dibaca, rapi, dan mudah di-maintain.
7. **Performance**: Pastikan efisiensi dari algoritma maupun eksekusi query database.

## 2. Penggunaan Skills Wajib
- **CodeIgniter 4**: Selalu gunakan skill `codeigniter4` setiap kali bekerja dengan environment dan kode backend project ini.
- **Code Graph**: Selalu cek dan manfaatkan skill `codegraph` untuk menganalisis kode atau mencari referensi fungsi, callers, dan dependencies dari fitur terkait di project codebase.

## 3. Aturan Pengujian (Testing)
- **Selalu Testing**: Kamu WAJIB selalu melakukan test (pengujian) setelah selesai melakukan suatu perubahan pada kode dengan menggunakan tool/skill **Playwright**. Pastikan UI atau fitur yang berubah tidak mengalami regresi.

## 4. Aturan Notifikasi
Jika sebuah perubahan atau penambahan fitur mengharuskan untuk **menambah atau merubah notifikasi**, kamu WAJIB:
1. Selalu cek instruksi dan panduan pada skill `sigapp-notifikasi`.
2. Selalu **perbaharui dokumentasi** pada file `docs/modul-notifikasi.md` dengan detail perubahan atau penambahan yang baru dilakukan.
