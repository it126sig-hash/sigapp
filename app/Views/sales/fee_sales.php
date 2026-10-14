<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/tables/datatable/dataTables.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/tables/datatable/responsive.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/extensions/sweetalert2.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/pickers/flatpickr/flatpickr.min.css">

<style>
  .fee-page .card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.6rem;
  }
  .fee-filter-bar {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem;
  }
  .table-fee th, .table-fee td {
    vertical-align: middle !important;
  }
  .termin-progress-mini {
    height: 6px;
    border-radius: 3px;
  }
</style>

<div class="app-content content fee-page">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper">
        <div class="content-header row">
            <div class="content-header-left col-md-9 col-12 mb-2">
                <div class="row breadcrumbs-top">
                    <div class="col-12">
                        <h2 class="content-header-title float-left mb-0">Tracking & Pencairan Fee Sales</h2>
                        <div class="breadcrumb-wrapper">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?= base_url() ?>">Home</a></li>
                                <li class="breadcrumb-item active">Fee Sales</li>
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
                        <h4 class="card-title mb-0"><i class="fas fa-hand-holding-usd mr-50 text-primary"></i> Daftar Fee Sales Konsumen Akad</h4>
                    </div>
                    <div class="fee-filter-bar">
                        <select id="filter-fee-proyek" class="form-control form-control-sm" style="width: 170px;">
                            <option value="">Semua Proyek</option>
                            <?php if (! empty($proyekList)) : ?>
                                <?php foreach ($proyekList as $p) : ?>
                                    <option value="<?= $p->id_proyek ?>"><?= esc($p->nama_proyek) ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>

                        <select id="filter-fee-status" class="form-control form-control-sm" style="width: 160px;">
                            <option value="all">Semua Status Fee</option>
                            <option value="belum_proses">Belum Diproses</option>
                            <option value="proses_spp">Proses SPP</option>
                            <option value="cair_sebagian">Cair Sebagian</option>
                            <option value="selesai">Selesai (Lunas)</option>
                        </select>

                        <select id="filter-fee-ajb" class="form-control form-control-sm" style="width: 140px;">
                            <option value="">Status Akad (Semua)</option>
                            <option value="1">AJB</option>
                            <option value="0">PPJB</option>
                        </select>

                        <button type="button" class="btn btn-outline-primary btn-sm" id="btn-refresh-fee">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                </div>
                <div class="card-body pt-2">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped table-fee" id="table-fee-sales" style="width: 100%;">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 40px;">No</th>
                                    <th>Konsumen & Unit</th>
                                    <th>Harga Net AJB</th>
                                    <th>Sales External</th>
                                    <th>Akad</th>
                                    <th>Pencairan Keuangan</th>
                                    <th>Total Fee</th>
                                    <th>Status Termin</th>
                                    <th style="width: 100px;">Aksi</th>
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

<!-- Modal Kelola Fee & Pencairan -->
<div class="modal fade" id="modal-fee-sales" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-calculator mr-50 text-primary"></i> Kelola Fee Sales & Termin Pencairan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Info Konsumen & Kavling -->
                <div class="card bg-light mb-2 border">
                    <div class="card-body py-1">
                        <div class="row">
                            <div class="col-md-4">
                                <small class="text-muted d-block">Nama Konsumen:</small>
                                <span class="font-weight-bold font-medium-1 text-dark" id="modal-info-konsumen">-</span>
                                <small class="text-secondary d-block mt-25" id="modal-info-hp">-</small>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted d-block">Unit Kavling:</small>
                                <span class="font-weight-bold font-medium-1 text-primary" id="modal-info-unit">-</span>
                                <small class="text-secondary d-block mt-25" id="modal-info-proyek">-</small>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted d-block">Realisasi Pencairan Akad Keuangan:</small>
                                <div class="d-flex align-items-center mt-25">
                                    <div class="progress progress-bar-primary flex-grow-1 mr-50" style="height: 10px;">
                                        <div id="modal-keu-progress" class="progress-bar" role="progressbar" style="width: 0%;"></div>
                                    </div>
                                    <span class="font-weight-bold font-small-3" id="modal-keu-percent">0%</span>
                                </div>
                                <small class="text-muted" id="modal-keu-nominal">Rp 0 / Rp 0</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Kalkulasi Fee -->
                <form id="form-fee-calc" class="border rounded p-2 mb-2 bg-white">
                    <input type="hidden" name="id_mkdt" id="calc-id_mkdt">
                    <h6 class="font-weight-bold mb-1 text-primary"><i class="fas fa-coins mr-50"></i> Konfigurasi Fee & Skema Termin</h6>
                    <div class="row">
                        <div class="col-md-3 form-group">
                            <label class="font-weight-bold">Harga Net AJB (Rp) <span class="text-danger">*</span></label>
                            <input type="text" name="harga_net_ajb" id="calc-harga_net_ajb" class="form-control font-weight-bold text-right num-input" required>
                            <small class="text-muted font-small-2">Sesuai nilai pada akta AJB.</small>
                        </div>
                        <div class="col-md-2 form-group">
                            <label class="font-weight-bold">Komisi (%) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" step="0.01" min="0" max="100" name="persentase_fee" id="calc-persentase_fee" class="form-control font-weight-bold text-right" required value="1.50">
                                <div class="input-group-append"><span class="input-group-text">%</span></div>
                            </div>
                            <small class="text-muted font-small-2" id="calc-tarif-hint">Subsidi 1.5% / Komersil 2%</small>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="font-weight-bold">Total Fee Sales (Rp)</label>
                            <div class="input-group">
                                <input type="text" name="total_fee" id="calc-total_fee" class="form-control font-weight-bold text-right num-input" readonly>
                                <div class="input-group-append">
                                    <div class="input-group-text">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="calc-override_fee" name="total_fee_manual" value="1">
                                            <label class="custom-control-label font-small-2" for="calc-override_fee">Override</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <small class="text-muted font-small-2">Dihitung otomatis: Harga AJB x % Komisi.</small>
                        </div>
                        <div class="col-md-3 form-group">
                            <label class="font-weight-bold">Skema Termin</label>
                            <select name="skema_termin" id="calc-skema_termin" class="form-control font-weight-bold">
                                <option value="100">100% (Sekaligus)</option>
                                <option value="30_60_10">30% - 60% - 10% (Bertahap)</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-8 form-group mb-0">
                            <label>Catatan / Memo Khusus</label>
                            <input type="text" name="catatan" id="calc-catatan" class="form-control" placeholder="Contoh: Fee dipotong retensi dokumen atau memo pimpinan">
                        </div>
                        <div class="col-md-4 form-group mb-0 d-flex align-items-end justify-content-end">
                            <button type="submit" class="btn btn-primary" id="btn-save-calc">
                                <i class="fas fa-save mr-50"></i> Simpan & Generate Termin
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Tabel Termin Pencairan -->
                <div class="border rounded p-2 bg-white">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <h6 class="font-weight-bold mb-0 text-primary"><i class="fas fa-list-ol mr-50"></i> Rincian Termin & Pencairan Dana</h6>
                        <span id="termin-summary-badge" class="badge badge-light-primary">Total: Rp 0</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped mb-0" id="table-termin">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 80px;">Termin</th>
                                    <th style="width: 80px;">Porsi (%)</th>
                                    <th>Nominal (Rp)</th>
                                    <th>Status</th>
                                    <th>No & Tgl SPP</th>
                                    <th>Tgl Cair</th>
                                    <th>Berkas</th>
                                    <th style="width: 140px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-termin">
                                <tr><td colspan="8" class="text-center py-2 text-muted">Belum ada termin dibangkitkan.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Pengajuan SPP -->
