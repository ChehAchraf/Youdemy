<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Models\Session;
use App\Models\TeacherCourse;

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

try {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
        throw new Exception('Access denied');
    }

    if (!isset($_POST['courseId'], $_POST['title'], $_POST['description'], $_POST['categoryId'], $_POST['price'])) {
        throw new Exception('Missing required fields');
    }

    $courseData = [
        'id' => $_POST['courseId'],
        'title' => $_POST['title'],
        'description' => $_POST['description'],
        'categoryId' => $_POST['categoryId'],
        'price' => $_POST['price']
    ];

    if (!empty($_FILES['thumbnail']['name'])) {
        $thumbnail = $_FILES['thumbnail'];
        $uploadDir = __DIR__ . '/../../uploads/thumbnails/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $thumbnailName = uniqid() . '_' . basename($thumbnail['name']);
        $thumbnailPath = $uploadDir . $thumbnailName;

        if (move_uploaded_file($thumbnail['tmp_name'], $thumbnailPath)) {
            $courseData['thumbnail'] = 'uploads/thumbnails/' . $thumbnailName;
        }
    }

    if (!empty($_FILES['content']['name'])) {
        $content = $_FILES['content'];
        $uploadDir = __DIR__ . '/../../uploads/contents/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $contentName = uniqid() . '_' . basename($content['name']);
        $contentPath = $uploadDir . $contentName;

        if (move_uploaded_file($content['tmp_name'], $contentPath)) {
            $courseData['media'] = 'uploads/contents/' . $contentName;
        }
    }

    $teacherCourse = new TeacherCourse();
    $success = $teacherCourse->updateCourse($courseData);

    if (!$success) {
        throw new Exception('Failed to update course');
    }

    $courses = $teacherCourse->getAllCourses();
    
    $html = '';
    foreach ($courses as $course) {
        $statusClass = match($course->status_label) {
            'Approved' => 'success',
            'Rejected' => 'danger',
            default => 'warning'
        };

        $html .= "<tr id='course-row-{$course->id}'>
            <td>
                <img src='{$course->thumbnail}' alt='thumbnail' class='img-thumbnail mr-2' style='width: 50px; height: 50px; object-fit: cover;'>
                " . htmlspecialchars($course->title) . "
            </td>
            <td>" . htmlspecialchars($course->category_name) . "</td>
            <td>$" . number_format($course->price, 2) . "</td>
            <td><span class='badge badge-{$statusClass}'>{$course->status_label}</span></td>
            <td>
                <button class='btn btn-sm btn-primary' onclick='editCourse({$course->id})'>
                    <i class='fas fa-edit'></i>
                </button>
                <button class='btn btn-sm btn-danger' onclick='deleteCourse({$course->id})'>
                    <i class='fas fa-trash'></i>
                </button>
            </td>
        </tr>";
    }

    if (empty($html)) {
        $html = "<tr><td colspan='5' class='text-center'>No courses found</td></tr>";
    }

    echo json_encode([
        'success' => true,
        'message' => 'Course updated successfully',
        'html' => $html
    ]);

} catch (Exception $e) {
    error_log("Error in edit-course.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}