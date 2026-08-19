# Design Document: list-kavling-redesign

## Overview

Dokumen ini adalah panduan implementasi lengkap untuk redesain halaman `app/Views/kavling/list-kavling.php`. Tujuan utama: (1) memindahkan kolom aksi ke posisi paling kiri tabel, (2) menyesuaikan freeze column agar action column ikut ter-freeze, (3) mengintegrasikan modal siteplan per departemen langsung di halaman list, dan (4) memastikan semua variabel global yang dibutuhkan modal tersedia.

**File yang diubah:** Hanya satu file — `app/Views/kavling/list-kavling.php`

**File yang TIDAK diubah:** Semua file di `app/Views/siteplan/` tidak dimodifikasi sama sekali.

---

## Architecture

### Alur Kerja Setelah Redesain

```
User membuka list-kavling.php
  │
  ├─ PHP: Ambil role user ($k, $v dari user()->getRoles())
  ├─ PHP: Render variabel global JS (rolename, roleid, has_akses, pph, ppn, conf, li_keu)
  ├─ PHP: Include modal siteplan sesuai role (conditional)
  │
  └─ Browser merender halaman:
       ├─ DataTable init dengan leftColumns: 6
       ├─ Action column (index 0) ter-freeze bersama 5 kolom identitas
       │
       └─ User klik tombol di action column:
            ├─ "Edit" → openEditModal(id_kavling)
            │     └─ Berdasarkan roleid, buka modal yang sesuai
            │           └─ Set .id_kavling.val(id_kavling) di dalam modal
            │
            └─ "Lihat Data" → openDetailModal(id_kavling)
                  └─ Panggil detail_kavling_list(id_kavling)
                        └─ Buka #modal_detail
```

### Mapping Role → Modal ID → Fungsi Pembuka

| roleid | Nama Role      | Modal ID yang dibuka          | Fungsi pembuka di siteplan |
|--------|----------------|-------------------------------|----------------------------|
| 1      | Admin          | Semua (ikut pilih-divisi)     | Semua fungsi open_*        |
| 3      | Keuangan       | `#modal_divisi3`              | `open_keuangan(sh, 3, id)` |
| 4      | Marketing Data | `#modal_divisi4`              | `open_mkdt(sh, 4, id)`     |
| 5      | Legal          | `#modal_flegal`               | `open_legal(sh, 5, id)`    |
| 6      | Planning       | `#modals-slide-in`            | `open_planning(sh, 6, id)` |
| 7      | Produksi       | `#modal_divisi7`              | `open_produksi(sh, 7, id)` |
| 8      | Sales          | `#modal_serah_terima`         | (langsung show modal)      |
| 9      | Direksi        | `#modal-diskresi`             | `open_direksi(sh, 9, id)`  |
| 10     | Pajak          | `#modal_divisi10`             | `open_pajak(sh, 10, id)`   |
| lain   | -              | Tidak ada modal               | Sembunyikan tombol Edit    |

> **Catatan penting:** Fungsi `open_*` (open_keuangan, open_produksi, dll.) sudah ada di file JS terpisah yang di-load oleh layout utama (`assets/js/keuangan.js`, `assets/js/produksi.js`, dll.). Fungsi-fungsi ini menerima parameter `sh` (shape object dari siteplan), `role`, dan `id_kavling`. Di konteks list-kavling, kita tidak punya `sh` object seperti di siteplan. Oleh karena itu, kita membuat fungsi wrapper `openEditModal(id_kavling)` yang membangun `sh` object minimal dan memanggil fungsi yang sesuai, ATAU langsung set `.id_kavling` dan show modal.

---

## Components and Interfaces

### Komponen yang Dimodifikasi

#### 1. `app/Views/kavling/list-kavling.php`

Perubahan yang dilakukan:
- Tambah blok PHP untuk ambil role user
- Tambah blok JS untuk variabel global
- Tambah blok PHP conditional untuk include modal siteplan
- Ubah `<thead>` tabel: pindahkan `<th>` action ke posisi pertama
- Ubah DataTable config: `leftColumns: 6`, tambah `columnDefs` untuk action column di index 0
- Tambah fungsi JS: `openEditModal(id_kavling)` dan `openDetailModal(id_kavling)`
- Tambah event listener untuk refresh tabel setelah modal save

---

## Data Models

### Struktur Data yang Relevan

#### Data per baris dari server (AJAX response)
Server mengembalikan array di `r.data`. Setiap elemen adalah array dengan urutan kolom sesuai query SQL. Kolom `id_kavling` tersedia sebagai `data.id_kavling` (object property) atau via `data[N]` (array index).

Berdasarkan kode existing di `list-kavling.php`, action column dirender via `columnDefs` dengan `targets: [N]` (index kolom terakhir). Setelah redesain, action column ada di index 0.

#### Variabel Global yang Dibutuhkan Modal Siteplan

```javascript
// Wajib tersedia sebelum modal siteplan di-include
const rolename = 'nama_role';      // string, nama role user
const roleid = 1;                   // integer, ID role user
const has_akses = {};               // object, hak akses dari server
const pph = {};                     // object, data PPH
const ppn = {};                     // object, data PPN
var conf = {};                      // object, konfigurasi sistem
const li_keu = {};                  // object, list keuangan
```

#### Object `sh` Minimal untuk Fungsi open_*

Fungsi `open_produksi`, `open_legal`, `open_mkdt`, `open_keuangan`, `open_pajak` di siteplan menerima parameter `sh` yang berisi:

```javascript
// sh object minimal yang dibutuhkan fungsi open_*
const sh = {
    data: {
        id_produksi: null,   // diisi dari response AJAX get detail
        id_legal: null,
        id_mkdt: null,
        id_keuangan: null,
        nama_jalan: '',
        no_kavling: '',
        tipe: 'kavling'
    },
    data2: {
        id_hargajual: null,
        no_tipe_rumah: '',
        tipe_rumah: '',
        harga_akhir: 0
    }
};
```

