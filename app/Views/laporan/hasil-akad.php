<link rel="stylesheet" type="text/css" href="<?= base_url('app-assets/vendors/css/tables/datatable/dataTables.bootstrap4.min.css') ?>">
<link rel="stylesheet" type="text/css" href="<?= base_url('app-assets/vendors/css/tables/datatable/responsive.bootstrap4.min.css') ?>">
<link rel="stylesheet" type="text/css" href="<?= base_url('assets/css/laporan-hasil-akad.css') ?>?v=<?= filemtime(FCPATH . 'assets/css/laporan-hasil-akad.css') ?>">

<div class="app-content content hasil-akad-report-page">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>

    <section>
        <div class="row">
            <div class="col-12">
                <div class="card hasil-akad-filter-card">
                    <div class="card-body">
                        <div class="d-flex flex-wrap justify-content-between align-items-start hasil-akad-heading-row">
                            <div>
                                <div class="divider divider-left mb-50"><div class="divider-text px-0"><?= esc($title) ?></div></div>
                                <p class="text-muted mb-0">Ringkasan hasil akad KPR bulanan proyek <strong><?= esc($activeProyek->nama_proyek ?? 'belum dipilih') ?></strong>.</p>
                            </div>
                            <div class="hasil-akad-filter-controls">
                                <div class="form-group mb-0">
                                    <label for="hasil_akad_year_a">Tahun</label>
                                    <select id="hasil_akad_year_a" class="form-control">
                                        <?php foreach ($availableYears as $year): ?>
                                            <option value="<?= (int) $year ?>" <?= (int) $year === (int) $defaultYearA ? 'selected' : '' ?>><?= (int) $year ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <button type="button" id="hasil_akad_add_comparison" class="btn btn-outline-primary"><i class="fas fa-plus mr-25"></i>Tahun Pembanding</button>
                                <div id="hasil_akad_comparison_controls" class="hasil-akad-comparison-controls" hidden>
                                    <div class="hasil-akad-versus" aria-hidden="true">vs</div>
                                    <div class="form-group mb-0">
                                        <label for="hasil_akad_year_b">Tahun Pembanding</label>
                                        <select id="hasil_akad_year_b" class="form-control">
                                            <?php foreach ($availableYears as $year): ?>
                                                <option value="<?= (int) $year ?>" <?= (int) $year === (int) $defaultYearB ? 'selected' : '' ?>><?= (int) $year ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <button type="button" id="hasil_akad_remove_comparison" class="btn btn-outline-danger hasil-akad-remove-comparison" title="Hapus tahun pembanding" aria-label="Hapus tahun pembanding"><i class="fas fa-times"></i></button>
                                </div>
                                <button type="button" id="hasil_akad_apply" class="btn btn-primary"><i class="fas fa-filter mr-25"></i>Tampilkan</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div id="hasil_akad_no_project" class="alert alert-warning" role="alert" <?= $activeProyek ? 'hidden' : '' ?>><i class="fas fa-exclamation-triangle mr-50"></i>Pilih proyek aktif terlebih dahulu untuk menampilkan laporan Hasil Akad.</div>
    <div id="hasil_akad_error" class="alert alert-danger" role="alert" hidden></div>

    <section id="hasil_akad_report_section" <?= $activeProyek ? '' : 'hidden' ?>>
        <div class="card hasil-akad-matrix-card">
            <div class="card-header">
                <div>
                    <h5 class="mb-25">Hasil Akad Bulanan</h5>
                    <small class="text-muted">Klik nominal untuk melihat transaksi penyusunnya.</small>
                </div>
                <div class="d-flex align-items-center hasil-akad-report-actions">
                    <span id="hasil_akad_loading" class="hasil-akad-loading" hidden><span class="spinner-border spinner-border-sm mr-50" role="status" aria-hidden="true"></span>Memuat...</span>
                    <button type="button" id="hasil_akad_toggle_chart" class="btn btn-outline-primary btn-sm" disabled><i class="fas fa-chart-bar mr-25"></i><span>Tampilkan Chart</span></button>
                </div>
            </div>
            <div class="card-datatable hasil-akad-table-wrap">
                <table id="hasil_akad_matrix" class="table table-bordered table-hover mb-0">
                    <thead id="hasil_akad_matrix_head"></thead>
                    <tbody id="hasil_akad_matrix_body"></tbody>
                    <tfoot id="hasil_akad_matrix_foot"></tfoot>
                </table>
            </div>
        </div>

        <div id="hasil_akad_chart_card" class="card hasil-akad-chart-card" hidden>
            <div class="card-header"><div><h5 class="mb-25">Chart Hasil Akad Bulanan</h5><small class="text-muted">Metrik berdiri sendiri; chart tidak ditumpuk.</small></div></div>
            <div class="card-body hasil-akad-chart-body"><canvas id="hasil_akad_chart"></canvas></div>
        </div>
    </section>
