<?php
namespace App\Models;

use App\Interfaces\ICourse;
use PDO;

class Teacher extends User implements ICourse {
    protected $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        if (Session::get('role') !== 'teacher') {
            throw new \Exception('Unauthorized: Teacher access required');
        }
    }

    
    public function getAllCourses(): array {
        return $this->getCourses();
    }

    public function getPendingCourses(): array {
        return $this->getCourses(['status' => 'pending']);
    }

    public function getDeletedCourses(): array {
        try {
            $stmt = $this->db->prepare("
                SELECT c.*, cat.name as category_name
                FROM courses c
                LEFT JOIN categories cat ON c.categoryId = cat.id
                WHERE c.teacherId = :teacherId
                AND c.deleted_at IS NOT NULL
                ORDER BY c.deleted_at DESC
            ");

            $stmt->execute([':teacherId' => Session::get('user_id')]);
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            error_log('Error in Teacher::getDeletedCourses: ' . $e->getMessage());
            throw $e;
        }
    }

    public function restoreCourse(int $id): bool {
        try {
            
            $stmt = $this->db->prepare("
                SELECT id FROM courses 
                WHERE id = :courseId 
                AND teacherId = :teacherId 
                AND deleted_at IS NOT NULL
            ");

            $stmt->execute([
                ':courseId' => $id,
                ':teacherId' => Session::get('user_id')
            ]);

            if (!$stmt->fetch()) {
                throw new \Exception('Course not found or unauthorized access');
            }

            // Restore the course
            $stmt = $this->db->prepare("
                UPDATE courses 
                SET deleted_at = NULL,
                    deleted_by = NULL
                WHERE id = :courseId 
                AND teacherId = :teacherId
            ");

            return $stmt->execute([
                ':courseId' => $id,
                ':teacherId' => Session::get('user_id')
            ]);

        } catch (\Exception $e) {
            error_log('Error in Teacher::restoreCourse: ' . $e->getMessage());
            throw $e;
        }
    }

    public function addCourse(array $courseData): bool {
        try {
            
            $requiredFields = ['title', 'description', 'price', 'categoryId'];
            foreach ($requiredFields as $field) {
                if (!isset($courseData[$field])) {
                    throw new \Exception("Missing required field: {$field}");
                }
            }

            
            $courseData['teacherId'] = Session::get('user_id');
            $courseData['status'] = 'pending';

            // Insert the course
            $stmt = $this->db->prepare("
                INSERT INTO courses (
                    title, description, price, categoryId, teacherId, 
                    thumbnail, status, createdAt
                ) VALUES (
                    :title, :description, :price, :categoryId, :teacherId,
                    :thumbnail, :status, CURRENT_TIMESTAMP
                )
            ");

            $params = [
                ':title' => $courseData['title'],
                ':description' => $courseData['description'],
                ':price' => $courseData['price'],
                ':categoryId' => $courseData['categoryId'],
                ':teacherId' => $courseData['teacherId'],
                ':thumbnail' => $courseData['thumbnail'] ?? null,
                ':status' => $courseData['status']
            ];

            return $stmt->execute($params);

        } catch (\Exception $e) {
            error_log('Error in Teacher::addCourse: ' . $e->getMessage());
            throw $e;
        }
    }

    public function updateCourse(int $courseId, array $courseData): bool {
        try {
            
            $this->verifyCourseOwnership($courseId);

            
            $updateFields = [];
            $params = [':courseId' => $courseId];

            foreach ($courseData as $field => $value) {
                if (in_array($field, ['title', 'description', 'price', 'categoryId', 'thumbnail'])) {
                    $updateFields[] = "{$field} = :{$field}";
                    $params[":{$field}"] = $value;
                }
            }

            if (empty($updateFields)) {
                throw new \Exception('No valid fields to update');
            }

            $updateFields[] = "updated_at = CURRENT_TIMESTAMP";
            
            $sql = "UPDATE courses SET " . implode(', ', $updateFields) . 
                   " WHERE id = :courseId AND teacherId = :teacherId";
            
            $params[':teacherId'] = Session::get('user_id');

            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);

        } catch (\Exception $e) {
            error_log('Error in Teacher::updateCourse: ' . $e->getMessage());
            throw $e;
        }
    }

    public function deleteCourse(int $courseId): bool {
        try {
            
            $this->verifyCourseOwnership($courseId);

            
            $stmt = $this->db->prepare("
                UPDATE courses 
                SET deleted_at = CURRENT_TIMESTAMP,
                    deleted_by = :teacherId
                WHERE id = :courseId AND teacherId = :teacherId
            ");

            return $stmt->execute([
                ':courseId' => $courseId,
                ':teacherId' => Session::get('user_id')
            ]);

        } catch (\Exception $e) {
            error_log('Error in Teacher::deleteCourse: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getCourseById(int $courseId): ?object {
        try {
            $stmt = $this->db->prepare("
                SELECT c.*, cat.name as category_name
                FROM courses c
                LEFT JOIN categories cat ON c.categoryId = cat.id
                WHERE c.id = :courseId 
                AND c.teacherId = :teacherId
                AND c.deleted_at IS NULL
            ");

            $stmt->execute([
                ':courseId' => $courseId,
                ':teacherId' => Session::get('user_id')
            ]);

            $result = $stmt->fetch(PDO::FETCH_OBJ);
            return $result ?: null;

        } catch (\Exception $e) {
            error_log('Error in Teacher::getCourseById: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getCourses(array $filters = []): array {
        try {
            $sql = "
                SELECT c.*, cat.name as category_name,
                       COUNT(e.id) as enrollment_count
                FROM courses c
                LEFT JOIN categories cat ON c.categoryId = cat.id
                LEFT JOIN enrollments e ON c.id = e.courseId
                WHERE c.teacherId = :teacherId
                AND c.deleted_at IS NULL
            ";

            // Apply filters
            $params = [':teacherId' => Session::get('user_id')];
            
            if (isset($filters['categoryId'])) {
                $sql .= " AND c.categoryId = :categoryId";
                $params[':categoryId'] = $filters['categoryId'];
            }

            if (isset($filters['status'])) {
                $sql .= " AND c.status = :status";
                $params[':status'] = $filters['status'];
            }

            $sql .= " GROUP BY c.id ORDER BY c.createdAt DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_OBJ);

        } catch (\Exception $e) {
            error_log('Error in Teacher::getCourses: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function verifyCourseOwnership(int $courseId): void {
        $stmt = $this->db->prepare("
            SELECT id FROM courses 
            WHERE id = :courseId AND teacherId = :teacherId
        ");

        $stmt->execute([
            ':courseId' => $courseId,
            ':teacherId' => Session::get('user_id')
        ]);

        if (!$stmt->fetch()) {
            throw new \Exception('Course not found or unauthorized access');
        }
    }

    public function displayCourse(int $id): ?object {
        try {
            
            $stmt = $this->db->prepare("
                SELECT c.*, 
                       cat.name as category_name,
                       COUNT(e.id) as enrollment_count,
                       CASE 
                           WHEN c.status = 'pending' THEN 'Pending Approval'
                           WHEN c.status = 'approved' THEN 'Active'
                           ELSE c.status
                       END as status_label
                FROM courses c
                LEFT JOIN categories cat ON c.categoryId = cat.id
                LEFT JOIN enrollments e ON c.id = e.courseId
                WHERE c.id = :courseId 
                AND c.teacherId = :teacherId
                AND c.deleted_at IS NULL
                GROUP BY c.id
            ");

            $stmt->execute([
                ':courseId' => $id,
                ':teacherId' => Session::get('user_id')
            ]);

            $result = $stmt->fetch(PDO::FETCH_OBJ);
            
            if ($result) {
                
                $result->can_edit = true;
                $result->can_delete = true;
                $result->awaiting_approval = ($result->status === 'pending');
            }

            return $result ?: null;

        } catch (\Exception $e) {
            error_log('Error in Teacher::displayCourse: ' . $e->getMessage());
            throw $e;
        }
    }
} 