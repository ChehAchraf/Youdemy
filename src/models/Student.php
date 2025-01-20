<?php
namespace App\Models;
use App\Models\User;
use App\Models\Session;
use App\Models\Database;
use PDO;

class Student extends User {
    protected $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        if (Session::get('role') !== 'student') {
            throw new \Exception('Unauthorized: Student access required');
        }
    }

    protected function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function addReview($courseId, $content) {
        try {
            // Check if student is enrolled in the course
            if (!$this->isEnrolledInCourse($courseId)) {
                throw new \Exception('You must be enrolled in the course to leave a comment');
            }

            // Add the comment
            $stmt = $this->db->prepare("
                INSERT INTO comments (courseId, userId, content, createdAt)
                VALUES (:courseId, :userId, :content, NOW())
            ");

            return $stmt->execute([
                ':userId' => Session::get('user_id'),
                ':courseId' => $courseId,
                ':content' => $content
            ]);

        } catch (\Exception $e) {
            error_log('Error in Student::addReview: ' . $e->getMessage());
            throw $e;
        }
    }

    public function isEnrolledInCourse($courseId): bool {
        try {
            $stmt = $this->db->prepare("
                SELECT id 
                FROM enrollments 
                WHERE studentId = :studentId 
                AND courseId = :courseId
            ");
            
            $stmt->execute([
                ':studentId' => Session::get('user_id'),
                ':courseId' => $courseId
            ]);

            return (bool)$stmt->fetch();
        } catch (\Exception $e) {
            error_log('Error in Student::isEnrolledInCourse: ' . $e->getMessage());
            return false;
        }
    }

    public function updateReview($reviewId, $content) {
        try {
            $stmt = $this->db->prepare("
                UPDATE comments 
                SET content = :content
                WHERE id = :reviewId 
                AND userId = :userId
            ");

            return $stmt->execute([
                ':reviewId' => $reviewId,
                ':userId' => Session::get('user_id'),
                ':content' => $content
            ]);

        } catch (\Exception $e) {
            error_log('Error in Student::updateReview: ' . $e->getMessage());
            throw $e;
        }
    }

    public function deleteReview($reviewId) {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM comments 
                WHERE id = :reviewId 
                AND userId = :userId
            ");

            return $stmt->execute([
                ':reviewId' => $reviewId,
                ':userId' => Session::get('user_id')
            ]);

        } catch (\Exception $e) {
            error_log('Error in Student::deleteReview: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getStudentReview($courseId) {
        try {
            $stmt = $this->db->prepare("
                SELECT c.*, 
                       CONCAT(u.firstName, ' ', u.lastName) as student_name,
                       u.profileImage
                FROM comments c
                INNER JOIN users u ON c.userId = u.id
                WHERE c.courseId = :courseId 
                AND c.userId = :userId
            ");

            $stmt->execute([
                ':courseId' => $courseId,
                ':userId' => Session::get('user_id')
            ]);

            return $stmt->fetch(PDO::FETCH_OBJ);

        } catch (\Exception $e) {
            error_log('Error in Student::getStudentReview: ' . $e->getMessage());
            return null;
        }
    }

    public function getEnrollmentDate($courseId) {
        try {
            $stmt = $this->db->prepare("
                SELECT enrollDate 
                FROM enrollments 
                WHERE studentId = :studentId 
                AND courseId = :courseId
            ");
            
            $stmt->execute([
                ':studentId' => Session::get('user_id'),
                ':courseId' => $courseId
            ]);

            $result = $stmt->fetch(PDO::FETCH_OBJ);
            return $result ? $result->enrollDate : null;
        } catch (\Exception $e) {
            error_log('Error in Student::getEnrollmentDate: ' . $e->getMessage());
            return null;
        }
    }

    public static function getCourseReviews($courseId, $page = 1, $limit = 5) {
        try {
            $db = Database::getInstance()->getConnection();
            $offset = ($page - 1) * $limit;

            $stmt = $db->prepare("
                SELECT c.*, 
                       CONCAT(u.firstName, ' ', u.lastName) as student_name
                FROM comments c
                INNER JOIN users u ON c.userId = u.id
                WHERE c.courseId = :courseId
                ORDER BY c.createdAt DESC
                LIMIT :limit OFFSET :offset
            ");

            $stmt->bindValue(':courseId', $courseId);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_OBJ);

        } catch (\Exception $e) {
            error_log('Error in Student::getCourseReviews: ' . $e->getMessage());
            return [];
        }
    }

    public static function getTotalReviews($courseId) {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                SELECT COUNT(*) as total 
                FROM comments 
                WHERE courseId = :courseId
            ");

            $stmt->execute([':courseId' => $courseId]);
            return $stmt->fetch(PDO::FETCH_OBJ)->total;

        } catch (\Exception $e) {
            error_log('Error in Student::getTotalReviews: ' . $e->getMessage());
            return 0;
        }
    }
} 