<!-- Modal Detail & History Batch Pengajuan (Terpadu) -->
<div class="modal fade text-left" id="modal_batch_detail" role="dialog" aria-labelledby="modal_batch_detail_label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary py-1">
                <div class="d-flex align-items-center flex-wrap" style="gap: .5rem;">
                    <h5 class="modal-title text-white mb-0" id="modal_batch_detail_label">
                        <i class="fas fa-file-invoice mr-50"></i> Detail Pengajuan Batch
                    </h5>
                    <span class="badge badge-light font-weight-bold" id="detail-batch-header-nomor">-</span>
                    <span id="detail-batch-header-status">-</span>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <!-- Nav Tabs -->
            <ul class="nav nav-tabs nav-justified px-1 pt-1 mb-0 border-bottom" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active font-weight-bold" id="tab-batch-detail-rincian" data-toggle="tab" href="#pane-batch-detail-rincian" role="tab" aria-controls="pane-batch-detail-rincian" aria-selected="true">
                        <i class="fas fa-list-alt mr-50"></i> Rincian Pengajuan
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link font-weight-bold" id="tab-batch-detail-history" data-toggle="tab" href="#pane-batch-detail-history" role="tab" aria-controls="pane-batch-detail-history" aria-selected="false">
                        <i class="fas fa-history mr-50"></i> Riwayat &amp; Timeline
                    </a>
                </li>
            </ul>

            <div class="modal-body p-1">
                <div class="tab-content">
                    <!-- Tab 1: Rincian -->
                    <div class="tab-pane fade show active" id="pane-batch-detail-rincian" role="tabpanel" aria-labelledby="tab-batch-detail-rincian">
                        <div class="row mb-1">
                            <div class="col-md-6">
                                <table class="table table-sm table-borderless mb-0" style="font-size: 0.85rem;">
                                    <tr>
                                        <th style="width: 40%;" class="text-muted p-25">Nomor Pengajuan:</th>
                                        <td class="font-weight-bold p-25" id="detail-batch-nomor">-</td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted p-25">Tgl Pengajuan:</th>
                                        <td class="p-25" id="detail-batch-tgl">-</td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted p-25">Tgl Rencana Cair:</th>
                                        <td class="p-25" id="detail-batch-tgl-rencana">-</td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted p-25">PIC Pengaju:</th>
                                        <td class="p-25" id="detail-batch-pic">-</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-sm table-borderless mb-0" style="font-size: 0.85rem;">
                                    <tr>
                                        <th style="width: 40%;" class="text-muted p-25">Total Pengajuan:</th>
                                        <td class="font-weight-bold text-primary p-25" id="detail-batch-total">Rp 0</td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted p-25">Total Sudah Cair:</th>
                                        <td class="font-weight-bold text-success p-25" id="detail-batch-cair">Rp 0</td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted p-25">Sisa Outstanding:</th>
                                        <td class="font-weight-bold text-danger p-25" id="detail-batch-sisa">Rp 0</td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted p-25">Surat Pengajuan:</th>
                                        <td class="p-25" id="detail-batch-lampiran">-</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="alert alert-light-secondary py-50 mb-1" id="detail-batch-catatan-box" style="display: none;">
                            <i class="fas fa-sticky-note mr-25"></i> <span id="detail-batch-catatan"></span>
                        </div>

                        <div class="divider divider-left mb-1">
                            <div class="divider-text font-weight-bold text-uppercase" style="font-size: 0.8rem;">
                                Daftar Kavling &amp; Status Pencairan
                            </div>
                        </div>

                        <div class="table-responsive" style="max-height: 280px; overflow-y: auto;">
                            <table class="table table-sm table-bordered mb-0" id="tbl-detail-batch-kavlings">
                                <thead class="thead-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Kavling &amp; Blok</th>
                                        <th>Konsumen</th>
                                        <th class="text-right">Diajukan</th>
                                        <th class="text-right">Cair</th>
                                        <th class="text-right">Sisa</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody id="detail-batch-tbody"></tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tab 2: Riwayat & Timeline -->
                    <div class="tab-pane fade" id="pane-batch-detail-history" role="tabpanel" aria-labelledby="tab-batch-detail-history">
                        <div class="p-1" style="max-height: 380px; overflow-y: auto;">
                            <div class="pa-history-timeline" id="detail-batch-history-timeline">
                                <!-- Timeline items will be injected here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer d-flex justify-content-between align-items-center">
                <div>
                    <button type="button" class="btn btn-outline-danger btn-sm text-uppercase" id="btn-detail-void-batch" style="display: none;">
                        <i class="fas fa-ban mr-25"></i> Batalkan Pengajuan
                    </button>
                </div>
                <div class="d-flex align-items-center" style="gap: .5rem;">
                    <button type="button" class="btn btn-success btn-sm text-uppercase" id="btn-detail-cair-batch" style="display: none;">
                        <i class="fas fa-hand-holding-usd mr-25"></i> Cairkan Dana
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm text-uppercase" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal History Standalone (Fallback Compatibility) -->
<div class="modal fade text-left" id="modal_batch_history" role="dialog" aria-labelledby="modal_batch_history_label" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-dialog-scrollable modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title text-white" id="modal_batch_history_label">
                    <i class="fas fa-history mr-50"></i> Riwayat &amp; Timeline Pengajuan Batch
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-2">
                <div class="d-flex align-items-center justify-content-between border-bottom pb-1 mb-2">
                    <div>
                        <span class="text-muted small font-weight-bold d-block">NOMOR BATCH</span>
                        <h5 class="mb-0 font-weight-bold" id="hist-batch-nomor">-</h5>
                    </div>
                    <div>
                        <span class="text-muted small font-weight-bold d-block">STATUS SAAT INI</span>
                        <span id="hist-batch-status-badge">-</span>
                    </div>
                </div>

                <div class="pa-history-timeline" id="batch-history-timeline">
                    <!-- Timeline items will be injected here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary btn-sm text-uppercase" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
