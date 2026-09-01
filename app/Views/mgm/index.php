<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/tables/datatable/dataTables.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/tables/datatable/responsive.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/extensions/sweetalert2.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/forms/select/select2.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>assets/css/mgm.css?v=<?= time() ?>">
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
                            <div class="card-header border-bottom d-flex flex-wrap align-items-center justify-content-between">
                                <h4 class="card-title">Daftar Referral</h4>
                                <div class="d-flex flex-wrap align-items-center">
                                    <div class="small text-muted mr-1" id="mgm-filter-summary">Semua data</div>
                                    <button type="button" class="btn btn-outline-primary" id="btn-filter-mgm">
                                        <i class="fas fa-filter mr-50"></i> Filter Data
                                        <span class="badge badge-light-primary ml-50 d-none" id="mgm-filter-count">0</span>
                                    </button>
                                </div>
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
                                            <th class="text-right">CAIR DARI KEUANGAN</th>
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

<div class="modal modal-slide-in fade" id="modalFilterMgm" tabindex="-1" role="dialog" aria-labelledby="modalFilterMgmTitle" aria-hidden="true">
    <div class="modal-dialog sidebar-sm" role="document">
        <div class="add-new-record modal-content pt-0 mgm-filter-modal">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">x</button>
            <div class="modal-header mb-1">
                <h5 class="modal-title" id="modalFilterMgmTitle">Filter Data MGM</h5>
            </div>
            <div class="modal-body flex-grow-1">
                <div class="form-group">
                    <label class="text-muted font-weight-bold mgm-form-label">PEMILIK KODE REFERAL</label>
                    <select id="filter_mgm_referrer" class="form-control" data-placeholder="Kode atau nama pemilik referal"></select>
                    <small class="text-muted">Hanya menampilkan pemilik kode yang sudah mengajak member lain.</small>
                </div>
                <div class="form-group">
                    <label class="text-muted font-weight-bold mgm-form-label">STATUS ACUAN</label>
                    <select id="filter_mgm_status" class="form-control">
                        <option value="">Semua Status</option>
                        <option value="booking">Booking - Tgl Booking</option>
                        <option value="akad">Akad - Tgl Akad</option>
                        <option value="cair_bonus_booking">Cair Bonus Booking - Tgl Cair</option>
                        <option value="cair_bonus_akad">Cair Bonus Akad - Tgl Cair</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="text-muted font-weight-bold mgm-form-label">TANGGAL MULAI</label>
                    <input type="text" class="form-control flatpickr-human-friendly" id="filter_mgm_tanggal_mulai">
                </div>
                <div class="form-group">
                    <label class="text-muted font-weight-bold mgm-form-label">TANGGAL SELESAI</label>
                    <input type="text" class="form-control flatpickr-human-friendly" id="filter_mgm_tanggal_selesai">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="btn-reset-filter-mgm" class="btn btn-outline-secondary">Reset</button>
                <button type="button" id="btn-apply-filter-mgm" class="btn btn-primary">
                    <i class="fas fa-check mr-50"></i> Terapkan
                </button>
            </div>
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
<script src="<?= base_url() ?>app-assets/vendors/js/forms/select/select2.full.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/pickers/flatpickr/flatpickr.min.js"></script>

<script>
    window.SIGAPP = window.SIGAPP || {};
    window.SIGAPP.mgmRoles = {
        canPromosi: <?= in_groups(['1', '8']) ? 'true' : 'false' ?>,
        canKeuangan: <?= in_groups(['1', '3']) ? 'true' : 'false' ?>
    };
</script>
<script src="<?= base_url() ?>assets/js/mgm.js?v=<?= time() ?>"></script>
