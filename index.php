<?php
session_start();
date_default_timezone_set('Africa/Porto-Novo');

// Configuration DriveLuxia
define('APP_NAME', 'DriveLuxia');
define('APP_SLOGAN', 'Location & Vente de Voitures Premium');
define('CREATIVE_CODE', 'Creative Code');
define('EUR_TO_FCFA', 655);

// Connexion MySQL
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'driveluxia';

try {
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname");
    $pdo->exec("USE $dbname");
    
    // Création des tables
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        first_name VARCHAR(50) NOT NULL,
        last_name VARCHAR(50) NOT NULL,
        phone VARCHAR(20),
        role ENUM('customer','admin') DEFAULT 'customer',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS cars (
        id INT AUTO_INCREMENT PRIMARY KEY,
        brand VARCHAR(50) NOT NULL,
        model VARCHAR(50) NOT NULL,
        category VARCHAR(50) NOT NULL,
        year INT,
        price_range VARCHAR(100) NOT NULL,
        price_min INT,
        price_max INT,
        fuel_type VARCHAR(30),
        transmission VARCHAR(30),
        seats INT,
        description TEXT,
        features TEXT,
        image_url VARCHAR(500),
        is_available BOOLEAN DEFAULT TRUE
    )");
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS bookings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        car_id INT NOT NULL,
        booking_type VARCHAR(50),
        event_type VARCHAR(100),
        start_date DATE NOT NULL,
        end_date DATE NOT NULL,
        status ENUM('pending','confirmed','completed','cancelled') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE
    )");
    
    // Admin par défaut
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = 'admin@driveluxia.com'");
    $stmt->execute();
    if (!$stmt->fetch()) {
        $pdo->prepare("INSERT INTO users (email, password, first_name, last_name, role) 
                      VALUES (?, ?, ?, ?, ?)")
            ->execute(['admin@driveluxia.com', password_hash('admin123', PASSWORD_DEFAULT), 'Admin', 'DriveLuxia', 'admin']);
    }
    
    // Les 10 voitures DriveLuxia
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM cars");
    if ($stmt->fetch()['count'] == 0) {
        $cars = [
            ['Mercedes-Benz', 'C-Class', 'Berline luxe', 2020, '7 500 000 – 20 000 000', 7500000, 20000000, 'Diesel/Essence', 'Automatique', 5, 'L\'élégance allemande', 'Intérieur cuir, GPS, Toit ouvrant', 'https://images.unsplash.com/photo-1563720223488-8f2f62a6e71a?w=800'],
            ['Honda', 'Accord', 'Berline', 2019, '4 000 000 – 11 000 000', 4000000, 11000000, 'Essence', 'Automatique', 5, 'Berline japonaise fiable', 'Climatisation, Bluetooth', 'https://images.unsplash.com/photo-1555215695-3004980ad54e?w=800'],
            ['Hyundai', 'Tucson', 'SUV', 2021, '4 500 000 – 10 500 000', 4500000, 10500000, 'Diesel', 'Automatique', 5, 'SUV spacieux', 'Toit panoramique, Caméra', 'https://images.unsplash.com/photo-1549399542-7e3f8b79c341?w=800'],
            ['Range Rover', 'Evoque', 'SUV haut de gamme', 2022, '12 000 000 – 35 000 000', 12000000, 35000000, 'Diesel', 'Automatique', 5, 'SUV britannique de luxe', 'Cuir, Toit panoramique', 'https://images.unsplash.com/photo-1566473356679-6a5c8c081f5d?w=800'],
            ['Honda', 'Civic', 'Compacte', 2020, '2 500 000 – 5 000 000', 2500000, 5000000, 'Essence', 'Manuelle', 5, 'Compacte dynamique', 'Écran tactile, Caméra', 'https://images.unsplash.com/photo-1580273916550-e323be2ae537?w=800'],
            ['Nissan', 'Altima', 'Berline', 2019, '5 000 000', 5000000, 5000000, 'Essence', 'CVT', 5, 'Berline américaine', 'Toit ouvrant, Navigation', 'https://images.unsplash.com/photo-1593941707882-a5bba5338fe2?w=800'],
            ['Kia', 'Rio', 'Citadine', 2021, '7 000 000 – 16 000 000', 7000000, 16000000, 'Essence', 'Manuelle', 5, 'Citadine moderne', 'Apple CarPlay, Android Auto', 'https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?w=800'],
            ['Peugeot', '2008', 'SUV compact', 2020, '13 500 000', 13500000, 13500000, 'Diesel', 'Automatique', 5, 'SUV français', 'i-Cockpit, Grip Control', 'https://images.unsplash.com/photo-1553440569-bcc63803a83d?w=800'],
            ['Mazda', 'CX-5', 'SUV', 2021, '7 600 000 – 8 800 000', 7600000, 8800000, 'Essence', 'Automatique', 5, 'SUV japonais racé', 'Cuir Nappa, Bose', 'https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?w=800'],
            ['Nissan', 'Almera', 'Berline compacte', 2018, '1 900 000', 1900000, 1900000, 'Essence', 'Manuelle', 5, 'Berline économique', 'Radio Bluetooth, ABS', 'https://images.unsplash.com/photo-1555215695-3004980ad54e?w=800']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO cars (brand, model, category, year, price_range, price_min, price_max, fuel_type, transmission, seats, description, features, image_url) 
                              VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
        foreach ($cars as $car) {
            $stmt->execute($car);
        }
    }
    
} catch (PDOException $e) {
    die("Erreur: " . $e->getMessage());
}

// ============================================
// FONCTIONS
// ============================================

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function formatPrice($amount) {
    return number_format($amount, 0, ',', ' ') . ' FCFA';
}

function showMessage($message, $type = 'success') {
    $_SESSION['message'] = ['text' => $message, 'type' => $type];
}

function getMessage() {
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        unset($_SESSION['message']);
        return $message;
    }
    return null;
}

