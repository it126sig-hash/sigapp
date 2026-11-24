<?php
$rkRoleId = 0;
if (isset($k) && $k !== '') {
    $rkRoleId = (int) $k;
} elseif (function_exists('user') && user()) {
    foreach (user()->getRoles() as $rkKey => $rkVal) {
        $rkRoleId = (int) $rkKey;
    }
}

$canCreateRequestKavling  = $canCreateRequestKavling ?? in_array($rkRoleId, [1, 4], true);
$canProcessRequestKavling = $canProcessRequestKavling ?? in_array($rkRoleId, [1, 6], true);
$rkProyekId               = isset($data['proyek']->id_proyek) ? (int) $data['proyek']->id_proyek : 0;
$rkProyekNama             = isset($data['proyek']->nama_proyek) ? (string) $data['proyek']->nama_proyek : '-';
?>
<script>
    window.SITEPLAN_REQUEST_KAVLING_CONFIG = <?= json_encode([
        'idProyek'    => $rkProyekId,
        'namaProyek'  => $rkProyekNama,
        'canCreate'   => (bool) $canCreateRequestKavling,
        'canProcess'  => (bool) $canProcessRequestKavling,
        'tipePageUrl' => base_url('tipe'),
    ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>;
</script>

<?php if ($canCreateRequestKavling): ?>
<!-- Floating Canvas Picker Bar untuk Mode Pilih dari Kanvas Siteplan -->
<div id="rk-canvas-picker-bar" class="rk-canvas-picker-bar" hidden aria-live="polite">
    <div id="rk-canvas-picker-card" class="rk-canvas-picker-card">
        <div class="rk-picker-top">
            <div class="rk-picker-brand">
                <span class="rk-picker-icon"><i class="fas fa-draw-polygon"></i></span>
                <div class="rk-picker-title-wrap">
                    <div class="rk-picker-title-row">
                        <span class="rk-picker-title">Pilih Kavling di Kanvas</span>
                        <span id="rk-picker-count" class="rk-picker-count-badge">0 dipilih</span>
                    </div>
                    <div class="rk-picker-hint">
                        <i class="fas fa-mouse-pointer mr-25"></i>Klik kavling pada siteplan untuk memilih atau membatalkan pilihan (kavling berstatus Akad / Akad Indent tidak dapat dipilih).
                    </div>
                </div>
            </div>
            <div class="rk-picker-actions">
                <button type="button" id="rk-picker-done-btn" class="btn btn-primary btn-sm rk-picker-btn">
                    <i class="fas fa-check mr-25"></i>Selesai Memilih
                </button>
                <button type="button" id="rk-picker-cancel-btn" class="btn btn-outline-secondary btn-sm rk-picker-btn">
                    <i class="fas fa-times mr-25"></i>Batal
                </button>
            </div>
        </div>
        <div class="rk-picker-list-wrap">
            <div class="rk-picker-list-head">
                <span class="rk-picker-list-label">Kavling Terpilih</span>
                <button type="button" id="rk-picker-clear-btn" class="btn btn-link btn-sm rk-picker-clear-btn p-0" disabled>
                    <i class="fas fa-undo-alt mr-25"></i>Kosongkan
                </button>
            </div>
            <div id="rk-picker-chip-list" class="rk-picker-chip-list">
                <div class="rk-picker-empty">Belum ada kavling dipilih. Klik kavling pada kanvas.</div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Modal Terpadu Request Kavling & Perubahan Tipe -->
<div class="modal fade" id="modal-request-kavling" tabindex="-1" role="dialog" aria-labelledby="modal-request-kavling-label" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-request-kavling-label">
                    <span class="rk-modal-title-icon"><i class="fas fa-exchange-alt"></i></span>
                    <span>Request Kavling &amp; Perubahan Tipe</span>
                    <span id="rk-modal-project-badge" class="rk-project-pill"><?= esc($rkProyekNama) ?></span>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <input type="hidden" id="req-id_proyek" value="<?= esc((string) $rkProyekId) ?>">

                <ul class="nav nav-pills rk-modal-tabs mb-1" id="rk-modal-tablist" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active" id="tab-rk-tambah" data-toggle="pill" href="#panel-rk-tambah" role="tab" aria-controls="panel-rk-tambah" aria-selected="true">
                            <i class="fas fa-plus-circle"></i>
                            <span>Request Tambah Kavling</span>
                            <span id="badge-rk-tab-tambah" class="rk-tab-badge is-pending" hidden>0</span>
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="tab-rk-ubah" data-toggle="pill" href="#panel-rk-ubah" role="tab" aria-controls="panel-rk-ubah" aria-selected="false">
                            <i class="fas fa-exchange-alt"></i>
                            <span>Request Ubah Kavling</span>
                            <span id="badge-rk-tab-ubah" class="rk-tab-badge is-pending" hidden>0</span>
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="tab-rk-riwayat" data-toggle="pill" href="#panel-rk-riwayat" role="tab" aria-controls="panel-rk-riwayat" aria-selected="false">
                            <i class="fas fa-history"></i>
                            <span>Riwayat</span>
                            <span id="badge-rk-tab-riwayat" class="rk-tab-badge is-neutral">0</span>
                        </a>
                    </li>
                </ul>

                <div class="tab-content" id="rk-modal-tabcontent">
                    <!-- =========================================================
                         TAB 1: REQUEST TAMBAH KAVLING
                         ========================================================= -->
                    <div class="tab-pane fade show active" id="panel-rk-tambah" role="tabpanel" aria-labelledby="tab-rk-tambah">
                        <?php if ($canCreateRequestKavling): ?>
                        <div class="divider divider-left mt-0">
                            <div class="divider-text">Form Pengajuan Tambah Kavling Baru</div>
                        </div>
                        <div class="card rk-card">
                            <div class="card-body">
                                <form id="form-rk-tambah" novalidate>
                                    <div class="row">
                                        <div class="col-lg-6 col-12 form-group">
                                            <label class="rk-field-label" for="rk-tambah-jalans">Nama Jalan (Multi-Pilih)</label>
                                            <select id="rk-tambah-jalans" name="id_jalans[]" class="form-control" multiple="multiple" style="width: 100%;"></select>
                                            <small class="rk-field-hint">
                                                Pilih satu atau lebih jalan pada proyek ini. Kosongkan bila jalan baru belum tersedia di sistem dan tulis detail lokasinya di Keterangan.
                                            </small>
                                        </div>
                                        <div class="col-lg-6 col-12 form-group">
                                            <label class="rk-field-label" for="rk-tambah-id_tipe">Tipe Rumah Available (Opsional)</label>
                                            <select id="rk-tambah-id_tipe" name="id_tipe" class="form-control" style="width: 100%;"></select>
                                            <small class="rk-field-hint">
                                                Kosongkan jika tipe rumah yang diminta belum terdaftar di Master Tipe.
                                            </small>
                                        </div>
                                    </div>

                                    <div id="rk-tambah-tipe-preview" class="rk-tipe-preview-box mb-1" hidden></div>

                                    <div class="form-group mb-1">
                                        <label class="rk-field-label" for="rk-tambah-keterangan">
                                            Keterangan / Detail Pengajuan <span class="text-danger">*</span>
                                        </label>
                                        <textarea id="rk-tambah-keterangan" name="keterangan" class="form-control" rows="3" placeholder="Contoh: Mohon buatkan 4 unit kavling baru di Jl. Mawar (No. 10-13) tipe 36/72, atau tulis nama cluster/jalan/tipe baru jika belum tersedia."></textarea>
                                    </div>

                                    <div class="d-flex flex-wrap justify-content-end" style="gap: .5rem;">
                                        <button type="button" id="btn-rk-reset-tambah" class="btn btn-outline-secondary btn-sm">
                                            <i class="fas fa-undo mr-25"></i>Reset Form
                                        </button>
                                        <button type="submit" id="btn-rk-submit-tambah" class="btn btn-primary btn-sm">
                                            <i class="fas fa-paper-plane mr-25"></i>Kirim Request Tambah Kavling
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if ($canCreateRequestKavling && ! $canProcessRequestKavling): ?>
                        <div class="divider divider-left">
                            <div class="divider-text">Pengajuan Tambah Kavling Menunggu (Pending)</div>
                        </div>
                        <div class="card rk-card mb-0" id="rk-mkdt-tambah-card">
                            <div class="rk-card-header">
                                <div class="d-flex align-items-center flex-wrap" style="gap: .5rem;">
                                    <span class="rk-status-pill is-pending">
                                        <i class="fas fa-clock"></i> Menunggu (<span id="rk-mkdt-tambah-pending-count">0</span>)
                                    </span>
                                    <small class="text-muted">Centang pengajuan milik Anda untuk dibatalkan sekaligus.</small>
                                </div>
                                <button type="button" id="btn-rk-mkdt-refresh-tambah" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-sync-alt mr-25"></i>Refresh
                                </button>
                            </div>
                            <div id="rk-mkdt-tambah-batch-bar" class="rk-batch-toolbar" hidden>
                                <div class="rk-batch-info">
                                    <span class="rk-batch-count-badge"><span id="rk-mkdt-tambah-selected-count">0</span> dipilih</span>
                                    <span class="rk-batch-text">Pengajuan tambah kavling siap dibatalkan</span>
                                </div>
                                <div class="rk-batch-actions">
                                    <button type="button" id="btn-rk-mkdt-batch-cancel-tambah" class="btn btn-danger btn-sm">
                                        <i class="fas fa-ban mr-25"></i>Batalkan Terpilih
                                    </button>
                                    <button type="button" id="btn-rk-mkdt-clear-tambah" class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-times mr-25"></i>Batal Pilih
                                    </button>
                                </div>
                            </div>
                            <div id="rk-mkdt-tambah-loading" class="rk-loading-box" hidden>
                                <div class="spinner-border text-primary spinner-border-sm mr-50" role="status"></div>
                                <span>Memuat pengajuan tambah kavling menunggu...</span>
                            </div>
                            <div id="rk-mkdt-tambah-table-wrap" class="table-responsive">
                                <table id="table-rk-mkdt-tambah" class="table table-hover table-sm mb-0 rk-table">
                                    <thead>
                                        <tr>
                                            <th class="text-center rk-check-col">
                                                <input type="checkbox" id="rk-mkdt-tambah-check-all" class="rk-row-checkbox" aria-label="Pilih semua pengajuan tambah kavling menunggu">
                                            </th>
                                            <th class="text-center" style="width: 48px;">No</th>
                                            <th style="min-width: 150px;">Tanggal &amp; Pengaju</th>
                                            <th style="min-width: 190px;">Daftar Jalan</th>
                                            <th style="min-width: 185px;">Tipe Rumah Usulan</th>
                                            <th style="min-width: 220px;">Keterangan MKDT</th>
                                            <th class="text-center" style="width: 110px;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbody-rk-mkdt-tambah"></tbody>
                                </table>
                            </div>
                            <div id="rk-mkdt-tambah-empty" class="rk-empty-box" hidden>
                                <i class="fas fa-inbox d-block"></i>
                                <div class="font-weight-bold">Belum ada pengajuan tambah kavling menunggu</div>
                                <small class="text-muted">Pengajuan yang Anda kirim dan masih menunggu tindak lanjut Planning akan tampil di sini.</small>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if ($canProcessRequestKavling): ?>
                        <div class="divider divider-left <?= $canCreateRequestKavling ? '' : 'mt-0' ?>">
                            <div class="divider-text">Antrean Request Tambah Kavling (Planning)</div>
                        </div>
                        <div class="card rk-card mb-0">
                            <div class="rk-card-header">
                                <div class="rk-filter-pills" id="rk-tambah-filter-pills" role="group" aria-label="Filter status tambah kavling">
                                    <button type="button" class="rk-filter-pill is-active" data-status="pending">
                                        Menunggu (<span id="rk-tambah-count-pending">0</span>)
                                    </button>
                                    <button type="button" class="rk-filter-pill" data-status="all">
                                        Semua
                                    </button>
                                </div>
                                <button type="button" id="btn-rk-refresh-tambah" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-sync-alt mr-25"></i>Refresh
                                </button>
                            </div>
                            <div id="rk-tambah-loading" class="rk-loading-box" hidden>
                                <div class="spinner-border text-primary spinner-border-sm mr-50" role="status"></div>
                                <span>Memuat daftar pengajuan tambah kavling...</span>
                            </div>
                            <div id="rk-tambah-table-wrap" class="table-responsive">
                                <table id="table-rk-tambah" class="table table-hover table-sm mb-0 rk-table">
                                    <thead>
                                        <tr>
                                            <th class="text-center" style="width: 48px;">No</th>
                                            <th style="min-width: 150px;">Tanggal &amp; Pengaju</th>
                                            <th style="min-width: 190px;">Daftar Jalan</th>
                                            <th style="min-width: 185px;">Tipe Rumah Usulan</th>
                                            <th style="min-width: 220px;">Keterangan MKDT</th>
                                            <th class="text-center" style="width: 115px;">Status</th>
                                            <th class="text-center" style="width: 145px;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbody-rk-tambah"></tbody>
                                </table>
                            </div>
                            <div id="rk-tambah-empty" class="rk-empty-box" hidden>
                                <i class="fas fa-inbox d-block"></i>
                                <div class="font-weight-bold">Tidak ada pengajuan tambah kavling</div>
                                <small class="text-muted">Belum ada data pengajuan untuk filter status yang dipilih.</small>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- =========================================================
                         TAB 2: REQUEST UBAH KAVLING
                         ========================================================= -->
                    <div class="tab-pane fade" id="panel-rk-ubah" role="tabpanel" aria-labelledby="tab-rk-ubah">
                        <?php if ($canCreateRequestKavling): ?>
                        <div class="divider divider-left mt-0">
                            <div class="divider-text">Form Pengajuan Ubah Tipe Kavling</div>
                        </div>
                        <div class="card rk-card">
                            <div class="card-body">
                                <form id="form-rk-ubah" novalidate>
                                    <div class="form-group">
                                        <label class="rk-field-label" for="rk-ubah-kavlings">
                                            Pilih Kavling (Multi-Pilih) <span class="text-danger">*</span>
                                        </label>
                                        <div class="rk-kavling-picker-row">
                                            <div class="rk-kavling-picker-select">
                                                <select id="rk-ubah-kavlings" name="id_kavlings[]" class="form-control" multiple="multiple" style="width: 100%;"></select>
                                            </div>
                                            <button type="button" id="btn-rk-pick-canvas" class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-map-marked-alt mr-50"></i>Pilih dari Kanvas Siteplan
                                            </button>
                                        </div>
                                        <small class="rk-field-hint">
                                            Pilih satu atau lebih kavling melalui pencarian atau langsung klik dari kanvas siteplan. Kavling berstatus aktif Akad / Akad Indent tidak dapat dipilih.
                                        </small>
                                        <div id="rk-ubah-selected-info" class="rk-konsumen-alert" hidden></div>
                                    </div>

                                    <div class="form-group">
                                        <label class="rk-field-label" for="rk-ubah-id_tipe">Tipe Rumah Available (Opsional)</label>
                                        <select id="rk-ubah-id_tipe" name="id_tipe" class="form-control" style="width: 100%;"></select>
                                        <small class="rk-field-hint">
                                            Kosongkan bila tipe rumah tujuan belum tersedia di Master Tipe dan tuliskan spesifikasinya di Keterangan.
                                        </small>
                                        <div id="rk-ubah-tipe-preview" class="rk-tipe-preview-box" hidden></div>
                                    </div>

                                    <div class="form-group mb-1">
                                        <label class="rk-field-label" for="rk-ubah-keterangan">
                                            Keterangan / Alasan Perubahan <span class="text-danger">*</span>
                                        </label>
                                        <textarea id="rk-ubah-keterangan" name="keterangan" class="form-control" rows="3" placeholder="Contoh: Mohon ubah tipe kavling tersebut menjadi tipe 45/84 sesuai permintaan konsumen / pembaruan siteplan."></textarea>
                                    </div>

                                    <div class="d-flex flex-wrap justify-content-end" style="gap: .5rem;">
                                        <button type="button" id="btn-rk-reset-ubah" class="btn btn-outline-secondary btn-sm">
                                            <i class="fas fa-undo mr-25"></i>Reset Form
                                        </button>
                                        <button type="submit" id="btn-rk-submit-ubah" class="btn btn-primary btn-sm">
                                            <i class="fas fa-paper-plane mr-25"></i>Kirim Request Ubah Kavling
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if ($canCreateRequestKavling && ! $canProcessRequestKavling): ?>
                        <div class="divider divider-left">
                            <div class="divider-text">Pengajuan Ubah Kavling Menunggu (Pending)</div>
                        </div>
                        <div class="card rk-card mb-0" id="rk-mkdt-ubah-card">
                            <div class="rk-card-header">
                                <div class="d-flex align-items-center flex-wrap" style="gap: .5rem;">
                                    <span class="rk-status-pill is-pending">
                                        <i class="fas fa-clock"></i> Menunggu (<span id="rk-mkdt-ubah-pending-count">0</span>)
                                    </span>
                                    <small class="text-muted">Centang pengajuan milik Anda untuk dibatalkan sekaligus.</small>
                                </div>
                                <button type="button" id="btn-rk-mkdt-refresh-ubah" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-sync-alt mr-25"></i>Refresh
                                </button>
                            </div>
                            <div id="rk-mkdt-ubah-batch-bar" class="rk-batch-toolbar" hidden>
                                <div class="rk-batch-info">
                                    <span class="rk-batch-count-badge"><span id="rk-mkdt-ubah-selected-count">0</span> dipilih</span>
                                    <span class="rk-batch-text">Pengajuan ubah kavling siap dibatalkan</span>
                                </div>
                                <div class="rk-batch-actions">
                                    <button type="button" id="btn-rk-mkdt-batch-cancel-ubah" class="btn btn-danger btn-sm">
                                        <i class="fas fa-ban mr-25"></i>Batalkan Terpilih
                                    </button>
                                    <button type="button" id="btn-rk-mkdt-clear-ubah" class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-times mr-25"></i>Batal Pilih
                                    </button>
                                </div>
                            </div>
                            <div id="rk-mkdt-ubah-loading" class="rk-loading-box" hidden>
                                <div class="spinner-border text-primary spinner-border-sm mr-50" role="status"></div>
                                <span>Memuat pengajuan ubah kavling menunggu...</span>
                            </div>
                            <div id="rk-mkdt-ubah-table-wrap" class="table-responsive">
                                <table id="table-rk-mkdt-ubah" class="table table-hover table-sm mb-0 rk-table">
                                    <thead>
                                        <tr>
                                            <th class="text-center rk-check-col">
                                                <input type="checkbox" id="rk-mkdt-ubah-check-all" class="rk-row-checkbox" aria-label="Pilih semua pengajuan ubah kavling menunggu">
                                            </th>
                                            <th class="text-center" style="width: 48px;">No</th>
                                            <th style="min-width: 150px;">Tanggal &amp; Pengaju</th>
                                            <th style="min-width: 230px;">Daftar Kavling &amp; Info Konsumen</th>
                                            <th style="min-width: 185px;">Tipe Rumah Usulan</th>
                                            <th style="min-width: 210px;">Keterangan MKDT</th>
                                            <th class="text-center" style="width: 110px;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbody-rk-mkdt-ubah"></tbody>
                                </table>
                            </div>
                            <div id="rk-mkdt-ubah-empty" class="rk-empty-box" hidden>
                                <i class="fas fa-inbox d-block"></i>
                                <div class="font-weight-bold">Belum ada pengajuan ubah kavling menunggu</div>
                                <small class="text-muted">Pengajuan yang Anda kirim dan masih menunggu tindak lanjut Planning akan tampil di sini.</small>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if ($canProcessRequestKavling): ?>
                        <div class="divider divider-left <?= $canCreateRequestKavling ? '' : 'mt-0' ?>">
                            <div class="divider-text">Antrean Request Ubah Tipe Kavling (Planning)</div>
                        </div>
                        <div class="card rk-card mb-0">
                            <div class="rk-card-header">
                                <div class="rk-filter-pills" id="rk-ubah-filter-pills" role="group" aria-label="Filter status ubah kavling">
                                    <button type="button" class="rk-filter-pill is-active" data-status="pending">
                                        Menunggu (<span id="rk-ubah-count-pending">0</span>)
                                    </button>
                                    <button type="button" class="rk-filter-pill" data-status="all">
                                        Semua
                                    </button>
                                </div>
                                <button type="button" id="btn-rk-refresh-ubah" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-sync-alt mr-25"></i>Refresh
                                </button>
                            </div>
                            <div id="rk-ubah-loading" class="rk-loading-box" hidden>
                                <div class="spinner-border text-primary spinner-border-sm mr-50" role="status"></div>
                                <span>Memuat daftar pengajuan ubah tipe kavling...</span>
                            </div>
                            <div id="rk-ubah-table-wrap" class="table-responsive">
                                <table id="table-rk-ubah" class="table table-hover table-sm mb-0 rk-table">
                                    <thead>
                                        <tr>
                                            <th class="text-center" style="width: 48px;">No</th>
                                            <th style="min-width: 150px;">Tanggal &amp; Pengaju</th>
                                            <th style="min-width: 230px;">Daftar Kavling &amp; Info Konsumen</th>
                                            <th style="min-width: 185px;">Tipe Rumah Usulan</th>
                                            <th style="min-width: 210px;">Keterangan MKDT</th>
                                            <th class="text-center" style="width: 115px;">Status</th>
                                            <th class="text-center" style="width: 155px;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbody-rk-ubah"></tbody>
                                </table>
                            </div>
                            <div id="rk-ubah-empty" class="rk-empty-box" hidden>
                                <i class="fas fa-inbox d-block"></i>
                                <div class="font-weight-bold">Tidak ada pengajuan ubah tipe kavling</div>
                                <small class="text-muted">Belum ada data pengajuan untuk filter status yang dipilih.</small>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- =========================================================
                         TAB 3: RIWAYAT (ADMIN, MKDT, PLANNING)
                         ========================================================= -->
                    <div class="tab-pane fade" id="panel-rk-riwayat" role="tabpanel" aria-labelledby="tab-rk-riwayat">
                        <div class="card rk-card">
                            <div class="card-body py-1">
                                <div class="d-flex flex-wrap align-items-center justify-content-between" style="gap: .75rem;">
                                    <div class="rk-filter-pills" id="rk-riwayat-status-pills" role="group" aria-label="Filter status riwayat">
                                        <button type="button" class="rk-filter-pill is-active" data-status="all">
                                            Semua (<span id="rk-riwayat-count-all">0</span>)
                                        </button>
                                        <button type="button" class="rk-filter-pill" data-status="pending">
                                            Menunggu (<span id="rk-riwayat-count-pending">0</span>)
                                        </button>
                                        <button type="button" class="rk-filter-pill" data-status="approved">
                                            Selesai (<span id="rk-riwayat-count-approved">0</span>)
                                        </button>
                                        <button type="button" class="rk-filter-pill" data-status="rejected">
                                            Ditolak (<span id="rk-riwayat-count-rejected">0</span>)
                                        </button>
                                        <button type="button" class="rk-filter-pill" data-status="cancelled">
                                            Dibatalkan (<span id="rk-riwayat-count-cancelled">0</span>)
                                        </button>
                                    </div>

                                    <div class="d-flex flex-wrap align-items-center" style="gap: .5rem;">
                                        <select id="rk-riwayat-jenis-filter" class="form-control form-control-sm" aria-label="Filter jenis request" style="min-width: 165px;">
                                            <option value="all">Semua Jenis Request</option>
                                            <option value="tambah_baru">Tambah Kavling</option>
                                            <option value="ubah_tipe">Ubah Kavling</option>
                                        </select>
                                        <button type="button" id="btn-rk-refresh-riwayat" class="btn btn-outline-secondary btn-sm">
                                            <i class="fas fa-sync-alt mr-25"></i>Refresh
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="divider divider-left">
                            <div class="divider-text">Riwayat Pengajuan Request Kavling</div>
                        </div>

                        <div class="card rk-card mb-0">
                            <?php if ($canCreateRequestKavling): ?>
                            <div id="rk-riwayat-batch-bar" class="rk-batch-toolbar" hidden>
                                <div class="rk-batch-info">
                                    <span class="rk-batch-count-badge"><span id="rk-riwayat-selected-count">0</span> dipilih</span>
                                    <span class="rk-batch-text">Request menunggu siap dibatalkan</span>
                                </div>
                                <div class="rk-batch-actions">
                                    <button type="button" id="btn-rk-riwayat-batch-cancel" class="btn btn-danger btn-sm">
                                        <i class="fas fa-ban mr-25"></i>Batalkan Terpilih
                                    </button>
                                    <button type="button" id="btn-rk-riwayat-clear-select" class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-times mr-25"></i>Batal Pilih
                                    </button>
                                </div>
                            </div>
                            <?php endif; ?>
                            <div id="rk-riwayat-loading" class="rk-loading-box" hidden>
                                <div class="spinner-border text-primary spinner-border-sm mr-50" role="status"></div>
                                <span>Memuat riwayat pengajuan request kavling...</span>
                            </div>
                            <div id="rk-riwayat-table-wrap" class="table-responsive">
                                <table id="table-rk-riwayat" class="table table-hover table-sm mb-0 rk-table">
                                    <thead>
                                        <tr>
                                            <?php if ($canCreateRequestKavling): ?>
                                            <th class="text-center rk-check-col">
                                                <input type="checkbox" id="rk-riwayat-check-all" class="rk-row-checkbox" aria-label="Pilih semua request menunggu yang dapat dibatalkan">
                                            </th>
                                            <?php endif; ?>
                                            <th class="text-center" style="width: 48px;">No</th>
                                            <th style="min-width: 145px;">Tanggal &amp; Pengaju</th>
                                            <th class="text-center" style="width: 130px;">Jenis Request</th>
                                            <th style="min-width: 210px;">Detail Jalan / Kavling</th>
                                            <th style="min-width: 195px;">Tipe Usulan &amp; Diterapkan</th>
                                            <th style="min-width: 190px;">Keterangan MKDT</th>
                                            <th class="text-center" style="width: 115px;">Status</th>
                                            <th style="min-width: 210px;">Catatan / Info Proses</th>
                                            <?php if ($canCreateRequestKavling): ?>
                                            <th class="text-center" style="width: 110px;">Aksi</th>
                                            <?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody id="tbody-rk-riwayat"></tbody>
                                </table>
                            </div>
                            <div id="rk-riwayat-empty" class="rk-empty-box" hidden>
                                <i class="fas fa-inbox d-block"></i>
                                <div class="font-weight-bold">Belum ada riwayat request kavling</div>
                                <small class="text-muted">Tidak ada data yang cocok dengan filter yang dipilih.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer justify-content-between">
                <small class="text-muted">
                    Pengajuan dan status tindak lanjut Planning otomatis tercatat dan mengirim notifikasi ke pihak terkait.
                </small>
                <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<?php if ($canProcessRequestKavling): ?>
<!-- Sub-Modal Planning: Terapkan Ubah Tipe Kavling -->
<div class="modal fade rk-nested-modal" id="modal-apply-ubah-kavling" tabindex="-1" role="dialog" aria-labelledby="modal-apply-ubah-kavling-label" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-apply-ubah-kavling-label">
                    <span class="rk-modal-title-icon"><i class="fas fa-tools"></i></span>
                    <span>Terapkan Perubahan Tipe Kavling</span>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form id="form-rk-apply-ubah" novalidate>
                <div class="modal-body">
                    <input type="hidden" id="rk-apply-id_request" name="id_request">

                    <div class="card rk-card">
                        <div class="card-body">
                            <div class="divider divider-left mt-0">
                                <div class="divider-text">Kavling yang Diajukan</div>
                            </div>
                            <div id="rk-apply-request-meta" class="mb-75 small text-muted"></div>
                            <div id="rk-apply-kavling-list" class="rk-chip-stack"></div>
                            <div id="rk-apply-konsumen-warning" class="rk-konsumen-alert" hidden></div>
                        </div>
                    </div>

                    <div class="card rk-card mb-0">
                        <div class="card-body">
                            <div class="divider divider-left mt-0">
                                <div class="divider-text">Pilih Tipe Rumah Baru</div>
                            </div>

                            <div class="form-group">
                                <label class="rk-field-label" for="rk-apply-id_tipe">
                                    Tipe Rumah Baru <span class="text-danger">*</span>
                                </label>
                                <div class="rk-apply-tipe-toolbar">
                                    <div class="rk-apply-tipe-select-wrap">
                                        <select id="rk-apply-id_tipe" name="id_tipe" class="form-control" style="width: 100%;"></select>
                                    </div>
                                    <div class="rk-apply-tipe-actions">
                                        <a href="<?= base_url('tipe') ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary btn-sm" id="btn-rk-tambah-tipe">
                                            <i class="fas fa-plus-circle mr-25"></i> Tambah Tipe
                                        </a>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-rk-refresh-tipes">
                                            <i class="fas fa-sync-alt mr-25"></i> Refresh Tipe
                                        </button>
                                    </div>
                                </div>
                                <div id="rk-apply-requested-hint" class="rk-field-hint text-primary font-weight-bold" hidden></div>
                                <div id="rk-apply-tipe-preview" class="rk-tipe-preview-box" hidden></div>
                            </div>

                            <div class="form-group mb-0">
                                <label class="rk-field-label" for="rk-apply-catatan">Catatan Planning (Opsional)</label>
                                <textarea id="rk-apply-catatan" name="catatan_planning" class="form-control" rows="2" placeholder="Catatan perubahan tipe kavling untuk pengaju..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">Batal</button>
                    <button type="submit" id="btn-rk-submit-apply" class="btn btn-primary btn-sm">
                        <i class="fas fa-check-circle mr-25"></i>Simpan &amp; Ubah Tipe Kavling
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Modal Preview Detail Tipe (3D, Denah, Spek Teknis, Gambar Kerja PDF) -->
<div class="modal fade rk-nested-modal" id="modal-rk-tipe-preview" tabindex="-1" role="dialog" aria-labelledby="modal-rk-tipe-preview-label" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-rk-tipe-preview-label">
                    <span class="rk-modal-title-icon"><i class="fas fa-home"></i></span>
                    <span id="rk-tipe-preview-modal-title">Spesifikasi &amp; Gambar Tipe</span>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="rk-tipe-preview-modal-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Lightbox Gambar 3D / Denah -->
<div class="modal fade rk-nested-modal" id="modal-rk-lightbox" tabindex="-1" role="dialog" aria-labelledby="modal-rk-lightbox-label" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-rk-lightbox-label">
                    <span class="rk-modal-title-icon"><i class="fas fa-image"></i></span>
                    <span id="rk-lightbox-title">Preview Gambar Tipe</span>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="rk-lightbox-stage">
                    <img id="rk-lightbox-img" class="rk-lightbox-img" src="" alt="Preview Gambar Tipe">
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <a id="rk-lightbox-newtab" href="#" target="_blank" rel="noopener noreferrer" class="btn btn-primary btn-sm">
                    <i class="fas fa-external-link-alt mr-25"></i>Buka Tab Baru
                </a>
                <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
