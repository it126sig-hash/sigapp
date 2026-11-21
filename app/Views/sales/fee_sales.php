<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/tables/datatable/dataTables.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/tables/datatable/responsive.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/forms/select/select2.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/extensions/sweetalert2.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/pickers/flatpickr/flatpickr.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url('assets/css/fee-sales.css') ?>?v=<?= filemtime(FCPATH . 'assets/css/fee-sales.css') ?>">

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
            <!-- Nav Tabs -->
            <ul class="nav nav-tabs mb-2" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active font-weight-bold" id="tab-fee-list" data-toggle="tab" href="#pane-fee-list" role="tab" aria-selected="true">
                        <i class="fas fa-list mr-50"></i> Daftar Fee Sales & Unit Akad
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link font-weight-bold" id="tab-fee-group" data-toggle="tab" href="#pane-fee-group" role="tab" aria-selected="false">
                        <i class="fas fa-layer-group mr-50"></i> Pengajuan Pencairan
                    </a>
                </li>
            </ul>

            <div class="tab-content">
                <!-- Tab 1: Daftar Utama -->
                <div class="tab-pane active" id="pane-fee-list" role="tabpanel" aria-labelledby="tab-fee-list">
                    <div class="card">
                        <div class="card-header border-bottom">
                            <div class="d-flex align-items-center">
                                <h4 class="card-title mb-0"><i class="fas fa-hand-holding-usd mr-50 text-primary"></i> Daftar Fee Sales Konsumen Akad</h4>
                            </div>
                            <div class="fee-filter-bar">
                                <button type="button" class="btn btn-warning btn-sm font-weight-bold mr-50" id="btn-batch-pengajuan" style="display: none;">
                                    <i class="fas fa-paper-plane mr-25"></i> Ajukan Pencairan (<span id="batch-count-badge">0</span>)
                                </button>

                                <select id="filter-fee-sales" class="form-control form-control-sm" style="width: 170px;">
                                    <option value="">Semua Sales</option>
                                </select>

                                <select id="filter-fee-status" class="form-control form-control-sm" style="width: 175px;">
                                    <option value="belum_lunas" selected>Semua Kecuali Lunas</option>
                                    <option value="all">Semua Status Fee</option>
                                    <option value="belum_proses">Belum Diproses</option>
                                    <option value="proses_spp">Proses SPP</option>
                                    <option value="cair_sebagian">Cair Sebagian</option>
                                    <option value="selesai">Selesai (Lunas)</option>
                                </select>

                                <select id="filter-fee-ajb" class="form-control form-control-sm" style="width: 140px;">
                                    <option value="">Status Akad (Semua)</option>
                                    <option value="AJB">AJB</option>
                                    <option value="PPJB">PPJB</option>
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
                                            <th style="width: 70px;" class="text-center">
                                                <div class="d-flex align-items-center justify-content-center">
                                                    <div class="custom-control custom-checkbox mr-50">
                                                        <input type="checkbox" class="custom-control-input" id="check-all-fee">
                                                        <label class="custom-control-label" for="check-all-fee"></label>
                                                    </div>
                                                    <span>No</span>
                                                </div>
                                            </th>
                                            <th style="width: 85px;" class="text-center">Aksi</th>
                                            <th class="text-left" style="min-width: 180px;">Konsumen & Unit</th>
                                            <th>Harga Jual</th>
                                            <th>Harga Jual Net</th>
                                            <th>Harga Jual Net AJB</th>
                                            <th>Sales External</th>
                                            <th>Akad</th>
                                            <th>Pencairan Keuangan</th>
                                            <th>Total Fee</th>
                                            <th>Status Termin</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab 2: Pengajuan Pencairan -->
                <div class="tab-pane" id="pane-fee-group" role="tabpanel" aria-labelledby="tab-fee-group">
                    <div class="card">
                        <div class="card-header border-bottom">
                            <div class="d-flex align-items-center">
                                <h4 class="card-title mb-0"><i class="fas fa-file-invoice-dollar mr-50 text-warning"></i> Daftar Pengajuan Pencairan Fee</h4>
                            </div>
                            <div class="fee-filter-bar">
                                <select id="filter-group-status" class="form-control form-control-sm" style="width: 170px;">
                                    <option value="all">Semua Status</option>
                                    <option value="diajukan">Diajukan</option>
                                    <option value="ditolak">Ditolak / Revisi</option>
                                    <option value="cair_sebagian">Cair Sebagian</option>
                                    <option value="selesai">Selesai (Tuntas)</option>
                                    <option value="void">Void / Dibatalkan</option>
                                </select>

                                <button type="button" class="btn btn-outline-primary btn-sm" id="btn-refresh-group">
                                    <i class="fas fa-sync-alt"></i> Refresh
                                </button>
                            </div>
                        </div>
                        <div class="card-body pt-2">
                            <!-- Banner Notifikasi Pengajuan Ditolak untuk Pembuat Pengajuan -->
                            <div class="alert alert-danger mb-2 fee-reject-banner" id="creator-rejected-notif-banner" style="display: none;">
                                <div class="alert-body d-flex align-items-center justify-content-between flex-wrap">
                                    <div class="d-flex align-items-center mb-50 mb-md-0">
                                        <i class="fas fa-exclamation-triangle font-medium-3 mr-1 text-danger"></i>
                                        <div>
                                            <strong>Perhatian: Ada pengajuan fee Anda yang ditolak / memerlukan revisi harga!</strong>
                                            <p class="mb-0 font-small-2 text-dark" id="creator-rejected-banner-text">Terdapat <span id="creator-rejected-count" class="font-weight-bold">0</span> pengajuan berstatus ditolak. Buka detail pengajuan untuk melihat catatan penolakan dan memperbarui nominal pencairan tiap termin sesuai harga rekomendasi.</p>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-danger font-weight-bold" id="btn-filter-rejected-group">
                                        <i class="fas fa-filter mr-25"></i> Tampilkan Yang Ditolak
                                    </button>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover table-striped" id="table-pengajuan-group" style="width: 100%;">
                                    <thead class="thead-light">
                                        <tr>
                                            <th style="width: 35px;">No</th>
                                            <th>Tanggal & Pembuat</th>
                                            <th>No Pengajuan</th>
                                            <th>Sales & Tgl Sales</th>
                                            <th>Jumlah Unit</th>
                                            <th>Total Pengajuan</th>
                                            <th>TTD MKDT</th>
                                            <th>TTD Keuangan</th>
                                            <th>Progress Pencairan</th>
                                            <th>Status</th>
                                            <th style="width: 150px;">Aksi</th>
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
    </div>
