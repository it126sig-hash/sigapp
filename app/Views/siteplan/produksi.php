<style>
	.select2-selection__choice {
		display: block;
		margin: 2px 0;
	}

	.select2-container--default .select2-selection--multiple {
		height: auto;
	}
</style>
<div class="modal fade text-left" id="modal_produksi_add_jalan" tabindex="-1" role="dialog"
	aria-labelledby="modal_produksi_add_jalan" aria-hidden="true">
	<div class="modal-dialog modal-sm" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Tambah Jalan Produksi</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<form id="fm-produksi-add-jalan" class="add-new-record modal-content pt-0">
				<div class="modal-body">
					<input type="hidden" class="form-control" id="prod_jalan_points" name="points" value="" />

					<div class="form-group">
						<label for="prod_jalan_id_cluster">Cluster</label>
						<select id="prod_jalan_id_cluster" name="id_cluster" class="select2 form-control"></select>
					</div>

					<div class="form-group">
						<label for="prod_jalan_id_jalan">Blok/Jalan</label>
						<select disabled id="prod_jalan_id_jalan" name="id_jalan" class="select2 form-control"></select>
					</div>

					<div class="form-group">
						<label for="prod_jalan_progres">Progres</label>
						<input type="range" onInput="$('.prod_jalan_r_progres').html($(this).val())" class="form-control-range"
							min="0" max="100" step="1" id="prod_jalan_progres" name="f_progres_jalan" value="0">
						<span class="prod_jalan_r_progres">0</span><span>%</span>
					</div>

					<div class="form-group">
						<label for="prod_jalan_luas">Luas Dilapangan</label>
						<input type="text" class="form-control" id="prod_jalan_luas" name="f_produksi_luas"
							placeholder="Luas jalan dilapangan" />
					</div>

					<div class="form-group">
						<label for="prod_jalan_keterangan">Keterangan</label>
						<textarea class="form-control" id="prod_jalan_keterangan" name="f_produksi_keterangan" rows="3"
							placeholder="Keterangan"></textarea>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" id="save_produksi_add_jalan-btn" class="btn btn-primary data-submit mr-1"
						onclick="save_jalan_produksi()" href="javascript:void(0)">Simpan</button>
					<button type="button" class="btn btn-outline-secondary" onclick="cancel_tambah_jalan_produksi()">Cancel</button>
				</div>
			</form>
		</div>
	</div>
</div>
<div class="modal fade text-left" id="modal_fothersproduksi" tabindex="-1" role="dialog"
	aria-labelledby="modal_fothersproduksi" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Progres Jalan Produksi</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<form id="fm-fotherproduksi" enctype="multipart/form-data" class="add-new-record modal-content pt-0">
				<div class="modal-body">
					<p class="modal-title label_alamat" id="label_fothersproduksi"></p>
					<input type="hidden" class="form-control id_kavling" name="id_kavling" value="" />
					<input type="hidden" class="form-control" id="id_proyek" name="id_produksi" value="" />

					<ul class="nav nav-tabs" role="tablist">
						<li class="nav-item">
							<a class="nav-link active" id="prod-jalan-progress-tab" data-toggle="tab"
								href="#prod-jalan-progress" role="tab" aria-selected="true">Progres</a>
						</li>
						<li class="nav-item produksi-jalan-only">
							<a class="nav-link" id="prod-jalan-history-tab" data-toggle="tab" href="#prod-jalan-history"
								role="tab" aria-selected="false">History</a>
						</li>
					</ul>

					<div class="tab-content pt-1">
						<div class="tab-pane active" id="prod-jalan-progress" aria-labelledby="prod-jalan-progress-tab"
							role="tabpanel">
							<div class="row">
								<div class="col-md-6">
									<span>Luas di Siteplan : <br>
										<span class='t_luas_planning'></span>
									</span>
								</div>
								<div class="col-md-6">
									<span>Luas di Sertifikat : <br>
										<span class='t_luas_legal'></span>
									</span>
								</div>
							</div>
							<hr>

							<div class="form-group">
								<label for="f_progres_jalan">Progres</label>
								<input type="range" onInput="$('.r_progres').html($(this).val())" class="form-control-range"
									min="0" max="100" step="1" id="f_progres_jalan" name="f_progres_jalan">
								<span class="r_progres"></span><span>%</span>
							</div>
							<div class="form-group">
								<label for="f_progres_jalan">Status</label>
								<select id="slf_jenis" name="slf_jenis" class="form-control">
									<option value="">Basecourse</option>
									<option value="Basecourse">Basecourse</option>
									<option value="Paving">Paving</option>
								</select>
							</div>

							<div class="form-group">
								<label for="f_produksi_luas">Luas Dilapangan</label>
								<input type="text" class="form-control" id="f_produksi_luas" name="f_produksi_luas"
									placeholder="Luas jalan dilapangan" />
							</div>

							<div class="form-group">
								<label for="f_produksi_keterangan">Keterangan</label>
								<textarea class="form-control" id="f_produksi_keterangan" name="f_produksi_keterangan" rows="3"
									placeholder="Keterangan"></textarea>
							</div>

							<div class="form-group produksi-jalan-only">
								<label for="produksi_jalan_foto">Foto Kondisi Jalan Saat Ini</label>
								<div class="custom-file">
									<input type="file" class="custom-file-input" accept="image/*" multiple
										name="produksi_jalan_foto[]" id="produksi_jalan_foto"
										onchange="displayUploadedFiles(this, 'list_produksi_jalan_foto')" />
									<label class="custom-file-label" id="label_produksi_jalan_foto"
										for="produksi_jalan_foto">Bisa lebih dari 1 foto</label>
								</div>
								<div id="list_produksi_jalan_foto" class="mt-1"></div>
							</div>
						</div>
						<div class="tab-pane produksi-jalan-only" id="prod-jalan-history" aria-labelledby="prod-jalan-history-tab"
							role="tabpanel">
							<div id="produksi_jalan_history" class="pt-1"></div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" id="save_fotherproduksi-btn" class="btn btn-primary data-submit mr-1"
						onclick="save_fotherproduksi()" href="javascript:void(0)">Simpan</button>
					<button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
				</div>
			</form>
		</div>
	</div>
