<link rel="stylesheet" href="<?= base_url() ?>app-assets/vendors/css/tables/datatable/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="<?= base_url() ?>app-assets/vendors/css/tables/datatable/responsive.bootstrap4.min.css">
<link rel="stylesheet" href="<?= base_url() ?>app-assets/vendors/css/extensions/sweetalert2.min.css">
<link rel="stylesheet" href="<?= base_url() ?>app-assets/vendors/css/forms/select/select2.min.css">
<link rel="stylesheet" href="<?= base_url() ?>assets/css/bpb.css?v=<?= time() ?>">

<div class="app-content content bpb-page">
    <div class="content-overlay"></div><div class="header-navbar-shadow"></div>
    <div class="content-wrapper">
        <div class="content-header row mb-1">
            <div class="content-header-left col-md-7 col-12"><h2 class="content-header-title mb-0">Bon Permintaan Barang</h2></div>
            <div class="content-header-right col-md-5 col-12 text-md-right mt-1 mt-md-0">
                <button class="btn btn-primary" id="bpb-create"><i class="fa fa-plus mr-50"></i>Buat BPB</button>
            </div>
        </div>

        <section id="bpb-list-page">
            <div class="card">
                <div class="card-header border-bottom d-flex flex-wrap align-items-center justify-content-between">
                    <h4 class="card-title mb-1 mb-md-0">Daftar BPB</h4>
                    <div class="bpb-list-controls d-flex align-items-center">
                        <button type="button" class="btn btn-outline-primary mr-50" id="bpb-open-filter"><i class="fa fa-filter mr-50"></i>Filter <span id="bpb-filter-count" class="badge badge-primary ml-25 d-none">0</span></button>
                        <button type="button" class="btn btn-outline-secondary" id="bpb-refresh" title="Muat ulang daftar" aria-label="Muat ulang daftar"><i class="fa fa-refresh"></i><span class="ml-50 d-none d-sm-inline">Refresh</span></button>
                    </div>
                    <div id="bpb-active-filters" class="bpb-active-filters d-none" aria-live="polite"></div>
                </div>
                <div class="card-datatable table-responsive">
                    <table id="bpb-table" class="table table-striped table-hover">
                        <thead><tr><th>Nomor</th><th>Nama Item</th><th>Pembuat BPB</th><th>Divisi</th><th>Tanggal Pengajuan</th><th>Status Terakhir</th></tr></thead>
                    </table>
                    <div id="bpb-mobile-list" class="d-none" aria-live="polite"></div>
                </div>
            </div>
        </section>

        <section id="bpb-form-page" class="d-none">
            <div class="card">
                <div class="card-header border-bottom"><div><button class="btn btn-sm btn-outline-secondary mr-1" id="bpb-form-back"><i class="fa fa-arrow-left"></i></button><span class="card-title" id="bpb-form-title">Buat BPB</span></div></div>
                <div class="card-body pt-2">
                    <form id="bpb-form" novalidate>
                        <input type="hidden" id="bpb-id">
                        <div class="divider divider-left"><div class="divider-text">Barang yang diminta</div></div>
                        <div id="bpb-items"></div>
                        <button type="button" class="btn btn-sm btn-outline-primary mb-2" id="bpb-add-item"><i class="fa fa-plus mr-50"></i>Tambah Item</button>

                        <div class="divider divider-left"><div class="divider-text">Lampiran dan Persetujuan</div></div>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Lampiran awal <span class="text-danger">*</span></label>
                                <input type="file" class="d-none" id="bpb-attachments" accept="image/jpeg,image/png,image/webp,application/pdf" multiple>
                                <div id="bpb-dropzone" class="bpb-dropzone" role="group" tabindex="0" aria-label="Lampiran BPB: tarik file, tempel gambar, atau pilih file">
                                    <i class="fa fa-cloud-upload-alt" aria-hidden="true"></i>
                                    <strong>Tarik file ke sini atau tempel gambar</strong>
                                    <small>JPG/PNG/WEBP/PDF · maksimal 5 file, 5 MB per file</small>
                                    <button type="button" class="btn btn-sm btn-outline-primary bpb-select-files">Pilih file</button>
                                </div>
                                <div id="bpb-upload-message" class="bpb-upload-message" role="status" aria-live="polite"></div>
                                <div id="bpb-selected-files" class="bpb-selected-files mt-1"></div>
                                <div id="bpb-existing-files" class="mt-1"></div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group"><label>CC / Sub Mengetahui</label><select class="form-control bpb-user-select" id="bpb-cc"><option value="">Tanpa CC</option></select></div>
                                <div class="form-group"><label>Mengetahui <span class="text-danger">*</span></label><select class="form-control bpb-user-select" id="bpb-approver"><option value="">Pilih penanda tangan</option></select></div>
                            </div>
                        </div>

                        <div id="bpb-submit-signature">
                            <div class="divider divider-left"><div class="divider-text">Tanda Tangan Pemohon</div></div>
                            <div class="row">
                                <div class="col-md-4 form-group"><label>Metode</label><select class="form-control" id="bpb-sign-method"><option value="canvas">Gambar sekarang</option><option value="profile">Gunakan TTD profil</option></select></div>
                                <div class="col-md-4 form-group"><label>Password saat ini</label><input type="password" autocomplete="current-password" class="form-control" id="bpb-password"></div>
                            </div>
                            <div class="bpb-canvas-wrap" id="bpb-form-canvas-wrap"><canvas id="bpb-form-canvas"></canvas><button type="button" class="btn btn-sm btn-outline-secondary bpb-clear-canvas" data-canvas="bpb-form-canvas">Hapus</button></div>
                            <div class="custom-control custom-checkbox mt-1"><input type="checkbox" class="custom-control-input" id="bpb-consent"><label class="custom-control-label" for="bpb-consent">Saya menyetujui isi BPB dan penggunaan tanda tangan elektronik internal ini.</label></div>
                        </div>
                        <div class="d-flex flex-wrap justify-content-end mt-2 bpb-sticky-actions">
                            <button type="button" class="btn btn-outline-secondary mr-1" id="bpb-save-draft">Simpan Draft</button>
                            <button type="submit" class="btn btn-primary" id="bpb-submit">Ajukan BPB</button>
                        </div>
                    </form>
                </div>
            </div>
            <div id="bpb-submit-overlay" class="bpb-submit-overlay d-none" aria-live="polite" aria-hidden="true">
                <div class="bpb-submit-card">
                    <div class="spinner-border text-primary mb-1" role="status"><span class="sr-only">Memproses...</span></div>
                    <strong id="bpb-submit-title">Menyimpan BPB</strong>
                    <small id="bpb-submit-status" class="text-muted">Sedang menyiapkan data dan lampiran...</small>
                    <div class="progress bpb-submit-progress"><div id="bpb-submit-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width:0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div></div>
                    <div class="bpb-submit-progress-meta"><small id="bpb-submit-file-info"></small><strong id="bpb-submit-percentage">0%</strong></div>
                    <small class="text-muted">Mohon tunggu hingga proses selesai.</small>
                </div>
            </div>
        </section>
    </div>
