<link rel="stylesheet" type="text/css" href="<?= base_url('app-assets/vendors/css/extensions/sweetalert2.min.css') ?>">
<link rel="stylesheet" type="text/css" href="<?= base_url('assets/css/fee-sales.css') ?>?v=<?= filemtime(FCPATH . 'assets/css/fee-sales.css') ?>">


<div class="app-content content fee-page fee-shared-page">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper">
        <div class="content-header row">
            <div class="content-header-left col-md-9 col-12 mb-1">
                <div class="row breadcrumbs-top">
                    <div class="col-12">
                        <h4 class="content-header-title float-left pr-1 mb-0 text-primary">
                            <i class="fas fa-file-invoice-dollar mr-50"></i> Lembar Pengajuan Pencairan Fee Sales
                        </h4>
                        <div class="breadcrumb-wrapper">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?= base_url('fee-sales') ?>">Fee Sales</a></li>
                                <li class="breadcrumb-item active"><?= esc(($group->nomor_pengajuan ?? '') !== '' ? $group->nomor_pengajuan : ('#' . ($group->id ?? ''))) ?></li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-header-right text-md-right col-md-3 col-12 d-md-block d-none mb-1">
                <a href="<?= base_url('fee-sales') ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left mr-25"></i> Kembali ke Modul
                </a>
            </div>
        </div>

        <div class="content-body">
            <!-- Header Ringkasan Pengajuan -->
            <div class="card mb-2">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-lg-7 col-12 mb-1 mb-lg-0">
                            <div class="d-flex align-items-center flex-wrap mb-50">
                                <h3 class="font-weight-bold mb-0 mr-1 text-dark">
                                    <?= esc(($group->nomor_pengajuan ?? '') !== '' ? $group->nomor_pengajuan : ('Pengajuan #' . ($group->id ?? ''))) ?>
                                </h3>
                                <?php if (($group->status ?? '') === 'void'): ?>
                                    <span class="badge badge-danger font-medium-1"><i class="fas fa-ban mr-25"></i> Void / Dibatalkan</span>
                                <?php elseif (($group->status ?? '') === 'ditolak'): ?>
                                    <span class="badge badge-danger font-medium-1"><i class="fas fa-times-circle mr-25"></i> Ditolak (<?= strtoupper(esc($group->rejected_role ?? 'Revisi')) ?>)</span>
                                <?php elseif (($group->status ?? '') === 'selesai'): ?>
                                    <span class="badge badge-success font-medium-1"><i class="fas fa-check-circle mr-25"></i> Selesai (Lunas)</span>
                                <?php elseif (($group->status ?? '') === 'cair_sebagian'): ?>
                                    <span class="badge badge-primary font-medium-1"><i class="fas fa-adjust mr-25"></i> Cair Sebagian</span>
                                <?php else: ?>
                                    <span class="badge badge-warning font-medium-1"><i class="fas fa-clock mr-25"></i> Diajukan</span>
                                <?php endif; ?>
                            </div>
                            <p class="text-muted font-small-3 mb-0">
                                <i class="fas fa-building mr-25"></i> Proyek: <strong><?= esc($group->nama_proyek ?? 'Semua Proyek') ?></strong> •
                                <i class="fas fa-calendar-alt ml-50 mr-25"></i> Tanggal Pengajuan: <strong><?= !empty($group->tanggal_pengajuan) ? date('d M Y', strtotime($group->tanggal_pengajuan)) : '-' ?></strong>
                                <?php if (!empty($group->tanggal_pengajuan_sales)): ?>
                                    • <i class="far fa-calendar-check ml-50 mr-25"></i> Tgl Sales: <strong><?= date('d M Y', strtotime($group->tanggal_pengajuan_sales)) ?></strong>
                                <?php endif; ?>
                            </p>
                            <?php if (!empty($group->catatan)): ?>
                                <p class="text-secondary font-small-2 font-italic mt-25 mb-0">
                                    <i class="fas fa-comment-alt mr-25"></i> Catatan: "<?= esc($group->catatan) ?>"
                                </p>
                            <?php endif; ?>
                            <?php if (($group->status ?? '') === 'void' && !empty($group->void_reason)): ?>
                                <div class="alert alert-danger py-50 px-1 mt-1 font-small-2 mb-0">
                                    <i class="fas fa-exclamation-triangle mr-25"></i> Dibatalkan oleh <strong><?= esc($group->voided_by_name ?? 'User') ?></strong> pada <?= !empty($group->voided_at) ? date('d/m/Y H:i', strtotime($group->voided_at)) : '-' ?>: <em>"<?= esc($group->void_reason) ?>"</em>
                                </div>
                            <?php endif; ?>
                            <?php if (($group->status ?? '') === 'ditolak'): ?>
                                <div class="alert alert-danger py-75 px-1 mt-1 font-small-2 mb-0">
                                    <div class="d-flex align-items-center justify-content-between flex-wrap mb-25">
                                        <strong><i class="fas fa-exclamation-triangle mr-25"></i> Ditolak oleh <?= esc($group->rejected_by_name ?? 'Reviewer') ?> (<?= strtoupper(esc($group->rejected_role ?? 'Reviewer')) ?>) pada <?= !empty($group->rejected_at) ? date('d/m/Y H:i', strtotime($group->rejected_at)) : '-' ?></strong>
                                        <?php if (!empty($permissions['can_sign_mkdt']) || !empty($permissions['can_sign_keuangan'])): ?>
                                            <button type="button" class="btn btn-outline-danger btn-xs font-weight-bold btn-trigger-reject" data-role="<?= !empty($permissions['can_sign_mkdt']) ? 'mkdt' : 'keuangan' ?>">
                                                <i class="fas fa-edit mr-25"></i> Ubah Penolakan / Saran Harga
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                    <div class="mb-50"><strong>Keterangan:</strong> <em>"<?= esc($group->reject_reason ?? '-') ?>"</em></div>
                                    <?php if (!empty($permissions['can_manage']) || !empty($permissions['can_submit_spp'])): ?>
                                        <div class="pt-50 border-top d-flex align-items-center justify-content-between flex-wrap">
                                            <span>Terapkan rekomendasi harga untuk memperbarui nominal tiap termin:</span>
                                            <button type="button" class="btn btn-warning btn-sm font-weight-bold" id="btn-shared-apply-recalc" data-id="<?= (int) ($group->id ?? 0) ?>">
                                                <i class="fas fa-sync-alt mr-25"></i> Perbaharui Nominal Pencairan Tiap Termin
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-lg-5 col-12 text-lg-right">
                            <div class="d-inline-block text-left mr-2">
                                <small class="text-muted d-block font-small-2">TOTAL PENGAJUAN</small>
                                <span class="font-large-1 font-weight-bolder text-primary">
                                    Rp <?= number_format((float) ($group->total_nominal ?? 0), 0, ',', '.') ?>
                                </span>
                                <small class="text-muted d-block font-small-1"><?= count($group->items ?? []) ?> Termin • <?= (int) ($group->total_unit ?? 0) ?> Kavling</small>
                            </div>
                            <div class="mt-50 mt-lg-0 d-inline-block">
                                <a href="<?= base_url('fee-sales/pengajuan-pdf/' . ($group->id ?? '')) ?>" target="_blank" class="btn btn-outline-danger btn-sm font-weight-bold">
                                    <i class="fas fa-file-pdf mr-25"></i> Unduh PDF
                                </a>
                                <?php
                                    $hasCair = false;
                                    foreach (($group->items ?? []) as $itmCheck) {
                                        if (($itmCheck->termin_status ?? '') === 'sudah_cair') { $hasCair = true; break; }
                                    }
                                    $hasAnySignature = (!empty($group->ttd_mkdt_by) || !empty($group->ttd_keuangan_by));
                                    $canReject = (($group->status ?? '') !== 'void' && ($group->status ?? '') !== 'selesai' && !$hasCair && !$hasAnySignature);
                                    $canSign = (($group->status ?? '') !== 'void' && ($group->status ?? '') !== 'ditolak' && !$hasCair);
                                    $hasBothRoles = (!empty($permissions['can_sign_mkdt']) && !empty($permissions['can_sign_keuangan']));
                                ?>
                                <?php if ($canReject && !empty($permissions['can_sign_mkdt'])): ?>
                                    <button type="button" class="btn btn-outline-danger btn-sm font-weight-bold btn-trigger-reject ml-25" data-role="mkdt">
                                        <i class="fas fa-times-circle mr-25"></i> <?= $hasBothRoles ? 'Tolak & Saran Harga (MKDT)' : 'Tolak & Saran Harga' ?>
                                    </button>
                                <?php endif; ?>
                                <?php if ($canSign && empty($group->ttd_mkdt_by) && !empty($permissions['can_sign_mkdt'])): ?>
                                    <button type="button" class="btn btn-primary btn-sm font-weight-bold btn-trigger-sign ml-25" data-role="mkdt">
                                        <i class="fas fa-pen-nib mr-25"></i> TTD MKDT
                                    </button>
                                <?php endif; ?>
                                <?php if ($canReject && !empty($permissions['can_sign_keuangan'])): ?>
                                    <button type="button" class="btn btn-outline-danger btn-sm font-weight-bold btn-trigger-reject ml-25" data-role="keuangan">
                                        <i class="fas fa-times-circle mr-25"></i> <?= $hasBothRoles ? 'Tolak & Saran Harga (Keuangan)' : 'Tolak & Saran Harga' ?>
                                    </button>
                                <?php endif; ?>
                                <?php if ($canSign && empty($group->ttd_keuangan_by) && !empty($permissions['can_sign_keuangan'])): ?>
                                    <button type="button" class="btn btn-info btn-sm font-weight-bold btn-trigger-sign ml-25" data-role="keuangan">
                                        <i class="fas fa-pen-nib mr-25"></i> TTD Keuangan
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3 Kartu Status Tanda Tangan -->
            <div class="row mb-2">
                <!-- Pembuat -->
                <div class="col-md-4 col-12 mb-1 mb-md-0">
                    <div class="fee-signature-card bg-white">
                        <div class="fee-signature-card-head">
                            <span>Diajukan Oleh</span>
                            <span class="badge badge-success font-small-1">Pembuat</span>
                        </div>
                        <div class="fee-signature-image">
                            <?php if (!empty($group->ttd_pembuat_path)): ?>
                                <img src="<?= base_url('fee-sales/pengajuan-signature/' . ($group->id ?? '') . '/pembuat') ?>" alt="TTD Pembuat">
                            <?php else: ?>
                                <span class="text-muted font-small-2">Tanda tangan sistem</span>
                            <?php endif; ?>
                        </div>
                        <span class="fee-signature-name"><?= esc(($group->ttd_pembuat_nama ?? '') ?: (($group->creator_name ?? '') ?: (($group->creator_username ?? '') ?: '-'))) ?></span>
                        <span class="fee-signature-date">
                            <?= !empty($group->ttd_pembuat_at) ? date('d M Y H:i', strtotime($group->ttd_pembuat_at)) : (!empty($group->tanggal_pengajuan) ? date('d M Y', strtotime($group->tanggal_pengajuan)) : '-') ?>
                        </span>
                    </div>
                </div>

                <!-- MKDT -->
                <div class="col-md-4 col-12 mb-1 mb-md-0">
                    <div class="fee-signature-card bg-white">
                        <div class="fee-signature-card-head">
                            <span>Diperiksa Oleh</span>
                            <?php if (!empty($group->ttd_mkdt_by)): ?>
                                <span class="badge badge-success font-small-1">Disetujui MKDT</span>
                            <?php else: ?>
                                <span class="badge badge-secondary font-small-1">Belum TTD</span>
                            <?php endif; ?>
                        </div>
                        <div class="fee-signature-image h-auto">
                            <?php if (!empty($group->ttd_mkdt_by) && !empty($group->ttd_mkdt_path)): ?>
                                <img src="<?= base_url('fee-sales/pengajuan-signature/' . ($group->id ?? '') . '/mkdt') ?>" alt="TTD MKDT">
                            <?php else: ?>
                                <div class="text-center p-50">
                                    <span class="text-muted font-small-2 d-block">Belum ditandatangani</span>
                                    <?php if ($canReject && !empty($permissions['can_sign_mkdt'])): ?>
                                        <button type="button" class="btn btn-xs btn-outline-danger mt-25 mr-25 btn-trigger-reject" data-role="mkdt">
                                            <i class="fas fa-times-circle mr-25"></i> Tolak & Saran Harga
                                        </button>
                                    <?php endif; ?>
                                    <?php if ($canSign && empty($group->ttd_mkdt_by) && !empty($permissions['can_sign_mkdt'])): ?>
                                        <button type="button" class="btn btn-xs btn-outline-primary mt-25 btn-trigger-sign" data-role="mkdt">
                                            <i class="fas fa-pen-nib mr-25"></i> Tanda Tangani
                                        </button>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <span class="fee-signature-name"><?= esc(($group->ttd_mkdt_nama ?? '') ?: '-') ?></span>
                        <span class="fee-signature-date">
                            <?= !empty($group->ttd_mkdt_at) ? date('d M Y H:i', strtotime($group->ttd_mkdt_at)) : '-' ?>
                        </span>
                    </div>
                </div>

                <!-- Keuangan -->
                <div class="col-md-4 col-12">
                    <div class="fee-signature-card bg-white">
                        <div class="fee-signature-card-head">
                            <span>Disetujui Oleh</span>
                            <?php if (!empty($group->ttd_keuangan_by)): ?>
                                <span class="badge badge-success font-small-1">Disetujui Keuangan</span>
                            <?php else: ?>
                                <span class="badge badge-secondary font-small-1">Belum TTD</span>
                            <?php endif; ?>
                        </div>
                        <div class="fee-signature-image h-auto">
                            <?php if (!empty($group->ttd_keuangan_by) && !empty($group->ttd_keuangan_path)): ?>
                                <img src="<?= base_url('fee-sales/pengajuan-signature/' . ($group->id ?? '') . '/keuangan') ?>" alt="TTD Keuangan">
                            <?php else: ?>
                                <div class="text-center p-50">
                                    <span class="text-muted font-small-2 d-block">Belum ditandatangani</span>
                                    <?php if ($canReject && !empty($permissions['can_sign_keuangan'])): ?>
                                        <button type="button" class="btn btn-xs btn-outline-danger mt-25 mr-25 btn-trigger-reject" data-role="keuangan">
                                            <i class="fas fa-times-circle mr-25"></i> Tolak & Saran Harga
                                        </button>
                                    <?php endif; ?>
                                    <?php if ($canSign && empty($group->ttd_keuangan_by) && !empty($permissions['can_sign_keuangan'])): ?>
                                        <button type="button" class="btn btn-xs btn-outline-info mt-25 btn-trigger-sign" data-role="keuangan">
                                            <i class="fas fa-pen-nib mr-25"></i> Tanda Tangani
                                        </button>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <span class="fee-signature-name"><?= esc(($group->ttd_keuangan_nama ?? '') ?: '-') ?></span>
                        <span class="fee-signature-date">
                            <?= !empty($group->ttd_keuangan_at) ? date('d M Y H:i', strtotime($group->ttd_keuangan_at)) : '-' ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Daftar Unit & Termin -->
            <?php
                $groupedUnitItems = [];
                if (!empty($group->items)) {
                    foreach ($group->items as $it) {
                        $unitKey = (!empty($it->id_fee_sales) ? $it->id_fee_sales : ((!empty($it->id_mkdt) ? $it->id_mkdt : '0') . '_' . (!empty($it->id_sales) ? $it->id_sales : '0')));
                        $terminTarget = (int) ($it->termin_yang_diajukan ?? 1);
                        if (!isset($groupedUnitItems[$unitKey])) {
                            $clone = clone $it;
                            $clone->termin_targets = [$terminTarget];
                            $clone->submitted_items = [$it];
                            $clone->nominal_diajukan_total = (float) ($it->nominal_diajukan ?? 0);
                            $groupedUnitItems[$unitKey] = $clone;
                        } else {
                            $groupedUnitItems[$unitKey]->termin_targets[] = $terminTarget;
                            $groupedUnitItems[$unitKey]->submitted_items[] = $it;
                            $groupedUnitItems[$unitKey]->nominal_diajukan_total += (float) ($it->nominal_diajukan ?? 0);
                        }
                    }
                }

                $canCairkan = !empty($permissions['can_cairkan']);
                $isVoidGroup = (($group->status ?? '') === 'void');
                $isDitolakGroup = (($group->status ?? '') === 'ditolak');

                $getTerminObj = static function ($allTermin, int $k): ?object {
                    if (empty($allTermin)) {
                        return null;
                    }
                    if (is_array($allTermin)) {
                        if (isset($allTermin[$k]) && is_object($allTermin[$k])) {
                            return $allTermin[$k];
                        }
                        foreach ($allTermin as $item) {
                            if (is_object($item) && (int) ($item->termin_ke ?? 0) === $k) {
                                return $item;
                            }
                        }
                    }
                    return null;
                };

                $renderTerminCell = static function (int $k, object $it, bool $isSkemaMatch, $getTerminObj) {
                    if (!$isSkemaMatch) {
                        return ['html' => '<span class="text-muted font-small-2">-</span>', 'cellClass' => ''];
                    }

                    $t = $getTerminObj($it->all_termin ?? [], $k);
                    $isTarget = in_array($k, $it->termin_targets ?? [], true);
                    $cellClass = $isTarget ? 'tenor-highlight-yellow' : '';

                    $nominalVal = 0.0;
                    if ($t && (float) ($t->nominal ?? 0) > 0) {
                        $nominalVal = (float) $t->nominal;
                    } elseif ($isTarget) {
                        foreach ($it->submitted_items ?? [] as $subItem) {
                            if ((int) ($subItem->termin_yang_diajukan ?? 0) === $k) {
                                $nominalVal = (float) ($subItem->nominal_diajukan ?? 0);
                                break;
                            }
                        }
                    }

                    if ($nominalVal <= 0) {
                        return ['html' => '<span class="text-muted font-small-2">-</span>', 'cellClass' => $cellClass];
                    }

                    $cellHtml = '<div class="font-weight-bold">Rp ' . number_format($nominalVal, 0, ',', '.') . '</div>';

                    if ($isTarget) {
                        $isCair = ($t && ($t->status ?? '') === 'sudah_cair');
                        if ($isCair) {
                            $cellHtml .= '<div class="tenor-status-box">';
                            $cellHtml .= '<span class="tenor-cair-badge"><i class="fas fa-check-circle mr-25"></i> Sudah Cair</span>';
                            if (!empty($t->tanggal_cair)) {
                                $cellHtml .= '<small class="text-success font-weight-bold d-block mt-25">' . date('d/m/Y', strtotime($t->tanggal_cair)) . '</small>';
                            }
                            if (!empty($t->no_cek)) {
                                $cellHtml .= '<small class="text-muted d-block font-small-1">Cek: ' . esc($t->no_cek) . '</small>';
                            }
                            $cellHtml .= '</div>';
                        } else {
                            $cellHtml .= '<div class="tenor-status-box">';
                            $cellHtml .= '<span class="badge badge-dark font-small-1 mt-25"><i class="fas fa-arrow-down mr-25"></i> Diajukan</span>';
                            $cellHtml .= '</div>';
                        }
                    } else {
                        if ($t && ($t->status ?? '') === 'sudah_cair') {
                            $cellHtml .= '<div class="tenor-status-box">';
                            $cellHtml .= '<span class="tenor-cair-badge"><i class="fas fa-check-circle mr-25"></i> Sudah Cair</span>';
                            if (!empty($t->tanggal_cair)) {
                                $cellHtml .= '<small class="text-success font-weight-bold d-block mt-25">' . date('d/m/Y', strtotime($t->tanggal_cair)) . '</small>';
                            }
                            if (!empty($t->no_cek)) {
                                $cellHtml .= '<small class="text-muted d-block font-small-1">Cek: ' . esc($t->no_cek) . '</small>';
                            }
                            if (!empty($t->pengajuan_nomor)) {
                                $cellHtml .= '<small class="text-muted d-block font-small-1" title="Nomor Pengajuan">No: ' . esc($t->pengajuan_nomor) . '</small>';
                            }
                            $cellHtml .= '</div>';
                        } elseif ($t && ($t->status ?? '') === 'pengajuan_spp') {
                            $tglPengajuan = $t->tanggal_pengajuan_spp ?? ($t->pengajuan_tanggal ?? null);
                            $cellHtml .= '<div class="tenor-status-box">';
                            $cellHtml .= '<span class="tenor-diajukan-other-badge"><i class="fas fa-clock mr-25"></i> Sudah Diajukan</span>';
                            if (!empty($tglPengajuan)) {
                                $cellHtml .= '<small class="text-warning font-weight-bold d-block mt-25">' . date('d/m/Y', strtotime($tglPengajuan)) . '</small>';
                            }
                            if (!empty($t->pengajuan_nomor)) {
                                $cellHtml .= '<small class="text-muted d-block font-small-1" title="Nomor Pengajuan">No: ' . esc($t->pengajuan_nomor) . '</small>';
                            }
                            $cellHtml .= '</div>';
                        } else {
                            $cellHtml .= '<div class="tenor-status-box">';
                            $cellHtml .= '<small class="text-muted font-small-1 d-block">Belum Diajukan</small>';
                            $cellHtml .= '</div>';
                        }
                    }

                    return ['html' => $cellHtml, 'cellClass' => $cellClass];
                };

                $renderMobileTenorBox = static function (int $k, string $label, bool $isSkemaMatch, object $it, $getTerminObj) {
                    if (!$isSkemaMatch) {
                        return '<div class="fee-tenor-box text-muted opacity-50">'
                             . '<div class="tenor-title"><span>' . $label . '</span><span class="badge badge-light-secondary font-small-1">-</span></div>'
                             . '<div class="tenor-nominal text-muted font-small-2">-</div>'
                             . '</div>';
                    }

                    $t = $getTerminObj($it->all_termin ?? [], $k);
                    $isTarget = in_array($k, $it->termin_targets ?? [], true);
                    $boxClass = $isTarget ? 'fee-tenor-box active-target' : 'fee-tenor-box';

                    $nominalVal = 0.0;
                    if ($t && (float) ($t->nominal ?? 0) > 0) {
                        $nominalVal = (float) $t->nominal;
                    } elseif ($isTarget) {
                        foreach ($it->submitted_items ?? [] as $subItem) {
                            if ((int) ($subItem->termin_yang_diajukan ?? 0) === $k) {
                                $nominalVal = (float) ($subItem->nominal_diajukan ?? 0);
                                break;
                            }
                        }
                    }

                    $out = '<div class="' . $boxClass . '">';
                    $out .= '<div class="tenor-title">';
                    $out .= '<span>' . $label . '</span>';
                    if ($isTarget) {
                        $isCair = ($t && ($t->status ?? '') === 'sudah_cair');
                        if ($isCair) {
                            $out .= '<span class="badge badge-success font-small-1"><i class="fas fa-check-circle mr-25"></i> Cair</span>';
                        } else {
                            $out .= '<span class="badge badge-dark font-small-1"><i class="fas fa-arrow-down mr-25"></i> Diajukan</span>';
                        }
                    } else {
                        if ($t && ($t->status ?? '') === 'sudah_cair') {
                            $out .= '<span class="tenor-cair-badge font-small-1">Cair</span>';
                        } elseif ($t && ($t->status ?? '') === 'pengajuan_spp') {
                            $out .= '<span class="tenor-diajukan-other-badge font-small-1">Diajukan</span>';
                        } else {
                            $out .= '<span class="badge badge-light-secondary font-small-1">Belum</span>';
                        }
                    }
                    $out .= '</div>';

                    $out .= '<div class="tenor-nominal ' . ($isTarget ? 'text-dark' : 'text-secondary') . '">';
                    $out .= $nominalVal > 0 ? ('Rp ' . number_format($nominalVal, 0, ',', '.')) : '-';
                    $out .= '</div>';

                    if ($isTarget) {
                        if ($t && ($t->status ?? '') === 'sudah_cair') {
                            if (!empty($t->tanggal_cair) || !empty($t->no_cek)) {
                                $out .= '<div class="font-small-1 text-success mt-25">';
                                if (!empty($t->tanggal_cair)) $out .= date('d/m/y', strtotime($t->tanggal_cair)) . ' ';
                                if (!empty($t->no_cek)) $out .= '(' . esc($t->no_cek) . ')';
                                $out .= '</div>';
                            }
                        }
                    } else {
                        if ($t && ($t->status ?? '') === 'sudah_cair') {
                            if (!empty($t->tanggal_cair) || !empty($t->no_cek)) {
                                $out .= '<div class="font-small-1 text-success mt-25">';
                                if (!empty($t->tanggal_cair)) $out .= date('d/m/y', strtotime($t->tanggal_cair)) . ' ';
                                if (!empty($t->no_cek)) $out .= '(' . esc($t->no_cek) . ')';
                                $out .= '</div>';
                            }
                        } elseif ($t && ($t->status ?? '') === 'pengajuan_spp') {
                            if (!empty($t->pengajuan_nomor)) {
                                $out .= '<div class="font-small-1 text-warning mt-25">No: ' . esc($t->pengajuan_nomor) . '</div>';
                            }
                        }
                    }

                    $out .= '</div>';
                    return $out;
                };
            ?>

            <div class="card">
                <div class="card-header border-bottom py-1">
                    <h5 class="card-title font-weight-bold mb-0 text-dark">
                        <i class="fas fa-list-ul mr-50 text-primary"></i> Rincian Unit & Termin Diajukan (<?= count($groupedUnitItems) ?> Kavling • <?= count($group->items ?? []) ?> Termin)
                    </h5>
                </div>
                <div class="card-body p-0">
                    <!-- Tampilan Desktop (Tabel Hierarchical Grouped Rows 12 Kolom) -->
                    <div class="table-responsive d-none d-md-block">
                        <table class="table table-bordered table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 35px;" class="text-center">No</th>
                                    <th class="text-right">Harga Jual</th>
                                    <th class="text-right">Harga Jual Net</th>
                                    <th class="text-right">Harga Jual Net AJB</th>
                                    <th class="text-center" style="width: 65px;">% Fee</th>
                                    <th class="text-right">Total Fee</th>
                                    <th class="text-right" style="width: 95px;">Termin 30%</th>
                                    <th class="text-right" style="width: 95px;">Termin 60%</th>
                                    <th class="text-right" style="width: 95px;">Termin 10%</th>
                                    <th class="text-right" style="width: 95px;">Termin 100%</th>
                                    <th class="text-center" style="width: 140px;">Status & No Cek</th>
                                    <th class="text-center" style="width: 95px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($groupedUnitItems)): ?>
                                    <?php $idx = 0; ?>
                                    <?php foreach ($groupedUnitItems as $it): ?>
                                        <?php
                                            $idx++;
                                            $skema = (!empty($it->skema_termin) ? $it->skema_termin : '30_60_10');
                                            $c30  = $renderTerminCell(1, $it, $skema !== '100', $getTerminObj);
                                            $c60  = $renderTerminCell(2, $it, $skema !== '100', $getTerminObj);
                                            $c10  = $renderTerminCell(3, $it, $skema !== '100', $getTerminObj);
                                            $c100 = $renderTerminCell(1, $it, $skema === '100', $getTerminObj);

                                            $ajbVal = (float) (($it->harga_net_ajb ?? 0) ?: (($it->fee_sales_harga_net_ajb ?? 0) ?: ($it->fee_harga_net_ajb ?? 0)));
                                            $netVal = (float) ($it->harga_jual_net ?? 0);
                                            $hjVal  = (float) ($it->harga_jual ?? 0);
                                            $isEstimasi = false;
                                            if ($ajbVal > 0) {
                                                $nominalAjb = $ajbVal;
                                            } else {
                                                $nominalAjb = $netVal > 0 ? $netVal : $hjVal;
                                                $isEstimasi = true;
                                            }

                                            $submittedItems = $it->submitted_items ?? [];
                                        ?>
                                        <tr class="group-header-row">
                                            <td colspan="12">
                                                <div class="d-flex flex-wrap justify-content-between align-items-center">
                                                    <div>
                                                        <span class="mr-1 font-weight-bold text-primary">
                                                            <i class="fas fa-user-tie mr-25"></i> Sales: <strong><?= esc(($it->sales_nama ?? '') ?: '-') ?></strong>
                                                            <?php if ((int) ($it->sales_has_pks ?? 0) === 1): ?>
                                                                <span class="badge badge-light-success font-small-1 ml-25">PKS</span>
                                                            <?php else: ?>
                                                                <span class="badge badge-light-secondary font-small-1 ml-25">Non-PKS</span>
                                                            <?php endif; ?>
                                                        </span>
                                                        <span class="mr-1 font-weight-bold text-dark">
                                                            <i class="fas fa-user mr-25"></i> Konsumen: <strong><?= esc(($it->nama_konsumen ?? '') ?: '-') ?></strong>
                                                        </span>
                                                        <span class="text-secondary font-weight-bold">
                                                            <i class="fas fa-home mr-25"></i> Kavling: <strong><?= esc(($it->nama_jalan ?? '') ?: '-') ?> No. <?= esc(($it->no_kavling ?? '') ?: '-') ?></strong><?= !empty($it->tipe_rumah) ? ' (' . esc($it->tipe_rumah) . ')' : '' ?>
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <span class="badge badge-light-secondary font-small-1">
                                                            <i class="far fa-calendar-alt mr-25"></i> Booking: <?= !empty($it->booking_tgl) ? date('d/m/Y', strtotime($it->booking_tgl)) : '-' ?> | Akad: <?= !empty($it->akad_tgl) ? date('d/m/Y', strtotime($it->akad_tgl)) : '-' ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-center font-weight-bold"><?= $idx ?></td>
                                            <td class="text-right">Rp <?= number_format((float) ($it->harga_jual ?? 0), 0, ',', '.') ?></td>
                                            <td class="text-right">Rp <?= number_format((float) ($it->harga_jual_net ?? 0), 0, ',', '.') ?></td>
                                            <td class="text-right">
                                                <?php if ($isEstimasi): ?>
                                                    <div>
                                                        <span class="font-weight-bold text-secondary">Rp <?= number_format($nominalAjb, 0, ',', '.') ?></span>
                                                        <span class="badge badge-light-warning font-small-1 d-block mt-25">Estimasi</span>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="font-weight-bold text-primary">Rp <?= number_format($nominalAjb, 0, ',', '.') ?></span>
                                                <?php endif; ?>
                                                <?php if ((float) ($it->suggested_harga_net_ajb ?? 0) > 0): ?>
                                                    <div class="badge-suggested-price mt-25" title="Harga Rekomendasi Reviewer">
                                                        <i class="fas fa-arrow-alt-circle-right mr-25"></i> Saran: Rp <?= number_format((float) $it->suggested_harga_net_ajb, 0, ',', '.') ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center"><?= number_format((float) ($it->persentase_fee ?? 0), 1, ',', '.') ?>%</td>
                                            <td class="text-right font-weight-bold text-success">
                                                Rp <?= number_format((float) ($it->total_fee ?? 0), 0, ',', '.') ?>
                                            </td>
                                            <td class="text-right <?= $c30['cellClass'] ?>"><?= $c30['html'] ?></td>
                                            <td class="text-right <?= $c60['cellClass'] ?>"><?= $c60['html'] ?></td>
                                            <td class="text-right <?= $c10['cellClass'] ?>"><?= $c10['html'] ?></td>
                                            <td class="text-right <?= $c100['cellClass'] ?>"><?= $c100['html'] ?></td>
                                            <td class="text-center">
                                                <?php if (count($submittedItems) === 1): ?>
                                                    <?php
                                                        $sub = $submittedItems[0];
                                                        $isCair = (($sub->termin_status ?? '') === 'sudah_cair');
                                                    ?>
                                                    <div class="d-flex align-items-center justify-content-center flex-wrap">
                                                        <span class="badge badge-primary font-small-1 mr-25">Termin <?= esc((isset($sub->termin_yang_diajukan) && $sub->termin_yang_diajukan !== '') ? $sub->termin_yang_diajukan : '-') ?><?= !empty($sub->termin_persen) ? ' (' . (float)$sub->termin_persen . '%)' : '' ?></span>
                                                        <?php if ($isCair): ?>
                                                            <span class="badge badge-success font-small-1"><i class="fas fa-check-circle mr-25"></i> Sudah Cair</span>
                                                        <?php elseif ($isVoidGroup): ?>
                                                            <span class="badge badge-secondary font-small-1"><i class="fas fa-ban mr-25"></i> Batal / Void</span>
                                                        <?php elseif ($isDitolakGroup): ?>
                                                            <span class="badge badge-danger font-small-1"><i class="fas fa-times-circle mr-25"></i> Ditolak</span>
                                                        <?php else: ?>
                                                            <span class="badge badge-warning font-small-1"><i class="fas fa-clock mr-25"></i> Diajukan</span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <?php if ($isCair): ?>
                                                        <?php if (!empty($sub->termin_no_cek)): ?>
                                                            <div class="mt-25"><span class="badge badge-light-primary font-small-1">Cek: <?= esc($sub->termin_no_cek) ?></span></div>
                                                        <?php endif; ?>
                                                        <?php if (!empty($sub->termin_tgl_cair)): ?>
                                                            <small class="text-success font-weight-bold d-block mt-25"><?= date('d/m/Y', strtotime($sub->termin_tgl_cair)) ?></small>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <?php foreach ($submittedItems as $sIdx => $sub): ?>
                                                        <?php $isCair = (($sub->termin_status ?? '') === 'sudah_cair'); ?>
                                                        <div class="<?= $sIdx > 0 ? 'mt-50 pt-25 border-top' : '' ?>">
                                                            <div class="d-flex align-items-center justify-content-center flex-wrap">
                                                                <span class="badge badge-primary font-small-1 mr-25">Termin <?= esc((isset($sub->termin_yang_diajukan) && $sub->termin_yang_diajukan !== '') ? $sub->termin_yang_diajukan : '-') ?><?= !empty($sub->termin_persen) ? ' (' . (float)$sub->termin_persen . '%)' : '' ?></span>
                                                                <?php if ($isCair): ?>
                                                                    <span class="badge badge-success font-small-1"><i class="fas fa-check-circle mr-25"></i> Cair</span>
                                                                <?php elseif ($isVoidGroup): ?>
                                                                    <span class="badge badge-secondary font-small-1"><i class="fas fa-ban mr-25"></i> Void</span>
                                                                <?php elseif ($isDitolakGroup): ?>
                                                                    <span class="badge badge-danger font-small-1"><i class="fas fa-times-circle mr-25"></i> Ditolak</span>
                                                                <?php else: ?>
                                                                    <span class="badge badge-warning font-small-1"><i class="fas fa-clock mr-25"></i> Diajukan</span>
                                                                <?php endif; ?>
                                                            </div>
                                                            <?php if ($isCair): ?>
                                                                <?php if (!empty($sub->termin_no_cek)): ?>
                                                                    <div class="mt-25"><span class="badge badge-light-primary font-small-1">Cek: <?= esc($sub->termin_no_cek) ?></span></div>
                                                                <?php endif; ?>
                                                                <?php if (!empty($sub->termin_tgl_cair)): ?>
                                                                    <small class="text-success font-weight-bold d-block mt-25"><?= date('d/m/Y', strtotime($sub->termin_tgl_cair)) ?></small>
                                                                <?php endif; ?>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php
                                                    $renderedAction = false;
                                                    if ($canCairkan && !$isVoidGroup && !$isDitolakGroup) {
                                                        foreach ($submittedItems as $sub) {
                                                            $isCair = (($sub->termin_status ?? '') === 'sudah_cair');
                                                            if (!$isCair) {
                                                                $renderedAction = true;
                                                                ?>
                                                                <button type="button" class="btn btn-success btn-sm btn-open-cair py-25 px-50 mb-25 font-small-2"
                                                                    data-id="<?= (int) ($sub->id_termin ?? $sub->id ?? 0) ?>"
                                                                    data-termin="<?= esc($sub->termin_yang_diajukan ?? '') ?>"
                                                                    data-nominal="<?= (float) ($sub->nominal_diajukan ?? 0) ?>">
                                                                    <i class="fas fa-check mr-25"></i> Cairkan<?= count($submittedItems) > 1 ? ' T' . esc($sub->termin_yang_diajukan ?? '') : '' ?>
                                                                </button>
                                                                <?php
                                                            }
                                                        }
                                                    }
                                                    if (!$renderedAction):
                                                ?>
                                                    <span class="text-muted font-small-2">-</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="12" class="text-center py-2 text-muted">Tidak ada unit dalam pengajuan ini.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Tampilan Mobile (Responsive Card Grid System) -->
                    <div class="d-block d-md-none p-1">
                        <?php if (!empty($groupedUnitItems)): ?>
                            <?php $idx = 0; ?>
                            <?php foreach ($groupedUnitItems as $it): ?>
                                <?php
                                    $idx++;
                                    $skema = (!empty($it->skema_termin) ? $it->skema_termin : '30_60_10');
                                    $submittedItems = $it->submitted_items ?? [];

                                    $allSubmittedCair = true;
                                    $hasAnyCair = false;
                                    foreach ($submittedItems as $sub) {
                                        if (($sub->termin_status ?? '') === 'sudah_cair') {
                                            $hasAnyCair = true;
                                        } else {
                                            $allSubmittedCair = false;
                                        }
                                    }
                                    $cardClass = $allSubmittedCair ? 'is-cair' : ($isVoidGroup ? 'is-void' : 'is-pending');

                                    $ajbVal = (float) (($it->harga_net_ajb ?? 0) ?: (($it->fee_sales_harga_net_ajb ?? 0) ?: ($it->fee_harga_net_ajb ?? 0)));
                                    $netVal = (float) ($it->harga_jual_net ?? 0);
                                    $hjVal  = (float) ($it->harga_jual ?? 0);
                                    $isEstimasi = false;
                                    if ($ajbVal > 0) {
                                        $nominalAjb = $ajbVal;
                                    } else {
                                        $nominalAjb = $netVal > 0 ? $netVal : $hjVal;
                                        $isEstimasi = true;
                                    }
                                ?>
                                <div class="fee-shared-item-card <?= $cardClass ?>">
                                    <!-- Header Kartu -->
                                    <div class="d-flex justify-content-between align-items-start mb-50">
                                        <div>
                                            <span class="badge badge-light-primary font-small-1 mr-25">#<?= $idx ?></span>
                                            <strong class="text-dark font-medium-1"><?= esc(!empty($it->no_kavling) ? ('Kav. ' . $it->no_kavling) : '-') ?></strong>
                                            <small class="text-muted d-block font-small-2">
                                                <?= esc(($it->nama_jalan ?? '') ?: '-') ?><?= !empty($it->tipe_rumah) ? ' • Tipe ' . esc($it->tipe_rumah) : '' ?>
                                            </small>
                                        </div>
                                        <div>
                                            <?php if ($allSubmittedCair): ?>
                                                <span class="badge badge-success font-small-1"><i class="fas fa-check-circle mr-25"></i> Lunas / Cair</span>
                                            <?php elseif ($hasAnyCair): ?>
                                                <span class="badge badge-primary font-small-1"><i class="fas fa-adjust mr-25"></i> Cair Sebagian</span>
                                            <?php elseif ($isVoidGroup): ?>
                                                <span class="badge badge-secondary font-small-1"><i class="fas fa-ban mr-25"></i> Void</span>
                                            <?php elseif ($isDitolakGroup): ?>
                                                <span class="badge badge-danger font-small-1"><i class="fas fa-times-circle mr-25"></i> Ditolak</span>
                                            <?php else: ?>
                                                <span class="badge badge-warning font-small-1"><i class="fas fa-clock mr-25"></i> Diajukan</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- Konsumen & Sales Info Box -->
                                    <div class="bg-light rounded p-50 mb-50 font-small-2">
                                        <div class="d-flex justify-content-between mb-25">
                                            <span class="text-muted">Konsumen:</span>
                                            <strong class="text-dark"><?= esc(($it->nama_konsumen ?? '') ?: '-') ?></strong>
                                        </div>
                                        <div class="d-flex justify-content-between mb-25">
                                            <span class="text-muted">Sales Terkait:</span>
                                            <span>
                                                <strong><?= esc(($it->sales_nama ?? '') ?: '-') ?></strong>
                                                <?php if ((int) ($it->sales_has_pks ?? 0) === 1): ?>
                                                    <span class="badge badge-light-success font-small-1 py-0 px-25">PKS</span>
                                                <?php else: ?>
                                                    <span class="badge badge-light-secondary font-small-1 py-0 px-25">Non-PKS</span>
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">Booking / Akad:</span>
                                            <span class="text-secondary">
                                                <?= !empty($it->booking_tgl) ? date('d/m/Y', strtotime($it->booking_tgl)) : '-' ?> / <?= !empty($it->akad_tgl) ? date('d/m/Y', strtotime($it->akad_tgl)) : '-' ?>
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Grid Finansial (2 Kolom) -->
                                    <div class="fee-shared-finance-grid">
                                        <div class="fee-finance-item">
                                            <span class="finance-label">Harga Jual</span>
                                            <span class="finance-value">Rp <?= number_format((float) ($it->harga_jual ?? 0), 0, ',', '.') ?></span>
                                        </div>
                                        <div class="fee-finance-item">
                                            <span class="finance-label">Harga Jual Net</span>
                                            <span class="finance-value">Rp <?= number_format((float) ($it->harga_jual_net ?? 0), 0, ',', '.') ?></span>
                                        </div>
                                        <div class="fee-finance-item">
                                            <span class="finance-label">Harga Net AJB</span>
                                            <div class="finance-value text-primary">
                                                Rp <?= number_format($nominalAjb, 0, ',', '.') ?>
                                                <?php if ($isEstimasi): ?>
                                                    <span class="badge badge-light-warning font-small-1">Estimasi</span>
                                                <?php endif; ?>
                                            </div>
                                            <?php if ((float) ($it->suggested_harga_net_ajb ?? 0) > 0): ?>
                                                <div class="badge-suggested-price mt-25" title="Harga Rekomendasi Reviewer">
                                                    <i class="fas fa-arrow-alt-circle-right mr-25"></i> Saran: Rp <?= number_format((float) $it->suggested_harga_net_ajb, 0, ',', '.') ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="fee-finance-item">
                                            <span class="finance-label">% Fee</span>
                                            <span class="finance-value"><?= number_format((float) ($it->persentase_fee ?? 0), 1, ',', '.') ?>%</span>
                                        </div>
                                        <div class="fee-finance-item fee-finance-full d-flex justify-content-between align-items-center">
                                            <span class="finance-label mb-0">Total Fee Sales:</span>
                                            <span class="finance-value text-success font-medium-1 font-weight-bolder">
                                                Rp <?= number_format((float) ($it->total_fee ?? 0), 0, ',', '.') ?>
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Grid Tenor Matriks (Grid 2x2) -->
                                    <div class="fee-shared-tenor-grid">
                                        <?= $renderMobileTenorBox(1, 'Termin 30%', $skema !== '100', $it, $getTerminObj) ?>
                                        <?= $renderMobileTenorBox(2, 'Termin 60%', $skema !== '100', $it, $getTerminObj) ?>
                                        <?= $renderMobileTenorBox(3, 'Termin 10%', $skema !== '100', $it, $getTerminObj) ?>
                                        <?= $renderMobileTenorBox(1, 'Termin 100%', $skema === '100', $it, $getTerminObj) ?>
                                    </div>

                                    <!-- Footer Kartu: Total Diajukan & Aksi Cairkan -->
                                    <div class="mt-75 pt-50 border-top d-flex justify-content-between align-items-center flex-wrap">
                                        <div>
                                            <small class="text-muted font-small-1 d-block">TOTAL NOMINAL DIAJUKAN</small>
                                            <span class="font-medium-1 font-weight-bolder text-primary">
                                                Rp <?= number_format((float) ($it->nominal_diajukan_total ?? 0), 0, ',', '.') ?>
                                            </span>
                                        </div>
                                        <div class="mt-25 mt-sm-0">
                                            <?php if ($canCairkan && !$isVoidGroup && !$isDitolakGroup): ?>
                                                <?php foreach ($submittedItems as $sub): ?>
                                                    <?php if (($sub->termin_status ?? '') !== 'sudah_cair'): ?>
                                                        <button type="button" class="btn btn-success btn-sm btn-open-cair py-25 px-50 ml-25 font-small-2"
                                                            data-id="<?= (int) ($sub->id_termin ?? $sub->id ?? 0) ?>"
                                                            data-termin="<?= esc($sub->termin_yang_diajukan ?? '') ?>"
                                                            data-nominal="<?= (float) ($sub->nominal_diajukan ?? 0) ?>">
                                                            <i class="fas fa-check mr-25"></i> Cairkan<?= count($submittedItems) > 1 ? ' T' . esc($sub->termin_yang_diajukan ?? '') : '' ?>
                                                        </button>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- Riwayat No Cek & Tanggal Cair Jika Ada -->
                                    <?php foreach ($submittedItems as $sub): ?>
                                        <?php if (($sub->termin_status ?? '') === 'sudah_cair' && (!empty($sub->termin_no_cek) || !empty($sub->termin_tgl_cair))): ?>
                                            <div class="mt-25 pt-25 font-small-1 text-success d-flex justify-content-between align-items-center">
                                                <span><i class="fas fa-receipt mr-25"></i> Cek T<?= esc($sub->termin_yang_diajukan ?? '') ?>: <?= esc(($sub->termin_no_cek ?? '') ?: '-') ?></span>
                                                <span><i class="fas fa-calendar mr-25"></i> <?= !empty($sub->termin_tgl_cair) ? date('d/m/Y', strtotime($sub->termin_tgl_cair)) : '-' ?></span>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-2 text-muted">Tidak ada unit dalam pengajuan ini.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <!-- Spacer agar kartu unit terakhir pada mobile tidak tertutup oleh sticky bottom bar -->
            <div class="d-block d-md-none" style="height: 90px;" aria-hidden="true"></div>
        </div>
    </div>
