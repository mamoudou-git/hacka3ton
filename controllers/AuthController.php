<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

class AuthController {
    private $userModel;

    public function __construct() {
        session_start();
        $this->userModel = new User();
    }

    
public function login() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];

        if ($this->userModel->login($email, $password)) {
            // Utiliser la constante DASHBOARD_URL pour la redirection
            header('Location: ' . DASHBOARD_URL);
            exit();
        } else {
            $_SESSION['error'] = $this->userModel->getError();
            header('Location: ' . LOGIN_URL);
            exit();
        }
    } else {
        header('Location: ' . LOGIN_URL);
        exit();
    }
}
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $password = $_POST['password'];
            $confirmPassword = $_POST['confirm_password'];

            if ($password !== $confirmPassword) {
                $_SESSION['error'] = "Les mots de passe ne correspondent pas";
                header('Location: ' . REGISTER_URL);
                exit();
            }

            if ($this->userModel->register($username, $email, $password)) {
                $_SESSION['success'] = "Compte créé avec succès. Veuillez vous connecter.";
                header('Location: ' . LOGIN_URL);
                exit();
            } else {
                $_SESSION['error'] = "Erreur lors de l'inscription";
                header('Location: ' . REGISTER_URL);
                exit();
            }
        }
        // Afficher la vue d'inscription
        require_once __DIR__ . '/../vue/user/inscription.php';
    }

    public function logout() {
        // Déconnexion de l'utilisateur
        $this->userModel->logout();
        
        // Message de succès pour la page d'accueil
        session_start(); // Démarrer une nouvelle session pour le message
        $_SESSION['success'] = "Vous avez été déconnecté avec succès.";
        
        // Redirection vers la page d'accueil
        header('Location: ' . BASE_URL . 'public/index.php');
        exit();
    }

    // Méthode pour gérer le profil utilisateur
    public function profile() {
        if (!$this->userModel->isLoggedIn()) {
            redirect('login');
        }
        
        $userId = $_SESSION['user_id'];
        $user = $this->userModel->getUserById($userId);
        
        if ($user) {
            require_once __DIR__ . '/../vue/user/profil.php';
        } else {
            redirect('login');
        }
    }
}

// Traitement de la requête
$controller = new AuthController();

// Déterminer quelle action exécuter
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch($action) {
    case 'login':
        $controller->login();
        break;
    case 'register':
        $controller->register();
        break;
    case 'logout':
        $controller->logout();
        break;
    case 'profile':
        $controller->profile();
        break;
    default:
        // Redirection par défaut
        header('Location: ' . BASE_URL . 'public/index.php');
        exit();
}
?>