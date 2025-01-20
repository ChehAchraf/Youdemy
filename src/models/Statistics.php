<?php
namespace App\Models;
use App\Models\Database;
use PDO;

class Statistics {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getTotalStudents() {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE role = 'student'");
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function getTotalTeachers() {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE role = 'teacher'");
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function getTotalCourses() {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM courses");
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function getTopCourses($limit = 5) {
        $stmt = $this->db->prepare("
            SELECT 
                c.id,
                c.title,
                CONCAT(u.firstName, ' ', u.lastName) as teacher,
                COUNT(e.id) as total_students,
                c.price,
                cat.name as category
            FROM courses c
            JOIN users u ON c.teacherId = u.id
            JOIN categories cat ON c.categoryId = cat.id
            LEFT JOIN enrollments e ON c.id = e.courseId
            GROUP BY c.id, c.title, u.firstName, u.lastName, c.price, cat.name
            ORDER BY total_students DESC
            LIMIT :limit
        ");
        
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRecentActivities($limit = 10) {
        $stmt = $this->db->prepare("
            (SELECT 
                'enrollment' as type,
                CONCAT(u.firstName, ' ', u.lastName) as user,
                c.title as content,
                e.enrollDate as date
            FROM enrollments e
            JOIN users u ON e.studentId = u.id
            JOIN courses c ON e.courseId = c.id)
            UNION
            (SELECT 
                'new_course' as type,
                CONCAT(u.firstName, ' ', u.lastName) as user,
                c.title as content,
                c.createdAt as date
            FROM courses c
            JOIN users u ON c.teacherId = u.id)
            ORDER BY date DESC
            LIMIT :limit
        ");
        
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCategoryStats() {
        $stmt = $this->db->prepare("
            SELECT 
                cat.name,
                COUNT(c.id) as course_count,
                COUNT(DISTINCT e.studentId) as student_count
            FROM categories cat
            LEFT JOIN courses c ON cat.id = c.categoryId
            LEFT JOIN enrollments e ON c.id = e.courseId
            GROUP BY cat.id
            ORDER BY course_count DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} 