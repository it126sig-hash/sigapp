<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#2057a3">
    <meta name="description" content="<?= esc($metaDescription) ?>">
    <title><?= esc($pageTitle) ?> - SIGAPP</title>

    <link rel="manifest" href="<?= base_url('manifest.webmanifest') ?>">
    <link rel="apple-touch-icon" href="<?= base_url('assets/images/pwa/apple-touch-icon.png') ?>">
    <link rel="shortcut icon" type="image/x-icon" href="<?= base_url('app-assets/images/ico/favicon.ico') ?>">
    <link rel="stylesheet" href="<?= base_url('app-assets/vendors/css/vendors.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('app-assets/css/bootstrap.css') ?>">
    <link rel="stylesheet" href="<?= base_url('app-assets/css/bootstrap-extended.css') ?>">
    <link rel="stylesheet" href="<?= base_url('app-assets/css/colors.css') ?>">
    <link rel="stylesheet" href="<?= base_url('app-assets/css/components.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/legal-pages.css') ?>">
</head>
<body class="legal-page">
    <a class="legal-skip-link" href="#legal-content">Lewati ke isi utama</a>

    <header class="legal-header">
        <div class="legal-container legal-header-inner">
            <a class="legal-brand" href="<?= base_url('login') ?>" aria-label="SIGAPP - Halaman masuk">
                <img src="<?= base_url('images/logo.png') ?>" alt="" width="42" height="42">
                <span>SIGAPP</span>
            </a>

            <nav class="legal-nav" aria-label="Navigasi halaman legal">
                <a href="<?= base_url('privacy-policy') ?>" <?= $activePage === 'privacy' ? 'aria-current="page"' : '' ?>>Privasi</a>
                <a href="<?= base_url('toc') ?>" <?= $activePage === 'terms' ? 'aria-current="page"' : '' ?>>Ketentuan</a>
                <a class="legal-login-link" href="<?= base_url('login') ?>">Masuk</a>
            </nav>
        </div>
    </header>

    <?= $this->renderSection('content') ?>

    <footer class="legal-footer">
        <div class="legal-container legal-footer-inner">
            <p>&copy; <?= date('Y') ?> SIGAPP. Seluruh hak dilindungi.</p>
            <div class="legal-footer-links">
                <a href="<?= base_url('privacy-policy') ?>">Kebijakan Privasi</a>
                <a href="<?= base_url('toc') ?>">Ketentuan Layanan</a>
            </div>
        </div>
    </footer>
</body>
</html>
