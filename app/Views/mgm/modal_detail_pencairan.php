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
                                                    <label class="text-muted font-weight-bold mgm-form-label">TANGGAL SPP</label>
                                                    <input type="date" class="form-control form-control-lg" id="form_tanggal_spp" name="tanggal_spp">
                                                </div>
                                                <div class="form-group" id="group_tanggal_cair">
                                                    <label class="text-muted font-weight-bold mgm-form-label">TANGGAL CAIR</label>
                                                    <input type="date" class="form-control form-control-lg" id="form_tanggal_cair_keuangan" name="tanggal_cair_keuangan">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group" id="group_upload">
                                                    <label class="text-muted font-weight-bold mgm-form-label" id="label_upload_bukti">UPLOAD BUKTI</label>
                                                    <div class="custom-file">
                                                        <input type="file" class="custom-file-input form-control-lg" id="form_bukti" name="bukti" accept="image/*,application/pdf">
                                                        <label class="custom-file-label" for="form_bukti"><i class="far fa-file mr-1"></i> Pilih File</label>
                                                    </div>
                                                    <small class="text-muted">Foto/PDF. Gambar bisa paste (Ctrl+V).</small>
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