> **Strategi implementasi:** Daripada membangun `sh` object lengkap (yang memerlukan AJAX call tambahan), kita langsung set `.id_kavling` input di dalam modal dan show modal. Fungsi `open_*` di siteplan sudah melakukan `$('.id_kavling').val(id_kavling)` sebagai salah satu langkah pertamanya. Kita bisa bypass fungsi `open_*` dan langsung:
> 1. Reset form modal
> 2. Set `.id_kavling` value
> 3. Show modal
> 4. Biarkan modal melakukan AJAX load data sendiri (modal sudah punya logic ini)

---

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system — essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: Action column render selalu menghasilkan tombol yang benar

*For any* `id_kavling` yang valid (integer positif), fungsi render action column SHALL menghasilkan string HTML yang mengandung:
- Tombol dengan `onclick="openEditModal(<id_kavling>)"` (jika roleid memiliki modal)
- Tombol dengan `onclick="openDetailModal(<id_kavling>)"`

**Validates: Requirements 3.2, 3.5, 3.6**

### Property 2: Modal yang dibuka sesuai dengan roleid

*For any* `roleid` yang valid dari set {1, 3, 4, 5, 6, 7, 8, 9, 10}, fungsi `openEditModal(id_kavling)` SHALL membuka modal dengan ID yang sesuai dengan mapping role → modal ID yang telah didefinisikan.

**Validates: Requirements 3.5, 4.1, 4.2, 4.3, 4.4, 4.5, 4.6, 4.7, 4.8, 4.9**

### Property 3: id_kavling selalu diteruskan ke modal

*For any* `id_kavling` yang valid, setelah `openEditModal(id_kavling)` dipanggil, semua elemen dengan class `.id_kavling` di dalam modal yang aktif SHALL memiliki value yang sama dengan `id_kavling` yang diberikan.

**Validates: Requirements 4.10**

---

## Error Handling

### Skenario Error dan Penanganannya

| Skenario | Penanganan |
|----------|-----------|
| Role user tidak dikenali (bukan 1,3,4,5,6,7,8,9,10) | Tombol Edit disembunyikan, hanya tampil tombol Lihat Data |
| `id_kavling` null atau undefined saat tombol diklik | Guard check di `openEditModal`: `if (!id_kavling) return;` |
| Modal siteplan tidak ter-load (PHP conditional gagal) | Tombol Edit tidak berfungsi; user tetap bisa klik Lihat Data |
| AJAX DataTable gagal | DataTable menampilkan pesan error bawaan |
| Variabel global tidak tersedia | Inisialisasi dengan nilai default sebelum modal di-include |

---

## Testing Strategy

Fitur ini adalah UI/frontend feature yang melibatkan HTML rendering, CSS, dan JavaScript event handling. Property-based testing tidak sepenuhnya applicable untuk semua aspek, namun beberapa property dapat diverifikasi dengan unit test JavaScript.

**Unit Tests (JavaScript):**
- Verifikasi fungsi `openEditModal` membuka modal yang benar per roleid
- Verifikasi fungsi render action column menghasilkan HTML yang benar
- Verifikasi `id_kavling` diteruskan dengan benar ke modal

**Smoke Tests (Manual):**
- Verifikasi halaman load tanpa error console
- Verifikasi freeze column berfungsi saat scroll horizontal
- Verifikasi tombol Edit membuka modal yang benar per role
- Verifikasi tombol Lihat Data membuka `#modal_detail`
- Verifikasi tabel refresh setelah save di modal

---

## Implementation Guide (Panduan Implementasi Step-by-Step)

> Bagian ini adalah panduan implementasi lengkap. Ikuti langkah-langkah berikut secara berurutan.


### Step 1: Tambah Blok PHP untuk Ambil Role User

Tambahkan blok PHP ini di bagian **paling atas** file `list-kavling.php`, sebelum tag `<link>` CSS pertama:

```php
<?php
// Ambil role user yang sedang login
// Mengikuti pola yang sama dengan siteplan/master.php
$k = null;
$v = null;
foreach (user()->getRoles() as $key => $val) {
    $k = $key;   // $k = role ID (integer): 1=Admin, 3=Keuangan, 4=Mkdt, 5=Legal, 6=Planning, 7=Produksi, 8=Sales, 9=Direksi, 10=Pajak
    $v = $val;   // $v = role name (string): 'Admin', 'Keuangan', dll.
}
?>
```

**Mengapa di paling atas?** Karena variabel `$k` dan `$v` dibutuhkan oleh blok PHP conditional yang akan di-include di bawah.

---

### Step 2: Tambah Blok JS Variabel Global

Tambahkan blok `<script>` ini **setelah** blok PHP Step 1, **sebelum** tag `<link>` CSS pertama:

```html
<script>
    // Variabel global yang dibutuhkan oleh modal siteplan
    // WAJIB ada sebelum modal siteplan di-include (di bawah)
    const rolename = '<?= $v ?>';
    const roleid = <?= $k ?>;
    const has_akses = JSON.parse('<?= json_encode($has_akses ?? []) ?>');
    const pph = JSON.parse('<?= json_encode($pph ?? []) ?>');
    const ppn = JSON.parse('<?= json_encode($ppn ?? []) ?>');
    var conf = JSON.parse('<?= $conf ?? '{}' ?>');
    const li_keu = JSON.parse('<?= $li_keu ?? '[]' ?>');

    // Variabel tambahan yang dibutuhkan fungsi open_* di siteplan JS
    // (dibutuhkan oleh open_keuangan, open_produksi, dll.)
    let editdtt = [];  // array shape yang dipilih (di siteplan diisi dari canvas click)
    const not_found = "/images/not_found.png";
</script>
```

