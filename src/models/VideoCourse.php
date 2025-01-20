<?php
namespace App\Models;

class VideoCourse extends Course {
    private $videoUrl;

    public function add() {
        try {
            $this->db->beginTransaction();
            
            
            $stmt = $this->db->prepare("
                INSERT INTO courses (title, description, teacherId, type, videoUrl) 
                VALUES (?, ?, ?, 'video', ?)
            ");
            $stmt->execute([$this->title, $this->description, $this->teacherId, $this->videoUrl]);
            
            
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function display() {
        $data = parent::display();
        
        return array_merge($data, ['type' => 'video', 'videoUrl' => $this->videoUrl]);
    }
} 