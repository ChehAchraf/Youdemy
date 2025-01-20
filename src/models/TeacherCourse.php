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

           
            $newTags = array_diff($tagNames, $existingTagNames);

            if (!empty($newTags)) {
                
                $insertPlaceholders = str_repeat('(?),', count($newTags) - 1) . '(?)';
                $insertStmt = $this->db->prepare("
                    INSERT INTO tags (name) 
                    VALUES $insertPlaceholders
                ");

                // Execute insert for new tags
                $insertValues = array_values($newTags);
                $insertStmt->execute($insertValues);

                
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
            
            $this->validateCourseData($courseData);

            
            $this->db->beginTransaction();

            
            $tags = [];
            if (!empty($courseData['tags'])) {
                
                $tagNames = array_map('trim', explode(',', $courseData['tags']));
                
                $tagNames = array_filter($tagNames);
                
                if (!empty($tagNames)) {
                    $tags = $this->addTags($tagNames);
                }
            }

            
            $courseData['teacherId'] = $this->user_id;
            $courseData['isApproved'] = 0;

            
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

    public function updateCourse(array $courseData): bool {
        try {
            if (!isset($courseData['id'])) {
                throw new \Exception('Course ID is required');
            }

            $checkStmt = $this->db->prepare("
                SELECT id FROM courses 
                WHERE id = :id 
                AND teacherId = :teacherId
                AND deleted_at IS NULL
            ");
            $checkStmt->execute([
                ':id' => $courseData['id'],
                ':teacherId' => $this->user_id
            ]);

            if (!$checkStmt->fetch()) {
                throw new \Exception('Course not found or access denied');
            }

            $updateFields = [];
            $params = [':id' => $courseData['id']];

            if (isset($courseData['title'])) {
                $updateFields[] = 'title = :title';
                $params[':title'] = $courseData['title'];
            }
            if (isset($courseData['description'])) {
                $updateFields[] = 'description = :description';
                $params[':description'] = $courseData['description'];
            }
            if (isset($courseData['price'])) {
                $updateFields[] = 'price = :price';
                $params[':price'] = $courseData['price'];
            }
            if (isset($courseData['categoryId'])) {
                $updateFields[] = 'categoryId = :categoryId';
                $params[':categoryId'] = $courseData['categoryId'];
            }

            // Add optional fields
            if (isset($courseData['thumbnail'])) {
                $updateFields[] = 'thumbnail = :thumbnail';
                $params[':thumbnail'] = $courseData['thumbnail'];
            }
            if (isset($courseData['media'])) {
                $updateFields[] = 'media = :media';
                $params[':media'] = $courseData['media'];
            }

            if (empty($updateFields)) {
                throw new \Exception('No fields to update');
            }

            $sql = "UPDATE courses SET " . implode(', ', $updateFields) . " WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($params);

            if (!$result) {
                throw new \Exception('Failed to update course');
            }

            return true;

        } catch (\Exception $e) {
            error_log('Error in TeacherCourse::updateCourse: ' . $e->getMessage());
            throw $e;
        }
    }
} 