<style>
    #pencairan_akad_modal .keu-pa-layout {
        display: flex;
        flex-wrap: nowrap;
        gap: 1rem;
        min-width: 0;
    }

    #pencairan_akad_modal .keu-pa-sidebar {
        align-self: flex-start;
        flex: 0 0 320px;
        max-height: calc(100vh - 8rem);
        max-width: 320px;
        overflow-y: auto;
        position: sticky;
        top: 0;
        z-index: 2;
    }

    #pencairan_akad_modal .keu-pa-content {
        flex: 1 1 auto;
        max-width: calc(100% - 336px);
        min-width: 0;
    }

    #pencairan_akad_modal .card {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        box-shadow: none;
        margin-bottom: 1rem;
        overflow: hidden;
    }

    #pencairan_akad_modal .card-body {
        padding: 1rem;
    }

    #pencairan_akad_modal .bg-primary {
        background: linear-gradient(145deg, #2057a3 0%, #1f7a8c 100%) !important;
    }

    #pencairan_akad_modal .keu-pa-summary-row {
        align-items: center;
        border-bottom: 1px solid #eef2f7;
        display: flex;
        gap: .75rem;
        justify-content: space-between;
        padding: .45rem 0;
    }

    #pencairan_akad_modal .keu-pa-summary-row:last-child {
        border-bottom: 0;
    }

    #pencairan_akad_modal .keu-pa-summary-row span {
        color: #6b7280;
        font-size: .78rem;
        font-weight: 700;
    }

    #pencairan_akad_modal .keu-pa-summary-row strong {
        color: #111827;
        font-size: .9rem;
        text-align: right;
    }

    #pencairan_akad_modal .nav-tabs {
        border-bottom: 1px solid #d8dde3;
        flex-wrap: nowrap;
        overflow-x: auto;
    }

    #pencairan_akad_modal .nav-tabs .nav-link {
        border-radius: 6px 6px 0 0;
        color: #4b5563;
        font-weight: 700;
        white-space: nowrap;
    }

    #pencairan_akad_modal .nav-tabs .nav-link.active {
        color: #2057a3;
    }

    #pencairan_akad_modal .table thead th {
        background: #f8fafc;
        border-bottom: 1px solid #e5e7eb;
        color: #374151;
        font-size: .78rem;
        font-weight: 700;
        white-space: nowrap;
    }

    #pencairan_akad_modal .table tbody td {
        font-size: .84rem;
        vertical-align: middle;
    }

    #pencairan_akad_modal .pa-history-timeline {
        position: relative;
        padding-left: 50px;
    }

    #pencairan_akad_modal .pa-history-timeline .timeline-item {
        position: relative;
    }

    #pencairan_akad_modal .pa-history-timeline .timeline-item::before {
        background: #e5e7eb;
        content: "";
        height: calc(100% - 30px);
        left: -32px;
        position: absolute;
        top: 40px;
        width: 2px;
    }

    #pencairan_akad_modal .pa-history-timeline .timeline-item:last-child::before {
        display: none;
    }

    #pencairan_akad_modal .pa-history-timeline .timeline-icon {
        align-items: center;
        border-radius: 50%;
        box-shadow: 0 4px 6px rgba(15, 23, 42, .12);
        color: #fff;
        display: flex;
        height: 40px;
        justify-content: center;
        left: -50px;
        position: absolute;
        width: 40px;
        z-index: 1;
    }

    @media (max-width: 1199.98px) {
        #pencairan_akad_modal .keu-pa-layout {
            flex-wrap: wrap;
        }

        #pencairan_akad_modal .keu-pa-sidebar,
        #pencairan_akad_modal .keu-pa-content {
            flex: 0 0 100%;
            max-width: 100%;
        }

        #pencairan_akad_modal .keu-pa-sidebar {
            max-height: none;
            overflow-y: visible;
            position: static;
        }
    }

    #pencairan_akad_modal .dropzone {
        position: relative;
        border: 2px dashed #ced4da;
        border-radius: .75rem;
        background: #fff;
        transition: .15s border-color, .15s background;
    }

    #pencairan_akad_modal .dropzone-lg {
        min-height: 90px;
    }

    #pencairan_akad_modal .dz-input {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
    }

    #pencairan_akad_modal .dz-inner {
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 1rem;
    }

    #pencairan_akad_modal .dz-placeholder {
        pointer-events: none;
    }

    #pencairan_akad_modal .dz-preview {
        display: none;
        width: 100%;
    }

    #pencairan_akad_modal .pa-kpi-icon {
        align-items: center;
        border-radius: 10px;
        display: flex;
        flex: 0 0 40px;
        height: 40px;
        justify-content: center;
        width: 40px;
    }

    #pencairan_akad_modal .pa-kpi-icon-sm {
        flex: 0 0 30px;
        height: 30px;
        width: 30px;
    }

    #pencairan_akad_modal .pa-kpi-icon i {
        font-size: 1rem;
    }

    #pencairan_akad_modal .pa-kpi-icon-sm i {
        font-size: .8rem;
    }

    #pencairan_akad_modal .pa-kpi-bank-box {
        align-items: center;
        background: #eef2ff;
        border-radius: 8px;
        display: flex;
        gap: .5rem;
        padding: .5rem .75rem;
    }

    #pencairan_akad_modal .pa-kpi-item {
        margin-bottom: 1rem;
    }

    #pencairan_akad_modal .pa-kpi-progress {
        background: #f1f5f9;
        border-radius: 999px;
        height: 6px;
        overflow: hidden;
    }

    #pencairan_akad_modal .pa-kpi-progress-bar {
        border-radius: 999px;
        height: 100%;
        transition: width .4s ease;
        width: 0%;
    }

    #pencairan_akad_modal .pa-kpi-footer {
        background: #f8fafc;
        border-radius: 8px;
        padding: .85rem;
    }

    #pencairan_akad_modal .pa-kpi-footer-col {
        padding: 0 .5rem;
    }

    #pencairan_akad_modal .pa-kpi-footer-col-border {
        border-left: 1px solid #e5e7eb;
    }

    #pencairan_akad_modal .pa-kpi-note {
        color: #6b7280;
        font-size: .72rem;
        margin-top: .85rem;
    }

    @media (max-width: 767.98px) {
        #pencairan_akad_modal .modal-dialog {
            max-width: calc(100vw - 12px);
            margin: .5rem auto;
        }

        #pencairan_akad_modal .keu-pa-body {
            max-height: calc(100vh - 5.5rem);
            padding: .75rem;
        }

        #pencairan_akad_modal .card-body {
            padding: .85rem;
        }
    }
