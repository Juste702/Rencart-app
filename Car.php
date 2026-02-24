<?php
namespace RentCar\Models;

use RentCar\Core\Database;

class Car {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findById($id) {
        $sql = "SELECT * FROM cars WHERE id = :id AND status = 'available'";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function getAllAvailable() {
        $sql = "SELECT * FROM cars WHERE status = 'available' ORDER BY created_at DESC";
        return $this->db->fetchAll($sql);
    }
}