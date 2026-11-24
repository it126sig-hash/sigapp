<?php
echo view('template/header');
echo view('template/menu');

$signaturesByRole = [];
foreach ($bpb['signatures'] ?? [] as $sig) {
    if (!empty($sig['revoked'])) {
        continue;
    }
    $signaturesByRole[$sig['role']] = $sig;
}

$applicantSig = $signaturesByRole['applicant'] ?? null;
$approverSig  = $signaturesByRole['approver'] ?? ($signaturesByRole['cc'] ?? null);
$verifierSig  = $signaturesByRole['expense_verifier'] ?? null;

$requestFiles  = array_filter($bpb['files'] ?? [], fn($f) => ($f['category'] ?? '') === 'request');
$purchaseFiles = array_filter($bpb['files'] ?? [], fn($f) => ($f['category'] ?? '') === 'purchase');

$statusTone = 'bpb-status--' . preg_replace('/[^a-z0-9]+/', '-', strtolower((string) ($bpb['status'] ?? 'draft')));
$applicantInitials = strtoupper(substr(trim((string) ($bpb['applicant_name'] ?? '?')), 0, 2));
?>

<link rel="stylesheet" type="text/css" href="<?= base_url('app-assets/vendors/css/extensions/sweetalert2.min.css') ?>">
<link rel="stylesheet" type="text/css" href="<?= base_url('assets/css/bpb.css') ?>?v=<?= filemtime(FCPATH . 'assets/css/bpb.css') ?>">