</div>

<!-- Sticky Bottom Bar Khusus Mobile -->
<div class="fee-mobile-sticky-bar d-block d-md-none fixed-bottom border-top p-1 shadow-lg">
    <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap: 6px;">
        <a href="<?= base_url('fee-sales/pengajuan-pdf/' . ($group->id ?? '')) ?>" target="_blank" class="btn btn-outline-danger btn-sm flex-fill">
            <i class="fas fa-file-pdf mr-25"></i> PDF
        </a>
        <?php if ($canReject && !empty($permissions['can_sign_mkdt'])): ?>
            <button type="button" class="btn btn-outline-danger btn-sm flex-fill font-weight-bold btn-trigger-reject" data-role="mkdt">
                <i class="fas fa-times-circle mr-25"></i> <?= $hasBothRoles ? 'Tolak (MKDT)' : 'Tolak & Saran Harga' ?>
            </button>
        <?php endif; ?>
        <?php if ($canSign && empty($group->ttd_mkdt_by) && !empty($permissions['can_sign_mkdt'])): ?>
            <button type="button" class="btn btn-primary btn-sm flex-fill font-weight-bold btn-trigger-sign" data-role="mkdt">
                <i class="fas fa-pen-nib mr-25"></i> TTD MKDT
            </button>
        <?php endif; ?>
        <?php if ($canReject && !empty($permissions['can_sign_keuangan'])): ?>
            <button type="button" class="btn btn-outline-danger btn-sm flex-fill font-weight-bold btn-trigger-reject" data-role="keuangan">
                <i class="fas fa-times-circle mr-25"></i> <?= $hasBothRoles ? 'Tolak (Keu)' : 'Tolak & Saran Harga' ?>
            </button>
        <?php endif; ?>
        <?php if ($canSign && empty($group->ttd_keuangan_by) && !empty($permissions['can_sign_keuangan'])): ?>
            <button type="button" class="btn btn-info btn-sm flex-fill font-weight-bold btn-trigger-sign" data-role="keuangan">
                <i class="fas fa-pen-nib mr-25"></i> TTD Keu
            </button>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Tolak Pengajuan Shared -->
