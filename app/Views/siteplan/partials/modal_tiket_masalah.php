<script>
    window.current_user_id = <?= (int) (user_id() ?? 0) ?>;
    window.is_supervisor_manager = <?= (function_exists('in_groups') && in_groups(['Admin', 'Direksi', 'Manager', 'Supervisor'])) ? 'true' : 'false' ?>;
</script>

<div class="modal fade" id="modal_tiket_masalah" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-xl" role="document">
        <div class="modal-content border-0">

            <!-- 1. SYSTEM TOP BAR -->
            <div class="tm-system-bar">
                <div class="d-flex align-items-center">
                    <span class="font-weight-bold text-white" style="font-size: 13px; letter-spacing: 0.3px;">Tiket Masalah</span>
                </div>
                <div class="d-flex align-items-center">
                    <button type="button" class="tm-btn-close-system" data-dismiss="modal" aria-label="Close">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <!-- 2. SUB-HEADER NAVIGATION (Populated dynamically in detail view) -->
            <div id="tm_subheader_nav" class="tm-subheader-nav d-none">
                <!-- Injected via renderTiketDetail() -->
            </div>

            <!-- 3. MAIN BODY 2-COLUMN LAYOUT -->
            <div class="modal-body p-0" style="background-color: #f8fafc; overflow: hidden;">
                <div class="row no-gutters h-100">
                    <!-- LEFT SIDEBAR: Gradient Hero Blue (col-12 col-md-3) -->
                    <div class="col-12 col-md-3 p-0">
                        <div class="tm-hero-sidebar">
                            <div>
                                <span class="tm-sidebar-label">KAVLING &amp; UNIT TERPILIH</span>
                                <h4 class="tm-sidebar-project-title" id="tm_hero_project_title">SANGGAR INDAH PALASTRI</h4>

                                <div class="tm-sidebar-meta-item" id="tm_hero_location_container">
                                    <div class="tm-sidebar-icon-box">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </div>
                                    <div>
                                        <div class="tm-sidebar-meta-val" id="tm_hero_location_text">CALYA DALAM, No.8</div>
                                        <span class="tm-sidebar-meta-sub">Alamat / Posisi Unit</span>
                                    </div>
                                </div>

                                <div class="tm-sidebar-meta-item" id="tm_hero_tipe_container">
                                    <div class="tm-sidebar-icon-box">
                                        <i class="fas fa-home"></i>
                                    </div>
                                    <div>
                                        <div class="tm-sidebar-meta-val" id="tm_hero_tipe_text">Tipe 31/72 DESAIN BARU</div>
                                        <span class="tm-sidebar-meta-sub">Spesifikasi Desain</span>
                                    </div>
                                </div>
                            </div>

                            <div class="tm-sidebar-progress-box">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="tm-sidebar-label mb-0">PROGRES FISIK</span>
                                    <span class="font-weight-bold text-white text-sm" id="tm_hero_progress_text">0%</span>
                                </div>
                                <div class="tm-progress-track">
                                    <div class="tm-progress-fill" id="tm_hero_progress_bar" style="width: 0%;"></div>
                                </div>
                                <span class="tm-sidebar-meta-sub mt-2">Terakhir diverifikasi dari opname lapangan</span>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT CONTENT AREA (col-12 col-md-9) -->
                    <div class="col-12 col-md-9 p-0 tm-main-content-scroll">

                        <!-- VIEW 1: LIST TIKET -->
                        <div id="view_list_tiket" class="p-3 p-md-4">
                            <!-- Mobile hero card placeholder at top of list view -->
                            <div id="tm_mobile_list_hero_container" class="d-block d-md-none mb-3"></div>

                            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                <div>
                                    <span class="tm-sidebar-label text-slate-500 mb-0" style="font-size: 10px; font-weight: 800; letter-spacing: 0.8px; color: #64748b; text-transform: uppercase;">DAFTAR LAPORAN</span>
                                    <h5 class="mb-0 font-weight-bold text-slate-800" style="font-size: 1.05rem;">Tiket Masalah &amp; Kendala Unit</h5>
                                </div>
                                <button class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm font-weight-bold transition-all" id="btn_show_buat_tiket">
                                    <i class="fas fa-plus mr-1"></i> Buat Laporan
                                </button>
                            </div>
                            <div id="list_tiket_masalah">
                                <!-- List cards rendered via JS -->
                            </div>
                        </div>

                        <!-- VIEW 2: DETAIL TIKET & HISTORY -->
                        <div id="view_detail_tiket" class="d-none p-3 p-md-4">
                            <div id="tm_detail_content">
                                <!-- Rendered via JS -->
                            </div>
                        </div>

                        <!-- VIEW 3: FORM BUAT / EDIT TIKET -->
                        <div id="form_buat_tiket" class="d-none p-3 p-md-4">
                            <!-- Mobile hero card placeholder at top of form view -->
                            <div id="tm_mobile_form_hero_container" class="d-block d-md-none mb-3"></div>

                            <div class="card border-0 shadow-sm mb-0" style="border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden;">
                                <div class="card-header bg-white p-3 p-md-4 border-bottom d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center flex-nowrap" style="gap: 8px;">
                                        <button type="button" class="btn btn-sm btn-light border rounded-pill px-3 font-weight-bold mr-2 text-nowrap flex-shrink-0" id="btn_batal_buat_tiket_top" style="white-space: nowrap;">
                                            <i class="fas fa-arrow-left mr-1"></i> Kembali
                                        </button>
                                        <div style="min-width: 0;">
                                            <span class="tm-sidebar-label text-slate-500 mb-0 text-truncate" id="tm_form_subtitle_label">FORM LAPORAN KENDALA</span>
                                            <h5 class="mb-0 font-weight-bold text-slate-800" id="tm_form_title_heading" style="font-size: 1.1rem;">Buat Laporan Baru</h5>
                                        </div>
                                    </div>
                                    <span class="badge badge-light-primary border border-primary px-3 py-1 font-weight-bold text-xs d-none d-sm-inline-block" id="tm_form_status_pill">
                                        Formulir Lapangan
                                    </span>
                                </div>
                                <div class="card-body p-3 p-md-4">
                                    <form id="form_buat_tiket_form">
                                        <!-- BLOK INPUT AREA BARU -->
                                        <div id="tm_new_others_fields" class="d-none border p-3 rounded-12 mb-3" style="border-radius: 12px; background: #f8fafc; border: 1px solid #e2e8f0;">
                                            <h6 class="font-weight-bold text-primary mb-3 border-bottom pb-2"><i class="feather icon-map-pin mr-1"></i> Data Area Baru</h6>
                                            <div class="row">
                                                <div class="col-md-6 form-group">
                                                    <label class="tm-detail-label" for="tm_id_jenis">Jenis Area <span class="text-danger">*</span></label>
                                                    <select id="tm_id_jenis" name="id_jenis" class="custom-select form-control">
                                                        <option value=""> - Pilih Jenis - </option>
                                                        <option value="jalan">Jalan</option>
                                                        <option value="fasos">Fasos</option>
                                                        <option value="rth">RTH</option>
                                                        <option value="fasum">Fasum</option>
                                                        <option value="ruko">RUKO</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6 form-group">
                                                    <label class="tm-detail-label" for="tm_nama_others">Nama Area <span class="text-danger">*</span></label>
                                                    <input type="text" id="tm_nama_others" name="nama" class="form-control" placeholder="Mis: Taman Utama">
                                                </div>
                                                <div class="col-md-6 form-group">
                                                    <label class="tm-detail-label" for="tm_id_cluster">Cluster (Opsional)</label>
                                                    <select id="tm_id_cluster" name="id_cluster" class="form-control" style="width:100%">
                                                        <option value=""> - Semua Cluster - </option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6 form-group">
                                                    <label class="tm-detail-label" for="tm_id_jalan">Jalan/Blok (Opsional)</label>
                                                    <select id="tm_id_jalan" name="id_jalan" class="form-control" style="width:100%">
                                                        <option value=""> - Pilih Jalan - </option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-4 form-group">
                                                <label class="tm-detail-label">Tanggal Laporan <span class="text-danger">*</span></label>
                                                <input type="text" name="tanggal_masalah" class="form-control flatpickr" value="<?= date('Y-m-d') ?>" required>
                                            </div>
                                            <div class="col-md-4 form-group">
                                                <label class="tm-detail-label">Tanggal Kunjungan</label>
                                                <input type="text" name="tanggal_kunjungan" class="form-control flatpickr" placeholder="Pilih Tanggal">
                                            </div>
                                            <div class="col-md-4 form-group">
                                                <label class="tm-detail-label">Skala Prioritas <span class="text-danger">*</span></label>
                                                <select name="prioritas" class="custom-select form-control" required>
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
                                            <textarea name="keterangan" id="keterangan_masalah" class="form-control richtext" rows="4" required placeholder="Deskripsikan temuan masalah dengan jelas..."></textarea>
                                        </div>

                                        <!-- ADVANCED FILE UPLOAD -->
                                        <div class="form-group">
                                            <label class="tm-detail-label">Foto Pendukung (Max 5MB/foto)</label>

                                            <div class="drag-drop-zone mb-2" id="tm_dropzone">
                                                <div class="mb-2" style="font-size: 2rem; color: #1e5da6;">
                                                    <i class="fas fa-cloud-upload-alt"></i>
                                                </div>
                                                <p class="mb-1 font-weight-bold text-slate-800">Tarik &amp; Lepas Foto di sini, atau Paste (Ctrl + V)</p>
                                                <span class="text-xs text-muted d-block mb-3">Format JPG, PNG, WEBP (Bisa upload beberapa foto sekaligus)</span>
                                                <div class="d-flex justify-content-center flex-wrap gap-2">
                                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 mr-2 font-weight-bold" onclick="$('#tm_foto').click()">
                                                        <i class="fas fa-folder-open mr-1"></i> Pilih File
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-3 font-weight-bold" onclick="$('#tm_foto_camera').click()">
                                                        <i class="fas fa-camera mr-1"></i> Ambil Kamera
                                                    </button>
                                                </div>
                                            </div>

                                            <input type="file" id="tm_foto" class="d-none" multiple accept="image/*">
                                            <input type="file" id="tm_foto_camera" class="d-none" accept="image/*" capture="environment">

                                            <div id="tm_existing_preview_container" class="upload-preview-container mb-2"></div>
                                            <div id="tm_preview_container" class="upload-preview-container"></div>
                                        </div>

                                        <div class="form-group">
                                            <label class="tm-detail-label">User yang Dilibatkan (Assigned)</label>
                                            <select name="assigned_users[]" id="tm_assigned_users" class="select2 form-control" multiple="multiple" style="width:100%">
                                                <!-- User options loaded via ajax -->
                                            </select>
                                            <small class="text-muted d-block mt-1">User yang dipilih akan mendapat notifikasi dan dapat menambah progres perbaikan.</small>
                                        </div>

                                        <div class="d-flex flex-column flex-sm-row justify-content-end align-items-center pt-3 border-top mt-4 gap-2">
                                            <button type="button" class="btn btn-light border rounded-pill px-4 font-weight-bold mr-sm-2 mb-2 mb-sm-0 w-100 w-sm-auto order-3 order-sm-1" id="btn_batal_buat_tiket">
                                                Batal
                                            </button>
                                            <button type="submit" class="btn btn-light border-warning text-warning rounded-pill px-4 font-weight-bold mr-sm-2 mb-2 mb-sm-0 w-100 w-sm-auto order-2 order-sm-2" id="btn_simpan_draft">
                                                <i class="far fa-save mr-1"></i> Simpan Draft
                                            </button>
                                            <button type="submit" class="btn btn-primary rounded-pill px-4 font-weight-bold shadow-sm w-100 w-sm-auto order-1 order-sm-3" id="btn_simpan_tiket">
                                                <i class="fas fa-paper-plane mr-1"></i> Simpan Tiket
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>

            <!-- 4. AUDIT FOOTER (Populated dynamically in detail view) -->
            <div id="tm_audit_footer" class="tm-audit-footer d-none">
                <!-- Injected via renderTiketDetail() -->
            </div>
        </div>
    </div>
