<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/tables/datatable/dataTables.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/tables/datatable/responsive.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/tables/datatable/buttons.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/extensions/sweetalert2.min.css">
<style>
	.float {
		position: fixed;
		width: auto;
		height: auto;
		bottom: 40px;
		/* left:100px; */
		/* background-color:#fff; */
		/* border: 1px solid; */
		/* color:#FFF; */
		/* border-radius:5px; */
		text-align: center;
		/* box-shadow: 2px 2px 3px #999; */
		z-index: 9999;
		/* padding:0 10px 10px 10px; */
	}

	.dropzone {
		position: relative;
		border: 2px dashed #ced4da;
		border-radius: .75rem;
		background: #fff;
		transition: .15s border-color, .15s background;
	}

	.dropzone-lg {
		min-height: 90px;
	}

	.dropzone.is-dragover {
		border-color: #007bff;
		background: rgba(0, 123, 255, .05);
	}

	.dz-input {
		position: absolute;
		inset: 0;
		width: 100%;
		height: 100%;
		opacity: 0;
		cursor: pointer;
	}

	.dz-inner {
		height: 100%;
		display: flex;
		align-items: center;
		justify-content: center;
		text-align: center;
		padding: 1rem;
	}

	.dz-placeholder {
		pointer-events: none;
	}

	.dz-preview {
		display: none;
		height: 100%;
		max-width: 90px;
	}

	.dz-preview .thumb-wrap {
		position: relative;
		display: inline-block;
		border: 1px solid #dee2e6;
		border-radius: .5rem;
		overflow: hidden;
		max-width: 100%;
	}

	.dz-preview img {
		display: block;
		max-height: 90px;
		width: auto;
	}

	.dz-remove {
		position: absolute;
		top: .25rem;
		right: .25rem;
		border: 0;
		border-radius: .35rem;
		padding: .25rem .5rem;
	}

	.dz-fileinfo {
		margin-top: .5rem;
		font-size: .9rem;
	}
</style>
<script>
	// var csrfName = '<?= csrf_token() ?>';
	// var csrfHash = '<?= csrf_hash() ?>';
	// const base_url = '<?= base_url() ?>';
</script>

