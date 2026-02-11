<!--#################################### Modal Keuangan #########################################-->
namespace App\Views\siteplan;
<div class="modal fade text-left" id="modal_divisi3">
    <div class="modal-dialog modal-dialog-scrollable modal-xl">
        <form id="fm-keuangan" class="add-new-record modal-content pt-0" autocomplete="off">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Bayar Tagihan</h5>
                <button type="button" class="close" data-dismiss="modal" id="close_modal_divisi3" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body flex-grow-1" style="background-color:#eee">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body bg-primary text-light">
                                <div class="row">
                                    <div class="col-8">
                                        <p class="modal-title label_alamat"></p>
                                    </div>
                                    <div class="col-4">
                                        <div class="card">
                                            <div class="card-body">
                                                <h5><i class="fas fa-users"></i> <span>Konsumen</span></h5>
                                                <h5><strong><span id="fm-bayar-label_konsumen"></span></strong></h5>
                                                <h5><i class="fas fa-calendar"></i> <span>Tanggal Booking</span></h5>
                                                <h5><strong><span id="fm-bayar-label_tgl"></span>(Rp. <span id="fm-bayar-label_bookingfee"></span>)</strong></h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body pb-0 pt-0">
                                <input type="hidden" class="form-control" name="status_mkdt" id="status_mkdt" value="" />
                                <input type="hidden" class="form-control id_kavling" name="id_kavling" value="" />
                                <input type="hidden" class="form-control" id="id_mkdt" name="id_mkdt" value="" />
                                <input type="hidden" class="form-control" id="nama_konsumen" name="nama_konsumen" value="" />
                                <!-- <div class="hidden">
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
                                </div> -->

                                <ul class="nav nav-tabs mb-1 mt-1" role="tablist">
                                    <li class="nav-item active">
                                        <a class="nav-link" id="tagihan-tab" data-toggle="tab" href="#tagihan" aria-controls="home"
                                            role="tab" aria-selected="true">Tagihan</a>
                                    </li>
                                    <!-- <li class="nav-item">
                                        <a class="nav-link" id="bb-tab" data-toggle="tab" href="#bb" aria-controls="home" role="tab"
                                            aria-selected="true">Biaya-biaya</a>
                                    </li> -->
                                    <li class="nav-item">
                                        <a class="nav-link" id="log_pembayaran-tab" data-toggle="tab" href="#log_pembayaran"
                                            aria-controls="log_pembayaran" role="tab" aria-selected="false">Riwayat Pembayaran</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="tab-content">
                            <div class="tab-pane active" id="tagihan" aria-labelledby="tagihan-tab" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-4 col-sm-12 col-lg-4">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="divider divider-left">
                                                    <div class="divider-text font-weight-bold">Status Konsumen</div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-9">
                                                        <h5 class="text-primary">Tandai Sebagai Sudah Lunas</h5>
                                                    </div>
                                                    <div class="col-3">
                                                        <div class="custom-control custom-switch custom-control-inline">
                                                            <input type="checkbox" class="custom-control-input cbp" id="is_lunas" name="is_lunas"
                                                                value="1" />
                                                            <label class="custom-control-label" for="is_lunas"></label>
                                                        </div>
                                                    </div>
                                                </div>


                                                <div class="divider divider-left">
                                                    <div class="divider-text font-weight-bold">List Tagihan</div>
                                                </div>

                                                <div id="tb-data-tagihan"></div>
                                                <!-- <div class="table-responsive">
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
                                                </div> -->
                                            </div>
                                        </div>


                                    </div>
                                    <div class="col-md-3 col-sm-12 col-lg-3" hidden>
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

                                    </div>
                                    <div class="col-md-8 col-sm-12 col-lg-8">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <div class="divider divider-left">
                                                            <div class="divider-text font-weight-bold">Form Bayar</div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-4 col-sm-12 col-lg-4">
                                                                <div class="form-group">
                                                                    <label for="bt-for">Pembayaran Angsuran Ke</label>
                                                                    <select multiple="multiple" name="bt-for[]" id="bt-for"
                                                                        class="form-control form-select"></select>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4 col-sm-12 col-lg-4">
                                                                <div class="form-group">
                                                                    <label for="tanggal_bayar">Tanggal Pembayaran</label>
                                                                    <input type="text" id="bt-tanggal_bayar_um" name="bt-tanggal_bayar_um"
                                                                        class="form-control flatpickr-human-friendly" placeholder="-" />
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4 col-sm-12 col-lg-4">
                                                                <div class="form-group">
                                                                    <label for="sisa_tagihan">Nominal Pembayaran</label>
                                                                    <input type="text" class="form-control num" id="bt-bayar_tagihan_um"
                                                                        name="bt-bayar_tagihan_um">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <div class="divider divider-left">
                                                            <div class="divider-text font-weight-bold">Alokasi Dana</div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-12">
                                                                <div class="p-1 mb-1 rounded border" style="background-color: #f1f1f1ff;">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <h5>Total harus Dialokasikan</h5>
                                                                        </div>
                                                                        <div class="col-8 text-right">
                                                                            <h5 class="text-success text-right"><strong id="fm-keu-total_dialokasi"></strong></h5>
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <h5>Sisa Belum Dialokasi</h5>
                                                                        </div>
                                                                        <div class="col-8 text-right">
                                                                            <h5 class="text-danger text-right"><strong id="fm-keu-sisa_belum_dialokasi"></strong></h5>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-12">
                                                                <button class="btn btn-sm btn-outline-primary mb-1" id="btn-add-item-alokasi" type="button">
                                                                    <i class="fas fa-plus"></i> Tambah Item
                                                                </button>
                                                                <div class="table-responsive">
                                                                    <table class="table table-sm table-bordered">
                                                                        <thead>
                                                                            <tr>
                                                                                <th></th>
                                                                                <th>Item</th>
                                                                                <th>Nominal</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody id="tb-alokasi-dana">
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>


                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="hide_lunas">
                                                    <div class="form-group">
                                                        <label for="berita_acara">Catatan</label>
                                                        <textarea class="form-control" id="bt-berita_acara_um" name="bt-berita_acara_um"
                                                            rows="3" placeholder="Keterangan"></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>

                            </div>

                            <div class="tab-pane" id="log_pembayaran" aria-labelledby="log_pembayaran-tab" role="tabpanel">
                                <div class="row">

                                    <div class="col-md-12 col-sm-12 col-lg-12">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="divider divider-left">
                                                    <div class="divider-text font-weight-bold">Riwayat Pembayaran</div>
                                                </div>
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
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                <button class="add-form-btn-keuangan btn btn-primary data-submit mr-1"
                    onclick="save_keuangan(); return false;" href="javascript:void(0)">Simpan</button>
            </div>
        </form>
    </div>
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
                    </ul> -->
                    <!-- <div class="tab-content">  -->
                    <!-- <div class="tab-pane active" id="da-tab_hasil_akad" aria-labelledby="da-hasil_akad-tab"
                            role="tabpanel"> -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body">
                                    <p class="modal-title label_konsumen" id="label_konsumen"></p>
                                    <p class="modal-title label_alamat" id="label_alamat3"></p>
                                </div>
                            </div>
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
                                <div class="card-body pb-0 pt-0">
                                    <ul class="nav nav-tabs mb-1 mt-1" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" id="keu-dajam-tab" data-toggle="tab"
                                                href="#keu-dajam" aria-controls="keu-dajam" role="tab"
                                                aria-selected="true">Dana Jaminan</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="keu-pengajuan-dajam-tab" data-toggle="tab" href="#keu-pengajuan-dajam"
                                                aria-controls="keu-pengajuan-dajam" role="tab" aria-selected="true">List Pengajuan Pencairan Dana Jaminan</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="keu-add-pengajuan-dajam-tab" data-toggle="tab" href="#keu-add-pengajuan-dajam"
                                                aria-controls="keu-pengajuan-dajam" role="tab" aria-selected="true">Tambah Pengajuan Dana Jaminan</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="card">
                                <div class="tab-content">
                                    <div class="tab-pane show active" id="keu-dajam" aria-labelledby="keu-dajam-tab" role="tabpanel">
                                        <div class="card-header">
                                            Dana Jaminan dan Pencairan
                                        </div>
                                        <div class="card-body">
                                            <div id="da-jaminan_here"></div>
                                        </div>
                                    </div>

                                    <div class="tab-pane" id="keu-add-pengajuan-dajam" aria-labelledby="keu-add-pengajuan-dajam-tab" role="tabpanel">
                                        <div class="card-body">
                                            <form id="form-pencairan" enctype="multipart/form-data">
                                                <!-- id_kavling static/readonly -->
                                                <div class="form-row">
                                                    <!-- <div class="form-group col-md-3">
                                                        <label>ID Kavling</label>
                                                        <input type="text" class="form-control" name="id_kavling" id="id_kavling" value="12345" readonly>
                                                    </div> -->
                                                    <div class="form-group col-md-3">
                                                        <label>Tanggal Pengajuan</label>
                                                        <input type="date" class="form-control" name="tanggal_pengajuan" required>
                                                    </div>
                                                    <div class="form-group col-md-3">
                                                        <label>Status</label>
                                                        <select class="form-control" name="status_cair" required>
                                                            <option value="0">Pengajuan</option>
                                                            <option value="1">Sudah Cair</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label>Keterangan Isi Surat</label>
                                                    <textarea class="form-control" name="keterangan" rows="3" placeholder="Ringkas isi/tujuan surat..."></textarea>
                                                </div>

                                                <div class="form-group">
                                                    <label>Lampiran Surat (PDF)</label>
                                                    <input type="file" class="form-control-file" name="surat" accept="application/pdf" required>
                                                    <small class="form-text text-muted">PDF, maksimum 4 MB.</small>
                                                </div>

                                                <button id="btn-saveDanaJaminan" type="submit" class="btn btn-primary">
                                                    Simpan
                                                </button>
                                            </form>
                                        </div>

                                    </div>
                                    <div class="tab-pane" id="keu-pengajuan-dajam" aria-labelledby="keu-pengajuan-dajam-tab" role="tabpanel">
                                        <div class="card-header">
                                            Riwayat Pengajuan Pencairan
                                        </div>
                                        <div class="card-body">
                                            <div id="da-pengajuan-jaminan_here"></div>
                                            <div class="table-responsive">
                                                <table class="table table-sm table-bordered" id="tbl-riwayat">
                                                    <thead class="thead-light">
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Diajukan Oleh</th>
                                                            <th>Tanggal Pengajuan</th>
                                                            <th>Tanggal Cair</th>
                                                            <th>Keterangan</th>
                                                            <th>Status</th>
                                                            <th>Lampiran</th>
                                                            <th>Aksi</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody><!-- rows via JS --></tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
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
    <div class="modal-dialog modal-dialog-scrollable modal-xl" role="document">
        <form id="fm-cashout-keu" class="" autocomplete="off">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Form Isi Cash Out</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body flex-grow-1" style="background-color:#eee">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body bg-primary text-light">
                                    <div class="row">
                                        <div class="col-8">
                                            <p class="modal-title label_alamat"></p>
                                        </div>
                                        <div class="col-4">
                                            <div class="card">
                                                <div class="card-body">
                                                    <h5><i class="fas fa-users"></i> <span>Konsumen</span></h5>
                                                    <h5><strong><span id="fm-co-label_konsumen"></span></strong></h5>
                                                    <h5><i class="fas fa-calendar"></i> <span>Tanggal Booking</span></h5>
                                                    <h5><strong><span id="fm-co-label_tgl"></span>(Rp. <span id="fm-co-label_bookingfee"></span>)</strong></h5>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="divider divider-left">
                                        <div class="divider-text font-weight-bold">Form Cash Out</div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3 col-sm-12 col-lg-3">
                                            <div class="form-group">
                                                <label for="co-untuk_pembayaran">Untuk Pembayaran</label>
                                                <select name="co-untuk_pembayaran" id="co-untuk_pembayaran"
                                                    class="form-control form-select"></select>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-sm-12 col-lg-3">
                                            <div class="form-group">
                                                <label for="co-tanggal_bayar">Tanggal Pembayaran</label>
                                                <input type="text" id="co-tanggal_bayar" name="co-tanggal_bayar"
                                                    class="form-control flatpickr-human-friendly" placeholder="-" />
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-sm-12 col-lg-3">
                                            <div class="form-group">
                                                <label for="co-nominal">Nominal Pembayaran</label>
                                                <input type="text" class="form-control num" id="co-nominal"
                                                    name="co-nominal">
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-sm-12 col-lg-3">
                                            <div class="form-group">
                                                <label for="co-keterangan">Keterangan Pembayaran</label>
                                                <textarea class="form-control" id="co-keterangan" name="co-keterangan"
                                                    rows="3" placeholder="Keterangan"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" class="form-control" id="cashout-id_kavling" name="id_kavling">

                    <div class="card">
                        <div class="card-body">
                            <div class="divider divider-left">
                                <div class="divider-text font-weight-bold">Riwayat Pembayaran Cash Out</div>
                            </div>
                            <table id="cashout-table" class="datatables-basic table compact">
                                <thead>
                                    <tr>
                                        <th width=""></th>
                                        <th width="20%">Item</th>
                                        <th width="20%">Tanggal Pembayaran</th>
                                        <th width="25%">Nominal</th>
                                        <th width="35%">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                            <div id="div-cashout-here" class="row">
                            </div>
                        </div>
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