// ============================================
// TRAITEMENT DES FORMULAIRES
// ============================================

$message = getMessage();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // ===== INSCRIPTION =====
    if (isset($_POST['register'])) {
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $phone = trim($_POST['phone'] ?? '');
        
        // Validation
        $errors = [];
        
        if (empty($first_name)) $errors[] = "Le prénom est requis";
        if (empty($last_name)) $errors[] = "Le nom est requis";
        if (empty($email)) $errors[] = "L'email est requis";
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email invalide";
        if (empty($password)) $errors[] = "Le mot de passe est requis";
        if (strlen($password) < 6) $errors[] = "Le mot de passe doit faire au moins 6 caractères";
        
        // Vérifier si l'email existe déjà
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            $errors[] = "Cet email est déjà utilisé";
        }
        
        if (empty($errors)) {
            // Créer le compte
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (email, password, first_name, last_name, phone) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$email, $hashed, $first_name, $last_name, $phone]);
            
            // Connecter automatiquement
            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['user_name'] = $first_name . ' ' . $last_name;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_role'] = 'customer';
            
            showMessage("Inscription réussie ! Bienvenue " . $first_name, 'success');
            header('Location: ?page=home');
            exit;
        } else {
            showMessage(implode("<br>", $errors), 'error');
        }
    }
    
    // ===== CONNEXION =====
    if (isset($_POST['login'])) {
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            
            showMessage("Connexion réussie ! Bon retour " . $user['first_name'], 'success');
            header('Location: ?page=home');
            exit;
        } else {
            showMessage("Email ou mot de passe incorrect", 'error');
        }
    }
    
    // ===== DÉCONNEXION =====
    if (isset($_POST['logout'])) {
        session_destroy();
        showMessage("Vous avez été déconnecté", 'info');
        header('Location: ?page=home');
        exit;
    }
    
    // ===== RÉSERVATION =====
    if (isset($_POST['booking'])) {
        if (!isLoggedIn()) {
            showMessage("Veuillez vous connecter pour réserver", 'error');
            header('Location: ?page=login');
            exit;
        }
        
        $car_id = $_POST['car_id'];
        $booking_type = $_POST['booking_type'];
        $event_type = $_POST['event_type'] ?? null;
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        
        // Validation
        $errors = [];
        if (empty($booking_type)) $errors[] = "Type de réservation requis";
        if (empty($start_date)) $errors[] = "Date de début requise";
        if (empty($end_date)) $errors[] = "Date de fin requise";
        
        if (empty($errors)) {
            $stmt = $pdo->prepare("INSERT INTO bookings (user_id, car_id, booking_type, event_type, start_date, end_date) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $car_id, $booking_type, $event_type, $start_date, $end_date]);
            
            showMessage("Demande de réservation envoyée ! Nous vous contacterons sous 24h.", 'success');
            header('Location: ?page=bookings');
            exit;
        } else {
            showMessage(implode("<br>", $errors), 'error');
        }
    }
}

