<?php
require_once __DIR__ . '/../vendor/autoload.php';

use RentCar\Core\Router;
use RentCar\Core\Database;
use RentCar\Core\JWT;
use RentCar\Middleware\AuthMiddleware;

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
    
    // ==================== ROUTES DE TEST ====================
    
    // Route principale
    $router->get('/', function() {
        echo json_encode([
            'message' => '🚗 RentCar API v1.0', 
            'status' => 'active',
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => '1.0.0'
        ]);
    });
    
    // Route santé API
    $router->get('/api/health', function() {
        try {
            $db = RentCar\Core\Database::getInstance()->getConnection();
            $db->query('SELECT 1');
            $dbStatus = 'connected';
        } catch (Exception $e) {
            $dbStatus = 'disconnected';
        }
        
        echo json_encode([
            'status' => 'OK', 
            'timestamp' => date('Y-m-d H:i:s'),
            'database' => $dbStatus,
            'environment' => $_ENV['APP_ENV'] ?? 'development'
        ]);
    });
    
    // Route test JWT
    $router->post('/api/test-auth', function() {
        $data = json_decode(file_get_contents('php://input'), true);
        $token = $data['token'] ?? '';
        
        if (empty($token)) {
            http_response_code(400);
            echo json_encode(['error' => 'Token is required']);
            return;
        }
        
        $userData = JWT::decode($token);
        
        if ($userData) {
            echo json_encode([
                'valid' => true,
                'user' => $userData
            ]);
        } else {
            http_response_code(401);
            echo json_encode(['valid' => false, 'error' => 'Invalid token']);
        }
    });
    
    // Route test base de données
    $router->get('/api/test-db', function() {
        try {
            $db = RentCar\Core\Database::getInstance()->getConnection();
            $stmt = $db->query('SELECT VERSION() as version');
            $result = $stmt->fetch();
            
            echo json_encode([
                'database' => 'connected',
                'mysql_version' => $result['version'],
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'database' => 'disconnected',
                'error' => $e->getMessage()
            ]);
        }
    });
    
    // ==================== ROUTES PROTÉGÉES (TEST) ====================
    
    $authMiddleware = new AuthMiddleware();
    
    // Route protégée de test
    $router->get('/api/protected', function() {
        $user = $GLOBALS['user'];
        echo json_encode([
            'message' => 'Access granted to protected route',
            'user' => $user,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }, [$authMiddleware]);
    
    // Route admin de test
    $router->get('/api/admin/test', function() {
        $user = $GLOBALS['user'];
        echo json_encode([
            'message' => 'Admin access granted',
            'admin_user' => $user,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }, [new \RentCar\Middleware\AdminMiddleware()]);
    
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
