<!DOCTYPE html>
<html class="loading" lang="en" data-textdirection="ltr">
<!-- BEGIN: Head-->

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1.0,user-scalable=0,minimal-ui">
    <meta name="description" content="SIGAPP adalah SIGGAPP.">
    <meta name="author" content="kamukapan_">
    <title>SIGAPP</title>
    <link rel="apple-touch-icon" href="<?= base_url() ?>/app-assets/images/ico/apple-icon-120.png">
    <link rel="shortcut icon" type="image/x-icon" href="<?= base_url() ?>/app-assets/images/ico/favicon.ico">
    <!-- <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,300;0,400;0,500;0,600;1,400;1,500;1,600" rel="stylesheet"> -->

    <!-- BEGIN: Vendor CSS-->
    <link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/vendors.min.css">
    <!-- END: Vendor CSS-->

    <!-- BEGIN: Theme CSS-->
    <link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/css/bootstrap-extended.css">
    <link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/css/colors.css">
    <link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/css/components.css">
    <link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/css/themes/dark-layout.css">
    <link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/css/themes/bordered-layout.css">
    <link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/css/themes/semi-dark-layout.css">
    <link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/fontawesome/all.min.css">

    <!-- BEGIN: Page CSS-->
    <link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/css/core/menu/menu-types/vertical-menu.css">
    <!-- END: Page CSS-->

    <!-- BEGIN: Custom CSS-->
    <link rel="stylesheet" type="text/css" href="<?= base_url() ?>/assets/css/style.css">
    <!-- END: Custom CSS-->
    <style>
        #loading {
            position: fixed;
            top: 0;
            left: 0;
            z-index: 99999 !important;
            width: 100vw;
            height: 100vh;
            background-color: rgba(34, 41, 47, 0.1);
            backdrop-filter: blur(1.5px);
            -webkit-backdrop-filter: blur(1.5px);
            transition: opacity 0.3s ease, visibility 0.3s ease;
            opacity: 1;
            visibility: visible;
        }

        #loading.hidden {
            display: block !important;
            opacity: 0 !important;
            visibility: hidden !important;
            pointer-events: none !important;
        }

        #loading::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            height: 3px;
            width: 100%;
            background: linear-gradient(90deg, #5B4FCF 0%, #7B6FE0 30%, #F59E0B 70%, #5B4FCF 100%);
            background-size: 200% 100%;
            animation: loading-bar-progress 1.5s infinite linear;
            box-shadow: 0 1px 10px rgba(91, 79, 207, 0.4);
        }

        @keyframes loading-bar-progress {
            0% {
                background-position: 200% 0;
            }
            100% {
                background-position: -200% 0;
            }
        }

        .main-menu .navbar-header .navbar-brand .brand-text {
            color: #eee;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        /* ==========================================
           FLOATING SIDEBAR & NAVBAR REDESIGN
           ========================================== */
        
        .header-navbar-shadow {
            display: none !important;
        }

        /* Glassmorphism & Floating Navbar */
        .header-navbar.floating-nav {
            top: 15px !important;
            left: 15px !important;
            right: 15px !important;
            width: auto !important;
            margin: 0 !important;
            background: rgba(255, 255, 255, 0.8) !important;
            backdrop-filter: blur(12px) !important;
            -webkit-backdrop-filter: blur(12px) !important;
            border: 1px solid rgba(255, 255, 255, 0.4) !important;
            border-radius: 16px !important;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.06) !important;
            z-index: 999 !important;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1) !important;
        }

        .dark-layout .header-navbar.floating-nav {
            background: rgba(40, 48, 70, 0.75) !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.2) !important;
        }

        /* Floating Sidebar (Desktop) */
        @media (min-width: 1201px) {
            .main-menu {
                top: 15px !important;
                left: 15px !important;
                height: calc(100vh - 30px) !important;
                border-radius: 16px !important;
                box-shadow: 0 10px 30px 0 rgba(0, 0, 0, 0.12) !important;
                border: 1px solid rgba(255, 255, 255, 0.08) !important;
                transition: width 0.3s cubic-bezier(0.25, 0.8, 0.25, 1), transform 0.3s cubic-bezier(0.25, 0.8, 0.25, 1) !important;
                margin: 0 !important;
            }

            .dark-layout .main-menu {
                background-color: rgba(40, 48, 70, 0.85) !important;
                border: 1px solid rgba(255, 255, 255, 0.05) !important;
                backdrop-filter: blur(10px) !important;
                -webkit-backdrop-filter: blur(10px) !important;
            }

            .main-menu .main-menu-content {
                height: calc(100% - 70px) !important;
            }

            .app-content {
                margin-left: 290px !important;
                margin-right: 15px !important;
                transition: margin-left 0.3s cubic-bezier(0.25, 0.8, 0.25, 1) !important;
                padding-top: 100px !important;
            }

            /* Collapsed State overrides */
            body.menu-collapsed .app-content {
                margin-left: 110px !important;
                margin-right: 15px !important;
            }

            body.menu-collapsed .main-menu {
                width: 80px !important;
            }

            body.menu-collapsed .main-menu:hover,
            body.menu-collapsed .main-menu.expanded {
                width: 260px !important;
            }

            body.menu-collapsed .header-navbar.floating-nav {
                left: 110px !important;
            }

            .header-navbar.floating-nav {
                left: 290px !important;
            }

            .footer {
                margin-left: 290px !important;
                transition: margin-left 0.3s cubic-bezier(0.25, 0.8, 0.25, 1) !important;
            }

            body.menu-collapsed .footer {
                margin-left: 110px !important;
            }

            body.menu-collapsed .main-menu:not(.expanded):not(:hover) .brand-text {
                display: none !important;
            }

            body.menu-collapsed .main-menu.expanded .brand-text,
            body.menu-collapsed .main-menu:hover .brand-text {
                display: inline-block !important;
            }
        }

        /* Floating Sidebar (Mobile / Tablet) */
        @media (max-width: 1200px) {
            .main-menu {
                position: fixed !important;
                top: 10px !important;
                left: 10px !important;
                bottom: 10px !important;
                height: calc(100vh - 20px) !important;
                width: 260px !important;
                border-radius: 16px !important;
                box-shadow: 0 10px 40px rgba(0, 0, 0, 0.25) !important;
                border: 1px solid rgba(255, 255, 255, 0.08) !important;
                transform: translate3d(-280px, 0, 0) !important;
                transition: transform 0.25s cubic-bezier(0.25, 0.8, 0.25, 1) !important;
                z-index: 1045 !important;
            }

            body.menu-open .main-menu {
                transform: translate3d(0, 0, 0) !important;
            }

            .header-navbar.floating-nav {
                top: 10px !important;
                left: 10px !important;
                right: 10px !important;
                width: auto !important;
                border-radius: 12px !important;
            }

            .app-content {
                margin-left: 0 !important;
                padding-top: 80px !important;
                padding-left: 10px !important;
                padding-right: 10px !important;
            }

            .footer {
                margin-left: 0 !important;
                padding: 1rem 15px !important;
            }
        }
    </style>

</head>
<!-- END: Head-->