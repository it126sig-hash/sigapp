<style>
	#modal-cashout-subkon .select2-selection__choice {
		display: block;
		margin: 2px 0;
	}

	#modal-cashout-subkon .select2-container--default .select2-selection--multiple {
		height: auto;
	}

	/* SIGAPP UI Acuan - Modal Cashout Subkon (mengikuti modal pembayaran produksi) */
	#modal-cashout-subkon .modal-dialog {
		max-width: min(1440px, calc(100vw - 32px));
		margin: 1rem auto;
	}

	#modal-cashout-subkon .modal-content {
		border: 0;
		border-radius: 10px;
		box-shadow: 0 18px 45px rgba(15, 23, 42, .18);
		overflow: hidden;
	}

	#modal-cashout-subkon .modal-header {
		align-items: center;
		background: #fff;
		border-bottom: 1px solid #e5e7eb;
		margin-bottom: 0 !important;
		padding: 1rem 1.25rem;
	}

	#modal-cashout-subkon .modal-title {
		color: #111827;
		font-size: 1.05rem;
		font-weight: 700;
	}

	#modal-cashout-subkon .cos-sk-body {
		background: #f3f5f7 !important;
		max-height: calc(100vh - 7rem);
		overflow-y: auto;
		padding: 1rem;
	}

	#modal-cashout-subkon .cos-sk-layout {
		display: flex;
		flex-wrap: nowrap;
		gap: 1rem;
		min-width: 0;
	}

	#modal-cashout-subkon .cos-sk-sidebar {
		align-self: flex-start;
		flex: 0 0 320px;
		max-height: calc(100vh - 8rem);
		max-width: 320px;
		overflow-y: auto;
		position: sticky;
		top: 0;
		z-index: 2;
	}

	#modal-cashout-subkon .cos-sk-content {
		flex: 1 1 auto;
		max-width: calc(100% - 336px);
		min-width: 0;
	}

	#modal-cashout-subkon .card {
		border: 1px solid #e5e7eb;
		border-radius: 8px;
		box-shadow: none;
		margin-bottom: 1rem;
		overflow: hidden;
	}

	#modal-cashout-subkon .card-body {
		padding: 1rem;
	}

	#modal-cashout-subkon .cos-sk-hero {
		border: 0;
	}

	#modal-cashout-subkon .bg-primary {
		background: linear-gradient(145deg, #2057a3 0%, #1f7a8c 100%) !important;
	}

	#modal-cashout-subkon .cos-sk-hero-label {
		font-size: 1rem;
		font-weight: 700;
		line-height: 1.35;
		margin-bottom: 0;
		overflow-wrap: anywhere;
	}

	#modal-cashout-subkon .cos-sk-meta-card {
		background: #fff;
		border: 1px solid #cfd6e3;
		border-radius: 8px;
		box-shadow: 0 8px 18px rgba(15, 23, 42, .05);
		margin-bottom: 0;
	}

	#modal-cashout-subkon .cos-sk-meta-card h6,
	#modal-cashout-subkon .cos-sk-meta-card h5 {
		color: #374151;
		line-height: 1.35;
		margin-bottom: .45rem;
	}

	#modal-cashout-subkon .cos-sk-meta-card h5:last-child,
	#modal-cashout-subkon .cos-sk-meta-card h6:last-of-type {
		margin-bottom: 0;
	}

	#modal-cashout-subkon .divider {
		margin: .65rem 0 .85rem;
	}

	#modal-cashout-subkon .divider-left {
		border-left-color: #2057a3;
		margin-bottom: .85rem;
		padding-left: .75rem;
	}

	#modal-cashout-subkon .divider .divider-text {
		color: #111827;
		font-size: .86rem;
		font-weight: 700;
	}

	#modal-cashout-subkon label,
	#modal-cashout-subkon .form-label {
		color: #6b7280;
		font-size: .78rem;
		font-weight: 700;
		letter-spacing: 0;
	}

	#modal-cashout-subkon .form-group {
		margin-bottom: .8rem;
	}

	#modal-cashout-subkon .form-control {
		background-color: #fff;
		border-color: #d8dde3;
		border-radius: 6px;
		min-height: 36px;
	}

	#modal-cashout-subkon .btn {
		border-radius: 6px;
		font-weight: 700;
		white-space: normal;
	}

	#modal-cashout-subkon .btn-primary {
		background-color: #2057a3 !important;
		border-color: #2057a3 !important;
	}

	#modal-cashout-subkon .btn-primary:hover,
	#modal-cashout-subkon .btn-primary:focus {
		background-color: #174b8f !important;
		border-color: #174b8f !important;
	}

	#modal-cashout-subkon .btn-outline-primary {
		border-color: #2057a3;
		color: #2057a3;
	}

	#modal-cashout-subkon .btn-outline-primary:hover {
		background-color: #2057a3;
		border-color: #2057a3;
	}

	#modal-cashout-subkon .cos-sk-tabs-card {
		margin-bottom: .75rem;
	}

	#modal-cashout-subkon .cos-sk-tabs-card .card-body {
		padding: .65rem 1rem;
	}

	#modal-cashout-subkon .cos-sk-tabs .nav-link {
		border-radius: 6px;
		color: #4b5563;
		font-size: .84rem;
		font-weight: 700;
		padding: .45rem .85rem;
	}

	#modal-cashout-subkon .cos-sk-tabs .nav-link.active {
		background: #2057a3;
		color: #fff;
	}

	#modal-cashout-subkon #fm-cashout-subkon-termin-table {
		margin-bottom: 0;
	}

	#modal-cashout-subkon #fm-cashout-subkon-termin-table thead th {
		background: #f8fafc;
		border-bottom: 1px solid #e5e7eb;
		color: #374151;
		font-size: .78rem;
		font-weight: 700;
		white-space: nowrap;
	}

	#modal-cashout-subkon #fm-cashout-subkon-termin-table tbody td {
		font-size: .84rem;
		vertical-align: middle;
	}

	#modal-cashout-subkon .termin-nominal {
		text-align: right;
	}

	#modal-cashout-subkon .cos-sk-timeline {
		position: relative;
		padding-left: 50px;
	}

	#modal-cashout-subkon .cos-sk-timeline .timeline-item {
		position: relative;
	}

	#modal-cashout-subkon .cos-sk-timeline .timeline-item::before {
		background: #e5e7eb;
		content: "";
		height: calc(100% - 30px);
		left: -32px;
		position: absolute;
		top: 40px;
		width: 2px;
	}

	#modal-cashout-subkon .cos-sk-timeline .timeline-item:last-child::before {
		display: none;
	}

	#modal-cashout-subkon .cos-sk-timeline .timeline-icon {
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

	#modal-cashout-subkon .cos-sk-timeline .badge-success-light {
		background-color: rgba(40, 167, 69, .1);
	}

	#modal-cashout-subkon .cos-sk-timeline .badge-primary-light {
		background-color: rgba(32, 87, 163, .12);
		color: #2057a3;
	}

	#modal-cashout-subkon .modal-footer {
		background: #fff;
		border-top: 1px solid #e5e7eb;
		padding: .85rem 1.25rem;
	}

	.dark-layout #modal-cashout-subkon .modal-header,
	.dark-layout #modal-cashout-subkon .card,
	.dark-layout #modal-cashout-subkon .cos-sk-meta-card,
	.dark-layout #modal-cashout-subkon .modal-footer {
		background: #283046 !important;
		border-color: rgba(255, 255, 255, .08) !important;
	}

	.dark-layout #modal-cashout-subkon .modal-title,
	.dark-layout #modal-cashout-subkon .divider .divider-text {
		color: #f8fafc;
	}

	.dark-layout #modal-cashout-subkon .cos-sk-body {
		background: #1f2937 !important;
	}

	@media (max-width: 1199.98px) {
		#modal-cashout-subkon .cos-sk-layout {
			flex-wrap: wrap;
		}

		#modal-cashout-subkon .cos-sk-sidebar,
		#modal-cashout-subkon .cos-sk-content {
			flex: 0 0 100%;
			max-width: 100%;
		}

		#modal-cashout-subkon .cos-sk-sidebar {
			max-height: none;
			overflow-y: visible;
			position: static;
		}
	}

	@media (max-width: 767.98px) {
		#modal-cashout-subkon .modal-dialog {
			max-width: calc(100vw - 12px);
			margin: .5rem auto;
		}

		#modal-cashout-subkon .cos-sk-body {
			max-height: calc(100vh - 5.5rem);
			padding: .75rem;
		}

		#modal-cashout-subkon .card-body {
			padding: .85rem;
		}

		#modal-cashout-subkon .cos-sk-tabs,
		#modal-cashout-subkon .cos-sk-tabs .nav-pills {
			flex-direction: row !important;
			flex-wrap: nowrap;
			overflow-x: auto;
			padding-bottom: .25rem;
		}

		#modal-cashout-subkon .cos-sk-tabs .nav-link {
			white-space: nowrap;
		}
	}
