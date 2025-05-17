<?php
session_start();
require_once __DIR__ . '/../../config/config.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . LOGIN_URL);
    exit();
}

// Rediriger vers le contrôleur d'authentification avec l'action de déconnexion
header('Location: ' . BASE_URL . 'controllers/AuthController.php?action=logout');
exit();
?>
