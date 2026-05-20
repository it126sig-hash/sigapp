<!-- Header Navbar -->
<header class="navbar-wrapper">
    <nav class="navbar navbar-expand align-items-center justify-content-between px-4 py-2">
        <!-- Left Side: Toggles -->
        <div class="d-flex align-items-center">
            <button id="mobile-toggle" class="btn btn-layout-toggle d-lg-none" aria-label="Toggle Mobile Menu">
                <i class="fa-solid fa-bars"></i>
            </button>
            <button id="sidebar-toggle" class="btn btn-layout-toggle d-none d-lg-inline-block" aria-label="Toggle Sidebar">
                <i class="fa-solid fa-bars-staggered"></i>
            </button>
        </div>

        <!-- Right Side: User & Notifications -->
        <div class="d-flex align-items-center">
            
            <!-- Notifications Dropdown -->
            <div class="dropdown mr-3">
                <a id="header-notif" class="nav-link-notif position-relative" href="javascript:void(0);" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fa-regular fa-bell"></i>
                    <span class="badge-notif-dot" id="notif-badge" style="display:none;"></span>
                </a>
                <div class="dropdown-menu dropdown-menu-right dropdown-menu-notif" aria-labelledby="header-notif">
                    <div class="dropdown-header d-flex justify-content-between align-items-center border-bottom pb-2">
                        <span class="font-weight-bold text-dark">Aktivitas</span>
                    </div>
                    <div class="notif-list-scrollable" id="notif-here">
                        <?= $notif ?>    
                    </div>
                    <div class="dropdown-footer border-top p-2">
                        <button class="btn btn-outline-dark btn-sm btn-block text-uppercase font-size-10 font-weight-bold" id="load-more-notif">Perbaharui Aktivitas</button>
                    </div>
                </div>
            </div>

            <!-- User Menu -->
            <div class="dropdown">
                <a class="d-flex align-items-center text-decoration-none dropdown-toggle user-menu-trigger" id="dropdown-user" href="javascript:void(0);" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <div class="user-info mr-2 text-right d-none d-sm-block">
                        <span class="user-name font-weight-semibold text-dark d-block"><?= user()->username ?></span>
                    </div>
                    <div class="avatar-wrapper">
                        <img class="avatar-img" src="<?= base_url() ?>/app-assets/images/portrait/small/avatar-s-11.jpg" alt="user avatar">
                        <span class="avatar-status-online-dot"></span>
                    </div>
                </a>
                <div class="dropdown-menu dropdown-menu-right dropdown-menu-user" aria-labelledby="dropdown-user">
                    <a class="dropdown-item d-flex align-items-center text-danger" href="<?= base_url('logout') ?>">
                        <i class="fa-solid fa-arrow-right-from-bracket mr-2"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>
</header>

<!-- Sidebar Navigation -->
<aside class="sidebar-wrapper">
    <div class="sidebar-header d-flex align-items-center justify-content-between">
        <a class="sidebar-brand d-flex align-items-center text-decoration-none" href="<?= base_url() ?>">
            <img class="brand-logo" src="<?= base_url("uploads/logo/logo.png") ?>" alt="SIGAPP logo" />
            <span class="brand-text ml-2">SIGAPP</span>
        </a>
        <button id="sidebar-close" class="btn btn-layout-toggle d-lg-none" aria-label="Close Sidebar">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
    
    <div class="sidebar-menu-wrapper">
        <?= $menu ?>
    </div>
</aside>