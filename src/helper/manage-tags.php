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
            $tagsString = $_POST['tags'] ?? '';
            if (empty($tagsString)) {
                throw new Exception('Tags are required');
            }
            
            // Split tags by comma and trim whitespace
            $tags = array_map('trim', explode(',', $tagsString));
            $tags = array_filter($tags); // Remove empty values
            
            if (empty($tags)) {
                throw new Exception('No valid tags provided');
            }

            $result = $admin->addTags($tags);
            $response = [
                'success' => true,
                'message' => sprintf(
                    'Added %d new tags, %d already existed',
                    count($result['added']),
                    count($result['existing'])
                ),
                'data' => $result
            ];
            break;

        case 'update':
            $id = $_POST['id'] ?? 0;
            $name = $_POST['name'] ?? '';
            
            if (empty($id) || empty($name)) {
                throw new Exception('Tag ID and name are required');
            }
            
            if ($admin->updateTag((int)$id, $name)) {
                $response = [
                    'success' => true,
                    'message' => 'Tag updated successfully'
                ];
            }
            break;

        case 'delete':
            $id = $_POST['id'] ?? 0;
            
            if (empty($id)) {
                throw new Exception('Tag ID is required');
            }
            
            if ($admin->deleteTag((int)$id)) {
                $response = [
                    'success' => true,
                    'message' => 'Tag deleted successfully'
                ];
            }
            break;

        case 'get':
            $tags = $admin->getAllTags();
            $response = [
                'success' => true,
                'data' => $tags
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