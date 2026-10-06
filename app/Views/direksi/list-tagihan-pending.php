<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/tables/datatable/dataTables.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/tables/datatable/responsive.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/extensions/sweetalert2.min.css">

<style>
/* ========================================================
   Tab Navigation Styling
   ======================================================== */
.nav-pills .nav-link {
    border-radius: 8px;
    padding: 0.65rem 1.25rem;
    color: #5e5873;
    font-weight: 600;
    transition: all 0.2s ease;
    border: 1px solid transparent;
}
.nav-pills .nav-link:hover {
    background-color: #f8f8f8;
    color: #7367f0;
}
.nav-pills .nav-link.active {
    background-color: #7367f0;
    color: #ffffff;
    box-shadow: 0 4px 12px rgba(115, 103, 240, 0.35);
}
.nav-pills .nav-link.active .badge-warning {
    background-color: #ffffff !important;
    color: #ff9f43 !important;
}
.nav-pills .nav-link.active .badge-secondary {
    background-color: rgba(255, 255, 255, 0.25) !important;
    color: #ffffff !important;
}

/* ========================================================
   Mobile Grid View for DataTable
   ======================================================== */
@media (max-width: 767.98px) {
    #table-pending-tagihan thead,
    #table-history-tagihan thead {
        display: none !important;
    }
    #table-pending-tagihan, 
    #table-pending-tagihan tbody,
    #table-history-tagihan,
    #table-history-tagihan tbody {
        display: block !important;
        width: 100% !important;
    }
    #table-pending-tagihan tbody,
    #table-history-tagihan tbody {
        display: grid !important;
        grid-template-columns: 1fr;
        gap: 1rem;
        padding: 0.5rem 0;
    }
    #table-pending-tagihan tbody tr,
    #table-history-tagihan tbody tr {
        display: block !important;
        background: #ffffff;
        border: 1px solid #ebe9f1;
        border-radius: 0.75rem;
        padding: 1rem;
        box-shadow: 0 4px 18px 0 rgba(34, 41, 47, 0.08);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    #table-pending-tagihan tbody tr:hover,
    #table-history-tagihan tbody tr:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 22px 0 rgba(34, 41, 47, 0.12);
    }
    #table-pending-tagihan tbody td,
    #table-history-tagihan tbody td {
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        padding: 0.5rem 0 !important;
        border: none !important;
        border-bottom: 1px dashed #f0f0f4 !important;
        font-size: 0.875rem;
        text-align: right;
    }
    #table-pending-tagihan tbody td::before,
    #table-history-tagihan tbody td::before {
        content: attr(data-label);
        font-weight: 600;
        font-size: 0.75rem;
        color: #82868b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        text-align: left;
        margin-right: 0.75rem;
        flex-shrink: 0;
    }
    #table-pending-tagihan tbody td[data-label="No"],
    #table-history-tagihan tbody td[data-label="No"] {
        display: none !important;
    }
    #table-pending-tagihan tbody td[data-label="Aksi"],
    #table-history-tagihan tbody td[data-label="Aksi"] {
        border-bottom: none !important;
        padding-top: 0.85rem !important;
        padding-bottom: 0 !important;
        margin-top: 0.25rem;
        justify-content: stretch !important;
    }
    #table-pending-tagihan tbody td[data-label="Aksi"]::before,
    #table-history-tagihan tbody td[data-label="Aksi"]::before {
        display: none !important;
    }
    #table-pending-tagihan tbody td[data-label="Aksi"] .action-buttons,
    #table-history-tagihan tbody td[data-label="Aksi"] .action-buttons {
        display: flex;
        width: 100%;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    #table-pending-tagihan tbody td[data-label="Aksi"] .action-buttons .btn,
    #table-history-tagihan tbody td[data-label="Aksi"] .action-buttons .btn {
        flex: 1 1 30%;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0.55rem 0.65rem;
        font-weight: 600;
        font-size: 0.8rem;
        border-radius: 0.5rem;
    }
}
@media (min-width: 576px) and (max-width: 767.98px) {
    #table-pending-tagihan tbody,
    #table-history-tagihan tbody {
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)) !important;
    }
}

/* ========================================================
   Detail Surat Card Styling
   ======================================================== */
.detail-surat-card {
    background: #ffffff;
    border: 1px solid #e9ecef;
    border-radius: 14px;
    padding: 1.15rem;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
}
.detail-surat-title-sub {
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.8px;
    color: #8c9ba5;
    text-transform: uppercase;
    margin-bottom: 0.2rem;
}
.detail-surat-no {
    font-size: 1.15rem;
    font-weight: 800;
    color: #1a202c;
    letter-spacing: -0.2px;
}
.detail-surat-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.45rem 0;
    font-size: 0.85rem;
}
.detail-surat-label {
    font-size: 0.75rem;
    font-weight: 700;
    color: #8c9ba5;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.detail-surat-val-nominal {
    font-weight: 800;
    color: #2b6cb0;
    font-size: 1.05rem;
}
.detail-surat-val-text {
    font-weight: 700;
    color: #2d3748;
}
.detail-surat-actions {
    display: flex;
    gap: 0.6rem;
    border-top: 1px solid #edf2f7;
    padding-top: 0.85rem;
    margin-top: 0.5rem;
    flex-wrap: wrap;
}
.btn-round-pill {
    border-radius: 20px;
    padding: 0.35rem 1rem;
    font-weight: 600;
    font-size: 0.8rem;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
}

