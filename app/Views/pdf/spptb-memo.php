<style>
    body {
        font-size: 9.5pt;
    }

    .button-green {
        border: none;
        padding: 0 32px;
        display: inline;
    }

    .tab {
        padding-left: 10px;
    }
</style>
<?php
if (!function_exists('num_format')) {
    function num_format($n)
    {
        $n = (int) $n;

        if (!is_int($n) || $n == 0) {
            return '-';
        } else {
            return number_format($n);
        }
    }
}


if (!function_exists('format_date')) {
    function format_date($d)
    {
        $bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

        if ($d) {
            $d = explode("-", $d);
            return $d[2] . " " . $bulan[(int) $d[1]] . " " . $d[0];
        }
        return $d;
    }
}

if (!function_exists('penyebut')) {
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
}

if (!function_exists('terbilang')) {
    function terbilang($nilai)
    {
        if ($nilai < 0) {
            $hasil = "minus " . trim(penyebut($nilai));
        } else {
            $hasil = trim(penyebut($nilai));
        }
        return $hasil;
    }
}
?>

<div style="padding-bottom: 20px; padding-top:20px">
    <p align="center">
        <strong>MEMO INTERNAL <br />
            RINCIAN PEMBAYARAN KONSUMEN</strong>
    </p>
</div>

<table border="0" style="margin-bottom:20px"  cellspacing="0" cellpadding="0">
    <tbody>
        <tr>
            <td class="td-width" valign="top">Dar</td>
            <td valign="top">: Marketing Data</td>
        </tr>
        <tr>
            <td class="td-width" valign="top">Kepada</td>
            <td valign="top">: Legal & Keuangan </td>
        </tr>
    </tbody>
</table>

<table border="0" style="margin-bottom:20px" cellspacing="0" cellpadding="0">
    <tbody>
        <tr>
            <td class="td-width" valign="top">Nama</td>
            <td valign="top">: <?= $data->nama_konsumen ?></td>
        </tr>
        <tr>
            <td class="td-width" valign="top">Kavling</td>
            <td valign="top">: <span class="firstletter"><?= $data->nama_jalan ?> No. <?= $data->no_kavling ?></span></td>
        </tr>
    </tbody>
</table>


