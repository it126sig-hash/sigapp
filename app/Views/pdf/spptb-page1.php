<style>
    body {
        font-size: 9.5pt;
    }

    .button-green {
        border: none;
        padding: 0 32px;
        display: inline;
    }

    .full-width-hr {

        border-top: 1px solid #111;
        /* Menambahkan garis atas */
        margin: -10;
        /* Menghapus margin */
        padding: 0;
        /* Menghapus padding */
        width: 100%;
        /* Mengatur lebar menjadi 100% */
    }
</style>
<?php
function num_format($n)
{
    $n = (int) $n;

    if (!is_int($n) || $n == 0) {
        return '-';
    } else {
        return number_format($n);
    }
}


function format_date($d)
{

    $bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

    if ($d) {
        $d = explode("-", $d);
        return $d[2] . " " . $bulan[(int) $d[1] - 1] . " " . $d[0];
    }
    return $d;
}

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
<table border="0">
    <tbody>
        <tr>
            <td width="50%" valign="top">
                <img width="162" height="96" src="<?= base_url('images/sig-logo.png') ?>" />
            </td>
            <td width="50%" style="border: 1px solid #111">
                <div>
                    <p>
                        &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; &nbsp; &nbsp; &nbsp;
                        &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; &nbsp; &nbsp; &nbsp;
                        &nbsp; &nbsp;
                        Asli : Keuangan Pusat
                    </p>
                    <p>
                        &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; &nbsp; &nbsp; &nbsp;
                        &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; &nbsp; &nbsp; &nbsp;
                        &nbsp; &nbsp;
                        Copy 1 : Konsumen
                    </p>
                    <p>
                        &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; &nbsp; &nbsp; &nbsp;
                        &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; &nbsp; &nbsp; &nbsp;
                        &nbsp; &nbsp;
                        Copy 2 : Keuangan Proyek
                    </p>
                    <p>
                        &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; &nbsp; &nbsp; &nbsp;
                        &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; &nbsp; &nbsp; &nbsp;
                        &nbsp; &nbsp;
                        Copy 3 : Marketing
                    </p>
                </div>
            </td>
        </tr>
        <tr>
            <td width="359" valign="top">
            </td>
            <td width="265">
            </td>
        </tr>
        <tr>
            <td width="359" valign="top">
                <p>
                    Proyek: <?= $proyek->nama_proyek ?>
                </p>
            </td>
            <td width="265" align="right">
                <strong>No. SPPTB : <?= $data->no_spptb ?></strong>
            </td>
        </tr>
    </tbody>
</table>
<div>
    <p align="center">
        <strong>SURAT PERNYATAAN <br />
            PEMESANAN TANAH &amp; BANGUNAN (SPPTB)</strong>
    </p>
    <hr class="full-width-hr">
</div>
<p style="margin-bottom:0">
    Yang bertanda tangan dibawah ini: <br />
    (Wajib diisi lengkap dan jelas)
</p>
<table border="0" cellspacing="0" cellpadding="0">
    <tbody>
        <tr>
            <td width="90" valign="top">
                <p>
                    Nama
                </p>
            </td>
            <td width="195" valign="top">
                <p>
                    : <?= $data->nama_konsumen ?>
                </p>
            </td>
            <td width="40" valign="top">
                <p align="right">
                    Hp
                </p>
            </td>
            <td width="266" colspan="3" valign="top">
                <p>
                    : <?= $data->hp_konsumen ?>
                </p>
            </td>
        </tr>
        <tr>
            <td width="90" valign="top" style="display:block">
                Alamat Rumah
            </td>
            <td width="195" valign="top">
                <p>
                    : <?= $data->alamat_konsumen ?>
                </p>
            </td>
            <td width="40" valign="top">
                <p align="right">
                    Telp
                </p>
            </td>
            <td width="96" valign="top">
                <p>
                    : ..............
                </p>
            </td>
            <td width="50" valign="top">
                <p align="right">
                    Email
                </p>
            </td>
            <td valign="top">
                <p>
                    : <?= $data->email_konsumen ?>
                </p>
            </td>
        </tr>
        <tr>
            <td width="90" valign="top">
                <p>
                    Instansi
                </p>
            </td>
            <td width="501" colspan="5" valign="top">
                <p>
                    : <?= $data->nama_instansi ?>
                </p>
            </td>
        </tr>
        <tr>
            <td width="90" valign="top">
                <p>
                    Alamat Instansi
                </p>
            </td>
            <td width="195" valign="top">
                <p>
                    : <?= $data->alamat_instansi ?>
                </p>
            </td>
            <td width="40" valign="top">
                <p align="right">
                    Telp
                </p>
            </td>
            <td width="96" valign="top">
                <p>
                    : <?= $data->tel_instansi ?>
                </p>
            </td>
            <td width="50" valign="top">
                <p align="right">
                    Email
                </p>
            </td>
            <td valign="top">
                <p>
                    : ………………
                </p>
            </td>
        </tr>
        <tr>
            <td width="90" valign="top">
                <p>
                    Alamat Surat
                </p>
            </td>
            <td width="195" valign="top">
                <p>
                    : ……………………………………
                </p>
            </td>
            <td width="40" valign="top">
            </td>
            <td width="96" valign="top">
            </td>
            <td width="50" valign="top">
                <p align="right">
                    Kode Pos
                </p>
            </td>
            <td valign="top">
                <p>
                    : ………………
                </p>
            </td>
        </tr>
        <tr>
            <td width="90" valign="top">
                <p>
                    No. KTP
                </p>
            </td>
            <td width="331" colspan="3" valign="top">
                <p>
                    : <?= $data->nik ?>
                </p>
            </td>
            <td width="170" colspan="2" valign="top">
                <p>
                    (Photocopy Terlampir)
                </p>
            </td>
        </tr>
        <tr>
            <td width="90" valign="top">
                <p>
                    Marketing
                </p>
            </td>
            <td width="501" colspan="5" valign="top">
                <p>
                    : <?= $data->sales ?>
                </p>
            </td>
        </tr>
    </tbody>
