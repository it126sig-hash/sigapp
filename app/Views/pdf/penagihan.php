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

// Parse Tagihan
$tagihanHTML = '';
$tagihanData = json_decode($inv->tagihan, true);
if (json_last_error() === JSON_ERROR_NONE && is_array($tagihanData)) {
    $total = 0;
    $jatuh_tempo_tgl = $tagihanData[0]['jatuh_tempo_tgl'];
    foreach ($tagihanData as $i => $item) {
        $nominal = (int)$item['nominal'];
        $total += $nominal;
    }

    $tagihanHTML .= "<tr>
        <td >Nilai Tagihan</td>
        <td >:</td>
        <td >Rp " . number_format($total, 0, ',', '.') . "</td>
    </tr>
    <tr>
        <td >Jatuh Tempo</td>
        <td >:</td>
        <td >" . date('d F Y', strtotime($jatuh_tempo_tgl)) . "</td>
    </tr>
    ";
}

if (empty($inv->nomor_surat) || $inv->nomor_surat == "") {
    $inv->nomor_surat = "No Surat Belum dibuat";
} else {
    if (strpos($inv->nomor_surat, '/') === 0) {
        $inv->nomor_surat = '&nbsp;&nbsp;&nbsp;' . $inv->nomor_surat;
    }
}
?>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tagihan <?= $konsumen->nama_konsumen ?></title>

    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .text-justify {
            text-align: justify;
        }

        .mt-2 {
            margin-top: 20px;
        }

        .mt-1 {
            margin-top: 10px;
        }

        .mb-2 {
            margin-bottom: 20px;
        }

        .indent {
            padding-left: 20px;
        }
    </style>
</head>

<body>
    <div class="container" style="z-index:999">
        <div class="content" style="">
            <div style="text-align: right; margin-bottom: 20px;">
                Bandung, <?= format_date($inv->tanggal_invoice) ?>
            </div>

            <table style="border: 0px; width: 100%;">
                <tr>
                    <td style="width: 100px; vertical-align: top;">Nomor</td>
                    <td style="width: 10px; vertical-align: top;">:</td>
                    <td style="vertical-align: top;"><?= $inv->nomor_surat ?? "No Surat Belum dibuat" ?></td>
                </tr>
                <tr>
                    <td style="vertical-align: top;">Lamp</td>
                    <td style="vertical-align: top;">:</td>
                    <td style="vertical-align: top;">-</td>
                </tr>
                <tr>
                    <td style="vertical-align: top;">Perihal</td>
                    <td style="vertical-align: top;">:</td>
                    <td style="vertical-align: top;">Penagihan Pembayaran Rumah</td>
                </tr>
            </table>

            <div class="mt-2 mb-2">
                Kepada Yth. Bapak/Ibu <?= ucwords(strtolower($konsumen->nama_konsumen)) ?? '-' ?><br>
                Di Tempat
            </div>

            <div class="text-justify mt-2 mb-2">
                Dengan hormat,<br>
                Terima kasih kami ucapkan atas kepercayaan Bapak/Ibu sudah memilih pembelian rumah di <?= ucwords(strtolower($proyek_info->nama_proyek)) ?>
                <br>
                <br>
                Bersama surat ini, kami dari <?= $proyek_info->nama_pt ?> bermaksud mengingatkan Bapak/Ibu terkait kewajiban pembayaran atas pembelian unit rumah di perumahan Sanggar Indah Palastri dengan rincian sebagai berikut
            </div>

            <table style="margin-bottom: 20px;">
                <tr>
                    <td style="width: 150px;">Nama Pemesan</td>
                    <td style="width: 10px;">:</td>
                    <td><?= ucwords(strtolower($konsumen->nama_konsumen)) ?? '-' ?></td>
                </tr>
                <tr>
                    <td>Kavling</td>
                    <td>:</td>
                    <td><?= ucwords(strtolower($kavling->nama_jalan ?? '-')) ?> No. <?= ($kavling->no_kavling ?? '-') ?></td>
                </tr>
                <?= $tagihanHTML ?>
            </table>

            <div class="text-justify mb-2">
                Hingga saat ini, kami mencatat bahwa pembayaran tersebut belum kami terima. Untuk itu, kami mohon agar Bapak/Ibu dapat segera melakukan pembayaran melalui transfer ke rekening:
            </div>

            <table style="margin-bottom: 20px;">
                <tr>
                    <td style="width: 150px;">BANK</td>
                    <td style="width: 10px;">:</td>
                    <td><?= $proyek_info->bank ?? '-' ?></td>
                </tr>
                <tr>
                    <td>No. Rekening</td>
                    <td>:</td>
                    <td><?= $proyek_info->no_rek ?? '-' ?></td>
                </tr>
                <tr>
                    <td>Atas Nama</td>
                    <td>:</td>
                    <td><?= $proyek_info->atas_nama ?? '-' ?></td>
                </tr>
            </table>



            <div class="text-justify mb-2">
                Mohon abaikan surat ini apabila Bapak/Ibu telah melakukan pembayaran.<br>
                Untuk konfirmasi dan informasi lebih lanjut, Bapak/Ibu dapat menghubungi kami di
                <b><?= $proyek_info->no_telepon ?? '-' ?></b>.
            </div>

            <div class="text-justify mb-2">
                Atas perhatian dan kerjasamanya yang baik, kami ucapkan terima kasih.
            </div>

            <table style="margin-bottom: 20px;vertical-align: top;">
                <tr>
                    <td style="width: 50%;vertical-align: top;">
                        <div>
                            <i>Tembusan :</i><br>
                            <i>1. Direktur Utama / CEO</i><br>
                            <i>2. Mgr. Akunting</i><br>
                            <i>3. Arsip</i>
                        </div>
                    </td>
                    <td style="width: 50%; text-align:center; vertical-align: top;">
                        <div>
                            Hormat kami,<br>
                            <b><?= $proyek_info->nama_pt ?? 'PT. Sanggarindah Karyasentosa Raya' ?></b>
                            <br>
                            <?php if (!empty($inv->ttd_img)): ?>
                                <img src="<?= $inv->ttd_img ?>" style="max-height: 80px; margin: 10px 0;">
                            <?php else: ?>
                                <br><br><br><br><br>
                            <?php endif; ?>
                            <br>
                            <b><?= $proyek_info->nama_direktur ?? 'Nama Direktur' ?></b><br>
                            Direktur
                        </div>
                    </td>
                </tr>

            </table>




        </div>
    </div>
</body>

</html>