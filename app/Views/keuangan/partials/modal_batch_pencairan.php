<!-- Modal Pencairan Dana Batch -->
<div class="modal fade text-left" id="modal_batch_pencairan" role="dialog" aria-labelledby="modal_batch_pencairan_label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h5 class="modal-title text-white" id="modal_batch_pencairan_label">
                    <i class="fas fa-money-bill-wave mr-50"></i> Pencairan Dana Hasil Akad (Batch)
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form-batch-pencairan" autocomplete="off">
                <input type="hidden" id="cair-batch-id" name="id_batch" value="">
                <input type="hidden" id="cair-batch-mode" name="mode" value="all">

                <div class="modal-body">
                    <!-- Batch Overview Cards -->
                    <div class="row mb-1">
                        <div class="col-md-3">
                            <div class="card bg-light border mb-0 p-1">
                                <span class="text-muted small font-weight-bold d-block">NOMOR BATCH</span>
                                <strong id="cair-batch-nomor" class="text-dark" style="font-size: 0.95rem;">-</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light border mb-0 p-1">
                                <span class="text-muted small font-weight-bold d-block">TOTAL PENGAJUAN</span>
                                <strong id="cair-batch-total-pengajuan" class="text-primary" style="font-size: 0.95rem;">Rp 0</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light border mb-0 p-1">
                                <span class="text-muted small font-weight-bold d-block">SUDAH CAIR</span>
                                <strong id="cair-batch-total-cair" class="text-success" style="font-size: 0.95rem;">Rp 0</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light border mb-0 p-1">
                                <span class="text-muted small font-weight-bold d-block">SISA BELUM CAIR</span>
                                <strong id="cair-batch-total-sisa" class="text-danger" style="font-size: 0.95rem;">Rp 0</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="d-flex align-items-center justify-content-between flex-wrap mb-1" style="gap: .5rem;">
                        <span class="font-weight-bold text-uppercase font-small-3 text-muted">
                            <i class="fas fa-list-check mr-25"></i> Pilih Item yang Akan Dicairkan:
                        </span>
                        <div>
                            <button type="button" class="btn btn-outline-primary btn-sm mr-25" id="btn-check-all-cair">
                                <i class="fas fa-check-square mr-25"></i> Centang Semua
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-uncheck-all-cair">
                                <i class="fas fa-square mr-25"></i> Hapus Centang
                            </button>
                        </div>
                    </div>

                    <!-- Tabel Kavling & Item Pencairan -->
                    <div class="table-responsive" style="max-height: 320px; overflow-y: auto;">
                        <table class="table table-sm table-bordered mb-0" id="tbl-cair-kavling-list">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 4%;" class="text-center">
                                        <input type="checkbox" id="cair-check-all-box" title="Centang Semua" style="cursor: pointer;">
                                    </th>
                                    <th style="width: 4%;" class="text-center">#</th>
                                    <th style="width: 20%;">Kavling &amp; Blok</th>
                                    <th style="width: 22%;">Konsumen</th>
                                    <th style="width: 18%;">Item Pencairan</th>
                                    <th style="width: 14%;" class="text-right">Sisa Belum Cair</th>
                                    <th style="width: 18%;" class="text-right">Nominal Cair</th>
                                </tr>
                            </thead>
                            <tbody id="cair-kavling-tbody"></tbody>
                        </table>
                    </div>

                    <!-- Input Tanggal & Catatan (Pojok Kanan Bawah) -->
                    <div class="row mt-1 mb-1 justify-content-end">
                        <div class="col-md-6 col-lg-5">
                            <div class="card bg-light border mb-0 p-1">
                                <div class="form-group mb-1">
                                    <label class="font-weight-bold font-small-3 mb-25" for="cair-input-tanggal">
                                        Tanggal Pencairan <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" class="form-control form-control-sm" id="cair-input-tanggal" name="tanggal_cair" required value="">
                                </div>
                                <div class="form-group mb-0">
                                    <label class="font-weight-bold font-small-3 mb-25" for="cair-input-catatan">
                                        Catatan Pencairan (Opsional)
                                    </label>
                                    <textarea class="form-control form-control-sm" id="cair-input-catatan" name="catatan" rows="2" placeholder="Misal: Pencairan rekening giro BTN tahap 1..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <div>
                        <span class="text-muted small">Total yang Dicairkan Hari Ini: </span>
                        <strong id="cair-current-total" class="text-success" style="font-size: 1.15rem;">Rp 0</strong>
                    </div>
                    <div>
                        <button type="button" class="btn btn-outline-secondary btn-sm text-uppercase" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success btn-sm text-uppercase" id="btn-submit-cair-batch">
                            <i class="fas fa-save mr-25"></i> Simpan Pencairan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
