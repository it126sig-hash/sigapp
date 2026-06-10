<style>
    #modal_divisi3 .modal-dialog {
        max-width: min(1440px, calc(100vw - 32px));
        margin: 1rem auto;
    }

    #modal_divisi3 .modal-content {
        border: 0;
        border-radius: 10px;
        box-shadow: 0 18px 45px rgba(15, 23, 42, .18);
        overflow: hidden;
    }

    #modal_divisi3 .modal-header {
        align-items: center;
        background: #fff;
        border-bottom: 1px solid #e5e7eb;
        padding: 1rem 1.25rem;
    }

    #modal_divisi3 .modal-title {
        color: #111827;
        font-size: 1.05rem;
        font-weight: 700;
    }

    #modal_divisi3 .keu-pay-body {
        background: #f3f5f7 !important;
        max-height: calc(100vh - 7rem);
        overflow-y: auto;
        padding: 1rem;
    }

    #modal_divisi3 .keu-pay-layout {
        display: flex;
        flex-wrap: nowrap;
        gap: 1rem;
        min-width: 0;
    }

    #modal_divisi3 .keu-pay-sidebar {
        align-self: flex-start;
        flex: 0 0 340px;
        max-height: calc(100vh - 7rem);
        max-width: 340px;
        overflow-y: auto;
        padding-right: .15rem;
        position: sticky;
        top: 0;
        z-index: 2;
    }

    #modal_divisi3 .keu-pay-content {
        flex: 1 1 auto;
        max-width: calc(100% - 356px);
        min-width: 0;
    }

    #modal_divisi3 .card {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        box-shadow: none;
        margin-bottom: 1rem;
    }

    #modal_divisi3 .card-body {
        padding: 1rem;
    }

    #modal_divisi3 .keu-pay-hero {
        background: linear-gradient(145deg, #2057a3 0%, #1f7a8c 100%);
        border: 0;
        color: #fff;
        overflow: hidden;
    }

    #modal_divisi3 .keu-pay-hero .card-body {
        background: transparent !important;
    }

    #modal_divisi3 .keu-pay-hero .label_alamat {
        font-size: .95rem;
        font-weight: 700;
        line-height: 1.35;
        margin-bottom: .85rem;
    }

    #modal_divisi3 .keu-pay-meta-card {
        background: rgba(255, 255, 255, .94);
        border: 0;
        color: #111827;
        margin-bottom: 0;
    }

    #modal_divisi3 .keu-pay-meta-card h6,
    #modal_divisi3 .keu-pay-meta-card h5 {
        color: #374151;
        line-height: 1.35;
        margin-bottom: .45rem;
    }

    #modal_divisi3 .divider {
        margin: .65rem 0 .85rem;
    }

    #modal_divisi3 .divider-left {
        border-left-color: #2057a3;
        padding-left: .75rem;
    }

    #modal_divisi3 .divider .divider-text {
        color: #111827;
        font-size: .86rem;
        font-weight: 700;
    }

    #modal_divisi3 .info-row,
    #modal_divisi3 .keu-cost-row {
        align-items: flex-start;
        background: #f9fafb;
        border: 1px solid #edf0f2;
        border-radius: 6px;
        display: flex;
        justify-content: space-between;
        gap: .75rem;
        margin-bottom: .45rem;
        padding: .45rem .55rem;
    }

    #modal_divisi3 .keu-cost-row.is-total {
        background: #eef5ff;
        border-color: #c9ddf5;
    }

    #modal_divisi3 .keu-cost-label,
    #modal_divisi3 label {
        color: #6b7280;
        font-size: .78rem;
        font-weight: 700;
        letter-spacing: 0;
    }

    #modal_divisi3 .keu-cost-value,
    #modal_divisi3 .info-value {
        color: #111827;
        font-weight: 700;
        overflow-wrap: anywhere;
        text-align: right;
    }

    #modal_divisi3 .form-control {
        border-color: #d8dde3;
        border-radius: 6px;
        min-height: 36px;
    }

    #modal_divisi3 .btn {
        border-radius: 6px;
        white-space: normal;
    }

    #modal_divisi3 .nav-tabs {
        border-bottom-color: #e5e7eb;
        gap: .35rem;
    }

    #modal_divisi3 .nav-tabs .nav-link {
        color: #4b5563;
        font-size: .82rem;
        font-weight: 700;
        white-space: nowrap;
    }

    #modal_divisi3 .nav-tabs .nav-link.active {
        color: #2057a3;
    }

    @media (max-width: 1199.98px) {
        #modal_divisi3 .keu-pay-layout {
            flex-wrap: wrap;
        }

        #modal_divisi3 .keu-pay-sidebar,
        #modal_divisi3 .keu-pay-content {
            flex: 0 0 100%;
            max-width: 100%;
        }

        #modal_divisi3 .keu-pay-sidebar {
            max-height: none;
            overflow-y: visible;
            padding-right: 0;
            position: static;
        }
    }

    @media (max-width: 767.98px) {
        #modal_divisi3 .modal-dialog {
            max-width: calc(100vw - 12px);
            margin: .5rem auto;
        }

        #modal_divisi3 .keu-pay-body {
            max-height: calc(100vh - 5.5rem);
            padding: .75rem;
        }

        #modal_divisi3 .nav-tabs {
            flex-direction: row !important;
            flex-wrap: nowrap;
            overflow-x: auto;
            padding-bottom: .25rem;
        }

        #modal_divisi3 .card-body {
            padding: .85rem;
        }
    }
</style>

<!--#################################### Modal Keuangan #########################################-->
<div class="modal fade text-left" id="modal_divisi3">
    <div class="modal-dialog modal-dialog-scrollable modal-xl">
        <form id="fm-keuangan" class="add-new-record modal-content pt-0" autocomplete="off">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Bayar Tagihan</h5>
                <button type="button" class="close" data-dismiss="modal" id="close_modal_divisi3" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body flex-grow-1 keu-pay-body">
                <div class="keu-pay-layout">
                    <aside class="keu-pay-sidebar">
                        <div class="card keu-pay-hero">
                            <div class="card-body bg-primary text-light">
                                <div class="row">
                                    <div class="col-12">
                                        <p class="modal-title label_alamat"></p>
                                    </div>
                                    <div class="col-12">
                                        <div class="card keu-pay-meta-card">
                                            <div class="card-body">
                                                <h6><i class="fas fa-users"></i> <span>Konsumen</span></h6>
                                                <h5><strong><span id="fm-bayar-label_konsumen">-</span></strong></h5>
                                                <h6><i class="fas fa-calendar"></i> <span>Tanggal Booking</span></h6>
                                                <h5 class="mb-0"><strong><span id="fm-bayar-label_tgl">-</span> (Rp. <span id="fm-bayar-label_bookingfee">0</span>)</strong></h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <div class="divider divider-left">
                                    <div class="divider-text font-weight-bold">Harga & Detail Biaya MKDT</div>
                                </div>
                                <div id="fm-keu-biaya-mkdt">
                                    <div class="keu-cost-row">
                                        <span class="keu-cost-label">Harga Jual</span>
                                        <span class="keu-cost-value" data-biaya-mkdt="harga_jual">Rp. 0</span>
                                    </div>
                                    <div class="keu-cost-row">
                                        <span class="keu-cost-label">Harga Jual Net</span>
                                        <span class="keu-cost-value" data-biaya-mkdt="harga_jual_net">Rp. 0</span>
                                    </div>
                                    <div class="keu-cost-row">
                                        <span class="keu-cost-label">Harga KPR</span>
                                        <span class="keu-cost-value" data-biaya-mkdt="harga_kpr">Rp. 0</span>
                                    </div>
                                    <div class="keu-cost-row">
                                        <span class="keu-cost-label">KPR ACC</span>
                                        <span class="keu-cost-value" data-biaya-mkdt="harga_kpr_acc">Rp. 0</span>
                                    </div>
                                    <div class="keu-cost-row">
                                        <span class="keu-cost-label">Uang Muka</span>
                                        <span class="keu-cost-value" data-biaya-mkdt="harga_uang_muka">Rp. 0</span>
                                    </div>
                                    <div class="keu-cost-row">
                                        <span class="keu-cost-label">Diskon UM</span>
                                        <span class="keu-cost-value" data-biaya-mkdt="harga_diskon_uang_muka">Rp. 0</span>
                                    </div>
                                    <div class="keu-cost-row">
                                        <span class="keu-cost-label">SBUM</span>
                                        <span class="keu-cost-value" data-biaya-mkdt="harga_sbum">Rp. 0</span>
                                    </div>
                                    <div class="keu-cost-row is-total">
                                        <span class="keu-cost-label">Total UM</span>
                                        <span class="keu-cost-value" data-biaya-mkdt="total_um">Rp. 0</span>
                                    </div>
                                    <div class="keu-cost-row">
                                        <span class="keu-cost-label">Administrasi</span>
                                        <span class="keu-cost-value" data-biaya-mkdt="harga_administrasi">Rp. 0</span>
                                    </div>
                                    <div class="keu-cost-row">
                                        <span class="keu-cost-label">BPHTB</span>
                                        <span class="keu-cost-value" data-biaya-mkdt="harga_bphtb">Rp. 0</span>
                                    </div>
                                    <div class="keu-cost-row">
                                        <span class="keu-cost-label">Biaya Proses</span>
                                        <span class="keu-cost-value" data-biaya-mkdt="harga_biaya_proses">Rp. 0</span>
                                    </div>
                                    <div class="keu-cost-row">
                                        <span class="keu-cost-label">PPN</span>
                                        <span class="keu-cost-value" data-biaya-mkdt="harga_ppn">Rp. 0</span>
                                    </div>
                                    <div class="keu-cost-row">
                                        <span class="keu-cost-label">Penambahan UM</span>
                                        <span class="keu-cost-value" data-biaya-mkdt="harga_penambahan_um">Rp. 0</span>
                                    </div>
                                    <div class="keu-cost-row">
                                        <span class="keu-cost-label">Penambahan</span>
                                        <span class="keu-cost-value" data-biaya-mkdt="harga_penambahan">Rp. 0</span>
                                    </div>
                                    <div class="keu-cost-row">
                                        <span class="keu-cost-label">Penambahan Tanah</span>
                                        <span class="keu-cost-value" data-biaya-mkdt="harga_penambahan_tanah">Rp. 0</span>
                                    </div>
                                    <div class="keu-cost-row is-total">
                                        <span class="keu-cost-label">Total Biaya Lain</span>
                                        <span class="keu-cost-value" data-biaya-mkdt="total_biaya_lain">Rp. 0</span>
                                    </div>
                                    <div class="keu-cost-row is-total mb-0">
                                        <span class="keu-cost-label">Total Tercatat</span>
                                        <span class="keu-cost-value" data-biaya-mkdt="total_tercatat">Rp. 0</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </aside>
                    <section class="keu-pay-content">
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
                    </section>
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

   let id_mkdt_old = null,
  id_konsumen_old = null,
  is_ganti_nama = false,
  alokasi_items = [];