</div>

<!-- Lightbox Modal -->
<div class="modal fade" id="tm_lightbox_modal" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content bg-transparent border-0">
            <div class="modal-header border-0 pb-0 justify-content-end position-absolute w-100" style="z-index: 10;">
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 1; text-shadow: 0 0 10px rgba(0,0,0,0.5); font-size: 2.5rem; padding: 1rem;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0 text-center bg-dark rounded">
                <div id="tm_lightbox_carousel" class="carousel slide" data-ride="carousel" data-interval="false">
                    <div class="carousel-inner" id="tm_lightbox_inner">
                        <!-- Items injected via JS -->
                    </div>
                    <a class="carousel-control-prev" href="#tm_lightbox_carousel" role="button" data-slide="prev" style="width: 10%;">
                        <span class="carousel-control-prev-icon" aria-hidden="true" style="width: 3rem; height: 3rem; filter: drop-shadow(0 0 5px rgba(0,0,0,0.5));"></span>
                        <span class="sr-only">Previous</span>
                    </a>
                    <a class="carousel-control-next" href="#tm_lightbox_carousel" role="button" data-slide="next" style="width: 10%;">
                        <span class="carousel-control-next-icon" aria-hidden="true" style="width: 3rem; height: 3rem; filter: drop-shadow(0 0 5px rgba(0,0,0,0.5));"></span>
                        <span class="sr-only">Next</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>