</style>

<div class="modal fade text-left" id="modal-cashout-subkon" tabindex="-1" role="dialog"
	aria-labelledby="modal-cashout-subkon-label" aria-hidden="true">
	<div class="modal-dialog modal-dialog-scrollable modal-xl" role="document">
		<form id="fm-cashout-subkon" class="add-new-record modal-content pt-0" autocomplete="off">
			<div class="modal-header">
				<h5 class="modal-title" id="modal-cashout-subkon-label">SPK Sub-Konstruksi</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body flex-grow-1 cos-sk-body">
				<div class="cos-sk-layout">
					<aside class="cos-sk-sidebar">
						<div class="card cos-sk-hero">
							<div class="card-body bg-primary text-light">
								<p class="cos-sk-hero-label mb-0">Belum ada kavling dipilih</p>
							</div>
						</div>
						<div class="card">
							<div class="card-body">
								<div class="divider divider-left">
									<div class="divider-text">Subkontraktor</div>
								</div>
								<div class="form-group">
									<label for="fm-cashout-subkon-id_subkon">Pilih Subkon</label>
									<select class="form-control select2 fm-input" id="fm-cashout-subkon-id_subkon" name="id_subkon"></select>
								</div>
								<div class="form-group">
									<label for="fm-cashout-subkon-nama_subkon">Nama Subkontraktor</label>
									<input type="text" class="form-control fm-input" id="fm-cashout-subkon-nama_subkon" name="nama_subkon">
								</div>
								<div class="form-group">
									<label for="fm-cashout-subkon-hp1_subkon">Kontak</label>
									<input type="text" class="form-control fm-input" id="fm-cashout-subkon-hp1_subkon" name="hp1_subkon">
								</div>
								<div class="form-group mb-0">
									<label for="fm-cashout-subkon-alamat_subkon">Alamat</label>
									<input type="text" class="form-control fm-input" id="fm-cashout-subkon-alamat_subkon" name="alamat_subkon">
								</div>
							</div>
						</div>
						<div class="card mb-0">
							<div class="card-body">
								<div class="divider divider-left">
									<div class="divider-text">Dokumen SPK</div>
								</div>
								<div class="dropzone dropzone-lg custom-file" id="dz-spk">
									<input type="file" class="custom-file-input dz-input fm-input"
										accept="application/pdf" name="file_surat" id="fm-cashout-subkon-file_surat">
									<div class="dz-inner">
										<div class="dz-preview" id="prev_file_surat"></div>
										<div class="dz-placeholder">
											<div class="h6 mb-1">Tarik & letakkan file ke sini</div>
											<div class="text-muted small">atau klik (PDF maks 5 MB)</div>
										</div>
									</div>
								</div>
								<a href="" id="fm-cashout-subkon-file_surat-here"
									onclick="window.open(this.href, '_blank'); return false;"
									class="btn btn-outline-primary btn-sm w-100 mt-2">Lihat file SPK</a>
							</div>
						</div>
					</aside>
					<section class="cos-sk-content">
						<input type="hidden" id="fm-cashout-subkon-id_cashout_subkon" name="id_cashout_subkon">

						<div class="card cos-sk-tabs-card">
							<div class="card-body">
								<ul class="nav nav-pills cos-sk-tabs flex-column flex-md-row row-gap-2" role="tablist">
									<li class="nav-item">
										<a class="nav-link active" data-toggle="tab" href="#form" role="tab">Form SPK</a>
									</li>
									<li class="nav-item">
										<a class="nav-link" data-toggle="tab" href="#fm-cashout-subkon-status" role="tab">Riwayat Perubahan Status</a>
									</li>
								</ul>
							</div>
						</div>

						<div class="tab-content">
							<div class="tab-pane fade show active" id="form" role="tabpanel">
								<div class="card">
									<div class="card-body">
										<div class="divider divider-left">
											<div class="divider-text">Detail SPK</div>
										</div>
										<div class="row">
											<div class="col-md-6 col-lg-4">
												<div class="form-group">
													<label for="fm-cashout-subkon-id_kavling">Pilih Kavling</label>
													<select class="form-control select2 fm-input" multiple="multiple"
														id="fm-cashout-subkon-id_kavling" name="id_kavling[]"></select>
												</div>
											</div>
											<div class="col-md-6 col-lg-4">
												<div class="form-group">
													<label for="fm-cashout-subkon-nomor_surat">No. SPK</label>
													<input type="text" class="form-control fm-input" id="fm-cashout-subkon-nomor_surat" name="nomor_surat">
												</div>
											</div>
											<div class="col-md-6 col-lg-4">
												<div class="form-group">
													<label for="fm-cashout-subkon-tanggal_surat">Tanggal SPK</label>
													<input type="text" class="form-control flatpickr-human-friendly fm-input"
														id="fm-cashout-subkon-tanggal_surat" name="tanggal_surat" placeholder="-">
												</div>
											</div>
											<div class="col-md-6 col-lg-4">
												<div class="form-group">
													<label for="fm-cashout-subkon-total_nominal">Total Nilai Kontrak</label>
													<input type="text" class="form-control num fm-input" id="fm-cashout-subkon-total_nominal" name="total_nominal">
												</div>
											</div>
											<div class="col-md-6 col-lg-8">
												<div class="form-group mb-0">
													<label for="fm-cashout-subkon-keterangan_cashout_subkon">Keterangan</label>
													<input type="text" class="form-control fm-input"
														id="fm-cashout-subkon-keterangan_cashout_subkon" name="keterangan_cashout_subkon"
														placeholder="Keterangan SPK">
												</div>
											</div>
										</div>
									</div>
								</div>

								<div class="card mb-0">
									<div class="card-body">
										<div class="divider divider-left">
											<div class="divider-text">Termin Pembayaran</div>
										</div>
										<div class="table-responsive">
											<table id="fm-cashout-subkon-termin-table" class="table table-sm compact mb-0">
												<thead>
													<tr>
														<th>Termin</th>
														<th style="width: 80px;">(%)</th>
														<th>Nominal</th>
														<th>Jatuh Tempo</th>
														<th>Status</th>
														<th>Keterangan</th>
														<th></th>
													</tr>
												</thead>
												<tbody id="fm-cashout-subkon-termin"></tbody>
											</table>
										</div>
									</div>
								</div>
							</div>

							<div class="tab-pane fade" id="fm-cashout-subkon-status" role="tabpanel">
								<div class="card mb-0">
									<div class="card-body">
										<div class="divider divider-left">
											<div class="divider-text">Aktivitas Terbaru</div>
										</div>
										<div class="cos-sk-timeline" id="cashout-subkon-history-timeline">
											<div class="text-center text-muted py-3">
												<i class="fas fa-spinner fa-spin mr-1"></i> Memuat riwayat...
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</section>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary data-submit mr-1" id="fm-cashout-subkon-submit">Simpan</button>
				<button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
			</div>
		</form>
	</div>