$("#btn-add-item-alokasi").click(function () {
  let nominal = removeComma($("#bt-bayar_tagihan_um").val());
  let tanggal = $("#bt-tanggal_bayar_um").val();
  let metode = $("#bt-for").val();
  if (metode == "") {
    Swal.fire({
      icon: "warning",
      title: "Peringatan",
      text: "Angsuran belum diisi",
      showConfirmButton: false,
      timer: 1500,
    }).then(() => {
      $("#bt-for").focus();
    });
    return;
  }
  if (tanggal == "") {
    Swal.fire({
      icon: "warning",
      title: "Peringatan",
      text: "Tanggal pembayaran belum diisi",
      showConfirmButton: false,
      timer: 1500,
    }).then(() => {
      $("#bt-tanggal_bayar_um").focus();
    });
    return;
  }
  if (nominal == 0 || nominal == "") {
    Swal.fire({
      icon: "warning",
      title: "Peringatan",
      text: "Nominal pembayaran belum diisi",
      showConfirmButton: false,
      timer: 1500,
    }).then(() => {
      $("#bt-bayar_tagihan_um").focus();
    });
    return;
  }

  let options = {};
  li_keu.forEach((item) => {
    options[item.id_keuangan_item_list] = item.item;
  });

  Swal.fire({
    title: "Pilih Item",
    input: "select",
    inputOptions: options,
    inputPlaceholder: "Pilih item alokasi",
    showCancelButton: true,
  }).then((result) => {
    if (result.value) {
      let selectedItem = li_keu.find(
        (item) => item.id_keuangan_item_list === result.value,
      );
      if (selectedItem) {
        // alokasi_items.push(selectedItem);
        renderTableAlokasi(selectedItem);
      }
    }
  });
});

function renderTableAlokasi(item) {
  let html = "";

  if ($(`#fm-bayar_nominal-${item.id_keuangan_item_list}`).length) {
    Swal.fire({
      icon: "error",
      title: item.item + " sudah ditambahkan",
      showConfirmButton: false,
    });
    return;
  }
  alokasi_items.push(item);
  html = `
  <tr id="tr-li-${item.id_keuangan_item_list}">
    <td><a href="javascript:void(0)" onclick="deleteItemAlokasi(${item.id_keuangan_item_list})" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></a></td>
    <td>${item.item}</td>
    <td class="text-right"><input type="text" onchange="setAlokasi(this)" name="nominal-${item.id_keuangan_item_list}" id="fm-bayar_nominal-${item.id_keuangan_item_list}" class="form-control num item-alokasi" placeholder="Nominal alokasi dari pembayaran"></td>
  </tr>`;
  $("#tb-alokasi-dana").append(html);
  setAlokasi();
}

function deleteItemAlokasi(id) {
  Swal.fire({
    title: "Apakah anda yakin?",
    text: "Item alokasi akan dihapus!",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Ya, hapus!",
  }).then((result) => {
    if (result.isConfirmed) {
      let index = alokasi_items.findIndex(
        (item) => item.id_keuangan_item_list == id,
      );
      if (index !== -1) {
        alokasi_items.splice(index, 1);
        $(`#tr-li-${id}`).remove();
        setAlokasi();
        Swal.fire("Terhapus!", "Item alokasi telah dihapus.", "success");
      }
    }
  });
}

/********************************* keuangan *******************************************/
$("#bt-bayar_tagihan_um").change(function () {
  ubahMaksNominal("#bt-bayar_tagihan_um");
});

function ubahMaksNominal(id) {
  let s = state.total_cicilan - state.sudah_bayar,
    b = removeComma($(id).val());

  if (b > s) $(id).val(s).keyup();
  else $(id).val(b).keyup();

  setAlokasi();
}

function setAlokasi(e = null) {
  const alokasi = $("#fm-keu-total_dialokasi");
  const sisa_alokasi = $("#fm-keu-sisa_belum_dialokasi");
  const nominal = removeComma($("#bt-bayar_tagihan_um").val());

  const sisa_old = removeComma(sisa_alokasi.html());

  let total = 0;
  $(".item-alokasi").each(function () {
    total += removeComma($(this).val());
  });

  let sisa = nominal - total;

  if (total > nominal) {
    Swal.fire({
      icon: "warning",
      title: "Total alokasi melebihi nominal",
      text: "Nominal akan disesuaikan dengan total alokasi",
      showConfirmButton: false,
    });
    sisa = 0;
    if (e) {
      e.value = sisa_old;
    }
  }
  alokasi.html(num_format(nominal));
  sisa_alokasi.html(num_format(sisa));
}

$("#idk-rincian").richText({
  fonts: false,
  // uploads
  imageUpload: false,
  fileUpload: false,

  // media
  videoEmbed: false,

  // link
  urls: false,
});
$("#snk").richText({
  // text formatting
  bold: true,
  italic: true,
  underline: true,

  // text alignment
  leftAlign: true,
  centerAlign: true,
  rightAlign: true,
  justify: true,

  // lists
  ol: true,
  ul: true,

  // title
  heading: true,
});
$("#kopsurat").select2({
  placeholder: "Pilih Kop Surat",
  allowClear: true,
  ajax: {
    url: base_url + "/Home/getKop",
    dataType: "json",
    delay: 250,
    method: "post",
    data: function (params) {
      return {
        [csrfName]: csrfHash,
        search: params.term,
      };
    },
    processResults: function (r) {
      csrfHash = r.token;

      let results = [];
      $.each(r.data, function (k, v) {
        results.push({
          id: v.id,
          text: v.nama + " (" + v.ukuran + ")",
          lokasi: v.lokasi,
          ukuran: v.ukuran,
          mt: v.mt,
          mb: v.mb,
          ml: v.ml,
          mr: v.mr,
        });
      });

      return {
        results: results,
      };
    },
    cache: false,
  },
});

function print_tagihan() {
  $("#pt_id_mkdt").html("");
  $("#pt_id_konsumen").html("");
  $("#pt_id_kavling").html("");
  $("#pt_detail_konsumen").html("");
  $("#pt_detail_kavling").html("");
  $("#list_inv-here").html("");

  $("#cp_telp").html("");
  $("#tb-print-data-tagihan").html("");

  $('.nav-tabs a[href="#form_list_inv"]').tab("show");

  document
    .querySelector("#tanggal_surat_tagihan")
    ._flatpickr.setDate(new Date().toDateInputValue());
  document
    .querySelector("#pt-tanggal_jatuh_tempo")
    ._flatpickr.setDate(new Date().fp_incr(7));

  let role,
    sh = editdtt[0];

  if (editdtt.length == 0) {
    Swal.fire({
      //position: 'bottom-end',
      icon: "error",
      title: "Terjadi Kesalahan.",
      text: "Tidak ada kavling yang dipilih",
      showConfirmButton: false,
    });
    return;
  } else if (!sh.data.id_mkdt) {
    Swal.fire({
      //position: 'bottom-end',
      icon: "error",
      title:
        "Belum ada data konsumen di kavling" +
        sh.data.nama_jalan +
        ", No." +
        sh.data.no_kavling,
      showConfirmButton: false,
      timer: 1500,
    });
    return;
  }

  let id_kavling = sh.id.substr(3);

  $.ajax({
    url: base_url + "keuangan/get_tagihan/inv",
    type: "post",
    data: {
      [csrfName]: csrfHash,
      id_keuangan: sh.data.id_keuangan,
      id_kavling: id_kavling,
      id_mkdt: sh.data.id_mkdt,
    },
    dataType: "json",
    success: function (r) {
      let kons = r.detail,
        lt = r.list_tagihan;
      csrfHash = r.token;

      if (!lt.length) {
        Swal.fire({
          //position: 'bottom-end',
          icon: "error",
          title: "Tagihan tidak ditemukan",
          text: "Isi tagihan terlebih dahulu",
          showConfirmButton: false,
        });
        return;
      }

      if (r.invoice.length) {
        let tb = "";
        $.each(r.invoice, function (i, v) {
          tb += "<tr>";
          tb +=
            "<td>" +
            v.no_inv +
            "</td> " +
            "<td>" +
            format_date(v.tanggal_invoice) +
            "</td> " +
            "<td>" +
            format_date(v.tanggal_jatuh_tempo) +
            "</td> " +
            "<td>" +
            v.uadd_by +
            " <br>" +
            format_date(v.date_add) +
            "</td> " +
            `<td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-outline-primary waves-effect btn-sm" onclick="print_inv('` +
            v.no_inv +
            `')"><i class="fa fa-print"></i></button>
                                    </div>
                                </td> `;
          tb += "</tr>";
        });

        $("#list_inv-here").append(tb);
      } else {
        $("#list_inv-here").html("<tr><td colspan=5>Tidak ada data</td></tr>");
      }

      //load company profile detail
      // $("#cp_nama_perusahaan").html(r.compro.nama_perusahaan)
      // $("#cp_alamat_perusahaan").html(r.compro.alamat)
      // $("#cp_telp").html(r.compro.telp + " - " + r.compro.telp2)

      //load konsumen detail
      $("#pt_id_mkdt").html(kons.id_mkdt);
      $("#pt_id_konsumen").html(kons.id_konsumen);
      $("#pt_id_kavling").html(kons.id_kavling);
      $("#pt_detail_konsumen").html(
        kons.nama_konsumen +
          " (" +
          kons.hp_konsumen +
          ")" +
          "<br>" +
          kons.alamat_konsumen,
      );
      $("#pt_detail_kavling").html(
        dt_proyek.nama_proyek +
          "<br>" +
          sh.data.nama_jalan +
          " No. " +
          sh.data.no_kavling,
      );
      // $("#pt_hp_konsumen").html(kons.hp_konsumen)

      /************************ load table tagihan ***************************/
      let tr_tg = "",
        no = 1,
        tot_tg = 0,
        sb_button = "",
        chkd = "",
        tg = r.list_tagihan,
        sudah_bayar = r.sudah_bayar ? r.sudah_bayar : 0;

      $.each(tg, function (i, v) {
        chkd = v.sudah_dibayar == 1 ? "checked" : "";
        sb_button =
          `<div class="form-group">
                                    <div class="custom-control custom-switch custom-control-inline">
                                        <input type="checkbox" ` +
          chkd +
          ` onchange="save_sb(` +
          v.id_keuangan +
          `)" class="custom-control-input " disabled id="sb_btn` +
          v.id_keuangan +
          `" name="sb_btn[` +
          v.id_keuangan +
          `]" value="1" />
                                        <label class="custom-control-label" for="sb_btn` +
          v.id_keuangan +
          `"></label>
                                    </div>
                                </div>`;

        tot_tg += parseInt(v.nominal);
        tr_tg +=
          "<tr>" +
          "<td>" +
          no +
          "</td>" +
          "<td>" +
          v.berita_acara +
          "</td>" +
          "<td>" +
          format_date(v.jatuh_tempo_tgl) +
          "</td>" +
          // "<td>" + sb_button + "</td>" +
          "<td style='text-align:right'>" +
          num_format(v.nominal) +
          "</td>" +
          "<tr>";
        no++;
      });

      tr_tg +=
        "<tr>" +
        "<th colspan='3' style='text-align:right'>Total Tagihan</th>" +
        "<th style='text-align:right'>" +
        num_format(tot_tg) +
        "</th>" +
        "<tr>";

      tr_tg +=
        "<tr>" +
        "<th colspan='3' style='text-align:right'>Sudah Bayar</th>" +
        "<th style='text-align:right'>" +
        num_format(sudah_bayar) +
        "</th>" +
        "<tr>";
      tr_tg +=
        "<tr>" +
        "<th colspan='3' style='text-align:right'>Sisa</th>" +
        "<th style='text-align:right'>" +
        num_format(tot_tg - parseInt(sudah_bayar)) +
        "</th>" +
        "<tr>";

      $("#tb-print-data-tagihan").append(tr_tg);

      $("#print_tagihan_modal").modal({
        backdrop: "static",
        keyboard: false,
      });
    },
    error: function () {
      Swal.fire({
        //position: 'bottom-end',
        icon: "error",
        title: "Terjadi kesalahan saat memuat data",
        showConfirmButton: false,
      });
    },
  });
}

