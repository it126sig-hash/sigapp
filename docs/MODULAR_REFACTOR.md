# Refaktor Modular CI4: Konteks, Keputusan, dan Rencana

Dokumen ini adalah sumber kebenaran untuk refaktor ke `app/Modules`. Baca seluruhnya sebelum mengubah kode apa pun.

## 1. Masalah yang diselesaikan

- Proyek CodeIgniter 4 fullstack, struktur masih flat: `Controllers/`, `Models/` dst. bercampur dalam satu tumpukan.
- Sebagian controller masih memanggil model langsung. Layering tidak konsisten.
- AI agent sering keluar konteks karena tidak ada batas jelas tentang apa yang boleh dibaca dan diubah.
- Penyebab akarnya adalah struktur, bukan kurangnya dokumentasi. Dokumentasi saja tidak akan menyelesaikan masalah ini.

## 2. Kebutuhan (dari pemilik proyek)

1. Setiap modul bisa berdiri sendiri.
2. Modul lain hanya boleh diakses lewat interface/contract publik.
3. Repository **wajib di semua modul**.
4. Dokumentasi per modul harus memuat: routes, hubungan controller → service → repository → model, filter/auth, validation rules, migration dan tabel, event/queue, view dan JS, config/env.
5. Dokumentasi harus terbaca oleh AI agent dan programmer lain, jelas soal constraint dan konteks, dan diperbarui setiap modul berubah.
6. Sebelum mengerjakan modul, agent harus memeriksa apakah dokumentasi modul itu perlu diperbarui atau sudah aman dipakai.
7. Tooling berupa **custom spark command** (bukan skrip eksternal).

## 3. Keputusan arsitektur

### 3.1 Struktur modul

```
app/Modules/<Nama>/
├── Contracts/        satu-satunya yang boleh dipakai modul lain
├── DTOs/             tipe data lintas modul (dikembalikan Contract)
├── Controllers/
├── Services/         aturan bisnis saja
├── Repositories/     semua query/join/filter data
├── Models/  Entities/
├── Validation/  Filters/  Events/
├── Database/Migrations/
├── Views/  Assets/
├── Config/           Routes.php, Events.php, Services.php
├── MODULE.md         ditulis manual, maks ±1 halaman
├── module.generated.md   dihasilkan command, jangan diedit tangan
└── .module-baseline.json utang pelanggaran lama yang diterima
```

Daftarkan tiap modul di `Config\Autoload::$psr4`, mis. `'App\Modules\Billing' => APPPATH . 'Modules/Billing'`. Tanpa ini `Config/Routes.php` modul tidak ter-discover dan bagian Routes pada dokumen generated kosong.

### 3.2 Aturan dependensi (ditegakkan oleh `module:check`, bukan oleh disiplin)

| Layer | Boleh bergantung pada (dalam modul yang sama) |
|---|---|
| Controllers | Services, Contracts, DTOs, Validation |
| Services | Repositories, Contracts, DTOs, Entities, Events |
| Repositories | Models, Entities, DTOs |
| Models | Entities |
| Contracts | DTOs |

Aturan tambahan:
- Lintas modul: hanya `Contracts` dan `DTOs` milik modul lain.
- `model()`, `db_connect()`, `Database::connect()` dilarang di Controller dan Service.
- Tabel hanya diakses modul pemiliknya. Data lintas modul diambil lewat Contract.
- Contract mengembalikan DTO atau array terdefinisi, jangan Model/Entity. Jika bocor, pemanggil ikut terikat ke skema tabel modul lain.
- Repository wajib, tapi harus punya tugas: Model hanya dipakai di Repository, Repository mengembalikan Entity/array terdefinisi, dan semua query hidup di sana. Sediakan `BaseRepository` abstrak untuk CRUD standar supaya boilerplate hampir nol. Kalau Repository hanya membungkus `find()`/`save()` tanpa logika, ia hanya menambah file dan beban konteks.
- Gunakan constructor injection. Service Locator (`service('x')`) membuat call graph generator bolong.

## 4. Tooling Dbelum di desa n dan dibuatbenar.

## 5. Preflight sebelum agent mengerjakan modul

**Tahap 1 (mekanis):** `php spark module:check <Modul>`. Jika BLOCKED atau NEEDS_UPDATE, selesaikan dulu sebagai langkah terpisah (perbarui dokumen, atau perbaiki pelanggaran) sebelum mengerjakan tugas.

**Tahap 2 (agent, hanya jika tahap 1 OK):**
1. Baca `MODULE.md` dan `module.generated.md`.
2. Untuk tiap invariant dan constraint, temukan satu bukti di kode (file dan method) bahwa masih berlaku.
3. Cek apakah tugas melanggar "Out of scope" atau invariant.
4. Keluarkan verdict dalam format tetap:

