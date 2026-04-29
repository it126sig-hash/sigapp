# Panduan Standar Desain Modal SIGAPP

Dokumen ini merupakan ringkasan dari refaktor desain modal (`#modals-set_harga` dan `#modal_divisi4`) dan berfungsi sebagai **acuan (guideline)** untuk mengubah atau membuat modal baru di dalam sistem SIGAPP agar tampilannya seragam, modern, dan fungsional.

---

## 1. Ringkasan Perubahan Sebelumnya
- **Penyeragaman CSS:** Membuat sistem kelas global kustom (`.modal-content-custom`, `.section-card`, dll.) yang diatur secara terpusat di `assets/css/style.css`.
- **Refaktor `#modals-set_harga`:** Mengubah modal form sederhana (grid 2/3 kolom) dari tampilan Bootstrap lama menjadi UI Card yang lebih ringan.
- **Refaktor `#modal_divisi4`:** Mengubah modal kompleks dengan banyak tab (Konsumen, Wawancara, KPR, dll.) menjadi antarmuka dengan **Sidebar ScrollSpy**. Tab ditiadakan, diganti dengan konten yang memanjang ke bawah, di mana navigasi sidebar di sebelah kiri akan menyala secara otomatis mengikuti posisi *scroll* pengguna.
- **Integritas Data:** Semua *ID form*, *ID input*, dan *Event Listener* dipertahankan dengan ketat agar tidak merusak logika AJAX dan backend CodeIgniter 4.

---

## 2. Acuan Struktur HTML Modal (Sederhana)

Gunakan struktur di bawah ini untuk modal form standar yang tidak membutuhkan navigasi *sidebar*.

```html
<div class="modal fade" id="modals-id-anda">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <!-- Gunakan kelas modal-content-custom -->
        <form id="form-id-anda" class="modal-content modal-content-custom pt-0" autocomplete="off">
            
            <!-- HEADER MODAL -->
            <div class="modal-header-custom">
                <div>
                    <div class="modal-title-main">Kategori Modul</div>
                    <div class="modal-title-kavling">Judul Aksi Modal</div>
                </div>
                <button type="button" class="btn-close-modal" data-dismiss="modal"><i class="fas fa-times"></i></button>
            </div>

            <!-- BODY MODAL -->
            <div class="modal-body-custom" style="padding: 24px;">
                <div class="modal-main w-100">
                    
                    <!-- KARTU SEKSI (Gunakan berulang sesuai kebutuhan kelompok data) -->
                    <div class="section-card mb-2">
                        <div class="section-card-header">
                            <div class="section-card-icon icon-blue"><i class="fas fa-info"></i></div>
                            <h6 class="section-card-title">Informasi Dasar</h6>
                        </div>
                        <div class="section-card-body">
                            
                            <!-- BUNGKUS INPUT -->
                            <div class="form-group-custom">
                                <label class="form-label-custom">Nama Field</label>
                                <input type="text" class="form-control-custom" id="id_field" name="name_field" />
                            </div>
                            
                        </div>
                    </div>

                </div>
            </div>
            
            <!-- FOOTER MODAL -->
            <div class="modal-footer-custom">
                <button type="button" class="btn-cancel" data-dismiss="modal"><i class="fas fa-times mr-1"></i> Batal</button>
                <button type="button" class="btn-save" onclick="save_function()"><i class="fas fa-save mr-1"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>
```

---

## 3. Acuan Struktur HTML Modal (Kompleks dengan Sidebar)

Gunakan struktur ini untuk form modal yang **sangat panjang dan memiliki banyak kategori rincian** (misal: Marketing Data, Legalitas Kompleks).

1. Pastikan `.modal-body-custom` memiliki `padding: 0;` dan menggunakan `flex-direction: row;` (di `style.css`).
2. Buat `.modal-sidebar` untuk menu navigasi.
3. Buat `.scroll-section` di dalam `.modal-main`.