function save_inv() {
  if (!$("#no_sruat").val()) {
    $("#no_sruat").addClass("is-invalid");
    return swal("warning", "Peringatan!", "No Invoice Harus Diisi!!");
  }

  $("#no_sruat").removeClass("is-invalid");
  $.ajax({
    url: base_url + "keuangan/save_inv",
    type: "post",
    data: {
      [csrfName]: csrfHash,
      no_inv: $("#no_sruat").val(),
      id_konsumen: $("#pt_id_konsumen").html(),
      id_mkdt: $("#pt_id_mkdt").html(),
      id_kavling: $("#pt_id_kavling").html(),
      id_kopsurat: $("#kopsurat").val(),
      tanggal_invoice: $("#tanggal_surat_tagihan").val(),
      tanggal_jatuh_tempo: $("#pt-tanggal_jatuh_tempo").val(),
      tagihan: $("#tb-print-data-tagihan").html(),
      terms: $("#snk").val(),
    },
    dataType: "json",
    beforeSend: function () {
      $("#loading").removeClass("hidden");
      $("#form_add_inv-btn").html("Menyimpan");
      $("#form_add_inv-btn").prop("disabled", true);
    },
    success: function (r) {
      csrfHash = r.token;
      $("#loading").addClass("hidden");
      if (r.success === true) {
        Swal.fire({
          //position: 'bottom-end',
          icon: "success",
          title: r.messages || r.message || "Data berhasil disimpan",
          showConfirmButton: false,
        }).then(function () {
          print_tagihan();
          // $('.nav-tabs a[href="#form_list_inv"]').tab('show');
          $("#form_add_inv-btn").html("Simpan");
          $("#form_add_inv-btn").prop("disabled", false);
        });
      } else {
        Swal.fire({
          //position: 'bottom-end',
          icon: "error",
          title: r.messages || r.message || "Terjadi kesalahan",
          showConfirmButton: false,
        }).then(function () {
          $("#form_add_inv-btn").html("Simpan");
          $("#form_add_inv-btn").prop("disabled", false);
        });
      }
    },
    error: function () {
      Swal.fire({
        //position: 'bottom-end',
        icon: "error",
        title: "Terjadi kesalahan",
        showConfirmButton: true,
        // timer: 1500
      }).then(function () {
        $("#form_add_inv-btn").html("Simpan");
        $("#form_add_inv-btn").prop("disabled", false);
      });
    },
  });
}

function print_inv(e) {
  var myWindow = window.open(
    base_url + "/keuangan/print_tagihan/?id=" + e,
    "_blank",
    "top=100,left=300,width=700,height=600",
  );
  setTimeout(function () {
    myWindow.focus();
  }, 1000);
}

function doPrint() {
  (async () => {
    const rawResponse = await fetch(base_url + "keuangan/doPrint", {
      method: "POST",
      headers: {
        Accept: "application/json",
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        [csrfName]: csrfHash,
        konsumen: $("#pt_nama_konsumen").html(),
        alamat: $("#pt_alamat_konsumen").html(),
        no_sruat: $("#no_sruat").val(),
        tanggal_surat_tagihan: $("#tanggal_surat_tagihan").val(),
        table: $("#tb-print-data-tagihan").html(),
      }),
    })
      .then((resp) => resp.blob())
      .then((blob) => {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement("a");
        a.style.display = "none";
        a.href = url;

        // the filename you want
        a.download =
          "Tagihan " +
          $("#pt_nama_konsumen").html() +
          " " +
          $("#tanggal_surat_tagihan").val() +
          ".pdf";
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
      })
      .catch(() => alert("oh no!"));
  })();
}

//if pelunasan lebih dari sisa tagihan pelunasan diisi sisa tagihan
// $("#bt-bayar_tagihan_um").change(function () {
//     if (parseFloat(removeComma(this.value)) > parseFloat(removeComma($("#bt-sisa_tagihan_um").val())))
//         $("#bt-bayar_tagihan_um").val($("#bt-sisa_tagihan_um").val())
// })

//simpan status sudah bayar
function save_sb(id) {
  let i = $("#sb_btn" + id).prop("checked") ? 1 : 0;
  $.ajax({
    url: base_url + "keuangan/save_sb",
    type: "post",
    data: {
      [csrfName]: csrfHash,
      id_keuangan: id,
      sb: i,
    },
    dataType: "json",
    beforeSend: function () {
      $("#loading").removeClass("hidden");
    },
    success: function (r) {
      csrfHash = r.token;
      $("#loading").addClass("hidden");
      if (r.success === false) {
        Swal.fire({
          icon: "error",
          title: r.messages || r.message || "Terjadi kesalahan",
          showConfirmButton: false,
        });
      }
    },
  });
}
$("#mk-id").select2({
  placeholder: "Pilih Pricelist",
  allowClear: true,
  ajax: {
    url: base_url + "/Hargajual/getAll",
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
      $.each(r.data, function (k, v) {
        results.push({
          id: v.id,
          text:
            "Rp. " +
            num_format(v.hargajual) +
            " (" +
            v.tipe_rumah +
            ")" +
            ": Per " +
            format_date(v.tgl_harga),
          row: v.row,
          tipe: v.tipe_rumah,
          lb: v.lb,
          lt: v.lt,
          hargajual: v.hargajual,
          kpr: v.kpr,
          uang_muka: v.uang_muka,
          bphtb: v.bphtb,
          biaya_adm: v.biaya_adm,
          biaya_proses: v.biaya_proses,
          id_tipe: v.id_tipe,
          tgl_harga: format_date(v.tgl_harga),
        });
      });

      return {
        results: results,
      };
    },
    cache: false,
  },
});
$("#mk-id").on("select2:selecting", function (e) {
  // if (Object.keys(data_um).length > 0 || Object.keys(data_bb).length > 0) {
  //     Swal.fire({
  //         title: 'Lakukan perubahan?',
  //         text: "data pada tabel tagihan akan terhapus!",
  //         type: 'warning',
  //         showCancelButton: true,
  //         confirmButtonColor: '#3085d6',
  //         cancelButtonColor: '#d33',
  //         confirmButtonText: 'Ya!',
  //         confirmButtonClass: 'btn btn-primary',
  //         cancelButtonClass: 'btn btn-danger ml-1',
  //         buttonsStyling: !1
  //     }).then(function (t) {
  //         if (t.value) {
  //             var i = e.params.args.data
  //             $.each(i, function (k, v) {
  //                 $("#mk-" + k).val(v).change().keyup()
  //             })
  //             sum_mktotal()
  //             data_um = {}
  //             data_bb = {}
  //             $("#list_cicilan_here").html("")
  //             $("#total_cicilan_um").val(0).change().keyup()
  //             $("#total_cicilan_bb").val(0).change().keyup()
  //             $("#id_list_keu").val('');
  //             $("#id_list_keu_bb").val('');
  //         } else
  //             return false
  //     })
  // } else {
  //     var i = e.params.args.data
  //     $.each(i, function (k, v) {
  //         $("#mk-" + k).val(v).change().keyup()
  //     })
  //     sum_mktotal()
  // }
});
$("#mk-id").change(function () {
  if (!this.value) $(".mk-fm").val("");
});
$(
  "#mk-harga_ppn, #mk-harga_penambahan, #mk-harga_penambahan_tanah, #mk-diskon",
).on("focusin", function () {
  $(this).data("val", $(this).val());
});
// $("#mk-harga_ppn, #mk-harga_penambahan, #mk-diskon").change(function () {
//     var prev = $(this).data('val'),
//         current = $(this).val(),
//         th = $(this);

//     if (Object.keys(data_um).length > 0 || Object.keys(data_bb).length > 0) {
//         Swal.fire({
//             title: 'Lakukan perubahan?',
//             text: "data pada tabel tagihan akan terhapus!",
//             type: 'warning',
//             showCancelButton: true,
//             confirmButtonColor: '#3085d6',
//             cancelButtonColor: '#d33',
//             confirmButtonText: 'Ya!',
//             confirmButtonClass: 'btn btn-primary',
//             cancelButtonClass: 'btn btn-danger ml-1',
//             buttonsStyling: !1
//         }).then(function (t) {
//             if (t.isConfirmed) {
//                 sum_mktotal()
//                 data_um = {}
//                 data_bb = {}
//                 $("#list_cicilan_here").html("")
//                 $("#total_cicilan_um").val(0).change().keyup()
//                 $("#total_cicilan_bb").val(0).change().keyup()
//                 $("#id_list_keu").val('');
//                 $("#id_list_keu_bb").val('');
//             } else
//                 th.val(prev)
//         })
//     } else
//         sum_mktotal()
// })

$("#isi_tagihan-modal").on("hidden.bs.modal", function () {
  data_um = {};
  data_bb = {};
});

// $("#mk-keterangan_harga_penambahan").change(function () {
//     // console.log(this.value)
//     if (this.value)
//         $("#berita_acara").append(`<option id='opt-keterangan_harga_penambahan'>${this.value}</option>`)
//     else
//         $("#berita_acara option[id='opt-keterangan_harga_penambahan']").remove()
// })

$("#berita_acara").change(function () {
  let nom = 0;
  switch (this.value) {
    case "Uang Muka":
      nom =
        parseFloat(removeComma($("#mk-uang_muka").val())) -
        parseFloat(removeComma($("#mk-diskon_uang_muka").val()));
      break;
    case "Biaya Administrasi":
      nom = parseFloat(removeComma($("#mk-biaya_adm").val()));
      break;
    case "Turun KPR":
      nom = parseFloat(removeComma($("#mk-harga_penambahan_um").val()));
      break;
    case "Biaya Kavling Strategis":
      nom = parseFloat(removeComma($("#mk-harga_penambahan").val()));
      break;
    case "Biaya Kelebihan Tanah":
      nom = parseFloat(removeComma($("#mk-harga_penambahan_tanah").val()));
      break;
    default:
      nom = 0;
      break;
  }
  changeVal("#nominal", nom);
});
$("#berita_acara_bb").change(function () {
  let nom = 0;
  switch (this.value) {
    case "PPN":
      nom = parseFloat(removeComma($("#mk-ppn").val()));
      break;
    case "BPHTB":
      nom = parseFloat(removeComma($("#mk-bphtb").val()));
      break;
    case "Biaya Proses":
      nom = parseFloat(removeComma($("#mk-biaya_proses").val()));
      break;
    default:
      nom = 0;
      break;
  }
  changeVal("#nominal_bb", nom);
});

// function ganti_kavling(){
//     if ($("#spptb_ttd_file").html() == 'Tidak ada data') {
//         return swal('error', 'Kamu harus mengunggah file SPPTB yang sudah ditandatangani')
//     }
//     Swal.fire({
//         title: 'Pindah kavling?',
//         text: "Apakah anda yakin akan memindahkan kavling?",
//         // type: 'warning',
//         showCancelButton: true,
//         confirmButtonColor: '#3085d6',
//         cancelButtonColor: '#d33',
//         confirmButtonText: 'Ya!',
//         confirmButtonClass: 'btn btn-primary',
//         cancelButtonClass: 'btn btn-danger ml-1',
//         buttonsStyling: !1
//     }).then(function (t) {
//         if (t.isConfirmed) {
//             id_mkdt_old = $("#idk-id_mkdt").val()
//             id_konsumen_old = $("#idk-id_konsumen").val()
//             is_ganti_nama = 'Pindah Kavling'

