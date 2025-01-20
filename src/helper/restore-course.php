<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Models\Session;
use App\Models\Course;

Session::start();

if (Session::get('role') !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

try {
    $courseId = $_POST['courseId'] ?? 0;
    
    if (empty($courseId)) {
        throw new Exception('Course ID is required');
    }

    $courseModel = new Course();
    if ($courseModel->restoreCourse((int)$courseId)) {
        echo json_encode([
            'success' => true,
            'message' => 'Course restored successfully'
        ]);
    } else {
        throw new Exception('Failed to restore course');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 