/* ========================================================
   Canvas & Signature Controls
   ======================================================== */
.signature-canvas-container {
    position: relative;
    width: 100%;
    height: 190px;
    border: 2px dashed #cbd5e0;
    border-radius: 10px;
    background: #ffffff;
    overflow: hidden;
    touch-action: none;
}
.signature-canvas-container canvas {
    width: 100%;
    height: 100%;
    display: block;
    cursor: crosshair;
}
.profile-sig-box {
    min-height: 150px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    background: #f8fafc;
    padding: 1rem;
}
.profile-sig-box img {
    max-height: 110px;
    max-width: 100%;
    object-fit: contain;
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
                        <h2 class="content-header-title float-left mb-0">Persetujuan Surat Tagihan</h2>
                        <div class="breadcrumb-wrapper col-12">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?= base_url() ?>">Home</a></li>
                                <li class="breadcrumb-item active">Persetujuan Surat Tagihan</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="content-body">
            <!-- Nav Tabs -->
            <ul class="nav nav-pills mb-2" id="tagihanTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="tab-pending-btn" data-toggle="pill" href="#pane-pending" role="tab" aria-controls="pane-pending" aria-selected="true">
                        <i class="fas fa-clock mr-50"></i> Menunggu Persetujuan
                        <span class="badge badge-warning badge-pill ml-50" id="badge-pending-count">0</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="tab-history-btn" data-toggle="pill" href="#pane-history" role="tab" aria-controls="pane-history" aria-selected="false">
                        <i class="fas fa-history mr-50"></i> Riwayat Persetujuan
                        <span class="badge badge-secondary badge-pill ml-50" id="badge-history-count">0</span>
                    </a>
                </li>
            </ul>

            <div class="tab-content">
                <!-- TAB 1: PENDING -->
                <div class="tab-pane fade show active" id="pane-pending" role="tabpanel" aria-labelledby="tab-pending-btn">
                    <section id="section-pending">
                        <div class="row">
                            <div class="col-12">
                                <div class="card shadow-sm border-0">
                                    <div class="card-header border-bottom py-1 d-flex justify-content-between align-items-center">
                                        <h4 class="card-title mb-0 font-weight-bolder text-dark">
                                            <i class="fas fa-file-invoice text-warning mr-50"></i> Daftar Surat Tagihan Menunggu Persetujuan
                                        </h4>
                                        <button type="button" class="btn btn-sm btn-outline-primary" id="btn-refresh-pending">
                                            <i class="fas fa-sync-alt mr-25"></i> Refresh
                                        </button>
                                    </div>
                                    <div class="card-body mt-1">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped w-100" id="table-pending-tagihan">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th style="width: 40px;" class="text-center">No</th>
                                                        <th>Nomor Surat</th>
                                                        <th>Tanggal Invoice</th>
                                                        <th>Konsumen</th>
                                                        <th>Kavling</th>
                                                        <th style="width: 220px;" class="text-center">Aksi</th>
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
                    </section>
                </div>

                <!-- TAB 2: RIWAYAT -->
                <div class="tab-pane fade" id="pane-history" role="tabpanel" aria-labelledby="tab-history-btn">
                    <section id="section-history">
                        <div class="row">
                            <div class="col-12">
                                <div class="card shadow-sm border-0">
                                    <div class="card-header border-bottom py-1 d-flex justify-content-between align-items-center">
                                        <h4 class="card-title mb-0 font-weight-bolder text-dark">
                                            <i class="fas fa-history text-primary mr-50"></i> Riwayat Surat Tagihan (Disetujui / Ditolak)
                                        </h4>
                                        <button type="button" class="btn btn-sm btn-outline-primary" id="btn-refresh-history">
                                            <i class="fas fa-sync-alt mr-25"></i> Refresh
                                        </button>
                                    </div>
                                    <div class="card-body mt-1">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped w-100" id="table-history-tagihan">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th style="width: 40px;" class="text-center">No</th>
                                                        <th>Nomor Surat</th>
                                                        <th>Tanggal Invoice</th>
                                                        <th>Konsumen & Kavling</th>
                                                        <th class="text-right">Nominal</th>
                                                        <th class="text-center" style="width: 140px;">Status</th>
                                                        <th>Diproses Oleh</th>
                                                        <th style="width: 100px;" class="text-center">Aksi</th>
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
                    </section>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================
     Modal Tanda Tangan Surat Tagihan
     ======================================================== -->
<div class="modal fade" id="modalSignTagihan" tabindex="-1" role="dialog" aria-labelledby="modalSignTagihanLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header bg-light border-bottom py-1" style="border-top-left-radius: 16px; border-top-right-radius: 16px;">
                <h5 class="modal-title font-weight-bolder text-dark" id="modalSignTagihanLabel">
                    <i class="fas fa-file-signature text-primary mr-50"></i> Tanda Tangan Surat Tagihan
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-2">
                <!-- Detail Surat Card -->
                <div class="detail-surat-card mb-2">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <div>
                            <div class="detail-surat-title-sub">DETAIL SURAT</div>
                            <div class="detail-surat-no" id="mdl_no_inv">-</div>
                        </div>
                        <div id="mdl_status_badge">
                            <span class="badge badge-dark font-weight-bold px-1 py-50" style="letter-spacing: 0.5px; border-radius: 6px;">DRAFT</span>
                        </div>
                    </div>

                    <div class="detail-surat-row border-top pt-50">
                        <span class="detail-surat-label">NOMINAL TAGIHAN</span>
                        <span class="detail-surat-val-nominal" id="mdl_nominal">RP 0</span>
                    </div>
                    <div class="detail-surat-row">
                        <span class="detail-surat-label">TANGGAL TERBIT</span>
                        <span class="detail-surat-val-text" id="mdl_tgl_terbit">-</span>
                    </div>
                    <div class="detail-surat-row">
                        <span class="detail-surat-label">JATUH TEMPO</span>
                        <span class="detail-surat-val-text" id="mdl_jatuh_tempo">-</span>
                    </div>
                    <div class="detail-surat-row">
                        <span class="detail-surat-label">TTD DIREKSI</span>
                        <span id="mdl_ttd_direksi"><span class="text-secondary font-weight-bold"><i class="fas fa-clock mr-25"></i> BELUM</span></span>
                    </div>
                    <div class="detail-surat-row">
                        <span class="detail-surat-label">DIBUAT OLEH</span>
                        <span class="detail-surat-val-text text-uppercase" id="mdl_pembuat">-</span>
                    </div>

                    <div class="detail-surat-actions" id="mdl_actions">
                        <a href="#" target="_blank" id="mdl_btn_download" class="btn btn-outline-primary btn-round-pill">
                            <i class="fas fa-download"></i> DOWNLOAD
                        </a>
                    </div>
                </div>

                <!-- Bagian Metode Tanda Tangan -->
                <div class="form-group mb-2">
                    <label class="font-weight-bolder text-dark d-block mb-75">
                        <i class="fas fa-pen-fancy text-primary mr-25"></i> Pilih Metode Tanda Tangan
                    </label>
                    <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                        <label class="btn btn-outline-primary active" id="lbl-method-profile" style="border-radius: 8px 0 0 8px;">
                            <input type="radio" name="sign_method" id="method_profile" value="profile" checked>
                            <i class="fas fa-user-check mr-50"></i> Gunakan TTD Profil
                        </label>
                        <label class="btn btn-outline-primary" id="lbl-method-canvas" style="border-radius: 0 8px 8px 0;">
                            <input type="radio" name="sign_method" id="method_canvas" value="canvas">
                            <i class="fas fa-signature mr-50"></i> Gambar di Kanvas
                        </label>
                    </div>
                </div>

                <!-- Konten Option 1: Profil Signature -->
                <div id="section_profile_sig" class="mb-2">
                    <div class="profile-sig-box" id="profile_sig_preview">
                        <div class="text-center text-muted">
                            <i class="fas fa-spinner fa-spin fa-2x text-primary mb-50"></i>
                            <div>Memeriksa tanda tangan profil...</div>
                        </div>
                    </div>
                    <div id="profile_sig_alert" class="mt-75 d-none">
                        <div class="alert alert-warning py-75 px-1 mb-0 font-small-3" role="alert">
                            <i class="fas fa-exclamation-triangle mr-50"></i>
                            Anda belum memiliki tanda tangan profil. Silakan pilih <strong>Gambar di Kanvas</strong>.
                        </div>
                    </div>
                </div>

                <!-- Konten Option 2: Canvas Signature -->
                <div id="section_canvas_sig" class="mb-2 d-none">
                    <div class="d-flex justify-content-between align-items-center mb-50">
                        <small class="text-muted"><i class="fas fa-hand-pointer mr-25"></i> Buat tanda tangan Anda di area bawah:</small>
                        <button type="button" class="btn btn-sm btn-outline-danger py-25 px-50" id="btn_clear_canvas">
                            <i class="fas fa-eraser mr-25"></i> Hapus Kanvas
                        </button>
                    </div>
                    <div class="signature-canvas-container" id="canvas_container">
                        <canvas id="sign_canvas"></canvas>
                    </div>
                </div>

                <!-- Bagian Konfirmasi Password (Wajib) -->
                <div class="form-group mb-1">
                    <label class="font-weight-bolder text-dark" for="sign_password">
                        <i class="fas fa-lock text-danger mr-25"></i> Password Akun <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="sign_password" placeholder="Masukkan password akun Anda untuk konfirmasi..." autocomplete="current-password" required>
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" id="btn_toggle_password" title="Lihat password">
                                <i class="fas fa-eye" id="toggle_password_icon"></i>
                            </button>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-25">
                        <i class="fas fa-shield-alt mr-25 text-primary"></i> Wajib memasukkan password untuk memverifikasi keabsahan tanda tangan direksi.
                    </small>
                </div>
            </div>
            <div class="modal-footer bg-light border-top py-1 d-flex justify-content-between align-items-center" style="border-bottom-left-radius: 16px; border-bottom-right-radius: 16px;">
                <button type="button" class="btn btn-outline-danger" id="btn_open_reject_from_sign">
                    <i class="fas fa-times-circle mr-50"></i> Tolak Surat
                </button>
                <div>
                    <button type="button" class="btn btn-outline-secondary mr-50" data-dismiss="modal">
                        Batal
                    </button>
                    <button type="button" class="btn btn-success font-weight-bold" id="btn_submit_sign">
                        <i class="fas fa-check-circle mr-50"></i> Setujui & Tanda Tangan
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================
     Modal Tolak Surat Tagihan
     ======================================================== -->
<div class="modal fade" id="modalRejectTagihan" tabindex="-1" role="dialog" aria-labelledby="modalRejectTagihanLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header bg-light border-bottom py-1" style="border-top-left-radius: 16px; border-top-right-radius: 16px;">
                <h5 class="modal-title font-weight-bolder text-danger" id="modalRejectTagihanLabel">
                    <i class="fas fa-times-circle mr-50"></i> Tolak Tanda Tangan Surat Tagihan
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-2">
                <!-- Info Surat Singkat -->
                <div class="alert alert-light border mb-2 py-1 px-1" style="border-radius: 8px;">
                    <div class="d-flex justify-content-between mb-25">
                        <span class="text-muted small font-weight-bold">NOMOR SURAT:</span>
                        <span class="font-weight-bolder text-dark" id="reject_info_no_inv">-</span>
                    </div>
                    <div class="d-flex justify-content-between mb-25">
                        <span class="text-muted small font-weight-bold">KONSUMEN:</span>
                        <span class="font-weight-bold text-primary" id="reject_info_konsumen">-</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small font-weight-bold">TOTAL TAGIHAN:</span>
                        <span class="font-weight-bolder text-danger" id="reject_info_nominal">RP 0</span>
                    </div>
                </div>

                <!-- Input Alasan Penolakan -->
                <div class="form-group mb-2">
                    <label class="font-weight-bolder text-dark" for="reject_alasan">
                        <i class="fas fa-comment-dots text-danger mr-25"></i> Alasan Penolakan <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control" id="reject_alasan" rows="3" placeholder="Tuliskan alasan penolakan secara jelas agar pembuat invoice dapat memperbaikinya..." required></textarea>
                    <small class="text-muted d-block mt-25">
                        Alasan ini akan dicatat ke riwayat status invoice dan dikirimkan ke tim keuangan.
                    </small>
                </div>

                <!-- Konfirmasi Password (Wajib) -->
                <div class="form-group mb-1">
                    <label class="font-weight-bolder text-dark" for="reject_password">
                        <i class="fas fa-lock text-danger mr-25"></i> Password Akun <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="reject_password" placeholder="Masukkan password akun Anda untuk konfirmasi..." autocomplete="current-password" required>
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" id="btn_toggle_reject_password" title="Lihat password">
                                <i class="fas fa-eye" id="toggle_reject_password_icon"></i>
                            </button>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-25">
                        <i class="fas fa-shield-alt mr-25 text-danger"></i> Wajib memasukkan password untuk keamanan otorisasi penolakan direksi.
                    </small>
                </div>
            </div>
            <div class="modal-footer bg-light border-top py-1" style="border-bottom-left-radius: 16px; border-bottom-right-radius: 16px;">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">
                    Batal
                </button>
                <button type="button" class="btn btn-danger font-weight-bold" id="btn_submit_reject">
                    <i class="fas fa-ban mr-50"></i> Konfirmasi Tolak
                </button>
            </div>
        </div>
    </div>
</div>

<!-- BEGIN: Page Vendor JS-->
<script src="<?= base_url() ?>/app-assets/vendors/js/vendors.min.js"></script>
<script src="<?= base_url() ?>/app-assets/vendors/js/tables/datatable/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>/app-assets/vendors/js/tables/datatable/datatables.bootstrap4.min.js"></script>
<script src="<?= base_url() ?>/app-assets/vendors/js/tables/datatable/dataTables.responsive.min.js"></script>
<script src="<?= base_url() ?>/app-assets/vendors/js/extensions/sweetalert2.all.min.js"></script>

<script>
    $(document).ready(function() {
        let csrfName = '<?= csrf_token() ?>';
        let csrfHash = '<?= csrf_hash() ?>';

        let selectedRowData = null;
        let hasProfileSignature = false;
        let canvasDirty = false;

        // ========================================================
        // 1. Inisialisasi DataTable: Pending Tagihan
        // ========================================================
        let tablePending = $('#table-pending-tagihan').DataTable({
            ajax: {
                url: base_url + 'direksi/get_pending_tagihan',
                dataSrc: function(json) {
                    const data = json.data || [];
                    $('#badge-pending-count').text(data.length);
                    return data;
                }
            },
            order: [[2, 'desc']],
            createdRow: function(row, data, dataIndex) {
                $('td:eq(0)', row).attr('data-label', 'No');
                $('td:eq(1)', row).attr('data-label', 'Nomor Surat');
                $('td:eq(2)', row).attr('data-label', 'Tanggal Invoice');
                $('td:eq(3)', row).attr('data-label', 'Konsumen');
                $('td:eq(4)', row).attr('data-label', 'Kavling');
                $('td:eq(5)', row).attr('data-label', 'Aksi');
            },
            columns: [
                { 
                    data: null, 
                    render: (data, type, row, meta) => meta.row + 1, 
                    className: 'text-center' 
                },
                { 
                    data: 'nomor_surat',
                    render: function(data, type, row) {
                        return `<span class="font-weight-bold text-dark">${escapeHtml(data || row.no_inv || '-')}</span>`;
                    }
                },
                { 
                    data: 'tanggal_invoice',
                    render: function(data) {
                        return data ? formatTanggalDisplay(data) : '-';
                    }
                },
                { 
                    data: 'nama_konsumen',
                    render: function(data) {
                        return `<span class="font-weight-bold text-primary">${escapeHtml(data || '-')}</span>`;
                    }
                },
                { 
                    data: null, 
                    render: (data) => escapeHtml(`${data.nama_proyek || '-'} - ${data.nama_jalan || '-'} No. ${data.no_kavling || '-'}`) 
                },
                { 
                    data: null,
                    className: 'text-center',
                    orderable: false,
                    render: function(data, type, row) {
                        const jsonStr = JSON.stringify(row).replace(/'/g, "&#39;");
                        return `<div class="action-buttons d-flex justify-content-center" style="gap: .35rem;">
                                    <button class="btn btn-sm btn-success btn-sign" data-row='${jsonStr}' title="Setujui dan tanda tangani surat">
                                        <i class="fas fa-check-circle mr-25"></i> TTD
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger btn-reject" data-row='${jsonStr}' title="Tolak surat tagihan">
                                        <i class="fas fa-times-circle mr-25"></i> Tolak
                                    </button>
                                    <a href="${base_url}keuangan/download_penagihan?id=${encodeURIComponent(row.no_inv)}" target="_blank" class="btn btn-sm btn-outline-info" title="Lihat invoice">
                                        <i class="fas fa-print mr-25"></i> Lihat
                                    </a>
                                </div>`;
                    }
                }
            ]
        });

        // ========================================================
        // 2. Inisialisasi DataTable: Riwayat Tagihan
        // ========================================================
        let tableHistory = $('#table-history-tagihan').DataTable({
            ajax: {
                url: base_url + 'direksi/get_history_tagihan',
                dataSrc: function(json) {
                    const data = json.data || [];
                    $('#badge-history-count').text(data.length);
                    return data;
                }
            },
            order: [[2, 'desc']],
            createdRow: function(row, data, dataIndex) {
                $('td:eq(0)', row).attr('data-label', 'No');
                $('td:eq(1)', row).attr('data-label', 'Nomor Surat');
                $('td:eq(2)', row).attr('data-label', 'Tanggal Invoice');
                $('td:eq(3)', row).attr('data-label', 'Konsumen & Kavling');
                $('td:eq(4)', row).attr('data-label', 'Nominal');
                $('td:eq(5)', row).attr('data-label', 'Status');
                $('td:eq(6)', row).attr('data-label', 'Diproses Oleh');
                $('td:eq(7)', row).attr('data-label', 'Aksi');
            },
            columns: [
                { 
                    data: null, 
                    render: (data, type, row, meta) => meta.row + 1, 
                    className: 'text-center' 
                },
                { 
                    data: 'nomor_surat',
                    render: function(data, type, row) {
                        return `<span class="font-weight-bold text-dark">${escapeHtml(data || row.no_inv || '-')}</span>`;
                    }
                },
                { 
                    data: 'tanggal_invoice',
                    render: function(data) {
                        return data ? formatTanggalDisplay(data) : '-';
                    }
                },
                { 
                    data: null,
                    render: function(data) {
                        const konsumen = escapeHtml(data.nama_konsumen || '-');
                        const unit = escapeHtml(`${data.nama_proyek || ''} - ${data.nama_jalan || ''} No. ${data.no_kavling || ''}`);
                        return `<div class="font-weight-bold text-primary">${konsumen}</div>
                                <div class="small text-muted">${unit}</div>`;
                    }
                },
                { 
                    data: 'total_nominal',
                    className: 'text-right',
                    render: function(data) {
                        return `<span class="font-weight-bold text-dark">Rp ${formatRupiah(parseFloat(data || 0))}</span>`;
                    }
                },
                { 
                    data: null,
                    className: 'text-center',
                    render: function(data) {
                        const isSigned = parseInt(data.is_signed_direktur || 0);
                        if (isSigned === 1) {
                            return `<span class="badge badge-success font-weight-bold px-75 py-50">
                                        <i class="fas fa-check-circle mr-25"></i> Disetujui
                                    </span>`;
                        } else if (isSigned === 2) {
                            const alasan = escapeHtml(data.keterangan_status || 'Tidak ada keterangan');
                            return `<div>
                                        <span class="badge badge-danger font-weight-bold px-75 py-50">
                                            <i class="fas fa-times-circle mr-25"></i> Ditolak
                                        </span>
                                        <div class="small text-danger mt-25 font-weight-bold" title="${alasan}">
                                            <i class="fas fa-info-circle mr-25"></i> ${alasan.length > 25 ? alasan.substring(0, 25) + '...' : alasan}
                                        </div>
                                    </div>`;
                        }
                        return `<span class="badge badge-secondary font-weight-bold px-75 py-50">Pending</span>`;
                    }
                },
                { 
                    data: null,
                    render: function(data) {
                        const diproses = escapeHtml(data.diproses_oleh || '-');
                        const tgl = data.signed_at ? formatTanggalDisplay(data.signed_at) : '-';
                        return `<div class="font-weight-bold text-dark">${diproses}</div>
                                <div class="small text-muted"><i class="fas fa-clock mr-25"></i> ${tgl}</div>`;
                    }
                },
                { 
                    data: null,
                    className: 'text-center',
                    orderable: false,
                    render: function(data, type, row) {
                        return `<div class="action-buttons d-flex justify-content-center">
                                    <a href="${base_url}keuangan/download_penagihan?id=${encodeURIComponent(row.no_inv)}" target="_blank" class="btn btn-sm btn-outline-info" title="Lihat/Download invoice">
                                        <i class="fas fa-print mr-25"></i> Lihat
                                    </a>
                                </div>`;
                    }
                }
            ]
        });

        // Tab switch adjust DataTable column width
        $('a[data-toggle="pill"]').on('shown.bs.tab', function(e) {
            $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
        });

        $('#btn-refresh-pending').on('click', function() {
            tablePending.ajax.reload(null, false);
        });

        $('#btn-refresh-history').on('click', function() {
            tableHistory.ajax.reload(null, false);
        });

        // ========================================================
        // Setup Canvas Signature
        // ========================================================
        const canvas = document.getElementById('sign_canvas');
        const ctx = canvas ? canvas.getContext('2d') : null;
        let isDrawing = false;

        function resizeCanvas() {
            const container = document.getElementById('canvas_container');
            if (!container || !canvas || !ctx) return;
            const ratio = window.devicePixelRatio || 1;
            const width = container.clientWidth || 300;
            const height = container.clientHeight || 190;

            const tempSnapshot = canvasDirty ? canvas.toDataURL() : null;

            canvas.width = width * ratio;
            canvas.height = height * ratio;
            ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
            ctx.lineWidth = 2.4;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
            ctx.strokeStyle = '#1a365d';

            if (tempSnapshot) {
                const img = new Image();
                img.onload = function() {
                    ctx.drawImage(img, 0, 0, width, height);
                };
                img.src = tempSnapshot;
            }
        }

        function getPointerPos(e) {
            const rect = canvas.getBoundingClientRect();
            return {
                x: e.clientX - rect.left,
                y: e.clientY - rect.top
            };
        }

        if (canvas && ctx) {
            canvas.addEventListener('pointerdown', function(e) {
                isDrawing = true;
                canvasDirty = true;
                canvas.setPointerCapture(e.pointerId);
                const pos = getPointerPos(e);
                ctx.beginPath();
                ctx.moveTo(pos.x, pos.y);
            });

            canvas.addEventListener('pointermove', function(e) {
                if (!isDrawing) return;
                const pos = getPointerPos(e);
                ctx.lineTo(pos.x, pos.y);
                ctx.stroke();
            });

            ['pointerup', 'pointercancel', 'pointerleave'].forEach(function(ev) {
                canvas.addEventListener(ev, function() {
                    isDrawing = false;
                });
            });

            $('#btn_clear_canvas').on('click', function() {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                canvasDirty = false;
            });
        }

        // ========================================================
        // Toggle Method Signature (Profile vs Canvas)
        // ========================================================
        $('input[name="sign_method"]').on('change', function() {
            const method = $(this).val();
            if (method === 'profile') {
                $('#section_profile_sig').removeClass('d-none');
                $('#section_canvas_sig').addClass('d-none');
            } else {
                $('#section_profile_sig').addClass('d-none');
                $('#section_canvas_sig').removeClass('d-none');
                setTimeout(resizeCanvas, 150);
            }
        });

        // Toggle Password Visibility (Sign)
        $('#btn_toggle_password').on('click', function() {
            const pwdInput = $('#sign_password');
            const icon = $('#toggle_password_icon');
            if (pwdInput.attr('type') === 'password') {
                pwdInput.attr('type', 'text');
                icon.removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                pwdInput.attr('type', 'password');
                icon.removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });

        // Toggle Password Visibility (Reject)
        $('#btn_toggle_reject_password').on('click', function() {
            const pwdInput = $('#reject_password');
            const icon = $('#toggle_reject_password_icon');
            if (pwdInput.attr('type') === 'password') {
                pwdInput.attr('type', 'text');
                icon.removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                pwdInput.attr('type', 'password');
                icon.removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });

        // ========================================================
        // Klik Tombol Tanda Tangan -> Buka Modal
        // ========================================================
        $('#table-pending-tagihan').on('click', '.btn-sign', function() {
            const rowDataStr = $(this).attr('data-row');
            if (!rowDataStr) return;

            selectedRowData = JSON.parse(rowDataStr);

            // 1. Populate Detail Surat Card
            $('#mdl_no_inv').text(selectedRowData.nomor_surat || selectedRowData.no_inv || '-');
            
            // Status badge
            const st = (selectedRowData.status_tagihan || 'draft').toLowerCase();
            let badgeClass = 'badge-dark';
            if (st === 'dikirim') badgeClass = 'badge-info';
            else if (st === 'draft') badgeClass = 'badge-secondary';
            else if (st === 'publish') badgeClass = 'badge-primary';
            else if (st === 'respon') badgeClass = 'badge-success';
            $('#mdl_status_badge').html(`<span class="badge ${badgeClass} font-weight-bold px-1 py-50" style="letter-spacing: 0.5px; border-radius: 6px;">${st.toUpperCase()}</span>`);

            // Nominal tagihan
            const nominal = parseFloat(selectedRowData.total_nominal || 0);
            $('#mdl_nominal').text('RP ' + formatRupiah(nominal));

            // Tanggal Terbit
            $('#mdl_tgl_terbit').text(selectedRowData.tanggal_invoice ? formatTanggalCard(selectedRowData.tanggal_invoice) : '-');

            // Jatuh Tempo
            $('#mdl_jatuh_tempo').text(selectedRowData.tanggal_jatuh_tempo ? formatTanggalCard(selectedRowData.tanggal_jatuh_tempo) : '-');

            // TTD Direksi Status
            const isSigned = parseInt(selectedRowData.is_signed_direktur || 0) === 1;
            if (isSigned) {
                const signedAtText = selectedRowData.signed_at ? ` (${formatTanggalCard(selectedRowData.signed_at)})` : '';
                $('#mdl_ttd_direksi').html(`<span class="text-success font-weight-bold"><i class="fas fa-check-circle mr-25"></i> SUDAH${signedAtText}</span>`);
            } else {
                $('#mdl_ttd_direksi').html(`<span class="text-secondary font-weight-bold"><i class="fas fa-clock mr-25"></i> BELUM</span>`);
            }

            // Dibuat Oleh
            $('#mdl_pembuat').text(selectedRowData.pembuat || '-');

            // Action Download Button
            $('#mdl_btn_download').attr('href', `${base_url}keuangan/download_penagihan?id=${encodeURIComponent(selectedRowData.no_inv)}`);

            // 2. Reset Form & Canvas
            $('#sign_password').val('').attr('type', 'password');
            $('#toggle_password_icon').removeClass('fa-eye-slash').addClass('fa-eye');
            canvasDirty = false;
            if (ctx && canvas) ctx.clearRect(0, 0, canvas.width, canvas.height);

            // 3. Cek Tanda Tangan Profil
            checkProfileSignature();

            // 4. Tampilkan Modal
            $('#modalSignTagihan').modal('show');
        });

        $('#modalSignTagihan').on('shown.bs.modal', function() {
            if ($('#method_canvas').is(':checked')) {
                resizeCanvas();
            }
        });

        // ========================================================
        // Buka Modal Tolak (dari tombol tabel atau tombol di dalam modal TTD)
        // ========================================================
        function openRejectModal(data) {
            selectedRowData = data;
            $('#reject_info_no_inv').text(data.nomor_surat || data.no_inv || '-');
            $('#reject_info_konsumen').text(data.nama_konsumen || '-');
            $('#reject_info_nominal').text('RP ' + formatRupiah(parseFloat(data.total_nominal || 0)));

            $('#reject_alasan').val('');
            $('#reject_password').val('').attr('type', 'password');
            $('#toggle_reject_password_icon').removeClass('fa-eye-slash').addClass('fa-eye');

            $('#modalRejectTagihan').modal('show');
        }

        // Klik tombol tolak di tabel
        $('#table-pending-tagihan').on('click', '.btn-reject', function() {
            const rowDataStr = $(this).attr('data-row');
            if (!rowDataStr) return;
            openRejectModal(JSON.parse(rowDataStr));
        });

        // Klik tombol tolak di footer modal TTD
        $('#btn_open_reject_from_sign').on('click', function() {
            const data = selectedRowData || {
                no_inv: $('#mdl_no_inv').text(),
                total_nominal: $('#mdl_nominal').text().replace(/[^\d]/g, '')
            };
            $('#modalSignTagihan').modal('hide');
            setTimeout(function() {
                openRejectModal(data);
            }, 300);
        });

        // ========================================================
        // Cek tanda tangan di profile
        // ========================================================
        function checkProfileSignature() {
            $('#profile_sig_preview').html(`
                <div class="text-center text-muted">
                    <i class="fas fa-spinner fa-spin text-primary mb-50"></i>
                    <div>Memeriksa tanda tangan profil...</div>
                </div>
            `);
            $('#profile_sig_alert').addClass('d-none');

            $.ajax({
                url: base_url + 'direksi/get_my_signature',
                type: 'GET',
                dataType: 'json',
                success: function(res) {
                    if (res.token) csrfHash = res.token;
                    hasProfileSignature = !!(res.data && res.data.has_signature);

                    if (hasProfileSignature) {
                        $('#profile_sig_preview').html(`
                            <div class="text-center">
                                <img src="${base_url}api/profile/signature/image?v=${Date.now()}" alt="Tanda Tangan Profil" style="max-height: 100px;">
                                <div class="small text-success mt-50 font-weight-bold">
                                    <i class="fas fa-check-circle mr-25"></i> Tanda tangan profil siap digunakan
                                </div>
                            </div>
                        `);
                        // Set method default ke profile
                        $('#method_profile').prop('checked', true);
                        $('#lbl-method-profile').addClass('active');
                        $('#lbl-method-canvas').removeClass('active');
                        $('#section_profile_sig').removeClass('d-none');
                        $('#section_canvas_sig').addClass('d-none');
                    } else {
                        $('#profile_sig_preview').html(`
                            <div class="text-center text-muted">
                                <i class="fas fa-signature fa-2x mb-50 text-secondary"></i>
                                <div>Belum ada tanda tangan di profil.</div>
                            </div>
                        `);
                        $('#profile_sig_alert').removeClass('d-none');
                        // Auto switch ke canvas jika profil belum ada TTD
                        $('#method_canvas').prop('checked', true);
                        $('#lbl-method-canvas').addClass('active');
                        $('#lbl-method-profile').removeClass('active');
                        $('#section_profile_sig').addClass('d-none');
                        $('#section_canvas_sig').removeClass('d-none');
                        setTimeout(resizeCanvas, 150);
                    }
                },
                error: function() {
                    $('#profile_sig_preview').html(`<span class="text-danger">Gagal memuat tanda tangan profil.</span>`);
                }
            });
        }

        // ========================================================
        // Submit Tanda Tangan
        // ========================================================
        $('#btn_submit_sign').on('click', function() {
            if (!selectedRowData || !selectedRowData.no_inv) {
                Swal.fire('Error', 'Data surat tidak ditemukan.', 'error');
                return;
            }

            const method = $('input[name="sign_method"]:checked').val() || 'profile';
            const password = $('#sign_password').val().trim();

            if (!password) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Password Wajib Diisi',
                    text: 'Silakan masukkan password akun Anda untuk melanjutkan proses tanda tangan.'
                });
                $('#sign_password').focus();
                return;
            }

            let signatureData = '';
            if (method === 'canvas') {
                if (!canvasDirty) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Tanda Tangan Kosong',
                        text: 'Silakan buat tanda tangan Anda terlebih dahulu pada area kanvas.'
                    });
                    return;
                }
                signatureData = canvas.toDataURL('image/png');
            } else {
                if (!hasProfileSignature) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Profil Belum Memiliki TTD',
                        text: 'Profil Anda belum memiliki tanda tangan. Silakan pilih opsi Gambar di Kanvas.'
                    });
                    return;
                }
            }

            const $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-50"></i> Memproses...');

            $.ajax({
                url: base_url + 'direksi/sign_tagihan',
                type: 'POST',
                data: {
                    no_inv: selectedRowData.no_inv,
                    password: password,
                    signature_method: method,
                    signature_data: signatureData,
                    [csrfName]: csrfHash
                },
                dataType: 'json',
                success: function(res) {
                    if (res.token) csrfHash = res.token;
                    $btn.prop('disabled', false).html('<i class="fas fa-check-circle mr-50"></i> Setujui & Tanda Tangan');

                    if (res.success) {
                        $('#modalSignTagihan').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: res.message || 'Surat tagihan berhasil ditandatangani.',
                            confirmButtonColor: '#28c76f'
                        });
                        tablePending.ajax.reload(null, false);
                        tableHistory.ajax.reload(null, false);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: res.message || 'Terjadi kesalahan saat memproses tanda tangan.'
                        });
                    }
                },
                error: function(xhr) {
                    $btn.prop('disabled', false).html('<i class="fas fa-check-circle mr-50"></i> Setujui & Tanda Tangan');
                    let errMsg = 'Terjadi kesalahan pada server.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errMsg = xhr.responseJSON.message;
                    }
                    Swal.fire('Error', errMsg, 'error');
                }
            });
        });

        // ========================================================
        // Submit Penolakan Surat Tagihan
        // ========================================================
        $('#btn_submit_reject').on('click', function() {
            if (!selectedRowData || !selectedRowData.no_inv) {
                Swal.fire('Error', 'Data surat tidak ditemukan.', 'error');
                return;
            }

            const alasan = $('#reject_alasan').val().trim();
            const password = $('#reject_password').val().trim();

            if (!alasan) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Alasan Wajib Diisi',
                    text: 'Silakan masukkan alasan penolakan surat tagihan ini.'
                });
                $('#reject_alasan').focus();
                return;
            }

            if (!password) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Password Wajib Diisi',
                    text: 'Silakan masukkan password akun Anda untuk konfirmasi penolakan.'
                });
                $('#reject_password').focus();
                return;
            }

            const $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-50"></i> Memproses...');

            $.ajax({
                url: base_url + 'direksi/reject_tagihan',
                type: 'POST',
                data: {
                    no_inv: selectedRowData.no_inv,
                    alasan: alasan,
                    password: password,
                    [csrfName]: csrfHash
                },
                dataType: 'json',
                success: function(res) {
                    if (res.token) csrfHash = res.token;
                    $btn.prop('disabled', false).html('<i class="fas fa-ban mr-50"></i> Konfirmasi Tolak');

                    if (res.success) {
                        $('#modalRejectTagihan').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: res.message || 'Surat tagihan berhasil ditolak.',
                            confirmButtonColor: '#ea5455'
                        });
                        tablePending.ajax.reload(null, false);
                        tableHistory.ajax.reload(null, false);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: res.message || 'Terjadi kesalahan saat memproses penolakan.'
                        });
                    }
                },
                error: function(xhr) {
                    $btn.prop('disabled', false).html('<i class="fas fa-ban mr-50"></i> Konfirmasi Tolak');
                    let errMsg = 'Terjadi kesalahan pada server.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errMsg = xhr.responseJSON.message;
                    }
                    Swal.fire('Error', errMsg, 'error');
                }
            });
        });

        // ========================================================
        // Helper Format Tanggal, Angka & Escape HTML
        // ========================================================
        function formatRupiah(amount) {
            return new Intl.NumberFormat('id-ID').format(amount);
        }

        function formatTanggalCard(dateStr) {
            if (!dateStr) return '-';
            const d = new Date(dateStr.replace(/-/g, '/'));
            if (isNaN(d.getTime())) return dateStr;
            const months = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];
            const day = String(d.getDate()).padStart(2, '0');
            const mon = months[d.getMonth()];
            const yr = d.getFullYear();
            return `${day} ${mon} ${yr}`;
        }

        function formatTanggalDisplay(dateStr) {
            if (!dateStr) return '-';
            const parts = dateStr.split(' ')[0].split('-');
            if (parts.length === 3) {
                return `${parts[2]}-${parts[1]}-${parts[0]}`;
            }
            return dateStr;
        }

        function escapeHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        // Window resize event for canvas
        window.addEventListener('resize', function() {
            if ($('#method_canvas').is(':checked') && $('#modalSignTagihan').is(':visible')) {
                resizeCanvas();
            }
        });
    });
</script>