//             $("#btn-print_spptb").prop("href", "#")
//             $("#idk-id_konsumen, #idk-id_mkdt").val("")
//             $("#idk_data_konsumen-tab").click()
//         }
//     })
// }
function ganti_nama() {
  if ($("#spptb_ttd_file").html() == "Tidak ada data") {
    return swal(
      "error",
      "Kamu harus mengunggah file SPPTB yang sudah ditandatangani",
    );
  }
  Swal.fire({
    title: "Ganti nama konsumen?",
    text: "Apakah anda yakin akan mengganti nama konsumen?",
    // type: 'warning',
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Ya!",
    confirmButtonClass: "btn btn-primary",
    cancelButtonClass: "btn btn-danger ml-1",
    buttonsStyling: !1,
  }).then(function (t) {
    if (t.isConfirmed) {
      id_mkdt_old = $("#idk-id_mkdt").val();
      id_konsumen_old = $("#idk-id_konsumen").val();
      is_ganti_nama = "Ganti Nama";

      $("#btn-print_spptb").prop("href", "#");
      $(".gn, #idk-id_konsumen, #idk-id_mkdt").val("");
      $("#idk_data_konsumen-tab").click();
    }
  });
}

//sudah t  idak dipakai
// function isi_tagihan() {
//     var sh = editdtt[0],
//         id_kavling = sh.id.substr(3);

//     if (sh.data2.status_mkdt == 'Batal') {
//         Swal.fire({
//             //position: 'bottom-end',
//             icon: 'error',
//             title: "Status konsumen batal",
//             text: "Silahkan isi kavling dengan konsumen baru terlebih dahulu",
//             // showConfirmButton: false,
//             // timer: 1500
//         })
//         return;
//     }

//     if (!sh.data.id_mkdt) {
//         Swal.fire({
//             //position: 'bottom-end',
//             icon: 'error',
//             title: "Belum ada data konsumen di kavling" + sh.data.nama_jalan + ", No." + sh.data.no_kavling,
//             showConfirmButton: false,
//             timer: 1500
//         })
//         return;
//     }

//     data_um = {}
//     data_bb = {}
//     $("#fm-isi_tagihan")[0].reset()
//     $("#list_cicilan_here").html("")
//     $("#total_cicilan_um").val(0).change().keyup()
//     $("#total_cicilan_bb").val(0).change().keyup()
//     $("#id_list_keu").val('');
//     $("#id_list_keu_bb").val('');

//     // $("#cicilan_belong_here").html("");
//     // $("#berita_acara0").val("Uang Muka 1");
//     // $("#nominal0").val(0).keyup();

//     $.ajax({
//         url: base_url + 'keuangan/get_data_by_id',
//         type: 'post',
//         data: {
//             [csrfName]: csrfHash,
//             id_keuangan: sh.data.id_keuangan,
//             id_kavling: id_kavling,
//             id_mkdt: sh.data.id_mkdt
//         },
//         dataType: 'json',
//         beforeSend: function() {
//             $("#loading").removeClass("hidden")
//         },
//         success: function(r) {
//             $("#loading").addClass("hidden")
//             let mkdt = r.mkdt,
//                 hj = r.hj,
//                 tg = r.tagihan

//             $("#mk-id_mkdt").val(sh.data.id_mkdt)

//             $('.select2').val(null).trigger('change');
//             if (hj.id) {
//                 // if (hj.id) {

//                 for (let i in hj) {
//                     $("#fm-isi_tagihan #mk-" + i).val(hj[i]).change().keyup();
//                 }
//                 $("#fm-isi_tagihan #mk-tgl_harga").val(format_date(hj.tgl_harga));
//                 $("#fm-isi_tagihan #mk-tipe").val(hj.tipe_rumah);
//                 $("#mk-id").append(
//                     $("<option selected></option>")
//                     .attr("value", hj.id)
//                     .text("Rp. " + num_format(hj.hargajual) + " (" + hj.tipe_rumah + ")" + ": Per " + hj.tgl_harga)
//                 ).trigger('change');
//                 // } else {
//                 //     $(".mk-fm").val(0)
//                 // }
//             } else {
//                 $("#mk-id").append(
//                     $("<option selected></option>")
//                     .attr("value", mkdt.id_hargajual)
//                     .text("Rp. " + num_format(mkdt.harga_jual) + " (" + mkdt.tipe_rumah + ")" + ": " + mkdt.tgl_harga)
//                 ).trigger('change');

//                 $("#fm-isi_tagihan #mk-tgl_harga").val(format_date(mkdt.tgl_harga));
//                 $("#mk-row").val(mkdt.row).change()
//                 $("#mk-tipe").val(mkdt.tipe_rumah).change()
//                 $("#mk-lb").val(mkdt.hj_lb).change()
//                 $("#mk-lt").val(mkdt.hj_lt).change()

//                 $("#mk-hargajual").val(mkdt.harga_jual).change()
//                 $("#mk-kpr").val(mkdt.harga_kpr).change()
//                 $("#mk-uang_muka").val(mkdt.harga_jual - mkdt.harga_kpr).change()
//                 $("#mk-bphtb").val(mkdt.harga_bphtb).change()
//                 $("#mk-biaya_adm").val(mkdt.harga_administrasi).change()
//                 $("#mk-biaya_proses").val(mkdt.harga_biaya_proses).change()
//             }
//             $("#mk-diskon").val(mkdt.harga_diskon).change().keyup()
//             $("#mk-harga_penambahan").val(mkdt.harga_penambahan).change().keyup()
//             $("#mk-harga_penambahan_tanah").val(mkdt.harga_penambahan_tanah).change().keyup()
//             $("#mk-keterangan_harga_penambahan").val(mkdt.keterangan_penambahan_biaya)

//             $("#mk-harga_ppn").val(mkdt.harga_ppn).change().keyup()
//             $("#mk-harga_kpr_acc").val(mkdt.harga_kpr_acc).change().keyup()

//             let turun_kpr = (mkdt.harga_kpr_acc == 0) ? 0 : mkdt.harga_kpr - mkdt.harga_kpr_acc;
//             $("#mk-harga_penambahan_um").val(turun_kpr).change().keyup()

//             sum_mktotal()

//             //load tagihan
//             if (tg) {
//                 let a = it;
//                 $.each(tg, function(i, v) {
//                     if (v.status == "UM") {
//                         data_um['lk' + a] = ({
//                             id_list_keu: 'lk' + a,
//                             id_keuangan: (v.id_keuangan),
//                             berita_acara: (v.berita_acara),
//                             nominal: num_format(v.nominal),
//                             jatuh_tempo_tgl: (v.jatuh_tempo_tgl),
//                         })
//                     }
//                     if (v.status == "BB") {
//                         data_bb['lk' + a] = ({
//                             id_list_keu_bb: 'lk' + a,
//                             id_keuangan_bb: (v.id_keuangan),
//                             berita_acara_bb: (v.berita_acara),
//                             nominal_bb: num_format(v.nominal),
//                             jatuh_tempo_tgl_bb: (v.jatuh_tempo_tgl),
//                         })
//                     }

//                     a++;
//                 })
//                 tambah_ketagihan()
//                 it = a;
//             }

//             $(".label_alamat").html(dt_proyek.nama_proyek + "<br/>" + sh.data.nama_jalan + ", No." + sh.data.no_kavling + "<br/>" + sh.data2.no_tipe_rumah + " (" + sh.data2.tipe_rumah + ")<br/>");
//             $('#isi_tagihan-modal').modal({
//                 backdrop: 'static',
//                 keyboard: false
//             });
//             //load data form
//             // for (let i in mkdt) {
//             //     $("#fm-isi_tagihan #" + i).val(mkdt[i]);
//             // }

//             // if (r.tagihan) {
//             //     it = 0;
//             //     let tg = r.tagihan
//             //     if (tg.length > 0) {
//             //         for (i = 0; i < tg.length; i++) {
//             //             if (i > 0)
//             //                 tambah();

//             //             fp = flatpickr("#fm-isi_tagihan #jatuh_tempo_tgl" + i, {
//             //                 altInput: true,
//             //                 altFormat: 'F j, Y',
//             //                 dateFormat: 'Y-m-d'
//             //             });
//             //             // console.log(tg[i]['id_keuangan']);
//             //             $("#fm-isi_tagihan #id_keuangan" + i).val(tg[i]['id_keuangan']);
//             //             $("#fm-isi_tagihan #nominal" + i).val(tg[i]['nominal']).keyup().change();
//             //             $("#fm-isi_tagihan #berita_acara" + i).val(tg[i]['berita_acara']);
//             //             document.querySelector("#fm-isi_tagihan #jatuh_tempo_tgl" + i)._flatpickr.setDate(tg[i]['jatuh_tempo_tgl']);
//             //         }
//             //     }
//             // }
//             // total('#fm-isi_tagihan');
//         },
//         error: function() {
//             $("#loading").addClass("hidden")
//             Swal.fire({
//                 //position: 'bottom-end',
//                 icon: 'error',
//                 title: "Terjadi kesalahan saat memuat data",
//                 showConfirmButton: false,
//                 timer: 1500
//             })
//             return;
//         }
//     });
// }

// $("#mk-jenis-diskon").change(function () {
//     if (this.value == "Harga Jual") {
//         $("#hjdis").removeClass("hidden")
//         // $("#umdis").addClass("hidden")
//     } else if (this.value == "Uang Muka") {
//         $("#hjdis").addClass("hidden")
//         // $("#umdis").removeClass("hidden")
//     }
//     sum_mktotal()
// })
$(
  "#add-form-isi-tagihan, #btn-ganti_nama, #add-form-btn-idk_keu, #btn-ganti_kavling",
).click(function (e) {
  e.preventDefault();
});

//sudah tidak di pakai
function save_isi_tagihan(e) {
  if (
    parseFloat(removeComma($("#total_cicilan_um").val() || 0)) > 0 ||
    parseFloat(removeComma($("#total_cicilan_bb").val() || 0)) > 0
  ) {
    if (
      $("#total_cicilan_um").val() != $("#mk-total_um").val() ||
      $("#total_cicilan_bb").val() != $("#mk-total_bb").val()
    ) {
      Swal.fire({
        //position: 'bottom-end',
        icon: "error",
        title: "Total Cicilan tidak sesuai dengan total biaya",
        showConfirmButton: false,
        timer: 1500,
      });
      return false;
    }
  }
  let dt = {};
  dt[csrfName] = csrfHash;
  $("form#fm-idk_keu :input").each(function () {
    dt[this.name] = this.value;
  });

  let i = 0;
  for (var k in data_um) {
    if (!data_um.hasOwnProperty(k)) continue;
    var obj = data_um[k];

    for (var d in obj) {
      if (!obj.hasOwnProperty(d)) continue;
      var x = obj[d];
      dt[d + "[" + i + "]"] = x;
    }
    i++;
  }
  i = 0;
  for (var k in data_bb) {
    if (!data_bb.hasOwnProperty(k)) continue;
    var obj = data_bb[k];

    for (var d in obj) {
      if (!obj.hasOwnProperty(d)) continue;
      var x = obj[d];
      dt[d + "[" + i + "]"] = x;
    }
    i++;
  }

  $.ajax({
    url: base_url + "Keuangan/isi_tagihan",
    type: "post",
    data: dt,
    dataType: "json",
    beforeSend: function () {
      $("#add-form-isi-tagihan").html(
        'Menyimpan <i class="fa fa-spinner fa-spin"></i>',
      );
      $("#add-form-isi-tagihan").addClass("disabled");
    },
    success: function (r) {
      csrfHash = r.token;

      if (r.success === true) {
        Swal.fire({
          //position: 'bottom-end',
          icon: "success",
          title: r.messages || r.message || "Data berhasil disimpan",
          showConfirmButton: false,
          timer: 1500,
        }).then(function () {
          // $('.modal').modal('hide');
          load_kavling();
          hapus_seleksi();
          $("#add-form-isi-tagihan").html("Simpan");
          $("#add-form-isi-tagihan").removeClass("disabled");
        });
      } else {
        Swal.fire({
          //position: 'bottom-end',
          icon: "error",
          title: r.messages || r.message || "Terjadi kesalahan",
          showConfirmButton: false,
          // timer: 1500
        }).then(function () {
          $("#add-form-isi-tagihan").html("Simpan");
          $("#add-form-isi-tagihan").removeClass("disabled");
        });
      }
    },
    error: function () {
      Swal.fire({
        //position: 'bottom-end',
        icon: "error",
        title: "terjadi kesalahan",
        showConfirmButton: false,
      }).then(function () {
        // $('.modal').modal('hide');
        $("#add-form-isi-tagihan").html("Simpan");
        $("#add-form-isi-tagihan").removeClass("disabled");
      });
      $("#add-form-isi-tagihan").html("Simpan");
      $("#add-form-isi-tagihan").removeClass("disabled");
    },
  });
}

