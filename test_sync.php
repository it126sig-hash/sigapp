<?php
define('FCPATH', __DIR__ . '/public/');
// Boot the framework manually
require 'system/Boot.php';
require 'app/Config/Paths.php';
$paths = new \Config\Paths();
$app = \CodeIgniter\Boot::bootWeb($paths);

$service = new \App\Services\MkdtSettlementService();
$result = $service->synchronize(251);
print_r($result);
