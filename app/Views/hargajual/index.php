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
</style>
<div class="app-content content ">
	<div class="content-overlay"></div>
	<div class="header-navbar-shadow"></div>
	<section id="basic-datatable">
		<div class="row">
			<div class="col-12">
				<div class="card">
					<div class="card-header border-bottom">
						<div class="col-md-4 mb-1">
							<!-- <label>Pilih Proyek </label> -->
							<select id="id_proyek" name="id_proyek" class="select2 form-control"></select>
						</div>
						<div class="col-md-4 mb-1 ">
							<select id="filter-is_active" name="filter-is_active" class="select2 form-control">
								<option value=1>Aktif</option>
								<option value=0>Tidak Aktif</option>
							</select>
						</div>
						<div class="col-md-4 mb-1">
							<button type="button" id="btn_draw" class="btn btn-outline-primary waves-effect ">Filter
								Data</button>
						</div>

						<hr class="col-12" />

						<div class="col-md-3 mb-1">
						</div>
						<div class="col-md-6 mb-1">
							<button type="button"
								class="float-right btn btn-primary data-submit  btn-sm mr-1 col-sm-12 col-md-3 col-lg-3"
								onclick="add()" title="Add"> <i class="fa fa-plus"></i> Tambah Data</button>
						</div>
					</div>
					<div class="col-12">
						<div class="card-datatable">
							<table id="data_table" class="datatables-basic table">
								<thead>
									<tr>
										<th>No</th>
										<th>Proyek</th>
										<th>Tanggal</th>
										<th>Row</th>
										<th>Tipe</th>
										<th>LB</th>
										<th>LT</th>
										<th>Harga Jual</th>
										<th>Harga Jual Net</th>
										<th>KPR</th>
										<th>UM</th>
										<th>Biaya Admin</th>
										<th>BPHTB</th>
										<th>PPn</th>
										<th>Biaya Proses</th>
										<!-- <th>Total</th> -->
										<th>Subsidi</th>
										<th>Keterangan</th>
										<th>Status</th>
										<th>Ditambah</th>
										<th>Pada</th>
										<th>Diubah</th>
										<th>Pada</th>
										<th></th>
									</tr>
								</thead>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- Modal to add new record -->
		<div class="modal fade" id="add-modal">
			<div class="modal-dialog modal-lg modal-dialog-scrollable">
				<form id="add-form" enctype="multipart/form-data" class="add-new-record modal-content pt-0 form-submit">
					<div class="modal-header mb-1">
						<h5 class="modal-title" id="exampleModalLabel">Harga Jual</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button>
					</div>
					<div class="modal-body flex-grow-1" style="background-color:#eee">
						<div class="row">
							<input type="hidden" id="id" name="id" class="form-control">
						</div>
						<div class="card">
							<div class="card-header section-title mb-0">Pilih Proyek</div>
							<div class="card-body">
								<div class="form-group">
									<label for="idProyek"> Proyek: </label>
									<select required id="idProyek" name="idProyek" class="custom-select select2">
										<?php
										foreach ($proyek as $p) {
											echo "<option value='$p->id_proyek'>$p->nama_proyek</option>";
										}
										?>
									</select>
								</div>
								<div class="form-group">
									<label>File Pricelist</label>
									<div class="custom-file">
										<input type="file" class="custom-file-input" accept="application/pdf"
											name="id_filehj" id="id_filehj" />
										<label class="custom-file-label" id="label-id_filehj"
											for="label-id_filehj">Upload Pricelist</label>
										<a href="" target=_blank id="list-id_filehj">Klik untuk lihat file</a>
									</div>
								</div>
							</div>
						</div>
						<div class="card">
							<div class="card-header section-title mb-0">Tipe</div>
							<div class="card-body">
								<div class="row">
									<div class="col-md-6">
										<div class="form-group">
											<label for="tgl_harga">Tanggal Pricelist</label>
											<input required type="text" id="tgl_harga" name="tgl_harga"
												class="form-control flatpickr-human-friendly" placeholder="Tanggal Pricelist" />
										</div>
										<div class="form-group">
											<label for="namaCluster"> Row </label>
											<input required type="text" id="row" name="row" class="form-control"
												placeholder="ROW" maxlength="255">
										</div>
										<div class="form-group">
											<label for="diskon"> Tipe </label>
											<input required type="text" id="id_tipe" name="id_tipe" class="form-control"
												placeholder="Isi dengan tipe, misal :22/60" maxlength="255">
										</div>
									</div>
									<div class="col-md-6">
										<div class="form-group">
											<label for="insentif">Luas Bangunan</label>
											<input required type="text" id="lb" name="lb" class="form-control"
												placeholder="Luas Bangungan" maxlength="255">
										</div>
										<div class="form-group">
											<label for="insentif">Luas Tanah</label>
											<input required type="text" id="lt" name="lt" class="form-control"
												placeholder="Luas Tanah" maxlength="255">
										</div>
										<div class="form-group">
											<label for="is_subsidi"> Subsidi/Non Subsidi </label>
											<select required id="is_subsidi" name="is_subsidi"
												class="custom-select form-control">
												<option value="1">Subsidi</option>
												<option value="0">Non-Subsidi</option>
											</select>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="card">
						<div class="card-header section-title mb-0">Detail Harga Jual</div>
							<div class="card-body">
								<div class="row">
									<div class="col-md-6">
										<div class="form-group">
											<label for="insentif">Harga Jual</label>
											<input required type="text" id="hargajual" name="hargajual"
												class="form-control num" placeholder="Harga Jual" maxlength="255">
										</div>
										<div class="form-group">
											<label for="insentif">Harga Jual Net</label>
											<input required type="text" id="hargajual_net" name="hargajual_net"
												class="form-control num" placeholder="Harga Jual Net" maxlength="255">
										</div>
										<div class="form-group">
											<label for="insentif">KPR</label>
											<input required type="text" id="kpr" name="kpr" class="form-control num"
												placeholder="KPR" maxlength="255">
										</div>
										<div class="form-group">
											<label for="insentif">Uang Muka</label>
											<input required type="text" id="uang_muka" name="uang_muka"
												class="form-control num" placeholder="Uang Muka" maxlength="255">
										</div>


									</div>
									<div class="col-md-6">
										<div class="form-group">
											<label for="insentif">Biaya Admin</label>
											<input required type="text" id="biaya_adm" name="biaya_adm"
												class="form-control num" placeholder="Biaya Administrasi"
												maxlength="255">
										</div>
										<div class="form-group">
											<label for="insentif">BPHTB</label>
											<input required type="text" id="bphtb" name="bphtb" class="form-control num"
												placeholder="BPHTB" maxlength="255">
										</div>
										<div class="form-group">
											<label for="insentif">PPn</label>
											<input required type="text" id="ppn" name="ppn" class="form-control num"
												placeholder="ppn" maxlength="255">
										</div>

										<div class="form-group">
											<label for="insentif">Biaya Proses</label>
											<input required type="text" id="biaya_proses" name="biaya_proses"
												class="form-control num" placeholder="Biaya Proses" maxlength="255">
										</div>
									</div>
								</div>
							</div>
						</div>

						<div class="card">
							<div class="card-body">
								<div class="row">
									<div class="col-md-12">
										<div class="form-group hidden">
											<label for="insentif">Total</label>
											<input required type="text" id="total" name="total" readonly
												class="form-control num" placeholder="Total" maxlength="255">
										</div>
										<div class="form-group">
											<label for="keterangan">Keterangan</label>
											<textarea id="keterangan" name="keterangan" class="form-control"
												placeholder="Keterangan" rows="3"></textarea>
										</div>
									</div>
								</div>
							</div>
						</div>




					</div>
					<div class="modal-footer">
						<button type="submit" class="btn btn-primary data-submit mr-1" id="add-form-btn">Simpan</button>
						<button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
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
<!-- <script src="https://adminlte.io/themes/v3/plugins/jquery-validation/additional-methods.min.js"></script> -->
<!-- END: Page Vendor JS-->
<script>
	$(function () {
		var table = $('#data_table').DataTable({
			scrollY: "50vh",
			scrollX: true,
			scrollCollapse: true,
			fixedColumns: true,
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
					data.id_proyek = $("#id_proyek").val()
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

		//select2 proyek
		$("#id_proyek, #idProyek").select2({
			placeholder: "Pilih Proyek",
			allowClear: true,
			ajax: {
				url: base_url + "proyek/get/all",
				dataType: 'json',
				delay: 250,
				method: 'post',
				data: function (params) {
					return {
						[csrfName]: csrfHash,
						search: params.term
					};
				},
				processResults: function (r) {
					csrfHash = r.token

					let results = [];
					$.each(r.data, function (index, item) {
						results.push({
							id: item[0],
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

		//select2 id tipe
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
		})
		//remove bug arrow select2
		$(".select2-selection__arrow").css("pointer-events", "none")
	});


	$("#add-form #hargajual, #add-form #kpr").change(function () {
		let hj = removeComma($("#add-form #hargajual").val()),
			kpr = removeComma($("#add-form #kpr").val())

		$("#add-form #uang_muka").val(parseFloat(hj) - parseFloat(kpr)).change()
	})

	$("#add-form .num").change(function () {
		load_total();
	})

	function load_total() {
		let tot = 0,
			bphtb = parseFloat(removeComma($("#add-form #bphtb").val())),
			um = parseFloat(removeComma($("#add-form #uang_muka").val())),
			ba = parseFloat(removeComma($("#add-form #biaya_adm").val())),
			bp = parseFloat(removeComma($("#add-form #biaya_proses").val()))

		tot = um + bphtb + ba + bp

		$("#add-form #total").val(tot).keyup()
	}
	var url = '';

	function add() {
		url = base_url + '/hargajual/add'
		// $(".form-submit").attr("id","add-form")
		$('#add-form-btn').prop('disabled', false);
		// reset the form 
		$("#add-form")[0].reset();
		$("#id").val('')
		$("#label-id_filehj").html('Upload Pricelist');

		$(".form-control").removeClass('is-invalid').removeClass('is-valid');
		$('#add-modal').modal('show');
		$("#idProyek, #id_tipe").append($("<option selected></option>")).trigger('change');

		// submit the add from 
		$.validator.setDefaults({
			highlight: function (element) {
				$(element).addClass('is-invalid').removeClass('is-valid');
			},
			unhighlight: function (element) {
				$(element).removeClass('is-invalid').addClass('is-valid');
			},
			errorElement: 'div ',
			errorClass: 'invalid-feedback',
			errorPlacement: function (error, element) {
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

			submitHandler: function (form) {
				var form = $('#add-form')[0];
				var fd = new FormData(form);
				fd.append(csrfName, csrfHash);

				// remove the text-danger
				$(".text-danger").remove();

				$.ajax({
					url: url,
					type: 'post',
					contentType: false,
					processData: false,
					data: fd,
					dataType: 'json',
					beforeSend: function () {
						$('#add-form-btn').prop('disabled', true);
						$('#add-form-btn').html('<i class="fa fa-spinner fa-spin"></i>');
					},
					success: function (response) {
						csrfHash = response.token;

						if (response.success === true) {

							Swal.fire({
								icon: 'success',
								title: response.messages,
								showConfirmButton: false,

							}).then(function () {
								$('#data_table').DataTable().ajax.reload(null, false).draw(false);
								$('#add-modal').modal('hide');
							})

						} else {

							if (response.messages instanceof Object) {
								$.each(response.messages, function (index, value) {
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
						$('#add-form-btn').prop('disabled', false);
						$('#add-form-btn').html('Add');
					}
				});

				return false;
			}
		});
		$('#add-form').validate();
	}

	function edit(id_cluster) {
		url = base_url + '/hargajual/edit'
		$('#add-form-btn').prop('disabled', false);

		$("#add-form")[0].reset();
		$("#label-id_filehj").html('Upload Pricelist');

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
				// reset the form 

				$(".form-control").removeClass('is-invalid').removeClass('is-valid');
				$('#add-modal').modal('show');

				$("#id").val(r.id);
				$("#row").val(r.row);
				$("#lb").val(r.lb);
				$("#lt").val(r.lt);
				$("#hargajual").val(r.hargajual).keyup();
				$("#hargajual_net").val(r.hargajual_net).keyup();
				$("#kpr").val(r.kpr).keyup();
				$("#ppn").val(r.ppn).keyup();
				$("#uang_muka").val(r.uang_muka).keyup();
				$("#bphtb").val(r.bphtb).keyup();
				$("#biaya_adm").val(r.biaya_adm).keyup();
				$("#biaya_proses").val(r.biaya_proses).keyup();
				$("#keterangan").val(r.keterangan);
				$("#is_subsidi").val(r.is_subsidi);

				$("#list-id_filehj").prop('href', r.lokasi + r.file_name);

				load_total();

				$("#idProyek").append($("<option selected></option>").attr("value", r.id_proyek).text(r.nama_proyek)).trigger('change');
				// $("#id_tipe").append($("<option selected></option>").attr("value", r.id_tipe).text(r.id_tipe)).trigger('change');
				$("#id_tipe").val(r.id_tipe);

				if (r.tgl_harga != "0000-00-00")
					document.querySelector("#tgl_harga")._flatpickr.setDate(r.tgl_harga);

				// submit the edit from 
				$.validator.setDefaults({
					highlight: function (element) {
						$(element).addClass('is-invalid').removeClass('is-valid');
					},
					unhighlight: function (element) {
						$(element).removeClass('is-invalid').addClass('is-valid');
					},
					errorElement: 'div ',
					errorClass: 'invalid-feedback',
					errorPlacement: function (error, element) {
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

					submitHandler: function (form) {
						var form = $('#add-form')[0];
						var fd = new FormData(form);
						fd.append(csrfName, csrfHash);

						$(".text-danger").remove();
						$.ajax({
							url: url,
							type: 'post',
							contentType: false,
							processData: false,
							data: fd,
							dataType: 'json',
							beforeSend: function () {
								$('#add-form-btn').prop('disabled', true);
								$('#add-form-btn').html('<i class="fa fa-spinner fa-spin"></i>');
							},
							success: function (response) {
								csrfHash = response.token;

								if (response.success === true) {

									Swal.fire({

										icon: 'success',
										title: response.messages,
										showConfirmButton: false,

									}).then(function () {
										$('#data_table').DataTable().ajax.reload(null, false).draw(false);
										$('#add-modal').modal('hide');
									})

								} else {

									if (response.messages instanceof Object) {
										$.each(response.messages, function (index, value) {
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
								$('#add-form-btn').prop('disabled', false);
								$('#add-form-btn').html('Simpan');
							},
							error: function () {
								Swal.fire({

									icon: 'error',
									title: "Terjadi kesalahan saat memperbaharui data",
									showConfirmButton: false,

								})
								$('#add-form-btn').prop('disabled', false);
								$('#add-form-btn').html('Simpan');
							}
						});

						return false;
					}
				});
				$('#add-form').validate();

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


	$('thead > tr> th:nth-child(1)').css({
		'min-width': '50px',
		'max-width': '50px'
	});
	$('thead > tr> th:nth-child(2)').css({
		'min-width': '200px',
		'max-width': '200px'
	});
	$('thead > tr> th:nth-child(3)').css({
		'min-width': '150px',
		'max-width': '150px'
	});
	$('thead > tr> th:nth-child(4)').css({
		'min-width': '50px',
		'max-width': '50px'
	});
	$('thead > tr> th:nth-child(5)').css({
		'min-width': '50px',
		'max-width': '50px'
	});
	$('thead > tr> th:nth-child(6)').css({
		'min-width': '50px',
		'max-width': '50px'
	});
	$('thead > tr> th:nth-child(7)').css({
		'min-width': '50px',
		'max-width': '50px'
	});
	$('thead > tr> th:nth-child(8)').css({
		'min-width': '100px',
		'max-width': '100px'
	});
	$('thead > tr> th:nth-child(9)').css({
		'min-width': '100px',
		'max-width': '100px'
	});
	$('thead > tr> th:nth-child(10)').css({
		'min-width': '100px',
		'max-width': '100px'
	});
	$('thead > tr> th:nth-child(11)').css({
		'min-width': '100px',
		'max-width': '100px'
	});
	$('thead > tr> th:nth-child(12)').css({
		'min-width': '100px',
		'max-width': '100px'
	});
	$('thead > tr> th:nth-child(13)').css({
		'min-width': '100px',
		'max-width': '100px'
	});
	$('thead > tr> th:nth-child(14)').css({
		'min-width': '100px',
		'max-width': '100px'
	});
	// $('thead > tr> th:nth-child(15)').css({
	// 'min-width': '100px',
	// 'max-width': '80px'
	// });
	$('thead > tr> th:nth-child(16)').css({
		'min-width': '100px',
		'max-width': '100px'
	});
	$('thead > tr> th:nth-child(17)').css({
		'min-width': '300px',
		'max-width': '300px'
	});
	$('thead > tr> th:nth-child(18), thead > tr> th:nth-child(20)').css({
		'min-width': '180px',
		'max-width': '180px'
	});
</script>