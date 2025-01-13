<?php 
session_start();
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Models\Database;
use App\Models\User;
use App\Models\Session;

header('Content-Type: application/json'); 

try {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $user = new User(
        null,
        '',
        '',
        $email,
        $password,
        'student',
        1
    );

    if ($user->login()) {
        echo json_encode([
            'success' => true,
            'redirect' => '/youdemy/src/index.php'
        ]);
        exit;
    }
    
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid credentials'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
    ?>