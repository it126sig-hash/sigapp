<p style="font-weight: bold; text-align:center">LAMPIRAN PENDUKUNG : SURAT PERNYATAAN KUALITAS BANGUNAN</p>
<p><br /></p>
<table style="border: none;">
    <tr>
        <td>No. Surat Pernyataan</td>
        <td>:</td>
        <td></td>
    </tr>
    <tr>
        <td>Kelurahan / Desa</td>
        <td>:</td>
        <td><?= $kavling[0]->kelurahan ?></td>
    </tr>
    <tr>
        <td>Kecamatan</td>
        <td>:</td>
        <td><?= $kavling[0]->kecamatan ?></td>
    </tr>
    <tr>
        <td>Kabupaten / Kota</td>
        <td>:</td>
        <td><?= $kavling[0]->kota ?></td>
    </tr>
    <tr>
        <td>Provinsi</td>
        <td>:</td>
        <td><?= $kavling[0]->provinsi ?></td>
    </tr>
    <tr>
        <td>Alamat Lokasi</td>
        <td>:</td>
        <td><?= $kavling[0]->alamat_proyek ?></td>
    </tr>
</table>

<p><br /></p>
<style>
#tb tr th, #tb tr td{
    border:1px solid #000;
    padding:5px;
}
</style>
<table id="tb" style="width: 100%; border: 1px solid #000; text-align: center;
    border-collapse: collapse;">
    <thead>
        <tr style="">
            <th >
                <p>No.</p>
            </th>
            <th style="width:230px">
                <p>Nama Konsumen</p>
            </th>
            <th style="width:220px;">
                <p>Kavling</p>
            </th>
            <th style="width:20px;">
                <p>No</p>
            </th>
            <th style="width:150px;">
                <p>Type</p>
            </th>
        </tr>
    </thead>
    <tbody>
        <?php
        $no = 1;
        foreach ($kavling as $kav) {
            $nama_konsumen = $kav->nama_konsumen?$kav->nama_konsumen:"Belum ada Konsumen";
            echo "<tr>
                <td>
                    <p>$no</p>
                </td>
                <td>
                    <p>$nama_konsumen</p>
                </td>
                <td>
                    <p>$kav->nama_jalan</p>
                </td>
                <td>
                    <p>$kav->no_kavling</p>
                </td>
                <td>
                    <p>$kav->tipe_rumah</p>
                </td>
            </tr>";
            $no ++;
        }
        ?>
    </tbody>
</table>