</div>
<div class="modal fade text-left" id="modal_divisi7" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-dialog-scrollable modal-xl">
		<div class="modal-content pt-0">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Produksi</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form id="fm-produksi" enctype="multipart/form-data" class="add-new-record">

					<div class="row">
						<div class="col-md-9">
							<p class="modal-title label_alamat" id="label_alamat7"></p>
						</div>
						<div class="col-md-3">
							<button id="download_gambar_kerja" type="button"
								class="btn btn-primary btn-block waves-effect">Unduh Gambar Kerja</button>
						</div>

					</div>

					<hr>
					<input type="hidden" class="form-control id_kavling" name="id_kavling" value="" />
					<input type="hidden" class="form-control" id="id_produksi" name="id_produksi" value="" />
					<ul class="nav nav-tabs" role="tablist">
						<li class="nav-item">
							<a class="nav-link active" id="fm-prod-progress-tab" data-toggle="tab"
								href="#fm-prod-progress" role="tab" aria-selected="true">Progres</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" id="fm-prod-dokumentasi-tab" data-toggle="tab"
								href="#fm-prod-dokumentasi" role="tab" aria-selected="true">Dokumentasi Bangunan</a>
						</li>
						<!-- <li class="nav-item">
							<a class="nav-link" id="fm-prod-slf-tab" data-toggle="tab" href="#fm-prod-slf" role="tab" aria-selected="true">SLF</a>
						</li> -->
						<li class="nav-item">
							<a class="nav-link" id="fm-prod-jalan-tab" data-toggle="tab" href="#fm-prod-jalan"
								role="tab" aria-selected="true">Jalan</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" id="fm-prod-listrik-tab" data-toggle="tab" href="#fm-prod-listrik"
								role="tab" aria-selected="true">Listrik</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" id="fm-prod-air-tab" data-toggle="tab" href="#fm-prod-air" role="tab"
								aria-selected="true">Air</a>
						</li>
					</ul>
					<div class="tab-content">
						<div class="tab-pane active" id="fm-prod-progress" aria-labelledby="fm-prod-progress-tab"
							role="tabpanel">
							<div class="row">
								<div class="col-md-3">
									<div class="form-group">
										<div class="custom-control custom-switch custom-control-inline">
											<input type="checkbox" value="1" class="custom-control-input cbp" id="st_0"
												name="st_0" />
											<label class="custom-control-label" for="st_0">sd Sloof</label>
										</div>
									</div>
									<div class="form-group">
										<div class="custom-control custom-switch custom-control-inline">
											<input type="checkbox" value="1" class="custom-control-input cbp" id="st_25"
												name="st_25" />
											<label class="custom-control-label" for="st_25">Dinding sd Ringbalok</label>
										</div>
									</div>
									<div class="form-group">
										<div class="custom-control custom-switch custom-control-inline">
											<input type="checkbox" value="1" class="custom-control-input cbp" id="st_50"
												name="st_50" />
											<label class="custom-control-label" for="st_50">Dinding Full, Atap, PLester
												dan Aci</label>
										</div>
									</div>
									<div class="form-group">
										<div class="custom-control custom-switch custom-control-inline">
											<input type="checkbox" value="1" class="custom-control-input cbp" id="st_75"
												name="st_75" />
											<label class="custom-control-label" for="st_75">Plafon, Keramik, Dapur,
												Kamar Mandi dan Cat</label>
										</div>
									</div>
									<div class="form-group">
										<div class="custom-control custom-switch custom-control-inline">
											<input type="checkbox" value="1" class="custom-control-input cbp"
												id="st_100" name="st_100" />
											<label class="custom-control-label" for="st_100">Kusen, Pintu, Jendela,
												Kaca, Halaman dan Finishing</label>
										</div>
									</div>
									<div class="form-group">
										<div class="custom-control custom-switch custom-control-inline">
											<input type="checkbox" value="1" class="custom-control-input cbp"
												id="st_saluran" name="st_saluran" />
											<label class="custom-control-label" for="st_saluran">Saluran Jalan</label>
										</div>
									</div>
									<div class="form-group">
										<div class="custom-control custom-switch custom-control-inline">
											<input type="checkbox" value="1" class="custom-control-input cbp"
												id="st_jalan" name="st_jalan" />
											<label class="custom-control-label" for="st_jalan">Listrik</label>
										</div>
									</div>
									<div class="form-group">
										<div class="custom-control custom-switch custom-control-inline">
											<input type="checkbox" value="1" class="custom-control-input cbp"
												id="st_air" name="st_air" />
											<label class="custom-control-label" for="st_air">Air</label>
										</div>
									</div>
									<!-- <div class="af"> -->
									<div class="">
										<div class="form-group">
											<div class="custom-control custom-switch custom-control-inline">
												<input type="checkbox" value="1" class="custom-control-input cbp"
													id="slo" name="slo" />
												<label class="custom-control-label" for="slo">SLO / NIDI</label>
											</div>
										</div>
										<!-- <div class="form-group">
											<div class="custom-control custom-switch custom-control-inline">
												<input type="checkbox" value="1" class="custom-control-input cbp"
													id="bp" name="bp" />
												<label class="custom-control-label" for="bp">BP</label>
											</div>
										</div> -->

									</div>

								</div>
								<div class="col-md-3">
									<div class="divider">
										<div class="divider-text">LPA</div>
									</div>
									<div class="form-group">
										<div class="custom-control custom-switch custom-control-inline">
											<input type="checkbox" value="1" class="custom-control-input cbp" id="lpa"
												name="lpa" />
											<label class="custom-control-label" for="lpa">LPA</label>
										</div>
									</div>
									<div class="form-group">
										<label>Tanggal LPA</label>
										<input type="text" class="form-control flatpickr-human-friendly"
											id="lpa_tanggal" name="lpa_tanggal">
									</div>
									<div class="divider">
										<div class="divider-text">Sumur Bor</div>
									</div>
									<div class="form-group">
										<div class="custom-control custom-switch custom-control-inline">
											<input type="checkbox" value="1" class="custom-control-input cbp" id="sumurbor"
												name="sumurbor" />
											<label class="custom-control-label" for="sumurbor">Sumur Bor</label>
										</div>
									</div>
									<div class="form-group">
										<label>Tanggal Pemasangan Sumur Bor</label>
										<input type="text" class="form-control flatpickr-human-friendly"
											id="sumurbor_tanggal" name="sumurbor_tanggal">
									</div>
									<div class="form-group">
										<label for="sumurbor_keterangan">Keterangan Sumur Bor</label>
										<textarea class="form-control" id="sumurbor_keterangan"
											name="sumurbor_keterangan" rows="3" placeholder="Keterangan"></textarea>

										<small id="last_update-sumurbor" class="text-muted"></small>
									</div>
								</div>

								<div class="col-md-3">
									<div class="form-group">
										<label for="progres_bangunan">Progres Bangunan</label>
										<input type="range" class="form-control-range" value="0" id="progres_bangunan"
											name="progres_bangunan" step="1">
										<span id="t_progres_bangunan"></span>%
									</div>
									<div class="form-group">
										<label for="produksi_keterangan">Keterangan Pembangunan</label>
										<textarea class="form-control" id="produksi_keterangan"
											name="produksi_keterangan" rows="3" placeholder="Keterangan"></textarea>
									</div>

								</div>
								<div class="col-md-3">
									<div class="divider">
										<div class="divider-text">Tanggal Pembangunan Rumah</div>
									</div>
									<div>

										<div class="form-group">
											<label>Tanggal Pembangunan</label>
											<input type="text" class="form-control tanggal_pembangunan flatpickr-human-friendly tgl_bangun"
												id="tanggal_pembangunan" name="tanggal_pembangunan">
											<input type="text" class="hidden" id="tanggal_pembangunan_old"
												name="tanggal_pembangunan_old">
										</div>
										<span class="text-muted" id="lu-tanggal_pembangunan"></span>

										<div class="form-group">
											<label>Tanggal Rencana Selesai Pembangunan</label>
											<input type="text" class="form-control tanggal_rencana_selesai_pembangunan flatpickr-human-friendly tgl_bangun"
												id="tanggal_rencana_selesai_pembangunan"
												name="tanggal_rencana_selesai_pembangunan">
											<input type="text" class="hidden" id="tanggal_rencana_selesai_pembangunan_old"
												name="tanggal_rencana_selesai_pembangunan_old">
										</div>
										<span class="text-muted" id="lu-tanggal_rencana_selesai_pembangunan"></span>


										<div class="form-group">
											<label>Tanggal Selesai Pembangunan</label>
											<input type="text" class="form-control flatpickr-human-friendly "
												id="tanggal_selesai_pembangunan" name="tanggal_selesai_pembangunan">
											<input type="text" class="hidden" id="tanggal_selesai_pembangunan_old"
												name="tanggal_selesai_pembangunan_old">
										</div>
										<span class="text-muted" id="lu-tanggal_selesai_pembangunan"></span>


										<div class="hidden">
											<div class="form-group">
												<label>Diinput oleh</label>
												<input type="text" class="form-control" id="tanggal_pembangunan_oleh" disabled
													name="tanggal_pembangunan_oleh">
											</div>
											<div class="form-group">
												<label>Diinput Pada</label>
												<input type="text" class="form-control flatpickr-human-friendly"
													id="tanggal_pembangunan_pada" disabled name="tanggal_pembangunan_pada">
											</div>
											<div class="form-group">
												<label>Diubah oleh</label>
												<input type="text" class="form-control" id="tanggal_pembangunan_diubah_oleh"
													disabled name="tanggal_pembangunan_diubah_oleh">
											</div>
											<div class="form-group">
												<label>Diubah Pada</label>
												<input type="text" class="form-control flatpickr-human-friendly"
													id="tanggal_pembangunan_diubah_pada" disabled
													name="tanggal_pembangunan_diubah_pada">
											</div>
											<div class="form-group">
												<label>Diinput oleh</label>
												<input type="text" class="form-control" id="tanggal_selesai_pembangunan_oleh"
													disabled name="tanggal_selesai_pembangunan_oleh">
											</div>
											<div class="form-group">
												<label>Diinput Pada</label>
												<input type="text" class="form-control flatpickr-human-friendly"
													id="tanggal_selesai_pembangunan_pada" disabled
													name="tanggal_selesai_pembangunan_pada">
											</div>
											<div class="form-group">
												<label>Diubah oleh</label>
												<input type="text" class="form-control"
													id="tanggal_selesai_pembangunan_diubah_oleh" disabled
													name="tanggal_selesai_pembangunan_diubah_oleh">
											</div>
											<div class="form-group">
												<label>Diubah Pada</label>
												<input type="text" class="form-control flatpickr-human-friendly"
													id="tanggal_selesai_pembangunan_diubah_pada" disabled
													name="tanggal_selesai_pembangunan_diubah_pada">
											</div>
										</div>
									</div>
								</div>
							</div>



							<div class="form-group hidden" style="min-height:100px; height: auto;">
								<label>RAB</label>
								<div class="custom-file">
									<input type="file" class="custom-file-input" accept=".xls,.xlsx,.pdf"
										name="rab_dokumen[]" id="rab_dokumen"
										onchange="displayUploadedFiles(this, 'list_rab_dokumen')" />
									<label class="custom-file-label" id="label_rab_dokumen" for="rab_dokumen">Upload
										dokuemn RAB</label>
								</div>
								<div id="list_rab_dokumen" style="display: flex; flex-wrap: wrap;"></div>
							</div>
						</div>
						<div class="tab-pane" id="fm-prod-dokumentasi" aria-labelledby="fm-prod-dokumentasi-tab"
							role="tabpanel">
							<div class="form-group foto-container">
								<label>Foto Konstruksi(Pembesian, Pondaasi Sloof & Kolom Ringbalok, Pekerjaan Dinding,
									Pekerjaan Atap & Plafon)</label>
								<div class="custom-file">
									<input type="file" class="custom-file-input" accept="image/*"
										name="prod_foto_konstruksi[]" id="prod_foto_konstruksi" multiple
										onchange="displayUploadedFiles(this, 'list_prod_foto_konstruksi')" />
									<label class="custom-file-label" id="label_prod_foto_konstruksi"
										for="prod_foto_konstruksi">Bisa Lebih dari 1 foto</label>
								</div>
								<div id="list_prod_foto_konstruksi" style="display: flex; flex-wrap: wrap;"></div>
							</div>
							<hr>
							<div class="form-group foto-container">
								<label for="upload_komplain_produksi">Foto Exterior(Depan dan Belakang, foto memiliki
									titik koordinat)</label>
								<div class="custom-file">
									<input type="file" class="custom-file-input" accept="image/*"
										name="prod_foto_exterior[]" id="prod_foto_exterior" multiple
										onchange="displayUploadedFiles(this, 'list_prod_foto_exterior')" />
									<label class="custom-file-label" id="label_prod_foto_exterior"
										for="prod_foto_exterior">Bisa Lebih dari 1 foto</label>
								</div>
								<div id="list_prod_foto_exterior" style="display: flex; flex-wrap: wrap;"></div>
							</div>
							<hr>
							<div class="form-group foto-container">
								<label for="upload_komplain_produksi">Foto Interior(kamar, dapur, toilet, ruang tengah,
									finishing cat kusen & pintu. Foto memiliki titik koordinat)</label>
								<div class="custom-file">
									<input type="file" class="custom-file-input" accept="image/*"
										name="prod_foto_interior[]" id="prod_foto_interior" multiple
										onchange="displayUploadedFiles(this, 'list_prod_foto_interior')" />
									<label class="custom-file-label" id="label_prod_foto_interior"
										for="prod_foto_interior">Bisa Lebih dari 1 foto</label>

								</div>
								<div id="list_prod_foto_interior" style="display: flex; flex-wrap: wrap;"></div>
							</div>

						</div>
						<!-- <div class="tab-pane" id="fm-prod-slf" aria-labelledby="fm-prod-slf-tab" role="tabpanel">
							<div class="divider">
								<div class="divider-text">Surat Pernyataan Laik Fungsi/Sertifikat Laik Fungsi (SLF)</div>
							</div>
							<div class="form-group">
								<label>Jenis Dokumen</label>
								<select id="slf_jenis" name="slf_jenis" class="form-control">
									<option value="SLF">SLF</option>
									<option value="Surat Pernyataan">Surat Pernyataan</option>
								</select>
							</div>
							<div id="slf-input-form">
								<div class="form-group">
									<label>No Surat Pernyataan Laik Fungsi/Sertifikat Laik Fungsi (sesuai dokumen)</label>
									<input type="text" class="form-control" id="slf_no" name="slf_no">
								</div>
								<div class="form-group">
									<label>Tanggal Surat Pernyataan Laik Fungsi/Sertifikat Laik Fungsi (sesuai dokumen)</label>
									<input type="text" class="form-control flatpickr-human-friendly" id="slf_tanggal" name="slf_tanggal">
								</div>
								<div class="form-group foto-container">
									<label for="label_slf_dokumen">Dokumen Surat Pernyataan Laik Fungsi/Sertifikat Laik Fungsi</label>
									<div class="custom-file">
										<input type="file" class="custom-file-input" accept="application/pdf" name="slf_dokumen[]" id="slf_dokumen" onchange="displayUploadedFiles(this, 'list_slf_dokumen')" />
										<label class="custom-file-label" id="label_slf_dokumen" for="slf_dokumen"></label>
									</div>
									<div id="list_slf_dokumen"></div>
								</div>
							</div>
							<div id="surat_pernyataan-input-form" class="hidden">
								<div class="form-group">
									<label>No Surat Pernyataan Laik Fungsi(sesuai dokumen)</label>
									<input type="text" class="form-control" id="surat_pernyataan_no" name="surat_pernyataan_no">
								</div>
								<div class="form-group">
									<label>NPWP Penertbit Surat Pernyataan Laik Fungsi (sesuai dokumen)</label>
									<input type="text" class="form-control" id="surat_pernyataan_npwp" name="surat_pernyataan_npwp">
								</div>
								<div class="form-group">
									<label>Nama Penertbit Surat Pernyataan Laik Fungsi (sesuai dokumen)</label>
									<input type="text" class="form-control" id="surat_pernyataan_nama" name="surat_pernyataan_nama">
								</div>
								<div class="form-group">
									<label>Tanggal Surat Pernyataan Laik Fungsi</label>
									<input type="text" class="form-control flatpickr-human-friendly" id="surat_pernyataan_tanggal" name="surat_pernyataan_tanggal">
								</div>
								<div class="form-group foto-container">
									<label for="label_surat_pernyataan_dokumen">Tanggal Surat Pernyataan Laik Fungsi</label>
									<div class="custom-file">
										<input type="file" class="custom-file-input" accept="application/pdf" name="surat_pernyataan_dokumen" id="surat_pernyataan_dokumen" onchange="displayUploadedFiles(this, 'list_surat_pernyataan_dokumen')" />
										<label class="custom-file-label" id="label_surat_pernyataan_dokumen" for="surat_pernyataan_dokumen"></label>

									</div>
									<div id="list_surat_pernyataan_dokumen" style="display: flex; flex-wrap: wrap;"></div>
								</div>
							</div>
						</div> -->
						<div class="tab-pane" id="fm-prod-jalan" aria-labelledby="fm-prod-jalan-tab" role="tabpanel">
							<div class="divider">
								<div class="divider-text">Foto Jalan</div>
							</div>
							<div>
								<div class="form-group foto-container">
									<label for="jalan_foto">Foto Jalan</label>
									<div class="custom-file">
										<input type="file" class="custom-file-input" accept="image/*"
											name="jalan_foto[]" id="jalan_foto"
											onchange="displayUploadedFiles(this, 'list_jalan_foto')" />
										<label class="custom-file-label" id="label_jalan_foto" for="jalan_foto"></label>
									</div>
									<div id="list_jalan_foto" style="display: flex; flex-wrap: wrap;"></div>
								</div>
							</div>
							<div>
								<div class="form-group foto-container">
									<label for="jalan_foto_update">Foto Jalan Update/Setelah Akad(Paving)</label>
									<div class="custom-file">
										<input type="file" class="custom-file-input" accept="image/*"
											name="jalan_foto_update[]" id="jalan_foto"
											onchange="displayUploadedFiles(this, 'list_jalan_foto_update')" />
										<label class="custom-file-label" id="label_jalan_foto_update"
											for="jalan_foto_update"></label>
									</div>
									<div id="list_jalan_foto_update" style="display: flex; flex-wrap: wrap;"></div>
								</div>
							</div>

						</div>
						<div class="tab-pane" id="fm-prod-listrik" aria-labelledby="fm-prod-listrik-tab"
							role="tabpanel">
							<div class="divider">
								<div class="divider-text">Ketersediaan Listrik</div>
							</div>
							<div class="form-group">
								<label>Jenis Sumber Listrik</label>
								<select id="listrik_jenis" name="listrik_jenis" class="form-control">
									<option value="PLN">PLN</option>
									<option value="Disendiakan Pengembang">Disendiakan Pengembang (Dalam Pengajuan)
									</option>
								</select>
							</div>
							<div id="listrik-pln-input-form">
								<div class="form-group">
									<label>No ID Pelanggan/Nomor Meteran Listrik PLN</label>
									<input type="text" class="form-control" id="listrik_pln" name="listrik_pln">
								</div>
								<div class="form-group foto-container">
									<label for="label_slf_dokumen">Foto Ketersediaan Lampu Menyala</label>
									<div class="custom-file">
										<input type="file" class="custom-file-input" accept="image/*"
											name="listrik_pln_foto[]" id="listrik_pln_foto"
											onchange="displayUploadedFiles(this, 'list_listrik_pln_foto')" />
										<label class="custom-file-label" id="label_slf_dokumen"
											for="slf_dokumen"></label>

									</div>
									<div id="list_listrik_pln_foto" style="display: flex; flex-wrap: wrap;"></div>
								</div>
							</div>
							<div id="listrik_disediakan" class="hidden">
								<div class="form-group">
									<label>No Pengajuan Listrik PLN</label>
									<input type="text" class="form-control" id="listrik_disediakan_no"
										name="listrik_disediakan_no">
								</div>
								<div class="form-group">
									<label>Tanggal Pengajuan Listrik PLN</label>
									<input type="text" class="form-control flatpickr-human-friendly"
										id="listrik_disediakan_tanggal" name="listrik_disediakan_tanggal">
								</div>
								<div class="form-group foto-container">
									<label for="label_listrik_disediakan_dokumen">Upload Bukti Pengajuan</label>
									<div class="custom-file">
										<input type="file" class="custom-file-input" accept="application/pdf"
											name="listrik_disediakan_dokumen" id="listrik_disediakan_dokumen"
											onchange="displayUploadedFiles(this, 'list_listrik_disediakan_dokumen')" />
										<label class="custom-file-label" id="label_listrik_disediakan_dokumen"
											for="listrik_disediakan_dokumen"></label>

									</div>
									<div id="list_listrik_disediakan_dokumen" style="display: flex; flex-wrap: wrap;">
									</div>
								</div>
								<div class="form-group foto-container">
									<label for="listrik_disediakan_foto">Foto Ketersediaan Lampu Menyala</label>
									<div class="custom-file">
										<input type="file" class="custom-file-input" accept="image/*"
											name="listrik_disediakan_foto" id="listrik_disediakan_foto"
											onchange="displayUploadedFiles(this, 'list_listrik_disediakan_foto')" />
										<label class="custom-file-label" id="labe_listrik_disediakan_foto"
											for="listrik_disediakan_foto"></label>

									</div>
									<div id="list_listrik_disediakan_foto" style="display: flex; flex-wrap: wrap;">
									</div>
								</div>
							</div>
						</div>
						<div class="tab-pane" id="fm-prod-air" aria-labelledby="fm-prod-air-tab" role="tabpanel">
							<div class="divider">
								<div class="divider-text">Ketersediaan Air</div>
							</div>
							<div class="form-group">
								<label>Jenis Sumber Air</label>
								<select id="air_jenis" name="air_jenis" class="form-control">
									<option value="Air Tanah">Air Tanah</option>
									<option value="Komunal Warga">Komunal Warga</option>
									<option value="PDAM">PDAM</option>
								</select>
							</div>
							<div id="air_tanah-input_form">
								<div class="form-group foto-container">
									<label for="air_tanah">Foto ketersediaan air bersih dengan air mengalir & sumber air
										(min. 1 foto)</label>
									<div class="custom-file">
										<input type="file" class="custom-file-input" accept="image/*" name="air_tanah[]"
											id="air_tanah" multiple
											onchange="displayUploadedFiles(this, 'list_air_tanah')" />
										<label class="custom-file-label" id="label_air_tanah" for="air_tanah"></label>

									</div>
									<div id="list_air_tanah" style="display: flex; flex-wrap: wrap;"></div>
								</div>
							</div>
							<div id="air_komunal-input_form" class="hidden">
								<div class="form-group foto-container">
									<label for="air_komunal">Foto ketersediaan air bersih dengan air mengalir & sumber
										air komunal bersama (min. 1 foto)</label>
									<div class="custom-file">
										<input type="file" class="custom-file-input" accept="image/*"
											name="air_komunal[]" id="air_komunal" multiple
											onchange="displayUploadedFiles(this, 'list_air_komunal')" />
										<label class="custom-file-label" id="label_air_komunal"
											for="air_komunal"></label>

									</div>
									<div id="list_air_komunal" style="display: flex; flex-wrap: wrap;"></div>
								</div>
							</div>
							<div id="air_pdam-input_form" class="hidden">
								<div class="form-group">
									<label>No Meteran Air PDAM</label>
									<input type="text" class="form-control" id="air_pdam_no" name="air_pdam_no">
								</div>
								<div class="form-group foto-container">
									<label for="air_pdam">Foto ketersediaan air bersih dengan air mengalir & meteran air
										PDAM (min. 1 foto)</label>
									<div class="custom-file">
										<input type="file" class="custom-file-input" accept="image/*" name="air_pdam[]"
											id="air_pdam" multiple
											onchange="displayUploadedFiles(this, 'list_air_pdam')" />
										<label class="custom-file-label" id="label_air_pdam" for="air_pdam"></label>

									</div>
									<div id="list_air_pdam" style="display: flex; flex-wrap: wrap;"></div>
								</div>
							</div>
							<div class="form-group">
								<label>Deskripsi Unit (informasi keunggulan unit)</label>
								<input type="text" class="form-control" id="air_deskripsi_unit"
									name="air_deskripsi_unit">
							</div>
						</div>
					</div>

					<div class="divider hidden">
						<div class="divider-text">Checklist</div>
					</div>
					<p>
						<button data-toggle="collapse" href="#collapseExample" type="button"
							class="btn btn-outline-primary btn-block waves-effect hidden">Tampilkan Checklist</button>
					</p>
					<div class="collapse" id="collapseExample">
						<small id="last_update_checklist_prod" class="text-muted"></small>
						<div class="card card-body">
							<?php
							$n = 1;
							foreach ($list as $l) {
								echo '
                                    <div class="divider">
                                        <div class="divider-text">' . $n . '.) ' . $l->nama_group . ' - ' . $l->nama_item . '</div>
                                    </div>
                                    <dl class="row">
                                        <dd class="col-sm-2">' . $l->nama_subitem . '</dd>
                                        <dd class="col-md-2">
                                            <div class="form-group">
                                                <div class="custom-control custom-switch custom-control-inline">
                                                    <input type="checkbox" value="1" class="custom-control-input" id="hasil_cek_t[' . $l->id_subitem . ']" name="hasil_cek_t[' . $l->id_subitem . ']"/>
                                                    <label class="custom-control-label" for="hasil_cek_t[' . $l->id_subitem . ']">Tes</label>
                                                </div>
                                            </div>
                                        </dd>
                                        <dd class="col-md-2">
                                            <div class="form-group">
                                                <div class="custom-control custom-switch custom-control-inline">
                                                    <input type="checkbox" value="1" class="custom-control-input" id="hasil_cek_f[' . $l->id_subitem . ']" name="hasil_cek_f[' . $l->id_subitem . ']"/>
                                                    <label class="custom-control-label" for="hasil_cek_f[' . $l->id_subitem . ']">Fungsi</label>
                                                </div>
                                            </div>
                                        </dd>
                                        <dd class="col-md-2">
                                            <div class="form-group">
                                                <div class="custom-control custom-switch custom-control-inline">
                                                    <input type="checkbox" value="1" class="custom-control-input" id="hasil_cek_v[' . $l->id_subitem . ']" name="hasil_cek_v[' . $l->id_subitem . ']"/>
                                                    <label class="custom-control-label" for="hasil_cek_v[' . $l->id_subitem . ']">Visual</label>
                                                </div>
                                            </div>
                                        </dd>
                                        <dd class="col-sm-4"><textarea placeholder="keterangan" type="text" class="form-control" id="keterangan_cek_produksi[' . $l->id_subitem . ']" name="keterangan_cek_produksi[' . $l->id_subitem . ']"></textarea></dd>
                                    </dl>
                                    ';
								$n++;
							}
							?>

						</div>
					</div>


				</form>
			</div>
			<div class="modal-footer">
				<button id="add-form-btn-produksi" class="btn btn-primary data-submit mr-1" onclick="save_produksi()"
					href="javascript:void(0)">Simpan</button>
				<button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="modal_komplain_produksi">
	<div class="modal-dialog modal-dialog-scrollable modal-xl">
		<form id="fm-komplain-produksi" class="add-new-record modal-content pt-0">
			<div class="modal-header mb-1">
				<h5 class="modal-title" id="exampleModalLabel">Komplain Kavling</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button>
			</div>
			<div class="modal-body flex-grow-1">
				<p class="modal-title label_alamat" id="label_alamat5"></p>
				<hr>
				<ul class="nav nav-tabs" role="tablist">
					<li class="nav-item">
						<a class="nav-link active" id="fmkp-komplain-tab" data-toggle="tab" href="#fmkp-komplain"
							aria-controls="fmkp-komplain" role="tab" aria-selected="true">Komplain</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" id="fmkp-ditangani-tab" data-toggle="tab" href="#fmkp-ditangani"
							aria-controls="fmkp-ditangani" role="tab" aria-selected="true">Tangani</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" id="fmkp-selesai-tab" data-toggle="tab" href="#fmkp-selesai"
							aria-controls="fmkp-ditangani" role="tab" aria-selected="true">Selesai</a>
					</li>
				</ul>

				<input type="hidden" class="form-control id_kavling" name="id_kavling" value="" />
				<input type="hidden" class="form-control" id="id_komplain" name="id_komplain" value="" />
				<small id="last_update_komplain_produksi" class="text-muted"></small>

				<div class="tab-content">
					<div class="tab-pane active" id="fmkp-komplain" aria-labelledby="fmkp-komplain-tab" role="tabpanel">
						<div class="row">
							<div class="col-sm-12 col-md-6 col-lg-6">
								<div class="form-group">
									<label for="username_komplain_oleh">Dikomplain Oleh</label>
									<input readonly type="text" class="form-control" id="username_komplain_oleh"
										name="username_komplain_oleh" />
								</div>
								<div class="form-group">
									<label for="komplain_tgl">Tanggal Komplain</label>
									<input disabled type="text" class="form-control flatpickr-human-friendly"
										id="komplain_tgl" name="komplain_tgl" />
								</div>
								<div class="form-group">
									<label for="keterangan_komplain">Keterangan Komplain</label>
									<textarea readonly class="form-control" id="keterangan_komplain"
										name="keterangan_komplain" rows="3" placeholder="Keterangan"></textarea>
								</div>
							</div>
							<div class="col-sm-12 col-md-6 col-lg-6">
								<button id="komplain_selesai_btn_produksi" type="button"
									class="btn btn-outline-success btn-block waves-effect hidden">Komplain
									Selesai</button>
								<h5>Foto Komplain</h5>
								<!-- -----------------------------------dikomplain--------------------------------------- -->
								<div id="controls_produksi_foto_komplain_sales" class="carousel slide">
									<div class="carousel-inner" id="foto_komplain_sales">
										<!-- Foto komplain belongs here -->
									</div>
									<a class="carousel-control-prev" href="#controls_produksi_foto_komplain_sales"
										role="button" data-slide="prev">
										<span class="carousel-control-prev-icon" aria-hidden="true"></span>
										<span class="sr-only">Previous</span>
									</a>
									<a class="carousel-control-next" href="#controls_produksi_foto_komplain_sales"
										role="button" data-slide="next">
										<span class="carousel-control-next-icon" aria-hidden="true"></span>
										<span class="sr-only">Next</span>
									</a>
								</div>

							</div>
						</div>
					</div>
					<div class="tab-pane" id="fmkp-ditangani" aria-labelledby="fmkp-ditangani-tab" role="tabpanel">
						<div class="row">
							<div class="col-sm-12 col-md-6 col-lg-6">
								<!-- ------------------------------terima komplain------------------------------ -->
								<div class="divider">
									<div class="divider-text">Terima Komplain</div>
								</div>
								<div class="form-group">
									<div class="custom-control custom-switch custom-control-inline">
										<input type="checkbox" value="1" class="custom-control-input"
											id="terima_komplain" name="terima_komplain" />
										<label class="custom-control-label" for="terima_komplain">Terima
											Komplain</label>
									</div>
								</div>
								<div id="terima_komplain_div" class="hidden ditangani_form">
									<div class="form-group">
										<label for="keterangan_ditangani">Keterangan</label>
										<textarea class="form-control" id="keterangan_ditangani"
											name="keterangan_ditangani" rows="3" placeholder="Keterangan"></textarea>
									</div>
								</div>
								<div class="hidden ditangani_form">
									<div class="form-group">
										<label for="username_ditangani_oleh">Komplain Diterima Oleh</label>
										<input disabled type="text" class="form-control" id="username_ditangani_oleh"
											name="username_ditangani_oleh" />
									</div>
									<div class="form-group">
										<label for="ditangani_tgl">Tanggal Komplain Diterima</label>
										<input disabled type="text" class="form-control flatpickr-human-friendly"
											id="ditangani_tgl" name="ditangani_tgl" />
									</div>
								</div>
							</div>
							<div class="col-sm-12 col-md-6 col-lg-6">
								<!-- ---------------------------------------- komplain diselesaikan ---------------------------->
								<div id="selesaikan_komplain_div" class="hidden">
									<div class="divider">
										<div class="divider-text">Selesaikan Komplain</div>
									</div>

									<div class="form-group">
										<div class="custom-control custom-switch custom-control-inline">
											<input type="checkbox" value="1" class="custom-control-input"
												id="is_selesai_produksi" name="is_selesai_produksi" />
											<label class="custom-control-label" for="is_selesai_produksi">Selesaikan
												Komplain</label>
										</div>
									</div>
									<div id="div_upload_komplain_produksi">
										<label for="upload_komplain_produksi">Foto Perbaikan</label>
										<div class="custom-file">
											<input type="file" class="custom-file-input" accept="image/*"
												name="upload_komplain_produksi[]" id="upload_komplain_produksi"
												multiple />
											<label class="custom-file-label" id="label_upload_komplain_produksi"
												for="upload_komplain_produksi">Bisa Lebih dari 1 foto</label>
											<div id="list_upload_komplain_produksi"></div>
										</div>
									</div>
									<div class="form-group">
										<label for="selesai_keterangan_produksi">Keterangan </label>
										<textarea class="form-control" id="selesai_keterangan_produksi"
											name="selesai_keterangan_produksi" rows="3"
											placeholder="Keterangan"></textarea>
									</div>
									<div class="form-group">
										<label for="username_selesai_oleh_produksi">Diselesakan Oleh</label>
										<input disabled type="text" class="form-control"
											id="username_selesai_oleh_produksi" name="username_selesai_oleh_produksi" />
									</div>
									<div class="form-group">
										<label for="selesai_tgl_produksi">Tanggal Diselesaikan</label>
										<input disabled type="text" class="form-control flatpickr-human-friendly"
											id="selesai_tgl_produksi" name="selesai_tgl_produksi" />
									</div>
									<div id="controls_produksi_foto_komplain_produksi" class="carousel slide">
										<div class="carousel-inner" id="foto_komplain_produksi">
											<!-- Foto komplain belongs here -->
										</div>
										<a class="carousel-control-prev"
											href="#controls_produksi_foto_komplain_produksi" role="button"
											data-slide="prev">
											<span class="carousel-control-prev-icon" aria-hidden="true"></span>
											<span class="sr-only">Previous</span>
										</a>
										<a class="carousel-control-next"
											href="#controls_produksi_foto_komplain_produksi" role="button"
											data-slide="next">
											<span class="carousel-control-next-icon" aria-hidden="true"></span>
											<span class="sr-only">></span>
										</a>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="tab-pane" id="fmkp-selesai" aria-labelledby="fmkp-selesai -tab" role="tabpanel">
						<div class="row">
							<div class="col-sm-12 col-md-6 col-lg-6">
								<!-- ---------------------------------------- komplain diselesaikan ---------------------------->
								<div id="komplain_selesai_sip" class="hidden">
									<div class="divider">
										<div class="divider-text">Komplain Selesai (sales)</div>
									</div>
									<div class="form-group">
										<label for="selesai_keterangan_sales">Keterangan </label>
										<textarea disabled class="form-control" id="selesai_keterangan_sales"
											name="selesai_keterangan_sales" rows="3"
											placeholder="Keterangan"></textarea>
									</div>
									<div class="form-group">
										<label for="username_selesai_oleh_sales">Diselesakan Oleh</label>
										<input disabled type="text" class="form-control"
											id="username_selesai_oleh_sales" name="username_selesai_oleh_sales" />
									</div>
									<div class="form-group">
										<label for="selesai_tgl_sales">Tanggal Diselesaikan</label>
										<input disabled type="text" class="form-control flatpickr-human-friendly"
											id="selesai_tgl_sales" name="selesai_tgl_sales" />
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<a id="komplain-produksi-form-btn" class="btn btn-primary data-submit mr-1"
					onclick="save_komplain_produksi()" href="javascript:void(0)">Simpan</a>
				<button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
			</div>
		</form>
	</div>
