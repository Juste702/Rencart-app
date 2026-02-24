<?php
namespace RentCar\Controllers;

use RentCar\Models\Rental;
use RentCar\Models\Car;

class RentalController {
    private $rentalModel;
    private $carModel;

    public function __construct() {
        $this->rentalModel = new Rental();
        $this->carModel = new Car();
    }

    public function create() {
        $user = $GLOBALS['user'];
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON data']);
            return;
        }

        $required = ['car_id', 'start_date', 'end_date'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                http_response_code(400);
                echo json_encode(['error' => "Field $field is required"]);
                return;
            }
        }

        // Validation des dates
        try {
            $start = new \DateTime($data['start_date']);
            $end = new \DateTime($data['end_date']);
            $today = new \DateTime();
            
            if ($start < $today) {
                http_response_code(400);
                echo json_encode(['error' => 'Start date cannot be in the past']);
                return;
            }
            
            if ($end <= $start) {
                http_response_code(400);
                echo json_encode(['error' => 'End date must be after start date']);
                return;
            }
        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid date format']);
            return;
        }

        // Vérifier la disponibilité
        if (!$this->rentalModel->checkAvailability($data['car_id'], $data['start_date'], $data['end_date'])) {
            http_response_code(409);
            echo json_encode(['error' => 'Car not available for selected dates']);
            return;
        }

        // Calculer le prix total
        $car = $this->carModel->findById($data['car_id']);
        if (!$car) {
            http_response_code(404);
            echo json_encode(['error' => 'Car not found']);
            return;
        }

        $days = $end->diff($start)->days;
        $totalPrice = $days * $car['daily_price'];

        // Créer la réservation
        $rentalData = [
            'user_id' => $user['user_id'],
            'car_id' => $data['car_id'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'total_price' => $totalPrice,
            'status' => 'pending'
        ];

        try {
            $rentalId = $this->rentalModel->create($rentalData);

            http_response_code(201);
            echo json_encode([
                'message' => 'Rental created successfully',
                'rental_id' => $rentalId,
                'total_price' => $totalPrice,
                'days' => $days,
                'car' => [
                    'brand' => $car['brand'],
                    'model' => $car['model'],
                    'daily_price' => $car['daily_price']
                ]
            ]);

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create rental: ' . $e->getMessage()]);
        }
    }

    public function getUserRentals() {
        $user = $GLOBALS['user'];
        $rentals = $this->rentalModel->findByUser($user['user_id']);

        echo json_encode(['rentals' => $rentals]);
    }

    public function cancel($id) {
        $user = $GLOBALS['user'];
        
        $success = $this->rentalModel->cancel($id, $user['user_id']);
        
        if ($success) {
            echo json_encode(['message' => 'Rental cancelled successfully']);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Cannot cancel rental. It may not exist or already be processed.']);
        }
    }

    // Méthodes admin
    public function getAllRentals() {
        $rentals = $this->rentalModel->getAll();
        echo json_encode(['rentals' => $rentals]);
    }

    public function updateRentalStatus($id) {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON data']);
            return;
        }

        $allowedStatuses = ['confirmed', 'cancelled', 'completed'];
        if (empty($data['status']) || !in_array($data['status'], $allowedStatuses)) {
            http_response_code(400);
            echo json_encode(['error' => 'Valid status required: ' . implode(', ', $allowedStatuses)]);
            return;
        }

        $rental = $this->rentalModel->findById($id);
        if (!$rental) {
            http_response_code(404);
            echo json_encode(['error' => 'Rental not found']);
            return;
        }

        $success = $this->rentalModel->updateStatus($id, $data['status']);

        if ($success) {
            echo json_encode(['message' => 'Rental status updated successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update rental status']);
        }
    }
}