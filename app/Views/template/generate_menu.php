<!-- BEGIN: Header-->
<nav class="header-navbar navbar navbar-expand-lg align-items-center floating-nav navbar-light ">
    <div class="navbar-container d-flex content">
        <div class="bookmark-wrapper d-flex align-items-center">
            <ul class="nav navbar-nav d-xl-none">
                <li class="nav-item"><a class="nav-link menu-toggle" href="javascript:void(0);"><i class="ficon" data-feather="menu"></i></a></li>
            </ul>
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
                <ul class="dropdown-menu dropdown-menu-media dropdown-menu-right" id='list-notif'>
                    <li class="dropdown-menu-header">
                        <div class="dropdown-header d-flex">
                            <h4 class="notification-title mb-0 mr-auto">Aktivitas</h4>
                            <!-- <div class="badge badge-pill badge-light-primary">6 New</div> -->
                        </div>
                    </li>
                    <li class="scrollable-container media-list" id="notif-here">
                        <?= $notif ?>    
                    </li>
                    <li class="dropdown-menu-footer"><a class="btn btn-primary btn-block" href="javascript:void(0)" id="load-more-notif">Perbaharui Aktivitas</a></li>
                </ul>
            </li>
            <li class="nav-item dropdown dropdown-user">
                <a class="nav-link dropdown-toggle dropdown-user-link" id="dropdown-user" href="javascript:void(0);" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <div class="user-nav d-sm-flex d-none"><span class="user-name font-weight-bolder"><?= user()->username ?></span><span class="user-status"></span></div><span class="avatar"><img class="round" src="<?= base_url() ?>/app-assets/images/portrait/small/avatar-s-11.jpg" alt="avatar" height="40" width="40"><span class="avatar-status-online"></span></span>
                </a>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdown-user">
                    <!-- <a class="dropdown-item" href="javascript:void(0);"><i class="mr-50" data-feather="user"></i> Profile</a><a class="dropdown-item" href="javascript:void(0);"><i class="mr-50" data-feather="mail"></i> Inbox</a><a class="dropdown-item" href="javascript:void(0);"><i class="mr-50" data-feather="check-square"></i> Task</a><a class="dropdown-item" href="javascript:void(0);"><i class="mr-50" data-feather="message-square"></i> Chats</a> -->
                    <!-- <div class="dropdown-divider"></div><a class="dropdown-item" href="javascript:void(0);"><i class="mr-50" data-feather="settings"></i> Settings</a><a class="dropdown-item" href="javascript:void(0);"><i class="mr-50" data-feather="credit-card"></i> Pricing</a><a class="dropdown-item" href="javascript:void(0);"><i class="mr-50" data-feather="help-circle"></i> FAQ</a> -->
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
                        <img src="<?= base_url("uploads/logo/logo.png") ?>" height="28" />
                    </span>
                    <h2 class="brand-text">SIGAPP</h2>
                </a></li>
            <!-- <li class="nav-item nav-toggle"><a class="nav-link modern-nav-toggle pr-0" data-toggle="collapse"><i class="d-block d-xl-none text-primary toggle-icon font-medium-4" data-feather="x"></i><i class="d-none d-xl-block collapse-toggle-icon font-medium-4  text-primary" data-feather="disc" data-ticon="disc"></i></a></li> -->
        </ul>
    </div>
    <div class="shadow-bottom"></div>
    <div class="main-menu-content">
        <?= $menu ?>
    </div>
</div>
<!-- END: Main Menu-->