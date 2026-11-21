<!-- Target Interactive Floating Card (portrait, mounted on canvas when Mode Set Target active) -->
<div id="target-interactive-bar" class="target-interactive-bar" hidden aria-live="polite">
    <div id="target-interactive-card" class="target-interactive-card">
        <div class="target-interactive-top">
            <div class="target-interactive-brand">
                <span class="target-interactive-icon"><i class="fas fa-bullseye"></i></span>
                <div class="target-interactive-title-wrap">
                    <div class="target-interactive-title-row">
                        <span class="target-interactive-title">Mode Set Target</span>
                        <span id="target-bar-count" class="target-bar-count-badge">0 kavling</span>
                    </div>
                    <div id="target-bar-hint" class="target-bar-hint">
                        <i class="fas fa-mouse-pointer mr-25"></i>Klik kavling kosong di kanvas untuk pilih, lalu Simpan ke Target
                    </div>
                </div>
            </div>
            <div class="target-interactive-actions">
                <button type="button" id="target-bar-manage-btn" class="btn btn-sm btn-outline-primary target-bar-btn">
                    <i class="fas fa-sliders-h mr-25"></i>Kelola &amp; History
                </button>
                <button type="button" id="target-bar-exit-btn" class="btn btn-sm btn-outline-secondary target-bar-btn">
                    <i class="fas fa-times mr-25"></i>Selesai
                </button>
            </div>
        </div>

        <div class="target-interactive-controls">
            <div class="target-control-group target-control-year">
                <label for="target-bar-year" class="target-control-label">Tahun Target</label>
                <select id="target-bar-year" class="form-control form-control-sm target-bar-year-select"></select>
            </div>

            <div class="target-control-group target-control-source">
                <span class="target-control-label">Sumber Tipe Target</span>
                <div class="target-source-toggle" role="radiogroup" aria-label="Sumber Tipe Target">
                    <label class="target-source-option is-active" for="target-source-master">
                        <input type="radio" id="target-source-master" name="target_bar_tipe_source" value="master" checked>
                        <span>Master Tipe Aktif</span>
                    </label>
                    <label class="target-source-option" for="target-source-manual">
                        <input type="radio" id="target-source-manual" name="target_bar_tipe_source" value="manual">
                        <span>Ketik Manual</span>
                    </label>
                </div>
            </div>

            <div class="target-control-group target-control-tipe">
                <label for="target-bar-tipe-select" class="target-control-label" id="target-bar-tipe-label">Pilih Master Tipe</label>
                <div id="target-bar-master-wrap">
                    <select id="target-bar-tipe-select" class="form-control form-control-sm target-bar-tipe-select" style="width: 100%;"></select>
                </div>
                <div id="target-bar-manual-wrap" hidden>
                    <input type="text"
                        id="target-bar-tipe-manual"
                        class="form-control form-control-sm target-bar-tipe-input"
                        maxlength="255"
                        placeholder="Contoh: 36/72">
                </div>
                <div id="target-bar-tipe-preview" class="target-bar-tipe-preview" hidden></div>
            </div>
        </div>

        <div class="target-staged-section">
            <div class="target-staged-header">
                <div class="target-staged-title-group">
                    <span class="target-control-label mb-0">Kavling Terpilih</span>
                    <span id="target-bar-staged-count" class="target-staged-count-badge">0 terpilih</span>
                </div>
                <button type="button" id="target-bar-staged-reset-btn" class="btn btn-link btn-sm target-staged-reset-btn p-0" disabled>
                    <i class="fas fa-undo-alt mr-25"></i>Reset
                </button>
            </div>
            <div id="target-bar-staged-list" class="target-staged-list">
                <div class="target-staged-empty">Klik kavling kosong di kanvas untuk memilih.</div>
            </div>
            <button type="button" id="target-bar-save-staged-btn" class="btn btn-primary btn-sm btn-block target-bar-save-staged-btn" disabled>
                <i class="fas fa-check-circle mr-25"></i>Simpan ke Target
            </button>
        </div>
    </div>
</div>

