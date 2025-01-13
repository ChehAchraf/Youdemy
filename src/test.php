<?php 
require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Database;
use App\Models\UserControllers;

try {

    $db = Database::getInstance();
    $conn = $db->getConnection();
    

    $stmt = $conn->query("SELECT * FROM users");
    $users = $stmt->fetchAll();

    $user = new UserControllers(1, 'Mohamed', 'alasri', 'moahmed@alasri.com', '123', 'student', 1);
    $user->register($conn);


    // echo "<pre>";
    // print_r($users);
    // echo "</pre>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}