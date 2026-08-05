<!-- modal detail kavling -->
<div class="modal fade" id="modal_detail">
    <div class="modal-dialog modal-dialog-scrollable modal-xl">
        <div class="add-new-record modal-content pt-0">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Detail Kavling</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">x</button>
            </div>
            <div class="modal-body flex-grow-1" style="background-color:#eee">
                <form action=""></form>
                <div class="row detail-kavling-layout" id="fm-detail">
                    <div class="col-md-3 detail-kavling-sidebar">
                         <div class="card detail-hero-card">
                            <div class="card-body bg-primary text-light">
                                <div class="row">
                                    <div class="col-12">
                                        <p class="card-text label_alamat" id="detail_kavling_header"></p>
                                        <p class="mb-0 text-light"><strong>ID SIKUMABANG:</strong></p>
                                        <p class="card-text id_sikumbang mb-1"></p>
                                    </div>
                                    <div class="col-12">
                                        <div class="card detail-price-card">
                                            <div class="card-body">
                                                <h5><i class="fas fa-money-bill"></i> Harga Jual</h5>
                                                <div class="detail-price-toggle" data-toggle="collapse"
                                                    data-target="#detailHargaJualPricelist" aria-expanded="false"
                                                    aria-controls="detailHargaJualPricelist" role="button">
                                                    <span id="label-hargajual"></span>
                                                    <i class="fas fa-chevron-down detail-price-toggle-icon"></i>
                                                </div>
                                                <div class="collapse" id="detailHargaJualPricelist">
                                                    <div class="detail-price-list mt-2">
                                                        <div class="detail-price-item">
                                                            <span class="detail-price-label">Tanggal PriceList</span>
                                                            <input type="text"
                                                                class="detail-price-value flatpickr-human-friendly"
                                                                id="dt-pl_tgl_harga" disabled name="dt-pl_tgl_harga"
                                                                value="" readonly />
                                                        </div>
                                                        <div class="detail-price-item">
                                                            <span class="detail-price-label">Harga Jual</span>
                                                            <span class="detail-price-value" id="dt-pl_hargajual">-</span>
                                                        </div>
                                                        <div class="detail-price-item" id="hjdis-pl">
                                                            <span class="detail-price-label">Diskon Harga Jual</span>
                                                            <span class="detail-price-value"
                                                                id="dt-pl_harga_diskon_hargajual">-</span>
                                                        </div>
                                                        <div class="detail-price-item is-highlight">
                                                            <span class="detail-price-label">Harga Jual Net</span>
                                                            <span class="detail-price-value" id="dt-pl_hargajual_net">-</span>
                                                        </div>
                                                        <div class="detail-price-item">
                                                            <span class="detail-price-label">KPR</span>
                                                            <span class="detail-price-value" id="dt-pl_kpr">-</span>
                                                        </div>
                                                        <div class="detail-price-item">
                                                            <span class="detail-price-label">Uang Muka</span>
                                                            <span class="detail-price-value" id="dt-pl_uang_muka">-</span>
                                                        </div>
                                                        <div class="detail-price-item" id="umdis-pl">
                                                            <span class="detail-price-label">Diskon Uang Muka</span>
                                                            <span class="detail-price-value"
                                                                id="dt-pl_harga_diskon_uang_muka">-</span>
                                                        </div>
                                                        <div class="detail-price-item">
                                                            <span class="detail-price-label">Biaya Adm</span>
                                                            <span class="detail-price-value" id="dt-pl_biaya_adm">-</span>
                                                        </div>
                                                        <div class="detail-price-item">
                                                            <span class="detail-price-label">PPN</span>
                                                            <span class="detail-price-value" id="dt-pl_ppn">-</span>
                                                        </div>
                                                        <div class="detail-price-item">
                                                            <span class="detail-price-label">BPHTB</span>
                                                            <span class="detail-price-value" id="dt-pl_bphtb">-</span>
                                                        </div>
                                                        <div class="detail-price-item">
                                                            <span class="detail-price-label">Biaya Proses</span>
                                                            <span class="detail-price-value" id="dt-pl_biaya_proses">-</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="card detail-meta-card mb-0">
                                            <div class="card-body p-1">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <h6 class="mb-0"><span id="dt-is_kpr">-</span></h6>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <h6 class="mb-0">
                                                            <span id="dt-is_subsidi">-</span>
                                                        </h6>
                                                    </div>
                                                    <div class="col-12">
                                                        <h6 class="mb-0">
                                                            <strong>Promo: </strong>
                                                            <span id="dt-promo">-</span>
                                                        </h6>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="card detail-consumer-card">
                            <div class="card-header">
                                <div class="divider divider-left pb-0">
                                    <div class="divider-text font-weight-bold"><strong><i class="fas fa-user"></i> Detail Konsumen</strong></div>
                                </div>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-2">
                                        <div class="me-2">
                                            <b class="mb-0 d-block">No SPPTB</b>
                                            <span id="dt-no_spptb">-</span>
                                        </div>
                                    </li>
                                    <li class="mb-2">
                                        <div class="me-2">
                                            <b class="mb-0 d-block">Nama Konsumen</b>
                                            <span id="dt-nama_konsumen">-</span>
                                        </div>
                                    </li>
                                </ul>
                                <button class="btn btn-outline-primary btn-block waves-effect detail-consumer-toggle"
                                    type="button" data-toggle="collapse" data-target="#detailConsumerMore"
                                    aria-expanded="false" aria-controls="detailConsumerMore">
                                    <span class="show-label">Tampilkan info lainnya</span>
                                    <span class="hide-label">Sembunyikan info lainnya</span>
                                    <i class="fas fa-chevron-down ml-1"></i>
                                </button>
                                <div class="collapse" id="detailConsumerMore">
                                    <ul class="list-unstyled mb-0 mt-1">
                                        <li class="mb-2">
                                            <div class="me-2">
                                                <b class="mb-0 d-block">Alamat Konsumen</b>
                                                <span id="dt-alamat_konsumen">-</span>
                                            </div>
                                        </li>
                                        <li class="mb-2">
                                            <div class="me-2">
                                                <b class="mb-0 d-block">NIK</b>
                                                <span id="dt-nik_konsumen">-</span>
                                            </div>
                                        </li>
                                        <li class="mb-2">
                                            <div class="me-2">
                                                <b class="mb-0 d-block">NPWP</b>
                                                <span id="dt-npwp_konsumen">-</span>
                                            </div>
                                        </li>
                                        <li class="mb-2">
                                            <div class="me-2">
                                                <b class="mb-0 d-block">Kontak Konsumen</b>
                                                <span id="dt-hp_konsumen">-</span>
                                            </div>
                                        </li>
                                        <li class="mb-2">
                                            <div class="me-2">
                                                <b class="mb-0 d-block">Email Konsumen</b>
                                                <span id="dt-email_konsumen">-</span>
                                            </div>
                                        </li>
                                        <li class="mb-2">
                                            <div class="me-2">
                                                <b class="mb-0 d-block">Sales</b>
                                                <span id="dt-sales">-</span>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-9 detail-kavling-content">
                        <div class="card detail-tabs-card">
                            <div class="card-body pb-0 pt-0">
                                <ul class="nav nav-pills flex-column flex-md-row mt-1 row-gap-2" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" id="dtt-summary-tab" data-toggle="tab"
                                            href="#dtt-summary" aria-controls="summary" role="tab"
                                            aria-selected="true">Ringkasan</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="dtt-hj-tab" data-toggle="tab" href="#dtt-hj"
                                            aria-controls="dj" role="tab" aria-selected="true">Harga Jual</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="dt-stdetail-tab" data-toggle="tab" href="#dt-stdetail"
                                            aria-controls="dt-stdetail-dt" role="tab" aria-selected="false">Status</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="dt-spesifikasi-tab" data-toggle="tab"
                                            href="#dt-spesifikasi" aria-controls="dt-spesifikasi" role="tab"
                                            aria-selected="false">Spesifikasi Teknis</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="dt-tagihan-tab" data-toggle="tab" href="#dt-tagihan"
                                            aria-controls="tgt" role="tab" aria-selected="false">Tagihan</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="dt-cashout-tab" data-toggle="tab" href="#dt-cashout"
                                            aria-controls="cashout-tab" role="tab" aria-selected="false">Cashout</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="dt-legal-tab" data-toggle="tab" href="#dt-legal"
                                            aria-controls="legal" role="tab" aria-selected="false">Legal</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="dt-produksi-tab" data-toggle="tab" href="#dt-produksi"
                                            aria-controls="produksi" role="tab" aria-selected="false">Bangunan</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="dt-pajak-tab" data-toggle="tab" href="#dt-pajak"
                                            aria-controls="pajak" role="tab" aria-selected="false">Bukti Bayar
                                            PPH/PPN</a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="card detail-panel-card">
                            <div class="card-body">
                                <div class="tab-content">
                                    <div class="tab-pane active" id="dtt-summary" aria-labelledby="dtt-summary-tab"
                                        role="tabpanel">
                                        <div class="detail-dashboard-grid">
                                            <div class="detail-summary-card detail-status-card">
                                                <div class="detail-status-card-header">
                                                    <div class="detail-card-icon"><i class="fas fa-user"></i></div>
                                                    <div class="detail-status-card-title">Status &amp; Akad</div>
                                                </div>

                                                <div class="detail-info-row">
                                                    <div class="detail-info-col">
                                                        <span class="detail-info-label">Status Kavling</span>
                                                        <span class="detail-status-badge badge-secondary" id="s-status_mkdt">-</span>
                                                    </div>
                                                    <div class="detail-info-col text-right">
                                                        <span class="detail-info-label">Tanggal Akad</span>
                                                        <span class="detail-info-value" id="s-akad_tgl">-</span>
                                                    </div>
                                                </div>

                                                <div class="detail-info-row">
                                                    <div class="detail-info-col">
                                                        <span class="detail-info-label">Bank Pelaksana</span>
                                                        <span class="detail-info-value" id="s-st_bank">-</span>
                                                    </div>
                                                    <div class="detail-info-col text-right">
                                                        <span class="detail-info-label">Notaris</span>
                                                        <span class="detail-info-value" id="s-notaris">-</span>
                                                    </div>
                                                </div>

                                                <div class="detail-section-divider">
                                                    <span class="detail-section-title detail-section-title-dot">SP3K</span>
                                                </div>
                                                <div class="detail-info-pair">
                                                    <div class="detail-mini-card">
                                                        <div class="detail-mini-label">Terbit</div>
                                                        <div class="detail-mini-value" id="s-st_sp3k_tgl">-</div>
                                                    </div>
                                                    <div class="detail-mini-card">
                                                        <div class="detail-mini-label">Kadaluarsa</div>
                                                        <div class="detail-mini-value" id="s-st_sp3k_tgl_exp">-</div>
                                                    </div>
                                                </div>

                                                <div class="detail-section-divider">
                                                    <span class="detail-section-title detail-section-title-dot">Standing Instruction</span>
                                                </div>
                                                <div id="s-si"></div>
                                            </div>

                                            <div class="detail-summary-card detail-status-card">
                                                <div class="detail-status-card-header">
                                                    <div class="detail-card-icon"><i class="fas fa-tools"></i></div>
                                                    <div class="detail-status-card-title">Bangunan</div>
                                                </div>

                                                <div class="mb-2">
                                                    <div class="d-flex justify-content-between mb-50">
                                                        <span class="detail-info-label">Progres Fisik</span>
                                                        <span class="detail-info-value" id="s-progress_bangunan_text">0%</span>
                                                    </div>
                                                    <div class="detail-progress-track">
                                                        <div id="s-progress_bangunan_bar" class="detail-progress-fill is-empty" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                </div>

                                                <div class="detail-info-row">
                                                    <div class="detail-info-col">
                                                        <span class="detail-info-label"><i class="fas fa-check-circle detail-text-primary"></i> Selesai Pembangunan</span>
                                                        <span class="detail-info-value" id="s-tanggal_selesai_pembangunan">-</span>
                                                    </div>
                                                    <div class="detail-info-col text-right">
                                                        <span class="detail-info-label">Mulai Bangun</span>
                                                        <span class="detail-info-value" id="s-tanggal_pembangunan">-</span>
                                                    </div>
                                                </div>
                                                <div class="detail-info-row">
                                                    <div class="detail-info-col">
                                                        <span class="detail-info-label">Tgl Turun Perintah Bangun</span>
                                                        <span class="detail-info-value" id="s-perintah_bangun_tgl">-</span>
                                                    </div>
                                                </div>

                                                <div class="detail-section-divider">
                                                    <span class="detail-section-title detail-section-title-dot">Site Amenities</span>
                                                </div>
                                                <div class="detail-info-row">
                                                    <div class="detail-info-col">
                                                        <span class="detail-info-label">Saluran</span>
                                                        <span class="detail-status-badge badge-secondary" id="s-st_saluran">-</span>
                                                    </div>
                                                    <div class="detail-info-col text-right">
                                                        <span class="detail-info-label">Air</span>
                                                        <span class="detail-status-badge badge-secondary" id="s-st_air">-</span>
                                                    </div>
                                                </div>
                                                <div class="detail-info-row">
                                                    <div class="detail-info-col">
                                                        <span class="detail-info-label">Listrik</span>
                                                        <span class="detail-status-badge badge-secondary" id="s-st_jalan">-</span>
                                                    </div>
                                                    <div class="detail-info-col text-right">
                                                        <span class="detail-info-label">SLO/NIDI</span>
                                                        <span class="detail-status-badge badge-secondary" id="s-slo">-</span>
                                                    </div>
                                                </div>
                                                <div class="detail-info-row">
                                                    <div class="detail-info-col">
                                                        <span class="detail-info-label">LPA</span>
                                                        <span class="detail-status-badge badge-secondary" id="s-lpa">-</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="detail-summary-card detail-status-card">
                                                <div class="detail-status-card-header">
                                                    <div class="detail-card-icon"><i class="fas fa-file-invoice-dollar"></i></div>
                                                    <div class="detail-status-card-title">Keuangan</div>
                                                </div>

                                                <div class="d-flex justify-content-center mb-1" style="position: relative; height:160px; width:100%">
                                                    <canvas id="keuanganChart"></canvas>
                                                    <div id="keuanganChart-empty" style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%); display:none; text-align:center; color:#999; font-size:0.75rem;">
                                                        Belum ada<br>pembayaran
                                                    </div>
                                                </div>

                                                <div class="detail-info-row mt-1">
                                                    <div class="detail-info-col">
                                                        <span class="detail-info-label"><span class="detail-dot detail-dot-success"></span>Telah Dibayar</span>
                                                        <span class="detail-info-value detail-text-success" id="s-total_dibayar">Rp 0</span>
                                                    </div>
                                                    <div class="detail-info-col text-right">
                                                        <span class="detail-info-label"><span class="detail-dot detail-dot-danger"></span>Sisa Tagihan</span>
                                                        <span class="detail-info-value detail-text-danger" id="s-sisa_tagihan">Rp 0</span>
                                                    </div>
                                                </div>

                                                <div class="info-row row no-gutters hidden">
                                                    <div class="col-6">
                                                        <label class="info-label">Uang Muka</label>
                                                    </div>
                                                    <div class="col-6">
                                                        : <span class="info-value"
                                                            id="s-persentase_bayar_tagihan_um"></span>
                                                    </div>
                                                </div>
                                                <div class="info-row row no-gutters hidden">
                                                    <div class="col-6">
                                                        <label class="info-label">Tagihan</label>
                                                    </div>
                                                    <div class="col-6">
                                                        : <span class="info-value"
                                                            id="s-persentase_bayar_tagihan_um_ll"></span>
                                                    </div>
                                                </div>
                                                <div class="info-row row no-gutters hidden">
                                                    <div class="col-6">
                                                        <label class="info-label">Biaya-biaya</label>
                                                    </div>
                                                    <div class="col-6">
                                                        : <span class="info-value"
                                                            id="s-persentase_bayar_tagihan_bb"></span>
                                                    </div>
                                                </div>

                                                <div class="detail-section-divider">
                                                    <span class="detail-section-title detail-section-title-dot">Cashout Status</span>
                                                </div>
                                                <div id="s-co"></div>

                                                <div class="detail-section-divider">
                                                    <span class="detail-section-title detail-section-title-dot">Hutang Subkon</span>
                                                </div>
                                                <div id="s-hutang-subkon"></div>
                                            </div>

                                            <div class="detail-summary-card detail-status-card">
                                                <div class="detail-status-card-header">
                                                    <div class="detail-card-icon"><i class="fas fa-hand-holding-usd"></i></div>
                                                    <div class="detail-status-card-title">Retensi &amp; Hasil Akad</div>
                                                </div>

                                                <div class="d-flex justify-content-center mb-1" style="position: relative; height:160px; width:100%">
                                                    <canvas id="hasilAkadChart"></canvas>
                                                    <div id="hasilAkadChart-empty" style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%); display:none; text-align:center; color:#999; font-size:0.75rem;">
                                                        Belum ada<br>data pencairan
                                                    </div>
                                                </div>

                                                <div class="detail-info-row mt-1">
                                                    <div class="detail-info-col">
                                                        <span class="detail-info-label">Total Hasil Akad</span>
                                                        <span class="detail-info-value" id="s-pa_total_hasil_akad">-</span>
                                                    </div>
                                                    <div class="detail-info-col text-right">
                                                        <span class="detail-info-label"><span class="detail-dot detail-dot-success"></span>Sudah Cair</span>
                                                        <span class="detail-info-value detail-text-success" id="s-pa_total_cair">-</span>
                                                    </div>
                                                </div>

                                                <div class="detail-section-divider">
                                                    <span class="detail-section-title detail-section-title-dot">Item Retensi</span>
                                                </div>
                                                <div id="s-pa-retensi"></div>
                                            </div>

                                        </div>
                                    </div>

                                    <div class="tab-pane" id="dtt-hj" aria-labelledby="dtt-hj-tab" role="tabpanel">
                                        <h5>Harga Jual</h5>
                                        <small class="text-muted">Terakhir diperbaharui oleh</small>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="divider divider-left">
                                                    <div class="divider-text">Harga Jual</div>
                                                </div>
                                                <div class="detail-price-item">
                                                    <span class="detail-price-label">Tanggal PriceList</span>
                                                    <input type="text"
                                                        class="detail-price-value flatpickr-human-friendly"
                                                        id="dt-tgl_harga" disabled name="dt-tgl_harga" value=""
                                                        readonly />
                                                </div>
                                                <div class="detail-price-item">
                                                    <span class="detail-price-label">Harga Jual</span>
                                                    <span class="detail-price-value" id="dt-hargajual">-</span>
                                                </div>
                                                <div class="detail-price-item" id="hjdis">
                                                    <span class="detail-price-label">Diskon Harga Jual</span>
                                                    <span class="detail-price-value" id="dt-harga_diskon_hargajual">-</span>
                                                </div>
                                                <div class="detail-price-item is-highlight">
                                                    <span class="detail-price-label">Harga Jual Net</span>
                                                    <span class="detail-price-value" id="dt-hargajual_net">-</span>
                                                </div>
                                                <div class="detail-price-item">
                                                    <span class="detail-price-label">KPR</span>
                                                    <span class="detail-price-value" id="dt-kpr">-</span>
                                                </div>
                                                <div class="detail-price-item">
                                                    <span class="detail-price-label">Uang Muka</span>
                                                    <span class="detail-price-value" id="dt-uang_muka">-</span>
                                                </div>
                                                <div class="detail-price-item" id="umdis">
                                                    <span class="detail-price-label">Diskon Uang Muka</span>
                                                    <span class="detail-price-value" id="dt-harga_diskon_uang_muka">-</span>
                                                </div>
                                                <div class="detail-price-item">
                                                    <span class="detail-price-label">Biaya Adm</span>
                                                    <span class="detail-price-value" id="dt-biaya_adm">-</span>
                                                </div>
                                                <div class="detail-price-item">
                                                    <span class="detail-price-label">PPN</span>
                                                    <span class="detail-price-value" id="dt-ppn">-</span>
                                                </div>
                                                <div class="detail-price-item">
                                                    <span class="detail-price-label">BPHTB</span>
                                                    <span class="detail-price-value" id="dt-bphtb">-</span>
                                                </div>
                                                <div class="detail-price-item">
                                                    <span class="detail-price-label">Biaya Proses</span>
                                                    <span class="detail-price-value" id="dt-biaya_proses">-</span>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="card detail-summary-card">
                                                    <div class="detail-status-card-header">
                                                        <div class="detail-card-icon"><i class="fas fa-home"></i>
                                                        </div>
                                                        <div class="detail-status-card-title">KPR</div>
                                                    </div>

                                                    <span class="detail-highlight-label">KPR Disetujui</span>
                                                    <span class="detail-highlight-value mb-1"
                                                        id="dt-st_harga_kpr_acc">-</span>

                                                    <div class="detail-highlight-box">
                                                        <span class="detail-highlight-label">Turun KPR</span>
                                                        <span class="detail-highlight-value"
                                                            id="dt-st_harga_penambahan_um">-</span>
                                                    </div>

                                                    <div class="detail-section-divider">
                                                        <span class="detail-section-title">Penambahan Biaya</span>
                                                    </div>
                                                    <div class="detail-metric-row">
                                                        <span class="detail-metric-label">Kavling Strategis</span>
                                                        <span class="detail-metric-value"
                                                            id="dt-st_harga_penambahan">-</span>
                                                    </div>
                                                    <div class="detail-metric-row">
                                                        <span class="detail-metric-label">Kelebihan Tanah</span>
                                                        <span class="detail-metric-value"
                                                            id="dt-st_harga_penambahan_tanah">-</span>
                                                    </div>
                                                    <div class="form-group hidden">
                                                        <label for="total_biaya2">Keterangan Penambahan Biaya</label>
                                                        <textarea readonly name="dt-st_keterangan_harga_penambahan"
                                                            id="dt-st_keterangan_harga_penambahan" class="form-control "
                                                            cols="30" rows="2"></textarea>
                                                    </div>
                                                </div>

                                                <div class="card detail-summary-card">
                                                    <div class="detail-status-card-header">
                                                        <div class="detail-card-icon"><i
                                                                class="fas fa-sticky-note"></i></div>
                                                        <div class="detail-status-card-title">Catatan Pricelist</div>
                                                    </div>
                                                    <div class="detail-note-content" id="dt-pl_keterangan">-</div>
                                                </div>

                                                <div class="card detail-summary-card">
                                                    <div class="detail-status-card-header">
                                                        <div class="detail-card-icon"><i
                                                                class="fas fa-folder-open"></i></div>
                                                        <div class="detail-status-card-title">Dokumen</div>
                                                    </div>
                                                    <div class="detail-document-list">
                                                        <a href="#" class="detail-document-item" id="dt-btn-ktp_here"
                                                            target="_blank">
                                                            <div class="detail-card-icon detail-document-icon"><i
                                                                    class="fas fa-id-card"></i></div>
                                                            <div class="detail-document-body">
                                                                <div class="detail-file-title">Kartu Tanda Penduduk
                                                                </div>
                                                                <div class="detail-file-meta" id="dt-doc-ktp_name">-
                                                                </div>
                                                            </div>
                                                            <i class="fas fa-download detail-document-arrow"></i>
                                                        </a>
                                                        <a href="#" class="detail-document-item" id="dt-btn-npwp_here"
                                                            target="_blank">
                                                            <div class="detail-card-icon detail-document-icon"><i
                                                                    class="fas fa-file-invoice"></i></div>
                                                            <div class="detail-document-body">
                                                                <div class="detail-file-title">NPWP</div>
                                                                <div class="detail-file-meta" id="dt-doc-npwp_name">-
                                                                </div>
                                                            </div>
                                                            <i class="fas fa-download detail-document-arrow"></i>
                                                        </a>
                                                        <a href="#" class="detail-document-item" id="dt-btn-bl_here"
                                                            target="_blank">
                                                            <div class="detail-card-icon detail-document-icon"><i
                                                                    class="fas fa-file-archive"></i></div>
                                                            <div class="detail-document-body">
                                                                <div class="detail-file-title">Berkas Lainnya</div>
                                                                <div class="detail-file-meta" id="dt-doc-bl_name">-
                                                                </div>
                                                            </div>
                                                            <i class="fas fa-download detail-document-arrow"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="tab-pane" id="dt-cashout" aria-labelledby="dt-cashout-tab"
                                        role="tabpanel">
                                        <table id="dt-cashout-table" class="datatables-basic table compact">
                                            <thead>
                                                <tr>
                                                    <th width="20%">Item</th>
                                                    <th width="20%">Tanggal Pembayaran</th>
                                                    <th width="25%">Nominal</th>
                                                    <th width="35%">Keterangan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                        </table>

                                    </div>
                                    <div class="tab-pane" id="dt-stdetail" aria-labelledby="dt-stdetail-tab"
                                        role="tabpanel">
                                        <div class="detail-dashboard-grid">
                                            <div class="detail-summary-card detail-status-card">
                                                <div class="detail-status-card-header">
                                                    <div class="detail-card-icon"><i class="far fa-calendar-check"></i>
                                                    </div>
                                                    <div class="detail-status-card-title">Booking &amp; Wawancara</div>
                                                </div>

                                                <div class="detail-info-row">
                                                    <div class="detail-info-col">
                                                        <span class="detail-info-label">Status</span>
                                                        <select class="detail-info-value" id="dt-status_mkdt"
                                                            name="dt-status_mkdt" disabled>
                                                            <option value="">-</option>
                                                            <option value="Booking">Booking</option>
                                                            <option value="Akad">Akad</option>
                                                            <option value="Batal">Batal</option>
                                                        </select>
                                                    </div>
                                                    <div class="detail-info-col text-right">
                                                        <span class="detail-info-label">Notaris</span>
                                                        <input type="text" class="detail-info-value" id="dt-notaris"
                                                            name="dt-notaris" placeholder="-" disabled />
                                                    </div>
                                                </div>

                                                <div class="detail-info-row">
                                                    <div class="detail-info-col">
                                                        <span class="detail-info-label">PPJB/AJB</span>
                                                        <input type="text" class="detail-info-value" id="dt-is_ajb"
                                                            name="dt-is_ajb" placeholder="-" disabled />
                                                    </div>
                                                    <div class="detail-info-col text-right">
                                                        <span class="detail-info-label">Booking Date</span>
                                                        <input type="text" id="dt-st_booking_tgl"
                                                            name="dt-st_booking_tgl"
                                                            class="detail-info-value flatpickr-human-friendly"
                                                            placeholder="-" disabled />
                                                    </div>
                                                </div>

                                                <div class="detail-highlight-box">
                                                    <span class="detail-highlight-label">Booking Fee</span>
                                                    <input type="text" class="detail-highlight-value num"
                                                        id="dt-st_booking_fee" name="dt-st_booking_fee" disabled />
                                                </div>

                                                <div class="detail-section-divider">
                                                    <span class="detail-section-title">Wawancara Section</span>
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" class="custom-control-input"
                                                            id="dt-st_wawancara" name="dt-st_wawancara" value="1"
                                                            disabled />
                                                        <label class="custom-control-label"
                                                            for="dt-st_wawancara">Sudah</label>
                                                    </div>
                                                </div>

                                                <div class="detail-info-row">
                                                    <div class="detail-info-col">
                                                        <span class="detail-info-label">Bank</span>
                                                        <input type="text" id="dt-st_bank" name="dt-st_bank"
                                                            class="detail-info-value" placeholder="-" disabled />
                                                    </div>
                                                    <div class="detail-info-col text-right">
                                                        <span class="detail-info-label">Wawancara Date</span>
                                                        <input type="text" id="dt-st_wawancara_tgl"
                                                            name="dt-st_wawancara_tgl"
                                                            class="detail-info-value flatpickr-human-friendly"
                                                            placeholder="-" disabled />
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="detail-summary-card detail-status-card">
                                                <div class="detail-status-card-header">
                                                    <div class="detail-card-icon"><i class="fas fa-file-alt"></i>
                                                    </div>
                                                    <div class="detail-status-card-title">SP3K</div>
                                                    <div class="detail-status-card-toggle custom-control custom-switch">
                                                        <input type="checkbox" class="custom-control-input"
                                                            id="dt-sp3k" name="dt-sp3k" value="1" disabled />
                                                        <label class="custom-control-label" for="dt-sp3k"></label>
                                                    </div>
                                                </div>

                                                <div class="detail-info-row">
                                                    <div class="detail-info-col">
                                                        <span class="detail-info-label">Status</span>
                                                        <span class="detail-status-badge badge-secondary"
                                                            id="dt-sp3k-status-badge">-</span>
                                                        <select class="detail-info-value hidden"
                                                            id="dt-st_mkdt_keterangan" name="dt-st_mkdt_keterangan"
                                                            disabled>
                                                            <option value="">-</option>
                                                            <option value="Disetujui">Disetujui</option>
                                                            <option value="Ditolak">Ditolak</option>
                                                        </select>
                                                    </div>
                                                    <div class="detail-info-col text-right">
                                                        <span class="detail-info-label">No SP3K</span>
                                                        <input type="text" id="dt-st_sp3k_no" name="dt-st_sp3k_no"
                                                            class="detail-info-value" placeholder="-" disabled />
                                                    </div>
                                                </div>

                                                <div class="detail-info-row">
                                                    <div class="detail-info-col">
                                                        <span class="detail-info-label">Pengajuan</span>
                                                        <input type="text" id="dt-st_harga_kpr" name="dt-st_harga_kpr"
                                                            class="detail-info-value num" placeholder="-" disabled />
                                                    </div>
                                                    <div class="detail-info-col text-right">
                                                        <span class="detail-info-label detail-text-primary">Disetujui</span>
                                                        <input type="text" id="dt-st_acc_harga_kpr"
                                                            name="dt-st_acc_harga_kpr"
                                                            class="detail-info-value detail-text-primary num"
                                                            placeholder="-" disabled />
                                                    </div>
                                                </div>

                                                <div class="detail-highlight-box">
                                                    <span class="detail-highlight-label">Turun KPR</span>
                                                    <input type="text" id="dt-st_harga_turun_kpr"
                                                        name="dt-st_harga_turun_kpr"
                                                        class="detail-highlight-value num" placeholder="-" disabled />
                                                </div>

                                                <div class="detail-info-row">
                                                    <div class="detail-info-col">
                                                        <span class="detail-info-label">Tanggal Terbit</span>
                                                        <input type="text" id="dt-st_sp3k_tgl" name="dt-st_sp3k_tgl"
                                                            class="detail-info-value flatpickr-human-friendly"
                                                            placeholder="-" disabled />
                                                    </div>
                                                    <div class="detail-info-col text-right">
                                                        <span class="detail-info-label">Tanggal Kadaluarsa</span>
                                                        <input type="text" id="dt-st_sp3k_tgl_exp"
                                                            name="dt-st_sp3k_tgl_exp"
                                                            class="detail-info-value flatpickr-human-friendly"
                                                            placeholder="-" disabled />
                                                    </div>
                                                </div>

                                                <a href="" class="btn btn-outline-primary btn-block detail-file-btn"
                                                    target="_blank" id="dt-st_list-upload_sp3k_file">
                                                    <i class="fas fa-eye mr-1"></i> Klik untuk lihat file
                                                </a>
                                            </div>

                                            <div class="detail-summary-card detail-status-card">
                                                <div class="detail-status-card-header">
                                                    <div class="detail-card-icon"><i class="fas fa-building"></i>
                                                    </div>
                                                    <div class="detail-status-card-title">Perintah Bangun &amp; Akad
                                                    </div>
                                                </div>

                                                <div class="detail-section-title detail-section-title-dot mb-2">
                                                    Perintah Bangun</div>

                                                <div class="detail-info-row">
                                                    <div class="detail-info-col">
                                                        <span class="detail-info-label">Tgl Perintah</span>
                                                        <input type="text" id="dt-st_perintah_bangun_tgl"
                                                            name="dt-st_perintah_bangun_tgl"
                                                            class="detail-info-value flatpickr-human-friendly"
                                                            placeholder="-" disabled />
                                                    </div>
                                                    <div class="detail-info-col text-right">
                                                        <span class="detail-info-label">Oleh</span>
                                                        <input type="text" id="dt-st_perintah_bangun_oleh"
                                                            name="dt-st_perintah_bangun_oleh"
                                                            class="detail-info-value" placeholder="-" disabled />
                                                    </div>
                                                </div>

                                                <a href="#" class="btn btn-block detail-file-btn detail-file-btn-success"
                                                    target="_blank" id="dt-st_list-upload_perintah_bangun_file">
                                                    <i class="fas fa-file-alt mr-1"></i> Klik untuk lihat file
                                                </a>

                                                <div class="detail-section-divider">
                                                    <span class="detail-section-title detail-section-title-dot">Akad</span>
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" class="custom-control-input"
                                                            id="dt-st_akad" name="dt-st_akad" value="1" disabled />
                                                        <label class="custom-control-label" for="dt-st_akad"></label>
                                                    </div>
                                                </div>

                                                <div class="detail-info-row">
                                                    <div class="detail-info-col">
                                                        <span class="detail-info-label">Rencana Akad</span>
                                                        <input type="text" id="dt-st_rencana_akad_tgl"
                                                            name="dt-st_rencana_akad_tgl"
                                                            class="detail-info-value flatpickr-human-friendly"
                                                            placeholder="-" disabled />
                                                    </div>
                                                    <div class="detail-info-col text-right">
                                                        <span class="detail-info-label">Tanggal Akad</span>
                                                        <input type="text" id="dt-st_akad_tgl" name="dt-st_akad_tgl"
                                                            class="detail-info-value flatpickr-human-friendly"
                                                            placeholder="-" disabled />
                                                    </div>
                                                </div>

                                                <div class="detail-info-row">
                                                    <div class="detail-info-col">
                                                        <span class="detail-info-label">No Debitur</span>
                                                        <input type="text" id="dt-st_debitur_no"
                                                            name="dt-st_debitur_no" class="detail-info-value"
                                                            placeholder="-" disabled />
                                                    </div>
                                                    <div class="detail-info-col text-right">
                                                        <span class="detail-info-label">No BAST</span>
                                                        <input type="text" id="dt-st_bast_no" name="dt-st_bast_no"
                                                            class="detail-info-value" placeholder="-" disabled />
                                                    </div>
                                                </div>

                                                <a href="" class="btn btn-primary btn-block detail-file-btn"
                                                    target="_blank" id="dt-st_list-upload_bast_file">
                                                    <i class="fas fa-shield-alt mr-1"></i> Lihat BAST
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane" id="dt-tagihan" aria-labelledby="dt-tagihan-tab"
                                        role="tabpanel">
                                        <small id="last_update_keuangan" class="text-muted"></small>
                                        <div class="row">
                                            <div class="col-md-4 col-sm-12 col-lg-4">
                                                <div class="divider">
                                                    <div class="divider-text">Total Uang Muka</div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="dt-total_biaya_um">Total Uang Muka</label>
                                                    <input readonly type="text" class="form-control num"
                                                        id="dt-total_biaya_um" name="dt-total_biaya_um">
                                                </div>

                                                <hr>
                                                <div class="form-group">
                                                    <label for="dt-sudah_bayar_um">Sudah Bayar Uang Muka</label>
                                                    <input type="text" class="form-control num" readonly
                                                        id="dt-sudah_bayar_um" name="dt-sudah_bayar_um">
                                                </div>
                                                <div class="form-group">
                                                    <label for="dt-sisa_tagihan_um">Sisa Tagihan Uang Muka</label>
                                                    <input type="text" class="form-control num" readonly
                                                        id="dt-sisa_tagihan_um" name="dt-sisa_tagihan_um">
                                                </div>
                                                <div class="form-group">
                                                    <label for="dt-persentase_bayar_tagihan_um">Persentase</label>
                                                    <input type="text" class="form-control" style="text-align:right"
                                                        readonly id="dt-persentase_bayar_tagihan_um"
                                                        name="dt-persentase_bayar_tagihan_um">
                                                </div>
                                            </div>
                                            <div class="col-md-4 col-sm-12 col-lg-4">
                                                <div class="divider">
                                                    <div class="divider-text">Total Biaya Adm + Turun KPR</div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="dt-total_biaya_um">Total Tagihan</label>
                                                    <input readonly type="text" class="form-control num"
                                                        id="dt-total_biaya_um_ll" name="dt-total_biaya_um_ll">
                                                </div>

                                                <hr>
                                                <div class="form-group">
                                                    <label for="dt-sudah_bayar_um">Sudah Bayar </label>
                                                    <input type="text" class="form-control num" readonly
                                                        id="dt-sudah_bayar_um_ll" name="dt-sudah_bayar_um_ll">
                                                </div>
                                                <div class="form-group">
                                                    <label for="dt-sisa_tagihan_um">Sisa Tagihan</label>
                                                    <input type="text" class="form-control num" readonly
                                                        id="dt-sisa_tagihan_um_ll" name="dt-sisa_tagihan_um_ll">
                                                </div>
                                                <div class="form-group">
                                                    <label for="dt-persentase_bayar_tagihan_um">Persentase</label>
                                                    <input type="text" class="form-control" style="text-align:right"
                                                        readonly id="dt-persentase_bayar_tagihan_um_ll"
                                                        name="dt-persentase_bayar_tagihan_um_ll">
                                                </div>
                                            </div>
                                            <div class="col-md-4 col-sm-12 col-lg-4">

                                                <div class="divider">
                                                    <div class="divider-text">Total Biaya-biaya</div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="dt-total_biaya_bb">Total Biaya-biaya</label>
                                                    <input readonly type="text" class="form-control num"
                                                        id="dt-total_biaya_bb" name="dt-total_biaya_bb">
                                                </div>

                                                <hr>
                                                <div class="form-group">
                                                    <label for="dt-sudah_bayar_bb">Sudah Bayar Biaya-biaya</label>
                                                    <input type="text" class="form-control num" readonly
                                                        id="dt-sudah_bayar_bb" name="dt-sudah_bayar_bb">
                                                </div>
                                                <div class="form-group">
                                                    <label for="dt-sisa_tagihan_um">Sisa Tagihan Biaya-biaya</label>
                                                    <input type="text" class="form-control num" readonly
                                                        id="dt-sisa_tagihan_bb" name="dt-sisa_tagihan_bb">
                                                </div>
                                                <div class="form-group">
                                                    <label for="dt-persentase_bayar_tagihan_bb">Persentase</label>
                                                    <input type="text" class="form-control" style="text-align:right"
                                                        readonly id="dt-persentase_bayar_tagihan_bb"
                                                        name="dt-persentase_bayar_tagihan_bb">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane" id="dt-legal" aria-labelledby="dt-legal-tab" role="tabpanel">
                                        <small id="last_update_legal" class="text-muted"></small>
                                        <div>
                                            <div class="card">
                                                <ul class="nav nav-pills flex-column flex-md-row mt-1 row-gap-2"
                                                    role="tablist">

                                                    <li class="nav-item">
                                                        <a class="nav-link active" id="dt-legal-sertifikat-tab"
                                                            data-toggle="tab" href="#dt-legal-sertifikat"
                                                            aria-controls="home" role="tab"
                                                            aria-selected="true">Sertipikat</a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a class="nav-link " id="dt-legal-pbb-tab" data-toggle="tab"
                                                            href="#dt-legal-pbb" aria-controls="home" role="tab"
                                                            aria-selected="true">PBB</a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a class="nav-link " id="dt-legal-bphtb-tab" data-toggle="tab"
                                                            href="#dt-legal-bphtb" aria-controls="home" role="tab"
                                                            aria-selected="true">BPHTB</a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a class="nav-link " id="dt-legal-pbg-tab" data-toggle="tab"
                                                            href="#dt-legal-pbg" aria-controls="home" role="tab"
                                                            aria-selected="true">IMB/PBG</a>
                                                    </li>

                                                    <li class="nav-item">
                                                        <a class="nav-link " id="dt-legal-pph-tab" data-toggle="tab"
                                                            href="#dt-legal-pph" aria-controls="home" role="tab"
                                                            aria-selected="true">PPH</a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a class="nav-link " id="dt-legal-ajb-tab" data-toggle="tab"
                                                            href="#dt-legal-ajb" aria-controls="home" role="tab"
                                                            aria-selected="true">AJB</a>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="tab-content">

                                                <div class="tab-pane" id="dt-legal-pbb"
                                                    aria-labelledby="dt-legal-pbb-tab" role="tabpanel">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="detail-summary-card mb-1">
                                                                <div class="detail-status-card-header">
                                                                    <div class="detail-card-icon"><i class="fas fa-map-marked-alt"></i></div>
                                                                    <div class="detail-status-card-title">Mutasi Pecah PBB</div>
                                                                </div>
                                                                <div class="detail-info-row">
                                                                    <div class="detail-info-col">
                                                                        <span class="detail-info-label">NOP PBB</span>
                                                                        <input type="text" class="detail-info-value" id="dt-pbb_pecah_nop" name="dt-pbb_pecah_nop" readonly>
                                                                    </div>
                                                                </div>
                                                                <div class="detail-info-row">
                                                                    <div class="detail-info-col">
                                                                        <span class="detail-info-label">Luas Bumi</span>
                                                                        <input type="text" class="detail-info-value" id="dt-pbb_pecah_luas_bumi" name="dt-pbb_pecah_luas_bumi" readonly>
                                                                    </div>
                                                                    <div class="detail-info-col text-right">
                                                                        <span class="detail-info-label">NJOP Bumi</span>
                                                                        <input type="text" class="detail-info-value num" id="dt-pbb_pecah_njop_bumi" name="dt-pbb_pecah_njop_bumi" readonly>
                                                                    </div>
                                                                </div>
                                                                <div class="detail-info-row">
                                                                    <div class="detail-info-col">
                                                                        <span class="detail-info-label">Luas Bangunan</span>
                                                                        <input type="text" class="detail-info-value" id="dt-pbb_pecah_luas_bangunan" name="dt-pbb_pecah_luas_bangunan" readonly>
                                                                    </div>
                                                                    <div class="detail-info-col text-right">
                                                                        <span class="detail-info-label">NJOP Bangunan</span>
                                                                        <input type="text" class="detail-info-value num" id="dt-pbb_pecah_njop_bangunan" name="dt-pbb_pecah_njop_bangunan" readonly>
                                                                    </div>
                                                                </div>
                                                                <div class="detail-info-row">
                                                                    <div class="detail-info-col">
                                                                        <span class="detail-info-label">Tanggal Bayar</span>
                                                                        <input type="text" id="dt-pbb_pecah_tanggal_bayar" name="dt-pbb_pecah_tanggal_bayar" class="detail-info-value flatpickr-human-friendly" placeholder="-" readonly />
                                                                    </div>
                                                                </div>
                                                                <div class="detail-highlight-box mb-0">
                                                                    <span class="detail-highlight-label">Jumlah Tagihan</span>
                                                                    <input type="text" class="detail-highlight-value num" id="dt-pbb_pecah_jumlah_tagihan" name="dt-pbb_pecah_jumlah_tagihan" readonly>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="detail-summary-card mb-1">
                                                                <div class="detail-status-card-header">
                                                                    <div class="detail-card-icon"><i class="fas fa-edit"></i></div>
                                                                    <div class="detail-status-card-title">Pembetulan PBB</div>
                                                                    <div class="detail-status-card-toggle">
                                                                        <span class="detail-status-badge badge-secondary" id="dt-pbb_is_pembetulan-badge">-</span>
                                                                    </div>
                                                                </div>
                                                                <select name="dt-pbb_is_pembetulan" id="dt-pbb_is_pembetulan" class="hidden" disabled>
                                                                    <option value="Tidak">Tidak</option>
                                                                    <option value="Iya">Iya</option>
                                                                </select>
                                                                <div id="select-pbb_is_pembetulan">
                                                                    <div class="detail-info-row">
                                                                        <div class="detail-info-col">
                                                                            <span class="detail-info-label">Tanggal Pembetulan</span>
                                                                            <input type="text" id="dt-pbb_tgl_pembetulan" name="dt-pbb_tgl_pembetulan" class="detail-info-value flatpickr-human-friendly" placeholder="-" readonly />
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="detail-summary-card mb-1">
                                                                <div class="detail-status-card-header">
                                                                    <div class="detail-card-icon"><i class="fas fa-exchange-alt"></i></div>
                                                                    <div class="detail-status-card-title">Balik Nama PBB</div>
                                                                    <div class="detail-status-card-toggle">
                                                                        <span class="detail-status-badge badge-secondary" id="dt-pbb_is_balik_nama-badge">-</span>
                                                                    </div>
                                                                </div>
                                                                <select name="dt-pbb_is_balik_nama" id="dt-pbb_is_balik_nama" class="hidden" disabled>
                                                                    <option value="Belum">Belum</option>
                                                                    <option value="Sudah">Sudah</option>
                                                                </select>
                                                                <div class="select-pbb_is_balik_nama">
                                                                    <div class="detail-info-row">
                                                                        <div class="detail-info-col">
                                                                            <span class="detail-info-label">Nama Konsumen</span>
                                                                            <input type="text" readonly id="dt-pbb_balik_nama" class="detail-info-value" name="dt-pbb_balik_nama">
                                                                        </div>
                                                                    </div>
                                                                    <div class="detail-info-row">
                                                                        <div class="detail-info-col">
                                                                            <span class="detail-info-label">Tanggal Pengiriman</span>
                                                                            <input type="text" id="dt-pbb_balik_nama_tgl_pengiriman" name="dt-pbb_balik_nama_tgl_pengiriman" class="detail-info-value flatpickr-human-friendly" placeholder="-" readonly />
                                                                        </div>
                                                                        <div class="detail-info-col text-right">
                                                                            <span class="detail-info-label">Dikirim Ke Bank/Konsumen/Notaris</span>
                                                                            <input type="text" class="detail-info-value" id="dt-pbb_balik_nama_ke" name="dt-pbb_balik_nama_ke" readonly>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>
                                                <div class="tab-pane active" id="dt-legal-sertifikat"
                                                    aria-labelledby="dt-legal-sertifikat-tab" role="tabpanel">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="detail-summary-card mb-1">
                                                                <div class="detail-status-card-header">
                                                                    <div class="detail-card-icon"><i class="fas fa-file-contract"></i></div>
                                                                    <div class="detail-status-card-title">Sertipikat</div>
                                                                    <div class="detail-status-card-toggle">
                                                                        <span class="detail-status-badge badge-secondary" id="dt-sertifikat_is_split-badge">-</span>
                                                                    </div>
                                                                </div>
                                                                <select name="dt-sertifikat_is_split" id="dt-sertifikat_is_split" class="hidden" disabled>
                                                                    <option value="0">Tidak</option>
                                                                    <option value="1">Ya</option>
                                                                </select>
                                                                <div class="detail-info-row">
                                                                    <div class="detail-info-col">
                                                                        <span class="detail-info-label">No HGB Induk/Nibel</span>
                                                                        <input type="text" class="detail-info-value" id="dt-sertifikat_split_no_hgb_induk" name="dt-sertifikat_split_no_hgb_induk" readonly>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="detail-summary-card mb-1">
                                                                <div class="detail-status-card-header">
                                                                    <div class="detail-card-icon"><i class="fas fa-clone"></i></div>
                                                                    <div class="detail-status-card-title">Sertipikat Split</div>
                                                                </div>
                                                                <div class="select-sertifikat_is_split">
                                                                    <div class="detail-info-row">
                                                                        <div class="detail-info-col">
                                                                            <span class="detail-info-label">No HGB</span>
                                                                            <input type="text" class="detail-info-value" id="dt-sertifikat_split_no_hgb" name="dt-sertifikat_split_no_hgb" readonly>
                                                                        </div>
                                                                    </div>
                                                                    <div class="detail-info-row">
                                                                        <div class="detail-info-col">
                                                                            <span class="detail-info-label">Terbit</span>
                                                                            <input type="text" id="dt-sertifikat_split_tanggal_terbit" name="dt-sertifikat_split_tanggal_terbit" class="detail-info-value flatpickr-human-friendly" placeholder="-" readonly />
                                                                        </div>
                                                                        <div class="detail-info-col text-right">
                                                                            <span class="detail-info-label">Berakhir</span>
                                                                            <input type="text" id="dt-sertifikat_split_tanggal_berakhir" name="dt-sertifikat_split_tanggal_berakhir" class="detail-info-value detail-text-danger flatpickr-human-friendly" placeholder="-" readonly />
                                                                        </div>
                                                                    </div>
                                                                    <div class="detail-info-row">
                                                                        <div class="detail-info-col">
                                                                            <span class="detail-info-label">NIB</span>
                                                                            <input type="text" class="detail-info-value" id="dt-sertifikat_split_nib" name="dt-sertifikat_split_nib" readonly>
                                                                        </div>
                                                                        <div class="detail-info-col text-right">
                                                                            <span class="detail-info-label">Luas Tanah (m2)</span>
                                                                            <input type="text" class="detail-info-value" id="dt-sertifikat_split_luas_tanah" name="dt-sertifikat_split_luas_tanah" readonly>
                                                                        </div>
                                                                    </div>
                                                                    <div class="detail-info-row">
                                                                        <div class="detail-info-col">
                                                                            <span class="detail-info-label">Tanggal Surat Ukur</span>
                                                                            <input type="text" id="dt-sertifikat_split_tanggal_surat_ukur" name="dt-sertifikat_split_tanggal_surat_ukur" class="detail-info-value flatpickr-human-friendly" placeholder="-" readonly />
                                                                        </div>
                                                                        <div class="detail-info-col text-right">
                                                                            <span class="detail-info-label">No Surat Ukur</span>
                                                                            <input type="text" id="dt-sertifikat_split_no_surat_ukur" name="dt-sertifikat_split_no_surat_ukur" class="detail-info-value" placeholder="-" readonly />
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="detail-summary-card mb-1">
                                                                <div class="detail-status-card-header">
                                                                    <div class="detail-card-icon"><i class="fas fa-exchange-alt"></i></div>
                                                                    <div class="detail-status-card-title">Sertipikat Balik Nama</div>
                                                                    <div class="detail-status-card-toggle">
                                                                        <span class="detail-status-badge badge-secondary" id="dt-sertifikat_is_balik_nama-badge">-</span>
                                                                    </div>
                                                                </div>
                                                                <select name="dt-sertifikat_is_balik_nama" class="hidden" id="dt-sertifikat_is_balik_nama" disabled>
                                                                    <option value="Belum">Belum</option>
                                                                    <option value="Sudah">Sudah</option>
                                                                </select>
                                                                <div class="select-sertifikat_is_balik_nama">
                                                                    <div class="detail-info-row">
                                                                        <div class="detail-info-col">
                                                                            <span class="detail-info-label">Nama Konsumen</span>
                                                                            <input type="text" readonly class="detail-info-value" id="dt-sertifikat_balik_nama" name="dt-sertifikat_balik_nama">
                                                                        </div>
                                                                    </div>
                                                                    <div class="detail-info-row">
                                                                        <div class="detail-info-col">
                                                                            <span class="detail-info-label">NIB Elektronik</span>
                                                                            <input type="text" class="detail-info-value" id="dt-sertifikat_nib_elektronik" name="dt-sertifikat_nib_elektronik" readonly>
                                                                        </div>
                                                                        <div class="detail-info-col text-right">
                                                                            <span class="detail-info-label">Tanggal Pengiriman</span>
                                                                            <input type="text" id="dt-sertifikat_balik_nama_tgl_pengiriman" name="dt-sertifikat_balik_nama_tgl_pengiriman" class="detail-info-value flatpickr-human-friendly" placeholder="-" readonly />
                                                                        </div>
                                                                    </div>
                                                                    <div class="detail-info-row">
                                                                        <div class="detail-info-col">
                                                                            <span class="detail-info-label">Dikirim Ke Bank/Konsumen/Notaris</span>
                                                                            <input type="text" class="detail-info-value" id="dt-sertifikat_balik_nama_ke" name="dt-sertifikat_balik_nama_ke" readonly>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="tab-pane " id="dt-legal-pbg"
                                                    aria-labelledby="dt-legal-pbg-tab" role="tabpanel">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="detail-summary-card mb-1">
                                                                <div class="detail-status-card-header">
                                                                    <div class="detail-card-icon"><i class="fas fa-drafting-compass"></i></div>
                                                                    <div class="detail-status-card-title">IMB/PBG</div>
                                                                    <div class="detail-status-card-toggle">
                                                                        <span class="detail-status-badge badge-secondary" id="dt-pbg_status-badge">-</span>
                                                                    </div>
                                                                </div>
                                                                <select id="dt-pbg_status" name="dt-pbg_status" class="hidden" disabled>
                                                                    <option value="">-</option>
                                                                    <option value="Proses">Proses</option>
                                                                    <option value="Selesai">Selesai</option>
                                                                    <option value="Terjadi Masalah">Terjadi Masalah</option>
                                                                </select>
                                                                <div class="detail-info-row">
                                                                    <div class="detail-info-col">
                                                                        <span class="detail-info-label">No IMB/PBG</span>
                                                                        <input type="text" class="detail-info-value" id="dt-pbg_no" name="dt-pbg_no" readonly>
                                                                    </div>
                                                                    <div class="detail-info-col text-right">
                                                                        <span class="detail-info-label">Tanggal Terbit</span>
                                                                        <input type="text" id="dt-pbg_tanggal_terbit" name="dt-pbg_tanggal_terbit" class="detail-info-value flatpickr-human-friendly" placeholder="-" readonly />
                                                                    </div>
                                                                </div>
                                                                <div class="detail-info-row">
                                                                    <div class="detail-info-col">
                                                                        <span class="detail-info-label">Tipe</span>
                                                                        <input type="text" id="dt-pbg_tipe" name="dt-pbg_tipe" class="detail-info-value" placeholder="-" readonly />
                                                                    </div>
                                                                </div>
                                                                <div class="detail-section-divider">
                                                                    <span class="detail-section-title detail-section-title-dot">Pengiriman</span>
                                                                </div>
                                                                <div class="detail-info-row">
                                                                    <div class="detail-info-col">
                                                                        <span class="detail-info-label">Dikirim Ke Bank/Konsumen</span>
                                                                        <select name="dt-pbg_dikirim_ke" class="detail-info-value" id="dt-pbg_dikirim_ke" disabled>
                                                                            <option value="null"></option>
                                                                            <option value="Bank BTN">Bank BTN</option>
                                                                            <option value="Konsumen">Konsumen</option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="detail-info-col text-right">
                                                                        <span class="detail-info-label">Tanggal Kirim</span>
                                                                        <input type="text" id="dt-pbg_tanggal_kirim" name="dt-pbg_tanggal_kirim" class="detail-info-value flatpickr-human-friendly" placeholder="-" readonly />
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="detail-summary-card mb-1">
                                                                <div class="detail-status-card-header">
                                                                    <div class="detail-card-icon"><i class="fas fa-history"></i></div>
                                                                    <div class="detail-status-card-title">Revisi IMB/PBG</div>
                                                                    <div class="detail-status-card-toggle">
                                                                        <span class="detail-status-badge badge-secondary" id="dt-pbg_is_revisi-badge">-</span>
                                                                    </div>
                                                                </div>
                                                                <select name="dt-pbg_is_revisi" class="hidden" id="dt-pbg_is_revisi" disabled>
                                                                    <option value="Tidak">Tidak</option>
                                                                    <option value="Ya">Ya</option>
                                                                </select>
                                                                <div class="select-pbg_is_revisi">
                                                                    <div class="detail-info-row">
                                                                        <div class="detail-info-col">
                                                                            <span class="detail-info-label">No IMB/PBG</span>
                                                                            <input type="text" class="detail-info-value" id="dt-pbg_no_revisi" name="dt-pbg_no_revisi" readonly>
                                                                        </div>
                                                                    </div>
                                                                    <div class="detail-info-row">
                                                                        <div class="detail-info-col">
                                                                            <span class="detail-info-label">Tanggal Terbit</span>
                                                                            <input type="text" id="dt-pbg_tanggal_terbit_revisi" name="dt-pbg_tanggal_terbit_revisi" class="detail-info-value flatpickr-human-friendly" placeholder="-" readonly />
                                                                        </div>
                                                                        <div class="detail-info-col text-right">
                                                                            <span class="detail-info-label">Tipe</span>
                                                                            <input type="text" id="dt-pbg_tipe_revisi" name="dt-pbg_tipe_revisi" class="detail-info-value" placeholder="-" readonly />
                                                                        </div>
                                                                    </div>
                                                                    <div class="detail-info-row">
                                                                        <div class="detail-info-col">
                                                                            <span class="detail-info-label">Status</span>
                                                                            <select id="dt-pbg_status_revisi" name="dt-pbg_status_revisi" class="detail-info-value" disabled>
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
                                                <div class="tab-pane " id="dt-legal-bphtb"
                                                    aria-labelledby="dt-legal-bphtb-tab" role="tabpanel">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="detail-summary-card mb-1">
                                                                <div class="detail-status-card-header">
                                                                    <div class="detail-card-icon"><i class="fas fa-check-circle"></i></div>
                                                                    <div class="detail-status-card-title">Verifikasi BPHTB</div>
                                                                </div>
                                                                <div class="detail-info-row">
                                                                    <div class="detail-info-col">
                                                                        <span class="detail-info-label">Tanggal Verifikasi</span>
                                                                        <input type="text" class="detail-info-value flatpickr-human-friendly" id="dt-bphtb_tanggal_verifikasi" name="dt-bphtb_tanggal_verifikasi" readonly>
                                                                    </div>
                                                                    <div class="detail-info-col text-right">
                                                                        <span class="detail-info-label">Jatuh Tempo</span>
                                                                        <input type="text" class="detail-info-value flatpickr-human-friendly" id="dt-bphtb_jatuh_tempo" name="dt-bphtb_jatuh_tempo" readonly>
                                                                    </div>
                                                                </div>
                                                                <div class="detail-info-row">
                                                                    <div class="detail-info-col">
                                                                        <span class="detail-info-label">Tanggal Perpanjangan Jatuh Tempo</span>
                                                                        <input type="text" class="detail-info-value flatpickr-human-friendly" id="dt-bphtb_perpanjang_jatuh_tempo" name="dt-bphtb_perpanjang_jatuh_tempo" readonly>
                                                                    </div>
                                                                    <div class="detail-info-col text-right">
                                                                        <span class="detail-info-label">Tanggal Pembayaran</span>
                                                                        <input type="text" class="detail-info-value flatpickr-human-friendly" id="dt-bphtb_tanggal_pembayaran" name="dt-bphtb_tanggal_pembayaran" readonly>
                                                                    </div>
                                                                </div>
                                                                <div class="detail-highlight-box mb-0">
                                                                    <span class="detail-highlight-label">Nominal Yang Disetujui</span>
                                                                    <input type="text" readonly class="detail-highlight-value num" id="dt-bphtb_nominal_disetujui" name="dt-bphtb_nominal_disetujui">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="detail-summary-card mb-1">
                                                                <div class="detail-status-card-header">
                                                                    <div class="detail-card-icon"><i class="fas fa-stamp"></i></div>
                                                                    <div class="detail-status-card-title">Validasi BPHTB</div>
                                                                </div>
                                                                <div class="detail-info-row">
                                                                    <div class="detail-info-col">
                                                                        <span class="detail-info-label">Tanggal Validasi</span>
                                                                        <input type="text" class="detail-info-value flatpickr-human-friendly" id="dt-bphtb_tanggal_validasi" name="dt-bphtb_tanggal_validasi" readonly>
                                                                    </div>
                                                                    <div class="detail-info-col text-right">
                                                                        <span class="detail-info-label">No NTPD</span>
                                                                        <input type="text" class="detail-info-value" id="dt-bphtb_nominal_tervalidasi" name="dt-bphtb_nominal_tervalidasi" readonly>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="tab-pane " id="dt-legal-pph"
                                                    aria-labelledby="dt-legal-pph-tab" role="tabpanel">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="detail-summary-card mb-1">
                                                                <div class="detail-status-card-header">
                                                                    <div class="detail-card-icon"><i class="fas fa-money-check-alt"></i></div>
                                                                    <div class="detail-status-card-title">PPH</div>
                                                                </div>
                                                                <div class="detail-highlight-box">
                                                                    <span class="detail-highlight-label">Nominal Dibayar</span>
                                                                    <input type="text" class="detail-highlight-value num" id="dt-pph_nominal_bayar" name="dt-pph_nominal_bayar" readonly>
                                                                </div>
                                                                <div class="detail-info-row">
                                                                    <div class="detail-info-col">
                                                                        <span class="detail-info-label">Tanggal Bayar</span>
                                                                        <input type="text" class="detail-info-value flatpickr-human-friendly" id="dt-pph_tgl_bayar" name="dt-pph_tgl_bayar" readonly>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="detail-summary-card mb-1">
                                                                <div class="detail-status-card-header">
                                                                    <div class="detail-card-icon"><i class="fas fa-clipboard-check"></i></div>
                                                                    <div class="detail-status-card-title">Validasi PPH</div>
                                                                </div>
                                                                <div class="detail-info-row">
                                                                    <div class="detail-info-col">
                                                                        <span class="detail-info-label">Jenis Validasi</span>
                                                                        <select class="detail-info-value" id="dt-pph_jenis_validasi" name="dt-pph_jenis_validasi" disabled>
                                                                            <option value=""></option>
                                                                            <option value="Offline">Offline</option>
                                                                            <option value="Online">Online</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="select-pph-validasi-offline" class="hide">
                                                                    <div class="detail-info-row">
                                                                        <div class="detail-info-col">
                                                                            <span class="detail-info-label">Tanggal Validasi</span>
                                                                            <input type="text" class="detail-info-value flatpickr-human-friendly" id="dt-pph_tanggal_validasi" name="dt-pph_tanggal_validasi" readonly>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="select-pph-validasi-online" class="hide">
                                                                    <div class="detail-info-row">
                                                                        <div class="detail-info-col">
                                                                            <span class="detail-info-label">Tanggal Permohonan</span>
                                                                            <input type="text" class="detail-info-value flatpickr-human-friendly" id="dt-pph_tgl_permohonan" name="dt-pph_tgl_permohonan" readonly>
                                                                        </div>
                                                                        <div class="detail-info-col text-right">
                                                                            <span class="detail-info-label">Tanggal Selesai</span>
                                                                            <input type="text" class="detail-info-value flatpickr-human-friendly" id="dt-pph_tgl_selesai" name="dt-pph_tgl_selesai" readonly>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="detail-info-row">
                                                                    <div class="detail-info-col">
                                                                        <span class="detail-info-label">NTPN</span>
                                                                        <input type="text" class="detail-info-value" id="dt-pph_ntpn" name="dt-pph_ntpn" readonly>
                                                                    </div>
                                                                    <div class="detail-info-col text-right">
                                                                        <span class="detail-info-label">No SKET</span>
                                                                        <input type="text" class="detail-info-value" id="dt-pph_no_sket" name="dt-pph_no_sket" readonly>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="tab-pane " id="dt-legal-ajb"
                                                    aria-labelledby="dt-legal-ajb-tab" role="tabpanel">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="detail-summary-card mb-1">
                                                                <div class="detail-status-card-header">
                                                                    <div class="detail-card-icon"><i class="fas fa-file-signature"></i></div>
                                                                    <div class="detail-status-card-title">AJB</div>
                                                                </div>
                                                                <div class="detail-info-row">
                                                                    <div class="detail-info-col">
                                                                        <span class="detail-info-label">No AJB</span>
                                                                        <input type="text" class="detail-info-value" id="dt-ajb_no" name="dt-ajb_no" readonly>
                                                                    </div>
                                                                    <div class="detail-info-col text-right">
                                                                        <span class="detail-info-label">Tanggal AJB</span>
                                                                        <input type="text" class="detail-info-value flatpickr-human-friendly" id="dt-ajb_tanggal" name="dt-ajb_tanggal" readonly>
                                                                    </div>
                                                                </div>
                                                                <div class="detail-info-row">
                                                                    <div class="detail-info-col">
                                                                        <span class="detail-info-label">Notaris</span>
                                                                        <input type="text" class="detail-info-value" id="dt-ajb_notaris" name="dt-ajb_notaris" readonly>
                                                                    </div>
                                                                    <div class="detail-info-col text-right">
                                                                        <span class="detail-info-label">Dikirim Ke Bank/Konsumen</span>
                                                                        <input type="text" class="detail-info-value" id="dt-ajb_dikirim_ke" name="dt-ajb_dikirim_ke" readonly>
                                                                    </div>
                                                                </div>
                                                                <div class="detail-info-row">
                                                                    <div class="detail-info-col">
                                                                        <span class="detail-info-label">Tanggal Dikirim Ke Bank/Konsumen</span>
                                                                        <input type="text" class="detail-info-value flatpickr-human-friendly" id="dt-ajb_tanggal_dikirim" name="dt-ajb_tanggal_dikirim" readonly>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="detail-summary-card mb-1">
                                                                <div class="detail-status-card-header">
                                                                    <div class="detail-card-icon"><i class="fas fa-file-alt"></i></div>
                                                                    <div class="detail-status-card-title">PPJB</div>
                                                                </div>
                                                                <div class="detail-info-row">
                                                                    <div class="detail-info-col">
                                                                        <span class="detail-info-label">No PPJB</span>
                                                                        <input type="text" class="detail-info-value" id="dt-ppjb_no" name="dt-ppjb_no" readonly>
                                                                    </div>
                                                                    <div class="detail-info-col text-right">
                                                                        <span class="detail-info-label">Tanggal PPJB</span>
                                                                        <input type="text" class="detail-info-value flatpickr-human-friendly" id="dt-ppjb_tanggal" name="dt-ppjb_tanggal" readonly>
                                                                    </div>
                                                                </div>
                                                                <div class="detail-info-row">
                                                                    <div class="detail-info-col">
                                                                        <span class="detail-info-label">Notaris</span>
                                                                        <input type="text" class="detail-info-value" id="dt-ppjb_notaris" name="dt-ppjb_notaris" readonly>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane" id="dt-spesifikasi" aria-labelledby="dt-spesifikasi-tab"
                                        role="tabpanel">
                                        <div class="detail-card-grid mb-1" id="dt-spesifikasi-summary">
                                            <div class="detail-mini-card">
                                                <div class="detail-mini-label">Tipe</div>
                                                <div class="detail-mini-value" id="dt-spesifikasi-tipe">-</div>
                                            </div>
                                            <div class="detail-mini-card">
                                                <div class="detail-mini-label">Luas Bangunan</div>
                                                <div class="detail-mini-value" id="dt-spesifikasi-lb">-</div>
                                            </div>
                                            <div class="detail-mini-card">
                                                <div class="detail-mini-label">Luas Tanah</div>
                                                <div class="detail-mini-value" id="dt-spesifikasi-lt">-</div>
                                            </div>
                                            <div class="detail-mini-card">
                                                <div class="detail-mini-label">Kamar Tidur</div>
                                                <div class="detail-mini-value" id="dt-spesifikasi-kamar-tidur">-</div>
                                            </div>
                                            <div class="detail-mini-card">
                                                <div class="detail-mini-label">Kamar Mandi</div>
                                                <div class="detail-mini-value" id="dt-spesifikasi-kamar-mandi">-</div>
                                            </div>
                                        </div>
                                        <div class="divider divider-left">
                                            <div class="divider-text">Spesifikasi Teknis</div>
                                        </div>
                                        <div class="detail-card-grid mb-1" id="dt-spesifikasi-teknis">
                                            <div class="detail-mini-card">
                                                <div class="detail-mini-label">Atap</div>
                                                <div class="detail-mini-value" id="dt-spesifikasi-atap">-</div>
                                            </div>
                                            <div class="detail-mini-card">
                                                <div class="detail-mini-label">Dinding</div>
                                                <div class="detail-mini-value" id="dt-spesifikasi-dinding">-</div>
                                            </div>
                                            <div class="detail-mini-card">
                                                <div class="detail-mini-label">Lantai</div>
                                                <div class="detail-mini-value" id="dt-spesifikasi-lantai">-</div>
                                            </div>
                                            <div class="detail-mini-card">
                                                <div class="detail-mini-label">Pondasi</div>
                                                <div class="detail-mini-value" id="dt-spesifikasi-pondasi">-</div>
                                            </div>
                                        </div>
                                        <div class="divider divider-left">
                                            <div class="divider-text">Gambar Tipe</div>
                                        </div>
                                        <div class="detail-card-grid" id="dt-spesifikasi-files">
                                            <div class="detail-spec-file-tile" id="dt-spesifikasi-gambar-tipe"></div>
                                            <div class="detail-spec-file-tile" id="dt-spesifikasi-gambar-denah"></div>
                                        </div>
                                    </div>
                                    <div class="tab-pane" id="dt-produksi" aria-labelledby="dt-produksi-tab"
                                        role="tabpanel">
                                        <small id="last_update_produksi" class="text-muted"></small>
                                        <ul class="nav nav-tabs" role="tablist">
                                            <li class="nav-item">
                                                <a class="nav-link active" id="dt-fm-prod-progress-tab"
                                                    data-toggle="tab" href="#dt-fm-prod-progress" role="tab"
                                                    aria-selected="true">Progres</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" id="dt-fm-prod-bayar_produksi-tab" data-toggle="tab"
                                                    href="#dt-fm-prod-bayar_produksi" role="tab"
                                                    aria-selected="true">Pembayaran</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" id="dt-fm-prod-dokumentasi-tab" data-toggle="tab"
                                                    href="#dt-fm-prod-dokumentasi" role="tab"
                                                    aria-selected="true">Dokumentasi Bangunan</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" id="dt-fm-prod-jalan-tab" data-toggle="tab"
                                                    href="#dt-fm-prod-jalan" role="tab" aria-selected="true">Jalan</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" id="dt-fm-prod-listrik-tab" data-toggle="tab"
                                                    href="#dt-fm-prod-listrik" role="tab"
                                                    aria-selected="true">Listrik</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" id="dt-fm-prod-air-tab" data-toggle="tab"
                                                    href="#dt-fm-prod-air" role="tab" aria-selected="true">Air</a>
                                            </li>
                                        </ul>
                                        <div class="tab-content">
                                            <div class="tab-pane active" id="dt-fm-prod-progress"
                                                aria-labelledby="dt-fm-prod-progress-tab" role="tabpanel">
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <div
                                                                class="custom-control custom-switch custom-control-inline">
                                                                <input type="checkbox" value="1"
                                                                    class="custom-control-input cbp" id="dt-st_0"
                                                                    name="dt-st_0" disabled />
                                                                <label class="custom-control-label" for="dt-st_0">sd
                                                                    Sloof</label>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <div
                                                                class="custom-control custom-switch custom-control-inline">
                                                                <input type="checkbox" value="1"
                                                                    class="custom-control-input cbp" id="dt-st_25"
                                                                    name="dt-st_25" disabled />
                                                                <label class="custom-control-label"
                                                                    for="dt-st_25">Dinding sd Ringbalok</label>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <div
                                                                class="custom-control custom-switch custom-control-inline">
                                                                <input type="checkbox" value="1"
                                                                    class="custom-control-input cbp" id="dt-st_50"
                                                                    name="dt-st_50" disabled />
                                                                <label class="custom-control-label"
                                                                    for="dt-st_50">Dinding Full, Atap, PLester
                                                                    dan Aci</label>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <div
                                                                class="custom-control custom-switch custom-control-inline">
                                                                <input type="checkbox" value="1"
                                                                    class="custom-control-input cbp" id="dt-st_75"
                                                                    name="dt-st_75" disabled />
                                                                <label class="custom-control-label"
                                                                    for="dt-st_75">Plafon, Keramik, Dapur,
                                                                    Kamar Mandi dan Cat</label>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <div
                                                                class="custom-control custom-switch custom-control-inline">
                                                                <input type="checkbox" value="1"
                                                                    class="custom-control-input cbp" id="dt-st_100"
                                                                    name="dt-st_100" disabled />
                                                                <label class="custom-control-label"
                                                                    for="dt-st_100">Kusen, Pintu, Jendela,
                                                                    Kaca, Halaman dan Finishing</label>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <div
                                                                class="custom-control custom-switch custom-control-inline">
                                                                <input type="checkbox" value="1"
                                                                    class="custom-control-input cbp" id="dt-st_saluran"
                                                                    name="dt-st_saluran" disabled />
                                                                <label class="custom-control-label"
                                                                    for="dt-st_saluran">Saluran Jalan</label>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <div
                                                                class="custom-control custom-switch custom-control-inline">
                                                                <input type="checkbox" value="1"
                                                                    class="custom-control-input cbp" id="dt-st_jalan"
                                                                    name="dt-st_jalan" disabled />
                                                                <label class="custom-control-label"
                                                                    for="dt-st_jalan">Listrik</label>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <div
                                                                class="custom-control custom-switch custom-control-inline">
                                                                <input type="checkbox" value="1"
                                                                    class="custom-control-input cbp" id="dt-st_air"
                                                                    name="dt-st_air" disabled />
                                                                <label class="custom-control-label"
                                                                    for="dt-st_air">Air</label>
                                                            </div>
                                                        </div>
                                                        <!-- <div class="af"> -->
                                                        <div class="">
                                                            <div class="form-group">
                                                                <div
                                                                    class="custom-control custom-switch custom-control-inline">
                                                                    <input type="checkbox" value="1"
                                                                        class="custom-control-input cbp" id="dt-slo"
                                                                        name="dt-slo" disabled />
                                                                    <label class="custom-control-label" for="dt-slo">SLO
                                                                        / NIDI</label>
                                                                </div>
                                                            </div>
                                                            <!-- <div class="form-group">
                                                                <div class="custom-control custom-switch custom-control-inline">
                                                                    <input type="checkbox" value="1" class="custom-control-input cbp"
                                                                        id="dt-bp" name="dt-bp" disabled />
                                                                    <label class="custom-control-label" for="dt-bp">BP</label>
                                                                </div>
                                                            </div> -->

                                                        </div>

                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="divider">
                                                            <div class="divider-text">LPA</div>
                                                        </div>
                                                        <div class="form-group">
                                                            <div
                                                                class="custom-control custom-switch custom-control-inline">
                                                                <input type="checkbox" value="1"
                                                                    class="custom-control-input cbp" id="dt-lpa"
                                                                    name="dt-lpa" disabled />
                                                                <label class="custom-control-label"
                                                                    for="dt-lpa">LPA</label>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Tanggal LPA</label>
                                                            <input type="text"
                                                                class="form-control flatpickr-human-friendly"
                                                                id="dt-lpa_tanggal" name="dt-lpa_tanggal" disabled>
                                                        </div>
                                                        <div class="divider">
                                                            <div class="divider-text">Sumur Bor</div>
                                                        </div>
                                                        <div class="form-group">
                                                            <div
                                                                class="custom-control custom-switch custom-control-inline">
                                                                <input type="checkbox" value="1"
                                                                    class="custom-control-input cbp" id="dt-sumurbor"
                                                                    name="dt-sumurbor" disabled />
                                                                <label class="custom-control-label"
                                                                    for="dt-sumurbor">Sumur Bor</label>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Tanggal Pemasangan Sumur Bor</label>
                                                            <input type="text"
                                                                class="form-control flatpickr-human-friendly"
                                                                id="dt-sumurbor_tanggal" name="dt-sumurbor_tanggal"
                                                                disabled>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="dt-sumurbor_keterangan">Keterangan Sumur
                                                                Bor</label>
                                                            <textarea class="form-control" id="dt-sumurbor_keterangan"
                                                                name="dt-sumurbor_keterangan" rows="3"
                                                                placeholder="Keterangan" disabled></textarea>

                                                            <small id="dt-last_update-sumurbor"
                                                                class="text-muted"></small>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <label for="dt-progres_bangunan">Progres Bangunan</label>
                                                            <input type="range" class="form-control-range" value="0"
                                                                id="dt-progres_bangunan" name="dt-progres_bangunan"
                                                                step="1" disabled>
                                                            <span id="dt-t_progres_bangunan"></span>%
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="dt-produksi_keterangan">Keterangan
                                                                Pembangunan</label>
                                                            <textarea class="form-control" id="dt-produksi_keterangan"
                                                                name="dt-produksi_keterangan" rows="3"
                                                                placeholder="Keterangan" disabled></textarea>
                                                        </div>

                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="divider">
                                                            <div class="divider-text">Tanggal Pembangunan Rumah</div>
                                                        </div>
                                                        <div>

                                                            <div class="form-group">
                                                                <label>Tanggal Pembangunan</label>
                                                                <input type="text"
                                                                    class="form-control tanggal_pembangunan flatpickr-human-friendly tgl_bangun"
                                                                    id="dt-tanggal_pembangunan"
                                                                    name="dt-tanggal_pembangunan" disabled>
                                                                <input type="text" class="hidden"
                                                                    id="dt-tanggal_pembangunan_old"
                                                                    name="dt-tanggal_pembangunan_old" disabled>
                                                            </div>
                                                            <span class="text-muted"
                                                                id="dt-lu-tanggal_pembangunan"></span>

                                                            <div class="form-group">
                                                                <label>Tanggal Rencana Selesai Pembangunan</label>
                                                                <input type="text"
                                                                    class="form-control tanggal_rencana_selesai_pembangunan flatpickr-human-friendly tgl_bangun"
                                                                    id="dt-tanggal_rencana_selesai_pembangunan"
                                                                    name="dt-tanggal_rencana_selesai_pembangunan"
                                                                    disabled>
                                                                <input type="text" class="hidden"
                                                                    id="dt-tanggal_rencana_selesai_pembangunan_old"
                                                                    name="dt-tanggal_rencana_selesai_pembangunan_old"
                                                                    disabled>
                                                            </div>
                                                            <span class="text-muted"
                                                                id="dt-lu-tanggal_rencana_selesai_pembangunan"></span>


                                                            <div class="form-group">
                                                                <label>Tanggal Selesai Pembangunan</label>
                                                                <input type="text"
                                                                    class="form-control flatpickr-human-friendly "
                                                                    id="dt-tanggal_selesai_pembangunan"
                                                                    name="dt-tanggal_selesai_pembangunan" disabled>
                                                                <input type="text" class="hidden"
                                                                    id="dt-tanggal_selesai_pembangunan_old"
                                                                    name="dt-tanggal_selesai_pembangunan_old" disabled>
                                                            </div>
                                                            <span class="text-muted"
                                                                id="dt-lu-tanggal_selesai_pembangunan"></span>


                                                            <div class="hidden">
                                                                <div class="form-group">
                                                                    <label>Diinput oleh</label>
                                                                    <input type="text" class="form-control"
                                                                        id="dt-tanggal_pembangunan_oleh" disabled
                                                                        name="dt-tanggal_pembangunan_oleh">
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>Diinput Pada</label>
                                                                    <input type="text"
                                                                        class="form-control flatpickr-human-friendly"
                                                                        id="dt-tanggal_pembangunan_pada" disabled
                                                                        name="dt-tanggal_pembangunan_pada">
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>Diubah oleh</label>
                                                                    <input type="text" class="form-control"
                                                                        id="dt-tanggal_pembangunan_diubah_oleh" disabled
                                                                        name="dt-tanggal_pembangunan_diubah_oleh">
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>Diubah Pada</label>
                                                                    <input type="text"
                                                                        class="form-control flatpickr-human-friendly"
                                                                        id="dt-tanggal_pembangunan_diubah_pada" disabled
                                                                        name="dt-tanggal_pembangunan_diubah_pada">
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>Diinput oleh</label>
                                                                    <input type="text" class="form-control"
                                                                        id="dt-tanggal_selesai_pembangunan_oleh"
                                                                        disabled
                                                                        name="dt-tanggal_selesai_pembangunan_oleh">
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>Diinput Pada</label>
                                                                    <input type="text"
                                                                        class="form-control flatpickr-human-friendly"
                                                                        id="dt-tanggal_selesai_pembangunan_pada"
                                                                        disabled
                                                                        name="dt-tanggal_selesai_pembangunan_pada">
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>Diubah oleh</label>
                                                                    <input type="text" class="form-control"
                                                                        id="dt-tanggal_selesai_pembangunan_diubah_oleh"
                                                                        disabled
                                                                        name="dt-tanggal_selesai_pembangunan_diubah_oleh">
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>Diubah Pada</label>
                                                                    <input type="text"
                                                                        class="form-control flatpickr-human-friendly"
                                                                        id="dt-tanggal_selesai_pembangunan_diubah_pada"
                                                                        disabled
                                                                        name="dt-tanggal_selesai_pembangunan_diubah_pada">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>


                                                <div class="form-group" style="min-height:100px; height: auto;">
                                                    <label>RAB</label>
                                                    <div id="dt-list_rab_dokumen" style="display: flex; flex-wrap: wrap;">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="tab-pane" id="dt-fm-prod-bayar_produksi"
                                                aria-labelledby="dt-fm-prod-bayar_produksi-tab" role="tabpanel">
                                                <div id="dt-div-bayar_produksi-here" class="row"></div>
                                            </div>
                                            <div class="tab-pane" id="dt-fm-prod-dokumentasi"
                                                aria-labelledby="dt-fm-prod-dokumentasi-tab" role="tabpanel">
                                                <div class="form-group foto-container">
                                                    <label>Foto Konstruksi(Jika Ada, Pembesian, Pondasi)</label>

                                                    <div id="dt-list_prod_foto_konstruksi"
                                                        style="display: flex; flex-wrap: wrap;"></div>
                                                </div>
                                                <hr>
                                                <div class="form-group foto-container">
                                                    <label for="upload_komplain_produksi">Foto Exterior(Depan dan
                                                        Belakang(min. 1 photo), foto memiliki titik koordinat)</label>

                                                    <div id="dt-list_prod_foto_exterior"
                                                        style="display: flex; flex-wrap: wrap;"></div>
                                                </div>
                                                <hr>
                                                <div class="form-group foto-container">
                                                    <label for="upload_komplain_produksi">Foto Interior(kamar, dapur,
                                                        toilet, dan ruang tengah (min. 1 photo), foto memiliki titik
                                                        koordinat)</label>

                                                    <div id="dt-list_prod_foto_interior"
                                                        style="display: flex; flex-wrap: wrap;"></div>
                                                </div>

                                            </div>

                                            <div class="tab-pane" id="dt-fm-prod-jalan"
                                                aria-labelledby="dt-fm-prod-jalan-tab" role="tabpanel">
                                                <div class="divider">
                                                    <div class="divider-text">Foto Jalan</div>
                                                </div>
                                                <div>
                                                    <div class="form-group foto-container">
                                                        <label for="jalan_foto">Foto Jalan</label>

                                                        <div id="dt-list_jalan_foto"
                                                            style="display: flex; flex-wrap: wrap;"></div>
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="form-group foto-container">
                                                        <label for="jalan_foto_update">Foto Jalan Update/Setelah
                                                            Akad(Paving)</label>

                                                        <div id="dt-list_jalan_foto_update"
                                                            style="display: flex; flex-wrap: wrap;"></div>
                                                    </div>
                                                </div>

                                            </div>
                                            <div class="tab-pane" id="dt-fm-prod-listrik"
                                                aria-labelledby="dt-fm-prod-listrik-tab" role="tabpanel">
                                                <div class="divider">
                                                    <div class="divider-text">Ketersediaan Listrik</div>
                                                </div>
                                                <div class="form-group">
                                                    <label>Jenis Sumber Listrik</label>
                                                    <select id="dt-listrik_jenis" name="dt-listrik_jenis"
                                                        class="form-control" disabled>
                                                        <option value="PLN">PLN</option>
                                                        <option value="Disendiakan Pengembang">Disendiakan Pengembang
                                                            (Dalam Pengajuan)</option>
                                                    </select>
                                                </div>
                                                <div id="dt-listrik-pln-input-form">
                                                    <div class="form-group">
                                                        <label>No ID Pelanggan/Nomor Meteran Listrik PLN</label>
                                                        <input type="text" class="form-control" id="dt-listrik_pln"
                                                            name="dt-listrik_pln" disabled>
                                                    </div>
                                                    <div class="form-group foto-container">
                                                        <label>Foto Ketersediaan Lampu
                                                            Menyala</label>

                                                        <div id="dt-list_listrik_pln_foto"
                                                            style="display: flex; flex-wrap: wrap;"></div>
                                                    </div>
                                                </div>
                                                <div id="listrik_disediakan" class="hidden">
                                                    <div class="form-group">
                                                        <label>No Pengajuan Listrik PLN</label>
                                                        <input type="text" class="form-control"
                                                            id="dt-listrik_disediakan_no"
                                                            name="dt-listrik_disediakan_no" disabled>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Tanggal Pengajuan Listrik PLN</label>
                                                        <input type="text" class="form-control flatpickr-human-friendly"
                                                            id="dt-listrik_disediakan_tanggal"
                                                            name="dt-listrik_disediakan_tanggal" disabled>
                                                    </div>
                                                    <div class="form-group foto-container">
                                                        <label for="label_listrik_disediakan_dokumen">Upload Bukti
                                                            Pengajuan</label>

                                                        <div id="dt-list_listrik_disediakan_dokumen"
                                                            style="display: flex; flex-wrap: wrap;"></div>
                                                    </div>
                                                    <div class="form-group foto-container">
                                                        <label for="dt-listrik_disediakan_foto">Foto Ketersediaan Lampu
                                                            Menyala</label>

                                                        <div id="dt-list_listrik_disediakan_foto"
                                                            style="display: flex; flex-wrap: wrap;"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="tab-pane" id="dt-fm-prod-air"
                                                aria-labelledby="dt-fm-prod-air-tab" role="tabpanel">
                                                <div class="divider">
                                                    <div class="divider-text">Ketersediaan Air</div>
                                                </div>
                                                <div class="form-group">
                                                    <label>Jenis Sumber Air</label>
                                                    <select id="dt-air_jenis" name="dt-air_jenis" class="form-control"
                                                        disabled>
                                                        <option value="Air Tanah">Air Tanah</option>
                                                        <option value="Komunal Warga">Komunal Warga</option>
                                                        <option value="PDAM">PDAM</option>
                                                    </select>
                                                </div>
                                                <div id="dt-air_tanah-input_form">
                                                    <div class="form-group foto-container">
                                                        <label for="dt-air_tanah">Foto ketersediaan air bersih dengan
                                                            air
                                                            mengalir & sumber air (min. 1 foto)</label>

                                                        <div id="dt-list_air_tanah"
                                                            style="display: flex; flex-wrap: wrap;"></div>
                                                    </div>
                                                </div>
                                                <div id="dt-air_komunal-input_form" class="hidden">
                                                    <div class="form-group foto-container">
                                                        <label for="dt-air_komunal">Foto ketersediaan air bersih dengan
                                                            air
                                                            mengalir & sumber air komunal bersama (min. 1 foto)</label>

                                                        <div id="dt-list_air_komunal"
                                                            style="display: flex; flex-wrap: wrap;"></div>
                                                    </div>
                                                </div>
                                                <div id="dt-air_pdam-input_form" class="hidden">
                                                    <div class="form-group">
                                                        <label>No Meteran Air PDAM</label>
                                                        <input type="text" class="form-control" id="dt-air_pdam_no"
                                                            name="dt-air_pdam_no" disabled>
                                                    </div>
                                                    <div class="form-group foto-container">
                                                        <label for="dt-air_pdam">Foto ketersediaan air bersih dengan air
                                                            mengalir & meteran air PDAM (min. 1 foto)</label>
                                                        <div id="dt-list_air_pdam"
                                                            style="display: flex; flex-wrap: wrap;">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label>Deskripsi Unit (informasi keunggulan unit)</label>
                                                    <input type="text" class="form-control" id="dt-air_deskripsi_unit"
                                                        name="dt-air_deskripsi_unit" disabled>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane" id="dt-pajak" aria-labelledby="dt-pajak-tab" role="tabpanel">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="divider">
                                                    <div class="divider-text">Bukti Pembayaran PPH</div>
                                                </div>
                                                <div id="dt-file_pph42-here"></div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="divider">
                                                    <div class="divider-text">Bukti Pembayaran PPn</div>
                                                </div>
                                                <div id="dt-file_ppn-here"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>
                </div>

            </div>
            <div class="modal-footer">
            </div>
        </div>
    </div>
</div>

<!-- lightbox untuk gambar embed di catatan pricelist -->
<div class="modal fade" id="modal_image_lightbox" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-transparent border-0">
            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"
                style="position:absolute; top:-2rem; right:0;">×</button>
            <img id="modal_image_lightbox_img" src="" class="img-fluid mx-auto d-block" alt="">
        </div>
    </div>
</div>
