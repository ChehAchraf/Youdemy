<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Controllers\CourseController;

$controller = new CourseController();

// Route pour ajouter un cours
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_GET['action'] === 'add') {
    try {
        $result = $controller->addCourse();
        echo json_encode(['success' => true, 'message' => 'Course added successfully']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

// Route pour afficher un cours
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    try {
        $course = $controller->displayCourse($_GET['id']);
        echo json_encode(['success' => true, 'data' => $course]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} 