<?php
namespace RentCar\Models;

use RentCar\Core\Database;

class Rental {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function create($rentalData) {
        $sql = "INSERT INTO rentals (user_id, car_id, start_date, end_date, total_price, status, created_at) 
                VALUES (:user_id, :car_id, :start_date, :end_date, :total_price, :status, NOW())";
        
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute([
            'user_id' => $rentalData['user_id'],
            'car_id' => $rentalData['car_id'],
            'start_date' => $rentalData['start_date'],
            'end_date' => $rentalData['end_date'],
            'total_price' => $rentalData['total_price'],
            'status' => $rentalData['status'] ?? 'pending'
        ]);

        return $this->db->lastInsertId();
    }

    public function findByUser($userId) {
        $sql = "SELECT r.*, c.brand, c.model, c.image, c.daily_price 
                FROM rentals r 
                JOIN cars c ON r.car_id = c.id 
                WHERE r.user_id = :user_id 
                ORDER BY r.created_at DESC";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function findById($id) {
        $sql = "SELECT r.*, c.brand, c.model, c.image, u.first_name, u.last_name, u.email 
                FROM rentals r 
                JOIN cars c ON r.car_id = c.id 
                JOIN users u ON r.user_id = u.id 
                WHERE r.id = :id";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function updateStatus($id, $status) {
        $sql = "UPDATE rentals SET status = :status, updated_at = NOW() WHERE id = :id";
        $stmt = $this->db->getConnection()->prepare($sql);
        return $stmt->execute(['status' => $status, 'id' => $id]);
    }

    public function cancel($id, $userId) {
        $sql = "UPDATE rentals SET status = 'cancelled', updated_at = NOW() 
                WHERE id = :id AND user_id = :user_id AND status = 'pending'";
        $stmt = $this->db->getConnection()->prepare($sql);
        return $stmt->execute(['id' => $id, 'user_id' => $userId]);
    }

    public function checkAvailability($carId, $startDate, $endDate, $excludeRentalId = null) {
        $sql = "SELECT COUNT(*) as count FROM rentals 
                WHERE car_id = :car_id 
                AND status IN ('pending', 'confirmed') 
                AND ((start_date BETWEEN :start_date AND :end_date) 
                     OR (end_date BETWEEN :start_date AND :end_date)
                     OR (start_date <= :start_date AND end_date >= :end_date))";
        
        $params = [
            'car_id' => $carId,
            'start_date' => $startDate,
            'end_date' => $endDate
        ];

        if ($excludeRentalId) {
            $sql .= " AND id != :exclude_id";
            $params['exclude_id'] = $excludeRentalId;
        }

        $result = $this->db->fetch($sql, $params);
        return $result['count'] == 0;
    }

    public function getAll() {
        $sql = "SELECT r.*, c.brand, c.model, u.first_name, u.last_name, u.email 
                FROM rentals r 
                JOIN cars c ON r.car_id = c.id 
                JOIN users u ON r.user_id = u.id 
                ORDER BY r.created_at DESC";
        return $this->db->fetchAll($sql);
    }
}