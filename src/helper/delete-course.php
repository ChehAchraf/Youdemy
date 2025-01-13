<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Models\Session;
use App\Models\Course;

header('Content-Type: application/json');

try {
    Session::start();
    
    if (Session::get('role') !== 'teacher') {
        throw new Exception('Unauthorized access');
    }

    // Get JSON data
    $data = json_decode(file_get_contents('php://input'), true);
    $courseId = $data['courseId'] ?? null;

    if (!$courseId) {
        throw new Exception('Course ID is required');
    }

    $courseModel = new Course();
    if ($courseModel->delete($courseId, Session::get('user_id'))) {
        echo json_encode([
            'success' => true,
            'message' => 'Course deleted successfully'
        ]);
    } else {
        throw new Exception('Failed to delete course');
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 