<!DOCTYPE html>
<html lang="en" data-textdirection="ltr">
<!-- BEGIN: Head-->

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width,initial-scale=1.0,user-scalable=0,minimal-ui">
	<meta name="theme-color" content="#2057a3">
	<meta name="mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-title" content="SIGAPP">
	<meta name="apple-mobile-web-app-status-bar-style" content="default">
	<title>Login - SIGAPP</title>
	<link rel="manifest" href="<?= base_url('manifest.webmanifest') ?>">
	<link rel="apple-touch-icon" href="<?= base_url('assets/images/pwa/apple-touch-icon.png') ?>">
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
			margin-top: 12px;
		}

		.login-mobile-footer {
			display: none;
		}

		@media (max-width: 991.98px) {
			body.login-page {
				background-color: #f6f8fb;
				background-image: 
					url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 200 200'%3E%3Cpath fill='%232e7cb6' d='M100,0 C150,50 200,20 200,100 L200,0 Z'/%3E%3Cpath fill='%23195f9c' d='M150,0 C180,30 200,10 200,70 L200,0 Z'/%3E%3C/svg%3E"),
					url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 200 200'%3E%3Cpath fill='%23ef8a2a' d='M0,200 C50,150 20,100 100,200 Z'/%3E%3Cpath fill='%23d67920' d='M0,200 C30,170 10,130 60,200 Z'/%3E%3C/svg%3E");
				background-position: top right, bottom left;
				background-repeat: no-repeat;
				background-size: 250px, 250px;
				background-attachment: fixed;
			}

			.login-aside {
				display: none;
			}
			
			.login-main {
				background: transparent;
				padding: 20px;
			}

			.login-card {
				background: #fff;
				border-radius: 16px;
				padding: 36px 24px;
				box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
			}

			.login-mobile-brand {
				display: flex;
				flex-direction: column;
				align-items: center;
				margin-bottom: 24px;
			}

			.login-mobile-brand img {
				display: block;
				width: 72px;
				height: auto;
				border-radius: 0;
			}

			.login-mobile-brand .brand-sigapp {
				font-size: 1.9rem;
				font-weight: 900;
				color: #1e5fa3;
				line-height: 1;
				letter-spacing: 1px;
			}

			.login-card h2 {
				text-align: center;
				font-size: 1.3rem;
				margin-bottom: 8px;
			}

			.login-card .login-subtitle {
				text-align: center;
				font-size: 0.85rem;
				color: #777;
				margin-bottom: 32px;
			}

			.login-form-group label {
				color: #5a7395;
				font-weight: 700;
				font-size: 0.8rem;
			}

			.login-input-group .form-control {
				border-radius: 8px;
				border: 1px solid #dce3eb;
				height: 50px;
			}

			.login-submit {
				border-radius: 8px;
				background: linear-gradient(90deg, #24649c, #2b84be);
				border: none;
				margin-top: 24px;
				height: 50px;
			}

			.login-mobile-footer {
				display: block;
				text-align: center;
				font-size: 0.7rem;
				color: #888;
				margin-top: 32px;
				padding-top: 16px;
				border-top: 1px solid #f0f0f0;
			}

			.login-mobile-footer .text-primary {
				color: #1e5fa3;
				font-weight: 700;
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
					<div class="brand-sigapp">SIGAPP</div>
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
						<input type="hidden" name="remember" value="1">
					<?php endif; ?>

					<button type="submit" class="btn btn-primary btn-block login-submit"><?= lang('Auth.loginAction') ?></button>
				</form>

				<div class="login-mobile-footer">
					&copy; <?= date('Y') ?> <span class="text-primary">SIGAPP</span>. All rights reserved.
				</div>
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
		window.SIGAPP_PWA = {
			serviceWorkerUrl: "<?= base_url('sw.js') ?>",
			serviceWorkerScope: "<?= base_url() ?>"
		};
	</script>
	<script src="<?= base_url('assets/js/pwa-install.js') ?>?<?= filemtime(FCPATH . 'assets/js/pwa-install.js') ?>"></script>

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
