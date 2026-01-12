<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tagihan </title>
</head>
<?php

use PHPUnit\Event\Test\TestStubForIntersectionOfInterfacesCreated;

function penyebut($nilai)
{
    $nilai = abs($nilai);
    $huruf = array("", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
    $temp = "";
    if ($nilai < 12) {
        $temp = " " . $huruf[$nilai];
    } else if ($nilai < 20) {
        $temp = penyebut($nilai - 10) . " belas";
    } else if ($nilai < 100) {
        $temp = penyebut($nilai / 10) . " puluh" . penyebut($nilai % 10);
    } else if ($nilai < 200) {
        $temp = " seratus" . penyebut($nilai - 100);
    } else if ($nilai < 1000) {
        $temp = penyebut($nilai / 100) . " ratus" . penyebut($nilai % 100);
    } else if ($nilai < 2000) {
        $temp = " seribu" . penyebut($nilai - 1000);
    } else if ($nilai < 1000000) {
        $temp = penyebut($nilai / 1000) . " ribu" . penyebut($nilai % 1000);
    } else if ($nilai < 1000000000) {
        $temp = penyebut($nilai / 1000000) . " juta" . penyebut($nilai % 1000000);
    } else if ($nilai < 1000000000000) {
        $temp = penyebut($nilai / 1000000000) . " milyar" . penyebut(fmod($nilai, 1000000000));
    } else if ($nilai < 1000000000000000) {
        $temp = penyebut($nilai / 1000000000000) . " trilyun" . penyebut(fmod($nilai, 1000000000000));
    }
    return $temp;
}
function tanggal_indo($d)
{

    $d = explode('-', $d);
    // var_dump($d[1]);
    $bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

    return $d[2] . " " . $bulan[(int) $d[1] - 1] . " " . $d[0];
}
function terbilang($nilai)
{
    if ($nilai < 0) {
        $hasil = "minus " . trim(penyebut($nilai));
    } else {
        $hasil = trim(penyebut($nilai));
    }
    return $hasil;
}


?>
<style>
    body {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 12px;
        color: #000;
    }

    .container {
        /* border: 2px solid #000; */
        padding: 15px;
    }

    /* Header Styles */
    .header-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 10px;
        border: 2px solid #000;
    }

    .header-logo {
        width: 20%;
        text-align: center;
        vertical-align: middle;
    }

    .header-text {
        width: 80%;
        text-align: center;
        vertical-align: middle;
    }

    .company-name {
        font-size: 18px;
        font-weight: bold;
        margin: 0;
        text-align: center;
        text-transform: uppercase;
    }

    .project-name {
        font-size: 14px;
        margin: 2px 0;
    }

    .address {
        font-size: 11px;
        line-height: 1.3;
    }

    /* Title Styles */
    .title-bar {
        width: 100%;
        margin-top: 10px;
        margin-bottom: 20px;
    }

    .kwitansi-title {
        text-align: center;
        font-weight: bold;
        font-size: 18px;
        text-decoration: underline;
        letter-spacing: 3px;
        text-transform: uppercase;
        width: 70%;
        display: inline-block;
    }

    .nomor-kwitansi {
        text-align: right;
        font-size: 12px;
        width: 25%;
        display: inline-block;
    }

    /* Main Form Styles */
    .main-table {
        width: 100%;
        border-collapse: collapse;
    }

    .main-table td {
        padding: 4px;
        vertical-align: top;
    }

    .label-col {
        width: 140px;
        white-space: nowrap;
    }

    .colon-col {
        width: 10px;
        text-align: center;
    }

    .input-line {
        border: 1px solid #000;
        height: 20px;
    }

    /* Payment List Table */
    .payment-table {
        width: 100%;
        border-collapse: collapse;
    }

    .payment-table td {
        padding: 2px;
    }

    .pay-label {
        width: 130px;
    }

    .pay-currency {
        width: 30px;
    }

    .pay-box {
        border: 1px solid #000;
        height: 18px;
    }

    /* Footer / Kavling Section */
    .footer-section {
        margin-top: 10px;
    }

    .kavling-table {
        width: 100%;
        border-collapse: collapse;
    }

    .kavling-box {
        border: 1px solid #000;
        height: 18px;
        width: 250px;
    }

    /* Signature */
    .signature-area {
        text-align: right;
        margin-top: 10px;
        padding-right: 20px;
    }

    .signature-space {
        height: 60px;
    }
