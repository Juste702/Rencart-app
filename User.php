<?php
namespace RentCar\Models;

use RentCar\Core\Database;

class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function create($userData) {
        $sql = "INSERT INTO users (email, password, first_name, last_name, phone, role, created_at) 
                VALUES (:email, :password, :first_name, :last_name, :phone, :role, NOW())";
        
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute([
            'email' => $userData['email'],
            'password' => password_hash($userData['password'], PASSWORD_DEFAULT),
            'first_name' => $userData['first_name'],
            'last_name' => $userData['last_name'],
            'phone' => $userData['phone'],
            'role' => $userData['role'] ?? 'customer'
        ]);

        return $this->db->lastInsertId();
    }

    public function findByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = :email";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute(['email' => $email]);
        return $stmt->fetch();
    }

    public function findById($id) {
        $sql = "SELECT id, email, first_name, last_name, phone, role, created_at 
                FROM users WHERE id = :id";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function updateProfile($id, $data) {
        $sql = "UPDATE users SET first_name = :first_name, last_name = :last_name, 
                phone = :phone, updated_at = NOW() WHERE id = :id";
        $stmt = $this->db->getConnection()->prepare($sql);
        return $stmt->execute([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'phone' => $data['phone'],
            'id' => $id
        ]);
    }
}