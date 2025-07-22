<!DOCTYPE html>
<html lang="en">
<?php
$bulan = ['Januari','Februari','Maret','April','Mei', 'Juni','Juli', 'Agustus','September','Oktober','November','Desember'];

function foramt_date($d){
    $bulan = ['Januari','Februari','Maret','April','Mei', 'Juni','Juli', 'Agustus','September','Oktober','November','Desember'];

    if($d){
        $d = explode("-", $d);
        return $d[2] . " " . $bulan[$d[1]]. " ". $d[0];
    }
    return $d;
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
        background-image: url("<?= ($kop) ?>");
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        font-size: 10pt;
        margin: -45px;
        /* background-position: right 50px; */
        background-size: 100%;
        background-repeat: no-repeat;
        /* background-position: center center;          */
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
    <div style="padding-top:120px; text-align:center">
        <h3>Surat Tagihan</h>
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
    <table style="width:100%; padding: 50px 70px px 70px">
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
            Bandung, <?= format_date($tanggal_surat_tagihan) ?>
            <br>
            <br>
            <br>
            <br>
            <br>
            <?=$nama?>
        </div>
</body>

</html>