<div class="modal fade" id="modal-spp" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form id="form-spp" class="modal-content" enctype="multipart/form-data">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-file-invoice-dollar mr-50 text-warning"></i> Pengajuan SPP: <span id="spp-termin-label" class="font-weight-bold"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_termin" id="spp-id_termin">
                <div class="form-group">
                    <label class="font-weight-bold">Nominal Termin</label>
                    <input type="text" id="spp-nominal-display" class="form-control font-weight-bold text-right" readonly>
                </div>
                <div class="form-group">
                    <label class="font-weight-bold">Nomor SPP <span class="text-danger">*</span></label>
                    <input type="text" name="nomor_spp" id="spp-nomor_spp" class="form-control" required placeholder="Contoh: SPP/UMUM/2026/X/001">
                </div>
                <div class="form-group">
                    <label class="font-weight-bold">Tanggal Pengajuan SPP <span class="text-danger">*</span></label>
                    <input type="text" name="tanggal_pengajuan_spp" id="spp-tanggal" class="form-control flatpickr-basic" required placeholder="YYYY-MM-DD">
                </div>
                <div class="form-group">
                    <label class="font-weight-bold">Unggah Berkas SPP (PDF/JPG/PNG)</label>
                    <div class="custom-file">
                        <input type="file" name="lampiran_spp" id="spp-file" class="custom-file-input">
                        <label class="custom-file-label" for="spp-file">Pilih file</label>
                    </div>
                </div>
                <div class="form-group mb-0">
                    <label>Keterangan Tambahan</label>
                    <textarea name="keterangan" id="spp-keterangan" class="form-control" rows="2" placeholder="Catatan pengajuan SPP"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-warning" id="btn-submit-spp">
                    <i class="fas fa-paper-plane mr-50"></i> Kirim Pengajuan SPP
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Konfirmasi Pencairan Dana -->
<div class="modal fade" id="modal-cair" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form id="form-cair" class="modal-content" enctype="multipart/form-data">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-check-circle mr-50 text-success"></i> Konfirmasi Pencairan: <span id="cair-termin-label" class="font-weight-bold"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_termin" id="cair-id_termin">
                <div class="form-group">
                    <label class="font-weight-bold">Nominal Cair</label>
                    <input type="text" id="cair-nominal-display" class="form-control font-weight-bold text-right text-success" readonly>
                </div>
                <div class="form-group">
                    <label class="font-weight-bold">Tanggal Realisasi Cair <span class="text-danger">*</span></label>
                    <input type="text" name="tanggal_cair" id="cair-tanggal" class="form-control flatpickr-basic" required placeholder="YYYY-MM-DD">
                </div>
                <div class="form-group">
                    <label class="font-weight-bold">Unggah Bukti Transfer / Pencairan</label>
                    <div class="custom-file">
                        <input type="file" name="lampiran_bukti_cair" id="cair-file" class="custom-file-input">
                        <label class="custom-file-label" for="cair-file">Pilih file</label>
                    </div>
                </div>
                <div class="form-group mb-0">
                    <label>Keterangan Pencairan</label>
                    <textarea name="keterangan" id="cair-keterangan" class="form-control" rows="2" placeholder="Nomor referensi bank / transfer"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-success" id="btn-submit-cair">
                    <i class="fas fa-check mr-50"></i> Konfirmasi Dana Cair
                </button>
            </div>
        </form>
    </div>
</div>

<script src="<?= base_url() ?>app-assets/vendors/js/vendors.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/tables/datatable/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/tables/datatable/datatables.bootstrap4.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/pickers/flatpickr/flatpickr.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/extensions/sweetalert2.all.min.js"></script>
<script src="<?= base_url() ?>assets/js/sales/fee_sales.js?v=<?= time() ?>"></script>
