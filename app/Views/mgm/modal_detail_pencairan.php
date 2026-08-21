<!-- Modal Detail Pencairan -->
<style>
    /* Styling for the redesign */
    .summary-card {
        border-radius: 8px;
        padding: 15px;
        text-align: center;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        border: 1px solid #e0e0e0;
    }
    .summary-card.potensi { background-color: #f3f0ff; border-color: #e5dcf7; }
    .summary-card.cair { background-color: #e8f7f0; border-color: #d1efdf; }
    .summary-card.sisa { background-color: #fdf5e6; border-color: #f7e6c9; }
    
    .summary-card .title { font-size: 0.8rem; font-weight: bold; margin-bottom: 5px; }
    .summary-card.potensi .title { color: #6b46c1; }
    .summary-card.cair .title { color: #2f855a; }
    .summary-card.sisa .title { color: #d69e2e; }
    
    .summary-card .amount { font-size: 1.1rem; font-weight: bold; }
    .summary-card.potensi .amount { color: #553c9a; }
    .summary-card.cair .amount { color: #22543d; }
    .summary-card.sisa .amount { color: #b7791f; }

    .stage-card {
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 12px 15px;
        margin-bottom: 10px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #fff;
    }
    .stage-card:last-child { margin-bottom: 0; }
    
    .timeline-container {
        position: relative;
        padding-left: 20px;
        margin-top: 15px;
    }
    .timeline-item {
        position: relative;
        padding-bottom: 20px;
        border-left: 1px solid #e0e0e0;
        padding-left: 20px;
    }
    .timeline-item:last-child {
        border-left: 0;
        padding-bottom: 0;
    }
    .timeline-item::before {
        content: '';
        position: absolute;
        left: -6px;
        top: 0;
        width: 11px;
        height: 11px;
        border-radius: 50%;
        background-color: #ccc;
    }
    .timeline-item.done::before { background-color: #38c172; }
    .timeline-item.active::before { background-color: #6574cd; }
    
    .timeline-title { font-weight: bold; margin-bottom: 2px; }
    .timeline-desc { font-size: 0.85rem; color: #6c757d; }
    .timeline-status {
        position: absolute;
        right: 0;
        top: 0;
        font-size: 0.75rem;
        font-weight: bold;
        color: #6c757d;
    }
    .timeline-item.active .timeline-title { color: #6574cd; }
    .timeline-item.active .timeline-status { color: #6574cd; }
    
    .form-pengajuan-box {
        border: 1px solid #e0e0e0;
        border-radius: 12px;
        padding: 20px;
        background: #fff;
        margin-top: 15px;
    }
    
    .section-title {
        font-size: 0.85rem;
        font-weight: 700;
        text-transform: uppercase;
        color: #4b5563;
        margin-bottom: 15px;
    }
</style>

<div class="modal fade" id="modalDetailPencairan" tabindex="-1" role="dialog" aria-labelledby="modalDetailPencairanTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content" style="background-color: #f9fbfd;">
            <div class="modal-header bg-white border-bottom-0 pb-1">
                <div>
                    <h5 class="modal-title font-weight-bold" id="modalDetailPencairanTitle" style="color: #4b5563;">Detail & Pengajuan Pencairan Bonus MGM</h5>
                    <small class="text-muted">Referrer: <span id="detail_nama_referrer" class="font-weight-bold text-dark"></span> | Referred: <span id="detail_nama_referred" class="font-weight-bold text-dark"></span> (<span id="detail_kavling_referred"></span>)</small>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            
            <div class="modal-body pt-2">
                <!-- TOP SECTION: Ringkasan Hak Bonus -->
                <div class="row mb-3">
                    <div class="col-12 col-md-7">
                        <div class="section-title">Ringkasan Hak Bonus</div>
                        <div id="stages_list_container">
                            <!-- Injected via JS -->
                        </div>
                    </div>
                    <div class="col-12 col-md-5 mt-4 mt-md-0 pt-md-4">
                        <div class="row h-100">
                            <div class="col-4 px-1">
                                <div class="summary-card potensi">
                                    <div class="title">POTENSI</div>
                                    <div class="amount" id="summary_potensi">0</div>
                                </div>
                            </div>
                            <div class="col-4 px-1">
                                <div class="summary-card cair">
                                    <div class="title">CAIR</div>
                                    <div class="amount" id="summary_cair">0</div>
                                </div>
                            </div>
                            <div class="col-4 px-1">
                                <div class="summary-card sisa">
                                    <div class="title">SISA</div>
                                    <div class="amount" id="summary_sisa">0</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- MIDDLE SECTION: Alur Pengajuan Pencairan -->
                <div class="row mb-3" id="timeline_section" style="display: none;">
                    <div class="col-12">
                        <div class="section-title">Alur Pengajuan Pencairan <span id="timeline_stage_name" class="text-primary text-lowercase"></span></div>
                        <div class="timeline-container" id="timeline_container">
                            <!-- Injected via JS -->
                        </div>
                    </div>
                </div>

                <!-- BOTTOM SECTION: Form Pengajuan Pencairan -->
                <div class="row" id="form_section" style="display: none;">
                    <div class="col-12">
                        <div class="form-pengajuan-box">
                            <h6 class="font-weight-bold mb-3"><i class="fas fa-bars text-primary mr-1"></i> Form <span id="form_title_action">Pengajuan Pencairan</span></h6>
                            <form id="formActionDinamis" onsubmit="submitFormActionDinamis(event)">
                                <input type="hidden" name="id_bonus" id="form_id_bonus">
                                <input type="hidden" name="action_type" id="form_action_type">
                                
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="text-muted font-weight-bold" style="font-size: 0.75rem;">NOMINAL PENGAJUAN</label>
                                            <input type="text" class="form-control form-control-lg font-weight-bold" id="form_nominal_pengajuan" name="nominal" readonly style="background-color: #f3f4f6;">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group" id="group_metode">
                                            <label class="text-muted font-weight-bold" style="font-size: 0.75rem;">METODE</label>
                                            <select class="form-control form-control-lg" name="metode" id="form_metode" required>
                                                <option value="Transfer Bank">Transfer Bank</option>
                                                <option value="Cash">Cash</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group" id="group_upload">
                                            <label class="text-muted font-weight-bold" style="font-size: 0.75rem;">UPLOAD BUKTI FORM</label>
                                            <div class="custom-file">
                                                <input type="file" class="custom-file-input form-control-lg" id="form_bukti" name="bukti" accept="image/*">
                                                <label class="custom-file-label" for="form_bukti"><i class="far fa-file-image mr-1"></i> Pilih File</label>
                                            </div>
                                            <small class="text-muted">Bisa Paste Image (Ctrl+V) disini</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group" id="group_keterangan" style="display:none;">
                                    <label class="text-muted font-weight-bold" style="font-size: 0.75rem;">KETERANGAN / CATATAN</label>
                                    <textarea class="form-control" name="keterangan" id="form_keterangan_input" rows="2"></textarea>
                                </div>

                                <button type="submit" class="btn btn-primary btn-block btn-lg mt-2" id="btn_submit_dinamis">Kirim Pengajuan Sekarang</button>
                            </form>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</div>
