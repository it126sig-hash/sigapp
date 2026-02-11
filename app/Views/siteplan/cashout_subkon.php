<style>
	.select2-selection__choice {
		display: block;
		margin: 2px 0;
	}

	.select2-container--default .select2-selection--multiple {
		height: auto;
	}

	/* Termin: pendek, 1 baris */
	.col-termin {
		white-space: nowrap;
		width: 90px;
		font-weight: 500;
	}

	/* Persentase */
	.col-percent {
		white-space: nowrap;
		width: 100px;
	}

	.percent-input {
		max-width: 60px;
		margin: auto;
		padding: 4px 6px;
	}

	/* Jatuh tempo: 1 baris, format teks */
	.col-date {
		white-space: nowrap;
		width: 120px;
	}

	.termin-nominal {
		width: 90px;
		text-align: right;
	}

	/* Status: maksimal 2 baris */
	.col-status span {
		display: -webkit-box;
		-webkit-line-clamp: 2;
		-webkit-box-orient: vertical;
		overflow: hidden;
		line-height: 1.3em;
		max-height: 2.6em;
	}

	/* Keterangan: panjang & multi-line */
	.col-keterangan {
		white-space: normal;
		min-width: 250px;
		max-width: 250px;
		line-height: 1.4em;
	}
</style>