</div>
<script>
const status_spp = {
  0: "<span class='badge badge-primary'>Terbit SPK</span>",
  1: "<span class='badge badge-secondary'>Turun Jatuh Tempo</span>",
  2: "<span class='badge badge-info'>Pengajuan SPP</span>",
  3: "<span class='badge badge-warning'>Pengajuan Pencairan</span>",
  4: "<span class='badge badge-success'>Dibayar Oleh Keuangan</span>",
};
const status_cashout_subkon = {
  0: ["bg-primary", "fa fa-plus"],
  1: ["bg-secondary", "fa fa-calendar"],
  2: ["bg-info", "fa fa-file"],
  3: ["bg-warning", "fa fa-share"],
  4: ["bg-success", "fa fa-check"],
};

function syncCashoutSubkonToken(response) {
  if (response && response.token) {
    csrfHash = response.token;
    $(`input[name="${csrfName}"]`).val(csrfHash);
  }
}

function cashoutSubkonPostData(data = {}) {
  return Object.assign({ [csrfName]: csrfHash }, data);
}

function updateCashoutSubkonHeroLabel(data) {
  let text = "Belum ada kavling dipilih";
  if (isNotEmpty(data)) {
    const parts = data.map((item) => {
      return item.text || `${item.nama_jalan} No ${item.no_kavling}`;
    });
    text = parts.join(", ");
  }
  $("#modal-cashout-subkon .cos-sk-hero-label").text(text);
}

/******** Checkout Subkon **********/
function normalizeCashoutSubkonOptions(options) {
  if (options === undefined || options === null) {
    return {};
  }

  if (Array.isArray(options)) {
    return { id_kavlings: options };
  }

  if (
    (typeof options === "number" || typeof options === "string") &&
    String(options) === "1" &&
    typeof roleid !== "undefined" &&
    roleid == 3
  ) {
    return {};
  }

  if (typeof options === "number" || typeof options === "string") {
    return { id_kavlings: [String(options)] };
  }

  return options;
}

function resetCashoutSubkonForm() {
  const select2_id_kavling = "#fm-cashout-subkon-id_kavling";

  load_cashout_subkon_detail([]);
  $("#fm-cashout-subkon")[0].reset();
  $(select2_id_kavling).val(null).trigger("change");
  $("#fm-cashout-subkon-id_subkon").val(null).trigger("change");
  state.id_cashout_subkon = null;
  load_cashout_subkon(null);
  load_subkon([]);
  load_list_kavling([]);
  updateCashoutSubkonHeroLabel([]);
  setImgOrPlaceholder(
    $("#fm-cashout-subkon-file_surat-here"),
    "",
    typeof not_found !== "undefined" ? not_found : "images/not_found.png",
  );
  load_dropzone("fm-cashout-subkon-file_surat");
}

function openCashoutSubkonCreate() {
  resetCashoutSubkonForm();
  initModalListener("#modal-cashout-subkon");
  $("#modal-cashout-subkon").modal("show");
}

