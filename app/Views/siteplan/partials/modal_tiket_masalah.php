<script>
    window.current_user_id = <?= (int) (user_id() ?? 0) ?>;
</script>

<style>
    #modal_tiket_masalah .modal-dialog {
        max-width: 100%;
        margin: 0;
        top: 0;
        bottom: 0;
        left: 0;
        right: 0;
        height: 100vh;
        display: flex;
    }
    #modal_tiket_masalah .modal-content {
        height: 100vh;
        border-radius: 0;
    }
    #modal_tiket_masalah .detail-kavling-sidebar,
    #modal_tiket_masalah .detail-hero-card {
        position: static !important;
    #modal_tiket_masalah .badge-prio-laporan { background-color: #00cfe8; color: #fff; }
</style>
<div class="modal fade" id="modal_tiket_masalah" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-fullscreen" role="document">
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
                    <!-- LEFT SIDEBAR (col-md-3 Sticky Hero Card) -->
                    <div class="col-md-3 detail-kavling-sidebar mb-3 mb-md-0">
                        <div class="card detail-hero-card">
                            <div class="card-body p-3">
                                <h4 class="hero-project-title mb-2" id="tm_hero_project_title">SANGGAR INDAH PALASTRI</h4>

                                <div class="hero-meta-item mb-2" id="tm_hero_location_container">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span id="tm_hero_location_text">CALYA DALAM, No.8</span>
                                </div>

                                <div class="hero-meta-item mb-3" id="tm_hero_tipe_container">
                                    <i class="fas fa-home"></i>
                                    <span id="tm_hero_tipe_text">Tipe 31/72 DESAIN BARU</span>
                                </div>

                                <div class="mt-3 pt-2 border-top border-white-50">
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

                    <!-- RIGHT CONTENT AREA (col-md-9) -->
                    <div class="col-md-9">

                        <!-- VIEW 1: LIST TIKET -->
                        <div id="view_list_tiket">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0 font-weight-bold text-slate-700">Daftar Tiket Kendala</h6>
                                <button class="btn btn-primary btn-sm rounded-pill px-3" id="btn_show_buat_tiket">
                                    <i class="fas fa-plus mr-1"></i> Buat Laporan
                                </button>
                            </div>
                            <div id="list_tiket_masalah">
                                <!-- List cards rendered via JS -->
                            </div>
                        </div>

                        <!-- VIEW 2: DETAIL TIKET & HISTORY -->
                        <div id="view_detail_tiket" class="d-none">
                            <button class="btn btn-sm btn-light border mb-3 rounded-pill" id="btn_back_to_list">
                                <i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar
                            </button>
                            <div id="tm_detail_content">
                                <!-- Rendered via JS -->
                            </div>
                        </div>

                        <!-- VIEW 3: FORM BUAT TIKET -->
                        <div id="form_buat_tiket" class="d-none">
                            <div class="card border-0 shadow-sm rounded-12 mb-0">
                                <div class="card-header bg-white p-3 border-bottom">
                                    <h6 class="mb-0 font-weight-bold">Buat Tiket Masalah Baru</h6>
                                </div>
                                <div class="card-body p-3">
                                    <form id="form_buat_tiket_form">
                                        <!-- BLOK INPUT AREA BARU (Disembunyikan jika tiket untuk area yang sudah ada) -->
                                        <div id="tm_new_others_fields" class="d-none border p-3 bg-light rounded mb-3">
                                            <h6 class="font-weight-bold text-primary mb-3 border-bottom pb-2"><i class="feather icon-map-pin mr-1"></i> Data Area Baru</h6>
                                            <div class="row">
                                                <div class="col-md-6 form-group">
                                                    <label class="form-label" for="tm_id_jenis">Jenis Area <span class="text-danger">*</span></label>
                                                    <select id="tm_id_jenis" name="id_jenis" class="custom-select">
                                                        <option value=""> - Pilih Jenis - </option>
                                                        <option value="jalan">Jalan</option>
                                                        <option value="fasos">Fasos</option>
                                                        <option value="rth">RTH</option>
                                                        <option value="fasum">Fasum</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6 form-group">
                                                    <label class="form-label" for="tm_nama_others">Nama Area <span class="text-danger">*</span></label>
                                                    <input type="text" id="tm_nama_others" name="nama" class="form-control" placeholder="Mis: Taman Utama">
                                                </div>
                                                <div class="col-md-6 form-group">
                                                    <label class="form-label" for="tm_id_cluster">Cluster (Opsional)</label>
                                                    <select id="tm_id_cluster" name="id_cluster" class="select2 form-control" style="width:100%">
                                                        <option value=""> - Semua Cluster - </option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6 form-group">
                                                    <label class="form-label" for="tm_id_jalan">Jalan/Blok (Opsional)</label>
                                                    <select id="tm_id_jalan" name="id_jalan" class="select2 form-control" style="width:100%">
                                                        <option value=""> - Pilih Jalan - </option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 form-group">
                                                <label class="tm-detail-label">Tanggal Masalah <span class="text-danger">*</span></label>
                                                <input type="text" name="tanggal_masalah" class="form-control flatpickr" value="<?= date('Y-m-d') ?>" required>
                                            </div>
                                            <div class="col-md-6 form-group">
                                                <label class="tm-detail-label">Skala Prioritas <span class="text-danger">*</span></label>
                                                <select name="prioritas" class="custom-select" required>
                                                <option value="normal">Normal</option>
                                                <option value="low">Low</option>
                                                <option value="medium">Medium</option>
                                                <option value="urgent">Urgent</option>
                                                <option value="laporan">Laporan</option>
                                            </select>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="tm-detail-label">Keterangan Masalah <span class="text-danger">*</span></label>
                                            <textarea name="keterangan" id="keterangan_masalah" class="form-control richtext" rows="3" required placeholder="Deskripsikan masalah dengan jelas..."></textarea>
                                        </div>

                                        <!-- ADVANCED FILE UPLOAD (Drag & Drop, Clipboard Paste, Kamera HTML5) -->
                                        <div class="form-group">
                                            <label class="tm-detail-label">Foto Pendukung (Max 5MB/foto)</label>

                                            <div class="drag-drop-zone mb-2" id="tm_dropzone">
                                                <i class="feather icon-upload-cloud font-large-1 text-primary mb-2"></i>
                                                <p class="mb-1 font-weight-bold text-dark">Tarik & Lepas Foto di sini, atau Paste (Ctrl + V)</p>
                                                <span class="text-xs text-muted">Bisa upload beberapa foto sekaligus</span>
                                                <div class="mt-2">
                                                    <button type="button" class="btn btn-sm btn-outline-primary mr-1" onclick="$('#tm_foto').click()">
                                                        <i class="feather icon-file-plus mr-1"></i> Pilih File
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="$('#tm_foto_camera').click()">
                                                        <i class="feather icon-camera mr-1"></i> Ambil Foto Kamera
                                                    </button>
                                                </div>
                                            </div>

                                            <input type="file" id="tm_foto" class="d-none" multiple accept="image/*">
                                            <input type="file" id="tm_foto_camera" class="d-none" accept="image/*" capture="environment">

                                            <div id="tm_preview_container" class="upload-preview-container"></div>
                                        </div>

                                        <div class="form-group">
                                            <label class="tm-detail-label">User yang Dilibatkan (Assigned)</label>
                                            <select name="assigned_users[]" id="tm_assigned_users" class="select2 form-control" multiple="multiple" style="width:100%">
                                                <!-- User options loaded via ajax -->
                                            </select>
                                            <small class="text-muted d-block mt-1">User yang dipilih akan mendapat notifikasi dan bisa menambah progress.</small>
                                        </div>

                                        <hr class="my-3">

                                        <div class="d-flex flex-column flex-md-row justify-content-end mt-3 gap-2">
                                            <button type="button" class="btn btn-light border mb-2 mb-md-0 order-2 order-md-1" id="btn_batal_buat_tiket">Batal</button>
                                            <button type="submit" class="btn btn-primary px-4 order-1 order-md-2">Simpan Tiket</button>
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
</div>