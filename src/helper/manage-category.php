<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Models\Session;
use App\Models\Admin;

Session::start();

// Check if user is admin
if (Session::get('role') !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

try {
    $admin = new Admin();
    $action = $_POST['action'] ?? '';
    $response = ['success' => false, 'message' => 'Invalid action'];

    switch ($action) {
        case 'add':
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? null;
            
            if (empty($name)) {
                throw new Exception('Category name is required');
            }
            
            if ($admin->addCategory($name, $description)) {
                $response = [
                    'success' => true,
                    'message' => 'Category added successfully'
                ];
            }
            break;

        case 'update':
            $id = $_POST['id'] ?? 0;
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? null;
            
            if (empty($id) || empty($name)) {
                throw new Exception('Category ID and name are required');
            }
            
            if ($admin->updateCategory((int)$id, $name, $description)) {
                $response = [
                    'success' => true,
                    'message' => 'Category updated successfully'
                ];
            }
            break;

        case 'delete':
            $id = $_POST['id'] ?? 0;
            
            if (empty($id)) {
                throw new Exception('Category ID is required');
            }
            
            if ($admin->deleteCategory((int)$id)) {
                $response = [
                    'success' => true,
                    'message' => 'Category deleted successfully'
                ];
            }
            break;

        case 'get':
            $categories = $admin->getAllCategories();
            $response = [
                'success' => true,
                'data' => $categories
            ];
            break;
    }

    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 