function refreshCashoutSubkonContext() {
  if (typeof loadSiteplanUrgentPanel === "function") {
    loadSiteplanUrgentPanel({ force: false });
  }
}

function reloadCurrentCashoutSubkon() {
  if (!state.id_cashout_subkon) {
    refreshCashoutSubkonContext();
    return;
  }

  openCOSubkon({
    id_cashout_subkon: state.id_cashout_subkon,
    id_proyek: typeof dt_proyek === "object" ? dt_proyek.id_proyek : "",
  });
  refreshCashoutSubkonContext();
}

function openCOSubkon(options = {}) {
  options = normalizeCashoutSubkonOptions(options);

  if (options.id_proyek && typeof dt_proyek === "object") {
    dt_proyek.id_proyek = options.id_proyek;
  }

  let sh = typeof editdtt !== "undefined" && Array.isArray(editdtt) ? editdtt : [];
  const id_cashout_subkon = options.id_cashout_subkon || "";
  const id_kavlings = isNotEmpty(options.id_kavlings)
    ? options.id_kavlings.map((item) => String(item))
    : sh.map((item) => item.id.substr(3));

  if (!id_cashout_subkon && id_kavlings.length == 0) {
    return swal("error", "Pilih kavling terlebih dahulu");
  }
  const select2_id_kavling = "#fm-cashout-subkon-id_kavling";
  resetCashoutSubkonForm();

  const selected_kavlings = isNotEmpty(options.selected_kavlings)
    ? options.selected_kavlings
    : sh.map((item) => ({
        id_kavling: item.id.substr(3),
        nama_jalan: item.data.nama_jalan,
        no_kavling: item.data.no_kavling,
      }));

  const modal = "#modal-cashout-subkon";

  $.ajax({
    url: base_url + "cashout/subkon/ambil",
    type: "post",
    data: cashoutSubkonPostData(
      id_cashout_subkon
        ? { id_cashout_subkon: id_cashout_subkon }
        : { id_kavlings: id_kavlings }
    ),
    dataType: "json",
    beforeSend: function () {
      $("#loading").removeClass("hidden");
    },
    success: function (r) {
      syncCashoutSubkonToken(r);
      $("#loading").addClass("hidden");
      if (r.status == "error") {
        return swal("error", r.message);
      }

      if (isNotEmpty(r.subkon)) {
        load_subkon(r.subkon);
      }

      let kavlings = selected_kavlings;
      if (isNotEmpty(r.detail_kavling)) {
        kavlings = r.detail_kavling;
      }

      load_selected_kavling(kavlings, select2_id_kavling);

      if (isNotEmpty(r.cashout_subkon)) {
        state.id_cashout_subkon = r.cashout_subkon.id_cashout_subkon;
        load_cashout_subkon(r.cashout_subkon);
        setImgOrPlaceholder(
          $("#fm-cashout-subkon-file_surat-here"),
          r.cashout_subkon.file_surat_access_url,
          not_found,
        );
      }

      let is_locked = false;
      if (isNotEmpty(r.cashout_subkon_detail)) {
        is_locked = r.cashout_subkon_detail.some((item) => item.status != 0);
        load_cashout_subkon_detail(r.cashout_subkon_detail);
      }
      load_list_kavling(kavlings, is_locked);

      initModalListener(modal);
      $(modal).modal("show");
    },
    error: function (r) {
      $("#loading").addClass("hidden");
      return swal("error", "Terjadi kesalahan saat memuat data");
    },
  });
}

function loadHistoryStatusCashoutSubkon() {
  const id_timeline = "#cashout-subkon-history-timeline";
  $.ajax({
    url: base_url + "cashout/subkon/history",
    type: "post",
    data: cashoutSubkonPostData({
      id_cashout_subkon: state.id_cashout_subkon,
    }),
    dataType: "json",
    beforeSend: function () {
      $(id_timeline).html(
        "<div class='text-center text-muted py-3'><i class='fas fa-spinner fa-spin mr-1'></i> Memuat riwayat...</div>",
      );
    },
    success: function (r) {
      syncCashoutSubkonToken(r);
      $(id_timeline).html("");
      if (r.status == "error") {
        return swal("error", r.message);
      }
      if (r.data.length == 0) {
        $(id_timeline).html("<p class='text-center'>Belum ada aktivitas</p>");
        return;
      }
      r.data.forEach((item) => {
        $(id_timeline).append(
          `
         <div class="timeline-item pb-4">
          <div class="timeline-icon ${status_cashout_subkon[item.status][0]}">
            <i class="${status_cashout_subkon[item.status][1]}"></i>
          </div>
          <div class="timeline-content">
            <div class="d-flex justify-content-between align-items-center">
              <h6 class="mb-0 font-weight-bold">${item.keterangan}</h6>
              <span class="badge badge-success-light text-success px-2"><i class="far fa-calendar-alt mr-1"></i> ${format_datetime(item.created_at)}</span>
            </div>
            <div class="text-muted small mt-1">
              <i class="far fa-user ml-3 mr-1"></i> ${item.username}
            </div>
          </div>
        </div>
          `,
        );
      });

      // if (isNotEmpty(r.cashout_subkon_detail)) {
      //   load_cashout_subkon_detail(r.cashout_subkon_detail);
      // }
    },
    error: function (r) {
      $(id_timeline).html(
        "<p class='text-center'>Terjadi kesalahan saat memuat data</p>",
      );
    },
  });
}