<div class="app-content content bpb-page bpb-shared-page">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper">
        <div class="content-header row mb-1">
            <div class="content-header-left col-md-7 col-12 mb-50 mb-md-0">
                <div class="row breadcrumbs-top">
                    <div class="col-12">
                        <h4 class="content-header-title float-left pr-1 mb-0 text-primary">
                            <i class="fa fa-file-text-o mr-50"></i>Lembar Bon Permintaan Barang (BPB)
                        </h4>
                        <div class="breadcrumb-wrapper">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?= base_url('bpb') ?>">BPB</a></li>
                                <li class="breadcrumb-item active"><?= esc(($bpb['nomor'] ?? '') !== '' ? $bpb['nomor'] : ('#' . ($bpb['id'] ?? ''))) ?></li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-header-right col-md-5 col-12 text-md-right">
                <div class="bpb-shared-actions">
                    <a href="<?= base_url('bpb') ?>" class="btn btn-outline-secondary btn-sm">
                        <i class="fa fa-arrow-left mr-50"></i>Kembali
                    </a>
                    <button type="button" class="btn btn-outline-info btn-sm font-weight-bold" id="bpb-btn-copy-share" data-share-url="<?= esc($bpb['share_url'] ?? site_url('bpb/shared/' . ($bpb['share_token'] ?? ''))) ?>">
                        <i class="fa fa-share-alt mr-50"></i>Salin Link
                    </button>
                    <a href="<?= esc($bpb['pdf_url'] ?? site_url('bpb/' . ($bpb['id'] ?? 0) . '/pdf')) ?>" target="_blank" class="btn btn-outline-danger btn-sm font-weight-bold">
                        <i class="fa fa-file-pdf mr-50"></i>Unduh PDF
                    </a>
                </div>
            </div>
        </div>

        <div class="content-body">
            <!-- Card 1: Ringkasan BPB & Pemohon -->
            <div class="card mb-2">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-center justify-content-between mb-1 pb-50 border-bottom">
                        <div>
                            <span class="bpb-shared-watermark mb-50">
                                <i class="fa fa-shield mr-25"></i> Dokumen Elektronik Sah Internal SIGAPP
                            </span>
                            <div class="d-flex align-items-center flex-wrap mt-25">
                                <h2 class="bpb-number font-weight-bold mr-1 mb-0 text-dark">
                                    <?= esc(($bpb['nomor'] ?? '') !== '' ? $bpb['nomor'] : ('Draft #' . ($bpb['id'] ?? ''))) ?>
                                </h2>
                                <span class="badge bpb-status-badge <?= esc($statusTone) ?> font-medium-1 px-75 py-50">
                                    <i class="fa fa-dot-circle-o mr-25"></i><?= esc($bpb['status_label'] ?? ucfirst($bpb['status'] ?? 'Draft')) ?>
                                </span>
                            </div>
                        </div>
                        <div class="bpb-applicant mt-1 mt-md-0">
                            <span class="bpb-applicant-avatar"><?= esc($applicantInitials) ?></span>
                            <span>
                                <small>Diajukan Oleh</small>
                                <strong><?= esc($bpb['applicant_name'] ?? '-') ?></strong>
                                <em><?= esc($bpb['applicant_department'] ?? '-') ?></em>
                            </span>
                        </div>
                    </div>

                    <div class="row text-secondary font-small-3">
                        <div class="col-md-4 col-sm-6 col-12 mb-50">
                            <i class="fa fa-calendar mr-50 text-primary"></i> Tanggal Pengajuan:
                            <strong class="text-dark ml-25">
                                <?= !empty($bpb['submitted_at']) ? date('d M Y H:i', strtotime($bpb['submitted_at'])) : (!empty($bpb['created_at']) ? date('d M Y H:i', strtotime($bpb['created_at'])) : '-') ?>
                            </strong>
                        </div>
                        <div class="col-md-4 col-sm-6 col-12 mb-50">
                            <i class="fa fa-refresh mr-50 text-primary"></i> Terakhir Diperbarui:
                            <strong class="text-dark ml-25">
                                <?= !empty($bpb['updated_at']) ? date('d M Y H:i', strtotime($bpb['updated_at'])) : '-' ?>
                            </strong>
                        </div>
                        <div class="col-md-4 col-12 mb-50">
                            <i class="fa fa-lock mr-50 text-primary"></i> Hash Dokumen:
                            <code class="font-small-2 ml-25 text-truncate d-inline-block" style="max-width: 220px; vertical-align: bottom;" title="<?= esc($bpb['current_document_hash'] ?? '-') ?>">
                                <?= esc($bpb['current_document_hash'] ?? 'Belum ada hash') ?>
                            </code>
                        </div>
                    </div>

                    <?php if (!empty($bpb['rejection_reason'])): ?>
                        <div class="alert alert-danger py-75 px-1 mt-1 font-small-3 mb-0" role="alert">
                            <i class="fa fa-times-circle mr-50"></i> <strong>Alasan Penolakan:</strong> <?= esc($bpb['rejection_reason']) ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($bpb['pending_reason'])): ?>
                        <div class="alert alert-warning py-75 px-1 mt-1 font-small-3 mb-0" role="alert">
                            <i class="fa fa-exclamation-triangle mr-50"></i> <strong>Alasan Pending:</strong> <?= esc($bpb['pending_reason']) ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($bpb['cancellation_reason'])): ?>
                        <div class="alert alert-secondary py-75 px-1 mt-1 font-small-3 mb-0" role="alert">
                            <i class="fa fa-ban mr-50"></i> <strong>Alasan Pembatalan:</strong> <?= esc($bpb['cancellation_reason']) ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($bpb['actual_amount']) || !empty($bpb['disbursed_at'])): ?>
                        <div class="mt-1 pt-1 border-top">
                            <div class="row align-items-center">
                                <div class="col-md-3 col-sm-6 col-12 mb-50 mb-md-0">
                                    <small class="text-muted d-block text-uppercase font-small-1 font-weight-bold">Nominal Pencairan</small>
                                    <span class="font-medium-3 font-weight-bolder text-success">
                                        Rp <?= number_format((float) ($bpb['actual_amount'] ?? 0), 0, ',', '.') ?>
                                    </span>
                                </div>
                                <div class="col-md-3 col-sm-6 col-12 mb-50 mb-md-0">
                                    <small class="text-muted d-block text-uppercase font-small-1 font-weight-bold">Penerima Dana</small>
                                    <strong class="text-dark font-small-3"><?= esc($bpb['recipient_name'] ?? '-') ?></strong>
                                </div>
                                <div class="col-md-3 col-sm-6 col-12 mb-50 mb-md-0">
                                    <small class="text-muted d-block text-uppercase font-small-1 font-weight-bold">No. Rekening Tujuan</small>
                                    <strong class="text-dark font-small-3"><?= esc($bpb['account_number'] ?? '-') ?></strong>
                                </div>
                                <div class="col-md-3 col-sm-6 col-12">
                                    <small class="text-muted d-block text-uppercase font-small-1 font-weight-bold">Waktu Pencairan</small>
                                    <span class="text-muted font-small-2">
                                        <?= !empty($bpb['disbursed_at']) ? date('d M Y H:i', strtotime($bpb['disbursed_at'])) : '-' ?>
                                    </span>
                                </div>
                            </div>
                            <?php if (!empty($bpb['disbursement_note'])): ?>
                                <p class="text-secondary font-small-2 font-italic mt-50 mb-0">
                                    <i class="fa fa-sticky-note-o mr-25"></i> <strong>Catatan Pencairan:</strong> "<?= esc($bpb['disbursement_note']) ?>"
                                </p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($bpb['purchase_date']) || !empty($bpb['purchase_note'])): ?>
                        <div class="mt-1 pt-1 border-top">
                            <div class="row align-items-center">
                                <div class="col-md-4 col-sm-6 col-12 mb-50 mb-md-0">
                                    <small class="text-muted d-block text-uppercase font-small-1 font-weight-bold">Tanggal Pembelian</small>
                                    <strong class="text-dark font-small-3">
                                        <?= !empty($bpb['purchase_date']) ? date('d M Y', strtotime($bpb['purchase_date'])) : '-' ?>
                                    </strong>
                                </div>
                                <div class="col-md-8 col-sm-6 col-12">
                                    <small class="text-muted d-block text-uppercase font-small-1 font-weight-bold">Keterangan Belanja</small>
                                    <span class="text-dark font-small-3"><?= esc($bpb['purchase_note'] ?? '-') ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Card 2: Rantai Persetujuan & Tanda Tangan (3 Kolom Responsif) -->
            <div class="card mb-2">
                <div class="card-header border-bottom py-1">
                    <h5 class="card-title font-weight-bold mb-0 text-dark">
                        <i class="fa fa-pencil-square-o mr-50 text-primary"></i>Rantai Persetujuan & Tanda Tangan Digital
                    </h5>
                </div>
                <div class="card-body pt-1 pb-1">
                    <div class="row">
                        <!-- Kolom 1: Pemohon -->
                        <div class="col-md-4 col-12 mb-1 mb-md-0">
                            <div class="bpb-sign-card">
                                <div class="bpb-sign-card-head">
                                    <span>Diajukan Oleh</span>
                                    <span class="badge badge-light-primary font-small-1">Pemohon</span>
                                </div>
                                <div class="bpb-sign-box">
                                    <?php if ($applicantSig && !empty($applicantSig['image_url'])): ?>
                                        <img src="<?= esc($applicantSig['image_url']) ?>" alt="TTD Pemohon">
                                    <?php else: ?>
                                        <span class="text-muted font-small-2"><i class="fa fa-check-circle text-success mr-25"></i>Tanda tangan sistem</span>
                                    <?php endif; ?>
                                </div>
                                <div class="bpb-sign-footer">
                                    <span class="bpb-sign-name"><?= esc($bpb['applicant_name'] ?? '-') ?></span>
                                    <span class="bpb-sign-dept"><?= esc($bpb['applicant_department'] ?? '-') ?></span>
                                    <span class="bpb-sign-date">
                                        <?= !empty($applicantSig['signed_at']) ? date('d M Y H:i', strtotime($applicantSig['signed_at'])) : (!empty($bpb['submitted_at']) ? date('d M Y H:i', strtotime($bpb['submitted_at'])) : '-') ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Kolom 2: Approver / Mengetahui -->
                        <div class="col-md-4 col-12 mb-1 mb-md-0">
                            <div class="bpb-sign-card">
                                <div class="bpb-sign-card-head">
                                    <span>Diperiksa & Disetujui</span>
                                    <?php if ($approverSig && !empty($approverSig['image_url'])): ?>
                                        <span class="badge bpb-status-badge bpb-status--approved font-small-1">Disetujui</span>
                                    <?php elseif (in_array(($bpb['status'] ?? ''), ['rejected', 'cancelled'])): ?>
                                        <span class="badge bpb-status-badge bpb-status--rejected font-small-1"><?= esc($bpb['status_label'] ?? 'Ditolak') ?></span>
                                    <?php else: ?>
                                        <span class="badge badge-light-warning font-small-1">Menunggu ACC</span>
                                    <?php endif; ?>
                                </div>
                                <div class="bpb-sign-box">
                                    <?php if ($approverSig && !empty($approverSig['image_url'])): ?>
                                        <img src="<?= esc($approverSig['image_url']) ?>" alt="TTD Approver">
                                    <?php elseif (!empty($bpb['actions']['sign'])): ?>
                                        <div class="text-center p-50">
                                            <p class="text-muted font-small-1 mb-50">Menunggu persetujuan Anda sebagai Approver.</p>
                                            <div class="d-flex justify-content-center flex-wrap" style="gap: .35rem;">
                                                <button type="button" class="btn btn-primary btn-xs font-weight-bold" data-bpb-action="sign">
                                                    <i class="fa fa-pencil mr-25"></i> ACC (TTD)
                                                </button>
                                                <?php if (!empty($bpb['actions']['reject'])): ?>
                                                    <button type="button" class="btn btn-outline-danger btn-xs font-weight-bold" data-bpb-action="reject">
                                                        <i class="fa fa-times mr-25"></i> Tolak
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center p-50">
                                            <span class="text-muted font-small-2 d-block">Belum ditandatangani</span>
                                            <small class="text-muted">Menunggu persetujuan approver</small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="bpb-sign-footer">
                                    <span class="bpb-sign-name">
                                        <?= esc($approverSig['signer_name'] ?? ($bpb['approver_name'] ?? 'Approver')) ?>
                                    </span>
                                    <span class="bpb-sign-dept"><?= esc($approverSig['signer_department'] ?? '-') ?></span>
                                    <span class="bpb-sign-date">
                                        <?= !empty($approverSig['signed_at']) ? date('d M Y H:i', strtotime($approverSig['signed_at'])) : '-' ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Kolom 3: Departemen UMUM -->
                        <div class="col-md-4 col-12">
                            <div class="bpb-sign-card">
                                <div class="bpb-sign-card-head">
                                    <span>Departemen UMUM</span>
                                    <?php if (($bpb['status'] ?? '') === 'purchased'): ?>
                                        <span class="badge bpb-status-badge bpb-status--purchased font-small-1">Selesai Belanja</span>
                                    <?php elseif (($bpb['status'] ?? '') === 'disbursed'): ?>
                                        <span class="badge bpb-status-badge bpb-status--disbursed font-small-1">Sudah Cair</span>
                                    <?php elseif (($bpb['status'] ?? '') === 'pending'): ?>
                                        <span class="badge bpb-status-badge bpb-status--pending font-small-1">Pending</span>
                                    <?php elseif (($bpb['status'] ?? '') === 'approved'): ?>
                                        <span class="badge badge-light-primary font-small-1">Siap Dicairkan</span>
                                    <?php else: ?>
                                        <span class="badge badge-light-secondary font-small-1">Menunggu Tahap ACC</span>
                                    <?php endif; ?>
                                </div>
                                <div class="bpb-sign-box">
                                    <?php if (($bpb['status'] ?? '') === 'purchased' && $verifierSig && !empty($verifierSig['image_url'])): ?>
                                        <img src="<?= esc($verifierSig['image_url']) ?>" alt="TTD Verifikator">
                                    <?php elseif (!empty($bpb['actions']['disburse'])): ?>
                                        <div class="text-center p-50">
                                            <p class="text-muted font-small-1 mb-50">Dokumen telah di-ACC. Siap dicairkan.</p>
                                            <button type="button" class="btn btn-success btn-xs font-weight-bold" data-bpb-action="disburse">
                                                <i class="fa fa-money mr-25"></i> Tandai Sudah Cair
                                            </button>
                                        </div>
                                    <?php elseif (!empty($bpb['actions']['finish'])): ?>
                                        <div class="text-center p-50">
                                            <p class="text-muted font-small-1 mb-50">Dana telah cair. Masukkan nota & verifikasi belanja.</p>
                                            <button type="button" class="btn btn-primary btn-xs font-weight-bold" data-bpb-action="finish">
                                                <i class="fa fa-check-square-o mr-25"></i> Verifikasi Pengeluaran
                                            </button>
                                        </div>
                                    <?php elseif (($bpb['status'] ?? '') === 'disbursed'): ?>
                                        <div class="text-center p-50">
                                            <span class="text-info font-small-2 d-block font-weight-bold"><i class="fa fa-check-circle mr-25"></i> Dana Sudah Cair</span>
                                            <small class="text-muted">Menunggu realisasi belanja & nota</small>
                                        </div>
                                    <?php elseif (($bpb['status'] ?? '') === 'approved'): ?>
                                        <div class="text-center p-50">
                                            <span class="text-primary font-small-2 d-block font-weight-bold"><i class="fa fa-hourglass-half mr-25"></i> Siap Dicairkan</span>
                                            <small class="text-muted">Menunggu proses kas oleh Dept Umum</small>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center p-50">
                                            <span class="text-muted font-small-2 d-block"><i class="fa fa-lock mr-25"></i> Menunggu Tahap ACC</span>
                                            <small class="text-muted">Diproses setelah approver ACC</small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="bpb-sign-footer">
                                    <span class="bpb-sign-name">
                                        <?= esc($verifierSig['signer_name'] ?? 'Verifikator UMUM') ?>
                                    </span>
                                    <span class="bpb-sign-dept"><?= esc($verifierSig['signer_department'] ?? 'Departemen UMUM') ?></span>
                                    <span class="bpb-sign-date">
                                        <?= !empty($verifierSig['signed_at']) ? date('d M Y H:i', strtotime($verifierSig['signed_at'])) : '-' ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 3: Daftar Barang yang Diajukan -->
            <div class="card mb-2">
                <div class="card-header border-bottom py-1">
                    <h5 class="card-title font-weight-bold mb-0 text-dark">
                        <i class="fa fa-list mr-50 text-primary"></i>Barang yang Diajukan (<?= count($bpb['items'] ?? []) ?> Item)
                    </h5>
                </div>
                <div class="card-body p-0">
                    <!-- Tampilan Desktop: Tabel -->
                    <div class="table-responsive d-none d-md-block">
                        <table class="table table-bordered table-hover mb-0 bpb-detail-items">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 45px;" class="text-center">No</th>
                                    <th>Deskripsi / Nama Barang</th>
                                    <th class="text-center" style="width: 140px;">Jumlah</th>
                                    <th>Keterangan / Spesifikasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($bpb['items'])): ?>
                                    <?php foreach ($bpb['items'] as $idx => $it): ?>
                                        <tr>
                                            <td class="text-center font-weight-bold"><?= $idx + 1 ?></td>
                                            <td>
                                                <span class="bpb-item-dot"></span>
                                                <strong><?= esc($it['nama_barang'] ?? '-') ?></strong>
                                            </td>
                                            <td class="text-center text-nowrap font-weight-bold">
                                                <?= esc((string) ($it['jumlah'] ?? 0)) ?> <?= esc($it['satuan'] ?? '') ?>
                                            </td>
                                            <td class="text-secondary">
                                                <?= esc(($it['keterangan'] ?? '') !== '' ? $it['keterangan'] : '-') ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-2">Belum ada item barang dalam BPB ini.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Tampilan Mobile: Kartu Item -->
                    <div class="d-block d-md-none p-1">
                        <div class="bpb-mobile-items-list">
                            <?php if (!empty($bpb['items'])): ?>
                                <?php foreach ($bpb['items'] as $idx => $it): ?>
                                    <div class="bpb-item-mobile-card">
                                        <div class="d-flex align-items-start justify-content-between">
                                            <div class="d-flex align-items-start mr-50 text-wrap" style="min-width: 0;">
                                                <span class="badge badge-light-primary font-small-1 mr-50 font-weight-bold flex-shrink-0" style="margin-top: 2px;">#<?= $idx + 1 ?></span>
                                                <strong class="text-dark font-small-3" style="word-break: break-word;"><?= esc($it['nama_barang'] ?? '-') ?></strong>
                                            </div>
                                            <span class="badge badge-primary font-small-2 px-75 py-25 text-nowrap flex-shrink-0 ml-50 font-weight-bold">
                                                <?= esc((string) ($it['jumlah'] ?? 0)) ?> <?= esc($it['satuan'] ?? '') ?>
                                            </span>
                                        </div>
                                        <?php if (!empty(trim((string) ($it['keterangan'] ?? '')))): ?>
                                            <div class="bpb-item-mobile-desc font-small-2 text-muted">
                                                <i class="fa fa-info-circle mr-25 text-muted"></i>
                                                <?= esc(trim((string) $it['keterangan'])) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center text-muted py-2">Belum ada item barang dalam BPB ini.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 4: Lampiran Dokumen & Bukti Pembelian -->
            <div class="card mb-2">
                <div class="card-header border-bottom py-1">
                    <h5 class="card-title font-weight-bold mb-0 text-dark">
                        <i class="fa fa-paperclip mr-50 text-primary"></i>Berkas Lampiran & Dokumen Pendukung
                    </h5>
                </div>
                <div class="card-body">
                    <div class="divider divider-left mb-1">
                        <div class="divider-text">Lampiran Pengajuan Awal</div>
                    </div>
                    <?php if (!empty($requestFiles)): ?>
                        <div class="bpb-attachments mb-2">
                            <?php $reqImgIdx = 0; ?>
                            <?php foreach ($requestFiles as $rf): ?>
                                <?php $isImg = str_starts_with(strtolower((string) ($rf['mime_type'] ?? '')), 'image/'); ?>
                                <?php if ($isImg): ?>
                                    <button type="button" class="bpb-image-thumb" data-bpb-image-group="request" data-bpb-image-index="<?= $reqImgIdx++ ?>" aria-label="Lihat <?= esc($rf['original_name'] ?? '') ?>">
                                        <img src="<?= esc($rf['thumbnail_url'] ?? $rf['url']) ?>" alt="">
                                        <span><?= esc($rf['original_name'] ?? 'Foto Lampiran') ?></span>
                                    </button>
                                <?php else: ?>
                                    <a class="bpb-file-pill" target="_blank" rel="noopener" href="<?= esc($rf['url']) ?>">
                                        <i class="fa fa-file-pdf-o"></i> <?= esc($rf['original_name'] ?? 'Dokumen PDF') ?>
                                    </a>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted font-small-2 mb-2">Tidak ada lampiran pengajuan awal.</p>
                    <?php endif; ?>

                    <div class="divider divider-left mb-1">
                        <div class="divider-text">Bukti Nota Pembelian / Belanja (Departemen UMUM)</div>
                    </div>
                    <?php if (!empty($purchaseFiles)): ?>
                        <div class="bpb-attachments">
                            <?php $purImgIdx = 0; ?>
                            <?php foreach ($purchaseFiles as $pf): ?>
                                <?php $isImg = str_starts_with(strtolower((string) ($pf['mime_type'] ?? '')), 'image/'); ?>
                                <?php if ($isImg): ?>
                                    <button type="button" class="bpb-image-thumb" data-bpb-image-group="purchase" data-bpb-image-index="<?= $purImgIdx++ ?>" aria-label="Lihat <?= esc($pf['original_name'] ?? '') ?>">
                                        <img src="<?= esc($pf['thumbnail_url'] ?? $pf['url']) ?>" alt="">
                                        <span><?= esc($pf['original_name'] ?? 'Nota Pembelian') ?></span>
                                    </button>
                                <?php else: ?>
                                    <a class="bpb-file-pill" target="_blank" rel="noopener" href="<?= esc($pf['url']) ?>">
                                        <i class="fa fa-file-pdf-o"></i> <?= esc($pf['original_name'] ?? 'Nota PDF') ?>
                                    </a>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted font-small-2 mb-0">Belum ada berkas nota atau bukti pembelian.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Card 5: Riwayat Status & Audit Trail -->
            <div class="card mb-2">
                <div class="card-header border-bottom py-1">
                    <h5 class="card-title font-weight-bold mb-0 text-dark">
                        <i class="fa fa-history mr-50 text-primary"></i>Riwayat Status & Audit Trail (<?= count($bpb['history'] ?? []) ?> Aktivitas)
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($bpb['history'])): ?>
                        <div class="bpb-timeline">
                            <?php $histCount = count($bpb['history']); ?>
                            <?php foreach ($bpb['history'] as $hIdx => $h): ?>
                                <?php
                                    $hTone = 'bpb-status--' . preg_replace('/[^a-z0-9]+/', '-', strtolower((string) ($h['to_status'] ?? 'draft')));
                                    $isLast = ($hIdx === $histCount - 1);
                                ?>
                                <article class="bpb-timeline-item <?= esc($hTone) ?><?= $isLast ? ' is-current' : '' ?>">
                                    <span class="bpb-timeline-dot"></span>
                                    <div class="bpb-timeline-copy">
                                        <strong><?= esc($h['summary'] ?? '-') ?></strong>
                                        <small>Oleh <?= esc($h['actor_name'] ?? 'Sistem') ?> (<?= esc($h['actor_department'] ?? '-') ?>)</small>
                                    </div>
                                    <div class="bpb-timeline-meta">
                                        <span class="badge bpb-status-badge <?= esc($hTone) ?>">
                                            <?= esc(ucwords(str_replace('_', ' ', (string) ($h['to_status'] ?? '-')))) ?>
                                        </span>
                                        <time><?= !empty($h['created_at']) ? date('d/m/Y H:i', strtotime($h['created_at'])) : '-' ?></time>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted font-small-2 mb-0">Belum ada riwayat aktivitas.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Aksi Dinamis (Sign / Reject / Disburse / Finish) -->