**Catatan penting:**
- `$has_akses`, `$pph`, `$ppn`, `$conf`, `$li_keu` harus tersedia dari controller yang merender view ini.
- Jika variabel tersebut tidak tersedia di controller, gunakan nilai default: `$has_akses ?? []`, `$conf ?? '{}'`, dll.
- Operator `??` (null coalescing) mencegah PHP error jika variabel tidak di-pass dari controller.

---

### Step 3: Tambah Blok PHP Conditional untuk Include Modal Siteplan

Tambahkan blok PHP ini **setelah** semua tag `<script>` vendor JS (setelah baris `dataTables.fixedColumns.js`), **sebelum** blok `<script>` DataTable config:

```php
<!-- Modal Siteplan per Role -->
<!-- Mengikuti pola kondisional yang sama dengan siteplan/master.php -->
<?php if ($k == 1 || $k == 6): ?>
    <?php echo view('siteplan/planning'); ?>
<?php endif; ?>

<?php if ($k == 7 || $k == 1): ?>
    <?php echo view('siteplan/produksi'); ?>
<?php endif; ?>

<?php if ($k == 8 || $k == 1): ?>
    <?php echo view('siteplan/sales'); ?>
<?php endif; ?>

<?php if ($k == 5 || $k == 1): ?>
    <?php echo view('siteplan/legal'); ?>
<?php endif; ?>

<?php if ($k == 4 || $k == 1): ?>
    <?php echo view('siteplan/mkdt'); ?>
<?php endif; ?>

<?php if ($k == 9 || $k == 1): ?>
    <?php echo view('siteplan/direksi'); ?>
<?php endif; ?>

<?php if ($k == 3 || $k == 1): ?>
    <?php echo view('siteplan/keuangan'); ?>
<?php endif; ?>

<?php if ($k == 10 || $k == 1): ?>
    <?php echo view('siteplan/pajak'); ?>
<?php endif; ?>
```

**Mengapa setelah vendor JS?** Modal siteplan mengandung script yang bergantung pada jQuery, flatpickr, select2, sweetalert2 — semua harus sudah di-load sebelumnya.

---

### Step 4: Ubah Struktur `<thead>` Tabel

Ganti seluruh blok `<thead>` yang ada dengan struktur baru berikut. Perubahan utama: `<th rowspan="3" id="tb-action"></th>` dipindah dari **akhir baris 1** ke **awal baris 1**.

**Struktur `<thead>` BARU (ganti yang lama):**

```html
<thead>
    <tr>
        <!-- ACTION COLUMN: dipindah ke PALING KIRI (index 0) -->
        <th rowspan="3" id="tb-action">AKSI</th>
        <!-- Kolom identitas kavling (index 1-7) -->
        <th rowspan="3" id="tb-NO">NO</th>
        <th colspan="2" id="tb-KAVLING">KAVLING</th>
        <th rowspan="3" id="tb-TYPE">TYPE</th>
        <th rowspan="3" id="tb-NAMA_KONSUMEN">NAMA KONSUMEN</th>
        <th rowspan="3" id="tb-SALES">SALES</th>
        <th rowspan="3" id="tb-TGL_BOOKING">TGL BOOKING</th>
        <th rowspan="3" id="tb-TGL_WAWANCARA">TGL WAWANCARA</th>
        <!-- Kolom departemen -->
        <th colspan="6" id="tb-MARKETING_DATA">MARKETING DATA</th>
        <th colspan="4" id="tb-KEUANGAN">KEUANGAN</th>
        <th colspan="4" id="tb-PRODUKSI">PRODUKSI</th>
        <th colspan="3" id="tb-LEGAL">LEGAL</th>
        <th id="tb-GA">GA</th>
    </tr>

    <tr>
        <!-- Baris 2: sub-header (tidak ada perubahan urutan, action sudah rowspan=3) -->
        <th rowspan="2" id="tb-BLOK">BLOK</th>
        <th rowspan="2" id="tb-NO_KAVLING">NO</th>

        <th colspan="2" id="tb-PENGAJUAN">PENGAJUAN</th>
        <th rowspan="2" id="tb-STATUS">STATUS</th>
        <th colspan="2" id="tb-SP3K">SP3K</th>
        <th rowspan="2" id="tb-SIKASEP">SIKASEP</th>

        <th rowspan="2" id="tb-TUNAI">TUNAI</th>
        <th rowspan="2" id="tb-UM">UM</th>
        <th rowspan="2" id="tb-B_ADM">B. ADM</th>
        <th rowspan="2" id="tb-BIAYA_BIAYA">BIAYA-BIAYA</th>

        <th colspan="2" id="tb-BANGUNAN">BANGUNAN</th>
        <th rowspan="2" id="tb-LISTRIK">LISTRIK</th>
        <th rowspan="2" id="tb-JALAN">JALAN</th>

        <th rowspan="2" id="tb-HGB">HGB</th>
        <th rowspan="2" id="tb-IMB">IMB</th>
        <th rowspan="2" id="tb-PBB">PBB</th>

        <th rowspan="2" id="tb-SIKUMBANG">SIKUMBANG</th>
    </tr>

    <tr>
        <!-- Baris 3: sub-sub-header -->
        <th id="tb-TUNAI_KPR">TUNAI/KPR</th>
        <th id="tb-BANK">BANK</th>
        <th id="tb-TERBIT">TERBIT</th>
        <th id="tb-EXPIRED">EXPIRED</th>

        <th id="tb-PERSEN">%</th>
        <th id="tb-LPA">LPA</th>
    </tr>
</thead>
```

**Penjelasan perubahan:**
- `<th rowspan="3" id="tb-action">AKSI</th>` dipindah ke posisi pertama di baris 1
- Semua kolom lain tetap sama, hanya urutan di HTML yang berubah
- Ini membuat action column menjadi **index 0** di DataTable

