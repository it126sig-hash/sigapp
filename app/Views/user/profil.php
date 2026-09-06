<?php
$displayName = old('name', $profile->name ?? '');
if ($displayName === '') {
    $displayName = $profile->username ?? '';
}
?>

<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/extensions/sweetalert2.min.css">

<style>
    /* Profile Summary Sidebar */
    .profile-page .profile-photo-frame {
        width: 132px;
        height: 132px;
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid #e5e7eb;
        background: #f8fafc;
        margin: 0 auto;
    }
    .profile-page .profile-photo-frame img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    /* Navigation Tabs */
    .profile-page .nav-tabs {
        border-bottom: 1px solid #e5e7eb;
    }
    .profile-page .nav-tabs .nav-link {
        font-weight: 600;
        color: #6b7280;
        border: none;
        border-bottom: 2px solid transparent;
        padding: 1rem 1.5rem;
    }
    .profile-page .nav-tabs .nav-link.active {
        color: #111827;
        border-bottom: 2px solid #2057a3;
        background: transparent;
    }
    
    /* Utilities */
    .profile-page .text-sm { font-size: 0.875rem; }
    .profile-page .text-muted { color: #6b7280 !important; }
    .profile-page .font-weight-bolder { font-weight: 700; }
    
    /* Notification Table Redesign */
    .table-notif thead th {
        border-top: none;
        border-bottom: 1px solid #e5e7eb;
        color: #6b7280;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        background: #f9fafb;
    }
    .table-notif tbody td {
        vertical-align: middle;
        border-bottom: 1px solid #e5e7eb;
    }
    .table-notif tbody tr:last-child td { border-bottom: none; }
    .table-notif .category-row td {
        background: #f9fafb;
        font-weight: 600;
        color: #111827;
        padding-top: 1rem;
        padding-bottom: 1rem;
    }
    
    /* Modern Custom Switch */
    .table-notif .custom-switch .custom-control-label::before {
        height: 1.25rem;
        width: 2.25rem;
        border-radius: 2rem;
    }
    .table-notif .custom-switch .custom-control-label::after {
        width: calc(1.25rem - 4px);
        height: calc(1.25rem - 4px);
        border-radius: 50%;
    }
    .table-notif .custom-control-input:checked ~ .custom-control-label::before {
        background-color: #10b981;
        border-color: #10b981;
    }
    .table-notif .custom-control-input:disabled ~ .custom-control-label::before {
        background-color: #e5e7eb;
        opacity: 1;
    }
    
    /* General Custom Switch */
    .profile-page .custom-switch-label {
        font-weight: 600;
        color: #111827;
    }
</style>

<div class="app-content content profile-page">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper p-0">
        <!-- Breadcrumb removed as requested -->
        
        <div class="content-body">
            <!-- Messages -->
            <?php if (session('message')) : ?>
                <div class="alert alert-success mt-1 mx-1" role="alert"><?= esc(session('message')) ?></div>
            <?php endif; ?>
            <?php if (session('error')) : ?>
                <div class="alert alert-danger mt-1 mx-1" role="alert"><?= esc(session('error')) ?></div>
            <?php endif; ?>
            <?php $errors = session('errors') ?? []; ?>

            <!-- Main Container -->
            <div class="card mt-1 mb-0 mx-1">
                <!-- Navigation Tabs -->
                <ul class="nav nav-tabs m-0 px-1 pt-1" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="profile-tab" data-toggle="tab" href="#profile-tab-content" role="tab">
                            <i class="fa fa-user mr-50"></i> Informasi Profil & Akun
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="notif-tab" data-toggle="tab" href="#notif-tab-content" role="tab">
                            <i class="fa fa-bell mr-50"></i> Pengaturan Notifikasi
                        </a>
                    </li>
                </ul>
            </div>

            <div class="tab-content mx-1 mt-1">
                <!-- Tab 1: Profil & Akun -->
                <div class="tab-pane active" id="profile-tab-content" role="tabpanel">
                    <div class="row">
                        <!-- Left Sidebar -->
                        <div class="col-lg-4 col-12 mb-2">
                            <div class="card h-100 mb-0 shadow-sm">
                                <div class="card-body text-center">
                                    <div class="profile-photo-frame mb-1 position-relative">
                                        <img id="profile-photo-preview" src="<?= esc($photoUrl) ?>" alt="Foto profil">
                                    </div>
                                    <h4 class="mb-25 font-weight-bolder"><?= esc($displayName) ?></h4>
                                    <!-- Role & Email -->
                                    <div class="text-muted text-sm font-weight-bold mb-1 text-uppercase">
                                        <?= esc($profile->username ?? 'USER') ?>
                                    </div>
                                    <div class="text-muted text-sm mb-1">
                                        <i class="fa fa-envelope mr-25"></i> <?= esc($profile->email ?? '-') ?>
                                    </div>
                                    
                                    <label for="profile_photo" class="btn btn-outline-primary btn-block mb-0 cursor-pointer">
                                        <i class="fa fa-cloud-upload mr-50"></i> Unggah Foto Baru
                                    </label>
                                    <small class="text-muted mt-50 d-block mb-2">Maksimal ukuran 2MB (JPG, PNG, atau WebP)</small>
                                    
                                    <!-- Google Calendar Section inside the same card -->
                                    <div class="divider divider-left mt-2">
                                        <div class="divider-text text-uppercase text-muted"><i class="fa fa-calendar mr-25"></i> Google Calendar</div>
                                    </div>
                                    <p class="text-sm text-muted text-left mb-1">Sinkronisasi otomatis agenda akad konsumen, wawancara, dan survei kelayakan ke kalender pribadi.</p>
                                    
                                    <?php $googleCalendarStatus = $googleCalendarStatus ?? ['connected' => false, 'configured' => false, 'ready' => false, 'message' => '']; ?>
                                    <?php if (! empty($googleCalendarStatus['connected'])) : ?>
                                        <div class="d-flex align-items-center mb-1">
                                            <span class="badge badge-light-success mr-50"><i class="fa fa-circle text-success" style="font-size: 8px;"></i> Terhubung</span>
                                            <small class="text-muted"><?= esc($googleCalendarStatus['google_email']) ?></small>
                                        </div>
                                        <form action="<?= base_url('google-calendar/disconnect') ?>" method="post">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-outline-danger btn-block btn-sm">
                                                <i class="fa fa-unlink mr-50"></i> Putuskan Sambungan
                                            </button>
                                        </form>
                                    <?php else : ?>
                                        <a href="<?= base_url('google-calendar/connect') ?>" class="btn btn-outline-primary btn-block btn-sm <?= (empty($googleCalendarStatus['configured']) || empty($googleCalendarStatus['ready'])) ? 'disabled' : '' ?>">
                                            <i class="fa fa-calendar-plus mr-50"></i> Hubungkan Kalender
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Right Content -->
                        <div class="col-lg-8 col-12">
                            <form action="<?= base_url('profil/update') ?>" method="post" id="form-profile" enctype="multipart/form-data">
                                <?= csrf_field() ?>
                                <input type="file" id="profile_photo" name="profile_photo" class="d-none" accept="image/jpeg,image/png,image/webp">
                                
                                <div class="card mb-2 shadow-sm">
                                    <div class="card-header border-bottom">
                                        <h4 class="card-title">
                                            <i class="fa fa-id-card mr-50"></i> Informasi Personal & Akun
                                        </h4>
                                    </div>
                                    <div class="card-body pt-2">
                                        <div class="row">
                                            <div class="col-md-6 col-12">
                                                <div class="form-group">
                                                    <label for="name">Nama Lengkap & Gelar</label>
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text"><i class="fa fa-user"></i></span>
                                                        </div>
                                                        <input type="text" id="name" name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" value="<?= esc($displayName) ?>" maxlength="120" required>
                                                    </div>
                                                    <?php if (isset($errors['name'])) : ?><div class="invalid-feedback d-block"><?= esc($errors['name']) ?></div><?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-12">
                                                <div class="form-group">
                                                    <label for="username">Username Aktif</label>
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text"><i class="fa fa-at"></i></span>
                                                        </div>
                                                        <input type="text" id="username" name="username" class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>" value="<?= old('username', esc($profile->username ?? '')) ?>" maxlength="30" required>
                                                    </div>
                                                    <?php if (isset($errors['username'])) : ?><div class="invalid-feedback d-block"><?= esc($errors['username']) ?></div><?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-12">
                                                <div class="form-group">
                                                    <label for="email">Alamat Email Resmi (Login SSO)</label>
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text"><i class="fa fa-envelope"></i></span>
                                                        </div>
                                                        <input type="email" id="email" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" value="<?= old('email', esc($profile->email ?? '')) ?>" maxlength="255" required>
                                                    </div>
                                                    <?php if (isset($errors['email'])) : ?><div class="invalid-feedback d-block"><?= esc($errors['email']) ?></div><?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-12">
                                                <div class="form-group">
                                                    <label for="department">Departemen / Penempatan Operasional</label>
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text"><i class="fa fa-building"></i></span>
                                                        </div>
                                                        <input type="text" id="department" class="form-control" value="<?= esc($department ?? '') ?>" readonly style="background-color: #f9fafb; cursor: not-allowed;">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="p-1 border rounded mt-1 bg-light d-flex align-items-center">
                                            <div class="mr-1">
                                                <div class="avatar bg-white border">
                                                    <div class="avatar-content"><i class="fa fa-envelope-open-text text-primary"></i></div>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-25 custom-switch-label">Terima Rangkuman Notifikasi Harian via Email</h6>
                                                <p class="text-sm text-muted mb-0">Kompilasi agenda kerja, transaksi tertunda, dan ringkasan sistem dikirim setiap pukul 17:00 WIB.</p>
                                            </div>
                                            <div class="ml-1">
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox" class="custom-control-input" id="email_notif_enabled" name="email_notif_enabled" <?= (isset($profile->email_notif_enabled) && $profile->email_notif_enabled) ? 'checked' : '' ?>>
                                                    <label class="custom-control-label" for="email_notif_enabled"></label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card mb-2 shadow-sm">
                                    <div class="card-header border-bottom">
                                        <h4 class="card-title"><i class="fa fa-lock mr-50"></i> Pembaruan Kata Sandi</h4>
                                        <div class="heading-elements">
                                            <span class="badge badge-light-primary"><i class="fa fa-shield"></i> Opsional</span>
                                        </div>
                                    </div>
                                    <div class="card-body pt-2">
                                        <p class="text-sm text-muted">Kosongkan kolom ini jika Anda tidak berniat merubah kredensial masuk.</p>
                                        <div class="row">
                                            <div class="col-md-6 col-12">
                                                <div class="form-group">
                                                    <label for="password">Kata Sandi Baru</label>
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text"><i class="fa fa-key"></i></span>
                                                        </div>
                                                        <input type="password" id="password" name="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" placeholder="Min. 4 karakter kombinasi" maxlength="50">
                                                    </div>
                                                    <?php if (isset($errors['password'])) : ?><div class="invalid-feedback d-block"><?= esc($errors['password']) ?></div><?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-12">
                                                <div class="form-group">
                                                    <label for="password_confirm">Ulangi Kata Sandi Baru</label>
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text"><i class="fa fa-key"></i></span>
                                                        </div>
                                                        <input type="password" id="password_confirm" name="password_confirm" class="form-control <?= isset($errors['password_confirm']) ? 'is-invalid' : '' ?>" placeholder="Konfirmasi kata sandi" maxlength="50">
                                                    </div>
                                                    <?php if (isset($errors['password_confirm'])) : ?><div class="invalid-feedback d-block"><?= esc($errors['password_confirm']) ?></div><?php endif; ?>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-flex justify-content-end mt-2">
                                            <a href="<?= base_url('/') ?>" class="btn btn-light mr-1">Batal Perubahan</a>
                                            <button type="submit" class="btn btn-primary" id="btn-save-profile">
                                                <i class="fa fa-save mr-50"></i> Simpan Profil & Keamanan
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Tab 2: Notifikasi -->
                <div class="tab-pane" id="notif-tab-content" role="tabpanel">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-2">
                                <div>
                                    <h3 class="mb-25">Pengaturan Detail Notifikasi Sistem</h3>
                                    <p class="text-muted text-sm mb-0">Pilih jenis aktivitas proyek dan kanal media penyampaian yang ingin Anda terima.</p>
                                </div>
                                <div class="mt-1 mt-md-0 d-none" id="notif-stats-container">
                                    <div class="badge badge-light-primary p-1 text-sm font-weight-bolder">
                                        <i class="fa fa-sliders mr-50"></i> <span id="notif-stats-text">0 dari 0 aktif</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="notif-preferences-container">
                                <div class="text-center py-3">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                    <p class="mt-1">Memuat preferensi...</p>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-3 border-top pt-2" id="notif-preferences-actions" style="display: none !important;">
                                <button type="button" class="btn btn-outline-danger mr-1" id="btn-reset-preferences">
                                    <i class="fa fa-undo mr-50"></i> Reset ke Default
                                </button>
                                <button type="button" class="btn btn-primary" id="btn-save-preferences">
                                    <i class="fa fa-save mr-50"></i> Simpan Preferensi
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= base_url() ?>app-assets/vendors/js/vendors.min.js"></script>

<script>
    (function () {
        // Photo preview script
        var input = document.getElementById('profile_photo');
        var preview = document.getElementById('profile-photo-preview');

        if (!input || !preview) return;

        input.addEventListener('change', function () {
            var file = input.files && input.files[0];
            if (!file) return;
            preview.src = URL.createObjectURL(file);
            preview.onload = function () {
                URL.revokeObjectURL(preview.src);
            };
        });
        
        // Prevent multiple form submissions
        $('#form-profile').on('submit', function() {
            var btn = $('#btn-save-profile');
            btn.prop('disabled', true);
            btn.html('<i class="fa fa-spinner fa-spin mr-50"></i> Menyimpan...');
        });
    })();

    // Notification Preferences Logic
    $(document).ready(function() {
        const apiUrl = '<?= base_url('api/notif/preferences') ?>';
        let currentPreferences = [];

        function loadPreferences() {
            $.ajax({
                url: apiUrl,
                type: 'GET',
                success: function(res) {
                    if (res.success) {
                        currentPreferences = res.data;
                        renderPreferences(res.data);
                        updateStats();
                    }
                },
                error: function() {
                    $('#notif-preferences-container').html('<div class="alert alert-danger">Gagal memuat preferensi notifikasi.</div>');
                }
            });
        }

        function renderPreferences(data) {
            let html = '';
            const grouped = {};
            
            // Group by category
            data.forEach(pref => {
                if (!grouped[pref.category]) grouped[pref.category] = [];
                grouped[pref.category].push(pref);
            });

            html += '<div class="table-responsive"><table class="table table-borderless table-notif">';
            html += '<thead><tr>';
            html += '<th class="align-middle">TIPE AKTIVITAS & PERISTIWA PROYEK</th>';
            html += '<th class="text-center" width="120"><i class="fa fa-desktop mr-25"></i> IN-APP<br><div class="custom-control custom-checkbox mt-50 d-inline-block"><input type="checkbox" class="custom-control-input select-all-channel" id="selectAllInApp" data-channel="in_app"><label class="custom-control-label" for="selectAllInApp" style="font-size: 0.8rem; text-transform:none;">Semua</label></div></th>';
            html += '<th class="text-center" width="120"><i class="fa fa-envelope mr-25"></i> EMAIL<br><div class="custom-control custom-checkbox mt-50 d-inline-block"><input type="checkbox" class="custom-control-input select-all-channel" id="selectAllEmail" data-channel="email"><label class="custom-control-label" for="selectAllEmail" style="font-size: 0.8rem; text-transform:none;">Semua</label></div></th>';
            html += '<th class="text-center" width="120"><i class="fa fa-bell mr-25"></i> PUSH & WA<br><div class="custom-control custom-checkbox mt-50 d-inline-block"><input type="checkbox" class="custom-control-input select-all-channel" id="selectAllWebPush" data-channel="web_push"><label class="custom-control-label" for="selectAllWebPush" style="font-size: 0.8rem; text-transform:none;">Semua</label></div></th>';
            html += '</tr></thead><tbody>';

            let catIndex = 1;
            for (const [category, prefs] of Object.entries(grouped)) {
                html += `<tr class="category-row"><td colspan="4">${catIndex}. ${category}</td></tr>`;
                
                prefs.forEach(pref => {
                    const disabled = pref.is_locked ? 'disabled' : '';
                    const lockedIcon = pref.is_locked ? ' <i class="fa fa-lock text-muted" title="Dikunci oleh Admin"></i>' : '';
                    
                    html += `<tr>`;
                    html += `<td>
                                <div class="font-weight-bolder text-dark mb-25">${pref.label}${lockedIcon}</div>
                                <div class="text-sm text-muted">${pref.description}</div>
                             </td>`;
                    
                    ['in_app', 'email', 'web_push'].forEach(channel => {
                        const isChecked = pref[`${channel}`] ? 'checked' : '';
                        html += `<td class="text-center">
                                    <div class="custom-control custom-switch custom-control-inline mr-0">
                                        <input type="checkbox" class="custom-control-input pref-checkbox" 
                                            id="pref_${pref.event_type}_${channel}" 
                                            data-event="${pref.event_type}" 
                                            data-channel="${channel}" 
                                            ${isChecked} ${disabled}>
                                        <label class="custom-control-label" for="pref_${pref.event_type}_${channel}"></label>
                                    </div>
                                 </td>`;
                    });
                    
                    html += `</tr>`;
                });
                catIndex++;
            }
            
            html += '</tbody></table></div>';
            
            $('#notif-preferences-container').html(html);
            $('#notif-preferences-actions').show();
            $('#notif-stats-container').removeClass('d-none');
            
            checkSelectAllState();
        }
        
        function checkSelectAllState() {
            ['in_app', 'email', 'web_push'].forEach(channel => {
                const total = $(`.pref-checkbox[data-channel="${channel}"]:not(:disabled)`).length;
                const checked = $(`.pref-checkbox[data-channel="${channel}"]:not(:disabled):checked`).length;
                if (total > 0 && total === checked) {
                    $(`#selectAll${channel === 'in_app' ? 'InApp' : (channel === 'email' ? 'Email' : 'WebPush')}`).prop('checked', true);
                } else {
                    $(`#selectAll${channel === 'in_app' ? 'InApp' : (channel === 'email' ? 'Email' : 'WebPush')}`).prop('checked', false);
                }
            });
        }
        
        function updateStats() {
            const total = $('.pref-checkbox').length;
            const checked = $('.pref-checkbox:checked').length;
            $('#notif-stats-text').text(`${checked} dari ${total} aktif`);
        }

        // Delegate event for Select All checkboxes
        $(document).on('change', '.select-all-channel', function() {
            const channel = $(this).data('channel');
            const isChecked = $(this).is(':checked');
            $(`.pref-checkbox[data-channel="${channel}"]:not(:disabled)`).prop('checked', isChecked);
            updateStats();
        });

        // Delegate event for individual checkboxes
        $(document).on('change', '.pref-checkbox', function() {
            checkSelectAllState();
            updateStats();
        });

        $('#btn-save-preferences').click(function() {
            const btn = $(this);
            const originalHtml = btn.html();
            btn.html('<i class="fa fa-spinner fa-spin mr-50"></i> Menyimpan...').prop('disabled', true);

            const payload = {};
            
            currentPreferences.forEach(pref => {
                if (pref.is_locked) return;
                
                payload[pref.event_type] = {
                    in_app: $(`#pref_${pref.event_type}_in_app`).is(':checked'),
                    email: $(`#pref_${pref.event_type}_email`).is(':checked'),
                    web_push: $(`#pref_${pref.event_type}_web_push`).is(':checked')
                };
            });

            $.ajax({
                url: apiUrl,
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ preferences: payload }),
                success: function(res) {
                    Swal.fire('Berhasil', res.messages, 'success');
                    btn.html(originalHtml).prop('disabled', false);
                },
                error: function(err) {
                    Swal.fire('Gagal', 'Terjadi kesalahan saat menyimpan', 'error');
                    btn.html(originalHtml).prop('disabled', false);
                }
            });
        });

        $('#btn-reset-preferences').click(function() {
            Swal.fire({
                title: 'Reset ke Default?',
                text: "Semua pengaturan notifikasi akan dikembalikan ke bawaan sistem.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Reset!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: apiUrl + '/reset',
                        type: 'POST',
                        success: function(res) {
                            Swal.fire('Berhasil', res.messages, 'success');
                            loadPreferences();
                        },
                        error: function() {
                            Swal.fire('Gagal', 'Terjadi kesalahan saat mereset', 'error');
                        }
                    });
                }
            })
        });

        loadPreferences();
    });
</script>