$("#fm-cashout-subkon-submit").click(function (e) {
  e.preventDefault();

  if (!isNotEmpty($("#fm-cashout-subkon-nama_subkon").val())) {
    return swal("error", "Subkon Harus diisi");
  }
  if (!isNotEmpty($("#fm-cashout-subkon-hp1_subkon").val())) {
    return swal("error", "No. HP Subkon Harus diisi");
  }
  if (!isNotEmpty($("#fm-cashout-subkon-alamat_subkon").val())) {
    return swal("error", "Alamat Subkon Harus diisi");
  }
  if (!isNotEmpty($("#fm-cashout-subkon-id_kavling").val())) {
    return swal("error", "Kavling Harus diisi");
  }
  if (!isNotEmpty($("#fm-cashout-subkon-tanggal_surat").val())) {
    return swal("error", "Tanggal Surat Harus diisi");
  }
  if (!isNotEmpty($("#fm-cashout-subkon-nomor_surat").val())) {
    return swal("error", "No. Surat Harus diisi");
  }
  if (
    !isNotEmpty($("#fm-cashout-subkon-total_nominal").val()) ||
    removeComma($("#fm-cashout-subkon-total_nominal").val()) == 0
  ) {
    return swal("error", "Total Nominal harus diisi");
  }

  let total_percentage = 0;
  $(".termin-percentage").each(function () {
    total_percentage += removeComma($(this).val());
  });
  if (total_percentage != 100) {
    return swal("error", "Total Persentase harus 100%");
  }
  let total_nominal = 0;
  $(".termin-nominal").each(function () {
    total_nominal += removeComma($(this).val());
  });
  if (
    total_nominal != removeComma($("#fm-cashout-subkon-total_nominal").val())
  ) {
    return swal("error", "Total Nominal harus sesuai dengan total persentase");
  }

  if (!isNotEmpty($("#fm-cashout-subkon-id_cashout_subkon").val())) {
    if (!isNotEmpty($("#fm-cashout-subkon-file_surat").val())) {
      return swal("error", "Soft file SPK harus diupload");
    }
  }

  const data = $("#fm-cashout-subkon").serializeArray();
  let formData = new FormData();
  data.forEach((item) => {
    formData.append(item.name, item.value);
  });
  const file = $("#fm-cashout-subkon-file_surat")[0].files[0];
  if (file) {
    formData.append("file_surat", file);
  }
  formData.append(csrfName, csrfHash);
  $.ajax({
    url: base_url + "cashout/subkon/save",
    type: "POST",
    data: formData,
    processData: false,
    contentType: false,
    success: function (r) {
      syncCashoutSubkonToken(r);
      if (r.status == "error") {
        return swal("error", r.message);
      }
      removeModalListener("#modal-cashout-subkon");
      swal("success", r.message);
      $("#modal-cashout-subkon").modal("hide");
      if ($.fn.DataTable.isDataTable("#cashout-subkon-table")) {
        $("#cashout-subkon-table").DataTable().ajax.reload(null, false);
      }
    },
    error: function (r) {
      return swal("error", "Terjadi kesalahan saat menyimpan data");
    },
  });
});

function disabled_input(is_locked) {
  if (!is_locked) {
    $(".termin-percentage").prop("disabled", false);
    $(".fm-input").prop("disabled", false);
    $("#fm-cashout-subkon-submit").prop("disabled", false);
  } else {
    $(".termin-percentage").prop("disabled", true);
    $(".fm-input").prop("disabled", true);
    $("#fm-cashout-subkon-submit").prop("disabled", true);
  }
  if (roleid == 3) {
    $(".fm-input").prop("disabled", true);
    $(".termin-percentage").prop("disabled", true);
    $("#fm-cashout-subkon-submit").prop("disabled", true);
  }

  // if (is_locked) {
  //   $("#fm-cashout-subkon :input").prop("disabled", true);
  //   $("#modal-cashout-subkon .modal-footer .btn-primary").prop(
  //     "disabled",
  //     true,
  //   );
  //   $(".remove-termin").prop("disabled", true);
  // } else {
  //   $("#fm-cashout-subkon :input").prop("disabled", false);
  //   $("#modal-cashout-subkon .modal-footer .btn-primary").prop(
  //     "disabled",
  //     false,
  //   );
  // }
}