</div>

<!-- Modal Buat Pengajuan Pencairan Fee -->
<div class="modal fade" id="modal-create-group" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <form id="form-create-group" class="modal-content" enctype="multipart/form-data">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-paper-plane mr-50 text-warning"></i> Form Pengajuan Pencairan Fee Sales</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-3 form-group">
                        <label class="font-weight-bold">Tgl Pengajuan Sales <span class="text-danger">*</span></label>
                        <input type="text" name="tanggal_pengajuan_sales" id="batch-tgl_pengajuan_sales" class="form-control flatpickr-basic" required placeholder="YYYY-MM-DD">
                    </div>
                    <div class="col-md-3 form-group">
                        <label class="font-weight-bold">Tgl Pengajuan Pencairan <span class="text-danger">*</span></label>
                        <input type="text" name="tanggal_pengajuan" id="batch-tgl_pengajuan" class="form-control flatpickr-basic" required placeholder="YYYY-MM-DD">
                    </div>
                    <div class="col-md-3 form-group">
                        <label class="font-weight-bold">No Pengajuan</label>
                        <input type="text" name="nomor_pengajuan" id="batch-nomor_pengajuan" class="form-control" placeholder="Otomatis jika dikosongkan">
                    </div>
                    <div class="col-md-3 form-group">
                        <label class="font-weight-bold">Lampiran Berkas (Opsional)</label>
                        <div class="custom-file">
                            <input type="file" name="lampiran_spp" id="batch-lampiran_spp" class="custom-file-input">
                            <label class="custom-file-label" for="batch-lampiran_spp">Pilih file</label>
                        </div>
                    </div>
                    <div class="col-12 form-group">
                        <label>Catatan Pengajuan</label>
                        <input type="text" name="catatan" id="batch-catatan" class="form-control" placeholder="Contoh: Pengajuan Fee Sales Periode Awal Bulan">
                    </div>
                </div>

                <div class="border rounded p-2 bg-light">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <h6 class="font-weight-bold mb-0 text-primary"><i class="fas fa-list-ol mr-50"></i> Daftar Unit & Termin yang Diajukan</h6>
                        <span id="batch-summary-badge" class="badge badge-primary font-medium-1">Total: Rp 0</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped bg-white mb-0" id="table-batch-prepare">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 40px;">No</th>
                                    <th>Konsumen & Unit</th>
                                    <th>Sales External</th>
                                    <th>Harga Jual Net AJB</th>
                                    <th>Total Fee</th>
                                    <th style="min-width: 260px;">Pilih Termin Diajukan</th>
                                    <th style="width: 150px;" class="text-right">Nominal Diajukan</th>
                                    <th style="width: 50px;" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-batch-prepare">
                                <tr><td colspan="8" class="text-center py-2 text-muted">Memuat kavling terpilih...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-2" id="batch-submit-signature">
                    <div class="divider divider-left"><div class="divider-text font-weight-bold text-primary"><i class="fas fa-signature mr-50"></i>Tanda Tangan Pembuat Pengajuan</div></div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Metode Tanda Tangan <span class="text-danger">*</span></label>
                            <select class="form-control" id="batch-sign-method" name="signature_method">
                                <option value="canvas">Gambar sekarang di layar</option>
                                <option value="profile">Gunakan tanda tangan profil akun</option>
                            </select>
                        </div>
                    </div>
                    <div id="batch-sign-canvas-section">
                        <label class="font-weight-bold">Area Tanda Tangan <span class="text-danger">*</span></label>
                        <div class="fee-canvas-wrap" id="batch-form-canvas-wrap">
                            <canvas id="batch-sign-canvas"></canvas>
                            <button type="button" class="btn btn-sm btn-outline-secondary fee-clear-canvas" id="btn-clear-batch-canvas">Hapus</button>
                        </div>
                        <small class="text-muted d-block mt-50">Gunakan mouse atau sentuhan jari/stylus di atas area canvas untuk menggambar tanda tangan.</small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-warning font-weight-bold" id="btn-submit-batch">
                    <i class="fas fa-paper-plane mr-50"></i> Simpan & Kirim Pengajuan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Detail & Kelola Pengajuan Pencairan -->
