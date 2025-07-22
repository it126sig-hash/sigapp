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
        padding-left:10px;
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

$total = $data->harga_ppn + $data->harga_bphtb + $data->harga_biaya_proses;
?>

<div style="margin-left:13px">
    <p>
        Dan sanggup membayar biaya-biaya yang berkenaan dengan pemesanan tanah
        &amp; bangunan tersebut diatas yaitu berupa:
    </p>
    <table border="0" cellspacing="0" cellpadding="0">
        <tbody>
            <tr>
                <td width="187" valign="top">
                    <p>
                        PPN 11% x Harga Jual
                    </p>
                </td>
                <td width="50">
                    : Rp.
                </td>
                <td width="201" valign="top" class="button-green" align="right">
                    <?= num_format($data->harga_ppn) ?>
                </td>
                <td width="300" valign="top">
                </td>
            </tr>
            <tr>
                <td width="187" valign="top">
                    <p>
                        BPHTB
                    </p>
                </td>
                <td>
                    : Rp.
                </td>
                <td width="201" valign="top" class="button-green" align="right">
                    <?= num_format($data->harga_bphtb) ?>
                </td>
                <td width="188" valign="top">
                </td>
            </tr>
            <tr>
                <td width="187" valign="top">
                    <p>
                        Biaya Proses Bank
                    </p>
                </td>
                <td>
                    : Rp.
                </td>
                <td width="201" valign="top" class="button-green" align="right">
                    <?= num_format($data->harga_biaya_proses) ?>
                </td>
                <td width="188" valign="top">
                    <p>
                        (ditetapkan bank)
                    </p>
                </td>
            </tr>
            <tr>
                <td width="187" valign="top">
                    <p>
                        Total
                    </p>
                </td>
                <td>
                    : Rp.
                </td>
                <td width="201" style="border-bottom:1 solid" valign="top" class="button-green" align="right">
                    <?= num_format($data->harga_ppn) ?>
                </td>
                <td width="188" valign="top">
                </td>
            </tr>
            <tr>
                <td width="187" valign="top">
                    <p>
                        Total Pajak, Biaya Bank dll
                    </p>
                </td>
                <td>
                    : Rp.
                </td>
                <td width="201" valign="top" class="button-green" align="right">
                    <strong> <?= num_format($total) ?></strong>
                </td>
                <td width="188" valign="top">
                </td>
            </tr>
            <tr>
                <td width="576" colspan="4" valign="top">
                    <p>
                        (terbilang: <?= terbilang($total) ?> rupiah) yang harus
                        dibayar pada:
                    </p>
                </td>
            </tr>
        </tbody>
    </table>
    <br>
    <table border="0" cellspacing="0" cellpadding="0" width="400px">
        <tbody>
            <tr>
                <td width="100" align="center" valign="top">
                    <p>
                        <strong>Tanggal Pembayaran</strong>
                    </p>
                </td>
                <td width="50" align="center" valign="top">
                    <p>
                        <strong>Uraian Pembayaran</strong>
                    </p>
                </td>
                <td></td>
                <td width="100" align="center" valign="top">
                    <p>
                        <strong>Jumlah</strong>
                    </p>
                </td>
                <td></td>
            </tr>

            <?php
            foreach ($list_tagihan as $tg) {
                if ($tg->status == 'BB') {
                    echo "
                <tr>
                    <td width='208'  align='center' valign='top'>
                        " . format_date($tg->jatuh_tempo_tgl) . "
                    </td>
                    <td width='208' align='center' valign='top'>
                        $tg->berita_acara
                    </td>
                     <td>
                        Rp. 
                    </td>
                    <td width='' align='center' class='button-green' align='right' valign='top'>
                        " . num_format($tg->nominal) . "
                    </td>
                    <td></td>
                </tr>                
                ";
                }
            }
            ?>

        </tbody>
    </table>
</div>
<p>
    3. Semua pembayaran akan saya lakukan tepat waktu sesuai dengan yang
    telah ditentukan pada butir 2(dua), tanpa perlu adanya pemberitahuan
    terlebih dahulu dari <strong><?= $proyek->nama_pt ?></strong>
</p>
<p>
    4. Saya telah mengetahui bahwa pembayaran yang sah yaitu apabila
    pembayaran dilakukan melalui transfer ke rekening <strong><?= $proyek->bank ?></strong> No. Acc
    <strong><?= $proyek->no_rek ?></strong> a/n <strong><?= $proyek->atas_nama ?></strong>.

</p>
<p>
    5. Apabila terjadi keterlambatan pembayaran seperti yang telah
    disebutkan dalam butir 2, maka saya bersedia membayar denda keterlambatan
    sebesar 1.5% dari seluruh sisa kewajiban yang dihitung per hari kerja
    dengan maksimal keterlambatan 30 hari kerja.
</p>
<p>
    6. Apabila keterlambatan pembayaran melewati waktu 30 hari kerja maka
    pembelian tanah dan rumah ini menjadi batal dengan sendirinya.
</p>
<p>
    7. Apabila saya membatalkan pembelian tanah dan rumah atau perjanjian
    ini batal dengan sendirinya, maka saya bersedia membayar denda pembatalan
    sebesar:
