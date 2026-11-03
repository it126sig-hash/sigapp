<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pengajuan Fee Sales</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 5.8pt;
            color: #000;
            line-height: 1.15;
        }
        .header-title {
            text-align: center;
            font-weight: bold;
            font-size: 9pt;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        table.table-report {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        table.table-report th, table.table-report td {
            border: 1px solid #000;
            padding: 3px 2px;
            vertical-align: middle;
        }
        table.table-report th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
            font-size: 5.5pt;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .instansi-sub {
            font-size: 4.8pt;
            font-weight: normal;
            color: #444;
            margin-top: 1px;
            line-height: 1.1;
        }
        .highlight-yellow {
            background-color: #ffff00 !important;
            font-weight: bold;
        }
        .status-desc {
            font-size: 5.5pt;
            display: block;
            margin-top: 2px;
            line-height: 1.1;
        }
        .status-diajukan {
            color: #856404;
        }
        .status-cair {
            color: #155724;
            font-weight: bold;
        }
        .no-cek-desc {
            font-size: 5.5pt;
            color: #004085;
            font-weight: bold;
            display: block;
            margin-top: 1px;
            line-height: 1.1;
        }
        .signature-box {
            width: 100%;
            margin-top: 5px;
            margin-bottom: 25px;
        }
        .signature-inner {
            float: right;
            width: 220px;
            text-align: center;
        }
        .signature-space {
            height: 35px;
        }
        .signature-name {
            font-weight: bold;
            text-decoration: underline;
        }
        .signature-date {
            font-size: 6.5pt;
            color: #333;
        }
        .total-row {
            background-color: #f9f9f9;
            font-weight: bold;
        }
    </style>
</head>
<body>

<?php
if (! function_exists('fmtDatePdf')) {
    function fmtDatePdf($d) {
        if (! $d || $d === '0000-00-00') return '-';
        return date('d-M-y', strtotime($d));
    }
}
if (! function_exists('fmtRupiahPdf')) {
    function fmtRupiahPdf($num) {
        if (! $num || $num == 0) return '-';
        return number_format((float) $num, 0, ',', '.');
    }
}
if (! function_exists('getSalesInitialsPdf')) {
    function getSalesInitialsPdf($name) {
        $clean = trim((string) $name);
        if ($clean === '' || $clean === '-') {
            return '';
        }
        $words = preg_split('/\s+/', $clean);
        $initials = '';
        foreach ($words as $w) {
            if ($w !== '') {
                $initials .= mb_strtoupper(mb_substr($w, 0, 1));
            }
        }
        return $initials;
    }
}

$tglPengajuanRaw = (! empty($group->tanggal_pengajuan_sales) && $group->tanggal_pengajuan_sales !== '0000-00-00')
    ? $group->tanggal_pengajuan_sales
    : (! empty($group->tanggal_pengajuan) && $group->tanggal_pengajuan !== '0000-00-00'
        ? $group->tanggal_pengajuan
        : date('Y-m-d'));

$tglPengajuanLabel = date('j F Y', strtotime($tglPengajuanRaw));

// Mapping nama bulan Indonesia
$bulanIndo = [
    'January' => 'JANUARI', 'February' => 'FEBRUARI', 'March' => 'MARET',
    'April' => 'APRIL', 'May' => 'MEI', 'June' => 'JUNI',
    'July' => 'JULI', 'August' => 'AGUSTUS', 'September' => 'SEPTEMBER',
    'October' => 'OKTOBER', 'November' => 'NOVEMBER', 'December' => 'DESEMBER'
];
foreach ($bulanIndo as $en => $id) {
    $tglPengajuanLabel = str_ireplace($en, $id, $tglPengajuanLabel);
}
?>

<?php 
$loopIdx = 0;
foreach ($salesGroups as $salesName => $items): 
    $salesInitials = getSalesInitialsPdf($salesName);
    $baseNomor = $group->nomor_pengajuan ?? '-';
    $nomorTampil = (! empty($salesInitials) && $baseNomor !== '-')
        ? "{$baseNomor}.{$salesInitials}"
        : $baseNomor;
?>
    <div style="<?= $loopIdx > 0 ? 'page-break-before: always; margin-top: 15px;' : '' ?>">
        <div style="font-size: 7.5pt; font-weight: bold; text-align: left; margin-bottom: 2px;">
            No: <?= esc($nomorTampil) ?>
        </div>
        <div class="header-title">
            PENGAJUAN FEE SALES &mdash; TANGGAL <?= esc(strtoupper($tglPengajuanLabel)) ?><br>
            <span style="font-size: 8.5pt; color: #222; font-weight: bold; letter-spacing: 0.3px;">
                SALES: <?= esc(strtoupper($salesName)) ?>
            </span>
        </div>
    </div>

    <table class="table-report">
        <thead>
            <tr>
                <th rowspan="2" style="width: 18px;">No</th>
                <th rowspan="2" style="width: 50px;">Tgl Booking</th>
                <th colspan="2">Kavling</th>
                <th rowspan="2" style="width: 42px;">Type</th>
                <th rowspan="2" style="width: 110px;">Nama</th>
                <th rowspan="2" style="width: 65px;">Harga Jual</th>
                <th rowspan="2" style="width: 50px;">Discount</th>
                <th rowspan="2" style="width: 65px;">harga jual</th>
                <th rowspan="2" style="width: 50px;">Kel Tanah</th>
                <th rowspan="2" style="width: 65px;">Harga Netto</th>
                <th rowspan="2" style="width: 35px;">PPJB /AJB</th>
                <th rowspan="2" style="width: 60px;">Sales</th>
                <th rowspan="2" style="width: 35px;">FEE</th>
                <th rowspan="2" style="width: 65px;">JUMLAH</th>
                <th colspan="4">Pencairan</th>
                <th rowspan="2" style="width: 50px;">TGL AKAD</th>
            </tr>
            <tr>
                <th style="width: 65px;">Blok</th>
                <th style="width: 25px;">No</th>
                <th style="width: 60px;">Termin 1 30%</th>
                <th style="width: 60px;">Termin 2 60%</th>
                <th style="width: 60px;">Termin 3 10%</th>
                <th style="width: 60px;">Termin 100%</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            $subtotalNominalDiajukan = 0;

            // Kelompokkan item per unit kavling (id_mkdt) agar 1 baris mewakili 1 unit kavling,
            // dan mendukung pemilihan lebih dari 1 termin pada kavling yang sama.
            $groupedUnitItems = [];
            foreach ($items as $item) {
                $unitKey = $item->id_mkdt;
                if (! isset($groupedUnitItems[$unitKey])) {
                    $groupedUnitItems[$unitKey] = clone $item;
                    $groupedUnitItems[$unitKey]->termin_targets = [(int) $item->termin_yang_diajukan];
                    $groupedUnitItems[$unitKey]->nominal_diajukan_total = (float) $item->nominal_diajukan;
                } else {
                    $groupedUnitItems[$unitKey]->termin_targets[] = (int) $item->termin_yang_diajukan;
                    $groupedUnitItems[$unitKey]->nominal_diajukan_total += (float) $item->nominal_diajukan;
                }
            }

            uasort($groupedUnitItems, function ($a, $b) {
                $tglA = (! empty($a->akad_tgl) && $a->akad_tgl !== '0000-00-00' && strtotime($a->akad_tgl) > 0) ? strtotime($a->akad_tgl) : PHP_INT_MAX;
                $tglB = (! empty($b->akad_tgl) && $b->akad_tgl !== '0000-00-00' && strtotime($b->akad_tgl) > 0) ? strtotime($b->akad_tgl) : PHP_INT_MAX;
                if ($tglA === $tglB) {
                    return ($a->id_mkdt ?? 0) <=> ($b->id_mkdt ?? 0);
                }
                return $tglA <=> $tglB;
            });

            foreach ($groupedUnitItems as $item):
                $diskonNilai = (float) ($item->harga_diskon_hargajual ?? 0);
                $hargaJualNetto = (float) ($item->harga_jual ?? 0) - $diskonNilai;
                $isAjbRaw = strtoupper(trim((string) ($item->is_ajb ?? '')));
                $isAjb = ($isAjbRaw === 'AJB' || $isAjbRaw === '1') ? 'AJB' : 'PPJB';
                $terminTargets = $item->termin_targets ?? [(int) $item->termin_yang_diajukan];
                $skema = $item->skema_termin ?? '30_60_10';

                $t1 = $item->all_termin[1] ?? null;
                $t2 = $item->all_termin[2] ?? null;
                $t3 = $item->all_termin[3] ?? null;

                $subtotalNominalDiajukan += (float) ($item->nominal_diajukan_total ?? 0);
            ?>
            <tr>
                <td class="text-center"><?= $no++ ?></td>
                <td class="text-center"><?= fmtDatePdf($item->booking_tgl ?? null) ?></td>
                <td class="text-left"><?= esc($item->nama_jalan ?? '-') ?></td>
                <td class="text-center"><?= esc($item->no_kavling ?? '-') ?></td>
                <td class="text-center"><?= esc($item->tipe_rumah ?? '-') ?></td>
                <td class="text-left font-bold">
                    <div><?= esc($item->nama_konsumen ?? '-') ?></div>
                    <?php if (! empty($item->nama_instansi)): ?>
                        <div class="instansi-sub"><?= esc($item->nama_instansi) ?></div>
                    <?php endif; ?>
                </td>
                <td class="text-right"><?= fmtRupiahPdf($item->harga_jual ?? 0) ?></td>
                <td class="text-right"><?= fmtRupiahPdf($diskonNilai) ?></td>
                <td class="text-right"><?= fmtRupiahPdf($hargaJualNetto) ?></td>
                <td class="text-right"><?= fmtRupiahPdf($item->harga_penambahan_tanah ?? 0) ?></td>
                <?php
                $ajbNom = (float) (($item->harga_net_ajb ?? 0) ?: (($item->fee_sales_harga_net_ajb ?? 0) ?: ($item->fee_harga_net_ajb ?? 0)));
                $netNom = (float) ($item->harga_jual_net ?? 0);
                $hjNom  = (float) ($item->harga_jual ?? 0);
                $hargaAjbTampil = $ajbNom > 0 ? $ajbNom : ($netNom > 0 ? $netNom : $hjNom);
                ?>
                <td class="text-right"><?= fmtRupiahPdf($hargaAjbTampil) ?></td>
                <td class="text-center"><?= $isAjb ?></td>
                <td class="text-center"><?= esc($item->sales_nama ?? '-') ?></td>
                <td class="text-center"><?= number_format((float) ($item->persentase_fee ?? 0), 2, ',', '.') ?>%</td>
                <td class="text-right font-bold"><?= fmtRupiahPdf($item->total_fee ?? 0) ?></td>

                <!-- Termin 1 (30%) -->
                <?php
                $isTarget1 = ($skema !== '100' && in_array(1, $terminTargets, true));
                $cls1 = $isTarget1 ? 'highlight-yellow' : '';
                ?>
                <td class="text-right <?= $cls1 ?>">
                    <?php if ($skema !== '100' && $t1): ?>
                        <div><?= fmtRupiahPdf($t1->nominal ?? 0) ?></div>
                        <?php if (($t1->status ?? '') === 'sudah_cair'): ?>
                            <span class="status-desc status-cair">Cair:<br><?= fmtDatePdf($t1->tanggal_cair ?? null) ?></span>
                            <?php if (! empty($t1->no_cek)): ?>
                                <span class="no-cek-desc">No Cek:<br><?= esc($t1->no_cek) ?></span>
                            <?php endif; ?>
                        <?php elseif (($t1->status ?? '') === 'pengajuan_spp'): ?>
                            <span class="status-desc status-diajukan">Diajukan:<br><?= fmtDatePdf($t1->tanggal_pengajuan_spp ?? null) ?></span>
                        <?php endif; ?>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>

                <!-- Termin 2 (60%) -->
                <?php
                $isTarget2 = ($skema !== '100' && in_array(2, $terminTargets, true));
                $cls2 = $isTarget2 ? 'highlight-yellow' : '';
                ?>
                <td class="text-right <?= $cls2 ?>">
                    <?php if ($skema !== '100' && $t2): ?>
                        <div><?= fmtRupiahPdf($t2->nominal ?? 0) ?></div>
                        <?php if (($t2->status ?? '') === 'sudah_cair'): ?>
                            <span class="status-desc status-cair">Cair:<br><?= fmtDatePdf($t2->tanggal_cair ?? null) ?></span>
                            <?php if (! empty($t2->no_cek)): ?>
                                <span class="no-cek-desc">No Cek:<br><?= esc($t2->no_cek) ?></span>
                            <?php endif; ?>
                        <?php elseif (($t2->status ?? '') === 'pengajuan_spp'): ?>
                            <span class="status-desc status-diajukan">Diajukan:<br><?= fmtDatePdf($t2->tanggal_pengajuan_spp ?? null) ?></span>
                        <?php endif; ?>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>

                <!-- Termin 3 (10%) -->
                <?php
                $isTarget3 = ($skema !== '100' && in_array(3, $terminTargets, true));
                $cls3 = $isTarget3 ? 'highlight-yellow' : '';
                ?>
                <td class="text-right <?= $cls3 ?>">
                    <?php if ($skema !== '100' && $t3): ?>
                        <div><?= fmtRupiahPdf($t3->nominal ?? 0) ?></div>
                        <?php if (($t3->status ?? '') === 'sudah_cair'): ?>
                            <span class="status-desc status-cair">Cair:<br><?= fmtDatePdf($t3->tanggal_cair ?? null) ?></span>
                            <?php if (! empty($t3->no_cek)): ?>
                                <span class="no-cek-desc">No Cek:<br><?= esc($t3->no_cek) ?></span>
                            <?php endif; ?>
                        <?php elseif (($t3->status ?? '') === 'pengajuan_spp'): ?>
                            <span class="status-desc status-diajukan">Diajukan:<br><?= fmtDatePdf($t3->tanggal_pengajuan_spp ?? null) ?></span>
                        <?php endif; ?>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>

                <!-- Termin 100% -->
                <?php
                $isTarget100 = ($skema === '100' && in_array(1, $terminTargets, true));
                $cls100 = $isTarget100 ? 'highlight-yellow' : '';
                ?>
                <td class="text-right <?= $cls100 ?>">
                    <?php if ($skema === '100' && $t1): ?>
                        <div><?= fmtRupiahPdf($t1->nominal ?? 0) ?></div>
                        <?php if (($t1->status ?? '') === 'sudah_cair'): ?>
                            <span class="status-desc status-cair">Cair:<br><?= fmtDatePdf($t1->tanggal_cair ?? null) ?></span>
                            <?php if (! empty($t1->no_cek)): ?>
                                <span class="no-cek-desc">No Cek:<br><?= esc($t1->no_cek) ?></span>
                            <?php endif; ?>
                        <?php elseif (($t1->status ?? '') === 'pengajuan_spp'): ?>
                            <span class="status-desc status-diajukan">Diajukan:<br><?= fmtDatePdf($t1->tanggal_pengajuan_spp ?? null) ?></span>
                        <?php endif; ?>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>

                <td class="text-center"><?= fmtDatePdf($item->akad_tgl ?? null) ?></td>
            </tr>
            <?php endforeach; ?>

            <!-- Baris Total Pengajuan untuk Sales ini -->
            <tr class="total-row">
                <td colspan="15" class="text-right font-bold">Total Pengajuan:</td>
                <td colspan="4" class="text-right font-bold highlight-yellow">
                    <?= fmtRupiahPdf($subtotalNominalDiajukan) ?>
                </td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <!-- Tanda Tangan: Pembuat, MKDT, Keuangan -->
    <table style="width: 100%; border: none; margin-top: 15px; margin-bottom: 25px; page-break-inside: avoid;">
        <tr>
            <td style="width: 33%; border: none; text-align: center; vertical-align: top;">
                <div style="font-size: 7.5pt; font-weight: bold; margin-bottom: 4px;">Diajukan Oleh:</div>
                <div style="height: 55px; line-height: 55px;">
                    <?php if (! empty($signatures['pembuat']['data'])): ?>
                        <img src="<?= $signatures['pembuat']['data'] ?>" style="max-height: 50px; max-width: 140px; vertical-align: middle;" alt="TTD Pembuat">
                    <?php else: ?>
                        <div class="signature-space"></div>
                    <?php endif; ?>
                </div>
                <div class="signature-name"><?= esc($signatures['pembuat']['nama'] ?? ($group->creator_username ?? 'Pembuat')) ?></div>
                <div class="signature-date">
                    <?= ! empty($signatures['pembuat']['at']) ? date('d/m/Y H:i', strtotime($signatures['pembuat']['at'])) : date('d/m/Y', strtotime($group->tanggal_pengajuan ?? date('Y-m-d'))) ?>
                </div>
            </td>
            <td style="width: 33%; border: none; text-align: center; vertical-align: top;">
                <div style="font-size: 7.5pt; font-weight: bold; margin-bottom: 4px;">Diperiksa Oleh (MKDT):</div>
                <div style="height: 55px; line-height: 55px;">
                    <?php if (! empty($signatures['mkdt']['data'])): ?>
                        <img src="<?= $signatures['mkdt']['data'] ?>" style="max-height: 50px; max-width: 140px; vertical-align: middle;" alt="TTD MKDT">
                    <?php else: ?>
                        <div class="signature-space"></div>
                    <?php endif; ?>
                </div>
                <div class="signature-name"><?= esc($signatures['mkdt']['nama'] ?: '(Belum Ditandatangani)') ?></div>
                <div class="signature-date">
                    <?= ! empty($signatures['mkdt']['at']) ? date('d/m/Y H:i', strtotime($signatures['mkdt']['at'])) : '-' ?>
                </div>
            </td>
            <td style="width: 34%; border: none; text-align: center; vertical-align: top;">
                <div style="font-size: 7.5pt; font-weight: bold; margin-bottom: 4px;">Disetujui Oleh (Keuangan):</div>
                <div style="height: 55px; line-height: 55px;">
                    <?php if (! empty($signatures['keuangan']['data'])): ?>
                        <img src="<?= $signatures['keuangan']['data'] ?>" style="max-height: 50px; max-width: 140px; vertical-align: middle;" alt="TTD Keuangan">
                    <?php else: ?>
                        <div class="signature-space"></div>
                    <?php endif; ?>
                </div>
                <div class="signature-name"><?= esc($signatures['keuangan']['nama'] ?: '(Belum Ditandatangani)') ?></div>
                <div class="signature-date">
                    <?= ! empty($signatures['keuangan']['at']) ? date('d/m/Y H:i', strtotime($signatures['keuangan']['at'])) : '-' ?>
                </div>
            </td>
        </tr>
    </table>
<?php 
    $loopIdx++;
endforeach; 
?>

</body>
</html>
