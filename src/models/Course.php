<?php
namespace App\Models;

use PDO;

class Course {
    protected $db;
    protected $id;
    protected $title;
    protected $description;
    protected $thumbnail;
    protected $media;
    protected $teacherId;
    protected $categoryId;
    protected $price;
    protected $isApproved;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getTeacherCourses($teacherId) {
        $stmt = $this->db->prepare("
            SELECT c.*, cat.name as category_name 
            FROM courses c 
            LEFT JOIN categories cat ON c.categoryId = cat.id 
            WHERE c.teacherId = ?
            ORDER BY c.id DESC
        ");
        
        $stmt->execute([$teacherId]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function delete($courseId, $teacherId) {
        try {
            // Verify ownership
            $stmt = $this->db->prepare("SELECT teacherId FROM courses WHERE id = ?");
            $stmt->execute([$courseId]);
            $course = $stmt->fetch(PDO::FETCH_OBJ);

            if (!$course || $course->teacherId != $teacherId) {
                throw new \Exception('Unauthorized access to this course');
            }

            // Delete course
            $stmt = $this->db->prepare("DELETE FROM courses WHERE id = ?");
            return $stmt->execute([$courseId]);

        } catch (\Exception $e) {
            throw $e;
        }
    }

    // Existing methods...
} 