<?php
$displayName = old('name', $profile->name ?? '');
if ($displayName === '') {
    $displayName = $profile->username ?? '';
}
?>

<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/extensions/sweetalert2.min.css">

<style>
    .profile-page .profile-photo-frame {
        width: 132px;
        height: 132px;
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid #e5e7eb;
        background: #f8fafc;
    }

    .profile-page .profile-photo-frame img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .profile-page .profile-summary {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        background: #fff;
    }

    .profile-page .profile-summary-label {
        color: #6b7280;
        font-size: .82rem;
        font-weight: 600;
    }

    .profile-page .btn-primary,
    .profile-page .btn-primary:focus,
    .profile-page .btn-primary:hover {
        background-color: #2057a3 !important;
        border-color: #2057a3 !important;
    }
</style>

<div class="app-content content profile-page">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper">
        <div class="content-header row">
            <div class="content-header-left col-md-9 col-12 mb-2">
                <div class="row breadcrumbs-top">
                    <div class="col-12">
                        <h2 class="content-header-title float-left mb-0"><?= esc($title) ?></h2>
                        <div class="breadcrumb-wrapper">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?= base_url('/') ?>">Dashboard</a></li>
                                <li class="breadcrumb-item active">Profil</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="content-body">
            <?php if (session('message')) : ?>
                <div class="alert alert-success" role="alert"><?= esc(session('message')) ?></div>
            <?php endif; ?>

            <?php if (session('error')) : ?>
                <div class="alert alert-danger" role="alert"><?= esc(session('error')) ?></div>
            <?php endif; ?>

            <?php $errors = session('errors') ?? []; ?>

            <div class="row">
                <div class="col-lg-4 col-12 mb-2">
                    <div class="profile-summary p-2 h-100">
                        <div class="divider divider-left">
                            <div class="divider-text">Foto Profil</div>
                        </div>
                        <div class="d-flex flex-column align-items-center text-center">
                            <div class="profile-photo-frame mb-1">
                                <img id="profile-photo-preview" src="<?= esc($photoUrl) ?>" alt="Foto profil">
                            </div>
                            <h5 class="mb-25"><?= esc($displayName) ?></h5>
                            <div class="text-muted"><?= esc($profile->email ?? '-') ?></div>
                        </div>

                        <div class="divider divider-left mt-2">
                            <div class="divider-text">Google Calendar</div>
                        </div>
                        <?php $googleCalendarStatus = $googleCalendarStatus ?? ['connected' => false, 'configured' => false, 'ready' => false, 'message' => '']; ?>
                        <?php if (! empty($googleCalendarStatus['connected'])) : ?>
                            <div class="alert alert-success mb-1" role="alert">
                                Terhubung<?= ! empty($googleCalendarStatus['google_email']) ? ' sebagai ' . esc($googleCalendarStatus['google_email']) : '' ?>.
                            </div>
                            <form action="<?= base_url('google-calendar/disconnect') ?>" method="post">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-outline-danger btn-block">
                                    <i class="fa fa-unlink mr-50"></i> Disconnect Google Calendar
                                </button>
                            </form>
                        <?php else : ?>
                            <?php if (empty($googleCalendarStatus['configured']) || empty($googleCalendarStatus['ready'])) : ?>
                                <div class="alert alert-warning mb-1" role="alert">
                                    <?= esc($googleCalendarStatus['message'] ?? 'Google Calendar belum dikonfigurasi.') ?>
                                </div>
                            <?php else : ?>
                                <div class="alert alert-secondary mb-1" role="alert">
                                    Belum terhubung. Event tiket urgent akan dilewati sampai akun Google Calendar dihubungkan.
                                </div>
                            <?php endif; ?>
                            <a href="<?= base_url('google-calendar/connect') ?>" class="btn btn-outline-primary btn-block <?= (empty($googleCalendarStatus['configured']) || empty($googleCalendarStatus['ready'])) ? 'disabled' : '' ?>">
                                <i class="fa fa-calendar-plus mr-50"></i> Connect Google Calendar
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-lg-8 col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="divider divider-left">
                                <div class="divider-text">Informasi Profil</div>
                            </div>

                            <form action="<?= base_url('profil/update') ?>" method="post" enctype="multipart/form-data">
                                <?= csrf_field() ?>

                                <div class="form-group">
                                    <label for="name">Nama</label>
                                    <input
                                        type="text"
                                        id="name"
                                        name="name"
                                        class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                                        value="<?= esc($displayName) ?>"
                                        maxlength="120"
                                        required>
                                    <?php if (isset($errors['name'])) : ?>
                                        <div class="invalid-feedback"><?= esc($errors['name']) ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input
                                        type="email"
                                        id="email"
                                        name="email"
                                        class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                                        value="<?= old('email', esc($profile->email ?? '')) ?>"
                                        maxlength="255"
                                        required>
                                    <?php if (isset($errors['email'])) : ?>
                                        <div class="invalid-feedback"><?= esc($errors['email']) ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="form-group">
                                    <label for="profile_photo">Foto Profil</label>
                                    <input
                                        type="file"
                                        id="profile_photo"
                                        name="profile_photo"
                                        class="form-control <?= isset($errors['profile_photo']) ? 'is-invalid' : '' ?>"
                                        accept="image/jpeg,image/png,image/webp">
                                    <small class="form-text text-muted">Format JPG, PNG, atau WEBP. Maksimal 2MB.</small>
                                    <?php if (isset($errors['profile_photo'])) : ?>
                                        <div class="invalid-feedback d-block"><?= esc($errors['profile_photo']) ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="divider divider-left">
                                    <div class="divider-text">Pengaturan Notifikasi</div>
                                </div>
                                
                                <div class="form-group">
                                    <div class="custom-control custom-switch custom-control-inline">
                                        <input type="checkbox" class="custom-control-input" id="email_notif_enabled" name="email_notif_enabled" <?= (isset($profile->email_notif_enabled) && $profile->email_notif_enabled) ? 'checked' : '' ?>>
                                        <label class="custom-control-label" for="email_notif_enabled">
                                        Terima Rangkuman Notifikasi via Email
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">Kami akan mengirimkan email rangkuman notifikasi jika Anda tidak sedang aktif di aplikasi.</small>
                                </div>

                                <div class="divider divider-left">
                                    <div class="divider-text">Ubah Password</div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 col-12">
                                        <div class="form-group">
                                            <label for="password">Password Baru</label>
                                            <input
                                                type="password"
                                                id="password"
                                                name="password"
                                                class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                                                placeholder="Kosongkan jika tidak diubah"
                                                maxlength="50">
                                            <?php if (isset($errors['password'])) : ?>
                                                <div class="invalid-feedback"><?= esc($errors['password']) ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-12">
                                        <div class="form-group">
                                            <label for="password_confirm">Konfirmasi Password</label>
                                            <input
                                                type="password"
                                                id="password_confirm"
                                                name="password_confirm"
                                                class="form-control <?= isset($errors['password_confirm']) ? 'is-invalid' : '' ?>"
                                                placeholder="Ulangi password baru"
                                                maxlength="50">
                                            <?php if (isset($errors['password_confirm'])) : ?>
                                                <div class="invalid-feedback"><?= esc($errors['password_confirm']) ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <a href="<?= base_url('/') ?>" class="btn btn-outline-secondary mr-1">Batal</a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fa fa-save mr-50"></i> Simpan Profil
                                    </button>
                                </div>
                            </form>
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
        var input = document.getElementById('profile_photo');
        var preview = document.getElementById('profile-photo-preview');

        if (!input || !preview) {
            return;
        }

        input.addEventListener('change', function () {
            var file = input.files && input.files[0];

            if (!file) {
                return;
            }

            preview.src = URL.createObjectURL(file);
            preview.onload = function () {
                URL.revokeObjectURL(preview.src);
            };
        });
    })();
</script>
