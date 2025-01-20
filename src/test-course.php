<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Database;
use PDO;

try {
    $db = Database::getInstance()->getConnection();
    
    // First, let's check if we have any categories
    $stmt = $db->query("SELECT id FROM categories LIMIT 1");
    $category = $stmt->fetch(PDO::FETCH_OBJ);
    
    if (!$category) {
        // Create a test category if none exists
        $db->exec("INSERT INTO categories (name) VALUES ('Test Category')");
        $categoryId = $db->lastInsertId();
    } else {
        $categoryId = $category->id;
    }
    
    // Now check if we have any teachers
    $stmt = $db->query("SELECT id FROM users WHERE role = 'teacher' LIMIT 1");
    $teacher = $stmt->fetch(PDO::FETCH_OBJ);
    
    if (!$teacher) {
        // Create a test teacher if none exists
        $db->exec("INSERT INTO users (firstName, lastName, email, password, role) VALUES ('Test', 'Teacher', 'test@teacher.com', '" . password_hash('password123', PASSWORD_DEFAULT) . "', 'teacher')");
        $teacherId = $db->lastInsertId();
    } else {
        $teacherId = $teacher->id;
    }
    
    // Create a test course
    $stmt = $db->prepare("
        INSERT INTO courses (title, description, price, categoryId, teacherId, isApproved, createdAt)
        VALUES (:title, :description, :price, :categoryId, :teacherId, 1, NOW())
    ");
    
    $stmt->execute([
        ':title' => 'Test Course',
        ':description' => 'This is a test course description',
        ':price' => 99.99,
        ':categoryId' => $categoryId,
        ':teacherId' => $teacherId
    ]);
    
    $courseId = $db->lastInsertId();
    
    echo "Test course created successfully with ID: " . $courseId . "\n";
    
    // Verify the course was created
    $stmt = $db->prepare("
        SELECT c.*, cat.name as category_name, CONCAT(u.firstName, ' ', u.lastName) as teacher_name
        FROM courses c
        LEFT JOIN categories cat ON c.categoryId = cat.id
        LEFT JOIN users u ON c.teacherId = u.id
        WHERE c.id = :id
    ");
    
    $stmt->execute([':id' => $courseId]);
    $course = $stmt->fetch(PDO::FETCH_OBJ);
    
    echo "Course details:\n";
    echo "Title: " . $course->title . "\n";
    echo "Teacher: " . $course->teacher_name . "\n";
    echo "Category: " . $course->category_name . "\n";
    echo "Price: $" . $course->price . "\n";
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
} 