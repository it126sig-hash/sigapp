<link rel="stylesheet" type="text/css"
	href="<?= base_url() ?>app-assets/vendors/css/tables/datatable/dataTables.bootstrap4.min.css">
<link rel="stylesheet" type="text/css"
	href="<?= base_url() ?>app-assets/vendors/css/tables/datatable/responsive.bootstrap4.min.css">
<link rel="stylesheet" type="text/css"
	href="<?= base_url() ?>app-assets/vendors/css/tables/datatable/buttons.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/extensions/sweetalert2.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/forms/select/select2.min.css">
<link rel="stylesheet" type="text/css"
	href="<?= base_url() ?>app-assets/vendors/css/pickers/flatpickr/flatpickr.min.css">
<!-- Quill CSS -->
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/editors/quill/quill.snow.css">

<script>
	// var csrfName = '<?= csrf_token() ?>';
	// var csrfHash = '<?= csrf_hash() ?>';
	// const base_url = '<?= base_url() ?>';
</script>
<style>
	.section-title {
		font-size: 14px;
		font-weight: 600;
		color: #495057;
		margin-bottom: 15px;
		padding-bottom: 10px;
		border-bottom: 2px solid #1d1d1d;
	}
	.pricelist-dropzone {
		border: 2px dashed #b4c6dc;
		border-radius: 8px;
		background: #fbfcfe;
		transition: all .2s ease-in-out;
		cursor: pointer;
	}
	.pricelist-dropzone:hover,
	.pricelist-dropzone.is-dragover {
		border-color: #2057a3 !important;
		background-color: #f0f6ff !important;
	}
	.border-light-subtle {
		border: 1px solid #e5e7eb !important;
	}
	.btn-primary {
		background-color: #2057a3 !important;
		border-color: #2057a3 !important;
	}
	.btn-outline-primary {
		color: #2057a3 !important;
		border-color: #2057a3 !important;
	}
	.btn-outline-primary:hover {
		background-color: #2057a3 !important;
		color: #fff !important;
	}

	/* Scoped Modal #add-modal untuk 3 kolom & sticky footer sebatas layar */
	#add-modal .modal-dialog {
		max-height: calc(100vh - 2.5rem);
		margin: 1.25rem auto;
		display: flex;
		align-items: center;
	}
	#add-modal .modal-content {
		max-height: calc(100vh - 2.5rem);
		display: flex;
		flex-direction: column;
		overflow: hidden;
		border-radius: 8px;
		width: 100%;
	}
	#add-modal .modal-header {
		flex-shrink: 0;
	}
	#add-modal .modal-body {
		overflow-y: auto;
		max-height: calc(100vh - 160px);
		padding: 1.25rem;
	}
	#add-modal .modal-footer {
		position: sticky;
		bottom: 0;
		z-index: 10;
		background-color: #ffffff;
		border-top: 1px solid #ebe9f1;
		box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.05);
		flex-shrink: 0;
	}
	#add-modal .ql-container {
		font-family: inherit;
		font-size: 0.9rem;
	}
	#add-modal .ql-editor {
		min-height: 160px;
	}
	#add-modal .ql-toolbar.ql-snow {
		border-top-left-radius: 6px;
		border-top-right-radius: 6px;
		border-color: #d8dde3;
	}
	#add-modal .ql-container.ql-snow {
		border-bottom-left-radius: 6px;
		border-bottom-right-radius: 6px;
		border-color: #d8dde3;
	}

	/* Styling DataTable agar rata kiri & memenuhi layar proporsional */
	#data_table,
	#data_table th,
	#data_table td,
	.dataTables_wrapper .dataTable th,
	.dataTables_wrapper .dataTable td,
	.dataTables_scrollHead th,
	.dataTables_scrollBody td {
		text-align: left !important;
	}
	#data_table td {
		vertical-align: middle;
	}
	.dataTables_scrollBody {
		border-bottom: 1px solid #ebe9f1;
	}
	#basic-datatable .card {
		margin-bottom: 1.25rem !important;
	}
