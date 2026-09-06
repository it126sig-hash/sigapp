<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="theme-color" content="#1e5fa3">
    <title><?= isset($pageTitle) ? $pageTitle : 'SIGAPP - Sanggar Indah Group' ?></title>
    <meta name="description" content="Landing page SIGAPP, sistem manajemen properti internal untuk proses bisnis Sanggar Indah Group.">
    <link rel="shortcut icon" type="image/x-icon" href="<?= base_url() ?>/app-assets/images/ico/favicon.ico">

    <!-- Plus Jakarta Sans Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
      :root {
        --primary: #1e5fa3;
        --primary-deep: #123d6b;
        --primary-light: #2b84be;
        --ink: #18212b;
        --muted: #6d7783;
        --line: #e8edf2;
        --soft: #f6f8fb;
        --silver: #c9d0d7;
        --white: #ffffff;
        --shadow: 0 22px 60px rgba(24, 33, 43, 0.1);
        font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
      }

      * {
        box-sizing: border-box;
      }

      html {
        scroll-behavior: smooth;
      }

      body {
        margin: 0;
        color: var(--ink);
        background:
          radial-gradient(circle at top left, rgba(30, 95, 163, 0.09), transparent 28rem),
          linear-gradient(180deg, #ffffff 0%, #f8fafc 46%, #ffffff 100%);
      }

      a {
        color: inherit;
        text-decoration: none;
      }

      .page-shell {
        min-height: 100vh;
        overflow: hidden;
      }

      .container {
        width: min(1180px, calc(100% - 40px));
        margin: 0 auto;
      }

      .nav {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
        padding: 24px 0;
      }

      .brand {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        font-weight: 800;
        letter-spacing: 0.08em;
      }

      .brand-mark {
        display: grid;
        width: 42px;
        height: 42px;
        place-items: center;
        overflow: hidden;
        border: 1px solid rgba(30, 95, 163, 0.14);
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 12px 24px rgba(24, 33, 43, 0.08);
      }

      .brand-mark img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        padding: 4px;
      }

      .nav-links {
        display: flex;
        align-items: center;
        gap: 24px;
        color: #5f6b77;
        font-size: 0.95rem;
      }

      .nav-cta {
        border: 1px solid rgba(30, 95, 163, 0.18);
        border-radius: 999px;
        padding: 10px 16px;
        color: var(--primary);
        font-weight: 700;
        background: rgba(30, 95, 163, 0.05);
        transition: all 0.2s ease;
      }
      
      .nav-cta:hover {
        background: var(--primary);
        color: #fff;
      }

      .hero {
        display: grid;
        grid-template-columns: 0.92fr 1.08fr;
        align-items: center;
        gap: 52px;
        padding: 58px 0 84px;
      }

      .eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        width: fit-content;
        margin-bottom: 24px;
        border: 1px solid rgba(30, 95, 163, 0.14);
        border-radius: 999px;
        padding: 8px 13px;
        color: var(--primary-deep);
        background: rgba(255, 255, 255, 0.82);
        box-shadow: 0 10px 30px rgba(24, 33, 43, 0.06);
        font-size: 0.9rem;
        font-weight: 700;
      }

      .eyebrow::before {
        content: "";
        width: 8px;
        height: 8px;
        border-radius: 999px;
        background: var(--primary);
        box-shadow: 0 0 0 6px rgba(30, 95, 163, 0.12);
      }

      h1 {
        max-width: 720px;
        margin: 0;
        font-size: clamp(3rem, 7vw, 6.8rem);
        line-height: 0.92;
        letter-spacing: -0.055em;
      }

      .hero-copy {
        max-width: 590px;
        margin: 28px 0 0;
        color: var(--muted);
        font-size: 1.12rem;
        line-height: 1.75;
      }

      .hero-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 14px;
        margin-top: 34px;
      }

      .button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 48px;
        border-radius: 14px;
        padding: 0 20px;
        font-weight: 800;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
      }
      
      .button:hover {
          transform: translateY(-2px);
      }

      .button.primary {
        color: #fff;
        background: linear-gradient(135deg, var(--primary), var(--primary-deep));
        box-shadow: 0 16px 30px rgba(30, 95, 163, 0.24);
      }

      .button.secondary {
        border: 1px solid var(--line);
        color: #2d3845;
        background: #fff;
      }

      .hero-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 14px;
        margin-top: 40px;
      }

      .stat {
        border-top: 1px solid var(--line);
        padding-top: 16px;
      }

      .stat strong {
        display: block;
        color: var(--primary);
        font-size: 1.45rem;
      }

      .stat span {
        color: var(--muted);
        font-size: 0.92rem;
      }

      .hero-visual {
        position: relative;
        min-height: 640px;
      }

      .logo-orbit {
        position: absolute;
        inset: -32px auto auto 50%;
        width: 420px;
        height: 420px;
        transform: translateX(-50%);
        border-radius: 36px;
        background: rgba(255, 255, 255, 0.82);
        box-shadow: var(--shadow);
        display: grid;
        place-items: center;
      }

      .logo-orbit::before,
      .logo-orbit::after {
        content: "";
        position: absolute;
        border: 1px solid rgba(30, 95, 163, 0.12);
        border-radius: 42px;
        inset: 26px;
        transform: rotate(9deg);
      }

      .logo-orbit::after {
        border-color: rgba(74, 88, 102, 0.12);
        inset: 58px;
        transform: rotate(-14deg);
      }

      .logo-orbit img {
        width: 250px;
        height: 250px;
        object-fit: contain;
        z-index: 1;
      }

      .dashboard-card {
        position: absolute;
        right: 0;
        bottom: 10px;
        width: min(560px, 95%);
        overflow: hidden;
        border: 1px solid rgba(232, 237, 242, 0.88);
        border-radius: 24px;
        background: rgba(255, 255, 255, 0.92);
        box-shadow: var(--shadow);
        backdrop-filter: blur(22px);
      }

      .dashboard-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid var(--line);
        padding: 18px 20px;
      }

      .window-dots {
        display: flex;
        gap: 8px;
      }

      .window-dots span {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #d9dee5;
      }

      .dashboard-title {
        font-size: 0.84rem;
        color: var(--muted);
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
      }

      .dashboard-body {
        display: grid;
        grid-template-columns: 190px 1fr;
        gap: 0;
        min-height: 350px;
      }

      .side-panel {
        border-right: 1px solid var(--line);
        padding: 18px;
        background: linear-gradient(180deg, #fff, #f7f9fb);
      }

      .menu-item {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 8px;
        border-radius: 12px;
        padding: 11px 12px;
        color: #66727f;
        font-size: 0.92rem;
        font-weight: 700;
      }

      .menu-item.active {
        color: var(--primary-deep);
        background: rgba(30, 95, 163, 0.08);
      }

      .menu-dot {
        width: 9px;
        height: 9px;
        border-radius: 999px;
        background: currentColor;
      }

      .content-panel {
        padding: 20px;
      }

      .panel-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 14px;
      }

      .mini-card {
        border: 1px solid var(--line);
        border-radius: 16px;
        padding: 16px;
        background: #fff;
      }

      .mini-card span {
        color: var(--muted);
        font-size: 0.78rem;
        font-weight: 800;
        letter-spacing: 0.05em;
        text-transform: uppercase;
      }

      .mini-card strong {
        display: block;
        margin-top: 10px;
        font-size: 1.65rem;
      }

      .timeline {
        margin-top: 18px;
        border: 1px solid var(--line);
        border-radius: 18px;
        padding: 18px;
        background: #fff;
      }

      .timeline-row {
        display: grid;
        grid-template-columns: 90px 1fr 58px;
        align-items: center;
        gap: 12px;
        padding: 11px 0;
        color: #3b4652;
        font-size: 0.9rem;
      }

      .timeline-row + .timeline-row {
        border-top: 1px solid var(--line);
      }

      .bar {
        height: 9px;
        overflow: hidden;
        border-radius: 999px;
        background: #eef2f6;
      }

      .bar span {
        display: block;
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, var(--primary), #697684);
      }

      section {
        padding: 74px 0;
      }

      .section-kicker {
        margin: 0 0 14px;
        color: var(--primary);
        font-size: 0.86rem;
        font-weight: 900;
        letter-spacing: 0.11em;
        text-transform: uppercase;
      }

      .section-heading {
        max-width: 720px;
        margin: 0;
        font-size: clamp(2rem, 4vw, 3.9rem);
        line-height: 1;
        letter-spacing: -0.04em;
      }

      .section-copy {
        max-width: 680px;
        margin: 20px 0 0;
        color: var(--muted);
        font-size: 1.02rem;
        line-height: 1.75;
      }

      .modules {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 18px;
        margin-top: 38px;
      }

      .module-card {
        position: relative;
        min-height: 240px;
        border: 1px solid var(--line);
        border-radius: 22px;
        padding: 24px;
        background: #fff;
        box-shadow: 0 16px 42px rgba(24, 33, 43, 0.06);
      }

      .module-card.featured {
        color: #fff;
        background:
          radial-gradient(circle at top right, rgba(255, 255, 255, 0.2), transparent 16rem),
          linear-gradient(135deg, var(--primary), var(--primary-deep));
      }

      .module-icon {
        display: grid;
        width: 48px;
        height: 48px;
        place-items: center;
        border-radius: 14px;
        color: var(--primary);
        background: rgba(30, 95, 163, 0.08);
        font-weight: 900;
      }

      .featured .module-icon {
        color: #fff;
        background: rgba(255, 255, 255, 0.16);
      }

      .module-card h3 {
        margin: 22px 0 10px;
        font-size: 1.25rem;
      }

      .module-card p {
        margin: 0;
        color: var(--muted);
        line-height: 1.65;
      }

      .featured p {
        color: rgba(255, 255, 255, 0.78);
      }

      .workflow {
        display: grid;
        grid-template-columns: 0.88fr 1.12fr;
        gap: 36px;
        align-items: stretch;
      }

      .workflow-board {
        border: 1px solid var(--line);
        border-radius: 26px;
        padding: 22px;
        background: #fff;
        box-shadow: var(--shadow);
      }

      .stage {
        display: grid;
        grid-template-columns: 52px 1fr auto;
        gap: 14px;
        align-items: center;
        border: 1px solid var(--line);
        border-radius: 18px;
        padding: 16px;
        background: #fff;
      }

      .stage + .stage {
        margin-top: 12px;
      }

      .stage-number {
        display: grid;
        width: 42px;
        height: 42px;
        place-items: center;
        border-radius: 13px;
        color: #fff;
        background: linear-gradient(135deg, var(--primary), var(--primary-deep));
        font-weight: 900;
      }

      .stage strong {
        display: block;
        margin-bottom: 4px;
      }

      .stage span {
        color: var(--muted);
        font-size: 0.92rem;
      }

      .status-pill {
        border-radius: 999px;
        padding: 8px 11px;
        color: #123d6b;
        background: rgba(30, 95, 163, 0.12);
        font-size: 0.78rem;
        font-weight: 800;
      }

      .identity-band {
        border-radius: 30px;
        padding: 42px;
        color: #fff;
        background:
          linear-gradient(135deg, rgba(30, 95, 163, 0.96), rgba(69, 80, 92, 0.96)),
          url("<?= base_url('images/logo.png') ?>") right -80px center / 360px no-repeat;
        box-shadow: 0 26px 62px rgba(30, 95, 163, 0.18);
        background-blend-mode: overlay;
      }

      .identity-band h2 {
        max-width: 700px;
        margin: 0;
        font-size: clamp(2rem, 4vw, 4rem);
        line-height: 1;
        letter-spacing: -0.045em;
      }

      .identity-band p {
        max-width: 660px;
        margin: 18px 0 0;
        color: rgba(255, 255, 255, 0.78);
        font-size: 1.04rem;
        line-height: 1.75;
      }

      .footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        border-top: 1px solid var(--line);
        padding: 28px 0 34px;
        color: var(--muted);
        font-size: 0.94rem;
      }

      @media (max-width: 960px) {
        .nav-links {
          display: none;
        }

        .hero,
        .workflow {
          grid-template-columns: 1fr;
        }

        .hero {
          padding-top: 26px;
        }

        .hero-visual {
          min-height: 580px;
        }

        .logo-orbit {
          left: 50%;
          width: 360px;
          height: 360px;
        }

        .logo-orbit img {
          width: 200px;
          height: 200px;
        }

        .dashboard-card {
          left: 50%;
          right: auto;
          transform: translateX(-50%);
        }

        .modules {
          grid-template-columns: repeat(2, 1fr);
        }
      }

      @media (max-width: 680px) {
        .container {
          width: min(100% - 28px, 1180px);
        }

        .hero-stats,
        .modules,
        .panel-grid {
          grid-template-columns: 1fr;
        }

        .hero-visual {
          min-height: 520px;
        }

        .logo-orbit {
          width: 270px;
          height: 270px;
          border-radius: 28px;
        }

        .logo-orbit img {
          width: 150px;
          height: 150px;
        }

        .dashboard-body {
          grid-template-columns: 1fr;
        }

        .side-panel {
          display: grid;
          grid-template-columns: repeat(2, 1fr);
          gap: 8px;
          border-right: 0;
          border-bottom: 1px solid var(--line);
        }

        .menu-item {
          margin: 0;
        }

        .timeline-row {
          grid-template-columns: 1fr;
          gap: 8px;
        }

        .stage {
          grid-template-columns: 44px 1fr;
        }

        .status-pill {
          grid-column: 2;
          width: fit-content;
        }

        .identity-band {
          padding: 30px;
          background:
            linear-gradient(135deg, rgba(30, 95, 163, 0.97), rgba(69, 80, 92, 0.97)),
            url("<?= base_url('images/logo.png') ?>") right -120px bottom -80px / 300px no-repeat;
          background-blend-mode: overlay;
        }

        .footer {
          align-items: flex-start;
          flex-direction: column;
        }
      }
    </style>
