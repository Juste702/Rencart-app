<?php
namespace RentCar\Middleware;

use RentCar\Core\JWT;

class AuthMiddleware {
    public function handle() {
        $token = JWT::getTokenFromHeader();

        if (!$token) {
            http_response_code(401);
            echo json_encode(['error' => 'Authorization token required']);
            return false;
        }

        $userData = JWT::decode($token);

        if (!$userData) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid or expired token']);
            return false;
        }

        // Stocker les données utilisateur globalement
        $GLOBALS['user'] = $userData;
        return true;
    }
}