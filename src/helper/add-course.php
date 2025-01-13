<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Models\Session;
use App\Models\Database;

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

try {
    Session::start();
    
    // Debug: Log incoming data
    error_log('POST Data: ' . print_r($_POST, true));
    error_log('FILES Data: ' . print_r($_FILES, true));
    
    if (Session::get('role') !== 'teacher') {
        throw new Exception('Unauthorized access');
    }

    // Check if uploads directory exists and is writable
    $uploadDir = __DIR__ . '/../../uploads/';
    if (!file_exists($uploadDir)) {
        if (!mkdir($uploadDir, 0777, true)) {
            throw new Exception('Failed to create uploads directory');
        }
    }

    $thumbnailDir = $uploadDir . 'thumbnails/';
    $contentDir = $uploadDir . 'content/';

    // Create directories if they don't exist
    if (!file_exists($thumbnailDir) && !mkdir($thumbnailDir, 0777, true)) {
        throw new Exception('Failed to create thumbnail directory');
    }
    if (!file_exists($contentDir) && !mkdir($contentDir, 0777, true)) {
        throw new Exception('Failed to create content directory');
    }

    // Validate files
    if (!isset($_FILES['thumbnail']) || $_FILES['thumbnail']['error'] !== 0) {
        throw new Exception('Course thumbnail is required');
    }

    if (!isset($_FILES['content']) || $_FILES['content']['error'] !== 0) {
        throw new Exception('Course content file is required');
    }

    // Process thumbnail
    $thumbnailName = uniqid() . '_' . basename($_FILES['thumbnail']['name']);
    $thumbnailPath = 'uploads/thumbnails/' . $thumbnailName;
    if (!move_uploaded_file($_FILES['thumbnail']['tmp_name'], $thumbnailDir . $thumbnailName)) {
        throw new Exception('Failed to upload thumbnail');
    }

    // Process content file
    $contentName = uniqid() . '_' . basename($_FILES['content']['name']);
    $contentPath = 'uploads/content/' . $contentName;
    if (!move_uploaded_file($_FILES['content']['tmp_name'], $contentDir . $contentName)) {
        throw new Exception('Failed to upload content file');
    }

    // Get database connection
    $db = Database::getInstance()->getConnection();

    try {
        $db->beginTransaction();

        // Insert course
        $stmt = $db->prepare("
            INSERT INTO courses (title, description, thumbnail, media, teacherId, categoryId, price)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $result = $stmt->execute([
            $_POST['title'],
            $_POST['description'],
            $thumbnailPath,
            $contentPath,
            Session::get('user_id'),
            $_POST['category'],
            floatval($_POST['price'])
        ]);

        if (!$result) {
            throw new Exception('Database error: ' . implode(', ', $stmt->errorInfo()));
        }

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Course created successfully!'
        ]);

    } catch (\Exception $e) {
        $db->rollBack();
        throw $e;
    }

} catch (Exception $e) {
    error_log('Error in add-course.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