function sum_percentage(el) {
  let total = 0;
  $(".termin-percentage").each(function () {
    total += removeComma($(this).val());
  });
  if (total > 100) {
    showToast("Total persentase melebihi 100%", "warning");
  }
  let tr = $(el).closest("tr");
  let p = removeComma($(el).val());
  let totals = removeComma($("#fm-cashout-subkon-total_nominal").val());
  let nominal = (totals * p) / 100;
  tr.find(".termin-nominal").val(num_format(nominal));
}
function load_cashout_subkon_detail(data) {
  let is_locked = data.some((item) => item.status != 0);
  let disabled = is_locked ? "disabled" : "";

  const tbody = $("#fm-cashout-subkon-termin");
  if (tbody.find("tr").length > 0 && tbody.find("tr").length == data.length) {
    data.forEach((item, i) => {
      // $(`#fm-cashout-subkon-id_cashout_subkon_detail-${i}`).val(
      //   item.id_cashout_subkon_detail ?? "",
      // );
      $(`#fm-cashout-subkon-berita_acara-${i}`).val(item.berita_acara ?? "");
      $(`#fm-cashout-subkon-persentase-${i}`).val(num_format(item.persentase));
      $(`#fm-cashout-subkon-nominal-${i}`).val(num_format(item.nominal));
      if (item.tanggal_jatuh_tempo) {
        $(`#fm-cashout-subkon-tanggal_jatuh_tempo-${i}`).val(
          item.tanggal_jatuh_tempo,
        );
      }
      if (item.keterangan) {
        $(`#fm-cashout-subkon-keterangan-${i}`).val(item.keterangan);
      }
    });
  } else {
    tbody.empty();
    let i = 0;
    data.forEach((item) => {
      let btn = "";
      let status = "";
      if (roleid == 3 && item.status == 0) {
        btn = `<button type="button" class="btn btn-sm btn-secondary turun-jatuh-tempo w-100" data-i="${i}" data-id="${item.id_cashout_subkon_detail}" data-status="${item.status}"><i class="fa fa-plus"></i> Terbit Jatuh Tempo</button>`;
      } else if (roleid == 3 && item.status == 1) {
        btn = `<button type="button" class="btn btn-sm btn-secondary turun-jatuh-tempo w-100" data-i="${i}" data-id="${item.id_cashout_subkon_detail}" data-status="${item.status}"><i class="fa fa-edit"></i> Ubah Jatuh Tempo</button>`;
      } else if (roleid == 7 && item.status == 1) {
        btn = `<button type="button" class="btn btn-sm btn-info ajukan-spp w-100" data-i="${i}" data-id="${item.id_cashout_subkon_detail}" data-status="${item.status}"><i class="fa fa-plus"></i> Ajukan SPP</button>`;
      } else if (roleid == 7 && item.status == 2) {
        status =
          "No SPP: " + item.spp_no + "(" + format_date(item.spp_tgl) + ")";
        btn = `<button type="button" class="btn btn-sm btn-info ajukan-spp w-100" data-i="${i}" data-id="${item.id_cashout_subkon_detail}" data-status="${item.status}"><i class="fa fa-edit"></i> Ubah SPP</button>`;
      } else if (roleid == 3 && item.status == 2) {
        status =
          "No SPP: " + item.spp_no + "(" + format_date(item.spp_tgl) + ")";
        btn = `<button type="button" class="btn btn-sm btn-warning ajukan-pencairan w-100" data-i="${i}" data-id="${item.id_cashout_subkon_detail}" data-status="${item.status}"><i class="fa fa-plus"></i> Ajukan Pencairan</button>`;
      } else if (roleid == 3 && item.status == 3) {
        status =
          "Tgl Pengajuan Cair: " + format_date(item.pengajuan_cair_tgl);
        btn = `<button type="button" class="btn btn-sm btn-warning ajukan-pencairan w-100" data-i="${i}" data-id="${item.id_cashout_subkon_detail}" data-status="${item.status}"><i class="fa fa-edit"></i> Ubah Tanggal</button>`;
        btn += `<button type="button" class="btn btn-sm btn-success pembayaran-pencairan w-100" data-i="${i}" data-id="${item.id_cashout_subkon_detail}" data-status="${item.status}"><i class="fa fa-plus"></i> Pembayaran</button>`;
      } else if (item.status == 4) {
        status =
          "No Cek: " + item.cek_no + "(" + format_date(item.cek_tgl) + ")";
      }

      const tr = $("<tr>");
      tr.append(`<td style="min-width:100px; nowrap">
      <input type="hidden" id="fm-cashout-subkon-berita_acara-${i}" name="berita_acara[]" value="${
        item.berita_acara ?? ""
      }">
      ${item.berita_acara ?? "-"}
      </td>`);
      tr.append(
        `<td style="min-width:100px">
        <input type="hidden" id="fm-cashout-subkon-id_cashout_subkon_detail-${i}" name="id_cashout_subkon_detail[]" value="${item.id_cashout_subkon_detail ?? ""}">
        <input type="text" class="form-control form-control-sm termin-percentage num" id="fm-cashout-subkon-persentase-${i}" name="persentase[]" value="${num_format(item.persentase)}">
        </td>`,
      );
      tr.append(
        `<td><input type="text" class="form-control form-control-sm termin-nominal num" id="fm-cashout-subkon-nominal-${i}" name="nominal[]" value="${num_format(
          item.nominal,
        )}" readonly></td>`,
      );
      tr.append(
        `<td style="min-width:150px"><input type="text" disabled class="form-control form-control-sm flatpickr-human-friendly termin-jatuh-tempo" id="fm-cashout-subkon-tanggal_jatuh_tempo-${i}" name="tanggal_jatuh_tempo[]" value="${
          item.tanggal_jatuh_tempo || ""
        }"></td>`,
      );
      tr.append(`
          <td>
            ${status_spp[item.status] || "-"}<br>
            ${status}
            </td>`);
      tr.append(
        `<td><input type="text" class="form-control form-control-sm termin-keterangan" id="fm-cashout-subkon-keterangan-${i}" name="keterangan[]" value="${
          item.keterangan || ""
        }"></td>`,
      );
      tr.append(`<td>${btn}</td>`);
      tbody.append(tr);
      i++;
    });

    disabled_input(is_locked);

    $(".termin-percentage").change(function () {
      let tr = $(this).closest("tr");
      if (tr.find(".termin-nominal").prop("readonly")) {
        sum_percentage(this);
      }
    });

    $(".remove-termin").click(function () {
      $(this).closest("tr").remove();
    });

    $(".flatpickr-human-friendly").flatpickr({
      altInput: true,
      altFormat: "j F Y",
      dateFormat: "Y-m-d",
    });
  }
}

// === Turun Jatuh Tempo: inline edit flow ===
$(document).on("click", ".turun-jatuh-tempo", function () {
  const btn = $(this);
  const idx = btn.data("i");
  const id = btn.data("id");
  const dateInput = $(`#fm-cashout-subkon-tanggal_jatuh_tempo-${idx}`);
  const fp = dateInput[0]._flatpickr;

  // Enable the date input (both original + flatpickr alt input)
  dateInput.prop("disabled", false);
  if (fp && fp.altInput) {
    fp.altInput.disabled = false;
  }

  // Replace button with confirm (✓) and cancel (✗) buttons
  const td = btn.closest("td");
  td.html(
    `<div class="btn-group w-100">
      <button type="button" class="btn btn-sm btn-success confirm-jatuh-tempo w-50" data-i="${idx}" data-id="${id}"><i class="fa fa-check"></i></button>
      <button type="button" class="btn btn-sm btn-danger cancel-jatuh-tempo w-50" data-i="${idx}" data-id="${id}"><i class="fa fa-times"></i></button>
    </div>`,
  );
});

// Confirm: validate & send to server
$(document).on("click", ".confirm-jatuh-tempo", function () {
  const btn = $(this);
  const idx = btn.data("i");
  const id = btn.data("id");
  const dateInput = $(`#fm-cashout-subkon-tanggal_jatuh_tempo-${idx}`);
  const tanggal = dateInput.val();

  if (!tanggal) {
    return swal("error", "Tanggal Jatuh Tempo harus diisi");
  }

  const berita_acara = $(`#fm-cashout-subkon-berita_acara-${idx}`).val();

  $.ajax({
    url: base_url + "cashout/subkon/turun-jatuh-tempo",
    type: "POST",
    data: cashoutSubkonPostData({
      id_cashout_subkon_detail: id,
      tanggal_jatuh_tempo: tanggal,
      berita_acara: berita_acara,
    }),
    dataType: "json",
    beforeSend: function () {
      btn.prop("disabled", true);
    },
    success: function (r) {
      syncCashoutSubkonToken(r);
      btn.prop("disabled", false);
      if (r.status == "error") {
        return swal("error", r.message);
      }
      swal("success", r.message);
      reloadCurrentCashoutSubkon();
    },
    error: function () {
      btn.prop("disabled", false);
      return swal("error", "Terjadi kesalahan saat menyimpan data");
    },
  });
});

