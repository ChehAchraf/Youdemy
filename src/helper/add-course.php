<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Models\Session;
use App\Models\TeacherCourse;

// Start session if not already started
Session::start();

// Set header to return JSON response
header('Content-Type: application/json');

// Check if user is logged in and is a teacher
if (!Session::get('user_id') || Session::get('role') !== 'teacher') {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized: Teacher access required'
    ]);
    exit;
}

try {
    // Handle file upload first
    $thumbnail = null;
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../../uploads/thumbnails/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileExtension = pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION);
        $fileName = uniqid() . '.' . $fileExtension;
        $thumbnailPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $thumbnailPath)) {
            $thumbnail = 'uploads/thumbnails/' . $fileName;
        } else {
            throw new Exception('Failed to upload thumbnail');
        }
    }

    // Handle content file upload
    $content = null;
    if (isset($_FILES['content']) && $_FILES['content']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../../uploads/content/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileExtension = pathinfo($_FILES['content']['name'], PATHINFO_EXTENSION);
        $fileName = uniqid() . '.' . $fileExtension;
        $contentPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['content']['tmp_name'], $contentPath)) {
            $content = 'uploads/content/' . $fileName;
        } else {
            throw new Exception('Failed to upload content file');
        }
    }

    // Prepare course data
    $courseData = [
        'title' => $_POST['title'],
        'description' => $_POST['description'],
        'price' => (float) $_POST['price'],
        'categoryId' => (int) $_POST['categoryId'],
        'thumbnail' => $thumbnail,
        'media' => $content,
        'tags' => $_POST['tags'] ?? ''
    ];

    // Create course
    $teacher = new TeacherCourse();
    $result = $teacher->addCourse($courseData);

    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Course created successfully'
        ]);
    } else {
        throw new Exception('Failed to create course');
    }

} catch (Exception $e) {
    error_log('Error in add-course.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}