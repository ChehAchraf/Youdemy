<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Models\Session;

header('Content-Type: application/json');

try {
    Session::start();
    Session::destroy();
    
    echo json_encode([
        'success' => true,
        'message' => 'Logged out successfully',
        'redirect' => 'login.php'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 