</style>

<body>

    <div class="container">
        <table class="header-table">
            <tr>
                <td class="header-logo" align="center">
                    <img src="<?= base_url($proyek->logo_pt) ?>" style="width: auto; height: 50px; display: block; margin: 0 auto;" alt="Logo">
                </td>
                <td class="header-text">
                    <div class="company-name"><?= $proyek->nama_pt ?></div>
                    <div class="project-name"><?= $proyek->nama_proyek ?></div>
                    <div class="address">
                        GRHA SATRIA, GF. Jl. Sukahaji No. 126 Bandung, 40152 - Jawa Barat<br>
                        Telp (022) 82006365 - 82006367 Fax (022) 82006362
                    </div>
                </td>
            </tr>
        </table>

        <table style="width: 100%; margin-bottom: 10px;">
            <tr>
                <td style="width: 25%;"></td>
                <td style="width: 50%; text-align: center;">
                    <span style="font-size: 20px; font-weight: bold; text-decoration: underline; letter-spacing: 4px;">K W I T A N S I</span>
                </td>
                <td style="width: 25%; text-align: right;">
                    No: .............................
                </td>
            </tr>
        </table>

        <table class="main-table">
            <tr>
                <td class="label-col">Telah Terima Dari</td>
                <td class="colon-col">:</td>
                <td class="input-line"><?= $konsumen->nama_konsumen ?></td>
            </tr>
            <tr>
                <td class="label-col">Uang Sejumlah</td>
                <td class="colon-col">:</td>
                <td class="input-line"><?= number_format($pembayaran->nominal) ?></td>
            </tr>
            <tr>
                <td class="label-col">Untuk Pembayaran</td>
                <td class="colon-col">:</td>
                <td style="padding: 0;">
                    <table class="payment-table">
                        <?php
                        if ($list) {
                            foreach ($list as $key => $value) {
                                $dt = current(array_filter($detail, fn($v) => $v['id_keuangan_item_list'] == $value->id_keuangan_item_list));
                                $nominal = 0;
                                if ($dt) {
                                    $nominal = $dt['nominal'];
                                }

                                echo "<tr>
                                    <td class='pay-label'>" . $value->item . "</td>
                                    <td class='pay-currency'>Rp.</td>
                                    <td class='pay-box'>" . number_format($nominal) . "</td>
                                </tr>";
                            }
                        }
                        ?>
                    </table>
                </td>
            </tr>
        </table>

        <div style="margin-top: 10px;  margin-left:5px">
            <i> <b>*Keterangan</b> : <?= $pembayaran->keterangan ?></i>
        </div>
        <div style=" margin-top: 5px; margin-left:5px">
            Untuk pembelian tanah & bangunan di Perumahan <?= $proyek->nama_proyek ?> :
        </div>

        <table class="kavling-table" style="margin-top: 5px;">
            <tr>
                <td style="width: 50%; vertical-align: top;">
                    <table style="width: 100%;">
                        <tr>
                            <td style="width: 60px;">KAVLING</td>
                            <td class="pay-box">&nbsp; <?= $kavling[0]->nama_jalan ?> No. <?= $kavling[0]->no_kavling ?></td>
                        </tr>
                        <tr>
                            <td style="width: 60px;">TIPE</td>
                            <td class="pay-box">&nbsp; <?= $kavling[0]->tipe_rumah ?></td>
                        </tr>
                    </table>
                </td>

                <td style="width: 50%; vertical-align: top; text-align: right; padding-right: 10px;">
                </td>
            </tr>
        </table>

    </div>
    <htmlpagefooter name="myFooter">
        <table class="kavling-table" style="margin-top: 5px;">
            <tr>
                <td style="width: 70%; vertical-align: top;">
                </td>

                <td style="width: 30%; vertical-align: top; text-align: center; padding-right: 10px;">
                    Bandung, <?= tanggal_indo($pembayaran->tanggal_bayar) ?>
                    <br>
                    <br>
                    <br>
                    <br>
                    <br>
                    <br>
                    (..............................)
                </td>
            </tr>
        </table>
    </htmlpagefooter>

    <sethtmlpagefooter name="myFooter" value="on" />

</body>

</html>