<!-- Modal Buat Pengajuan Batch -->
<div class="modal fade text-left" id="modal_batch_pengajuan" role="dialog" aria-labelledby="modal_batch_pengajuan_label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title text-white" id="modal_batch_pengajuan_label">
                    <i class="fas fa-layer-group mr-50"></i> Buat Pengajuan Pencairan Batch
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form-batch-pengajuan" enctype="multipart/form-data" autocomplete="off">
                <div class="modal-body">
                    <div class="alert alert-primary py-1 mb-1">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <i class="fas fa-info-circle mr-50"></i>
                                <span>Kavling Terpilih: <strong id="batch-modal-kavling-count">0</strong> Kavling</span>
                            </div>
                            <div>
                                <span>Total Pengajuan: <strong id="batch-modal-grand-total" class="text-white" style="font-size: 1.05rem;">Rp 0</strong></span>
                            </div>
                        </div>
                    </div>

                    <div class="divider divider-left mb-1">
                        <div class="divider-text font-weight-bold text-uppercase" style="font-size: 0.8rem;">
                            1. Pilih Kavling &amp; Item yang Diajukan
                        </div>
                    </div>

                    <!-- Step: Pilih Kavling -->
                    <div class="card border p-1 mb-1 bg-light">
                        <div class="form-group mb-50">
                            <label class="font-weight-bold font-small-3 mb-25">
                                <i class="fas fa-home mr-25 text-primary"></i> Pilih Kavling (Akad KPR)
                            </label>
                            <select id="batch-select-kavling" class="form-control select2" style="width: 100%;">
                                <option value="">-- Pilih Kavling untuk Diajukan --</option>
                            </select>
                            <small class="text-muted d-block mt-25">Pilih kavling untuk melihat item termin / retensi yang belum diajukan.</small>
                        </div>

                        <!-- Item Picker for Selected Kavling -->
                        <div id="batch-item-picker-wrap" style="display: none;" class="mt-1 pt-1 border-top">
                            <div class="d-flex align-items-center justify-content-between mb-50">
                                <span class="font-weight-bold font-small-3 text-dark">
                                    Pilih Item untuk: <span id="batch-picker-kavling-label" class="text-primary font-weight-bolder"></span>
                                </span>
                                <div>
                                    <button type="button" class="btn btn-outline-secondary btn-xs py-25 px-50" id="btn-batch-picker-check-all">Pilih Semua</button>
                                </div>
                            </div>
                            <div id="batch-picker-items-list" class="p-50 bg-white border rounded mb-1" style="max-height: 140px; overflow-y: auto;">
                                <!-- Dynamic checkboxes of available items -->
                            </div>
                            <button type="button" class="btn btn-primary btn-sm btn-block waves-effect" id="btn-add-kavling-to-batch">
                                <i class="fas fa-plus mr-25"></i> Tambahkan Kavling &amp; Item ke Daftar Pengajuan
                            </button>
                        </div>
                    </div>

                    <div class="divider divider-left mb-1">
                        <div class="divider-text font-weight-bold text-uppercase" style="font-size: 0.8rem;">
                            2. Daftar Kavling &amp; Item Terpilih Dalam Pengajuan Ini
                        </div>
                    </div>

                    <div class="table-responsive mb-1" style="max-height: 220px; overflow-y: auto;">
                        <table class="table table-sm table-bordered table-striped mb-0" id="tbl-batch-kavling-list">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 5%;" class="text-center">#</th>
                                    <th style="width: 30%;">Kavling &amp; Konsumen</th>
                                    <th style="width: 40%;">Item yang Diajukan</th>
                                    <th style="width: 15%;" class="text-right">Nominal</th>
                                    <th style="width: 10%;" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="batch-kavling-tbody">
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-2">Belum ada kavling yang ditambahkan. Silakan pilih kavling di atas.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="divider divider-left mb-1">
                        <div class="divider-text font-weight-bold text-uppercase" style="font-size: 0.8rem;">
                            3. Berkas &amp; Informasi Pengajuan ke Bank
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-1">
                                <label class="font-weight-bold">Nomor Pengajuan / Batch</label>
                                <input type="text" class="form-control form-control-sm" id="batch-input-nomor" name="nomor_batch" placeholder="Otomatis digenerate" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-1">
                                <label class="font-weight-bold">Tanggal Pengajuan <span class="text-danger">*</span></label>
                                <input type="date" class="form-control form-control-sm" id="batch-input-tgl-pengajuan" name="tanggal_pengajuan" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-1">
                                <label class="font-weight-bold">Tanggal Rencana Cair (Opsional)</label>
                                <input type="date" class="form-control form-control-sm" id="batch-input-tgl-rencana" name="tanggal_rencana_cair">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-1">
                                <label class="font-weight-bold">Lampiran Surat Pengajuan (PDF / Gambar, Opsional)</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input form-control-sm" id="batch-input-lampiran" name="lampiran_surat" accept="application/pdf,image/*">
                                    <label class="custom-file-label" for="batch-input-lampiran" id="batch-lampiran-label">Pilih file...</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <label class="font-weight-bold">Catatan Pengajuan (Opsional)</label>
                        <textarea class="form-control form-control-sm" id="batch-input-catatan" name="catatan" rows="2" placeholder="Catatan internal pengajuan batch..."></textarea>
                    </div>

                    <!-- Hidden inputs container for kavlings & items -->
                    <div id="batch-hidden-inputs"></div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <div>
                        <span class="text-muted small">Total: </span>
                        <strong id="batch-footer-total" class="text-primary" style="font-size: 1.1rem;">Rp 0</strong>
                    </div>
                    <div>
                        <button type="button" class="btn btn-outline-secondary btn-sm text-uppercase" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm text-uppercase" id="btn-submit-batch-pengajuan">
                            <i class="fas fa-paper-plane mr-25"></i> Simpan Pengajuan Batch
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
