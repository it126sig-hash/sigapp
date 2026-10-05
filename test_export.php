<?php
require 'vendor/autoload.php';

$url = "http://sigapp.dev/tagihan/jatuh-tempo/export-excel";
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'id_proyek' => 1
]));
$response = curl_exec($ch);
curl_close($ch);

if ($response === false) {
    echo "cURL Error";
} else {
    $json = json_decode($response, true);
    if ($json && isset($json['status'])) {
        echo "SUCCESS: " . substr($json['file'], 0, 50) . "...";
    } else {
        echo "FAILED: " . substr($response, 0, 500);
    }
}
