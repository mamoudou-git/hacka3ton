<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../config/config.php';

class AuthController {
    private $userModel;

    public function __construct() {
        session_start();
        $this->userModel = new User();
    }

   public function login($email, $password) {
    $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        // Ajoute d'autres infos de session si besoin
        return true;
    }
    return false;
}
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $password = $_POST['password'];
            $confirmPassword = $_POST['confirm_password'];

            if ($password !== $confirmPassword) {
                $_SESSION['error'] = "Les mots de passe ne correspondent pas";
                redirect('inscription');
            }

            if ($this->userModel->register($username, $email, $password)) {
                $_SESSION['success'] = "Compte créé avec succès. Veuillez vous connecter.";
                redirect('login');
            } else {
                $_SESSION['error'] = "Erreur lors de l'inscription";
                redirect('inscription');
            }
        }
        // Afficher la vue d'inscription
        require_once __DIR__ . '/../vue/user/inscription.php';
    }

    public function logout() {
        $this->userModel->logout();
        redirect('home');
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
        redirect('home');
}
?> 