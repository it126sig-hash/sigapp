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
</style>
<!-- BEGIN: Content-->
<div class="app-content content ">
	<div class="content-overlay"></div>
	<div class="header-navbar-shadow"></div>
	<div class="content-wrapper">
		<div class="content-header row"></div>
		<div class="content-body">
			<!-- ###########################first row ############################-->
			<div class="row match-height">
				<!-- Medal Card -->
				<div class="col-md-4">
					<div class="card card-congratulation-medal">
						<div class="card-body">
							<h4 class="card-title">Pilih Proyek</h4>
							<p class="card-text font-small-3">
								<select id="id_proyek" name="id_proyek" class="select2 form-control"></select>
							</p>
							<button id="btn-filter_data" type="button" class="btn btn-primary">Filter Data</button>
						</div>
					</div>
				</div>
				<!--/ Medal Card -->

				<div class="col-md-8">
					<div class="row">
<!-- Statistics Card -->
<div class="col-md-12">
	<div class="card card-statistics">
		<div class="card-header">
			<h4 class="card-title">Statistik Sales</h4>
			<div class="d-flex align-items-center">
				<p class="card-text font-small-2 mr-25 mb-0" id="filter-statistik">Per <?= date("F") ?></p>
				<div class="dropdown chart-dropdown">
					<i data-feather="more-vertical" class="font-medium-3 cursor-pointer" data-toggle="dropdown"></i>
					<div class="dropdown-menu dropdown-menu-right">
						<a id="filter-bulan" class="dropdown-item" href="javascript:void(0);">Per <?= date("F") ?></a>
						<a id="filter-6bulan" class="dropdown-item" data-val="<?= date("Y") ?>" href="javascript:void(0);">Per <?= date("Y") ?></a>
						<a id="filter-1tahun" class="dropdown-item" data-val="<?= date("Y", strtotime("-1 year")) ?>" href="javascript:void(0);">Per <?= date("Y", strtotime("-1 year")) ?></a>
					</div>
				</div>
			</div>
		</div>
		<div class="card-body statistics-body">
			<div class="row">
				<div class="col-md-3">
					<div class="media">
						<div class="avatar booking mr-2">
							<div class="avatar-content">
								<i data-feather="book-open" class="avatar-icon"></i>
							</div>
						</div>
						<div class="media-body my-auto">
							<h4 class="font-weight-bolder mb-0" id="st_booking">-</h4>
							<p class="card-text font-small-3 mb-0">Booking</p>
						</div>
					</div>
				</div>
				<div class="col-md-3">
					<div class="media">
						<div class="avatar akad mr-2">
							<div class="avatar-content">
								<i data-feather="dollar-sign" class="avatar-icon"></i>
							</div>
						</div>
						<div class="media-body my-auto">
							<h4 class="font-weight-bolder mb-0" id="st_akad">-</h4>
							<p class="card-text font-small-3 mb-0">Akad</p>
						</div>
					</div>
				</div>
				<div class="col-md-3">
					<div class="media">
						<div class="avatar batal mr-2">
							<div class="avatar-content">
								<i data-feather="x-circle" class="avatar-icon"></i>
							</div>
						</div>
						<div class="media-body my-auto">
							<h4 class="font-weight-bolder mb-0" id="st_batal">-</h4>
							<p class="card-text font-small-3 mb-0">Batal</p>
						</div>
					</div>
				</div>
				<div class="col-md-3">
					<div class="media">
						<div class="avatar sp3k mr-2">
							<div class="avatar-content">
								<i data-feather="calendar" class="avatar-icon"></i>
							</div>
						</div>
						<div class="media-body my-auto">
							<h4 class="font-weight-bolder mb-0" id="st_sp3k">-</h4>
							<p class="card-text font-small-3 mb-0">Terbit SP3K</p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	</div>
	<!--/ Statistics Card -->

	<!-- Statistics Card -->
	<div class="col-md-12">
		<div class="card card-statistics">
			<div class="card-header">
				<h4 class="card-title">Statistik Pembangunan</h4>
				<div class="d-flex align-items-center">
					<p class="card-text font-small-2 mr-25 mb-0" id="filter-statistik-pembangunan">Per <?= date("F") ?></p>
					<div class="dropdown chart-dropdown">
						<i data-feather="more-vertical" class="font-medium-3 cursor-pointer" data-toggle="dropdown"></i>
						<div class="dropdown-menu dropdown-menu-right">
							<a id="filter-pembangunan-bulan" class="dropdown-item" href="javascript:void(0);">Per <?= date("F") ?></a>
							<a id="filter-pembangunan-6bulan" class="dropdown-item" data-val="<?= date("Y") ?>" href="javascript:void(0);">Per <?= date("Y") ?></a>
							<a id="filter-pembangunan-1tahun" class="dropdown-item" data-val="<?= date("Y", strtotime("-1 year")) ?>" href="javascript:void(0);">Per <?= date("Y", strtotime("-1 year")) ?></a>
						</div>
					</div>
				</div>
			</div>
			<div class="card-body statistics-pembangunan-body">
				<div class="row">
					<!-- <div class="col-md-3">
						<div class="media">
							<div class="avatar primary mr-2">
								<div class="avatar-content">
									<i data-feather="target" class="avatar-icon"></i>
								</div>
							</div><br>
							<div class="media-body my-auto">
								<h4 class="font-weight-bolder mb-0" id="st_turun_pembangunan">-</h4>
								<p class="card-text font-small-3 mb-0">Turun Pembangunan</p>
							</div>
							
						</div>
					</div> -->
					<div class="col-md-4">
						<div class="media">
							<div class="avatar mr-2">
								<div class="avatar-content">
									<i data-feather="alert-triangle" class="avatar-icon"></i>
								</div>
							</div>
							<div class="media-body my-auto">
								<h4 class="font-weight-bolder mb-0" id="st_pembangunan">-</h4>
								<p class="card-text font-small-3 mb-0">Pembangunan</p>
							</div>
						</div>
					</div>
					<div class="col-md-4">
						<div class="media">
							<div class="avatar akad mr-2">
								<div class="avatar-content">
									<i data-feather="check-square" class="avatar-icon"></i>
								</div>
							</div>
							<div class="media-body  my-auto">
								<h4 class="font-weight-bolder mb-0" id="st_100persen">-</h4>
								<p class="card-text font-small-3 mb-0">Bangunan 100%</p>
							</div>
						</div>
					</div>
					<div class="col-md-4">
						<div class="media">
							<div class="avatar batal mr-2">
								<div class="avatar-content">
									<i data-feather="clock" class="avatar-icon"></i>
								</div>
							</div>
							<div class="media-body my-auto">
								<h4 class="font-weight-bolder mb-0" id="st_telat">-</h4>
								<p class="card-text font-small-3 mb-0">Telat Bangun</p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!--/ Statistics Card -->
		</div>
	</div>



	</div>

	<div class="row match-height">
		<!-- Sales Stats Chart Card starts -->
		<div class="col-lg-8  col-12">
			<div class="card" style="height:500px;max-height: 500px">
				<div class="card-header d-flex justify-content-between align-items-start pb-1">
					<div>
						<h4 id ="chart-judul" class="card-title mb-25">Sales Tahun <?= date('Y') ?></h4>
						<!-- <p class="card-text">Selama 1 1tahun</p> -->
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
					<!-- <div class="d-inline-block mr-1">
						<div class="d-flex align-items-center">
							<i data-feather="circle" class="font-small-3 text-primary mr-50"></i>
							<h6 class="mb-0">Booking</h6>
						</div>
					</div>
					<div class="d-inline-block">
						<div class="d-flex align-items-center">
							<i data-feather="circle" class="font-small-3 text-info mr-50"></i>
							<h6 class="mb-0">Akad</h6>
						</div>
					</div> -->
					<div id="sales-visit-chart" class="mt-50" style="height:100%">
						<canvas id="myLineChart"></canvas>
					</div>
				</div>
			</div>
		</div>
		<!-- Sales Stats Chart Card ends -->
		<!-- Timeline Card -->
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
							<h6>Pilih proyek dan klik filterdata untuk menampilkan aktivitas terbaru</h6>
						</li>
					</ul>
				</div>
			</div>
		</div>
		<!--/ Timeline Card -->

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
	let sdate = getFirstDate(0),
		edate = getLastDate()
		thn = "<?=date("Y")?>"


	$("#id_proyek").select2({
		placeholder: "Pilih Proyek",
		allowClear: true,
		ajax: {
			url: base_url + "/proyek/getAll",
			dataType: 'json',
			delay: 250,
			method: 'post',
			data: function(params) {
				return {
					[csrfName]: csrfHash,
					search: params.term
				};
			},
			processResults: function(r) {
				csrfHash = r.token

				let results = [];
				$.each(r.data, function(index, item) {
					results.push({
						id: item['id_proyek'],
						text: item[1] + ' (' + item[2] + ')'
					});
				});

				return {
					results: results
				};
			},
			cache: true
		},
	})

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

		$("#chart-judul").html("Sales Tahun <?=date("Y")?>")
		// $("#filter-chart-tahun-sekarang").click()
		thn = "<?=date("Y")?>"

		edate = $("#filter-6bulan").attr('data-val') + "-12-31"
		sdate = $("#filter-6bulan").attr('data-val') + "-01-01"

		load_dashboard(true, false, true)
	})

	$("#filter-1tahun, #filter-pembangunan-1tahun").click(function() {
		$("#filter-statistik").html($(this).html())
		$("#filter-statistik-pembangunan").html($(this).html())

		$("#chart-judul").html("Sales Tahun <?= date("Y", strtotime("-1 year")) ?>")

		// $("#filter-chart-tahun-lalu").click()

		thn = "<?= date("Y", strtotime("-1 year")) ?>"

		edate = $("#filter-1tahun").attr('data-val') + "-12-31"
		sdate = $("#filter-1tahun").attr('data-val') + "-01-01"

		load_dashboard(true, false, true)
	})
	$("#filter-chart-tahun-sekarang").click(function() {
		$("#chart-judul").html("Sales Tahun <?=date("Y")?>")

		thn = "<?=date("Y")?>"

		load_dashboard(false, false, true)
	})
	$("#filter-chart-tahun-lalu").click(function() {
		$("#chart-judul").html("Sales Tahun <?= date("Y", strtotime("-1 year")) ?>")

		thn = "<?= date("Y", strtotime("-1 year")) ?>"

		load_dashboard(false, false, true)
	})

	function load_dashboard(statistik = false, aktivitas = false, chart = false) {
		if (!$("#id_proyek").val()) {
			return toastr['error']('Pilih proyek terlebih dahulu.', 'Terjadi Kesalahan!', {
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
				id_proyek: $("#id_proyek").val(),
				statistik: statistik,
				aktivitas: aktivitas,
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
				}

				if (aktivitas) {
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
							</li>
						`
					});
					$("#aktivitas-here").html(aktivitas)
				}
				
				if (chart) {
					// Update the chart data
					let booking = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
					let akad = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
					$.each(r.cbooking, function(i, v) {
						booking[parseInt(v.bulan) - 1] = v.jumlah;
					});
					$.each(r.cakad, function(i, v) {
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
				id_proyek: $("#id_proyek").val()
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


	//bug select2
	$(".select2-selection__arrow").removeClass("select2-selection__arrow")
</script>
