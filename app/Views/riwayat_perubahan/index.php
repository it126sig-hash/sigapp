<link rel="stylesheet" type="text/css" href="<?= base_url() ?>assets/css/riwayat_perubahan.css?v=<?= filemtime(FCPATH . 'assets/css/riwayat_perubahan.css') ?>">

<div class="app-content content riwayat-perubahan-page">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper">
        <div class="content-body">
            <div class="divider divider-left">
                <div class="divider-text"><?= esc($title ?? 'Riwayat Perubahan') ?></div>
            </div>

            <div class="page-toolbar mb-1">
                <div class="row align-items-end">
                    <div class="col-md-4">
                        <div class="form-group mb-md-0">
                            <label for="history-module">Modul</label>
                            <select id="history-module" class="form-control">
                                <option value="">Semua Modul</option>
                                <?php foreach (($modules ?? []) as $key => $label) : ?>
                                    <option value="<?= esc($key) ?>"><?= esc($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mb-md-0">
                            <label for="history-date-from">Dari Tanggal</label>
                            <input type="date" id="history-date-from" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mb-md-0">
                            <label for="history-date-to">Sampai Tanggal</label>
                            <input type="date" id="history-date-to" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="button" id="history-filter" class="btn btn-primary btn-block">
                            <i data-feather="filter" class="mr-50"></i>Filter
                        </button>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Modul</th>
                                <th>Aksi</th>
                                <th>Ringkasan</th>
                                <th>User</th>
                                <th class="text-right">Detail</th>
                            </tr>
                        </thead>
                        <tbody id="history-log-body">
                            <tr>
                                <td colspan="6" class="text-center text-muted py-3">Memuat data...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <small id="history-log-info" class="text-muted">-</small>
                    <div>
                        <button type="button" id="history-prev" class="btn btn-outline-secondary btn-sm" disabled>Sebelumnya</button>
                        <button type="button" id="history-next" class="btn btn-outline-primary btn-sm" disabled>Berikutnya</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="history-detail-modal" tabindex="-1" role="dialog" aria-labelledby="history-detail-title" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="history-detail-title">Detail Riwayat</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="history-debug-box" class="debug-meta-box d-none" aria-live="polite">
                    <div class="debug-meta-title">
                        <i data-feather="terminal" class="mr-50"></i> Debug Metadata Context
                    </div>
                    <div class="debug-meta-grid">
                        <div class="debug-meta-item">
                            <span class="debug-meta-label">IP Address</span>
                            <span class="debug-meta-value" id="history-debug-ip">-</span>
                        </div>
                        <div class="debug-meta-item">
                            <span class="debug-meta-label">URI / Endpoint</span>
                            <span class="debug-meta-value" id="history-debug-uri">-</span>
                        </div>
                        <div class="debug-meta-item debug-meta-item-full">
                            <span class="debug-meta-label">User Agent</span>
                            <span class="debug-meta-value" id="history-debug-ua">-</span>
                        </div>
                    </div>
                </div>

                <div class="divider divider-left">
                    <div class="divider-text">Data Lama</div>
                </div>
                <pre id="history-old-data" class="riwayat-json-view"></pre>

                <div class="divider divider-left">
                    <div class="divider-text">Data Baru</div>
                </div>
                <pre id="history-new-data" class="riwayat-json-view"></pre>

                <div class="divider divider-left">
                    <div class="divider-text">Metadata</div>
                </div>
                <pre id="history-metadata" class="riwayat-json-view"></pre>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script src="<?= base_url() ?>app-assets/vendors/js/vendors.min.js"></script>
<script>
    window.RIWAYAT_PERUBAHAN_CONFIG = <?= json_encode([
        'listUrl'  => base_url('api/riwayat-perubahan/list'),
        'csrfName' => csrf_token(),
        'csrfHash' => csrf_hash(),
        'limit'    => 20,
    ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
</script>
<script src="<?= base_url() ?>assets/js/riwayat_perubahan.js?v=<?= filemtime(FCPATH . 'assets/js/riwayat_perubahan.js') ?>"></script>
