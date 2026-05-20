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
<p style="margin-left:20px;">Demikian surat ini dibuat sebagai kesanggupan atas syarat Pemesanan Tanah &
    Bangunan
    Rumah.</p>
<br>
<!-- <sethtmlpagefooter name="footer_page1" value="off" page="1-" /> -->
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
                    Materai Rp. 10.0000
                    <br>
                    <br>
                    <br>
                    <br>
                    <br>
                    (<?= strtoupper($data->nama_konsumen) ?>)
                </p>
            </td>
        </tr>
    </tbody>
</table>