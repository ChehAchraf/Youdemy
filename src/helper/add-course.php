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
        }
    }

    $media = null;
    if (isset($_FILES['media']) && $_FILES['media']['error'] === UPLOAD_ERR_OK) {
        $fileExtension = strtolower(pathinfo($_FILES['media']['name'], PATHINFO_EXTENSION));
        $videoExtensions = ['mp4', 'webm', 'ogg'];
        $documentExtensions = ['pdf', 'doc', 'docx', 'ppt', 'pptx'];
        $archiveExtensions = ['zip', 'rar'];
        
        $allowedExtensions = array_merge($videoExtensions, $documentExtensions, $archiveExtensions);
        
        if (!in_array($fileExtension, $allowedExtensions)) {
            throw new \Exception('Invalid file type. Allowed types: ' . implode(', ', $allowedExtensions));
        }
        
        if (in_array($fileExtension, $videoExtensions)) {
            $uploadDir = '../../uploads/videos/';
        } elseif (in_array($fileExtension, $documentExtensions)) {
            $uploadDir = '../../uploads/documents/';
        } else {
            $uploadDir = '../../uploads/archives/';
        }
        
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileName = uniqid() . '.' . $fileExtension;
        $mediaPath = $uploadDir . $fileName;
        
        if ($_FILES['media']['size'] > 500 * 1024 * 1024) {
            throw new \Exception('File size exceeds 500MB limit');
        }
        
        if (move_uploaded_file($_FILES['media']['tmp_name'], $mediaPath)) {
            $media = 'uploads/' . basename(dirname($mediaPath)) . '/' . $fileName;
        } else {
            throw new \Exception('Failed to upload media file');
        }
    }

    $courseData = [
        'title' => $_POST['title'] ?? '',
        'description' => $_POST['description'] ?? '',
        'price' => $_POST['price'] ?? 0,
        'categoryId' => $_POST['categoryId'] ?? null,
        'thumbnail' => $thumbnail,
        'media' => $media
    ];

    $courseModel = new TeacherCourse();
    if ($courseModel->addCourse($courseData)) {
        echo json_encode([
            'success' => true,
            'message' => 'Course added successfully! It will be reviewed by an admin.',
            'redirect' => 'teacher.php'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to add course'
        ]);
    }

} catch (\Exception $e) {
    error_log('Error in add-course.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}