</div>

<div class="modal fade bpb-filter-modal" id="bpb-filter-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-slideout" role="document"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title"><i class="fa fa-filter text-primary mr-50"></i>Filter Daftar BPB</h5><button type="button" class="close" data-dismiss="modal" aria-label="Tutup">&times;</button></div>
        <div class="modal-body">
            <div class="form-group"><label for="bpb-filter-scope">Cakupan</label><select id="bpb-filter-scope" class="form-control"><option value="related">Terkait Saya</option><option value="all">Semua</option></select></div>
            <div class="form-group"><label for="bpb-filter-current-status">Status saat ini</label><select id="bpb-filter-current-status" class="form-control"><option value="">Semua Status</option></select></div>
            <div class="divider divider-left"><div class="divider-text">Tanggal perubahan status</div></div>
            <div class="form-group"><label for="bpb-filter-history-status">Status yang dicari</label><select id="bpb-filter-history-status" class="form-control"><option value="">Pilih Status</option></select><small class="form-text text-muted">Pilih status untuk mencari waktu BPB masuk ke status tersebut.</small></div>
            <div class="form-row"><div class="form-group col-6"><label for="bpb-filter-date-from">Dari tanggal</label><input id="bpb-filter-date-from" type="date" class="form-control" disabled></div><div class="form-group col-6"><label for="bpb-filter-date-to">Sampai tanggal</label><input id="bpb-filter-date-to" type="date" class="form-control" disabled></div></div>
            <div class="form-group"><label for="bpb-filter-department">Divisi pembuat</label><select id="bpb-filter-department" class="form-control"><option value="">Semua Divisi</option></select></div>
            <div class="form-group"><label for="bpb-filter-applicant">Pembuat BPB</label><select id="bpb-filter-applicant" class="form-control"><option value="">Semua Pembuat</option></select></div>
        </div>
        <div class="modal-footer justify-content-between"><button type="button" id="bpb-filter-clear" class="btn btn-outline-secondary">Bersihkan</button><div><button type="button" class="btn btn-link mr-50" data-dismiss="modal">Tutup</button><button type="button" id="bpb-filter-apply" class="btn btn-primary">Terapkan</button></div></div>
    </div></div>
