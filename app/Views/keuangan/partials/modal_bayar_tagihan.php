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

    #modal_divisi3 .keu-pay-form-sticky {
        position: sticky;
        top: 0;
        z-index: 4;
    }

    #modal_divisi3 .keu-pay-form-sticky .card-body {
        background: #fff;
        border-radius: 8px;
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

    #modal_divisi3 .keu-payment-summary {
        background: #fff;
        border: 1px solid #cfd6e3;
        border-radius: 8px;
        box-shadow: 0 8px 18px rgba(15, 23, 42, .06);
        margin-top: 1rem;
        padding: 1.35rem;
        position: relative;
    }

    #modal_divisi3 .keu-payment-summary-header {
        align-items: center;
        color: #344054;
        display: flex;
        font-size: .98rem;
        font-weight: 700;
        gap: .75rem;
        margin-bottom: 1.15rem;
    }

    #modal_divisi3 .keu-payment-summary-icon {
        align-items: center;
        background: #205792;
        border-radius: 10px;
        color: #dbeafe;
        display: inline-flex;
        flex: 0 0 44px;
        height: 44px;
        justify-content: center;
        width: 44px;
    }

    #modal_divisi3 .keu-payment-percent {
        color: #003b78;
        font-size: 1.1rem;
        font-weight: 700;
        position: absolute;
        right: 1.35rem;
        top: 1.35rem;
    }

    #modal_divisi3 .keu-payment-primary-label {
        color: #344054;
        font-size: .86rem;
        font-weight: 700;
        margin-bottom: .35rem;
    }

    #modal_divisi3 .keu-payment-primary-value {
        color: #c40000;
        font-size: 1.65rem;
        font-weight: 900;
        line-height: 1.18;
        margin-bottom: 1.2rem;
    }

    #modal_divisi3 .keu-payment-metric-row {
        align-items: center;
        display: flex;
        gap: 1rem;
        justify-content: space-between;
        margin-bottom: .75rem;
    }

    #modal_divisi3 .keu-payment-metric-label {
        color: #344054;
        font-size: .92rem;
    }

    #modal_divisi3 .keu-payment-metric-value {
        color: #020617;
        font-weight: 800;
        text-align: right;
        white-space: nowrap;
    }

    #modal_divisi3 .keu-payment-metric-value.is-paid {
        color: #006b35;
    }

    #modal_divisi3 .keu-payment-progress-track {
        background: #e7eefb;
        border-radius: 999px;
        height: 9px;
        margin: .9rem 0 1.2rem;
        overflow: hidden;
        width: 100%;
    }

    #modal_divisi3 .keu-payment-progress-fill {
        background: #4ade80;
        border-radius: inherit;
        height: 100%;
        transition: width .2s ease;
        width: 0%;
    }

    #modal_divisi3 .keu-payment-progress-fill.is-partial {
        background: #2563eb;
    }

    #modal_divisi3 .keu-payment-progress-fill.is-empty {
        background: transparent;
    }

    #modal_divisi3 .keu-payment-detail-title {
        align-items: center;
        color: #6b7280;
        display: flex;
        font-size: .74rem;
        font-weight: 800;
        gap: .7rem;
        justify-content: center;
        letter-spacing: .12em;
        margin: 1.15rem 0 .95rem;
        text-transform: uppercase;
    }

    #modal_divisi3 .keu-payment-detail-title::before,
    #modal_divisi3 .keu-payment-detail-title::after {
        background: #edf0f4;
        content: "";
        flex: 1 1 auto;
        height: 1px;
    }

    #modal_divisi3 .keu-payment-allocation-row,
    #modal_divisi3 .keu-payment-allocation-total {
        align-items: center;
        display: flex;
        justify-content: space-between;
        gap: .75rem;
    }

    #modal_divisi3 .keu-payment-allocation-row {
        color: #1f2937;
        font-size: .9rem;
        margin-bottom: .7rem;
    }

    #modal_divisi3 .keu-payment-allocation-total {
        border-top: 1px solid #f0f2f6;
        color: #344054;
        font-size: .82rem;
        font-weight: 700;
        margin-top: .35rem;
        padding-top: .85rem;
    }

    #modal_divisi3 .keu-payment-allocation-label {
        align-items: center;
        display: flex;
        gap: .45rem;
        min-width: 0;
    }

    #modal_divisi3 .keu-payment-allocation-badge {
        background: #e3e7ff;
        border-radius: 4px;
        color: #4f46e5;
        flex: 0 0 auto;
        font-size: .65rem;
        font-weight: 800;
        line-height: 1;
        padding: .28rem .4rem;
        text-transform: uppercase;
    }

    #modal_divisi3 .keu-payment-allocation-name {
        overflow-wrap: anywhere;
    }

    #modal_divisi3 .keu-payment-allocation-value {
        color: #10213b;
        flex: 0 0 auto;
        font-weight: 800;
        text-align: right;
        white-space: nowrap;
    }

    #modal_divisi3 .keu-payment-allocation-total .keu-payment-allocation-value {
        color: #020617;
    }

    #modal_divisi3 .keu-payment-empty {
        color: #98a2bd;
        font-size: .82rem;
        font-weight: 700;
        margin-bottom: 1rem;
        text-align: center;
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

        #modal_divisi3 .keu-pay-form-sticky {
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

        #modal_divisi3 .keu-payment-summary {
            padding: 1rem;
        }

        #modal_divisi3 .keu-payment-percent {
            position: static;
            text-align: right;
        }

        #modal_divisi3 .keu-payment-metric-row,
        #modal_divisi3 .keu-payment-allocation-row,
        #modal_divisi3 .keu-payment-allocation-total {
            align-items: flex-start;
            flex-direction: column;
            gap: .2rem;
        }

        #modal_divisi3 .keu-payment-metric-value,
        #modal_divisi3 .keu-payment-allocation-value {
            text-align: left;
            white-space: normal;
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
                                        <span class="keu-cost-label">Turun KPR</span>
                                        <span class="keu-cost-value" data-biaya-mkdt="harga_penambahan_um">Rp. 0</span>
                                    </div>
                                    <div class="keu-cost-row">
                                        <span class="keu-cost-label">Biaya Kavling Strategis</span>
                                        <span class="keu-cost-value" data-biaya-mkdt="harga_penambahan">Rp. 0</span>
                                    </div>
                                    <div class="keu-cost-row">
                                        <span class="keu-cost-label">Biaya Kelebihan Tanah</span>
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
                                                <div class="divider divider-left hidden">
                                                    <div class="divider-text font-weight-bold">Status Konsumen</div>
                                                </div>
                                                <div class="row hidden">
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
                                                <div class="card keu-pay-form-sticky">
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
