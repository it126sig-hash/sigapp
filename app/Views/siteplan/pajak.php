<style>
    /* SIGAPP UI Acuan - Modal Pajak mengikuti layout cashout keuangan */
    #modal_divisi10 .modal-dialog {
        max-width: min(1440px, calc(100vw - 32px));
        margin: 1rem auto;
    }

    #modal_divisi10 .modal-content {
        border: 0;
        border-radius: 10px;
        box-shadow: 0 18px 45px rgba(15, 23, 42, .18);
        overflow: hidden;
    }

    #modal_divisi10 .modal-header {
        align-items: center;
        background: #fff;
        border-bottom: 1px solid #e5e7eb;
        margin-bottom: 0 !important;
        padding: 1rem 1.25rem;
    }

    #modal_divisi10 .modal-title {
        color: #111827;
        font-size: 1.05rem;
        font-weight: 700;
    }

    #modal_divisi10 .pajak-body {
        background: #f3f5f7 !important;
        max-height: calc(100vh - 7rem);
        overflow-y: auto;
        padding: 1rem;
    }

    #modal_divisi10 .pajak-layout {
        display: flex;
        flex-wrap: nowrap;
        gap: 1rem;
        min-width: 0;
    }

    #modal_divisi10 .pajak-sidebar {
        align-self: flex-start;
        flex: 0 0 320px;
        max-height: calc(100vh - 8rem);
        max-width: 320px;
        overflow-y: auto;
        position: sticky;
        top: 0;
        z-index: 2;
    }

    #modal_divisi10 .pajak-content {
        flex: 1 1 auto;
        max-width: calc(100% - 336px);
        min-width: 0;
    }

    #modal_divisi10 .card {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        box-shadow: none;
        margin-bottom: 1rem;
        overflow: hidden;
    }

    #modal_divisi10 .card-body {
        padding: 1rem;
    }

    #modal_divisi10 .pajak-hero {
        border: 0;
    }

    #modal_divisi10 .bg-primary {
        background: linear-gradient(145deg, #2057a3 0%, #1f7a8c 100%) !important;
    }

    #modal_divisi10 .label_alamat {
        font-size: 1rem;
        font-weight: 700;
        line-height: 1.35;
        margin-bottom: 0;
        overflow-wrap: anywhere;
    }

    #modal_divisi10 .pajak-meta-card {
        background: #fff;
        border: 1px solid #cfd6e3;
        border-radius: 8px;
        box-shadow: 0 8px 18px rgba(15, 23, 42, .05);
        margin-bottom: 0;
    }

    #modal_divisi10 .pajak-meta-card h6,
    #modal_divisi10 .pajak-meta-card h5 {
        color: #374151;
        line-height: 1.35;
        margin-bottom: .45rem;
    }

    #modal_divisi10 .divider {
        margin: .65rem 0 .85rem;
    }

    #modal_divisi10 .divider-left {
        border-left-color: #2057a3;
        margin-bottom: .85rem;
        padding-left: .75rem;
    }

    #modal_divisi10 .divider .divider-text {
        color: #111827;
        font-size: .86rem;
        font-weight: 700;
    }

    #modal_divisi10 label,
    #modal_divisi10 .form-label {
        color: #6b7280;
        font-size: .78rem;
        font-weight: 700;
        letter-spacing: 0;
    }

    #modal_divisi10 .form-group {
        margin-bottom: .8rem;
    }

    #modal_divisi10 .form-control {
        background-color: #fff;
        border-color: #d8dde3;
        border-radius: 6px;
        min-height: 36px;
    }

    #modal_divisi10 .form-control:disabled,
    #modal_divisi10 .form-control[readonly] {
        background: #f8fafc;
        color: #374151;
    }

    #modal_divisi10 .btn {
        border-radius: 6px;
        font-weight: 700;
        white-space: normal;
    }

    #modal_divisi10 .btn-primary,
    #modal_divisi10 .nav-pills .nav-link.active {
        background-color: #2057a3 !important;
        border-color: #2057a3 !important;
    }

    #modal_divisi10 .btn-primary:hover,
    #modal_divisi10 .btn-primary:focus {
        background-color: #174b8f !important;
        border-color: #174b8f !important;
    }

    #modal_divisi10 .pajak-tax-tabs {
        flex-direction: row !important;
        flex-wrap: nowrap;
        gap: .5rem;
        overflow-x: auto;
        padding-bottom: .25rem;
    }

    #modal_divisi10 .pajak-tax-tabs .nav-link {
        border-radius: 6px;
        color: #374151;
        font-weight: 700;
        white-space: nowrap;
    }

    #modal_divisi10 .pajak-file-list {
        display: flex;
        flex-direction: column;
        gap: .75rem;
    }

    #modal_divisi10 .pajak-file-tile {
        background: #fff;
        border: 1px solid #d8dde3;
        border-radius: 8px;
        color: #374151;
        display: block;
        padding: .75rem;
        text-decoration: none;
        white-space: normal;
    }

    #modal_divisi10 .pajak-file-tile:hover,
    #modal_divisi10 .pajak-file-tile:focus {
        border-color: #2057a3;
        color: #2057a3;
        text-decoration: none;
    }

    #modal_divisi10 .pajak-file-title {
        color: #111827;
        font-size: .9rem;
        font-weight: 700;
        margin-bottom: .25rem;
        overflow-wrap: anywhere;
    }

    #modal_divisi10 .pajak-file-meta {
        color: #6b7280;
        font-size: .78rem;
        line-height: 1.35;
        overflow-wrap: anywhere;
    }

    #modal_divisi10 .pajak-file-preview {
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        display: block;
        height: 120px;
        margin-top: .6rem;
        width: 100%;
    }

    #modal_divisi10 .pajak-empty-file {
        background: #f8fafc;
        border: 1px dashed #cfd6e3;
        border-radius: 8px;
        color: #6b7280;
        font-size: .82rem;
        padding: .85rem;
        text-align: center;
    }

    #modal_divisi10 .pajak-section-line {
        border-top: 1px solid #e5e7eb;
        margin: 1rem 0;
    }

    #modal_divisi10 .modal-footer {
        background: #fff;
        border-top: 1px solid #e5e7eb;
        padding: .85rem 1.25rem;
    }

    .dark-layout #modal_divisi10 .modal-header,
    .dark-layout #modal_divisi10 .card,
    .dark-layout #modal_divisi10 .pajak-meta-card,
    .dark-layout #modal_divisi10 .modal-footer,
    .dark-layout #modal_divisi10 .pajak-file-tile {
        background: #283046 !important;
        border-color: rgba(255, 255, 255, .08) !important;
    }

    .dark-layout #modal_divisi10 .modal-title,
    .dark-layout #modal_divisi10 .divider .divider-text,
    .dark-layout #modal_divisi10 .pajak-file-title {
        color: #f8fafc;
    }

    .dark-layout #modal_divisi10 .pajak-body {
        background: #1f2937 !important;
    }

    @media (max-width: 1199.98px) {
        #modal_divisi10 .pajak-layout {
            flex-wrap: wrap;
        }

        #modal_divisi10 .pajak-sidebar,
        #modal_divisi10 .pajak-content {
            flex: 0 0 100%;
            max-width: 100%;
        }

        #modal_divisi10 .pajak-sidebar {
            max-height: none;
            overflow-y: visible;
            position: static;
        }
    }

    @media (max-width: 767.98px) {
        #modal_divisi10 .modal-dialog {
            max-width: calc(100vw - 12px);
            margin: .5rem auto;
        }

        #modal_divisi10 .pajak-body {
            max-height: calc(100vh - 5.5rem);
            padding: .75rem;
        }

        #modal_divisi10 .card-body {
            padding: .85rem;
        }
    }
