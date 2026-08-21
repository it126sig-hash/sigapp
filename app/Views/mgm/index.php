<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/tables/datatable/dataTables.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/tables/datatable/responsive.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/extensions/sweetalert2.min.css">

<style>
    .subrow-table {
        background: #f8f8f8;
    }

    .subrow-table th {
        background: #e0e0e0;
    }

    td.details-control {
        background: url('<?= base_url() ?>assets/images/details_open.png') no-repeat center center;
        cursor: pointer;
        width: 30px;
    }

    tr.shown td.details-control {
        background: url('<?= base_url() ?>assets/images/details_close.png') no-repeat center center;
    }
</style>

<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper">
        <div class="content-header row">
            <div class="content-header-left col-md-9 col-12 mb-2">
                <div class="row breadcrumbs-top">
                    <div class="col-12">
                        <h2 class="content-header-title float-left mb-0">Member Get Member (MGM)</h2>
                    </div>
                </div>
            </div>
            <div class="content-header-right text-md-right col-md-3 col-12 d-md-block d-none">
                <button type="button" class="btn btn-outline-primary" id="btn-setting-stages">
                    <i class="fas fa-cog"></i> Pengaturan Tahapan
                </button>
            </div>
        </div>

        <div class="content-body">
            <section id="basic-datatable">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header border-bottom">
                                <h4 class="card-title">Daftar Referral</h4>
                            </div>
                            <div class="card-datatable">
                                <table class="datatables-mgm table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>Nama Konsumen (Referrer)</th>
                                            <th>Kode Referal</th>
                                            <th>Kavling Dimiliki</th>
                                            <th>Jml Referal</th>
                                            <th>Total Penghasilan</th>
                                            <th>Sudah Cair (Promosi)</th>
                                            <th>Sudah Cair (Keuangan)</th>
                                            <th>Sisa Belum Cair</th>
                                        </tr>
                                    </thead>
                                    <tbody id="mgm-tbody">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>

<?= $this->include('mgm/modal_bonus_action') ?>
<?= $this->include('mgm/modal_setting_stages') ?>

<script src="<?= base_url() ?>app-assets/vendors/js/vendors.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/tables/datatable/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/tables/datatable/datatables.bootstrap4.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/tables/datatable/dataTables.responsive.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/extensions/sweetalert2.all.min.js"></script>

<script src="<?= base_url() ?>assets/js/mgm.js"></script>