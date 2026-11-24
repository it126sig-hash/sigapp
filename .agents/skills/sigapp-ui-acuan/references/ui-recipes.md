# SIGAPP UI Recipes & Copy-Paste Blueprints

Dokumen referensi ini berisi blueprint HTML & CSS standar yang diambil dari modul-modul acuan terbaik di SIGAPP (`bpb`, `laporan/hasil-akad`, `siteplan/partials/modal_detail`, `tiket_masalah`). Gunakan pola ini saat membuat halaman atau komponen baru agar tampilannya 100% seragam.

---

## 1. Blueprint CSS Modul (`public/assets/css/<nama-modul>.css`)

Selalu mulai file CSS modul dengan variabel lokal pada root class halaman dan awali seluruh selector dengan class halaman atau ID modal:

```css
/* ==========================================================================
   MODUL: NAMA MODUL
   Scope: .nama-modul-page, #nama-modul-detail-modal, #nama-modul-filter-modal
   ========================================================================== */

.nama-modul-page {
    --modul-primary: #2057a3;
    --modul-primary-dark: #163f78;
    --modul-primary-soft: #edf4ff;
    --modul-border: #e5e7eb;
    --modul-input-border: #d8dde3;
    --modul-text: #111827;
    --modul-muted: #6b7280;
    --modul-bg-subtle: #f3f5f7;
}

.nama-modul-page [hidden] {
    display: none !important;
}

/* Card & Header Standar Modul */
.nama-modul-page .card {
    border: 1px solid var(--modul-border);
    border-radius: 8px;
    box-shadow: none;
}

.nama-modul-page .card-header {
    align-items: center;
    background: #fff;
    border-bottom: 1px solid var(--modul-border);
    padding: 1rem 1.25rem;
}

/* Active Filter Chips */
.nama-modul-active-filters {
    display: flex;
    flex: 1 0 100%;
    flex-wrap: wrap;
    align-items: center;
    gap: .4rem;
    padding-top: .7rem;
}

.nama-modul-filter-chip {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    max-width: 100%;
    padding: .28rem .55rem;
    color: #24466f;
    background: var(--modul-primary-soft);
    border: 1px solid #d5e4fa;
    border-radius: 999px;
    font-size: .72rem;
    font-weight: 600;
}

/* Slide-out Filter Modal (Kanan) */
#nama-modul-filter-modal .modal-dialog {
    position: fixed;
    top: 0;
    right: 0;
    width: min(420px, 100vw);
    max-width: 100vw;
    height: 100vh;
    height: 100dvh;
    margin: 0;
    transform: translateX(100%);
    transition: transform .25s ease-out;
}

#nama-modul-filter-modal.show .modal-dialog {
    transform: translateX(0);
}

#nama-modul-filter-modal .modal-content {
    display: flex;
    height: 100%;
    overflow: hidden;
    border: 0;
    border-left: 1px solid var(--modul-border);
    border-radius: .65rem 0 0 .65rem;
}

#nama-modul-filter-modal .modal-header,
#nama-modul-filter-modal .modal-footer {
    flex: 0 0 auto;
    background: #fff;
}

#nama-modul-filter-modal .modal-body {
    flex: 1 1 auto;
    overflow-y: auto;
    padding: 1rem;
    background: #f8f9fb;
}

/* Detail Modal Standar (#modal_detail / #bpb-detail-modal style) */
#nama-modul-detail-modal .modal-dialog {
    margin: 1rem auto;
    max-width: min(1200px, calc(100vw - 32px));
}

#nama-modul-detail-modal .modal-content {
    border: 1px solid var(--modul-border);
    border-radius: 10px;
    box-shadow: 0 18px 42px rgba(15, 23, 42, .16);
    overflow: hidden;
}

#nama-modul-detail-modal .modal-header {
    align-items: center;
    background: #fff;
    border-bottom: 1px solid var(--modul-border);
    padding: .9rem 1.25rem;
}

#nama-modul-detail-modal .modal-title {
    color: var(--modul-text);
    font-size: 1.05rem;
    font-weight: 700;
}

#nama-modul-detail-modal .modal-body {
    background: var(--modul-bg-subtle);
    max-height: calc(100vh - 8rem);
    overflow-y: auto;
    padding: 1rem;
}

#nama-modul-detail-modal .card {
    background: #fff;
    border: 1px solid var(--modul-border);
    border-radius: 8px;
    box-shadow: none;
    margin-bottom: 1rem;
}

#nama-modul-detail-modal .info-row {
    align-items: flex-start;
    background: #f9fafb;
    border: 1px solid #edf0f2;
    border-radius: 6px;
    margin: 0 0 .45rem;
    padding: .45rem .55rem;
}

#nama-modul-detail-modal .info-label {
    color: var(--modul-muted);
    font-size: .78rem;
    font-weight: 700;
}

#nama-modul-detail-modal .info-value {
    color: var(--modul-text);
    font-weight: 600;
    overflow-wrap: anywhere;
}

/* Responsivitas Mobile */
@media (max-width: 767.98px) {
    #nama-modul-filter-modal .modal-dialog {
        width: 100vw;
    }

    #nama-modul-filter-modal .modal-content {
        border-radius: 0;
    }

    #nama-modul-detail-modal .modal-dialog {
        margin: .5rem auto;
        max-width: calc(100vw - 16px);
    }

    #nama-modul-detail-modal .modal-body {
        max-height: calc(100vh - 6rem);
        padding: .75rem;
    }

    #nama-modul-detail-modal .nav-tabs,
    #nama-modul-detail-modal .nav-pills {
        flex-direction: row !important;
        flex-wrap: nowrap;
        overflow-x: auto;
        padding-bottom: .25rem;
    }

    #nama-modul-detail-modal .nav-tabs .nav-link,
    #nama-modul-detail-modal .nav-pills .nav-link {
        white-space: nowrap;
    }
}
```