<div class="modal fade" id="modal-shared-reject" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <form id="form-shared-reject" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold text-danger"><i class="fas fa-times-circle mr-50"></i> <span id="shared-reject-title">Tolak Pengajuan & Saran Revisi Harga AJB</span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_pengajuan" value="<?= (int) ($group->id ?? 0) ?>">
                <input type="hidden" name="role" id="shared-reject-role" value="mkdt">

                <div class="alert alert-warning py-75 mb-2 font-small-3">
                    <i class="fas fa-info-circle mr-50"></i> Memberikan penolakan akan mengubah status pengajuan menjadi <strong>Ditolak</strong> dan memberitahukan pembuat pengajuan untuk menyesuaikan nominal sesuai rekomendasi Anda.
                </div>

                <div class="card border mb-2 shadow-none">
                    <div class="card-header py-75 border-bottom bg-light">
                        <strong class="font-small-3 text-dark"><i class="fas fa-tags mr-50 text-primary"></i> Rekomendasi Harga Jual Net AJB per Unit Kavling</strong>
                    </div>
                    <div class="card-body p-1">
                        <?php
                            $uniqueUnits = [];
                            foreach (($group->items ?? []) as $it) {
                                if (!empty($it->id_mkdt) && !isset($uniqueUnits[$it->id_mkdt])) {
                                    $uniqueUnits[$it->id_mkdt] = $it;
                                }
                            }
                        ?>
                        <?php foreach ($uniqueUnits as $idMkdt => $uItem): ?>
                            <?php
                                $uAjbNom = (float) (($uItem->harga_net_ajb ?? 0) ?: (($uItem->fee_sales_harga_net_ajb ?? 0) ?: ($uItem->fee_harga_net_ajb ?? 0)));
                                $uNetNom = (float) ($uItem->harga_jual_net ?? 0);
                                $uHjNom  = (float) ($uItem->harga_jual ?? 0);
                                $currentAjb = $uAjbNom > 0 ? $uAjbNom : ($uNetNom > 0 ? $uNetNom : $uHjNom);
                                $suggestedAjb = (float) ($uItem->suggested_harga_net_ajb ?? 0);
                                $initPrice = $suggestedAjb > 0 ? $suggestedAjb : $currentAjb;
                            ?>
                            <div class="reject-unit-row mb-1 p-75 border rounded bg-white">
                                <div class="row align-items-center">
                                    <div class="col-md-7 col-12 mb-50 mb-md-0">
                                        <strong class="text-dark d-block"><?= esc(($uItem->nama_konsumen ?? '') ?: '-') ?></strong>
                                        <small class="text-muted"><?= esc(($uItem->nama_jalan ?? '') ?: '-') ?> • Kav. <?= esc(($uItem->no_kavling ?? '') ?: '-') ?></small>
                                        <div class="font-small-1 text-secondary mt-25">
                                            Harga AJB Saat Ini: <strong>Rp <?= number_format($currentAjb, 0, ',', '.') ?></strong>
                                        </div>
                                    </div>
                                    <div class="col-md-5 col-12">
                                        <label class="font-small-2 font-weight-bold text-dark mb-25">Rekomendasi Harga Net AJB (Rp):</label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend"><span class="input-group-text font-small-1">Rp</span></div>
                                            <input type="text" name="unit_prices[<?= (int) $idMkdt ?>]" class="form-control form-control-sm text-right font-weight-bold shared-num-input" value="<?= number_format($initPrice, 0, ',', '.') ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="form-group mb-0">
                    <label class="font-weight-bold text-dark">Keterangan / Alasan Penolakan <span class="text-danger">*</span></label>
                    <textarea name="alasan" id="shared-reject-alasan" class="form-control" rows="3" required placeholder="Jelaskan alasan penolakan dan instruksi revisi harga net AJB..."><?= esc($group->reject_reason ?? '') ?></textarea>
                    <small class="text-muted font-small-1">Wajib diisi sebagai panduan pembuat pengajuan.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-danger font-weight-bold" id="btn-submit-shared-reject">
                    <i class="fas fa-paper-plane mr-50"></i> Simpan Penolakan & Kirim Revisi
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tanda Tangan Shared -->
<div class="modal fade" id="modal-shared-sign" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form id="form-shared-sign" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-pen-nib mr-50 text-primary"></i> <span id="shared-sign-title">Tanda Tangan Pengajuan</span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_pengajuan" value="<?= (int) ($group->id ?? 0) ?>">
                <input type="hidden" name="role" id="shared-sign-role" value="mkdt">

                <div class="form-group">
                    <label class="font-weight-bold">Metode Tanda Tangan <span class="text-danger">*</span></label>
                    <select class="form-control" id="shared-sign-method" name="signature_method">
                        <option value="canvas">Gambar sekarang di layar</option>
                        <option value="profile">Gunakan tanda tangan profil akun</option>
                    </select>
                </div>

                <div id="shared-sign-canvas-section">
                    <label class="font-weight-bold">Area Tanda Tangan <span class="text-danger">*</span></label>
                    <div class="fee-canvas-wrap" id="shared-sign-canvas-wrap">
                        <canvas id="shared-sign-canvas"></canvas>
                        <button type="button" class="btn btn-sm btn-outline-secondary fee-clear-canvas" id="btn-clear-shared-canvas">Hapus</button>
                    </div>
                    <small class="text-muted d-block mt-50">Gunakan mouse atau sentuhan jari di layar untuk menggambar.</small>
                </div>

                <div class="form-group mt-1">
                    <label class="font-weight-bold">Konfirmasi Password Akun Anda <span class="text-danger">*</span></label>
                    <input type="password" name="password" id="shared-sign-password" class="form-control" required autocomplete="current-password" placeholder="Masukkan password login Anda">
                    <small class="text-muted font-small-2">Dibutuhkan untuk memvalidasi keabsahan penandatanganan dokumen.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary font-weight-bold" id="btn-submit-shared-sign">
                    <i class="fas fa-check mr-50"></i> Simpan Tanda Tangan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Konfirmasi Pencairan Dana -->
