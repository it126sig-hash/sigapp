<!DOCTYPE html>
<html lang="en">
<?php
function format_date($d)
{
    $bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    if ($d) {
        $d = explode("-", $d);
        return $d[2] . " " . $bulan[$d[1] - 1] . " " . $d[0];
    }
    return $d;
}

// var_dump(base_url($kop));die();
?>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tagihan <?= $konsumen->nama_konsumen ?></title>

    <style>
        table {
            width: 100%;
            /* padding: 70px 0px 0px 0px; */
            padding-bottom: 40px;
            ;
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
            <div>
                <p style="float:left">
                    <span><b>Ditagihkan Ke:</b></span>
                    <span><br></span>
                    <span><?= $konsumen->nama_konsumen ?></span>
                    <span><br></span>
                    <span><?= $konsumen->alamat_konsumen ?></span>
                </p>
                <p style="float:right; text-align:right">
                    <b>Perumahan :</b><span>
                        <br><?= $kavling->nama_proyek ?><br>
                        <?= $kavling->nama_jalan ?> No. <?= $kavling->no_kavling ?><br>

                    </span>
                    <span></span>
                </p>
            </div>
            <table>
                <thead>
                    <tr>
                        <th scope="col" class="text-nowrap">No</th>
                        <th scope="col" class="text-nowrap">Berita Acara</th>
                        <th scope="col" class="text-nowrap">Jatuh Tempo</th>

                        <th scope="col" class="text-nowrap">Nominal</th>

                    </tr>
                </thead>
                <tbody id="tb-print-data-tagihan">
                    <?= $inv->tagihan ?>
                </tbody>
            </table>
            <div class="">
                <span><b>Keterangan:</b></span><br>
                <?= $inv->terms ?>
            </div>
            <br>
            <table style="border:0px">
                <tr>
                    <td width="33%" style="border:0px"></td>
                    <td width="33%" style="border:0px"></td>
                    <td width="33%" style="text-align: center; border:0px">
                        Bandung, <?= format_date(date('Y-m-d')) ?>
                        <br>
                        <br>
                        <br>
                        <br>
                        <br>
                        <br>
                        <br>
                        <?= $nama->nama_karyawan ?>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>

</html>