<!--#################################### Modal Keuangan #########################################-->
<div class="modal fade text-left" id="modal_divisi3">
    <div class="modal-dialog modal-dialog-scrollable modal-xl">
        <form id="fm-keuangan" class="add-new-record modal-content pt-0" autocomplete="off">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Keuangan</h5>

                <button type="button" class="close" data-dismiss="modal" id="close_modal_divisi3" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body flex-grow-1">
                <p class="modal-title label_konsumen" id="label_konsumen"></p>
                <p class="modal-title label_alamat" id="label_alamat3"></p>
                <div class="form-group">
                    <div class="custom-control custom-switch custom-control-inline">
                        <input type="checkbox" class="custom-control-input cbp" id="is_lunas" name="is_lunas"
                            value="1" />
                        <label class="custom-control-label" for="is_lunas">Pembayaran Lunas</label>
                    </div>
                </div>
                <hr>

                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="booking-tab" data-toggle="tab" href="#booking"
                            aria-controls="home" role="tab" aria-selected="true">Booking</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tagihan-tab" data-toggle="tab" href="#tagihan" aria-controls="home"
                            role="tab" aria-selected="true">Uang Muka</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="bb-tab" data-toggle="tab" href="#bb" aria-controls="home" role="tab"
                            aria-selected="true">Biaya-biaya</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="log_pembayaran-tab" data-toggle="tab" href="#log_pembayaran"
                            aria-controls="log_pembayaran" role="tab" aria-selected="false">Riwayat Pembayaran</a>
                    </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="booking" aria-labelledby="booking-tab" role="tabpanel">
                        <input type="hidden" class="form-control" name="status_mkdt" id="status_mkdt" value="" />
                        <input type="hidden" class="form-control id_kavling" name="id_kavling" value="" />
                        <input type="hidden" class="form-control" id="id_mkdt" name="id_mkdt" value="" />
                        <input type="hidden" class="form-control" id="nama_konsumen" name="nama_konsumen" value="" />

                        <div class="col-md-6 col-sm-12 col-lg-6">
                            <div class="form-group">
                                <label for="booking_tgl">Tanggal Booking</label>
                                <input disabled type="text" id="booking_tgl" name="booking_tgl"
                                    class="form-control flatpickr-human-friendly" placeholder="-" />
                            </div>
                            <div class="form-group">
                                <label for="harga_jual">Booking Fee</label>
                                <input readonly type="text" class="form-control num" id="booking_fee"
                                    name="booking_fee">
                            </div>

                            <hr>
                            <div class="hidden">
                                <div class="form-group">
                                    <label for="booking_fee_paid">Sudah Bayar Booking Fee</label>
                                    <select class="form-control" id="booking_fee_paid" name="booking_fee_paid">
                                        <option value="0">Belum</option>
                                        <option value="1" selected>Sudah</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="keu_booking_fee">Booking Fee</label>
                                    <input type="text" id="keu_booking_fee" name="keu_booking_fee"
                                        class="form-control num" value="" />
                                </div>
                                <div class="form-group">
                                    <label for="keu_booking_tgl">Tanggal Bayar Booking Fee</label>
                                    <input type="text" id="keu_booking_tgl" name="keu_booking_tgl"
                                        class="form-control flatpickr-human-friendly" placeholder="-" />
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                            <button class="add-form-btn-keuangan btn btn-primary data-submit mr-1"
                                onclick="save_keuangan(); return false;" href="javascript:void(0)">Simpan</button>
                        </div>
                    </div>
                    <div class="tab-pane" id="tagihan" aria-labelledby="tagihan-tab" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6 col-sm-12 col-lg-6">
                                <div class="divider">
                                    <div class="divider-text">Tagihan Uang Muka + Biaya Adm + Turun KPR + Lainnya</div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table mb-0">
                                        <thead>
                                            <tr>
                                                <th scope="col" class="text-nowrap">No</th>
                                                <th scope="col" class="text-nowrap">Berita Acara</th>
                                                <th scope="col" class="text-nowrap">Nominal</th>
                                                <th scope="col" class="text-nowrap">Jatuh Tempo</th>
                                                <th scope="col" class="text-nowrap">Oleh</th>
                                                <th scope="col" class="text-nowrap">Sudah DIbayar </th>
                                            </tr>
                                        </thead>
                                        <tbody id="tb-data-tagihan">
                                        </tbody>
                                    </table>
                                </div>

                            </div>
                            <div class="col-md-3 col-sm-12 col-lg-3">
                                <div class="divider">
                                    <div class="divider-text">Total Uang Muka</div>
                                </div>
                                <div class="form-group">
                                    <label for="bt-total_biaya_um">Total Tagihan</label>
                                    <input readonly type="text" class="form-control num" id="bt-total_biaya_um"
                                        name="bt-total_biaya_um">
                                </div>

                                <hr>
                                <div class="form-group">
                                    <label for="bt-sudah_bayar_um">Sudah Bayar</label>
                                    <input type="text" class="form-control num" readonly id="bt-sudah_bayar_um"
                                        name="bt-sudah_bayar_um">
                                </div>
                                <div class="form-group">
                                    <label for="bt-sisa_tagihan_um">Sisa Tagihan</label>
                                    <input type="text" class="form-control num" readonly id="bt-sisa_tagihan_um"
                                        name="bt-sisa_tagihan_um">
                                </div>
                                <div class="form-group">
                                    <label for="bt-persentase_bayar_tagihan_um">Persentase</label>
                                    <input type="text" class="form-control" style="text-align:right" readonly
                                        id="bt-persentase_bayar_tagihan_um" name="bt-persentase_bayar_tagihan_um">
                                </div>
                                <div id="hide_refund">
                                    <div class="divider">
                                        <div class="divider-text">Refund</div>
                                    </div>
                                    <div class="form-group">
                                        <div class="custom-control custom-switch custom-control-inline">
                                            <input type="checkbox" class="custom-control-input cbp" id="refund_paid"
                                                name="refund_paid" value="1" />
                                            <label class="custom-control-label" for="refund_paid">Pembayaran
                                                Selesai</label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="keterangan_refund">Keterangan</label>
                                        <textarea class="form-control" id="keterangan_refund" name="keterangan_refund"
                                            rows="3" placeholder="Keterangan"></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="nominal_refund">Nominal</label>
                                        <input type="text" class="form-control num" id="nominal_refund"
                                            name="nominal_refund">
                                    </div>
                                    <div class="form-group">
                                        <label for="tanggal_refund">Tanggal Refund</label>
                                        <input type="text" id="tanggal_refund" name="tanggal_refund"
                                            class="form-control flatpickr-human-friendly" placeholder="-" />
                                    </div>
                                </div>
                                <!-- <a class="btn btn-outline-primary waves-effect col-sm-12" data-toggle="collapse"
                                    href="#collapseExample" role="button" aria-expanded="false"
                                    aria-controls="collapseExample">
                                    Lihat Detail Biaya
                                </a> -->
                                <!-- <div class="collapse" id="collapseExample">                                
                                    <div class="form-group">
                                        <label class="form-label" for="basic-icon-default-fullname">Harga Jual</label>
                                        <input type="text" class="form-control num bt-fm" id="bt-harga_jual"
                                            name="bt-harga_jual" value="" readonly />
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label" for="basic-icon-default-fullname">ACC KPR</label>
                                        <input type="text" class="form-control num bt-fm" id="bt-harga_kpr_acc"
                                            name="bt-harga_kpr_acc" value="" readonly />
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label" for="basic-icon-default-fullname">Diskon</label>
                                        <input type="text" class="form-control num bt-fm" id="bt-harga_diskon"
                                            name="bt-harga_diskon" value="" readonly />
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label" for="basic-icon-default-fullname">Biaya Adm</label>
                                        <input type="text" class="form-control num bt-fm" id="bt-harga_administrasi"
                                            name="bt-harga_administrasi" value="" readonly />
                                    </div>
                                </div> -->

                            </div>
                            <!-- <div class="col-md-2 col-sm-12 col-lg-2">
                                <div class="divider">
                                    <div class="divider-text">Total Biaya Adm + Turun KPR + Lainnnya</div>
                                </div>
                                <div class="form-group">
                                    <label for="bt-total_biaya_um">Total Tagihan</label>
                                    <input readonly type="text" class="form-control num" id="bt-total_biaya_um_ll"
                                        name="bt-total_biaya_um_ll">
                                </div>

                                <hr>
                                <div class="form-group">
                                    <label for="bt-sudah_bayar_um">Sudah Bayar</label>
                                    <input type="text" class="form-control num" readonly id="bt-sudah_bayar_um_ll"
                                        name="bt-sudah_bayar_um_ll">
                                </div>
                                <div class="form-group">
                                    <label for="bt-sisa_tagihan_um">Sisa Tagihan</label>
                                    <input type="text" class="form-control num" readonly id="bt-sisa_tagihan_um_ll"
                                        name="bt-sisa_tagihan_um_ll">
                                </div>
                                <div class="form-group">
                                    <label for="bt-persentase_bayar_tagihan_um">Persentase</label>
                                    <input type="text" class="form-control" style="text-align:right" readonly
                                        id="bt-persentase_bayar_tagihan_um_ll" name="bt-persentase_bayar_tagihan_um_ll">
                                </div>
                               

                            </div> -->
                            <div class="col-md-3 col-sm-12 col-lg-3">
                                <div class="hide_lunas">
                                    <div class="divider">
                                        <div class="divider-text">Pembayaran</div>
                                    </div>
                                    <div class="form-group">
                                        <label for="keterangan_refund">Untuk Pembayaran</label>
                                        <select multiple="multiple" name="bt-for[]" id="bt-for"
                                            class="form-control form-select"></select>
                                    </div>
                                    <div class="form-group">
                                        <label for="berita_acara">Keterangan Pembayaran</label>
                                        <textarea class="form-control" id="bt-berita_acara_um" name="bt-berita_acara_um"
                                            rows="3" placeholder="Keterangan"></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="sisa_tagihan">Nominal Pembayaran</label>
                                        <input type="text" class="form-control num" id="bt-bayar_tagihan_um"
                                            name="bt-bayar_tagihan_um">
                                    </div>
                                    <div class="form-group">
                                        <label for="tanggal_bayar">Tanggal Pembayaran</label>
                                        <input type="text" id="bt-tanggal_bayar_um" name="bt-tanggal_bayar_um"
                                            class="form-control flatpickr-human-friendly" placeholder="-" />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                            <button class="add-form-btn-keuangan btn btn-primary data-submit mr-1"
                                onclick="save_keuangan(); return false;" href="javascript:void(0)">Simpan</button>
                        </div>
                    </div>
                    <div class="tab-pane" id="log_pembayaran" aria-labelledby="log_pembayaran-tab" role="tabpanel">
                        <div class="row">
                            <div class="col-md-12 col-sm-12 col-lg-12">
                                <div class="divider">
                                    <div class="divider-text">Riwayat Pembayaran</div>
                                    <div class="table-responsive">
                                        <table class="table mb-0">
                                            <thead>
                                                <tr>
                                                    <th scope="col" class="text-nowrap">No</th>
                                                    <th scope="col" class="text-nowrap">Tanggal Bayar</th>
                                                    <th scope="col" class="text-nowrap">Nominal</th>
                                                    <th scope="col" class="text-nowrap">Berita Acara</th>
                                                    <th scope="col" class="text-nowrap">Oleh</th>
                                                    <th scope="col" class="text-nowrap"></th>
                                                </tr>
                                            </thead>
                                            <tbody id="tb-data-log_pembayaran">
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <!-- <div class="col-md-12 col-sm-12 col-lg-12">
                                <div class="divider">
                                    <div class="divider-text">Riwayat Pembayaran Biaya-biaya</div>
                                    <div class="table-responsive">
                                        <table class="table mb-0">
                                            <thead>
                                                <tr>
                                                    <th scope="col" class="text-nowrap">No</th>
                                                    <th scope="col" class="text-nowrap">Tanggal Bayar</th>
                                                    <th scope="col" class="text-nowrap">Nominal</th>
                                                    <th scope="col" class="text-nowrap">Berita Acara</th>
                                                    <th scope="col" class="text-nowrap">Oleh</th>
                                                    <th scope="col" class="text-nowrap"></th>
                                                </tr>
                                            </thead>
                                            <tbody id="tb-data-log_pembayaran_bb">
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div> -->
                        </div>
                    </div>
                    <div class="tab-pane" id="bb" aria-labelledby="bb-tab" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6 col-sm-12 col-lg-6">
                                <div class="divider">
                                    <div class="divider-text">Tagihan Biaya-biaya</div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table mb-0">
                                        <thead>
                                            <tr>
                                                <th scope="col" class="text-nowrap">No</th>
                                                <th scope="col" class="text-nowrap">Berita Acara</th>
                                                <th scope="col" class="text-nowrap">Nominal</th>
                                                <th scope="col" class="text-nowrap">Jatuh Tempo</th>
                                                <th scope="col" class="text-nowrap">Oleh</th>
                                                <th scope="col" class="text-nowrap">Sudah DIbayar </th>
                                            </tr>
                                        </thead>
                                        <tbody id="tb-data-tagihan_bb">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-12 col-lg-3">

                                <div class="divider">
                                    <div class="divider-text">Total Biaya-biaya</div>
                                </div>
                                <div class="form-group">
                                    <label for="bt-total_biaya_bb">Total Biaya-biaya</label>
                                    <input readonly type="text" class="form-control num" id="bt-total_biaya_bb"
                                        name="bt-total_biaya_bb">
                                </div>

                                <hr>
                                <div class="form-group">
                                    <label for="bt-sudah_bayar_bb">Sudah Bayar Biaya-biaya</label>
                                    <input type="text" class="form-control num" readonly id="bt-sudah_bayar_bb"
                                        name="bt-sudah_bayar_bb">
                                </div>
                                <div class="form-group">
                                    <label for="bt-sisa_tagihan_um">Sisa Tagihan Biaya-biaya</label>
                                    <input type="text" class="form-control num" readonly id="bt-sisa_tagihan_bb"
                                        name="bt-sisa_tagihan_bb">
                                </div>
                                <div class="form-group">
                                    <label for="bt-persentase_bayar_tagihan_bb">Persentase</label>
                                    <input type="text" class="form-control" style="text-align:right" readonly
                                        id="bt-persentase_bayar_tagihan_bb" name="bt-persentase_bayar_tagihan_bb">
                                </div>
                                <!-- <a class="btn btn-outline-primary waves-effect col-sm-12" data-toggle="collapse"
                                    href="#collapseExampleBB" role="button" aria-expanded="false"
                                    aria-controls="collapseExampleBB">
                                    Lihat Detail Biaya
                                </a> -->
                                <!-- <div class="collapse" id="collapseExampleBB">
                                    <div class="form-group">
                                        <label class="form-label" for="basic-icon-default-fullname">PPN</label>
                                        <input type="text" class="form-control num bt-fm" id="bt-harga_ppn"
                                            name="bt-harga_ppn" value="" readonly />
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label" for="basic-icon-default-fullname">BPHTB</label>
                                        <input type="text" class="form-control num bt-fm" id="bt-harga_bphtb"
                                            name="bt-harga_bphtb" value="" readonly />
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label" for="basic-icon-default-fullname">Biaya Proses</label>
                                        <input type="text" class="form-control num bt-fm" id="bt-harga_biaya_proses"
                                            name="bt-harga_biaya_proses" value="" readonly />
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label" for="basic-icon-default-fullname">Penambahan
                                            Biaya</label>
                                        <input type="text" class="form-control bt-fm"
                                            id="bt-keterangan_penambahan_biaya" name="bt-keterangan_penambahan_biaya"
                                            value="" readonly />
                                        <input type="text" class="form-control num bt-fm" id="bt-harga_penambahan"
                                            name="bt-harga_penambahan" value="" readonly />
                                    </div>
                                </div> -->
                            </div>
                            <div class="col-md-3 col-sm-12 col-lg-3">
                                <div class="hide_lunas">
                                    <div class="divider">
                                        <div class="divider-text">Pembayaran</div>
                                    </div>
                                    <div class="form-group">
                                        <label for="keterangan_refund">Untuk Pembayaran</label>
                                        <select multiple="multiple" name="bt-for_bb[]" id="bt-for_bb"
                                            class="form-control form-select"></select>
                                    </div>
                                    <div class="form-group">
                                        <label for="berita_acara">Keterangan Pembayaran</label>
                                        <textarea class="form-control" id="bt-berita_acara_bb" name="bt-berita_acara_bb"
                                            rows="3" placeholder="Keterangan"></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="sisa_tagihan">Nominal Pembayaran</label>
                                        <input type="text" class="form-control num" id="bt-bayar_tagihan_bb"
                                            name="bt-bayar_tagihan_bb">
                                    </div>
                                    <div class="form-group">
                                        <label for="tanggal_bayar">Tanggal Pembayaran</label>
                                        <input type="text" id="bt-tanggal_bayar_bb" name="bt-tanggal_bayar_bb"
                                            class="form-control flatpickr-human-friendly" placeholder="-" />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                            <button class="add-form-btn-keuangan btn btn-primary data-submit mr-1"
                                onclick="save_keuangan('bb'); return false;" href="javascript:void(0)">Simpan</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- ################################## Modal Isi Data Konsumen ##########################################-->
