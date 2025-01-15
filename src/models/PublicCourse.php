<?php
namespace App\Models;

use PDO;

class PublicCourse extends Course {
    public function addCourse(array $courseData): bool {
        throw new \Exception('Public users cannot add courses');
    }

    public function displayCourse(int $id): ?object {
        try {
            $stmt = $this->db->prepare("
                SELECT c.*, 
                       cat.name as category_name,
                       CONCAT(u.firstName, ' ', u.lastName) as teacher_name,
                       COUNT(e.id) as enrollment_count
                FROM courses c
                LEFT JOIN categories cat ON c.categoryId = cat.id
                LEFT JOIN users u ON c.teacherId = u.id
                LEFT JOIN enrollments e ON c.id = e.courseId
                WHERE c.id = :courseId 
                AND c.isApproved = 1
                AND c.deleted_at IS NULL
                GROUP BY c.id
            ");

            $stmt->execute([':courseId' => $id]);
            return $stmt->fetch(PDO::FETCH_OBJ) ?: null;
        } catch (\Exception $e) {
            error_log('Error in PublicCourse::displayCourse: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getAllCourses(): array {
        return $this->getApprovedCourses();
    }

    public function getPendingCourses(): array {
        return [];
    }

    public function getDeletedCourses(): array {
        return [];
    }

    public function getApprovedCoursesWithPagination(int $page, int $itemsPerPage): array {
        try {
            $offset = ($page - 1) * $itemsPerPage;
            
            $stmt = $this->db->prepare("
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
                LIMIT :limit OFFSET :offset
            ");

            $stmt->bindValue(':limit', $itemsPerPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            error_log('Error in PublicCourse::getApprovedCoursesWithPagination: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getTotalApprovedCourses(): int {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as total
                FROM courses
                WHERE isApproved = 1
                AND deleted_at IS NULL
            ");
            
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_OBJ);
            
            return (int)$result->total;
        } catch (\Exception $e) {
            error_log('Error in PublicCourse::getTotalApprovedCourses: ' . $e->getMessage());
            throw $e;
        }
    }
} 