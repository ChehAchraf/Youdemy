<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Models\User;
use App\Models\Session;

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    $raw_input = file_get_contents('php://input');
    error_log('Raw input: ' . $raw_input);
    error_log('POST data: ' . print_r($_POST, true));

    $required = ['firstname', 'lastname', 'email', 'password', 'confirm_password', 'role'];
    foreach ($required as $field) {
        if (!isset($_POST[$field]) || trim($_POST[$field]) === '') {
            throw new Exception("Field '{$field}' is required");
        }
    }

    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Invalid email format");
    }

    if ($_POST['password'] !== $_POST['confirm_password']) {
        throw new Exception("Passwords do not match");
    }

    $allowedRoles = ['student', 'teacher'];
    if (!in_array($_POST['role'], $allowedRoles)) {
        throw new Exception("Invalid role selected");
    }

    if ($_POST['role'] === 'teacher' && empty($_POST['specialization'])) {
        throw new Exception("Specialization is required for teachers");
    }

    $user = new User(
        null,
        trim($_POST['firstname']),
        trim($_POST['lastname']),
        trim($_POST['email']),
        $_POST['password'],
        $_POST['role'],
        true
    );

    if ($_POST['role'] === 'teacher') {
        $user->setSpecialization(trim($_POST['specialization']));
        $user->setVerificationStatus('pending');
    }

    if ($user->register()) {
        $message = $_POST['role'] === 'teacher' 
            ? "<div class='alert alert-success'>Registration successful! Your teacher account is pending admin approval.</div>"
            : "<div class='alert alert-success'>Registration successful! Redirecting to login page...</div>";

        echo json_encode([
            'success' => true,
            'message' => $message
        ]);
    } else {
        throw new Exception("Registration failed. The email might already be in use.");
    }

} catch (Exception $e) {
    error_log('Registration error: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => "<div class='alert alert-danger'>" . htmlspecialchars($e->getMessage()) . "</div>",
        'debug' => [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]
    ]);
}