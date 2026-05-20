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
    
    <!-- Google Fonts: Inter & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:ital,wght@0,400;0,500;0,700;1,400&display=swap" rel="stylesheet">

    <!-- Bootstrap 4.6.2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">

    <!-- jQuery 3.6.0 (Loaded early for inline scripts in views) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap 4.6.2 JS Bundle (includes Popper.js) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- FontAwesome 6 & Feather Icons CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS (Monochrome Tech Theme Override) -->
    <link rel="stylesheet" type="text/css" href="<?= base_url() ?>/assets/css/style.css">
    <!-- END: Custom CSS-->
    <style>
        #loading {
            position: fixed;
            top: 0;
            left: 0;
            z-index: 99999;
            width: 100vw;
            height: 3px;
            background-color: rgb(255, 255, 255, 0.75);
            pointer-events: none; /* agar tidak menghalangi interaksi saat loading */
            transition: opacity 0.25s ease, visibility 0.25s ease;
        }

        #loading.hidden {
            opacity: 0;
            visibility: hidden;
        }

        #loading::after {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            width: 50%;
            background-color: #000000; /* Monochrome Tech black */
            animation: loading-bar 1.5s infinite linear;
            transform-origin: left;
        }

        @keyframes loading-bar {
            0% {
                left: -50%;
                width: 30%;
            }
            50% {
                width: 70%;
            }
            100% {
                left: 100%;
                width: 30%;
            }
        }

        .main-menu .navbar-header .navbar-brand .brand-text {
            color: #eee;
        }
    </style>

</head>
<!-- END: Head-->