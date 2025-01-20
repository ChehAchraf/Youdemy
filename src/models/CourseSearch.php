<?php

namespace App\Models;

use PDO;

class CourseSearch {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function searchCourses($query = '', $categoryId = null, $tags = [], $page = 1, $itemsPerPage = 9) {
        try {
            $offset = ($page - 1) * $itemsPerPage;
            $params = [];
            $conditions = ['c.isApproved = 1', 'c.deleted_at IS NULL'];

            if (!empty($query)) {
                $conditions[] = '(c.title LIKE :query OR c.description LIKE :query)';
                $params[':query'] = '%' . $query . '%';
            }

            if (!empty($categoryId)) {
                $conditions[] = 'c.categoryId = :categoryId';
                $params[':categoryId'] = $categoryId;
            }

            if (!empty($tags)) {
                $tagPlaceholders = [];
                foreach ($tags as $index => $tag) {
                    $tagKey = ":tag{$index}";
                    $tagPlaceholders[] = $tagKey;
                    $params[$tagKey] = $tag;
                }
                $conditions[] = 'EXISTS (
                    SELECT 1 FROM course_tags ct 
                    WHERE ct.courseId = c.id 
                    AND ct.tag IN (' . implode(',', $tagPlaceholders) . ')
                )';
            }

            $whereClause = implode(' AND ', $conditions);

            $sql = "
                SELECT DISTINCT c.*, 
                       cat.name as category_name,
                       CONCAT(u.firstName, ' ', u.lastName) as teacher_name,
                       COUNT(DISTINCT e.id) as enrollment_count,
                       COALESCE(AVG(r.rating), 0) as rating,
                       COUNT(DISTINCT r.id) as rating_count
                FROM courses c
                LEFT JOIN categories cat ON c.categoryId = cat.id
                LEFT JOIN users u ON c.teacherId = u.id
                LEFT JOIN enrollments e ON c.id = e.courseId
                LEFT JOIN ratings r ON c.id = r.courseId
                WHERE {$whereClause}
                GROUP BY c.id
                ORDER BY c.createdAt DESC
                LIMIT :limit OFFSET :offset
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', $itemsPerPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ);

        } catch (\Exception $e) {
            error_log('Error in CourseSearch::searchCourses: ' . $e->getMessage());
            return [];
        }
    }

    public function getTotalSearchResults($query = '', $categoryId = null, $tags = []) {
        try {
            $params = [];
            $conditions = ['c.isApproved = 1', 'c.deleted_at IS NULL'];

            if (!empty($query)) {
                $conditions[] = '(c.title LIKE :query OR c.description LIKE :query)';
                $params[':query'] = '%' . $query . '%';
            }

            if (!empty($categoryId)) {
                $conditions[] = 'c.categoryId = :categoryId';
                $params[':categoryId'] = $categoryId;
            }

            if (!empty($tags)) {
                $tagPlaceholders = [];
                foreach ($tags as $index => $tag) {
                    $tagKey = ":tag{$index}";
                    $tagPlaceholders[] = $tagKey;
                    $params[$tagKey] = $tag;
                }
                $conditions[] = 'EXISTS (
                    SELECT 1 FROM course_tags ct 
                    WHERE ct.courseId = c.id 
                    AND ct.tag IN (' . implode(',', $tagPlaceholders) . ')
                )';
            }

            $whereClause = implode(' AND ', $conditions);

            $sql = "
                SELECT COUNT(DISTINCT c.id) as total
                FROM courses c
                LEFT JOIN course_tags ct ON c.id = ct.courseId
                WHERE {$whereClause}
            ";

            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_OBJ)->total;

        } catch (\Exception $e) {
            error_log('Error in CourseSearch::getTotalSearchResults: ' . $e->getMessage());
            return 0;
        }
    }

    public function getAllTags() {
        try {
            $stmt = $this->db->query("
                SELECT DISTINCT tag 
                FROM course_tags 
                ORDER BY tag
            ");
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (\Exception $e) {
            error_log('Error in CourseSearch::getAllTags: ' . $e->getMessage());
            return [];
        }
    }
} 