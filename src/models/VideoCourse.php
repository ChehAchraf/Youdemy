<?php
namespace App\Models;

class VideoCourse extends Course {
    private $videoUrl;

    public function add() {
        try {
            $this->db->beginTransaction();
            
            // Insert course
            $stmt = $this->db->prepare("
                INSERT INTO courses (title, description, teacherId, type, videoUrl) 
                VALUES (?, ?, ?, 'video', ?)
            ");
            $stmt->execute([$this->title, $this->description, $this->teacherId, $this->videoUrl]);
            
            // Rest of the implementation similar to parent
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function display() {
        $data = parent::display();
        // Add video-specific display logic
        return array_merge($data, ['type' => 'video', 'videoUrl' => $this->videoUrl]);
    }
} 