<!-- BEGIN: Vendor CSS-->
<link rel="stylesheet" type="text/css" href="<?= base_url('app-assets/vendors/css/vendors.min.css') ?>">
<link rel="stylesheet" type="text/css" href="<?= base_url('app-assets/vendors/css/extensions/sweetalert2.min.css') ?>">
<link rel="stylesheet" type="text/css" href="<?= base_url('app-assets/vendors/css/pickers/flatpickr/flatpickr.min.css') ?>">
<link rel="stylesheet" type="text/css" href="<?= base_url('app-assets/vendors/css/forms/select/select2.min.css') ?>">
<link rel="stylesheet" type="text/css" href="<?= base_url('app-assets/vendors/css/extensions/toastr.min.css') ?>">
<!-- END: Vendor CSS-->

<!-- BEGIN: Module CSS (SIGAPP 3-Tier Architecture & filemtime Cache Busting) -->
<link rel="stylesheet" type="text/css" href="<?= base_url('assets/css/dashboard.css') ?>?v=<?= filemtime(FCPATH . 'assets/css/dashboard.css') ?>">
<!-- END: Module CSS -->

<!-- Configuration Inject -->
<script>
    window.DASHBOARD_CONFIG = <?= json_encode([
        'roleContext' => $roleContext ?? null,
        'csrfName'    => csrf_token(),
        'csrfHash'    => csrf_hash(),
        'baseUrl'     => base_url('/'),
    ]) ?>;
</script>

<?php
$visibleTabs = $roleContext['visible_tabs'] ?? ['operasional'];
$defaultTab = $roleContext['default_tab'] ?? 'operasional';
$canViewFinance = !empty($roleContext['can_view_finance']);
?>

