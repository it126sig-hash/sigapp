<link rel="stylesheet" type="text/css" href="<?= base_url('app-assets/vendors/css/extensions/sweetalert2.min.css') ?>">
<link rel="stylesheet" type="text/css" href="<?= base_url('assets/css/copilot.css') ?>?v=<?= filemtime(FCPATH . 'assets/css/copilot.css') ?>">

<div class="app-content content copilot-page">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper">
        <div class="content-header row mb-1 align-items-center">
            <div class="content-header-left col-md-6 col-12">
                <div class="row breadcrumbs-top">
                    <div class="col-12">
                        <h2 class="content-header-title float-left mb-0"><?= esc($title ?? 'AI Copilot') ?></h2>
                        <div class="breadcrumb-wrapper">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?= base_url() ?>">Home</a></li>
                                <li class="breadcrumb-item active">AI Copilot</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-header-right col-md-6 col-12">
                <div class="copilot-header-badges">
                    <span id="copilot-provider-badge" class="copilot-pill copilot-pill--primary" title="Provider dan model AI aktif">
                        <i class="fas fa-robot" aria-hidden="true"></i>
                        <span>Memuat provider...</span>
                    </span>
                    <span id="copilot-key-badge" class="copilot-pill copilot-pill--neutral" title="Status API key server (.env)">
                        <i class="fas fa-key" aria-hidden="true"></i>
                        <span>Memeriksa status...</span>
                    </span>
                    <button type="button" id="copilot-refresh-meta-btn" class="btn btn-sm btn-outline-secondary" title="Muat ulang status &amp; kapabilitas">
                        <i class="fas fa-sync-alt" aria-hidden="true"></i>
                        <span class="d-none d-sm-inline ml-25">Refresh</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="content-body">
            <!-- Status Bar: Proyek Dapat Diakses & Kapabilitas Tools -->
            <div class="card copilot-status-card mb-1">
                <div class="card-body">
                    <div class="copilot-meta-grid">
                        <div class="copilot-info-row">
                            <div class="copilot-info-label">
                                <i class="fas fa-building" aria-hidden="true"></i>
                                <span>Proyek Dapat Diakses</span>
                            </div>
                            <div id="copilot-projects-list" class="copilot-chip-list">
                                <span class="copilot-chip copilot-chip--muted">Memuat daftar proyek...</span>
                            </div>
                        </div>
                        <div class="copilot-info-row">
                            <div class="copilot-info-label">
                                <i class="fas fa-toolbox" aria-hidden="true"></i>
                                <span>Kapabilitas Diizinkan</span>
                            </div>
                            <div id="copilot-tools-list" class="copilot-chip-list">
                                <span class="copilot-chip copilot-chip--muted">Memuat kapabilitas...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mobile Tabs (1 Baris Horizontal Scroll) -->
            <div class="d-lg-none mb-1">
                <ul id="copilot-mobile-tabs" class="nav nav-pills copilot-mobile-tabs" role="tablist" aria-label="Navigasi panel Copilot">
                    <li class="nav-item">
                        <button type="button" id="copilot-tab-chat" class="nav-link active" data-copilot-tab="chat" role="tab" aria-selected="true">
                            <i class="fas fa-comments mr-25" aria-hidden="true"></i>Chat
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" id="copilot-tab-telegram" class="nav-link" data-copilot-tab="telegram" role="tab" aria-selected="false">
                            <i class="fas fa-paper-plane mr-25" aria-hidden="true"></i>Telegram
                        </button>
                    </li>
                    <li class="nav-item" id="copilot-tab-settings-item" hidden>
                        <button type="button" id="copilot-tab-settings" class="nav-link" data-copilot-tab="settings" role="tab" aria-selected="false">
                            <i class="fas fa-sliders-h mr-25" aria-hidden="true"></i>Provider AI
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" id="copilot-tab-policy" class="nav-link" data-copilot-tab="policy" role="tab" aria-selected="false">
                            <i class="fas fa-shield-alt mr-25" aria-hidden="true"></i>Kebijakan
                        </button>
                    </li>
                </ul>
            </div>

            <!-- Main Layout: Chat (Left) + Sidebar Cards (Right) -->
            <div class="row copilot-layout">
                <div class="col-lg-8 col-12 copilot-panel is-active" data-copilot-panel="chat">
                    <div id="copilot-chat-card" class="card copilot-chat-card mb-1">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <div>
                                <h4 class="card-title">Asisten Data Operasional SIGAPP</h4>
                                <div class="copilot-chat-subtitle">Tanyakan ringkasan booking, konversi akad, atau cicilan jatuh tempo sesuai hak akses Anda.</div>
                            </div>
                            <button type="button" id="copilot-history-refresh-btn" class="btn btn-sm btn-outline-primary" title="Muat ulang riwayat chat">
                                <i class="fas fa-history mr-25" aria-hidden="true"></i>
                                <span class="d-none d-sm-inline">Riwayat</span>
                            </button>
                        </div>

                        <div class="card-body copilot-chat-body">
                            <div id="copilot-messages" class="copilot-messages" aria-live="polite">
                                <div id="copilot-empty-state" class="copilot-empty-state">
                                    <div class="copilot-empty-icon">
                                        <i class="fas fa-robot" aria-hidden="true"></i>
                                    </div>
                                    <h5 class="copilot-empty-title">Selamat datang di AI Copilot</h5>
                                    <p class="copilot-empty-desc">
                                        Pilih salah satu contoh pertanyaan di bawah atau ketik pertanyaan Anda terkait data proyek yang dapat Anda akses.
                                    </p>
                                </div>
                            </div>

                            <div id="copilot-typing-indicator" class="copilot-typing" hidden role="status" aria-live="polite">
                                <div class="copilot-typing-dots" aria-hidden="true">
                                    <span></span><span></span><span></span>
                                </div>
                                <span>AI Copilot sedang memproses jawaban...</span>
                            </div>

                            <div id="copilot-chat-error" class="copilot-chat-error" hidden role="alert">
                                <span id="copilot-chat-error-text"></span>
                                <button type="button" id="copilot-chat-error-close" class="copilot-chat-error-close" aria-label="Tutup pesan error">&times;</button>
                            </div>
                        </div>

                        <div class="card-footer copilot-chat-footer">
                            <div id="copilot-quick-prompts" class="copilot-quick-prompts" aria-label="Saran pertanyaan cepat"></div>

                            <form id="copilot-chat-form" novalidate>
                                <label for="copilot-input" class="sr-only">Pesan ke AI Copilot</label>
                                <textarea
                                    id="copilot-input"
                                    class="form-control copilot-input"
                                    rows="2"
                                    maxlength="2000"
                                    placeholder="Ketik pertanyaan Anda... (Enter untuk kirim, Shift+Enter untuk baris baru)"
                                ></textarea>
                                <div class="copilot-input-actions">
                                    <small id="copilot-char-count" class="copilot-char-count">0 / 2000</small>
                                    <button type="submit" id="copilot-send-btn" class="btn btn-primary btn-sm px-2">
                                        <i class="fas fa-paper-plane mr-50" aria-hidden="true"></i>Kirim
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-12 copilot-sidebar">
                    <!-- Kartu Hubungkan Telegram -->
                    <div class="copilot-panel" data-copilot-panel="telegram">
                        <div id="copilot-telegram-card" class="card copilot-side-card">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <h4 class="card-title">
                                    <i class="fas fa-paper-plane text-primary mr-50" aria-hidden="true"></i>Hubungkan Telegram
                                </h4>
                                <span id="copilot-telegram-status-badge" class="copilot-pill copilot-pill--neutral">Memuat...</span>
                            </div>
                            <div class="card-body">
                                <div class="divider divider-left mt-0">
                                    <div class="divider-text">Tautan Akun Bot</div>
                                </div>

                                <!-- Belum Tertaut -->
                                <div id="copilot-telegram-unlinked">
                                    <p class="copilot-side-desc">
                                        Tautkan akun SIGAPP Anda dengan bot Telegram agar dapat bertanya langsung melalui chat privat Telegram.
                                    </p>

                                    <div id="copilot-telegram-pending-note" class="copilot-inline-feedback copilot-inline-feedback--success" hidden></div>

                                    <div id="copilot-telegram-code-box" class="copilot-telegram-code-box" hidden>
                                        <div class="copilot-telegram-code-row">
                                            <div>
                                                <span class="copilot-kv-label">Kode Pairing (6 Digit)</span>
                                                <div id="copilot-telegram-code" class="copilot-telegram-code">------</div>
                                            </div>
                                            <button type="button" id="copilot-telegram-copy-btn" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-copy mr-25" aria-hidden="true"></i><span id="copilot-telegram-copy-label">Salin <span class="d-none d-sm-inline">/link</span></span>
                                            </button>
                                        </div>
                                        <div id="copilot-telegram-expires" class="small text-muted mb-50"></div>
                                        <div>
                                            <span class="copilot-kv-label">Perintah di Bot Telegram:</span>
                                            <code id="copilot-telegram-command" class="copilot-telegram-command">/link 000000</code>
                                        </div>
                                        <ol class="copilot-telegram-steps">
                                            <li>Buka chat privat dengan bot Telegram AI Copilot SIGAPP.</li>
                                            <li>Salin dan kirim perintah <strong id="copilot-telegram-step-cmd">/link 000000</strong>.</li>
                                            <li>Klik <strong>Cek Status</strong> di bawah setelah bot mengonfirmasi.</li>
                                        </ol>
                                    </div>

                                    <div class="d-flex flex-wrap align-items-center" style="gap: .5rem;">
                                        <button type="button" id="copilot-telegram-link-btn" class="btn btn-primary btn-sm">
                                            <i class="fas fa-link mr-25" aria-hidden="true"></i>Buat Kode Tautan
                                        </button>
                                        <button type="button" id="copilot-telegram-refresh-btn" class="btn btn-outline-secondary btn-sm">
                                            <i class="fas fa-sync-alt mr-25" aria-hidden="true"></i>Cek Status
                                        </button>
                                    </div>
                                </div>

                                <!-- Sudah Tertaut -->
                                <div id="copilot-telegram-linked" hidden>
                                    <div class="copilot-kv-row">
                                        <span class="copilot-kv-label">Akun Telegram</span>
                                        <span id="copilot-telegram-username" class="copilot-kv-value">-</span>
                                    </div>
                                    <div class="copilot-kv-row">
                                        <span class="copilot-kv-label">Terverifikasi Pada</span>
                                        <span id="copilot-telegram-verified-at" class="copilot-kv-value">-</span>
                                    </div>
                                    <button type="button" id="copilot-telegram-unlink-btn" class="btn btn-outline-danger btn-sm btn-block">
                                        <i class="fas fa-unlink mr-25" aria-hidden="true"></i>Putuskan Tautan
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Kartu Pengaturan Provider AI (KHUSUS ADMIN) -->
                    <div id="copilot-settings-panel" class="copilot-panel" data-copilot-panel="settings" hidden>
                        <div id="copilot-settings-card" class="card copilot-side-card">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <h4 class="card-title">
                                    <i class="fas fa-sliders-h text-primary mr-50" aria-hidden="true"></i>Pengaturan Provider AI
                                </h4>
                                <span class="copilot-pill copilot-pill--primary">Admin</span>
                            </div>
                            <div class="card-body">
                                <div class="divider divider-left mt-0">
                                    <div class="divider-text">Konfigurasi Model LLM</div>
                                </div>

                                <form id="copilot-settings-form" novalidate>
                                    <div class="form-group mb-1">
                                        <label for="copilot-setting-provider" class="copilot-form-label">Provider LLM</label>
                                        <select id="copilot-setting-provider" class="form-control form-control-sm"></select>
                                        <div id="copilot-setting-key-status" class="mt-50"></div>
                                    </div>

                                    <div class="form-group mb-1">
                                        <label for="copilot-setting-model" class="copilot-form-label">Model Aktif</label>
                                        <input
                                            type="text"
                                            id="copilot-setting-model"
                                            class="form-control form-control-sm"
                                            list="copilot-model-suggestions"
                                            maxlength="150"
                                            placeholder="Contoh: gemini-flash-latest"
                                            required
                                        >
                                        <datalist id="copilot-model-suggestions"></datalist>
                                    </div>

                                    <div class="form-group mb-1">
                                        <label for="copilot-setting-base-url" class="copilot-form-label">Base URL (Opsional)</label>
                                        <input
                                            type="url"
                                            id="copilot-setting-base-url"
                                            class="form-control form-control-sm"
                                            placeholder="Kosongkan untuk default provider"
                                        >
                                    </div>

                                    <div class="copilot-env-note">
                                        <i class="fas fa-lock mr-25" aria-hidden="true"></i>
                                        API key disimpan aman di file <code>.env</code> server dan tidak pernah disimpan di browser atau database.
                                    </div>

                                    <div id="copilot-settings-feedback" class="copilot-inline-feedback" hidden role="status"></div>

                                    <button type="submit" id="copilot-settings-save-btn" class="btn btn-primary btn-sm btn-block">
                                        <i class="fas fa-save mr-25" aria-hidden="true"></i>Simpan Pengaturan
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Kartu Info Kebijakan & Keamanan -->
                    <div class="copilot-panel" data-copilot-panel="policy">
                        <div id="copilot-policy-card" class="card copilot-side-card">
                            <div class="card-header">
                                <h4 class="card-title">
                                    <i class="fas fa-shield-alt text-primary mr-50" aria-hidden="true"></i>Kebijakan &amp; Keamanan
                                </h4>
                            </div>
                            <div class="card-body">
                                <div class="divider divider-left mt-0">
                                    <div class="divider-text">Batasan Akses &amp; Privasi</div>
                                </div>
                                <ul class="copilot-policy-list">
                                    <li class="copilot-policy-item">
                                        <i class="fas fa-eye" aria-hidden="true"></i>
                                        <span><strong>Read-Only:</strong> Copilot hanya membaca ringkasan operasional sesuai hak akses proyek &amp; role Anda, tidak dapat mengubah data.</span>
                                    </li>
                                    <li class="copilot-policy-item">
                                        <i class="fas fa-user-shield" aria-hidden="true"></i>
                                        <span><strong>Perlindungan PII:</strong> Copilot tidak memiliki akses ke NIK/KTP, KK, NPWP, nomor HP, maupun alamat konsumen.</span>
                                    </li>
                                    <li class="copilot-policy-item">
                                        <i class="fas fa-tachometer-alt" aria-hidden="true"></i>
                                        <span><strong>Batas Penggunaan:</strong> <span id="copilot-policy-rate-limit">Maksimal 20 pesan per 10 menit.</span></span>
                                    </li>
                                    <li class="copilot-policy-item">
                                        <i class="fas fa-archive" aria-hidden="true"></i>
                                        <span><strong>Retensi Riwayat:</strong> <span id="copilot-policy-retention">Riwayat percakapan disimpan selama 90 hari.</span></span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= base_url('app-assets/vendors/js/vendors.min.js') ?>"></script>
<script src="<?= base_url('app-assets/vendors/js/extensions/sweetalert2.all.min.js') ?>"></script>
<script>
    window.COPILOT_CONFIG = <?= json_encode([
        'baseUrl'  => rtrim(base_url(), '/'),
        'csrfName' => csrf_token(),
        'csrfHash' => csrf_hash(),
        'maxChars' => 2000,
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
</script>
<script src="<?= base_url('assets/js/copilot.js') ?>?v=<?= filemtime(FCPATH . 'assets/js/copilot.js') ?>"></script>