<!-- Modal Kelola Target & History Versioning -->
<div class="modal fade" id="modal-target-siteplan" tabindex="-1" role="dialog" aria-labelledby="modal-target-siteplan-title" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-target-siteplan-title">
                    <span class="target-modal-title-icon"><i class="fas fa-bullseye"></i></span>
                    <span>Kelola Target Siteplan</span>
                    <span id="target-modal-project-badge" class="target-modal-project-pill"></span>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-pills target-modal-tabs mb-1" id="target-modal-tablist" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active" id="tab-target-kavling" data-toggle="pill" href="#panel-target-kavling" role="tab" aria-controls="panel-target-kavling" aria-selected="true">
                            <i class="fas fa-th-list mr-50"></i>Daftar Kavling Target
                            <span id="target-tab-kavling-count" class="badge target-tab-badge ml-25">0</span>
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="tab-target-history" data-toggle="pill" href="#panel-target-history" role="tab" aria-controls="panel-target-history" aria-selected="false">
                            <i class="fas fa-history mr-50"></i>History Versioning
                            <span id="target-tab-history-count" class="badge target-tab-badge ml-25">0</span>
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="tab-target-requests" data-toggle="pill" href="#panel-target-requests" role="tab" aria-controls="panel-target-requests" aria-selected="false">
                            <i class="fas fa-exchange-alt mr-50"></i>Request Perubahan
                            <span id="target-request-pending-badge" class="badge badge-warning target-request-pending-badge ml-25" hidden>0</span>
                        </a>
                    </li>
                </ul>

                <div class="tab-content" id="target-modal-tabcontent">
                    <!-- Tab 1: Daftar Kavling Target -->
                    <div class="tab-pane fade show active" id="panel-target-kavling" role="tabpanel" aria-labelledby="tab-target-kavling">
                        <div class="card target-modal-card">
                            <div class="card-body py-1">
                                <div class="d-flex flex-wrap align-items-end justify-content-between" style="gap: .75rem;">
                                    <div class="d-flex flex-wrap align-items-end" style="gap: .75rem;">
                                        <div class="form-group mb-0">
                                            <label for="target-modal-year-select" class="target-field-label">Tahun Target</label>
                                            <select id="target-modal-year-select" class="form-control form-control-sm target-year-select"></select>
                                        </div>
                                        <div id="target-selected-count" class="target-summary-pill">0 kavling target</div>
                                    </div>
                                    <button type="button" id="target-btn-add-from-canvas" class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus mr-25"></i>Tambah dari Kanvas
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="divider divider-left" id="target-notes-divider">
                            <div class="divider-text">Catatan Target Tahun <span id="target-modal-year-label">-</span></div>
                        </div>

                        <div class="card target-modal-card" id="target-notes-card">
                            <div class="card-body">
                                <form id="fm-target-siteplan">
                                    <input type="hidden" id="target-id_target" name="id_target">
                                    <input type="hidden" id="target-id_proyek" name="id_proyek">
                                    <input type="hidden" id="target-id_kavling" name="id_kavling">
                                    <input type="hidden" id="target-tahun_target" name="tahun_target">
                                    <div class="row align-items-end">
                                        <div class="col-md-4 col-12 mb-1 mb-md-0">
                                            <label for="target-nama_proyek" class="target-field-label">Proyek</label>
                                            <input type="text" class="form-control form-control-sm" id="target-nama_proyek" readonly>
                                        </div>
                                        <div class="col-md-6 col-12 mb-1 mb-md-0">
                                            <label for="target-deskripsi" class="target-field-label">Deskripsi / Catatan Target</label>
                                            <input type="text" class="form-control form-control-sm" id="target-deskripsi" name="deskripsi" placeholder="Catatan target tahunan (opsional)">
                                        </div>
                                        <div class="col-md-2 col-12">
                                            <button type="button" class="btn btn-outline-primary btn-sm btn-block" id="target-save-btn" onclick="save_target_siteplan()">
                                                <i class="fas fa-save mr-25"></i>Simpan Catatan
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="divider divider-left">
                            <div class="divider-text">Daftar Kavling Target</div>
                        </div>

                        <div class="card target-modal-card mb-0">
                            <div class="card-header target-table-card-header">
                                <div class="d-flex flex-wrap align-items-center justify-content-between w-100" style="gap: .5rem;">
                                    <div class="target-table-meta" id="target-kavling-summary">Memuat data kavling target...</div>
                                    <div class="target-search-wrap">
                                        <input type="search" id="target-kavling-search" class="form-control form-control-sm" placeholder="Cari blok, no kavling, atau tipe...">
                                    </div>
                                </div>
                            </div>
                            <div id="target-batch-action-bar" class="target-batch-action-bar" hidden aria-live="polite">
                                <div class="target-batch-action-info">
                                    <span class="target-batch-check-icon"><i class="fas fa-check-square"></i></span>
                                    <span id="target-batch-selected-count" class="target-batch-count-badge">0 kavling dipilih</span>
                                </div>
                                <div class="target-batch-action-buttons">
                                    <button type="button" id="target-btn-batch-edit-tipe" class="btn btn-primary btn-sm" hidden>
                                        <i class="fas fa-edit mr-25"></i>Ubah Tipe Batch
                                    </button>
                                    <button type="button" id="target-btn-batch-req-planning" class="btn btn-outline-warning btn-sm" hidden>
                                        <i class="fas fa-paper-plane mr-25"></i>Request Ubah ke Planning
                                    </button>
                                    <button type="button" id="target-btn-batch-remove" class="btn btn-outline-danger btn-sm" hidden>
                                        <i class="fas fa-trash-alt mr-25"></i>Hapus dari Target
                                    </button>
                                    <button type="button" id="target-btn-batch-clear" class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-times mr-25"></i>Batal Pilih
                                    </button>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover table-sm mb-0" id="target-kavling-table">
                                    <thead>
                                        <tr>
                                            <th class="text-center" style="width: 38px;">
                                                <input type="checkbox" id="target-check-all" aria-label="Pilih semua kavling target">
                                            </th>
                                            <th class="text-center" style="width: 48px;">No</th>
                                            <th>Cluster / Jalan &amp; Kavling</th>
                                            <th>Tipe Rumah Saat Ini</th>
                                            <th style="min-width: 190px;">Tipe Target</th>
                                            <th>Status MKDT</th>
                                            <th class="text-center" style="min-width: 175px;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="target-kavling-tbody">
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-2">Memuat daftar kavling target...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 2: History Versioning -->
                    <div class="tab-pane fade" id="panel-target-history" role="tabpanel" aria-labelledby="tab-target-history">
                        <div class="card target-modal-card">
                            <div class="card-body py-1">
                                <div class="d-flex flex-wrap align-items-end justify-content-between" style="gap: .75rem;">
                                    <div class="form-group mb-0">
                                        <label for="target-history-year-select" class="target-field-label">Pilih Target Tahun</label>
                                        <select id="target-history-year-select" class="form-control form-control-sm target-year-select"></select>
                                    </div>
                                    <button type="button" id="target-history-refresh-btn" class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-sync-alt mr-25"></i>Muat Ulang History
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="divider divider-left">
                            <div class="divider-text">Riwayat Perubahan Versi</div>
                        </div>

                        <div id="target-history-here" class="target-history-list">
                            <div class="card target-modal-card mb-0">
                                <div class="card-body text-center text-muted py-2">Pilih target untuk melihat histori perubahan.</div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 3: Request Perubahan Tipe Target -->
                    <div class="tab-pane fade" id="panel-target-requests" role="tabpanel" aria-labelledby="tab-target-requests">
                        <div class="card target-modal-card">
                            <div class="card-body py-1">
                                <div class="target-requests-toolbar">
                                    <div class="target-requests-filters">
                                        <div class="form-group mb-0">
                                            <label for="target-requests-status-filter" class="target-field-label">Status Pengajuan</label>
                                            <select id="target-requests-status-filter" class="form-control form-control-sm target-requests-select">
                                                <option value="all">Semua</option>
                                                <option value="pending">Pending</option>
                                                <option value="approved">Disetujui</option>
                                                <option value="rejected">Ditolak</option>
                                            </select>
                                        </div>
                                        <div class="form-group mb-0">
                                            <label for="target-requests-year-select" class="target-field-label">Tahun Target</label>
                                            <select id="target-requests-year-select" class="form-control form-control-sm target-year-select">
                                                <option value="">Semua Tahun</option>
                                            </select>
                                        </div>
                                        <div id="target-requests-summary" class="target-summary-pill">0 pengajuan</div>
                                    </div>
                                    <button type="button" id="target-requests-refresh-btn" class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-sync-alt mr-25"></i>Muat Ulang
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="divider divider-left">
                            <div class="divider-text">Daftar Pengajuan Perubahan Tipe Target</div>
                        </div>

                        <div class="card target-modal-card mb-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-sm mb-0" id="target-requests-table">
                                    <thead>
                                        <tr>
                                            <th>Tahun &amp; Kavling</th>
                                            <th>Tipe Target Lama &rarr; Tipe Baru Usulan</th>
                                            <th>Pengaju &amp; Catatan Request</th>
                                            <th>Status &amp; Pemeriksa</th>
                                            <th class="text-center" style="width: 148px;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="target-requests-tbody">
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-2">Memuat daftar pengajuan perubahan tipe target...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <small class="text-muted">Setiap penambahan, perubahan tipe, atau penghapusan kavling otomatis tercatat sebagai versi baru.</small>
                <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Preview Gambar & Spesifikasi Teknis Master Tipe -->