<table border="0" cellspacing="" cellpadding="1">
    <tbody>
        <tr>
            <td width="160" valign="top">Jenis Pembayaran</td>
            <?php
            $kpr = "";
            $tunai = "";

            if ($data->is_kpr == 1) {
                $kpr = 'checked = "true"';
            } else {
                $tunai = 'checked = "true"';
            }

            ?>
            <td width="208" valign="top" style="padding-left: -36px">
                &nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <input type="checkbox" name='is_kpr' <?= $tunai ?>> Tunai
                &nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <input type="checkbox" name='is_kpr' <?= $kpr ?>> KPR
            </td>
            <td valign="top"></td>
            <td width="208" valign="top">Dengan perincian sbb:</td>
        </tr>
        <tr>
            <td width="160" valign="top">Harga Jual Nett</td>
            <td width="208" valign="top"></td>
            <td> : Rp.</td>
            <td width="208" valign="top" class="button-green" align="right">
                <span style="margin:500pt"><?= num_format($data->harga_jual_net) ?></span>
            </td>
        </tr>
        <tr>
            <td width="160" valign="top">Rencana KPR</td>
            <td width="208" valign="top"></td>
            <td> : Rp.</td>
            <td width="208" valign="top" class="button-green" align="right">
                <span style="margin:500pt"><?= $data->is_kpr == 1 ? num_format($data->harga_kpr) : '-' ?></span>
            </td>
        </tr>
        <tr>
            <td width="160" valign="top">UM</td>
            <td width="208" valign="top"></td>
            <td> : Rp.</td>
            <td width="208" valign="top" class="button-green" align="right">
                <span style="margin:500pt"><?= num_format($data->harga_uang_muka)  ?></span>
            </td>
        </tr>
        <tr>
            <td width="160" valign="top">SBUM</td>
            <td width="208" valign="top"></td>
            <td> : Rp.</td>
            <td width="208" valign="top" class="button-green" align="right">
                <span style="margin:500pt">(<?= num_format($data->harga_sbum)  ?>)</span>
            </td>
        </tr>
        <tr>
            <td width="160" valign="top"><b>TOTAL UM</b></td>
            <td width="208" valign="top"></td>
            <td> : Rp.</td>
            <td width="208" valign="top" class="button-green" align="right">
                <span style="margin:500pt"><b><?= num_format($data->harga_uang_muka - $data->harga_sbum)  ?></b></span>
            </td>
        </tr>
        <tr>
            <td>BIAYA-BIAYA</td>
            <td></td>
            <td></td>
        </tr>
        <tr class="">
            <td width="160" valign="top">
                <ul>
                    <li>Biaya Administrasi</li>
                </ul>
            </td>
            <td width="208" valign="top"></td>
            <td> : Rp.</td>
            <td width="208" valign="top" class="button-green" align="right">
                <span style="margin:500pt"><?= num_format($data->harga_administrasi)  ?></span>
            </td>
        </tr>
        <tr class="">
            <td width="160" valign="top">
                <ul>
                    <li>PPN11 %</li>
                </ul>
            </td>
            <td width="208" valign="top"></td>
            <td> : Rp.</td>
            <td width="208" valign="top" class="button-green" align="right">
                <span style="margin:500pt"><?= num_format($data->harga_ppn)  ?></span>
            </td>
        </tr>
        <tr class="">
            <td width="160" valign="top">
                <ul>
                    <li>BPHTB</li>
                </ul>
            </td>
            <td width="208" valign="top"></td>
            <td> : Rp.</td>
            <td width="208" valign="top" class="button-green" align="right">
                <span style="margin:500pt"><?= num_format($data->harga_bphtb)  ?></span>
            </td>
        </tr>
        <tr class="">
            <td width="160" valign="top">
                <ul>
                    <li>Biaya Proses</li>
                </ul>
            </td>
            <td width="208" valign="top"></td>
            <td> : Rp.</td>
            <td width="208" valign="top" class="button-green" align="right">
                <span style="margin:500pt"><?= num_format($data->harga_biaya_proses)  ?></span>
            </td>
        </tr>
        <tr class="">
            <td width="160" valign="top">
                <ul>
                    <li>Biaya lain-lain (Kavling Strategis dll)</li>
                </ul>
            </td>
            <td width="208" valign="top"></td>
            <td> : Rp.</td>
            <td width="208" valign="top" class="button-green" align="right">
                <span style="margin:500pt"><?= num_format($data->harga_penambahan_tanah + $data->harga_penambahan)  ?></span>
            </td>
        </tr>
       
        <tr class="">
            <td width="160" valign="top">
                <ul>
                    <li>Discount</li>
                </ul>
            </td>
            <td width="208" valign="top"></td>
            <td> : Rp.</td>
            <td width="208" valign="top" class="button-green" align="right">
                <span style="margin:500pt">(<?= num_format($data->harga_diskon_uang_muka)  ?>)</span>
            </td>
        </tr>
        <tr class="">
            <td width="160" valign="top">
                Total HJ Brutto
            </td>
            <td width="208" valign="top"></td>
            <td> : Rp.</td>
            <td width="208" valign="top" class="button-green" align="right">
                <span style="margin:500pt"><?= num_format($data->harga_jual)  ?></span>
            </td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td colspan="3">Yang dibayarkan konsumen (tidak termasuk booking fee)</td>
            <td width="208" valign="top" class="button-green" align="right">
                <span style="margin:500pt"><?= num_format($data->harga_allin)  ?></span>
            </td>
        </tr>

    </tbody>
</table>