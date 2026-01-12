<!DOCTYPE html>
<html>

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
                <th rowspan="3" id="tb-NO">NO</th>
                <th colspan="2" id="tb-KAVLING" width="15%">KAVLING</th>
                <th rowspan="3" id="tb-TYPE">TYPE</th>
                <th rowspan="3" id="tb-NAMA_KONSUMEN">NAMA KONSUMEN</th>
                <th rowspan="3" id="tb-SALES">SALES</th>
                <th rowspan="3" id="tb-TGL_BOOKING">TGL BOOKING</th>
                <th rowspan="3" id="tb-TGL_WAWANCARA">TGL WAWANCARA</th>
                <th colspan="6" id="tb-MARKETING_DATA">MARKETING DATA</th>
                <th colspan="4" id="tb-KEUANGAN">KEUANGAN</th>
                <th colspan="4" id="tb-PRODUKSI">PRODUKSI</th>
                <th colspan="3" id="tb-LEGAL">LEGAL</th>
                <th id="tb-GA">GA</th>
            </tr>

            <tr>
                <th rowspan="2" id="tb-BLOK">BLOK</th>
                <th rowspan="2" id="tb-NO_KAVLING">NO</th>

                <th colspan="2" id="tb-PENGAJUAN">PENGAJUAN</th>
                <th rowspan="2" id="tb-STATUS">STATUS</th>
                <th colspan="2" id="tb-SP3K">SP3K</th>
                <th rowspan="2" id="tb-SIKASEP">SIKASEP</th>

                <th rowspan="2" id="tb-TUNAI">TUNAI</th>
                <th rowspan="2" id="tb-UM">UM</th>
                <th rowspan="2" id="tb-B_ADM">B. ADM</th>
                <th rowspan="2" id="tb-BIAYA_BIAYA">BIAYA-BIAYA</th>

                <th colspan="2" id="tb-BANGUNAN">BANGUNAN</th>
                <th rowspan="2" id="tb-LISTRIK">LISTRIK</th>
                <th rowspan="2" id="tb-JALAN">JALAN</th>

                <th rowspan="2" id="tb-HGB">HGB</th>
                <th rowspan="2" id="tb-IMB">IMB</th>
                <th rowspan="2" id="tb-PBB">PBB</th>

                <th rowspan="2" id="tb-SIKUMBANG">SIKUMBANG</th>
            </tr>

            <tr>
                <th id="tb-TUNAI_KPR">TUNAI/KPR</th>
                <th id="tb-TERBIT">BANK</th>
                <th id="tb-TERBIT">TERBIT</th>
                <th id="tb-EXPIRED">EXPIRED</th>

                <th id="tb-%">%</th>
                <th id="tb-LPA">LPA</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; ?>
            <?php foreach ($poskon as $row): ?>
                <?php
                $total = $row->um + $row->adm + $row->bb;
                $bayar = $row->total_um + $row->total_adm + $row->total_bb;
                $persen_tunai = "-";

                $um = $row->um;
                $adm = $row->adm;
                $bb = $row->bb;

                $kpr = "KPR";
                if ($row->is_kpr == 0) {
                    $kpr = 'TUNAI';
                    $um = "-";
                    $adm = "-";
                    $bb = "-";
                    if ($bayar <= 0) {
                        $persen_tunai = '0%';
                    } else {
                        $persen_tunai = ($bayar / $total) * 100;
                    }
                } else {
                    $um = $row->total_um <= 0 ? "0%" : round(($row->total_um / $row->um) * 100) . "%";
                    $adm = $row->total_adm <= 0 ? "0%" : round(($row->total_adm / $row->adm) * 100) . "%";
                    $bb = $row->total_bb <= 0 ? "0%" : round(($row->total_bb / $row->bb) * 100) . "%";
                }
                ?>
                <tr>
                    <td class="center"><?= $no++; ?></td>
                    <td><?= $row->nama_jalan; ?></td>
                    <td class="center"><?= $row->no_kavling; ?></td>
                    <td class="center"><?= $row->lb . "/" . $row->lt; ?></td>
                    <td><?= $row->nama_konsumen; ?></td>
                    <td><?= $row->sales; ?></td>
                    <td class="center"><?= ($row->booking_tgl == "0000-00-00" || !$row->booking_tgl) ? "" : $row->booking_tgl; ?></td>
                    <td class="center"><?= ($row->wawancara_tgl == "0000-00-00" || !$row->wawancara_tgl) ? "" : $row->wawancara_tgl; ?></td>
                    <td class="center"><?= $kpr; ?></td>
                    <td><?= $row->bank; ?></td>
                    <td><?= $row->keterangan; ?></td>
                    <td class="center"><?= ($row->sp3k_tgl == "0000-00-00" || !$row->sp3k_tgl) ? "" : $row->sp3k_tgl; ?></td>
                    <td class="center"><?= ($row->sp3k_tgl_exp == "0000-00-00" || !$row->sp3k_tgl_exp) ? "" : $row->sp3k_tgl_exp; ?></td>
                    <td><?= $row->sikasep; ?></td>
                    <td class="center"><?= $persen_tunai ?></td>
                    <td class="center"><?= $um ?></td>
                    <td class="center"><?= $adm ?></td>
                    <td class="center"><?= $bb ?></td>
                    <td class="center"><?= $row->progres_bangunan; ?>%</td>
                    <td class="center"><?= $row->lpa ? '✓' : ''; ?></td>
                    <td class="center"><?= $row->st_listrik ? '✓' : ''; ?></td>
                    <td class="center"><?= ''; ?></td>
                    <td class="center"><?= $row->sertifikat_split_no_hgb ? '✓' : ''; ?></td>
                    <td class="center"><?= $row->pbg_no ? '✓' : ''; ?></td>
                    <td class="center"><?= $row->pbb_pecah_nop ? '✓' : ''; ?></td>
                    <td class="center"><?= $row->sikumbang ? '✓' : ''; ?></td>
                </tr>
            <?php endforeach; ?>

        </tbody>
    </table>

</body>

</html>