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
                                                    <th>Lokasi</th>
                                                    <th>Keterangan Masalah / Progress</th>
                                                    <th>Status</th>
                                                    <th>Prioritas</th>
                                                    <th>PIC</th>
                                                    <th>Assigned Users</th>
                                                    <th>Tanggal</th>
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
<script src="<?= base_url('assets/js/tiket_masalah/index.js') ?>"></script>
<!-- Memanggil js modal agar fungsinya jalan -->
<script src="<?= base_url('assets/js/siteplan/tiket-masalah.js') ?>"></script>