$("#bt-for, #bt-for_bb").select2();
let keu_tg,
  keu_lp,
  keu_nom_ll,
  keu_nom_bb,
  keu_sb,
  keu_current_id_mkdt,
  keu_total_sudah_bayar = 0,
  keu_riwayat_loaded = false,
  keu_riwayat_loading = false;

function keuToNumber(value) {
  if (value === null || value === undefined || value === "") return 0;
  return parseFloat(String(value).replace(/,/g, "")) || 0;
}

function renderBiayaMkdt(biaya = {}) {
  $("#fm-keu-biaya-mkdt [data-biaya-mkdt]").each(function () {
    const key = $(this).data("biaya-mkdt");
    $(this).html("Rp. " + num_format(keuToNumber(biaya[key])));
  });
}

function loadKeuanganRiwayatLazy(done) {
  if (keu_riwayat_loaded) {
    loadLogPembayaran(keu_lp);
    if (typeof done === "function") done();
    return;
  }

  if (keu_riwayat_loading || !keu_current_id_mkdt) {
    if (typeof done === "function") done();
    return;
  }

  keu_riwayat_loading = true;
  $.ajax({
    url: base_url + "tagihan/riwayat/ambilsatu",
    type: "post",
    data: {
      [csrfName]: csrfHash,
      id_mkdt: keu_current_id_mkdt,
    },
    dataType: "json",
    success: function (r) {
      csrfHash = r.token;
      if (r.success === false) {
        loaded["keu_lp"] = false;
        swal("error", r.messages || "Riwayat pembayaran tidak ditemukan");
        return;
      }
      keu_lp = Array.isArray(r.log_pembayaran) ? r.log_pembayaran : [];
      keu_sb = keu_lp;
      keu_riwayat_loaded = true;
      loadLogPembayaran(keu_lp);
    },
    error: function (xhr, st, err) {
      loaded["keu_lp"] = false;
      swal("error", "Terjadi kesalahan saat memuat riwayat pembayaran", err);
    },
    complete: function () {
      keu_riwayat_loading = false;
      if (typeof done === "function") done();
    },
  });
}

function open_keuangan(sh, role, id_kavling) {
  loading(true);
  $("#tb-alokasi-dana").html("");

  loaded = [];
  keu_lp = [];
  keu_tg = [];
  keu_current_id_mkdt = sh.data.id_mkdt;
  keu_total_sudah_bayar = 0;
  keu_riwayat_loaded = false;
  keu_riwayat_loading = false;
  renderBiayaMkdt({});

  keu_sb = [];
  keu_nom_bb = 0;
  keu_nom_ll = 0;

  state.sisa_cicilan = 0;
  state.sudah_bayar = 0;
  state.total_cicilan = 0;

  $("#tagihan-tab").tab("show");
  $("#fm-keuangan")[0].reset(); //reset form
  $("#label_konsumen").html(""); //reset label nama
  $(
    "#tb-data-log_pembayaran, #tb-data-log_pembayaran_bb, #tb-data-tagihan, #tb-data-tagihan_bb",
  ).empty(); //reset table log
  $("#booking_fee_paid, #keu_booking_fee").prop("disabled", false); //set disabled false untuk input booking

  // document.querySelector("#keu_booking_tgl")._flatpickr._input.disabled = false; //set disabled false untuk input tanggal booking

  $("#hide_lunas").removeClass("hidden");
  $("#hide_refund").addClass("hidden");

  $("#is_lunas").prop("checked", false).change();

  $(".id_kavling").val(id_kavling);
  $("#fm-keuangan #id_mkdt").val(sh.data.id_mkdt);

  $("#add-form-btn-keuangan").prop("disabled", false);
  $("#keterangan_refund, #nominal_refund, #tanggal_refund, #refund_paid").prop(
    "disabled",
    0,
  );
  document.querySelector("#tanggal_refund")._flatpickr._input.disabled = false;

  $.ajax({
    url: base_url + "tagihan/ambilsatu",
    type: "post",
    data: {
      [csrfName]: csrfHash,
      // id_keuangan: sh.data.id_keuangan,
      // id_kavling: id_kavling,
      id_mkdt: sh.data.id_mkdt,
      include_log: 0,
      // id_hargajual: sh.data2.id_hargajual,
    },
    dataType: "json",
    beforeSend: function () {
      loading(true);
    },
    success: function (r) {
      loading(false);
      let mkdt = r.mkdt,
        sb = Array.isArray(r.log_pembayaran) ? r.log_pembayaran : [],
        lp = Array.isArray(r.log_pembayaran) ? r.log_pembayaran : [],
        disabled = "";
      tg = r.tagihan;
      csrfHash = r.token;
      keu_total_sudah_bayar = keuToNumber(r.total_sudah_bayar);
      renderBiayaMkdt(r.biaya_mkdt || mkdt || {});

      if (!Array.isArray(tg) || tg.length === 0) {
        Swal.fire({
          icon: "error",
          title: "Oops!",
          text: "Belum ada konsumen dan tagihannya",
          showConfirmButton: false,
        });
        return;
      }

      let nama_proyek = dt_proyek?.nama_proyek ?? sh.data.nama_proyek;

      //load label alamat
      let label_alamat = setLabelAlamat(
        nama_proyek,
        sh.data.nama_jalan,
        sh.data.no_kavling,
        sh.data2.no_tipe_rumah,
        sh.data2.tipe_rumah,
      );
      $(".label_alamat").html(label_alamat);

      //load label konsumen
      $("#fm-bayar-label_konsumen").html(mkdt.nama_konsumen);
      $("#fm-bayar-label_tgl").html(format_date(mkdt.booking_tgl));
      $("#fm-bayar-label_bookingfee").html(num_format(mkdt.booking_fee));

      $("#modal_divisi" + role).modal({
        backdrop: "static",
        keyboard: false,
      });

      //load detail biaya dari mkdt
      $("#label_konsumen").html(mkdt.nama_konsumen);

      if (mkdt) {
        $("#fm-keuangan #status_mkdt").val(mkdt.status_mkdt);

        //jika status batal
        if (mkdt.status_mkdt == "Batal") {
          $("#hide_lunas").addClass("hidden");
          $("#hide_refund").removeClass("hidden");
        }

        // console.log(hitung_total(true, mkdt));

        //matikan tombol simpan jika sudah refund
        if (mkdt.refund_paid == 1) {
          $("#add-form-btn-keuangan").prop("disabled", true);
          $("#hide_lunas").addClass("hidden");
          $(
            "#keterangan_refund, #nominal_refund, #tanggal_refund, #refund_paid",
          ).prop("disabled", 1);
          $("#fm-keuangan #refund_paid").prop("checked", 1);

          $("#keterangan_refund").val(mkdt.refund_keterangan).change();
          $("#nominal_refund").val(mkdt.refund).change();

          setDatePicker(mkdt.refund_tgl, "#tanggal_refund");
          document.querySelector("#tanggal_refund")._flatpickr._input.disabled =
            true;

          disabled = "disabled";
        }

        //matikan tombol simpan jika sudah lunas
        if (mkdt.is_lunas == 1) {
          $("#is_lunas").prop("checked", true);
          // $(".hide_lunas").addClass("hidden");
          // disabled = "disabled"
        }

        $("#fm-keuangan #nama_konsumen").val(mkdt.nama_konsumen);

        $("#bt-total_biaya_um")
          .val(
            parseFloat(mkdt.harga_uang_muka) -
              parseFloat(mkdt.harga_diskon_uang_muka),
          )
          .change();
        // $("#bt-total_biaya_um_ll")
        //   .val(
        //     parseFloat(mkdt.harga_penambahan_um) +
        //       parseFloat(mkdt.harga_penambahan) +
        //       parseFloat(mkdt.harga_penambahan_tanah) +
        //       parseFloat(mkdt.harga_administrasi)
        //   )
        //   .change();

        // $("#bt-total_biaya_bb")
        //   .val(
        //     parseFloat(mkdt.harga_bphtb) +
        //       parseFloat(mkdt.harga_biaya_proses) +
        //       parseFloat(mkdt.harga_ppn)
        //   )
        //   .change();

        //set checnkbox value to prevent being 0 by automatic load data
        $("#fm-keuangan #is_lunas").val(1);

        // setDatePicker(mkdt.booking_tgl, "#fm-keuangan #booking_tgl");
        // setDatePicker(mkdt.booking_tgl, "#keu_booking_tgl");

        $(".num").keyup().change();
        // total('#fm-keuangan');

        $("#fm-keuangan #booking_fee").val(mkdt.booking_fee).change();
        $("#keu_booking_fee").val(mkdt.booking_fee).change();

        //set booking paid/not
        // $("#booking_fee_paid").val(1);
        // if (mkdt.booking_paid == 1) {
        //   $("#booking_fee_paid, #keu_booking_fee").prop("disabled", true);
        //   document.querySelector(
        //     "#keu_booking_tgl"
        //   )._flatpickr._input.disabled = true;
        // }
      }
      //untuk load data sudah bayar
      keu_sb = sb;

      //load_table tagihan
      keu_tg = tg;
      state.total_cicilan = tg.reduce(
        (sum, item) => sum + parseInt(item.nominal, 10),
        0,
      );

      // /************************ load table log pembayaran ***************************/
      //   load table riwayat bayar
      keu_lp = lp;

      /************************ end of load table log pembayaran ***************************/

      loadTableTagihan(tg);
      loaded["keu_tg"] = true;

      removeModalListener("#modal_divisi3");
      initModalListener("#modal_divisi3");
    },
    error: function (xhr, st, err) {
      $("#loading").addClass("hidden");
      return swal("error", "Terjadi kesalahan saat memuat data", err);
    },
  });
}

