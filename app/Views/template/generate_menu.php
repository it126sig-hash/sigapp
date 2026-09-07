<?php
$currentUser = user();
$profileName = $currentUser->name ?? '';
$profileName = $profileName !== '' ? $profileName : ($currentUser->username ?? '');
$profileUsername = $currentUser->username ?? '';
$profileDisplayName = ($profileUsername !== '' && $profileName !== $profileUsername)
    ? $profileName . ' (' . $profileUsername . ')'
    : $profileName;
$profileRoles = $currentUser ? array_values($currentUser->getRoles()) : [];
$profileDivisi = ! empty($profileRoles) ? implode(', ', $profileRoles) : '-';
$profilePhotoUrl = base_url('app-assets/images/portrait/small/avatar-s-11.jpg');

if (! empty($currentUser->profile_photo)) {
    try {
        $profilePhotoUrl = (new \App\Services\FileAccessService())->pathThumbnailUrl('profile_photo', $currentUser->profile_photo);
    } catch (\Throwable $e) {
        $profilePhotoUrl = base_url('app-assets/images/portrait/small/avatar-s-11.jpg');
    }
}

$activeProyek = $activeProyek ?? null;
$accessibleProyek = $accessibleProyek ?? [];
$defaultProjectLogo = base_url('assets/images/pwa/icon-192.png');
$activeProyekId = $activeProyek ? (int) $activeProyek->id_proyek : null;
$activeProyekName = $activeProyek->nama_proyek ?? 'Pilih proyek';
$activeProyekLogoUrl = ($activeProyek && ! empty($activeProyek->logo_thumbnail_url ?? $activeProyek->logo_access_url))
    ? ($activeProyek->logo_thumbnail_url ?? $activeProyek->logo_access_url)
    : $defaultProjectLogo;
