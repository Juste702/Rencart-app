<?php
namespace RentCar\Middleware;

use RentCar\Core\JWT;

class AdminMiddleware {
    public function handle() {
        $token = JWT::getTokenFromHeader();

        if (!$token) {
            http_response_code(401);
            echo json_encode(['error' => 'Authorization token required']);
            return false;
        }

        $userData = JWT::decode($token);

        if (!$userData || ($userData['role'] ?? 'customer') !== 'admin') {
            http_response_code(403);
            echo json_encode(['error' => 'Admin access required']);
            return false;
        }

        $GLOBALS['user'] = $userData;
        return true;
    }
}