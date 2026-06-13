<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/tables/datatable/dataTables.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/tables/datatable/dataTables.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/tables/datatable/responsive.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/tables/datatable/buttons.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/extensions/sweetalert2.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/coloris/coloris.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/forms/select/select2.min.css">

<script>
	// var csrfName = '<?= csrf_token() ?>',
	// csrfHash = '<?= csrf_hash() ?>',
	// base_url = '<?= base_url() ?>'
</script>
<style>
	#clr-picker {
		z-index: 3000 !important;
	}
</style>
<div class="app-content content ">
	<div class="content-overlay"></div>
	<div class="header-navbar-shadow"></div>
	<section id="basic-datatable">
		<div class="row">
			<div class="col-12">
				<div class="card">
					<div class="card-header border-bottom">

						<!-- <button type="button" id="btn_draw" class="btn btn-outline-primary waves-effect btn-sm">Filter Data</button> -->

						<!-- <button type="button" class="btn btn-primary data-submit  btn-sm mr-1 col-sm-12 col-md-3 col-lg-3" onclick="add()" title="Add"> <i class="fa fa-plus"></i> Tambah Data</button> -->
					</div>
					<div class="card-datatable">
						<table id="data_table" class="table table-bordered table-striped">
							<thead>
								<tr>
									<th>Proyek</th>
									<th>Akses Pengguna</th>
									<th></th>
								</tr>
							</thead>
						</table>
					</div>
				</div>
			</div>
		</div>

		<!-- Add modal content -->
		<div id="edit-modal" class="modal modal-slide-in fade" role="dialog" aria-hidden="true">
			<div class="modal-dialog sidebar-sm">
				<div class="modal-content pt-0">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button>
					<div class="modal-header mb-1">
						<h5 class="modal-title" id="exampleModalLabel">Perbaharui Data</h5>
					</div>
					<div class="modal-body flex-grow-1">
						<form id="edit-form" class="">

							<div class="form-group">
								<label for="fill"> Nama Proyek</label>
								<input type="hidden" readonly id="id_proyek" name="id_proyek" class="form-control" placeholder="Item" required>
								<input type="text" readonly id="nama_proyek" name="nama_proyek" class="form-control" placeholder="Nama Proyek" required>
							</div>
							<div class="form-group">
								<label for="fill"> Username: <span class="text-danger">*</span> </label> <Br>
								<select multiple="multiple" name="id_users[]" id="id_users" class="form-control form-select"></select>
							</div>
							<div class="form-group text-center">
								<div class="btn-group">
									<button type="submit" class="btn btn-primary" id="edit-form-btn">Simpan</button>
									<button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button>
								</div>
							</div>
						</form>

					</div>
				</div><!-- /.modal-content -->
			</div><!-- /.modal-dialog -->
		</div><!-- /.modal -->
		<!-- /.content -->
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
<script src="<?= base_url() ?>/app-assets/vendors/js/forms/select/select2.full.min.js"></script>
<script src="<?= base_url() ?>/app-assets/vendors/js/coloris/coloris.js"></script>
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

		$("#id_users").select2({
			placeholder: "Pilih Pengguna",
			allowClear: true,
			ajax: {
				url: base_url + "getAkases",
				dataType: 'json',
				delay: 250,
				method: 'post',
				data: function(params) {
					return {
						[csrfName]: csrfHash,
						search: params.term,
					};
				},
				processResults: function(r) {
					csrfHash = r.token

					// console.log(r)

					let results = [];
					$.each(r.data, function(k, v) {
						results.push({
							id: v.id,
							text: v.username
						});
					});

					return {
						results: results
					};
				},
				cache: false
			},
		})


	});
// let pasldk = 0;
	function setValId(selectedValues) {
		// console.log(selectedValues)
		$('#id_users').val(null).trigger('change');
			// Pastikan tampilan Select2 terupdate
			$('#id_users').trigger('change');

		if (selectedValues.length == 0)
			return;
		$.ajax({
			url: base_url + "getAkases",
			dataType: 'json',
			type: 'post',
			data: {
				[csrfName]: csrfHash
			},
			success: function(r) {
				let data = r.data
				// Tambahkan opsi ke Select2 (jika belum ada)`
					var option = '';
				selectedValues.forEach(value => {
					var exists = data.find(item => item.id === value.id);
					if (exists) {
						// console.log(exists.id)
						// $('#id_users').val([exists.id]).trigger('change');
						option += `<option value=${exists.id} selected>${exists.username}</option>`;
					}
				});
					// console.log(option)
					$('#id_users').html(option).trigger('change');
			}
		});
	}

	function edit(id) {
		$.ajax({ 
			url: '<?php echo base_url($controller . '/getOne') ?>',
			type: 'post',
			data: {
				[csrfName]: csrfHash,
				id_proyek: id
			},
			dataType: 'json',
			success: function(response) {
				csrfHash = response.token;
				// reset the form 


				$("#edit-form")[0].reset();
				$(".form-control").removeClass('is-invalid').removeClass('is-valid');
				$('#edit-modal').modal('show');

				$("#nama_proyek").val(response.nama_proyek);
				$("#id_proyek").val(response.id_proyek);

				// console.log(response.id_users)

        let id_users = response.id_users ? response.id_users.split(',').map(id => ({
          id: id.trim()
        })) : [];
        setValId(id_users)



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
						var form = $('#edit-form');
						$(".text-danger").remove();
						$.ajax({
							url: '<?php echo base_url($controller . '/edit') ?>',
							type: 'post',
							data: form.serialize() + "&" + csrfName + "=" + csrfHash,
							dataType: 'json',
							beforeSend: function() {
								$('#edit-form-btn').html('<i class="fa fa-spinner fa-spin"></i>');
							},
							success: function(response) {

								if (response.success === true) {

									Swal.fire({
										position: 'bottom-end',
										icon: 'success',
										title: response.messages,
										showConfirmButton: false,
										timer: 1500
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
											position: 'bottom-end',
											icon: 'error',
											title: response.messages,
											showConfirmButton: false,
											timer: 1500
										})

									}
								}
								$('#edit-form-btn').html('Update');
							}
						});

						return false;
					}
				});
				$('#edit-form').validate();

			}
		});
	}

	function remove(config_name) {
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
						config_name: config_name,
						[csrfName]: csrfHash
					},
					data: function(data) {
						data[csrfName] = csrfHash
					},
					dataSrc: function(r) {
						csrfHash = r.token
						return r.data;
					},
					async: "true",
					dataType: 'json',
					success: function(response) {

						if (response.success === true) {
							Swal.fire({
								position: 'bottom-end',
								icon: 'success',
								title: response.messages,
								showConfirmButton: false,
								timer: 1500
							}).then(function() {
								$('#data_table').DataTable().ajax.reload(null, false).draw(false);
							})
						} else {
							Swal.fire({
								position: 'bottom-end',
								icon: 'error',
								title: response.messages,
								showConfirmButton: false,
								timer: 1500
							})


						}
					}
				});
			}
		})
	}

	//######################################

	//on chnage search
	$(".dataTables_filter input")
		.off()
		.on('change', function(e) {
			table.search(this.value).draw();
		});



	//on click btn filter
	$("#btn_draw").on("click", function(e) {
		table.draw();
	})

	//remove bug arrow select2
	$(".select2-selection__arrow").css("pointer-events", "none")
</script>