</div>

<div class="modal fade" id="hasil_akad_detail_modal" tabindex="-1" role="dialog" aria-labelledby="hasil_akad_detail_title" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header"><div><h5 class="modal-title" id="hasil_akad_detail_title">Detail Hasil Akad</h5><small id="hasil_akad_detail_subtitle" class="text-muted"></small></div><button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button></div>
            <div class="modal-body">
                <div id="hasil_akad_detail_error" class="alert alert-danger" role="alert" hidden></div>
                <div class="card mb-0"><div class="card-datatable hasil-akad-detail-table-wrap">
                    <table id="hasil_akad_detail_table" class="table table-bordered table-striped mb-0 w-100">
                        <thead id="hasil_akad_detail_table_head"><tr><th>Jenis Laporan</th><th>Jalan / No. Kavling</th><th>Nama Konsumen</th><th>Tanggal</th><th>Nominal</th><th>Aksi</th></tr></thead>
                    </table>
                </div></div>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" type="text/css" href="<?= base_url('app-assets/vendors/css/extensions/sweetalert2.min.css') ?>">
<script src="<?= base_url('app-assets/vendors/js/vendors.min.js') ?>"></script>
<script src="<?= base_url('app-assets/vendors/js/extensions/sweetalert2.all.min.js') ?>"></script>
<script src="<?= base_url('app-assets/vendors/js/tables/datatable/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('app-assets/vendors/js/tables/datatable/datatables.bootstrap4.min.js') ?>"></script>
<script src="<?= base_url('app-assets/vendors/js/tables/datatable/dataTables.responsive.min.js') ?>"></script>
<script src="<?= base_url('app-assets/vendors/js/tables/datatable/responsive.bootstrap4.js') ?>"></script>
<script src="<?= base_url('app-assets/vendors/js/charts/chart.min.js') ?>"></script>

<?= view('siteplan/partials/modal_detail', ['data' => []]) ?>
<script>
window.HASIL_AKAD_REPORT = <?= json_encode([
    'summaryUrl' => base_url('api/laporan/hasil-akad/summary'),
    'detailUrl' => base_url('api/laporan/hasil-akad/detail'),
    'hasProject' => (bool) $activeProyek,
    'projectName' => (string) ($activeProyek->nama_proyek ?? ''),
    'csrfName' => csrf_token(),
    'csrfHash' => csrf_hash(),
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
if (typeof dt_proyek === 'undefined') var dt_proyek = <?= json_encode($activeProyek ?? new \stdClass()) ?>;
if (typeof csrfName === 'undefined') var csrfName = '<?= csrf_token() ?>';
if (typeof csrfHash === 'undefined') var csrfHash = '<?= csrf_hash() ?>';
if (typeof not_found === 'undefined') var not_found = '<?= base_url('assets/images/not-found.png') ?>';
if (typeof editdtt === 'undefined') var editdtt = [];
if (typeof roleid === 'undefined') var roleid = '<?= user_id() ? (session()->get('role_id') ?? 0) : 0 ?>';
</script>
<script src="<?= base_url('assets/js/siteplan-detail-modal.js') ?>?<?= filemtime(FCPATH . 'assets/js/siteplan-detail-modal.js') ?>"></script>
<script src="<?= base_url('assets/js/laporan-hasil-akad.js') ?>?v=<?= filemtime(FCPATH . 'assets/js/laporan-hasil-akad.js') ?>"></script>
