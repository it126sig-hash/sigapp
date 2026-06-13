<!DOCTYPE html>
<html lang="en" data-textdirection="ltr">
<!-- BEGIN: Head-->

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width,initial-scale=1.0,user-scalable=0,minimal-ui">
	<title>Login - SIGAPP</title>
	<link rel="apple-touch-icon" href="<?= base_url() ?>/app-assets/images/ico/apple-icon-120.png">
	<link rel="shortcut icon" type="image/x-icon" href="<?= base_url() ?>/app-assets/images/ico/favicon.ico">

	<!-- BEGIN: Vendor CSS-->
	<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/vendors.min.css">
	<!-- END: Vendor CSS-->

	<!-- BEGIN: Theme CSS-->
	<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/css/bootstrap.css">
	<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/css/bootstrap-extended.css">
	<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/css/colors.css">
	<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/css/components.css">
	<!-- END: Theme CSS-->

	<!-- BEGIN: Custom CSS-->
	<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/assets/css/style.css">
	<!-- END: Custom CSS-->

	<style>
		html, body {
			height: 100%;
		}

		body.login-page {
			font-family: 'Plus Jakarta Sans', sans-serif;
			background: var(--sigapp-light);
			color: var(--sigapp-gray-700);
		}

		.login-wrapper {
			display: flex;
			min-height: 100vh;
		}

		/* ── Brand side ───────────────────────────── */
		.login-aside {
			flex: 1 1 55%;
			position: relative;
			overflow: hidden;
			display: flex;
			flex-direction: column;
			justify-content: space-between;
			padding: 56px;
			background: var(--sigapp-primary-gradient);
			color: #fff;
		}

		.login-aside::before {
			content: '';
			position: absolute;
			top: -120px;
			right: -120px;
			width: 360px;
			height: 360px;
			border-radius: 50%;
			background: rgba(255, 255, 255, 0.08);
		}

		.login-aside::after {
			content: '';
			position: absolute;
			bottom: -140px;
			left: -100px;
			width: 320px;
			height: 320px;
			border-radius: 50%;
			background: rgba(255, 255, 255, 0.06);
		}

		.login-aside-brand {
			display: flex;
			align-items: center;
			gap: 12px;
			position: relative;
			z-index: 1;
		}

		.login-aside-brand img {
			width: 44px;
			height: 44px;
			border-radius: 12px;
			background: rgba(255, 255, 255, 0.9);
			padding: 4px;
		}

		.login-aside-brand span {
			font-size: 1.35rem;
			font-weight: 700;
			letter-spacing: 0.04em;
		}

		.login-aside-content {
			position: relative;
			z-index: 1;
			max-width: 420px;
		}

		.login-aside-content h1 {
			color: #fff;
			font-size: 2rem;
			font-weight: 700;
			line-height: 1.3;
			margin-bottom: 14px;
		}

		.login-aside-content p {
			color: rgba(255, 255, 255, 0.78);
			font-size: 0.95rem;
			line-height: 1.6;
			margin-bottom: 0;
		}

		.login-aside-features {
			list-style: none;
			padding: 0;
			margin: 28px 0 0;
			display: flex;
			flex-direction: column;
			gap: 14px;
			position: relative;
			z-index: 1;
		}

		.login-aside-features li {
			display: flex;
			align-items: center;
			gap: 12px;
			font-size: 0.88rem;
			color: rgba(255, 255, 255, 0.92);
		}

		.login-aside-features li i {
			width: 30px;
			height: 30px;
			flex-shrink: 0;
			border-radius: 9px;
			display: flex;
			align-items: center;
			justify-content: center;
			background: rgba(255, 255, 255, 0.12);
		}

		.login-aside-footer {
			position: relative;
			z-index: 1;
			font-size: 0.78rem;
			color: rgba(255, 255, 255, 0.6);
		}

		/* ── Form side ────────────────────────────── */
		.login-main {
			flex: 1 1 45%;
			display: flex;
			align-items: center;
			justify-content: center;
			padding: 40px 24px;
			background: #fff;
		}

		.login-card {
			width: 100%;
			max-width: 400px;
		}

		.login-mobile-brand {
			display: none;
			align-items: center;
			gap: 10px;
			margin-bottom: 32px;
		}

		.login-mobile-brand img {
			width: 38px;
			height: 38px;
			border-radius: 10px;
		}

		.login-mobile-brand span {
			font-size: 1.15rem;
			font-weight: 700;
			color: var(--sigapp-dark);
		}

		.login-card h2 {
			font-size: 1.5rem;
			font-weight: 700;
			color: var(--sigapp-dark);
			margin-bottom: 6px;
		}

		.login-card .login-subtitle {
			color: var(--sigapp-secondary);
			font-size: 0.88rem;
			margin-bottom: 28px;
		}

		.login-form-group {
			margin-bottom: 18px;
		}

		.login-form-group label {
			font-size: 0.78rem;
			font-weight: 600;
			color: var(--sigapp-gray-700);
			margin-bottom: 6px;
			display: block;
		}

		.login-input-group {
			position: relative;
		}

		.login-input-group i,
		.login-input-group > svg {
			position: absolute;
			top: 50%;
			left: 14px;
			transform: translateY(-50%);
			color: var(--sigapp-gray-500);
			pointer-events: none;
		}

		.login-input-group .form-control {
			height: 46px;
			padding-left: 42px;
			font-size: 0.9rem;
			border: 1.5px solid var(--border);
		}

		.login-input-group .toggle-password {
			position: absolute;
			top: 50%;
			right: 14px;
			transform: translateY(-50%);
			color: var(--sigapp-gray-500);
			background: none;
			border: none;
			padding: 0;
			cursor: pointer;
			display: flex;
			align-items: center;
		}

		.login-input-group .toggle-password:hover {
			color: var(--sigapp-primary-solid);
		}

		.login-input-group.has-icon-right .form-control {
			padding-right: 42px;
		}

		.login-remember {
			display: flex;
			align-items: center;
			justify-content: space-between;
			margin-bottom: 24px;
		}

		.login-remember .form-check-label {
			font-size: 0.85rem;
			color: var(--sigapp-gray-700);
		}

		.login-submit {
			height: 46px;
			font-weight: 700;
			font-size: 0.92rem;
			letter-spacing: 0.02em;
		}

		@media (max-width: 991.98px) {
			.login-aside {
				display: none;
			}

			.login-mobile-brand {
				display: flex;
			}

			.login-main {
				padding: 32px 20px;
			}
		}
	</style>