<div class="modal fade" id="bpb-action-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bpb-action-title">Aksi BPB</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">&times;</button>
            </div>
            <form id="bpb-action-form">
                <div class="modal-body">
                    <div id="bpb-action-fields"></div>
                    <div id="bpb-action-signature" class="d-none">
                        <div class="divider divider-left mb-1">
                            <div class="divider-text">Tanda Tangan Elektronik Internal</div>
                        </div>
                        <div class="form-group">
                            <label>Metode Tanda Tangan</label>
                            <select id="bpb-action-sign-method" class="form-control">
                                <option value="canvas">Gambar Tanda Tangan Sekarang</option>
                                <option value="profile">Gunakan TTD dari Profil</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Konfirmasi Password Akun Anda <span class="text-danger">*</span></label>
                            <input type="password" id="bpb-action-password" autocomplete="current-password" class="form-control" placeholder="Masukkan password akun Anda untuk verifikasi">
                        </div>
                        <div class="bpb-canvas-wrap" id="bpb-action-canvas-wrap">
                            <canvas id="bpb-action-canvas"></canvas>
                            <button type="button" class="btn btn-sm btn-outline-secondary bpb-clear-canvas" data-canvas="bpb-action-canvas">Hapus Goresan</button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" data-bpb-submit>Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Lightbox Pratinjau Gambar -->
