<?php
require_once __DIR__ . '/../config/config.php';

class User {
    private $db;
    private $error;
    
    public function __construct() {
        try {
            $this->db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            $this->error = "Erreur de connexion: " . $e->getMessage();
            error_log($this->error);
            throw new Exception($this->error);
        }
    }

    public function register($username, $email, $password, $role = 'utilisateur') {
        try {
            // Vérifier si l'email existe déjà
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM utilisateur WHERE email = :email");
            $stmt->execute([':email' => $email]);
            if ($stmt->fetchColumn() > 0) {
                $this->error = "Cet email est déjà utilisé";
                return false;
            }

            // Hasher le mot de passe
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Préparer la requête d'insertion avec les bons noms de colonnes
            $stmt = $this->db->prepare("INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, role) 
                                      VALUES (:nom, :prenom, :email, :mot_de_passe, :role)");
            
            // Séparer le nom d'utilisateur en nom et prénom (on utilise le même pour les deux si pas de séparation)
            $nameParts = explode(' ', $username);
            $nom = $nameParts[0];
            $prenom = isset($nameParts[1]) ? $nameParts[1] : $nameParts[0];

            $success = $stmt->execute([
                ':nom' => $nom,
                ':prenom' => $prenom,
                ':email' => $email,
                ':mot_de_passe' => $hashedPassword,
                ':role' => $role
            ]);

            if (!$success) {
                $this->error = "Erreur lors de l'inscription";
                return false;
            }

            return true;
        } catch(PDOException $e) {
            $this->error = "Erreur lors de l'inscription: " . $e->getMessage();
            error_log($this->error);
            return false;
        }
    }

    public function getError() {
        return $this->error;
    }

    public function login($email, $password) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM utilisateur WHERE email = :email");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['mot_de_passe'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['nom'] . ' ' . $user['prenom'];
                $_SESSION['role'] = $user['role'];
                return true;
            }
            return false;
        } catch(PDOException $e) {
            $this->error = "Erreur lors de la connexion: " . $e->getMessage();
            return false;
        }
    }

    public function getUserById($id) {
        try {
            $stmt = $this->db->prepare("SELECT id, nom, prenom, email, role FROM utilisateur WHERE id = :id");
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            $this->error = "Erreur lors de la récupération de l'utilisateur: " . $e->getMessage();
            return false;
        }
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public function logout() {
        session_destroy();
        return true;
    }
}
?>
