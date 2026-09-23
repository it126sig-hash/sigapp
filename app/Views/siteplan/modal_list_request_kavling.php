<div class="modal fade" id="modal-list-request-kavling" tabindex="-1" role="dialog" aria-labelledby="modal-list-request-kavling-label" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold" id="modal-list-request-kavling-label">
                    <i class="fa fa-list-alt mr-1 text-primary"></i> Daftar Request Kavling & Perubahan Tipe
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-3">
                <!-- Filter Bar -->
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <div class="btn-group btn-group-sm mb-1 mb-md-0" role="group" id="btn-group-filter-status">
                        <button type="button" class="btn btn-primary active" data-status="">
                            Semua (<span id="count-req-all">0</span>)
                        </button>
                        <button type="button" class="btn btn-outline-primary" data-status="pending">
                            <i class="fa fa-clock-o mr-1"></i> Menunggu (<span id="count-req-pending">0</span>)
                        </button>
                        <button type="button" class="btn btn-outline-primary" data-status="approved">
                            <i class="fa fa-check-circle mr-1"></i> Selesai (<span id="count-req-approved">0</span>)
                        </button>
                        <button type="button" class="btn btn-outline-primary" data-status="rejected">
                            <i class="fa fa-times-circle mr-1"></i> Ditolak (<span id="count-req-rejected">0</span>)
                        </button>
                    </div>

                    <div>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="reloadListRequestKavling()">
                            <i class="fa fa-refresh mr-1"></i> Refresh Data
                        </button>
                    </div>
                </div>

                <!-- Loading Spinner -->
                <div id="loading-list-request" class="text-center py-4 d-none">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <div class="text-muted mt-2 small">Memuat daftar request kavling...</div>
                </div>

                <!-- Table Container -->
                <div class="table-responsive" id="table-list-request-container">
                    <table class="table table-hover table-bordered table-sm mb-0" id="table-list-request">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 50px;" class="text-center">No</th>
                                <th style="width: 140px;">Tanggal & Pengaju</th>
                                <th style="width: 130px;" class="text-center">Jenis Request</th>
                                <th style="min-width: 220px;">Detail Lokasi & Tipe</th>
                                <th style="min-width: 200px;">Keterangan / Catatan</th>
                                <th style="width: 110px;" class="text-center">Status</th>
                                <th style="width: 160px;" class="text-center">Aksi Tindak Lanjut</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-list-request">
                            <!-- Populated by JavaScript -->
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div id="empty-list-request" class="alert alert-light text-center py-4 d-none border">
                    <i class="fa fa-inbox fa-3x text-muted mb-2"></i>
                    <h6 class="text-muted">Tidak ada pengajuan request kavling untuk kriteria ini.</h6>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">
                    <i class="fa fa-times mr-1"></i> Tutup
                </button>
            </div>
        </div>
    </div>
</div>
