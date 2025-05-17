<?php
require_once __DIR__ . '/../models/signalements.php';
require_once __DIR__ . '/../config/config.php';

class SignalementController {
    private $signalementModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        try {
            $this->signalementModel = new Signalement();
        } catch (Exception $e) {
            error_log("Erreur lors de l'initialisation du modèle: " . $e->getMessage());
            $_SESSION['error'] = "Erreur système. Veuillez réessayer.";
        }
    }

    public function create() {
        try {
            // Vérifier si l'utilisateur est connecté
            if (!isset($_SESSION['user_id'])) {
                $_SESSION['error'] = "Vous devez être connecté pour créer un signalement";
                header('Location: ' . LOGIN_URL);
                exit();
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                error_log("Début du traitement POST pour la création de signalement");
                
                // Récupération et validation des données
                $titre = filter_input(INPUT_POST, 'titre', FILTER_SANITIZE_STRING);
                $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
                $latitude = filter_input(INPUT_POST, 'latitude', FILTER_SANITIZE_STRING);
                $longitude = filter_input(INPUT_POST, 'longitude', FILTER_SANITIZE_STRING);
                $categorie = filter_input(INPUT_POST, 'categorie', FILTER_SANITIZE_STRING);

                error_log("Données reçues - Titre: $titre, Catégorie: $categorie, Lat: $latitude, Long: $longitude");

                // Gestion de l'upload de l'image
                $photo = null;
                if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                    $photo = $this->handleImageUpload($_FILES['photo']);
                    if (!$photo) {
                        $_SESSION['error'] = "Erreur lors de l'upload de l'image";
                        error_log("Échec de l'upload de l'image");
                        header('Location: ' . BASE_URL . 'vue/signalements/creation.php');
                        exit();
                    }
                    error_log("Photo uploadée avec succès: $photo");
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

                error_log("Tentative de création du signalement: " . print_r($signalement, true));

                if ($this->signalementModel->create($signalement)) {
                    error_log("Signalement créé avec succès");
                    $_SESSION['success'] = "Votre signalement a été créé avec succès";
                    header('Location: ' . BASE_URL . 'vue/user/profil.php');
                    exit();
                } else {
                    error_log("Échec de la création du signalement");
                    $_SESSION['error'] = "Erreur lors de la création du signalement";
                    header('Location: ' . BASE_URL . 'vue/signalements/creation.php');
                    exit();
                }
            }
        } catch (Exception $e) {
            error_log("Erreur lors de la création du signalement: " . $e->getMessage());
            $_SESSION['error'] = "Une erreur est survenue lors de la création du signalement";
            header('Location: ' . BASE_URL . 'vue/signalements/creation.php');
            exit();
        }

        // Afficher le formulaire
        require_once __DIR__ . '/../vue/signalements/creation.php';
    }

    private function handleImageUpload($file) {
        try {
            $targetDir = __DIR__ . '/../public/uploads/signalements/';
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

            $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png'];

            if (!in_array($fileExtension, $allowedExtensions)) {
                error_log("Extension de fichier non autorisée: $fileExtension");
                return false;
            }

            $fileName = uniqid() . '.' . $fileExtension;
            $targetFile = $targetDir . $fileName;

            if (move_uploaded_file($file['tmp_name'], $targetFile)) {
                error_log("Fichier uploadé avec succès: $fileName");
                return 'uploads/signalements/' . $fileName;
            }

            error_log("Échec de l'upload du fichier");
            return false;
        } catch (Exception $e) {
            error_log("Erreur lors de l'upload de l'image: " . $e->getMessage());
            return false;
        }
    }
}

// Traitement de la requête
try {
    $controller = new SignalementController();

    // Déterminer quelle action exécuter
    $action = isset($_GET['action']) ? $_GET['action'] : '';
    error_log("Action demandée: $action");

    switch($action) {
        case 'create':
            $controller->create();
            break;
        default:
            error_log("Action non reconnue: $action");
            header('Location: ' . BASE_URL);
            exit();
    }
} catch (Exception $e) {
    error_log("Erreur critique dans le contrôleur: " . $e->getMessage());
    $_SESSION['error'] = "Une erreur système est survenue";
    header('Location: ' . BASE_URL);
    exit();
}
?> 