</head>
<body>
    <div class="page-shell">
      <header class="container nav" aria-label="Navigasi utama">
        <a class="brand" href="#">
          <span class="brand-mark" aria-hidden="true">
            <img src="<?= base_url('images/logo.png') ?>" alt="Logo" onerror="this.src='<?= base_url('app-assets/images/logo/logo.png') ?>'">
          </span>
          SIGAPP
        </a>

        <nav class="nav-links" aria-label="Bagian halaman">
          <a href="#modul">Modul</a>
          <a href="#workflow">Workflow</a>
          <a href="#identity">Identitas</a>
          <a class="nav-cta" href="<?= base_url('login') ?>">Masuk ke Dashboard</a>
        </nav>
      </header>

      <main>
        <section class="container hero">
          <div>
            <p class="eyebrow">Sanggar Indah Group App</p>
            <h1>Satu pusat kerja untuk properti, data, dan keputusan.</h1>
            <p class="hero-copy">
              SIGAPP dirancang sebagai ERP internal yang menyatukan Produksi, MKDT,
              Proyek, Legal, dan Keuangan dalam satu pengalaman kerja yang tersentralisasi, akurat,
              dan konsisten dengan standar Sanggar Indah Group.
            </p>

            <div class="hero-actions">
              <a class="button primary" href="<?= base_url('login') ?>">Login ke Dashboard</a>
              <a class="button secondary" href="#modul">Lihat Modul</a>
            </div>

            <div class="hero-stats" aria-label="Ringkasan SIGAPP">
              <div class="stat">
                <strong>5+</strong>
                <span>divisi terhubung</span>
              </div>
              <div class="stat">
                <strong>20+</strong>
                <span>user siap berkembang</span>
              </div>
              <div class="stat">
                <strong>1</strong>
                <span>sumber kerja terpadu</span>
              </div>
            </div>
          </div>

          <div class="hero-visual" aria-label="Preview dashboard SIGAPP">
            <div class="logo-orbit" aria-hidden="true">
              <img src="<?= base_url('images/logo.png') ?>" alt="Logo Besar" onerror="this.src='<?= base_url('app-assets/images/logo/logo.png') ?>'">
            </div>

            <div class="dashboard-card">
              <div class="dashboard-top">
                <div class="window-dots" aria-hidden="true">
                  <span></span>
                  <span></span>
                  <span></span>
                </div>
                <div class="dashboard-title">SIGAPP Control Center</div>
              </div>

              <div class="dashboard-body">
                <aside class="side-panel" aria-label="Menu modul">
                  <div class="menu-item active"><span class="menu-dot"></span>Siteplan</div>
                  <div class="menu-item"><span class="menu-dot"></span>Konsumen</div>
                  <div class="menu-item"><span class="menu-dot"></span>Keuangan</div>
                  <div class="menu-item"><span class="menu-dot"></span>Legalitas</div>
                  <div class="menu-item"><span class="menu-dot"></span>Produksi</div>
                </aside>

                <div class="content-panel">
                  <div class="panel-grid">
                    <div class="mini-card">
                      <span>Kavling Aktif</span>
                      <strong>128</strong>
                    </div>
                    <div class="mini-card">
                      <span>Berkas Legal</span>
                      <strong>842</strong>
                    </div>
                    <div class="mini-card">
                      <span>Tagihan Aktif</span>
                      <strong>36</strong>
                    </div>
                    <div class="mini-card">
                      <span>Tiket Masalah</span>
                      <strong>14</strong>
                    </div>
                  </div>

                  <div class="timeline" aria-label="Progress pekerjaan">
                    <div class="timeline-row">
                      <strong>Produksi</strong>
                      <div class="bar"><span style="width: 72%"></span></div>
                      <span>72%</span>
                    </div>
                    <div class="timeline-row">
                      <strong>Legal</strong>
                      <div class="bar"><span style="width: 54%"></span></div>
                      <span>54%</span>
                    </div>
                    <div class="timeline-row">
                      <strong>Finance</strong>
                      <div class="bar"><span style="width: 81%"></span></div>
                      <span>81%</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>

        <section class="container" id="modul">
          <p class="section-kicker">Modul Inti</p>
          <h2 class="section-heading">Didesain mengikuti cara kerja developer properti.</h2>
          <p class="section-copy">
            Setiap modul dibuat ringkas, visual, dan mudah dipindai agar pekerjaan antar divisi
            tidak berhenti di chat, spreadsheet terpisah, atau file yang tercecer.
          </p>

          <div class="modules">
            <article class="module-card featured">
              <div class="module-icon">S</div>
              <h3>Siteplan Interaktif</h3>
              <p>Manajemen kavling, pantau status penjualan unit, serta update harga real-time dalam satu kanvas interaktif.</p>
            </article>
            <article class="module-card">
              <div class="module-icon">K</div>
              <h3>Keuangan &amp; Pencairan</h3>
              <p>Pencatatan tagihan konsumen, riwayat pembayaran, pajak, hingga cashout dan pencairan retensi subkon.</p>
            </article>
            <article class="module-card">
              <div class="module-icon">L</div>
              <h3>Legalitas &amp; Dokumen</h3>
              <p>Pantau progress sertifikasi HGB, PBB, PBG, hingga AJB secara terpusat untuk setiap unit kavling.</p>
            </article>
            <article class="module-card">
              <div class="module-icon">M</div>
              <h3>Manajemen Konsumen</h3>
              <p>Sentralisasi manajemen konsumen (MKDT), pencatatan transaksi, pembatalan, hingga serah terima unit.</p>
            </article>
            <article class="module-card">
              <div class="module-icon">P</div>
              <h3>Progress Produksi</h3>
              <p>Tracking akurat milestone pembangunan (0% hingga 100%) dan pengawasan sub-kontraktor lapangan.</p>
            </article>
            <article class="module-card">
              <div class="module-icon">T</div>
              <h3>Tiket Masalah</h3>
              <p>Manajemen pengaduan komplain lapangan, pelacakan riwayat masalah, serta distribusi tugas antar divisi.</p>
            </article>
          </div>
        </section>

        <section class="container workflow" id="workflow">
          <div>
            <p class="section-kicker">Workflow</p>
            <h2 class="section-heading">Dari data lapangan sampai keputusan finance.</h2>
            <p class="section-copy">
              SIGAPP mengintegrasikan seluruh tahapan: mulai dari master data kavling, transaksi konsumen, legalitas, hingga kontrol progress pembangunan dan arus kas.
            </p>
          </div>

          <div class="workflow-board">
            <div class="stage">
              <div class="stage-number">01</div>
              <div>
                <strong>Master Data & Transaksi</strong>
                <span>Setup siteplan, harga kavling, dan pencatatan awal transaksi konsumen (MKDT).</span>
              </div>
              <div class="status-pill">Input</div>
            </div>
            <div class="stage">
              <div class="stage-number">02</div>
              <div>
                <strong>Legalitas & Konstruksi</strong>
                <span>Pemrosesan dokumen legal unit dan pemantauan progress produksi di lapangan secara riil.</span>
              </div>
              <div class="status-pill">Review</div>
            </div>
            <div class="stage">
              <div class="stage-number">03</div>
              <div>
                <strong>Keuangan & Pencairan</strong>
                <span>Verifikasi penerimaan pembayaran konsumen dan persetujuan pencairan (cashout) untuk sub-kontraktor.</span>
              </div>
              <div class="status-pill">Approve</div>
            </div>
            <div class="stage">
              <div class="stage-number">04</div>
              <div>
                <strong>Insight & Evaluasi</strong>
                <span>Monitor posisi stok, laporan jatuh tempo, dan evaluasi proyek melalui dashboard terintegrasi.</span>
              </div>
              <div class="status-pill">Insight</div>
            </div>
          </div>
        </section>

        <section class="container" id="identity">
          <div class="identity-band">
            <h2>Clean seperti aplikasi modern. Tegas seperti identitas SIG.</h2>
            <p>
              Arah visual SIGAPP memakai putih sebagai ruang kerja utama, biru SIG sebagai aksen keputusan,
              dan silver sebagai lapisan profesional. Logo S-shape menjadi pusat identitas yang lebih
              cocok untuk aplikasi, dashboard, favicon, dan dokumen internal.
            </p>
          </div>
        </section>
      </main>

      <footer class="container footer">
        <span>&copy; <?= date('Y') ?> SIGAPP — Sanggar Indah Group</span>
        <span>ERP internal untuk proses bisnis developer properti.</span>
      </footer>
    </div>
</body>
</html>