?>
<!-- BEGIN: Header-->
<nav class="header-navbar navbar navbar-expand-lg align-items-center floating-nav navbar-light ">
    <div class="navbar-container d-flex content align-items-center w-100">
        <div class="bookmark-wrapper navbar-project-area d-flex align-items-center flex-grow-1 min-width-0">
            <ul class="nav navbar-nav d-xl-none flex-shrink-0">
                <li class="nav-item"><a class="nav-link menu-toggle" href="javascript:void(0);"><i class="ficon" data-feather="menu"></i></a></li>
            </ul>
            <!-- <a class="navbar-brand d-xl-none d-flex align-items-center ml-1 flex-shrink-0" href="<?= base_url() ?>" style="gap: 8px;">
                <img src="<?= base_url("images/logo.png") ?>" height="24" />
                <span class="brand-text font-weight-bold" style="color: #5B4FCF; font-size: 1.15rem; letter-spacing: 0.5px;">SIGAPP</span>
            </a> -->

            <div class="navbar-project-switcher dropdown ml-50 ml-md-1 min-width-0">
                <?php if (empty($accessibleProyek)) : ?>
                    <div class="navbar-project-pill navbar-project-pill--empty">
                        <span class="navbar-project-logo-wrap">
                            <img src="<?= esc($defaultProjectLogo) ?>" alt="" class="navbar-project-logo">
                        </span>
                        <span class="navbar-project-name text-truncate">Belum ada proyek</span>
                    </div>
                <?php else : ?>
                    <button
                        class="navbar-project-pill dropdown-toggle"
                        type="button"
                        id="navbar-project-switcher"
                        data-toggle="dropdown"
                        aria-haspopup="true"
                        aria-expanded="false">
                        <span class="navbar-project-logo-wrap">
                            <img
                                src="<?= esc($activeProyekLogoUrl) ?>"
                                alt=""
                                class="navbar-project-logo"
                                id="navbar-project-logo">
                        </span>
                        <span class="navbar-project-name" id="navbar-project-name"><?= esc($activeProyekName) ?></span>
                        <i data-feather="chevron-down" class="navbar-project-chevron"></i>
                    </button>
                    <div class="dropdown-menu navbar-project-dropdown" aria-labelledby="navbar-project-switcher">
                        <?php foreach ($accessibleProyek as $proyek) :
                            $isActive = $activeProyekId === (int) $proyek->id_proyek;
                            $logoUrl = ! empty($proyek->logo_thumbnail_url ?? $proyek->logo_access_url) ? ($proyek->logo_thumbnail_url ?? $proyek->logo_access_url) : $defaultProjectLogo;
                        ?>
                            <a
                                class="dropdown-item navbar-project-item<?= $isActive ? ' active' : '' ?>"
                                href="javascript:void(0);"
                                data-id-proyek="<?= (int) $proyek->id_proyek ?>"
                                data-nama-proyek="<?= esc($proyek->nama_proyek) ?>"
                                data-logo-url="<?= esc($logoUrl) ?>">
                                <span class="navbar-project-item-logo-wrap">
                                    <img src="<?= esc($logoUrl) ?>" alt="" class="navbar-project-item-logo">
                                </span>
                                <span class="navbar-project-item-name"><?= esc($proyek->nama_proyek) ?></span>
                                <?php if ($isActive) : ?>
                                    <i data-feather="check" class="navbar-project-item-check"></i>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <ul class="nav navbar-nav">
                <!-- <li class="nav-item d-none d-lg-block"><button onclick="export_siteplan()" class="btn btn-outline-primary " id="btn-export-siteplan"> Export </button></li> -->
            </ul>
        </div>
        <ul class="nav navbar-nav navbar-header-actions align-items-center ml-auto flex-shrink-0">
            <?php
            /*<li class="nav-item dropdown mr-25">
                <a class="" onclick="load_kavling(true)">
                    <i class="ficon" data-feather='refresh-ccw'></i>
                </a>
                &nbsp;
                <select id="pilih-divisi" class="form-control-sm">
                    <option value="0">Pilih Divisi</option>
                    <!-- <option value="8" class="dropdown-item">Sales & Promotion</option> -->
                    <option value="7" class="dropdown-item">Produksi</option>
                    <option value="4" class="dropdown-item">Marketing Data</option>
                    <option value="5" class="dropdown-item">Legal & Pertanahan</option>
                    <!-- <option value="10" class="dropdown-item">Pajak</option> -->
                    <option value="3" class="dropdown-item">Keuangan</option>
                    <option value="6" class="dropdown-item">Planning</option>
                    <!-- <option value="9" class="dropdown-item">Management</option> -->
                </select>
            </li>*/
            ?>

            <li class="nav-item dropdown dropdown-notification mr-25">
                <a id="header-notif" class="nav-link" href="javascript:void(0);" data-toggle="dropdown">
                    <i class="ficon" data-feather="bell"></i>
                    <span class="badge badge-pill badge-danger badge-up" id="notif-badge" style="display:none;">0</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-media dropdown-menu-right notification-center-menu" id="list-notif">
                    <li class="dropdown-menu-header">
                        <div class="dropdown-header notification-center-header d-flex align-items-center">
                            <div class="mr-auto">
                                <h4 class="notification-title mb-0">Notifikasi</h4>
                                <small class="text-muted">Aktivitas dan pengingat terbaru</small>
                            </div>
                            <span class="badge badge-pill badge-light-primary mr-50" id="notif-summary-badge" style="display:none;">0</span>
                            <button type="button" class="btn btn-flat-success btn-icon btn-sm mr-25 notification-mark-read-btn" id="mark-all-read-notif-center" title="Tandai semua dibaca" aria-label="Tandai semua dibaca">
                                <i data-feather="check-square"></i>
                            </button>
                            <button type="button" class="btn btn-flat-primary btn-icon btn-sm notification-refresh-btn" id="refresh-notif-center" title="Perbarui notifikasi" aria-label="Perbarui notifikasi">
                                <i data-feather="refresh-cw"></i>
                            </button>
                        </div>
                    </li>
                    <li class="notification-center-tabs px-1 pt-50 pb-50 border-bottom">
                        <ul class="nav nav-tabs nav-justified mb-0" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="notif-urgent-tab" href="javascript:void(0)" data-notif-target="urgent" role="tab">
                                    Jatuh Tempo <span class="badge badge-pill badge-light-danger ml-25" id="notif-urgent-count">0</span>
                                </a>
                            </li>
                            <li class="nav-item border-left">
                                <a class="nav-link" id="notif-activity-tab" href="javascript:void(0)" data-notif-target="activity" role="tab">
                                    Aktivitas <span class="badge badge-pill badge-light-primary ml-25" id="notif-activity-count">0</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="notification-project-filter px-1 py-50 border-bottom">
                        <div class="d-flex align-items-center mb-50" style="gap: 6px;">
                            <i data-feather="folder" class="text-primary" style="width: 16px; height: 16px;"></i>
                            <span class="text-dark font-weight-bolder" id="notif-active-project-name" style="font-size: 0.9rem;"><?= $activeProyekId ? esc($activeProyekName) : 'Semua Proyek' ?></span>
                            <i data-feather="chevron-down" class="text-muted" style="width: 14px; height: 14px;"></i>
                        </div>
                        <div class="notif-project-scroll" style="display: flex; overflow-x: auto; gap: 8px; padding-bottom: 4px;">
                            <style>
                                .notif-project-scroll { scrollbar-width: thin; scrollbar-color: #d0d2d6 transparent; overscroll-behavior: contain; }
                                .notif-project-scroll::-webkit-scrollbar { height: 4px; }
                                .notif-project-scroll::-webkit-scrollbar-track { background: transparent; }
                                .notif-project-scroll::-webkit-scrollbar-thumb { background: #d0d2d6; border-radius: 10px; }
                                .notif-project-scroll::-webkit-scrollbar-thumb:hover { background: #b4b7bd; }
                            </style>
                            <button type="button" class="btn btn-sm <?= !$activeProyekId ? 'btn-primary active text-white' : 'border bg-white text-secondary' ?> rounded-pill notif-project-btn flex-shrink-0" data-id-proyek="all" data-nama-proyek="Semua Proyek" style="display: flex; align-items: center; gap: 6px; padding: 4px 12px;">
                                <i data-feather="folder" style="width: 14px; height: 14px;"></i>
                                Semua Proyek
                            </button>
                            <?php foreach ($accessibleProyek as $proyek) :
                                $logoUrl = ! empty($proyek->logo_thumbnail_url ?? $proyek->logo_access_url) ? ($proyek->logo_thumbnail_url ?? $proyek->logo_access_url) : $defaultProjectLogo;
                                $shortName = strlen($proyek->nama_proyek) > 7 ? substr($proyek->nama_proyek, 0, 7) . '...' : $proyek->nama_proyek;
                                $isActive = ($activeProyekId === (int) $proyek->id_proyek);
                            ?>
                                <button type="button" class="btn btn-sm <?= $isActive ? 'btn-primary active text-white' : 'border bg-white text-secondary' ?> rounded-pill notif-project-btn flex-shrink-0" data-id-proyek="<?= (int) $proyek->id_proyek ?>" data-nama-proyek="<?= esc($proyek->nama_proyek) ?>" style="display: flex; align-items: center; gap: 6px; padding: 4px 12px;">
                                    <img src="<?= esc($logoUrl) ?>" alt="logo" style="width: 14px; height: 14px; border-radius: 50%; object-fit: cover;">
                                    <?= esc($shortName) ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </li>

                    <li class="scrollable-container media-list notification-center-body" id="notification-center-body" style="overscroll-behavior: contain;">
                        <div class="notification-center-content">
                            <div class="notification-center-pane is-active" id="notif-urgent-pane" role="tabpanel" aria-labelledby="notif-urgent-tab">
                                <div id="notif-urgent-here">
                                    <div class="notification-center-empty">Memuat jatuh tempo...</div>
                                </div>
                            </div>
                            <div class="notification-center-pane" id="notif-activity-pane" role="tabpanel" aria-labelledby="notif-activity-tab">
                                <div id="notif-here">
                                    <div class="notification-center-empty">Memuat aktivitas...</div>
                                </div>
                            </div>
                        </div>
                    </li>
                </ul>
            </li>
            <li class="nav-item dropdown dropdown-user">
                <a class="nav-link dropdown-toggle dropdown-user-link" id="dropdown-user" href="javascript:void(0);" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <div class="user-nav d-sm-flex d-none flex-column align-items-end">
                        <span class="user-name font-weight-bolder"><?= esc($profileDisplayName) ?></span>
                        <span class="user-status text-muted small"><?= esc($profileDivisi) ?></span>
                    </div><span class="avatar"><img class="round" src="<?= esc($profilePhotoUrl) ?>" alt="avatar" height="40" width="40"><span class="avatar-status-online"></span></span>
                </a>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdown-user">
                    <a class="dropdown-item d-none" href="javascript:void(0);" id="sigapp-pwa-install-action"><i class="mr-50" data-feather="download"></i> Install SIGAPP</a>
                    <a class="dropdown-item d-none" href="javascript:void(0);" id="sigapp-push-action"><i class="mr-50" data-feather="bell"></i> <span data-push-label>Aktifkan Notifikasi</span></a>
                    <a class="dropdown-item" href="<?= base_url('profil') ?>"><i class="mr-50" data-feather="user"></i> Ubah Profil</a>
                    <!-- <div class="dropdown-divider"></div><a class="dropdown-item" href="javascript:void(0);"><i class="mr-50" data-feather="settings"></i> Settings</a><a class="dropdown-item" href="javascript:void(0);"><i class="mr-50" data-feather="credit-card"></i> Pricing</a><a class="dropdown-item" href="javascript:void(0);"><i class="mr-50" data-feather="help-circle"></i> FAQ</a> -->
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="<?= base_url('logout') ?>"><i class="mr-50" data-feather="power"></i> Logout</a>
                </div>
            </li>
        </ul>
    </div>
</nav>
<!-- END: Header-->


<!-- BEGIN: Main Menu-->
<div class="main-menu menu-fixed menu-accordion menu-shadow menu-dark" data-scroll-to-active="true">
    <div class="navbar-header">
        <ul class="nav navbar-nav flex-row">
            <li class="nav-item mr-auto"><a class="navbar-brand" href="<?= base_url() ?>"><span class="brand-logo">
                        <img src="<?= base_url("images/logo.png") ?>" height="28" />
                    </span>
                    <h2 class="brand-text">SIGAPP</h2>
                </a></li>
            <li class="nav-item nav-toggle"><a class="nav-link modern-nav-toggle pr-0" data-toggle="collapse"><i class="d-block d-xl-none text-primary toggle-icon font-medium-4" data-feather="x"></i><i class="d-none d-xl-block collapse-toggle-icon font-medium-4  text-primary" data-feather="disc" data-ticon="disc"></i></a></li>
        </ul>
    </div>
    <div class="shadow-bottom"></div>
    <div class="main-menu-content">
        <?= $menu ?>
    </div>
</div>
<!-- END: Main Menu-->
