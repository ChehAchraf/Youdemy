<?php
require_once __DIR__ . '/../../vendor/autoload.php';
use App\Models\Session;

Session::start();
Session::destroy();

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'redirect' => '/youdemy/src/login.php'
]); 