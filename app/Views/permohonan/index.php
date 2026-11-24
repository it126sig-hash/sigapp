<?php
$meta        = is_array($meta ?? null) ? $meta : [];
$summary     = is_array($summary ?? null) ? $summary : [];
$projects    = is_array($projects ?? null) ? $projects : ($meta['projects'] ?? []);
$deepLink    = is_array($deepLink ?? null) ? $deepLink : [];
$statuses    = is_array($meta['statuses'] ?? null) ? $meta['statuses'] : [];
$roleIdInt   = (int) ($roleId ?? 0);
$roleNameStr = (string) ($roleName ?? '');
$userIdInt   = (int) (user_id() ?? 0);
$initialScope  = (string) ($deepLink['scope'] ?? 'assigned_to_me');
$initialModule = (string) ($deepLink['module'] ?? 'all');
$initialProyek = $deepLink['id_proyek'] ?? null;
$byScopeAndMod = is_array($summary['by_scope_and_module'] ?? null) ? $summary['by_scope_and_module'] : [];
$scopeModCounts = is_array($byScopeAndMod[$initialScope] ?? null) ? $byScopeAndMod[$initialScope] : ($summary['by_module'] ?? []);

$moduleList = [
    ['key' => 'all', 'label' => 'Semua', 'icon' => 'fa-layer-group'],
    ['key' => 'bpb', 'label' => 'BPB', 'icon' => 'fa-box-open'],
    ['key' => 'fee_sales', 'label' => 'Fee Sales', 'icon' => 'fa-hand-holding-usd'],
    ['key' => 'kavling_request', 'label' => 'Request Kavling', 'icon' => 'fa-map-marked-alt'],
    ['key' => 'tagihan_direksi', 'label' => 'Tagihan Direksi', 'icon' => 'fa-file-signature'],
    ['key' => 'cashout_subkon', 'label' => 'Cashout Subkon', 'icon' => 'fa-hard-hat'],
    ['key' => 'mgm', 'label' => 'MGM', 'icon' => 'fa-users'],
    ['key' => 'tiket_masalah', 'label' => 'Tiket Masalah', 'icon' => 'fa-exclamation-triangle'],
];
?>
<link rel="stylesheet" type="text/css" href="<?= base_url('app-assets/vendors/css/extensions/sweetalert2.min.css') ?>">
<link rel="stylesheet" type="text/css" href="<?= base_url('app-assets/vendors/css/forms/select/select2.min.css') ?>">
<link rel="stylesheet" type="text/css" href="<?= base_url('assets/css/richtext.min.css') ?>">
<link rel="stylesheet" type="text/css" href="<?= base_url('assets/css/permohonan.css') ?>?v=<?= filemtime(FCPATH . 'assets/css/permohonan.css') ?>">

