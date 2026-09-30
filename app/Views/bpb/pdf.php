<?php
$activeSignatures = array_values(array_filter(
    $bpb['signatures'],
    static fn (array $signature): bool => empty($signature['revoked_at'])
));
$signatureByRole = [];
foreach ($activeSignatures as $signature) {
    $signatureByRole[$signature['role']] = $signature;
}

$signatureMarkup = static function (?array $signature, string $class = ''): string {
    if ($signature && ! empty($signature['data_uri'])) {
        return '<img class="signature-image ' . esc($class) . '" src="' . esc($signature['data_uri']) . '" alt="Tanda tangan">';
    }
    return '<div class="signature-space ' . esc($class) . '">&nbsp;</div>';
};
$signerName = static fn (?array $signature): string => $signature
    ? esc($signature['signer_name'])
    : '................................................';
$ccSignature = $signatureByRole['cc'] ?? null;
$ccSignedAt = $ccSignature ? date('d/m/Y H:i', strtotime((string) $ccSignature['signed_at'])) : '';
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: dejavusans, sans-serif; font-size: 8.5pt; color: #111; }
        h1 { text-align: center; text-decoration: underline; font-family: serif; font-size: 15pt; margin: 0 0 5mm; }
        .header { width: 100%; margin: 0 0 4mm; border-collapse: collapse; table-layout: fixed; }
        .header td { vertical-align: top; }
        .header-meta { width: auto; border-collapse: collapse; font-size: 8pt; }
        .header-meta td { padding: 1mm .7mm; }
        .header-qr-cell { width: 42mm; vertical-align: top; text-align: right; padding: 0 1mm 1mm; }
        .qr-frame { display: inline-block; background: #fff; padding: 2mm; text-align: center; }
        .qr { display: block; width: 37mm; height: 37mm; }
        .qr-caption { font-size: 6pt; margin-top: .5mm; }
        .items { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .items thead { display: table-header-group; }
        .items th, .items td { border: .2mm solid #222; padding: 1.3mm 1.5mm; vertical-align: middle; }
        .items th { background: #e8e8e8; text-align: center; font-size: 7.5pt; }
        .items td { font-size: 7.5pt; }
        .items .center { text-align: center; }
        .items .blank-row td { height: 4.4mm; }
        .signatures { width: 100%; margin-top: 4mm; border-collapse: collapse; table-layout: fixed; page-break-inside: avoid; }
        .signatures > tbody > tr > td { width: 33.333%; vertical-align: top; text-align: center; padding: 0 2mm; }
        .signature-heading { width: 100%; height: 9mm; border-collapse: collapse; }
        .signature-heading td { vertical-align: top; text-align: center; padding: 0; }
        .signature-heading .cc-slot { width: 30%; text-align: right; }
        .role-label { font-weight: bold; padding-top: 1mm !important; }
        .cc-mini { display: inline-block; width: 17mm; text-align: center; font-size: 5.5pt; line-height: 1.1; }
        .cc-mini strong { display: block; font-size: 5.5pt; }
        .cc-mini .signature-image { display: block; max-width: 15mm; max-height: 6mm; margin: .5mm auto 0; }
        .cc-mini .cc-date { display: block; white-space: nowrap; font-size: 5pt; }
        .signature-box { height: 18mm; display: block; text-align: center; }
        .signature-image { display: block; max-width: 42mm; max-height: 18mm; margin: 0 auto; }
        .signature-space { height: 16mm; line-height: 16mm; }
        .signer-name { position: relative; z-index: 1; margin-top: -4mm; border-top: .2mm dotted #444; padding-top: .7mm; min-height: 4mm; font-size: 7pt; background: transparent; }
        .applicant-date { height: 5mm; font-size: 7pt; }
        .verification { width: 100%; margin-top: 3mm; border-collapse: collapse; border-top: .2mm solid #aaa; page-break-inside: avoid; }
        .verification td { vertical-align: top; padding-top: 2mm; }
        .verification-copy { width: 100%; }
        .status { font-size: 7.5pt; }
        .hash { margin-top: .5mm; font-family: dejavusansmono, monospace; font-size: 6pt; word-break: break-all; }
    </style>
</head>
<body>
    <h1>BON PERMINTAAN BARANG</h1>
    <table class="header">
        <tr>
            <td>
                <table class="header-meta">
                    <tr><td width="28%">Nomor</td><td>: <?= esc($bpb['nomor'] ?: 'Draft') ?></td></tr>
                    <tr><td>Nama</td><td>: <?= esc($bpb['applicant_name']) ?></td></tr>
                    <tr><td>Departemen</td><td>: <?= esc($bpb['applicant_department'] ?: '-') ?></td></tr>
                </table>
            </td>
            <td class="header-qr-cell">
                <?php if ($bpb['qr_data_uri']): ?>
                    <div class="qr-frame">
                        <img class="qr" src="<?= esc($bpb['qr_data_uri']) ?>" alt="QR verifikasi">
                        <div class="qr-caption">Verifikasi dokumen</div>
                    </div>
                <?php endif; ?>
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr><th width="6%">No</th><th width="48%">Nama Barang</th><th width="17%">Jumlah / Satuan</th><th>Keterangan</th></tr>
        </thead>
        <tbody>
            <?php foreach ($bpb['items'] as $index => $item): ?>
                <tr>
                    <td class="center"><?= $index + 1 ?></td>
                    <td><?= esc($item['nama_barang']) ?></td>
                    <td class="center"><?= esc($item['jumlah']) ?> <?= esc($item['satuan'] ?? '') ?></td>
                    <td><?= esc($item['keterangan'] ?: '') ?></td>
                </tr>
            <?php endforeach; ?>
            <?php for ($i = count($bpb['items']); $i < 5; $i++): ?>
                <tr class="blank-row"><td>&nbsp;</td><td></td><td></td><td></td></tr>
            <?php endfor; ?>
        </tbody>
    </table>

    <table class="signatures">
        <tr>
            <td>
                <div class="applicant-date">&nbsp;</div>
                <table class="signature-heading"><tr><td class="role-label">Verifikasi Pengeluaran</td></tr></table>
                <div class="signature-box"><?= $signatureMarkup($signatureByRole['expense_verifier'] ?? null) ?></div>
                <div class="signer-name">(<?= $signerName($signatureByRole['expense_verifier'] ?? null) ?>)</div>
            </td>
            <td>
                <div class="applicant-date">&nbsp;</div>
                <table class="signature-heading">
                    <tr>
                        <td class="role-label">Mengetahui</td>
                        <td class="cc-slot">
                            <?php if ($ccSignature): ?>
                                <span class="cc-mini">
                                    <strong>Paraf CC</strong>
                                    <?= $signatureMarkup($ccSignature) ?>
                                    <span class="cc-date"><?= esc($ccSignedAt) ?></span>
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
                <div class="signature-box"><?= $signatureMarkup($signatureByRole['approver'] ?? null) ?></div>
                <div class="signer-name">(<?= $signerName($signatureByRole['approver'] ?? null) ?>)</div>
            </td>
            <td>
                <div class="applicant-date"><?= esc($bpb['submitted_date_label']) ?></div>
                <table class="signature-heading"><tr><td class="role-label">Pemohon</td></tr></table>
                <div class="signature-box"><?= $signatureMarkup($signatureByRole['applicant'] ?? null) ?></div>
                <div class="signer-name">(<?= $signerName($signatureByRole['applicant'] ?? null) ?>)</div>
            </td>
        </tr>
    </table>

    <table class="verification">
        <tr>
            <td class="verification-copy">
                <div class="status"><strong>Status:</strong> <?= esc($bpb['status_label']) ?></div>
                <strong>Hash SHA-256:</strong>
                <div class="hash"><?= esc($bpb['current_document_hash'] ?: '-') ?></div>
            </td>
        </tr>
    </table>
</body>
</html>
