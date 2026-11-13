<!DOCTYPE html>
<html lang="en">
<?php
if (!function_exists('format_date')) {
    function format_date($d)
    {
        $raw = trim((string) ($d ?? ''));
        if ($raw === '' || $raw === '-' || strpos($raw, '0000-00-00') === 0) {
            return '-';
        }

        $bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw)) {
            $ts = strtotime($raw);
            if ($ts === false) {
                return htmlspecialchars((string) $d, ENT_QUOTES, 'UTF-8');
            }
            $raw = date('Y-m-d', $ts);
        }

        $parts = explode('-', $raw);
        if (count($parts) !== 3) {
            return htmlspecialchars((string) $d, ENT_QUOTES, 'UTF-8');
        }

        $monthIndex = (int) $parts[1] - 1;
        if (!isset($bulan[$monthIndex])) {
            return htmlspecialchars((string) $d, ENT_QUOTES, 'UTF-8');
        }

        return $parts[2] . ' ' . $bulan[$monthIndex] . ' ' . $parts[0];
    }
}
?>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tagihan <?= $konsumen ?></title>
</head>
<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        font-size: 10pt;
        margin: -45px;
    }

    th,
    td {
        padding: 5px;
    }

    th {
        border-bottom: 0.5px solid #000 !important;
        border-collapse: collapse;
    }

    td {
        border-bottom: 1px solid #ddd;
        border-collapse: collapse;
    }

    .foot {
        border-top: 0.5px solid #000 !important;
        font-weight: bold;
    }

    .bold td {
        font-weight: bold;
    }

    p {
        margin: 0px;
    }
</style>

<body>
    <?php if (!empty($kop)): ?>
        <img src="<?= htmlspecialchars((string) $kop, ENT_QUOTES, 'UTF-8') ?>" alt="" style="position: fixed; top: -45px; left: -45px; width: 210mm; height: auto; z-index: -1;">
    <?php endif; ?>
    <div style="padding-top:120px; text-align:center">
        <h3>Surat Tagihan</h3>
    </div>
    <div style="padding: 10px 70px 50px 70px">
        <p style="float:left">
            <span><b>Ditagihkan Ke:</b></span>
            <span><br></span>
            <span><?= $konsumen ?></span>
            <span><br></span>
            <span><?= $alamat ?></span>
        </p>
        <p style="float:right; text-align:right">
            <b>No :</b><span><br></span>
            <span><?= $no_sruat ?></span>
        </p>
    </div>
    <table style="width:100%; padding: 50px 70px 0px 70px;">
        <thead>
            <tr>
                <th scope="col" class="text-nowrap">No</th>
                <th scope="col" class="text-nowrap">Berita Acara</th>
                <th scope="col" class="text-nowrap">Jatuh Tempo</th>

                <th scope="col" class="text-nowrap">Nominal</th>

            </tr>
        </thead>
        <tbody id="tb-print-data-tagihan">
            <?= $table ?>
        </tbody>
    </table>
    <div style="padding: 10px 70px 10px 70px">
        <span><b>Keterangan:</b></span><br>
        <ol>
            <li>Lakukan pembayaran sebelum tanggal jatuh tempo untuk menghindari denda</li>
            <li>Pembayaran yang sah hanya melalui transfer ke rekening atas nama PT. Sanggarindah Karya Sentosa Raya BCA KC Setiabudi - Bandung, Nomor Rekening : 2337 887 887 wajib melampirkan bukti transfer</li>
            <li>Lakukan pembayaran sebelum tanggal jatuh tempo untuk menghindari denda</li>
            <li>Pembayaran yang sah hanya melalui transfer ke rekening atas nama PT. Sanggarindah Karya Sentosa Raya BCA KC Setiabudi - Bandung, Nomor Rekening : 2337 887 887 wajib melampirkan bukti transfer</li>
            <li>Lakukan pembayaran sebelum tanggal jatuh tempo untuk menghindari denda</li>
            <li>Pembayaran yang sah hanya melalui transfer ke rekening atas nama PT. Sanggarindah Karya Sentosa Raya BCA KC Setiabudi - Bandung, Nomor Rekening : 2337 887 887 wajib melampirkan bukti transfer</li>
        </ol>
    </div>
    <div style="float: right; text-align:center;padding: 20px 70px 0px 70px">
            Bandung, <?= format_date($tanggal_surat_tagihan ?? '') ?>
            <br>
            <br>
            <br>
            <br>
            <br>
            <?=$nama?>
        </div>
</body>

</html>