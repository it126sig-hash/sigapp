<script>
    window.current_user_id = <?= (int) (user_id() ?? 0) ?>;
</script>

<div class="modal fade" id="modal_tiket_masalah" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <div>
                    <h5 class="modal-title mb-0">
                        <i class="feather icon-alert-circle text-warning"></i> Tiket Masalah
                    </h5>
                    <div class="text-sm text-muted mt-1" id="tm_ref_title"></div>
                    <span class="badge badge-light-secondary mt-1" id="tm_ref_tag"></span>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            
            <div class="modal-body p-3 bg-white">
                
                <!-- VIEW 1: LIST TIKET -->
                <div id="view_list_tiket">
                    <button class="btn btn-primary btn-sm mb-3" id="btn_show_buat_tiket">
                        <i class="feather icon-plus"></i> Buat Masalah Baru
                    </button>
                    <div id="list_tiket_masalah">
                        <!-- Loading spinner / list card here -->
                    </div>
                </div>

                <!-- VIEW 2: DETAIL TIKET & HISTORY -->
                <div id="view_detail_tiket" class="d-none">
                    <button class="btn btn-sm btn-outline-secondary mb-3" id="btn_back_to_list">
                        <i class="feather icon-arrow-left"></i> Kembali ke Daftar
                    </button>
                    <div id="tm_detail_content">
                        <!-- Detail dan Form Progress di sini -->
                    </div>
                </div>

                <!-- VIEW 3: FORM BUAT TIKET -->
                <div id="form_buat_tiket" class="d-none">
                    <div class="card border mb-0">
                        <div class="card-header bg-light p-2">
                            <h6 class="mb-0">Buat Tiket Masalah Baru</h6>
                        </div>
                        <div class="card-body p-3">
                            <form id="form_buat_tiket_form">
                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label>Tanggal Masalah <span class="text-danger">*</span></label>
                                        <input type="text" name="tanggal_masalah" class="form-control flatpickr" value="<?= date('Y-m-d') ?>" required>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label>Prioritas <span class="text-danger">*</span></label>
                                        <select name="prioritas" class="form-control" required>
                                            <option value="normal" selected>Normal (Kuning)</option>
                                            <option value="low">Low (Abu-abu)</option>
                                            <option value="medium">Medium (Oranye)</option>
                                            <option value="urgent">Urgent (Merah)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Keterangan Masalah <span class="text-danger">*</span></label>
                                    <textarea name="keterangan" class="form-control" rows="3" required placeholder="Deskripsikan masalah dengan jelas..."></textarea>
                                </div>
                                <div class="form-group">
                                    <label>Foto Pendukung (Max 5MB/foto)</label>
                                    <input type="file" name="foto[]" id="tm_foto" class="form-control-file" multiple accept="image/*">
                                    <small class="text-muted">Bisa pilih beberapa foto sekaligus.</small>
                                </div>
                                <div class="form-group">
                                    <label>User yang Dilibatkan (Assigned)</label>
                                    <select name="assigned_users[]" id="tm_assigned_users" class="select2 form-control" multiple="multiple" style="width:100%">
                                        <!-- User options loaded via ajax -->
                                    </select>
                                    <small class="text-muted">User yang dipilih akan mendapat notifikasi dan bisa menambah progress.</small>
                                </div>
                                <hr>
                                <div class="text-right">
                                    <button type="button" class="btn btn-outline-secondary mr-2" id="btn_batal_buat_tiket">Batal</button>
                                    <button type="submit" class="btn btn-primary">Simpan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