<div class="modal fade" id="modal-group-detail" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-folder-open mr-50 text-primary"></i> Detail Pengajuan Pencairan Fee: <span id="detail-group-title" class="font-weight-bold"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Alert jika Pengajuan Dibatalkan / Void -->
                <div class="alert alert-danger mb-2" id="detail-group-void-alert" style="display: none;">
                    <div class="alert-body font-small-3">
                        <div class="d-flex align-items-center mb-25">
                            <i class="fas fa-ban mr-50 font-medium-2"></i>
                            <strong>Pengajuan ini telah dibatalkan (VOID)</strong>
                        </div>
                        <div><strong>Alasan:</strong> <span id="detail-group-void-reason">-</span></div>
                        <div class="mt-25 font-small-2 text-dark" id="detail-group-void-meta">-</div>
                    </div>
                </div>

                <!-- Alert jika Pengajuan Ditolak / Memerlukan Revisi Harga -->
                <div class="alert alert-danger mb-2" id="detail-group-reject-alert" style="display: none;">
                    <div class="alert-body font-small-3">
                        <div class="d-flex align-items-center justify-content-between flex-wrap mb-50">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-exclamation-triangle mr-50 font-medium-2 text-danger"></i>
                                <strong>Pengajuan Ditolak & Memerlukan Revisi Harga Net AJB</strong>
                            </div>
                            <span class="badge badge-danger" id="detail-group-reject-role-badge">Ditolak</span>
                        </div>
                        <div class="reject-meta-box mb-75 text-dark">
                            <div><strong>Ditolak Oleh:</strong> <span id="detail-group-reject-by">-</span> (<span id="detail-group-reject-role">-</span>) pada <span id="detail-group-reject-at">-</span></div>
                            <div class="mt-25"><strong>Keterangan / Alasan:</strong> <span id="detail-group-reject-reason" class="font-italic">-</span></div>
                        </div>
                        <div id="detail-group-reject-prices-section" class="mb-75" style="display: none;">
                            <strong class="d-block mb-25 text-danger"><i class="fas fa-tag mr-25"></i> Saran Perubahan Harga Jual Net AJB:</strong>
                            <div id="detail-group-reject-prices-list" class="font-small-2"></div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between flex-wrap pt-50 border-top">
                            <span class="text-muted font-small-2 mb-25 mb-md-0"><i class="fas fa-info-circle mr-25"></i> Menerapkan saran akan memperbarui nominal tiap termin dan mengembalikan status pengajuan ke <strong>Diajukan</strong>.</span>
                            <button type="button" class="btn btn-warning btn-sm font-weight-bold" id="btn-apply-reject-recalc">
                                <i class="fas fa-sync-alt mr-25"></i> Perbaharui Nominal Pencairan Tiap Termin
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card bg-light mb-2 border">
                    <div class="card-body py-1">
                        <div class="row align-items-center">
                            <div class="col-md-2">
                                <small class="text-muted d-block">No Pengajuan:</small>
                                <span class="font-weight-bold text-dark font-medium-1" id="detail-group-nomor">-</span>
                            </div>
                            <div class="col-md-2">
                                <small class="text-muted d-block">Tgl Pengajuan Sales:</small>
                                <span class="font-weight-bold text-dark" id="detail-group-tanggal-sales">-</span>
                            </div>
                            <div class="col-md-2">
                                <small class="text-muted d-block">Tgl Pengajuan Cair:</small>
                                <span class="font-weight-bold text-dark" id="detail-group-tanggal">-</span>
                            </div>
                            <div class="col-md-2">
                                <small class="text-muted d-block">Status / Progress:</small>
                                <div id="detail-group-status-badge">-</div>
                            </div>
                            <div class="col-md-4 text-right">
                                <div class="btn-group btn-group-sm mb-25">
                                    <button type="button" class="btn btn-outline-info font-weight-bold" id="btn-share-group-detail" title="Salin link / bagikan pengajuan">
                                        <i class="fas fa-share-alt mr-25"></i> Bagikan Link
                                    </button>
                                    <a href="#" target="_blank" class="btn btn-outline-danger font-weight-bold" id="btn-export-group-pdf" title="Unduh format PDF">
                                        <i class="fas fa-file-pdf mr-25"></i> Export PDF
                                    </a>
                                </div>
                                <div class="mt-25">
                                    <button type="button" class="btn btn-outline-danger btn-sm font-weight-bold mr-25" id="btn-reject-mkdt-detail" style="display: none;">
                                        <i class="fas fa-times-circle mr-25"></i> Tolak & Saran Harga
                                    </button>
                                    <button type="button" class="btn btn-primary btn-sm font-weight-bold mr-25" id="btn-sign-mkdt-detail" style="display: none;">
                                        <i class="fas fa-pen-nib mr-25"></i> TTD MKDT
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-sm font-weight-bold mr-25" id="btn-reject-keuangan-detail" style="display: none;">
                                        <i class="fas fa-times-circle mr-25"></i> Tolak & Saran Harga
                                    </button>
                                    <button type="button" class="btn btn-info btn-sm font-weight-bold mr-25" id="btn-sign-keuangan-detail" style="display: none;">
                                        <i class="fas fa-pen-nib mr-25"></i> TTD Keuangan
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-sm font-weight-bold mr-25" id="btn-void-group-detail" style="display: none;">
                                        <i class="fas fa-ban mr-25"></i> Void
                                    </button>
                                    <button type="button" class="btn btn-success btn-sm font-weight-bold" id="btn-batch-cair-modal">
                                        <i class="fas fa-check-double mr-25"></i> Cairkan Sekaligus
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3 Kartu Status TTD (Pembuat, MKDT, Keuangan) -->
                <div class="row mb-2" id="detail-group-signatures-row">
                    <div class="col-md-4">
                        <div class="fee-signature-card" id="detail-card-ttd-pembuat">
                            <div class="fee-signature-card-head">
                                <span>Diajukan Oleh</span>
                                <span class="badge badge-success" id="badge-ttd-pembuat">Pembuat</span>
                            </div>
                            <div class="fee-signature-image" id="img-ttd-pembuat">
                                <span class="text-muted font-small-2">Belum ada tanda tangan</span>
                            </div>
                            <span class="fee-signature-name" id="name-ttd-pembuat">-</span>
                            <span class="fee-signature-date" id="date-ttd-pembuat">-</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="fee-signature-card" id="detail-card-ttd-mkdt">
                            <div class="fee-signature-card-head">
                                <span>Diperiksa (MKDT)</span>
                                <span class="badge badge-secondary" id="badge-ttd-mkdt">Belum TTD</span>
                            </div>
                            <div class="fee-signature-image" id="img-ttd-mkdt">
                                <span class="text-muted font-small-2">Belum ada tanda tangan</span>
                            </div>
                            <span class="fee-signature-name" id="name-ttd-mkdt">-</span>
                            <span class="fee-signature-date" id="date-ttd-mkdt">-</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="fee-signature-card" id="detail-card-ttd-keuangan">
                            <div class="fee-signature-card-head">
                                <span>Disetujui (Keuangan)</span>
                                <span class="badge badge-secondary" id="badge-ttd-keuangan">Belum TTD</span>
                            </div>
                            <div class="fee-signature-image" id="img-ttd-keuangan">
                                <span class="text-muted font-small-2">Belum ada tanda tangan</span>
                            </div>
                            <span class="fee-signature-name" id="name-ttd-keuangan">-</span>
                            <span class="fee-signature-date" id="date-ttd-keuangan">-</span>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="table-group-items">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 35px;" class="text-center">No</th>
                                <th class="text-right">Harga Jual</th>
                                <th class="text-right">Harga Jual Net</th>
                                <th class="text-right">Harga Jual Net AJB</th>
                                <th class="text-center" style="width: 65px;">% Fee</th>
                                <th class="text-right">Total Fee</th>
                                <th class="text-right" style="width: 95px;">Termin 30%</th>
                                <th class="text-right" style="width: 95px;">Termin 60%</th>
                                <th class="text-right" style="width: 95px;">Termin 10%</th>
                                <th class="text-right" style="width: 95px;">Termin 100%</th>
                                <th class="text-center" style="width: 140px;">Status & No Cek</th>
                                <th class="text-center" style="width: 95px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-group-items">
                            <tr><td colspan="12" class="text-center py-2 text-muted">Memuat item...</td></tr>
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