```html
<!-- ... (Header Modal sama seperti di atas) ... -->

<div class="modal-body-custom" style="padding: 0; align-items: stretch;">
    <!-- SIDEBAR NAVIGATION -->
    <div class="modal-sidebar">
        <div class="sidebar-section-label">Grup Menu</div>
        <ul class="nav nav-pills modal-sidebar-nav" id="sidebar-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link sidebar-nav-item active" id="tab-sd-section1" href="#sd-section1">
                    <i class="fas fa-user"></i> Seksi Pertama
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link sidebar-nav-item" id="tab-sd-section2" href="#sd-section2">
                    <i class="fas fa-flag"></i> Seksi Kedua
                </a>
            </li>
        </ul>
    </div>

    <!-- MAIN CONTENT AREA (SCROLLABLE) -->
    <div class="modal-main" id="modal-main-scroll-area" style="flex-grow: 1; padding: 24px; overflow-y: auto; scroll-behavior: smooth;">
        
        <!-- BAGIAN 1 -->
        <div id="sd-section1" class="scroll-section" style="padding-bottom: 2rem;">
            <div class="section-card mb-2">
                <!-- isi form -->
            </div>
        </div>

        <!-- BAGIAN 2 -->
        <div id="sd-section2" class="scroll-section" style="padding-bottom: 2rem;">
            <div class="section-card mb-2">
                <!-- isi form -->
            </div>
        </div>

    </div>
</div>
```

### Script Javascript untuk ScrollSpy Sidebar
*Wajib disematkan di bawah struktur modal (atau di file JS masing-masing modul).*
```javascript
document.addEventListener("DOMContentLoaded", function() {
    const scrollArea = document.getElementById("modal-main-scroll-area");
    const sections = Array.from(document.querySelectorAll(".scroll-section"));
    const navItems = document.querySelectorAll(".sidebar-nav-item");

    if (scrollArea && sections.length > 0) {
        // Klik Sidebar = Scroll ke section terkait
        navItems.forEach(item => {
            item.addEventListener("click", function(e) {
                e.preventDefault();
                const targetId = this.getAttribute("href").substring(1);
                const targetEl = document.getElementById(targetId);
                if (targetEl && scrollArea) {
                    scrollArea.scrollTo({
                        top: targetEl.offsetTop - scrollArea.offsetTop,
                        behavior: "smooth"
                    });
                }
            });
        });

        // Scroll konten = Update Sidebar Active
        scrollArea.addEventListener("scroll", function() {
            let current = "";
            const currentPosition = scrollArea.scrollTop;
            
            sections.forEach(section => {
                const sectionTop = section.offsetTop - scrollArea.offsetTop - 50; 
                if (currentPosition >= sectionTop) {
                    current = section.getAttribute("id");
                }
            });

            navItems.forEach(item => {
                item.classList.remove("active");
                if (item.getAttribute("href") === "#" + current) {
                    item.classList.add("active");
                }
            });
        });
    }
});
```

---

## 4. Peraturan Penting saat Refaktor Modal
1. **Dilarang Keras Mengubah ID:** 
   Jangan mengubah atribut `id` pada field `<input>`, `<select>`, `<button>`, maupun pada tag `<form>`. ID sangat vital karena menjadi target dari fungsi jQuery (terutama saat validasi dan `FormData()`) dan logika *DataTables*.
2. **Posisikan Input `hidden` dengan Benar:** 
   Usahakan semua input bertipe `hidden` tetap berada di dalam tag `<form>` dan jangan sampai terhapus.
3. **Komponen Grid Ekstra:**
   Gunakan properti CSS global yang sudah ada seperti `<div class="two-col">` atau `<div class="three-col">` untuk memecah form secara mendatar secara elegan ketimbang menulis class `col-md-` yang berlebihan, terutama jika berada di dalam `.section-card-body`.
4. **Warna Ikon:**
   Tersedia beberapa helper class untuk `.section-card-icon`: `icon-blue`, `icon-amber`, `icon-teal`, `icon-grey`, `icon-green`. Sesuaikan dengan konteks modul.

