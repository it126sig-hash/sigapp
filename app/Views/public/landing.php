<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="theme-color" content="#2057a3">
    <!-- Ganti content di bawah dengan tag verifikasi Google Search Console yang asli -->
    <meta name="google-site-verification" content="KODE_VERIFIKASI_ANDA_DISINI" />
    
    <title><?= isset($pageTitle) ? $pageTitle : 'SIGAPP - Sanggar Indah Group' ?></title>
    
    <link rel="shortcut icon" type="image/x-icon" href="<?= base_url() ?>/app-assets/images/ico/favicon.ico">

    <!-- Bootstrap & Vuexy Core CSS -->
    <link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/vendors.min.css">
    <link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/css/bootstrap.css">
    <link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/css/bootstrap-extended.css">
    <link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/css/colors.css">
    <link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/css/components.css">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        
        .navbar-brand img {
            height: 40px;
            margin-right: 10px;
        }

        .navbar-brand span {
            font-size: 1.5rem;
            font-weight: 800;
            color: #1e5fa3;
            letter-spacing: 1px;
        }

        .hero-section {
            padding: 100px 0;
            background: linear-gradient(135deg, #1e5fa3 0%, #2b84be 100%);
            color: #fff;
            text-align: center;
        }

        .hero-section h1 {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 20px;
            color: #fff;
        }

        .hero-section p.lead {
            font-size: 1.25rem;
            margin-bottom: 40px;
            opacity: 0.9;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }

        .features-section {
            padding: 80px 0;
            background: #fff;
        }
        
        .feature-card {
            padding: 30px;
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            text-align: center;
            height: 100%;
            transition: transform 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-5px);
        }

        .feature-icon {
            font-size: 3rem;
            color: #1e5fa3;
            margin-bottom: 20px;
        }

        .feature-card h3 {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 15px;
            color: #333;
        }

        .feature-card p {
            color: #666;
            font-size: 0.95rem;
        }

        .cta-section {
            padding: 80px 0;
            text-align: center;
            background-color: #f0f4f8;
        }

        footer {
            padding: 30px 0;
            text-align: center;
            background-color: #222;
            color: #888;
        }
        
        .btn-custom {
            padding: 12px 30px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm py-3">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="<?= base_url('landing') ?>">
                <img src="<?= base_url('images/logo.png') ?>" alt="Logo SIGAPP" onerror="this.src='<?= base_url('app-assets/images/logo/logo.png') ?>'">
                <span>SIGAPP</span>
            </a>
            <div class="ml-auto">
                <a href="<?= base_url('login') ?>" class="btn btn-outline-primary font-weight-bold px-4">Login</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <h1>Sistem Internal Sanggar Indah Group</h1>
            <p class="lead">Platform manajemen properti dan kavling terpadu yang dirancang khusus untuk mengoptimalkan operasional dan pengelolaan proyek perumahan di Sanggar Indah Group.</p>
            <a href="<?= base_url('login') ?>" class="btn btn-light text-primary btn-custom mt-2">Masuk ke Dashboard</a>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="font-weight-bold">Solusi Terintegrasi Kami</h2>
                <p class="text-muted">Meningkatkan efisiensi dan transparansi di setiap tahap proyek properti.</p>
            </div>
            <div class="row">
                <!-- Feature 1 -->
                <div class="col-md-4 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i data-feather="grid"></i>
                        </div>
                        <h3>Manajemen Kavling</h3>
                        <p>Pemantauan status ketersediaan kavling, harga, dan spesifikasi secara real-time dari siteplan interaktif.</p>
                    </div>
                </div>
                <!-- Feature 2 -->
                <div class="col-md-4 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i data-feather="pie-chart"></i>
                        </div>
                        <h3>Keuangan & Transaksi</h3>
                        <p>Pengelolaan tagihan, pembayaran konsumen, serta termin sub-kontraktor yang tercatat secara akurat.</p>
                    </div>
                </div>
                <!-- Feature 3 -->
                <div class="col-md-4 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i data-feather="file-text"></i>
                        </div>
                        <h3>Legal & Dokumen</h3>
                        <p>Sentralisasi dokumen legalitas kavling, progress perizinan, sertifikat, dan administrasi lainnya.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <h2 class="font-weight-bold mb-4">Siap untuk mulai bekerja?</h2>
            <p class="mb-5 text-muted">Akses dashboard internal untuk mengelola semua aspek proyek perumahan Sanggar Indah Group.</p>
            <a href="<?= base_url('login') ?>" class="btn btn-primary btn-custom">Login Sekarang</a>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p class="mb-0">&copy; <?= date('Y') ?> Sanggar Indah Group. All rights reserved.</p>
            <p class="mt-2" style="font-size: 0.85rem;">Internal Property Management System (SIGAPP)</p>
        </div>
    </footer>

    <script src="<?= base_url() ?>/app-assets/vendors/js/vendors.min.js"></script>
    <script>
        $(window).on('load', function() {
            if (feather) {
                feather.replace({ width: 48, height: 48 });
            }
        })
    </script>
</body>
</html>