<div class="modal fade" id="modal-isi_data_konsumen">
    <div class="modal-dialog modal-dialog-scrollable modal-xl" role="document">
        <form id="fm-idk_keu" class="add-new-record modal-content pt-0" enctype="multipart/form-data"
            autocomplete="off">
            <!-- <button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button> -->
            <div class="modal-header mb-1">
                <h5 class="modal-title" id="exampleModalLabel">Isi Data Konsumen</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body flex-grow-1" style="background-color:#eee">
                <div class="row">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <p class="modal-title label_alamat" id="label_alamat4"></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3" id="div-hargajual">
                        <div class="card">
                            <div class="card-body">
                                <div class="form-group">
                                    <label class="form-label" for="basic-icon-default-fullname">Harga Jual
                                        Pricelist</label>
                                    <input type="text" readonly class="form-control num mk-fm" id="idk-mkdt_hargajual"
                                        name="idk-mkdt_hargajual" value="" />
                                    <span>Harga diinput oleh: <span id="idk-mkdt_hargajual_by"
                                            style="font-weight:bold"></span>
                                        pada: <span id="idk-mkdt_hargajual_tgl" style="font-weight:bold"></span></span>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="col-md-5 row" id="idk-diskresi_st">
                        <div style=" border: 1px solid red; background-color: red; border-radius: 10px 0px 0px 10px; color: white;"
                            class="col-md-6">
                            <div class="form-group">
                                <label class="form-label" for="basic-icon-default-fullname" style="color:white">Diskresi
                                    harga</label>
                                <input type="text" readonly class="form-control num" id="idk-diskresi_harga"
                                    name="mkdt_hargajual" value="" />
                                <span>Diskresi diinput oleh: <span style="font-weight:bold"
                                        id="idk-diskresi_oleh"></span> pada: <span id="idk-diskresi_tgl"
                                        style="font-weight:bold"></span></span>

                            </div>
                        </div>
                        <div class="col-md-6"
                            style="border: 1px solid red; background-color: red; border-radius: 0px 10px 10px 0px; color: white;">
                            <div class="form-group">
                                <label class="form-label" for="basic-icon-default-fullname"
                                    style="color:white">Memo</label>
                                <textarea name="idk-diskresi_memo" readonly id="idk-diskresi_memo" class="form-control"
                                    cols="30" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>
                <div class="card">
                    <div class="card-body pb-0 pt-0">
                        <ul class="nav nav-tabs mb-1 mt-1" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="idk_data_konsumen-tab" data-toggle="tab"
                                    href="#idk_data_konsumen" aria-controls="idk_data_konsumen" role="tab"
                                    aria-selected="true">Data Konsumen</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="idk_biaya-tab" data-toggle="tab" href="#idk_biaya"
                                    aria-controls="idk_biaya" role="tab" aria-selected="true">Biaya</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="idk_tagihan-tab" data-toggle="tab" href="#idk_tagihan"
                                    aria-controls="data_konsumen" role="tab" aria-selected="true">Tagihan</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="idk_arsip-tab" data-toggle="tab" href="#idk_arsip"
                                    aria-controls="idk_arsip" role="tab" aria-selected="true">SPPTB Ditandatangani</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="idk_riwayat-tab" data-toggle="tab" href="#idk_riwayat"
                                    aria-controls="idk_riwayat" role="tab" aria-selected="true">Riwayat Pindah
                                    Kavling/Ganti Nama</a>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="tab-content">
                    <div class="tab-pane show active" id="idk_data_konsumen" aria-labelledby="idk_data_konsumen-tab"
                        role="tabpanel">
                        <input type="hidden" class="form-control id_kavling" name="id_kavling" value="" />
                        <input type="hidden" class="form-control" id="idk-id_mkdt" name="id_mkdt" value="" />
                        <input type="hidden" class="form-control" id="idk-id_konsumen" name="id_konsumen" value="" />

                        <input type="hidden" class="form-control" id="idk-harga_akhir" name="idk-harga_akhir" value=""
                            readonly />
                        <input type="hidden" class="form-control" id="idk-hargajual" name="idk-hargajual" value=""
                            readonly />
                        <input type="hidden" class="form-control" id="idk-kpr" name="idk-kpr" value="" readonly />
                        <input type="hidden" class="form-control" id="idk-uang_muka" name="mkdt-uang_muka" value=""
                            readonly />
                        <input type="hidden" class="form-control" id="idk-bphtb" name="idk-bphtb" value="" readonly />
                        <input type="hidden" class="form-control" id="idk-biaya_adm" name="idk-biaya_adm" value=""
                            readonly />
                        <input type="hidden" class="form-control" id="idk-biaya_proses" name="idk-biaya_proses" value=""
                            readonly />

                        <input type="hidden" class="form-control" id="idk_data_baru" name="mkdt_data_baru" value="" />

                        <div class="row">
                            <div class="col-sm-12 col-md-4 col-lg-4">
                                <div class="refresh_fmmkdt_div">
                                    <button id="refresh-btn-idk_keu" type="button"
                                        class="btn btn-outline-primary btn-block waves-effect">Tambah Konsumen
                                        Baru</button>
                                </div>
                                <div class="delete_kons_div">
                                    <button id="delete-btn-idk_keu" type="button"
                                        class="btn btn-outline-danger btn-block waves-effect"
                                        onclick="delete_kons(false)">Hapus Konsumen</button>
                                </div>
                            </div>
                        </div>
                        <div class="row">

                            <div class="col-sm-12 col-md-3 col-lg-3">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Status Kavling</h5>
                                    </div>
                                    <div class="card-body">
                                        <!-- 
                                        <div class="divider">
                                            <div class="divider-text"></div>
                                        </div> -->
                                        <div class="form-group">
                                            <label for="status_kavling">Status Kavling</label>
                                            <select required class="form-control" id="idk-status_mkdt"
                                                name="dt-status_mkdt">
                                                <option value="">-</option>
                                                <option value="Booking">Booking</option>
                                                <option value="Akad">Akad</option>
                                                <option value="Batal">Batal</option>
                                            </select>
                                        </div>
                                        <div id="idk-show_keterangan_batal" class="hidden">
                                            <div class="form-group">
                                                <label for="keterangan_batal">Keterangan Batal</label>
                                                <textarea class="form-control" id="idk-keterangan_batal"
                                                    name="dt-keterangan_batal" rows="3"
                                                    placeholder="Keterangan"></textarea>
                                            </div>
                                            <div class="form-group">
                                                <label for="harga_jual">Nominal Pengembalian Dana</label>
                                                <input type="text" class="form-control num" id="idk-refund"
                                                    name="dt-refund">
                                            </div>

                                        </div>
                                    </div>
                                </div>
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Data Konsumen</h5>
                                    </div>
                                    <div class="card-body">

                                        <div class="form-group">
                                            <label for="no_spptb">No SPPTB</label>
                                            <input type="text" class="form-control gn" id="idk-no_spptb"
                                                name="no_spptb">
                                        </div>
                                        <div class="form-group">
                                            <label for="nama_konsumen">Nama Konsumen</label>
                                            <input type="text" class="form-control gn" id="idk-nama_konsumen" required
                                                name="nama_konsumen">
                                        </div>
                                        <div class="form-group">
                                            <label for="alamat_konsumen">Alamat Konsumen</label>
                                            <input type="text" class="form-control gn" id="idk-alamat_konsumen"
                                                name="alamat_konsumen">
                                        </div>
                                        <div class="form-group">
                                            <label for="nik_konsumen">NIK</label>
                                            <input type="text" class="form-control gn" id="idk-nik_konsumen"
                                                name="nik_konsumen">
                                        </div>
                                        <div class="form-group">
                                            <label for="npwp_konsumen">NPWP</label>
                                            <input type="text" class="form-control gn" id="idk-npwp_konsumen"
                                                name="npwp_konsumen">
                                        </div>
                                        <div class="form-group">
                                            <label for="hp_konsumen">Kontak Konsumen</label>
                                            <input type="text" class="form-control gn" id="idk-hp_konsumen"
                                                name="hp_konsumen">
                                        </div>
                                        <div class="form-group">
                                            <label for="hp_konsumen">Email Konsumen</label>
                                            <input type="text" class="form-control gn" id="idk-email_konsumen"
                                                name="email_konsumen">
                                        </div>
                                        <div class="form-group hidden">
                                            <label for="status_kavling">Status Konsumen</label>
                                            <select class="form-control" id="idk-status_konsumen"
                                                name="status_konsumen">
                                                <option value="">-</option>
                                                <option value="Umum">Umum</option>
                                                <option value="TWP">TWP</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-3 col-lg-3">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Data Pasangan</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="status_kavling">Status Pernikahan</label>
                                            <select class="form-control gn" id="idk-status_pernikahan"
                                                name="status_pernikahan">
                                                <option value="Belum Kawin">Belum Kawin</option>
                                                <option value="Kawin">Kawin</option>
                                                <option value="Cerai Mati">Cerai Mati</option>
                                                <option value="Cerai Hidup">Cerai Hidup</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="nama_pasangan">Nama Pasangan</label>
                                            <input type="text" class="form-control gn" id="idk-nama_pasangan"
                                                name="nama_pasangan">
                                        </div>
                                        <div class="form-group">
                                            <label for="hp_konsumen">NIK Pasangan</label>
                                            <input type="text" class="form-control gn" id="idk-nik_pasangan"
                                                name="nik_pasangan">
                                        </div>

                                    </div>

                                </div>
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Data Instansi</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="nama_instansi">Nama Instansi</label>
                                            <input type="text" class="form-control gn" id="idk-nama_instansi"
                                                name="nama_instansi">
                                        </div>
                                        <div class="form-group">
                                            <label for="alamat_instansi">Alamat Instansi</label>
                                            <input type="text" class="form-control gn" id="idk-alamat_instansi"
                                                name="alamat_instansi">
                                        </div>
                                        <div class="form-group">
                                            <label for="tel_instansi">Telepon Instansi</label>
                                            <input type="text" class="form-control gn" id="idk-tel_instansi"
                                                name="tel_instansi">
                                        </div>
                                    </div>
                                </div>



                            </div>
                            <div class="col-sm-12 col-md-3 col-lg-3">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Sales</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="alamat_instansi">Sales</label>
                                            <input type="text" class="form-control" id="idk-sales" name="sales">
                                        </div>
                                    </div>
                                </div>
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Promo/Bonus/Hadiah</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="alamat_instansi">Promo/Bonus/Hadiah</label>
                                            <input type="text" class="form-control" id="idk-promo" name="promo">
                                        </div>
                                    </div>
                                </div>
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">TUNAI/KPR</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="is_kpr">Tunai/KPR</label>
                                            <select required class="form-control" id="idk-is_kpr" name="is_kpr"
                                                onchange="sum_mktotal()">
                                                <option value="0">TUNAI/CASH KERAS</option>
                                                <option value="2">TUNAI/CASH BERTAHAP</option>
                                                <option value="1">KPR</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="is_subsidi">Subsidi/Non-Subsidi</label>
                                            <select required class="form-control" id="idk-is_subsidi" name="is_subsidi">
                                                <option value="0">Non-Subsidi</option>
                                                <option value="1">Subsidi</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="idk-jenis_subsidi">Jenis Subsidi</label>
                                            <input type="text" placeholder="FLPP/TAPERA/LAIN-LAIN" class="form-control"
                                                id="idk-jenis_subsidi" name="jenis_subsidi">
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div class="col-sm-12 col-md-3 col-lg-3 text-center">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">File Upload</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label>KTP</label>
                                            <div class="custom-file">
                                                <input type="file" class="custom-file-input" accept="image/*"
                                                    name="idk-file_ktp" id="idk-file_ktp" />
                                                <label class="custom-file-label" id="label-idk-file_ktp"
                                                    for="label-idk-file_ktp">Upload File KTP</label>
                                                <div id="list_upload_komplain_sales"></div>

                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label>NPWP</label>
                                            <div class="custom-file">
                                                <input type="file" class="custom-file-input" accept="image/*"
                                                    name="idk-file_npwp" id="idk-file_npwp" />
                                                <label class="custom-file-label" id="label-idk-file_npwp"
                                                    for="label-idk-file_npwp">Upload File NPWP</label>
                                                <div id="list_upload_komplain_sales"></div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label>Data Diri</label>
                                            <div class="custom-file">
                                                <input type="file" class="custom-file-input" accept="application/pdf"
                                                    name="file_data_diri" id="idk-file_data_diri" />
                                                <label class="custom-file-label" id="label-file_data_diri"
                                                    for="label-file_data_diri">Upload
                                                    Data Diri</label>
                                                <div id="list-upload_file_data_diri"></div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="divider">
                                    <div class="divider-text">KTP</div>
                                </div>
                                <a href="" id="idk-file_ktp-here" target="_blank"
                                    class="w-100 btn btn-outline-primary">klik
                                    untuk melihat file</a>

                                <div class="divider">
                                    <div class="divider-text">NPWP</div>
                                </div>
                                <a href="" id="idk-file_npwp-here" target="_blank"
                                    class=" btn btn-outline-primary w-100">klik untuk melihat file</a>
                                <div class="divider">
                                    <div class="divider-text">Data Diri</div>
                                </div>
                                <a href="" id="idk-file_data_diri-here" class="btn btn-outline-primary w-100"
                                    target="_blank">klik untuk melihat file</a>
                            </div>

                        </div>
                    </div>
                    <div class="tab-pane" id="idk_biaya" aria-labelledby="idk_biaya-tab" role="tabpanel">
                        <div class="row">
                            <div class="col-sm-12 col-md-3 col-lg-3">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Harga Jual</h5>
                                    </div>
                                    <div class="card-body">
                                        <!-- <input type="hidden" name="mk-id_mkdt" id="mk-id_mkdt"> -->
                                        <!-- <div class="form-group">
                                    <label class="form-label" for="basic-icon-default-fullname">No Tipe</label>
                                    <input readonly class="form-control mk-fm" id="mk-no_tipe" name="mk-text_hargajual" value="">
                                </div> -->
                                        <div class="form-group">
                                            <label class="form-label" for="basic-icon-default-fullname">Tanggal
                                                PriceList</label>
                                            <input type="text"
                                                class="form-control text-right mk-fm flatpickr-human-friendly"
                                                id="mk-tgl_harga" name="mk-tgl_harga" value="" readonly />
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="basic-icon-default-fullname">Harga
                                                Jual</label>
                                            <input type="text" class="form-control num mk-fm" id="mk-hargajual"
                                                name="mk-hargajual" value="" />
                                        </div>
                                        <div class="form-group" id="hjdis">
                                            <label class="form-label" for="basic-icon-default-fullname">Diskon Harga
                                                Jual</label>
                                            <input type="text" class="form-control num mk-fm" id="mk-diskon_harga_jual"
                                                name="mk-diskon_harga_jual" value="" />
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="basic-icon-default-fullname">Harga Jual
                                                Net</label>
                                            <input type="text" class="form-control num mk-fm" id="mk-hargajual_net"
                                                name="mk-hargajual_net" value="" />
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="basic-icon-default-fullname">KPR</label>
                                            <input type="text" class="form-control num mk-fm" id="mk-kpr" name="mk-kpr"
                                                value="" />
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="basic-icon-default-fullname">Uang
                                                Muka</label>
                                            <input type="text" class="form-control num mk-fm" id="mk-uang_muka"
                                                name="mk-uang_muka" value="" />
                                        </div>
                                        <div class="form-group" id="umdis">
                                            <label class="form-label" for="basic-icon-default-fullname">Diskon Uang
                                                Muka</label>
                                            <input type="text" class="form-control num mk-fm" id="mk-diskon_uang_muka"
                                                name="mk-diskon_uang_muka" value="" />
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="basic-icon-default-fullname">Biaya
                                                Adm</label>
                                            <input type="text" class="form-control num mk-fm" id="mk-biaya_adm"
                                                name="mk-biaya_adm" value="" />
                                        </div>
                                        <div class="form-group">
                                            <label for="total_biaya2">PPN</label>
                                            <input type="text" class="form-control num mk-fm totalbb" id="mk-ppn"
                                                name="mk-ppn">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="basic-icon-default-fullname">BPHTB</label>
                                            <input type="text" class="form-control num mk-fm totalbb" id="mk-bphtb"
                                                name="mk-bphtb" value="" />
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="basic-icon-default-fullname">Biaya
                                                Proses</label>
                                            <input type="text" class="form-control num mk-fm totalbb"
                                                id="mk-biaya_proses" name="mk-biaya_proses" value="" />
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="basic-icon-default-fullname">ROW</label>
                                            <input type="text" class="form-control num mk-fm" id="mk-row" name="mk-row"
                                                value="" />
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="basic-icon-default-fullname">Tipe</label>
                                            <input type="text" class="form-control mk-fm text-right" id="mk-tipe"
                                                name="mk-tipe" value="" />
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="basic-icon-default-fullname">LB</label>
                                            <input type="text" class="form-control num mk-fm" id="mk-lb" name="mk-lb"
                                                value="" />
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="basic-icon-default-fullname">LT</label>
                                            <input type="text" class="form-control num mk-fm" id="mk-lt" name="mk-lt"
                                                value="" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-3 col-lg-3">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Booking</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="booking_tgl">Tanggal Booking</label>
                                            <input type="text" id="idk-booking_tgl" name="dt-booking_tgl"
                                                class="form-control flatpickr-human-friendly" placeholder="-" />
                                        </div>
                                        <div class="form-group">
                                            <label for="harga_jual">Booking Fee</label>
                                            <input type="text" class="form-control num" id="idk-booking_fee"
                                                name="dt-booking_fee">
                                        </div>
                                    </div>
                                </div>
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">KPR</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="total_biaya2">KPR Disetujui</label>
                                            <input readonly type="text" class="form-control num mk-fm"
                                                id="mk-harga_kpr_acc" name="mk-harga_kpr_acc">
                                        </div>
                                        <div class="form-group">
                                            <label for="total_biaya2">Turun KPR</label>
                                            <input readonly type="text" class="form-control num mk-fm"
                                                id="mk-harga_penambahan_um" name="mk-harga_penambahan_um">
                                        </div>
                                    </div>
                                </div>
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Penambahan Biaya</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="total_biaya2">Biaya Kavling Strategis</label>
                                            <input type="text" class="form-control num mk-fm" id="mk-harga_penambahan"
                                                name="mk-harga_penambahan">
                                        </div>
                                        <div class="form-group">
                                            <label for="total_biaya2">Biaya Kelebihan Tanah</label>
                                            <input type="text" class="form-control num mk-fm"
                                                id="mk-harga_penambahan_tanah" name="mk-harga_penambahan_tanah">
                                        </div>
                                        <div class="form-group hidden">
                                            <label for="total_biaya2">Keterangan Penambahan Biaya</label>
                                            <textarea name="mk-keterangan_harga_penambahan"
                                                id="mk-keterangan_harga_penambahan" class="form-control mk-fm" cols="30"
                                                rows="2"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <div class="col-sm-12 col-md-3 col-lg-3">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Rincian Biaya</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <textarea class="form-control" rows="7" id="idk-rincian"
                                                name="rincian"></textarea>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div class="col-sm-12 col-md-3 col-lg-3">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Total Biaya</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label>Total Uang Muka + Biaya ADM</label>
                                            <input readonly type="text" class="form-control num tum" id="mk-tum"
                                                name="mk-tum">
                                        </div>
                                        <div class="form-group">
                                            <label>Total Biaya-Biaya</label>
                                            <input readonly type="text" class="form-control num tbb" id="mk-tbb"
                                                name="mk-tbb">
                                        </div>
                                    </div>
                                </div>

                            </div>


                        </div>

                    </div>
                    <div class="tab-pane" id="idk_tagihan" aria-labelledby="idk_tagihan-tab" role="tabpanel">
                        <div class="row">
                            <div class="col-sm-12 col-md-6 col-lg-6">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table id="list_kendaraan" class="table">
                                                <thead class="thead-dark">
                                                    <tr>
                                                        <th>Keterangan</th>
                                                        <th>Jatuh Tempo</th>
                                                        <th>Nominal</th>
                                                        <th></th>
                                                    </tr>
                                                </thead>
                                                <tbody id="list_cicilan_here">
                                                    <tr>
                                                        <td colspan="5" class="text-center">Tidak Ada Data</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            <!-- <button class="btn btn-sm btn-primary" onclick="addRow()">Tambah Baris</button> -->

                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div class="col-sm-12 col-md-6 col-lg-6">
                                <div class="row">
                                    <div class="col-sm-12 col-md-6 col-lg-6">
                                        <div class="card">
                                            <div class="card-header">
                                                <h5 class="card-title">Tagihan Uang Muka + Biaya Lain</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="form-group">
                                                    <label for="mk-total_um">Total Uang Muka</label>
                                                    <input readonly type="text" class="form-control num tum"
                                                        id="mk-total_um" name="mk-total_um">
                                                </div>
                                                <!-- <div class="form-group">
                                        <label for="total_cicilan">Total Cicilan UM</label> -->
                                                <input readonly type="hidden" class="form-control num"
                                                    id="total_cicilan_um" name="total_cicilan_um">
                                                <!-- </div> -->
                                                <input name="id_list_keu" id="id_list_keu" class="form-control"
                                                    type="hidden">
                                                <input name="id_keuangan" id="id_keuangan" class="form-control"
                                                    type="hidden">
                                                <div class="form-group">
                                                    <label>Untuk Tagihan</label>
                                                    <select class="form-control" required name="berita_acara"
                                                        id="berita_acara">
                                                        <option value="Uang Muka">Uang Muka</option>
                                                        <option value="Biaya Administrasi">Biaya Administrasi</option>
                                                        <option value="Turun KPR">Turun KPR</option>
                                                        <option value="Biaya Kavling Strategis">Biaya Kavling Strategis
                                                        </option>
                                                        <option value="Biaya Kelebihan Tanah">Biaya Kelebihan Tanah
                                                        </option>
                                                    </select>
                                                    <!-- <input required name="berita_acara" id="berita_acara"
                                                        class="form-control" type="text"> -->
                                                    <span class="help-block"></span>
                                                </div>
                                                <div class="form-group">
                                                    <label>Nominal</label>
                                                    <input required name="nominal" id="nominal"
                                                        onchange="sum_tg(this.value)" class="form-control num tg"
                                                        type="text">
                                                    <span class="help-block"></span>
                                                </div>
                                                <div class="form-group">
                                                    <label>Tanggal Jatuh Tempo</label>
                                                    <input required name="jatuh_tempo_tgl" id="jatuh_tempo_tgl"
                                                        class="form-control flatpickr-human-friendly" type="date">
                                                    <span class="help-block"></span>
                                                </div>
                                                <div id="cicilan_belong_here"></div>
                                                <button id="tambah_list" type="button"
                                                    class="btn btn-outline-primary btn-block waves-effect"
                                                    onclick="tambah_()">+
                                                    Tagihan Uang Muka</button>
                                                <!-- <button id="hapus_list" type="button" class="btn btn-outline-danger btn-block waves-effect" onclick="hapus()">+ Hapus List</button> -->
                                            </div>
                                        </div>

                                    </div>
                                    <div class="col-sm-12 col-md-6 col-lg-6 ">
                                        <div class="card">
                                            <div class="card-header">
                                                <h5 class="card-title">Tagihan Biaya-biaya</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="form-group">
                                                    <label for="mk-total_bb">Total Biaya-biaya</label>
                                                    <input readonly type="text" class="form-control num tbb"
                                                        id="mk-total_bb" name="mk-total_bb">
                                                </div>
                                                <!-- <div class="form-group">
                                        <label for="total_cicilan">Total Cicilan UM</label> -->
                                                <input readonly type="hidden" class="form-control num"
                                                    id="total_cicilan_bb" name="total_cicilan_bb">
                                                <!-- </div> -->
                                                <input name="id_list_keu_bb" id="id_list_keu_bb" class="form-control"
                                                    type="hidden">
                                                <input name="id_keuangan_bb" id="id_keuangan_bb" class="form-control"
                                                    type="hidden">
                                                <div class="form-group">
                                                    <label>Untuk Tagihan</label>
                                                    <select class="form-control" required name="berita_acara_bb"
                                                        id="berita_acara_bb">
                                                        <option value="PPN">PPN</option>
                                                        <option value="BPHTB">BPHTB</option>
                                                        <option value="Biaya Proses">Biaya Proses</option>
                                                    </select>

                                                    <!-- <input required name="berita_acara_bb" id="berita_acara_bb"
                                                        class="form-control" type="text"> -->
                                                    <span class="help-block"></span>
                                                </div>
                                                <div class="form-group">
                                                    <label>Nominal</label>
                                                    <input required name="nominal_bb" id="nominal_bb"
                                                        onchange="sum_tg(this.value, '_bb')" class="form-control num tg"
                                                        type="text">
                                                    <span class="help-block"></span>
                                                </div>
                                                <div class="form-group">
                                                    <label>Tanggal Jatuh Tempo</label>
                                                    <input required name="jatuh_tempo_tgl_bb" id="jatuh_tempo_tgl_bb"
                                                        class="form-control flatpickr-human-friendly" type="date">
                                                    <span class="help-block"></span>
                                                </div>
                                                <button id="tambah_list_bb" type="button"
                                                    class="btn btn-outline-primary btn-block waves-effect"
                                                    onclick="tambah_('_bb')">+ Tagihan Biaya-biaya</button>
                                                <!-- <button id="hapus_list" type="button" class="btn btn-outline-danger btn-block waves-effect" onclick="hapus()">+ Hapus List</button> -->
                                            </div>
                                        </div>

                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>
                    <div class="tab-pane" id="idk_arsip" aria-labelledby="idk_arsip-tab" role="tabpanel">
                        <div class="row">
                            <div class="col-sm-12 col-md-6 col-lg-6">
                                <div class="card file-container">
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label>File SPPTB Yang Sudah Ditandatangani</label>
                                            <div class="custom-file">
                                                <input type="file" class="custom-file-input" accept="application/pdf"
                                                    name="file_spptb" id="idk_file_spptb" onchange="" />
                                                <label class="custom-file-label" id="label-idk_file_spptb"
                                                    for="idk_file_spptb">Upload SPPTB yang sudah ditandatangani</label>
                                            </div>
                                            <div id="list-idk_file_spptb" style="">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label>Lampiran Surat Kuasa SPPTB</label>
                                            <div class="custom-file">
                                                <input type="file" class="custom-file-input" accept="application/pdf"
                                                    name="file_surat_kuasa" id="idk_file_surat_kuasa" onchange="" />
                                                <label class="custom-file-label" id="label-idk_file_surat_kuasa"
                                                    for="idk_file_surat_kuasa">Upload Lampiran Surat Kuasa SPPTB</label>
                                            </div>
                                            <div id="list-idk_lampiran" style="">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-6 col-lg-6">
                                <div class="card file-container">
                                    <div class="card-head">
                                        <ul class="nav nav-tabs mb-1 mt-1" role="tablist">
                                            <li class="nav-item">
                                                <a class="nav-link active" id="spptb_ttd-tab" data-toggle="tab"
                                                    href="#spptb_ttd" aria-controls="spptb_ttd" role="tab"
                                                    aria-selected="true">SPPTB Sudah Ditandatangan</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" id="spptb_riwayat-tab" data-toggle="tab"
                                                    href="#spptb_riwayat" aria-controls="spptb_riwayat" role="tab"
                                                    aria-selected="true">Riwayat Upload SPPTB</a>
                                            </li>
                                    </div>
                                    <div class="card-body">
                                        <div class="tab-content">
                                            <div class="tab-pane show active" id="spptb_ttd"
                                                aria-labelledby="spptb_ttd-tab" role="tabpanel">
                                                <div id="spptb_ttd_file"></div>
                                            </div>
                                            <div class="tab-pane" id="spptb_riwayat" aria-labelledby="spptb_riwayat-tab"
                                                role="tabpanel">
                                                <div class="table-responsive">
                                                    <table class="table mb-0">
                                                        <thead>
                                                            <tr>
                                                                <th>No</th>
                                                                <th>File</th>
                                                                <th>Oleh</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="spptb_ttd_file-here"></tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane" id="idk_riwayat" aria-labelledby="idk_riwayat-tab" role="tabpanel">
                        <div class="row">
                            <div class="col-sm-12 col-md-6 col-lg-6">
                                <div class="card file-container">
                                    <div class="card-body">
                                        <button class="btn btn-outline-primary" id="btn-ganti_nama"
                                            onclick="ganti_nama()">Klik Untuk Ganti Nama Konsumen</button>
                                        <button class="btn btn-outline-warning" id="btn-refresh-ganti_nama"
                                            onclick="getRiwayatGantinama()">Muat Ulang Diwayat</button>
                                        <div class="divider">
                                            <div class="divider-text">Riwayat Ganti Nama </div>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>No</th>
                                                        <th>File</th>
                                                        <th>Oleh</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="riwayat_ganti_nama-here"></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-6 col-lg-6">
                                <div class="card file-container">
                                    <div class="card-body">
                                        <button class="btn btn-outline-primary" id="btn-ganti_kavling"
                                            onclick="ganti_kavling()">Klik Untuk Ganti Kavling</button>
                                        <button class="btn btn-outline-warning" id="btn-refresh-ganti_kavling"
                                            onclick="getRiwayatGantiKavling()">Muat Ulang Data </button>
                                        <div class="divider">
                                            <div class="divider-text">Riwayat Ganti Nama </div>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>No</th>
                                                        <th>File</th>
                                                        <th>Oleh</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="ganti_kavling-here"></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <br>
            </div>
            <div class="modal-footer">
                <a href="" id="btn-print_spptb" target="_blank" class="btn btn-outline-success">Print SPPTB</a>

                <button id="add-form-btn-idk_keu" class="btn btn-primary data-submit mr-1"
                    onclick="simpan_dt_konsumen_keuangan(this)" href="javascript:void(0)">Simpan</button>
                <button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
            </div>
    </div>
    </form>
