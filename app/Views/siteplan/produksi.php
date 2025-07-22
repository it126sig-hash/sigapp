<style>
    .select2-selection__choice {
        display: block;
        margin: 2px 0;
    }

    .select2-container--default .select2-selection--multiple {
        height: auto;
    }
</style>
<div class="modal fade text-left" id="modal_fothersproduksi" tabindex="-1" role="dialog"
    aria-labelledby="modal_fothersproduksi" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Produksi</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="fm-fotherproduksi" class="add-new-record modal-content pt-0">
                <div class="modal-body">
                    <p class="modal-title label_alamat" id="label_fothersproduksi"></p>
                    <br>
                    <span>Luas di Siteplan : <br>
                        <span class='t_luas_planning'></span>
                    </span>
                    <br>
                    <br>
                    <span>Luas di Sertifikat : <br>
                        <span class='t_luas_legal'></span>
                    </span>
                    <hr>
                    <input type="hidden" class="form-control id_kavling" name="id_kavling" value="" />
                    <input type="hidden" class="form-control" id="id_proyek" name="id_produksi" value="" />

                    <div class="form-group">
                        <label for="f_progres_jalan">Progres</label>
                        <input type="range" onInput="$('.r_progres').html($(this).val())" class="form-control-range"
                            min="0" max="100" step="5" id="f_progres_jalan" name="f_progres_jalan">
                        <span class="r_progres"></span><span>%</span>
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
                </div>
                <div class="modal-footer">
                    <button id="save_fotherproduksi-btn" class="btn btn-primary data-submit mr-1"
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
                                href="#fm-prod-progress" role="tab" aria-selected="true">Proges</a>
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
                                <div class="col-md-4">
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

                                </div>
                                <div class="col-md-4">
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
                                                <label class="custom-control-label" for="slo">SLO</label>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="custom-control custom-switch custom-control-inline">
                                                <input type="checkbox" value="1" class="custom-control-input cbp"
                                                    id="bp" name="bp" />
                                                <label class="custom-control-label" for="bp">BP</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
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
                                    <div class="form-group">
                                        <label for="progres_bangunan">Progres Bangunan</label>
                                        <input type="range" class="form-control-range" value="0" id="progres_bangunan"
                                            name="progres_bangunan" step="5">
                                        <span id="t_progres_bangunan"></span>%
                                    </div>
                                    <div class="form-group">
                                        <label for="produksi_keterangan">Keterangan</label>
                                        <textarea class="form-control" id="produksi_keterangan"
                                            name="produksi_keterangan" rows="3" placeholder="Keterangan"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="divider">
                                <div class="divider-text">Tanggal Pembangunan Rumah</div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Tanggal Pembangunan</label>
                                        <input type="text" class="form-control flatpickr-human-friendly"
                                            id="tanggal_pembangunan" name="tanggal_pembangunan">
                                        <input type="text" class="hidden" id="tanggal_pembangunan_old"
                                            name="tanggal_pembangunan_old">
                                    </div>
                                    <span class="text-muted" id="lu-tanggal_pembangunan"></span>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Tanggal Rencana Selesai Pembangunan</label>
                                        <input type="text" class="form-control flatpickr-human-friendly"
                                            id="tanggal_rencana_selesai_pembangunan"
                                            name="tanggal_rencana_selesai_pembangunan">
                                        <input type="text" class="hidden" id="tanggal_rencana_selesai_pembangunan_old"
                                            name="tanggal_rencana_selesai_pembangunan_old">
                                    </div>
                                    <span class="text-muted" id="lu-tanggal_rencana_selesai_pembangunan"></span>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Tanggal Selesai Pembangunan</label>
                                        <input type="text" class="form-control flatpickr-human-friendly"
                                            id="tanggal_selesai_pembangunan" name="tanggal_selesai_pembangunan">
                                        <input type="text" class="hidden" id="tanggal_selesai_pembangunan_old"
                                            name="tanggal_selesai_pembangunan_old">
                                    </div>
                                    <span class="text-muted" id="lu-tanggal_selesai_pembangunan"></span>
                                </div>

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

                            <div class="form-group" style="min-height:100px; height: auto;">
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
                                <label>Foto Konstruksi(Pembesian, Pondasi Sloof & Kolom Ringbalok, Pekerjaan Dinding,
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

                    <div class="divider">
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
                                            <span class="sr-only">
                                                << /span>
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

<script>

</script>