<!-- Modal Pencairan Massal -->
<div class="modal fade" id="modal-batch-cair" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form id="form-batch-cair" class="modal-content" enctype="multipart/form-data">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-check-double mr-50 text-success"></i> Konfirmasi Pencairan Massal</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_pengajuan" id="batch-cair-id_pengajuan">
                <div class="alert alert-info py-75 mb-2 font-small-3">
                    <i class="fas fa-info-circle mr-50"></i> Akan mencairkan seluruh termin yang belum cair pada pengajuan ini.
                </div>
                <div class="form-group">
                    <label class="font-weight-bold">Tanggal Realisasi Cair <span class="text-danger">*</span></label>
                    <input type="text" name="tanggal_cair" id="batch-cair-tanggal" class="form-control flatpickr-basic" required placeholder="YYYY-MM-DD">
                </div>
                <div class="form-group">
                    <label class="font-weight-bold">Nomor Cek / Bilyet Giro</label>
                    <input type="text" name="no_cek" id="batch-cair-no_cek" class="form-control" placeholder="Contoh: CK-889212">
                    <small class="text-muted font-small-2">Nomor cek yang dicantumkan pada seluruh item yang dicairkan.</small>
                </div>
                <div class="form-group">
                    <label class="font-weight-bold">Unggah Bukti Transfer / Pencairan (Opsional)</label>
                    <div class="custom-file">
                        <input type="file" name="lampiran_bukti_cair" id="batch-cair-file" class="custom-file-input">
                        <label class="custom-file-label" for="batch-cair-file">Pilih file</label>
                    </div>
                </div>
                <div class="form-group mb-0">
                    <label>Keterangan Pencairan</label>
                    <textarea name="keterangan" id="batch-cair-keterangan" class="form-control" rows="2" placeholder="Keterangan transfer massal"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-success" id="btn-submit-batch-cair">
                    <i class="fas fa-check mr-50"></i> Konfirmasi Cair Semua
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Kelola Fee & Pencairan -->
<div class="modal fade" id="modal-fee-sales" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-calculator mr-50 text-primary"></i> Kelola Fee Sales & Termin Pencairan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Info Konsumen & Kavling + Harga Jual Net AJB Seragam -->
                <div class="card bg-light mb-1 border modal-sticky-header">
                    <div class="card-body py-1">
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <small class="text-muted d-block">Nama Konsumen:</small>
                                <span class="font-weight-bold font-medium-1 text-dark" id="modal-info-konsumen">-</span>
                                <small class="text-secondary d-block mt-25" id="modal-info-hp">-</small>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted d-block">Unit Kavling:</small>
                                <span class="font-weight-bold font-medium-1 text-primary" id="modal-info-unit">-</span>
                                <small class="text-secondary d-block mt-25" id="modal-info-proyek">-</small>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted d-block font-weight-bold">Harga Jual Net AJB (Kavling):</small>
                                <div class="input-group input-group-sm mt-25">
                                    <div class="input-group-prepend"><span class="input-group-text font-small-2">Rp</span></div>
                                    <input type="text" id="modal-shared-harga-ajb" class="form-control font-weight-bold text-right num-input" placeholder="0">
                                </div>
                                <button type="button" class="btn btn-outline-warning btn-sm font-small-1 font-weight-bold mt-25 w-100" id="btn-recalc-modal-termin" style="display: none;">
                                    <i class="fas fa-sync-alt mr-25"></i> Perbaharui Nominal Tiap Termin
                                </button>
                                <div id="modal-unit-rejection-hint" class="py-25 px-50 font-small-1 mt-25 rounded" style="display: none;"></div>
                                <small class="text-muted font-small-1 d-block mt-25" id="modal-shared-harga-ajb-hint">Berlaku seragam untuk seluruh sales di unit ini.</small>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted d-block">Realisasi Akad Keuangan:</small>
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

                <!-- Section Header untuk Daftar Fee Sales -->
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <div>
                        <h6 class="font-weight-bold mb-0 text-primary">
                            <i class="fas fa-users-cog mr-50"></i> Daftar Fee Sales Penerima Komisi (<span id="modal-sales-count-badge">0</span> Sales)
                        </h6>
                        <small class="text-muted font-small-2">Masing-masing sales memiliki komisi, total fee, dan termin tersendiri.</small>
                    </div>
                    <button type="button" class="btn btn-primary btn-sm font-weight-bold shadow-sm" id="btn-add-fee-sales-card">
                        <i class="fas fa-user-plus mr-25"></i> Tambah Sales Lainnya
                    </button>
                </div>

                <!-- Container dinamis tempat render card masing-masing sales -->
                <div id="modal-sales-cards-container">
                    <div class="text-center py-3 text-muted">
                        <span class="spinner-border spinner-border-sm mr-50"></span> Memuat data fee sales...
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Pengajuan Pencairan -->
<div class="modal fade" id="modal-spp" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form id="form-spp" class="modal-content" enctype="multipart/form-data">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-file-invoice-dollar mr-50 text-warning"></i> Pengajuan Pencairan: <span id="spp-termin-label" class="font-weight-bold"></span></h5>
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
                    <label class="font-weight-bold">Nomor Registrasi <span class="text-danger">*</span></label>
                    <input type="text" name="nomor_spp" id="spp-nomor_spp" class="form-control" required placeholder="Contoh: REG-FEE-202610-001">
                </div>
                <div class="form-group">
                    <label class="font-weight-bold">Tanggal Pengajuan <span class="text-danger">*</span></label>
                    <input type="text" name="tanggal_pengajuan_spp" id="spp-tanggal" class="form-control flatpickr-basic" required placeholder="YYYY-MM-DD">
                </div>
                <div class="form-group">
                    <label class="font-weight-bold">Unggah Berkas Lampiran (PDF / Gambar)</label>
                    <div class="dropzone-box text-center p-2 border rounded" id="spp-dropzone" style="border: 2px dashed #7367f0 !important; cursor: pointer; background-color: #f8f8fd;">
                        <i class="fas fa-cloud-upload-alt fa-2x text-primary mb-50"></i>
                        <p class="mb-25 font-small-3 font-weight-bold text-dark">Tarik & lepas file ke sini, atau <span class="text-primary underline">klik untuk memilih</span></p>
                        <small class="text-muted d-block font-small-2">Bisa juga tekan <kbd>Ctrl+V</kbd> langsung setelah tangkapan layar (screenshot)</small>
                        <input type="file" name="lampiran_spp" id="spp-file" class="d-none" accept=".pdf,image/*">
                        <div id="spp-file-preview" class="mt-50 d-none text-left bg-white p-50 rounded border"></div>
                    </div>
                </div>
                <div class="form-group mb-0">
                    <label>Keterangan Tambahan</label>
                    <textarea name="keterangan" id="spp-keterangan" class="form-control" rows="2" placeholder="Catatan pengajuan pencairan"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-warning" id="btn-submit-spp">
                    <i class="fas fa-paper-plane mr-50"></i> Kirim Pengajuan Pencairan
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
                    <label class="font-weight-bold">Nomor Cek / Bilyet Giro</label>
                    <input type="text" name="no_cek" id="cair-no_cek" class="form-control" placeholder="Contoh: CK-889212">
                    <small class="text-muted font-small-2">Nomor cek yang dicantumkan pada laporan pengajuan fee.</small>
                </div>
                <div class="form-group">
                    <label class="font-weight-bold">Unggah Bukti Transfer / Pencairan (PDF / Gambar)</label>
                    <div class="dropzone-box text-center p-2 border rounded" id="cair-dropzone" style="border: 2px dashed #28c76f !important; cursor: pointer; background-color: #f8fdf9;">
                        <i class="fas fa-receipt fa-2x text-success mb-50"></i>
                        <p class="mb-25 font-small-3 font-weight-bold text-dark">Tarik & lepas file ke sini, atau <span class="text-success underline">klik untuk memilih</span></p>
                        <small class="text-muted d-block font-small-2">Bisa juga tekan <kbd>Ctrl+V</kbd> langsung setelah tangkapan layar (screenshot)</small>
                        <input type="file" name="lampiran_bukti_cair" id="cair-file" class="d-none" accept=".pdf,image/*">
                        <div id="cair-file-preview" class="mt-50 d-none text-left bg-white p-50 rounded border"></div>
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