</style>
<div class="app-content content ">
	<div class="content-overlay"></div>
	<div class="header-navbar-shadow"></div>
	<section id="basic-datatable">
		<div class="row">
			<div class="col-12">
				<div class="card">
					<div class="card-header border-bottom d-flex flex-wrap justify-content-between align-items-center">
						<h4 class="card-title mb-1 mb-md-0">Halaman Pricelist</h4>
						<div class="d-flex align-items-center" style="gap: 10px;">
							<div style="min-width: 150px;">
								<select id="filter-is_active" name="filter-is_active" class="select2 form-control">
									<option value=1>Aktif</option>
									<option value=0>Tidak Aktif</option>
								</select>
							</div>
							<button type="button" id="btn_draw" class="btn btn-outline-primary waves-effect">Filter</button>
							<button type="button" class="btn btn-primary btn-sm text-nowrap" onclick="add()" title="Add"> <i class="fa fa-plus"></i> Tambah Data</button>
						</div>
					</div>
					<div class="col-12">
						<div class="card-datatable">
							<table id="data_table" class="datatables-basic table">
								<thead>
									<tr>
										<th>No</th>
										<th>Aksi</th>
										<th>Tanggal (Subsidi)</th>
										<th>Row</th>
										<th>Tipe (LB/LT)</th>
										<th>Harga (Jual/Net/KPR/UM)</th>
										<th>Biaya (Admin/BPHTB/PPn/Proses)</th>
										<th>Keterangan</th>
										<th>Status</th>
										<th>Ditambah</th>
										<th>Diubah</th>
									</tr>
								</thead>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- Modal to add new record -->
		<div class="modal fade" id="add-modal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
			<div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
				<form id="add-form" enctype="multipart/form-data" class="add-new-record modal-content border-0 shadow-lg">
					<div class="modal-header bg-white border-bottom py-1">
						<h5 class="modal-title font-weight-bolder text-primary d-flex align-items-center" id="exampleModalLabel">
							<i class="fas fa-tags mr-50"></i>
							<span id="modal-title-text">Form Harga Jual</span>
						</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body p-2" style="background-color: #f8f9fa;">
						<input type="hidden" id="id" name="id">

						<div class="row">
							<!-- KOLOM 1: IDENTITAS PROYEK & SPESIFIKASI -->
							<div class="col-lg-4 col-md-12 mb-1 mb-lg-0 d-flex flex-column">
								<div class="divider divider-left mb-1">
									<div class="divider-text font-weight-bold">
										<i class="fas fa-building mr-50 text-primary"></i>1. Identitas & Spesifikasi
									</div>
								</div>
								<div class="card border mb-0 shadow-none flex-grow-1" style="border-radius: 8px; border-color: #e5e7eb !important;">
									<div class="card-body p-2 d-flex flex-column justify-content-between">
										<div class="row">
											<div class="col-12 form-group mb-1">
												<label class="font-weight-bold font-small-3" for="idProyek">Proyek <span class="text-danger">*</span></label>
												<select required id="idProyek" name="idProyek" class="custom-select select2 w-100">
													<?php
													foreach ($proyek as $p) {
														echo "<option value='$p->id_proyek'>$p->nama_proyek</option>";
													}
													?>
												</select>
											</div>
											<div class="col-sm-6 form-group mb-1">
												<label class="font-weight-bold font-small-3" for="tgl_harga">Tanggal Pricelist <span class="text-danger">*</span></label>
												<input required type="text" id="tgl_harga" name="tgl_harga"
													class="form-control flatpickr-human-friendly bg-white" placeholder="Pilih Tanggal" />
											</div>
											<div class="col-sm-6 form-group mb-1">
												<label class="font-weight-bold font-small-3" for="is_subsidi">Kategori Rumah <span class="text-danger">*</span></label>
												<select required id="is_subsidi" name="is_subsidi" class="custom-select form-control">
													<option value="1">Subsidi</option>
													<option value="0">Non-Subsidi</option>
												</select>
											</div>
											<div class="col-sm-6 form-group mb-1">
												<label class="font-weight-bold font-small-3" for="row">Row (Jalan) <span class="text-danger">*</span></label>
												<input required type="text" id="row" name="row" class="form-control" placeholder="Contoh: 6 atau 7" maxlength="50">
											</div>
											<div class="col-sm-6 form-group mb-1">
												<label class="font-weight-bold font-small-3" for="id_tipe">Tipe Rumah <span class="text-danger">*</span></label>
												<input required type="text" id="id_tipe" name="id_tipe" class="form-control" placeholder="Contoh: 22/60, 36/72" maxlength="100">
											</div>
											<div class="col-sm-6 form-group mb-0">
												<label class="font-weight-bold font-small-3" for="lb">Luas Bangunan (m²) <span class="text-danger">*</span></label>
												<input required type="text" id="lb" name="lb" class="form-control" placeholder="Contoh: 22 atau 36" maxlength="50">
											</div>
											<div class="col-sm-6 form-group mb-0">
												<label class="font-weight-bold font-small-3" for="lt">Luas Tanah (m²) <span class="text-danger">*</span></label>
												<input required type="text" id="lt" name="lt" class="form-control" placeholder="Contoh: 60 atau 72" maxlength="50">
											</div>
										</div>
									</div>
								</div>
							</div>

							<!-- KOLOM 2: RINCIAN HARGA & PEMBIAYAAN -->
							<div class="col-lg-4 col-md-12 mb-1 mb-lg-0 d-flex flex-column">
								<div class="divider divider-left mb-1">
									<div class="divider-text font-weight-bold">
										<i class="fas fa-money-bill-wave mr-50 text-primary"></i>2. Rincian Harga
									</div>
								</div>
								<div class="card border mb-0 shadow-none flex-grow-1" style="border-radius: 8px; border-color: #e5e7eb !important;">
									<div class="card-body p-2">
										<div class="row">
											<div class="col-12 form-group mb-1">
												<label class="font-weight-bold font-small-3" for="hargajual">Harga Jual <span class="text-danger">*</span></label>
												<div class="input-group">
													<div class="input-group-prepend"><span class="input-group-text font-small-2">Rp</span></div>
													<input required type="text" id="hargajual" name="hargajual" class="form-control num font-weight-bold" placeholder="0">
												</div>
											</div>
											<div class="col-12 form-group mb-1">
												<div class="d-flex justify-content-between align-items-center">
													<label class="font-weight-bold font-small-3" for="hargajual_net">Harga Jual Net <span class="text-danger">*</span></label>
													<a href="#" id="btn-sync-net" class="font-small-1 text-primary font-weight-bold" title="Samakan dengan Harga Jual"><i class="fas fa-sync-alt mr-25"></i>Samakan</a>
												</div>
												<div class="input-group">
													<div class="input-group-prepend"><span class="input-group-text font-small-2">Rp</span></div>
													<input required type="text" id="hargajual_net" name="hargajual_net" class="form-control num font-weight-bold" placeholder="0">
												</div>
												<small id="hint-hargajual_net" class="d-block text-muted font-small-1 mt-25"><i class="fas fa-info-circle text-primary mr-25"></i> Tipe Subsidi: otomatis sama dengan Harga Jual.</small>
											</div>
											<div class="col-sm-6 form-group mb-1">
												<label class="font-weight-bold font-small-3" for="kpr">Plafon KPR <span class="text-danger">*</span></label>
												<div class="input-group">
													<div class="input-group-prepend"><span class="input-group-text font-small-2">Rp</span></div>
													<input required type="text" id="kpr" name="kpr" class="form-control num" placeholder="0">
												</div>
											</div>
											<div class="col-sm-6 form-group mb-1">
												<label class="font-weight-bold font-small-3" for="uang_muka">Uang Muka (UM) <span class="text-danger">*</span></label>
												<div class="input-group">
													<div class="input-group-prepend"><span class="input-group-text font-small-2">Rp</span></div>
													<input required type="text" id="uang_muka" name="uang_muka" class="form-control num" placeholder="0">
												</div>
												<small class="text-muted font-small-1 mt-25 d-block">Otomatis = Harga Jual - KPR</small>
											</div>

											<div class="col-sm-6 form-group mb-1">
												<label class="font-weight-bold font-small-3" for="biaya_adm">Biaya Administrasi</label>
												<div class="input-group">
													<div class="input-group-prepend"><span class="input-group-text font-small-2">Rp</span></div>
													<input required type="text" id="biaya_adm" name="biaya_adm" class="form-control num" placeholder="0">
												</div>
											</div>
											<div class="col-sm-6 form-group mb-1">
												<label class="font-weight-bold font-small-3" for="bphtb">BPHTB</label>
												<div class="input-group">
													<div class="input-group-prepend"><span class="input-group-text font-small-2">Rp</span></div>
													<input required type="text" id="bphtb" name="bphtb" class="form-control num" placeholder="0">
												</div>
											</div>
											<div class="col-sm-6 form-group mb-1">
												<label class="font-weight-bold font-small-3" for="ppn">PPn</label>
												<div class="input-group">
													<div class="input-group-prepend"><span class="input-group-text font-small-2">Rp</span></div>
													<input required type="text" id="ppn" name="ppn" class="form-control num" placeholder="0">
												</div>
											</div>
											<div class="col-sm-6 form-group mb-1">
												<label class="font-weight-bold font-small-3" for="biaya_proses">Biaya Proses</label>
												<div class="input-group">
													<div class="input-group-prepend"><span class="input-group-text font-small-2">Rp</span></div>
													<input required type="text" id="biaya_proses" name="biaya_proses" class="form-control num" placeholder="0">
												</div>
											</div>

											<!-- Total summary strip -->
											<div class="col-12 mt-25">
												<div class="d-flex align-items-center justify-content-between p-1 rounded bg-light border border-light-subtle">
													<div class="d-flex align-items-center">
														<i class="fas fa-calculator text-primary fa-lg mr-50"></i>
														<div>
															<span class="font-weight-bolder text-dark font-small-3 d-block">Estimasi Total Tagihan</span>
															<small class="text-muted font-small-1">UM + BPHTB + Admin + Proses</small>
														</div>
													</div>
													<div class="text-right">
														<span id="display-total-tagihan" class="badge badge-light-primary font-small-3 font-weight-bolder px-75 py-50">Rp 0</span>
														<input type="hidden" id="total" name="total" value="0">
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>

							<!-- KOLOM 3: KETERANGAN/CATATAN LEBIH BESAR & FILE UPLOAD -->
							<div class="col-lg-4 col-md-12 d-flex flex-column">
								<div class="divider divider-left mb-1">
									<div class="divider-text font-weight-bold">
										<i class="fas fa-file-alt mr-50 text-primary"></i>3. Catatan & Lampiran
									</div>
								</div>
								<div class="card border mb-0 shadow-none flex-grow-1" style="border-radius: 8px; border-color: #e5e7eb !important;">
									<div class="card-body p-2 d-flex flex-column justify-content-between">
										<!-- Keterangan Editor Lebih Besar -->
										<div class="form-group mb-1">
											<label class="font-weight-bold font-small-3 mb-50 d-flex align-items-center justify-content-between">
												<span><i class="fas fa-sticky-note mr-25 text-primary"></i> Keterangan / Catatan Tambahan</span>
												<small class="text-muted font-small-1 font-weight-normal">Rich Text Editor</small>
											</label>
											<input type="hidden" name="keterangan" id="keterangan">
											<div id="keterangan-editor" style="height: 200px; background-color: #fff; border-radius: 0 0 6px 6px;"></div>
										</div>

										<!-- Berkas Lampiran PDF Dropzone -->
										<div class="form-group mb-0">
											<label class="font-weight-bold font-small-3 mb-50 d-flex align-items-center justify-content-between">
												<span><i class="fas fa-file-pdf mr-25 text-danger"></i> Berkas Lampiran Pricelist (.PDF)</span>
												<small class="text-muted font-small-1 font-weight-normal">Maks 10MB</small>
											</label>

											<!-- File tersimpan saat ini (jika ada) -->
											<div id="existing-file-container" class="alert alert-secondary d-none p-75 mb-75 d-flex flex-wrap align-items-center justify-content-between">
												<div class="d-flex align-items-center mb-50 mb-sm-0">
													<i class="fas fa-file-pdf text-danger fa-lg mr-50"></i>
													<div>
														<span id="existing-file-name" class="font-small-2 text-dark font-weight-bold text-truncate d-inline-block" style="max-width: 170px;"></span>
													</div>
												</div>
												<div class="d-flex align-items-center">
													<a href="#" target="_blank" id="existing-file-btn" class="btn btn-xs btn-outline-primary mr-25 waves-effect">
														<i class="fas fa-external-link-alt"></i>
													</a>
													<button type="button" id="btn-remove-existing-file" class="btn btn-xs btn-outline-danger waves-effect" title="Tandai hapus">
														<i class="fas fa-trash-alt"></i>
													</button>
												</div>
											</div>

											<!-- Dropzone Area -->
											<div id="dropzone-filehj" class="pricelist-dropzone p-1 text-center border-dashed rounded bg-white cursor-pointer position-relative">
												<input type="file" id="id_filehj" name="id_filehj" accept="application/pdf" class="d-none">
												<input type="hidden" id="remove_filehj" name="remove_filehj" value="0">

												<div id="dropzone-prompt" class="py-50">
													<i class="fas fa-cloud-upload-alt text-primary fa-lg mb-25"></i>
													<p class="mb-25 font-weight-bold text-dark font-small-2">Tarik & Lepaskan berkas PDF ke sini, atau <span class="text-primary text-decoration-underline">Pilih Berkas</span></p>
													<small class="text-muted d-block font-small-1">
														<i class="fas fa-paste mr-25"></i> Bisa paste clipboard (<strong>Ctrl + V</strong>) &bull; Format <strong>.PDF</strong> (Maks 10MB)
													</small>
												</div>

												<div id="dropzone-preview" class="d-none py-25">
													<div class="d-inline-flex align-items-center bg-light border rounded px-75 py-25">
														<i class="fas fa-file-pdf text-danger fa-lg mr-50"></i>
														<div class="text-left mr-50">
															<span id="preview-filename" class="d-block font-weight-bolder text-dark font-small-2 text-truncate" style="max-width: 170px;"></span>
															<span id="preview-filesize" class="badge badge-light-primary font-small-1"></span>
														</div>
														<button type="button" id="btn-cancel-file" class="btn btn-icon btn-xs btn-outline-danger rounded-circle waves-effect" title="Batalkan berkas">
															<i class="fas fa-times"></i>
														</button>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>

					</div>
					<div class="modal-footer bg-white border-top py-1">
						<button type="button" class="btn btn-outline-secondary waves-effect" data-dismiss="modal">
							<i class="fas fa-times mr-25"></i> Batal
						</button>
						<button type="submit" class="btn btn-primary waves-effect waves-float waves-light" id="add-form-btn">
							<i class="fas fa-save mr-25"></i> Simpan Data
						</button>
					</div>
				</form>
			</div>
		</div>

	</section>
