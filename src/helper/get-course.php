<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Models\Session;
use App\Models\TeacherCourse;

Session::start();

header('Content-Type: application/json');

if (!Session::get('user_id') || Session::get('role') !== 'teacher') {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized: Teacher access required'
    ]);
    exit;
}

try {
    $courseId = $_GET['id'] ?? null;
    if (!$courseId) {
        throw new Exception('Course ID is required');
    }

    $teacher = new TeacherCourse();
    $course = $teacher->displayCourse($courseId);

    if (!$course) {
        throw new Exception('Course not found or access denied');
    }

    echo json_encode([
        'success' => true,
        'course' => $course
    ]);

} catch (Exception $e) {
    error_log('Error in get-course.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 