</p>
<p class="tab">
    a. Tanda jadi hangus (sebelum wawancara).
</p>
<p class="tab">
    b. 10% dari harga jual yang disepakati.
</p>
<p>
    8. Persyaratan data-data untuk pengajuan KPR dan semua data lain yang
    diperlukan atau saya serahkan secara lengkap kepada <strong><?= $proyek->nama_pt ?></strong>
    paling lambat 2 (dua) minggu setelah Tanggal SPPTB ini ditandatangani.
</p>
<p>
    9. Apabila terjadi keterlambatan penyerahan data seperti yang disebut
    dalam butir 8, maka saya bersedia membayar denda keterlambatan penyerahan
    data sebesar 1% dari rencana KPR yang dihitung per hari kerja dengan
    maksimal keterlambatan 60 hari kerja.
</p>
<p>
    10. Apabila keterlambatan penyerahan data melewati waktu 60 hari kerja maka
    pembelian tanah dan rumah ini menjadi batal dengan sendirinya, sesuai dengan
    ketentuan pada butir 7.
</p>
<p>
    11. Apabila terjadi penurunan KPR dari pihak Bank, maka saya bersedia
    membayar kekurangan /penurunan KPR tersebut paling lambat 2 minggu setelah
    tanggal persetujuan kredit dari Bank.
</p>
<p>
    12. Apabila pihak Bank telah menyetujui KPR yang saya ajukan dan ada
    kekurangan biaya proses maka paling lambat 2 minggu. Sejak tanggal
    persetujuan KPR tersebut akan melunasinya.
</p>
<p>
    13. Apabila KPR saya ditolak oleh bank dengan alasan hasil penilaian tidak
    memenuhi syarat sesuai dengan ketentuan yang berlaku, maka saya bersedia
    dikenakan denda sebesar 5% dari harga jual.
</p>
<p>
    14. Apabila suami/ istri sudah mempunyai KPR, maka saya bersedia dikenakan
    bunga non subsidi dan apabila saya membatalkan pembelaan rumah, maka saya
    bersedia membayar denda pembatalan sesuai dengan butir 7.
</p>
<p>
    15. Apabila terjal pindah kavling, saya bersedia dikenakan biaya pindah
    kavling sebesar:
</p>
<p class="tab">
    a. Rp. 2.000.000,- (kavling sudah dipesan, belum dibangun).
</p>
<p class="tab">
    b. Rp. S.000.000,- (kavling sudah dipesan, sudah dibangun dan sudah
    wawancara).
</p>
<p>
    16. Apabila terjadi ganti nama maka saya bersedia dikenakan denda sesuai
    butir 7 dan kemudian SPPTB ini diganti dengan SPPTB pemohon baru.
</p>
<p>
    17. Ukuran kelebihan tanah mengacu kepada hasil ukur Kantor Pertanahan
    {BPN), apabila terjadi kekurangan atau kelebihan ukuran tanah maka saya
    bersedia:
</p>
<p class="tab">
    a. Membayar kekurangan pembayaran tersebut paling lambat 2 minggu
    setelah tanggal surat pemberitahuan dari <strong><?= $proyek->nama_pt ?></strong>
</p>
<p class="tab">
    b. Menerima kelebihan pembayaran tersebut paling lambat 2 minggu
    setelah surat pemberitahuan dari <strong><?= $proyek->nama_pt ?></strong>
</p>
<p>
    18. Jika semua persyaratan untuk akad kredit sudah terpenuhi maka selambat-
    lambatnya 30 hari sejak pemberitahuan dari pengembang, saya bersedia
    melakukan akad kredit. Apabila tidak dilakukan akad kredit maka pengembang
    berhak melakukan pembatalan secara sepihak dan dengan demikian maka berlaku
    ketentuan sesuai butir 7.
</p>
<p>
    19. Saya bersedia menerima penyerahan rumah dari <strong><?= $proyek->nama_pt ?></strong> sejak penandatanganan
    Akta Jual
    Beli (AJB).
</p>
<p>
    Demikian surat ini dibuat sebagai kesanggupan atas syarat Pemesanan Tanah
    &amp; Bangunan Rumah.
</p>
<table border="0" cellspacing="0" cellpadding="0">
    <tbody>
        <tr>
            <td width="312" valign="top">
            </td>
            <td width="312" align="center" valign="top">
                <p>
                    Bandung, ...................................................
                </p>
            </td>
        </tr>
        <tr>
            <td width="312" align="center" valign="top">
                <p align="center">
                    Mengetahui
                </p>
                <p align="center">
                    <br>
                    <br>
                    <br>
                    <br>
                    <br>
                    <br>
                    <br>
                    <br>
                    (........................................................)
                </p>
            </td>
            <td width="312" align="center" valign="top">
                <p align="center">
                    Yang Menyatakan,
                </p>
                <p align="center">
                    <br>
                    <br>
                    <br>
                    <br>
                    <br>
                    <br>
                    <br>
                    <br>
                    (........................................................)
                </p>
            </td>
        </tr>
    </tbody>
</table>