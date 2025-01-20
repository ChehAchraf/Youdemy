<?php
namespace App\Models;
use App\Models\Database;
use App\Models\Session;

class User {
    private $id;
    private $firstname;
    private $lastname;
    private $email;
    private $password;
    private $role;
    private $is_active;
    private $specialization;
    private $verification_status;
    private $hashed_password;

    public function __construct($id, $firstname, $lastname, $email, $password, $role, $is_active) {
        $this->id = $id;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->email = $email;
        $this->password = $password;
        $this->role = $role;
        $this->is_active = $is_active;
        $this->hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $this->verification_status = 'approved'; 
    }

    public function setSpecialization($specialization) {
        $this->specialization = $specialization;
    }

    public function setVerificationStatus($status) {
        $this->verification_status = $status;
    }

    public function register() {
        $db = Database::getInstance();
        $conn = $db->getConnection();

        try {
            
            $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $checkStmt->execute([$this->email]);
            if ($checkStmt->fetch()) {
                throw new \Exception('Email already exists');
            }

            $stmt = $conn->prepare("
                INSERT INTO users (
                    firstname, 
                    lastname, 
                    email, 
                    password, 
                    role, 
                    isActive,
                    specialization,
                    verification_status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");

            return $stmt->execute([
                $this->firstname,
                $this->lastname,
                $this->email,
                $this->hashed_password,
                $this->role,
                $this->is_active,
                $this->specialization,
                $this->verification_status
            ]);
        } catch (\Exception $e) {
            throw new \Exception('Registration failed: ' . $e->getMessage());
        }
    }

    public function login() {
        $db = Database::getInstance();
        $conn = $db->getConnection();

        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$this->email]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($user && password_verify($this->password, $user['password'])) {
            if ($user['role'] === 'teacher' && $user['verification_status'] !== 'approved') {
                throw new \Exception('Your teacher account is pending approval');
            }

            Session::set('user_id', $user['id']);
            Session::set('firstname', $user['firstName']);
            Session::set('lastname', $user['lastName']);
            Session::set('role', $user['role']);
            Session::set('is_active', $user['isActive']);
            return true;
        }
        return false;
    }
}