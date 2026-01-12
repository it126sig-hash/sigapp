<!--#################################### Modal Legal #########################################-->
<div class="modal modal-slide-in fade" id="modal_fotherlegal">
    <div class="modal-dialog sidebar-sm">
        <form id="fm-fotherlegal" class="add-new-record modal-content pt-0">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button>
            <div class="modal-header mb-1">
                <h5 class="modal-title" id="exampleModalLabel">Legal</h5>
            </div>
            <div class="modal-body flex-grow-1">
                <p class="modal-title label_alamat" id="label_alamat5"></p>
                <br>
                <span>Luas di Siteplan : <br>
                    <span class='t_luas_planning'></span>
                </span>
                <br>
                <br>
                <span>Luas di Lapangan : <br>
                    <span class='t_luas_produksi'></span>
                </span>
                <hr>
                <input type="hidden" class="form-control id_kavling" name="id_kavling" value="" />

                <div class="form-group">
                    <label for="fl_progres_jalan">Progres</label>
                    <input disabled type="range" onInput="$('.r_progres').html($(this).val())"
                        class="form-control-range" min="0" max="100" step="5" id="fl_progres_jalan">
                    <span class="r_progres"></span><span>%</span>
                </div>

                <div class="form-group">
                    <label for="f_legal_luas">Luas di Sertifikat</label>
                    <input type="text" class="form-control" id="f_legal_luas" name="f_legal_luas"
                        placeholder="Luas jalan di sertifikat" />
                </div>

                <div class="form-group">
                    <label for="f_legal_keterangan">Keterangan</label>
                    <textarea class="form-control" id="f_legal_keterangan" name="f_legal_keterangan" rows="3"
                        placeholder="Keterangan"></textarea>
                </div>

                <button id="save-fother-btn-legal" class="btn btn-primary data-submit mr-1" onclick="save_fotherlegal()"
                    href="javascript:void(0)">Simpan</button>
                <button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>