</div>


<div class="modal fade text-left" id="modal-pr_slf" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-dialog-scrollable modal-xl">
		<div class="modal-content pt-0">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Produksi</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body" style="background-color:ccc">
				<div class="card">
					<div class="card-body">
						<p class="modal-title label_alamat"></p>
					</div>
				</div>

				<form id="fm-pr_slf" enctype="multipart/form-data">
					<div class="card">
						<div class="card-body">
							<ul class="nav nav-pills flex-column flex-md-row mt-1 row-gap-2" role="tablist">
								<li class="nav-item">
									<a class="nav-link active" id="fm-pr_list_slf-tab" data-toggle="tab"
										href="#fm-pr_list_slf" role="tab" aria-selected="true">List SLF</a>
								</li>
								<li class="nav-item">
									<a class="nav-link" id="fm-pr_cr_slf-tab" data-toggle="tab" href="#fm-pr_cr_slf"
										role="tab" aria-selected="true">Buat SLF</a>
								</li>
							</ul>
						</div>
					</div>

					<div class="card">
						<div class="card-body">
							<div class="tab-content">
								<div class="tab-pane active" id="fm-pr_list_slf" aria-labelledby="fm-pr_list_slf-tab"
									role="tabpanel">
									<div class="table-responsive">
										<table class="table mb-0">
											<thead>
												<tr>
													<th width="10px">No</th>
													<th width="150px">No SLF</th>
													<th>Kavling</th>
													<th width="180px">File</th>
													<th width="150px">Oleh</th>
												</tr>
											</thead>
											<tbody id="tb-pr_lsit_slf-here">
											</tbody>

										</table>
									</div>

								</div>
								<div class="tab-pane" id="fm-pr_cr_slf" aria-labelledby="fm-pr_cr_slf-tab"
									role="tabpanel">
									<div class="row">


										<div class="col-md-4">
											<div class="divider">
												<div class="divider-text">SURAT PERNYATAAN PEMERIKSAAN KELAIKAN FUNGSI
													BANGUNAN GEDUNG</div>
											</div>
											<div class="form-group">
												<label>No Surat Pernyataan</label>
												<input type="text" class="form-control" id="fm-slf-no_slf" name="no_slf"
													placeholder="" required
													value="...../PROD-..../EX/BTN/DIR/...../<?= date('Y') ?>" />
											</div>
											<div class="form-group">
												<label>Tanggal</label>
												<input type="text" class="form-control flatpickr-human-friendly"
													id="fm-slf-tgl_slf" required name="tgl_slf" placeholder=""
													value="" />
											</div>
											<div class="divider">
												<div class="divider-text">Penyedia Jasa Pengawas/MK/Instansi Teksnis
													Pembina
													Penyelenggaraan Bangunan gedung</div>
											</div>
											<div class="form-group">
												<label>Nama Penanggung Jawab</label>
												<input type="text" required class="form-control"
													id="fm-slf-penanggungjawab" name="penanggungjawab" placeholder=""
													value="" />
											</div>
											<div class="form-group">
												<label>Nama Perusahaan/Instansi Teknis</label>
												<input type="text" required class="form-control" readonly
													id="fm-slf-nama_perusahaan" name="nama_perusahaan" placeholder=""
													value="" />
											</div>
											<div class="divider">
												<div class="divider-text">Bangunan Gedung</div>
											</div>
											<div class="form-group">
												<label>Funsgi Utama </label>
												<input type="text" class="form-control" id="fm-slf-fungsi_utama"
													name="fungsi_utama" required placeholder="" value="Rumah Tinggal" />
											</div>
											<div class="form-group">
												<label>Funsgi Tambahan </label>
												<input type="text" class="form-control" id="fm-slf-fungsi_tambahan"
													name="fungsi_tambahan" placeholder="" value="" />
											</div>
											<div class="form-group">
												<label>Jenis Bangunan </label>
												<input type="text" class="form-control" id="fm-slf-jenis_bangunan"
													name="jenis_bangunan" required placeholder=""
													value="Rumah Tinggal" />
											</div>
											<div class="form-group">
												<label>Nama Bangunan Gedung </label>
												<input type="text" class="form-control" id="fm-slf-nama_bangunan"
													name="nama_bangunan" placeholder="" value="" />
											</div>
											<div class="form-group">
												<label>No Pendaftaran Bangunan </label>
												<input type="text" class="form-control"
													id="fm-slf-nomor_pendaftaran_bangunan"
													name="nomor_pendaftaran_bangunan" placeholder="" value="" />
											</div>
										</div>
										<div class="col-md-4">
											<div class="divider">
												<div class="divider-text">Lokasi Bangunan Gedung</div>
											</div>
											<div class="form-group">
												<label>Kampung</label>
												<input type="text" readonly class="form-control" id="fm-slf-kampung"
													name="kampung" placeholder="" value="" />
											</div>
											<div class="form-group">
												<label>Kelurahan/desa</label>
												<input type="text" readonly class="form-control" id="fm-slf-kelurahan"
													name="kelurahan" placeholder="" value="" />
											</div>
											<div class="form-group">
												<label>Kecamatan</label>
												<input type="text" readonly class="form-control" id="fm-slf-kecamatan"
													name="kecamatan" placeholder="" value="" />
											</div>
											<div class="form-group">
												<label>Kabupaten/Kota</label>
												<input type="text" readonly class="form-control" id="fm-slf-kota"
													name="kota" placeholder="" value="" />
											</div>
											<div class="form-group">
												<label>Provinsi</label>
												<input type="text" readonly class="form-control" id="fm-slf-provinsi"
													name="provinsi" placeholder="" value="" />
											</div>
											<div class="form-group">
												<label>Alamat lokasi terletak di</label>
												<input type="text" readonly class="form-control" id="fm-slf-alamat"
													name="alamat" placeholder="" value="" />
											</div>
											<div class="divider">
												<div class="divider-text">Permohonan</div>
											</div>
											<div class="form-group">
												<label>No Penerbitan SLF</label>
												<input type="text" class="form-control" id="fm-slf-penerbitan_slf_no"
													required name="penerbitan_slf_no" placeholder="" value="" />
											</div>
											<div class="form-group">
												<label>Tanggal Penerbitan SLF</label>
												<input type="text" class="form-control flatpickr-human-friendly"
													id="fm-slf-penerbitan_slf_tgl" required name="penerbitan_slf_tgl"
													placeholder="" value="" />
											</div>
											<div class="form-group">
												<label>No Perpanjangan SLF</label>
												<input type="text" class="form-control" id="fm-slf-perpanjangan_slf_no"
													name="perpanjangan_slf_no" placeholder="" value="" />
											</div>
											<div class="form-group">
												<label>Tanggal Perpanjangan SLF</label>
												<input type="text" class="form-control flatpickr-human-friendly"
													id="fm-slf-perpanjangan_slf_tgl" name="perpanjangan_slf_tgl"
													placeholder="" value="" />
											</div>
											<div class="form-group">
												<label>Perpanjangan ke</label>
												<input type="text" class="form-control" id="fm-slf-perpanjangan_slf_ke"
													name="perpanjangan_slf_ke" placeholder="" value="" />
											</div>
										</div>

										<div class="col-md-4">
											<div class="form-group">
												<label>Persyaratan Administrasi</label>
												<input type="text" class="form-control"
													id="fm-slf-persyaratan_administrasi" name="persyaratan_administrasi"
													placeholder="" value="" />
											</div>
											<div class="divider">
												<div class="divider-text">Persyaratan Teknis</div>
											</div>
											<div class="form-group">
												<label>Fungsi Bangunan</label>
												<input type="text" class="form-control" id="fm-slf-fungsi_bangunan"
													name="fungsi_bangunan" placeholder="" value="Layak" />
											</div>
											<div class="form-group">
												<label>Peruntukan</label>
												<input type="text" class="form-control" id="fm-slf-fungsi_peruntukan"
													name="fungsi_peruntukan" placeholder="" value="Sesuai" />
											</div>
											<div class="form-group">
												<label>Tata Bangunan</label>
												<input type="text" class="form-control" id="fm-slf-fungsi_tata_bangunan"
													name="fungsi_tata_bangunan" placeholder="" value="Sesuai" />
											</div>
											<div class="form-group">
												<label>Kelaikan Fungsi Bangunan gedung dinyatakan</label>
												<select class="form-control" id="fm-slf-persyaratan_kelaikan"
													name="persyaratan_kelaikan">
													<option value="Laik fungsi seluruhnya">Laik fungsi seluruhnya
													</option>
													<option value="Laik fungsi sebagian">Laik fungsi sebagian</option>
												</select>
											</div>
											<div class="divider">
												<div class="divider-text">Pilih Kavling</div>
											</div>
											<select name="id_kavling[]" required class="form-control-sm select2"
												id="fm-slf-id_kavling" multiple="multiple"></select>
										</div>
									</div>



								</div>

							</div>
						</div>
					</div>


				</form>
			</div>
			<div class="modal-footer">
				<button id="btn-slf-simpan" class="btn btn-primary data-submit mr-1" onclick="simpan_slf()"
					href="javascript:void(0)">Simpan</button>
				<button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade text-left" id="modal-bayar_produksi-prod" tabindex="-1" role="dialog" aria-labelledby="dana_akad_modal"
	aria-hidden="true">
	<div class="modal-dialog modal-dialog-scrollable modal-lg" role="document">
		<form id="fm-bayar_produksi-prod" class="" autocomplete="off">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="exampleModalLabel">Pembayaran Produksi</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body flex-grow-1" style="background-color:#eee">
					<div class="card">
						<div class="card-body">
							<p class="modal-title label_konsumen"></p>
							<p class="modal-title label_alamat"></p>
						</div>
					</div>
					<input type="hidden" class="form-control" id="bayar_produksi-id_kavling" name="id_kavling">

					<div id="div-bayar_produksi-here" class="row"></div>
				</div>
				<div class="modal-footer">
					<button id="add-form-btn-bayar_produksi" class="btn btn-primary data-submit mr-1"
						onclick="save_bayar_produksi(); return false;" href="javascript:void(0)">Simpan</button>
					<button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
				</div>
			</div>
		</form>
	</div>