---

## 2. Blueprint View Halaman Operasional / CRUD (`app/Views/<modul>/index.php`)

```php
<link rel="stylesheet" type="text/css" href="<?= base_url('app-assets/vendors/css/tables/datatable/dataTables.bootstrap4.min.css') ?>">
<link rel="stylesheet" type="text/css" href="<?= base_url('app-assets/vendors/css/tables/datatable/responsive.bootstrap4.min.css') ?>">
<link rel="stylesheet" type="text/css" href="<?= base_url('app-assets/vendors/css/extensions/sweetalert2.min.css') ?>">
<link rel="stylesheet" type="text/css" href="<?= base_url('app-assets/vendors/css/forms/select/select2.min.css') ?>">
<link rel="stylesheet" type="text/css" href="<?= base_url('assets/css/nama-modul.css') ?>?v=<?= filemtime(FCPATH . 'assets/css/nama-modul.css') ?>">

<div class="app-content content nama-modul-page">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper">
        <div class="content-header row mb-1">
            <div class="content-header-left col-md-7 col-12">
                <h2 class="content-header-title mb-0"><?= esc($title ?? 'Judul Modul') ?></h2>
            </div>
            <div class="content-header-right col-md-5 col-12 text-md-right mt-1 mt-md-0">
                <button type="button" class="btn btn-primary" id="modul-btn-create">
                    <i class="fa fa-plus mr-50"></i>Tambah Data
                </button>
            </div>
        </div>

        <div class="content-body">
            <section id="modul-list-section">
                <div class="card">
                    <div class="card-header border-bottom d-flex flex-wrap align-items-center justify-content-between">
                        <h4 class="card-title mb-1 mb-md-0">Daftar Data</h4>
                        <div class="d-flex align-items-center" style="gap: .35rem;">
                            <button type="button" class="btn btn-outline-primary" data-toggle="modal" data-target="#nama-modul-filter-modal">
                                <i class="fa fa-filter mr-50"></i>Filter
                                <span id="modul-filter-count" class="badge badge-primary ml-25 d-none">0</span>
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="modul-btn-refresh">
                                <i class="fa fa-refresh"></i><span class="ml-50 d-none d-sm-inline">Refresh</span>
                            </button>
                        </div>
                        <div id="modul-active-filters" class="nama-modul-active-filters d-none" aria-live="polite"></div>
                    </div>
                    <div class="card-datatable table-responsive">
                        <table id="modul-table" class="table table-striped table-hover mb-0 w-100">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nomor Dokumen</th>
                                    <th>Keterangan</th>
                                    <th>Tanggal</th>
                                    <th class="text-right">Nominal</th>
                                    <th>Status</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>

<script src="<?= base_url('app-assets/vendors/js/vendors.min.js') ?>"></script>
<script src="<?= base_url('app-assets/vendors/js/tables/datatable/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('app-assets/vendors/js/tables/datatable/datatables.bootstrap4.min.js') ?>"></script>
<script src="<?= base_url('app-assets/vendors/js/extensions/sweetalert2.all.min.js') ?>"></script>
<script src="<?= base_url('app-assets/vendors/js/forms/select/select2.full.min.js') ?>"></script>
<script src="<?= base_url('assets/js/nama-modul.js') ?>?v=<?= filemtime(FCPATH . 'assets/js/nama-modul.js') ?>"></script>
```

---

## 3. Blueprint View Halaman Laporan (`app/Views/laporan/<nama-laporan>.php`)

```php
<link rel="stylesheet" type="text/css" href="<?= base_url('app-assets/vendors/css/tables/datatable/dataTables.bootstrap4.min.css') ?>">
<link rel="stylesheet" type="text/css" href="<?= base_url('app-assets/vendors/css/tables/datatable/responsive.bootstrap4.min.css') ?>">
<link rel="stylesheet" type="text/css" href="<?= base_url('assets/css/laporan-nama.css') ?>?v=<?= filemtime(FCPATH . 'assets/css/laporan-nama.css') ?>">

<div class="app-content content nama-report-page">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>

    <section>
        <div class="row">
            <div class="col-12">
                <div class="card nama-filter-card">
                    <div class="card-body">
                        <div class="d-flex flex-wrap justify-content-between align-items-start" style="gap: 1rem;">
                            <div>
                                <div class="divider divider-left mb-50">
                                    <div class="divider-text px-0"><?= esc($title) ?></div>
                                </div>
                                <p class="text-muted mb-0">
                                    Ringkasan laporan proyek <strong><?= esc($activeProyek->nama_proyek ?? 'belum dipilih') ?></strong>.
                                </p>
                            </div>
                            <div class="nama-filter-controls d-flex flex-wrap align-items-end" style="gap: .65rem;">
                                <div class="form-group mb-0">
                                    <label for="filter_year">Tahun</label>
                                    <select id="filter_year" class="form-control">
                                        <!-- opsi tahun -->
                                    </select>
                                </div>
                                <button type="button" id="btn_apply_report" class="btn btn-primary">
                                    <i class="fas fa-filter mr-25"></i> Tampilkan
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div id="report_no_project" class="alert alert-warning" role="alert" <?= $activeProyek ? 'hidden' : '' ?>>
        <i class="fas fa-exclamation-triangle mr-50"></i>
        Pilih proyek aktif terlebih dahulu untuk menampilkan laporan.
    </div>
</div>
```