<!-- BEGIN: Content-->
<div class="app-content content dashboard-page">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper">
        <div class="content-header row"></div>
        <div class="content-body">

            <!-- 1. Dashboard Header & Period Filter Pills -->
            <div class="dashboard-header-row">
                <div>
                    <h2>Dashboard</h2>
                    <p class="dashboard-subtitle" id="dashboard-active-proyek">Real-time operational and sales monitoring</p>
                </div>
                <div class="dashboard-period-pills" id="filter-statistik-pills">
                    <input type="month" id="filter-bulan" class="btn active" max="<?= date('Y-m') ?>" value="<?= date('Y-m') ?>" onclick="try { this.showPicker(); } catch(e) {}">
                    <button type="button" id="filter-6bulan" class="btn" data-val="<?= date('Y') ?>">Per Tahun <?= date('Y') ?></button>
                    <button type="button" id="filter-1tahun" class="btn" data-val="<?= date('Y', strtotime('-1 year')) ?>">Per Tahun <?= date('Y', strtotime('-1 year')) ?></button>
                </div>
            </div>

            <!-- 2. Project Stock & Target Strip -->
            <div class="project-stock-target-card">
                <div class="stock-target-header">
                    <h5 class="stock-target-title">
                        <i data-feather="pie-chart"></i>
                        <span>Status Stok Kavling & Realisasi Target</span>
                    </h5>
                    <div class="text-muted font-small-3">
                        Tahun Target: <strong id="target-tahun-label" class="text-primary"><?= date('Y') ?></strong> &middot; Target: <strong id="target-total-kavling">0</strong> unit
                    </div>
                </div>

                <!-- 6 Mini Stock Cards -->
                <div class="stock-grid">
                    <div class="stock-item stock-total">
                        <div class="stock-label">Total Kavling</div>
                        <div class="stock-value" id="stock-val-total">-</div>
                    </div>
                    <div class="stock-item stock-tersedia">
                        <div class="stock-label">Tersedia</div>
                        <div class="stock-value" id="stock-val-tersedia">-</div>
                    </div>
                    <div class="stock-item stock-booking">
                        <div class="stock-label">Booking</div>
                        <div class="stock-value" id="stock-val-booking">-</div>
                    </div>
                    <div class="stock-item stock-akad">
                        <div class="stock-label">Akad</div>
                        <div class="stock-value" id="stock-val-akad">-</div>
                    </div>
                    <div class="stock-item stock-lunas">
                        <div class="stock-label">Lunas</div>
                        <div class="stock-value" id="stock-val-lunas">-</div>
                    </div>
                    <div class="stock-item stock-batal">
                        <div class="stock-label">Batal</div>
                        <div class="stock-value" id="stock-val-batal">-</div>
                    </div>
                </div>

                <!-- Target Progress Bars -->
                <div class="target-bar-section">
                    <div class="target-bar-wrapper">
                        <!-- Realisasi Booking -->
                        <div class="target-col-item">
                            <div class="target-col-header">
                                <span class="target-col-label"><i data-feather="bookmark" style="width:13px;height:13px;"></i> Realisasi Booking</span>
                                <span class="target-col-metric"><span id="target-real-booking">0</span> unit (<span id="target-pct-booking">0%</span>)</span>
                            </div>
                            <div class="target-col-progress">
                                <div class="target-col-progress-bar bar-booking" id="target-bar-booking"></div>
                            </div>
                        </div>
                        <!-- Realisasi Akad -->
                        <div class="target-col-item">
                            <div class="target-col-header">
                                <span class="target-col-label"><i data-feather="check-square" style="width:13px;height:13px;"></i> Realisasi Akad</span>
                                <span class="target-col-metric"><span id="target-real-akad">0</span> unit (<span id="target-pct-akad">0%</span>)</span>
                            </div>
                            <div class="target-col-progress">
                                <div class="target-col-progress-bar bar-akad" id="target-bar-akad"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Action Queue Strip (Tugas Saya & Persetujuan Mendesak) -->
            <div id="dashboard-action-queue-section" class="dashboard-action-queue" style="display: none;">
                <div class="divider divider-left">
                    <div class="divider-text"><i data-feather="bell" class="mr-50"></i> Tugas Saya & Menunggu Persetujuan</div>
                </div>
                <div class="action-queue-grid" id="dashboard-action-queue-list"></div>
            </div>

            <!-- 4. Primary KPI Grid (Role-Aware) -->
            <div class="dashboard-kpi-grid" id="dashboard-primary-kpi-grid">
                <!-- Rendered dynamically by renderRoleKpiCards(r) -->
                <div class="dashboard-kpi-card text-center py-3">
                    <div class="spinner-border spinner-border-sm text-primary mb-1" role="status"></div>
                    <div class="text-muted font-small-3">Memuat indikator utama...</div>
                </div>
            </div>

            <!-- 5. Chart & Actionable Alerts Row -->
            <div class="row match-height">
                <!-- Sales Trend Chart -->
                <div class="col-lg-8 col-12">
                    <div class="card chart-card">
                        <div class="card-header d-flex justify-content-between align-items-start pb-1">
                            <div>
                                <h4 id="chart-judul" class="card-title mb-25">Trend Sales Tahun <?= date('Y') ?></h4>
                                <p class="card-text font-small-2 text-muted mb-0">Booking dibanding Akad (klik titik data untuk rincian unit)</p>
                            </div>
                            <div class="dropdown chart-dropdown">
                                <i data-feather="more-vertical" class="font-medium-3 cursor-pointer" data-toggle="dropdown"></i>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a id="filter-chart-tahun-sekarang" class="dropdown-item" href="javascript:void(0);">Per <?= date('Y') ?></a>
                                    <a id="filter-chart-tahun-lalu" class="dropdown-item" href="javascript:void(0);">Per <?= date('Y', strtotime('-1 year')) ?></a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="sales-visit-chart" class="mt-50" style="height:100%">
                                <canvas id="myLineChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Alert Perlu Tindakan (Clickable) -->
                <div class="col-lg-4 col-12">
                    <div class="card alerts-card">
                        <div class="card-header border-bottom pb-1">
                            <h4 class="card-title mb-0">Alert Perlu Tindakan</h4>
                        </div>
                        <div class="card-body py-1" id="dashboard-alerts" style="overflow-y:auto">
                            <div class="dashboard-alert-item">
                                <div class="dashboard-alert-head">
                                    <span class="dashboard-alert-label">Memuat alert...</span>
                                    <span class="dashboard-alert-value">-</span>
                                </div>
                                <div class="dashboard-alert-desc">Data alert akan dimuat otomatis sesuai proyek aktif</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 6. Department Workspace Tabs & Panels -->
            <div class="divider divider-left mt-1 mb-1">
                <div class="divider-text"><i data-feather="layers" class="mr-50"></i> Workspace & Rincian Departemen</div>
            </div>

            <div class="dashboard-dept-nav-wrapper">
                <ul class="nav nav-tabs dashboard-dept-tabs" id="dashboard-dept-tabs" role="tablist">
                    <?php if (in_array('mkdt_sales', $visibleTabs, true)): ?>
                        <li class="nav-item" id="tab-mkdt_sales">
                            <a class="nav-link dashboard-dept-tab-link <?= $defaultTab === 'mkdt_sales' ? 'active' : '' ?>" id="tab-btn-mkdt_sales" data-toggle="tab" href="#panel-mkdt_sales" role="tab" aria-controls="panel-mkdt_sales">
                                <i data-feather="users"></i> MKDT & Sales
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if (in_array('produksi', $visibleTabs, true)): ?>
                        <li class="nav-item">
                            <a class="nav-link dashboard-dept-tab-link <?= $defaultTab === 'produksi' ? 'active' : '' ?>" id="tab-btn-produksi" data-toggle="tab" href="#panel-produksi" role="tab" aria-controls="panel-produksi">
                                <i data-feather="tool"></i> Produksi & Lapangan
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if ($canViewFinance && in_array('keuangan', $visibleTabs, true)): ?>
                        <li class="nav-item">
                            <a class="nav-link dashboard-dept-tab-link <?= $defaultTab === 'keuangan' ? 'active' : '' ?>" id="tab-btn-keuangan" data-toggle="tab" href="#panel-keuangan" role="tab" aria-controls="panel-keuangan">
                                <i data-feather="dollar-sign"></i> Keuangan & Cashflow
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if (in_array('legal', $visibleTabs, true)): ?>
                        <li class="nav-item">
                            <a class="nav-link dashboard-dept-tab-link <?= $defaultTab === 'legal' ? 'active' : '' ?>" id="tab-btn-legal" data-toggle="tab" href="#panel-legal" role="tab" aria-controls="panel-legal">
                                <i data-feather="file-text"></i> Legalitas & Sertifikat
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if (in_array('planning', $visibleTabs, true)): ?>
                        <li class="nav-item">
                            <a class="nav-link dashboard-dept-tab-link <?= $defaultTab === 'planning' ? 'active' : '' ?>" id="tab-btn-planning" data-toggle="tab" href="#panel-planning" role="tab" aria-controls="panel-planning">
                                <i data-feather="map"></i> Planning & Master Data
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if (in_array('operasional', $visibleTabs, true)): ?>
                        <li class="nav-item">
                            <a class="nav-link dashboard-dept-tab-link <?= $defaultTab === 'operasional' ? 'active' : '' ?>" id="tab-btn-operasional" data-toggle="tab" href="#panel-operasional" role="tab" aria-controls="panel-operasional">
                                <i data-feather="activity"></i> Operasional
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Tab Panels Content -->
            <div class="tab-content" id="dashboard-dept-tab-content">

                <!-- 6.1 Panel MKDT & Sales -->
                <?php if (in_array('mkdt_sales', $visibleTabs, true)): ?>
                    <div class="tab-pane fade <?= $defaultTab === 'mkdt_sales' ? 'show active' : '' ?>" id="panel-mkdt_sales" role="tabpanel">
                        <div class="dept-panel-card">
                            <h5 class="mb-50 font-weight-bold text-primary"><i data-feather="filter" class="mr-50"></i> Funnel Pipeline Penjualan & MKDT</h5>
                            <p class="text-muted font-small-3 mb-1">Tahapan proses penjualan konsumen dari NUP hingga siap akad kredit.</p>

                            <!-- Funnel Steps -->
                            <div class="funnel-grid">
                                <div class="funnel-step">
                                    <div class="funnel-step-label">1. NUP Aktif</div>
                                    <div class="funnel-step-val" id="mkdt-funnel-nup">0</div>
                                </div>
                                <div class="funnel-step">
                                    <div class="funnel-step-label">2. Booking Aktif</div>
                                    <div class="funnel-step-val" id="mkdt-funnel-booking">0</div>
                                </div>
                                <div class="funnel-step">
                                    <div class="funnel-step-label">3. Wawancara</div>
                                    <div class="funnel-step-val" id="mkdt-funnel-wawancara">0</div>
                                </div>
                                <div class="funnel-step">
                                    <div class="funnel-step-label">4. Sudah SP3K</div>
                                    <div class="funnel-step-val" id="mkdt-funnel-sp3k">0</div>
                                </div>
                                <div class="funnel-step">
                                    <div class="funnel-step-label">5. Perintah Bangun</div>
                                    <div class="funnel-step-val" id="mkdt-funnel-spk">0</div>
                                </div>
                                <div class="funnel-step">
                                    <div class="funnel-step-label">6. Sudah Akad</div>
                                    <div class="funnel-step-val text-success" id="mkdt-funnel-akad">0</div>
                                </div>
                            </div>

                            <!-- Composition Breakdown -->
                            <div class="composition-grid">
                                <div class="composition-card">
                                    <div class="font-weight-bold font-small-3 text-dark mb-50"><i data-feather="home" class="mr-50" style="width:14px;height:14px;"></i> Komposisi Unit Penjualan</div>
                                    <div class="d-flex justify-content-between border-bottom py-50 font-small-3">
                                        <span>Subsidi Pemerintah</span>
                                        <strong id="mkdt-count-subsidi" class="text-primary">0</strong>
                                    </div>
                                    <div class="d-flex justify-content-between py-50 font-small-3">
                                        <span>Komersil / Non-Subsidi</span>
                                        <strong id="mkdt-count-komersil" class="text-info">0</strong>
                                    </div>
                                </div>
                                <div class="composition-card">
                                    <div class="font-weight-bold font-small-3 text-dark mb-50"><i data-feather="credit-card" class="mr-50" style="width:14px;height:14px;"></i> Skema Pembayaran Konsumen</div>
                                    <div class="d-flex justify-content-between border-bottom py-50 font-small-3">
                                        <span>KPR Bank</span>
                                        <strong id="mkdt-count-kpr" class="text-primary">0</strong>
                                    </div>
                                    <div class="d-flex justify-content-between py-50 font-small-3">
                                        <span>Tunai / Cash Bertahap</span>
                                        <strong id="mkdt-count-tunai" class="text-success">0</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- 6.3 Panel Produksi & Lapangan -->
                <?php if (in_array('produksi', $visibleTabs, true)): ?>
                    <div class="tab-pane fade <?= $defaultTab === 'produksi' ? 'show active' : '' ?>" id="panel-produksi" role="tabpanel">
                        <div class="dept-panel-card">
                            <h5 class="mb-50 font-weight-bold text-primary"><i data-feather="tool" class="mr-50"></i> Distribusi Tahapan Fisik Bangunan</h5>
                            <p class="text-muted font-small-3 mb-1">Status dan sebaran tahapan konstruksi kavling di lapangan.</p>

                            <!-- Stage Progress Distribution -->
                            <div class="prod-progress-grid">
                                <div class="prod-stage-box">
                                    <div class="stage-percent">Tahap 0% (Fondasi)</div>
                                    <div class="stage-count" id="prod-stage-0">0</div>
                                </div>
                                <div class="prod-stage-box">
                                    <div class="stage-percent">Tahap 25% (Struktur)</div>
                                    <div class="stage-count" id="prod-stage-25">0</div>
                                </div>
                                <div class="prod-stage-box">
                                    <div class="stage-percent">Tahap 50% (Dinding/Atap)</div>
                                    <div class="stage-count" id="prod-stage-50">0</div>
                                </div>
                                <div class="prod-stage-box">
                                    <div class="stage-percent">Tahap 75% (Finishing)</div>
                                    <div class="stage-count" id="prod-stage-75">0</div>
                                </div>
                                <div class="prod-stage-box">
                                    <div class="stage-percent text-success font-weight-bold">Tahap 100% (Selesai)</div>
                                    <div class="stage-count text-success" id="prod-stage-100">0</div>
                                </div>
                            </div>

                            <!-- Readiness & Utilities -->
                            <div class="prod-utility-grid">
                                <div class="prod-utility-card">
                                    <div class="prod-utility-icon slf"><i data-feather="check-circle"></i></div>
                                    <div>
                                        <div class="prod-utility-title">Kelaikan Fungsi (SLF)</div>
                                        <div class="prod-utility-val" id="prod-util-slf">0</div>
                                    </div>
                                </div>
                                <div class="prod-utility-card">
                                    <div class="prod-utility-icon pln"><i data-feather="zap"></i></div>
                                    <div>
                                        <div class="prod-utility-title">Jaringan Listrik (PLN)</div>
                                        <div class="prod-utility-val" id="prod-util-listrik">0</div>
                                    </div>
                                </div>
                                <div class="prod-utility-card">
                                    <div class="prod-utility-icon pdam"><i data-feather="droplet"></i></div>
                                    <div>
                                        <div class="prod-utility-title">Air Bersih (PDAM/Sumur)</div>
                                        <div class="prod-utility-val" id="prod-util-air">0</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- 6.4 Panel Keuangan & Cashflow (Role-Guarded) -->
                <?php if ($canViewFinance && in_array('keuangan', $visibleTabs, true)): ?>
                    <div class="tab-pane fade <?= $defaultTab === 'keuangan' ? 'show active' : '' ?>" id="panel-keuangan" role="tabpanel">
                        <div class="dept-panel-card">
                            <h5 class="mb-50 font-weight-bold text-primary"><i data-feather="credit-card" class="mr-50"></i> Ringkasan Keuangan & Arus Kas</h5>
                            <p class="text-muted font-small-3 mb-1">Status tagihan konsumen, pencairan akad bank, dan kewajiban pengeluaran proyek.</p>

                            <div class="finance-grid">
                                <div class="finance-box">
                                    <div class="finance-box-label">Tagihan Belum Dibayar</div>
                                    <div class="finance-box-val text-danger" id="fin-nominal-unpaid">Rp 0</div>
                                    <div class="text-muted font-small-2"><span id="fin-tagihan-unpaid">0</span> berkas tagihan</div>
                                </div>
                                <div class="finance-box">
                                    <div class="finance-box-label">Tagihan Lewat Tempo</div>
                                    <div class="finance-box-val text-danger" id="fin-tagihan-overdue">0</div>
                                    <div class="text-muted font-small-2">Jatuh tempo 7 hari: <strong id="fin-tagihan-due">0</strong></div>
                                </div>
                                <div class="finance-box">
                                    <div class="finance-box-label">Pembayaran Masuk Periode</div>
                                    <div class="finance-box-val text-success" id="fin-payment-in">Rp 0</div>
                                    <div class="text-muted font-small-2">Kas masuk terverifikasi</div>
                                </div>
                                <div class="finance-box">
                                    <div class="finance-box-label">Total Cashout Periode</div>
                                    <div class="finance-box-val text-dark" id="fin-cashout-total">Rp 0</div>
                                    <div class="text-muted font-small-2">Pengeluaran operasional & bahan</div>
                                </div>
                                <div class="finance-box">
                                    <div class="finance-box-label">Subkon Jatuh Tempo</div>
                                    <div class="finance-box-val text-warning" id="fin-cashout-subkon-due">0</div>
                                    <div class="text-muted font-small-2">Termin pemborong perlu cair</div>
                                </div>
                                <div class="finance-box">
                                    <div class="finance-box-label">Piutang Pencairan Akad</div>
                                    <div class="finance-box-val text-info" id="fin-piutang-akad">Rp 0</div>
                                    <div class="text-muted font-small-2">Dana akad menunggu pencairan bank</div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- 6.5 Panel Legalitas & Sertifikat -->
                <?php if (in_array('legal', $visibleTabs, true)): ?>
                    <div class="tab-pane fade <?= $defaultTab === 'legal' ? 'show active' : '' ?>" id="panel-legal" role="tabpanel">
                        <div class="dept-panel-card">
                            <h5 class="mb-50 font-weight-bold text-primary"><i data-feather="file-text" class="mr-50"></i> Status Legalitas & Sertifikasi Unit</h5>
                            <p class="text-muted font-small-3 mb-1">Monitoring pemecahan sertifikat, perizinan gedung, dan validasi pajak pembeli.</p>

                            <div class="legal-docs-grid">
                                <div class="legal-doc-item">
                                    <div class="legal-doc-label">Unit Terjual Membutuhkan Legal</div>
                                    <div class="legal-doc-value" id="legal-terjual">0</div>
                                    <div class="text-muted font-small-2">Belum ada berkas: <strong class="text-danger" id="legal-belum">0</strong></div>
                                </div>
                                <div class="legal-doc-item">
                                    <div class="legal-doc-label">Sertifikat Split HGB</div>
                                    <div class="legal-doc-value text-success" id="legal-split">0</div>
                                    <div class="text-muted font-small-2">Unit sudah pecah sertifikat</div>
                                </div>
                                <div class="legal-doc-item">
                                    <div class="legal-doc-label">Sertifikat Balik Nama</div>
                                    <div class="legal-doc-value text-info" id="legal-baliknama">0</div>
                                    <div class="text-muted font-small-2">Atas nama pembeli</div>
                                </div>
                                <div class="legal-doc-item">
                                    <div class="legal-doc-label">Persetujuan Bangunan (PBG)</div>
                                    <div class="legal-doc-value text-primary" id="legal-pbg">0</div>
                                    <div class="text-muted font-small-2">Izin mendirikan bangunan</div>
                                </div>
                                <div class="legal-doc-item">
                                    <div class="legal-doc-label">Pecah NOP PBB</div>
                                    <div class="legal-doc-value" id="legal-pbb">0</div>
                                    <div class="text-muted font-small-2">Pajak bumi & bangunan per unit</div>
                                </div>
                                <div class="legal-doc-item">
                                    <div class="legal-doc-label">Akta Jual Beli (AJB)</div>
                                    <div class="legal-doc-value" id="legal-ajb">0</div>
                                    <div class="text-muted font-small-2">Akta PPAT selesai</div>
                                </div>
                                <div class="legal-doc-item">
                                    <div class="legal-doc-label">BPHTB Tervalidasi</div>
                                    <div class="legal-doc-value text-success" id="legal-bphtb-valid">0</div>
                                    <div class="text-muted font-small-2">Pajak perolehan hak sah</div>
                                </div>
                                <div class="legal-doc-item">
                                    <div class="legal-doc-label">BPHTB Mendekati Jatuh Tempo</div>
                                    <div class="legal-doc-value text-warning" id="legal-bphtb-tempo">0</div>
                                    <div class="text-muted font-small-2">Perlu segera disetorkan</div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- 6.6 Panel Planning & Master Data -->
                <?php if (in_array('planning', $visibleTabs, true)): ?>
                    <div class="tab-pane fade <?= $defaultTab === 'planning' ? 'show active' : '' ?>" id="panel-planning" role="tabpanel">
                        <div class="dept-panel-card">
                            <h5 class="mb-50 font-weight-bold text-primary"><i data-feather="map" class="mr-50"></i> Master Planning & Pemetaan Siteplan</h5>
                            <p class="text-muted font-small-3 mb-1">Status persetujuan permohonan kavling baru dan data infrastruktur proyek.</p>

                            <div class="planning-grid">
                                <div class="planning-box">
                                    <h6 class="font-weight-bold text-dark mb-1"><i data-feather="inbox" class="mr-50"></i> Permohonan Kavling (Approval Queue)</h6>
                                    <div class="planning-stat-row">
                                        <span>Menunggu Persetujuan (Pending)</span>
                                        <strong class="badge badge-light-warning" id="plan-req-pending">0</strong>
                                    </div>
                                    <div class="planning-stat-row">
                                        <span>Disetujui (Approved)</span>
                                        <strong class="badge badge-light-success" id="plan-req-approved">0</strong>
                                    </div>
                                    <div class="planning-stat-row">
                                        <span>Ditolak (Rejected)</span>
                                        <strong class="badge badge-light-danger" id="plan-req-rejected">0</strong>
                                    </div>
                                    <div class="planning-stat-row">
                                        <span>Total Riwayat Permohonan</span>
                                        <strong id="plan-req-total">0</strong>
                                    </div>
                                </div>

                                <div class="planning-box">
                                    <h6 class="font-weight-bold text-dark mb-1"><i data-feather="grid" class="mr-50"></i> Infrastruktur & Koordinat Kavling</h6>
                                    <div class="planning-stat-row">
                                        <span>Total Cluster</span>
                                        <strong id="plan-infra-cluster">0</strong>
                                    </div>
                                    <div class="planning-stat-row">
                                        <span>Total Ruas Jalan</span>
                                        <strong id="plan-infra-jalan">0</strong>
                                    </div>
                                    <div class="planning-stat-row">
                                        <span>Tipe Desain Bangunan</span>
                                        <strong id="plan-infra-tipe">0</strong>
                                    </div>
                                    <div class="planning-stat-row">
                                        <span>Kavling Terpetakan Koordinat</span>
                                        <strong class="text-success" id="plan-infra-terpetakan">0</strong>
                                    </div>
                                    <div class="planning-stat-row">
                                        <span>Kavling Belum Terpetakan</span>
                                        <strong class="text-danger" id="plan-infra-belum">0</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- 6.7 Panel Operasional -->
                <?php if (in_array('operasional', $visibleTabs, true)): ?>
                    <div class="tab-pane fade <?= $defaultTab === 'operasional' ? 'show active' : '' ?>" id="panel-operasional" role="tabpanel">
                        <div class="dept-panel-card">
                            <h5 class="mb-50 font-weight-bold text-primary"><i data-feather="activity" class="mr-50"></i> Aktivitas Operasional Proyek</h5>
                            <p class="text-muted font-small-3 mb-1">Ringkasan aktivitas harian dan pemantauan kendala lapangan.</p>
                            <div class="alert alert-primary mb-0" role="alert">
                                <div class="alert-body font-small-3">
                                    Silakan gunakan tab departemen di atas atau pilih proyek pada bilah navigasi utama untuk melihat data operasional terperinci.
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

            </div>

            <!-- 7. Riwayat Tiket Masalah & Aktivitas Terakhir Row -->
            <div class="row match-height">
                <!-- Tiket Masalah -->
                <div class="col-lg-8 col-12">
                    <div class="card dashboard-table-card">
                        <div class="card-header border-bottom">
                            <h4 class="card-title">Riwayat Tiket Masalah (Update Terakhir)</h4>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th style="min-width: 170px;">Waktu & Lokasi</th>
                                        <th>Keterangan</th>
                                        <th style="width: 100px;">Prioritas</th>
                                        <th style="width: 110px;">Status</th>
                                        <th style="width: 90px;" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="dashboard-tiket-masalah-body">
                                    <tr>
                                        <td colspan="5" class="text-center py-2">Pilih proyek untuk memuat data tiket masalah</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Aktivitas Terakhir -->
                <div class="col-lg-4 col-12">
                    <div class="card card-user-timeline" style="max-height: 500px;">
                        <div class="card-header border-bottom pb-1">
                            <div class="d-flex align-items-center">
                                <i data-feather="list" class="mr-50 text-primary"></i>
                                <h4 class="card-title mb-0">Aktivitas Terakhir</h4>
                            </div>
                        </div>
                        <div class="card-body p-0" style="overflow-y: scroll;" id="aktivitas-body">
                            <div class="activity-feed" id="aktivitas-here">
                                <div class="activity-empty">Pilih proyek untuk melihat aktivitas terbaru</div>
                            </div>
                        </div>
                        <div class="card-footer text-center py-50 border-top">
                            <a href="javascript:void(0)" id="dashboard-view-all-activity" class="font-weight-bold text-primary font-small-3">Lihat semua notifikasi</a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
