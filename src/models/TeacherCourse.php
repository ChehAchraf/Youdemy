<?php
namespace App\Models;

use \PDO;

class TeacherCourse extends Course {
    protected function addTags(array $tagNames): array {
        try {
            $inTransaction = $this->db->inTransaction();
            if (!$inTransaction) {
                $this->db->beginTransaction();
            }

            $result = [
                'added' => [],
                'existing' => []
            ];

            // First, get all existing tags that match our input
            $placeholders = str_repeat('?,', count($tagNames) - 1) . '?';
            $stmt = $this->db->prepare("
                SELECT id, name 
                FROM tags 
                WHERE name IN ($placeholders)
            ");
            $stmt->execute($tagNames);
            $existingTags = $stmt->fetchAll(PDO::FETCH_OBJ);
            
            // Get existing tag names
            $existingTagNames = array_map(function($tag) { 
                return $tag->name; 
            }, $existingTags);
            
            $result['existing'] = $existingTags;

            // Filter out new tags
            $newTags = array_diff($tagNames, $existingTagNames);

            if (!empty($newTags)) {
                // Prepare the insert statement
                $insertPlaceholders = str_repeat('(?),', count($newTags) - 1) . '(?)';
                $insertStmt = $this->db->prepare("
                    INSERT INTO tags (name) 
                    VALUES $insertPlaceholders
                ");

                // Execute insert for new tags
                $insertValues = array_values($newTags);
                $insertStmt->execute($insertValues);

                // Get the newly inserted tags
                $newTagsStmt = $this->db->prepare("
                    SELECT id, name 
                    FROM tags 
                    WHERE name IN ($placeholders)
                ");
                $newTagsStmt->execute($newTags);
                $result['added'] = $newTagsStmt->fetchAll(PDO::FETCH_OBJ);
            }

            if (!$inTransaction) {
                $this->db->commit();
            }
            return array_merge($result['existing'], $result['added']);
        } catch (\Exception $e) {
            if (!$inTransaction && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw new \Exception('Error adding tags: ' . $e->getMessage());
        }
    }

    public function addCourse(array $courseData): bool {
        try {
            // Validate course data
            $this->validateCourseData($courseData);

            // Start transaction
            $this->db->beginTransaction();

            // Process tags if provided
            $tags = [];
            if (!empty($courseData['tags'])) {
                // Split tags by comma and trim whitespace
                $tagNames = array_map('trim', explode(',', $courseData['tags']));
                // Remove empty tags
                $tagNames = array_filter($tagNames);
                
                if (!empty($tagNames)) {
                    $tags = $this->addTags($tagNames);
                }
            }

            // Teachers can only create pending courses
            $courseData['teacherId'] = $this->user_id;
            $courseData['isApproved'] = 0;

            // Insert the course
            $stmt = $this->db->prepare("
                INSERT INTO courses (
                    title, description, price, categoryId, teacherId, 
                    thumbnail, media, isApproved, createdAt
                ) VALUES (
                    :title, :description, :price, :categoryId, :teacherId,
                    :thumbnail, :media, :isApproved, CURRENT_TIMESTAMP
                )
            ");

            $params = [
                ':title' => $courseData['title'],
                ':description' => $courseData['description'],
                ':price' => $courseData['price'],
                ':categoryId' => $courseData['categoryId'],
                ':teacherId' => $courseData['teacherId'],
                ':thumbnail' => $courseData['thumbnail'] ?? null,
                ':media' => $courseData['media'] ?? null,
                ':isApproved' => $courseData['isApproved']
            ];

            $stmt->execute($params);
            $courseId = $this->db->lastInsertId();

            // Insert tags if we have any
            if (!empty($tags)) {
                $tagValues = [];
                $tagParams = [];
                foreach ($tags as $i => $tag) {
                    $tagValues[] = "(:courseId{$i}, :tagId{$i})";
                    $tagParams[":courseId{$i}"] = $courseId;
                    $tagParams[":tagId{$i}"] = $tag->id;
                }

                $tagSql = "INSERT INTO course_tags (courseId, tagId) VALUES " . implode(', ', $tagValues);
                $tagStmt = $this->db->prepare($tagSql);
                $tagStmt->execute($tagParams);
            }

            $this->db->commit();
            return true;

        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log('Error in TeacherCourse::addCourse: ' . $e->getMessage());
            throw $e;
        }
    }

    public function displayCourse(int $id): ?object {
        try {
            // Teachers can only view their own courses
            $stmt = $this->db->prepare("
                SELECT c.*, 
                       cat.name as category_name,
                       COUNT(e.id) as enrollment_count,
                       CASE 
                           WHEN c.isApproved = 0 AND c.rejectedBy IS NULL THEN 'Pending Approval'
                           WHEN c.isApproved = 1 THEN 'Approved'
                           WHEN c.rejectedBy IS NOT NULL THEN 'Rejected'
                           ELSE 'Unknown'
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
                ':teacherId' => $this->user_id
            ]);

            $result = $stmt->fetch(PDO::FETCH_OBJ);
            
            if ($result) {
                // Add teacher-specific permissions
                $result->can_edit = true;
                $result->can_delete = true;
                $result->awaiting_approval = ($result->isApproved == 0 && $result->rejectedBy === null);
            }

            return $result ?: null;

        } catch (\Exception $e) {
            error_log('Error in TeacherCourse::displayCourse: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getAllCourses(): array {
        try {
            $stmt = $this->db->prepare("
                SELECT c.*, 
                       cat.name as category_name,
                       COUNT(e.id) as enrollment_count,
                       CASE 
                           WHEN c.isApproved = 0 AND c.rejectedBy IS NULL THEN 'Pending Approval'
                           WHEN c.isApproved = 1 THEN 'Approved'
                           WHEN c.rejectedBy IS NOT NULL THEN 'Rejected'
                           ELSE 'Unknown'
                       END as status_label
                FROM courses c
                LEFT JOIN categories cat ON c.categoryId = cat.id
                LEFT JOIN enrollments e ON c.id = e.courseId
                WHERE c.teacherId = :teacherId
                AND c.deleted_at IS NULL
                GROUP BY c.id
                ORDER BY c.createdAt DESC
            ");
            
            $stmt->execute([':teacherId' => $this->user_id]);
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            error_log('Error in TeacherCourse::getAllCourses: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getPendingCourses(): array {
        try {
            $stmt = $this->db->prepare("
                SELECT c.*, 
                       cat.name as category_name,
                       COUNT(e.id) as enrollment_count,
                       'Pending Approval' as status_label
                FROM courses c
                LEFT JOIN categories cat ON c.categoryId = cat.id
                LEFT JOIN enrollments e ON c.id = e.courseId
                WHERE c.teacherId = :teacherId
                AND c.isApproved = 0
                AND c.rejectedBy IS NULL
                AND c.deleted_at IS NULL
                GROUP BY c.id
                ORDER BY c.createdAt DESC
            ");
            
            $stmt->execute([':teacherId' => $this->user_id]);
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            error_log('Error in TeacherCourse::getPendingCourses: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getDeletedCourses(): array {
        try {
            $stmt = $this->db->prepare("
                SELECT c.*, 
                       cat.name as category_name,
                       COUNT(e.id) as enrollment_count,
                       'Deleted' as status_label,
                       u.firstName as deleted_by_firstname,
                       u.lastName as deleted_by_lastname
                FROM courses c
                LEFT JOIN categories cat ON c.categoryId = cat.id
                LEFT JOIN enrollments e ON c.id = e.courseId
                LEFT JOIN users u ON c.deleted_by = u.id
                WHERE c.teacherId = :teacherId
                AND c.deleted_at IS NOT NULL
                GROUP BY c.id
                ORDER BY c.deleted_at DESC
            ");
            
            $stmt->execute([':teacherId' => $this->user_id]);
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            error_log('Error in TeacherCourse::getDeletedCourses: ' . $e->getMessage());
            throw $e;
        }
    }
} 