function loadKeuSB(sb) {
  let nom = 0,
    tot = state.total_cicilan,
    sisa = 0,
    prs = 0;
  // nom_bb = 0,
  // tot_bb = removeComma($("#bt-total_biaya_bb").val()) || 0,
  // sisa_bb = 0,
  // prs_bb = 0,
  // nom_ll = 0,
  // // tot_ll = removeComma($("#bt-total_biaya_um_ll").val()) || 0,
  // sisa_ll = 0,
  // prs_ll = 0;

  if ((!Array.isArray(sb) || sb.length === 0) && keu_total_sudah_bayar > 0) {
    nom = keu_total_sudah_bayar;
    nom = nom > tot ? tot : nom;
    prs = nom == 0 || tot == 0 ? 0 : (nom / tot) * 100;

    return {
      total_sudah_bayar: nom,
      sisa_tagihan: sisa,
      persentase: prs.toFixed(2) + "%",
    };
  }

  $.each(sb, function (i, v) {
    if (v.payment_type != "Booking") {
      nom += parseFloat(v.nominal) || 0;

      // let sp = v.payment_type.split(";");
      // if (sp.includes("Uang Muka")) nom += parseFloat(v.nominal) || 0;
      // if (v.status == "UM") nom_ll += parseFloat(v.nominal) || 0;
      // else if (v.status == "BB") nom_bb += parseFloat(v.nominal) || 0;
    }
  });
  nom = nom > tot ? tot : nom;
  // sisa = tot - nom;
  // sisa_bb = tot_bb - nom_bb;

  prs = nom == 0 || tot == 0 ? 0 : (nom / tot) * 100;

  return {
    total_sudah_bayar: nom,
    sisa_tagihan: sisa,
    persentase: prs.toFixed(2) + "%",
  };

  // prs_bb = nom_bb == 0 ? 0 : (nom_bb / tot_bb) * 100;

  // $("#bt-sudah_bayar_um").val(nom).keyup();
  // $("#bt-sisa_tagihan_um").val(sisa).keyup();

  // $("#bt-persentase_bayar_tagihan_um").val(prs.toFixed(2) + "%");

  // $("#bt-sudah_bayar_bb").val(nom_bb).keyup();
  // $("#bt-sisa_tagihan_bb").val(sisa_bb).keyup();

  // $("#bt-persentase_bayar_tagihan_bb").val(prs_bb.toFixed(2) + "%");

  // keu_nom_bb = nom_bb;
  // keu_nom_ll = nom_ll;
}

function loadTableTagihan(tg) {
  let sudah_bayar = loadKeuSB(keu_sb);
  state.sudah_bayar = sudah_bayar.total_sudah_bayar;
  // if (!loaded["keu_sb"]) {
  //   sudah_bayar =  loadKeuSB(keu_sb);
  // }
  $("#tb-data-tagihan").html("");

  let tr_tg = "",
    no = 1,
    tot_tg = 0,
    sb_button = "",
    chkd = "",
    opt = "",
    dsb = "",
    disabled = "";
  $("#bt-for").html("");
  $.each(tg, function (i, v) {
    chkd = "";
    dsb = "";

    if (v.sudah_dibayar == 1) {
      chkd = "checked";
      // dsb = "disabled"
    }
    sb_button = `
        <div class="form-group">
            <div class="custom-control custom-switch custom-control-inline">
                <input type="checkbox" ${chkd} onchange="save_sb(${v.id_keuangan})" class="custom-control-input " ${disabled} id="sb_btn${v.id_keuangan}" name="sb_btn[${v.id_keuangan}]" value="1" />
                <label class="custom-control-label" for="sb_btn${v.id_keuangan}"></label>
            </div>
        </div>`;

    tot_tg += parseInt(v.nominal);
    tr_tg += `
        <div class="p-1 mb-1 rounded border" style="">
          <div class="row">
            <div class="col-9">
                <h5 class="text-primary"><strong>${v.berita_acara}</strong></h5>
                <h5 class="text-success"><strong>Rp. ${num_format(
                  v.nominal,
                )}</strong></h5>
                <small class="muted">Jatuh Tempo: ${format_date(
                  v.jatuh_tempo_tgl,
                )}</small>
            </div>
            <div class="col-3 text-right">
              ${sb_button}
            </div>
          </div>
        </div>
    `;
    no++;

    opt += `<option ${dsb} value='${v.id_keuangan}'>${v.berita_acara}</option>`;
  });

  tr_tg += `
      <div class="p-1 mb-1 rounded border" style="background-color: #f1f1f1ff;">
          <div class="row">
            <div class="col-4">
                <h5>Total</h5>
            </div>
            <div class="col-8 text-right">
                <h5 class="text-success text-right"><strong>Rp. ${num_format(
                  tot_tg,
                )}</strong></h5>
            </div>
            <div class="col-4">
                <h5>Sudah Bayar</h5>
            </div>
            <div class="col-8 text-right">
                <h5 class="text-primary text-right"><strong>Rp. ${num_format(
                  sudah_bayar.total_sudah_bayar,
                )}</strong></h5>
            </div>
            <div class="col-4">
                <h5>Sisa Tagihan</h5>
            </div>
            <div class="col-8 text-right">
                <h5 class="text-danger text-right"><strong>Rp. ${num_format(
                  tot_tg - sudah_bayar.total_sudah_bayar,
                )}</strong></h5>
            </div>
          </div>
        </div>
     `;

  $("#bt-for").append(opt);
  //   $("#bt-for_bb").append(opt);

  $("#tb-data-tagihan").append(tr_tg);
  //   $("#tb-data-tagihan_bb").append(tr_tg_bb);
}

function loadLogPembayaran(lp) {
  if (!loaded["keu_sb"]) {
    loadKeuSB(keu_sb);
  }
  $("#tb-data-log_pembayaran").html("");
  let t = "",
    tot_lp = 0,
    no = 1;

  $.each(lp, function (k, v) {
    //set tgl & booking fee yang diinput oleh keuangan
    // if (v.payment_type == "Booking") {
    //   $("#keu_booking_fee").val(v.nominal).keyup();
    //   setDatePicker(v.tanggal_bayar, "#keu_booking_tgl");
    // }
    let detail = v.detail;
    let item = "";
    $.each(detail, function (k2, v2) {
      item += `<strong>${v2.item}</strong>: Rp. ${num_format(v2.nominal)}<br>`;
    });

    tot_lp += parseInt(v.nominal);
    t += `
      <tr>
        <td>${no}</td>
        <td>${format_date(v.tanggal_bayar)}</td>
        <td style="text-align:right">${num_format(v.nominal)}</td>
        <td class="text-left">Untuk Pembayaran: ${
          v.payment_type
        }<br>Dengan Detail: <br>${item}</td>
        <td>
          ${v.username}<br/>
          ${format_datetime(v.created_at)}
        </td>
        <td>
          <div class="btn-group">
            <button
              type="button"
              class="btn btn-outline-primary waves-effect btn-sm"
              onclick="printRiwayatBayar('${v.id_pembayaran}', '${
                v.id_mkdt
              }', '${dt_proyek["id_proyek"]}')"
            >
              <i class="fa fa-print"></i>
            </button>

            <button
              type="button"
              class="btn btn-outline-danger waves-effect btn-sm"
              onclick="removeRiwayatBayar('${v.id_pembayaran}')"
            >
              <i class="fa fa-trash"></i>
            </button>
          </div>
        </td>
      </tr>
    `;
    no++;
  });

  t +=
    "<tr>" +
    "<th colspan='2'>Total</th>" +
    "<th style='text-align:right'>" +
    num_format(tot_lp) +
    "</th>" +
    "<th colspan='3'></th>" +
    "<tr>";

  $("#tb-data-log_pembayaran").append($(t).hide().slideDown(2000));
}

function printRiwayatBayar(ee, e2, e3) {
  $("#btnDetail").off("click");
  $("#btnUangMuka").off("click");
  Swal.fire({
    title: "Pilih Jenis Kuitansi",
    text: "Silakan pilih jenis cetakan yang ingin dibuat:",
    showCancelButton: true,
    showConfirmButton: false,
    cancelButtonText: "Batal",
    html: `
    <div class="d-grid gap-2">
      <button id="btnDetail" class="swal2-confirm swal2-styled" style="background:#0d6efd">
        Kuitansi Detail
      </button>
      <button id="btnUangMuka" class="swal2-confirm swal2-styled" style="background:#198754">
        Kuitansi Uang Muka
      </button>
    </div>
  `,
  });
  $("#btnDetail").on("click", function () {
    Swal.close();
    var myWindow = window.open(
      base_url + `pembayaran/kuitansi/cetak?e=${ee}&e2=${e2}&e3=${e3}`,
      "_blank",
      "top=100,left=300,width=700,height=600",
    );
    setTimeout(function () {
      myWindow.focus();
    }, 1000);
  });
  $("#btnUangMuka").on("click", function () {
    Swal.close();
    var myWindow = window.open(
      base_url + `pembayaran/kuitansi-um/cetak?e=${ee}&e2=${e2}&e3=${e3}`,
      "_blank",
      "top=100,left=300,width=700,height=600",
    );
    setTimeout(function () {
      myWindow.focus();
    }, 1000);
  });
}

function removeRiwayatBayar(e) {
  Swal.fire({
    title: "Hapus Data?",
    text: "Apakah anda yakin akan menghapus data?",
    // type: 'warning',
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Ya!",
    confirmButtonClass: "btn btn-primary",
    cancelButtonClass: "btn btn-danger ml-1",
    buttonsStyling: !1,
  }).then(function (t) {
    if (t.value) {
      $.ajax({
        url: base_url + "pembayaran/hapus",
        type: "post",
        data: {
          [csrfName]: csrfHash,
          id_pembayaran: e,
        },
        dataType: "json",
        beforeSend: function () {
          $("#loading").removeClass("hidden");
        },
        success: function (r) {
          $("#loading").addClass("hidden");
          csrfHash = r.token;
          if (r.success) {
            Swal.fire({
              //position: 'bottom-end',
              icon: "success",
              title: r.messages,
              showConfirmButton: false,
              timer: 1500,
            }).then(function () {
              isi_data();
            });
          } else {
            Swal.fire({
              //position: 'bottom-end',
              icon: "error",
              title: r.messages,
              showConfirmButton: false,
              timer: 1500,
            });
          }
        },
        error: function (e) {
          $("#loading").addClass("hidden");
          Swal.fire({
            //position: 'bottom-end',
            icon: "error",
            title: "Terjadi Kesalahan",
            showConfirmButton: true,
            // timer: 1500
          });
        },
      });
    }
  });
}

function save_keuangan(e = "") {
  let nominal = removeComma($("#bt-bayar_tagihan_um").val());
  let tanggal = $("#bt-tanggal_bayar_um").val();
  let metode = $("#bt-for").val();
  if (metode == "") {
    Swal.fire({
      icon: "warning",
      title: "Peringatan",
      text: "Angsuran belum diisi",
      showConfirmButton: false,
      timer: 1500,
    }).then(() => {
      $("#bt-for").focus();
    });
    return;
  }
  if (tanggal == "") {
    Swal.fire({
      icon: "warning",
      title: "Peringatan",
      text: "Tanggal pembayaran belum diisi",
      showConfirmButton: false,
      timer: 1500,
    }).then(() => {
      $("#bt-tanggal_bayar_um").focus();
    });
    return;
  }
  if (nominal == 0 || nominal == "") {
    Swal.fire({
      icon: "warning",
      title: "Peringatan",
      text: "Nominal pembayaran belum diisi",
      showConfirmButton: false,
      timer: 1500,
    }).then(() => {
      $("#bt-bayar_tagihan_um").focus();
    });
    return;
  }

  let total = 0;
  $(".item-alokasi").each(function () {
    total += removeComma($(this).val());
  });

  if (total != nominal) {
    Swal.fire({
      icon: "warning",
      title: "Peringatan",
      text: "Total alokasi tidak sesuai dengan nominal pembayaran",
      showConfirmButton: false,
      timer: 1500,
    }).then(() => {
      $("#bt-bayar_tagihan_um").focus();
    });
    return;
  }

  Swal.fire({
    title: "Simpan Data?",
    text: "",
    type: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Ya!",
    confirmButtonClass: "btn btn-primary",
    cancelButtonClass: "btn btn-danger ml-1",
    buttonsStyling: !1,
  }).then(function (t) {
    if (t.value) {
      let text_um = [];

      $("#bt-for option:selected").each(function () {
        text_um.push($(this).text()); // Get the text of the selected option
      });

      // Join the texts with semicolon
      text_um = text_um.join(";");
      text_um = text_um != "" ? text_um + ";" : text_um;

      let text_bb = [];

      $("#bt-for_bb option:selected").each(function () {
        text_bb.push($(this).text()); // Get the text of the selected option
      });

      // Join the texts with semicolon
      text_bb = text_bb.join(";");

      text_bb = text_bb != "" ? text_bb + ";" : text_bb;

      $.ajax({
        url: base_url + "pembayaran/simpan",
        type: "post",
        data:
          $("#fm-keuangan").serialize() +
          "&" +
          csrfName +
          "=" +
          csrfHash +
          "&text_um=" +
          text_um +
          "&text_bb=" +
          text_bb +
          "&e=" +
          e +
          "&cis_lunas=" +
          $("#is_lunas").prop("checked"),
        dataType: "json",
        beforeSend: function () {
          simpanBtn(".add-form-btn-keuangan", true);
        },
        success: function (r) {
          csrfHash = r.token;
          if (r.status === true) {
            swal("success", r.message);

            if (typeof isi_data === "function") {
              isi_data(); // Panggil jika ada
            } else {
              $(".modal").modal("hide");
            }
          } else {
            swal("error", r.message || r.messages || "Terjadi kesalahan");
          }
          simpanBtn(".add-form-btn-keuangan", false);

          // load_kavling();
          // hapus_seleksi();
        },
        error: function (e, f, g) {
          simpanBtn(".add-form-btn-keuangan", false);
          swal("error", g);
        },
      });
    } else return false;
  });
}
function badgeStatus(s) {
  return s == 1
    ? '<span class="badge badge-success">Sudah Cair</span>'
    : '<span class="badge badge-secondary">Belum Cair</span>';
}