---

### Step 5: Ubah CSS Inline

Ganti blok `<style>` yang ada:

```html
<!-- LAMA (ganti ini): -->
<style>
  table,
  tr {
    vertical-align: middle !important;
    text-align: center !important;
    font-size: 10px;
  }
</style>
```

Dengan:

```html
<!-- BARU: -->
<style>
    /* Compact mode: padding dikurangi, font kecil */
    #data_tables td,
    #data_tables th {
        vertical-align: middle !important;
        text-align: center !important;
        font-size: 10px !important;
        padding: 4px 6px !important;  /* max 4px vertikal, 6px horizontal */
        white-space: nowrap;           /* cegah text wrap di sel */
    }

    /* Action column: lebar minimal 80px agar kedua tombol tidak terpotong */
    #data_tables td:first-child,
    #data_tables th:first-child {
        min-width: 80px;
        width: 80px;
    }

    /* Tombol di action column: ukuran kecil, tidak wrap */
    #data_tables .btn-action {
        font-size: 9px;
        padding: 2px 5px;
        white-space: nowrap;
    }
</style>
```

---

### Step 6: Ubah DataTable Config

Ganti seluruh blok `$('#data_tables').DataTable({...})` dengan konfigurasi baru berikut.

**Perubahan kunci:**
1. `leftColumns: 5` → `leftColumns: 6` (action column + 5 kolom identitas)
2. Tambah `columnDefs` untuk action column di index 0

```javascript
var table = $('#data_tables').DataTable({
    fnDrawCallback: function() {
        $('[data-toggle="popover"]').popover();
    },
    scrollY: "50vh",
    scrollX: true,
    scrollCollapse: true,
    fixedColumns: {
        leftColumns: 6  // DIUBAH dari 5 ke 6: action(1) + NO(1) + BLOK(1) + NO_KAV(1) + TYPE(1) + NAMA_KONSUMEN(1) = 6
    },
    processing: true,
    serverSide: true,
    lengthChange: true,
    searching: true,
    ordering: false,
    paging: true,
    ajax: {
        url: base_url + 'list-kavling/ambil',
        type: "POST",
        dataType: "json",
        data: function(data) {
            data[csrfName] = csrfHash;
            data.id_proyek = $("#id_proyek").val();
            data.id_cluster = $("#id_cluster").val();
            data.id_jalan = $("#id_jalan").val();
            data.sp3k = $("#sp3k").val();
            data.wawancara = $("#wawancara").val();
            data.akad = $("#akad").val();
        },
        dataSrc: function(r) {
            csrfHash = r.token;
            return r.data;
        },
        async: "true"
    },
    columnDefs: [
        {
            // Action column di index 0 (paling kiri)
            targets: 0,
            orderable: false,
            searchable: false,
            width: "80px",
            render: function(data, type, row) {
                // row adalah object atau array dari server
                // Ambil id_kavling dari property 'id_kavling' atau index tertentu
                // SESUAIKAN dengan struktur data yang dikembalikan server
                var id_kavling = row.id_kavling || row[0];

                var btnEdit = '';
                // Tampilkan tombol Edit hanya jika role memiliki modal
                // roleid tersedia sebagai variabel global dari Step 2
                var rolesWithModal = [1, 3, 4, 5, 6, 7, 8, 9, 10];
                if (rolesWithModal.indexOf(roleid) !== -1) {
                    btnEdit = '<button class="btn btn-primary btn-action btn-sm mr-1" ' +
                              'onclick="openEditModal(' + id_kavling + ')" ' +
                              'title="Edit Data">' +
                              '<i class="fa fa-edit"></i> Edit' +
                              '</button>';
                }

                var btnDetail = '<button class="btn btn-info btn-action btn-sm" ' +
                                'onclick="openDetailModal(' + id_kavling + ')" ' +
                                'title="Lihat Detail">' +
                                '<i class="fa fa-eye"></i> Lihat' +
                                '</button>';

                return '<div style="white-space:nowrap">' + btnEdit + btnDetail + '</div>';
            }
        }
    ]
});
```

**PENTING — Tentukan index `id_kavling` dari server:**
Buka controller `app/Controllers/Kavling.php` atau endpoint `list-kavling/ambil` dan cek struktur array yang dikembalikan. Jika server mengembalikan object dengan property `id_kavling`, gunakan `row.id_kavling`. Jika array numerik, gunakan `row[N]` sesuai posisi kolom `id_kavling` di query.

---

### Step 7: Tambah Fungsi `openEditModal(id_kavling)`

Tambahkan fungsi ini di dalam blok `<script>` DataTable (setelah inisialisasi `table`):

