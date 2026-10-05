<?php
require 'vendor/autoload.php';

$url = "http://sigapp.dev/tagihan/jatuh-tempo/ambil-grouped";
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'draw' => 1,
    'start' => 0,
    'length' => 10,
    'id_proyek' => 1,
]));
$response = curl_exec($ch);
curl_close($ch);

if ($response === false) {
    echo "cURL Error";
} else {
    echo substr($response, 0, 1000);
}