<!-- Modal Tanda Tangan Pengajuan (MKDT & Keuangan) -->
<div class="modal fade" id="modal-sign-pengajuan" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form id="form-sign-pengajuan" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-file-signature mr-50 text-primary"></i> <span id="modal-sign-title">Tanda Tangan Pengajuan</span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_pengajuan" id="sign-pengajuan-id">
                <input type="hidden" name="role" id="sign-pengajuan-role">

                <div class="form-group">
                    <label class="font-weight-bold">Metode Tanda Tangan <span class="text-danger">*</span></label>
                    <select class="form-control" id="sign-pengajuan-method" name="signature_method">
                        <option value="canvas">Gambar sekarang di layar</option>
                        <option value="profile">Gunakan tanda tangan profil akun</option>
                    </select>
                </div>

                <div id="sign-pengajuan-canvas-section">
                    <label class="font-weight-bold">Area Tanda Tangan <span class="text-danger">*</span></label>
                    <div class="fee-canvas-wrap" id="sign-pengajuan-canvas-wrap">
                        <canvas id="sign-pengajuan-canvas"></canvas>
                        <button type="button" class="btn btn-sm btn-outline-secondary fee-clear-canvas" id="btn-clear-sign-canvas">Hapus</button>
                    </div>
                    <small class="text-muted d-block mt-50">Gunakan mouse atau sentuhan jari di layar untuk menggambar.</small>
                </div>

                <div class="form-group mt-1">
                    <label class="font-weight-bold">Konfirmasi Password Akun Anda <span class="text-danger">*</span></label>
                    <input type="password" name="password" id="sign-pengajuan-password" class="form-control" required autocomplete="current-password" placeholder="Masukkan password login akun Anda">
                    <small class="text-muted font-small-2">Dibutuhkan untuk memvalidasi keabsahan penandatanganan dokumen ini.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary font-weight-bold" id="btn-submit-sign-pengajuan">
                    <i class="fas fa-check mr-50"></i> Simpan Tanda Tangan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Void Pengajuan Pencairan -->