</div>

<div class="modal fade" id="bpb-detail-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document"><div class="modal-content">
        <div class="modal-header bpb-detail-header"><h5 class="modal-title" id="bpb-detail-title">Detail BPB</h5><div class="bpb-detail-header-actions"><span class="badge bpb-status-badge bpb-status--draft" id="bpb-detail-status">Draft</span><button type="button" class="close" data-dismiss="modal" aria-label="Tutup">&times;</button></div></div>
        <div class="modal-body" id="bpb-detail-body"></div>
        <div class="modal-footer flex-wrap" id="bpb-detail-actions"></div>
    </div></div>
</div>

<div class="modal fade" id="bpb-action-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title" id="bpb-action-title">Aksi BPB</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
        <form id="bpb-action-form"><div class="modal-body">
            <div id="bpb-action-fields"></div>
            <div id="bpb-action-signature" class="d-none">
                <div class="form-group"><label>Metode tanda tangan</label><select id="bpb-action-sign-method" class="form-control"><option value="canvas">Gambar sekarang</option><option value="profile">Gunakan TTD profil</option></select></div>
                <div class="form-group"><label>Password saat ini</label><input type="password" id="bpb-action-password" autocomplete="current-password" class="form-control"></div>
                <div class="bpb-canvas-wrap" id="bpb-action-canvas-wrap"><canvas id="bpb-action-canvas"></canvas><button type="button" class="btn btn-sm btn-outline-secondary bpb-clear-canvas" data-canvas="bpb-action-canvas">Hapus</button></div>
            </div>
        </div><div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Tutup</button><button type="submit" class="btn btn-primary">Simpan</button></div></form>
    </div></div>
</div>

<div id="bpb-image-lightbox" class="bpb-lightbox d-none" role="dialog" aria-modal="true" aria-label="Pratinjau lampiran" aria-hidden="true">
    <button type="button" id="bpb-lightbox-close" class="bpb-lightbox-close" aria-label="Tutup pratinjau"><i class="fa fa-times"></i></button>
    <button type="button" id="bpb-lightbox-prev" class="bpb-lightbox-nav bpb-lightbox-prev" aria-label="Gambar sebelumnya"><i class="fa fa-chevron-left"></i></button>
    <figure class="bpb-lightbox-figure" id="bpb-lightbox-stage">
        <img id="bpb-lightbox-image" src="" alt="">
        <figcaption id="bpb-lightbox-caption"></figcaption>
    </figure>
    <button type="button" id="bpb-lightbox-next" class="bpb-lightbox-nav bpb-lightbox-next" aria-label="Gambar berikutnya"><i class="fa fa-chevron-right"></i></button>
    <span id="bpb-lightbox-counter" class="bpb-lightbox-counter"></span>
</div>

<script src="<?= base_url() ?>app-assets/vendors/js/vendors.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/tables/datatable/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/tables/datatable/datatables.bootstrap4.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/extensions/sweetalert2.all.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/forms/select/select2.full.min.js"></script>
<script>window.SIGAPP=window.SIGAPP||{};window.SIGAPP.bpb={baseUrl:<?= json_encode(rtrim(base_url(), '/')) ?>,csrfName:<?= json_encode(csrf_token()) ?>,csrfHash:<?= json_encode(csrf_hash()) ?>,openId:<?= json_encode((int) (service('request')->getGet('open') ?? 0)) ?>};</script>
<script src="<?= base_url() ?>assets/js/bpb.js?v=<?= time() ?>"></script>
