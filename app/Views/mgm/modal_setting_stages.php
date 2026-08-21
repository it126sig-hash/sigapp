<!-- Modal Setting Stages -->
<div class="modal fade" id="modalSettingStages" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pengaturan Tahapan Bonus Referral</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive mb-2">
                    <table class="table table-sm table-bordered" id="table-stages">
                        <thead>
                            <tr>
                                <th>Tahapan</th>
                                <th>Trigger Status MKDT</th>
                                <th>Nominal Default</th>
                                <th>Urutan</th>
                                <th>Aktif</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <hr>
                <h6>Tambah / Edit Tahapan</h6>
                <form id="form-stage">
                    <input type="hidden" id="stage_id">
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label>Nama Tahapan</label>
                            <input type="text" class="form-control" id="stage_nama" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Trigger Status</label>
                            <select class="form-control" id="stage_trigger" required>
                                <option value="Booking">Booking</option>
                                <option value="Akad">Akad</option>
                                <option value="SP3K">SP3K</option>
                                <option value="Batal">Batal</option>
                                <!-- add more if needed -->
                            </select>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Nominal Default</label>
                            <input type="text" class="form-control number-format" id="stage_nominal" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Urutan</label>
                            <input type="number" class="form-control" id="stage_urutan" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Aktif</label>
                            <select class="form-control" id="stage_aktif">
                                <option value="1">Ya</option>
                                <option value="0">Tidak</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <button type="button" class="btn btn-sm btn-primary" id="btn-save-stage">Simpan Tahapan</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-reset-stage">Batal</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
