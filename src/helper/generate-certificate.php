<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Models\Session;
use App\Models\Database;

Session::start();

if (!Session::isLoggedIn() || Session::get('role') !== 'student') {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit();
}

$courseId = $_GET['id'] ?? null;
if (!$courseId) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['success' => false, 'message' => 'Course ID is required']);
    exit();
}

try {
    $db = Database::getInstance()->getConnection();
    $studentId = Session::get('user_id');

    $stmt = $db->prepare("
        SELECT c.title, c.id,
               CONCAT(u.firstName, ' ', u.lastName) as student_name,
               e.enrollDate
        FROM courses c
        INNER JOIN enrollments e ON c.id = e.courseId
        INNER JOIN users u ON e.studentId = u.id
        WHERE c.id = :courseId 
        AND e.studentId = :studentId
        AND c.isApproved = 1
        AND c.deleted_at IS NULL
    ");

    $stmt->execute([
        ':courseId' => $courseId,
        ':studentId' => $studentId
    ]);

    $data = $stmt->fetch(PDO::FETCH_OBJ);
    
    if (!$data) {
        header('HTTP/1.1 404 Not Found');
        echo json_encode(['success' => false, 'message' => 'Course not found or not enrolled']);
        exit();
    }

    while (ob_get_level()) {
        ob_end_clean();
    }

    $width = 800;
    $height = 600;
    $image = imagecreatetruecolor($width, $height);
    
    $white = imagecolorallocate($image, 255, 255, 255);
    imagefill($image, 0, 0, $white);
    
    $black = imagecolorallocate($image, 0, 0, 0);
    
    $text = "Certificate of Completion";
    imagestring($image, 5, 300, 100, $text, $black);
    
    $text = $data->student_name;
    imagestring($image, 5, 300, 200, $text, $black);
    
    $text = $data->title;
    imagestring($image, 5, 300, 300, $text, $black);
    
    $text = date('F d, Y', strtotime($data->enrollDate));
    imagestring($image, 5, 300, 400, $text, $black);
    
    header('Content-Type: image/png');
    header('Content-Disposition: attachment; filename="certificate.png"');
    
    imagepng($image);
    imagedestroy($image);
    exit();

} catch (Exception $e) {
    error_log('Error generating certificate: ' . $e->getMessage());
    header('HTTP/1.1 500 Internal Server Error');
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Failed to generate certificate: ' . $e->getMessage()]);
    exit();
}