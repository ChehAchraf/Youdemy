<?php
class MediaUploader {
    private $uploadDir;
    private $allowedTypes;
    private $maxSize;
    private $db;

    public function __construct($db) {
        $this->db = $db;
        $this->uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/';
        $this->allowedTypes = [
            'video' => ['video/mp4', 'video/webm'],
            'image' => ['image/jpeg', 'image/png', 'image/gif'],
            'document' => ['application/pdf', 'application/msword']
        ];
        $this->maxSize = 500 * 1024 * 1024; // 500MB max
    }

    public function uploadFile($file, $type) {
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            throw new Exception('No file uploaded');
        }

        
        if (!in_array($file['type'], $this->allowedTypes[$type])) {
            throw new Exception('Invalid file type');
        }

        
        if ($file['size'] > $this->maxSize) {
            throw new Exception('File too large');
        }

        
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $extension;
        $filepath = $this->uploadDir . $type . 's/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            throw new Exception('Failed to move uploaded file');
        }

        
        $stmt = $this->db->prepare("INSERT INTO media_files (fileName, fileType, filePath, fileSize) VALUES (?, ?, ?, ?)");
        $stmt->execute([$filename, $type, $filepath, $file['size']]);

        return $this->db->lastInsertId();
    }
} 