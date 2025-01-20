<?php
namespace App\Models;

use \PDO;

class AdminCourse extends Course {
    public function addCourse(array $courseData): bool {
        throw new \Exception('Admins cannot create courses directly. Only teachers can create courses.');
    }

    public function approveCourse(int $courseId): bool {
        try {
            $stmt = $this->db->prepare("
                UPDATE courses 
                SET isApproved = 1,
                    approvedBy = :adminId,
                    approvedAt = CURRENT_TIMESTAMP
                WHERE id = :courseId
            ");

            return $stmt->execute([
                ':adminId' => $this->user_id,
                ':courseId' => $courseId
            ]);

        } catch (\Exception $e) {
            error_log('Error in AdminCourse::approveCourse: ' . $e->getMessage());
            throw $e;
        }
    }

    public function displayCourse(int $id): ?object {
        try {
            // view all courses with additional information
            $stmt = $this->db->prepare("
                SELECT c.*, 
                       cat.name as category_name,
                       u.firstName as teacher_firstname,
                       u.lastName as teacher_lastname,
                       approver.firstName as approved_by_firstname,
                       approver.lastName as approved_by_lastname,
                       rejecter.firstName as rejected_by_firstname,
                       rejecter.lastName as rejected_by_lastname,
                       COUNT(e.id) as enrollment_count,
                       CASE 
                           WHEN c.isApproved = 0 AND c.rejectedBy IS NULL THEN 'Pending Approval'
                           WHEN c.isApproved = 1 THEN 'Approved'
                           WHEN c.rejectedBy IS NOT NULL THEN 'Rejected'
                           ELSE 'Unknown'
                       END as status_label
                FROM courses c
                LEFT JOIN categories cat ON c.categoryId = cat.id
                LEFT JOIN users u ON c.teacherId = u.id
                LEFT JOIN users approver ON c.approvedBy = approver.id
                LEFT JOIN users rejecter ON c.rejectedBy = rejecter.id
                LEFT JOIN enrollments e ON c.id = e.courseId
                WHERE c.id = :courseId 
                AND c.deleted_at IS NULL
                GROUP BY c.id
            ");

            $stmt->execute([':courseId' => $id]);
            $result = $stmt->fetch(PDO::FETCH_OBJ);
            
            if ($result) {
                $result->can_edit = true;
                $result->can_delete = true;
                $result->can_approve = ($result->isApproved == 0 && $result->rejectedBy === null);
                $result->can_reject = ($result->isApproved == 0 && $result->rejectedBy === null);
                $result->teacher_full_name = $result->teacher_firstname . ' ' . $result->teacher_lastname;
            }

            return $result ?: null;

        } catch (\Exception $e) {
            error_log('Error in AdminCourse::displayCourse: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getAllCourses(): array {
        try {
            $stmt = $this->db->prepare("
                SELECT c.*, 
                       cat.name as category_name,
                       u.firstName as teacher_firstname,
                       u.lastName as teacher_lastname,
                       approver.firstName as approved_by_firstname,
                       approver.lastName as approved_by_lastname,
                       COUNT(e.id) as enrollment_count,
                       CASE 
                           WHEN c.isApproved = 0 AND c.rejectedBy IS NULL THEN 'Pending Approval'
                           WHEN c.isApproved = 1 THEN 'Approved'
                           WHEN c.rejectedBy IS NOT NULL THEN 'Rejected'
                           ELSE 'Unknown'
                       END as status_label
                FROM courses c
                LEFT JOIN categories cat ON c.categoryId = cat.id
                LEFT JOIN users u ON c.teacherId = u.id
                LEFT JOIN users approver ON c.approvedBy = approver.id
                LEFT JOIN enrollments e ON c.id = e.courseId
                WHERE c.deleted_at IS NULL
                GROUP BY c.id
                ORDER BY c.createdAt DESC
            ");
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            error_log('Error in AdminCourse::getAllCourses: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getPendingCourses(): array {
        try {
            $stmt = $this->db->prepare("
                SELECT c.*, 
                       cat.name as category_name,
                       u.firstName as teacher_firstname,
                       u.lastName as teacher_lastname,
                       COUNT(e.id) as enrollment_count,
                       'Pending Approval' as status_label
                FROM courses c
                LEFT JOIN categories cat ON c.categoryId = cat.id
                LEFT JOIN users u ON c.teacherId = u.id
                LEFT JOIN enrollments e ON c.id = e.courseId
                WHERE c.isApproved = 0
                AND c.rejectedBy IS NULL
                AND c.deleted_at IS NULL
                GROUP BY c.id
                ORDER BY c.createdAt DESC
            ");
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            error_log('Error in AdminCourse::getPendingCourses: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getDeletedCourses(): array {
        try {
            $stmt = $this->db->prepare("
                SELECT c.*, 
                       cat.name as category_name,
                       u.firstName as teacher_firstname,
                       u.lastName as teacher_lastname,
                       COUNT(e.id) as enrollment_count,
                       'Deleted' as status_label,
                       deleter.firstName as deleted_by_firstname,
                       deleter.lastName as deleted_by_lastname,
                       approver.firstName as approved_by_firstname,
                       approver.lastName as approved_by_lastname,
                       rejecter.firstName as rejected_by_firstname,
                       rejecter.lastName as rejected_by_lastname
                FROM courses c
                LEFT JOIN categories cat ON c.categoryId = cat.id
                LEFT JOIN users u ON c.teacherId = u.id
                LEFT JOIN users deleter ON c.deleted_by = deleter.id
                LEFT JOIN users approver ON c.approvedBy = approver.id
                LEFT JOIN users rejecter ON c.rejectedBy = rejecter.id
                LEFT JOIN enrollments e ON c.id = e.courseId
                WHERE c.deleted_at IS NOT NULL
                GROUP BY c.id
                ORDER BY c.deleted_at DESC
            ");
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            error_log('Error in AdminCourse::getDeletedCourses: ' . $e->getMessage());
            throw $e;
        }
    }

    public function deleteCourse(int $courseId): bool {
        try {
            $stmt = $this->db->prepare("
                UPDATE courses 
                SET deleted_at = CURRENT_TIMESTAMP,
                    deleted_by = :adminId
                WHERE id = :courseId
            ");

            return $stmt->execute([
                ':adminId' => $this->user_id,
                ':courseId' => $courseId
            ]);

        } catch (\Exception $e) {
            error_log('Error in AdminCourse::deleteCourse: ' . $e->getMessage());
            throw $e;
        }
    }

    public function rejectCourse(int $courseId, string $reason): bool {
        try {
            $stmt = $this->db->prepare("
                UPDATE courses 
                SET rejectedBy = :adminId,
                    rejectedAt = CURRENT_TIMESTAMP,
                    rejectionReason = :reason
                WHERE id = :courseId
            ");

            return $stmt->execute([
                ':adminId' => $this->user_id,
                ':courseId' => $courseId,
                ':reason' => $reason
            ]);

        } catch (\Exception $e) {
            error_log('Error in AdminCourse::rejectCourse: ' . $e->getMessage());
            throw $e;
        }
    }
} 