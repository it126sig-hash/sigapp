<!-- Modal Detail Pencairan -->
<div class="modal fade" id="modalDetailPencairan" tabindex="-1" role="dialog" aria-labelledby="modalDetailPencairanTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div class="mgm-header-wrap">
                    <div class="mgm-header-icon">
                        <i class="fas fa-gift"></i>
                    </div>
                    <div>
                        <h5 class="modal-title font-weight-bold" id="modalDetailPencairanTitle">Detail & Pengajuan Pencairan Bonus MGM</h5>
                        <div class="mgm-header-meta">
                            <span><i class="far fa-user mr-50"></i> <span class="meta-label">Referer:</span> <span id="detail_nama_referrer" class="meta-value"></span></span>
                            <span class="meta-arrow"><i class="fas fa-long-arrow-alt-right"></i></span>
                            <span><span class="meta-label">Referred:</span> <span id="detail_nama_referred" class="meta-value"></span> <span id="detail_kavling_referred"></span></span>
                        </div>
                    </div>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            
            <div class="modal-body pt-2">
                <div class="row">
                    <div class="col-12 col-md-4 mb-1">
                        <div class="summary-card potensi">
                            <div class="summary-icon"><i class="fas fa-chart-bar"></i></div>
                            <div>
                                <div class="title">Potensi</div>
                                <div class="amount" id="summary_potensi">0</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4 mb-1">
                        <div class="summary-card cair">
                            <div class="summary-icon"><i class="fas fa-arrow-down"></i></div>
                            <div>
                                <div class="title">Cair</div>
                                <div class="amount" id="summary_cair">0</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4 mb-1">
                        <div class="summary-card sisa">
                            <div class="summary-icon"><i class="far fa-clock"></i></div>
                            <div>
                                <div class="title">Sisa</div>
                                <div class="amount" id="summary_sisa">0</div>
                            </div>
                        </div>
                    </div>
                </div>

                <ul class="nav nav-pills mgm-detail-tabs" id="mgmDetailTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="mgm-summary-tab" data-toggle="pill" href="#mgm-summary-pane" role="tab" aria-controls="mgm-summary-pane" aria-selected="true">Ringkasan Bonus</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="mgm-history-tab" data-toggle="pill" href="#mgm-history-pane" role="tab" aria-controls="mgm-history-pane" aria-selected="false">Riwayat Bonus</a>
                    </li>
                </ul>

                <div class="tab-content mgm-detail-tab-content" id="mgmDetailTabContent">
                    <div class="tab-pane fade show active" id="mgm-summary-pane" role="tabpanel" aria-labelledby="mgm-summary-tab">
                        <div class="mgm-section-label">Ringkasan Hak Bonus</div>
                        <div id="stages_list_container">
                            <!-- Injected via JS -->
                        </div>

                        <!-- BOTTOM SECTION: Form Pengajuan Pencairan -->
                        <div class="row d-none" id="form_section">
                            <div class="col-12">
                                <div class="form-pengajuan-box">
                                    <h6 class="font-weight-bold mb-3"><i class="fas fa-bars text-primary mr-1"></i> Form <span id="form_title_action">Pengajuan Pencairan</span></h6>
                                    <form id="formActionDinamis" onsubmit="submitFormActionDinamis(event)">
                                        <input type="hidden" name="id_bonus" id="form_id_bonus">
                                        <input type="hidden" name="action_type" id="form_action_type">

                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="text-muted font-weight-bold mgm-form-label" id="label_nominal_pengajuan">NOMINAL</label>
                                                    <input type="text" class="form-control form-control-lg font-weight-bold mgm-readonly-control" id="form_nominal_pengajuan" name="nominal" readonly>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group" id="group_tanggal_spp">
                                                    <label class="text-muted font-weight-bold mgm-form-label">TANGGAL PENGAJUAN</label>
                                                    <input type="date" class="form-control form-control-lg" id="form_tanggal_spp" name="tanggal_spp">
                                                </div>
                                                <div class="form-group" id="group_tanggal_cair">
                                                    <label class="text-muted font-weight-bold mgm-form-label">TANGGAL CAIR</label>
                                                    <input type="date" class="form-control form-control-lg" id="form_tanggal_cair_keuangan" name="tanggal_cair_keuangan">
                                                </div>
                                                <div class="form-group" id="group_tanggal_pembayaran">
                                                    <label class="text-muted font-weight-bold mgm-form-label">TANGGAL PEMBAYARAN</label>
                                                    <input type="date" class="form-control form-control-lg" id="form_tanggal_pembayaran" name="tanggal_pembayaran">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group" id="group_upload">
                                                    <label class="text-muted font-weight-bold mgm-form-label" id="label_upload_bukti">UPLOAD BUKTI</label>
                                                    <div class="mgm-upload-dropzone" id="mgm_upload_dropzone">
                                                        <input type="file" id="form_bukti" name="bukti" accept="image/*,application/pdf">
                                                        <div class="mgm-upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                                                        <div class="mgm-upload-title">Drop file, klik, atau paste</div>
                                                        <div class="mgm-upload-filename" id="form_bukti_filename">Foto/PDF</div>
                                                    </div>
                                                    <small class="text-muted">Gambar akan dikompres sebelum dikirim.</small>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row" id="group_recipient">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="text-muted font-weight-bold mgm-form-label" id="label_nama_penerima">NAMA PENERIMA</label>
                                                    <input type="text" class="form-control form-control-lg" id="form_nama_penerima" name="nama_penerima">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="text-muted font-weight-bold mgm-form-label" id="label_no_rekening">NO REKENING</label>
                                                    <input type="text" class="form-control form-control-lg" id="form_no_rekening_penerima" name="no_rekening_penerima">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="text-muted font-weight-bold mgm-form-label" id="label_bank_penerima">BANK</label>
                                                    <input type="text" class="form-control form-control-lg" id="form_bank_penerima" name="bank_penerima">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group d-none" id="group_keterangan">
                                            <label class="text-muted font-weight-bold mgm-form-label">KETERANGAN / CATATAN</label>
                                            <textarea class="form-control" name="keterangan" id="form_keterangan_input" rows="2"></textarea>
                                        </div>

                                        <button type="submit" class="btn btn-primary btn-block btn-lg mt-2" id="btn_submit_dinamis">Kirim Pengajuan Sekarang</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="mgm-history-pane" role="tabpanel" aria-labelledby="mgm-history-tab">
                        <div class="divider divider-left">
                            <div class="divider-text">Riwayat Bonus Keseluruhan</div>
                        </div>
                        <div class="history-list" id="history_container"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Preview Lampiran Progres Bonus -->
<div class="modal fade" id="modalMgmLampiran" tabindex="-1" role="dialog" aria-labelledby="modalMgmLampiranTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content mgm-lampiran-modal">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title font-weight-bold" id="modalMgmLampiranTitle">Lampiran Progres Bonus</h5>
                    <div class="small text-muted" id="lampiran_progress_stage">-</div>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-lg-7 mb-1 mb-lg-0">
                        <div class="mgm-lampiran-preview" id="lampiran_progress_preview">
                            <div class="text-muted small">Belum ada lampiran.</div>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="mgm-lampiran-info" id="lampiran_progress_info"></div>
                        <a href="#" class="btn btn-outline-primary btn-block mt-1 d-none" id="lampiran_open_link" target="_blank" rel="noopener">
                            <i class="fas fa-external-link-alt mr-50"></i>Buka Lampiran
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
