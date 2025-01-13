<?php 
session_start();
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Models\Database;
use App\Models\User;
use App\Models\Session;

header('Content-Type: text/html'); 

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
    } else {
        http_response_code(401);
        echo "<div class='alert alert-danger text-center'>Invalid credentials</div>";
    }
} catch (Exception $e) {
    http_response_code(500);
    echo "<div class='alert alert-danger text-center'>Server error: " . $e->getMessage() . "</div>";
}
    ?>