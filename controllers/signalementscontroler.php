<?php
require_once 'models/Signalement.php';

class SignalementController {
    private $signalementModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->signalementModel = new Signalement();
    }

    public function create() {
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = "Vous devez être connecté pour créer un signalement";
            redirect('login');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Récupération des données du formulaire
            $titre = filter_input(INPUT_POST, 'titre', FILTER_SANITIZE_STRING);
            $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
            $latitude = filter_input(INPUT_POST, 'latitude', FILTER_SANITIZE_STRING);
            $longitude = filter_input(INPUT_POST, 'longitude', FILTER_SANITIZE_STRING);
            $categorie = filter_input(INPUT_POST, 'categorie', FILTER_SANITIZE_STRING);

            // Gestion de l'upload de l'image
            $photo = null;
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $photo = $this->handleImageUpload($_FILES['photo']);
                if (!$photo) {
                    $_SESSION['error'] = "Erreur lors de l'upload de l'image";
                    redirect('signalement/create');
                }
            }

            // Création du signalement
            $signalement = [
                'user_id' => $_SESSION['user_id'],
                'titre' => $titre,
                'description' => $description,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'categorie' => $categorie,
                'photo' => $photo,
                'statut' => 'en_attente'
            ];

            if ($this->signalementModel->create($signalement)) {
                $_SESSION['success'] = "Votre signalement a été créé avec succès";
                redirect('profil');
            } else {
                $_SESSION['error'] = "Erreur lors de la création du signalement";
                redirect('signalement/create');
            }
        }

        // Afficher le formulaire
        require_once 'vue/signalements/creation.php';
    }

    private function handleImageUpload($file) {
        $targetDir = "uploads/signalements/";
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png'];

        if (!in_array($fileExtension, $allowedExtensions)) {
            return false;
        }

        $fileName = uniqid() . '.' . $fileExtension;
        $targetFile = $targetDir . $fileName;

        if (move_uploaded_file($file['tmp_name'], $targetFile)) {
            return $fileName;
        }

        return false;
    }
}
?> 