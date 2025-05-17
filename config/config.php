<?php
// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'gestion_signalement');
define('DB_USER', 'root');
define('DB_PASS', '');

// Configuration des chemins
define('ROOT_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
define('BASE_URL', 'http://localhost/Projet_hackaton2/');

// Configuration des routes pour l'authentification
define('LOGIN_URL', BASE_URL . 'vue/user/login.php');
define('REGISTER_URL', BASE_URL . 'vue/user/inscription.php');
define('DASHBOARD_URL', BASE_URL . 'vue/user/profil.php');

// Configuration des rôles
define('ROLE_USER', 'user');
define('ROLE_ADMIN', 'admin');
define('ROLE_AUTHORITY', 'authority');

// Configuration des routes
$routes = [
    // Routes pour l'authentification
    'login' => [
        'path' => 'vue/user/login.php',
        'title' => 'Connexion',
        'auth_required' => false
    ],
    'inscription' => [
        'path' => 'vue/user/inscription.php',
        'title' => 'Inscription',
        'auth_required' => false
    ],
    'logout' => [
        'path' => 'controllers/AuthController.php?action=logout',
        'title' => 'Déconnexion',
        'auth_required' => true
    ],
    // Route par défaut (page d'accueil)
    'home' => [
        'path' => 'public/index.php',
        'title' => 'Accueil',
        'auth_required' => false
    ],
    // Route pour le profil utilisateur
    'profil' => [
        'path' => 'vue/user/profil.php',
        'title' => 'Mon Profil',
        'auth_required' => true
    ]
];

// Fonction pour obtenir l'URL complète d'une route
function getRoute($routeName) {
    global $routes;
    if (isset($routes[$routeName])) {
        return BASE_URL . $routes[$routeName]['path'];
    }
    return BASE_URL;
}

// Fonction pour vérifier si une route nécessite une authentification
function requiresAuth($routeName) {
    global $routes;
    return isset($routes[$routeName]) && $routes[$routeName]['auth_required'];
}

// Fonction pour rediriger vers une route
function redirect($routeName) {
    global $routes;
    
    switch($routeName) {
        case 'login':
            header('Location: ' . BASE_URL . 'vue/user/login.php');
            break;
        case 'inscription':
            header('Location: ' . BASE_URL . 'vue/user/inscription.php');
            break;
        case 'profil':
            header('Location: ' . BASE_URL . 'vue/user/profil.php');
            break;
        case 'home':
            header('Location: ' . BASE_URL);
            break;
        default:
            if (isset($routes[$routeName])) {
                header('Location: ' . BASE_URL . $routes[$routeName]['path']);
            } else {
                header('Location: ' . BASE_URL);
            }
    }
    exit();
}

// Fonction pour obtenir le titre de la page
function getPageTitle($routeName) {
    global $routes;
    return isset($routes[$routeName]) ? $routes[$routeName]['title'] : 'Urban Report';
}

// Middleware de vérification d'authentification
function checkAuth() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $currentRoute = getCurrentRoute();
    
    if (requiresAuth($currentRoute) && !isset($_SESSION['user_id'])) {
        redirect('login');
    }
}

// Fonction pour obtenir la route actuelle
function getCurrentRoute() {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $path = trim(str_replace(dirname($_SERVER['SCRIPT_NAME']), '', $path), '/');
    
    global $routes;
    foreach ($routes as $routeName => $route) {
        if (strpos($path, $route['path']) !== false) {
            return $routeName;
        }
    }
    return 'home';
}
?>