```
VERDICT: OK | NEEDS_UPDATE | BLOCKED
Evidence: <file:method per invariant yang dicek>
Drift: <bagian doc yang tidak sesuai kode, atau "none">
Task conflict: <konflik dengan scope/invariant, atau "none">
```

Verdict `OK` tanpa baris Evidence tidak valid. Verdict "sudah oke" dari agent tanpa bukti tidak boleh dipercaya.

## 6. Template `MODULE.md`

Format baris Provides, Depends on, dan Owner tables dibaca regex oleh `module:check`. Ikuti persis.

```markdown
# Module: <Nama>
Owner table(s): `tabel_a`, `tabel_b`

## Purpose
<2-3 kalimat: masalah bisnis yang diselesaikan>

## Out of scope
<apa yang sengaja tidak ditangani modul ini>

## Provides (Contracts)
- `BillingContract::createInvoice(CreateInvoiceDTO): InvoiceDTO` - <kapan dipakai>

## Depends on (Contracts modul lain)
- `Customer\Contracts\CustomerLookup` - <untuk apa>

## Invariants
- <aturan yang tidak boleh dilanggar>

## Design decisions
- <keputusan non-obvious + alasannya>

## Last verified
<tanggal> @ <commit hash>
```

## 7. Aturan agent (salin ke `AGENTS.md` / `CLAUDE.md` di root)

1. Sebelum mengubah modul X, jalankan `php spark module:check X`, lalu lakukan preflight tahap 2.
2. Dilarang mengerjakan tugas jika verdict BLOCKED atau NEEDS_UPDATE belum diselesaikan.
3. Baca hanya `MODULE.md` dan `module.generated.md` milik X, serta file di `Contracts/` dan `DTOs/` modul lain.
4. Dilarang membuka `Services/`, `Repositories/`, `Models/` modul lain.
5. Butuh fungsi baru dari modul lain: tambahkan method di Contract modul itu dulu (tugas terpisah), jangan akses langsung.
6. Satu sesi = satu modul = satu jenis perubahan (pindah file / rapikan layer / ekstrak contract). Jangan dicampur.
7. Selesai: jalankan `module:generate X` dan `module:check X`, lalu commit dokumen bersama kodenya.

## 8. Urutan refaktor

1. **Verifikasi tooling** (lihat bagian 4). Tanpa ini semua langkah berikut tidak bisa dipercaya.
2. **Kunci perilaku:** simpan output `php spark routes` sebagai snapshot dan tulis test untuk alur kritis. Tanpa ini tidak ada cara tahu refaktor merusak sesuatu.
3. **Pasang skeleton dan baseline** sebelum memindahkan apa pun: daftarkan psr4, buat `BaseRepository`, jalankan `module:check --baseline`.
4. **Pilih modul "daun"** (paling sedikit dipakai modul lain) sebagai percobaan.
5. **Per modul, tiga langkah terpisah, masing-masing commit/sesi sendiri:**
   - a. Pindahkan file ke folder modul tanpa mengubah logika. Verifikasi dengan snapshot routes.
   - b. Rapikan controller yang memanggil model langsung: masukkan Service dan Repository.
   - c. Ekstrak Contract dan DTO untuk kebutuhan modul lain.
6. Tulis `MODULE.md`, jalankan `module:generate` dan `module:check`, lalu lanjut ke modul berikutnya.
7. Pasang `php spark module:check --all` di pre-commit atau CI. Tanpa ini "selalu diperbarui" kembali bergantung pada disiplin manual, dan itu akan gagal.

## 9. Rekomendasi utama (urut prioritas)

1. Tegakkan batas dengan tool, bukan dokumen. Dokumen mencatat, tool memaksa.
2. Pisahkan dokumen jadi dua lapis: `MODULE.md` (manual, tujuan dan invariant) dan `module.generated.md` (otomatis, trace mekanis). Jangan minta agent menebak ulang trace dari kode setiap sesi.
3. Wajibkan pengecekan di CI/pre-commit, bukan kebiasaan.
4. Refaktor bertahap dengan baseline: utang lama dicatat, pelanggaran baru langsung gagal.
5. Satu sesi, satu modul, satu jenis perubahan. Ini yang paling langsung mencegah agent keluar konteks.
6. Perhatikan kopling lewat database dan kebocoran Model/Entity lewat Contract. Keduanya tidak terlihat oleh analisis import.

## 10. Risiko yang harus dipantau

- Tooling belum teruji. Kemungkinan ada bug sintaks atau asumsi yang salah terhadap versi CI4 yang dipakai.
- Repository wajib tanpa `BaseRepository` yang baik akan menghasilkan kelas pembungkus kosong.
- Dokumen yang lolos `module:check` bisa tetap salah di bagian invariant. Itu hanya tertangkap oleh tahap 2 dan review manusia.
- Kalau codebase banyak memakai `service()` atau `model()` di dalam method, call graph dan cek layer akan menunjukkan gambaran lebih bersih dari kenyataan.
