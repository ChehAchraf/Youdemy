<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Models\Session;
use App\Models\AdminCourse;

header('Content-Type: application/json');

Session::start();

if (Session::get('role') !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

try {
    $courseId = $_POST['courseId'] ?? null;
    
    if (!$courseId) {
        throw new Exception('Course ID is required');
    }

    $courseModel = new AdminCourse();
    
    if ($courseModel->deleteCourse($courseId)) {
        echo json_encode([
            'success' => true,
            'message' => 'Course deleted successfully'
        ]);
    } else {
        throw new Exception('Failed to delete course');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}