// ============================================
// ROUTAGE
// ============================================

$page = $_GET['page'] ?? 'home';
$id = $_GET['id'] ?? 0;

// Récupérer les données
$cars = $pdo->query("SELECT * FROM cars ORDER BY price_min ASC")->fetchAll();

if ($id) {
    $car = $pdo->prepare("SELECT * FROM cars WHERE id = ?");
    $car->execute([$id]);
    $car = $car->fetch();
}

if (isLoggedIn()) {
    $bookings = $pdo->prepare("SELECT b.*, c.brand, c.model, c.image_url 
                               FROM bookings b 
                               JOIN cars c ON b.car_id = c.id 
                               WHERE b.user_id = ? 
                               ORDER BY b.created_at DESC");
    $bookings->execute([$_SESSION['user_id']]);
    $bookings = $bookings->fetchAll();
}

// Statistiques admin
if (isAdmin()) {
    $total_users = $pdo->query("SELECT COUNT(*) as count FROM users")->fetch()['count'];
    $total_cars = $pdo->query("SELECT COUNT(*) as count FROM cars")->fetch()['count'];
    $total_bookings = $pdo->query("SELECT COUNT(*) as count FROM bookings")->fetch()['count'];
    $recent_bookings = $pdo->query("SELECT b.*, c.brand, c.model, u.first_name, u.last_name 
                                    FROM bookings b 
                                    JOIN cars c ON b.car_id = c.id 
                                    JOIN users u ON b.user_id = u.id 
                                    ORDER BY b.created_at DESC LIMIT 5")->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DriveLuxia - Location de Voitures Premium</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-gray-50">
    
    <!-- Navigation -->
    <nav class="bg-white shadow-lg sticky top-0 z-50">
        <div class="container mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <!-- Logo avec voiture -->
                <a href="?page=home" class="flex items-center space-x-3">
                    <div class="bg-primary p-3 rounded-xl">
                        <i class="fas fa-car text-secondary text-2xl"></i>
                    </div>
                    <span class="text-2xl font-bold text-primary">Drive<span class="text-secondary">Luxia</span></span>
                </a>
                
                <!-- Menu Desktop -->
                <div class="hidden md:flex space-x-8">
                    <a href="?page=home" class="nav-link">Accueil</a>
                    <a href="?page=cars" class="nav-link">Nos Véhicules</a>
                    <a href="#services" class="nav-link">Services</a>
                    <a href="#contact" class="nav-link">Contact</a>
                    <?php if (isLoggedIn()): ?>
                        <a href="?page=bookings" class="nav-link">Mes Réservations</a>
                        <?php if (isAdmin()): ?>
                            <a href="?page=dashboard" class="nav-link">Dashboard</a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                
                <!-- Actions -->
                <div class="flex items-center space-x-4">
                    <?php if (isLoggedIn()): ?>
                        <span class="hidden md:inline text-gray-700">
                            <i class="fas fa-user mr-1"></i><?php echo htmlspecialchars($_SESSION['user_name']); ?>
                        </span>
                        <form method="POST" class="inline">
                            <button type="submit" name="logout" class="btn-driveluxia">
                                <i class="fas fa-sign-out-alt mr-2"></i>Déconnexion
                            </button>
                        </form>
                    <?php else: ?>
                        <a href="?page=login" class="text-gray-700 hover:text-secondary">Connexion</a>
                        <a href="?page=register" class="btn-driveluxia">
                            <i class="fas fa-user-plus mr-2"></i>Inscription
                        </a>
                    <?php endif; ?>
                    
                    <!-- Menu Mobile Button -->
                    <button id="mobileMenuBtn" class="md:hidden text-gray-700">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
            
            <!-- Menu Mobile -->
            <div id="mobileMenu" class="hidden md:hidden mt-6 space-y-4 border-t pt-6">
                <a href="?page=home" class="block text-gray-700 hover:text-secondary">Accueil</a>
                <a href="?page=cars" class="block text-gray-700 hover:text-secondary">Nos Véhicules</a>
                <a href="#services" class="block text-gray-700 hover:text-secondary">Services</a>
                <a href="#contact" class="block text-gray-700 hover:text-secondary">Contact</a>
                <?php if (isLoggedIn()): ?>
                    <a href="?page=bookings" class="block text-gray-700 hover:text-secondary">Mes Réservations</a>
                    <?php if (isAdmin()): ?>
                        <a href="?page=dashboard" class="block text-gray-700 hover:text-secondary">Dashboard</a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    
    <!-- Messages -->
    <?php if ($message): ?>
    <div class="container mx-auto px-4 mt-4">
        <div class="message-<?php echo $message['type']; ?> p-4 rounded-lg">
            <?php echo $message['text']; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Contenu -->
    <main>
        <?php if ($page == 'home'): ?>
        <!-- ACCUEIL -->
        <section class="hero-driveluxia text-white py-32">
            <div class="container mx-auto px-4 text-center">
                <span class="gold-badge mb-6">✦ Location & Vente de Prestige ✦</span>
                <h1 class="text-5xl font-bold mb-6">Vivez l'expérience <span class="text-secondary">DriveLuxia</span></h1>
                <p class="text-xl mb-10 max-w-2xl mx-auto">Des véhicules d'exception pour vos événements spéciaux, mariages, tournages.</p>
                <a href="?page=cars" class="btn-gold text-lg px-10">Découvrir notre collection</a>
            </div>
        </section>
        
        <!-- Voitures -->
        <section class="py-20">
            <div class="container mx-auto px-4">
                <h2 class="section-title">Notre Collection</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <?php foreach (array_slice($cars, 0, 6) as $car): ?>
                    <div class="luxury-card">
                        <img src="<?php echo $car['image_url']; ?>" alt="<?php echo $car['brand']; ?>" class="car-image">
                        <div class="p-6">
                            <h3 class="text-xl font-bold text-primary"><?php echo $car['brand'] . ' ' . $car['model']; ?></h3>
                            <p class="text-secondary font-medium mb-3"><?php echo $car['category']; ?></p>
                            <p class="text-2xl font-bold text-primary mb-4"><?php echo formatPrice($car['price_min']); ?></p>
                            <a href="?page=car&id=<?php echo $car['id']; ?>" class="btn-driveluxia w-full text-center">Voir détails</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="text-center mt-12">
                    <a href="?page=cars" class="text-primary hover:text-secondary font-medium">Voir tous nos véhicules →</a>
                </div>
            </div>
        </section>
        
        <!-- Services -->
        <section id="services" class="py-20 bg-white">
            <div class="container mx-auto px-4">
                <h2 class="section-title center text-center">Nos Services</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="text-center">
                        <div class="feature-icon mx-auto mb-4"><i class="fas fa-ring"></i></div>
                        <h3 class="text-xl font-bold mb-2">Mariages & Cérémonies</h3>
                        <p class="text-gray-600">Des véhicules d'exception pour le plus beau jour</p>
                    </div>
                    <div class="text-center">
                        <div class="feature-icon mx-auto mb-4"><i class="fas fa-film"></i></div>
                        <h3 class="text-xl font-bold mb-2">Tournages & Événements</h3>
                        <p class="text-gray-600">Pour vos productions et événements spéciaux</p>
                    </div>
                    <div class="text-center">
                        <div class="feature-icon mx-auto mb-4"><i class="fas fa-suitcase"></i></div>
                        <h3 class="text-xl font-bold mb-2">Location Simple</h3>
                        <p class="text-gray-600">Pour vos déplacements professionnels ou personnels</p>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Contact -->
        <section id="contact" class="py-20 bg-primary text-white">
            <div class="container mx-auto px-4 text-center">
                <h2 class="text-3xl font-bold mb-8">Contactez-nous</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-3xl mx-auto">
                    <div>
                        <i class="fas fa-map-marker-alt text-secondary text-2xl mb-2"></i>
                        <p>Cotonou, Bénin</p>
                    </div>
                    <div>
                        <i class="fas fa-phone text-secondary text-2xl mb-2"></i>
                        <p>+229 61 23 45 67</p>
                    </div>
                    <div>
                        <i class="fas fa-envelope text-secondary text-2xl mb-2"></i>
                        <p>contact@driveluxia.com</p>
                    </div>
                </div>
            </div>
        </section>
        
        <?php elseif ($page == 'cars'): ?>
        <!-- NOS VÉHICULES -->
        <div class="container mx-auto px-4 py-12">
            <h1 class="text-4xl font-bold text-primary mb-8">Notre Collection</h1>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($cars as $car): ?>
                <div class="luxury-card">
                    <img src="<?php echo $car['image_url']; ?>" alt="<?php echo $car['brand']; ?>" class="car-image">
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-primary"><?php echo $car['brand'] . ' ' . $car['model']; ?></h3>
                        <p class="text-secondary mb-2"><?php echo $car['category']; ?> • <?php echo $car['year']; ?></p>
                        <p class="text-gray-600 text-sm mb-3"><?php echo $car['description']; ?></p>
                        <p class="text-lg font-bold text-primary mb-4"><?php echo $car['price_range']; ?> FCFA</p>
                        <a href="?page=car&id=<?php echo $car['id']; ?>" class="btn-driveluxia w-full text-center">Voir détails</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <?php elseif ($page == 'car' && $car): ?>
        <!-- DÉTAILS VOITURE -->
        <div class="container mx-auto px-4 py-12">
            <a href="?page=cars" class="text-primary hover:text-secondary mb-6 inline-block">← Retour</a>
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                <div class="grid grid-cols-1 lg:grid-cols-2">
                    <img src="<?php echo $car['image_url']; ?>" alt="<?php echo $car['brand']; ?>" class="h-96 object-cover w-full">
                    <div class="p-8">
                        <h1 class="text-3xl font-bold text-primary mb-2"><?php echo $car['brand'] . ' ' . $car['model']; ?></h1>
                        <p class="text-secondary text-lg mb-4"><?php echo $car['category']; ?> • <?php echo $car['year']; ?></p>
                        <p class="text-3xl font-bold text-primary mb-6"><?php echo $car['price_range']; ?> FCFA</p>
                        <p class="text-gray-600 mb-6"><?php echo $car['description']; ?></p>
                        
                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div><i class="fas fa-gas-pump text-secondary mr-2"></i><?php echo $car['fuel_type']; ?></div>
                            <div><i class="fas fa-cog text-secondary mr-2"></i><?php echo $car['transmission']; ?></div>
                            <div><i class="fas fa-users text-secondary mr-2"></i><?php echo $car['seats']; ?> places</div>
                            <div><i class="fas fa-calendar text-secondary mr-2"></i><?php echo $car['year']; ?></div>
                        </div>
                        
                        <?php if (!isLoggedIn()): ?>
                            <a href="?page=login" class="btn-driveluxia w-full text-center">Connectez-vous pour réserver</a>
                        <?php else: ?>
                            <form method="POST" class="space-y-4">
                                <input type="hidden" name="car_id" value="<?php echo $car['id']; ?>">
                                <select name="booking_type" class="input-luxury w-full" required>
                                    <option value="">Type de réservation</option>
                                    <option value="location">Location simple</option>
                                    <option value="mariage">Mariage / Cérémonie</option>
                                    <option value="tournage">Tournage / Événement</option>
                                </select>
                                <div class="grid grid-cols-2 gap-4">
                                    <input type="date" name="start_date" class="input-luxury" required>
                                    <input type="date" name="end_date" class="input-luxury" required>
                                </div>
                                <button type="submit" name="booking" class="btn-gold w-full">Demander la réservation</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <?php elseif ($page == 'login'): ?>
        <!-- CONNEXION -->
        <div class="container mx-auto px-4 py-12">
            <div class="max-w-md mx-auto bg-white rounded-2xl shadow-xl p-8">
                <h2 class="text-2xl font-bold text-primary mb-6 text-center">Connexion</h2>
                <form method="POST">
                    <div class="space-y-4">
                        <input type="email" name="email" placeholder="Email" class="input-luxury w-full" required>
                        <input type="password" name="password" placeholder="Mot de passe" class="input-luxury w-full" required>
                        <button type="submit" name="login" class="btn-driveluxia w-full">Se connecter</button>
                    </div>
                </form>
                <p class="text-center mt-4">Pas de compte ? <a href="?page=register" class="text-secondary">Inscription</a></p>
                <p class="text-center text-sm text-gray-500 mt-4">Admin: admin@driveluxia.com / admin123</p>
            </div>
        </div>
        
        <?php elseif ($page == 'register'): ?>
        <!-- INSCRIPTION -->
        <div class="container mx-auto px-4 py-12">
            <div class="max-w-md mx-auto bg-white rounded-2xl shadow-xl p-8">
                <h2 class="text-2xl font-bold text-primary mb-6 text-center">Inscription</h2>
                <form method="POST">
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <input type="text" name="first_name" placeholder="Prénom" class="input-luxury w-full" required>
                            <input type="text" name="last_name" placeholder="Nom" class="input-luxury w-full" required>
                        </div>
                        <input type="email" name="email" placeholder="Email" class="input-luxury w-full" required>
                        <input type="text" name="phone" placeholder="Téléphone" class="input-luxury w-full">
                        <input type="password" name="password" placeholder="Mot de passe (min 6 caractères)" class="input-luxury w-full" required>
                        <button type="submit" name="register" class="btn-driveluxia w-full">S'inscrire</button>
                    </div>
                </form>
                <p class="text-center mt-4">Déjà un compte ? <a href="?page=login" class="text-secondary">Connexion</a></p>
            </div>
        </div>
        
        <?php elseif ($page == 'bookings' && isLoggedIn()): ?>
        <!-- MES RÉSERVATIONS -->
        <div class="container mx-auto px-4 py-12">
            <h1 class="text-3xl font-bold text-primary mb-8">Mes Réservations</h1>
            <?php if (empty($bookings)): ?>
                <div class="bg-white rounded-xl p-12 text-center">
                    <i class="fas fa-calendar-times text-gray-300 text-6xl mb-4"></i>
                    <p class="text-gray-600">Aucune réservation pour le moment.</p>
                    <a href="?page=cars" class="btn-driveluxia mt-4">Découvrir nos véhicules</a>
                </div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($bookings as $booking): ?>
                    <div class="bg-white rounded-xl shadow p-6">
                        <div class="flex items-center">
                            <img src="<?php echo $booking['image_url']; ?>" alt="" class="w-20 h-20 object-cover rounded mr-4">
                            <div class="flex-1">
                                <h3 class="font-bold"><?php echo $booking['brand'] . ' ' . $booking['model']; ?></h3>
                                <p class="text-sm text-gray-600">
                                    Du <?php echo $booking['start_date']; ?> au <?php echo $booking['end_date']; ?>
                                </p>
                                <p class="text-sm text-gray-600">Type: <?php echo $booking['booking_type']; ?></p>
                                <span class="status-<?php echo $booking['status']; ?> inline-block mt-2">
                                    <?php echo ucfirst($booking['status']); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <?php elseif ($page == 'dashboard' && isAdmin()): ?>
        <!-- DASHBOARD ADMIN -->
        <div class="container mx-auto px-4 py-12">
            <h1 class="text-3xl font-bold text-primary mb-8">Dashboard Admin</h1>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $total_cars; ?></div>
                    <p>Véhicules</p>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $total_users; ?></div>
                    <p>Utilisateurs</p>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $total_bookings; ?></div>
                    <p>Réservations</p>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow p-6">
                <h2 class="text-xl font-bold text-primary mb-4">Dernières réservations</h2>
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="p-3 text-left">Client</th>
                            <th class="p-3 text-left">Véhicule</th>
                            <th class="p-3 text-left">Dates</th>
                            <th class="p-3 text-left">Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_bookings as $booking): ?>
                        <tr class="border-t">
                            <td class="p-3"><?php echo $booking['first_name'] . ' ' . $booking['last_name']; ?></td>
                            <td class="p-3"><?php echo $booking['brand'] . ' ' . $booking['model']; ?></td>
                            <td class="p-3"><?php echo $booking['start_date']; ?> au <?php echo $booking['end_date']; ?></td>
                            <td class="p-3">
                                <span class="status-<?php echo $booking['status']; ?>">
                                    <?php echo ucfirst($booking['status']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </main>
    
    <!-- Footer avec Creative Code -->
    <footer class="bg-dark text-white py-12">
        <div class="container mx-auto px-4 text-center">
            <div class="flex items-center justify-center space-x-2 mb-4">
                <i class="fas fa-car text-secondary"></i>
                <span class="text-2xl font-bold text-secondary">DriveLuxia</span>
            </div>
            <p class="text-gray-400 mb-4">© 2026 DriveLuxia. Tous droits réservés.</p>
            <p class="text-gray-500 text-sm">
                <i class="fas fa-code text-secondary mr-1"></i>
                Développement & Design par <span class="text-secondary font-bold">Creative Code</span>
            </p>
            <p class="text-gray-500 text-xs mt-2">
                Agence spécialisée en graphisme, développement web et mobile
            </p>
            <p class="text-gray-600 text-xs mt-4">📍 Cotonou, Bénin | 📞 +229 61 23 45 67</p>
        </div>
    </footer>
    
    <script src="script.js"></script>
</body>
</html>