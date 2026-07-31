<script>
    window.current_user_id = <?= (int) (user_id() ?? 0) ?>;
</script>

<div class="modal fade" id="modal_tiket_masalah" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-xl" role="document">
        <div class="modal-content border-0">

            <div class="modal-header bg-white border-bottom-0 pb-0">
                <h5 class="modal-title font-weight-bold text-dark">
                    <i class="feather icon-alert-circle text-warning mr-1"></i> Tiket Masalah
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body p-3" style="background-color: #f8fafc;">
                <div class="row">
                    <!-- HERO CARD HEADER (Standard SIGAPP Detail Style) -->
                    <div class="card detail-hero-card mb-3 col-md-3">
                        <div class="card-body p-3">
                            <div class="row align-items-center">
                                <div class="col-md-7 mb-2 mb-md-0">
                                    <div class="hero-sub text-white-50 text-uppercase font-weight-bold" id="tm_ref_tag_text">KAVLING</div>
                                    <div class="hero-title text-white mt-1" id="tm_ref_title">Proyek > Cluster > Jalan</div>
                                </div>
                                <div class="col-md-5">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="text-xs text-white-50 font-weight-bold">PROGRES PEMBANGUNAN</span>
                                        <span class="text-sm font-weight-bold text-white" id="tm_hero_progress_text">0%</span>
                                    </div>
                                    <div class="progress-track">
                                        <div class="progress-fill" id="tm_hero_progress_bar" style="width: 0%;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- VIEW 1: LIST TIKET -->
                    <div id="view_list_tiket" class="col-md-9">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0 font-weight-bold text-slate-700">Daftar Tiket Kendala</h6>
                            <button class="btn btn-primary btn-sm rounded-pill px-3" id="btn_show_buat_tiket">
                                <i class="feather icon-plus mr-1"></i> Buat Masalah Baru
                            </button>
                        </div>
                        <div id="list_tiket_masalah">
                            <!-- List cards rendered via JS -->
                        </div>
                    </div>
                    <!-- VIEW 2: DETAIL TIKET & HISTORY -->
                    <div id="view_detail_tiket" class="d-none col-md-9">
                        <button class="btn btn-sm btn-light border mb-3 rounded-pill" id="btn_back_to_list">
                            <i class="feather icon-arrow-left mr-1"></i> Kembali ke Daftar
                        </button>
                        <div id="tm_detail_content">
                            <!-- Rendered via JS -->
                        </div>
                    </div>

                    <!-- VIEW 3: FORM BUAT TIKET -->
                    <div id="form_buat_tiket" class="d-none col-md-9">
                        <div class="card border-0 shadow-sm rounded-12 mb-0">
                            <div class="card-header bg-white p-3 border-bottom">
                                <h6 class="mb-0 font-weight-bold">Buat Tiket Masalah Baru</h6>
                            </div>
                            <div class="card-body p-3">
                                <form id="form_buat_tiket_form">
                                    <div class="row">
                                        <div class="col-md-6 form-group">
                                            <label class="tm-detail-label">Tanggal Masalah <span class="text-danger">*</span></label>
                                            <input type="text" name="tanggal_masalah" class="form-control flatpickr" value="<?= date('Y-m-d') ?>" required>
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label class="tm-detail-label">Skala Prioritas <span class="text-danger">*</span></label>
                                            <select name="prioritas" class="form-control" required>
                                                <option value="normal" selected>Normal (Kuning)</option>
                                                <option value="low">Low (Abu-abu)</option>
                                                <option value="medium">Medium (Oranye)</option>
                                                <option value="urgent">Urgent (Merah)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="tm-detail-label">Keterangan Masalah <span class="text-danger">*</span></label>
                                        <textarea name="keterangan" class="form-control" rows="3" required placeholder="Deskripsikan masalah dengan jelas..."></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label class="tm-detail-label">Foto Pendukung (Max 5MB/foto)</label>
                                        <input type="file" name="foto[]" id="tm_foto" class="form-control-file" multiple accept="image/*">
                                        <small class="text-muted d-block mt-1">Bisa pilih beberapa foto sekaligus.</small>
                                    </div>
                                    <div class="form-group">
                                        <label class="tm-detail-label">User yang Dilibatkan (Assigned)</label>
                                        <select name="assigned_users[]" id="tm_assigned_users" class="select2 form-control" multiple="multiple" style="width:100%">
                                            <!-- User options loaded via ajax -->
                                        </select>
                                        <small class="text-muted d-block mt-1">User yang dipilih akan mendapat notifikasi dan bisa menambah progress.</small>
                                    </div>
                                    <hr class="my-3">
                                    <div class="text-right">
                                        <button type="button" class="btn btn-light border mr-2" id="btn_batal_buat_tiket">Batal</button>
                                        <button type="submit" class="btn btn-primary px-4">Simpan Tiket</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>







            </div>
        </div>
    </div>
</div>