</div>


<div class="modal fade text-left" id="modal-cashoutsubkon" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-dialog-scrollable modal-xl">
		<div class="modal-content pt-0">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Produksi</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-md-12">
						<div class="card">
							<div class="card-body bg-primary text-light">
								<p class="modal-title label_alamat"></p>
							</div>
						</div>
					</div>
					<div class="col-md-12">
						<div class="card">
							<div class="card-body pb-0 pt-0">
								<ul class="nav nav-pills flex-column flex-md-row mt-1 row-gap-2" role="tablist">
									<li class="nav-item">
										<a class="nav-link active" id="status-tab" data-toggle="tab" href="#status"
											aria-controls="detail_tagihan" role="tab" aria-selected="false">Status </a>
									</li>
								</ul>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button id="add-form-btn-produksi" class="btn btn-primary data-submit mr-1" onclick="save_produksi()"
					href="javascript:void(0)">Simpan</button>
				<button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
			</div>
		</div>
	</div>
</div>

<script>
	function updateState(elementId, variableName) {
  $(elementId).change(function () {
    window[variableName] = this.checked ? 1 : 0;
  });
}
/******************************** produksi ******************************************/
//cekbok produksi
var slo = 0,
  bp = 0,
  lpa = 0,
  tot = 0,
  st_0 = 0,
  st_25 = 0,
  st_50 = 0,
  st_75 = 0,
  st_100 = 0,
  st_saluran = 0,
  st_jalan = 0,
  st_air = 0;