</head>
<!-- END: Head-->

<!-- BEGIN: Body-->

<body class="login-page">
	<div class="login-wrapper">
		<!-- Brand side -->
		<div class="login-aside">
			<div class="login-aside-brand">
				<img src="<?= base_url('images/logo.png') ?>" alt="SIGAPP">
				<span>SIGAPP</span>
			</div>
			<div class="login-aside-content">
				<h1>Kelola proyek properti Anda lebih mudah</h1>
				<p>Satu platform terintegrasi untuk mengelola kavling, transaksi, keuangan, dan dokumen proyek perumahan Anda.</p>
				<ul class="login-aside-features">
					<li><i data-feather="check-circle"></i> Pantau status kavling &amp; penjualan secara real-time</li>
					<li><i data-feather="check-circle"></i> Kelola transaksi dan riwayat pembayaran konsumen</li>
					<li><i data-feather="check-circle"></i> Akses laporan keuangan dan legalitas proyek</li>
				</ul>
			</div>
			<div class="login-aside-footer">
				&copy; <?= date('Y') ?> SIGAPP. All rights reserved.
			</div>
		</div>
		<!-- /Brand side -->

		<!-- Form side -->
		<div class="login-main">
			<div class="login-card">
				<div class="login-mobile-brand">
					<img src="<?= base_url('images/logo.png') ?>" alt="SIGAPP">
					<span>SIGAPP</span>
				</div>

				<h2>Selamat Datang 👋</h2>
				<p class="login-subtitle">Masuk untuk melanjutkan ke akun Anda</p>

				<?= view('Myth\Auth\Views\_message_block') ?>

				<form class="auth-login-form" action="<?= base_url('login') ?>" method="post">
					<?= csrf_field() ?>

					<?php if ($config->validFields === ['email']) : ?>
						<div class="login-form-group">
							<label for="login"><?= lang('Auth.email') ?></label>
							<div class="login-input-group">
								<i data-feather="mail"></i>
								<input type="email" id="login" class="form-control <?php if (session('errors.login')) : ?>is-invalid<?php endif ?>" name="login" placeholder="<?= lang('Auth.email') ?>">
							</div>
							<div class="invalid-feedback d-block">
								<?= session('errors.login') ?>
							</div>
						</div>
					<?php else : ?>
						<div class="login-form-group">
							<label for="login"><?= lang('Auth.emailOrUsername') ?></label>
							<div class="login-input-group">
								<i data-feather="user"></i>
								<input type="text" id="login" class="form-control <?php if (session('errors.login')) : ?>is-invalid<?php endif ?>" name="login" placeholder="<?= lang('Auth.emailOrUsername') ?>">
							</div>
							<div class="invalid-feedback d-block">
								<?= session('errors.login') ?>
							</div>
						</div>
					<?php endif; ?>

					<div class="login-form-group">
						<label for="password"><?= lang('Auth.password') ?></label>
						<div class="login-input-group has-icon-right">
							<i data-feather="lock"></i>
							<input type="password" id="password" name="password" class="form-control <?php if (session('errors.password')) : ?>is-invalid<?php endif ?>" placeholder="<?= lang('Auth.password') ?>">
							<button type="button" class="toggle-password" data-target="password" aria-label="Show password">
								<i data-feather="eye"></i>
							</button>
						</div>
						<div class="invalid-feedback d-block">
							<?= session('errors.password') ?>
						</div>
					</div>

					<?php if ($config->allowRemembering) : ?>
						<div class="login-remember">
							<div class="form-check">
								<label class="form-check-label">
									<input type="checkbox" name="remember" class="form-check-input" <?php if (old('remember')) : ?> checked <?php endif ?>>
									<?= lang('Auth.rememberMe') ?>
								</label>
							</div>
						</div>
					<?php endif; ?>

					<button type="submit" class="btn btn-primary btn-block login-submit"><?= lang('Auth.loginAction') ?></button>
				</form>

			</div>
		</div>
		<!-- /Form side -->
	</div>

	<!-- BEGIN: Vendor JS-->
	<script src="<?= base_url() ?>/app-assets/vendors/js/vendors.min.js"></script>
	<!-- END: Vendor JS-->

	<!-- BEGIN: Page Vendor JS-->
	<script src="<?= base_url() ?>/app-assets/vendors/js/forms/validation/jquery.validate.min.js"></script>
	<!-- END: Page Vendor JS-->

	<!-- BEGIN: Page JS-->
	<script src="<?= base_url() ?>/app-assets/js/scripts/pages/page-auth-login.js"></script>
	<!-- END: Page JS-->

	<script>
		$(function () {
			if (feather) {
				feather.replace({
					width: 16,
					height: 16
				});
			}

			$('.toggle-password').on('click', function () {
				var input = $('#' + $(this).data('target'));
				var icon = $(this).find('i');
				var isPassword = input.attr('type') === 'password';

				input.attr('type', isPassword ? 'text' : 'password');
				icon.attr('data-feather', isPassword ? 'eye-off' : 'eye');
				feather.replace({ width: 16, height: 16 });
			});
		});
	</script>
</body>
<!-- END: Body-->

</html>
