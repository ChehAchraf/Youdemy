<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Models\Session;
use App\Models\Admin;

Session::start();

error_reporting(E_ERROR | E_PARSE);

header('Content-Type: application/json');

if (!Session::get('user_id') || Session::get('role') !== 'admin') {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized: Admin access required'
    ]);
    exit;
}

try {
    $jsonData = file_get_contents('php://input');
    $data = json_decode($jsonData);

    if (!$data || !isset($data->teacherId) || !isset($data->action)) {
        throw new Exception('Invalid request data');
    }

    $admin = new Admin();
    $teacherId = (int) $data->teacherId;
    $action = $data->action;
    $reason = $data->reason ?? null;

    if ($action === 'approve') {
        $result = $admin->verifyTeacher($teacherId, true);
        $message = 'Teacher approved successfully';
    } elseif ($action === 'reject') {
        if (empty($reason)) {
            throw new Exception('Rejection reason is required');
        }
        $result = $admin->rejectTeacher($teacherId, $reason);
        $message = 'Teacher rejected successfully';
    } else {
        throw new Exception('Invalid action');
    }

    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => $message
        ]);
    } else {
        throw new Exception('Failed to process request');
    }

} catch (Exception $e) {
    error_log('Error in verify-teacher.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}