/* ************************ dana akad ************************ */
function dana_akad() {
  $("#fm-dana_akad")[0].reset();
  $("#da-jaminan_here").html("");
  $("#da-cair_jaminan_here").html("");
  if (!editdtt[0]) {
    return swal("error", "Tidak ada kavling yang dipilih");
  }

  var role,
    sh = editdtt[0],
    id_kavling = sh.id.substr(3);

  if (!sh.data.id_mkdt) {
    return swal(
      "error",
      "Terjadi kesalahan",
      "Belum ada data konsumen di kavling" +
        sh.data.nama_jalan +
        ", No." +
        sh.data.no_kavling,
    );
  }

  if (sh.data2.status_mkdt != "Akad") {
    return swal(
      "error",
      "Terjadi kesalahan",
      "Kavling" +
        sh.data.nama_jalan +
        ", No." +
        sh.data.no_kavling +
        "Belum Akad!",
    );
  }

  $.ajax({
    url: base_url + "keuangan/getDanaAkad",
    type: "post",
    data: {
      [csrfName]: csrfHash,
      id_kavling: id_kavling,
      id_mkdt: sh.data.id_mkdt,
    },
    dataType: "json",
    beforeSend: function () {
      $("#loading").removeClass("hidden");
    },
    success: function (r) {
      $("#loading").addClass("hidden");
      csrfHash = r.token;
      let ld = r.list_dajam;

      $("#da-id_mkdt").val(sh.data.id_mkdt);
      $("#da-id_kavling").val(sh.id.substr(3));

      let dv = "",
        dvc = "";
      let id_list_dajam = "";
      let kpr_acc = r.mkdt.harga_kpr_acc ? r.mkdt.harga_kpr_acc : 0;
      let hasil_akad = parseFloat(kpr_acc);
      let id_dajam = "";
      let z = 0;
      let sc = "";
      let dis = "";

      $("#da-dajam_selesai").prop("checked", parseInt(r.mkdt.dajam_selesai)); //set chceked status selesai
      changeVal("#da-kpr_acc", kpr_acc);

      dv = `
      <table class="datatables-basic table compact">
      <thead>
        <tr>
          <th>Nama Jaminan</th>
          <th>Nilai Jaminan</th>
          <th>Sudah Cair</th>
          <th>Tanggal Cair</th>
          <th>Keterangan</th>
        </tr>
      </thead>
      <tbody>
      `;
      $.each(ld, function (i, v) {
        // --- Bagian Logika (Tidak Berubah) ---
        hasil_akad += parseFloat(v.nominal ? v.nominal : 0);
        id_list_dajam = v.id_list_dajam ? v.id_list_dajam : v.id_list_dajam_ori;
        id_dajam = v.id == null ? "n" + z : v.id;
        sc = v.sudah_cair == 1 ? "checked" : "";
        dis = v.sudah_cair == 1 ? "" : "disabled";

        // --- Bagian Tampilan (Diubah ke Table Row) ---
        dv += `
        <tr>
            <td style="vertical-align: middle;">
                <strong>${v.nama_jaminan}</strong>
            </td>

            <td style="vertical-align: middle;">
                <input type="hidden" value="${id_list_dajam}" id="da-id_dajam[${id_dajam}][id_list_dajam]" name="id_dajam[${id_dajam}][id_list_dajam]" />

                <input type="text" value="${v.nominal ? v.nominal : 0}"
                      id="da-id_dajam[${id_dajam}][nominal]"
                      name="id_dajam[${id_dajam}][nominal]"
                      class="form-control num daf"
                      onchange="hitung_dana_akad()"/>

                <input type="hidden" ${dis} value="${
                  v.nominal_cair ? v.nominal_cair : 0
                }"
                      id="da-id_dajam[${id_dajam}][nominal_cair]"
                      name="id_dajam[${id_dajam}][nominal_cair]"
                      class="form-control num cl${id_dajam}" />
            </td>

            <td style="vertical-align: middle; text-align: center;">
                <input type="checkbox" ${sc} value="1"
                      id="da-id_dajam[${id_dajam}][sudah_cair]"
                      name="id_dajam[${id_dajam}][sudah_cair]"
                      onclick="is_cair(this, '${id_dajam}')"
                      style="transform: scale(1.5); margin-top: 5px;"/>
            </td>

            <td style="vertical-align: middle;">
                <input ${dis} type="text" value="${
                  v.tgl_cair ? v.tgl_cair : ""
                }"
                      id="da-id_dajam[${id_dajam}][tgl_cair]"
                      name="id_dajam[${id_dajam}][tgl_cair]"
                      class="form-control flatpickr-human-friendly fp-dajam cl${id_dajam}" />
            </td>

            <td style="vertical-align: middle;">
                <textarea rows="2" ${dis}
                          class="form-control cl${id_dajam}"
                          id="da-id_dajam[${id_dajam}][keterangan]"
                          name="id_dajam[${id_dajam}][keterangan]">${
                            v.keterangan == null ? "" : v.keterangan
                          }</textarea>
            </td>
        </tr>
        `;
        z++;
      });
      dv += "</tbody></table>";
      console.log(dv);
      $("#da-jaminan_here").append(dv);

      if (r.list_pengajuan.length > 0) {
        const tb = document.querySelector("#tbl-riwayat tbody");
        tb.innerHTML = "";
        let i = 0;
        $.each(r.list_pengajuan, function (k, v) {
          // console.log(v);
          const tr = document.createElement("tr");
          tr.innerHTML = `
            <td>${i + 1}</td>
            <td>${v.created_by ?? ""}</td>
            <td>${v.tanggal_pengajuan ?? ""}</td>
            <td>${v.tanggal_cair ?? ""}</td>
            <td>${(v.keterangan || "").replace(/\n/g, "<br>")}</td>
            <td data-status="${v.status_cair}">${badgeStatus(
              v.status_cair,
            )}</td>
            <td>
              ${
                v.access_url
                  ? `<a href="${v.access_url}" target="_blank" class="btn btn-link btn-sm">Lihat Surat</a>`
                  : "-"
              }
            </td>
            <td>
              <button class="btn btn-sm ${
                v.status_cair == 1 ? "btn-warning" : "btn-success"
              } btn-toggle" data-id="${r.id}">
                ${v.status_cair == 1 ? "Tandai Belum" : "Tandai Sudah"}
              </button>
            </td>
          `;
          tb.appendChild(tr);
          i++;
        });
      }

      // $("#da-cair_jaminan_here").append(dvc)

      flatpickr(".fp-dajam", {
        altInput: true,
        altFormat: "F j, Y",
        dateFormat: "Y-m-d",
      });

      changeVal("#da-hasil_akad", hasil_akad);
      $("#fm-dana_akad .num").keyup();

      hitung_dana_akad();

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
      $("#dana_akad_modal").modal({
        backdrop: "static",
        keyboard: false,
      });
    },
    error: function (a, b, c) {
      $("#loading").addClass("hidden");
      return swal("error", "Terjadi kesalahan", c);
    },
  });
}

function hitung_dana_akad() {
  let total = parseFloat(removeComma($("#da-kpr_acc").val())) || 0;
  let total_dajam = 0;
  $(".daf").each(function () {
    total_dajam += parseFloat(removeComma($(this).val())) || 0;
  });
  changeVal("#da-total_dajam", total_dajam);
  changeVal("#da-hasil_akad", total - total_dajam);
}

function is_cair(e, id) {
  let is_true = $(e).prop("checked");
  $(`.cl${id}`).prop("disabled", !is_true);
}