<div class="modal fade" id="modal-cashout-subkon" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-xl" role="document">
		<div class="modal-content spk-modal">
			<!-- Header -->
			<div class="modal-header">
				<div>
					<h5 class="modal-title">SPK Sub-Konstruksi</h5>
					<small class="text-muted">Detail Cashout Subkon</small>
				</div>
				<button type="button" class="close" data-dismiss="modal">
					<span>&times;</span>
				</button>
			</div>

			<!-- Tabs -->
			<div class="modal-body pt-0" style="background-color: #eee;">
				<form id="fm-cashout-subkon">
					<div class="row">
						<div class="col-md-3">
							<!-- Subkontraktor -->
							<div class="card mb-3">
								<div class="card-body">
									<div class="divider divider-left">
										<div class="divider-text font-weight-bold">SUBKONTRAKTOR</div>
									</div>
									<div class="form-group">
										<label for="fm-cashout-subkon-id_subkon">Pilih Subkon</label>
										<select class="form-control form-control-sm select2 fm-input" id="fm-cashout-subkon-id_subkon" name="id_subkon">
										</select>
									</div>
									<div class="form-row">
										<div class="form-group col-md-12 floating-label">
											<input type="text" placeholder=" " class="form-control fm-input" id="fm-cashout-subkon-nama_subkon" name="nama_subkon">
											<label for="fm-cashout-subkon-nama_subkon">Nama Subkontraktor</label>
										</div>
										<div class="form-group col-md-12 floating-label">
											<input type="text" placeholder=" " class="form-control fm-input" id="fm-cashout-subkon-hp1_subkon" name="hp1_subkon">
											<label for="fm-cashout-subkon-hp1_subkon">Kontak</label>
										</div>
										<div class="form-group col-md-12 floating-label">
											<input type="text" placeholder=" " class="form-control fm-input" id="fm-cashout-subkon-alamat_subkon" name="alamat_subkon">
											<label for="fm-cashout-subkon-alamat_subkon">Alamat</label>
										</div>
									</div>

									<!-- Detail SPK -->
									<div class="divider divider-left">
										<div class="divider-text font-weight-bold">SPK</div>
									</div>
									<div class="form-group">
										<label for="fm-cashout-subkon-id_kavling">Pilih Kavling</label>
										<select class="form-control form-control-sm select2 fm-input" multiple="multiple"
											id="fm-cashout-subkon-id_kavling" name="id_kavling[]">
										</select>
									</div>
									<div class="form-row">
										<div class="form-group col-md-12 floating-label">
											<input type="hidden" id="fm-cashout-subkon-id_cashout_subkon" name="id_cashout_subkon">
											<input type="text" placeholder=" " class="form-control fm-input" id="fm-cashout-subkon-nomor_surat" name="nomor_surat">
											<label for="fm-cashout-subkon-nomor_surat">No. SPK</label>
										</div>
										<div class="form-group col-md-12 floating-label">
											<input type="text" placeholder=" " class="form-control flatpickr-human-friendly fm-input" id="fm-cashout-subkon-tanggal_surat" name="tanggal_surat">
											<label for="fm-cashout-subkon-tanggal_surat">Tanggal SPK</label>
										</div>
										<div class="form-group col-md-12 floating-label">
											<input type="text" placeholder=" " class="form-control num fm-input" id="fm-cashout-subkon-total_nominal" name="total_nominal">
											<label for="fm-cashout-subkon-total_nominal">Total Nilai Kontrak</label>
										</div>
										<div class="form-group col-md-12 floating-label">
											<input type="text" placeholder=" " class="form-control fm-input" id="fm-cashout-subkon-keterangan_cashout_subkon" name="keterangan_cashout_subkon">
											<label for="fm-cashout-subkon-keterangan_cashout_subkon">Keterangan</label>
										</div>
									</div>

									<div class="form-group">
										<div class="form-group">
											<label class="font-weight-bold">SPK</label>
											<div class="dropzone dropzone-lg custom-file"
												id="dz-spk">
												<input type="file"
													class="custom-file-input dz-input fm-input"
													accept="application/pdf" name="file_surat"
													id="fm-cashout-subkon-file_surat">
												<div class="dz-inner">
													<div class="dz-preview" id="prev_file_surat">
													</div>
													<div class="dz-placeholder">
														<div class="h5 mb-1">Tarik & letakkan gambar
															ke sini</div>
														<div class="text-muted">atau klik (PDF maks 5 MB)</div>
													</div>
												</div>
											</div>
										</div>
										<a href="" id="fm-cashout-subkon-file_surat-here"
											onclick="window.open(this.href, '_blank'); return false;"
											class=" btn btn-outline-primary w-100">klik untuk
											melihat file</a>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-9">
							<div class="card">
								<div class="card-body pb-0 pt-0">
									<ul class="nav nav-pills flex-column flex-md-row mt-1 row-gap-2">
										<li class="nav-item">
											<a class="nav-link active" data-toggle="tab" href="#form">Form SPK</a>
										</li>
										<li class="nav-item">
											<a class="nav-link" data-toggle="tab" href="#fm-cashout-subkon-status">Riwayat Perubahan Status</a>
										</li>
										<!-- <li class="nav-item">
											<a class="nav-link" data-toggle="tab" href="#pembayaran">Riwayat Pembayaran</a>
										</li> -->
									</ul>
								</div>
							</div>
							<div class="tab-content">
								<!-- FORM -->
								<div class="tab-pane fade show active" id="form">
									<div class="card">
										<div class="card-body">
											<div class="divider divider-left">
												<div class="divider-text font-weight-bold">LIST KAVLING</div>
											</div>
											<ul class="list-group list-group-flush" id="fm-cashout-subkon-list_kavlings"></ul>
										</div>
										<!-- Termin -->
										<div class="card">
											<div class="card-header d-flex justify-content-between align-items-center">
												<div class="divider divider-left">
													<div class="divider-text font-weight-bold">TERMIN PEMBAYARAN</div>
												</div>
												<!-- <button class="btn btn-sm btn-outline-primary">AUTO-BREAKDOWN</button> -->
											</div>
											<div class="card-body p-0">
												<div class="table-responsive">
													<table class="table mb-0">
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
														<tbody id="fm-cashout-subkon-termin">
														</tbody>
													</table>

												</div>
											</div>
										</div>
									</div>
								</div>

								<div class="tab-pane fade" id="fm-cashout-subkon-status">
									<div class="card">
										<div class="card-body">
											<style>
												.timeline {
													position: relative;
													padding-left: 50px;
												}

												/* Garis Vertikal */
												.timeline-item {
													position: relative;
												}

												.timeline-item::before {
													content: "";
													position: absolute;
													left: -32px;
													top: 40px;
													height: calc(100% - 30px);
													width: 2px;
													background: #e9ecef;
													/* Default color */
												}

												/* Warna garis khusus (optional jika ingin berwarna sesuai status) */
												.timeline-item:nth-child(1)::before {
													background: #28a745;
													opacity: 0.3;
												}

												.timeline-item:nth-child(2)::before {
													background: #007bff;
													opacity: 0.3;
												}

												.timeline-item:nth-child(3)::before {
													background: #ffc107;
													opacity: 0.3;
												}

												.timeline-item:last-child::before {
													display: none;
												}

												/* Ikon Lingkaran */
												.timeline-icon {
													position: absolute;
													left: -50px;
													width: 40px;
													height: 40px;
													border-radius: 50%;
													display: flex;
													align-items: center;
													justify-content: center;
													color: white;
													z-index: 1;
													box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
												}

												/* Styling Badge Transparan */
												.badge-success-light {
													background-color: rgba(40, 167, 69, 0.1);
												}

												.badge-primary-light {
													background-color: rgba(0, 123, 255, 0.1);
												}

												.badge-warning-light {
													background-color: rgba(255, 193, 7, 0.1);
												}

												.italic {
													font-style: italic;
												}
											</style>

											<div class="container py-5">
												<div class="row">
													<div class="col-md-8 offset-md-2">
														<h5 class="mb-4"><i class="fas fa-history mr-2 text-primary"></i><strong>Aktivitas Terbaru</strong></h5>
														<div class="timeline" id="cashout-subkon-history-timeline">
															<div class="text-center text-muted py-3">
																<i class="fas fa-spinner fa-spin mr-1"></i> Memuat riwayat...
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>

							</div>

						</div>
				</form>
			</div>

			<!-- Footer -->
			<div class="modal-footer">
				<button class="btn btn-link" data-dismiss="modal">Cancel</button>
				<button class="btn btn-primary" id="fm-cashout-subkon-submit">Simpan</button>
			</div>
		</div>
	</div>
</div>
</div>