</div>
</div>
</div>

<!-- BEGIN: Page Vendor JS-->
<script src="<?= base_url() ?>app-assets/vendors/js/vendors.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/tables/datatable/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/tables/datatable/datatables.bootstrap4.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/tables/datatable/dataTables.responsive.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/tables/datatable/responsive.bootstrap4.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/tables/datatable/datatables.buttons.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/tables/datatable/dataTables.rowGroup.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/pickers/flatpickr/flatpickr.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/forms/validation/jquery.validate.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/extensions/sweetalert2.all.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/extensions/polyfill.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/forms/select/select2.full.min.js"></script>
<!-- Quill JS -->
<script src="<?= base_url() ?>app-assets/vendors/js/editors/quill/quill.min.js"></script>
<!-- <script src="https://adminlte.io/themes/v3/plugins/jquery-validation/additional-methods.min.js"></script> -->
<!-- END: Page Vendor JS-->
<script>
	function getTableScrollY() {
		var windowHeight = $(window).height();
		// Offset 350px agar tinggi table proporsional dan batas bawah card sejajar harmonis dengan sidebar
		var offset = 350;
		var h = windowHeight - offset;
		return h > 280 ? h + "px" : "280px";
	}

	$(function () {
		var table = $('#data_table').DataTable({
			scrollY: getTableScrollY(),
			scrollX: true,
			scrollCollapse: true,
			fixedColumns: {
				leftColumns: 5
			},
			columnDefs: [
				{ targets: '_all', className: 'text-left' }
			],
			paging: true,
			// columnDefs: [{
			// targets: [1],
			// visible: false
			// }],
			lengthChange: true,
			searching: true,
			ordering: false,
			info: true,
			autoWidth: true,
			responsive: false,
			processing: true,
			serverSide: true,
			ajax: {
				url: '<?php echo base_url($controller . '/get/all') ?>',
				type: "POST",
				dataType: "json",
				data: {
					[csrfName]: csrfHash
				},
				data: function (data) {
					data[csrfName] = csrfHash
					data.id_proyek = activeProyekId()
					data.is_active = $("#filter-is_active").val()
				},
				dataSrc: function (r) {
					csrfHash = r.token
					return r.data;
				},
				async: "true"
			}
		}),
			fp = flatpickr(".flatpickr-human-friendly", {
				altInput: true,
				altFormat: 'F j, Y',
				dateFormat: 'Y-m-d'
			});

		// $("#idProyek").select2();
		//on chnage search
		$(".dataTables_filter input")
			.off()
			.on('change', function (e) {
				table.search(this.value).draw();
			});

		$("#idProyek").select2({
			placeholder: "Pilih Proyek",
			allowClear: true,
			dropdownParent: $('#add-modal')
		});

		if (activeProyekId()) {
			table.draw();
		}

		//on click btn filter
		// $("#id_tipe").select2({
		// 	placeholder: "Pilih Tipe",
		// 	allowClear: true,
		// 	ajax: {
		// 		url: base_url + "tipe/get/all",
		// 		dataType: 'json',
		// 		delay: 250,
		// 		method: 'post',
		// 		data: function (params) {
		// 			return {
		// 				[csrfName]: csrfHash,
		// 				search: params.term,
		// 				id_proyek: $("#idProyek").val()
		// 			};
		// 		},
		// 		processResults: function (r) {
		// 			csrfHash = r.token

		// 			// let results = [];
		// 			let results = r.data.map(item => ({
		// 				id: item[0],
		// 				text: item[3] + " - " + item[2],
		// 				bold: item[3],     // simpan untuk dipakai template
		// 				label: item[2]
		// 			}));

		// 			return { results };
		// 		},
		// 		cache: true
		// 	},

		// 	// 🔥 Izinkan HTML
		// 	escapeMarkup: function (markup) { return markup; },

		// 	// 🔥 Render opsi dropdown
		// 	templateResult: function (data) {
		// 		if (!data.id) return data.text;
		// 		return `
		// 	<span><strong>${data.bold}</strong> - ${data.label}</span>
		// `;
		// 	},

		// 	// 🔥 Render teks yang tampil di box terpilih
		// 	templateSelection: function (data) {
		// 		if (!data.id) return data.text;
		// 		return `<strong>${data.bold}</strong> - ${data.label}`;
		// 	}
		// })

		//on click btn filter
		$("#btn_draw").on("click", function (e) {
			table.draw();
		});

		// Dynamic table height on resize
		$(window).on('resize', function () {
			var newH = getTableScrollY();
			$('.dataTables_scrollBody').css('max-height', newH);
			table.columns.adjust();
		});
		//remove bug arrow select2
		$(".select2-selection__arrow").css("pointer-events", "none")

		// Initialize Quill Editor
		quill = new Quill('#keterangan-editor', {
			theme: 'snow',
			modules: {
				toolbar: [
					['bold', 'italic', 'underline', 'strike'],
					[{ 'list': 'ordered'}, { 'list': 'bullet' }],
					['clean']
				]
			}
		});

		// Update hidden input before submit
		quill.on('text-change', function() {
			$('#keterangan').val(quill.root.innerHTML);
		});
	});


	// Helper untuk Drag & Drop dan Clipboard Paste pada Berkas Lampiran PDF
	function setupDropzoneAndPaste() {
		const $zone = $('#dropzone-filehj');
		const $input = $('#id_filehj');
		const $prompt = $('#dropzone-prompt');
		const $preview = $('#dropzone-preview');
		const $previewName = $('#preview-filename');
		const $previewSize = $('#preview-filesize');
		const $modal = $('#add-modal');

		function formatSize(bytes) {
			if (!bytes || bytes <= 0) return '0 B';
			if (bytes < 1024) return bytes + ' B';
			if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
			return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
		}

		function setStagedFile(file) {
			if (!file) {
				$input.val('');
				$preview.addClass('d-none');
				$prompt.removeClass('d-none');
				return false;
			}

			const isPdf = (file.type && file.type === 'application/pdf') || (file.name && file.name.toLowerCase().endsWith('.pdf'));
			if (!isPdf) {
				Swal.fire({
					icon: 'warning',
					title: 'Format Tidak Sesuai',
					text: 'Hanya berkas berformat PDF (.pdf) yang diperbolehkan.',
					confirmButtonColor: '#2057a3'
				});
				return false;
			}

			if (file.size > 10 * 1024 * 1024) {
				Swal.fire({
					icon: 'warning',
					title: 'Ukuran Terlalu Besar',
					text: 'Ukuran berkas PDF maksimal adalah 10 MB.',
					confirmButtonColor: '#2057a3'
				});
				return false;
			}

			$previewName.text(file.name);
			$previewSize.text(formatSize(file.size));
			$prompt.addClass('d-none');
			$preview.removeClass('d-none');
			return true;
		}

		// Klik untuk memilih berkas
		$zone.off('click').on('click', function (e) {
			if ($(e.target).closest('#btn-cancel-file').length) return;
			$input.trigger('click');
		});

		$input.off('change').on('change', function () {
			if (this.files && this.files[0]) {
				if (!setStagedFile(this.files[0])) {
					$input.val('');
				}
			}
		});

		$('#btn-cancel-file').off('click').on('click', function (e) {
			e.stopPropagation();
			setStagedFile(null);
		});

		// Drag & drop events
		$zone.off('dragover dragenter').on('dragover dragenter', function (e) {
			e.preventDefault();
			e.stopPropagation();
			$zone.addClass('is-dragover');
		});

		$zone.off('dragleave dragend').on('dragleave dragend', function (e) {
			e.preventDefault();
			e.stopPropagation();
			$zone.removeClass('is-dragover');
		});

		$zone.off('drop').on('drop', function (e) {
			e.preventDefault();
			e.stopPropagation();
			$zone.removeClass('is-dragover');

			const dt = e.originalEvent && e.originalEvent.dataTransfer;
			if (dt && dt.files && dt.files.length > 0) {
				const droppedFile = dt.files[0];
				if (setStagedFile(droppedFile)) {
					const dataTransfer = new DataTransfer();
					dataTransfer.items.add(droppedFile);
					$input[0].files = dataTransfer.files;
				}
			}
		});

		// Tangkap clipboard paste pada modal (Ctrl + V)
		$modal.off('paste').on('paste', function (e) {
			const clipboard = (e.originalEvent && e.originalEvent.clipboardData) || e.clipboardData;
			if (!clipboard || !clipboard.items) return;

			let foundPdf = false;
			let foundOtherFile = false;

			for (let i = 0; i < clipboard.items.length; i++) {
				const item = clipboard.items[i];
				if (item.kind === 'file') {
					const blob = item.getAsFile();
					if (blob) {
						const isPdf = (blob.type && blob.type === 'application/pdf') || (blob.name && blob.name.toLowerCase().endsWith('.pdf'));
						if (isPdf) {
							foundPdf = true;
							const filename = (blob.name && blob.name !== 'blob') ? blob.name : ('pricelist_pasted_' + Date.now() + '.pdf');
							const pdfFile = new File([blob], filename, { type: 'application/pdf' });
							const dataTransfer = new DataTransfer();
							dataTransfer.items.add(pdfFile);
							$input[0].files = dataTransfer.files;
							setStagedFile(pdfFile);

							Swal.fire({
								toast: true,
								position: 'top-end',
								icon: 'success',
								title: 'Berkas PDF dari clipboard berhasil dilampirkan',
								showConfirmButton: false,
								timer: 2000
							});
							e.preventDefault();
							break;
						} else {
							foundOtherFile = true;
						}
					}
				}
			}

			if (!foundPdf && foundOtherFile) {
				Swal.fire({
					icon: 'warning',
					title: 'Format Tidak Sesuai',
					text: 'Hanya berkas berformat PDF yang dapat dilampirkan.',
					confirmButtonColor: '#2057a3'
				});
				e.preventDefault();
			}
		});

		// Hapus berkas lama
		$('#btn-remove-existing-file').off('click').on('click', function (e) {
			e.preventDefault();
			$('#remove_filehj').val('1');
			$('#existing-file-container').addClass('d-none');
			Swal.fire({
				toast: true,
				position: 'top-end',
				icon: 'info',
				title: 'Berkas lama akan dihapus saat disimpan',
				showConfirmButton: false,
				timer: 2500
			});
		});
	}

	function syncHargaNet() {
		const isSubsidi = $('#is_subsidi').val() === '1';
		const hj = parseFloat(removeComma($('#add-form #hargajual').val())) || 0;
		if (isSubsidi) {
			$('#add-form #hargajual_net').val(num_format(hj)).trigger('keyup');
			$('#hint-hargajual_net').html('<i class="fas fa-info-circle text-primary mr-25"></i> Tipe Subsidi: Harga Jual Net otomatis sama dengan Harga Jual.');
		} else {
			$('#hint-hargajual_net').html('<i class="fas fa-edit text-muted mr-25"></i> Tipe Non-Subsidi: Isi net secara spesifik atau klik tombol Samakan.');
		}
	}

	$('#is_subsidi').on('change', function () {
		syncHargaNet();
	});

	$('#add-form #hargajual, #add-form #kpr').on('input change keyup', function () {
		let hj = parseFloat(removeComma($('#add-form #hargajual').val())) || 0,
			kpr = parseFloat(removeComma($('#add-form #kpr').val())) || 0;

		$('#add-form #uang_muka').val(num_format(hj - kpr)).trigger('keyup');

		const currentNet = parseFloat(removeComma($('#add-form #hargajual_net').val())) || 0;
		if ($('#is_subsidi').val() === '1' || currentNet === 0) {
			$('#add-form #hargajual_net').val(num_format(hj)).trigger('keyup');
		}

		load_total();
	});

	$('#btn-sync-net').on('click', function (e) {
		e.preventDefault();
		let hj = parseFloat(removeComma($('#add-form #hargajual').val())) || 0;
		$('#add-form #hargajual_net').val(num_format(hj)).trigger('keyup');
	});

	$('#add-form .num').on('input change keyup', function () {
		load_total();
	});

	function load_total() {
		let tot = 0,
			bphtb = parseFloat(removeComma($('#add-form #bphtb').val())) || 0,
			um = parseFloat(removeComma($('#add-form #uang_muka').val())) || 0,
			ba = parseFloat(removeComma($('#add-form #biaya_adm').val())) || 0,
			bp = parseFloat(removeComma($('#add-form #biaya_proses').val())) || 0;

		tot = um + bphtb + ba + bp;

		$('#add-form #total').val(num_format(tot)).trigger('keyup');
		$('#display-total-tagihan').text('Rp ' + num_format(tot));
	}

	var url = '';

	function initFormValidation() {
		$.validator.setDefaults({
			highlight: function (element) {
				$(element).addClass('is-invalid').removeClass('is-valid');
			},
			unhighlight: function (element) {
				$(element).removeClass('is-invalid').addClass('is-valid');
			},
			errorElement: 'div',
			errorClass: 'invalid-feedback',
			errorPlacement: function (error, element) {
				if (element.parent('.input-group').length) {
					error.insertAfter(element.parent());
				} else if ($(element).is('.select')) {
					element.next().after(error);
				} else if (element.hasClass('select2')) {
					error.insertAfter(element.next());
				} else if (element.hasClass('selectpicker')) {
					error.insertAfter(element.next());
				} else {
					error.insertAfter(element);
				}
			},
			submitHandler: function (form) {
				// Pastikan keterangan tersinkron dari quill
				if (typeof quill !== 'undefined') {
					$('#keterangan').val(quill.root.innerHTML);
				}

				var formEl = $('#add-form')[0];
				var fd = new FormData(formEl);
				fd.append(csrfName, csrfHash);

				$('.text-danger.validation-error').remove();

				$.ajax({
					url: url,
					type: 'post',
					contentType: false,
					processData: false,
					data: fd,
					dataType: 'json',
					beforeSend: function () {
						$('#add-form-btn').prop('disabled', true);
						$('#add-form-btn').html('<i class="fa fa-spinner fa-spin mr-25"></i> Menyimpan...');
					},
					success: function (response) {
						csrfHash = response.token;

						if (response.success === true) {
							Swal.fire({
								icon: 'success',
								title: response.messages,
								showConfirmButton: false,
								timer: 1500
							}).then(function () {
								$('#data_table').DataTable().ajax.reload(null, false).draw(false);
								// Tandai tutup form terkonfirmasi agar tidak memicu initModalListener
								$('#add-modal').data('isConfirmedClose', true).modal('hide');
							});
						} else {
							if (response.messages instanceof Object) {
								$.each(response.messages, function (index, value) {
									var id = $('#' + index);
									id.closest('.form-control')
										.removeClass('is-invalid')
										.removeClass('is-valid')
										.addClass(value.length > 0 ? 'is-invalid' : 'is-valid');
									id.after('<div class="invalid-feedback validation-error">' + value + '</div>');
								});
							} else {
								Swal.fire({
									icon: 'error',
									title: 'Gagal Menyimpan',
									text: response.messages,
									confirmButtonColor: '#2057a3'
								});
							}
						}
						$('#add-form-btn').prop('disabled', false);
						$('#add-form-btn').html('<i class="fas fa-save mr-25"></i> Simpan Data');
					},
					error: function () {
						Swal.fire({
							icon: 'error',
							title: 'Kesalahan Sistem',
							text: 'Terjadi kesalahan saat memproses data ke server.',
							confirmButtonColor: '#2057a3'
						});
						$('#add-form-btn').prop('disabled', false);
						$('#add-form-btn').html('<i class="fas fa-save mr-25"></i> Simpan Data');
					}
				});

				return false;
			}
		});

		$('#add-form').validate();
	}

	$(document).ready(function () {
		setupDropzoneAndPaste();
		initFormValidation();
	});

	function add() {
		url = base_url + '/hargajual/add';
		$('#add-form-btn').prop('disabled', false).html('<i class="fas fa-save mr-25"></i> Simpan Data');

		// Reset form
		$('#add-form')[0].reset();
		$('#id').val('');
		$('#remove_filehj').val('0');

		// Reset dropzone
		$('#id_filehj').val('');
		$('#dropzone-preview').addClass('d-none');
		$('#dropzone-prompt').removeClass('d-none');
		$('#existing-file-container').addClass('d-none');

		if (typeof quill !== 'undefined') {
			quill.root.innerHTML = '';
			$('#keterangan').val('');
		}

		$('#modal-title-text').text('Tambah Harga Jual / Pricelist');
		$('.form-control').removeClass('is-invalid').removeClass('is-valid');

		// Inisialisasi modal listener untuk konfirmasi batal
		if (typeof initModalListener === 'function') {
			initModalListener('#add-modal');
		}

		$('#add-modal').modal('show');

		if (activeProyekId()) {
			$('#add-form #idProyek').val(activeProyekId()).trigger('change');
		} else {
			$('#idProyek').val($('#idProyek option:first').val()).trigger('change');
		}

		$('#is_subsidi').val('1').trigger('change');
		load_total();
	}

	function edit(id_cluster) {
		url = base_url + '/hargajual/edit';
		$('#add-form-btn').prop('disabled', false).html('<i class="fas fa-save mr-25"></i> Simpan Data');

		$('#add-form')[0].reset();
		$('#remove_filehj').val('0');
		$('#id_filehj').val('');
		$('#dropzone-preview').addClass('d-none');
		$('#dropzone-prompt').removeClass('d-none');
		$('#existing-file-container').addClass('d-none');

		$.ajax({
			url: '<?php echo base_url($controller . '/getOne') ?>',
			type: 'post',
			data: {
				[csrfName]: csrfHash,
				id_cluster: id_cluster
			},
			dataType: 'json',
			success: function (r) {
				csrfHash = r.token;

				$('#modal-title-text').text('Edit Harga Jual / Pricelist');
				$('.form-control').removeClass('is-invalid').removeClass('is-valid');

				// Inisialisasi modal listener untuk konfirmasi batal
				if (typeof initModalListener === 'function') {
					initModalListener('#add-modal');
				}

				$('#add-modal').modal('show');

				$('#id').val(r.id);
				$('#row').val(r.row);
				$('#lb').val(r.lb);
				$('#lt').val(r.lt);
				$('#id_tipe').val(r.id_tipe);

				$('#hargajual').val(num_format(r.hargajual)).trigger('keyup');
				$('#hargajual_net').val(num_format(r.hargajual_net)).trigger('keyup');
				$('#kpr').val(num_format(r.kpr)).trigger('keyup');
				$('#ppn').val(num_format(r.ppn)).trigger('keyup');
				$('#uang_muka').val(num_format(r.uang_muka)).trigger('keyup');
				$('#bphtb').val(num_format(r.bphtb)).trigger('keyup');
				$('#biaya_adm').val(num_format(r.biaya_adm)).trigger('keyup');
				$('#biaya_proses').val(num_format(r.biaya_proses)).trigger('keyup');

				$('#keterangan').val(r.keterangan || '');
				if (typeof quill !== 'undefined') {
					quill.root.innerHTML = r.keterangan || '';
				}

				$('#is_subsidi').val(r.is_subsidi).trigger('change');

				// Integrasi berkas lampiran dengan FileAccessService gateway
				if (r.access_url) {
					$('#existing-file-container').removeClass('d-none');
					$('#existing-file-name').text(r.default_filename || r.file_name || 'Berkas Pricelist Tersimpan');
					$('#existing-file-btn').attr('href', r.access_url);
				} else {
					$('#existing-file-container').addClass('d-none');
				}

				load_total();

				// Gunakan pemilihan nilai langsung pada select2 yang sudah ada (mencegah opsi duplikat)
				$('#idProyek').val(r.id_proyek).trigger('change');

				if (r.tgl_harga && r.tgl_harga !== '0000-00-00') {
					const datePicker = document.querySelector('#tgl_harga');
					if (datePicker && datePicker._flatpickr) {
						datePicker._flatpickr.setDate(r.tgl_harga);
					}
				}
			},
			error: function () {
				Swal.fire({
					icon: 'error',
					title: 'Gagal Memuat Data',
					text: 'Tidak dapat mengambil rincian pricelist dari server.',
					confirmButtonColor: '#2057a3'
				});
			}
		});
	}

	function remove(id_cluster, st) {
		Swal.fire({
			title: 'Ubah status pricelist?',
			text: "Pricelist tidak akan terhapus, hanya jadi berubah status",
			icon: 'warning',
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: 'Confirm',
			cancelButtonText: 'Cancel'
		}).then((result) => {

			if (result.value) {
				$.ajax({
					url: '<?php echo base_url($controller . '/remove') ?>',
					type: 'post',
					data: {
						[csrfName]: csrfHash,
						id: id_cluster,
						is_active: st
					},
					dataType: 'json',
					success: function (response) {
						csrfHash = response.token;

						if (response.success === true) {
							Swal.fire({

								icon: 'success',
								title: response.messages,
								showConfirmButton: false,

							}).then(function () {
								$('#data_table').DataTable().ajax.reload(null, false).draw(false);
							})
						} else {
							Swal.fire({

								icon: 'error',
								title: response.messages,
								showConfirmButton: false,

							})
						}
					},
					error: function () {
						Swal.fire({

							icon: 'error',
							title: "Oops!! Terjadi kesalahan.",
							showConfirmButton: false,

						})
					}
				});
			}
		})
	}


	$('thead > tr> th:nth-child(1)').css({ 'min-width': '30px', 'max-width': '30px' });
	$('thead > tr> th:nth-child(2)').css({ 'min-width': '100px', 'max-width': '100px' });
	$('thead > tr> th:nth-child(3)').css({ 'min-width': '130px', 'max-width': '130px' });
	$('thead > tr> th:nth-child(4)').css({ 'min-width': '50px', 'max-width': '50px' });
	$('thead > tr> th:nth-child(5)').css({ 'min-width': '120px', 'max-width': '120px' });
	$('thead > tr> th:nth-child(6)').css({ 'min-width': '180px', 'max-width': '180px' });
	$('thead > tr> th:nth-child(7)').css({ 'min-width': '180px', 'max-width': '180px' });
	$('thead > tr> th:nth-child(8)').css({ 'min-width': '250px', 'max-width': '250px' });
	$('thead > tr> th:nth-child(9)').css({ 'min-width': '100px', 'max-width': '100px' });
	$('thead > tr> th:nth-child(10), thead > tr> th:nth-child(11)').css({ 'min-width': '150px', 'max-width': '150px' });
</script>
