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

	.proyek-preview {
		display: none;
		margin-top: .75rem;
		padding: .5rem;
		border: 1px solid #ebe9f1;
		border-radius: .25rem;
		background: #f8f8f8;
	}

	.proyek-preview img {
		display: block;
		width: 100%;
		max-height: 220px;
		object-fit: contain;
		background: #fff;
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
		<div class="modal modal-slide-in fade" id="add-modal">
			<div class="modal-dialog sidebar-sm">
				<form id="add-form" enctype="multipart/form-data" class="add-new-record modal-content pt-0">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button>
					<div class="modal-header mb-1">
						<h5 class="modal-title" id="exampleModalLabel">Tambah Data</h5>
					</div>
					<div class="modal-body flex-grow-1">
						<input type="hidden" id="idProyek" name="idProyek" class="form-control" placeholder="Id proyek" maxlength="255" required>
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
							<label for="siteplan"> Siteplan: </label>
							<input type="file" name="file" id="file" />
						</div>
						<div class="form-group">
							<label for="logo">Logo: </label>
							<input type="file" name="logo" id="logo" />
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
									<label for="siteplan"> Siteplan: </label>
									<input type="file" name="file" id="file" />
									<input type="hidden" name="no_up" id="no_up" />
									<input type="hidden" id="siteplan" name="siteplan" class="form-control" placeholder="Siteplan" maxlength="255">
									<div class="proyek-preview" id="preview-siteplan-wrapper">
										<img id="preview-siteplan" alt="Preview Siteplan">
									</div>
									<a href="" id="link-siteplan" target=_blank><button id="view_siteplan" type="button" class="btn btn-outline-primary btn-block waves-effect">Lihat Siteplan</button></a>

								</div>
								<div class="form-group">
									<label for="siteplan"> Logo: </label>
									<input type="file" name="logon" id="logon" />
									<input type="hidden" name="no_up_logo" id="no_up_logo" />
									<input type="hidden" id="logo" name="logo" class="form-control" placeholder="Logo" maxlength="255">
									<div class="proyek-preview" id="preview-logo-wrapper">
										<img id="preview-logo" alt="Preview Logo">
									</div>
									<a href="" id="link-logo" target=_blank><button id="view_logo" type="button" class="btn btn-outline-primary btn-block waves-effect">Lihat Logo</button></a>
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

		$('#edit-form #file').on('change', function() {
			previewSelectedImage(this, '#preview-siteplan', '#preview-siteplan-wrapper');
		});

		$('#edit-form #logon').on('change', function() {
			previewSelectedImage(this, '#preview-logo', '#preview-logo-wrapper');
		});
	});

	const proyekPreviewUrls = {};

	function setImagePreview(previewSelector, wrapperSelector, src) {
		const previewKey = previewSelector.replace('#', '');
		if (proyekPreviewUrls[previewKey]) {
			URL.revokeObjectURL(proyekPreviewUrls[previewKey]);
			delete proyekPreviewUrls[previewKey];
		}

		if (!src) {
			$(previewSelector).removeAttr('src');
			$(wrapperSelector).hide();
			return;
		}

		$(previewSelector).attr('src', src);
		$(wrapperSelector).show();
	}

	function previewSelectedImage(input, previewSelector, wrapperSelector) {
		const file = input.files && input.files[0] ? input.files[0] : null;
		if (!file || !file.type.match(/^image\//)) {
			return;
		}

		const previewKey = previewSelector.replace('#', '');
		const objectUrl = URL.createObjectURL(file);
		setImagePreview(previewSelector, wrapperSelector, objectUrl);
		proyekPreviewUrls[previewKey] = objectUrl;
	}

	function add() {
		// reset the form 
		$("#add-form")[0].reset();
		$(".form-control").removeClass('is-invalid').removeClass('is-valid');
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
				$('#edit-modal').modal('show');

				$("#edit-form #idProyek").val(response.id_proyek);
				$("#edit-form #namaProyek").val(response.nama_proyek);
				$("#edit-form #alamatProyek").val(response.alamat_proyek);
				$("#edit-form #kelurahanProyek").val(response.kelurahan);
				$("#edit-form #kecamatanProyek").val(response.kecamatan);
				$("#edit-form #kotaProyek").val(response.kota);
				$("#edit-form #provinsiProyek").val(response.provinsi);
				$("#edit-form #siteplan").val(response.siteplan);
				$("#edit-form #logo").val(response.logo);

				const siteplanUrl = response.siteplan ? (response.siteplan_access_url || file_url('proyek_siteplan', response.id_proyek)) : '';
				const logoUrl = response.logo ? (response.logo_access_url || file_url('proyek_logo', response.id_proyek)) : '';
				$("#link-siteplan").prop("href", siteplanUrl || '#')
				$("#link-logo").prop("href", logoUrl || '#')
				$("#view_siteplan").prop("disabled", !siteplanUrl)
				$("#view_logo").prop("disabled", !logoUrl)
				setImagePreview('#preview-siteplan', '#preview-siteplan-wrapper', siteplanUrl);
				setImagePreview('#preview-logo', '#preview-logo-wrapper', logoUrl);

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
						var files = $('#edit-form #file')[0].files,
							logo = $('#edit-form #logon')[0].files,
							form = $('#edit-form')[0];

						if (files.length == 0)
							$("#no_up").val(1)
						else
							$("#no_up").val(0)

						if (logo.length == 0)
							$("#no_up_logo").val(1)
						else
							$("#no_up_logo").val(0)

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
