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
    
    if ($courseModel->approveCourse($courseId)) {
        $updatedCourse = $courseModel->displayCourse($courseId);
        $statusClass = match($updatedCourse->status_label) {
            'Approved' => 'success',
            'Rejected' => 'danger',
            default => 'warning'
        };

        $html = "<tr id='course-{$updatedCourse->id}'>
            <td>
                <img src='{$updatedCourse->thumbnail}' alt='thumbnail' class='img-thumbnail mr-2' style='width: 50px; height: 50px; object-fit: cover;'>
                " . htmlspecialchars($updatedCourse->title) . "
            </td>
            <td>" . htmlspecialchars($updatedCourse->teacher_firstname . ' ' . $updatedCourse->teacher_lastname) . "</td>
            <td>" . htmlspecialchars($updatedCourse->category_name) . "</td>
            <td><span class='badge badge-{$statusClass}'>{$updatedCourse->status_label}</span></td>
            <td>
                <button class='btn btn-sm btn-success' 
                        hx-post='helper/approve-course.php'
                        hx-vals='{\"courseId\": {$updatedCourse->id}}'
                        hx-confirm='Are you sure you want to approve this course?'
                        hx-target='#course-{$updatedCourse->id}'
                        hx-swap='outerHTML'
                        disabled>
                    <i class='fas fa-check'></i>
                </button>
                <button class='btn btn-sm btn-danger' 
                        onclick='rejectCourseWithReason({$updatedCourse->id})'
                        disabled>
                    <i class='fas fa-times'></i>
                </button>
                <button class='btn btn-sm btn-secondary'
                        hx-post='helper/delete-course.php'
                        hx-vals='{\"courseId\": {$updatedCourse->id}}'
                        hx-confirm='Are you sure you want to delete this course?'
                        hx-target='#course-{$updatedCourse->id}'
                        hx-swap='outerHTML'>
                    <i class='fas fa-trash'></i>
                </button>
            </td>
        </tr>";

        echo json_encode([
            'success' => true,
            'message' => 'Course approved successfully',
            'html' => $html
        ]);
    } else {
        throw new Exception('Failed to approve course');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}