</style>

<!-- ################################## Pencairan Akad ##########################################-->
<div class="modal fade text-left" id="pencairan_akad_modal" tabindex="-1" role="dialog"
    aria-labelledby="pencairan_akad_modal_label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pencairan_akad_modal_label">Pencairan Akad</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body keu-pa-body">
                <div class="keu-pa-layout">
                    <aside class="col-md-4">
                        <div class="card">
                            <div class="card-body bg-primary text-light">
                                <p class="modal-title label_alamat" id="pa-label-alamat"></p>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <div class="divider divider-left">
                                    <div class="divider-text">Info Akad</div>
                                </div>
                                <h6><i class="fas fa-users"></i> Konsumen</h6>
                                <h5><strong><span id="pa-label-konsumen">-</span></strong></h5>
                                <h6><i class="fas fa-check-circle"></i> Status</h6>
                                <h5 class="mb-0"><strong><span id="pa-status-mkdt">-</span></strong></h5>
                            </div>
                        </div>
                        <div class="card mb-0">
                            <div class="card-body pa-kpi-card">
                                <div class="d-flex align-items-start justify-content-between flex-wrap mb-1" style="gap:.6rem;">
                                    <div>
                                        <div class="d-flex align-items-center mb-50" style="gap:.6rem;">
                                            <div class="pa-kpi-icon bg-light-primary text-primary"><i class="fas fa-home"></i></div>
                                            <h6 class="mb-0 font-weight-bold">Ringkasan KPR</h6>
                                        </div>
                                        <span class="text-muted d-block" style="font-size:.76rem;">Plafon KPR Disetujui</span>
                                        <h3 class="text-primary font-weight-bolder mb-0" id="pa-acc-kpr-label">Rp 0</h3>
                                    </div>
                                    <div class="pa-kpi-bank-box" id="pa-bank-box" style="display:none;">
                                        <i class="fas fa-university text-primary"></i>
                                        <div>
                                            <span class="text-muted d-block" style="font-size:.66rem;">Bank Pemberi KPR</span>
                                            <strong id="pa-bank-label">-</strong>
                                        </div>
                                    </div>
                                </div>

                                <div class="pa-kpi-item">
                                    <div class="d-flex align-items-center" style="gap:.65rem;">
                                        <div class="flex-grow-1 min-width-0">
                                            <div class="d-flex align-items-start justify-content-between" style="gap:.5rem;">
                                                <div>
                                                    <strong class="text-success d-block">Dana Sudah Cair</strong>
                                                    <span class="text-muted" style="font-size:.7rem;">Dana yang sudah dicairkan bank</span>
                                                </div>
                                                <div class="text-right">
                                                    <strong id="pa-total-cair-label">Rp 0</strong>
                                                    <span class="badge badge-pill badge-success ml-25" id="pa-total-cair-pct">0%</span>
                                                </div>
                                            </div>
                                            <div class="pa-kpi-progress mt-50"><div class="pa-kpi-progress-bar bg-success" id="pa-total-cair-bar"></div></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="pa-kpi-item">
                                    <div class="d-flex align-items-center" style="gap:.65rem;">
                                        <div class="flex-grow-1 min-width-0">
                                            <div class="d-flex align-items-start justify-content-between" style="gap:.5rem;">
                                                <div>
                                                    <strong class="text-warning d-block">Pengajuan Pencairan</strong>
                                                    <span class="text-muted" style="font-size:.7rem;">Dana yang sedang diajukan</span>
                                                </div>
                                                <div class="text-right">
                                                    <strong id="pa-total-pengajuan-label">Rp 0</strong>
                                                    <span class="badge badge-pill badge-warning ml-25" id="pa-total-pengajuan-pct">0%</span>
                                                </div>
                                            </div>
                                            <div class="pa-kpi-progress mt-50"><div class="pa-kpi-progress-bar bg-warning" id="pa-total-pengajuan-bar"></div></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="pa-kpi-item mb-0">
                                    <div class="d-flex align-items-center" style="gap:.65rem;">
                                        <div class="flex-grow-1 min-width-0">
                                            <div class="d-flex align-items-start justify-content-between" style="gap:.5rem;">
                                                <div>
                                                    <strong class="text-danger d-block">Sisa Dana Belum Cair</strong>
                                                    <span class="text-muted" style="font-size:.7rem;">Sisa dana yang belum dicairkan</span>
                                                </div>
                                                <div class="text-right">
                                                    <strong id="pa-sisa-hasil-akad-label">Rp 0</strong>
                                                    <span class="badge badge-pill badge-danger ml-25" id="pa-sisa-hasil-akad-pct">0%</span>
                                                </div>
                                            </div>
                                            <div class="pa-kpi-progress mt-50"><div class="pa-kpi-progress-bar bg-danger" id="pa-sisa-hasil-akad-bar"></div></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="pa-kpi-footer">
                                    <div class="row no-gutters">
                                        <div class="col-6 pa-kpi-footer-col">
                                            <div class="d-flex align-items-center" style="gap:.5rem;">
                                                <div class="pa-kpi-icon pa-kpi-icon-sm bg-light-info text-info"><i class="fas fa-file-invoice"></i></div>
                                                <strong>Dana Akad Bersih</strong>
                                            </div>
                                            <span class="text-muted d-block mt-50" style="font-size:.68rem;">Dana yang dapat dicairkan setelah dikurangi retensi</span>
                                            <strong class="text-info d-block mt-50" id="pa-hasil-akad-label" style="font-size:1.05rem;">Rp 0</strong>
                                        </div>
                                        <div class="col-6 pa-kpi-footer-col pa-kpi-footer-col-border">
                                            <div class="d-flex align-items-center" style="gap:.5rem;">
                                                <div class="pa-kpi-icon pa-kpi-icon-sm bg-light-primary text-primary"><i class="fas fa-lock"></i></div>
                                                <strong>Dana Retensi</strong>
                                            </div>
                                            <span class="text-muted d-block mt-50" style="font-size:.68rem;">Dana yang ditahan oleh bank dengan kondisi tertentu</span>
                                            <strong class="text-primary d-block mt-50" id="pa-total-retensi-label" style="font-size:1.05rem;">Rp 0</strong>
                                        </div>
                                    </div>
                                </div>

                                <div class="pa-kpi-note">
                                    <i class="fas fa-info-circle mr-25"></i> Persentase dihitung berdasarkan Plafon KPR Disetujui.
                                </div>
                            </div>
                        </div>
                    </aside>
                    <section class="col-md-8">
                        <div class="card">
                            <div class="card-body pb-0">
                                <ul class="nav nav-tabs mb-1" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" id="pa-retensi-tab" data-toggle="tab"
                                            href="#pa-retensi-pane" role="tab">Retensi</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="pa-tenor-tab" data-toggle="tab"
                                            href="#pa-tenor-pane" role="tab">Dana Akad Bersih</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="pa-pengajuan-pencairan-tab" data-toggle="tab"
                                            href="#pa-pengajuan-pencairan-pane" role="tab">Pengajuan &amp; Pencairan</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="pa-history-tab" data-toggle="tab"
                                            href="#pa-history-pane" role="tab" onclick="loadPencairanAkadHistory()">History</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="tab-content">
                            <div class="tab-pane show active" id="pa-retensi-pane" role="tabpanel">
                                <div class="card mb-0">
                                    <div class="card-body">
                                        <div class="divider divider-left">
                                            <div class="divider-text">Item Retensi (Dana Jaminan Bank)</div>
                                        </div>
                                        <div class="form-row align-items-end mb-1">
                                            <div class="col-md-8">
                                                <label class="mb-25">Pilih Item Retensi</label>
                                                <select class="form-control form-control-sm" id="pa-retensi-picker"></select>
                                            </div>
                                            <div class="col-md-4">
                                                <button type="button" class="btn btn-outline-primary btn-sm btn-block" onclick="addPencairanAkadRetensiRow()">
                                                    <i class="fas fa-plus"></i> Tambah
                                                </button>
                                            </div>
                                        </div>
                                        <div id="pa-retensi_here"></div>
                                        <button type="button" class="btn btn-primary mt-1" onclick="savePencairanAkadRetensi(); return false;">
                                            Simpan Retensi
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane" id="pa-tenor-pane" role="tabpanel">
                                <div class="card mb-0">
                                    <div class="card-body">
                                        <div class="divider divider-left">
                                            <div class="divider-text">Termin Pencairan Dana Akad Bersih (manual, total tidak boleh melebihi Dana Akad Bersih)</div>
                                        </div>
                                        <div class="alert alert-light-primary py-1 mb-1" id="pa-tenor-sisa-box">
                                            Sisa Dana Akad Bersih: <strong id="pa-tenor-sisa-label">Rp 0</strong>
                                        </div>
                                        <div id="pa-tenor_here"></div>
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="addPencairanAkadTenorRow()">
                                            <i class="fas fa-plus"></i> Tambah Termin
                                        </button>
                                        <button type="button" class="btn btn-primary btn-sm" onclick="savePencairanAkadTenor(); return false;">
                                            Simpan
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane" id="pa-pengajuan-pencairan-pane" role="tabpanel">
                                <div class="card mb-0">
                                    <div class="card-body">
                                        <div class="d-flex flex-wrap" style="gap: .5rem;">
                                            <button type="button" class="btn btn-primary btn-sm" id="pa-btn-show-pengajuan">
                                                <i class="fas fa-paper-plane"></i> Buat Pengajuan Pencairan
                                            </button>
                                            <button type="button" class="btn btn-success btn-sm" id="pa-btn-show-pencairan">
                                                <i class="fas fa-money-bill"></i> Pencairan Dana
                                            </button>
                                        </div>

                                        <div class="position-relative mt-1" id="pa-pengajuan-form-wrap" style="display: none;">
                                            <button type="button" class="btn btn-sm btn-outline-secondary pa-form-close"
                                                data-target="#pa-pengajuan-form-wrap" style="position: absolute; top: 0; right: 0;">
                                                <i class="fas fa-times"></i> Tutup
                                            </button>
                                            <div class="divider divider-left">
                                                <div class="divider-text">Buat Pengajuan ke Bank</div>
                                            </div>
                                            <form id="form-pencairan-akad-pengajuan" enctype="multipart/form-data" autocomplete="off">
                                                <input type="hidden" id="pa-pengajuan-id_plan" name="id_plan" value="">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="divider divider-left">
                                                            <div class="divider-text">Pilih Item Pencairan</div>
                                                        </div>
                                                        <div id="pa-pengajuan-item_here"></div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label>Tanggal Pengajuan</label>
                                                            <input type="date" class="form-control" name="tanggal_pengajuan" required>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Tanggal Rencana Cair</label>
                                                            <input type="date" class="form-control" name="tanggal_rencana_cair">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Lampiran Surat (PDF/Gambar, opsional)</label>
                                                            <div class="dropzone dropzone-lg custom-file" id="pa-dz-lampiran">
                                                                <input type="file" class="custom-file-input dz-input" id="pa-lampiran-input"
                                                                    name="lampiran_surat" accept="application/pdf,image/*">
                                                                <div class="dz-inner">
                                                                    <div class="dz-preview" id="pa-lampiran-preview"></div>
                                                                    <div class="dz-placeholder">
                                                                        <div class="h6 mb-1">Tarik &amp; letakkan file ke sini</div>
                                                                        <div class="text-muted small">atau klik / paste (Ctrl+V) — PDF/PNG/JPG</div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Catatan Pengajuan</label>
                                                            <textarea class="form-control" name="catatan" rows="2"></textarea>
                                                        </div>
                                                        <button type="submit" class="btn btn-primary mt-1">Simpan Pengajuan</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>

                                        <div class="position-relative mt-1" id="pa-pencairan-form-wrap" style="display: none;">
                                            <button type="button" class="btn btn-sm btn-outline-secondary pa-form-close"
                                                data-target="#pa-pencairan-form-wrap" style="position: absolute; top: 0; right: 0;">
                                                <i class="fas fa-times"></i> Tutup
                                            </button>
                                            <div class="divider divider-left">
                                                <div class="divider-text">Catat Pencairan (Partial Manual per Item)</div>
                                            </div>
                                            <div class="form-group">
                                                <label>Pilih Pengajuan</label>
                                                <select class="form-control" id="pa-cair-select_pengajuan" onchange="renderPencairanAkadCairForm()"></select>
                                            </div>
                                            <form id="form-pencairan-akad-cair" autocomplete="off">
                                                <input type="hidden" id="pa-cair-id_pengajuan" name="id_pengajuan" value="">
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label>Tanggal Cair</label>
                                                            <input type="date" class="form-control" name="tanggal_cair" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <div class="form-group">
                                                            <label>Catatan</label>
                                                            <input type="text" class="form-control" name="catatan">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div id="pa-cair-detail_here"></div>
                                                <button type="submit" class="btn btn-primary mt-1">Simpan Pencairan</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <div class="card mb-0">
                                    <div class="card-body">
                                        <div class="divider divider-left">
                                            <div class="divider-text">Daftar Pengajuan</div>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered mb-0" id="tbl-pencairan-akad-pengajuan">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Tgl Pengajuan</th>
                                                        <th>Rencana Cair</th>
                                                        <th>Item Diajukan</th>
                                                        <th class="text-right">Total</th>
                                                        <th class="text-right">Cair</th>
                                                        <th>Status</th>
                                                        <th>PIC</th>
                                                        <th>Lampiran</th>
                                                        <th>Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane" id="pa-history-pane" role="tabpanel">
                                <div class="card mb-0">
                                    <div class="card-body">
                                        <div class="divider divider-left">
                                            <div class="divider-text">History Pencairan Akad</div>
                                        </div>
                                        <div class="pa-history-timeline" id="pa-history_here"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
            <div class="modal-footer">
                <button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
