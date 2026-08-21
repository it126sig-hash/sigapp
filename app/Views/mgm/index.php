<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/tables/datatable/dataTables.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/tables/datatable/responsive.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/extensions/sweetalert2.min.css">

<style>
    td.details-control {
        cursor: pointer;
        width: 30px;
    }

    .subrow-wrapper {
        background: #f4f5f7;
        padding: 1rem;
        border-radius: 0.5rem;
    }

    .subrow-table {
        background: #ffffff;
    }

    .subrow-table th {
        background: #f8f8f8;
        font-size: 0.8rem;
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
                                            <th>NAMA KONSUMEN (KAVLING DIMILIKI)</th>
                                            <th>KODE REFERAL</th>
                                            <th>JML REFERAL</th>
                                            <th>TOTAL PENGHASILAN</th>
                                            <th>CAIR KE MEMBER (PROMOSI)</th>
                                            <th>SUDAH CAIR (KEUANGAN)</th>
                                            <th>SISA BELUM CAIR</th>
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


<?= $this->include('mgm/modal_setting_stages') ?>
<?= $this->include('mgm/modal_detail_pencairan') ?>

<script src="<?= base_url() ?>app-assets/vendors/js/vendors.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/tables/datatable/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/tables/datatable/datatables.bootstrap4.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/tables/datatable/dataTables.responsive.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/extensions/sweetalert2.all.min.js"></script>

<script src="<?= base_url() ?>assets/js/mgm.js?v=<?= time() ?>"></script>