<div class="modal fade" id="modal-void-pengajuan" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form id="form-void-pengajuan" class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title text-white"><i class="fas fa-ban mr-50"></i> Batalkan (Void) Pengajuan Pencairan</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_pengajuan" id="void-pengajuan-id">
                <div class="alert alert-warning py-75 mb-2 font-small-3">
                    <i class="fas fa-exclamation-triangle mr-50"></i> <strong>Peringatan:</strong> Pengajuan ini akan dibatalkan secara permanen. Seluruh unit dan termin di dalamnya akan dikembalikan ke status <strong>Belum Diajukan</strong> sehingga dapat diajukan kembali.
                </div>
                <div class="form-group mb-0">
                    <label class="font-weight-bold text-danger">Alasan Pembatalan (Void) <span class="text-danger">*</span></label>
                    <textarea name="alasan" id="void-pengajuan-alasan" class="form-control" rows="3" required placeholder="Contoh: Kesalahan pemilihan nomor rekening / revisi termin atas instruksi direksi"></textarea>
                    <small class="text-muted font-small-1">Wajib dicatat untuk keperluan audit trail.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Tutup</button>
                <button type="submit" class="btn btn-danger font-weight-bold" id="btn-submit-void-pengajuan">
                    <i class="fas fa-ban mr-50"></i> Konfirmasi Void Pengajuan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tolak Pengajuan & Saran Revisi Harga AJB -->