```javascript
/**
 * Membuka modal edit sesuai role user yang sedang login.
 * Menggunakan variabel global 'roleid' yang di-set di Step 2.
 *
 * @param {number} id_kavling - ID kavling yang akan diedit
 */
function openEditModal(id_kavling) {
    if (!id_kavling) {
        console.error('openEditModal: id_kavling tidak valid');
        return;
    }

    // Set id_kavling ke semua input dengan class .id_kavling di dalam modal
    // (semua modal siteplan menggunakan class ini untuk menyimpan id_kavling)
    $('.id_kavling').val(id_kavling);

    // Buka modal sesuai roleid
    switch (parseInt(roleid)) {
        case 3: // Keuangan
            // Reset form keuangan
            $('#fm-keuangan')[0].reset();
            // Set id_kavling (sudah di-set di atas via .id_kavling)
            // Show modal
            $('#modal_divisi3').modal({
                backdrop: 'static',
                keyboard: false
            });
            break;

        case 4: // Marketing Data
            // Reset form mkdt
            $('#fm-mkdt')[0].reset();
            $('#modal_divisi4').modal({
                backdrop: 'static',
                keyboard: false
            });
            break;

        case 5: // Legal
            // Legal memiliki 2 modal: modal_fotherlegal dan modal_flegal
            // Buka modal_flegal (form utama legal)
            $('#modal_flegal').modal({
                backdrop: 'static',
                keyboard: false
            });
            break;

        case 6: // Planning
            // Planning menggunakan modal slide-in
            $('#fm-add_kavling')[0].reset();
            $('#modals-slide-in').modal({
                backdrop: 'static',
                keyboard: false
            });
            break;

        case 7: // Produksi
            // Reset form produksi
            $('#fm-produksi')[0].reset();
            $('#modal_divisi7').modal({
                backdrop: 'static',
                keyboard: false
            });
            break;

        case 8: // Sales
            // Sales menggunakan modal serah terima
            $('#fm-serah-terima')[0].reset();
            $('#modal_serah_terima').modal({
                backdrop: 'static',
                keyboard: false
            });
            break;

        case 9: // Direksi
            // Direksi menggunakan modal-diskresi
            $('#fm-diskresi')[0].reset();
            $('#modal-diskresi').modal({
                backdrop: 'static',
                keyboard: false
            });
            break;

        case 10: // Pajak
            // Pajak menggunakan modal_divisi10
            $('#fm-pajak')[0].reset();
            $('#modal_divisi10').modal({
                backdrop: 'static',
                keyboard: false
            });
            break;

        case 1: // Admin - buka modal sesuai pilihan divisi (jika ada dropdown pilih-divisi)
            // Jika tidak ada dropdown pilih-divisi di halaman ini,
            // Admin bisa diarahkan ke modal mkdt sebagai default
            $('#fm-mkdt')[0].reset();
            $('#modal_divisi4').modal({
                backdrop: 'static',
                keyboard: false
            });
            break;

        default:
            // Role tidak dikenali, tidak ada modal
            console.warn('openEditModal: roleid ' + roleid + ' tidak memiliki modal yang terdaftar');
            break;
    }
}
```

**Catatan untuk implementor:**
- Setiap modal siteplan sudah memiliki logic AJAX load data sendiri yang dipicu saat modal dibuka (via event `show.bs.modal` atau `shown.bs.modal`).
- Pastikan modal siteplan yang bersangkutan sudah memiliki event listener yang membaca `.id_kavling` value untuk load data.
- Jika modal belum memiliki auto-load, tambahkan event listener di dalam modal view-nya (tapi ingat: kita tidak mengubah file siteplan).

---

### Step 8: Tambah Fungsi `openDetailModal(id_kavling)`

Tambahkan fungsi ini di dalam blok `<script>` DataTable (setelah `openEditModal`):

```javascript
/**
 * Membuka modal detail kavling (#modal_detail).
 * Modal detail sudah ada di siteplan/master.php dan di-include via layout utama.
 *
 * @param {number} id_kavling - ID kavling yang akan dilihat detailnya
 */
function openDetailModal(id_kavling) {
    if (!id_kavling) {
        console.error('openDetailModal: id_kavling tidak valid');
        return;
    }

    // Set id_kavling ke semua input .id_kavling
    $('.id_kavling').val(id_kavling);

    // Panggil AJAX untuk load data detail, lalu show modal
    // Mengikuti pola detail_kavling() di siteplan/master.php
    $.ajax({
        url: base_url + 'siteplan/get/detail',
        type: 'post',
        data: {
            [csrfName]: csrfHash,
            id_kavling: id_kavling
        },
        dataType: 'json',
        beforeSend: function() {
            // Tampilkan loading indicator jika ada
            // Siteplan menggunakan: $("#loading").removeClass("hidden");
        },
        success: function(r) {
            csrfHash = r.token;
            // Show modal detail
            $('#modal_detail').modal('show');
        },
        error: function(xhr, status, err) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal memuat data detail',
                text: err,
                showConfirmButton: true
            });
        }
    });
}
```

**Alternatif lebih sederhana** (jika modal_detail sudah punya auto-load via event):

```javascript
function openDetailModal(id_kavling) {
    if (!id_kavling) return;
    $('.id_kavling').val(id_kavling);
    $('#modal_detail').modal('show');
}
```

---

### Step 9: Tambah Mekanisme Refresh Tabel Setelah Modal Save

Modal siteplan memanggil `$('.modal').modal('hide')` setelah save berhasil (terlihat di `assets/js/produksi.js` baris `$(".modal").modal("hide")`). Kita perlu menangkap event ini untuk refresh tabel.

Tambahkan event listener ini di dalam blok `<script>` DataTable (setelah inisialisasi `table`):

```javascript
// Refresh DataTable setelah modal siteplan ditutup (setelah save)
// Menggunakan event 'hidden.bs.modal' yang dipicu saat modal ditutup
$(document).on('hidden.bs.modal', function(e) {
    var modalId = $(e.target).attr('id');

    // Daftar modal siteplan yang jika ditutup harus refresh tabel
    var siteplanModals = [
        'modal_divisi3',    // Keuangan
        'modal_divisi4',    // Marketing Data
        'modal_flegal',     // Legal
        'modal_fotherlegal',// Legal (other)
        'modals-slide-in',  // Planning
        'modal_divisi7',    // Produksi
        'modal_serah_terima', // Sales
        'modal-diskresi',   // Direksi
        'modal_divisi10'    // Pajak
    ];

    if (siteplanModals.indexOf(modalId) !== -1) {
        // Refresh DataTable untuk menampilkan data terbaru
        table.draw(false); // false = pertahankan halaman saat ini
    }
});
```

**Penjelasan `table.draw(false)`:**
- `table.draw()` tanpa parameter = reload dari halaman 1
- `table.draw(false)` = reload tapi tetap di halaman yang sama
- Gunakan `table.draw(false)` agar user tidak kehilangan posisi scroll/halaman

