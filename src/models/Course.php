<?php
namespace App\Models;



use App\Interfaces\ICourse;
use PDO;

class Course implements ICourse {
    protected $db;
    protected $id;
    protected $title;
    protected $description;
    protected $teacherId;
    protected $tags = [];

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function add() {
        try {
            $this->db->beginTransaction();


            $stmt = $this->db->prepare("INSERT INTO courses (title, description, teacherId) VALUES (?, ?, ?)");
            $stmt->execute([$this->title, $this->description, $this->teacherId]);
            $courseId = $this->db->lastInsertId();


            if (!empty($this->tags)) {
                $tagStmt = $this->db->prepare("INSERT INTO course_tags (courseId, tagId) VALUES (?, ?)");
                foreach ($this->tags as $tagId) {
                    $tagStmt->execute([$courseId, $tagId]);
                }
            }

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function display() {
        $stmt = $this->db->prepare("
            SELECT c.*, GROUP_CONCAT(t.name) as tags 
            FROM courses c 
            LEFT JOIN course_tags ct ON c.id = ct.courseId 
            LEFT JOIN tags t ON ct.tagId = t.id 
            WHERE c.id = ?
            GROUP BY c.id
        ");
        $stmt->execute([$this->id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function setTags(array $tags) {
        $this->tags = $tags;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setTitle($title) {
        $this->title = $title;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function setTeacherId($teacherId) {
        $this->teacherId = $teacherId;
    }
} 