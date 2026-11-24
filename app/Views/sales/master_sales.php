<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/tables/datatable/dataTables.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/tables/datatable/responsive.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/extensions/sweetalert2.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/pickers/flatpickr/flatpickr.min.css">

<style>
  .sales-page .card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem;
  }
  .table-sales th, .table-sales td {
    vertical-align: middle !important;
  }
</style>

<div class="app-content content sales-page">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper">
        <div class="content-header row">
            <div class="content-header-left col-md-9 col-12 mb-2">
                <div class="row breadcrumbs-top">
                    <div class="col-12">
                        <h2 class="content-header-title float-left mb-0">Master Sales External</h2>
                        <div class="breadcrumb-wrapper">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?= base_url() ?>">Home</a></li>
                                <li class="breadcrumb-item active">Master Sales</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="content-body">
            <div class="card">
                <div class="card-header border-bottom">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title mb-0"><i class="fas fa-users-cog mr-50 text-primary"></i> Data Master Sales External</h4>
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        <select id="filter-sales-status" class="form-control form-control-sm mr-1" style="width: 140px;">
                            <option value="all">Semua Status</option>
                            <option value="active" selected>Aktif</option>
                            <option value="inactive">Non-Aktif</option>
                        </select>
                        <button type="button" class="btn btn-primary btn-sm" id="btn-add-sales">
                            <i class="fas fa-plus mr-50"></i> Tambah Sales
                        </button>
                    </div>
                </div>
                <div class="card-body pt-2">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped table-sales" id="table-master-sales" style="width: 100%;">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 50px;">No</th>
                                    <th>Nama Sales</th>
                                    <th>Kontak</th>
                                    <th>Bank & Rekening</th>
                                    <th>Fee Subsidi (%)</th>
                                    <th>Fee Komersil (%)</th>
                                    <th style="width: 90px;" class="text-center">Tipe Sales</th>
                                    <th>Periode PKS</th>
                                    <th>Berkas PKS</th>
                                    <th>Status</th>
                                    <th style="width: 110px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah / Edit Sales -->
<div class="modal fade" id="modal-sales" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <form id="form-sales" class="modal-content" enctype="multipart/form-data">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-sales-title">Tambah Sales External</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="sales-id">
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label class="font-weight-bold">Nama Sales / Partner <span class="text-danger">*</span></label>
                        <input type="text" name="nama" id="sales-nama" class="form-control" required placeholder="Contoh: Yenny">
                    </div>
                    <div class="col-md-6 form-group">
                        <label class="font-weight-bold">No. Kontak / HP</label>
                        <input type="text" name="kontak" id="sales-kontak" class="form-control" placeholder="Contoh: 08123456789">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label class="font-weight-bold">Nama Bank</label>
                        <input type="text" name="bank" id="sales-bank" class="form-control" placeholder="Contoh: BCA / Mandiri">
                    </div>
                    <div class="col-md-6 form-group">
                        <label class="font-weight-bold">Nomor Rekening</label>
                        <input type="text" name="no_rek" id="sales-no_rek" class="form-control" placeholder="Contoh: 1234567890">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label class="font-weight-bold">Fee Kavling Subsidi (%) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" max="100" name="fee_subsidi" id="sales-fee_subsidi" class="form-control" required value="1.50">
                        <small class="text-muted">Persentase fee untuk kavling bersubsidi.</small>
                    </div>
                    <div class="col-md-6 form-group">
                        <label class="font-weight-bold">Fee Kavling Komersil (%) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" max="100" name="fee_komersil" id="sales-fee_komersil" class="form-control" required value="2.00">
                        <small class="text-muted">Persentase fee untuk kavling komersil.</small>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label class="font-weight-bold">Tanggal Mulai PKS</label>
                        <input type="text" name="tanggal_pks" id="sales-tanggal_pks" class="form-control flatpickr-basic" placeholder="YYYY-MM-DD">
                    </div>
                    <div class="col-md-6 form-group">
                        <label class="font-weight-bold">Tanggal Berakhir PKS (Opsional)</label>
                        <input type="text" name="tanggal_exp_pks" id="sales-tanggal_exp_pks" class="form-control flatpickr-basic" placeholder="YYYY-MM-DD">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label class="font-weight-bold">Unggah Berkas PKS (PDF/JPG/PNG)</label>
                        <div class="custom-file">
                            <input type="file" name="lampiran_pks" id="sales-lampiran_pks" class="custom-file-input">
                            <label class="custom-file-label" for="sales-lampiran_pks">Pilih file</label>
                        </div>
                        <small id="sales-lampiran_pks-preview" class="text-muted d-block mt-50"></small>
                    </div>
                    <div class="col-md-3 form-group">
                        <label class="font-weight-bold">Tipe Kontrak <span class="text-danger">*</span></label>
                        <select name="has_pks" id="sales-has_pks" class="form-control font-weight-bold">
                            <option value="1">Dengan PKS (Kontrak)</option>
                            <option value="0">Tanpa PKS (Non-Kontrak)</option>
                        </select>
                    </div>
                    <div class="col-md-3 form-group">
                        <label class="font-weight-bold">Status Keaktifan</label>
                        <select name="status" id="sales-status" class="form-control">
                            <option value="active">Aktif</option>
                            <option value="inactive">Non-Aktif</option>
                        </select>
                    </div>
                </div>
                <div class="form-group mb-0">
                    <label class="font-weight-bold">Catatan Khusus</label>
                    <textarea name="catatan" id="sales-catatan" class="form-control" rows="2" placeholder="Catatan tambahan terkait sales / PKS"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary" id="btn-save-sales">
                    <i class="fas fa-save mr-50"></i> Simpan Data Sales
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Riwayat Perubahan Fee & PKS -->
<div class="modal fade" id="modal-sales-history" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-history mr-50 text-primary"></i> Riwayat Perubahan Fee & PKS: <span id="history-sales-nama" class="font-weight-bold"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Waktu Perubahan</th>
                                <th>Fee Subsidi</th>
                                <th>Fee Komersil</th>
                                <th>Periode PKS</th>
                                <th>Catatan</th>
                                <th>Oleh</th>
                            </tr>
                        </thead>
                        <tbody id="history-sales-tbody">
                            <tr><td colspan="6" class="text-center py-2 text-muted">Memuat data riwayat...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script src="<?= base_url() ?>app-assets/vendors/js/vendors.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/tables/datatable/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/tables/datatable/datatables.bootstrap4.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/pickers/flatpickr/flatpickr.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/extensions/sweetalert2.all.min.js"></script>
<script src="<?= base_url() ?>assets/js/sales/master_sales.js?v=<?= time() ?>"></script>
