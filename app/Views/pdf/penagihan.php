<!DOCTYPE html>
<html lang="en">
<?php
function format_date($d)
{
    $bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    if ($d) {
        $d = explode("-", $d);
        return $d[2] . " " . $bulan[(int)$d[1] - 1] . " " . $d[0];
    }
    return $d;
}

// Check if JSON or HTML string
$tagihanHTML = '';
$tagihanData = json_decode($inv->tagihan, true);
if (json_last_error() === JSON_ERROR_NONE && is_array($tagihanData)) {
    $total = 0;
    foreach ($tagihanData as $i => $item) {
        $nominal = (int)$item['nominal'];
        $total += $nominal;
        $tagihanHTML .= "<tr>
            <td style='text-align:center'>" . ($i + 1) . "</td>
            <td>{$item['berita_acara']}</td>
            <td>" . format_date($item['jatuh_tempo_tgl']) . "</td>
            <td style='text-align:right'>Rp " . number_format($nominal, 0, ',', '.') . "</td>
        </tr>";
    }
    $tagihanHTML .= "<tr>
        <th colspan='3' class='foot' style='text-align:right'>Total Tagihan</th>
        <th class='foot' style='text-align:right'>Rp " . number_format($total, 0, ',', '.') . "</th>
    </tr>";
} else {
    // legacy support if needed
    $tagihanHTML = $inv->tagihan;
}

?>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tagihan <?= $konsumen->nama_konsumen ?></title>

    <style>
        table {
            width: 100%;
            padding-bottom: 40px;
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

        .section {
            margin-top: 200px;
        }
    </style>
</head>

<body>
    <div class="container" style="z-index:999">
        <div class="content">
            <div style="text-align:center">
                <h3>Surat Tagihan<br>(<?= $inv->no_inv ?>)</h3>
            </div>
            <table style="border: 0px solid !important">
                <tr>
                    <td>
                        <p style="float:left">
                            <span><b>Ditagihkan Ke:</b></span>
                            <span><br></span>
                            <span><?= $konsumen->nama_konsumen ?? '-' ?></span>
                            <span><br></span>
                            <span><?= $konsumen->alamat_konsumen ?? '-' ?></span>
                        </p>
                    </td>
                    <td style="float:right; text-align:right">
                        <p>
                            <b>Perumahan :</b><span>
                                <br><?= $kavling->nama_proyek ?? '-' ?><br>
                                <?= ($kavling->nama_jalan ?? '-') ?> No. <?= ($kavling->no_kavling ?? '-') ?><br>
                            </span>
                            <span></span>
                        </p>
                    </td>
                </tr>
            </table>
            <table id="table">
                <thead>
                    <tr>
                        <th scope="col" class="text-nowrap" style="text-align:center">No</th>
                        <th scope="col" class="text-nowrap" style="text-align:left">Berita Acara</th>
                        <th scope="col" class="text-nowrap" style="text-align:left">Jatuh Tempo</th>
                        <th scope="col" class="text-nowrap" style="text-align:right">Nominal</th>
                    </tr>
                </thead>
                <tbody id="tb-print-data-tagihan">
                    <?= $tagihanHTML ?>
                </tbody>
            </table>
            <div class="">
                <span><b>Syarat & Ketentuan:</b></span><br>
                <?= $inv->terms ?>
            </div>
            <br>
            <table style="border:0px">
                <tr>
                    <td width="33%" style="border:0px"></td>
                    <td width="33%" style="border:0px"></td>
                    <td width="33%" style="text-align: center; border:0px">
                        Bandung, <?= format_date($inv->tanggal_invoice) ?>
                        <br>
                        <?php if(!empty($inv->ttd_img)): ?>
                            <?php if(strpos($inv->ttd_img, 'data:image') === 0): ?>
                                <img src="<?= $inv->ttd_img ?>" style="max-height: 80px; margin: 10px 0;">
                            <?php else: ?>
                                <img src="<?= base_url($inv->ttd_img) ?>" style="max-height: 80px; margin: 10px 0;">
                            <?php endif; ?>
                        <?php else: ?>
                            <br><br><br><br>
                        <?php endif; ?>
                        <br>
                        <?= $nama->nama_karyawan ?? 'Pembuat' ?>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>