</div>
<div class="modal fade" id="modal_flegal">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="add-new-record modal-content pt-0">
            <div class="modal-header mb-1">
                <h5 class="modal-title" id="exampleModalLabel">Legal</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button>
            </div>
            <div class="modal-body flex-grow-1" style="background-color:#eee">
                <p class="modal-title label_alamat" id="label_alamat5"></p>
                <hr>
                <div class="card">
                    <ul class="nav nav-pills flex-column flex-md-row mt-1 row-gap-2" role="tablist">

                        <li class="nav-item">
                            <a class="nav-link active" id="legal-sertifikat-tab" data-toggle="tab"
                                href="#legal-sertifikat" aria-controls="home" role="tab"
                                aria-selected="true">Sertipikat</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link " id="legal-pbb-tab" data-toggle="tab" href="#legal-pbb"
                                aria-controls="home" role="tab" aria-selected="true">PBB</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link " id="legal-bphtb-tab" data-toggle="tab" href="#legal-bphtb"
                                aria-controls="home" role="tab" aria-selected="true">BPHTB</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link " id="legal-pbg-tab" data-toggle="tab" href="#legal-pbg"
                                aria-controls="home" role="tab" aria-selected="true">IMB/PBG</a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link " id="legal-pph-tab" data-toggle="tab" href="#legal-pph"
                                aria-controls="home" role="tab" aria-selected="true">PPH</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link " id="legal-ajb-tab" data-toggle="tab" href="#legal-ajb"
                                aria-controls="home" role="tab" aria-selected="true">AJB</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="upload-sertifikat-tab" data-toggle="tab" href="#upload-sertifikat"
                                aria-controls="home" role="tab" aria-selected="true">Upload Softfile</a>
                        </li>
                    </ul>
                </div>
                <div class="tab-content">

                    <div class="tab-pane" id="legal-pbb" aria-labelledby="legal-pbb-tab" role="tabpanel">
                        <form id="fm-legal">
                            <input type="hidden" class="form-control id_kavling" name="id_kavling" value="" />
                            <input type="hidden" class="form-control" id="id_legal" name="id_legal" value="" />

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5>Mutasi Pecah PBB</h5>
                                        </div>
                                        <div class="card-body">

                                            <div class="form-group">
                                                <label>NOP PBB</label>
                                                <input type="text" class="form-control" id="pbb_pecah_nop"
                                                    name="pbb_pecah_nop">
                                            </div>
                                            <div class="form-group">
                                                <label>Luas Bumi</label>
                                                <input type="text" class="form-control" id="pbb_pecah_luas_bumi"
                                                    name="pbb_pecah_luas_bumi">
                                            </div>
                                            <div class="form-group">
                                                <label>NJOP Bumi</label>
                                                <input type="text" class="form-control num" id="pbb_pecah_njop_bumi"
                                                    name="pbb_pecah_njop_bumi">
                                            </div>
                                            <div class="form-group">
                                                <label>Luas Bangunan</label>
                                                <input type="text" class="form-control" id="pbb_pecah_luas_bangunan"
                                                    name="pbb_pecah_luas_bangunan">
                                            </div>
                                            <div class="form-group">
                                                <label>NJOP Bangunan</label>
                                                <input type="text" class="form-control num" id="pbb_pecah_njop_bangunan"
                                                    name="pbb_pecah_njop_bangunan">
                                            </div>
                                            <div class="form-group">
                                                <label>Tanggal bayar</label>
                                                <input type="text" id="pbb_pecah_tanggal_bayar"
                                                    name="pbb_pecah_tanggal_bayar"
                                                    class="form-control flatpickr-human-friendly" placeholder="-" />
                                            </div>
                                            <div class="form-group">
                                                <label>Jumlah Tagihan</label>
                                                <input type="text" class="form-control num"
                                                    id="pbb_pecah_jumlah_tagihan" name="pbb_pecah_jumlah_tagihan">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5>Pembetulan</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="form-group">
                                                <label>Pembetulan PBB</label>
                                                <select name="pbb_is_pembetulan" class="form-control"
                                                    id="pbb_is_pembetulan">
                                                    <option value="Tidak">Tidak</option>
                                                    <option value="Iya">Iya</option>
                                                </select>
                                            </div>
                                            <div id="select-pbb_is_pembetulan">
                                                <div class="form-group">
                                                    <label>Tanggal Pembetulan</label>
                                                    <input type="text" id="pbb_tgl_pembetulan" name="pbb_tgl_pembetulan"
                                                        class="form-control flatpickr-human-friendly" placeholder="-" />
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                    <div class="card">
                                        <div class="card-header">
                                            <h5>Balik Nama PBB</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="form-group">
                                                <label>Balik Nama PBB</label>
                                                <select name="pbb_is_balik_nama" class="form-control"
                                                    id="pbb_is_balik_nama">
                                                    <option value="Belum">Belum</option>
                                                    <option value="Sudah">Sudah</option>
                                                </select>
                                            </div>
                                            <div class="select-pbb_is_balik_nama">
                                                <div class="form-group">
                                                    <label>Nama Konsumen</label>
                                                    <input type="text" readonly class="form-control" id="pbb_balik_nama"
                                                        name="pbb_balik_nama">
                                                </div>
                                                <div class="form-group">
                                                    <label>Tanggal Pengiriman</label>
                                                    <input type="text" id="pbb_balik_nama_tgl_pengiriman"
                                                        name="pbb_balik_nama_tgl_pengiriman"
                                                        class="form-control flatpickr-human-friendly" placeholder="-" />
                                                </div>
                                                <div class="form-group">
                                                    <label>Dikirim Ke Bank/Konsumen/Notaris</label>
                                                    <input type="text" class="form-control" id="pbb_balik_nama_ke"
                                                        name="pbb_balik_nama_ke">
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                                <!-- 
                                <div class="col-sm-12 col-md-3 col-lg-3">
                                    <h5 class="modal-title" id="exampleModalLabel">Sertifikat</h5>
                                    <div class="form-group">
                                        <label for="sertifikat_tgl">Tanggal Sertifikat</label>
                                        <input type="text" id="sertifikat_tgl" name="sertifikat_tgl" class="form-control flatpickr-human-friendly" placeholder="-" />
                                    </div>
                                    <div class="form-group">
                                        <label for="sertifikat_luas">Luas Tanah</label>
                                        <input type="text" class="form-control" id="sertifikat_luas" name="sertifikat_luas">
                                    </div>
                                    <div class="form-group">
                                        <label for="sertifikat_no_hgb">No HGB</label>
                                        <input type="text" class="form-control" id="sertifikat_no_hgb" name="sertifikat_no_hgb">
                                    </div>
                                    <div class="form-group">
                                        <label for="sertifikat_no_split">No Split</label>
                                        <input type="text" class="form-control" id="sertifikat_no_split" name="sertifikat_no_split">
                                    </div>
                                    <div class="form-group">
                                        <label for="sertifikat_masa_berlaku">Masa Berlaku</label>
                                        <input type="text" id="sertifikat_masa_berlaku" name="sertifikat_masa_berlaku" class="form-control flatpickr-human-friendly" placeholder="-" />
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-4 col-lg-4">
                                    <div class="divider">
                                        <div class="divider-text">IMB</div>
                                    </div>
                                    <div class="form-group">
                                        <label for="imb_tgl">Tanggal IMB</label>
                                        <input type="text" id="imb_tgl" name="imb_tgl" class="form-control flatpickr-human-friendly" placeholder="-" />
                                    </div>
                                    <div class="form-group">
                                        <label for="imb_no_induk">No Induk</label>
                                        <input type="text" class="form-control" id="imb_no_induk" name="imb_no_induk">
                                    </div>
                                    <div class="form-group">
                                        <label for="imb_no_split">No Split</label>
                                        <input type="text" class="form-control" id="imb_no_split" name="imb_no_split">
                                    </div>
                                    <div class="divider">
                                        <div class="divider-text">BPHTB</div>
                                    </div>
                                    <div class="form-group">
                                        <label for="bphtb_tgl">Tanggal BPHTB</label>
                                        <input type="text" id="bphtb_tgl" name="bphtb_tgl" class="form-control flatpickr-human-friendly" placeholder="-" />
                                    </div>
                                    <div class="form-group">
                                        <label for="bphtb_masa_berlaku">Masa Berlaku</label>
                                        <input type="text" id="bphtb_masa_berlaku" name="bphtb_masa_berlaku" class="form-control flatpickr-human-friendly" placeholder="-" />
                                    </div>
                                    <div class="form-group">
                                        <label for="bphtb_validasi">Validasi</label>
                                        <input type="text" id="bphtb_validasi" name="bphtb_validasi" class="form-control flatpickr-human-friendly" placeholder="-" />
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-4 col-lg-4">
                                    <div class="divider">
                                        <div class="divider-text">NOP</div>
                                    </div>
                                    <div class="form-group">
                                        <label for="nop_pbb">NOP</label>
                                        <input type="text" class="form-control" id="nop_pbb" name="nop_pbb">
                                    </div>
                                    <div class="form-group">
                                        <label for="pph">PPh</label>
                                        <input type="text" class="form-control" id="pph" name="pph">
                                    </div>
                                    <div class="form-group">
                                        <label for="pbg">PBG</label>
                                        <input type="text" class="form-control" id="pbg" name="pbg">
                                    </div>
                                  
                                    <div class="form-group">
                                        <label for="legal_keterangan">Keterangan</label>
                                        <textarea class="form-control" id="legal_keterangan" name="legal_keterangan" rows="3" placeholder="Keterangan"></textarea>
                                    </div>
                                </div> -->
                            </div>
                    </div>
                    <div class="tab-pane active" id="legal-sertifikat" aria-labelledby="legal-sertifikat-tab"
                        role="tabpanel">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Sertipikat</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label>No HGB Induk/Nibel</label>
                                            <input type="text" class="form-control" id="sertifikat_split_no_hgb_induk"
                                                name="sertifikat_split_no_hgb_induk">
                                        </div>
                                        <div class="form-group">
                                            <label>Split Sertifikat</label>
                                            <select name="sertifikat_is_split" class="form-control"
                                                id="sertifikat_is_split">
                                                <option value="0">Tidak</option>
                                                <option value="1">Ya</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="card-header">
                                        <h5>Sertipikat Split</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="select-sertifikat_is_split ">
                                            <div class="form-group">
                                                <label>No HGB</label>
                                                <input type="text" class="form-control" id="sertifikat_split_no_hgb"
                                                    name="sertifikat_split_no_hgb">
                                            </div>
                                            <div class="form-group">
                                                <label>Tanggal Terbit Sertipikat</label>
                                                <input type="text" id="sertifikat_split_tanggal_terbit"
                                                    name="sertifikat_split_tanggal_terbit"
                                                    class="form-control flatpickr-human-friendly" placeholder="-" />
                                            </div>
                                            <div class="form-group">
                                                <label>Tanggal Terbit Berakhir</label>
                                                <input type="text" id="sertifikat_split_tanggal_berakhir"
                                                    name="sertifikat_split_tanggal_berakhir"
                                                    class="form-control flatpickr-human-friendly" placeholder="-" />
                                            </div>
                                            <div class="form-group">
                                                <label>NIB</label>
                                                <input type="text" class="form-control" id="sertifikat_split_nib"
                                                    name="sertifikat_split_nib">
                                            </div>
                                            <div class="form-group">
                                                <label>Tanggal Surat Ukur</label>
                                                <input type="text" id="sertifikat_split_tanggal_surat_ukur"
                                                    name="sertifikat_split_tanggal_surat_ukur"
                                                    class="form-control flatpickr-human-friendly" placeholder="-" />
                                            </div>
                                            <div class="form-group">
                                                <label>No Surat Ukur</label>
                                                <input type="text" id="sertifikat_split_no_surat_ukur"
                                                    name="sertifikat_split_no_surat_ukur" class="form-control"
                                                    placeholder="-" />
                                            </div>
                                            <div class="form-group">
                                                <label>Luas Tanah (m2)</label>
                                                <input type="text" class="form-control" id="sertifikat_split_luas_tanah"
                                                    name="sertifikat_split_luas_tanah">
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Sertipikat Balik Nama</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label>Balik Nama Sertifikat</label>
                                            <select name="sertifikat_is_balik_nama" class="form-control"
                                                id="sertifikat_is_balik_nama">
                                                <option value="Belum">Belum</option>
                                                <option value="Sudah">Sudah</option>
                                            </select>
                                        </div>
                                        <div class="select-sertifikat_is_balik_nama">
                                            <div class="form-group">
                                                <label>Nama Konsumen</label>
                                                <input type="text" readonly class="form-control"
                                                    id="sertifikat_balik_nama" name="sertifikat_balik_nama">
                                            </div>
                                            <div class="form-group">
                                                <label>NIB Elektronik</label>
                                                <input type="text" class="form-control" id="sertifikat_nib_elektronik"
                                                    name="sertifikat_nib_elektronik">
                                            </div>
                                            <div class="form-group">
                                                <label>Tanggal Pengiriman</label>
                                                <input type="text" id="sertifikat_balik_nama_tgl_pengiriman"
                                                    name="sertifikat_balik_nama_tgl_pengiriman"
                                                    class="form-control flatpickr-human-friendly" placeholder="-" />
                                            </div>
                                            <div class="form-group">
                                                <label>Dikirim Ke Bank/Konsumen/Notaris</label>
                                                <input type="text" class="form-control" id="sertifikat_balik_nama_ke"
                                                    name="sertifikat_balik_nama_ke">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="tab-pane " id="legal-pbg" aria-labelledby="legal-pbg-tab" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>IMB/PBG</h5>
                                    </div>
                                    <div class="card-body">

                                        <div class="form-group">
                                            <label>No IMB/PBG</label>
                                            <input type="text" class="form-control" id="pbg_no" name="pbg_no">
                                        </div>
                                        <div class="form-group">
                                            <label>Tanggal terbit</label>
                                            <input type="text" id="pbg_tanggal_terbit" name="pbg_tanggal_terbit"
                                                class="form-control flatpickr-human-friendly" placeholder="-" />
                                        </div>
                                        <!-- <div class="form-group">
                                            <label>Tanggal Pengajuan</label>
                                            <input type="hidden" id="pbg_tanggal_pengajuan" name="pbg_tanggal_pengajuan"
                                                class="form-control flatpickr-human-friendly" placeholder="-" />
                                        </div> -->
                                        <div class="form-group">
                                            <label>Tipe</label>
                                            <input type="text" id="pbg_tipe" name="pbg_tipe" class="form-control"
                                                placeholder="-" />
                                        </div>
                                        <div class="form-group">
                                            <label>Status</label>
                                            <select id="pbg_status" name="pbg_status" class="form-control">
                                                <option value="">-</option>
                                                <option value="Proses">Proses</option>
                                                <option value="Selesai">Selesai</option>
                                                <option value="Terjadi Masalah">Terjadi Masalah</option>
                                            </select>
                                        </div>
                                        <div class="divider">
                                            <div class="divider-text">Pengiriman</div>
                                        </div>
                                        <div class="form-group">
                                            <label>Dikirim Ke Bank/Konsumen</label>
                                            <select name="pbg_dikirim_ke" class="form-control" id="pbg_dikirim_ke">
                                                <option value="null"></option>
                                                <option value="Bank BTN">Bank BTN</option>
                                                <option value="Konsumen">Konsumen</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Tanggal Kirim Ke Bank/Konsumen</label>
                                            <input type="text" id="pbg_tanggal_kirim" name="pbg_tanggal_kirim"
                                                class="form-control flatpickr-human-friendly" placeholder="-" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Revisi IMB/PBG</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label>Revisi IMB/PBG</label>
                                            <select name="pbg_is_revisi" class="form-control" id="pbg_is_revisi">
                                                <option value="Tidak">Tidak</option>
                                                <option value="Ya">Ya</option>
                                            </select>
                                        </div>
                                        <div class="select-pbg_is_revisi">
                                            <div class="form-group">
                                                <label>No IMB/PBG</label>
                                                <input type="text" class="form-control" id="pbg_no_revisi"
                                                    name="pbg_no_revisi">
                                            </div>
                                            <div class="form-group">
                                                <label>Tanggal terbit</label>
                                                <input type="text" id="pbg_tanggal_terbit_revisi"
                                                    name="pbg_tanggal_terbit_revisi"
                                                    class="form-control flatpickr-human-friendly" placeholder="-" />
                                            </div>
                                            <!-- <div class="form-group">
                                            <label>Tanggal Pengajuan</label>
                                            <input type="hidden" id="pbg_tanggal_pengajuan" name="pbg_tanggal_pengajuan"
                                                class="form-control flatpickr-human-friendly" placeholder="-" />
                                        </div> -->
                                            <div class="form-group">
                                                <label>Tipe</label>
                                                <input type="text" id="pbg_tipe_revisi" name="pbg_tipe_revisi"
                                                    class="form-control" placeholder="-" />
                                            </div>
                                            <div class="form-group">
                                                <label>Status</label>
                                                <select id="pbg_status_revisi" name="pbg_status_revisi"
                                                    class="form-control">
                                                    <option value="">-</option>
                                                    <option value="Proses">Proses</option>
                                                    <option value="Selesai">Selesai</option>
                                                    <option value="Terjadi Masalah">Terjadi Masalah</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">

                            <!-- <div class="form-group">
                                <label>NJOP Tanah/Bumi</label>
                                <input type="text" id="pbg_njop_tanah" name="pbg_njop_tanah" class="form-control" placeholder="-" />
                            </div>
                            <div class="form-group">
                                <label>NJOP Bangunan</label>
                                <input type="text" id="pbg_njop_bangunan" name="pbg_njop_bangunan" class="form-control" placeholder="-" />
                            </div>
                            <div class="form-group">
                                <label>Mutasi Pecah/Split</label>
                                <input type="text" id="pbg_mutsai_split" name="pbg_mutsai_split" class="form-control" placeholder="-" />
                            </div>
                            <div class="form-group">
                                <label>Mutasi Habis</label>
                                <input type="text" id="pbg_mutasi_habis" name="pbg_mutasi_habis" class="form-control" placeholder="-" />
                            </div>
                            <div class="form-group">
                                <label>Tipe di Siteplan</label>
                                <input type="text" id="pbg_tipe_siteplan" name="pbg_tipe_siteplan" class="form-control" placeholder="-" />
                            </div>
                            <div class="form-group">
                                <label>Tipe di PBG</label>
                                <input type="text" id="pbg_tipe" name="pbg_tipe" class="form-control" placeholder="-" />
                            </div>
                            <div class="form-group">
                                <label>Tipe di Bangun</label>
                                <input type="text" id="pbg_tipe_dibangun" name="pbg_tipe_dibangun" class="form-control" placeholder="-" />
                            </div> -->
                        </div>
                    </div>
                    <div class="tab-pane " id="legal-bphtb" aria-labelledby="legal-bphtb-tab" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Verifikasi BPHTB</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label>Tanggal Verifikasi</label>
                                            <input type="text" class="form-control flatpickr-human-friendly"
                                                id="bphtb_tanggal_verifikasi" name="bphtb_tanggal_verifikasi">
                                        </div>
                                        <div class="form-group">
                                            <label>Jatuh Tempo</label>
                                            <input type="text" class="form-control flatpickr-human-friendly"
                                                id="bphtb_jatuh_tempo" name="bphtb_jatuh_tempo">
                                        </div>
                                        <div class="form-group">
                                            <label>Tanggal Perpanjangan Jatuh Tempo</label>
                                            <input type="text" class="form-control flatpickr-human-friendly"
                                                id="bphtb_perpanjang_jatuh_tempo" name="bphtb_perpanjang_jatuh_tempo">
                                        </div>

                                        <div class="form-group">
                                            <label>Tanggal Pembayaran</label>
                                            <input type="text" class="form-control flatpickr-human-friendly"
                                                id="bphtb_tanggal_pembayaran" name="bphtb_tanggal_pembayaran">
                                        </div>

                                        <div class="form-group">
                                            <label>Nominal Yang Disetujui</label>
                                            <input type="text" readonly class="form-control num"
                                                id="bphtb_nominal_disetujui" name="bphtb_nominal_disetujui">
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Validasi BPHTB</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label>Tanggal Validasi</label>
                                            <input type="text" class="form-control flatpickr-human-friendly"
                                                id="bphtb_tanggal_validasi" name="bphtb_tanggal_validasi">
                                        </div>
                                        <div class="form-group">
                                            <label>No NTPD</label>
                                            <input type="text" class="form-control" id="bphtb_nominal_tervalidasi"
                                                name="bphtb_nominal_tervalidasi">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane " id="legal-pph" aria-labelledby="legal-pph-tab" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                    </div>
                                    <div class="card-body">

                                        <div class="form-group">
                                            <label>Nominal Dibayar</label>
                                            <input type="text" readonly class="form-control num" id="pph_nominal_bayar"
                                                name="pph_nominal_bayar">
                                        </div>
                                        <div class="form-group">
                                            <label>Tanggal Bayar</label>
                                            <input type="text" class="form-control flatpickr-human-friendly"
                                                id="pph_tgl_bayar" name="pph_tgl_bayar">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label>Jenis Validasi</label>
                                            <select class="form-control" id="pph_jenis_validasi"
                                                name="pph_jenis_validasi">
                                                <option value=""></option>
                                                <option value="Offline">Offline</option>
                                                <option value="Online">Online</option>
                                            </select>
                                        </div>
                                        <div class="select-pph-validasi-offline" class="hide">
                                            <div class="form-group">
                                                <label>Tanggal Validasi</label>
                                                <input type="text" class="form-control flatpickr-human-friendly"
                                                    id="pph_tanggal_validasi" name="pph_tanggal_validasi">
                                            </div>
                                        </div>
                                        <div class="select-pph-validasi-online" class="hide">
                                            <div class="form-group">
                                                <label>Tanggal Permohonan</label>
                                                <input type="text" class="form-control flatpickr-human-friendly"
                                                    id="pph_tgl_permohonan" name="pph_tgl_permohonan">
                                            </div>
                                            <div class="form-group">
                                                <label>Tanggal Selesai</label>
                                                <input type="text" class="form-control flatpickr-human-friendly"
                                                    id="pph_tgl_selesai" name="pph_tgl_selesai">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label>NTPN</label>
                                            <input type="text" class="form-control" readonly id="pph_ntpn" name="pph_ntpn">
                                        </div>

                                        <div class="form-group">
                                            <label>No SKET</label>
                                            <input type="text" class="form-control" id="pph_no_sket" name="pph_no_sket">
                                        </div>

                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="col-12">

                        </div>
                    </div>
                    <div class="tab-pane " id="legal-ajb" aria-labelledby="legal-ajb-tab" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>AJB</h5>
                                    </div>
                                    <div class="card-body">

                                        <div class="form-group">
                                            <label>No AJB</label>
                                            <input type="text" class="form-control " id="ajb_no" name="ajb_no">
                                        </div>
                                        <div class="form-group">
                                            <label>Tanggal AJB</label>
                                            <input type="text" class="form-control flatpickr-human-friendly"
                                                id="ajb_tanggal" name="ajb_tanggal">
                                        </div>
                                        <div class="form-group">
                                            <label>Notaris</label>
                                            <input type="text" class="form-control " id="ajb_notaris"
                                                name="ajb_notaris">
                                        </div>
                                        <div class="form-group">
                                            <label>Dikirim Ke Bank/Konsumen</label>
                                            <input type="text" class="form-control " id="ajb_dikirim_ke"
                                                name="ajb_dikirim_ke">
                                        </div>
                                        <div class="form-group">
                                            <label>Tanggal Dikirim Ke Bank/Konsumen</label>
                                            <input type="text" class="form-control flatpickr-human-friendly"
                                                id="ajb_tanggal_dikirim" name="ajb_tanggal_dikirim">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>PPJB</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label>No PPJB</label>
                                            <input type="text" class="form-control " id="ppjb_no" name="ppjb_no">
                                        </div>
                                        <div class="form-group">
                                            <label>Tanggal PPJB</label>
                                            <input type="text" class="form-control flatpickr-human-friendly"
                                                id="ppjb_tanggal" name="ppjb_tanggal">
                                        </div>
                                        <div class="form-group">
                                            <label>Notaris</label>
                                            <input type="text" class="form-control " id="ppjb_notaris"
                                                name="ppjb_notaris">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">


                        </div>
                    </div>
                    </form>
                    <div class="tab-pane" id="upload-sertifikat" aria-labelledby="upload-sertifikat-tab"
                        role="tabpanel">
                        <div class="card">
                            <div class="card-body">
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
                                                <th scope="col" class="text-nowrap"></th>
                                            </tr>
                                        </thead>
                                        <tbody id="tb-fl-file">
                                            <tr>
                                                <td colspan="6" class="text-center">Tidak ada data</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <br>
                        <form id="fl-legal">
                            <div class="row">
                                <div class="col-sm-12 col-md-6 col-lg-6">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="form-group">
                                                <label for="fl-kategori"> Ketgori </label>
                                                <select id="fl-kategori" class="form-control" name="fl-kategori">
                                                    <!-- <option value="1">Sertifikat</option> -->
                                                    <option value="2">AJB</option>
                                                    <option value="3">PPJB</option>
                                                    <option value="5">PBB</option>
                                                    <option value="6">BAST</option>
                                                    <option value="7">BPHTB</option>
                                                    <option value="14">IMB</option>
                                                    <option value="0">Lainnya</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label for="fl-file_name"> Nama File: </label>
                                                <input type="text" id="fl-file_name" name="fl-file_name" class="form-control"
                                                    placeholder="Nama File" maxlength="255">
                                            </div>
                                            <div class="form-group">
                                                <label for="fl-keterangan"> Keterangan: </label>
                                                <textarea cols="40" rows="5" id="fl-keterangan" name="fl-keterangan"
                                                    class="form-control" placeholder="Keterangan"></textarea>
                                            </div>
                                            <div class="form-group">
                                                <label for="fl-file"> Pilih File: </label>
                                                <div class="custom-file">
                                                    <input type="file" class="custom-file-input" name="fl-file"
                                                        accept="application/pdf" id="fl-file" />
                                                    <label class="custom-file-label" id="fl-label" for="customFile">Pilih
                                                        Berkas</label>
                                                </div>
                                            </div>
                                            <button type="reset" onclick="$('#fl-label').html('Pilih Berkas')"
                                                class="btn btn-outline-secondary">Reset</button>
                                            <button onclick="fl_upload()" class="btn btn-primary data-submit mr-1"
                                                id="fl-btn-upload">Unggah</button>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                <button id="add-form-btn-legal" class="btn btn-primary data-submit mr-1" onclick="save_legal()"
                    href="javascript:void(0)">Simpan</button>
            </div>
        </div>
    </div>
</div>