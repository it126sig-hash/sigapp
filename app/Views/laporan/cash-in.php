<link rel="stylesheet" type="text/css" href="<?= base_url('app-assets/vendors/css/tables/datatable/dataTables.bootstrap4.min.css') ?>">
<link rel="stylesheet" type="text/css" href="<?= base_url('app-assets/vendors/css/tables/datatable/responsive.bootstrap4.min.css') ?>">
<link rel="stylesheet" type="text/css" href="<?= base_url('assets/css/laporan-cash-in.css') ?>?v=<?= filemtime(FCPATH . 'assets/css/laporan-cash-in.css') ?>">

<div class="app-content content cash-in-report-page">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>

    <section>
        <div class="row">
            <div class="col-12">
                <div class="card cash-in-filter-card">
                    <div class="card-body">
                        <div class="d-flex flex-wrap justify-content-between align-items-start cash-in-heading-row">
                            <div>
                                <div class="divider divider-left mb-50">
                                    <div class="divider-text px-0"><?= esc($title) ?></div>
                                </div>
                                <p class="text-muted mb-0">
                                    Ringkasan pendapatan bulanan proyek
                                    <strong><?= esc($activeProyek->nama_proyek ?? 'belum dipilih') ?></strong>.
                                </p>
                            </div>
                            <div class="cash-in-filter-controls">
                                <div class="form-group mb-0">
                                    <label for="cash_in_year_a">Tahun</label>
                                    <select id="cash_in_year_a" class="form-control">
                                        <?php foreach ($availableYears as $year): ?>
                                            <option value="<?= (int) $year ?>" <?= (int) $year === (int) $defaultYearA ? 'selected' : '' ?>>
                                                <?= (int) $year ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <button type="button" id="cash_in_add_comparison" class="btn btn-outline-primary">
                                    <i class="fas fa-plus mr-25"></i> Tahun Pembanding
                                </button>
                                <div id="cash_in_comparison_controls" class="cash-in-comparison-controls" hidden>
                                    <div class="cash-in-versus" aria-hidden="true">vs</div>
                                    <div class="form-group mb-0">
                                        <label for="cash_in_year_b">Tahun Pembanding</label>
                                        <select id="cash_in_year_b" class="form-control">
                                            <?php foreach ($availableYears as $year): ?>
                                                <option value="<?= (int) $year ?>" <?= (int) $year === (int) $defaultYearB ? 'selected' : '' ?>>
                                                    <?= (int) $year ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <button type="button" id="cash_in_remove_comparison" class="btn btn-outline-danger cash-in-remove-comparison" title="Hapus tahun pembanding" aria-label="Hapus tahun pembanding">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <button type="button" id="cash_in_apply" class="btn btn-primary">
                                    <i class="fas fa-filter mr-25"></i> Tampilkan
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div id="cash_in_no_project" class="alert alert-warning" role="alert" <?= $activeProyek ? 'hidden' : '' ?>>
        <i class="fas fa-exclamation-triangle mr-50"></i>
        Pilih proyek aktif terlebih dahulu untuk menampilkan laporan Cash In.
    </div>

    <div id="cash_in_error" class="alert alert-danger" role="alert" hidden></div>

    <section id="cash_in_report_section" <?= $activeProyek ? '' : 'hidden' ?>>
        <div class="card cash-in-matrix-card">
            <div class="card-header">
                <div>
                    <h5 class="mb-25">Pendapatan Bulanan</h5>
                    <small class="text-muted">Klik nominal untuk melihat transaksi penyusunnya.</small>
                </div>
                <div class="d-flex align-items-center cash-in-report-actions">
                    <span id="cash_in_loading" class="cash-in-loading" hidden>
                        <span class="spinner-border spinner-border-sm mr-50" role="status" aria-hidden="true"></span>
                        Memuat...
                    </span>
                    <button type="button" id="cash_in_toggle_chart" class="btn btn-outline-primary btn-sm" disabled>
                        <i class="fas fa-chart-bar mr-25"></i>
                        <span>Tampilkan Chart</span>
                    </button>
                </div>
            </div>
            <div class="card-datatable cash-in-table-wrap">
                <table id="cash_in_matrix" class="table table-bordered table-hover mb-0">
                    <thead id="cash_in_matrix_head"></thead>
                    <tbody id="cash_in_matrix_body"></tbody>
                    <tfoot id="cash_in_matrix_foot"></tfoot>
                </table>
            </div>
        </div>

        <div id="cash_in_chart_card" class="card cash-in-chart-card" hidden>
            <div class="card-header">
                <div>
                    <h5 class="mb-25">Chart Cash In Bulanan</h5>
                    <small class="text-muted">Perbandingan komposisi Booking Fee, Uang Muka, dan Hasil Akad.</small>
                </div>
            </div>
            <div class="card-body cash-in-chart-body">
                <canvas id="cash_in_chart"></canvas>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="cash_in_detail_modal" tabindex="-1" role="dialog" aria-labelledby="cash_in_detail_title" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="cash_in_detail_title">Detail Cash In</h5>
                    <small id="cash_in_detail_subtitle" class="text-muted"></small>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="cash_in_detail_error" class="alert alert-danger" role="alert" hidden></div>
                <div class="card mb-0">
                    <div class="card-datatable cash-in-detail-table-wrap">
                        <table id="cash_in_detail_table" class="table table-bordered table-striped mb-0 w-100">
                            <thead>
                                <tr>
                                    <th>Jenis Pendapatan</th>
                                    <th>Jalan / No. Kavling</th>
                                    <th>Nama Konsumen</th>
                                    <th>Keterangan</th>
                                    <th>Tanggal Bayar / Cair</th>
                                    <th>Nominal</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= base_url('app-assets/vendors/js/vendors.min.js') ?>"></script>
<script src="<?= base_url('app-assets/vendors/js/tables/datatable/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('app-assets/vendors/js/tables/datatable/datatables.bootstrap4.min.js') ?>"></script>
<script src="<?= base_url('app-assets/vendors/js/tables/datatable/dataTables.responsive.min.js') ?>"></script>
<script src="<?= base_url('app-assets/vendors/js/tables/datatable/responsive.bootstrap4.js') ?>"></script>
<script src="<?= base_url('app-assets/vendors/js/charts/chart.min.js') ?>"></script>
<script>
window.CASH_IN_REPORT = <?= json_encode([
    'summaryUrl' => base_url('api/laporan/cash-in/summary'),
    'detailUrl' => base_url('api/laporan/cash-in/detail'),
    'hasProject' => (bool) $activeProyek,
    'projectName' => (string) ($activeProyek->nama_proyek ?? ''),
    'csrfName' => csrf_token(),
    'csrfHash' => csrf_hash(),
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
</script>
<script src="<?= base_url('assets/js/laporan-cash-in.js') ?>?v=<?= filemtime(FCPATH . 'assets/js/laporan-cash-in.js') ?>"></script>
