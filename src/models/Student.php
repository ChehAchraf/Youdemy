<?php
namespace App\Models;
use App\Models\User;
use App\Models\Session;
use App\Models\Database;
use PDO;

class Student extends User {
    protected $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        if (Session::get('role') !== 'student') {
            throw new \Exception('Unauthorized: Student access required');
        }
    }

    protected function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }
} 