updateState("#bp", "bp");
updateState("#slo", "slo");
updateState("#lpa", "lpa");
updateState("#st_0", "st_0");
updateState("#st_25", "st_25");
updateState("#st_50", "st_50");
updateState("#st_75", "st_75");
updateState("#st_100", "st_100");
updateState("#st_saluran", "st_saluran");

function isProduksiManualSelectionActive() {
  return $("#produksi_tambah_jalan").prop("checked");
}

function setProduksiJalanSelectionMode(active, clearSelection) {
  $("#produksi_tambah_jalan").prop("checked", active);
  $("#produksi_menu").toggleClass("produksi-jalan-selecting", active);
  $("#produksi_menu > button, #produksi_menu > .btn-icon")
    .not("#produksi_add_jalan_ok, #produksi_add_jalan_undo, #produksi_add_jalan_batal")
    .toggleClass("d-none", active);
  $("#produksi_add_jalan_ok, #produksi_add_jalan_undo, #produksi_add_jalan_batal").toggleClass(
    "d-none",
    !active,
  );
  $("#produksi_add_jalan_hint").toggleClass("d-none", !active);

  if (clearSelection) {
    hapus_seleksi();
  }
}

function start_tambah_jalan_produksi() {
  setProduksiJalanSelectionMode(true, true);
}

function cancel_tambah_jalan_produksi() {
  $("#modal_produksi_add_jalan").modal("hide");
  setProduksiJalanSelectionMode(false, true);
}

function tambah_jalan_produksi() {
  if (!isProduksiManualSelectionActive()) {
    setProduksiJalanSelectionMode(true, false);
  }

  if (!dtt || dtt.length < 6) {
    return swal("error", "Seleksi manual minimal 3 titik");
  }

  $("#fm-produksi-add-jalan")[0].reset();
  $("#prod_jalan_id_cluster, #prod_jalan_id_jalan").val(null).trigger("change");
  $("#prod_jalan_id_jalan").prop("disabled", true);
  $("#prod_jalan_points").val(dtt.join(","));
  $(".prod_jalan_r_progres").html("0");
  setProduksiJalanSelectionMode(false, false);

  $("#modal_produksi_add_jalan").modal({
    backdrop: "static",
    keyboard: false,
  });
}

function save_jalan_produksi() {
  let points = $("#prod_jalan_points").val().split(",").filter(function (point) {
    return point !== "";
  });

  if (!$("#prod_jalan_id_jalan").val()) {
    return swal("error", "Blok/Jalan harus diisi");
  }

  if (points.length < 6) {
    return swal("error", "Seleksi manual minimal 3 titik");
  }

  $.ajax({
    url: base_url + "api/produksi/add_jalan",
    type: "POST",
    data: $("#fm-produksi-add-jalan").serialize() + "&" + csrfName + "=" + csrfHash,
    dataType: "json",
    beforeSend: function () {
      $("#save_produksi_add_jalan-btn").prop("disabled", true);
      $("#save_produksi_add_jalan-btn").html(
        'Menyimpan <i class="fa fa-spinner fa-spin"></i>',
      );
    },
    success: function (r) {
      csrfHash = r.token;

      if (r.success === true) {
        Swal.fire({
          icon: "success",
          title: r.messages,
          showConfirmButton: false,
          timer: 1500,
        }).then(function () {
          $("#modal_produksi_add_jalan").modal("hide");
          load_kavling();
          setProduksiJalanSelectionMode(false, true);
        });
      } else {
        Swal.fire({
          icon: "error",
          title: r.messages,
          showConfirmButton: false,
        });
      }

      $("#save_produksi_add_jalan-btn").html("Simpan");
      $("#save_produksi_add_jalan-btn").prop("disabled", false);
    },
    error: function () {
      Swal.fire({
        icon: "error",
        title: "Terjadi kesalahan saat menambahkan jalan",
        showConfirmButton: false,
      });
      $("#save_produksi_add_jalan-btn").html("Simpan");
      $("#save_produksi_add_jalan-btn").prop("disabled", false);
    },
  });
}

$("#prod_jalan_id_cluster").select2({
  placeholder: "Pilih Cluster",
  allowClear: true,
  dropdownParent: $("#modal_produksi_add_jalan"),
  ajax: {
    url: base_url + "/cluster/getAll",
    dataType: "json",
    delay: 250,
    method: "post",
    data: function (params) {
      return {
        [csrfName]: csrfHash,
        search: params.term,
        id_proyek: dt_proyek.id_proyek,
      };
    },
    processResults: function (r) {
      csrfHash = r.token;

      let results = [];
      $.each(r.data, function (index, item) {
        results.push({
          id: item[0],
          text: item[3],
        });
      });

      return {
        results: results,
      };
    },
    cache: false,
  },
});

$("#prod_jalan_id_cluster").on("change", function () {
  $("#prod_jalan_id_jalan").val(null).trigger("change");
  $("#prod_jalan_id_jalan").prop("disabled", !this.value);
});

$("#prod_jalan_id_jalan").select2({
  placeholder: "Pilih Blok",
  allowClear: true,
  dropdownParent: $("#modal_produksi_add_jalan"),
  ajax: {
    url: base_url + "/jalan/getAll",
    dataType: "json",
    delay: 250,
    method: "post",
    data: function (params) {
      return {
        [csrfName]: csrfHash,
        search: params.term,
        id_cluster: $("#prod_jalan_id_cluster").val(),
        id_proyek: dt_proyek.id_proyek,
      };
    },
    processResults: function (r) {
      csrfHash = r.token;

      let results = [];
      $.each(r.data, function (index, item) {
        results.push({
          id: item[0],
          text: item[3],
        });
      });

      return {
        results: results,
      };
    },
    cache: true,
  },
});


$("#listrik_jenis").change(function () {
  if (this.value == "PLN") {
    $("#listrik-pln-input-form").removeClass("hidden");
    $("#listrik_disediakan").addClass("hidden");
  } else {
    $("#listrik-pln-input-form").addClass("hidden");
    $("#listrik_disediakan").removeClass("hidden");
  }
});
$("#air_jenis").change(function () {
  if (this.value == "Air Tanah") {
    $("#air_tanah-input_form").removeClass("hidden");
    $("#air_komunal-input_form").addClass("hidden");
    $("#air_pdam-input_form").addClass("hidden");
  } else if (this.value == "Komunal Warga") {
    $("#air_tanah-input_form").addClass("hidden");
    $("#air_komunal-input_form").removeClass("hidden");
    $("#air_pdam-input_form").addClass("hidden");
  } else {
    $("#air_tanah-input_form").addClass("hidden");
    $("#air_komunal-input_form").addClass("hidden");
    $("#air_pdam-input_form").removeClass("hidden");
  }
});

$("#progres_bangunan").on("input", function () {
  $("#t_progres_bangunan").html($(this).val());
});


