<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate PDF CodeIgniter 4 - qadrLabs</title>

</head>

<body>
    <h2>Data Mahasiswa </h2>
    <a href="<?php echo base_url('TestPdf/generate') ?>">
        Download PDF
    </a>
    <table border=1 width=80% cellpadding=2 cellspacing=0 style="margin-top: 5px; text-align:center">
        <thead>
            <tr bgcolor=silver align=center>
                <td width="5%">No</td>
                <td width="25%">Nim</td>
                <td width="50%">Nama</td>
                <td width="20%">Nilai</td>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>1930511041</td>
                <td>Resita</td>
                <td>85</td>
            </tr>
            <tr>
                <td>2</td>
                <td>1930511044</td>
                <td>Tika</td>
                <td>85</td>
            </tr>
            <tr>
                <td>3</td>
                <td>1930511050</td>
                <td>Ramdan</td>
                <td>80</td>
            </tr>
            <tr>
                <td>4</td>
                <td>1930511051</td>
                <td>Nahla</td>
                <td>85</td>
            </tr>
            <tr>
                <td>5</td>
                <td>1930511052</td>
                <td>Reski</td>
                <td>95</td>
            </tr>
        </tbody>
    </table>
    <p>Jumlah data : 5</p>
</body>

</html>