// Cancel: revert to original button
$(document).on("click", ".cancel-jatuh-tempo", function () {
  const btn = $(this);
  const idx = btn.data("i");
  const id = btn.data("id");
  const dateInput = $(`#fm-cashout-subkon-tanggal_jatuh_tempo-${idx}`);

  // Disable input again (both original + flatpickr alt input)
  dateInput.prop("disabled", true);
  if (dateInput[0]._flatpickr && dateInput[0]._flatpickr.altInput) {
    dateInput[0]._flatpickr.altInput.disabled = true;
  }

  // Restore original button
  const td = btn.closest("td");
  td.html(
    `<button type="button" class="btn btn-sm btn-secondary turun-jatuh-tempo" data-i="${idx}" data-id="${id}" data-status="0"><i class="fa fa-edit"></i> Terbit Jatuh Tempo</button>`,
  );
});

// === Ajukan SPP: SweetAlert popup with form ===
$(document).on("click", ".ajukan-spp", function () {
  const btn = $(this);
  const idx = btn.data("i");
  const id = btn.data("id");

  Swal.fire({
    title: "Ajukan SPP",
    html: `
      <div class="text-left">
        <div class="form-group">
          <label for="swal-spp_no">No. SPP</label>
          <input type="text" id="swal-spp_no" class="form-control" placeholder="Masukkan No. SPP">
        </div>
        <div class="form-group">
          <label for="swal-spp_tgl">Tanggal SPP</label>
          <input type="text" id="swal-spp_tgl" class="form-control flatpickr-basic" placeholder="Pilih Tanggal SPP">
        </div>
      </div>
    `,
    showCancelButton: true,
    confirmButtonText: "Simpan",
    cancelButtonText: "Batal",
    focusConfirm: false,
    didOpen: () => {
      $("#swal-spp_tgl").flatpickr({
        altInput: true,
        altFormat: "j F Y",
        dateFormat: "Y-m-d",
        defaultDate: new Date(),
      });
    },
    preConfirm: () => {
      const spp_no = Swal.getPopup().querySelector("#swal-spp_no").value;
      const spp_tgl = Swal.getPopup().querySelector("#swal-spp_tgl").value;
      if (!spp_no || !spp_tgl) {
        Swal.showValidationMessage(`No SPP dan Tanggal SPP harus diisi`);
      }
      return { spp_no: spp_no, spp_tgl: spp_tgl };
    },
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        url: base_url + "cashout/subkon/ajukan-spp",
        type: "POST",
        data: cashoutSubkonPostData({
          id_cashout_subkon_detail: id,
          spp_no: result.value.spp_no,
          spp_tgl: result.value.spp_tgl,
        }),
        dataType: "json",
        beforeSend: function () {
          Swal.fire({
            title: "Mohon Tunggu",
            allowOutsideClick: false,
            didOpen: () => {
              Swal.showLoading();
            },
          });
        },
        success: function (r) {
          syncCashoutSubkonToken(r);
          if (r.status == "error") {
            return swal("error", r.message);
          }
          swal("success", r.message);
          reloadCurrentCashoutSubkon();
        },
        error: function () {
          return swal("error", "Terjadi kesalahan saat menyimpan data");
        },
      });
    }
  });
});

// === Ajukan Pencairan: SweetAlert popup with form ===
$(document).on("click", ".ajukan-pencairan", function () {
  const btn = $(this);
  const idx = btn.data("i");
  const id = btn.data("id");

  Swal.fire({
    title: "Ajukan Pencairan",
    html: `
      <div class="text-left">
        <div class="form-group">
          <label for="swal-pencairan_tgl">Tanggal Pengajuan Cair</label>
          <input type="text" id="swal-pencairan_tgl" class="form-control flatpickr-basic" placeholder="Pilih Tanggal Pengajuan">
        </div>
      </div>
    `,
    showCancelButton: true,
    confirmButtonText: "Simpan",
    cancelButtonText: "Batal",
    focusConfirm: false,
    didOpen: () => {
      $("#swal-pencairan_tgl").flatpickr({
        altInput: true,
        altFormat: "j F Y",
        dateFormat: "Y-m-d",
        defaultDate: new Date(),
      });
    },
    preConfirm: () => {
      const pencairan_tgl = Swal.getPopup().querySelector(
        "#swal-pencairan_tgl",
      ).value;
      if (!pencairan_tgl) {
        Swal.showValidationMessage(`Tanggal Pengajuan Cair harus diisi`);
      }
      return { pencairan_tgl: pencairan_tgl };
    },
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        url: base_url + "cashout/subkon/ajukan-pencairan",
        type: "POST",
        data: cashoutSubkonPostData({
          id_cashout_subkon_detail: id,
          pencairan_tgl: result.value.pencairan_tgl,
        }),
        dataType: "json",
        beforeSend: function () {
          Swal.fire({
            title: "Mohon Tunggu",
            allowOutsideClick: false,
            didOpen: () => {
              Swal.showLoading();
            },
          });
        },
        success: function (r) {
          syncCashoutSubkonToken(r);
          if (r.status == "error") {
            return swal("error", r.message);
          }
          swal("success", r.message);
          reloadCurrentCashoutSubkon();
        },
        error: function () {
          return swal("error", "Terjadi kesalahan saat menyimpan data");
        },
      });
    }
  });
});

