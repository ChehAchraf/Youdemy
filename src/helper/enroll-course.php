<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Models\Session;
use App\Models\Database;

header('Content-Type: application/json');

Session::start();

if (!Session::isLoggedIn() || Session::get('role') !== 'student') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Please login as a student to enroll in courses'
    ]);
    exit();
}

try {
    $courseId = $_POST['courseId'] ?? null;
    $studentId = Session::get('user_id');

    if (!$courseId) {
        throw new \Exception('Course ID is required');
    }

    $db = Database::getInstance()->getConnection();

    $checkStmt = $db->prepare("
        SELECT id FROM enrollments 
        WHERE studentId = :studentId 
        AND courseId = :courseId
    ");
    $checkStmt->execute([
        ':studentId' => $studentId,
        ':courseId' => $courseId
    ]);

    if ($checkStmt->fetch()) {
        echo json_encode([
            'success' => true,
            'message' => 'You are already enrolled in this course',
            'redirect' => "course-content.php?id=" . $courseId
        ]);
        exit();
    }

    $courseStmt = $db->prepare("
        SELECT id, title 
        FROM courses 
        WHERE id = :courseId 
        AND isApproved = 1 
        AND deleted_at IS NULL
    ");
    $courseStmt->execute([':courseId' => $courseId]);
    $course = $courseStmt->fetch(PDO::FETCH_OBJ);

    if (!$course) {
        throw new \Exception('Course not found or not available');
    }

    $enrollStmt = $db->prepare("
        INSERT INTO enrollments (studentId, courseId, enrollDate)
        VALUES (:studentId, :courseId, CURRENT_TIMESTAMP)
    ");

    $result = $enrollStmt->execute([
        ':studentId' => $studentId,
        ':courseId' => $courseId
    ]);

    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Successfully enrolled in the course',
            'redirect' => "course-content.php?id=" . $courseId
        ]);
    } else {
        throw new \Exception('Failed to enroll in the course');
    }

} catch (\Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}