<?php
namespace RentCar\Controllers;

use RentCar\Models\User;
use RentCar\Core\JWT;

class AuthController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function register() {
        $data = json_decode(file_get_contents('php://input'), true);

        // Validation
        if (!$data) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON data']);
            return;
        }

        $required = ['email', 'password', 'first_name', 'last_name', 'phone'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                http_response_code(400);
                echo json_encode(['error' => "Field $field is required"]);
                return;
            }
        }

        // Validation email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid email format']);
            return;
        }

        // Vérifier si l'email existe déjà
        if ($this->userModel->findByEmail($data['email'])) {
            http_response_code(409);
            echo json_encode(['error' => 'Email already exists']);
            return;
        }

        try {
            // Créer l'utilisateur
            $userId = $this->userModel->create($data);
            
            // Générer le token JWT
            $token = JWT::encode([
                'user_id' => $userId,
                'email' => $data['email'],
                'role' => 'customer'
            ]);

            http_response_code(201);
            echo json_encode([
                'message' => 'User created successfully',
                'token' => $token,
                'user' => [
                    'id' => $userId,
                    'email' => $data['email'],
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'role' => 'customer'
                ]
            ]);

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Registration failed: ' . $e->getMessage()]);
        }
    }

    public function login() {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON data']);
            return;
        }

        if (empty($data['email']) || empty($data['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Email and password are required']);
            return;
        }

        $user = $this->userModel->findByEmail($data['email']);

        if (!$user || !password_verify($data['password'], $user['password'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid credentials']);
            return;
        }

        // Générer le token JWT
        $token = JWT::encode([
            'user_id' => $user['id'],
            'email' => $user['email'],
            'role' => $user['role']
        ]);

        echo json_encode([
            'message' => 'Login successful',
            'token' => $token,
            'user' => [
                'id' => $user['id'],
                'email' => $user['email'],
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'role' => $user['role']
            ]
        ]);
    }

    public function getProfile() {
        $user = $GLOBALS['user'];
        $userData = $this->userModel->findById($user['user_id']);

        if (!$userData) {
            http_response_code(404);
            echo json_encode(['error' => 'User not found']);
            return;
        }

        echo json_encode($userData);
    }

    public function updateProfile() {
        $user = $GLOBALS['user'];
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON data']);
            return;
        }

        $allowedFields = ['first_name', 'last_name', 'phone'];
        $updateData = array_intersect_key($data, array_flip($allowedFields));

        if (empty($updateData)) {
            http_response_code(400);
            echo json_encode(['error' => 'No valid fields to update']);
            return;
        }

        $success = $this->userModel->updateProfile($user['user_id'], $updateData);

        if ($success) {
            echo json_encode(['message' => 'Profile updated successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update profile']);
        }
    }
}