function save_produksi() {
  if ($("#tanggal_pembangunan").val() == "") {
    $(".tanggal_pembangunan").addClass("is-invalid");
    return swal("error", "Tanggal pembangunan harus diisi");
  }
  $(".tanggal_pembangunan").removeClass("is-invalid");

  if ($("#tanggal_rencana_selesai_pembangunan").val() == "") {
    $(".tanggal_rencana_selesai_pembangunan").addClass("is-invalid");
    return swal("error", "Tanggal rencana selesai pembangunan harus diisi");
  }
  $(".tanggal_rencana_selesai_pembangunan").removeClass("is-invalid");

  let form = $("#fm-produksi")[0];
  let fd = new FormData(form);
  fd.append(csrfName, csrfHash);

  $.ajax({
    url: base_url + "api/produksi/save",
    type: "post",
    contentType: false,
    processData: false,
    data: fd,
    dataType: "json",
    beforeSend: function () {
      simpanBtn("#add-form-btn-produksi", true);
    },
    success: function (r) {
      csrfHash = r.token;
      // $('#add-form-btn-produksi').prop('disabled', false);
      // return;
      if (r.success === true) {
        Swal.fire({
          //position: 'bottom-end',
          icon: "success",
          title: r.messages,
          showConfirmButton: false,
          timer: 1500,
        }).then(function () {
          $(".modal").modal("hide");
          simpanBtn("#add-form-btn-produksi", false);
        });
      } else {
        Swal.fire({
          //position: 'bottom-end',
          icon: "error",
          title: r.messages,
          showConfirmButton: false,
          timer: 1500,
        }).then(function () {
          simpanBtn("#add-form-btn-produksi", false);
        });
      }
      load_kavling();
      hapus_seleksi();
    },
    error: function (xhr, st, err) {
      simpanBtn("#add-form-btn-produksi", false);
      return swal("error", err);
    },
  });
}
$("#terima_komplain").change(function () {
  if (this.checked) {
    $("#terima_komplain_div").removeClass("hidden");
  } else {
    $("#terima_komplain_div").addClass("hidden", true);
  }
});

function open_komplain_produksi() {
  if (!editdtt[0]) {
    Swal.fire({
      //position: 'bottom-end',
      icon: "error",
      title: "Pilih salahsatu kavling",
      showConfirmButton: false,
      timer: 1500,
    });
    return;
  }

  var sh = editdtt[0],
    id_kavling = sh.id.substr(3);

  if (!sh.data2.id_komplain) {
    Swal.fire({
      //position: 'bottom-end',
      icon: "error",
      title: "Tidak ada komplain",
      showConfirmButton: false,
      timer: 1500,
    });
    return;
  }

  $("#fm-komplain-produksi")[0].reset();

  $(
    "#fm-komplain-produksi #foto_komplain_sales, #fm-komplain-produksi #foto_komplain_produksi",
  ).html("");

  $(
    ".ditangani_form, #selesaikan_komplain_div, #komplain_selesai_btn_produksi",
  ).addClass("hidden", true);
  $("#keterangan_ditangani").prop("readonly", false);
  $("#komplain-produksi-form-btn").prop("disabled", false);

  $("#terima_komplain, #is_selesai_produksi").attr("onclick", "");
  $("#fm-komplain-produksi #keterangan_ditangani").prop("disabled", false);
  $("#fm-komplain-produksi #selesai_keterangan_produksi").prop(
    "disabled",
    false,
  );

  $("#komplain_selesai_sip").addClass("hidden");

  $("#last_update_komplain_produksi").html(
    "Terakhir diupdate oleh: -, pada: -",
  );

  $(".id_kavling").val(id_kavling);
  $("#fm-komplain-produksi #id_komplain").val(sh.data2.id_komplain);

  $.ajax({
    url: base_url + "api/produksi/get_data_komplain_by_id",
    type: "post",
    data: {
      [csrfName]: csrfHash,
      id_komplain: sh.data2.id_komplain,
      id_kavling: id_kavling,
    },
    dataType: "json",
    success: function (r) {
      csrfHash = r.token;
      let st = r.komplain,
        fotok,
        fotok_display = "",
        fotokp,
        fotokp_display = "";

      if (st) {
        //display foto komplain dari sales
        fotok = st.upload_komplain_sales_urls || [];

        if (Array.isArray(fotok)) {
          let is_active = "active";
          for (let e = 0; e < fotok.length; e++) {
            if (e > 0) is_active = "";

            fotok_display +=
              '<div class="carousel-item ' +
              is_active +
              '">' +
              '<img class="d-block w-100 ft_kom" src="' +
              fotok[e] +
              '" alt="First slide">' +
              "</div>";
          }
        }
        $("#fm-komplain-produksi #foto_komplain_sales").html(fotok_display);

        //display foto penyelsaian dari produksi
        fotokp = st.upload_komplain_produksi_urls || [];

        if (Array.isArray(fotokp)) {
          let is_active = "active";
          for (let e = 0; e < fotokp.length; e++) {
            if (e > 0) is_active = "";

            fotokp_display +=
              '<div class="carousel-item ' +
              is_active +
              '">' +
              '<img class="d-block w-100 ft_kom" src="' +
              fotokp[e] +
              '" alt="First slide">' +
              "</div>";
          }
        }
        $("#fm-komplain-produksi #foto_komplain_produksi").html(fotokp_display);

        //komplain
        $("#fm-komplain-produksi #keterangan_komplain").val(
          st.keterangan_komplain,
        );
        $("#fm-komplain-produksi #username_komplain_oleh").val(
          st.username_komplain_oleh,
        );

        if (st.komplain_tgl != "0000-00-00")
          document
            .querySelector("#fm-komplain-produksi #komplain_tgl")
            ._flatpickr.setDate(st.komplain_tgl);

        $("#last_update_komplain_produksi").html(
          "Terakhir diupdate oleh: " +
            st.username_last_update +
            ", pada: " +
            format_datetime(st.updated_at),
        );

        //ditangani
        if (st.status_komplain == 2) {
          $("#terima_komplain").attr("onclick", "return false;");
          $("#terima_komplain").prop("checked", true);

          $(".ditangani_form, #selesaikan_komplain_div").removeClass("hidden");

          $("#fm-komplain-produksi #keterangan_ditangani").val(
            st.keterangan_ditangani,
          );
          $("#fm-komplain-produksi #username_ditangani_oleh").val(
            st.username_ditangani_oleh,
          );

          if (st.ditangani_tgl != "0000-00-00")
            document
              .querySelector("#fm-komplain-produksi #ditangani_tgl")
              ._flatpickr.setDate(st.ditangani_tgl);
        } else if (st.status_komplain == 3) {
          $("#terima_komplain").attr("onclick", "return false;");
          $("#terima_komplain").prop("checked", true);

          $("#is_selesai_produksi").attr("onclick", "return false;");
          $("#is_selesai_produksi").prop("checked", true);

          $("#keterangan_ditangani").prop("readonly", true);

          $(".ditangani_form, #selesaikan_komplain_div").removeClass("hidden");

          $("#fm-komplain-produksi #keterangan_ditangani").val(
            st.keterangan_ditangani,
          );
          $("#fm-komplain-produksi #username_ditangani_oleh").val(
            st.username_ditangani_oleh,
          );

          if (st.ditangani_tgl != "0000-00-00")
            document
              .querySelector("#fm-komplain-produksi #ditangani_tgl")
              ._flatpickr.setDate(st.ditangani_tgl);

          $("#fm-komplain-produksi #selesai_keterangan_produksi").val(
            st.selesai_keterangan_produksi,
          );
          $("#fm-komplain-produksi #username_selesai_oleh_produksi").val(
            st.username_selesai_oleh_produksi,
          );

          if (st.selesai_tgl_produksi != "0000-00-00")
            document
              .querySelector("#fm-komplain-produksi #selesai_tgl_produksi")
              ._flatpickr.setDate(st.selesai_tgl_produksi);
        } else if (st.status_komplain == 4) {
          $("#terima_komplain").attr("onclick", "return false;");
          $("#terima_komplain").prop("checked", true);

          $("#is_selesai_produksi").attr("onclick", "return false;");
          $("#is_selesai_produksi").prop("checked", true);

          $("#komplain-produksi-form-btn").prop("disabled", true);

          $("#keterangan_ditangani").prop("readonly", true);

          $("#fm-komplain-produksi #selesai_keterangan_produksi").prop(
            "disabled",
            true,
          );

          $("#fm-komplain-produksi #keterangan_ditangani").prop(
            "disabled",
            true,
          );

          $(
            ".ditangani_form, #selesaikan_komplain_div, #komplain_selesai_btn_produksi",
          ).removeClass("hidden");

          $("#fm-komplain-produksi #keterangan_ditangani").val(
            st.keterangan_ditangani,
          );
          $("#fm-komplain-produksi #username_ditangani_oleh").val(
            st.username_ditangani_oleh,
          );

          if (st.ditangani_tgl != "0000-00-00")
            document
              .querySelector("#fm-komplain-produksi #ditangani_tgl")
              ._flatpickr.setDate(st.ditangani_tgl);

          $("#fm-komplain-produksi #selesai_keterangan_produksi").val(
            st.selesai_keterangan_produksi,
          );
          $("#fm-komplain-produksi #username_selesai_oleh_produksi").val(
            st.username_selesai_oleh_produksi,
          );

          if (st.selesai_tgl_produksi != "0000-00-00")
            document
              .querySelector("#fm-komplain-produksi #selesai_tgl_produksi")
              ._flatpickr.setDate(st.selesai_tgl_produksi);

          $("#komplain_selesai_sip").removeClass("hidden");

          $("#fm-komplain-produksi #selesai_keterangan_sales").val(
            st.selesai_keterangan_sales,
          );
          $("#fm-komplain-produksi #username_selesai_oleh_sales").val(
            st.username_selesai_oleh_sales,
          );

          if (st.selesai_tgl_sales != "0000-00-00")
            document
              .querySelector("#fm-komplain-produksi #selesai_tgl_sales")
              ._flatpickr.setDate(st.selesai_tgl_sales);
        }
      }
      $(".label_alamat").html(
        dt_proyek.nama_proyek +
          "<br/>" +
          sh.data.nama_jalan +
          ", No." +
          sh.data.no_kavling +
          "<br/>" +
          sh.data2.no_tipe_rumah +
          " (" +
          sh.data2.tipe_rumah +
          ")<br/>",
      );
      $("#modal_komplain_produksi").modal({
        backdrop: "static",
        keyboard: false,
      });
    },
    error: function () {},
  });
}

function save_komplain_produksi() {
  var files = $("#upload_komplain_sales")[0].files;
  var form = $("#fm-komplain-produksi")[0];
  var fd = new FormData(form);
  fd.append(csrfName, csrfHash);

  $.ajax({
    url: base_url + "api/produksi/save_komplain_produksi",
    type: "POST",
    contentType: false,
    processData: false,
    // data: $("#fm-komplain-sales").serialize() + "&" + csrfName + "=" + csrfHash,
    data: fd,
    dataType: "json",
    beforeSend: function () {
      $("#komplain-produksi-form-btn").prop("disabled", true);
      $("#komplain-produksi-form-btn").html(
        'Menyimpan <i class="fa fa-spinner fa-spin"></i>',
      );
    },
    success: function (r) {
      csrfHash = r.token;

      if (r.success === true) {
        swal("success", r.messages);
        // Swal.fire({
        //   //position: 'bottom-end',
        //   icon: "success",
        //   title: r.messages,
        //   showConfirmButton: false,
        //   timer: 1500,
        // });

        $(".modal").modal("hide");
        hapus_seleksi();
        load_kavling();
      } else {
        swal("error", r.messages);
      }
      $("#komplain-produksi-form-btn").html("Simpan");
      $("#komplain-produksi-form-btn").prop("disabled", false);
    },
  });
}
//open form add/edit
function open_produksi(sh, role, id_kavling) {
  if (editdtt.length > 1) {
    swal("error", "Tidak bisa mengisi data lebih dari 1 secara bersamaan");
  }
  if (sh.data.tipe == "kavling") {
    return open_fproduksi(sh, role, id_kavling);
  } else {
    return open_fotherproduksi(sh);
  }
}