<div class="modal fade" id="modal-cair" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form id="form-cair" class="modal-content" enctype="multipart/form-data">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold text-success"><i class="fas fa-check-circle mr-50"></i> Konfirmasi Pencairan: <span id="cair-termin-label" class="font-weight-bold"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_termin" id="cair-id_termin">
                <div class="form-group">
                    <label class="font-weight-bold">Nominal Cair</label>
                    <input type="text" id="cair-nominal-display" class="form-control font-weight-bold text-right text-success" readonly>
                </div>
                <div class="form-group">
                    <label class="font-weight-bold">Tanggal Realisasi Cair <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal_cair" id="cair-tanggal" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="font-weight-bold">Nomor Cek / Bilyet Giro</label>
                    <input type="text" name="no_cek" id="cair-no_cek" class="form-control" placeholder="Contoh: CK-889212">
                    <small class="text-muted font-small-2">Nomor cek yang dicantumkan pada laporan pengajuan fee.</small>
                </div>
                <div class="form-group">
                    <label class="font-weight-bold">Unggah Bukti Transfer / Pencairan (PDF / Gambar)</label>
                    <input type="file" name="lampiran_bukti_cair" id="cair-file" class="form-control-file" accept=".pdf,image/*">
                </div>
                <div class="form-group mb-0">
                    <label class="font-weight-bold">Keterangan Pencairan</label>
                    <textarea name="keterangan" id="cair-keterangan" class="form-control" rows="2" placeholder="Nomor referensi bank / transfer"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-success font-weight-bold" id="btn-submit-cair">
                    <i class="fas fa-check mr-50"></i> Konfirmasi Dana Cair
                </button>
            </div>
        </form>
    </div>
