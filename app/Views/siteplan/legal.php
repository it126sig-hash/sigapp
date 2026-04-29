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
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content modal-content-custom pt-0">
            <!-- HEADER MODAL -->
            <div class="modal-header-custom">
                <div>
                    <div class="modal-title-main">Siteplan</div>
                    <div class="modal-title-kavling">Data Legal</div>
                </div>
                <button type="button" class="btn-close-modal" data-dismiss="modal"><i class="fas fa-times"></i></button>
            </div>

            <!-- BODY MODAL -->
            <div class="modal-body-custom" style="padding: 0; align-items: stretch;">
                <!-- SIDEBAR NAVIGATION -->
                <div class="modal-sidebar">
                    <div class="sidebar-section-label">Navigasi</div>
                    <nav class="sidebar-nav" style="display: flex; flex-direction: column;">
                        <a href="#legal-sertifikat" class="sidebar-nav-item active"><i class="fas fa-file-signature"></i> Sertipikat</a>
                        <a href="#legal-pbb" class="sidebar-nav-item"><i class="fas fa-file-invoice-dollar"></i> PBB</a>
                        <a href="#legal-bphtb" class="sidebar-nav-item"><i class="fas fa-money-check-alt"></i> BPHTB</a>
                        <a href="#legal-pbg" class="sidebar-nav-item"><i class="fas fa-building"></i> IMB/PBG</a>
                        <a href="#legal-pph" class="sidebar-nav-item"><i class="fas fa-percent"></i> PPH</a>
                        <a href="#legal-ajb" class="sidebar-nav-item"><i class="fas fa-handshake"></i> AJB & PPJB</a>
                        <a href="#upload-sertifikat" class="sidebar-nav-item"><i class="fas fa-upload"></i> Upload Softfile</a>
                    </nav>
                </div>

                <!-- Main Scrollable Content -->
                <div class="modal-main" id="legal-main-scroll-area">
                    <div class="p-2">
                            <p class="modal-title label_alamat mb-2" id="label_alamat5"></p>

                            <form id="fm-legal">
                                <input type="hidden" class="form-control id_kavling" name="id_kavling" value="" />
                                <input type="hidden" class="form-control" id="id_legal" name="id_legal" value="" />

                                <!-- Section: Sertipikat -->
                                <div id="legal-sertifikat" class="scroll-section mb-3">
                                    <h4 class="section-title">Sertipikat</h4>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="card mb-2">
                                                <div class="card-header">
                                                    <h5>Sertipikat Induk</h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="form-group">
                                                        <label>No HGB Induk/Nibel</label>
                                                        <input type="text" class="form-control" id="sertifikat_split_no_hgb_induk" name="sertifikat_split_no_hgb_induk">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Split Sertifikat</label>
                                                        <select name="sertifikat_is_split" class="form-control" id="sertifikat_is_split">
                                                            <option value="0">Tidak</option>
                                                            <option value="1">Ya</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="card mb-2">
                                                <div class="card-header">
                                                    <h5>Sertipikat Split</h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="select-sertifikat_is_split">
                                                        <div class="form-group">
                                                            <label>No HGB</label>
                                                            <input type="text" class="form-control" id="sertifikat_split_no_hgb" name="sertifikat_split_no_hgb">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Tanggal Terbit Sertipikat</label>
                                                            <input type="text" id="sertifikat_split_tanggal_terbit" name="sertifikat_split_tanggal_terbit" class="form-control flatpickr-human-friendly" placeholder="-" />
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Tanggal Terbit Berakhir</label>
                                                            <input type="text" id="sertifikat_split_tanggal_berakhir" name="sertifikat_split_tanggal_berakhir" class="form-control flatpickr-human-friendly" placeholder="-" />
                                                        </div>
                                                        <div class="form-group">
                                                            <label>NIB</label>
                                                            <input type="text" class="form-control" id="sertifikat_split_nib" name="sertifikat_split_nib">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Tanggal Surat Ukur</label>
                                                            <input type="text" id="sertifikat_split_tanggal_surat_ukur" name="sertifikat_split_tanggal_surat_ukur" class="form-control flatpickr-human-friendly" placeholder="-" />
                                                        </div>
                                                        <div class="form-group">
                                                            <label>No Surat Ukur</label>
                                                            <input type="text" id="sertifikat_split_no_surat_ukur" name="sertifikat_split_no_surat_ukur" class="form-control" placeholder="-" />
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Luas Tanah (m2)</label>
                                                            <input type="text" class="form-control" id="sertifikat_split_luas_tanah" name="sertifikat_split_luas_tanah">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="card mb-2">
                                                <div class="card-header">
                                                    <h5>Sertipikat Balik Nama</h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="form-group">
                                                        <label>Balik Nama Sertifikat</label>
                                                        <select name="sertifikat_is_balik_nama" class="form-control" id="sertifikat_is_balik_nama">
                                                            <option value="Belum">Belum</option>
                                                            <option value="Sudah">Sudah</option>
                                                        </select>
                                                    </div>
                                                    <div class="select-sertifikat_is_balik_nama">
                                                        <div class="form-group">
                                                            <label>Nama Konsumen</label>
                                                            <input type="text" readonly class="form-control" id="sertifikat_balik_nama" name="sertifikat_balik_nama">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>NIB Elektronik</label>
                                                            <input type="text" class="form-control" id="sertifikat_nib_elektronik" name="sertifikat_nib_elektronik">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Tanggal Pengiriman</label>
                                                            <input type="text" id="sertifikat_balik_nama_tgl_pengiriman" name="sertifikat_balik_nama_tgl_pengiriman" class="form-control flatpickr-human-friendly" placeholder="-" />
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Dikirim Ke Bank/Konsumen/Notaris</label>
                                                            <input type="text" class="form-control" id="sertifikat_balik_nama_ke" name="sertifikat_balik_nama_ke">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Section: PBB -->
                                <div id="legal-pbb" class="scroll-section mb-3">
                                    <h4 class="section-title">PBB</h4>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="card mb-2">
                                                <div class="card-header">
                                                    <h5>Mutasi Pecah PBB</h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="form-group">
                                                        <label>NOP PBB</label>
                                                        <input type="text" class="form-control" id="pbb_pecah_nop" name="pbb_pecah_nop">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Luas Bumi</label>
                                                        <input type="text" class="form-control" id="pbb_pecah_luas_bumi" name="pbb_pecah_luas_bumi">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>NJOP Bumi</label>
                                                        <input type="text" class="form-control num" id="pbb_pecah_njop_bumi" name="pbb_pecah_njop_bumi">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Luas Bangunan</label>
                                                        <input type="text" class="form-control" id="pbb_pecah_luas_bangunan" name="pbb_pecah_luas_bangunan">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>NJOP Bangunan</label>
                                                        <input type="text" class="form-control num" id="pbb_pecah_njop_bangunan" name="pbb_pecah_njop_bangunan">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Tanggal bayar</label>
                                                        <input type="text" id="pbb_pecah_tanggal_bayar" name="pbb_pecah_tanggal_bayar" class="form-control flatpickr-human-friendly" placeholder="-" />
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Jumlah Tagihan</label>
                                                        <input type="text" class="form-control num" id="pbb_pecah_jumlah_tagihan" name="pbb_pecah_jumlah_tagihan">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="card mb-2">
                                                <div class="card-header">
                                                    <h5>Pembetulan</h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="form-group">
                                                        <label>Pembetulan PBB</label>
                                                        <select name="pbb_is_pembetulan" class="form-control" id="pbb_is_pembetulan">
                                                            <option value="Tidak">Tidak</option>
                                                            <option value="Iya">Iya</option>
                                                        </select>
                                                    </div>
                                                    <div id="select-pbb_is_pembetulan">
                                                        <div class="form-group">
                                                            <label>Tanggal Pembetulan</label>
                                                            <input type="text" id="pbb_tgl_pembetulan" name="pbb_tgl_pembetulan" class="form-control flatpickr-human-friendly" placeholder="-" />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card mb-2">
                                                <div class="card-header">
                                                    <h5>Balik Nama PBB</h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="form-group">
                                                        <label>Balik Nama PBB</label>
                                                        <select name="pbb_is_balik_nama" class="form-control" id="pbb_is_balik_nama">
                                                            <option value="Belum">Belum</option>
                                                            <option value="Sudah">Sudah</option>
                                                        </select>
                                                    </div>
                                                    <div class="select-pbb_is_balik_nama">
                                                        <div class="form-group">
                                                            <label>Nama Konsumen</label>
                                                            <input type="text" readonly class="form-control" id="pbb_balik_nama" name="pbb_balik_nama">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Tanggal Pengiriman</label>
                                                            <input type="text" id="pbb_balik_nama_tgl_pengiriman" name="pbb_balik_nama_tgl_pengiriman" class="form-control flatpickr-human-friendly" placeholder="-" />
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Dikirim Ke Bank/Konsumen/Notaris</label>
                                                            <input type="text" class="form-control" id="pbb_balik_nama_ke" name="pbb_balik_nama_ke">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Section: BPHTB -->
                                <div id="legal-bphtb" class="scroll-section mb-3">
                                    <h4 class="section-title">BPHTB</h4>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="card mb-2">
                                                <div class="card-header">
                                                    <h5>Verifikasi BPHTB</h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="form-group">
                                                        <label>Tanggal Verifikasi</label>
                                                        <input type="text" class="form-control flatpickr-human-friendly" id="bphtb_tanggal_verifikasi" name="bphtb_tanggal_verifikasi">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Jatuh Tempo</label>
                                                        <input type="text" class="form-control flatpickr-human-friendly" id="bphtb_jatuh_tempo" name="bphtb_jatuh_tempo">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Tanggal Perpanjangan Jatuh Tempo</label>
                                                        <input type="text" class="form-control flatpickr-human-friendly" id="bphtb_perpanjang_jatuh_tempo" name="bphtb_perpanjang_jatuh_tempo">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Tanggal Pembayaran</label>
                                                        <input type="text" class="form-control flatpickr-human-friendly" id="bphtb_tanggal_pembayaran" name="bphtb_tanggal_pembayaran">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Nominal Yang Disetujui</label>
                                                        <input type="text" readonly class="form-control num" id="bphtb_nominal_disetujui" name="bphtb_nominal_disetujui">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="card mb-2">
                                                <div class="card-header">
                                                    <h5>Validasi BPHTB</h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="form-group">
                                                        <label>Tanggal Validasi</label>
                                                        <input type="text" class="form-control flatpickr-human-friendly" id="bphtb_tanggal_validasi" name="bphtb_tanggal_validasi">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>No NTPD</label>
                                                        <input type="text" class="form-control" id="bphtb_nominal_tervalidasi" name="bphtb_nominal_tervalidasi">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Section: IMB/PBG -->
                                <div id="legal-pbg" class="scroll-section mb-3">
                                    <h4 class="section-title">IMB/PBG</h4>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="card mb-2">
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
                                                        <input type="text" id="pbg_tanggal_terbit" name="pbg_tanggal_terbit" class="form-control flatpickr-human-friendly" placeholder="-" />
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Tipe</label>
                                                        <input type="text" id="pbg_tipe" name="pbg_tipe" class="form-control" placeholder="-" />
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
                                                        <input type="text" id="pbg_tanggal_kirim" name="pbg_tanggal_kirim" class="form-control flatpickr-human-friendly" placeholder="-" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="card mb-2">
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
                                                            <input type="text" class="form-control" id="pbg_no_revisi" name="pbg_no_revisi">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Tanggal terbit</label>
                                                            <input type="text" id="pbg_tanggal_terbit_revisi" name="pbg_tanggal_terbit_revisi" class="form-control flatpickr-human-friendly" placeholder="-" />
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Tipe</label>
                                                            <input type="text" id="pbg_tipe_revisi" name="pbg_tipe_revisi" class="form-control" placeholder="-" />
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Status</label>
                                                            <select id="pbg_status_revisi" name="pbg_status_revisi" class="form-control">
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
                                </div>

                                <!-- Section: PPH -->
                                <div id="legal-pph" class="scroll-section mb-3">
                                    <h4 class="section-title">PPH</h4>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="card mb-2">
                                                <div class="card-body">
                                                    <div class="form-group">
                                                        <label>Nominal Dibayar</label>
                                                        <input type="text" readonly class="form-control num" id="pph_nominal_bayar" name="pph_nominal_bayar">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Tanggal Bayar</label>
                                                        <input type="text" class="form-control flatpickr-human-friendly" id="pph_tgl_bayar" name="pph_tgl_bayar">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="card mb-2">
                                                <div class="card-body">
                                                    <div class="form-group">
                                                        <label>Jenis Validasi</label>
                                                        <select class="form-control" id="pph_jenis_validasi" name="pph_jenis_validasi">
                                                            <option value=""></option>
                                                            <option value="Offline">Offline</option>
                                                            <option value="Online">Online</option>
                                                        </select>
                                                    </div>
                                                    <div class="select-pph-validasi-offline" class="hide">
                                                        <div class="form-group">
                                                            <label>Tanggal Validasi</label>
                                                            <input type="text" class="form-control flatpickr-human-friendly" id="pph_tanggal_validasi" name="pph_tanggal_validasi">
                                                        </div>
                                                    </div>
                                                    <div class="select-pph-validasi-online" class="hide">
                                                        <div class="form-group">
                                                            <label>Tanggal Permohonan</label>
                                                            <input type="text" class="form-control flatpickr-human-friendly" id="pph_tgl_permohonan" name="pph_tgl_permohonan">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Tanggal Selesai</label>
                                                            <input type="text" class="form-control flatpickr-human-friendly" id="pph_tgl_selesai" name="pph_tgl_selesai">
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
                                </div>

                                <!-- Section: AJB -->
                                <div id="legal-ajb" class="scroll-section mb-3">
                                    <h4 class="section-title">AJB & PPJB</h4>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="card mb-2">
                                                <div class="card-header">
                                                    <h5>AJB</h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="form-group">
                                                        <label>No AJB</label>
                                                        <input type="text" class="form-control" id="ajb_no" name="ajb_no">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Tanggal AJB</label>
                                                        <input type="text" class="form-control flatpickr-human-friendly" id="ajb_tanggal" name="ajb_tanggal">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Notaris</label>
                                                        <input type="text" class="form-control" id="ajb_notaris" name="ajb_notaris">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Dikirim Ke Bank/Konsumen</label>
                                                        <input type="text" class="form-control" id="ajb_dikirim_ke" name="ajb_dikirim_ke">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Tanggal Dikirim Ke Bank/Konsumen</label>
                                                        <input type="text" class="form-control flatpickr-human-friendly" id="ajb_tanggal_dikirim" name="ajb_tanggal_dikirim">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="card mb-2">
                                                <div class="card-header">
                                                    <h5>PPJB</h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="form-group">
                                                        <label>No PPJB</label>
                                                        <input type="text" class="form-control" id="ppjb_no" name="ppjb_no">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Tanggal PPJB</label>
                                                        <input type="text" class="form-control flatpickr-human-friendly" id="ppjb_tanggal" name="ppjb_tanggal">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Notaris</label>
                                                        <input type="text" class="form-control" id="ppjb_notaris" name="ppjb_notaris">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>

                            <!-- Section: Upload Softfile -->
                            <div id="upload-sertifikat" class="scroll-section mb-3">
                                <h4 class="section-title">Upload Softfile</h4>
                                <div class="card mb-2">
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

                                <form id="fl-legal">
                                    <div class="row">
                                        <div class="col-sm-12 col-md-6 col-lg-6">
                                            <div class="card mb-2">
                                                <div class="card-body">
                                                    <div class="form-group">
                                                        <label for="fl-kategori"> Kategori </label>
                                                        <select id="fl-kategori" class="form-control" name="fl-kategori">
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
                                                        <input type="text" id="fl-file_name" name="fl-file_name" class="form-control" placeholder="Nama File" maxlength="255">
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="fl-keterangan"> Keterangan: </label>
                                                        <textarea cols="40" rows="5" id="fl-keterangan" name="fl-keterangan" class="form-control" placeholder="Keterangan"></textarea>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="fl-file"> Pilih File: </label>
                                                        <div class="custom-file">
                                                            <input type="file" class="custom-file-input" name="fl-file" accept="application/pdf" id="fl-file" />
                                                            <label class="custom-file-label" id="fl-label" for="customFile">Pilih Berkas</label>
                                                        </div>
                                                    </div>
                                                    <button type="reset" onclick="$('#fl-label').html('Pilih Berkas')" class="btn btn-outline-secondary">Reset</button>
                                                    <button onclick="fl_upload()" type="button" class="btn btn-primary data-submit mr-1" id="fl-btn-upload">Unggah</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>

                        </div>
                    </div> <!-- End modal-main -->
            </div> <!-- End modal-body-custom -->
            <div class="modal-footer-custom">
                <button type="button" class="btn-cancel" data-dismiss="modal"><i class="fas fa-times mr-1"></i> Batal</button>
                <button type="button" id="add-form-btn-legal" class="btn-save" onclick="save_legal()"><i class="fas fa-save mr-1"></i> Simpan</button>
            </div>
        </div>
    </div>
</div>