function save_fotherproduksi() {
  let form = $("#fm-fotherproduksi")[0],
    formData = new FormData(form);
  formData.append(csrfName, csrfHash);

  $.ajax({
    url: base_url + "api/produksi/edit_others",
    type: "POST",
    data: formData,
    processData: false,
    contentType: false,
    dataType: "json",
    beforeSend: function () {
      $("#save_fotherproduksi-btn").prop("disabled", true);
      $("#save_fotherproduksi-btn").html(
        'Menyimpan <i class="fa fa-spinner fa-spin"></i>',
      );
    },
    success: function (r) {
      csrfHash = r.token;

      if (r.success === true) {
        Swal.fire({
          //position: 'bottom-end',
          icon: "success",
          title: r.messages,
          showConfirmButton: false,
          timer: 1500,
        });

        $(".modal").modal("hide");
        hapus_seleksi();
        load_kavling();
      } else {
        Swal.fire({
          //position: 'bottom-end',
          icon: "error",
          title: r.messages,
          showConfirmButton: false,
          timer: 1500,
        });
      }
      $("#save_fotherproduksi-btn").html("Simpan");
      $("#save_fotherproduksi-btn").prop("disabled", false);
    },
    error: function () {
      Swal.fire({
        //position: 'bottom-end',
        icon: "error",
        title: "Terjadi kesalahan saat menginput data",
        showConfirmButton: false,
        timer: 1500,
      });
      $("#save_fotherproduksi-btn").html("Simpan");
      $("#save_fotherproduksi-btn").prop("disabled", false);
    },
  });
}
$("#save_fotherproduksi-btn").click(function (e) {
  e.preventDefault();
});

function open_fotherproduksi(sh) {
  $("#fm-fotherproduksi")[0].reset();
  $("#list_produksi_jalan_foto, #produksi_jalan_history").html("");
  resetProduksiJalanHistoryTimeline("#produksi_jalan_history");
  $("#label_produksi_jalan_foto").html("Bisa lebih dari 1 foto");
  $("#f_progres_jalan").val(0);
  $(".t_luas_legal, .t_luas_produksi, .r_progres").html(" ");
  $("#prod-jalan-progress-tab").tab("show");
  $.ajax({
    url: base_url + "siteplan/get_others",
    type: "post",
    data: {
      [csrfName]: csrfHash,
      id_kavling: editdtt[0].id.substr(6),
      history_limit: PRODUKSI_JALAN_HISTORY_LIMIT,
      history_offset: 0,
    },
    dataType: "json",
    success: function (r) {
      csrfHash = r.token;

      if (r.data) {
        let d = r.data[0],
          progres = d.progres ? d.progres : 0;
        $(".produksi-jalan-only").toggleClass("hidden", d.tipe !== "jalan");
        $(".id_kavling").val(d.id);
        $(".t_luas_legal, .t_luas_produksi").html("-");

        if (d.planning_luas)
          $(".t_luas_planning").html(
            d.planning_luas +
              "  m&sup2  (" +
              d.planning_edit +
              ": " +
              format_datetime(d.planning_updated_at) +
              ")",
          );
        if (d.legal_luas)
          $(".t_luas_legal").html(
            d.legal_luas +
              "  m&sup2  (" +
              d.legal_edit +
              ": " +
              format_datetime(d.legal_updated_at) +
              ")",
          );

        $("#f_produksi_luas").val(d.produksi_luas);
        $("#f_produksi_keterangan").val(d.produksi_keterangan);
        $("#f_progres_jalan").val(progres);
        $(".r_progres").html(progres);
        renderProduksiJalanHistoryTimeline("#produksi_jalan_history", r.history || [], r, false);
      }
    },
    error: function () {
      Swal.fire({
        //position: 'bottom-end',
        icon: "error",
        title: "Terjadi kesalahan saat memuat data",
        showConfirmButton: false,
        timer: 1500,
      });
      return;
    },
  });

  $(".label_alamat").html(
    dt_proyek.nama_proyek +
      "<br/> <span class='capitalize'>" +
      sh.data.tipe +
      "<span>: " +
      sh.data.nama_jalan +
      "",
  );
  $("#modal_fothersproduksi").modal({
    backdrop: "static",
    keyboard: false,
  });
}

function open_fproduksi(sh, role, id_kavling) {
  let update_tanggal_pembangunan = has_akses.update_tanggal_pembangunan
    ? true
    : false;
  $(".tgl_bangun").prop("disabled", !update_tanggal_pembangunan);

  $("#fm-prod-progress-tab").click();
  ((st_0 = 0),
    (st_25 = 0),
    (st_50 = 0),
    (st_75 = 0),
    (st_100 = 0),
    (st_saluran = 0),
    (st_air = 0),
    (st_jalan = 0),
    (bp = 0),
    (lpa = 0),
    (tot = 0));

  let categories = [
    "rab_dokumen",
    "prod_foto_konstruksi",
    "prod_foto_exterior",
    "prod_foto_interior",
    "jalan_foto",
    "jalan_foto_update",
    "listrik_pln_foto",
    "listrik_disediakan_dokumen",
    "air_komunal",
    "air_tanah",
    "air_pdam",
  ];

  categories.forEach((cat) => {
    $("#list_" + cat).html("");
    $("#label_" + cat).html("Upload file/Foto");
  });

  $(".af .cbp").prop("disabled", true);

  $("#t_progres_bangunan").html("0");
  $("#fm-produksi")[0].reset();
  $("#last_update_checklist_prod").html("Terakhir diupdate oleh: -, pada: -");

  $(".id_kavling").val(id_kavling);
  $("#id_produksi").val(sh.data.id_produksi);

  $("#download_gambar_kerja").click(function () {
    simpanBtn(
      "#download_gambar_kerja",
      true,
      'Mengunduh <i class="fa fa-spinner fa-spin"></i>',
      "Unduh Gambar Kerja",
    );
    download(sh.data2.id_gambar_kerja, () => {
      simpanBtn(
        "#download_gambar_kerja",
        false,
        'Mengunduh <i class="fa fa-spinner fa-spin"></i>',
        "Unduh Gambar Kerja",
      );
    });
  });

  $.ajax({
    url: base_url + "api/produksi/get_data_by_id",
    type: "post",
    data: {
      [csrfName]: csrfHash,
      id_produksi: sh.data.id_produksi,
      id_kavling: id_kavling,
    },
    dataType: "json",
    success: function (r) {
      csrfHash = r.token;
      let cl = r.cl,
        pb = 0;
      if (r) {
        if (r.progres_bangunan) {
          $("#progres_bangunan").val(r.progres_bangunan);
          $("#t_progres_bangunan").html(r.progres_bangunan);
        }

        changeVal("#air_jenis", r.air_jenis);
        changeVal("#listrik_jenis", r.listrik_jenis);

        changeVal("#listrik_pln", r.listrik_pln);
        changeVal("#listrik_disediakan_no", r.listrik_disediakan_no);
        changeVal("#listrik_disediakan_tanggal", r.listrik_disediakan_tanggal);
        changeVal("#air_deskripsi_unit", r.air_deskripsi_unit);
        changeVal("#air_pdam_no", r.air_pdam_no);
        // changeVal("#lpa_tanggal", r.lpa_tanggal);

        setDatePicker(r.lpa_tanggal, "#lpa_tanggal");

        setDatePicker(r.tanggal_pembangunan, "#tanggal_pembangunan");
        setDatePicker(
          r.tanggal_rencana_selesai_pembangunan,
          "#tanggal_rencana_selesai_pembangunan",
        );
        setDatePicker(
          r.tanggal_selesai_pembangunan,
          "#tanggal_selesai_pembangunan",
        );

        if (!r.tanggal_pembangunan) {
          $(".tgl_bangun").prop("disabled", false);
        }

        $("#tanggal_pembangunan_old").val(r.tanggal_pembangunan);
        $("#tanggal_rencana_selesai_pembangunan_old").val(
          r.tanggal_rencana_selesai_pembangunan,
        );
        $("#tanggal_selesai_pembangunan_old").val(
          r.tanggal_selesai_pembangunan,
        );

        $("#lu-tanggal_pembangunan").html(
          `Diinput pada:
            ${r.tanggal_pembangunan_pada ? format_datetime(r.tanggal_pembangunan_pada) : "-"},
            oleh: ${r.tanggal_pembangunan_oleh ? r.tanggal_pembangunan_oleh_u : "-"}`,
        );
        $("#lu-tanggal_rencana_selesai_pembangunan").html(
          `Diubah pada:
            ${r.tanggal_pembangunan_diubah_pada ? format_datetime(r.tanggal_pembangunan_diubah_pada) : "-"},
            oleh: ${r.tanggal_pembangunan_diubah_oleh ? r.tanggal_pembangunan_diubah_oleh_u : "-"}`,
        );
        $("#lu-tanggal_selesai_pembangunan").html(
          `Diinput pada:
            ${r.tanggal_selesai_pembangunan_diubah_pada ? format_datetime(r.tanggal_selesai_pembangunan_diubah_pada) : "-"},
            oleh: ${r.tanggal_selesai_pembangunan_diubah_oleh ? r.tanggal_selesai_pembangunan_diubah_oleh_u : "-"}`,
        );

        changeVal("#tanggal_pembangunan_oleh", r.tanggal_pembangunan_oleh);
        changeVal(
          "#tanggal_pembangunan_diubah_oleh",
          r.tanggal_pembangunan_diubah_oleh,
        );
        changeVal(
          "#tanggal_selesai_pembangunan_oleh",
          r.tanggal_selesai_pembangunan_oleh,
        );
        changeVal(
          "#tanggal_selesai_pembangunan_diubah_oleh",
          r.tanggal_selesai_pembangunan_diubah_oleh,
        );

        setDatePicker(r.tanggal_pembangunan_pada, "#tanggal_pembangunan_pada");
        setDatePicker(
          r.tanggal_pembangunan_diubah_pada,
          "#tanggal_pembangunan_diubah_pada",
        );
        setDatePicker(
          r.tanggal_selesai_pembangunan_pada,
          "#tanggal_selesai_pembangunan_pada",
        );
        setDatePicker(
          r.tanggal_selesai_pembangunan_diubah_pada,
          "#tanggal_selesai_pembangunan_diubah_pada",
        );

        changeVal("#sumurbor_keterangan", r.sumurbor_keterangan);
        setDatePicker(r.sumurbor_tanggal, "#sumurbor_tanggal");
        $("#last_update-sumurbor").html(
          `Diubah pada: ${r.sumurbor_updated ? format_datetime(r.sumurbor_updated) : "-"},
                    oleh: ${r.sumurbor_oleh_u ? r.sumurbor_oleh_u : "-"}`,
        );

        const fields = [
          { id: "st_0", value: r.st_0 },
          { id: "st_25", value: r.st_25 },
          { id: "st_50", value: r.st_50 },
          { id: "st_75", value: r.st_75 },
          { id: "st_100", value: r.st_100 },
          { id: "st_saluran", value: r.st_saluran },
          { id: "st_air", value: r.st_air },
          { id: "st_jalan", value: r.st_jalan },
          { id: "bp", value: r.bp },
          { id: "lpa", value: r.lpa },
          { id: "slo", value: r.slo },
          { id: "sumurbor", value: r.sumurbor },
        ];

        fields.forEach((field) => {
          $("#" + field.id)
            .prop("checked", field.value == 1)
            .change();
        });

        if (cl && cl.length > 0) {
          let lates_date = cl[0].produksi_cek_tgl;
          cl.forEach((val) => {
            ["t", "f", "v"].forEach((type) => {
              if (val["hasil_cek_" + type] == 1) {
                $("#hasil_cek_" + type + "\\[" + val.id_subitem + "\\]").prop(
                  "checked",
                  true,
                );
              }
            });

            $("#keterangan_cek_produksi\\[" + val.id_subitem + "\\]").val(
              val.keterangan_cek_produksi,
            );

            if (lates_date < val.produksi_cek_tgl)
              lates_date = val.produksi_cek_tgl;
          });
          $("#last_update_checklist_prod").html(
            "Terakhir diupdate oleh: " +
              cl[0].username +
              ", pada: " +
              format_date(lates_date),
          );
        }

        $("#produksi_keterangan").val(r.keterangan);

        showFoto(r.files);
      }
    },
    error: function () {
      Swal.fire({
        //position: 'bottom-end',
        icon: "error",
        title: "Terjadi kesalahan saat memuat data",
        showConfirmButton: false,
        timer: 1500,
      });
      return;
    },
  });
  $(".label_alamat").html(
    dt_proyek.nama_proyek +
      "<br/>" +
      sh.data.nama_jalan +
      ", No." +
      sh.data.no_kavling +
      "<br/>" +
      sh.data2.no_tipe_rumah +
      " (" +
      sh.data2.tipe_rumah +
      ")<br/>",
  );
  $("#modal_divisi" + role).modal({
    backdrop: "static",
    keyboard: false,
  });
}