---

### Step 10: Verifikasi Struktur Akhir File

Setelah semua perubahan, urutan elemen di `list-kavling.php` harus seperti ini:

```
1. <?php // Step 1: Ambil role user ($k, $v) ?>
2. <script> // Step 2: Variabel global (rolename, roleid, has_akses, dll.) </script>
3. <link> CSS DataTables, FixedColumns, SweetAlert2, Select2 (tidak berubah)
4. <style> // Step 5: CSS compact mode (diubah) </style>
5. <div class="app-content"> ... </div>  // HTML utama (tidak berubah kecuali thead)
   └─ <thead> // Step 4: Action column di posisi pertama
6. <script src="vendors.min.js"> ... </script>  // Vendor JS (tidak berubah)
7. <script src="dataTables.fixedColumns.js"> </script>  // (tidak berubah)
8. <?php // Step 3: Include modal siteplan per role ?>
9. <script>
   // Step 6: DataTable config (leftColumns: 6, columnDefs action index 0)
   // Step 7: fungsi openEditModal(id_kavling)
   // Step 8: fungsi openDetailModal(id_kavling)
   // Step 9: event listener hidden.bs.modal untuk refresh tabel
   // ... semua JS yang sudah ada (filter, export, dll.) tetap di sini
   </script>
```

---

### Step 11: Verifikasi `id_kavling` dari Server Response

Buka file controller yang menangani endpoint `list-kavling/ambil` (kemungkinan di `app/Controllers/Kavling.php` atau `app/Services/`). Cari bagian yang membangun array data untuk DataTable response.

Contoh: jika server mengembalikan:
```json
{
  "data": [
    {"id_kavling": 123, "no": 1, "blok": "A", ...},
    ...
  ]
}
```
Maka di `columnDefs render`: gunakan `row.id_kavling`

Jika server mengembalikan array numerik:
```json
{
  "data": [
    [123, 1, "A", ...],
    ...
  ]
}
```
Maka gunakan `row[0]` (sesuaikan index dengan posisi `id_kavling` di query)

---

### Step 12: Test Manual Checklist

Setelah implementasi, lakukan test berikut:

- [ ] Halaman load tanpa error di browser console
- [ ] Action column muncul di posisi paling kiri tabel
- [ ] Action column ter-freeze saat scroll horizontal (tidak ikut bergeser)
- [ ] 5 kolom identitas (NO, BLOK, NO KAV, TYPE, NAMA KONSUMEN) juga ter-freeze
- [ ] Tombol "Edit" muncul untuk role yang memiliki modal (3,4,5,6,7,8,9,10,1)
- [ ] Tombol "Edit" tidak muncul untuk role yang tidak memiliki modal
- [ ] Klik "Edit" membuka modal yang benar sesuai role
- [ ] `id_kavling` terisi dengan benar di dalam modal setelah dibuka
- [ ] Klik "Lihat Data" membuka `#modal_detail`
- [ ] Setelah save di modal dan modal ditutup, tabel otomatis refresh
- [ ] Filter proyek/cluster/blok masih berfungsi
- [ ] Export Excel dan PDF masih berfungsi
- [ ] Tab "Riwayat Eksport" masih berfungsi
- [ ] Padding sel tabel ≤ 4px vertikal, ≤ 6px horizontal
- [ ] Font size tabel 10px


---

## Appendix: Kode Lengkap Blok yang Diubah

### A. Blok PHP + JS di Bagian Atas File (Step 1 + 2)

Letakkan ini di baris paling atas `list-kavling.php`, sebelum `<link>` CSS pertama:

```php
<?php
$k = null;
$v = null;
foreach (user()->getRoles() as $key => $val) {
    $k = $key;
    $v = $val;
}
?>
<script>
    const rolename = '<?= $v ?>';
    const roleid = <?= $k ?>;
    const has_akses = JSON.parse('<?= json_encode($has_akses ?? []) ?>');
    const pph = JSON.parse('<?= json_encode($pph ?? []) ?>');
    const ppn = JSON.parse('<?= json_encode($ppn ?? []) ?>');
    var conf = JSON.parse('<?= $conf ?? '{}' ?>');
    const li_keu = JSON.parse('<?= $li_keu ?? '[]' ?>');
    let editdtt = [];
    const not_found = "/images/not_found.png";
</script>
```

### B. Blok PHP Include Modal (Step 3)

Letakkan ini setelah semua `<script src="...">` vendor JS, sebelum `<script>` DataTable config:

```php
<?php if ($k == 1 || $k == 6): ?><?php echo view('siteplan/planning'); ?><?php endif; ?>
<?php if ($k == 7 || $k == 1): ?><?php echo view('siteplan/produksi'); ?><?php endif; ?>
<?php if ($k == 8 || $k == 1): ?><?php echo view('siteplan/sales'); ?><?php endif; ?>
<?php if ($k == 5 || $k == 1): ?><?php echo view('siteplan/legal'); ?><?php endif; ?>
<?php if ($k == 4 || $k == 1): ?><?php echo view('siteplan/mkdt'); ?><?php endif; ?>
<?php if ($k == 9 || $k == 1): ?><?php echo view('siteplan/direksi'); ?><?php endif; ?>
<?php if ($k == 3 || $k == 1): ?><?php echo view('siteplan/keuangan'); ?><?php endif; ?>
<?php if ($k == 10 || $k == 1): ?><?php echo view('siteplan/pajak'); ?><?php endif; ?>
```

### C. Blok `<script>` DataTable Lengkap (Step 6 + 7 + 8 + 9)

Ganti seluruh blok `<script>$(function() { var table = ... });</script>` yang ada dengan ini:

```javascript
<script>
  $(function() {
    var table = $('#data_tables').DataTable({
      fnDrawCallback: function() {
        $('[data-toggle="popover"]').popover();
      },
      scrollY: "50vh",
      scrollX: true,
      scrollCollapse: true,
      fixedColumns: {
        leftColumns: 6
      },
      processing: true,
      serverSide: true,
      lengthChange: true,
      searching: true,
      ordering: false,
      paging: true,
      ajax: {
        url: base_url + 'list-kavling/ambil',
        type: "POST",
        dataType: "json",
        data: function(data) {
          data[csrfName] = csrfHash;
          data.id_proyek = $("#id_proyek").val();
          data.id_cluster = $("#id_cluster").val();
          data.id_jalan = $("#id_jalan").val();
          data.sp3k = $("#sp3k").val();
          data.wawancara = $("#wawancara").val();
          data.akad = $("#akad").val();
        },
        dataSrc: function(r) {
          csrfHash = r.token;
          return r.data;
        },
        async: "true"
      },
      columnDefs: [
        {
          targets: 0,
          orderable: false,
          searchable: false,
          width: "80px",
          render: function(data, type, row) {
            // SESUAIKAN: gunakan row.id_kavling jika server return object,
            // atau row[0] jika server return array numerik
            var id_kavling = row.id_kavling !== undefined ? row.id_kavling : row[0];

            var rolesWithModal = [1, 3, 4, 5, 6, 7, 8, 9, 10];
            var btnEdit = '';
            if (rolesWithModal.indexOf(parseInt(roleid)) !== -1) {
              btnEdit = '<button class="btn btn-primary btn-action btn-sm mr-1" ' +
                        'onclick="openEditModal(' + id_kavling + ')" ' +
                        'title="Edit Data">' +
                        '<i class="fa fa-edit"></i> Edit' +
                        '</button>';
            }

            var btnDetail = '<button class="btn btn-info btn-action btn-sm" ' +
                            'onclick="openDetailModal(' + id_kavling + ')" ' +
                            'title="Lihat Detail">' +
                            '<i class="fa fa-eye"></i> Lihat' +
                            '</button>';

            return '<div style="white-space:nowrap">' + btnEdit + btnDetail + '</div>';
          }
        }
      ]
    });

    // ─── Fungsi openEditModal ───────────────────────────────────────────────
    function openEditModal(id_kavling) {
      if (!id_kavling) return;
      $('.id_kavling').val(id_kavling);

      switch (parseInt(roleid)) {
        case 3:
          $('#fm-keuangan')[0].reset();
          $('#modal_divisi3').modal({ backdrop: 'static', keyboard: false });
          break;
        case 4:
          $('#fm-mkdt')[0].reset();
          $('#modal_divisi4').modal({ backdrop: 'static', keyboard: false });
          break;
        case 5:
          $('#modal_flegal').modal({ backdrop: 'static', keyboard: false });
          break;
        case 6:
          $('#fm-add_kavling')[0].reset();
          $('#modals-slide-in').modal({ backdrop: 'static', keyboard: false });
          break;
        case 7:
          $('#fm-produksi')[0].reset();
          $('#modal_divisi7').modal({ backdrop: 'static', keyboard: false });
          break;
        case 8:
          $('#fm-serah-terima')[0].reset();
          $('#modal_serah_terima').modal({ backdrop: 'static', keyboard: false });
          break;
        case 9:
          $('#fm-diskresi')[0].reset();
          $('#modal-diskresi').modal({ backdrop: 'static', keyboard: false });
          break;
        case 10:
          $('#fm-pajak')[0].reset();
          $('#modal_divisi10').modal({ backdrop: 'static', keyboard: false });
          break;
        case 1:
          // Admin: default ke mkdt, atau tambahkan dropdown pilih divisi
          $('#fm-mkdt')[0].reset();
          $('#modal_divisi4').modal({ backdrop: 'static', keyboard: false });
          break;
        default:
          console.warn('Role ' + roleid + ' tidak memiliki modal terdaftar');
      }
    }

    // ─── Fungsi openDetailModal ─────────────────────────────────────────────
    function openDetailModal(id_kavling) {
      if (!id_kavling) return;
      $('.id_kavling').val(id_kavling);
      $('#modal_detail').modal('show');
    }

    // ─── Refresh tabel setelah modal siteplan ditutup ───────────────────────
    $(document).on('hidden.bs.modal', function(e) {
      var modalId = $(e.target).attr('id');
      var siteplanModals = [
        'modal_divisi3', 'modal_divisi4', 'modal_flegal', 'modal_fotherlegal',
        'modals-slide-in', 'modal_divisi7', 'modal_serah_terima',
        'modal-diskresi', 'modal_divisi10'
      ];
      if (siteplanModals.indexOf(modalId) !== -1) {
        table.draw(false);
      }
    });

    // ─── Semua kode JS yang sudah ada di bawah ini (TIDAK DIUBAH) ──────────

    $(".dataTables_filter input")
      .off()
      .on('change', function(e) {
        table.search(this.value).draw();
      });

    $(".self").select2();

    $("#id_proyek").select2({
      placeholder: "Pilih Proyek",
      allowClear: true,
      ajax: {
        url: base_url + "proyek/getAll",
        dataType: 'json',
        delay: 250,
        method: 'post',
        data: function(params) {
          return { [csrfName]: csrfHash, search: params.term };
        },
        processResults: function(r) {
          csrfHash = r.token;
          let results = [];
          $.each(r.data, function(index, item) {
            results.push({ id: item['id_proyek'], text: item[1] + ' (' + item[2] + ')' });
          });
          return { results: results };
        },
        cache: true
      },
    });

    $("#id_proyek").on("change", function(e) {
      $('#id_cluster').val(null).trigger('change');
      if (this.value) $("#id_cluster").prop("disabled", false);
      else $("#id_cluster").prop("disabled", true);
    });

    $("#id_cluster").select2({
      placeholder: "Pilih Cluster",
      allowClear: true,
      ajax: {
        url: base_url + "/cluster/getAll",
        dataType: 'json',
        delay: 250,
        method: 'post',
        data: function(params) {
          return { [csrfName]: csrfHash, search: params.term, id_proyek: $("#id_proyek").val() };
        },
        processResults: function(r) {
          csrfHash = r.token;
          let results = [];
          $.each(r.data, function(index, item) {
            results.push({ id: item[0], text: item[3] });
          });
          return { results: results };
        },
        cache: true
      },
    });

    $("#id_cluster").on("change", function(e) {
      $('#id_jalan').val(null).trigger('change');
      if (this.value) $("#id_jalan").prop("disabled", false);
      else $("#id_jalan").prop("disabled", true);
    });

    $("#id_jalan").select2({
      placeholder: "Pilih Blok",
      allowClear: true,
      ajax: {
        url: base_url + "/jalan/getAll",
        dataType: 'json',
        delay: 250,
        method: 'post',
        data: function(params) {
          return {
            [csrfName]: csrfHash,
            search: params.term,
            id_cluster: $("#id_cluster").val(),
            id_proyek: $("#id_proyek").val()
          };
        },
        processResults: function(r) {
          csrfHash = r.token;
          let results = [];
          $.each(r.data, function(index, item) {
            results.push({ id: item[0], text: item[3] });
          });
          return { results: results };
        },
        cache: true
      },
    });

    $("#btn_draw").on("click", function(e) {
      table.draw();
      load_riwayat();
    });

    $("#btn_export_excel").on('click', function(e) {
      if (!$("#id_proyek").val()) {
        return Swal.fire({ icon: 'error', title: "Proyek belum dipilih", showConfirmButton: false });
      }
      e.preventDefault();
      const $btn = $(this);
      $btn.prop("disabled", true);
      $btn.data("old-text", $btn.text());
      export_file("xlsx", $btn);
    });

    $("#btn_export_pdf").on('click', function(e) {
      if (!$("#id_proyek").val()) {
        return Swal.fire({ icon: 'error', title: "Proyek belum dipilih", showConfirmButton: false });
      }
      e.preventDefault();
      const $btn = $(this);
      $btn.prop("disabled", true);
      $btn.data("old-text", $btn.text());
      export_file("pdf", $btn);
    });

    function export_file(type, $btn) {
      $.ajax({
        type: "post",
        url: base_url + "export/poskon/" + type + "/aktif",
        data: {
          [csrfName]: csrfHash,
          id_proyek: $("#id_proyek").val(),
          id_cluster: $("#id_cluster").val(),
          id_jalan: $("#id_jalan").val(),
        },
        dataType: "json",
        beforeSend: function() {
          $btn.html("<i class='fa fa-spinner fa-spin'></i> Sedang Mengeksport");
          $btn.prop("disabled", true);
        },
        success: function(data) {
          var d = new Date();
          d = format_date(d.getFullYear() + "-" + (parseInt(d.getMonth()) + 1) + "-" + d.getDate());
          var $a = $("<a>");
          $a.attr("href", data.file);
          $("body").append($a);
          $a.attr("download", "Konsumen Aktif Per " + d + ": " + $("#id_proyek").select2('data')[0].text + "." + type);
          $a[0].click();
          $a.remove();
          $btn.html($btn.data("old-text"));
          $btn.prop("disabled", false);
        },
        error: function() {
          $btn.html($btn.data("old-text"));
          $btn.prop("disabled", false);
        }
      });
    }

    function load_riwayat() {
      $div = $("#riwayat-here");
      $div.empty();
      $.ajax({
        type: "post",
        url: base_url + "riwayat/poskon/aktif",
        data: {
          [csrfName]: csrfHash,
          id_proyek: $("#id_proyek").val(),
          id_cluster: $("#id_cluster").val(),
          id_jalan: $("#id_jalan").val(),
        },
        dataType: "json",
        beforeSend: function() {
          $div.append("<tr><td class='text-center' colspan=6><i class='fa fa-spinner fa-spin'></i> Memuat Data</td></tr>");
        },
        success: function(data) {
          $div.empty();
          let no = 1;
          if (data.length == 0) {
            $div.append("<tr><td class='text-center' colspan=6>Data Tidak Ditemukan</td></tr>");
          }
          $.each(data, function(index, item) {
            let icon = "PDF <i class='fa fa-file-pdf text-danger'></i>";
            if (item.tipe_file == "xlsx") {
              icon = "Excel <i class='fa fa-file-excel text-success'></i>";
            }
            $div.append("<tr><td>" + no++ + "</td><td>" + item.nama_proyek + "</td><td>" + format_datetime(item.export_tgl) + "</td><td>" + item.export_by + "</td><td>" + icon + "</td><td><a href='" + base_url + item.path + item.randomname + "' target='_blank'>Download</a></td></tr>");
          });
        },
        error: function() {
          $div.empty();
          $div.append("<tr><td class='text-center' colspan=6>Data Tidak Ditemukan</td></tr>");
        }
      });
    }

    $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
      var target = $(e.target).attr("href");
      if (target === '#riwayat_eksport') {
        load_riwayat();
      }
    });

    $(".select2-selection__arrow").removeClass("select2-selection__arrow");
  });

  // CSS width untuk kolom-kolom tertentu (tidak berubah dari kode asli)
  $('#tb-BLOK').css({ 'min-width': '150px', 'max-width': '150px' });
  $('#tb_nama_konsumen').css({ 'min-width': '150px', 'max-width': '150px' });
  $('#tb_tgl_booking, #tb_tgl_wwc, #tb_terbit, #tb_expired, #tb_pricelist').css({
    'min-width': '100px',
    'max-width': '100px'
  });
</script>
```