function save_dana_akad() {
  $.ajax({
    url: base_url + "keuangan/saveDanaAkad",
    type: "post",
    data: $("#fm-dana_akad").serialize() + "&" + csrfName + "=" + csrfHash,
    dataType: "json",
    beforeSend: function () {
      simpanBtn("#add-form-btn-dana_akad", true);
    },
    success: function (r) {
      csrfHash = r.token;
      if (r.success === true) {
        swal("success", r.messages || r.message || "Data berhasil disimpan");
        $(".modal").modal("hide");
        simpanBtn("#add-form-btn-dana_akad", false);
      } else {
        swal("error", "Terjadi kesalahan", r.messages || r.message || "Terjadi kesalahan");
        simpanBtn("#add-form-btn-dana_akad", false);
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
      simpanBtn("#add-form-btn-dana_akad", false);
    },
  });
}

function getRiwayatGantinama() {
  if (!editdtt[0]) {
    return swal("error", "Tidak ada kavling yang dipilih");
  }
  let sh = editdtt[0];

  $.ajax({
    url: base_url + "keuangan/get_riwayat_gantinama",
    type: "post",
    data: {
      [csrfName]: csrfHash,
      id_mkdt: sh.data.id_mkdt,
    },
    dataType: "json",
    beforeSend: function () {
      simpanBtn(
        "#btn-refresh-ganti_nama",
        true,
        'Sedang Memuat <i class="fa fa-spinner fa-spin"></i>',
        "Muat ulang riwayat",
      );
    },
    success: function (x) {
      csrfHash = x.token;

      let tb = "<tr><td colspan = 2> Tidak Ada Data</td></tr>";
      if (x.riwayat) {
        tb = "";
        let n = 1;
        x.riwayat.forEach((e) => {
          tb += `
                    <tr>
                            <td>${n}</td>
                            <td>
                                ${
                                  e.file_spptb_access_url
                                    ? `<a href="${e.file_spptb_access_url}" target=_blank class="btn btn-outline-primary">Klik untuk melihat file SPPTB Seblumnya</a>`
                                    : "-"
                                }
                            </td>
                            <td>
                                -
                            </td>
                        </tr>"`;
          n++;
        });
      }

      $("#riwayat_ganti_nama-here").html(tb);

      simpanBtn(
        "#btn-refresh-ganti_nama",
        false,
        'Sedang Memuat <i class="fa fa-spinner fa-spin"></i>',
        "Muat ulang riwayat",
      );
    },
    error: function (xhr, st, err) {
      simpanBtn(
        "#btn-refresh-ganti_nama",
        false,
        'Sedang Memuat <i class="fa fa-spinner fa-spin"></i>',
        "Muat ulang riwayat",
      );
      return swal("error", err);
    },
  });
}

$(".modal").on("hidden.bs.modal", function () {
  data_um = {};
  data_bb = {};
});

$("#idk_riwayat-tab").click(function () {
  getRiwayatGantinama();
});

$("#btn-saveDanaJaminan").click(function (e) {
  e.preventDefault();
  savePengajuanDajam();
});
function savePengajuanDajam() {
  const btnSave = "#add-form-btn-idk_keu";
  // updateButtons(btnSave, "#prev-form-btn-idk_keu");

  if (parseFloat(removeComma($("#mk-total_cicilan_um").val() || 0)) > 0) {
    if ($("#mk-total_tot").val() != $("#mk-total_cicilan_um").val()) {
      return swal(
        "error",
        "Gagal Menyimpan Data",
        "Total tagihan dan total yang harus dibayar tidak sesuai",
      );
    }
  }

  let dt = {};
  dt[csrfName] = csrfHash;
  $("form#fm-idk_keu :input").each(function () {
    dt[this.name] = this.value;
  });

  let i = 0;
  //cicilan um

  let form = $("#fm-idk_keu")[0];
  let fd = new FormData(form);
  fd.append(csrfName, csrfHash);
  let is_ganti_nama = false;

  if (is_ganti_nama) {
    fd.append("id_mkdt_old", id_mkdt_old);
    fd.append("id_konsumen_old", id_konsumen_old);
    fd.append("is_ganti_nama", is_ganti_nama);
  }

  appendCollectionToFormData(fd, state.data_um);

  // for (var k in state.data_um) {
  //   if (!data_um.hasOwnProperty(k)) continue;
  //   var obj = state.data_umk[k];

  //   for (var d in obj) {
  //     if (!obj.hasOwnProperty(d)) continue;
  //     let x = obj[d];

  //     dt[d + "[" + i + "]"] = is_ganti_nama == "Ganti Nama" ? "" : x;
  //     fd.append(`${d}[${i}]`, x);
  //   }
  //   i++;
  // }

  // console.log(dt)
  // fd.forEach((value, key) => {
  //   console.log(key, value);
  // });
  // return
  //cicilan bb
  // i = 0;
  // for (var k in data_bb) {
  //   if (!data_bb.hasOwnProperty(k)) continue;
  //   var obj = data_bb[k];

  //   for (var d in obj) {
  //     if (!obj.hasOwnProperty(d)) continue;
  //     var x = obj[d];
  //     dt[d + "[" + i + "]"] = is_ganti_nama == "Ganti Nama" ? "" : x;
  //     fd.append(`${d}[${i}]`, x);
  //   }
  //   i++;
  // }

  $.ajax({
    url: base_url + "api/transaksi/simpan",
    type: "post",
    contentType: false,
    processData: false,
    data: fd,
    dataType: "json",
    beforeSend: function () {
      simpanBtn(btnSave, true);
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
        }).then(function () {
          removeModalListener("#modal-isi_data_konsumen");
          $(".modal").modal("hide");
          simpanBtn(btnSave, false);

          load_kavling();
          hapus_seleksi();
        });
      } else {
        Swal.fire({
          //position: 'bottom-end',
          icon: "error",
          title: r.messages,
          showConfirmButton: false,
          timer: 1500,
        }).then(function () {
          simpanBtn(btnSave, false);
        });
      }
    },
    error: function (e) {
      Swal.fire({
        //position: 'bottom-end',
        icon: "error",
        title: "Terjadi kesalahan",
        showConfirmButton: true,
        // timer: 1500
      }).then(function () {
        simpanBtn(btnSave, false);
      });
    },
  });
}

/****************************** end of dana akad ****************************************/
/****************************** end of keunagan ****************************************/
/****************************** Cash Out ****************************************/
var co = [];
$("#co-untuk_pembayaran").select2({
  placeholder: "Pilih Item Pembayaran",
  allowClear: true,
  ajax: {
    url: base_url + "keuangan/cashout/listitem/ambil",
    dataType: "json",
    delay: 250,
    method: "post",
    data: function (params) {
      return {
        [csrfName]: csrfHash,
        search: params.term,
      };
    },
    processResults: function (r) {
      csrfHash = r.token;

      let results = [];
      $.each(r.list_item, function (k, v) {
        results.push({
          id: v.id,
          text: v.item,
        });
      });

      return {
        results: results,
      };
    },
    cache: false,
  },
});
function hapus_cashout(id) {
  Swal.fire({
    title: "Hapus Data?",
    text: "",
    type: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Ya!",
    confirmButtonClass: "btn btn-primary",
    cancelButtonClass: "btn btn-danger ml-1",
    buttonsStyling: !1,
  }).then(function (t) {
    if (t.value) {
      let sbtn = ".co-del-btn";
      $.ajax({
        url: base_url + "keuangan/cashout/delete",
        type: "post",
        data: {
          [csrfName]: csrfHash,
          id: id,
        },
        dataType: "json",
        beforeSend: function () {
          simpanBtn(sbtn, true, '<i class="fa fa-spinner fa-spin"></i>');
        },
        success: function (r) {
          csrfHash = r.token;
          if (r.success === true) {
            swal("success", r.messages);
            simpanBtn(sbtn, false, "", '<i class="fa fa-trash"></i>');
            isi_cashout(r.id_kavling);
          } else {
            swal("error", "Terjadi kesalahan", r.messages);
            simpanBtn(sbtn, false, "", '<i class="fa fa-trash"></i>');
          }
        },
        error: function (r) {
          Swal.fire({
            //position: 'bottom-end',
            icon: "error",
            title: "terjadi kesalahan",
            showConfirmButton: false,
            // timer: 1500
          });
          simpanBtn(sbtn, false, "", '<i class="fa fa-trash"></i>');
        },
      });
    }
  });
}
function isi_cashout(id_kav = null) {
  if (!editdtt[0] && !id_kav) {
    return swal("error", "Tidak ada kavling yang dipilih");
  }

  var sh = editdtt[0],
    id_kavling = id_kav ?? sh.id.substr(3);

  co = [];

  $("#fm-cashout-keu")[0].reset();
  $("#cashout-table tbody").html("");
  $.ajax({
    url: base_url + "keuangan/cashout/ambil",
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
      let d = r.riwayat_bayar;

      if (d.length == 0) {
        $("#cashout-table tbody").html(
          "<tr><td colspan='5' class='text-center'>Data tidak ditemukan</td></tr>",
        );
      } else {
        $.each(d, function (index, val) {
          let btn = `<button type="button" class="btn btn-danger btn-sm co-del-btn" onclick="hapus_cashout(${val.id})"><i class="fa fa-trash"></i></button>`;
          let row = `
        <tr>
            <td>${btn}</td>
            <td>${val.item}</td>
            <td>${format_date(val.tanggal_bayar) ?? "-"}</td>
            <td>${num_format(val.nominal) ?? "0"}</td>
            <td>${val.keterangan ?? "-"}</td>
        </tr>`;
          $("#cashout-table tbody").append(row);
        });
      }

      $("#cashout-id_kavling").val(id_kavling);

      let nama_proyek = dt_proyek?.nama_proyek ?? sh.data.nama_proyek;
      let label_alamat = setLabelAlamat(
        nama_proyek,
        sh.data.nama_jalan,
        sh.data.no_kavling,
        sh.data2.no_tipe_rumah,
        sh.data2.tipe_rumah,
      );
      $(".label_alamat").html(label_alamat);

      // load label konsumen
      $("#fm-co-la bel_konsumen").html(r.konsumen.nama_konsumen);
      $("#fm-co-label_tgl").html(format_date(r.konsumen.booking_tgl));
      $("#fm-co-label_bookingfee").html(num_format(r.konsumen.harga_jual));
      initModalListener("#modal-cashout-keu");
      $("#modal-cashout-keu").modal({
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

function save_cashout() {
  if ($("#co-untuk_pembayaran").val() == null) {
    return swal("error", "Item pembayaran harus diisi");
  }
  if ($("#co-tanggal_bayar").val() == "") {
    return swal("error", "Tanggal pembayaran harus diisi");
  }
  if ($("#co-nominal").val() == "" || $("#co-nominal").val() <= 0) {
    return swal("error", "Nominal pembayaran harus diisi");
  }

  Swal.fire({
    title: "Simpan Data?",
    text: "",
    type: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Ya!",
    confirmButtonClass: "btn btn-primary",
    cancelButtonClass: "btn btn-danger ml-1",
    buttonsStyling: !1,
  }).then(function (t) {
    if (t.value) {
      let sbtn = "#add-form-btn-cashout";
      $.ajax({
        url: base_url + "keuangan/cashout/save",
        type: "post",
        data:
          $("#fm-cashout-keu").serialize() + "&" + csrfName + "=" + csrfHash,
        dataType: "json",
        beforeSend: function () {
          simpanBtn(sbtn, true);
        },
        success: function (r) {
          csrfHash = r.token;
          if (r.success === true) {
            swal("success", r.messages);
            simpanBtn(sbtn, false);
            isi_cashout(r.id_kavling);
          } else {
            swal("error", "Terjadi kesalahan", r.messages);
            simpanBtn(sbtn, false);
          }
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
  });
}

/****************************** End Of Cash Out ****************************************/

(function () {
  function badgeStatus(s) {
    return s == 1
      ? '<span class="badge badge-success">Sudah Cair</span>'
      : '<span class="badge badge-secondary">Pengajuan</span>';
  }

  async function loadRiwayat() {
    if (!idKavling) return;
    const res = await fetch(`${base_url}pencairan/list/${idKavling}`);
    const js = await res.json();
    const tb = document.querySelector("#tbl-riwayat tbody");
    tb.innerHTML = "";
    js.data.forEach((r, i) => {
      const tr = document.createElement("tr");
      tr.innerHTML = `
        <td>${i + 1}</td>
        <td>${r.tanggal_pengajuan ?? ""}</td>
        <td>${(r.keterangan || "").replace(/\n/g, "<br>")}</td>
        <td data-status="${r.status_cair}">${badgeStatus(r.status_cair)}</td>
        <td>
          ${
            r.access_url
              ? `<a href="${r.access_url}" target="_blank" class="btn btn-link btn-sm">Lihat PDF</a>
                            <a href="${r.download_url}" class="btn btn-outline-secondary btn-sm">Unduh</a>`
              : "-"
          }
        </td>
        <td>
          <button class="btn btn-sm ${
            r.status_cair == 1 ? "btn-warning" : "btn-success"
          } btn-toggle" data-id="${r.id}">
            ${r.status_cair == 1 ? "Tandai Belum" : "Tandai Sudah"}
          </button>
        </td>
      `;
      tb.appendChild(tr);
    });
    bindToggle();
  }

  function bindToggle() {
    document.querySelectorAll(".btn-toggle").forEach((btn) => {
      btn.addEventListener("click", async (e) => {
        e.preventDefault();
        const id = btn.getAttribute("data-id");
        btn.disabled = true;
        try {
          const res = await fetch(`${base_url}pencairan/toggle/${id}`, {
            method: "POST",
          });
          const js = await res.json();
          if (js.success) {
            await loadRiwayat();
          } else {
            alert(js.message || "Gagal memperbarui status");
          }
        } catch (err) {
          alert("Error koneksi.");
        } finally {
          btn.disabled = false;
        }
      });
    });
  }
  // init
  loadRiwayat();
})();

</script>