</table>
<p style="margin-bottom:0">
    Dengan ini “menyatakan”
</p>
<p style="margin-bottom:0; margin-top: 0px;">
    1. Setuju untuk membeli tanah &amp; bangunan rumah dilokasi
    perumahan <b><?= $proyek->nama_proyek ?></b>
</p>
<table style="margin-left:13px" border="0" cellspacing="0" cellpadding="0">
    <tbody>
        <tr>
            <td width="65" valign="top">
                <p>
                    Tipe
                </p>
            </td>
            <td width="189" valign="top">
                <p>
                    : <?= $data->tipe_rumah ?>
                </p>
            </td>
            <td width="123" valign="top">
                <p>
                    Luas Bangunan
                </p>
            </td>
            <td width="198" valign="top">
                <p>
                    : ± <?= $data->lb ?> m<sup>2</sup>
                </p>
            </td>
        </tr>
        <tr>
            <td width="65" valign="top">
                <p>
                    Kavling
                </p>
            </td>
            <td width="189" valign="top">
                <p>
                    : <?= $data->nama_jalan ?> No. <?= $data->no_kavling ?>
                </p>
            </td>
            <td width="123" valign="top">
                <p>
                    Luas Tanah
                </p>
            </td>
            <td width="198" valign="top">
                <p>
                    : ± <?= $data->luas_tanah ?> m <sup>2</sup>
                </p>
            </td>
        </tr>
    </tbody>
</table>
<p style="margin-bottom:0">
    2. Setuju dan sanggup untuk membayar sebagai berikut:
</p>
<div style="margin-left:13px">


    <table border="0" cellspacing="" cellpadding="1">
        <tbody>
            <tr>
                <td width="160" valign="top">
                    Jenis Pembayaran 
                </td>
                <td valign="top">
                </td>
                <?php
                    $kpr = "";
                    $tunai = "";

                    if($data->is_kpr == 1){
                        $kpr = 'checked = "true"';
                    }else{
                        $tunai = 'checked = "true"';
                    }

                ?>
                <td width="208" valign="top" style="padding-left: -36px" >
                    <input type="checkbox" name='is_kpr' <?=$tunai?>> Tunai  
                    &nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <input type="checkbox" name='is_kpr' <?=$kpr?>> KPR
                </td>
                <td width="208" valign="top">
                    Dengan perincian sbb:
                </td>
            </tr>
            <tr>
                <td width="160" valign="top">
                    Harga Jual
                </td>
                <td>: Rp. </td>
                <td width="208" valign="top" class="button-green" align="right">
                    <span style="margin:500pt"><?= num_format($data->harga_jual) ?></span>
                </td>
                <td width="208" rowspan="8" valign="top">
                    <?= $data->rincian ?>
                </td>
            </tr>
            <tr>
                <td width="160" valign="top">
                    Rencana KPR
                </td>
                <td>: ( Rp. </td>
                <td width="208" valign="top" class="button-green" align="right" style="padding-right:24">
                    <span style="margin:500pt"><?= $data->is_kpr == 1 ? num_format($data->harga_kpr) : '-' ?> )</span>
                </td>

            </tr>
            <tr>
                <td width="160" valign="top">
                    Uang Muka
                </td>
                <td>: Rp. </td>
                <td width="208" valign="top" class="button-green" align="right">
                    <span style="margin:500pt"><?= num_format($data->harga_uang_muka) ?></span>
                </td>

            </tr>
            <tr>
                <td width="160" valign="top">
                    Biaya Administrasi
                </td>
                <td>: Rp. </td>
                <td width="208" valign="top" class="button-green" align="right">
                    <span style="margin:500pt"><?= num_format($data->harga_administrasi) ?></span>
                </td>

            </tr>
            <tr>
                <td width="160" valign="top">
                    Biaya Kavling Strategis
                </td>
                <td>: Rp. </td>
                <td width="208" valign="top" class="button-green" align="right">
                    <span style="margin:500pt"><?= num_format($data->harga_penambahan) ?></span>
                </td>

            </tr>
            <tr>
                <td width="160" valign="top">
                    Kelebihan Tanah
                </td>
                <td>: Rp. </td>
                <td width="208" style="border-bottom:1 solid" valign="top" class="button-green" align="right">
                    <span style="margin:500pt"><?= num_format($data->harga_penambahan_tanah) ?></span>
                </td>

            </tr>
            <tr>
                <td width="160" valign="top">
                    <strong>TOTAL UANG MUKA</strong>
                </td>
                <td>: Rp. </td>
                <td width="208" valign="top" class="button-green" align="right">
                    <?php
                    $tot = $data->harga_uang_muka + $data->harga_administrasi + $data->harga_penambahan + $data->harga_penambahan_tanah;
                    ?>
                    <span style="margin:500pt"><?= num_format($tot) ?></span>
                </td>

            </tr>
            <tr>
                <td width="160" valign="top">
                    Discount Uang Muka
                </td>
                <td>: ( Rp. </td>
                <td width="208" valign="top" style="border-bottom:1 solid; padding-right:24" class="button-green"
                    align="right">
                    <span style="margin:500pt"><?= num_format($data->harga_penambahan_um) ?> )</span>
                </td>

            </tr>
            <tr>
                <td width="160" valign="top">
                    Total uang muka yang harus diangsur
                </td>
                <td>: Rp. </td>
                <td width="208" valign="center" class="button-green" align="right">
                    <span style="margin:500pt"><b><?= num_format($tot - $data->harga_penambahan_um) ?></b></span>
                </td>

            </tr>
        </tbody>
    </table>
    <p style="margin-bottom:0">
        Yang akan dibayar dengan angsuran sebagai berikut:
    </p>
    <table border="0" cellspacing="0" cellpadding="0">
        <tbody>
            <tr>
                <td width="188" valign="top">
                    <p align="center">
                        Tanggal Pembayaran
                    </p>
                </td>
                <td width="213" valign="top">
                    <p align="center">
                        Uraiain Pembayaran
                    </p>
                </td>
                <td></td>
                <td width="174" valign="top">
                    <p align="center">
                        Jumlah
                    </p>
                </td>
            </tr>
            <tr>
                <td width="188" valign="top">
                    <p align="center">
                        <?= format_date($data->booking_tgl) ?>
                    </p>
                </td>
                <td width="213" valign="top">
                    Tanda Jadi/Booking Fee
                </td>
                <td>
                    Rp.
                </td>
                <td width="174" valign="top" class="button-green" align="right">
                    <?= num_format($data->booking_fee) ?>
                </td>
            </tr>
            <?php
            $n = 1;
            $tot_tg = 0;
            foreach ($list_tagihan as $tagihan) {

                if ($tagihan->status == 'UM') {
                    echo "
                    <tr>
                        <td width='188' valign='top'>
                            <p align='center'>
                                " . format_date($tagihan->jatuh_tempo_tgl) . "
                            </p>
                        </td>
                        <td width='213' valign='top'>
                            <p>
                                 Angsuran $n
                            </p>
                        </td>
                        <td>
                            Rp. 
                        </td>
                        <td width='174' valign='top' class='button-green' align='right'>
                           " . num_format($tagihan->nominal) . "
                        </td>
                    </tr>
                ";
                    $tot_tg += $tagihan->nominal;
                    $n++;
                }


            }
            ?>


            <tr>
                <td width="188" valign="top">
                    ……………………………………
                </td>
                <td width="213" valign="top">

                    Angsuran …

                </td>
                <td>
                    Rp.
                </td>
                <td width="174" valign="top" class="button-green" align="right">
                    .......................
                </td>
            </tr>
            <tr>
                <td width="188" valign="top">
                    ……………………………………
                </td>
                <td width="213" valign="top">

                    Angsuran …

                </td>
                <td>
                    Rp.
                </td>
                <td width="174" valign="top" class="button-green" align="right">
                    .......................
                </td>
            </tr>
            <tr>
                <td width="188" valign="top">
                    <p align="center">
                        ……………………………………
                    </p>
                </td>
                <td width="213" valign="top">
                    <p>
                        PUMP Jamsostek/Baperatatum
                    </p>
                </td>
                <td>
                    Rp.
                </td>
                <td width="174" style="border-bottom:1 solid" valign="top" class="button-green" align="right">
                    .......................
                </td>
            </tr>
            <tr>
                <td width="188" valign="top">
                </td>
                <td width="213" valign="top">
                </td>
                <td>
                    Rp.
                </td>
                <td width="174" valign="top" class="button-green" align="right">
                    <?= number_format($tot_tg) ?>
                </td>
            </tr>
        </tbody>
    </table>
    <?php
    $ktp = $data->file_ktp ? base_url($data->file_ktp) : "";


    $npwp = $data->file_npwp ? base_url($data->file_npwp) : "";

    if ($ktp != "" || $npwp != "") {
        echo "
                <img src='$ktp' width ='85mm' height='54mm' alt=''>
                <img src='$npwp' width ='85mm' height='54mm' alt=''>
            ";
    }
    $total = $data->harga_ppn + $data->harga_bphtb + $data->harga_biaya_proses;
    ?>
</div>