<div id="bpb-image-lightbox" class="bpb-lightbox d-none" role="dialog" aria-modal="true" aria-label="Pratinjau lampiran" aria-hidden="true">
    <button type="button" id="bpb-lightbox-close" class="bpb-lightbox-close" aria-label="Tutup pratinjau"><i class="fa fa-times"></i></button>
    <button type="button" id="bpb-lightbox-prev" class="bpb-lightbox-nav bpb-lightbox-prev" aria-label="Gambar sebelumnya"><i class="fa fa-chevron-left"></i></button>
    <figure class="bpb-lightbox-figure" id="bpb-lightbox-stage">
        <img id="bpb-lightbox-image" src="" alt="">
        <figcaption id="bpb-lightbox-caption"></figcaption>
    </figure>
    <button type="button" id="bpb-lightbox-next" class="bpb-lightbox-nav bpb-lightbox-next" aria-label="Gambar berikutnya"><i class="fa fa-chevron-right"></i></button>
    <span id="bpb-lightbox-counter" class="bpb-lightbox-counter"></span>
</div>

<script src="<?= base_url('app-assets/vendors/js/vendors.min.js') ?>"></script>
<script src="<?= base_url('app-assets/vendors/js/extensions/sweetalert2.all.min.js') ?>"></script>
<script>
    window.SIGAPP = window.SIGAPP || {};
    window.SIGAPP.bpb = {
        baseUrl: <?= json_encode(rtrim(base_url(), '/')) ?>,
        csrfName: <?= json_encode(csrf_token()) ?>,
        csrfHash: <?= json_encode(csrf_hash()) ?>,
        openId: <?= json_encode((int) ($bpb['id'] ?? 0)) ?>
    };
    window.BPB_SHARED_DATA = <?= json_encode($bpb) ?>;
</script>
<script src="<?= base_url('assets/js/bpb.js') ?>?v=<?= filemtime(FCPATH . 'assets/js/bpb.js') ?>"></script>

<?= view('template/footer') ?>