<div class="modal fade" id="modal-target-tipe-spec" tabindex="-1" role="dialog" aria-labelledby="modal-target-tipe-spec-title" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-target-tipe-spec-title">
                    <span class="target-spec-modal-icon"><i class="fas fa-home"></i></span>
                    <span id="target-spec-modal-heading">Detail Spesifikasi Tipe</span>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="target-spec-modal-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit Tipe Target (Single & Batch) -->
<div class="modal fade" id="modal-target-edit-tipe" tabindex="-1" role="dialog" aria-labelledby="modal-target-edit-tipe-title" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-target-edit-tipe-title">
                    <span class="target-edit-modal-icon"><i class="fas fa-edit"></i></span>
                    <span id="target-edit-tipe-modal-heading">Ubah Tipe Target Kavling</span>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="card target-edit-modal-card">
                    <div class="card-body">
                        <div class="divider divider-left mt-0">
                            <div class="divider-text">Kavling yang Akan Diubah</div>
                        </div>
                        <div id="target-edit-tipe-kavling-summary" class="target-edit-kavling-summary"></div>
                    </div>
                </div>

                <div class="card target-edit-modal-card mb-0">
                    <div class="card-body">
                        <div class="divider divider-left mt-0">
                            <div class="divider-text">Pilih Tipe Target Baru</div>
                        </div>

                        <div class="form-group mb-1">
                            <span class="target-edit-field-label">Sumber Tipe Target</span>
                            <div class="target-edit-source-toggle" role="radiogroup" aria-label="Sumber Tipe Target">
                                <label class="target-edit-source-option is-active" for="target-edit-source-master">
                                    <input type="radio" id="target-edit-source-master" name="target_edit_tipe_source" value="master" checked>
                                    <span>Master Tipe Aktif</span>
                                </label>
                                <label class="target-edit-source-option" for="target-edit-source-manual">
                                    <input type="radio" id="target-edit-source-manual" name="target_edit_tipe_source" value="manual">
                                    <span>Ketik Manual</span>
                                </label>
                            </div>
                        </div>

                        <div class="form-group mb-0" id="target-edit-master-wrap">
                            <label for="target-edit-tipe-select" class="target-edit-field-label">Master Tipe Aktif</label>
                            <select id="target-edit-tipe-select" class="form-control form-control-sm" style="width: 100%;"></select>
                        </div>

                        <div class="form-group mb-0" id="target-edit-manual-wrap" hidden>
                            <label for="target-edit-tipe-manual" class="target-edit-field-label">Nama / Kode Tipe Manual</label>
                            <input type="text"
                                id="target-edit-tipe-manual"
                                class="form-control form-control-sm target-edit-manual-input"
                                maxlength="255"
                                placeholder="Contoh: 36/72">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary btn-sm" id="target-edit-tipe-submit-btn">
                    <i class="fas fa-check mr-25"></i>Simpan Perubahan Tipe
                </button>
            </div>
        </div>
    </div>
</div>

<script src="<?= base_url() ?>assets/js/siteplan/target.js?v=<?= filemtime(FCPATH . 'assets/js/siteplan/target.js') ?>"></script>