<div class="app-content content permohonan-page">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper">
        <div class="content-header row mb-1 align-items-center">
            <div class="content-header-left col-md-8 col-12">
                <h2 class="content-header-title mb-0"><?= esc($title ?? 'Pusat Permohonan & Approval') ?></h2>
                <p class="permohonan-subtitle mb-0">
                    Antrian respon langsung, status pengajuan pribadi, dan monitoring permohonan lintas departemen.
                </p>
            </div>
            <div class="content-header-right col-md-4 col-12 text-md-right mt-1 mt-md-0">
                <button type="button" class="btn btn-outline-primary" id="permohonan-btn-refresh">
                    <i class="fas fa-sync-alt mr-50"></i>Segarkan
                </button>
            </div>
        </div>

        <div class="content-body">
            <!-- 3 Scope Tabs + KPI Summary Cards -->
            <div class="permohonan-scope-tabs" role="tablist" aria-label="Cakupan permohonan">
                <button type="button"
                    class="permohonan-scope-card <?= $initialScope === 'assigned_to_me' ? 'is-active' : '' ?>"
                    data-scope="assigned_to_me"
                    role="tab"
                    aria-selected="<?= $initialScope === 'assigned_to_me' ? 'true' : 'false' ?>">
                    <span class="permohonan-scope-left">
                        <span class="permohonan-scope-icon"><i class="fas fa-inbox"></i></span>
                        <span class="permohonan-scope-copy">
                            <span class="permohonan-scope-title">Menunggu Respon Saya</span>
                            <span class="permohonan-scope-desc">Butuh TTD, ACC, atau verifikasi Anda</span>
                        </span>
                    </span>
                    <span class="permohonan-scope-count" id="permohonan-count-assigned_to_me"><?= (int) ($summary['assigned_to_me'] ?? 0) ?></span>
                </button>

                <button type="button"
                    class="permohonan-scope-card <?= $initialScope === 'my_requests' ? 'is-active' : '' ?>"
                    data-scope="my_requests"
                    role="tab"
                    aria-selected="<?= $initialScope === 'my_requests' ? 'true' : 'false' ?>">
                    <span class="permohonan-scope-left">
                        <span class="permohonan-scope-icon"><i class="fas fa-paper-plane"></i></span>
                        <span class="permohonan-scope-copy">
                            <span class="permohonan-scope-title">Pengajuan Saya</span>
                            <span class="permohonan-scope-desc">Permohonan yang Anda buat atau ajukan</span>
                        </span>
                    </span>
                    <span class="permohonan-scope-count" id="permohonan-count-my_requests"><?= (int) ($summary['my_requests'] ?? 0) ?></span>
                </button>

                <button type="button"
                    class="permohonan-scope-card <?= $initialScope === 'department_monitoring' ? 'is-active' : '' ?>"
                    data-scope="department_monitoring"
                    role="tab"
                    aria-selected="<?= $initialScope === 'department_monitoring' ? 'true' : 'false' ?>">
                    <span class="permohonan-scope-left">
                        <span class="permohonan-scope-icon"><i class="fas fa-chart-line"></i></span>
                        <span class="permohonan-scope-copy">
                            <span class="permohonan-scope-title">Monitoring Departemen</span>
                            <span class="permohonan-scope-desc">Pantau seluruh status sesuai akses divisi</span>
                        </span>
                    </span>
                    <span class="permohonan-scope-count" id="permohonan-count-department_monitoring"><?= (int) ($summary['department_monitoring'] ?? 0) ?></span>
                </button>
            </div>

            <!-- Main List Card -->
            <section id="permohonan-list-section">
                <div class="card">
                    <div class="card-header d-block">
                        <!-- Module Filter Pills -->
                        <div class="permohonan-module-pills" id="permohonan-module-pills" role="group" aria-label="Filter modul">
                            <?php foreach ($moduleList as $mod): ?>
                                <?php $modCount = (int) ($scopeModCounts[$mod['key']] ?? 0); ?>
                                <button type="button"
                                    class="permohonan-module-pill <?= $initialModule === $mod['key'] ? 'is-active' : '' ?>"
                                    data-module="<?= esc($mod['key']) ?>">
                                    <i class="fas <?= esc($mod['icon']) ?>"></i>
                                    <span><?= esc($mod['label']) ?></span>
                                    <span class="permohonan-pill-badge" data-module-count="<?= esc($mod['key']) ?>"><?= $modCount ?></span>
                                </button>
                            <?php endforeach; ?>
                        </div>

                        <!-- Filter Controls -->
                        <div class="permohonan-filter-bar">
                            <div class="permohonan-filter-group">
                                <label for="permohonan-filter-proyek">Proyek</label>
                                <select id="permohonan-filter-proyek" class="form-control">
                                    <option value="">Semua Proyek</option>
                                    <?php foreach ($projects as $p): ?>
                                        <option value="<?= (int) $p['id_proyek'] ?>" <?= ((int) ($initialProyek ?? 0) === (int) $p['id_proyek']) ? 'selected' : '' ?>>
                                            <?= esc($p['nama_proyek'] ?? ('Proyek #' . $p['id_proyek'])) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="permohonan-filter-group">
                                <label for="permohonan-filter-status">Status</label>
                                <select id="permohonan-filter-status" class="form-control">
                                    <?php if (! empty($statuses)): ?>
                                        <?php foreach ($statuses as $st): ?>
                                            <option value="<?= esc($st['key']) ?>" <?= $st['key'] === 'all' ? 'selected' : '' ?>>
                                                <?= esc($st['label']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="all" selected>Semua Status</option>
                                        <option value="active">Aktif / Perlu Proses</option>
                                        <option value="pending">Menunggu</option>
                                        <option value="in_progress">Sedang Diproses</option>
                                        <option value="completed">Selesai / Disetujui</option>
                                        <option value="rejected">Ditolak / Dibatalkan</option>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="permohonan-filter-group permohonan-filter-group--search">
                                <label for="permohonan-filter-search">Cari Nomor Dokumen, Judul, Konsumen, atau Pemohon</label>
                                <div class="permohonan-search-wrap">
                                    <i class="fas fa-search permohonan-search-icon"></i>
                                    <input type="text" id="permohonan-filter-search" class="form-control" placeholder="Ketik kata kunci lalu tekan Enter...">
                                    <button type="button" id="permohonan-search-clear" class="permohonan-search-clear d-none" aria-label="Hapus pencarian">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div id="permohonan-active-filters" class="permohonan-active-filters d-none" aria-live="polite"></div>
                    </div>

                    <!-- Loading / Error / Empty States -->
                    <div id="permohonan-loading" class="permohonan-state-box d-none">
                        <div class="spinner-border text-primary mb-1" role="status"><span class="sr-only">Memuat...</span></div>
                        <div class="font-weight-bold text-dark">Memuat daftar permohonan...</div>
                    </div>

                    <div id="permohonan-error" class="permohonan-state-box d-none">
                        <div class="permohonan-state-icon bg-light-danger text-danger"><i class="fas fa-exclamation-circle"></i></div>
                        <h5 class="font-weight-bold mb-50">Gagal Memuat Data</h5>
                        <p id="permohonan-error-message" class="mb-1">Terjadi kesalahan saat menghubungi server.</p>
                        <button type="button" class="btn btn-sm btn-primary" id="permohonan-btn-retry">Coba Lagi</button>
                    </div>

                    <div id="permohonan-empty" class="permohonan-state-box d-none">
                        <div class="permohonan-state-icon"><i class="fas fa-check-circle"></i></div>
                        <h5 class="font-weight-bold mb-50">Tidak Ada Permohonan</h5>
                        <p class="mb-0">Belum ada permohonan yang cocok dengan filter atau cakupan aktif saat ini.</p>
                    </div>

                    <!-- Desktop Table -->
                    <div id="permohonan-table-wrap" class="table-responsive d-none d-md-block">
                        <table id="permohonan-table" class="table table-hover mb-0 permohonan-table w-100">
                            <thead>
                                <tr>
                                    <th>No. Dokumen</th>
                                    <th>Modul</th>
                                    <th>Judul / Keterangan</th>
                                    <th>Proyek &amp; Kavling</th>
                                    <th>Pemohon</th>
                                    <th class="text-right">Nominal</th>
                                    <th>Status</th>
                                    <th>Tanggal</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="permohonan-table-body"></tbody>
                        </table>
                    </div>

                    <!-- Mobile Cards -->
                    <div id="permohonan-mobile-list" class="permohonan-mobile-list d-md-none" aria-live="polite"></div>

                    <!-- Pagination Bar -->
                    <div id="permohonan-pagination-bar" class="permohonan-pagination-bar d-none">
                        <div id="permohonan-pagination-info" class="small text-muted font-weight-bold"></div>
                        <div id="permohonan-pagination-controls" class="btn-group btn-group-sm" role="group" aria-label="Navigasi halaman"></div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>

<!-- ======================================================================
     MODAL 1: BPB DETAIL & ACTION
     ====================================================================== -->
<div class="modal fade" id="permohonan-bpb-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <span class="permohonan-modal-title-icon"><i class="fas fa-box-open"></i></span>
                    <span>
                        <span id="permohonan-bpb-title">Detail BPB</span>
                        <small class="d-block text-muted font-weight-normal" id="permohonan-bpb-subtitle">Bon Permintaan Barang</small>
                    </span>
                </h5>
                <div class="permohonan-modal-header-right">
                    <span class="permohonan-status-badge permohonan-status--secondary" id="permohonan-bpb-status">Draft</span>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">&times;</button>
                </div>
            </div>
            <div class="modal-body" id="permohonan-bpb-body"></div>
            <div class="modal-footer flex-wrap" id="permohonan-bpb-actions"></div>
        </div>
    </div>
</div>

<div class="modal fade" id="permohonan-bpb-action-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <form id="permohonan-bpb-action-form" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="permohonan-bpb-action-title">Aksi BPB</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">&times;</button>
            </div>
            <div class="modal-body">
                <div id="permohonan-bpb-action-fields"></div>
                <div id="permohonan-bpb-action-signature" class="d-none mt-1">
                    <div class="divider divider-left"><div class="divider-text">Tanda Tangan Elektronik</div></div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="permohonan-bpb-sign-method">Metode Tanda Tangan</label>
                            <select id="permohonan-bpb-sign-method" class="form-control">
                                <option value="canvas">Gambar Sekarang di Kanvas</option>
                                <option value="profile">Gunakan TTD Profil</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="permohonan-bpb-password">Password Akun Saat Ini <span class="text-danger">*</span></label>
                            <input type="password" id="permohonan-bpb-password" class="form-control" autocomplete="current-password" placeholder="Masukkan password login">
                        </div>
                    </div>
                    <div id="permohonan-bpb-canvas-wrap" class="permohonan-canvas-wrap">
                        <canvas id="permohonan-bpb-canvas"></canvas>
                        <button type="button" class="btn btn-sm btn-outline-secondary permohonan-canvas-clear" data-clear-canvas="bpb">Hapus</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary" id="permohonan-bpb-action-submit">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- ======================================================================
     MODAL 2: FEE SALES DETAIL & ACTION MODALS
     ====================================================================== -->
<div class="modal fade" id="permohonan-fee-sales-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <span class="permohonan-modal-title-icon"><i class="fas fa-hand-holding-usd"></i></span>
                    <span>
                        <span id="permohonan-fs-title">Detail Pengajuan Fee Sales</span>
                        <small class="d-block text-muted font-weight-normal" id="permohonan-fs-subtitle">Pengajuan Pencairan Komisi</small>
                    </span>
                </h5>
                <div class="permohonan-modal-header-right">
                    <span class="permohonan-status-badge permohonan-status--warning" id="permohonan-fs-status">Diajukan</span>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">&times;</button>
                </div>
            </div>
            <div class="modal-body" id="permohonan-fs-body"></div>
            <div class="modal-footer flex-wrap" id="permohonan-fs-actions"></div>
        </div>
    </div>
</div>

<div class="modal fade" id="permohonan-fs-sign-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form id="permohonan-fs-sign-form" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="permohonan-fs-sign-title">Tanda Tangan Pengajuan Fee Sales</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_pengajuan" id="permohonan-fs-sign-id">
                <input type="hidden" name="role" id="permohonan-fs-sign-role">
                <div class="form-group">
                    <label for="permohonan-fs-sign-method">Metode Tanda Tangan</label>
                    <select name="signature_method" id="permohonan-fs-sign-method" class="form-control">
                        <option value="canvas">Gambar Sekarang di Kanvas</option>
                        <option value="profile">Gunakan TTD Profil Akun</option>
                    </select>
                </div>
                <div id="permohonan-fs-canvas-wrap" class="form-group">
                    <label>Area Tanda Tangan <span class="text-danger">*</span></label>
                    <div class="permohonan-canvas-wrap">
                        <canvas id="permohonan-fs-canvas"></canvas>
                        <button type="button" class="btn btn-sm btn-outline-secondary permohonan-canvas-clear" data-clear-canvas="fs">Hapus</button>
                    </div>
                </div>
                <div class="form-group mb-0">
                    <label for="permohonan-fs-sign-password">Password Akun Saat Ini <span class="text-danger">*</span></label>
                    <input type="password" name="password" id="permohonan-fs-sign-password" class="form-control" required autocomplete="current-password">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary" id="permohonan-fs-sign-submit">Simpan Tanda Tangan</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="permohonan-fs-reject-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
        <form id="permohonan-fs-reject-form" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger" id="permohonan-fs-reject-title">Tolak Pengajuan &amp; Saran Revisi Harga</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_pengajuan" id="permohonan-fs-reject-id">
                <input type="hidden" name="role" id="permohonan-fs-reject-role">
                <div class="alert alert-warning mb-1">
                    Penolakan akan mereset tanda tangan dan mengirim saran revisi Harga Jual Net AJB ke pembuat pengajuan.
                </div>
                <div id="permohonan-fs-reject-units" class="mb-1"></div>
                <div class="form-group mb-0">
                    <label for="permohonan-fs-reject-alasan">Alasan Penolakan <span class="text-danger">*</span></label>
                    <textarea name="alasan" id="permohonan-fs-reject-alasan" class="form-control" rows="3" required placeholder="Tuliskan alasan penolakan..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-danger" id="permohonan-fs-reject-submit">Kirim Penolakan</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="permohonan-fs-cair-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form id="permohonan-fs-cair-form" class="modal-content" enctype="multipart/form-data">
            <div class="modal-header">
                <h5 class="modal-title text-success">Konfirmasi Cair Massal Fee Sales</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_pengajuan" id="permohonan-fs-cair-id">
                <div class="form-group">
                    <label for="permohonan-fs-cair-tanggal">Tanggal Realisasi Cair <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal_cair" id="permohonan-fs-cair-tanggal" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="permohonan-fs-cair-no-cek">Nomor Cek / Bilyet Giro</label>
                    <input type="text" name="no_cek" id="permohonan-fs-cair-no-cek" class="form-control" placeholder="Contoh: CK-889212">
                </div>
                <div class="form-group">
                    <label>Bukti Transfer / Pencairan (Opsional)</label>
                    <input type="file" name="lampiran_bukti_cair" id="permohonan-fs-cair-file" class="d-none" accept=".pdf,image/*">
                    <div class="bpb-dropzone" id="permohonan-fs-cair-dropzone" tabindex="0">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <strong>Pilih atau tarik file bukti transfer</strong>
                        <small id="permohonan-fs-cair-filename">PDF, JPG, PNG</small>
                    </div>
                </div>
                <div class="form-group mb-0">
                    <label for="permohonan-fs-cair-keterangan">Keterangan</label>
                    <textarea name="keterangan" id="permohonan-fs-cair-keterangan" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-success" id="permohonan-fs-cair-submit">Konfirmasi Cair Semua</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="permohonan-fs-void-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form id="permohonan-fs-void-form" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">Batalkan (Void) Pengajuan Fee Sales</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_pengajuan" id="permohonan-fs-void-id">
                <div class="alert alert-warning mb-1">
                    Seluruh unit dan termin akan dikembalikan ke status Belum Diajukan.
                </div>
                <div class="form-group mb-0">
                    <label for="permohonan-fs-void-alasan">Alasan Void <span class="text-danger">*</span></label>
                    <textarea name="alasan" id="permohonan-fs-void-alasan" class="form-control" rows="3" required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Tutup</button>
                <button type="submit" class="btn btn-danger" id="permohonan-fs-void-submit">Konfirmasi Void</button>
            </div>
        </form>
    </div>
</div>

<!-- ======================================================================
     MODAL 3: REQUEST KAVLING SITEPLAN
     ====================================================================== -->
<div class="modal fade" id="permohonan-kavling-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <span class="permohonan-modal-title-icon"><i class="fas fa-map-marked-alt"></i></span>
                    <span>
                        <span id="permohonan-kr-title">Detail Request Kavling</span>
                        <small class="d-block text-muted font-weight-normal" id="permohonan-kr-subtitle">Siteplan &amp; Perubahan Tipe</small>
                    </span>
                </h5>
                <div class="permohonan-modal-header-right">
                    <span class="permohonan-status-badge permohonan-status--warning" id="permohonan-kr-status">Pending</span>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">&times;</button>
                </div>
            </div>
            <div class="modal-body" id="permohonan-kr-body"></div>
            <div class="modal-footer flex-wrap" id="permohonan-kr-actions"></div>
        </div>
    </div>
</div>

<!-- ======================================================================
     MODAL 4: TTD SURAT TAGIHAN DIREKSI
     ====================================================================== -->
<div class="modal fade" id="permohonan-tagihan-direksi-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <span class="permohonan-modal-title-icon"><i class="fas fa-file-signature"></i></span>
                    <span>
                        <span id="permohonan-td-title">Persetujuan Surat Tagihan Direksi</span>
                        <small class="d-block text-muted font-weight-normal" id="permohonan-td-subtitle">Verifikasi &amp; Tanda Tangan Direksi</small>
                    </span>
                </h5>
                <div class="permohonan-modal-header-right">
                    <span class="permohonan-status-badge permohonan-status--warning" id="permohonan-td-status">Menunggu TTD</span>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">&times;</button>
                </div>
            </div>
            <div class="modal-body" id="permohonan-td-body"></div>
            <div class="modal-footer flex-wrap" id="permohonan-td-actions"></div>
        </div>
    </div>
</div>

<!-- ======================================================================
     MODAL 6: MGM REFERRAL
     ====================================================================== -->
<div class="modal fade" id="permohonan-mgm-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <span class="permohonan-modal-title-icon"><i class="fas fa-users"></i></span>
                    <span>
                        <span id="permohonan-mgm-title">Detail Bonus MGM Referral</span>
                        <small class="d-block text-muted font-weight-normal" id="permohonan-mgm-subtitle">Member Get Member</small>
                    </span>
                </h5>
                <div class="permohonan-modal-header-right">
                    <span class="permohonan-status-badge permohonan-status--warning" id="permohonan-mgm-status">Eligible</span>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">&times;</button>
                </div>
            </div>
            <div class="modal-body" id="permohonan-mgm-body"></div>
            <div class="modal-footer flex-wrap" id="permohonan-mgm-actions">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Image Lightbox -->
<div id="permohonan-image-lightbox" class="d-none" role="dialog" aria-modal="true" aria-label="Pratinjau Lampiran">
    <button type="button" class="permohonan-lightbox-close" id="permohonan-lightbox-close" aria-label="Tutup pratinjau">
        <i class="fas fa-times"></i>
    </button>
    <img id="permohonan-lightbox-img" src="" alt="Pratinjau lampiran">
</div>

<!-- Global Variables Required by Reused Partials (Cashout Subkon & Tiket Masalah) -->
<script>
    const state = {
        status: { tab: { isClosed: false } },
        id_cashout_subkon: null
    };
    const roleid = <?= $roleIdInt ?>;
    const rolename = <?= json_encode($roleNameStr) ?>;
    const not_found = 'images/not_found.png';
    let dt_proyek = {
        id_proyek: <?= json_encode($initialProyek ?? '') ?>,
        nama_proyek: ''
    };
    window.editdtt = [];
    window.current_user_id = <?= $userIdInt ?>;
    window.PERMOHONAN_CONFIG = <?= json_encode([
        'baseUrl'  => rtrim(base_url(), '/'),
        'csrfName' => csrf_token(),
        'csrfHash' => csrf_hash(),
        'userId'   => $userIdInt,
        'roleId'   => $roleIdInt,
        'roleName' => $roleNameStr,
        'meta'     => $meta,
        'summary'  => $summary,
        'deepLink' => $deepLink,
    ]) ?>;
</script>

<!-- Vendor JS -->
<script src="<?= base_url('app-assets/vendors/js/vendors.min.js') ?>"></script>
<script src="<?= base_url('app-assets/vendors/js/extensions/sweetalert2.all.min.js') ?>"></script>
<script src="<?= base_url('app-assets/vendors/js/forms/select/select2.full.min.js') ?>"></script>
<script src="<?= base_url('app-assets/vendors/js/pickers/flatpickr/flatpickr.min.js') ?>"></script>
<script src="<?= base_url('assets/js/jquery.richtext.min.js') ?>"></script>
<script src="<?= base_url('assets/js/vendor/browser-image-compression.js') ?>"></script>

<!-- Reused Modal 5 (Cashout Subkon) & Modal 7 (Tiket Masalah) -->
<?= view('siteplan/cashout_subkon') ?>
<?= view('siteplan/partials/modal_tiket_masalah_styles') ?>
<?= view('siteplan/partials/modal_tiket_masalah') ?>
<script src="<?= base_url('assets/js/siteplan/tiket-masalah.js') ?>?v=<?= filemtime(FCPATH . 'assets/js/siteplan/tiket-masalah.js') ?>"></script>

<!-- Module JS -->
<script src="<?= base_url('assets/js/permohonan.js') ?>?v=<?= filemtime(FCPATH . 'assets/js/permohonan.js') ?>"></script>
