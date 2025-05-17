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

    public function register($username, $email, $password, $telephone = null, $role = 'citoyen') {
        try {
            // Vérifier si l'email existe déjà
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM utilisateur WHERE email = :email");
            $stmt->execute([':email' => $email]);
            if ($stmt->fetchColumn() > 0) {
                $this->error = "Cet email est déjà utilisé";
                return false;
            }

            // Hasher le mot de passe avec Argon2id
            $hashedPassword = password_hash($password, PASSWORD_ARGON2ID, [
                'memory_cost' => 65536,
                'time_cost' => 4,
                'threads' => 3
            ]);
            
            // Préparer la requête d'insertion avec les bons noms de colonnes
            $stmt = $this->db->prepare("INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, telephone, 
                                      role, est_verifie) 
                                      VALUES (:nom, :prenom, :email, :mot_de_passe, :telephone, 
                                      :role, 1)");
            
            // Séparer le nom d'utilisateur en nom et prénom
            $nameParts = explode(' ', $username);
            $nom = $nameParts[0];
            $prenom = isset($nameParts[1]) ? $nameParts[1] : $nameParts[0];

            $success = $stmt->execute([
                ':nom' => $nom,
                ':prenom' => $prenom,
                ':email' => $email,
                ':mot_de_passe' => $hashedPassword,
                ':telephone' => $telephone,
                ':role' => $role
            ]);

            if ($success) {
                return true;
            }

            $this->error = "Erreur lors de l'inscription";
            return false;
        } catch(PDOException $e) {
            $this->error = "Erreur lors de l'inscription: " . $e->getMessage();
            error_log($this->error);
            return false;
        }
    }

    private function sendVerificationEmail($email, $token) {
        $verificationLink = BASE_URL . "controllers/AuthController.php?action=verify&token=" . $token;
        
        $to = $email;
        $subject = "Vérification de votre compte - Signalement Urbain";
        
        $message = "
        <html>
        <head>
            <title>Vérification de votre compte</title>
        </head>
        <body>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px; font-family: Arial, sans-serif;'>
                <h2 style='color: #1e3c72;'>Bienvenue sur Signalement Urbain !</h2>
                <p>Merci de vous être inscrit. Pour activer votre compte, veuillez cliquer sur le lien ci-dessous :</p>
                <p style='margin: 25px 0;'>
                    <a href='{$verificationLink}' 
                       style='background-color: #4CAF50; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px;'>
                        Vérifier mon compte
                    </a>
                </p>
                <p>Ce lien expirera dans 24 heures.</p>
                <p>Si vous n'avez pas créé de compte, vous pouvez ignorer cet email.</p>
                <hr style='margin: 20px 0;'>
                <p style='font-size: 12px; color: #666;'>
                    Ceci est un email automatique, merci de ne pas y répondre.
                </p>
            </div>
        </body>
        </html>";

        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: Signalement Urbain <no-reply@signalement-urbain.com>' . "\r\n";

        return mail($to, $subject, $message, $headers);
    }

    public function verifyAccount($token) {
        try {
            $stmt = $this->db->prepare("SELECT id, token_expiry FROM utilisateur 
                                      WHERE verification_token = :token AND est_verifie = 0");
            $stmt->execute([':token' => $token]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                $this->error = "Token de vérification invalide";
                return false;
            }

            if (strtotime($user['token_expiry']) < time()) {
                $this->error = "Le lien de vérification a expiré";
                return false;
            }

            $updateStmt = $this->db->prepare("UPDATE utilisateur SET est_verifie = 1, 
                                            verification_token = NULL, token_expiry = NULL 
                                            WHERE id = :id");
            return $updateStmt->execute([':id' => $user['id']]);
        } catch(PDOException $e) {
            $this->error = "Erreur lors de la vérification: " . $e->getMessage();
            return false;
        }
    }

    public function login($email, $password) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM utilisateur WHERE email = :email");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                $this->error = "Email ou mot de passe incorrect";
                return false;
            }

            if (!password_verify($password, $user['mot_de_passe'])) {
                $this->error = "Email ou mot de passe incorrect";
                return false;
            }

            // Générer un JWT pour la session
            $jwt = $this->generateJWT($user['id']);
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['nom'] . ' ' . $user['prenom'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['jwt'] = $jwt;
            
            return true;
        } catch(PDOException $e) {
            $this->error = "Erreur lors de la connexion: " . $e->getMessage();
            return false;
        }
    }

    private function generateJWT($userId) {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode([
            'user_id' => $userId,
            'iat' => time(),
            'exp' => time() + (60 * 60 * 24) // 24 heures
        ]);

        $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

        $secret = 'votre_secret_jwt_tres_securise';
        $signature = hash_hmac('sha256', $base64Header . "." . $base64Payload, $secret, true);
        $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        return $base64Header . "." . $base64Payload . "." . $base64Signature;
    }

    public function requestPasswordReset($email) {
        try {
            $stmt = $this->db->prepare("SELECT id FROM utilisateur WHERE email = :email AND est_verifie = 1");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                $this->error = "Aucun compte vérifié trouvé avec cet email";
                return false;
            }

            $resetToken = bin2hex(random_bytes(32));
            $tokenExpiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $stmt = $this->db->prepare("UPDATE utilisateur SET reset_token = :token, 
                                      reset_token_expiry = :expiry WHERE id = :id");
            $success = $stmt->execute([
                ':token' => $resetToken,
                ':expiry' => $tokenExpiry,
                ':id' => $user['id']
            ]);

            if ($success) {
                $this->sendPasswordResetEmail($email, $resetToken);
                return true;
            }

            return false;
        } catch(PDOException $e) {
            $this->error = "Erreur lors de la demande de réinitialisation: " . $e->getMessage();
            return false;
        }
    }

    private function sendPasswordResetEmail($email, $token) {
        $resetLink = BASE_URL . "vue/user/reset-password.php?token=" . $token;
        
        $to = $email;
        $subject = "Réinitialisation de votre mot de passe - Signalement Urbain";
        
        $message = "
        <html>
        <head>
            <title>Réinitialisation de mot de passe</title>
        </head>
        <body>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px; font-family: Arial, sans-serif;'>
                <h2 style='color: #1e3c72;'>Réinitialisation de votre mot de passe</h2>
                <p>Vous avez demandé à réinitialiser votre mot de passe. Cliquez sur le lien ci-dessous :</p>
                <p style='margin: 25px 0;'>
                    <a href='{$resetLink}' 
                       style='background-color: #4CAF50; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px;'>
                        Réinitialiser mon mot de passe
                    </a>
                </p>
                <p>Ce lien expirera dans 1 heure.</p>
                <p>Si vous n'avez pas demandé cette réinitialisation, ignorez cet email.</p>
            </div>
        </body>
        </html>";

        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: Signalement Urbain <no-reply@signalement-urbain.com>' . "\r\n";

        return mail($to, $subject, $message, $headers);
    }

    public function resetPassword($token, $newPassword) {
        try {
            $stmt = $this->db->prepare("SELECT id FROM utilisateur 
                                      WHERE reset_token = :token 
                                      AND reset_token_expiry > NOW()");
            $stmt->execute([':token' => $token]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                $this->error = "Token invalide ou expiré";
                return false;
            }

            $hashedPassword = password_hash($newPassword, PASSWORD_ARGON2ID);

            $stmt = $this->db->prepare("UPDATE utilisateur 
                                      SET mot_de_passe = :password, 
                                          reset_token = NULL, 
                                          reset_token_expiry = NULL 
                                      WHERE id = :id");
            return $stmt->execute([
                ':password' => $hashedPassword,
                ':id' => $user['id']
            ]);
        } catch(PDOException $e) {
            $this->error = "Erreur lors de la réinitialisation: " . $e->getMessage();
            return false;
        }
    }

    public function getError() {
        return $this->error;
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
        return isset($_SESSION['user_id']) && isset($_SESSION['jwt']);
    }

    public function logout() {
        session_destroy();
        return true;
    }

    public function isAdmin() {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }

    public function hasPermission($action) {
        switch($action) {
            case 'validate_signalement':
            case 'delete_signalement':
            case 'manage_users':
                return $this->isAdmin();
            case 'create_signalement':
            case 'view_signalement':
                return $this->isLoggedIn();
            default:
                return false;
        }
    }
}
?>