<!-- END: Content-->

<!-- Modal Pratinjau Kavling Chart (Clean & Scoped) -->
<div class="modal fade" id="modal-chart-kavling" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-chart-kavling-title">Daftar Kavling</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <ul class="kavling-pill-list" id="modal-chart-kavling-list"></ul>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tiket Masalah Partials -->
<script>
    var current_user_id = <?= (int) (user_id() ?? 0) ?>;
</script>
<?= view('siteplan/partials/modal_tiket_masalah_styles') ?>
<?= view('siteplan/partials/modal_tiket_masalah') ?>

<!-- BEGIN: Page Vendor JS-->
<script src="<?= base_url('app-assets/vendors/js/vendors.min.js') ?>"></script>
<script src="<?= base_url('app-assets/vendors/js/extensions/sweetalert2.all.min.js') ?>"></script>
<script src="<?= base_url('app-assets/vendors/js/pickers/flatpickr/flatpickr.min.js') ?>"></script>
<script src="<?= base_url('app-assets/vendors/js/forms/select/select2.full.min.js') ?>"></script>
<script src="<?= base_url('app-assets/vendors/js/extensions/toastr.min.js') ?>"></script>
<script src="<?= base_url('app-assets/js/scripts/charts/chart.js') ?>"></script>
<!-- END: Page Vendor JS-->

<script src="<?= base_url('assets/js/siteplan/tiket-masalah.js?v=' . filemtime(FCPATH . 'assets/js/siteplan/tiket-masalah.js')) ?>"></script>

<!-- BEGIN: Module JS (SIGAPP 3-Tier Architecture & filemtime Cache Busting) -->
<script src="<?= base_url('assets/js/dashboard.js') ?>?v=<?= filemtime(FCPATH . 'assets/js/dashboard.js') ?>"></script>
<!-- END: Module JS -->