</style>

<!-- ################################## Pajak ##########################################-->
<div class="modal fade text-left" id="modal_divisi10" tabindex="-1" role="dialog" aria-labelledby="modal_divisi10_label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-xl" role="document">
        <form id="fm-pajak" class="add-new-record modal-content pt-0" autocomplete="off" style="height: 95vh;">
            <div class="modal-header">
                <h5 class="modal-title" id="modal_divisi10_label">Form Pajak</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body flex-grow-1 pajak-body">
                <div class="pajak-layout">
                    <aside class="pajak-sidebar">
                        <div class="card pajak-hero">
                            <div class="card-body bg-primary text-light">
                                <p class="modal-title label_alamat" id="label_alamat3"></p>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <div class="divider divider-left">
                                    <div class="divider-text">Info Konsumen</div>
                                </div>
                                <div class="card pajak-meta-card">
                                    <div class="card-body">
                                        <h6><i class="fas fa-users"></i> Konsumen</h6>
                                        <h5><strong><span class="label_konsumen" id="label_konsumen">-</span></strong></h5>
                                        <div class="form-group">
                                            <label>Nama Konsumen</label>
                                            <input type="text" id="pajak-nama_konsumen" name="nama_konsumen" class="form-control" readonly placeholder="Nama Konsumen" />
                                        </div>
                                        <div class="form-group">
                                            <label>Alamat Konsumen</label>
                                            <input type="text" id="pajak-alamat_konsumen" name="alamat_konsumen" class="form-control" readonly placeholder="Alamat Konsumen" />
                                        </div>
                                        <div class="form-group">
                                            <label>NPWP</label>
                                            <input type="text" id="pajak-npwp" name="npwp" class="form-control" readonly placeholder="NPWP" />
                                        </div>
                                        <div class="form-group">
                                            <label>No Telepon</label>
                                            <input type="text" id="pajak-hp_konsumen" name="hp_konsumen" class="form-control" readonly placeholder="Kontak Konsumen" />
                                        </div>
                                        <div class="form-group">
                                            <label>NOP</label>
                                            <input type="text" id="pajak-pbb_pecah_nop" name="pbb_pecah_nop" class="form-control" readonly placeholder="NOP" />
                                        </div>
                                        <div class="form-group">
                                            <label>Harga Jual (Net)</label>
                                            <input type="text" id="pajak-harga_jual_net" name="harga_jual_net" class="form-control num" readonly placeholder="Harga Jual Net" />
                                        </div>
                                        <div class="form-group">
                                            <label>Tanggal Akad</label>
                                            <input type="text" id="pajak-akad_tgl" name="akad_tgl" class="form-control flatpickr-human-friendly" disabled />
                                        </div>
                                        <div class="form-group mb-0">
                                            <label>Nominal PPN</label>
                                            <input type="text" id="pajak-harga_ppn" name="harga_ppn" class="form-control num" readonly />
                                        </div>
                                    </div>
                                </div>
                                <div class="divider divider-left">
                                    <div class="divider-text">Berkas AJB</div>
                                </div>
                                <div id="file_ajb-here" class="pajak-file-list"></div>
                            </div>
                        </div>
                    </aside>

                    <section class="pajak-content">
                        <input type="hidden" class="form-control id_kavling" name="id_kavling" value="" />
                        <input type="hidden" class="form-control" id="id_mkdt" name="id_mkdt" value="" />
                        <input type="hidden" class="form-control" id="pajak-id_pajak" name="id" value="" />

                        <div class="card">
                            <div class="card-body pb-0">
                                <ul class="nav nav-pills pajak-tax-tabs" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" id="fp-pph42-tab" data-toggle="tab" href="#fp-pph42" aria-controls="fp-pph42" role="tab" aria-selected="true">PPh4(2)</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="fp-ppn-tab" data-toggle="tab" href="#fp-ppn" aria-controls="fp-ppn" role="tab" aria-selected="false">PPN</a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="card mb-0">
                            <div class="card-body">
                                <div class="tab-content">
                                    <div class="tab-pane active" id="fp-pph42" aria-labelledby="fp-pph42-tab" role="tabpanel">
                                        <div class="row">
                                            <div class="col-lg-4">
                                                <div class="divider divider-left">
                                                    <div class="divider-text">Form PPh4(2)</div>
                                                </div>
                                                <div class="form-group">
                                                    <label>Tarif PPH 4(2)</label>
                                                    <select name="pph42_tarif" class="form-control" id="pajak-pph42_tarif">
                                                        <?php
                                                        foreach ($pph as $p) {
                                                            echo "<option data-ket='$p->ket' value='$p->id'>$p->besar%</option>";
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label>Nominal PPH 4(2)</label>
                                                    <input type="text" id="pajak-pph42_nilai" name="pph42_nilai" class="form-control num" placeholder="Nilai Bayar PPH" />
                                                    <small id="pajak-pph42_ledger_badge" class="d-block mt-1"></small>
                                                </div>
                                                <div class="form-group">
                                                    <label>ID Billing</label>
                                                    <input type="text" id="pajak-pph42_id_billing" name="pph42_id_billing" class="form-control" placeholder="ID Billing" />
                                                </div>
                                                <div class="form-group">
                                                    <label>NTPN</label>
                                                    <input type="text" id="pajak-pph42_ntpn" name="pph42_ntpn" class="form-control" placeholder="NTPN" />
                                                </div>
                                                <div class="form-group">
                                                    <label>Tanggal Pembayaran PPH</label>
                                                    <input type="text" id="pajak-pph42_tgl_bayar" name="pph42_tgl_bayar" class="form-control flatpickr-human-friendly" placeholder="Tanggal Pembayaran" />
                                                </div>
                                                <div class="form-group mb-lg-0">
                                                    <label for="pajak-pph42_keterangan">Keterangan</label>
                                                    <textarea class="form-control" id="pajak-pph42_keterangan" name="pph42_keterangan" rows="3" placeholder="Keterangan"></textarea>
                                                </div>
                                            </div>
                                            <div class="col-lg-4">
                                                <div class="divider divider-left">
                                                    <div class="divider-text">Upload Berkas PPh4(2)</div>
                                                </div>
                                                <div class="hidden form-group">
                                                    <label>Kategori</label>
                                                    <select id="pajak-pph42_kategori" class="form-control" name="pph42_kategori-ebilling">
                                                        <option selected value="E-billing">E-billing</option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="pph42_file-ebilling">Unggah E-billing</label>
                                                    <div class="custom-file">
                                                        <input type="file" class="custom-file-input" name="pph42_file-ebilling" accept="application/pdf" id="pph42_file-ebilling" />
                                                        <label class="custom-file-label" id="pph42_file-ebilling-label" for="pph42_file-ebilling">Pilih Berkas</label>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label>Keterangan</label>
                                                    <textarea cols="40" rows="3" id="pajak-pph42_file_keterangan-ebilling" name="pph42_file_keterangan-ebilling" class="form-control" placeholder="Keterangan"></textarea>
                                                </div>

                                                <div class="pajak-section-line"></div>
                                                <div class="hidden form-group">
                                                    <label>Kategori</label>
                                                    <select id="pajak-pph42_kategori-bpn" class="form-control" name="pph42_kategori-bpn">
                                                        <option selected value="Bukti Penerimaan Negara (BPN)">Bukti Penerimaan Negara (BPN)</option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="pph42_file-bpn">Unggah Bukti Penerimaan Negara (BPN)</label>
                                                    <div class="custom-file">
                                                        <input type="file" class="custom-file-input" name="pph42_file-bpn" accept="application/pdf" id="pph42_file-bpn" />
                                                        <label class="custom-file-label" id="pph42_file-bpn-label" for="pph42_file-bpn">Pilih Berkas</label>
                                                    </div>
                                                </div>
                                                <div class="form-group mb-lg-0">
                                                    <label>Keterangan</label>
                                                    <textarea cols="40" rows="3" id="pajak-pph42_file_keterangan-bpn" name="pph42_file_keterangan-bpn" class="form-control" placeholder="Keterangan"></textarea>
                                                </div>
                                            </div>
                                            <div class="col-lg-4">
                                                <div class="divider divider-left">
                                                    <div class="divider-text">Berkas PPh4(2)</div>
                                                </div>
                                                <div id="file_pph42-here" class="pajak-file-list"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="tab-pane" id="fp-ppn" aria-labelledby="fp-ppn-tab" role="tabpanel">
                                        <div class="row">
                                            <div class="col-lg-4">
                                                <div class="divider divider-left">
                                                    <div class="divider-text">Form PPN</div>
                                                </div>
                                                <div class="form-group">
                                                    <label>Tarif PPN</label>
                                                    <select name="ppn_tarif" class="form-control" id="pajak-ppn_tarif">
                                                        <?php
                                                        foreach ($ppn as $p) {
                                                            echo "<option value='$p->id'>$p->besar%</option>";
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label>Nominal PPN</label>
                                                    <input type="text" id="pajak-ppn_nilai" name="ppn_nilai" class="form-control num" />
                                                    <small id="pajak-ppn_ledger_badge" class="d-block mt-1"></small>
                                                </div>
                                                <div class="form-group">
                                                    <label>ID Billing</label>
                                                    <input type="text" id="pajak-ppn_id_billing" name="ppn_id_billing" class="form-control" placeholder="ID Billing" />
                                                </div>
                                                <div class="form-group">
                                                    <label>NTPN</label>
                                                    <input type="text" id="pajak-ppn_ntpn" name="ppn_ntpn" class="form-control" placeholder="NTPN" />
                                                </div>
                                                <div class="form-group">
                                                    <label>No Faktur</label>
                                                    <input type="text" id="pajak-ppn_no_faktur" name="ppn_no_faktur" class="form-control" placeholder="No Faktur" />
                                                </div>
                                                <div class="form-group">
                                                    <label>Tanggal Pembayaran PPN</label>
                                                    <input type="text" id="pajak-ppn_tgl_bayar" name="ppn_tgl_bayar" class="form-control flatpickr-human-friendly" placeholder="Tanggal Pembayaran" />
                                                </div>
                                                <div class="form-group mb-lg-0">
                                                    <label for="pajak-ppn_keterangan">Keterangan</label>
                                                    <textarea class="form-control" id="pajak-ppn_keterangan" name="ppn_keterangan" rows="3" placeholder="Keterangan"></textarea>
                                                </div>
                                            </div>
                                            <div class="col-lg-4">
                                                <div class="divider divider-left">
                                                    <div class="divider-text">Upload Berkas PPN</div>
                                                </div>
                                                <div class="hidden form-group">
                                                    <label>Unggah E-Billing</label>
                                                    <select id="pajak-ppn_kategori" class="form-control hidden" name="ppn_kategori-ebilling">
                                                        <option selected value="E-billing">E-billing</option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="ppn_file-ebilling">Unggah E-Billing</label>
                                                    <div class="custom-file">
                                                        <input type="file" class="custom-file-input" name="ppn_file-ebilling" accept="application/pdf" id="ppn_file-ebilling" />
                                                        <label class="custom-file-label" id="ppn_file-ebilling-label" for="ppn_file-ebilling">Pilih Berkas</label>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label>Keterangan</label>
                                                    <textarea cols="40" rows="3" id="pajak-ppn_file_keterangan-ebilling" name="ppn_file_keterangan-ebilling" class="form-control" placeholder="Keterangan"></textarea>
                                                </div>

                                                <div class="pajak-section-line"></div>
                                                <div class="hidden form-group">
                                                    <label>Unggah BPN</label>
                                                    <select id="pajak-ppn_kategori-bpn" class="hidden form-control" name="ppn_kategori-bpn">
                                                        <option selected value="Bukti Penerimaan Negara (BPN)">Bukti Penerimaan Negara (BPN)</option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="ppn_file-bpn">Unggah BPN</label>
                                                    <div class="custom-file">
                                                        <input type="file" class="custom-file-input" name="ppn_file-bpn" accept="application/pdf" id="ppn_file-bpn" />
                                                        <label class="custom-file-label" id="ppn_file-bpn-label" for="ppn_file-bpn">Pilih Berkas</label>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label>Keterangan</label>
                                                    <textarea cols="40" rows="3" id="pajak-ppn_file_keterangan-bpn" name="ppn_file_keterangan-bpn" class="form-control" placeholder="Keterangan"></textarea>
                                                </div>

                                                <div class="pajak-section-line"></div>
                                                <div class="hidden form-group">
                                                    <label>Unggah Faktur Pajak</label>
                                                    <select id="pajak-ppn_kategori-faktur" class="hidden form-control" name="ppn_kategori-faktur">
                                                        <option selected value="Faktur Pajak">Faktur Pajak</option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="ppn_file-faktur">Unggah Faktur Pajak</label>
                                                    <div class="custom-file">
                                                        <input type="file" class="custom-file-input" name="ppn_file-faktur" accept="application/pdf" id="ppn_file-faktur" />
                                                        <label class="custom-file-label" id="ppn_file-faktur-label" for="ppn_file-faktur">Pilih Berkas</label>
                                                    </div>
                                                </div>
                                                <div class="form-group mb-lg-0">
                                                    <label>Keterangan</label>
                                                    <textarea cols="40" rows="3" id="pajak-ppn_file_keterangan-faktur" name="ppn_file_keterangan-faktur" class="form-control" placeholder="Keterangan"></textarea>
                                                </div>
                                            </div>
                                            <div class="col-lg-4">
                                                <div class="divider divider-left">
                                                    <div class="divider-text">Berkas PPN</div>
                                                </div>
                                                <div id="file_ppn-here" class="pajak-file-list"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
            <div class="modal-footer">
                <button id="add-form-btn-pajak" class="btn btn-primary data-submit mr-1" onclick="save_pajak(); return false;" href="javascript:void(0)">Simpan</button>
                <button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script src="<?= base_url() ?>assets/js/siteplan/pajak.js?v=<?= filemtime(FCPATH.'assets/js/siteplan/pajak.js') ?>"></script>
</script>
