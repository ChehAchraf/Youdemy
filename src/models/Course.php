<?php
namespace App\Models;
use pdo;
abstract class Course {
    protected $db;
    protected $user_id;
    protected $role;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->user_id = Session::get('user_id');
        $this->role = Session::get('role');
    }


    abstract public function addCourse(array $courseData): bool;
    abstract public function displayCourse(int $id): ?object;
    abstract public function getAllCourses(): array;
    abstract public function getPendingCourses(): array;
    abstract public function getDeletedCourses(): array;


    protected function validateCourseData(array $courseData): void {
        $requiredFields = ['title', 'description', 'price', 'categoryId'];
        foreach ($requiredFields as $field) {
            if (!isset($courseData[$field])) {
                throw new \Exception("Missing required field: {$field}");
            }
        }
    }

    protected function getCourseStatus(string $status): string {
        return match($status) {
            'pending' => 'Pending Approval',
            'approved' => 'Active',
            default => $status
        };
    }

    public function getApprovedCourses(): array {
        try {
            if (!$this->db) {
                throw new \Exception('Database connection not established');
            }

            $query = "
                SELECT 
                    c.*, 
                    cat.name as category_name,
                    CONCAT(u.firstName, ' ', u.lastName) as teacher_name,
                    (SELECT COUNT(*) FROM enrollments e WHERE e.courseId = c.id) as enrollment_count
                FROM courses c
                LEFT JOIN categories cat ON c.categoryId = cat.id
                LEFT JOIN users u ON c.teacherId = u.id
                WHERE c.isApproved = 1
                AND c.deleted_at IS NULL
                ORDER BY c.createdAt DESC
            ";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $courses = $stmt->fetchAll(PDO::FETCH_OBJ);
            
            if (empty($courses)) {
                error_log('No approved courses found');
            } else {
                error_log('Found ' . count($courses) . ' approved courses');
            }
            
            return $courses;
        } catch (\Exception $e) {
            error_log('Error in Course::getApprovedCourses: ' . $e->getMessage());
            throw $e;
        }
    }
} 