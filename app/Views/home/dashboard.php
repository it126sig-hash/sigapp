<!-- BEGIN: Vendor CSS-->
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/vendors.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/pickers/flatpickr/flatpickr.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/forms/select/select2.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/extensions/toastr.min.css">

<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/pickers/flatpickr/flatpickr.min.css">

<!-- END: Vendor CSS-->
<script>
	// const base_url = "<?= base_url() ?>"
	// var csrfName = '<?= csrf_token() ?>';
	// var csrfHash = '<?= csrf_hash() ?>';
</script>
<style>
	.dashboard-header-row {
		display: flex;
		align-items: center;
		justify-content: space-between;
		flex-wrap: wrap;
		gap: .75rem;
		margin-bottom: 1rem;
	}

	.dashboard-header-row h2 {
		margin-bottom: 0;
	}

	.dashboard-header-row .dashboard-subtitle {
		color: #94a3b8;
		font-size: .86rem;
		margin-top: .15rem;
		margin-bottom: 0;
	}

	.dashboard-period-pills {
		display: inline-flex;
		background: #f1f0f5;
		border-radius: 999px;
		padding: .25rem;
		gap: .15rem;
	}

	.dashboard-period-pills .btn {
		border: none;
		border-radius: 999px;
		padding: .4rem 1rem;
		font-size: .86rem;
		font-weight: 600;
		color: #6e6b7b;
		background: transparent;
	}

	.dashboard-period-pills .btn.active {
		background: #fff;
		color: #7367f0;
		box-shadow: 0 1px 4px rgba(0, 0, 0, .12);
	}

	.dashboard-kpi-grid {
		display: grid;
		grid-template-columns: repeat(4, minmax(0, 1fr));
		gap: 1rem;
		height: 100%;
		flex: 1 1 auto;
		align-items: stretch;
	}

	.dashboard-kpi-card {
		border: 1px solid #eef0f4;
		border-radius: 16px;
		background: #fff;
		height: 100%;
		padding: 1.15rem 1.25rem;
		min-height: 150px;
		display: flex;
		flex-direction: column;
		box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
	}

	.kpi-card-head {
		display: flex;
		align-items: flex-start;
		justify-content: space-between;
		gap: .5rem;
	}

	.dashboard-kpi-card .kpi-label {
		color: #94a3b8;
		font-size: .7rem;
		font-weight: 700;
		letter-spacing: .05em;
		text-transform: uppercase;
	}

	.kpi-icon-badge {
		width: 34px;
		height: 34px;
		border-radius: 50%;
		display: flex;
		align-items: center;
		justify-content: center;
		flex-shrink: 0;
	}

	.kpi-icon-badge svg {
		width: 17px;
		height: 17px;
		stroke-width: 2;
	}

	.kpi-icon-badge.green { background: #dcfce7; color: #16a34a; }
	.kpi-icon-badge.amber { background: #fef3c7; color: #d97706; }
	.kpi-icon-badge.slate { background: #e2e8f0; color: #475569; }

	.kpi-value-row {
		display: flex;
		align-items: baseline;
		gap: .35rem;
		margin: .65rem 0 .6rem;
	}

	.dashboard-kpi-card .kpi-value {
		color: #0f172a;
		font-size: 1.9rem;
		font-weight: 800;
		line-height: 1;
		word-break: break-word;
	}

	.kpi-unit {
		font-size: .78rem;
		color: #94a3b8;
		font-weight: 600;
	}

	.kpi-progress {
		height: 6px;
		border-radius: 999px;
		background: #f1f5f9;
		overflow: hidden;
		margin-bottom: .75rem;
	}

	.kpi-progress-bar {
		height: 100%;
		width: 0%;
		border-radius: 999px;
		transition: width .4s ease;
	}

	.kpi-progress-bar.green { background: #22c55e; }
	.kpi-progress-bar.amber { background: #f59e0b; }

	.kpi-stats-row {
		display: flex;
		gap: 1.25rem;
		font-size: .78rem;
		color: #64748b;
	}

	.kpi-stats-row strong {
		color: #0f172a;
		font-weight: 700;
		margin-right: .25rem;
	}

	.kpi-dot {
		width: 7px;
		height: 7px;
		border-radius: 50%;
		display: inline-block;
		margin-right: .35rem;
	}

	.kpi-dot.green { background: #22c55e; }
	.kpi-dot.red { background: #ef4444; }

	.kpi-alert-pill {
		display: inline-flex;
		align-items: center;
		gap: .35rem;
		background: #fee2e2;
		color: #dc2626;
		font-weight: 700;
		font-size: .76rem;
		padding: .3rem .65rem;
		border-radius: 999px;
		width: fit-content;
	}

	.kpi-alert-pill.ok {
		background: #dcfce7;
		color: #16a34a;
	}

	.kpi-card-caption {
		color: #94a3b8;
		font-size: .76rem;
		margin-top: .5rem;
	}

	.dashboard-mini-list {
		display: grid;
		grid-template-columns: repeat(3, minmax(0, 1fr));
		gap: .75rem;
	}

	.dashboard-mini-item {
		border: 1px solid #eef0f4;
		border-radius: 14px;
		padding: 1rem;
		background: #fff;
	}

	.dashboard-mini-head {
		display: flex;
		align-items: flex-start;
		justify-content: space-between;
		gap: .5rem;
		margin-bottom: .55rem;
	}

	.dashboard-mini-icon {
		width: 30px;
		height: 30px;
		border-radius: 50%;
		display: flex;
		align-items: center;
		justify-content: center;
		flex-shrink: 0;
	}

	.dashboard-mini-icon svg {
		width: 15px;
		height: 15px;
		stroke-width: 2;
	}

	.dashboard-mini-icon.gray { background: #f1f5f9; color: #64748b; }
	.dashboard-mini-icon.green { background: #dcfce7; color: #16a34a; }
	.dashboard-mini-icon.red { background: #fee2e2; color: #dc2626; }

	.dashboard-mini-label,
	.dashboard-alert-desc {
		color: #94a3b8;
		font-size: .7rem;
		font-weight: 700;
		letter-spacing: .05em;
		text-transform: uppercase;
	}

	.dashboard-mini-value {
		color: #0f172a;
		font-size: 1.25rem;
		font-weight: 800;
	}

	.dashboard-mini-sub {
		color: #94a3b8;
		font-size: .74rem;
		margin-top: .35rem;
	}

	.dashboard-mini-progress {
		width: 60px;
		height: 5px;
		border-radius: 999px;
		background: #f1f5f9;
		overflow: hidden;
		margin-top: .3rem;
	}

	.dashboard-mini-progress-bar {
		height: 100%;
		width: 0%;
		background: #22c55e;
		border-radius: 999px;
		transition: width .4s ease;
	}

	.dashboard-card-title-icon {
		width: 28px;
		height: 28px;
		border-radius: 8px;
		background: #dcfce7;
		color: #16a34a;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		margin-right: .5rem;
	}

	.dashboard-card-title-icon svg {
		width: 15px;
		height: 15px;
		stroke-width: 2;
	}

	.dashboard-alert-item {
		border: 1px solid #ebe9f1;
		border-radius: 8px;
		padding: .8rem .9rem;
		margin-bottom: .75rem;
		background: #fff;
	}

	.dashboard-alert-item:last-child {
		margin-bottom: 0;
	}

	.dashboard-alert-head {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: .75rem;
	}

	.dashboard-alert-value {
		font-weight: 700;
		font-size: 1.05rem;
	}

	.dashboard-alert-danger {
		border-left: 4px solid #ea5455;
	}

	.dashboard-alert-warning {
		border-left: 4px solid #ff9f43;
	}

	.dashboard-alert-info {
		border-left: 4px solid #00cfe8;
	}

	.card-header .chart-dropdown,
	.card-header .chart-dropdown .dropdown-menu {
		overflow: visible !important;
	}

	.content-body > .row,
	.content-body > .row > [class*="col-"] {
		overflow: visible;
	}

	.card-header .chart-dropdown .dropdown-menu {
		z-index: 2050;
	}

	@media (max-width: 1199.98px) {
		.dashboard-kpi-grid,
		.dashboard-mini-list {
			grid-template-columns: repeat(2, minmax(0, 1fr));
		}
	}

	@media (max-width: 767.98px) {
		.dashboard-kpi-grid,
		.dashboard-mini-list {
			grid-template-columns: 1fr;
		}
	}

	.activity-feed {
		max-height: 420px;
	}
</style>
<!-- BEGIN: Content-->
<div class="app-content content ">
	<div class="content-overlay"></div>
	<div class="header-navbar-shadow"></div>
	<div class="content-wrapper">
		<div class="content-header row"></div>
		<div class="content-body">
			<div class="dashboard-header-row">
				<div>
					<h2>Dashboard</h2>
					<p class="dashboard-subtitle">Real-time operational and sales monitoring</p>
				</div>
				<div class="dashboard-period-pills" id="filter-statistik-pills">
					<button type="button" id="filter-bulan" class="btn active">Per <?= date("F") ?></button>
					<button type="button" id="filter-6bulan" class="btn" data-val="<?= date("Y") ?>">Per <?= date("Y") ?></button>
					<button type="button" id="filter-1tahun" class="btn" data-val="<?= date("Y", strtotime("-1 year")) ?>">Per <?= date("Y", strtotime("-1 year")) ?></button>
				</div>
			</div>
			<div class="row match-height pb-1">
				<div class="col-12">
					<div class="dashboard-kpi-grid">
						<div class="dashboard-kpi-card">
							<div class="kpi-card-head">
								<div class="kpi-label">Kavling</div>
								<div class="kpi-icon-badge green"><i data-feather="home"></i></div>
							</div>
							<div class="kpi-value-row">
								<div class="kpi-value" id="dash_total_kavling">-</div>
								<div class="kpi-unit">units</div>
							</div>
							<div class="kpi-progress"><div class="kpi-progress-bar green" id="kavling-progress-bar"></div></div>
							<div class="kpi-stats-row">
								<div><strong id="dash_kavling_available">-</strong>tersedia</div>
								<div><strong id="dash_kavling_lunas">-</strong>lunas</div>
							</div>
						</div>
						<div class="dashboard-kpi-card">
							<div class="kpi-card-head">
								<div class="kpi-label">Sales Periode</div>
								<div class="kpi-icon-badge amber"><i data-feather="tag"></i></div>
							</div>
							<div class="kpi-value-row">
								<div class="kpi-value" id="st_booking">-</div>
								<div class="kpi-unit">booking</div>
							</div>
							<div class="kpi-progress"><div class="kpi-progress-bar amber" id="sales-progress-bar"></div></div>
							<div class="kpi-stats-row">
								<div><strong id="st_booking_akad">-</strong>akad</div>
								<div><strong id="st_booking_aktif">-</strong>booking</div>
							</div>
							<div class="kpi-card-caption"><span id="st_sp3k">-</span> SP3K &middot; <span id="st_booking_batal">-</span> batal &middot; Konversi <span id="dash_sales_rate">0%</span></div>
						</div>
						<div class="dashboard-kpi-card">
							<div class="kpi-card-head">
								<div class="kpi-label">Keuangan</div>
								<div class="kpi-icon-badge slate"><i data-feather="credit-card"></i></div>
							</div>
							<div class="kpi-card-caption" style="margin-top:.5rem">Total Belum Dibayar</div>
							<div class="kpi-value-row">
								<div class="kpi-value" id="dash_finance_unpaid" style="font-size:1.5rem">Rp 0</div>
							</div>
							<div class="kpi-alert-pill" id="finance-overdue-pill"><i data-feather="alert-circle"></i> <span id="dash_finance_overdue">0</span> overdue</div>
							<div class="kpi-card-caption">Masuk periode: <span id="dash_payment_in">Rp 0</span></div>
						</div>
						<div class="dashboard-kpi-card">
							<div class="kpi-card-head">
								<div class="kpi-label">Produksi</div>
								<div class="kpi-icon-badge green"><i data-feather="tool"></i></div>
							</div>
							<div class="kpi-value-row">
								<div class="kpi-value"><span id="dash_prod_progress">0</span></div>
								<div class="kpi-unit">%</div>
							</div>
							<div class="kpi-stats-row">
								<div><span class="kpi-dot green"></span><strong id="dash_prod_active">0</strong>ongoing</div>
								<div><span class="kpi-dot red"></span><strong id="st_telat">0</strong>delayed</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="row match-height">
				<div class="col-lg-8 col-12">
					<div class="card" style="height:420px;max-height:420px">
						<div class="card-header d-flex justify-content-between align-items-start pb-1">
							<div>
								<h4 id="chart-judul" class="card-title mb-25">Trend Sales Tahun <?= date('Y') ?></h4>
								<p class="card-text font-small-2 text-muted mb-0">Booking dibanding Akad</p>
							</div>
							<div class="dropdown chart-dropdown">
								<i data-feather="more-vertical" class="font-medium-3 cursor-pointer" data-toggle="dropdown"></i>
								<div class="dropdown-menu dropdown-menu-right">
									<a id="filter-chart-tahun-sekarang" class="dropdown-item" href="javascript:void(0);">Per <?= date("Y") ?></a>
									<a id="filter-chart-tahun-lalu" class="dropdown-item" href="javascript:void(0);">Per <?= date("Y", strtotime("-1 year")) ?></a>
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
				<div class="col-lg-4 col-12">
					<div class="card" style="height:420px;max-height:420px">
						<div class="card-header">
							<h4 class="card-title">Alert Perlu Tindakan</h4>
						</div>
						<div class="card-body" id="dashboard-alerts" style="overflow-y:auto">
							<div class="dashboard-alert-item">
								<div class="dashboard-alert-head">
									<span>Pilih proyek</span>
									<span class="dashboard-alert-value">-</span>
								</div>
								<div class="dashboard-alert-desc">Data alert akan dimuat otomatis</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="row match-height">
				<div class="col-lg-8 col-12">
					<div class="card">
						<div class="card-header">
							<div class="d-flex align-items-center">
								<div class="dashboard-card-title-icon"><i data-feather="bar-chart-2"></i></div>
								<h4 class="card-title">Rincian Operasional</h4>
							</div>
						</div>
						<div class="card-body">
							<div class="dashboard-mini-list">
								<div class="dashboard-mini-item">
									<div class="dashboard-mini-head">
										<div class="dashboard-mini-label">Batal Periode</div>
										<div class="dashboard-mini-icon gray"><i data-feather="x-circle"></i></div>
									</div>
									<div class="dashboard-mini-value" id="st_batal">-</div>
								</div>
								<div class="dashboard-mini-item">
									<div class="dashboard-mini-head">
										<div class="dashboard-mini-label">Pembangunan Periode</div>
										<div class="dashboard-mini-icon green"><i data-feather="home"></i></div>
									</div>
									<div class="dashboard-mini-value" id="st_pembangunan">-</div>
								</div>
								<div class="dashboard-mini-item">
									<div class="dashboard-mini-head">
										<div class="dashboard-mini-label">Bangunan 100%</div>
										<div class="dashboard-mini-icon green"><i data-feather="check-circle"></i></div>
									</div>
									<div class="dashboard-mini-value" id="st_100persen">-</div>
								</div>
								<div class="dashboard-mini-item">
									<div class="dashboard-mini-head">
										<div class="dashboard-mini-label">Tagihan Belum Dibayar</div>
										<div class="dashboard-mini-icon red"><i data-feather="file-text"></i></div>
									</div>
									<div class="dashboard-mini-value"><span id="detail_tagihan_belum_bayar">-</span> item</div>
								</div>
								<div class="dashboard-mini-item">
									<div class="dashboard-mini-head">
										<div class="dashboard-mini-label">Cashout Periode</div>
										<div class="dashboard-mini-icon green"><i data-feather="dollar-sign"></i></div>
									</div>
									<div class="dashboard-mini-value" id="detail_cashout_total">Rp 0</div>
								</div>
								<div class="dashboard-mini-item">
									<div class="dashboard-mini-head">
										<div class="dashboard-mini-label">Target <span id="dash_target_year"><?= date('Y') ?></span></div>
										<div class="dashboard-mini-progress"><div class="dashboard-mini-progress-bar" id="target-progress-bar"></div></div>
									</div>
									<div class="dashboard-mini-value"><span id="dash_target_percent">0</span>%</div>
									<div class="dashboard-mini-sub"><span id="dash_target_realization">0</span> / <span id="dash_target_count">0</span> kavling</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-lg-4 col-12">
					<div class="card card-user-timeline" style="max-height:500px">
						<div class="card-header">
							<div class="d-flex align-items-center">
								<i data-feather="list" class="user-timeline-title-icon"></i>
								<h4 class="card-title">Aktivitas Terakhir</h4>
							</div>
						</div>
						<div class="card-body p-0" style="overflow-y:scroll" id="aktivitas-body">
							<div class="activity-feed" id="aktivitas-here">
								<div class="activity-empty">Pilih proyek untuk melihat aktivitas terbaru</div>
							</div>
						</div>
						<div class="card-footer text-center py-50">
							<a href="javascript:void(0)" id="dashboard-view-all-activity">Lihat semua aktivitas</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	</div>
</div>
<!-- END: Content-->

<div class="modal fade" id="modal-chart-kavling">
	<div class="modal-dialog modal-dialog-scrollable">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="modal-chart-kavling-title"></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">x</button>
			</div>
			<div class="modal-body">
				<ul class="list-unstyled mb-0" id="modal-chart-kavling-list"></ul>
			</div>
		</div>
	</div>
</div>

<!-- BEGIN: Vendor JS-->
<script src="<?= base_url() ?>/app-assets/vendors/js/vendors.min.js"></script>
<script src="<?= base_url() ?>/app-assets/vendors/js/pickers/flatpickr/flatpickr.min.js"></script>
<!-- BEGIN Vendor JS-->
<!-- BEGIN: Page Vendor JS-->
<script src="<?= base_url() ?>/app-assets/vendors/js/forms/select/select2.full.min.js"></script>
<script src="<?= base_url() ?>/app-assets/vendors/js/extensions/toastr.min.js"></script>
<script src="<?= base_url() ?>/app-assets/js/scripts/charts/chart.js"></script>
<script src="<?= base_url() ?>/app-assets/vendors/js/pickers/flatpickr/flatpickr.min.js"></script>
<!-- END: Page Vendor JS-->

<script>
	function getFirstDate(m = 0) {
		const today = new Date();
		var year = today.getFullYear();
		var month = (today.getMonth() + 1) - m; // Months are zero-based
		if(month < 0){
			month = 12 + month;
			year = year - 1;
		}

		return `${year}-${month.toString().padStart(2, '0')}-01`;
	}

	function getLastDate() {
		const today = new Date();
		var year = today.getFullYear();
		var month = (today.getMonth() + 1); // Months are zero-based
		const lastDay = new Date(year, month, 0).getDate();

		return `${year}-${month.toString().padStart(2, '0')}-${lastDay.toString().padStart(2, '0')}`;
	}

	function formatNumber(value) {
		return Number(value || 0).toLocaleString('id-ID');
	}

	function formatRupiah(value) {
		return 'Rp ' + formatNumber(Math.round(Number(value || 0)));
	}

	function formatPercent(value) {
		return Number(value || 0).toLocaleString('id-ID', {
			minimumFractionDigits: 0,
			maximumFractionDigits: 1
		});
	}

	function escapeHtml(value) {
		return $('<div>').text(value || '').html();
	}

	function updateDashboardCommandCenter(r) {
		const summary = r.summary || {};
		const finance = r.finance || {};
		const production = r.production || {};
		const target = r.target || {};

		$("#dash_total_kavling").html(formatNumber(summary.total_kavling));
		$("#dash_kavling_available").html(formatNumber(summary.kavling_tersedia));
		$("#dash_kavling_lunas").html(formatNumber(summary.kavling_lunas));
		$("#dash_sales_rate").html(formatPercent(summary.booking_to_akad_rate) + '%');

		const totalKavling = Number(summary.total_kavling || 0);
		const kavlingTerjualPct = totalKavling > 0 ? Math.min(100, ((totalKavling - Number(summary.kavling_tersedia || 0)) / totalKavling) * 100) : 0;
		$("#kavling-progress-bar").css('width', kavlingTerjualPct + '%');
		$("#sales-progress-bar").css('width', Math.min(100, Number(summary.booking_to_akad_rate || 0)) + '%');

		$("#st_booking_akad").html(formatNumber(r.booking_akad));
		$("#st_booking_batal").html(formatNumber(r.booking_batal));
		$("#st_booking_aktif").html(formatNumber(r.booking_aktif));

		$("#dash_finance_unpaid").html(formatRupiah(finance.nominal_belum_bayar));
		$("#dash_finance_overdue").html(formatNumber(finance.tagihan_lewat_tempo));
		$("#dash_payment_in").html(formatRupiah(finance.pembayaran_masuk));
		$("#detail_tagihan_belum_bayar").html(formatNumber(finance.tagihan_belum_bayar));
		$("#detail_cashout_total").html(formatRupiah(finance.cashout_total));

		const overdueCount = Number(finance.tagihan_lewat_tempo || 0);
		$("#finance-overdue-pill")
			.toggleClass('ok', overdueCount === 0)
			.find('svg, i').attr('data-feather', overdueCount === 0 ? 'check-circle' : 'alert-circle');
		if (window.feather) feather.replace({ width: 14, height: 14 });

		$("#dash_prod_progress").html(formatPercent(production.progres_rata_rata));
		$("#dash_prod_active").html(formatNumber(production.pembangunan_berjalan));

		$("#dash_target_percent").html(formatPercent(target.persen_akad));
		$("#dash_target_realization").html(formatNumber(target.realisasi_akad));
		$("#dash_target_count").html(formatNumber(target.target_kavling));
		$("#dash_target_year").html(target.tahun || thn);
		$("#target-progress-bar").css('width', Math.min(100, Number(target.persen_akad || 0)) + '%');

		renderDashboardAlerts(r.alerts || []);
	}

	function renderDashboardAlerts(alerts) {
		if (!alerts.length) {
			$("#dashboard-alerts").html(`
				<div class="dashboard-alert-item">
					<div class="dashboard-alert-head">
						<span>Belum ada alert</span>
						<span class="dashboard-alert-value">0</span>
					</div>
					<div class="dashboard-alert-desc">Tidak ada data yang perlu tindakan cepat</div>
				</div>
			`);
			return;
		}

		let html = '';
		$.each(alerts, function(i, item) {
			const type = ['danger', 'warning', 'info'].includes(item.type) ? item.type : 'info';
			html += `
				<div class="dashboard-alert-item dashboard-alert-${type}">
					<div class="dashboard-alert-head">
						<span>${escapeHtml(item.label)}</span>
						<span class="dashboard-alert-value">${formatNumber(item.value)}</span>
					</div>
					<div class="dashboard-alert-desc">${escapeHtml(item.description)}</div>
				</div>
			`;
		});
		$("#dashboard-alerts").html(html);
	}

	let sdate = getFirstDate(0),
		edate = getLastDate(),
		thn = "<?=date("Y")?>";
	let aktStart = 0,
		aktIsLoading = false;

	let bookingKavlingByMonth = Array.from({length: 12}, () => []);
	let akadKavlingByMonth = Array.from({length: 12}, () => []);

	function showChartKavlingModal(seriesLabel, monthLabel, list) {
		$("#modal-chart-kavling-title").text(seriesLabel + ' — ' + monthLabel);
		$("#modal-chart-kavling-list").html(
			list.length
				? list.map(k => `<li>${escapeHtml(k)}</li>`).join('')
				: '<li class="text-muted">Tidak ada kavling</li>'
		);
		$("#modal-chart-kavling").modal('show');
	}

	if (window.SIGAPP && window.SIGAPP.activeProyekName) {
		$("#dashboard-active-proyek").text("Proyek: " + window.SIGAPP.activeProyekName);
	}

	$(document).ready(function() {
		if (activeProyekId()) {
			load_dashboard(true, true, true);
		}
	});


	$("#filter-statistik-pills .btn").click(function() {
		$("#filter-statistik-pills .btn").removeClass('active')
		$(this).addClass('active')
	})

	$("#dashboard-view-all-activity").click(function() {
		$("#header-notif").dropdown('toggle');
	})

	$("#filter-bulan").click(function() {
		sdate = getFirstDate(0)
		edate = getLastDate()

		load_dashboard(true, false, false)
	})
	$("#filter-6bulan").click(function() {
		$("#chart-judul").html("Trend Sales Tahun <?=date("Y")?>")
		thn = "<?=date("Y")?>"

		edate = $("#filter-6bulan").attr('data-val') + "-12-31"
		sdate = $("#filter-6bulan").attr('data-val') + "-01-01"

		load_dashboard(true, false, true)
	})

	$("#filter-1tahun").click(function() {
		$("#chart-judul").html("Trend Sales Tahun <?= date("Y", strtotime("-1 year")) ?>")

		thn = "<?= date("Y", strtotime("-1 year")) ?>"

		edate = $("#filter-1tahun").attr('data-val') + "-12-31"
		sdate = $("#filter-1tahun").attr('data-val') + "-01-01"

		load_dashboard(true, false, true)
	})
	$("#filter-chart-tahun-sekarang").click(function() {
		$("#chart-judul").html("Trend Sales Tahun <?=date("Y")?>")

		thn = "<?=date("Y")?>"

		load_dashboard(false, false, true)
	})
	$("#filter-chart-tahun-lalu").click(function() {
		$("#chart-judul").html("Trend Sales Tahun <?= date("Y", strtotime("-1 year")) ?>")

		thn = "<?= date("Y", strtotime("-1 year")) ?>"

		load_dashboard(false, false, true)
	})

	function load_dashboard(statistik = false, aktivitas = false, chart = false) {
		if (!activeProyekId()) {
			return toastr['error']('Belum ada proyek aktif. Pilih proyek dari navbar.', 'Terjadi Kesalahan!', {
				timeOut: 3000,
				closeButton: true,
				tapToDismiss: true,
				progressBar: true,
				positionClass: 'toast-bottom-right',
			});
		}

		$.ajax({
			type: "post",
			url: base_url + "get-dashboard",
			data: {
				[csrfName]: csrfHash,
				id_proyek: activeProyekId(),
				statistik: statistik,
				aktivitas: aktivitas,
				chart: chart,
				sdate: sdate,
				edate: edate,
				tahun: thn
			},
			dataType: "json",
			beforeSend: function() {
				if (statistik) {
					$("#st_booking").html("-")
					$("#st_batal").html("-")
					$("#st_booking_akad").html("-")
					$("#st_booking_batal").html("-")
					$("#st_booking_aktif").html("-")
					$("#st_sp3k").html("-")

					$("#st_turun_pembangunan").html("-")
					$("#st_pembangunan").html("-")
					$("#st_100persen").html("-")
					$("#st_telat").html("-")
					$("#dash_total_kavling").html("-")
					$("#dash_kavling_available").html("-")
					$("#dash_kavling_lunas").html("-")
					$("#dash_sales_rate").html("0%")
					$("#dash_finance_unpaid").html("Rp 0")
					$("#dash_finance_overdue").html("0")
					$("#dash_payment_in").html("Rp 0")
					$("#detail_tagihan_belum_bayar").html("-")
					$("#detail_cashout_total").html("Rp 0")
					$("#dash_prod_progress").html("0")
					$("#dash_prod_active").html("0")
					$("#dash_target_percent").html("0")
					$("#dash_target_realization").html("0")
					$("#dash_target_count").html("0")
					$("#dash_target_year").html(thn)
				}

				if (aktivitas)
					$("#aktivitas-here").html("<div class='activity-empty'>Memuat data</div>")
			},
			success: function(r) {
				if (statistik) {
					$("#st_booking").html(r.booking)
					$("#st_batal").html(r.batal)
					$("#st_sp3k").html(r.sp3k)

					// $("#st_turun_pembangunan").html(r.perintah_bangun)
					$("#st_pembangunan").html(r.pembangunan)
					$("#st_100persen").html(r.pembangunan_selesai)
					$("#st_telat").html(r.pembangunan_telat)
					updateDashboardCommandCenter(r)
				}

				if (aktivitas) {
					let ac = r.aktivitas || []
					let aktivitas = ""
					$.each(ac, function(i, v) {
						aktivitas += renderActivityItem(v)
					});
					if (!aktivitas) {
						aktivitas = "<div class='activity-empty'>Belum ada aktivitas pada proyek ini</div>"
					}
					$("#aktivitas-here").html(aktivitas)
					aktStart = ac.length
				}

				if (chart) {
					// Update the chart data
					let booking = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
					let akad = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
					bookingKavlingByMonth = Array.from({length: 12}, () => []);
					akadKavlingByMonth = Array.from({length: 12}, () => []);
					$.each(r.cbooking || [], function(i, v) {
						booking[parseInt(v.bulan) - 1] = v.jumlah;
						bookingKavlingByMonth[parseInt(v.bulan) - 1] = v.kavling || [];
					});
					$.each(r.cakad || [], function(i, v) {
						akad[parseInt(v.bulan) - 1] = v.jumlah;
						akadKavlingByMonth[parseInt(v.bulan) - 1] = v.kavling || [];
					});
					myLineChart.data.datasets[0].data = booking;
					myLineChart.data.datasets[1].data = akad;

					// Update the chart
					myLineChart.update();
				}

			},
			error: function() {
				return toastr['error']('Galat mengambil data dari server.', 'Terjadi Kesalahan!', {
					timeOut: 3000,
					closeButton: true,
					tapToDismiss: true,
					progressBar: true,
					positionClass: 'toast-bottom-right',
				})
			}
		});
	}

	function load_aktivitas() {
		aktIsLoading = true;
		$.ajax({
			url: base_url + 'loadaktivitas',
			method: 'GET',
			data: {
				[csrfName]: csrfHash,
				offset: aktStart,
				id_proyek: activeProyekId()
			},
			success: function(r) {
				let ac = r.aktivitas || []
				let aktivitas = ""
				$.each(ac, function(i, v) {
					aktivitas += renderActivityItem(v)
				});

				$("#aktivitas-here").append(aktivitas)

				aktStart += ac.length;
				aktIsLoading = false;

			},
			error: function() {
				aktIsLoading = false;
				console.log('Error loading data');
			}
		});
	}

	// Deteksi scroll pada div
	$('#aktivitas-body').scroll(function() {
		if ($(this).scrollTop() + $(this).innerHeight() >= $(this)[0].scrollHeight && !aktIsLoading) {
			load_aktivitas();
		}
	});
	// Sample data for the line chart

	var data = {
		labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'],
		datasets: [{
			label: 'Booking',
			backgroundColor: '#fca103', // fill color
			borderColor: '#fca103', // line color
			data: [],
			fill: false // no fill beneath the line
			},
			{
				label: 'Akad',
				backgroundColor: '#03fcb6', // fill color
				borderColor: '#03fcb6', // line color
				data: [],
				fill: false // no fill beneath the line
			},
		]
	};

	// Configuration options
	var options = {
		responsive: true,
		maintainAspectRatio: false,
		onClick: function(evt, elements) {
			if (!elements.length) return;
			const el = elements[0];
			const label = el.datasetIndex === 0 ? 'Booking' : 'Akad';
			const list = (el.datasetIndex === 0 ? bookingKavlingByMonth : akadKavlingByMonth)[el.index] || [];
			showChartKavlingModal(label, data.labels[el.index], list);
		},
		plugins: {
			tooltip: {
				callbacks: {
					afterBody: function(items) {
						if (!items.length) return [];
						const it = items[0];
						const list = (it.datasetIndex === 0 ? bookingKavlingByMonth : akadKavlingByMonth)[it.dataIndex] || [];
						if (!list.length) return [];
						const preview = list.slice(0, 8);
						const lines = preview.map(k => '• ' + k);
						if (list.length > preview.length) lines.push(`+${list.length - preview.length} lainnya`);
						return lines;
					}
				}
			}
		},
		scales: {
			x: {
				type: 'category', // category scale for X-axis
				title: {
					display: true,
					text: 'Bulan'
				}
			},
			y: {
				title: {
					display: true,
					text: 'Penjualan'
				}
			}
		}
	};

	// Get the canvas element
	var ctx = document.getElementById('myLineChart').getContext('2d');

	// Create the line chart
	var myLineChart = new Chart(ctx, {
		type: 'line',
		data: data,
		options: options
	});
</script>
