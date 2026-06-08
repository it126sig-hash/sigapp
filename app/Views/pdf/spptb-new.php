<style>
    body {
        font-size: 10pt;
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

    .td-width {
        width: 150px;
    }

    table #tb-identitas {
        border-spacing: 5px 1rem;
    }

    .firstletter {
        text-transform: capitalize;
    }

    .mb-0 {
        margin-bottom: 0px;
    }

    .ml-17 {
        margin-left: 17px
    }

    .terms {
        margin: 10px 0;
    }

    .terms ol {
        padding-left: -5px;
    }

    .terms li {
        margin-bottom: 8px;
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

function mark($cond)
{
    return $cond ? '&#10003;' : '&nbsp;';
} // ✓ atau kosong // checkbox ala mPDF (unicode)
?>

<htmlpageheader name="header_pages">
    <div style="text-align:right; font-size:10pt; padding-right:10mm;">
        Halaman {PAGENO} dari {nbpg}
    </div>
</htmlpageheader>
<sethtmlpageheader name="header_pages" value="on" />

<table border="0">
    <tbody>
        <tr>
            <td width="50%" valign="top">
                <img height="150" src="<?= (new \App\Services\FileAccessService())->existingPath($proyek->logo) ?: base_url($proyek->logo) ?>" />
            </td>
            <td width="30%" style="border: 1px solid #111; padding-left: 100px">
                <div style="text-align: left;">
                    <p>
                        Asli : Keuangan Pusat
                    </p>
                    <p>
                        Copy 1 : Konsumen
                    </p>
                    <p>
                        Copy 2 : Keuangan Proyek
                    </p>
                    <p>
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
                    Proyek: <?= strtoupper($proyek->nama_proyek) ?>
                </p>
            </td>
            <td width="265" align="right">
                <strong>No. SPPTB : <?= strtoupper($data->no_spptb) ?></strong>
            </td>
        </tr>
    </tbody>
</table>
<div style="padding-bottom: -30px;">
    <p align="center">
        <strong>SURAT PERNYATAAN <br />
            PEMESANAN TANAH &amp; BANGUNAN (SPPTB)</strong>
    </p>
    <hr class="full-width-hr">
</div>
<p style="margin-bottom:20px">
    Yang bertanda tangan dibawah ini: <br />
    (Wajib diisi lengkap dan jelas)
</p>
<table border="0" id="tb-identitas" cellspacing="10" cellpadding="0">
    <tbody>
        <tr>
            <td class="td-width" valign="top">

                Nama

            </td>
            <td valign="top">

                : <?= strtoupper($data->nama_konsumen) ?>

            </td>
        </tr>
        <tr>
            <td class="td-width" valign="top">

                No. KTP

            </td>
            <td valign="top">

                : <?= strtoupper($data->nik) ?>

            </td>
        </tr>
        <tr>
            <td class="td-width" valign="top" style="display:block">
                Alamat Rumah
            </td>
            <td>

                : <?= strtoupper($data->alamat_konsumen) ?>

            </td>
        </tr>
        <tr>
            <td class="td-width" valign="top">

                NPWP

            </td>
            <td valign="top">

                : <?= strtoupper($data->npwp) ?>

            </td>
        </tr>
        <tr>
            <td class="td-width" valign="top">

                Email

            </td>
            <td valign="top">

                : <?= strtoupper($data->email_konsumen) ?>

            </td>
        </tr>
        <tr>
            <td class="td-width" valign="top">

                Instansi

            </td>
            <td valign="top">

                : <?= strtoupper($data->nama_instansi) ?>

            </td>
        </tr>
        <tr>
            <td class="td-width" valign="top">
                Alamat Instansi
            </td>
            <td valign="top">

                : <?= strtoupper($data->alamat_instansi) ?>

            </td>
        </tr>
        <tr>
            <td class="td-width" valign="top">
                No HP/telp
            </td>
            <td valign="top">
                : <?= strtoupper($data->tel_instansi) ?>
            </td>
        </tr>
        <tr>
            <td class="td-width" valign="top">
                Email
            </td>
            <td valign="top">
                : <?= strtoupper($data->email_instansi) ?>
            </td>
        </tr>
        <tr>
            <td class="td-width" valign="top">
                Alamat Surat
            </td>
            <td valign="top">
                : <?= strtoupper($data->alamat_surat) ?>
            </td>
        </tr>
        <tr>
            <td>Pekerjaan</td>
            <td>:
                <?php
                $kar = "";
                $wir = "";
                if ($data->pekerjaan == "Karyawan")
                    $kar = "checked='true'";
                else if ($data->pekerjaan == "Wirausaha")
                    $wir = "checked='true'";
                ?>
                <input type="checkbox" <?= $kar ?> /> Karyawan
                &nbsp;&nbsp;
                <input type="checkbox" <?= $wir ?> /> Wirausaha
            </td>
        </tr>
        <tr>
            <td></td>
            <td>1. Sudah berapa lama? <b><?= strtoupper($data->lama_bekerja) ?></b></td>
        </tr>
        <tr>
            <td></td>
            <td>2. Di bidang apa? <b><?= strtoupper($data->bidang_pekerjaan) ?></b></td>
        </tr>
    </tbody>
</table>
<p style="margin-bottom:20px; margin-top: - 200px">
    <strong>Selaku pemohon</strong>
</p>
<table border="0" id="tb-identitas" cellspacing="10" cellpadding="0">
    <tbody>
        <tr>
            <td class="td-width" valign="top">
                Nama
            </td>
            <td valign="top">
                : <?= strtoupper($data->nama_pasangan) ?>
            </td>
        </tr>
        <tr>
            <td class="td-width" valign="top">
                No. KTP
            </td>
            <td valign="top">
                : <?= strtoupper($data->nik_pasangan) ?>
            </td>
        </tr>
        <tr>
            <td class="td-width" valign="top" style="display:block">
                No. HP/telp
            </td>
            <td>

                : <?= strtoupper($data->hp_pasangan) ?>

            </td>
        </tr>
        <tr>
            <td>Status Pekerjaan</td>
            <td>:
                <?php
                $bek = "";
                $tbek = "";
                $irt = "";
                if ($data->status_pekerjaan_pasangan == "Bekerja")
                    $bek = "checked='true'";
                elseif ($data->status_pekerjaan_pasangan == "Tidak Bekerja")
                    $tbek = "checked='true'";
                elseif ($data->status_pekerjaan_pasangan == "Ibu Rumah Tangga")
                    $irt = "checked='true'";
                ?>
                <input type="checkbox" <?= $bek ?> /> Bekerja
                &nbsp;&nbsp;
                <input type="checkbox" <?= $tbek ?> /> Tidak Bekerja
                &nbsp;&nbsp;
                <input type="checkbox" <?= $irt ?> /> Ibu Rumah Tangga
            </td>
        </tr>
        <tr>
            <td></td>
            <td>Instansi: <b><?= strtoupper($data->instansi_pasangan) ?></b></td>
        </tr>
    </tbody>
</table>
<p style="margin-bottom:20px; margin-top: - 200px">
    <strong>Selaku Suami/Istri</strong>
</p>
<p class="mb-0">Dengan ini <b>"menyatakan"</b></p>
<p class="mb-0">
    1. Setuju untuk membeli tanah &amp; bangunan rumah di lokasi Perumahan <span
        class="firstletter"><?= strtoupper(htmlspecialchars($proyek->nama_proyek)) ?></span>.
</p>
<table class="ml-17" border="0" cellspacing="0" cellpadding="0">
    <tbody>
        <tr>
            <td width="65" valign="top">
                <p>
                    Tipe
                </p>
            </td>
            <td width="189" valign="top">
                <p>
                    : <?= strtoupper($data->tipe_rumah) ?>
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
                    : <span class="firstletter"><?= strtoupper($data->nama_jalan) ?> No. <?= strtoupper($data->no_kavling) ?></span>
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
        <tr>
            <td width="65" valign="top">
                <p>
                    Marketing
                </p>
            </td>
            <td width="189" valign="top">
                <p>
                    : <span class="firstletter"><?= strtoupper($data->sales) ?></span>
                </p>
            </td>
        </tr>
    </tbody>
</table>
<!-- <sethtmlpagefooter name="footer_page1" value="on" page="1" /> -->
