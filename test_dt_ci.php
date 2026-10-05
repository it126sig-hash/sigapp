<?php
// Load CodeIgniter
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);
require FCPATH . '../app/Config/Paths.php';
$paths = new Config\Paths();
require rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';

$app = Config\Services::codeigniter();
$app->initialize();

$request = \Config\Services::request();
$request->setMethod('post');
$request->setGlobal('post', [
    'draw' => 1,
    'start' => 0,
    'length' => 10,
    'id_proyek' => 1
]);

$service = new \App\Services\KeuanganService();
$response = $service->getListTagihanJatuhTempoGrouped($request);
// Note: DataTable::of returns an object that implements ResponseInterface when you call ->toJson() or something?
// Actually Hermawan DataTables ->toJson() returns a JSON response object. Let's see it.
echo $response->getBody();
