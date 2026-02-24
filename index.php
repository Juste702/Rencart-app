<?php
require_once __DIR__ . '/../vendor/autoload.php';

use RentCar\Core\Router;
use RentCar\Core\Database;
use RentCar\Core\JWT;
use RentCar\Controllers\AuthController;
use RentCar\Controllers\RentalController;
use RentCar\Middleware\AuthMiddleware;
use RentCar\Middleware\AdminMiddleware;

// Headers CORS pour le développement
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");

// Gérer les requêtes OPTIONS pour CORS
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Charger la configuration
    $config = require __DIR__ . '/../config/database.php';
    
    // Initialiser la base de données
    Database::init($config);
    
    // Initialiser JWT
    $env = parse_ini_file(__DIR__ . '/../.env');
    JWT::init($env['JWT_SECRET'] ?? 'fallback-secret-key');
    
    // Créer le routeur
    $router = new Router();
    
    // ==================== ROUTES PUBLIQUES ====================
    
    // Route de santé
    $router->get('/', function() {
        echo json_encode([
            'message' => '🚗 DriveLuxia API v1.0', 
            'status' => 'active',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    });
    
    $router->get('/api/health', function() {
        echo json_encode(['status' => 'OK', 'timestamp' => date('Y-m-d H:i:s')]);
    });
    
    // Authentification
    $router->post('/api/register', [AuthController::class, 'register']);
    $router->post('/api/login', [AuthController::class, 'login']);
    
    // ==================== ROUTES PROTÉGÉES ====================
    
    // Middleware d'authentification générale
    $authMiddleware = new AuthMiddleware();
    $router->addMiddleware($authMiddleware);
    
    // Profil utilisateur
    $router->get('/api/profile', [AuthController::class, 'getProfile']);
    $router->put('/api/profile', [AuthController::class, 'updateProfile']);
    
    // Réservations utilisateur
    $router->post('/api/rentals', [RentalController::class, 'create']);
    $router->get('/api/rentals', [RentalController::class, 'getUserRentals']);
    $router->delete('/api/rentals/(\d+)', [RentalController::class, 'cancel']);
    
    // ==================== ROUTES ADMIN ====================
    
    // Middleware admin
    $adminMiddleware = new AdminMiddleware();
    
    // Routes admin pour les réservations
    $router->get('/api/admin/rentals', [RentalController::class, 'getAllRentals'], [$adminMiddleware]);
    $router->put('/api/admin/rentals/(\d+)/status', [RentalController::class, 'updateRentalStatus'], [$adminMiddleware]);
    
    // Démarrer le routeur
    $router->dispatch();
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Internal Server Error',
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}