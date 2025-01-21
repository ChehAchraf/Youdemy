<?php
namespace App\Models;
use App\Models\User;
use App\Models\Session;
use App\Models\Database;
use App\Interfaces\ICategory;
use App\Interfaces\ITag;
use App\Interfaces\ICourse;
use PDO;

class Admin extends User implements ICategory, ITag, ICourse {
    protected $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        if (Session::get('role') !== 'admin') {
            throw new \Exception('Unauthorized: Admin access required');
        }
        $this->user_id = Session::get('user_id');
    }

    public function getStatistics() {
        try {
            // Get total students
            $studentStmt = $this->db->prepare("
                SELECT COUNT(*) as total 
                FROM users 
                WHERE role = 'student'
            ");
            $studentStmt->execute();
            $totalStudents = $studentStmt->fetch(PDO::FETCH_OBJ)->total;

            // Get total teachers
            $teacherStmt = $this->db->prepare("
                SELECT COUNT(*) as total 
                FROM users 
                WHERE role = 'teacher'
            ");
            $teacherStmt->execute();
            $totalTeachers = $teacherStmt->fetch(PDO::FETCH_OBJ)->total;

            // Get total courses
            $courseStmt = $this->db->prepare("
                SELECT COUNT(*) as total 
                FROM courses
                WHERE deleted_at IS NULL
            ");
            $courseStmt->execute();
            $totalCourses = $courseStmt->fetch(PDO::FETCH_OBJ)->total;

            return [
                'students' => $totalStudents,
                'teachers' => $totalTeachers,
                'courses' => $totalCourses
            ];
        } catch (\Exception $e) {
            throw new \Exception('Error getting statistics: ' . $e->getMessage());
        }
    }

    public function getTopCourses($limit = 5) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    c.id,
                    c.title,
                    c.price,
                    c.isApproved,
                    cat.name as category_name,
                    CONCAT(u.firstname, ' ', u.lastname) as teacher_name,
                    COUNT(e.id) as enrollment_count
                FROM courses c
                JOIN users u ON c.teacherId = u.id
                LEFT JOIN categories cat ON c.categoryId = cat.id
                LEFT JOIN enrollments e ON c.id = e.courseId
                WHERE c.deleted_at IS NULL
                GROUP BY c.id, c.title, c.price, c.isApproved, cat.name, u.firstname, u.lastname
                ORDER BY enrollment_count DESC
                LIMIT :limit
            ");
            
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            throw new \Exception('Error getting top courses: ' . $e->getMessage());
        }
    }

    public function getPendingTeachers() {
        return $this->getUsersByRole('teacher', 'pending');
    }

    public function getAllTeachers() {
        return $this->getUsersByRole('teacher');
    }

    protected function getUsersByRole($role, $verificationStatus = null) {
        $sql = "
            SELECT 
                u.id,
                u.firstname,
                u.lastname,
                u.email,
                u.created_at,
                u.specialization,
                u.verification_status
            FROM users u
            WHERE u.role = ?
        ";

        $params = [$role];

        if ($verificationStatus) {
            $sql .= " AND u.verification_status = ?";
            $params[] = $verificationStatus;
        }

        $sql .= " ORDER BY u.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    protected function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function verifyTeacher(int $teacherId, bool $approve = true): bool {
        try {
            // Check if teacher exists and is pending
            $stmt = $this->db->prepare("
                SELECT * FROM users 
                WHERE id = ? 
                AND role = 'teacher' 
                AND verification_status = 'pending'
            ");
            $stmt->execute([$teacherId]);
            
            if (!$stmt->fetch()) {
                throw new \Exception('Teacher not found or not in pending status');
            }

            // Update teacher status to approved
            $stmt = $this->db->prepare("
                UPDATE users 
                SET verification_status = 'approved',
                    verification_reason = NULL,
                    verified_at = CURRENT_TIMESTAMP,
                    verified_by = ?
                WHERE id = ?
            ");

            return $stmt->execute([$this->user_id, $teacherId]);

        } catch (\Exception $e) {
            error_log('Error in Admin::verifyTeacher: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getRecentActivities($limit = 10) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM (
                    SELECT 
                        'course_created' as activity_type,
                        c.title as subject,
                        CONCAT(u.firstName, ' ', u.lastName) as actor,
                        c.createdAt as activity_date
                    FROM courses c
                    JOIN users u ON c.teacherId = u.id
                    WHERE c.deleted_at IS NULL
                    UNION ALL
                    SELECT 
                        'enrollment' as activity_type,
                        c.title as subject,
                        CONCAT(u.firstName, ' ', u.lastName) as actor,
                        e.enrollDate as activity_date
                    FROM enrollments e
                    JOIN courses c ON e.courseId = c.id
                    JOIN users u ON e.studentId = u.id
                ) activities
                ORDER BY activity_date DESC
                LIMIT :limit
            ");
            
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            throw new \Exception('Error getting recent activities: ' . $e->getMessage());
        }
    }

    public function getCategoryStats() {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    cat.name,
                    COUNT(DISTINCT c.id) as course_count,
                    COUNT(DISTINCT e.studentId) as student_count,
                    COALESCE(SUM(c.price), 0) as total_revenue
                FROM categories cat
                LEFT JOIN courses c ON cat.id = c.categoryId AND c.deleted_at IS NULL
                LEFT JOIN enrollments e ON c.id = e.courseId
                GROUP BY cat.id, cat.name
                ORDER BY course_count DESC
            ");
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            throw new \Exception('Error getting category statistics: ' . $e->getMessage());
        }
    }

    public function addCategory(string $name, string $description = null): bool {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO categories (name, description)
                VALUES (:name, :description)
            ");
            
            return $stmt->execute([
                ':name' => $name,
                ':description' => $description
            ]);
        } catch (\Exception $e) {
            throw new \Exception('Error adding category: ' . $e->getMessage());
        }
    }

    public function updateCategory(int $id, string $name, string $description = null): bool {
        try {
            $stmt = $this->db->prepare("
                UPDATE categories 
                SET name = :name, 
                    description = :description
                WHERE id = :id
            ");
            
            return $stmt->execute([
                ':id' => $id,
                ':name' => $name,
                ':description' => $description
            ]);
        } catch (\Exception $e) {
            throw new \Exception('Error updating category: ' . $e->getMessage());
        }
    }

    public function deleteCategory(int $id): bool {
        try {
            //  check if category has any courses
            $checkStmt = $this->db->prepare("
                SELECT COUNT(*) as count 
                FROM courses 
                WHERE categoryId = :id
            ");
            $checkStmt->execute([':id' => $id]);
            $result = $checkStmt->fetch(PDO::FETCH_OBJ);

            if ($result->count > 0) {
                throw new \Exception('Cannot delete category: It has associated courses');
            }

            $stmt = $this->db->prepare("
                DELETE FROM categories 
                WHERE id = :id
            ");
            
            return $stmt->execute([':id' => $id]);
        } catch (\Exception $e) {
            throw new \Exception('Error deleting category: ' . $e->getMessage());
        }
    }

    public function getAllCategories(): array {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    c.*,
                    COUNT(DISTINCT co.id) as course_count
                FROM categories c
                LEFT JOIN courses co ON c.id = co.categoryId AND co.deleted_at IS NULL
                GROUP BY c.id
                ORDER BY c.name ASC
            ");
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            throw new \Exception('Error getting categories: ' . $e->getMessage());
        }
    }

    public function getCategoryById(int $id): ?object {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM categories 
                WHERE id = :id
            ");
            
            $stmt->execute([':id' => $id]);
            $result = $stmt->fetch(PDO::FETCH_OBJ);
            
            return $result ?: null;
        } catch (\Exception $e) {
            throw new \Exception('Error getting category: ' . $e->getMessage());
        }
    }

    public function addTags(array $tags): array {
        try {
            $this->db->beginTransaction();
            $result = [
                'added' => [],
                'existing' => []
            ];

            //  get all existing tags that match our input
            $placeholders = str_repeat('?,', count($tags) - 1) . '?';
            $stmt = $this->db->prepare("
                SELECT name 
                FROM tags 
                WHERE name IN ($placeholders)
            ");
            $stmt->execute($tags);
            $existingTags = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // Filter  existing tags
            $newTags = array_diff($tags, $existingTags);
            $result['existing'] = $existingTags;

            if (!empty($newTags)) {
                // insert statement
                $insertPlaceholders = str_repeat('(?),', count($newTags) - 1) . '(?)';
                $insertStmt = $this->db->prepare("
                    INSERT INTO tags (name) 
                    VALUES $insertPlaceholders
                ");

                // Execute insert for new tags
                $insertValues = array_values($newTags);
                $insertStmt->execute($insertValues);
                $result['added'] = $insertValues;
            }

            $this->db->commit();
            return $result;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw new \Exception('Error adding tags: ' . $e->getMessage());
        }
    }

    public function updateTag(int $id, string $name): bool {
        try {
            // Check if tag with this name already exists
            $checkStmt = $this->db->prepare("
                SELECT id FROM tags 
                WHERE name = ? AND id != ?
            ");
            $checkStmt->execute([$name, $id]);
            if ($checkStmt->fetch()) {
                throw new \Exception('Tag with this name already exists');
            }

            $stmt = $this->db->prepare("
                UPDATE tags 
                SET name = ? 
                WHERE id = ?
            ");
            return $stmt->execute([$name, $id]);
        } catch (\Exception $e) {
            throw new \Exception('Error updating tag: ' . $e->getMessage());
        }
    }

    public function deleteTag(int $id): bool {
        try {
            // Check if tag is being used
            $checkStmt = $this->db->prepare("
                SELECT COUNT(*) as count 
                FROM course_tags 
                WHERE tagId = ?
            ");
            $checkStmt->execute([$id]);
            $result = $checkStmt->fetch(PDO::FETCH_OBJ);

            if ($result->count > 0) {
                throw new \Exception('Cannot delete tag: It is being used in courses');
            }

            $stmt = $this->db->prepare("
                DELETE FROM tags 
                WHERE id = ?
            ");
            return $stmt->execute([$id]);
        } catch (\Exception $e) {
            throw new \Exception('Error deleting tag: ' . $e->getMessage());
        }
    }

    public function getAllTags(): array {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    t.*,
                    COUNT(DISTINCT ct.courseId) as course_count
                FROM tags t
                LEFT JOIN course_tags ct ON t.id = ct.tagId
                GROUP BY t.id
                ORDER BY t.name ASC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            throw new \Exception('Error getting tags: ' . $e->getMessage());
        }
    }

    public function getTagById(int $id): ?object {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM tags 
                WHERE id = ?
            ");
            $stmt->execute([$id]);
            $result = $stmt->fetch(PDO::FETCH_OBJ);
            return $result ?: null;
        } catch (\Exception $e) {
            throw new \Exception('Error getting tag: ' . $e->getMessage());
        }
    }

    public function getTagsByNames(array $names): array {
        try {
            $placeholders = str_repeat('?,', count($names) - 1) . '?';
            $stmt = $this->db->prepare("
                SELECT * FROM tags 
                WHERE name IN ($placeholders)
            ");
            $stmt->execute($names);
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            throw new \Exception('Error getting tags by names: ' . $e->getMessage());
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
                SELECT c.*, 
                       u.firstName as teacher_firstname,
                       u.lastName as teacher_lastname,
                       cat.name as category_name
                FROM courses c
                LEFT JOIN users u ON c.teacherId = u.id
                LEFT JOIN categories cat ON c.categoryId = cat.id
                WHERE c.deleted_at IS NOT NULL
                ORDER BY c.deleted_at DESC
            ");

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            error_log('Error in Admin::getDeletedCourses: ' . $e->getMessage());
            throw $e;
        }
    }

    public function restoreCourse(int $id): bool {
        try {
            $stmt = $this->db->prepare("
                UPDATE courses 
                SET deleted_at = NULL,
                    deleted_by = NULL
                WHERE id = :courseId
            ");

            return $stmt->execute([':courseId' => $id]);

        } catch (\Exception $e) {
            error_log('Error in Admin::restoreCourse: ' . $e->getMessage());
            throw $e;
        }
    }

    public function addCourse(array $courseData): bool {
        throw new \Exception('Admins cannot create courses directly. Only teachers can create courses.');
    }

    public function displayCourse(int $id): ?object {
        try {
           
            $stmt = $this->db->prepare("
                SELECT c.*, 
                       cat.name as category_name,
                       u.firstName as teacher_firstname,
                       u.lastName as teacher_lastname,
                       COUNT(e.id) as enrollment_count,
                       CASE 
                           WHEN c.status = 'pending' THEN 'Pending Approval'
                           WHEN c.status = 'approved' THEN 'Active'
                           ELSE c.status
                       END as status_label
                FROM courses c
                LEFT JOIN categories cat ON c.categoryId = cat.id
                LEFT JOIN users u ON c.teacherId = u.id
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
                $result->can_approve = ($result->status === 'pending');
                $result->can_reject = ($result->status === 'pending');
                $result->teacher_full_name = $result->teacher_firstname . ' ' . $result->teacher_lastname;
            }

            return $result ?: null;

        } catch (\Exception $e) {
            error_log('Error in Admin::displayCourse: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getCourses(array $filters = []): array {
        try {
            $sql = "
                SELECT c.*, 
                       cat.name as category_name,
                       u.firstName as teacher_firstname,
                       u.lastName as teacher_lastname,
                       COUNT(e.id) as enrollment_count
                FROM courses c
                LEFT JOIN categories cat ON c.categoryId = cat.id
                LEFT JOIN users u ON c.teacherId = u.id
                LEFT JOIN enrollments e ON c.id = e.courseId
                WHERE c.deleted_at IS NULL
            ";

            
            $params = [];
            
            if (isset($filters['categoryId'])) {
                $sql .= " AND c.categoryId = :categoryId";
                $params[':categoryId'] = $filters['categoryId'];
            }

            if (isset($filters['status'])) {
                $sql .= " AND c.status = :status";
                $params[':status'] = $filters['status'];
            }

            if (isset($filters['teacherId'])) {
                $sql .= " AND c.teacherId = :teacherId";
                $params[':teacherId'] = $filters['teacherId'];
            }

            $sql .= " GROUP BY c.id ORDER BY c.createdAt DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_OBJ);

        } catch (\Exception $e) {
            error_log('Error in Admin::getCourses: ' . $e->getMessage());
            throw $e;
        }
    }

    public function rejectTeacher(int $teacherId, string $reason): bool {
        try {
            
            $stmt = $this->db->prepare("
                SELECT * FROM users 
                WHERE id = ? 
                AND role = 'teacher' 
                AND verification_status = 'pending'
            ");
            $stmt->execute([$teacherId]);
            
            if (!$stmt->fetch()) {
                throw new \Exception('Teacher not found or not in pending status');
            }

            
            $stmt = $this->db->prepare("
                UPDATE users 
                SET verification_status = 'rejected',
                    verification_reason = ?,
                    verified_at = CURRENT_TIMESTAMP,
                    verified_by = ?
                WHERE id = ?
            ");

            return $stmt->execute([$reason, $this->user_id, $teacherId]);

        } catch (\Exception $e) {
            error_log('Error in Admin::rejectTeacher: ' . $e->getMessage());
            throw $e;
        }
    }
} 