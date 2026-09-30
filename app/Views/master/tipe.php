<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/tables/datatable/dataTables.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/tables/datatable/responsive.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/tables/datatable/buttons.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/extensions/sweetalert2.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/forms/select/select2.min.css">
<script>
	// var csrfName = '<?= csrf_token() ?>';
	// var csrfHash = '<?= csrf_hash() ?>';
	// var base_url = '<?= base_url() ?>'
</script>
<style>
	.tipe-upload-grid {
		display: grid;
		grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
		gap: 1rem;
	}

	.tipe-upload-card .dropzone {
		position: relative;
		border: 2px dashed #ced4da;
		border-radius: .75rem;
		background: #fff;
		transition: .15s border-color, .15s background;
	}

	.tipe-upload-card .dropzone-lg {
		min-height: 120px;
	}

	.tipe-upload-card .dz-input {
		position: absolute;
		inset: 0;
		width: 100%;
		height: 100%;
		opacity: 0;
		cursor: pointer;
	}

	.tipe-upload-card .dz-inner {
		height: 100%;
		display: flex;
		align-items: center;
		justify-content: center;
		text-align: center;
		padding: 1rem;
	}

	.tipe-upload-card .dz-placeholder {
		pointer-events: none;
	}

	.tipe-upload-card .dz-preview {
		display: none;
		width: 100%;
		height: 100%;
	}

	.tipe-upload-card .dz-preview img,
	.tipe-upload-card .preview-thumb {
		display: block;
		max-height: 95px;
		width: auto;
		max-width: 100%;
		margin: 0 auto .25rem;
		object-fit: contain;
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
						<hr class="col-12" />
						<button type="button" id="btn_draw" class="btn btn-outline-primary waves-effect btn-sm">Filter Data</button>
						<button type="button" class="btn btn-primary data-submit  btn-sm mr-1 col-sm-12 col-md-3 col-lg-3" onclick="add()" title="Add"> <i class="fa fa-plus"></i> Tambah Data</button>
					</div>
					<div class="card-datatable">
						<table id="data_table" class="datatables-basic table">
							<thead>
								<tr>
									<th>No</th>
									<th>Nomor</th>
									<th>Tipe Rumah (LB/LT)</th>
									<th>Subsidi</th>
									<th>Jumlah Kamar Tidur</th>
									<th>Jumlah Kamar Mandi</th>
									<th>Total Kavling</th>
									<th>Keterangan</th>
									<th>Aksi</th>
								</tr>
							</thead>
						</table>
					</div>
				</div>
			</div>
		</div>
		<!-- Modal to add new record -->
		<div class="modal fade" id="add-modal">
			<div class="modal-dialog modal-dialog-scrollable modal-xl">
				<form id="add-form" enctype="multipart/form-data" class="add-new-record modal-content pt-0">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button>
					<div class="modal-header mb-1">
						<h5 class="modal-title" id="exampleModalLabel">Tambah Data</h5>
					</div>
					<div class="modal-body flex-grow-1 p-2">
						<div class="row">
							<input type="hidden" id="idTipe" name="idTipe" class="form-control" placeholder="Id tipe" maxlength="255" required>
						</div>

						<fieldset class="border p-2 mb-2 rounded">
							<legend class="w-auto px-2" style="font-size: 12px; font-weight: 600; color: #6e6b7b; text-transform: uppercase;">Pilih Proyek: <span class="text-danger">*</span></legend>
							<div class="form-group mb-0">
								<select id="idProyek" name="idProyek" class="custom-select" required>
									<option value=''>Pilih Proyek</option>
									<?php foreach ($proyek as $p) {
										echo "<option value='$p->id_proyek'>$p->nama_proyek</option>";
									} ?>
								</select>
							</div>
						</fieldset>

						<div class="row">
							<div class="col-md-5">
								<fieldset class="border p-2 mb-2 rounded h-100">
									<legend class="w-auto px-2 text-primary" style="font-size: 12px; font-weight: 600; text-transform: uppercase;"><i class="fa fa-home"></i> Detail Unit &amp; Dimensi</legend>

									<div class="form-group col-12">
										<label for="no_tipe_rumah">Nomor:</label>
										<input type="text" id="no_tipe_rumah" name="no_tipe_rumah" class="form-control" placeholder="Contoh: A-12" maxlength="255">
									</div>
									<div class="form-group col-12">
										<label for="tipeRumah">Tipe rumah:</label>
										<input type="text" id="tipeRumah" name="tipeRumah" class="form-control" placeholder="Contoh: 36/72" maxlength="255">
									</div>

									<div class="form-group">
										<label for="isSubsidi">Subsidi:</label>
										<select id="isSubsidi" name="isSubsidi" class="custom-select">
											<option value="1">Subsidi</option>
											<option value="0">Non-Subsidi</option>
										</select>
									</div>

									<div class="row">
										<div class="form-group col-6">
											<label for="lb">Luas Bangunan:</label>
											<div class="input-group">
												<input type="text" id="lb" name="lb" class="form-control" placeholder="Lb">
												<div class="input-group-append"><span class="input-group-text">m&sup2;</span></div>
											</div>
										</div>
										<div class="form-group col-6">
											<label for="lt">Luas Tanah:</label>
											<div class="input-group">
												<input type="text" id="lt" name="lt" class="form-control" placeholder="Lt">
												<div class="input-group-append"><span class="input-group-text">m&sup2;</span></div>
											</div>
										</div>
									</div>

									<div class="row">
										<div class="form-group col-6">
											<label for="jumlah_kamar_tidur">Kamar Tidur:</label>
											<div class="input-group">
												<input type="number" id="jumlah_kamar_tidur" name="jumlah_kamar_tidur" class="form-control" placeholder="0" min="0">
												<div class="input-group-append"><span class="input-group-text">unit</span></div>
											</div>
										</div>
										<div class="form-group col-6">
											<label for="jumlah_kamar_mandi">Kamar Mandi:</label>
											<div class="input-group">
												<input type="number" id="jumlah_kamar_mandi" name="jumlah_kamar_mandi" class="form-control" placeholder="0" min="0">
												<div class="input-group-append"><span class="input-group-text">unit</span></div>
											</div>
										</div>
									</div>

									<div class="form-group mb-0">
										<label for="keterangan" class="d-flex justify-content-between"><span>Keterangan:</span> <span class="text-muted" style="font-size: 11px;">Opsional</span></label>
										<textarea cols="40" rows="3" id="keterangan" name="keterangan" class="form-control" placeholder="Tambahkan catatan khusus, nomor izin persetujuan..."></textarea>
									</div>
								</fieldset>
							</div>

							<div class="col-md-7 d-flex flex-column">
								<fieldset class="border p-2 mb-2 rounded">
									<legend class="w-auto px-2 text-primary" style="font-size: 12px; font-weight: 600; text-transform: uppercase;"><i class="fa fa-file-alt"></i> Spesifikasi Teknis Material</legend>

									<div class="row">
										<div class="form-group col-6">
											<label for="spesifikasi_teknis_atap">Spesifikasi Teknis Atap:</label>
											<input type="text" id="spesifikasi_teknis_atap" name="spesifikasi_teknis_atap" class="form-control" placeholder="Rangka baja ringan...">
										</div>
										<div class="form-group col-6">
											<label for="spesifikasi_teknis_dinding">Spesifikasi Teknis Dinding:</label>
											<input type="text" id="spesifikasi_teknis_dinding" name="spesifikasi_teknis_dinding" class="form-control" placeholder="Bata ringan / hebel...">
										</div>
									</div>

									<div class="row">
										<div class="form-group col-6 mb-0">
											<label for="spesifikasi_teknis_lantai">Spesifikasi Teknis Lantai:</label>
											<input type="text" id="spesifikasi_teknis_lantai" name="spesifikasi_teknis_lantai" class="form-control" placeholder="Keramik 40x40...">
										</div>
										<div class="form-group col-6 mb-0">
											<label for="spesifikasi_teknis_pondasi">Spesifikasi Teknis Pondasi:</label>
											<input type="text" id="spesifikasi_teknis_pondasi" name="spesifikasi_teknis_pondasi" class="form-control" placeholder="Batu kali / cakar ayam...">
										</div>
									</div>
								</fieldset>

								<fieldset class="border p-2 mb-2 rounded flex-grow-1">
									<legend class="w-auto px-2 text-primary d-flex align-items-center flex-wrap" style="font-size: 12px; font-weight: 600; text-transform: uppercase;">
										<i class="fa fa-image mr-50"></i> Unggah Dokumen &amp; Denah 
										<span class="text-muted text-lowercase" style="font-size: 10px; font-weight: normal; margin-left: 5px;">(Maks. 12 MB per file)</span>
									</legend>

									<div class="tipe-upload-grid mb-0">
										<div class="form-group tipe-upload-card mb-0">
											<label class="font-weight-bold" style="font-size: 11px;" for="add_gambar_kerja">Gambar Kerja:</label>
											<div class="dropzone dropzone-lg custom-file">
												<input type="file" name="gambar_kerja" id="add_gambar_kerja" accept="application/pdf" class="custom-file-input dz-input" />
												<div class="dz-inner">
													<div class="dz-preview" id="prev_add_gambar_kerja"></div>
													<div class="dz-placeholder">
														<div class="text-danger mb-50"><i class="fa fa-file-pdf fa-2x"></i></div>
														<div class="font-weight-bold" style="font-size: 12px;">Tarik PDF ke sini</div>
														<div class="text-muted" style="font-size: 10px;">atau <a href="javascript:void(0)" class="text-primary">klik pilih file</a><br />PDF (maks 12 MB)</div>
													</div>
												</div>
											</div>
										</div>
										<div class="form-group tipe-upload-card mb-0">
											<label class="font-weight-bold" style="font-size: 11px;" for="add_gambar_tipe">Gambar Ilustrasi:</label>
											<div class="dropzone dropzone-lg custom-file">
												<input type="file" name="gambar_tipe" id="add_gambar_tipe" accept="image/*" class="custom-file-input dz-input" />
												<div class="dz-inner">
													<div class="dz-preview" id="prev_add_gambar_tipe"></div>
													<div class="dz-placeholder">
														<div class="text-primary mb-50"><i class="fa fa-image fa-2x"></i></div>
														<div class="font-weight-bold" style="font-size: 12px;">Tarik gambar</div>
														<div class="text-muted" style="font-size: 10px;">atau <a href="javascript:void(0)" class="text-primary">klik pilih file</a><br />PNG/JPG maks 12 MB</div>
													</div>
												</div>
											</div>
										</div>
										<div class="form-group tipe-upload-card mb-0">
											<label class="font-weight-bold" style="font-size: 11px;" for="add_gambar_denah">Denah Arsitektural:</label>
											<div class="dropzone dropzone-lg custom-file">
												<input type="file" name="gambar_denah" id="add_gambar_denah" accept="image/*" class="custom-file-input dz-input" />
												<div class="dz-inner">
													<div class="dz-preview" id="prev_add_gambar_denah"></div>
													<div class="dz-placeholder">
														<div class="text-success mb-50"><i class="fa fa-columns fa-2x"></i></div>
														<div class="font-weight-bold" style="font-size: 12px;">Tarik denah</div>
														<div class="text-muted" style="font-size: 10px;">atau <a href="javascript:void(0)" class="text-primary">klik pilih file</a><br />PNG/JPG maks 12 MB</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</fieldset>
							</div>
						</div>

						<div class="d-flex justify-content-between align-items-center mt-1">
							<div class="text-success" style="font-size: 12px;"><i class="fa fa-circle" style="font-size: 8px;"></i> Pastikan semua isian spesifikasi teknis sudah sesuai data lapangan</div>
							<div>
								<button type="reset" class="btn btn-outline-secondary mr-1" data-dismiss="modal">Batal</button>
								<button type="submit" class="btn btn-primary data-submit" id="add-form-btn"><i class="fa fa-check"></i> Simpan</button>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
		<!-- Modal to add new record -->
		<div class="modal fade" id="edit-modal">
			<div class="modal-dialog modal-dialog-scrollable modal-xl">
				<form id="edit-form" enctype="multipart/form-data" class="add-new-record modal-content pt-0">
					<div class="modal-header mb-1">
						<h5 class="modal-title" id="exampleModalLabel">Perbaharui Data</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button>
					</div>
					<div class="modal-body flex-grow-1">
						<ul class="nav nav-tabs" role="tablist">
							<li class="nav-item">
								<a class="nav-link active" id="data-tipe-tab" data-toggle="tab" href="#data-tipe" aria-controls="home" role="tab" aria-selected="true">Data Tipe</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" id="upload-gambar-kerja-tab" data-toggle="tab" href="#upload-gambar-kerja" aria-controls="home" role="tab" aria-selected="true">Riwayat Gambar Kerja</a>
							</li>
						</ul>
						<div class="tab-content">
							<div class="tab-pane active p-2" id="data-tipe" aria-labelledby="data-tipe-tab" role="tabpanel">
								<div class="row">
									<input type="hidden" id="idTipe" name="idTipe" class="form-control" placeholder="Id tipe" maxlength="255" required>
								</div>

								<fieldset class="border p-2 mb-2 rounded">
									<legend class="w-auto px-2" style="font-size: 12px; font-weight: 600; color: #6e6b7b; text-transform: uppercase;">Pilih Proyek: <span class="text-danger">*</span></legend>
									<div class="form-group mb-0">
										<select id="_idProyek" disabled name="_idProyek" class="custom-select">
											<option value=''>Pilih Proyek</option>
											<?php foreach ($proyek as $p) {
												echo "<option value='$p->id_proyek'>$p->nama_proyek</option>";
											} ?>
										</select>
										<input type="hidden" id="idProyek" name="idProyek">
									</div>
								</fieldset>

								<div class="row">
									<div class="col-md-5">
										<fieldset class="border p-2 mb-2 rounded h-100">
											<legend class="w-auto px-2 text-primary" style="font-size: 12px; font-weight: 600; text-transform: uppercase;"><i class="fa fa-home"></i> Detail Unit &amp; Dimensi</legend>

											<div class="form-group col-12">
												<label for="no_tipe_rumah">Nomor:</label>
												<input type="text" id="no_tipe_rumah" name="no_tipe_rumah" class="form-control" placeholder="Contoh: A-12" maxlength="255">
											</div>
											<div class="form-group col-12">
												<label for="tipeRumah">Tipe rumah:</label>
												<input type="text" id="tipeRumah" name="tipeRumah" class="form-control" placeholder="Contoh: 36/72" maxlength="255">
											</div>

											<div class="form-group">
												<label for="isSubsidi">Subsidi:</label>
												<select id="isSubsidi" name="isSubsidi" class="custom-select">
													<option value="1">Subsidi</option>
													<option value="0">Non-Subsidi</option>
												</select>
											</div>

											<div class="row">
												<div class="form-group col-6">
													<label for="lb">Luas Bangunan:</label>
													<div class="input-group">
														<input type="text" id="lb" name="lb" class="form-control" placeholder="Lb">
														<div class="input-group-append"><span class="input-group-text">m&sup2;</span></div>
													</div>
												</div>
												<div class="form-group col-6">
													<label for="lt">Luas Tanah:</label>
													<div class="input-group">
														<input type="text" id="lt" name="lt" class="form-control" placeholder="Lt">
														<div class="input-group-append"><span class="input-group-text">m&sup2;</span></div>
													</div>
												</div>
											</div>

											<div class="row">
												<div class="form-group col-6">
													<label for="jumlah_kamar_tidur">Kamar Tidur:</label>
													<div class="input-group">
														<input type="number" id="jumlah_kamar_tidur" name="jumlah_kamar_tidur" class="form-control" placeholder="0" min="0">
														<div class="input-group-append"><span class="input-group-text">unit</span></div>
													</div>
												</div>
												<div class="form-group col-6">
													<label for="jumlah_kamar_mandi">Kamar Mandi:</label>
													<div class="input-group">
														<input type="number" id="jumlah_kamar_mandi" name="jumlah_kamar_mandi" class="form-control" placeholder="0" min="0">
														<div class="input-group-append"><span class="input-group-text">unit</span></div>
													</div>
												</div>
											</div>

											<div class="form-group mb-0">
												<label for="keterangan" class="d-flex justify-content-between"><span>Keterangan:</span> <span class="text-muted" style="font-size: 11px;">Opsional</span></label>
												<textarea cols="40" rows="3" id="keterangan" name="keterangan" class="form-control" placeholder="Tambahkan catatan khusus, nomor izin persetujuan..."></textarea>
											</div>
										</fieldset>
									</div>

									<div class="col-md-7 d-flex flex-column">
										<fieldset class="border p-2 mb-2 rounded">
											<legend class="w-auto px-2 text-primary" style="font-size: 12px; font-weight: 600; text-transform: uppercase;"><i class="fa fa-file-alt"></i> Spesifikasi Teknis Material</legend>

											<div class="row">
												<div class="form-group col-6">
													<label for="spesifikasi_teknis_atap">Spesifikasi Teknis Atap:</label>
													<input type="text" id="spesifikasi_teknis_atap" name="spesifikasi_teknis_atap" class="form-control" placeholder="Rangka baja ringan...">
												</div>
												<div class="form-group col-6">
													<label for="spesifikasi_teknis_dinding">Spesifikasi Teknis Dinding:</label>
													<input type="text" id="spesifikasi_teknis_dinding" name="spesifikasi_teknis_dinding" class="form-control" placeholder="Bata ringan / hebel...">
												</div>
											</div>

											<div class="row">
												<div class="form-group col-6 mb-0">
													<label for="spesifikasi_teknis_lantai">Spesifikasi Teknis Lantai:</label>
													<input type="text" id="spesifikasi_teknis_lantai" name="spesifikasi_teknis_lantai" class="form-control" placeholder="Keramik 40x40...">
												</div>
												<div class="form-group col-6 mb-0">
													<label for="spesifikasi_teknis_pondasi">Spesifikasi Teknis Pondasi:</label>
													<input type="text" id="spesifikasi_teknis_pondasi" name="spesifikasi_teknis_pondasi" class="form-control" placeholder="Batu kali / cakar ayam...">
												</div>
											</div>
										</fieldset>

										<fieldset class="border p-2 mb-2 rounded flex-grow-1">
											<legend class="w-auto px-2 text-primary d-flex align-items-center flex-wrap" style="font-size: 12px; font-weight: 600; text-transform: uppercase;">
												<i class="fa fa-image mr-50"></i> Unggah Dokumen &amp; Denah 
												<span class="text-muted text-lowercase" style="font-size: 10px; font-weight: normal; margin-left: 5px;">(Maks. 12 MB per file)</span>
											</legend>

											<div class="tipe-upload-grid mb-0">
												<div class="form-group tipe-upload-card mb-0">
													<label class="font-weight-bold" style="font-size: 11px;" for="edit_gambar_kerja">Gambar Kerja:</label>
													<div class="dropzone dropzone-lg custom-file">
														<input type="file" name="gambar_kerja" id="edit_gambar_kerja" accept="application/pdf" class="custom-file-input dz-input" />
														<div class="dz-inner">
															<div class="dz-preview" id="prev_edit_gambar_kerja"></div>
															<div class="dz-placeholder">
																<div class="text-danger mb-50"><i class="fa fa-file-pdf fa-2x"></i></div>
																<div class="font-weight-bold" style="font-size: 12px;">Tarik PDF ke sini</div>
																<div class="text-muted" style="font-size: 10px;">atau <a href="javascript:void(0)" class="text-primary">klik pilih file</a><br />PDF (maks 12 MB)</div>
															</div>
														</div>
													</div>
													<input type="hidden" name="no_up" id="no_up" />
													<input type="hidden" id="id_gambar_kerja" name="id_gambar_kerja" class="form-control" maxlength="255">
													<a id="download_gambar_kerja" href="javascript:void(0)" target="_blank" class="btn btn-primary btn-block waves-effect mt-1" style="font-size: 10px; padding: 0.4rem;">Lihat PDF</a>
												</div>
												<div class="form-group tipe-upload-card mb-0">
													<label class="font-weight-bold" style="font-size: 11px;" for="edit_gambar_tipe">Gambar Ilustrasi:</label>
													<div class="dropzone dropzone-lg custom-file">
														<input type="file" name="gambar_tipe" id="edit_gambar_tipe" accept="image/*" class="custom-file-input dz-input" />
														<div class="dz-inner">
															<div class="dz-preview" id="prev_edit_gambar_tipe"></div>
															<div class="dz-placeholder">
																<div class="text-primary mb-50"><i class="fa fa-image fa-2x"></i></div>
																<div class="font-weight-bold" style="font-size: 12px;">Tarik gambar</div>
																<div class="text-muted" style="font-size: 10px;">atau <a href="javascript:void(0)" class="text-primary">klik pilih file</a><br />PNG/JPG maks 12 MB</div>
															</div>
														</div>
													</div>
													<input type="hidden" name="no_up_gambar_tipe" id="no_up_gambar_tipe" />
													<input type="hidden" id="id_gambar_tipe" name="id_gambar_tipe" class="form-control" maxlength="255">
													<a id="download_gambar_tipe" href="javascript:void(0)" target="_blank" class="btn btn-primary btn-block waves-effect mt-1" style="font-size: 10px; padding: 0.4rem;">Lihat Gambar</a>
												</div>
												<div class="form-group tipe-upload-card mb-0">
													<label class="font-weight-bold" style="font-size: 11px;" for="edit_gambar_denah">Denah Arsitektural:</label>
													<div class="dropzone dropzone-lg custom-file">
														<input type="file" name="gambar_denah" id="edit_gambar_denah" accept="image/*" class="custom-file-input dz-input" />
														<div class="dz-inner">
															<div class="dz-preview" id="prev_edit_gambar_denah"></div>
															<div class="dz-placeholder">
																<div class="text-success mb-50"><i class="fa fa-columns fa-2x"></i></div>
																<div class="font-weight-bold" style="font-size: 12px;">Tarik denah</div>
																<div class="text-muted" style="font-size: 10px;">atau <a href="javascript:void(0)" class="text-primary">klik pilih file</a><br />PNG/JPG maks 12 MB</div>
															</div>
														</div>
													</div>
													<input type="hidden" name="no_up_gambar_denah" id="no_up_gambar_denah" />
													<input type="hidden" id="id_gambar_denah" name="id_gambar_denah" class="form-control" maxlength="255">
													<a id="download_gambar_denah" href="javascript:void(0)" target="_blank" class="btn btn-primary btn-block waves-effect mt-1" style="font-size: 10px; padding: 0.4rem;">Lihat Denah</a>
												</div>
											</div>
										</fieldset>
									</div>
								</div>

								<div class="d-flex justify-content-between align-items-center mt-1">
									<div class="text-success" style="font-size: 12px;"><i class="fa fa-circle" style="font-size: 8px;"></i> Pastikan semua isian spesifikasi teknis sudah sesuai data lapangan</div>
									<div>
										<button type="reset" class="btn btn-outline-secondary mr-1" data-dismiss="modal">Batal</button>
										<button type="submit" class="btn btn-primary data-submit" id="edit-form-btn"><i class="fa fa-check"></i> Simpan</button>
									</div>
								</div>
							</div>
							<div class="tab-pane" id="upload-gambar-kerja" aria-labelledby="upload-gambar-kerja-tab" role="tabpanel">
								<div class="table-responsive">
									<table class="table mb-0">
										<thead>
											<tr>
												<th scope="col" class="text-nowrap">No</th>
												<th scope="col" class="text-nowrap">Nama File</th>
												<th scope="col" class="text-nowrap">Keterangan</th>
												<th scope="col" class="text-nowrap">Link</th>
												<th scope="col" class="text-nowrap">Oleh</th>
												<th scope="col" class="text-nowrap">Tanggal Upload</th>
											</tr>
										</thead>
										<tbody id="tb-gambar-kerja-file">
											<tr>
												<td colspan="6" class="text-center">Tidak ada data</td>
											</tr>
										</tbody>
									</table>
								</div>

							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
	</section>
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
<!-- <script src="https://adminlte.io/themes/v3/plugins/jquery-validation/additional-methods.min.js"></script> -->
<!-- END: Page Vendor JS-->
<script src="<?= base_url() ?>assets/js/vendor/browser-image-compression.js"></script>
<script>
	// Membuat objek Date baru
	const today = new Date();

	// Mendapatkan tanggal, bulan, dan tahun
	const day = today.getDate(); // Hari (1-31)
	const month = today.getMonth() + 1; // Bulan (0-11, jadi perlu ditambah 1)
	const year = today.getFullYear(); // Tahun (4 digit)

	// Format tanggal sebagai string (contoh: "DD/MM/YYYY")
	const formattedDate = `${day}-${month}-${year}`;

	var table
	const tipeUploadInputs = [
		'add_gambar_kerja',
		'add_gambar_tipe',
		'add_gambar_denah',
		'edit_gambar_kerja',
		'edit_gambar_tipe',
		'edit_gambar_denah',
	];

	function initTipeUploadInputs() {
		tipeUploadInputs.forEach(function(inputId) {
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

	function renderExistingUploadPreview(inputId, src, label, type = 'image') {
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

		preview.innerHTML = type === 'pdf' ?
			`<div class="p-2 border rounded bg-light text-center">
				<i class="fa fa-file-pdf fa-3x text-danger"></i>
				<div class="text-truncate">${escapeHtml(label)} sudah diunggah</div>
			</div>` :
			`<img src="${escapeAttribute(src)}" class="preview-thumb" alt="${escapeAttribute(label)}">
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

	function getImageFileFromClipboard(e) {
		const clipboard = (e.originalEvent && e.originalEvent.clipboardData) || e.clipboardData;
		if (!clipboard || !clipboard.items) return null;

		for (const item of clipboard.items) {
			if (item.kind === 'file' && item.type.startsWith('image/')) {
				return item.getAsFile();
			}
		}

		return null;
	}

	function assignFileToInput(inputId, file, prefix) {
		const input = document.getElementById(inputId);
		if (!input || !file) return;

		const extension = getImageExtension(file.type);
		const uploadFile = new File(
			[file],
			file.name || `${prefix}-${Date.now()}.${extension}`, {
				type: file.type || 'image/png'
			},
		);
		const dataTransfer = new DataTransfer();
		dataTransfer.items.add(uploadFile);
		input.files = dataTransfer.files;
		input.dispatchEvent(new Event('change', {
			bubbles: true
		}));
	}

	function getImageExtension(type) {
		const map = {
			'image/jpeg': 'jpg',
			'image/png': 'png',
			'image/webp': 'webp',
		};

		return map[type] || 'png';
	}

	function bindTipeClipboardUpload() {
		if (window.tipeClipboardUploadBound) return;
		window.tipeClipboardUploadBound = true;

		document.addEventListener('paste', async function(e) {
			const addVisible = $('#add-modal').hasClass('show');
			const editVisible = $('#edit-modal').hasClass('show');
			if (!addVisible && !editVisible) return;

			const pastedFile = getImageFileFromClipboard(e);
			if (!pastedFile) return;

			e.preventDefault();
			const result = await Swal.fire({
				title: 'Upload dari Clipboard',
				text: 'Pilih tujuan gambar yang ditempel.',
				icon: 'question',
				showDenyButton: true,
				showCancelButton: true,
				confirmButtonText: 'Gambar Ilustrasi',
				denyButtonText: 'Denah Arsitektural',
				cancelButtonText: 'Batal',
			});

			const prefix = addVisible ? 'add' : 'edit';
			if (result.isConfirmed) {
				assignFileToInput(`${prefix}_gambar_tipe`, pastedFile, `${prefix}-gambar-ilustrasi`);
			} else if (result.isDenied) {
				assignFileToInput(`${prefix}_gambar_denah`, pastedFile, `${prefix}-denah`);
			}
		});
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

	$(function() {
		initTipeUploadInputs();
		bindTipeClipboardUpload();

		table = $('#data_table').DataTable({
			paging: true,
			lengthChange: true,
			searching: true,
			ordering: true,
			info: true,
			autoWidth: false,
			responsive: true,
			processing: true,
			serverSide: true,
			ajax: {
				url: '<?php echo base_url($controller . '/getDataTables') ?>',
				type: "POST",
				dataType: "json",
				data: {
					[csrfName]: csrfHash
				},
				data: function(data) {
					data[csrfName] = csrfHash
					data.id_proyek = activeProyekId();
				},
				dataSrc: function(r) {
					csrfHash = r.token
					return r.data;
				},
				async: "true"
			}
		});
		//on click btn filter
		$("#btn_draw").on("click", function(e) {
			table.draw();
		})

		if (activeProyekId()) {
			table.draw();
		}
	});
	//remove bug arrow select2
	$(".select2-selection__arrow").css("pointer-events", "none")

	function add() {
		// reset the form 
		$("#add-form")[0].reset();
		resetUploadPreviews('#add-form');
		$(".form-control").removeClass('is-invalid').removeClass('is-valid');
		initModalListener('#add-modal');
		$('#add-modal').modal('show');

		if (activeProyekId()) {
			$("#add-form #idProyek").val(activeProyekId()).trigger('change');
		}

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
					error.insertAfter(element.next());
				} else if (element.hasClass('selectpicker')) {
					error.insertAfter(element.next());
				} else {
					error.insertAfter(element);
				}
			},

			submitHandler: async function(form) {
				var formObj = $('#add-form')[0];
				var fd = new FormData(formObj);
				fd.append(csrfName, csrfHash);
				$(".text-danger").remove();

				$('#add-form-btn').html('<i class="fa fa-spinner fa-spin"></i> Memproses...');
				$('#add-form-btn').prop('disabled', true);

				if (typeof imageCompression !== "undefined") {
					const compressOptions = {
						maxSizeMB: 1,
						maxWidthOrHeight: 1920,
						useWebWorker: true
					};
					let filesToCompress = [{
							id: 'add_gambar_tipe',
							name: 'gambar_tipe'
						},
						{
							id: 'add_gambar_denah',
							name: 'gambar_denah'
						}
					];

					for (let f of filesToCompress) {
						let fileInput = document.getElementById(f.id);
						if (fileInput && fileInput.files.length > 0) {
							let file = fileInput.files[0];
							if (file.type.startsWith('image/')) {
								try {
									let compressedFile = await imageCompression(file, compressOptions);
									fd.set(f.name, compressedFile, compressedFile.name || file.name);
								} catch (error) {
									console.error("Compression error for", f.name, error);
								}
							}
						}
					}
				}

				$.ajax({
					url: '<?php echo base_url($controller . '/add') ?>',
					type: 'POST',
					contentType: false,
					processData: false,
					data: fd,
					success: function(response) {
						csrfHash = response.token;

						if (response.success === true) {
							Swal.fire({
								position: 'bottom-end',
								icon: 'success',
								title: response.messages,
								showConfirmButton: false,
								timer: 1500
							}).then(function() {
								$('#data_table').DataTable().ajax.reload(null, false).draw(false);
								removeModalListener('#add-modal');
								$('#add-modal').modal('hide');
							});
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
									position: 'center',
									icon: 'error',
									title: response.messages,
									showConfirmButton: false
								});
							}
						}
						$('#add-form-btn').prop('disabled', false);
						$('#add-form-btn').html('<i class="fa fa-check"></i> Simpan');
					},
					error: function() {
						$('#add-form-btn').prop('disabled', false);
						$('#add-form-btn').html('<i class="fa fa-check"></i> Simpan');
					}
				});

				return false;
			}
		});
		$('#add-form').validate();
	}

	function edit(id_tipe, isView = false) {
		$.ajax({
			url: '<?php echo base_url($controller . '/getOne') ?>',
			type: 'post',
			data: {
				[csrfName]: csrfHash,
				id_tipe: id_tipe
			},
			dataType: 'json',
			success: function(response) {
				csrfHash = response.token;
				// reset the form 
				$("#edit-form")[0].reset();
				resetUploadPreviews('#edit-form');
				$(".form-control").removeClass('is-invalid').removeClass('is-valid');
				initModalListener('#edit-modal');
				$('#edit-modal').modal('show');

				if (isView) {
					$('#edit-modal .modal-title').text('Detail Tipe');
					$('#edit-form input, #edit-form textarea, #edit-form select').prop('disabled', true);
					$('#edit-form-btn').hide();
					$('#edit-form .dz-placeholder').hide();
				} else {
					$('#edit-modal .modal-title').text('Perbaharui Data');
					$('#edit-form input, #edit-form textarea, #edit-form select').prop('disabled', false);
					$('#edit-form #_idProyek').prop('disabled', true);
					$('#edit-form-btn').show();
					$('#edit-form .dz-placeholder').show();
				}

				$("#edit-form #idTipe").val(response.id_tipe);
				$("#edit-form #_idProyek").val(response.id_proyek);
				$("#edit-form #idProyek").val(response.id_proyek);
				$("#edit-form #no_tipe_rumah").val(response.no_tipe_rumah);
				$("#edit-form #tipeRumah").val(response.tipe_rumah);
				$("#edit-form #isSubsidi").val(response.is_subsidi);
				$("#edit-form #lb").val(response.lb);
				$("#edit-form #lt").val(response.lt);
				$("#edit-form #jumlah_kamar_tidur").val(response.jumlah_kamar_tidur);
				$("#edit-form #jumlah_kamar_mandi").val(response.jumlah_kamar_mandi);
				$("#edit-form #spesifikasi_teknis_atap").val(response.spesifikasi_teknis_atap);
				$("#edit-form #spesifikasi_teknis_dinding").val(response.spesifikasi_teknis_dinding);
				$("#edit-form #spesifikasi_teknis_lantai").val(response.spesifikasi_teknis_lantai);
				$("#edit-form #spesifikasi_teknis_pondasi").val(response.spesifikasi_teknis_pondasi);

				$("#edit-form #id_gambar_kerja").val(response.id_gambar_kerja);
				$("#edit-form #id_gambar_tipe").val(response.id_gambar_tipe);
				$("#edit-form #id_gambar_denah").val(response.id_gambar_denah);

				renderExistingUploadPreview('edit_gambar_kerja', response.gambar_kerja_access_url, 'Gambar Kerja', 'pdf');
				renderExistingUploadPreview('edit_gambar_tipe', response.gambar_tipe_access_url, 'Gambar Ilustrasi');
				renderExistingUploadPreview('edit_gambar_denah', response.gambar_denah_access_url, 'Denah Arsitektural');

				setFileAction('#download_gambar_kerja', response.gambar_kerja_access_url || (response.id_gambar_kerja ? file_url('gambar_kerja', response.id_gambar_kerja) : null));
				setFileAction('#download_gambar_tipe', response.gambar_tipe_access_url || (response.id_gambar_tipe ? file_url('gambar_kerja', response.id_gambar_tipe) : null));
				setFileAction('#download_gambar_denah', response.gambar_denah_access_url || (response.id_gambar_denah ? file_url('gambar_kerja', response.id_gambar_denah) : null));

				$("#edit-form #keterangan").val(response.keterangan);

				//set riwayat gambar kerja 
				$("#tb-gambar-kerja-file").html("");
				let tb = `<tr><td colspan="6" class="text-center">Tidak ada data</td></tr>`

				if (response.gambarkerja && response.gambarkerja.length > 0) {
					tb = '';
					let no = 0
					$.each(response.gambarkerja, function(i, v) {
						no++
						tb += `<tr>
								<td>` + no + `</td>
								<td>` + v.default_filename + `</td>
								<td>` + v.keterangan + `</td>
								<td> <a href='` + (v.access_url || file_url('gambar_kerja', v.id_gambar_kerja)) + `' target=blank>Klik disini untuk melihat</a></td>
								<td> ` + v.uadd_by + `</td>
								<td> ` + format_datetime(v.upload_at) + ` </td>
								</tr>`
					});
				}
				$("#tb-gambar-kerja-file").html(tb);

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
							error.insertAfter(element.next());
						} else if (element.hasClass('selectpicker')) {
							error.insertAfter(element.next());
						} else {
							error.insertAfter(element);
						}
					},

					submitHandler: async function(form) {
						var files = $('#edit_gambar_kerja')[0].files,
							files_d = $('#edit_gambar_denah')[0].files,
							files_t = $('#edit_gambar_tipe')[0].files;

						var formObj = $('#edit-form')[0];

						$("#no_up").val(files.length == 0 ? 1 : 0)
						$("#no_up_gambar_tipe").val(files_t.length == 0 ? 1 : 0)
						$("#no_up_gambar_denah").val(files_d.length == 0 ? 1 : 0)

						var fd = new FormData(formObj);
						fd.append(csrfName, csrfHash);

						$(".text-danger").remove();

						$('#edit-form-btn').html('<i class="fa fa-spinner fa-spin"></i> Memproses...');
						$('#edit-form-btn').prop('disabled', true);

						if (typeof imageCompression !== "undefined") {
							const compressOptions = {
								maxSizeMB: 1,
								maxWidthOrHeight: 1920,
								useWebWorker: true
							};
							let filesToCompress = [{
									id: 'edit_gambar_tipe',
									name: 'gambar_tipe'
								},
								{
									id: 'edit_gambar_denah',
									name: 'gambar_denah'
								}
							];

							for (let f of filesToCompress) {
								let fileInput = document.getElementById(f.id);
								if (fileInput && fileInput.files.length > 0) {
									let file = fileInput.files[0];
									if (file.type.startsWith('image/')) {
										try {
											let compressedFile = await imageCompression(file, compressOptions);
											fd.set(f.name, compressedFile, compressedFile.name || file.name);
										} catch (error) {
											console.error("Compression error for", f.name, error);
										}
									}
								}
							}
						}

						$.ajax({
							url: '<?php echo base_url($controller . '/edit') ?>',
							type: 'POST',
							contentType: false,
							processData: false,
							data: fd,
							success: function(response) {
								csrfHash = response.token;
								if (response.success === true) {
									Swal.fire({
										position: 'bottom-end',
										icon: 'success',
										title: response.messages,
										showConfirmButton: false,
										timer: 1500
									}).then(function() {
										$('#data_table').DataTable().ajax.reload(null, false).draw(false);
										removeModalListener('#edit-modal');
										$('#edit-modal').modal('hide');
									});
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
										});
									}
								}
								$('#edit-form-btn').html('<i class="fa fa-check"></i> Simpan');
								$('#edit-form-btn').prop('disabled', false);
							},
							error: function() {
								$('#edit-form-btn').html('<i class="fa fa-check"></i> Simpan');
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

	function download(id) {
		if (!id) {
			return swal('error', 'File tidak ditemukan');
		}

		window.open(file_url('gambar_kerja', id, true), '_blank');
	}

	function remove(id_tipe) {
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
						id_tipe: id_tipe
					},
					dataType: 'json',
					success: function(response) {
						csrfHash = response.token;
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

	function view(id_tipe) {
		edit(id_tipe, true);
	}

	function listKavling(id_tipe) {
		$.ajax({
			url: '<?php echo base_url($controller . '/getListKavling') ?>',
			type: 'post',
			data: {
				[csrfName]: csrfHash,
				id_tipe: id_tipe
			},
			dataType: 'json',
			success: function(response) {
				csrfHash = response.token;
				if (response.success) {
					if (response.data.length === 0) {
						Swal.fire({
							icon: 'info',
							title: 'Informasi',
							text: 'Tidak ada kavling yang menggunakan tipe ini.',
						});
					} else {
						let html = '<ul class="list-group list-group-flush text-left">';
						let seq = 1;
						response.data.forEach(function(item) {
							let jalanName = item.nama_jalan ? item.nama_jalan : '-';
							let labelTitle = seq + '. ' + jalanName + ' / ' + item.no_kavling;
							html += '<li class="list-group-item d-flex justify-content-between align-items-center">' + labelTitle;
							if (item.status_kavling) {
								let badgeClass = 'badge-light-primary';
								if (item.status_kavling.toLowerCase() == 'terjual') badgeClass = 'badge-light-success';
								html += ' <span class="badge ' + badgeClass + '">' + item.status_kavling + '</span>';
							}
							html += '</li>';
							seq++;
						});
						html += '</ul>';
						
						Swal.fire({
							title: 'Daftar Kavling',
							html: '<div style="max-height: 300px; overflow-y: auto;">' + html + '</div>',
							confirmButtonText: 'Tutup',
							width: '400px'
						});
					}
				}
			}
		});
	}
</script>
