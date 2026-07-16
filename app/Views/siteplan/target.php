<div class="modal fade" id="modal-target-siteplan" tabindex="-1" role="dialog" aria-labelledby="modal-target-siteplan" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Target Siteplan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4">
                        <form id="fm-target-siteplan">
                            <input type="hidden" id="target-id_target" name="id_target">
                            <input type="hidden" id="target-id_proyek" name="id_proyek">
                            <input type="hidden" id="target-id_kavling" name="id_kavling">
                            <div class="form-group">
                                <label>Proyek</label>
                                <input type="text" class="form-control" id="target-nama_proyek" readonly>
                            </div>
                            <div class="form-group">
                                <label>Tahun Target</label>
                                <input type="number" min="2000" max="2100" class="form-control" id="target-tahun_target" name="tahun_target">
                            </div>
                            <div class="form-group">
                                <label>Deskripsi</label>
                                <textarea class="form-control" id="target-deskripsi" name="deskripsi" rows="4"></textarea>
                            </div>
                            <div class="alert alert-info py-1" id="target-selected-count">0 kavling dipilih</div>
                            <button type="button" class="btn btn-primary btn-block" id="target-save-btn" onclick="save_target_siteplan()">Simpan Target</button>
                            <button type="button" class="btn btn-outline-secondary btn-block" onclick="reset_target_form()">Target Baru</button>
                        </form>
                    </div>
                    <div class="col-md-4">
                        <h6>Daftar Target</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Tahun</th>
                                        <th>Kavling</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="target-list-here"></tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <h6>Histori Perubahan</h6>
                        <div id="target-history-here" class="small text-muted">Pilih target untuk melihat histori.</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script src="<?= base_url() ?>assets/js/siteplan/target.js?v=<?= filemtime(FCPATH.'assets/js/siteplan/target.js') ?>"></script>