<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Models\Database;
use App\Models\User;
use App\Models\Session;

header('Content-Type: application/json');

try {
    // Validate input
    $firstname = trim($_POST['firstname'] ?? '');
    $lastname = trim($_POST['lastname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation checks
    if (empty($firstname) || empty($lastname) || empty($email) || empty($password)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'All fields are required'
        ]);
        exit;
    }

    if ($password !== $confirm_password) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Passwords do not match'
        ]);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid email format'
        ]);
        exit;
    }

    // Check if email already exists
    $db = Database::getInstance();
    $conn = $db->getConnection();
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->fetch()) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Email already exists'
        ]);
        exit;
    }

    // Create new user
    $user = new User(
        null,
        $firstname,
        $lastname,
        $email,
        $password,
        'student', // Default role
        1  // Active by default
    );

    $user->register($db);

    // Auto login after registration
    if ($user->login()) {
        echo json_encode([
            'success' => true,
            'redirect' => '/youdemy/src/index.php'
        ]);
        exit;
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
} 