// === Pembayaran Pencairan: SweetAlert popup with form ===
$(document).on("click", ".pembayaran-pencairan", function () {
  const btn = $(this);
  const idx = btn.data("i");
  const id = btn.data("id");

  Swal.fire({
    title: "Pembayaran Pencairan",
    html: `
      <div class="text-left">
        <div class="form-group">
          <label for="swal-cek_no">No. Cek</label>
          <input type="text" id="swal-cek_no" class="form-control" placeholder="Masukkan No. Cek">
        </div>
        <div class="form-group">
          <label for="swal-cek_tgl">Tanggal Cek</label>
          <input type="text" id="swal-cek_tgl" class="form-control flatpickr-basic" placeholder="Pilih Tanggal Cek">
        </div>
      </div>
    `,
    showCancelButton: true,
    confirmButtonText: "Simpan",
    cancelButtonText: "Batal",
    focusConfirm: false,
    didOpen: () => {
      $("#swal-cek_tgl").flatpickr({
        altInput: true,
        altFormat: "j F Y",
        dateFormat: "Y-m-d",
        defaultDate: new Date(),
      });
    },
    preConfirm: () => {
      const cek_no = Swal.getPopup().querySelector("#swal-cek_no").value;
      const cek_tgl = Swal.getPopup().querySelector("#swal-cek_tgl").value;
      if (!cek_no || !cek_tgl) {
        Swal.showValidationMessage(`No Cek dan Tanggal Cek harus diisi`);
      }
      return { cek_no: cek_no, cek_tgl: cek_tgl };
    },
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        url: base_url + "cashout/subkon/pembayaran",
        type: "POST",
        data: cashoutSubkonPostData({
          id_cashout_subkon_detail: id,
          cek_no: result.value.cek_no,
          cek_tgl: result.value.cek_tgl,
        }),
        dataType: "json",
        beforeSend: function () {
          Swal.fire({
            title: "Mohon Tunggu",
            allowOutsideClick: false,
            didOpen: () => {
              Swal.showLoading();
            },
          });
        },
        success: function (r) {
          syncCashoutSubkonToken(r);
          if (r.status == "error") {
            return swal("error", r.message);
          }
          swal("success", r.message);
          reloadCurrentCashoutSubkon();
        },
        error: function () {
          return swal("error", "Terjadi kesalahan saat menyimpan data");
        },
      });
    }
  });
});
function load_list_kavling(data, is_locked = false) {
  updateCashoutSubkonHeroLabel(data);
}
function load_cashout_subkon(data) {
  if (data == null) {
    $("#fm-cashout-subkon-id_cashout_subkon").val("");
    $("#fm-cashout-subkon-nomor_surat").val("");
    setDatePicker("", "#fm-cashout-subkon-tanggal_surat");
    $("#fm-cashout-subkon-total_nominal").val("");
    $("#fm-cashout-subkon-keterangan_cashout_subkon").val("");
  } else {
    $("#fm-cashout-subkon-id_cashout_subkon").val(data.id_cashout_subkon);
    $("#fm-cashout-subkon-nomor_surat").val(data.nomor_surat);
    setDatePicker(data.tanggal_surat, "#fm-cashout-subkon-tanggal_surat");
    $("#fm-cashout-subkon-total_nominal").val(num_format(data.total_nominal));
    $("#fm-cashout-subkon-keterangan_cashout_subkon").val(data.keterangan);
  }
}
function load_selected_kavling(data, id_select2) {
  $(id_select2).empty();
  data.forEach((item) => {
    const option = new Option(
      item.nama_jalan + " - " + item.no_kavling,
      item.id_kavling,
      true,
      true,
    );
    $(id_select2).append(option);
  });
  $(id_select2).trigger("change");
}
function load_subkon(data) {
  if (!isNotEmpty(data)) {
    $("#fm-cashout-subkon-id_subkon").val("");
    $("#fm-cashout-subkon-nama_subkon").val("");
    $("#fm-cashout-subkon-hp1_subkon").val("");
    $("#fm-cashout-subkon-alamat_subkon").val("");
  } else {
    const option = new Option(data.nama_subkon, data.id, true, true);
    $("#fm-cashout-subkon-id_subkon").append(option);

    $("#fm-cashout-subkon-id_subkon").val(data.id);
    $("#fm-cashout-subkon-nama_subkon").val(data.nama_subkon);
    $("#fm-cashout-subkon-hp1_subkon").val(data.hp1_subkon);
    $("#fm-cashout-subkon-alamat_subkon").val(data.alamat_subkon);
  }
}

$("#fm-cashout-subkon-total_nominal").change(function () {
  let val = removeComma($(this).val());
  let termin = $("#fm-cashout-subkon-termin");
  const data_subkon = [];
  if (val > 0) {
    let percentages = [20, 20, 20, 20, 15, 5];
    percentages.forEach((p, i) => {
      let nominal = (val * p) / 100;
      data_subkon.push({
        berita_acara: "Termin " + (i + 1),
        persentase: p,
        nominal: nominal,
        tanggal_jatuh_tempo: "",
        status: 0,
        keterangan: "",
      });
    });
    load_cashout_subkon_detail(data_subkon);
  } else {
    termin.empty();
  }
});

$("#fm-cashout-subkon-id_kavling")
  .select2({
    placeholder: "Pilih Kavling",
    allowClear: true,
    multiple: true,
    ajax: {
      url: base_url + "kavling/list/ambil",
      dataType: "json",
      delay: 250,
      method: "post",
      data: function (params) {
        return {
          [csrfName]: csrfHash,
          search: params.term,
          is_cashout_subkon: 1,
          id_proyek: dt_proyek.id_proyek,
          limit: 20,
        };
      },
      processResults: function (r) {
        let results = [];
        $.each(r, function (i, v) {
          let disabled = false;
          let text = `${v.nama_jalan} No ${v.no_kavling}`;
          if (v.id_cashout_subkon) {
            disabled = true;
            text += " (Sudah dibuat tagihan)";
          }
          results.push({
            id: v.id_kavling,
            text: text,
            disabled: disabled,
          });
        });
        return {
          results: results,
        };
      },
      cache: true,
    },
  })
  .on("change", function () {
    const items = $(this).select2("data").map((item) => ({
      id_kavling: item.id,
      nama_jalan: item.text ? item.text.split(" No ")[0] : "",
      no_kavling: item.text ? item.text.split(" No ")[1] : "",
      text: item.text,
    }));
    load_list_kavling(items);
  });

$("#fm-cashout-subkon-id_subkon")
  .select2({
    placeholder: "Pilih Subkon",
    allowClear: true,
    ajax: {
      url: base_url + "subkon/list/ambil",
      dataType: "json",
      delay: 250,
      method: "post",
      data: function (params) {
        return {
          [csrfName]: csrfHash,
          search: params.term,
          limit: 20,
        };
      },
      processResults: function (r) {
        let results = [];
        $.each(r, function (i, v) {
          results.push({
            id: v.id,
            text: `${v.nama_subkon}`,
            nama_subkon: v.nama_subkon,
            hp1_subkon: v.hp1_subkon,
            alamat_subkon: v.alamat_subkon,
          });
        });
        return {
          results: results,
        };
      },
      cache: true,
    },
  })
  .on("change", function () {
    let data = $(this).select2("data");
    if (data.length > 0) {
      load_subkon(data[0]);
    } else {
      load_subkon([]);
    }
  });


</script>
