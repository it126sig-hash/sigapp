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
?>
<style type="text/css">
    * {
        margin: 0;
        padding: 0;
        text-indent: 0;
    }

    h1 {
        color: black;
        font-family: Calibri, sans-serif;
        font-style: normal;
        font-weight: bold;
        text-decoration: underline;
        font-size: 12pt;
    }

    p {
        color: black;
        font-family: Calibri, sans-serif;
        font-style: normal;
        font-weight: normal;
        text-decoration: none;
        font-size: 11pt;
        margin: 0pt;
    }


    li {
        display: block;
    }

    table,
    tbody {
        vertical-align: top;
        overflow: visible;
    }
</style>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <h1 style="text-align:center">Lampiran 3</h1>
    <h1 style="text-align:center">SURAT PERNYAȚAAN PEMERIKSAAN KELAIKAN FUNGSI BANGUNAN GEDUNG</h1>
    <table>
        <tr>
            <td>Nomor Surat Pernyataan </td>
            <td>: <?= $list_slf->no_slf ?></td>
        </tr>
        <tr>
            <td>Tanggal</td>
            <td>: <?= format_date($list_slf->tanggal_slf) ?></td>
        </tr>
    </table>
    <p><br /></p>
    <p>Pada hari…………………………..tanggal………………….bulan …………………………tahun ,yang</p>
    <p>bertanda tangan di bawah ini,</p>
    <p>Penyedia jasa Pengawasan/MK/instansi teknis pembina penyelenggaraan bangunan gedung</p>
    <table style="border: none; border-collapse: collapse;margin:5px 5px">
        <tr>
            <td style="vertical-align: top;">a.</td>
            <td width="300px">Nama penanggung jawab</td>
            <td>: <?= $list_slf->penanggungjawab ?></td>
        </tr>
        <tr>
            <td style="vertical-align: top;">b.</td>
            <td width="300px">Nama jasa Pengawasan/MK.instansi teknis</td>
            <td>: <?= $kavling[0]->nama_pt ?></td>
        </tr>
    </table>
    <p>
        telah melaksanakan pemeriksaan kelaikan fungsi bangunan gedung pada
    </p>
    <br>
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td colspan="3" style="font-weight: bold;">1. Bangunan Gedung</td>
        </tr>
        <tr>
            <td style="width: 5%;"></td>
            <td style="width: 30%;">a. Fungsi utama</td>
            <td>: <?= $list_slf->fungsi_utama ?></td>
        </tr>
        <tr>
            <td></td>
            <td>b. Fungsi tambahan</td>
            <td>: <?= $list_slf->fungsi_tambahan ?></td>
        </tr>
        <tr>
            <td></td>
            <td>c. Jenis bangunan gedung</td>
            <td>: <?= $list_slf->jenis_bangunan ?></td>
        </tr>
        <tr>
            <td></td>
            <td>d. Nama bangunan gedung</td>
            <td>: <?= $kavling[0]->nama_proyek ?></td>
        </tr>
        <tr>
            <td></td>
            <td>e. Nomor pendaftaran bangunan gedung</td>
            <td>:<?= $list_slf->nomor_pendaftaran_bangunan ?></td>
        </tr>
        <tr>
            <td colspan="3" style="font-weight: bold;">2. Lokasi bangunan gedung</td>
        </tr>
        <tr>
            <td></td>
            <td>a. Kampung</td>
            <td>:</td>
        </tr>
        <tr>
            <td></td>
            <td>b. Kelurahan/desa</td>
            <td>: <?= $kavling[0]->kelurahan ?></td>
        </tr>
        <tr>
            <td></td>
            <td>c. Kecamatan</td>
            <td>: <?= $kavling[0]->kecamatan ?></td>
        </tr>
        <tr>
            <td></td>
            <td>d. Kabupaten/kota</td>
            <td>: <?= $kavling[0]->kota ?></td>
        </tr>
        <tr>
            <td></td>
            <td>e. Provinsi</td>
            <td>: <?= $kavling[0]->provinsi ?></td>
        </tr>
        <tr>
            <td></td>
            <td>f. Alamat lokasi terletak di</td>
            <td>: <?= $kavling[0]->alamat_proyek ?></td>
        </tr>
        <tr>
            <td colspan="3" style="font-weight: bold;">3. Permohonan</td>
        </tr>
        <tr>
            <td></td>
            <td>a. Penerbitan Sertifikat Laik Fungsi</td>
            <td>: No. <?= $list_slf->penerbitan_slf_no ?></td>
        </tr>
        <tr>
            <td></td>
            <td>b. Perpanjangan Sertifikat Laik Fungsi</td>
            <td>: Nomor <?= $list_slf->perpanjangan_slf_no ? $list_slf->perpanjangan_slf_no : "........." ?>.tanggal
                <?= $list_slf->perpanjangan_slf_no ? format_date($list_slf->perpanjangan_slf_no) : "..........." ?>.</td>
        </tr>
        <tr>
            <td></td>
            <td>c. Perpanjangan ke</td>
            <td>: <?= $list_slf->perpanjangan_slf_no ?></td>
        </tr>
    </table>
    <br>

    <p>Dengan ini menyatakan bahwa</p>
    <ol id="l6">
        <li data-list-text="1.">
            <p>Persyaratan administrati : <?= $list_slf->persyaratan_administrasi ?></p>
        </li>
        <li data-list-text="2.">
            <p>Persyaratan teknis</p>
            <table>
                <tr>
                    <td style="width: 5px;">a.</td>
                    <td style="width: 250px;">Fungsi bangunan Gedung</td>
                    <td>: <?= $list_slf->persyaratan_fungsi_bangunan ?></td>
                </tr>
                <tr>
                    <td>b.</td>
                    <td>Peruntukan</td>
                    <td>: <?= $list_slf->persyaratan_peruntukan ?></td>
                </tr>
                <tr>
                    <td>c.</td>
                    <td>Tata bangunan</td>
                    <td>: <?= $list_slf->persyaratan_tata_bangunan ?></td>
                </tr>
                <tr>
                    <td>d.</td>
                    <td>Kelaikan fungsi bangunan gedung<br>dinyatakan</td>
                    <td>: <?= $list_slf->persyaratan_kelaikan ?></td>
                </tr>
            </table>
        </li>
    </ol>

    <p><br /></p>
    <p>Sesuai dengan kesimpulan berdasarkan analisis terhadap Daftar Simak Pemeriksaan Kelaikan Fungsi Bangunan Gedung
        terlampir.</p>
    <p><br /></p>
    <p>Surat pernyataan ini berlaku sepanjang tidak ada perubahan yang dilakukan oleh pemilik/pengguna yang mengubah
        sistem dan/atau spesifikasi teknis, atau gangguan penyebab lainnya yang dibuktikan kemudian.</p>
    <p><br /></p>
    <p>Selanjutnya pemilik/pengguna bangunan gedung dapat mengurus permohonan penerbitan Sertifikat Laik Fungsi bangunan
        gedung.</p>
    <p>Demikian surat pemyataan ini dibuat dengan penuh tanggung jawab profesional.</p>
    <p><br /></p>

    <div style="float: right; text-align: center; width: 40%;">
        <p class="s1">.................., 20....</p>
        <p>Penyedia Jasa Pengawasan/MK</p>
        <p>selaku Penanggung Jawab</p>
        <p style="padding-bottom:100px"><br /></p>
        <p class="s2">(<?= $list_slf->penanggungjawab ?>)</p>
    </div>


    <p style="padding-bottom:250px"><br /></p>
    <div style="text-align: center;">
        <p>Disetujui,</p>
        <p>PEMERINTAH PROVINSI/KABUPATEN/KOTA………………………….</p>
        <p>DINAS (instansi teknis Pembina penyelenggaraan bangunan gedung)</p>
        <p style="padding-bottom:100px"><br /></p>
        <p>………………………………………………………………………………………………… </p>
        <p>NIP : …………………………………… </p>
    </div>
    <?php

    ?>
    <p><br /></p>

</body>

</html>