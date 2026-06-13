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
        $profilePhotoUrl = (new \App\Services\FileAccessService())->pathUrl('profile_photo', $currentUser->profile_photo);
    } catch (\Throwable $e) {
        $profilePhotoUrl = base_url('app-assets/images/portrait/small/avatar-s-11.jpg');
    }
}
?>
<!-- BEGIN: Header-->
<nav class="header-navbar navbar navbar-expand-lg align-items-center floating-nav navbar-light ">
    <div class="navbar-container d-flex content">
        <div class="bookmark-wrapper d-flex align-items-center">
            <ul class="nav navbar-nav d-xl-none">
                <li class="nav-item"><a class="nav-link menu-toggle" href="javascript:void(0);"><i class="ficon" data-feather="menu"></i></a></li>
            </ul>
            <a class="navbar-brand d-xl-none d-flex align-items-center ml-1" href="<?= base_url() ?>" style="gap: 8px;">
                <img src="<?= base_url("images/logo.png") ?>" height="24" />
                <span class="brand-text font-weight-bold" style="color: #5B4FCF; font-size: 1.15rem; letter-spacing: 0.5px;">SIGAPP</span>
            </a>
            <ul class="nav navbar-nav">
                <!-- <li class="nav-item d-none d-lg-block"><button onclick="export_siteplan()" class="btn btn-outline-primary " id="btn-export-siteplan"> Export </button></li> -->
            </ul>
        </div>
        <ul class="nav navbar-nav align-items-center ml-auto">
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
                        <div class="dropdown-header d-flex align-items-center">
                            <h4 class="notification-title mb-0 mr-auto">Notifikasi</h4>
                            <span class="badge badge-pill badge-light-primary" id="notif-summary-badge" style="display:none;">0</span>
                        </div>
                    </li>
                    <li class="notification-center-tabs px-1 pt-50">
                        <ul class="nav nav-tabs nav-justified" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="notif-urgent-tab" href="javascript:void(0)" data-notif-target="urgent" role="tab">
                                    Jatuh Tempo <span class="badge badge-pill badge-light-danger ml-25" id="notif-urgent-count">0</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="notif-activity-tab" href="javascript:void(0)" data-notif-target="activity" role="tab">
                                    Aktivitas <span class="badge badge-pill badge-light-primary ml-25" id="notif-activity-count">0</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="scrollable-container media-list notification-center-body" id="notification-center-body">
                        <div class="notification-center-content">
                            <div class="notification-center-pane is-active" id="notif-urgent-pane" role="tabpanel" aria-labelledby="notif-urgent-tab">
                                <div id="notif-urgent-here">
                                    <div class="notification-center-empty">Memuat jatuh tempo...</div>
                                </div>
                            </div>
                            <div class="notification-center-pane" id="notif-activity-pane" role="tabpanel" aria-labelledby="notif-activity-tab">
                                <div id="notif-here">
                                    <?= $notif ?>
                                </div>
                            </div>
                        </div>
                    </li>
                    <li class="dropdown-menu-footer">
                        <div class="d-flex">
                            <a class="btn btn-primary flex-fill mr-50" href="javascript:void(0)" id="refresh-notif-center">Perbarui</a>
                            <a class="btn btn-outline-primary flex-fill" href="javascript:void(0)" id="load-more-notif">Aktivitas Lagi</a>
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
            <li class="nav-item mr-auto"><a class="navbar-brand" href="<?= base_url() ?>/starter-kit/ltr/vertical-menu-template/"><span class="brand-logo">
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