</div>

<!-- ################################## Modal Tagihan ##########################################-->
<div class="modal fade text-left" id="print_tagihan_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel17"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Print Tagihan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="col-xl-12 col-md-12 col-12">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="form_list_inv-tab" data-toggle="tab" href="#form_list_inv"
                            aria-controls="form_list_inv" role="tab" aria-selected="true">List Invoice</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link " id="form_add_inv-tab" data-toggle="tab" href="#form_add_inv"
                            aria-controls="form_add_inv" role="tab" aria-selected="true">Tambah Invoice</a>
                    </li>
                </ul>
                <div class="tab-content">

                    <div class="tab-pane active" id="form_list_inv" aria-labelledby="form_list_inv-tab" role="tabpanel">
                        <div class="card invoice-preview-card">
                            <div class="card-body invoice-padding pb-0">
                                <div class="row">
                                    <div class="table-responsive">
                                        <table class="table mb-0" id="tbl-tagihan">
                                            <thead>
                                                <tr>
                                                    <th>No Invoice</th>
                                                    <th>Tanggal Terbit</th>
                                                    <th>Tanggal Kadaluarsa</th>
                                                    <th>Oleh</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody id="list_inv-here"></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane " id="form_add_inv" aria-labelledby="form_add_inv-tab" role="tabpanel">
                        <div class="card invoice-preview-card">
                            <div class="card-body invoice-padding pb-0">
                                <div
                                    class="d-flex justify-content-between flex-md-row flex-column invoice-spacing mt-0">
                                    <div class="col-md-4">
                                        <select class="select2 custom-select " id="kopsurat" name="kopsurat"></select>
                                        <!-- <div class="logo-wrapper" id="pc-logo_perusahaan"></div>
                                        <p class="card-text mb-25" id="pc-alamat_perusahaan">Office 149, 450 South Brand Brooklyn</p>
                                        <p class="card-text mb-25" id="pc-website_perusahaan">San Diego County, CA 91905, USA</p>
                                        <p class="card-text mb-0" id="pc-kontak_perusahaan">+1 (123) 456 7891, +44 (876) 543 2198</p> -->

                                    </div>
                                    <div class="invoice-number-date mt-md-0 mt-4">
                                        <div class="">
                                            <h4 class="invoice-title">No Invoice</h4>
                                            <div class="input-group input-group-merge invoice-edit-input-group">
                                                <input id="no_sruat" name="no_sruat" type="text"
                                                    class="form-control invoice-edit-input" placeholder="53634">
                                            </div>
                                        </div>
                                        <div class="">
                                            <span class="title">Tanggal:</span>
                                            <input type="text" id="tanggal_surat_tagihan" name="tanggal_surat_tagihan"
                                                class="form-control flatpickr-human-friendly" placeholder="-">
                                        </div>
                                        <div class="">
                                            <span class="title">Tenggat Waktu:</span>
                                            <input type="text" id="pt-tanggal_jatuh_tempo" name="pt-tanggal_jatuh_tempo"
                                                class="form-control flatpickr-human-friendly" placeholder="-">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Header starts -->
                            <!-- <div class="card-body invoice-padding pb-0">
                        <div class="form-group">
                            <label for="no_sruat">No Surat</label>
                            <input type="text" class="form-control" id="no_sruat" name="no_sruat">
                        </div>
                        <div class="form-group">
                            <label for="tanggal_surat_tagihan">Tanggal Surat Tagihan</label>
                            <input type="text" id="tanggal_surat_tagihan" name="tanggal_surat_tagihan" class="form-control flatpickr-human-friendly" placeholder="-" />
                        </div>
                    </div> -->
                            <!-- Header ends -->

                            <hr class="invoice-spacing" />

                            <!-- Address and Contact starts -->
                            <div class="card-body invoice-padding pt-0">
                                <div class="invoice-spacing  row">
                                    <div class="col-xl-6 p-0">
                                        <h6 class="mb-2">Ditagihkan Ke:</h6>
                                        <h6 class="hidden" id="pt_id_konsumen"></h6>
                                        <h6 class="mb-25" id="pt_detail_konsumen"></h6>
                                        <!-- <p class="card-text mb-25" id="pt_hp_konsumen"></p> -->
                                    </div>
                                    <div class="col-xl-6 p-0">
                                        <h6 class="mb-2">Perumahan</h6>
                                        <h6 class="hidden" id="pt_id_kavling"></h6>
                                        <h6 class="hidden" id="pt_id_mkdt"></h6>
                                        <h6 class="mb-25" id="pt_detail_kavling"></h6>
                                        <!-- <p class="card-text mb-25" id="pt_hp_konsumen"></p> -->
                                    </div>
                                </div>
                            </div>
                            <!-- Address and Contact ends -->

                            <!-- Product Details starts -->
                            <div class="card-body invoice-padding invoice-product-details">
                                <form class="source-item">
                                    <div data-repeater-list="group-a">
                                        <div class="repeater-wrapper" data-repeater-item>
                                            <div class="row">
                                                <div class="table-responsive">
                                                    <table class="table mb-0" id="tbl-tagihan">
                                                        <thead>
                                                            <tr>
                                                                <th scope="col" class="text-nowrap">No</th>
                                                                <th scope="col" class="text-nowrap">Berita Acara</th>
                                                                <th scope="col" class="text-nowrap">Jatuh Tempo</th>
                                                                <!-- <th scope="col" class="text-nowrap">Sudah Dibayar</th> -->
                                                                <th scope="col" class="text-nowrap">Nominal</th>
                                                                <!-- <th scope="col" class="text-nowrap">Masukan Dalam Surat</th> -->
                                                            </tr>
                                                        </thead>
                                                        <tbody id="tb-print-data-tagihan">
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <!-- Product Details ends -->
                            <hr class="invoice-spacing mt-0" />

                            <div class="card-body invoice-padding py-0">
                                <!-- Invoice Note starts -->
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group mb-2">
                                            <label for="note" class="form-label font-weight-bold">Syarat &
                                                Ketentuan:</label>
                                            <textarea class="form-control" rows="5"
                                                id="snk"><ol><li><span style="font-size: 1rem; letter-spacing: 0.01rem;">Lakukan pembayaran sebelum tanggal jatuh tempo untuk menghindari denda&nbsp;</span></li><li><span style="font-size: 1rem; letter-spacing: 0.01rem;">Pembayaran yang sah hanya melalui transfer ke rekening atas nama <br><b>PT. Sanggarindah Karya Sentosa</b> <b>Raya</b> BCA KC Setiabudi - Bandung, Nomor Rekening :<b>2337 887 887</b>&nbsp;</span></li><li>Konfirmasi pembayaran ke bagian keuangan kami dan lampirkan bukti transfer.</li></ol></textarea>
                                        </div>
                                    </div>
                                </div>
                                <!-- Invoice Note ends -->

                            </div>
                            <div class="modal-footer">
                                <button id="form_add_inv-btn" class="btn btn-primary data-submit mr-1"
                                    onclick="save_inv()" href="javascript:void(0)">Simpan Invoice</button>
                                <button type="reset" class="btn btn-outline-secondary"
                                    data-dismiss="modal">Batal</button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- ################################## Dana Akad ##########################################-->
