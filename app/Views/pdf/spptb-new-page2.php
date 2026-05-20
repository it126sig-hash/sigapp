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
<br><br>
<p class="ml-17">
    Setuju dan sanggup untuk membayar sebagai berikut:
</p>
<div class="ml-17">
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
                <td width="160" valign="top">Harga Jual</td>
                <td width="208" valign="top"></td>
                <td> : Rp.</td>
                <td width="208" valign="top" class="button-green" align="right">
                    <span style="margin:500pt"><?= num_format($data->harga_jual) ?></span>
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

            <?php if ($data->is_allin == 0):
                $pengurang = $data->harga_sbum + $data->harga_diskon_uang_muka;
                $total = $data->harga_uang_muka + $data->harga_bphtb + $data->harga_biaya_proses + $data->harga_administrasi + $data->harga_penambahan_tanah + $data->harga_penambahan;
                $grandTotal = $total - $pengurang;
            ?>
                <tr>
                    <td width="160" valign="top">UM </td>
                    <td width="208" valign="top"></td>
                    <td> : Rp.</td>
                    <td width="208" valign="top" class="button-green" align="right">
                        <span style="margin:500pt"><?= num_format($data->harga_uang_muka) ?></span>
                    </td>
                </tr>
                <tr class="is_hidden">
                    <td width="160" valign="top">
                        <ul>
                            <li>Biaya Administrasi</li>
                        </ul>
                    </td>
                    <td width="208" valign="top"></td>
                    <td> : Rp.</td>
                    <td width="208" valign="top" class="button-green" align="right">
                        <span style="margin:500pt"><?= num_format($data->harga_administrasi) ?></span>
                    </td>
                </tr>
                <tr class="is_hidden">
                    <td width="160" valign="top">
                        <ul>
                            <li>PPN11 %</li>
                        </ul>
                    </td>
                    <td width="208" valign="top"></td>
                    <td> : Rp.</td>
                    <td width="208" valign="top" class="button-green" align="right">
                        <span style="margin:500pt"><?= num_format($data->harga_ppn) ?></span>
                    </td>
                </tr>
                <tr class="is_hidden">
                    <td width="160" valign="top">
                        <ul>
                            <li>BPHTB</li>
                        </ul>
                    </td>
                    <td width="208" valign="top"></td>
                    <td> : Rp.</td>
                    <td width="208" valign="top" class="button-green" align="right">
                        <span style="margin:500pt"><?= num_format($data->harga_bphtb) ?></span>
                    </td>
                </tr>
                <tr class="is_hidden">
                    <td width="160" valign="top">
                        <ul>
                            <li>Biaya Proses</li>
                        </ul>
                    </td>
                    <td width="208" valign="top"></td>
                    <td> : Rp.</td>
                    <td width="208" valign="top" class="button-green" align="right">
                        <span style="margin:500pt"><?= num_format($data->harga_biaya_proses) ?></span>
                    </td>
                </tr>
                <tr class="is_hidden">
                    <td width="160" valign="top">
                        <ul>
                            <li>Biaya lain-lain (Kavling Strategis dll)</li>
                        </ul>
                    </td>
                    <td width="208" valign="top"></td>
                    <td> : Rp.</td>
                    <td width="208" valign="top" class="button-green" align="right">
                        <span
                            style="margin:500pt"><?= num_format($data->harga_penambahan_tanah + $data->harga_penambahan) ?></span>
                    </td>
                </tr>
                <tr class="is_hidden">
                    <td width="160" valign="top">
                        <ul>
                            <li>SBUM</li>
                        </ul>
                    </td>
                    <td width="208" valign="top"></td>
                    <td> : Rp.</td>
                    <td width="208" valign="top" class="button-green" align="right">
                        <span style="margin:500pt">(<?= num_format($data->harga_sbum) ?>)</span>
                    </td>
                </tr>
                <tr class="is_hidden">
                    <td width="160" valign="top">
                        <ul>
                            <li>Discount</li>
                        </ul>
                    </td>
                    <td width="208" valign="top"></td>
                    <td> : Rp.</td>
                    <td width="208" valign="top" class="button-green" align="right">
                        <span style="margin:500pt">(<?= num_format($data->harga_diskon_uang_muka) ?>)</span>
                    </td>
                </tr>
            <?php else:
                $grandTotal = $data->harga_allin;
            ?>

                </tr>

            <?php endif; ?>


        </tbody>
    </table>

    <p style="margin-bottom:20px;padding-top:-10px">
        <strong>Total yang harus dibayar: Rp. <?= num_format($grandTotal) ?>( <?= $data->booking_fee == 0 ? "TERMASUK" : "TIDAK TERMASUK" ?> <i>booking
                fee</i>)</strong>
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
        </tbody>
    </table>
    <div class="terms">
        <ol start="2">
            <li>Semua pembayaran akan saya lakukan dengan tepat waktu sesuai dengan yang telah ditentukan pada butir
                1
                (satu), tanpa perlu adanya pemberitahuan terlebih dahulu dari pihak developer.</li>

            <li>Saya telah mengetahui bahwa pembayaran yang sah yaitu apabila pembayaran dilakukan melalui transfer
                ke
                rekening <strong><?= strtoupper($proyek->bank) ?></strong> No. Acc <strong><?= strtoupper($proyek->no_rek) ?></strong> a/n
                <strong><?= strtoupper($proyek->atas_nama) ?>.
            </li>

            <li>Apabila keterlambatan pembayaran melewati waktu 30 hari kerja. Saya bersedia pembelian tanah dan
                rumah
                ini dibatalkan dengan sendirinya.</li>

            <li>Apabila saya membatalkan pembelian tanah dan rumah atau perjanjian ini batal dengan sendirinya, maka
                saya bersedia membayar denda pembatalan sebesar:
                <br>a. Booking Fee hangus.
                <br>b. Denda 5 % dari harga jual yang disepakati.
            </li>

            <li>Persyaratan data-data untuk pengajuan KPR dan semua data lain yang diperlukan atau saya serahkan
                secara
                lengkap <?= strtoupper($proyek->nama_pt) ?>. paling lambat 2 (dua) minggu setelah tanggal SPPTB ini
                ditandatangani.
            </li>

            <li>Apabila keterlambatan penyerahan data melewati waktu 30 hari kerja maka pembelian tanah dan rumah
                ini
                menjadi batal dengan sendirinya, sesuai dengan ketentuan pada butir 5.</li>

            <li>Apabila terjadi penurunan KPR dari pihak Bank, maka saya bersedia membayar kekurangan / penurunan
                KPR
                tersebut paling lambat 2 minggu setelah tanggal persetujuan kredit dari Bank.</li>

            <li>Apabila terjadi ganti nama atau pindah kavling maka saya bersedia dikenakan pembayaran booking fee
                kembali dan kemudian SPPTB ini diganti dengan SPPTB pemohon baru.</li>

            <li>Ukuran kelebihan tanah mengacu kepada hasil ukur Kantor Pertanahan (BPN), apabila terjadi kekurangan
                atau kelebihan ukuran tanah maka saya bersedia:
                <br>a. Membayar kekurangan pembayaran tersebut paling lambat 1 bulan setelah tanggal surat
                pemberitahuan
                dari <?= strtoupper($proyek->nama_pt) ?>.
                <br>b. Menerima kelebihan pembayaran tersebut paling lambat 1 bulan setelah tanggal surat
                pemberitahuan
                dari <?= strtoupper($proyek->nama_pt) ?>.
            </li>

            <li>Jika semua persyaratan untuk akad kredit sudah terpenuhi maka selambat- lambatnya 30 hari sejak
                pemberitahuan dari pengembang, saya bersedia melakukan akad kredit. Apabila tidak dilakukan akad
                kredit
                maka pengembang berhak melakukan pembatalan secara sepihak dan dengan demikian maka berlaku
                ketentuan
                sesuai butir 5.</li>

            <li>Setelah serah terima dilakukan maka saya bersedia untuk membayar iuran Pemeliharaan Lingkungan yang
                besaran awalnya senilai Rp. 10.000 (sepuluh ribu rupiah) setiap bulan.</li>
        </ol>
    </div>
</div>