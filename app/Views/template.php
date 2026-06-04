<?php
echo view('template/header');
echo view('template/menu');
echo view($content, isset($data)?$data:array());
echo view('template/footer');