<div class="modal fade text-left" id="dana_akad_modal" tabindex="-1" role="dialog" aria-labelledby="dana_akad_modal"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <form id="fm-dana_akad" class="add-new-record modal-content pt-0" autocomplete="off">
            <div class="modal-content d-flex flex-column" style="height: 95vh">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Dana Akad</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body flex-grow-1 overflow-auto" style="background-color:#eee">
                    <div class="card">
                        <div class="card-body">
                            <p class="modal-title label_konsumen" id="label_konsumen"></p>
                            <p class="modal-title label_alamat" id="label_alamat3"></p>
                        </div>
                    </div>

                    <!-- <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="da-tab_hasil_akad-tab" data-toggle="tab"
                                href="#da-tab_hasil_akad" aria-controls="home" role="tab" aria-selected="true">Hasil
                                Akad</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="da-tab_pencairan_hasil_akad-tab" data-toggle="tab"
                                href="#da-tab_pencairan_hasil_akad" aria-controls="home" role="tab"
                                aria-selected="true">Pencairan Hasil Akad</a>
                        </li>
                    </ul>
                    <div class="tab-content"> -->
                    <!-- <div class="tab-pane active" id="da-tab_hasil_akad" aria-labelledby="da-hasil_akad-tab"
                            role="tabpanel"> -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    Dana Akad
                                </div>
                                <div class="card-body">
                                    <input type="hidden" class="form-control" id="da-id_mkdt" name="id_mkdt" value="" />
                                    <input type="hidden" class="form-control" id="da-id_kavling" name="id_kavling"
                                        value="" />
                                    <div class="form-group">
                                        <label for="nominal_dana_akad">KPR Acc</label>
                                        <input type="text" id="da-kpr_acc" name="da-kpr_acc" readonly
                                            class="form-control num" />
                                    </div>
                                    <div class="form-group">
                                        <label for="nominal_dana_akad">Hasil Akad</label>
                                        <input type="text" value="" id="da-hasil_akad" name="hasil_akad" readonly
                                            class="form-control num" />
                                    </div>
                                    <div class="form-group">
                                        <label for="nominal_dana_akad">Total Dana Jaminan</label>
                                        <input type="text" value="" id="da-total_dajam" name="total_dajam" readonly
                                            class="form-control num" />
                                    </div>
                                    <hr>
                                    <div class="form-group">
                                        <div class="custom-control custom-switch custom-control-inline">
                                            <input type="checkbox" class="custom-control-input cbp" id="da-dajam_selesai" name="dajam_selesai"
                                                value="1" />
                                            <label class="custom-control-label" for="da-dajam_selesai">Tandai Sudah Selesai</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    Dana Jaminan dan Pencairan
                                </div>
                                <div class="card-body">
                                    <div id="da-jaminan_here"></div>
                                </div>
                            </div>
                        </div>

                        <!-- </div>
                        <div class="tab-pane" id="da-tab_pencairan_hasil_akad"
                            aria-labelledby="da-tab_pencairan_hasil_akad-tab" role="tabpanel"> -->
                        <!-- <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    Pencairan Dana Jaminan
                                </div>
                                <div class="card-body">
                                    <div id="da-cair_jaminan_here"></div>
                                </div>
                            </div>
                        </div> -->

                    </div>
                    <!-- </div> -->
                    <!-- </div> -->
                </div>
                <div class="modal-footer">
                    <button id="add-form-btn-dana_akad" class="btn btn-primary data-submit mr-1"
                        onclick="save_dana_akad(); return false;" href="javascript:void(0)">Simpan</button>
                    <button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                </div>

            </div>
        </form>
    </div>

