<!-- Datatables CSS -->
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/tables/datatable/dataTables.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/tables/datatable/responsive.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/extensions/sweetalert2.min.css">

<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper">
        <div class="content-header row">
            <div class="content-header-left col-md-9 col-12 mb-2">
                <div class="row breadcrumbs-top">
                    <div class="col-12">
                        <h2 class="content-header-title float-left mb-0">Tiket Masalah</h2>
                        <div class="breadcrumb-wrapper col-12">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?= base_url() ?>">Home</a></li>
                                <li class="breadcrumb-item active">Tiket Masalah</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="content-body">
            <section id="basic-datatable">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                                <h4 class="card-title mb-0">Daftar Tiket Masalah Global</h4>
                                <div class="heading-elements d-flex align-items-center gap-2">
                                    <div class="form-group mb-0 mr-1">
                                        <select id="filter_status" class="form-control form-control-sm">
                                            <option value="active" selected>Aktif (Tanpa Selesai/Batal)</option>
                                            <option value="">Semua Status</option>
                                            <option value="dibuat">Dibuat</option>
                                            <option value="dalam_proses">Dalam Proses</option>
                                            <option value="selesai">Selesai</option>
                                            <option value="batal">Batal</option>
                                        </select>
                                    </div>
                                    <div class="form-group mb-0">
                                        <select id="filter_prioritas" class="form-control form-control-sm">
                                            <option value="">Semua Prioritas</option>
                                            <option value="low">Low</option>
                                            <option value="normal">Normal</option>
                                            <option value="medium">Medium</option>
                                            <option value="urgent">Urgent</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="card-content">
                                <div class="card-body card-dashboard">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered dt-responsive nowrap" id="table-tiket-masalah-global" style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th width="50">Aksi</th>
                                                    <th width="20">No</th>
                                                    <th>Lokasi &amp; Foto</th>
                                                    <th>Keterangan</th>
                                                    <th>Tanggal Kunjungan</th>
                                                    <th>Pembuat Laporan</th>
                                                    <th>PIC Penanganan</th>
                                                    <th>Status</th>
                                                    <th>Prioritas</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>

<!-- Sertakan Modal Tiket Masalah -->
<?= view('siteplan/partials/modal_tiket_masalah_styles') ?>
<?= view('siteplan/partials/modal_tiket_masalah') ?>

<!-- Custom CSS if needed -->
<style>
    /* Styling jika diperlukan */
    .table td {
        vertical-align: top;
    }

    .badge-prioritas {
        font-size: 85%;
    }

    .img-thumb-grid {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 4px;
        border: 1px solid #ddd;
    }

    .foto-overlay-count {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        border-radius: 4px;
        font-size: 1.2rem;
    }

    /* Mobile Grid Layout */
    @media (max-width: 767.98px) {
        #table-tiket-masalah-global {
            border: none;
        }
        #table-tiket-masalah-global thead {
            display: none;
        }
        #table-tiket-masalah-global tbody {
            display: block;
        }
        #table-tiket-masalah-global tr {
            display: flex;
            flex-wrap: wrap;
            padding: 15px;
            background: #fff;
            border-radius: 12px;
            margin-bottom: 1rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border: 1px solid #f0f0f0;
            position: relative;
            align-items: flex-start;
        }
        #table-tiket-masalah-global td {
            display: contents; 
        }
        
        .m-aksi {
            position: absolute;
            top: 15px;
            right: 15px;
            z-index: 2;
        }
        .m-no {
            order: 1;
            margin-right: 8px;
            margin-bottom: 10px;
        }
        .m-no .badge {
            background: #f1f3f5;
            color: #888;
            font-weight: bold;
            padding: 6px 10px;
            border-radius: 6px;
        }
        .m-status {
            order: 2;
            margin-right: 8px;
            margin-bottom: 10px;
        }
        .m-prio {
            order: 3;
            margin-right: 8px;
            margin-bottom: 10px;
        }
        .m-lokasi {
            order: 4;
            width: 100%;
            font-size: 1.1rem;
            font-weight: 800;
            margin-bottom: 12px;
            text-transform: uppercase;
        }
        .m-foto {
            order: 5;
            width: 80px;
            margin-right: 15px;
            margin-bottom: 15px;
            position: relative;
        }
        .m-foto img {
            width: 80px;
            height: 80px;
            border-radius: 8px;
            object-fit: cover;
        }
        .m-ket {
            order: 6;
            flex: 1;
            min-width: 0;
            margin-bottom: 15px;
        }
        .m-ket-label {
            font-size: 0.7rem;
            color: #888;
            text-transform: uppercase;
            font-weight: bold;
            margin-bottom: 4px;
            display: block;
        }
        .m-ket-text {
            font-size: 0.95rem;
            margin-bottom: 8px;
            color: #333;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;  
            overflow: hidden;
        }
        .m-ket-update {
            background: #f4f8fd;
            border-radius: 6px;
            padding: 8px 10px;
            border: 1px solid #e1ebf6;
        }
        .m-ket-update-title {
            color: #2b5cbe;
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 2px;
        }
        .m-ket-update-text {
            color: #444;
            font-size: 0.85rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;  
            overflow: hidden;
        }
        .m-ket-update-date {
            color: #2b5cbe;
            font-size: 0.75rem;
            margin-top: 4px;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        
        .m-divider {
            order: 7;
            width: 100%;
            height: 1px;
            background: #f0f0f0;
            margin: 0 0 12px 0;
        }
        
        .m-tgl {
            order: 8;
            width: 50%;
            margin-bottom: 12px;
        }
        .m-pic {
            order: 9;
            width: 50%;
            margin-bottom: 12px;
        }
        .m-pembuat {
            order: 10;
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }
        .m-lbl {
            font-size: 0.75rem;
            color: #888;
            margin-bottom: 2px;
            display: block;
        }
        .m-val {
            font-size: 0.95rem;
            color: #222;
            font-weight: 500;
        }
    }
    
    @media (min-width: 768px) {
        .m-ket-label, .m-lbl {
            display: none;
        }
        .m-ket-update {
            margin-top: 5px;
        }
        .m-no .badge {
            background: none;
            color: inherit;
            font-weight: normal;
            padding: 0;
        }
        .m-pembuat {
            display: block;
        }
        .m-pembuat .m-val-right {
            display: block;
            font-size: 0.8em;
            color: #888;
        }
        .m-divider {
            display: none;
        }
        .m-aksi {
            display: block;
        }
        .m-foto img {
            width: 60px;
            height: 60px;
            border-radius: 4px;
            object-fit: cover;
            border: 1px solid #ddd;
        }
        .m-foto {
            position: relative;
            display: inline-block;
        }
    }
</style>

<!-- Variabel Global untuk Modal Tiket Masalah -->
<script>
    var current_user_id = <?= user_id() ?>;
</script>

<!-- Load Vendor JS (termasuk jQuery) -->
<script src="<?= base_url() ?>app-assets/vendors/js/vendors.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/tables/datatable/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/tables/datatable/datatables.bootstrap4.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/tables/datatable/dataTables.responsive.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/extensions/sweetalert2.all.min.js"></script>
<!-- Load JS Khusus untuk Halaman Ini -->
<script src="<?= base_url('assets/js/tiket_masalah/index.js?v=' . filemtime(FCPATH . 'assets/js/tiket_masalah/index.js')) ?>"></script>
<!-- Memanggil js modal agar fungsinya jalan -->
<script src="<?= base_url('assets/js/siteplan/tiket-masalah.js?v=' . filemtime(FCPATH . 'assets/js/siteplan/tiket-masalah.js')) ?>"></script>