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
        /* background-image: url(""); */
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        font-size: 10pt;
        margin: -20px 0px 0px 0px;
        /* background-position: right 50px; */
        background-size: 100%;
        background-repeat: no-repeat;
        /* background-position: center center;          */
    }

    .row {
        width: 100%;
        border: 1px solid #000;
    }

    .col-30 {
        float: left;
        width: 40%;
    }

    .col-70 {
        float: left;
        width: 60%;
    }

    /* Clear floats after the columns */
    .row:after {
        content: "";
        display: table;
        clear: both;
    }

    .alamat {
        text-align: center;
        padding-bottom: 10px;
    }

    .logo {
        text-align: center;
    }

    td {
        padding: 5px 0px;
    }

    table tr td {
        font-size: 10pt;
    }
</style>

<body>
    <div class="row">
        <div class="col-30 logo">
            <h1><img src='<?= base_url($proyek->logo_pt) ?>' height="50px"></h1>
        </div>
        <div class="col-70 alamat">
            <h3 style="padding-bottom: -12px"><?= $proyek->nama_pt ?></h3>
            <h3 style="margin-bottom: 2px">PERUMAHAN <?= $proyek->nama_proyek ?></h3>
            <span>Jl. Sukahaji No 126 Bandung 40152</span>
            <br>
            <span>Tel (022) 8200 63 65 - 8200 63 67 Fax (022) 8200 63 62</span>
        </div>
    </div>
    <div style="padding-top:0px; text-align:center">
        <h2>KWITANSI</h2>
    </div>
    <table>
        <tr>
            <td style="vertical-align: top; width:200px">
                Sudah terima dari
            </td>
            <td>
                : <?= $konsumen->nama_konsumen ?>
            </td>
        </tr>
        <tr>
            <td>
                Uang Sejumlah
            </td>
            <td style="display:block">
                <div>
                    : Rp. <?= number_format($pembayaran->nominal) ?>
                </div>
            </td>
        </tr>
        <tr>
            <td>Terbilang</td>
            <td>: <?php echo ucfirst(terbilang($pembayaran->nominal)) ?> rupiah</td>
        </tr>
        <tr>
            <td>Untuk Pembayaran</td>
            <td>:
                <?php
                // var_dump($kavling[0]->lb);
                $pt = $pembayaran->payment_type;
                if ($pembayaran->payment_type != "Booking") {
                    $pt = str_replace(";", ", ", $pembayaran->payment_type);
                    $pt = substr($pt, 0, -2);
                }
                echo "$pt Rumah tipe " . $kavling[0]->lb . "/" . $kavling[0]->lt . " kavling " . $kavling[0]->nama_jalan . " No. " . $kavling[0]->no_kavling;
                ?>
            </td>
        </tr>
    </table>
    <br>
    <div style=" width: 30%; padding:5px 0px;  float:right; margin-top:-2px; text-align:center">
        Bandung, <?= tanggal_indo($pembayaran->tanggal_bayar) ?>
        <br>
        <br>
        <br>
        <br>
        <br>
        <br>
        (..............................)
    </div>
</body>

</html>