<div class="modal fade" id="modal-reject-pengajuan" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <form id="form-reject-pengajuan" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold text-danger">
                    <i class="fas fa-times-circle mr-50"></i> <span id="modal-reject-title">Tolak Pengajuan & Saran Revisi Harga AJB</span>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_pengajuan" id="reject-pengajuan-id">
                <input type="hidden" name="role" id="reject-pengajuan-role">

                <div class="alert alert-warning py-75 mb-2 font-small-3">
                    <i class="fas fa-info-circle mr-50"></i> Mengisi form ini akan menolak pengajuan, mereset tanda tangan, dan mengirim notifikasi revisi ke pembuat pengajuan beserta rekomendasi Harga Jual Net AJB per unit kavling.
                </div>

                <div class="card border mb-2 shadow-none">
                    <div class="card-header py-75 border-bottom bg-light">
                        <strong class="font-small-3 text-dark">
                            <i class="fas fa-tags mr-50 text-primary"></i> Rekomendasi Harga Jual Net AJB per Unit Kavling
                        </strong>
                    </div>
                    <div class="card-body p-1" id="reject-unit-prices-container">
                        <div class="text-center py-2 text-muted">
                            <span class="spinner-border spinner-border-sm mr-50"></span> Menyiapkan daftar kavling...
                        </div>
                    </div>
                </div>

                <div class="form-group mb-0">
                    <label class="font-weight-bold text-dark">Keterangan / Alasan Penolakan <span class="text-danger">*</span></label>
                    <textarea name="alasan" id="reject-pengajuan-alasan" class="form-control" rows="3" required placeholder="Contoh: Harga Net AJB unit kavling A1 tidak sesuai berkas akad, mohon disesuaikan ke nominal Rp ..."></textarea>
                    <small class="text-muted font-small-1">Wajib diisi sebagai instruksi perbaikan bagi pembuat pengajuan.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-danger font-weight-bold" id="btn-submit-reject-pengajuan">
                    <i class="fas fa-paper-plane mr-50"></i> Simpan Penolakan & Kirim Revisi
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Pencairan Dana -->
<div class="modal fade" id="modal-edit-cair" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form id="form-edit-cair" class="modal-content" enctype="multipart/form-data">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title text-white"><i class="fas fa-edit mr-50"></i> Edit Realisasi Pencairan: <span id="edit-cair-termin-label" class="font-weight-bold"></span></h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_termin" id="edit-cair-id_termin">
                <div class="form-group">
                    <label class="font-weight-bold">Tanggal Realisasi Cair <span class="text-danger">*</span></label>
                    <input type="text" name="tanggal_cair" id="edit-cair-tanggal" class="form-control flatpickr-basic" required placeholder="YYYY-MM-DD">
                </div>
                <div class="form-group">
                    <label class="font-weight-bold">Nomor Cek / Bilyet Giro</label>
                    <input type="text" name="no_cek" id="edit-cair-no_cek" class="form-control" placeholder="Contoh: CK-889212">
                </div>
                <div class="form-group">
                    <label class="font-weight-bold">Ganti Berkas Bukti Transfer (Opsional)</label>
                    <div class="dropzone-box text-center p-2 border rounded" id="edit-cair-dropzone" style="border: 2px dashed #28c76f !important; cursor: pointer; background-color: #f8fdf9;">
                        <i class="fas fa-receipt fa-2x text-success mb-50"></i>
                        <p class="mb-25 font-small-3 font-weight-bold text-dark">Tarik & lepas file baru ke sini, atau <span class="text-success underline">klik untuk memilih</span></p>
                        <small class="text-muted d-block font-small-2">Bisa juga tekan <kbd>Ctrl+V</kbd> langsung setelah tangkapan layar (screenshot)</small>
                        <input type="file" name="lampiran_bukti_cair" id="edit-cair-file" class="d-none" accept=".pdf,image/*">
                        <div id="edit-cair-file-preview" class="mt-50 d-none text-left bg-white p-50 rounded border"></div>
                    </div>
                    <small class="text-muted font-small-2" id="edit-cair-current-file"></small>
                </div>
                <div class="form-group">
                    <label>Keterangan Pencairan</label>
                    <textarea name="keterangan" id="edit-cair-keterangan" class="form-control" rows="2" placeholder="Nomor referensi bank / transfer"></textarea>
                </div>
                <div class="form-group mb-0 border-top pt-1">
                    <label class="font-weight-bold text-danger"><i class="fas fa-info-circle mr-25"></i> Alasan Perubahan / Edit Pencairan <span class="text-danger">*</span></label>
                    <textarea name="alasan_perubahan" id="edit-cair-alasan" class="form-control" rows="2" required placeholder="Contoh: Koreksi nomor cek atau tanggal transfer dari rekening koran"></textarea>
                    <small class="text-muted font-small-1">Wajib dicatat untuk keperluan audit trail histori sistem.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-success" id="btn-submit-edit-cair">
                    <i class="fas fa-save mr-50"></i> Simpan Perubahan Pencairan
                </button>
            </div>
        </form>
    </div>
</div>

<script src="<?= base_url() ?>app-assets/vendors/js/vendors.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/tables/datatable/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/tables/datatable/datatables.bootstrap4.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/forms/select/select2.full.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/pickers/flatpickr/flatpickr.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/extensions/sweetalert2.all.min.js"></script>
<script>
    window.FEE_SALES_PERMISSIONS = <?= json_encode($permissions ?? ['can_manage' => 1, 'can_submit_spp' => 1, 'can_cairkan' => 1, 'can_sign_mkdt' => 0, 'can_sign_keuangan' => 0]) ?>;
</script>
<script src="<?= base_url('assets/js/sales/fee_sales.js') ?>?v=<?= filemtime(FCPATH . 'assets/js/sales/fee_sales.js') ?>"></script>
