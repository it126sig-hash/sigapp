<!DOCTYPE html>
<html>
<?php
function format_tgl($tgl)
{
    if ($tgl == "" || $tgl == "0000-00-00" || $tgl == null)
        return "-";
    return date_format(date_create($tgl), "d-M-Y");
}
?>

<head>
    /<style>
        /* Pengaturan Halaman A4 Landscape untuk mPDF */
        body {
            font-family: Arial, sans-serif;
            font-size: 7pt;
            /* Ukuran font lebih kecil agar kolom muat */
            margin: 0;
            padding: 0;
        }

        h2 {
            text-align: center;
            text-transform: uppercase;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            /* Membantu menjaga lebar kolom tetap konsisten */
        }

        th,
        td {
            border: 0.5pt solid #000;
            padding: 5px 3px;
            text-align: left;
            word-wrap: break-word;
            /* Memastikan teks panjang tidak merusak tabel */
        }

        th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
            vertical-align: middle;
        }

        .center {
            text-align: center;
        }

        /* Mewarnai baris selang-seling agar mudah dibaca */
        tr:nth-child(even) {
            background-color: #fafafa;
        }
    </style>
</head>

<body>
    <?php
    $tgl = date('d F Y');
    ?>
    <h2>List Konsumen <?= $status ?> - <?= $poskon[0]->nama_proyek ?><br>
        <span style="font-size: 11pt;">Per <?= $tgl; ?></span>
    </h2>

    <table>
        <thead>
            <tr>
                <th rowspan="2" id="tb-NO">NO</th>
                <th colspan="2" id="tb-KAVLING">KAVLING</th>
                <th rowspan="2" id="tb-TYPE">TYPE</th>
                <th rowspan="2" id="tb-KET_BATAL">Keterangan Batal</th>
                <th rowspan="2">Nama Konsumen</th>
                <th rowspan="2">Tanggal Booking</th>
                <th rowspan="2">TUNAI/KPR</th>
                <th rowspan="2" id="tb-TOTAL_TAGIHAN">TOTAL TAGIHAN</th>
                <th rowspan="2" id="tb-SUDAH_BAYAR">SUDAH BAYAR</th>
                <th rowspan="2" id="tb-SISA_TAGIHAN">SISA TAGIHAN</th>
            </tr>
            <tr>
                <th id="tb-BLOK">BLOK</th>
                <th id="tb-NO_KAVLING">NO</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; ?>
            <?php foreach ($poskon as $row): ?>
                <?php
                $total = $row->um + $row->adm + $row->bb;
                $bayar = $row->total_um + $row->total_adm + $row->total_bb;
                $sisa = $total - $bayar;

                $tanggal_batal = format_tgl($row->mkdt_batal_tgl);
                $keterangan_batal = $row->keterangan_batal;
                $keterangan_batal . "<br> <span class='text-muted'>Dibatalkan pada: " . $tanggal_batal . "</span>";

                $kpr = "KPR";
                if ($row->is_kpr == 0) {
                    $kpr = 'TUNAI';
                }
                ?>
                <tr>
                    <td class="center"><?= $no++; ?></td>
                    <td><?= $row->nama_jalan; ?></td>
                    <td class="center"><?= $row->no_kavling; ?></td>
                    <td class="center"><?= $row->id_tipe; ?></td>
                    <td><?= $keterangan_batal; ?></td>
                    <td><?= $row->nama_konsumen; ?></td>
                    <td class="center"><?= ($row->booking_tgl == "0000-00-00" || !$row->booking_tgl) ? "" : $row->booking_tgl; ?></td>
                    <td class="center"><?= $kpr; ?></td>
                    <td class="center"><?= $total; ?></td>
                    <td class="center"><?= $bayar; ?></td>
                    <td class="center"><?= $sisa; ?></td>
                </tr>
            <?php endforeach; ?>

        </tbody>
    </table>

</body>

</html>