</div>

<!-- ################################## isi_cashout ##########################################-->
<div class="modal fade text-left" id="modal-cashout-keu" tabindex="-1" role="dialog" aria-labelledby="dana_akad_modal"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg" role="document">
        <form id="fm-cashout-keu" class="" autocomplete="off">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Cash Out</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body flex-grow-1" style="background-color:#eee">
                    <div class="card">
                        <div class="card-body">
                            <p class="modal-title label_konsumen" id="label_konsumen"></p>
                            <p class="modal-title label_alamat" id="label_alamat3"></p>
                        </div>
                    </div>
                    <input type="hidden" class="form-control" id="cashout-id_kavling" name="id_kavling">

                    <div id="div-cashout-here" class="row">
                    </div>



                </div>
                <div class="modal-footer">
                    <button id="add-form-btn-cashout" class="btn btn-primary data-submit mr-1"
                        onclick="save_cashout(); return false;" href="javascript:void(0)">Simpan</button>
                    <button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                </div>

            </div>
        </form>
    </div>

</div>

<script>
    function ganti_kavling() {
        if ($("#spptb_ttd_file").html() == 'Tidak ada data') {
            return swal('error', 'Kamu harus mengunggah file SPPTB yang sudah ditandatangani')
        }
        Swal.fire({
            title: 'Apakah anda yakin akan memindahkan kavling',
            text: "Setelah menekan tombol 'Ya!', pilih salah satu kavling dipasarkan.",
            // type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya!',
            confirmButtonClass: 'btn btn-primary',
            cancelButtonClass: 'btn btn-danger ml-1',
            buttonsStyling: !1
        }).then(function(t) {
            if (t.isConfirmed) {
                $("#modal-isi_data_konsumen").modal('hide');
                $(".div_menu").hide();

                let div_menu = `
                <div id="menu-ganti_kavling" class="float div_menu">
                    <h5>Tekan tombol jika sudah selesai memilih kavling</h5>
                    <button id="btn-ganti_kavling_ok" type="button" onclick="ganti_kavling_selection(1)"
                        class="my-float btn-icon btn btn-primary btn-round "  style="float:left">
                        Selesai
                    </button>
                    <button id="btn-batal_ganti_kavling" type="button" onclick="ganti_kavling_selection(0)"
                        class="my-float btn-icon btn btn-danger btn-round" style="float:left">
                        Batal
                    </button>
                </div>
                `

                $("#menu_here").append(div_menu)
            }
        })
    }

    function ganti_kavling_selection(e) {
        if (e) {
            let sh = editdtt[0]

            id_mkdt_old = $("#idk-id_mkdt").val()
            id_konsumen_old = $("#idk-id_konsumen").val()
            is_ganti_nama = 'Pindah Kavling'

            $("#btn-print_spptb").prop("href", "#")
            $("#idk-id_konsumen, #idk-id_mkdt").val("")
            $(".id_kavling").val(sh.id.substr(3));

            $("#idk_data_konsumen-tab").click()

            $(".label_alamat").append(`
                <hr>
                <span style='color:red'>Pindah ke Kavling ${sh.data.nama_jalan} No. ${sh.data.no_kavling}</div> 
            `)

        } else {

        }

        $("#menu-ganti_kavling").remove()
        $("#modal-isi_data_konsumen").modal('show');
        $("#keuangan_menu").show();
    }

    // function addRow() {
    //         const table = document.getElementById('list_kendaraan').getElementsByTagName('tbody')[0];
    //         const newRow = table.insertRow();

    //         // Create cells
    //         const keteranganCell = newRow.insertCell(0);
    //         const jatuhTempoCell = newRow.insertCell(1);
    //         const nominalCell = newRow.insertCell(2);
    //         const actionCell = newRow.insertCell(3);

    //         // Set cells to be editable
    //         keteranganCell.innerHTML = '<span class="editable" onclick="editCell(this)">Keterangan</span>';
    //         jatuhTempoCell.innerHTML = '<span class="editable" onclick="editCell(this)">Jatuh Tempo</span>';
    //         nominalCell.innerHTML = '<span class="editable" onclick="editCell(this)">Nominal</span>';
    //         actionCell.innerHTML = '<button onclick="deleteRow(this)">Hapus</button>';
    //     }

    //     function editCell(element) {
    //         const cell = element.parentNode;
    //         const currentValue = element.innerText;
    //         cell.innerHTML = `<input type="text" value="${currentValue}" onblur="saveCell(this)">`;
    //         cell.firstChild.focus();
    //     }

    //     function saveCell(input) {
    //         const cell = input.parentNode;
    //         const newValue = input.value;
    //         cell.innerHTML = `<span class="editable" onclick="editCell(this)">${newValue}</span>`;
    //     }

    //     function deleteRow(button) {
    //         const row = button.parentNode.parentNode;
    //         row.parentNode.removeChild(row);
    //     }
</script>