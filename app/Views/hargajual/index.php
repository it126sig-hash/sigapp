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
<link rel="stylesheet" type="text/css"
	href="<?= base_url('assets/css/hargajual.css') ?>?v=<?= filemtime(FCPATH . 'assets/css/hargajual.css') ?>">

<div class="app-content content hargajual-page">
	<div class="content-overlay"></div>
	<div class="header-navbar-shadow"></div>
	<div class="content-wrapper">
		<div class="content-body">
			<section id="basic-datatable">
				<div class="row">
					<div class="col-12">
						<div class="card">
							<div class="card-header border-bottom d-flex flex-wrap justify-content-between align-items-center">
								<h4 class="card-title mb-1 mb-md-0">Halaman Pricelist</h4>
								<div class="d-flex align-items-center hargajual-header-actions">
									<div class="hargajual-filter-wrap">
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
								<div class="card-datatable table-responsive">
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
							<div class="modal-body p-2">
								<input type="hidden" id="id" name="id">

								<div class="row">
									<!-- KOLOM 1: IDENTITAS PROYEK & SPESIFIKASI -->
									<div class="col-lg-4 col-md-12 mb-1 mb-lg-0 d-flex flex-column">
										<div class="divider divider-left mb-1">
											<div class="divider-text font-weight-bold">
												<i class="fas fa-building mr-50 text-primary"></i>1. Identitas & Spesifikasi
											</div>
										</div>
										<div class="card border mb-0 shadow-none flex-grow-1 hargajual-form-card">
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
										<div class="card border mb-0 shadow-none flex-grow-1 hargajual-form-card">
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
										<div class="card border mb-0 shadow-none flex-grow-1 hargajual-form-card">
											<div class="card-body p-2 d-flex flex-column justify-content-between">
												<!-- Keterangan Editor Lebih Besar -->
												<div class="form-group mb-1">
													<label class="font-weight-bold font-small-3 mb-50 d-flex align-items-center justify-content-between">
														<span><i class="fas fa-sticky-note mr-25 text-primary"></i> Keterangan / Catatan Tambahan</span>
														<small class="text-muted font-small-1 font-weight-normal">Rich Text Editor (mendukung Ctrl + V gambar)</small>
													</label>
													<input type="hidden" name="keterangan" id="keterangan">
													<div id="keterangan-editor"></div>
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
																<span id="existing-file-name" class="font-small-2 text-dark font-weight-bold text-truncate d-inline-block hargajual-file-name"></span>
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
													<div id="dropzone-filehj" class="pricelist-dropzone bpb-dropzone p-1 text-center border-dashed rounded bg-white cursor-pointer position-relative">
														<input type="file" id="id_filehj" name="id_filehj" accept="application/pdf" class="d-none">
														<input type="hidden" id="remove_filehj" name="remove_filehj" value="0">

														<div id="dropzone-prompt" class="py-50">
															<i class="fas fa-cloud-upload-alt text-primary fa-lg mb-25"></i>
															<p class="mb-25 font-weight-bold text-dark font-small-2">Tarik & Lepaskan berkas PDF ke sini, atau <span class="text-primary text-decoration-underline">Pilih Berkas</span></p>
															<small class="text-muted d-block font-small-1">
																<i class="fas fa-file-pdf mr-25"></i> Format <strong>.PDF</strong> (Maks 10MB)
															</small>
														</div>

														<div id="dropzone-preview" class="d-none py-25">
															<div class="d-inline-flex align-items-center bg-light border rounded px-75 py-25">
																<i class="fas fa-file-pdf text-danger fa-lg mr-50"></i>
																<div class="text-left mr-50">
																	<span id="preview-filename" class="d-block font-weight-bolder text-dark font-small-2 text-truncate hargajual-file-name"></span>
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
<!-- END: Page Vendor JS-->
<script>
	window.HARGAJUAL_CONFIG = <?= json_encode([
		'baseUrl'        => rtrim(base_url(), '/'),
		'controller'     => $controller ?? 'hargajual',
		'csrfName'       => csrf_token(),
		'csrfHash'       => csrf_hash(),
		'activeProyekId' => function_exists('resolve_active_proyek_id') ? resolve_active_proyek_id(null) : null,
	], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
</script>
<script src="<?= base_url('assets/js/hargajual.js') ?>?v=<?= filemtime(FCPATH . 'assets/js/hargajual.js') ?>"></script>
