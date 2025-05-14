<?php
require_once '../models/User.php';
require_once '../models/Signalement.php';

class UserController {
    private $userModel;
    private $signalementModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->userModel = new User();
        $this->signalementModel = new Signalement();
    }

    public function updateProfile() {
        if (!isset($_SESSION['user_id'])) {
            return json_encode(['status' => 'error', 'message' => 'Non autorisé']);
        }

        $userId = $_SESSION['user_id'];
        $nom = filter_input(INPUT_POST, 'nom', FILTER_SANITIZE_STRING);
        $prenom = filter_input(INPUT_POST, 'prenom', FILTER_SANITIZE_STRING);
        $telephone = filter_input(INPUT_POST, 'telephone', FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);

        if (!$email) {
            return json_encode(['status' => 'error', 'message' => 'Email invalide']);
        }

        try {
            $result = $this->userModel->updateUser($userId, [
                'nom' => $nom,
                'prenom' => $prenom,
                'email' => $email,
                'telephone' => $telephone
            ]);

            if ($result) {
                $_SESSION['success'] = "Profil mis à jour avec succès";
                return json_encode(['status' => 'success']);
            } else {
                return json_encode(['status' => 'error', 'message' => 'Erreur lors de la mise à jour']);
            }
        } catch (Exception $e) {
            return json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function getUserSignalements() {
        if (!isset($_SESSION['user_id'])) {
            return json_encode(['status' => 'error', 'message' => 'Non autorisé']);
        }

        try {
            $signalements = $this->signalementModel->getUserSignalements($_SESSION['user_id']);
            return json_encode(['status' => 'success', 'data' => $signalements]);
        } catch (Exception $e) {
            return json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function changePassword() {
        if (!isset($_SESSION['user_id'])) {
            return json_encode(['status' => 'error', 'message' => 'Non autorisé']);
        }

        $oldPassword = $_POST['old_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if ($newPassword !== $confirmPassword) {
            return json_encode(['status' => 'error', 'message' => 'Les mots de passe ne correspondent pas']);
        }

        try {
            if ($this->userModel->changePassword($_SESSION['user_id'], $oldPassword, $newPassword)) {
                $_SESSION['success'] = "Mot de passe modifié avec succès";
                return json_encode(['status' => 'success']);
            } else {
                return json_encode(['status' => 'error', 'message' => 'Ancien mot de passe incorrect']);
            }
        } catch (Exception $e) {
            return json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}

// Traitement des requêtes AJAX
if (isset($_GET['action'])) {
    $controller = new UserController();
    
    switch ($_GET['action']) {
        case 'updateProfile':
            echo $controller->updateProfile();
            break;
        case 'getSignalements':
            echo $controller->getUserSignalements();
            break;
        case 'changePassword':
            echo $controller->changePassword();
            break;
    }
}
?>