</div>


<script src="<?= base_url('app-assets/vendors/js/vendors.min.js') ?>"></script>
<script src="<?= base_url('app-assets/vendors/js/extensions/sweetalert2.all.min.js') ?>"></script>
<script>
$(function () {
    "use strict";

    let csrfName = "<?= csrf_token() ?>";
    let csrfHash = "<?= csrf_hash() ?>";
    let baseUrl  = "<?= rtrim(base_url(), '/') . '/' ?>";

    // Canvas Signature Init
    let sharedCanvas = null;
    const canvas = document.getElementById("shared-sign-canvas");
    if (canvas) {
        const ctx = canvas.getContext("2d");
        let drawing = false;
        let dirty = false;
        const resize = () => {
            const snapshot = dirty ? canvas.toDataURL("image/png") : null;
            const ratio = window.devicePixelRatio || 1;
            const width = Math.floor(canvas.parentElement.getBoundingClientRect().width);
            if (width < 2) return;
            canvas.width = Math.max(1, Math.floor(width * ratio));
            canvas.height = Math.floor(200 * ratio);
            ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
            ctx.lineWidth = 2.2;
            ctx.lineCap = "round";
            ctx.lineJoin = "round";
            ctx.strokeStyle = "#172b4d";
            if (snapshot) {
                const image = new Image();
                image.onload = () => ctx.drawImage(image, 0, 0, width, 200);
                image.src = snapshot;
            }
        };
        const point = (event) => {
            const rect = canvas.getBoundingClientRect();
            return { x: event.clientX - rect.left, y: event.clientY - rect.top };
        };
        const stopDrawing = (event) => {
            drawing = false;
            if (event && canvas.hasPointerCapture && canvas.hasPointerCapture(event.pointerId)) {
                canvas.releasePointerCapture(event.pointerId);
            }
        };
        canvas.addEventListener("pointerdown", (event) => {
            if (event.isPrimary === false) return;
            event.preventDefault();
            drawing = true;
            dirty = true;
            if (canvas.setPointerCapture) canvas.setPointerCapture(event.pointerId);
            const p = point(event);
            ctx.beginPath();
            ctx.moveTo(p.x, p.y);
        });
        canvas.addEventListener("pointermove", (event) => {
            if (!drawing) return;
            event.preventDefault();
            const p = point(event);
            ctx.lineTo(p.x, p.y);
            ctx.stroke();
        });
        ["pointerup", "pointercancel"].forEach((name) => canvas.addEventListener(name, stopDrawing));

        $("#btn-clear-shared-canvas").on("click", function () {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            dirty = false;
        });

        sharedCanvas = {
            resize,
            clear: () => {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                dirty = false;
            },
            data: () => (dirty ? canvas.toDataURL("image/png") : "")
        };
    }

    $("#shared-sign-method").on("change", function () {
        if ($(this).val() === "canvas") {
            $("#shared-sign-canvas-section").show();
            if (sharedCanvas) sharedCanvas.resize();
        } else {
            $("#shared-sign-canvas-section").hide();
        }
    });

    $("#modal-shared-sign").on("shown.bs.modal", function () {
        if (sharedCanvas) sharedCanvas.resize();
    });

    // Buka Modal Sign
    $(".btn-trigger-sign").on("click", function () {
        let role = $(this).data("role");
        $("#form-shared-sign")[0].reset();
        $("#shared-sign-role").val(role);
        $("#shared-sign-method").val("canvas").trigger("change");
        if (sharedCanvas) sharedCanvas.clear();
        let label = role === "mkdt" ? "MKDT" : "Keuangan";
        $("#shared-sign-title").text("Tanda Tangan Pengajuan Fee (" + label + ")");
        $("#modal-shared-sign").modal("show");
    });

    // Submit TTD
    $("#form-shared-sign").on("submit", function (e) {
        e.preventDefault();
        let method = $("#shared-sign-method").val();
        let signData = "";
        if (method === "canvas") {
            signData = sharedCanvas ? sharedCanvas.data() : "";
            if (!signData) {
                Swal.fire("Perhatian", "Silakan bubuhkan tanda tangan pada canvas terlebih dahulu.", "warning");
                return;
            }
        }
        let pwd = $("#shared-sign-password").val();
        if (!pwd) {
            Swal.fire("Perhatian", "Password akun wajib diisi untuk verifikasi keabsahan tanda tangan.", "warning");
            return;
        }

        let fd = new FormData(this);
        fd.append(csrfName, csrfHash);
        if (method === "canvas") {
            fd.append("signature_data", signData);
        }

        $("#btn-submit-shared-sign").prop("disabled", true).html('<span class="spinner-border spinner-border-sm mr-50"></span> Menyimpan...');

        $.ajax({
            url: baseUrl + "api/sales-external/sign-group",
            type: "POST",
            data: fd,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (res) {
                if (res.token) csrfHash = res.token;
                $("#btn-submit-shared-sign").prop("disabled", false).html('<i class="fas fa-check mr-50"></i> Simpan Tanda Tangan');

                if (res.success) {
                    $("#modal-shared-sign").modal("hide");
                    Swal.fire({
                        icon: "success",
                        title: "Berhasil!",
                        text: res.messages || "Tanda tangan berhasil disimpan.",
                        timer: 1500,
                        showConfirmButton: false,
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire("Gagal", res.messages || "Gagal menyimpan tanda tangan.", "error");
                }
            },
            error: function (xhr) {
                $("#btn-submit-shared-sign").prop("disabled", false).html('<i class="fas fa-check mr-50"></i> Simpan Tanda Tangan');
                let err = xhr.responseJSON ? xhr.responseJSON.messages : "Terjadi kesalahan sistem.";
                Swal.fire("Error", err, "error");
            }
        });
    });

    // Format angka ribuan pada input shared
    $(document).on("input", ".shared-num-input", function () {
        let val = $(this).val().replace(/[^\d]/g, "");
        if (val) {
            $(this).val(parseInt(val, 10).toLocaleString("id-ID"));
        } else {
            $(this).val("");
        }
    });

    // Buka Modal Reject Shared
    $(document).on("click", ".btn-trigger-reject", function () {
        let role = $(this).data("role") || "mkdt";
        $("#form-shared-reject")[0].reset();
        $("#shared-reject-role").val(role);
        let label = role === "mkdt" ? "MKDT" : "Keuangan";
        $("#shared-reject-title").text("Tolak Pengajuan & Saran Revisi Harga AJB (" + label + ")");
        $("#modal-shared-reject").modal("show");
    });

    // Submit Reject Shared
    $("#form-shared-reject").on("submit", function (e) {
        e.preventDefault();
        let alasan = $("#shared-reject-alasan").val().trim();
        if (!alasan) {
            Swal.fire("Perhatian", "Keterangan / alasan penolakan wajib diisi.", "warning");
            return;
        }

        let fd = new FormData(this);
        fd.append(csrfName, csrfHash);

        $("#btn-submit-shared-reject").prop("disabled", true).html('<span class="spinner-border spinner-border-sm mr-50"></span> Menyimpan...');

        $.ajax({
            url: baseUrl + "api/sales-external/reject-group",
            type: "POST",
            data: fd,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (res) {
                if (res.token) csrfHash = res.token;
                $("#btn-submit-shared-reject").prop("disabled", false).html('<i class="fas fa-paper-plane mr-50"></i> Simpan Penolakan & Kirim Revisi');

                if (res.success) {
                    $("#modal-shared-reject").modal("hide");
                    Swal.fire({
                        icon: "success",
                        title: "Pengajuan Ditolak!",
                        text: res.messages || "Penolakan dan saran harga berhasil disimpan.",
                        timer: 1500,
                        showConfirmButton: false,
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire("Gagal", res.messages || "Gagal menyimpan penolakan.", "error");
                }
            },
            error: function (xhr) {
                $("#btn-submit-shared-reject").prop("disabled", false).html('<i class="fas fa-paper-plane mr-50"></i> Simpan Penolakan & Kirim Revisi');
                let err = xhr.responseJSON ? xhr.responseJSON.messages : "Terjadi kesalahan sistem.";
                Swal.fire("Error", err, "error");
            }
        });
    });

    // Recalculate Termin dari Shared View
    $("#btn-shared-apply-recalc").on("click", function () {
        let idPengajuan = $(this).data("id");
        if (!idPengajuan) return;

        Swal.fire({
            title: "Perbaharui Nominal Tiap Termin?",
            text: "Sistem akan menerapkan saran harga rekomendasi, menghitung ulang nominal tiap termin, dan mengembalikan status pengajuan menjadi Diajukan.",
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: "#2057a3",
            cancelButtonColor: "#82868b",
            confirmButtonText: "Ya, Perbaharui Nominal",
            cancelButtonText: "Batal"
        }).then((result) => {
            if (result.isConfirmed) {
                let data = { id_pengajuan: idPengajuan };
                data[csrfName] = csrfHash;

                Swal.fire({
                    title: "Memproses Perhitungan Ulang...",
                    text: "Mohon tunggu sebentar.",
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: baseUrl + "api/sales-external/recalculate-termin",
                    type: "POST",
                    data: data,
                    dataType: "json",
                    success: function (res) {
                        if (res.token) csrfHash = res.token;
                        if (res.success) {
                            Swal.fire({
                                icon: "success",
                                title: "Nominal Berhasil Diperbarui!",
                                text: res.messages || "Nominal pencairan tiap termin telah diperbarui.",
                                timer: 1500,
                                showConfirmButton: false,
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire("Gagal", res.messages || "Gagal memperbarui nominal termin.", "error");
                        }
                    },
                    error: function (xhr) {
                        let err = xhr.responseJSON ? xhr.responseJSON.messages : "Terjadi kesalahan sistem.";
                        Swal.fire("Error", err, "error");
                    }
                });
            }
        });
    });
    // Buka Modal Konfirmasi Cair dari Lembar Shared
    $(document).on("click", ".btn-open-cair", function () {
        let idTermin = $(this).data("id");
        let terminKe = $(this).data("termin");
        let nom = parseFloat($(this).data("nominal") || 0);

        $("#form-cair")[0].reset();
        $("#cair-id_termin").val(idTermin);
        $("#cair-termin-label").text("Termin " + terminKe);
        $("#cair-nominal-display").val("Rp " + nom.toLocaleString("id-ID"));
        $("#cair-tanggal").val(new Date().toISOString().slice(0, 10));
        $("#modal-cair").modal("show");
    });

    // Submit Konfirmasi Cair dari Lembar Shared
    $("#form-cair").on("submit", function (e) {
        e.preventDefault();
        let fd = new FormData(this);
        fd.append(csrfName, csrfHash);

        $("#btn-submit-cair").prop("disabled", true).html('<span class="spinner-border spinner-border-sm mr-50"></span> Menyimpan...');

        $.ajax({
            url: baseUrl + "api/sales-external/konfirmasi-cair",
            type: "POST",
            data: fd,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (res) {
                if (res.token) csrfHash = res.token;
                $("#btn-submit-cair").prop("disabled", false).html('<i class="fas fa-check mr-50"></i> Konfirmasi Dana Cair');

                if (res.success) {
                    $("#modal-cair").modal("hide");
                    Swal.fire({
                        icon: "success",
                        title: "Pencairan Berhasil!",
                        text: res.messages || "Dana termin berhasil dicairkan.",
                        timer: 1500,
                        showConfirmButton: false,
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire("Gagal", res.messages || "Gagal mencairkan termin.", "error");
                }
            },
            error: function (xhr) {
                $("#btn-submit-cair").prop("disabled", false).html('<i class="fas fa-check mr-50"></i> Konfirmasi Dana Cair');
                let err = xhr.responseJSON ? xhr.responseJSON.messages : "Terjadi kesalahan sistem.";
                Swal.fire("Error", err, "error");
            }
        });
    });
});
</script>
