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
	.booking {
		background-color: #ede618;
	}

	.akad {
		background-color: #22ed18;
	}

	.batal {
		background-color: #ed4218;
	}

	.sp3k {
		background-color: #18cded;
	}

	.dashboard-kpi-grid {
		display: grid;
		grid-template-columns: repeat(4, minmax(0, 1fr));
		gap: 1rem;
		height: 100%;
		flex: 1 1 auto;
		align-items: stretch;
	}

	.dashboard-filter-card,
	.dashboard-kpi-card {
		border: 1px solid #ebe9f1;
		border-radius: 8px;
		background: #fff;
		height: 100%;
	}

	.dashboard-filter-card {
		overflow: visible;
		position: relative;
		z-index: 30;
		flex: 1 1 auto;
		margin-bottom: 0;
	}

	.dashboard-kpi-card {
		padding: 1rem;
		min-height: 150px;
		display: flex;
		flex-direction: column;
		justify-content: space-between;
	}

	.dashboard-kpi-card .kpi-label {
		color: #6e6b7b;
		font-size: .86rem;
		margin-bottom: .35rem;
	}

	.dashboard-kpi-card .kpi-value {
		color: #2f2b3d;
		font-size: 1.55rem;
		font-weight: 700;
		line-height: 1.1;
		word-break: break-word;
	}

	.dashboard-kpi-card .kpi-meta {
		color: #6e6b7b;
		font-size: .82rem;
		margin-top: .45rem;
	}

	.dashboard-mini-list {
		display: grid;
		grid-template-columns: repeat(2, minmax(0, 1fr));
		gap: .75rem;
	}

	.dashboard-mini-item {
		border: 1px solid #ebe9f1;
		border-radius: 8px;
		padding: .85rem;
		background: #fff;
	}

	.dashboard-mini-label,
	.dashboard-alert-desc {
		color: #6e6b7b;
		font-size: .82rem;
	}

	.dashboard-mini-value {
		color: #2f2b3d;
		font-size: 1.15rem;
		font-weight: 700;
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

	.dashboard-filter-card .select2-container {
		width: 100% !important;
	}

	.dashboard-filter-card .select2-container--default .select2-selection--single {
		height: calc(1.5em + .75rem + 2px);
		border: 1px solid #d8d6de;
		border-radius: .357rem;
		display: flex;
		align-items: center;
	}

	.dashboard-filter-card .select2-container--default .select2-selection--single .select2-selection__rendered {
		line-height: normal;
		padding-left: .75rem;
		padding-right: 2.25rem;
		width: 100%;
	}

	.dashboard-filter-card .select2-container--default .select2-selection--single .select2-selection__arrow {
		height: 100%;
		right: .5rem;
		top: 0;
		width: 1.25rem;
		pointer-events: none;
	}

	.dashboard-filter-card .select2-container--default .select2-selection--single .select2-selection__arrow b {
		margin-left: -4px;
		margin-top: -2px;
	}

	.dashboard-filter-card .select2-container--default .select2-selection--single .select2-selection__clear {
		line-height: 1;
		margin-right: 1.75rem;
	}

	.dashboard-filter-card,
	.dashboard-filter-card .dropdown,
	.dashboard-filter-card .dropdown-menu,
	.card-header .chart-dropdown,
	.card-header .chart-dropdown .dropdown-menu {
		overflow: visible;
	}

	.content-body > .row,
	.content-body > .row > [class*="col-"] {
		overflow: visible;
	}

	.dashboard-filter-card .chart-dropdown {
		position: relative;
		z-index: 2100;
	}

	.dashboard-filter-card .chart-dropdown.show {
		z-index: 2300;
	}

	.dashboard-filter-card .dropdown-menu,
	.card-header .chart-dropdown .dropdown-menu {
		z-index: 2050;
	}

	.dashboard-filter-card .chart-dropdown.show > .dropdown-menu,
	.dashboard-filter-card .dropdown-menu.show {
		display: block !important;
		min-width: 100%;
		z-index: 2300 !important;
	}

	@media (max-width: 1199.98px) {
		.dashboard-kpi-grid {
			grid-template-columns: repeat(2, minmax(0, 1fr));
		}
	}

	@media (max-width: 767.98px) {
		.dashboard-kpi-grid,
		.dashboard-mini-list {
			grid-template-columns: 1fr;
		}
	}
</style>
<!-- BEGIN: Content-->
<div class="app-content content ">
	<div class="content-overlay"></div>
	<div class="header-navbar-shadow"></div>
	<div class="content-wrapper">
		<div class="content-header row"></div>
		<div class="content-body">
			<div class="row match-height pb-1">
				<div class="col-lg-3 col-md-5 col-12">
					<div class="card dashboard-filter-card">
						<div class="card-body">
							<h4 class="card-title mb-1">Dashboard Proyek</h4>
							<p class="card-text font-small-3 text-muted mb-1" id="dashboard-active-proyek"></p>
							<button id="btn-filter_data" type="button" class="btn btn-primary btn-block">Tampilkan Data</button>
							<p class="card-text font-small-2 text-muted mt-1 mb-0" id="filter-statistik">Per <?= date("F") ?></p>
							<div class="dropdown chart-dropdown mt-50">
								<button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown" data-boundary="viewport">
									Ubah Periode
								</button>
								<div class="dropdown-menu">
									<a id="filter-bulan" class="dropdown-item" href="javascript:void(0);">Per <?= date("F") ?></a>
									<a id="filter-6bulan" class="dropdown-item" data-val="<?= date("Y") ?>" href="javascript:void(0);">Per <?= date("Y") ?></a>
									<a id="filter-1tahun" class="dropdown-item" data-val="<?= date("Y", strtotime("-1 year")) ?>" href="javascript:void(0);">Per <?= date("Y", strtotime("-1 year")) ?></a>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="col-lg-9 col-md-7 col-12">
					<div class="dashboard-kpi-grid">
						<div class="dashboard-kpi-card">
							<div>
								<div class="kpi-label">Kavling</div>
								<div class="kpi-value" id="dash_total_kavling">-</div>
								<div class="kpi-meta"><span id="dash_kavling_available">-</span> tersedia</div>
							</div>
							<div class="kpi-meta"><span id="dash_kavling_lunas">-</span> lunas</div>
						</div>
						<div class="dashboard-kpi-card">
							<div>
								<div class="kpi-label">Sales Periode</div>
								<div class="kpi-value"><span id="st_booking">-</span> booking</div>
								<div class="kpi-meta"><span id="st_akad">-</span> akad, <span id="st_sp3k">-</span> SP3K</div>
							</div>
							<div class="kpi-meta">Konversi: <span id="dash_sales_rate">0%</span></div>
						</div>
						<div class="dashboard-kpi-card">
							<div>
								<div class="kpi-label">Keuangan</div>
								<div class="kpi-value" id="dash_finance_unpaid">Rp 0</div>
								<div class="kpi-meta"><span id="dash_finance_overdue">0</span> lewat jatuh tempo</div>
							</div>
							<div class="kpi-meta">Masuk periode: <span id="dash_payment_in">Rp 0</span></div>
						</div>
						<div class="dashboard-kpi-card">
							<div>
								<div class="kpi-label">Produksi</div>
								<div class="kpi-value"><span id="dash_prod_progress">0</span>%</div>
								<div class="kpi-meta"><span id="dash_prod_active">0</span> berjalan</div>
							</div>
							<div class="kpi-meta"><span id="st_telat">0</span> telat bangun</div>
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
								<div class="dashboard-alert-desc">Klik Tampilkan Data untuk melihat alert</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="row match-height">
				<div class="col-lg-8 col-12">
					<div class="card">
						<div class="card-header">
							<h4 class="card-title">Rincian Operasional</h4>
						</div>
						<div class="card-body">
							<div class="dashboard-mini-list">
								<div class="dashboard-mini-item">
									<div class="dashboard-mini-label">Batal periode</div>
									<div class="dashboard-mini-value" id="st_batal">-</div>
								</div>
								<div class="dashboard-mini-item">
									<div class="dashboard-mini-label">Pembangunan periode</div>
									<div class="dashboard-mini-value" id="st_pembangunan">-</div>
								</div>
								<div class="dashboard-mini-item">
									<div class="dashboard-mini-label">Bangunan 100%</div>
									<div class="dashboard-mini-value" id="st_100persen">-</div>
								</div>
								<div class="dashboard-mini-item">
									<div class="dashboard-mini-label">Tagihan belum dibayar</div>
									<div class="dashboard-mini-value" id="detail_tagihan_belum_bayar">-</div>
								</div>
								<div class="dashboard-mini-item">
									<div class="dashboard-mini-label">Cashout periode</div>
									<div class="dashboard-mini-value" id="detail_cashout_total">Rp 0</div>
								</div>
								<div class="dashboard-mini-item">
									<div class="dashboard-mini-label">Target <span id="dash_target_year"><?= date('Y') ?></span></div>
									<div class="dashboard-mini-value"><span id="dash_target_percent">0</span>%</div>
									<div class="dashboard-mini-label"><span id="dash_target_realization">0</span> / <span id="dash_target_count">0</span> kavling</div>
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
						<div class="card-body" style="overflow-y:scroll" id="aktivitas-body">
							<ul class="timeline ml-50" id="aktivitas-here">
								<li class="timeline-item">
									<h6>Pilih proyek dan klik Tampilkan Data untuk melihat aktivitas terbaru</h6>
								</li>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	</div>
</div>
<!-- END: Content-->

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

		$("#dash_finance_unpaid").html(formatRupiah(finance.nominal_belum_bayar));
		$("#dash_finance_overdue").html(formatNumber(finance.tagihan_lewat_tempo));
		$("#dash_payment_in").html(formatRupiah(finance.pembayaran_masuk));
		$("#detail_tagihan_belum_bayar").html(formatNumber(finance.tagihan_belum_bayar) + ' item');
		$("#detail_cashout_total").html(formatRupiah(finance.cashout_total));

		$("#dash_prod_progress").html(formatPercent(production.progres_rata_rata));
		$("#dash_prod_active").html(formatNumber(production.pembangunan_berjalan));

		$("#dash_target_percent").html(formatPercent(target.persen_akad));
		$("#dash_target_realization").html(formatNumber(target.realisasi_akad));
		$("#dash_target_count").html(formatNumber(target.target_kavling));
		$("#dash_target_year").html(target.tahun || thn);

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
	let start = 0,
		offset = 10,
		isLoading = false;

	if (window.SIGAPP && window.SIGAPP.activeProyekName) {
		$("#dashboard-active-proyek").text("Proyek: " + window.SIGAPP.activeProyekName);
	}

	$("#btn-filter_data").click(function() {
		// $("#filter-statistik").html($("#filter-bulan").html())
		load_dashboard(true, true, true)
	})

	
	$("#filter-bulan, #filter-pembangunan-bulan").click(function() {
		$("#filter-statistik").html($(this).html())
		$("#filter-statistik-pembangunan").html($(this).html())

		

		sdate = getFirstDate(0)
		edate = getLastDate()

		load_dashboard(true, false, false)
	})
	$("#filter-6bulan, #filter-pembangunan-6bulan").click(function() {
		$("#filter-statistik").html($(this).html())
		$("#filter-statistik-pembangunan").html($(this).html())

		$("#chart-judul").html("Trend Sales Tahun <?=date("Y")?>")
		// $("#filter-chart-tahun-sekarang").click()
		thn = "<?=date("Y")?>"

		edate = $("#filter-6bulan").attr('data-val') + "-12-31"
		sdate = $("#filter-6bulan").attr('data-val') + "-01-01"

		load_dashboard(true, false, true)
	})

	$("#filter-1tahun, #filter-pembangunan-1tahun").click(function() {
		$("#filter-statistik").html($(this).html())
		$("#filter-statistik-pembangunan").html($(this).html())

		$("#chart-judul").html("Trend Sales Tahun <?= date("Y", strtotime("-1 year")) ?>")

		// $("#filter-chart-tahun-lalu").click()

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
					$("#st_akad").html("-")
					$("#st_batal").html("-")
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
					$("#aktivitas-here").html("<li class='timeline-item'><h6>Memuat data</h6></li>")
			},
			success: function(r) {
				if (statistik) {
					$("#st_booking").html(r.booking)
					$("#st_akad").html(r.akad)
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
						aktivitas += `
						<li class="timeline-item">
							<span class="timeline-point timeline-point-indicator"></span>
							<div class="timeline-event">
								<h6>` + v.nama_jalan + `, No. ` + v.no_kavling + `</h6>
								<p><b>` + v.username + `</b>: ` + v.notif + `</p>
								<div class="media align-items-center">
									<h6 class="media-body mb-0">` + format_datetime(v.created_at) + `</h6>
								</div>
							</div>
							</li>
						`
					});
					if (!aktivitas) {
						aktivitas = "<li class='timeline-item'><h6>Belum ada aktivitas pada proyek ini</h6></li>"
					}
					$("#aktivitas-here").html(aktivitas)
					start = ac.length
				}
				
				if (chart) {
					// Update the chart data
					let booking = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
					let akad = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
					$.each(r.cbooking || [], function(i, v) {
						booking[parseInt(v.bulan) - 1] = v.jumlah;
					});
					$.each(r.cakad || [], function(i, v) {
						akad[parseInt(v.bulan) - 1] = v.jumlah;
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
		isLoading = true;
		$.ajax({
			url: base_url + '/loadaktivitas', // Gantilah dengan path menuju file yang menyediakan data
			method: 'GET',
			data: {
				[csrfName]: csrfHash,
				offset: start,
				id_proyek: activeProyekId()
			},
			success: function(r) {
				let ac = r.aktivitas
				let aktivitas = ""
				$.each(ac, function(i, v) {
					aktivitas += `
						<li class="timeline-item">
							<span class="timeline-point timeline-point-indicator"></span>
							<div class="timeline-event">
								<h6>` + v.nama_jalan + `, No. ` + v.no_kavling + `</h6>
								<p><b>` + v.username + `</b>: ` + v.notif + `</p>
								<div class="media align-items-center">
									<h6 class="media-body mb-0">` + format_datetime(v.created_at) + `</h6>
								</div>
							</div>
							</li> `
				});

				$("#aktivitas-here").append(aktivitas)

				if (r.aktivitas.length > 0)
					start += offset;
				isLoading = false;

			},
			error: function() {
				isLoading = false;
				console.log('Error loading data');
			}
		});
	}

	// Deteksi scroll pada div
	$('#aktivitas-body').scroll(function() {
		if ($(this).scrollTop() + $(this).innerHeight() >= $(this)[0].scrollHeight && !isLoading) {
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

	if (activeProyekId()) {
		load_dashboard(true, true, true);
	}

</script>