function download(e, callback = null) {
  (async () => {
    const response = await fetch(base_url + "api/produksi/get_gambarkerja", {
      method: "POST",
      headers: {
        Accept: "application/json",
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        [csrfName]: csrfHash,
        id_gambar_kerja: e,
        pass: "password",
      }),
    });

    if (!response.ok) {
      throw new Error("Gagal mengunduh file");
    }

    const data = await response.json();
    const url = data.lokasi;
    const a = document.createElement("a");
    let sh = editdtt[0].data2;

    a.href = url;
    const date = new Date();
    const filename = `${sh.no_tipe_rumah}: diunduh pada: ${date.toISOString().split("T")[0]} - ${date.getHours()}:${date.getMinutes()}}.pdf`;
    a.download = filename;
    a.click();
    callback();
  })().catch((error) => {
    swal("error", "Gagal mengunduh file");
    callback();
  });
}

$("#fm-slf-id_kavling").select2({
  placeholder: "Pilih Kavling",
  allowClear: true,
  ajax: {
    url: base_url + "api/produksi/getKavling",
    dataType: "json",
    delay: 250,
    method: "post",
    data: function (params) {
      return {
        [csrfName]: csrfHash,
        search: params.term,
        id_proyek: dt_proyek.id_proyek,
      };
    },
    processResults: function (r) {
      csrfHash = r.token;

      let results = [];
      $.each(r.data, function (i, v) {
        results.push({
          id: v.id_kavling,
          text: `${v.nama_jalan} No ${v.no_kavling}: ${v.nama_konsumen ? v.nama_konsumen : "-"}`,
        });
      });

      return {
        results: results,
      };
    },
    cache: true,
  },
});

function simpan_slf() {
  // Validate required fields
  let requiredFields = $("#fm-pr_slf").find("[required]");
  let isValid = true;

  requiredFields.each(function () {
    if ($(this).val() === "") {
      isValid = false;
      $(this).addClass("is-invalid");
    } else {
      $(this).removeClass("is-invalid");
    }
  });

  if ($("#fm-slf-id_kavling").val().length == 0) {
    isValid = false;
    $(this).addClass("is-invalid");
  } else {
    isValid = true;
    $(this).removeClass("is-invalid");
  }

  if (!isValid) {
    swal("Error", "Mohon lengkapi semua field yang wajib diisi", "error");
    return;
  }

  // If all required fields are filled, proceed with form submission
  let formData = new FormData($("#fm-pr_slf")[0]);

  formData.append(csrfName, csrfHash);
  formData.append("id_proyek", dt_proyek.id_proyek);

  $.ajax({
    url: base_url + "api/produksi/saveSLF",
    type: "POST",
    data: formData,
    processData: false,
    contentType: false,
    success: function (response) {
      if (response.success === true) {
        swal("success", "Berhasil", "Data SLF berhasil disimpan");
        // Optionally, refresh the SLF list or perform other actions
        form_slf_reset();
        // Refresh the SLF list
        getlistSLF();

        // Reset and focus on the SLF tab
        $("#fm-slf-id_kavling").val(null).trigger("change");
        $('a[href="#fm-pr_list_slf"]').tab("show");
      } else {
        swal("error", "terjadi kesalahan", "Gagal menyimpan data SLF");
      }
    },
    error: function () {
      swal(
        "error",
        "Terjadi kesalahan",
        "Terjadi kesalahan saat menyimpan data",
      );
    },
  });
}

function getlistSLF() {
  $.ajax({
    url: base_url + "api/produksi/getSlf",
    type: "GET",
    data: { id_proyek: dt_proyek.id_proyek },
    success: function (response) {
      if (response.data && response.data.length > 0) {
        let tableContent = "";
        $.each(response.data, function (i, v) {
          tableContent += `
                                  <tr>
                                      <td>${i + 1}</td>
                                      <td>${v.no_slf}</td>
                                      <td>${v.kavling}</td>
                                      <td>
                                          <div class="form-group">
                                               <a href="${base_url}api/produksi/getSLFPDF/${v.id}" class="btn btn-outline-primary waves-effect btn-sm" target="_blank"><i class="fa fa-eye"></i></a>
                                              <a href=javascript:void(0) onclick="editSLF(${v.id})" class="btn btn-outline-warning waves-effect btn-sm"><i class="fa fa-edit"></i></a>
                                              <a href=javascript:void(0) onclick="hapusSLF(${v.id})" class="btn btn-outline-danger waves-effect btn-sm"><i class="fa fa-trash"></i></a>
                                          </div>
                                         </td>
                                      <td>${v.username + "<br>" + format_datetime(v.created_at)}</td>
                                  </tr>
                              `;
        });
        $("#tb-pr_lsit_slf-here").html(tableContent);
      } else {
        $("#tb-pr_lsit_slf-here").html(
          "<tr><td colspan='5' style='text-align: center'>Tidak ada data</td></tr>",
        );
      }
    },
    error: function () {
      swal("Error", "Gagal memuat data SLF", "error");
    },
  });
}

function hapusSLF(id) {
  Swal.fire({
    title: "Apakah Anda yakin?",
    text: "Data SLF akan dihapus secara permanen!",
    icon: "warning",
    showCancelButton: true,
    onfirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Ya, hapus!",
    dangerMode: true,
  }).then((willDelete) => {
    if (willDelete.isConfirmed) {
      $.ajax({
        url: base_url + "api/produksi/hapusSLF",
        type: "POST",
        data: { id: id, [csrfName]: csrfHash },
        success: function (response) {
          if (response.success === true) {
            swal("Berhasil!", "Data SLF telah dihapus.", "success");
            getlistSLF(); // Refresh the SLF list
          } else {
            swal("Error", "Gagal menghapus data SLF", "error");
          }
        },
        error: function () {
          swal("Error", "Terjadi kesalahan saat menghapus data SLF", "error");
        },
      });
    }
  });
}

function form_slf_reset() {
  $("#fm-pr_slf")[0].reset();
  $("#tb-pr_lsit_slf-here").html(
    "<tr><td colspan='5' style='text-align: center'>Tidak ada data</td></tr>",
  );

  $("#fm-slf-id_kavling").val(null).trigger("change");

  $("#fm-slf-kelurahan").val(dt_proyek.kelurahan);
  $("#fm-slf-kecamatan").val(dt_proyek.kecamatan);
  $("#fm-slf-kota").val(dt_proyek.kota);
  $("#fm-slf-provinsi").val(dt_proyek.provinsi);
  $("#fm-slf-alamat_proyek").val(dt_proyek.alamat_proyek);
  $("#fm-slf-nama_perusahaan").val(dt_proyek.nama_pt);
  $("#fm-slf-nama_bangunan").val(dt_proyek.nama_proyek);
}

function buat_slf() {
  let sh = editdtt;
  let dvl = "";
  form_slf_reset();
  getlistSLF();

  $(".label_alamat").html(dt_proyek.nama_proyek);
  $("#modal-pr_slf").modal({
    backdrop: "static",
    keyboard: false,
  });
}
var bpItems = [];

function isi_pembayaran() {
  if (editdtt.length == 0)
    return swal("error", "Terjad Kesalahan", "Tidak ada kavling yang dipilih");

  var sh = editdtt[0],
    id_kavling = sh.id.substr(3);

  bpItems = [];

  $("#fm-bayar_produksi-prod")[0].reset();
  $("#div-bayar_produksi-here").html("");

  $.ajax({
    url: base_url + "api/produksi/getBayarProduksi",
    type: "post",
    data: {
      [csrfName]: csrfHash,
      id_kavling: id_kavling,
    },
    dataType: "json",
    beforeSend: function () {
      $("#loading").removeClass("hidden");
    },
    success: function (r) {
      $("#loading").addClass("hidden");
      csrfHash = r.token;
      let d = r.list_bayar_produksi;
      let div = "",
        id;

      $.each(d, function (i, v) {
        bpItems.push(v.id_bayar_produksi);

        id = !v.id ? "n" + v.id_bayar_produksi : v.id;
        div += `
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <strong>${v.item}</strong>
                            </div>
                            <div class="card-body">
                                    <div class="row">
                                    <div class="col-md-6">

                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Tanggal Pembayaran</label>
                                            <input type="text" class="form-control fp-bayar_produksi flatpickr-human-friendly tbp${v.id_bayar_produksi}"
                                                id="id-bayar_produksi[${id}][tanggal_bayar]" value="${v.tanggal_bayar ? v.tanggal_bayar : ""}" name="id-bayar_produksi[${id}][tanggal_bayar]">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="sumurbor_bayar_nominal">Nominal</label>
                                            <input type="text" class="form-control num nbp${v.id_bayar_produksi}" id="id-bayar_produksi[${id}][nominal]"
                                                name="id-bayar_produksi[${id}][nominal]" value="${v.nominal ? v.nominal : ""}">
                                            <input type="hidden" class="form-control" id="id-bayar_produksi[${id}][id_item_produksi]"
                                                name="id-bayar_produksi[${id}][id_item_produksi]" value="${id}">
                                        </div>
                                    </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Keterangan</label>
                                            <textarea class="form-control" id="id-bayar_produksi[${id}][keterangan]"
                                                name="id-bayar_produksi[${id}][keterangan]" rows="4" placeholder="Keterangan">${v.keterangan ? v.keterangan : ""}</textarea>
                                            <small id="last_update-sumurbor_bayar" class=""></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                 `;
      });

      $("#div-bayar_produksi-here").html(div);

      flatpickr(".fp-bayar_produksi", {
        altInput: true,
        altFormat: "F j, Y",
        dateFormat: "Y-m-d",
      });
      $(".num").change();

      $("#bayar_produksi-id_kavling").val(id_kavling);

      $(".label_alamat").html(
        dt_proyek.nama_proyek +
          "<br/>" +
          sh.data.nama_jalan +
          ", No." +
          sh.data.no_kavling +
          "<br/>" +
          sh.data2.no_tipe_rumah +
          " (" +
          sh.data2.tipe_rumah +
          ")<br/>",
      );
      $("#modal-bayar_produksi-prod").modal({
        backdrop: "static",
        keyboard: false,
      });
    },
    error: function (r) {
      $("#loading").addClass("hidden");
      Swal.fire({
        //position: 'bottom-end',
        icon: "error",
        title: "terjadi kesalahan",
        showConfirmButton: false,
        // timer: 1500
      });
    },
  });
}

function save_bayar_produksi() {
  $.each(bpItems, function (_i, v) {
    if ($(".tbp" + v)[0].value != "") {
      if ($(".nbp" + v)[0].value == "") {
        $(".nbp").addClass("is-invalid");
        return swal("error", "Nominal pembayaran harus diisi");
      } else {
        $(".nbp").removeClass("is-invalid");
      }
    }
    if ($(".nbp" + v)[0].value != "") {
      if ($(".tbp" + v)[0].value == "") {
        $(".tbp").addClass("is-invalid");
        return swal("error", "Tanggal pembayaran harus diisi");
      } else {
        $(".tbp").removeClass("is-invalid");
      }
    }
  });

  let sbtn = "#add-form-btn-bayar_produksi";
  $.ajax({
    url: base_url + "api/produksi/saveBayarProduksi",
    type: "post",
    data:
      $("#fm-bayar_produksi-prod").serialize() +
      "&" +
      csrfName +
      "=" +
      csrfHash,
    dataType: "json",
    beforeSend: function () {
      simpanBtn(sbtn, true);
    },
    success: function (r) {
      csrfHash = r.token;
      if (r.success === true) {
        swal("success", r.messages);
        $(".modal").modal("hide");
        simpanBtn(sbtn, false);
      } else {
        swal("error", "Terjadi kesalahan", r.messages);
        simpanBtn(sbtn, false);
      }
      load_kavling();
      hapus_seleksi();
    },
    error: function (r) {
      Swal.fire({
        //position: 'bottom-end',
        icon: "error",
        title: "terjadi kesalahan",
        showConfirmButton: false,
        // timer: 1500
      });
      simpanBtn(sbtn, false);
    },
  });
}

</script>