<div class="app-content content ">
	<div class="content-overlay"></div>
	<div class="header-navbar-shadow"></div>
	<section id="basic-datatable">
		<div class="row">
			<div class="col-12">
				<div class="card">
					<div class="card-header border-bottom">
						<button type="button" class="btn btn-primary data-submit  btn-sm mr-1 col-sm-12 col-md-3 col-lg-3" onclick="add()" title="Add"> <i class="fa fa-plus"></i> Tambah Data</button>
					</div>
					<div class="card-datatable">
						<table id="data_table" class="datatables-basic table">
							<thead>
								<tr>
									<th>Id proyek</th>
									<th>Nama proyek</th>
									<th>Alamat proyek</th>
									<th>Logo</th>

									<th></th>
								</tr>
							</thead>
						</table>
					</div>
				</div>
			</div>
		</div>
		<!-- Modal to add new record -->
		<div class="modal fade" id="add-modal">
			<div class="modal-dialog modal-dialog-scrollable modal-lg">
				<form id="add-form" enctype="multipart/form-data" class="add-new-record modal-content pt-0">
					<div class="modal-header mb-1">
						<h5 class="modal-title" id="exampleModalLabel">Tambah Data</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button>
					</div>
					<div class="modal-body flex-grow-1">
						<input type="hidden" id="idProyek" name="idProyek" class="form-control" placeholder="Id proyek" maxlength="255" required>
						<div class="row">
							<div class="col-md-7">
								<div class="divider divider-left">
									<div class="divider-text font-weight-bold">Data Proyek</div>
								</div>
								<div class="form-group">
									<label for="namaProyek"> Nama proyek: </label>
									<input type="text" id="namaProyek" name="namaProyek" class="form-control" placeholder="Nama proyek" maxlength="255">
								</div>
								<div class="form-group">
									<label for="alamatProyek"> Alamat proyek: </label>
									<input type="text" id="alamatProyek" name="alamatProyek" class="form-control" placeholder="Alamat proyek" maxlength="255">
								</div>
								<div class="form-group">
									<label for="kelurahanProyek"> Kelurahan proyek: </label>
									<input type="text" id="kelurahanProyek" name="kelurahanProyek" class="form-control" placeholder="Kelurahan proyek" maxlength="255">
								</div>
								<div class="form-group">
									<label for="kecamatanProyek"> Kecamatan proyek: </label>
									<input type="text" id="kecamatanProyek" name="kecamatanProyek" class="form-control" placeholder="Kecamatan proyek" maxlength="255">
								</div>
								<div class="form-group">
									<label for="kotaProyek"> Kota proyek: </label>
									<input type="text" id="kotaProyek" name="kotaProyek" class="form-control" placeholder="Kota proyek" maxlength="255">
								</div>
								<div class="form-group">
									<label for="provinsiProyek"> Provinsi proyek: </label>
									<input type="text" id="provinsiProyek" name="provinsiProyek" class="form-control" placeholder="Provinsi proyek" maxlength="255">
								</div>
								<div class="form-group">
									<label for="namaPt"> Nama PT: </label>
									<input type="text" id="namaPt" name="namaPt" class="form-control" placeholder="Nama PT" maxlength="255">
								</div>
								<div class="form-group">
									<label for="bank"> Bank: </label>
									<input type="text" id="bank" name="bank" class="form-control" placeholder="Bank" maxlength="255">
								</div>
								<div class="form-group">
									<label for="noRek"> No rekening: </label>
									<input type="text" id="noRek" name="noRek" class="form-control" placeholder="No rekening" maxlength="255">
								</div>
								<div class="form-group">
									<label for="atasNama"> Atas nama: </label>
									<input type="text" id="atasNama" name="atasNama" class="form-control" placeholder="Atas nama" maxlength="255">
								</div>
							</div>
							<div class="col-md-5">
								<div class="divider divider-left">
									<div class="divider-text font-weight-bold">Upload File</div>
								</div>
								<div class="form-group">
									<label class="font-weight-bold" for="add_siteplan">Siteplan</label>
									<div class="dropzone dropzone-lg custom-file">
										<input type="file" name="file" id="add_siteplan" accept="image/*" class="custom-file-input dz-input" />
										<div class="dz-inner">
											<div class="dz-preview" id="prev_add_siteplan"></div>
											<div class="dz-placeholder">
												<div class="h5 mb-1">Tarik & letakkan gambar</div>
												<div class="text-muted">atau klik untuk pilih file (maks 12 MB)</div>
											</div>
										</div>
									</div>
								</div>
								<div class="form-group">
									<label class="font-weight-bold" for="add_logo">Logo Proyek</label>
									<div class="dropzone dropzone-lg custom-file">
										<input type="file" name="logo" id="add_logo" accept="image/*" class="custom-file-input dz-input" />
										<div class="dz-inner">
											<div class="dz-preview" id="prev_add_logo"></div>
											<div class="dz-placeholder">
												<div class="h5 mb-1">Tarik & letakkan gambar</div>
												<div class="text-muted">atau klik untuk pilih file (maks 12 MB)</div>
											</div>
										</div>
									</div>
								</div>
								<div class="form-group">
									<label class="font-weight-bold" for="add_logo_pt">Logo PT</label>
									<div class="dropzone dropzone-lg custom-file">
										<input type="file" name="logo_pt" id="add_logo_pt" accept="image/*" class="custom-file-input dz-input" />
										<div class="dz-inner">
											<div class="dz-preview" id="prev_add_logo_pt"></div>
											<div class="dz-placeholder">
												<div class="h5 mb-1">Tarik & letakkan gambar</div>
												<div class="text-muted">atau klik untuk pilih file (opsional, maks 12 MB)</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<button type="submit" class="btn btn-primary data-submit mr-1" id="add-form-btn">Simpan</button>
						<button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>

					</div>
				</form>

			</div>
		</div>
		<!-- Modal to add new record -->
		<div class="modal fade" id="edit-modal">
			<div class="modal-dialog modal-dialog-scrollable modal-lg">
				<form id="edit-form" enctype="multipart/form-data" class="add-new-record modal-content pt-0">
					<div class="modal-header mb-1">
						<h5 class="modal-title" id="exampleModalLabel">Perbaharui Data</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button>
					</div>
					<div class="modal-body flex-grow-1">
						<ul class="nav nav-tabs" role="tablist">
							<li class="nav-item">
								<a class="nav-link active" id="data-proyek-tab" data-toggle="tab" href="#data-proyek" aria-controls="home" role="tab" aria-selected="true">Data Proyek</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" id="upload-siteplan-tab" data-toggle="tab" href="#upload-siteplan" aria-controls="home" role="tab" aria-selected="true">Riwayat Upload Siteplan</a>
							</li>
						</ul>
						<div class="tab-content">
							<div class="tab-pane active" id="data-proyek" aria-labelledby="data-tipe-proyek" role="tabpanel">
								<div class="row">
									<div class="col-md-7">
										<div class="divider divider-left">
											<div class="divider-text font-weight-bold">Data Proyek</div>
										</div>
										<div class="form-group">
											<label for="namaProyek"> Nama proyek: </label>
											<input type="text" id="namaProyek" name="namaProyek" class="form-control" placeholder="Nama proyek" maxlength="255">
										</div>
										<div class="form-group">
											<label for="alamatProyek"> Alamat proyek: </label>
											<input type="text" id="alamatProyek" name="alamatProyek" class="form-control" placeholder="Alamat proyek" maxlength="255">
										</div>
										<div class="form-group">
											<label for="kelurahanProyek"> Kelurahan proyek: </label>
											<input type="text" id="kelurahanProyek" name="kelurahanProyek" class="form-control" placeholder="Kelurahan proyek" maxlength="255">
										</div>
										<div class="form-group">
											<label for="kecamatanProyek"> Kecamatan proyek: </label>
											<input type="text" id="kecamatanProyek" name="kecamatanProyek" class="form-control" placeholder="Kecamatan proyek" maxlength="255">
										</div>
										<div class="form-group">
											<label for="kotaProyek"> Kota proyek: </label>
											<input type="text" id="kotaProyek" name="kotaProyek" class="form-control" placeholder="Kota proyek" maxlength="255">
										</div>
										<div class="form-group">
											<label for="provinsiProyek"> Provinsi proyek: </label>
											<input type="text" id="provinsiProyek" name="provinsiProyek" class="form-control" placeholder="Provinsi proyek" maxlength="255">
										</div>
										<div class="form-group">
											<label for="namaPt"> Nama PT: </label>
											<input type="text" id="namaPt" name="namaPt" class="form-control" placeholder="Nama PT" maxlength="255">
										</div>
										<div class="form-group">
											<label for="bank"> Bank: </label>
											<input type="text" id="bank" name="bank" class="form-control" placeholder="Bank" maxlength="255">
										</div>
										<div class="form-group">
											<label for="noRek"> No rekening: </label>
											<input type="text" id="noRek" name="noRek" class="form-control" placeholder="No rekening" maxlength="255">
										</div>
										<div class="form-group">
											<label for="atasNama"> Atas nama: </label>
											<input type="text" id="atasNama" name="atasNama" class="form-control" placeholder="Atas nama" maxlength="255">
										</div>
									</div>
									<div class="col-md-5">
										<div class="divider divider-left">
											<div class="divider-text font-weight-bold">Upload File</div>
										</div>
										<div class="form-group">
											<label class="font-weight-bold" for="edit_siteplan">Siteplan</label>
											<div class="dropzone dropzone-lg custom-file">
												<input type="file" name="file" id="edit_siteplan" accept="image/*" class="custom-file-input dz-input" />
												<div class="dz-inner">
													<div class="dz-preview" id="prev_edit_siteplan"></div>
													<div class="dz-placeholder">
														<div class="h5 mb-1">Tarik & letakkan gambar</div>
														<div class="text-muted">atau klik untuk pilih file (maks 12 MB)</div>
													</div>
												</div>
											</div>
											<input type="hidden" name="no_up" id="no_up" />
											<input type="hidden" id="siteplan" name="siteplan" class="form-control" maxlength="255">
											<a id="link_siteplan" href="javascript:void(0)" target="_blank" class="btn btn-outline-primary btn-block waves-effect mt-1">Lihat / Unduh Siteplan</a>
										</div>
										<div class="form-group">
											<label class="font-weight-bold" for="edit_logo">Logo Proyek</label>
											<div class="dropzone dropzone-lg custom-file">
												<input type="file" name="logon" id="edit_logo" accept="image/*" class="custom-file-input dz-input" />
												<div class="dz-inner">
													<div class="dz-preview" id="prev_edit_logo"></div>
													<div class="dz-placeholder">
														<div class="h5 mb-1">Tarik & letakkan gambar</div>
														<div class="text-muted">atau klik untuk pilih file (maks 12 MB)</div>
													</div>
												</div>
											</div>
											<input type="hidden" name="no_up_logo" id="no_up_logo" />
											<input type="hidden" id="logo" name="logo" class="form-control" maxlength="255">
											<a id="link_logo" href="javascript:void(0)" target="_blank" class="btn btn-outline-primary btn-block waves-effect mt-1">Lihat / Unduh Logo Proyek</a>
										</div>
										<div class="form-group">
											<label class="font-weight-bold" for="edit_logo_pt">Logo PT</label>
											<div class="dropzone dropzone-lg custom-file">
												<input type="file" name="logo_pt" id="edit_logo_pt" accept="image/*" class="custom-file-input dz-input" />
												<div class="dz-inner">
													<div class="dz-preview" id="prev_edit_logo_pt"></div>
													<div class="dz-placeholder">
														<div class="h5 mb-1">Tarik & letakkan gambar</div>
														<div class="text-muted">atau klik untuk pilih file (opsional, maks 12 MB)</div>
													</div>
												</div>
											</div>
											<input type="hidden" name="no_up_logo_pt" id="no_up_logo_pt" />
											<input type="hidden" id="logo_pt_old" name="logo_pt_old" class="form-control" maxlength="255">
											<a id="link_logo_pt" href="javascript:void(0)" target="_blank" class="btn btn-outline-primary btn-block waves-effect mt-1">Lihat / Unduh Logo PT</a>
										</div>
									</div>
								</div>

								<button type="submit" class="btn btn-primary data-submit mr-1" id="edit-form-btn">Simpan</button>
								<button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
							</div>
							<div class="tab-pane" id="upload-siteplan" aria-labelledby="upload-siteplan-tab" role="tabpanel">
								<div class="table-responsive">
									<table class="table mb-0">
										<thead>
											<tr>
												<th scope="col" class="text-nowrap">No</th>
												<th scope="col" class="text-nowrap">Nama File</th>
												<th scope="col" class="text-nowrap">Panjang</th>
												<th scope="col" class="text-nowrap">Lebar</th>
												<th scope="col" class="text-nowrap">Tipe File</th>
												<th scope="col" class="text-nowrap">Link</th>
												<th scope="col" class="text-nowrap">Oleh</th>
												<th scope="col" class="text-nowrap">Tanggal Upload</th>
											</tr>
										</thead>
										<tbody id="tb-upload_siteplan">
											<tr>
												<td colspan="8" class="text-center">Tidak ada data</td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>
						</div>

						<input type="hidden" id="idProyek" name="idProyek" class="form-control" placeholder="Id proyek" maxlength="255" required>
					</div>
				</form>
			</div>
		</div>
	</section>
