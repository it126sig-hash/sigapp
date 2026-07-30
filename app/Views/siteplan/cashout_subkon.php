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

	#modal-cashout-subkon .form-control:disabled,
	#modal-cashout-subkon .form-control[readonly] {
		background-color: #eef1f4;
		color: #6b7280;
		cursor: not-allowed;
	}

	#modal-cashout-subkon .select2-container--disabled .select2-selection {
		background-color: #eef1f4;
		cursor: not-allowed;
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

	#modal-cashout-subkon .cashout-subkon-urgent-focus {
		animation: cashoutSubkonUrgentFocus 2.8s ease-out;
		background: #fff7e6;
	}

	@keyframes cashoutSubkonUrgentFocus {
		0% {
			box-shadow: inset 4px 0 0 #ff9f43;
		}

		100% {
			box-shadow: inset 4px 0 0 transparent;
		}
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

	.dark-layout #modal-cashout-subkon .form-control:disabled,
	.dark-layout #modal-cashout-subkon .form-control[readonly] {
		background: #1f2937 !important;
		color: #9ca3af;
	}

	.dark-layout #modal-cashout-subkon .select2-container--disabled .select2-selection {
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
<script src="<?= base_url() ?>assets/js/siteplan/cashout_subkon.js?v=<?= filemtime(FCPATH.'assets/js/siteplan/cashout_subkon.js') ?>"></script>