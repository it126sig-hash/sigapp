<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/tables/datatable/dataTables.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/tables/datatable/responsive.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/extensions/sweetalert2.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/forms/select/select2.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/pickers/flatpickr/flatpickr.min.css">

<style>
    #cashout-kavling-table td {
        vertical-align: top;
    }

    .ck-detail-wrap {
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        margin: .5rem 0;
        padding: .75rem;
    }

    .ck-detail-table {
        background: #fff;
        margin-bottom: 0;
    }

    .ck-detail-empty {
        color: #6b7280;
        font-size: .86rem;
        padding: .35rem;
    }

    .btn-ck-detail {
        border-radius: 6px;
    }
</style>

<script>
    let dt_proyek = {
        id_proyek: <?= (int) ($proyek->id_proyek ?? 0) ?>,
        nama_proyek: <?= json_encode($proyek->nama_proyek ?? '') ?>
    };
    window.editdtt = [];
</script>

<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <section id="cashout-kavling-list">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header border-bottom d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="card-title mb-0"><?= esc($title ?? 'Rekap Cashout per Kavling') ?></h4>
                            <small class="text-muted">Proyek: <?= esc($proyek->nama_proyek ?? '-') ?></small>
                        </div>
                    </div>
                    <div class="card-datatable table-responsive">
                        <table id="cashout-kavling-table" class="datatables-basic table table-hover">
                            <thead>
                                <tr>
                                    <th>Detail</th>
                                    <th>No</th>
                                    <th>Blok / No Kavling</th>
                                    <th>Nama Konsumen</th>
                                    <th>Cashout Keuangan</th>
                                    <th>Pembayaran Produksi</th>
                                    <th>Cashout Subkon</th>
                                    <th>Pajak</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script src="<?= base_url() ?>app-assets/vendors/js/vendors.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/tables/datatable/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/tables/datatable/datatables.bootstrap4.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/tables/datatable/dataTables.responsive.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/tables/datatable/responsive.bootstrap4.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/pickers/flatpickr/flatpickr.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/extensions/sweetalert2.all.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/forms/select/select2.full.min.js"></script>

<?= view('siteplan/keuangan') ?>
<?= view('siteplan/produksi') ?>
<?= view('siteplan/pajak') ?>

<script src="<?= base_url() ?>assets/js/cashout_kavling/index.js?v=<?= filemtime(FCPATH . 'assets/js/cashout_kavling/index.js') ?>"></script>