</div>
</div>
</div>

<!-- BEGIN: Page Vendor JS-->
<script src="<?= base_url() ?>/app-assets/vendors/js/vendors.min.js"></script>
<script src="<?= base_url() ?>/app-assets/vendors/js/tables/datatable/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>/app-assets/vendors/js/tables/datatable/datatables.bootstrap4.min.js"></script>
<script src="<?= base_url() ?>/app-assets/vendors/js/tables/datatable/dataTables.responsive.min.js"></script>
<script src="<?= base_url() ?>/app-assets/vendors/js/tables/datatable/responsive.bootstrap4.js"></script>
<script src="<?= base_url() ?>/app-assets/vendors/js/tables/datatable/datatables.buttons.min.js"></script>
<script src="<?= base_url() ?>/app-assets/vendors/js/tables/datatable/dataTables.rowGroup.min.js"></script>
<script src="<?= base_url() ?>/app-assets/vendors/js/pickers/flatpickr/flatpickr.min.js"></script>
<script src="<?= base_url() ?>/app-assets/vendors/js/forms/validation/jquery.validate.min.js"></script>
<script src="<?= base_url() ?>/app-assets/vendors/js/extensions/sweetalert2.all.min.js"></script>
<script src="<?= base_url() ?>/app-assets/vendors/js/extensions/polyfill.min.js"></script>
<!-- <script src="https://adminlte.io/themes/v3/plugins/jquery-validation/additional-methods.min.js"></script> -->
<!-- END: Page Vendor JS-->
<script>
	$(function() {
		$('#data_table').DataTable({
			"paging": true,
			"lengthChange": false,
			"searching": true,
			"ordering": true,
			"info": true,
			"autoWidth": false,
			"responsive": true,
			"ajax": {
				"url": '<?php echo base_url($controller . '/getAll') ?>',
				"type": "POST",
				"dataType": "json",
				data: {
					[csrfName]: csrfHash
				},
				data: function(data) {
					data[csrfName] = csrfHash
				},
				dataSrc: function(r) {
					csrfHash = r.token
					return r.data;
				},
				async: "true"
			}
		});

		initProyekUploadInputs();
	});

	const proyekUploadInputs = [
		'add_siteplan', 'add_logo', 'add_logo_pt',
		'edit_siteplan', 'edit_logo', 'edit_logo_pt',
	];

	function initProyekUploadInputs() {
		proyekUploadInputs.forEach(function(inputId) {
			const input = document.getElementById(inputId);
			if (!input || input.dataset.dropzoneLoaded === '1') return;
			load_dropzone(inputId);
			input.dataset.dropzoneLoaded = '1';
		});
	}

	function resetUploadPreviews(formSelector) {
		$(formSelector).find('.dz-preview').html('').hide();
		$(formSelector).find('.dz-placeholder').show();
	}

	function renderExistingUploadPreview(inputId, src, label) {
		const input = document.getElementById(inputId);
		if (!input) return;

		const dropzone = input.closest('.dropzone');
		if (!dropzone) return;

		const preview = dropzone.querySelector('.dz-preview');
		const placeholder = dropzone.querySelector('.dz-placeholder');
		if (!preview || !placeholder) return;

		if (!src) {
			preview.innerHTML = '';
			preview.style.display = 'none';
			placeholder.style.display = 'block';
			return;
		}

		preview.innerHTML = `<img src="${escapeAttribute(src)}" class="preview-thumb" alt="${escapeAttribute(label)}">
			<div class="text-truncate mb-1">${escapeHtml(label)} sudah diunggah</div>`;
		preview.style.display = 'block';
		placeholder.style.display = 'none';
	}

	function setFileAction(selector, url) {
		if (url) {
			$(selector).attr('href', url).removeClass('disabled');
		} else {
			$(selector).attr('href', 'javascript:void(0)').addClass('disabled');
		}
	}

	function escapeHtml(value) {
		return String(value == null ? '' : value)
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;')
			.replace(/'/g, '&#039;');
	}

	function escapeAttribute(value) {
		return escapeHtml(value).replace(/`/g, '&#096;');
	}

	function add() {
		// reset the form
		$("#add-form")[0].reset();
		$(".form-control").removeClass('is-invalid').removeClass('is-valid');
		resetUploadPreviews('#add-form');
		$('#add-modal').modal('show');

		// submit the add from 
		$.validator.setDefaults({
			highlight: function(element) {
				$(element).addClass('is-invalid').removeClass('is-valid');
			},
			unhighlight: function(element) {
				$(element).removeClass('is-invalid').addClass('is-valid');
			},
			errorElement: 'div ',
			errorClass: 'invalid-feedback',
			errorPlacement: function(error, element) {
				if (element.parent('.input-group').length) {
					error.insertAfter(element.parent());
				} else if ($(element).is('.select')) {
					element.next().after(error);
				} else if (element.hasClass('select2')) {
					//error.insertAfter(element);
					error.insertAfter(element.next());
				} else if (element.hasClass('selectpicker')) {
					error.insertAfter(element.next());
				} else {
					error.insertAfter(element);
				}
			},

			submitHandler: function(form) {
				// var files = $('#file')[0].files;
				var form = $('#add-form')[0];
				var fd = new FormData(form);

				fd.append(csrfName, csrfHash);

				// remove the text-danger
				$(".text-danger").remove();

				$.ajax({
					url: '<?php echo base_url($controller . '/add') ?>',
					type: 'POST',
					contentType: false,
					processData: false,
					data: fd, // /converting the form data into array and sending it to server
					// dataType: 'json',
					beforeSend: function() {
						$('#add-form-btn').html('<i class="fa fa-spinner fa-spin"></i> Menyimpan');
						$('#add-form-btn').prop('disabled', true);
					},
					success: function(response) {
						csrfHash = response.token;

						if (response.success === true) {

							Swal.fire({

								icon: 'success',
								title: response.messages,
								showConfirmButton: false,

							}).then(function() {
								$('#data_table').DataTable().ajax.reload(null, false).draw(false);
								$('#add-modal').modal('hide');
							})

						} else {

							if (response.messages instanceof Object) {
								$.each(response.messages, function(index, value) {
									var id = $("#" + index);

									id.closest('.form-control')
										.removeClass('is-invalid')
										.removeClass('is-valid')
										.addClass(value.length > 0 ? 'is-invalid' : 'is-valid');

									id.after(value);

								});
							} else {
								Swal.fire({

									icon: 'error',
									title: response.messages,
									showConfirmButton: false,

								})

							}
						}
						$('#add-form-btn').html('Simpan');
						$('#add-form-btn').prop('disabled', false);
					},
					error: function(request, error) {
						Swal.fire({
							icon: 'error',
							title: error,
							showConfirmButton: false,
						})
						$('#add-form-btn').html('Simpan');
						$('#add-form-btn').prop('disabled', false);
					}
				});

				return false;
			}
		});
		$('#add-form').validate();
	}

	function edit(id_proyek) {
		$.ajax({
			url: '<?php echo base_url($controller . '/getOne') ?>',
			type: 'post',
			data: {
				[csrfName]: csrfHash,
				id_proyek: id_proyek
			},
			dataType: 'json',
			success: function(response) {
				csrfHash = response.token;
				// reset the form
				$("#edit-form")[0].reset();
				$(".form-control").removeClass('is-invalid').removeClass('is-valid');
				resetUploadPreviews('#edit-form');
				$('#edit-modal').modal('show');

				$("#edit-form #idProyek").val(response.id_proyek);
				$("#edit-form #namaProyek").val(response.nama_proyek);
				$("#edit-form #alamatProyek").val(response.alamat_proyek);
				$("#edit-form #kelurahanProyek").val(response.kelurahan);
				$("#edit-form #kecamatanProyek").val(response.kecamatan);
				$("#edit-form #kotaProyek").val(response.kota);
				$("#edit-form #provinsiProyek").val(response.provinsi);
				$("#edit-form #namaPt").val(response.nama_pt);
				$("#edit-form #bank").val(response.bank);
				$("#edit-form #noRek").val(response.no_rek);
				$("#edit-form #atasNama").val(response.atas_nama);
				$("#edit-form #siteplan").val(response.siteplan);
				$("#edit-form #logo").val(response.logo);
				$("#edit-form #logo_pt_old").val(response.logo_pt);

				const siteplanUrl = response.siteplan ? (response.siteplan_access_url || file_url('proyek_siteplan', response.id_proyek)) : '';
				const logoUrl = response.logo ? (response.logo_access_url || file_url('proyek_logo', response.id_proyek)) : '';
				const logoPtUrl = response.logo_pt ? (response.logo_pt_access_url || file_url('proyek_logo_pt', response.id_proyek)) : '';

				setFileAction('#link_siteplan', siteplanUrl);
				setFileAction('#link_logo', logoUrl);
				setFileAction('#link_logo_pt', logoPtUrl);

				renderExistingUploadPreview('edit_siteplan', siteplanUrl, 'Siteplan');
				renderExistingUploadPreview('edit_logo', logoUrl, 'Logo Proyek');
				renderExistingUploadPreview('edit_logo_pt', logoPtUrl, 'Logo PT');

				//set riwayat upload siteplan
				$("#tb-upload_siteplan").html("");
				let tb = `<tr><td colspan="6" class="text-center">Tidak ada data</td></tr>`
				if (response.list_siteplan.length > 0) {
					tb = '';
					let no = 0
					$.each(response.list_siteplan, function(i, v) {
						no++
						tb += `<tr>
                      <td>` + no + `</td>
                      <td>` + v.file_name + `</td>
                      <td>` + v.width + ` px</td>
                      <td>` + v.height + ` px</td>
                      <td>` + v.file_type + `</td>
                      <td> <a href='` + (v.access_url || file_url('siteplan_upload', v.id)) + `' target=blank>Klik disini untuk mengunduh</a></td>
                      <td> ` + v.uadd_by + `</td>
                      <td> ` + format_datetime(v.upload_at) + ` </td>
                    </tr>`
					});
				}
				$("#tb-upload_siteplan").html(tb);

				// submit the edit from 
				$.validator.setDefaults({
					highlight: function(element) {
						$(element).addClass('is-invalid').removeClass('is-valid');
					},
					unhighlight: function(element) {
						$(element).removeClass('is-invalid').addClass('is-valid');
					},
					errorElement: 'div ',
					errorClass: 'invalid-feedback',
					errorPlacement: function(error, element) {
						if (element.parent('.input-group').length) {
							error.insertAfter(element.parent());
						} else if ($(element).is('.select')) {
							element.next().after(error);
						} else if (element.hasClass('select2')) {
							//error.insertAfter(element);
							error.insertAfter(element.next());
						} else if (element.hasClass('selectpicker')) {
							error.insertAfter(element.next());
						} else {
							error.insertAfter(element);
						}
					},

					submitHandler: function(form) {
						var files = $('#edit-form #edit_siteplan')[0].files,
							logo = $('#edit-form #edit_logo')[0].files,
							logoPt = $('#edit-form #edit_logo_pt')[0].files,
							form = $('#edit-form')[0];

						if (files.length == 0)
							$("#no_up").val(1)
						else
							$("#no_up").val(0)

						if (logo.length == 0)
							$("#no_up_logo").val(1)
						else
							$("#no_up_logo").val(0)

						if (logoPt.length == 0)
							$("#no_up_logo_pt").val(1)
						else
							$("#no_up_logo_pt").val(0)

						var fd = new FormData(form);

						fd.append(csrfName, csrfHash);


						$(".text-danger").remove();
						$.ajax({
							url: '<?php echo base_url($controller . '/edit') ?>',
							type: 'POST',
							contentType: false,
							processData: false,
							data: fd, // /converting the form data into array and sending it to server
							beforeSend: function() {
								$('#edit-form-btn').html('<i class="fa fa-spinner fa-spin"></i> Menyimpan');
								$('#edit-form-btn').prop('disabled', true);
							},
							success: function(response) {
								csrfHash = response.token;

								if (response.success === true) {

									Swal.fire({

										icon: 'success',
										title: response.messages,
										showConfirmButton: false,

									}).then(function() {
										$('#data_table').DataTable().ajax.reload(null, false).draw(false);
										$('#edit-modal').modal('hide');
									})

								} else {

									if (response.messages instanceof Object) {
										$.each(response.messages, function(index, value) {
											var id = $("#" + index);

											id.closest('.form-control')
												.removeClass('is-invalid')
												.removeClass('is-valid')
												.addClass(value.length > 0 ? 'is-invalid' : 'is-valid');

											id.after(value);

										});
									} else {
										Swal.fire({
											icon: 'error',
											title: response.messages,
											showConfirmButton: false,
										})

									}
								}
								$('#edit-form-btn').html('Simpan');
								$('#edit-form-btn').prop('disabled', false);
							},
							error: function(request, error) {
								Swal.fire({
									icon: 'error',
									title: error,
									showConfirmButton: false,
								})
								$('#edit-form-btn').html('Simpan');
								$('#edit-form-btn').prop('disabled', false);
							}
						});

						return false;
					}
				});
				$('#edit-form').validate();

			}
		});
	}

	function remove(id_proyek) {
		Swal.fire({
			title: 'Are you sure of the deleting process?',
			text: "You cannot back after confirmation",
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
						id_proyek: id_proyek
					},
					dataType: 'json',
					success: function(response) {
						csrfHash = response.token;
						if (response.success === true) {
							Swal.fire({

								icon: 'success',
								title: response.messages,
								showConfirmButton: false,

							}).then(function() {
								$('#data_table').DataTable().ajax.reload(null, false).draw(false);
							})
						} else {
							Swal.fire({

								icon: 'error',
								title: response.messages,
								showConfirmButton: false,

							})


						}
					}
				});
			}
		})
	}
</script>
