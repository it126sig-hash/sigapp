# Implementasi Redesain Tabel List Kavling & Global Style

Dokumen ini berisi langkah-langkah implementasi teknis (Step-by-Step) untuk memenuhi fungsi yang didefinisikan pada `issue.md` dan `requirements.md`. Panduan ini dirancang khusus agar mudah dipahami oleh programmer junior maupun AI assistant lainnya.

## Tahap 1: Membuat Style DataTables Menjadi Global

Saat ini *styling* untuk teks berukuran 10px, rata tengah, dan vertical-align middle ada secara *hardcode* (`<style>`) di file `list-kavling.php`. Kita harus memindahkannya ke file CSS global agar teraplikasi ke semua tabel.

**Langkah-langkah:**
1. **Cari file CSS global:** Temukan file CSS utama aplikasi, seperti `public/assets/css/custom.css` atau file CSS yang di-load di layout utama/header (misalnya `app-assets/css/style.css`).
2. **Hapus style lokal:** Buka `app/Views/kavling/list-kavling.php`, cari tag `<style>` pada baris 18-25 berikut lalu **hapus file ini**:
   ```css
   <style>
     table, tr {
       vertical-align: middle !important;
       text-align: center !important;
       font-size: 10px;
     }
   </style>
   ```
3. **Tambahkan style global:** Di dalam file CSS global, tambahkan rule berikut untuk mengatur format tabel menjadi *compact*:
   ```css
   /* Global DataTables Styling */
   table.dataTable, 
   table.dataTable th, 
   table.dataTable td, 
   table.dataTable tr {
       vertical-align: middle !important;
       text-align: center !important;
       font-size: 10px !important;
   }

   /* Compact Padding Khusus untuk tabel bermarkup .compact */
   table.dataTable.compact tbody th, 
   table.dataTable.compact tbody td {
       padding: 4px 6px !important;
   }
   ```

---

## Tahap 2: Memindahkan Kolom Aksi ke Kiri

Untuk memastikan aksi langsung bisa diakses tanpa *scroll* kanan, kita harus mereposisi kolom HTML dan penyesuaian data dari Controller.

**Langkah-langkah Frontend (`app/Views/kavling/list-kavling.php`):**
1. Pindahkan `<th rowspan="3" id="tb-action"></th>` yang tadinya ada di paling akhir block `<thead>` (sekitar baris 121), ke paling **awal** elemen `<tr>` (baris 109), jadinya begini:
   ```html
   <tr>
     <th rowspan="3" id="tb-action">AKSI</th> <!-- DIPINDAHKAN KESINI -->
     <th rowspan="3" id="tb-NO">NO</th>
     <th colspan="2" id="tb-KAVLING">KAVLING</th>
     <!-- ... kolom lainnya ... -->
   </tr>
   ```
2. Ubah konfigurasi JavaScript DataTables untuk menambah jangkauan *Freeze Column* (karena kolom Aksi dimasukkan sebelum kolom No, `leftColumns` harus ditambah 1).
   ```javascript
   // Cari di inisialisasi DataTable pada baris 218:
   fixedColumns: {
     leftColumns: 6 // SEBELUMNYA: 5, UBAH JADI: 6
   }, // baris 218-219 juga nampak tertulis fixedColumns duplicate, pastikan membersihkannya
   ```

**Langkah-langkah Backend (Controller API / Method Pengambil Data):**
1. Buka Controller/Repository yang menghandle URL endpoint `list-kavling/ambil` (ini url origin data AJAX pada setup list-kavling.php).
2. Di dalam file kode API Server-Side Datatables tersebut (di bagian perulangan *mapping* array baris data JSON), blok string untuk Tombol Aksi (**$btn_html**) sebelumnya di letakkan pada akhir array `$row[] = $btn_html;`.
3. **Pindahkan array elemen tombol aksi tersebut** dan pasang sebagai elemen terawal (`$row[0]` pada associative arrays - atau bisa gunakan perintah `array_unshift($row, $btn_html);`) pada masing-masing hasil iterasi agar order datanya cocok dengan struktur header table (Index 0 adalah Aksi, Index 1 adalah NO, dst).

---

## Tahap 3: Integrasi Modal dari folder `/siteplan`

Agar pengguna bisa menge-klik tombol **Edit** berdasarkan Departemen (`Role`), kita akan meng-include Modal khusus secara kondisional. Tombol edit untuk kavling ada pada tiap index record tabel.

**Langkah-langkah Frontend (`app/Views/kavling/list-kavling.php`):**
1. Tambahkan kode Conditional PHP pembaca Role Session (pada umumnya menggunakan `session('role')` atau standar Codeigniter lain yang berlaku di sistem) dan rendering View tepat setelah HTML Container. Taruh bagian baris bawah file sebelum `</body>`:
   ```php
   <?php
     // Dapatkan role_id dari session 
     $role_id = session('role'); 
     
     // Include Modal View Sesuai Role
     if (in_array($role_id, [6, 1])) {
         echo view('siteplan/planning');
     } elseif (in_array($role_id, [7, 1])) {
         echo view('siteplan/produksi');
     } elseif (in_array($role_id, [8, 1])) {
         echo view('siteplan/sales');
     } elseif (in_array($role_id, [5, 1])) {
         echo view('siteplan/legal');
     } elseif (in_array($role_id, [4, 1])) {
         echo view('siteplan/mkdt');
     } elseif (in_array($role_id, [9, 1])) {
         echo view('siteplan/direksi');
     } elseif (in_array($role_id, [3, 1])) {
         echo view('siteplan/keuangan');
     } elseif (in_array($role_id, [10, 1])) {
         echo view('siteplan/pajak');
     }
   ?>
   ```
2. **Siapkan Variabel Global Bawaan Javascript:** Modal dari folder `/siteplan` pada aplikasi ini memiliki file internal javascript spesifik (seperti select2, dsb) dan sangat bergantung pada deklarasi Variabel yang bersifat general dan Global (cek `siteplan/master.php` sebagai referensi). Deklarasikan variabel awal (berikan block logic js set) pada tag script utama file `list-kavling.php`:
   ```javascript
   <script>
      // Sediakan context state variable default per ekspektasi role view
      var roleid    = "<?= session('role'); ?>";
      var rolename  = "<?= session('role_name'); ?>"; 
      var has_akses = true;
      var pph       = 0;
      var ppn       = 0;
      var li_keu    = []; 
   </script>
   ```
3. **Trigger Modal:** Ubah logic Handler untuk tag/class tombol **Edit**.
   ```javascript
   // Tambahkan event ketika delegasi klik tomol edit DataTables terbaca
   $('#data_tables tbody').on('click', '.btn-edit', function() {
       let id_kavling = $(this).data('id'); // asosiate target modal
       
       // Sesuai fungsional eksisting pada modal bawaan Siteplan.
       // File modal memiliki fungsi standard pengisi state seperti editData(id_kavling). Validasi callnya: 
       if(typeof editData === "function"){
           editData(id_kavling); 
       } else {
           console.error("[INFO] :: Form Handler Component (editData()) Belum ter-Load!");
       }
   });
   ```
4. **Reload Status:** Pada callback Response API (fungsi Sukses POST Data form dari `/view/siteplan`) Anda harus menangkap sinyal _Succeeded Save_ dan mengeksekusi function `table.draw(false);` (referensi DataTable Obj JS `var table`) agar data tabel UI me _refresh_ hasil pengeditan otomatis.
