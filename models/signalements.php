<?php
require_once __DIR__ . '/../config/config.php';

class Signalement {
    private $db;
    
    public function __construct() {
        try {
            $this->db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            error_log("Connexion à la base de données réussie");
        } catch(PDOException $e) {
            error_log("Erreur de connexion à la base de données: " . $e->getMessage());
            throw new Exception("Erreur de connexion à la base de données");
        }
    }

    public function create($data) {
        try {
            error_log("Début de la création du signalement dans la base de données");
            error_log("Données reçues: " . print_r($data, true));

            // Vérification des données requises
            $required_fields = ['user_id', 'titre', 'description', 'latitude', 'longitude', 'categorie'];
            foreach ($required_fields as $field) {
                if (empty($data[$field])) {
                    error_log("Champ requis manquant: $field");
                    return false;
                }
            }

            $sql = "INSERT INTO signalement (id_utilisateur, titre, description, latitude, longitude, 
                    categorie, photo, statut_signalement, niveau_urgence, date_signalement) 
                    VALUES (:user_id, :titre, :description, :latitude, :longitude, 
                    :categorie, :photo, 'nouveau', 'faible', NOW())";
            
            error_log("Requête SQL préparée: $sql");
            
            $stmt = $this->db->prepare($sql);
            $params = [
                ':user_id' => $data['user_id'],
                ':titre' => $data['titre'],
                ':description' => $data['description'],
                ':latitude' => $data['latitude'],
                ':longitude' => $data['longitude'],
                ':categorie' => $data['categorie'],
                ':photo' => $data['photo']
            ];
            
            error_log("Paramètres de la requête: " . print_r($params, true));
            
            $result = $stmt->execute($params);

            if ($result) {
                error_log("Signalement créé avec succès. ID: " . $this->db->lastInsertId());
                return true;
            } else {
                error_log("Erreur lors de l'exécution de la requête: " . print_r($stmt->errorInfo(), true));
                return false;
            }
        } catch(PDOException $e) {
            error_log("Erreur PDO lors de la création du signalement: " . $e->getMessage());
            error_log("Trace: " . $e->getTraceAsString());
            return false;
        } catch(Exception $e) {
            error_log("Erreur lors de la création du signalement: " . $e->getMessage());
            error_log("Trace: " . $e->getTraceAsString());
            return false;
        }
    }

    public function getUserSignalements($userId) {
        try {
            $sql = "SELECT * FROM signalement WHERE user_id = :user_id ORDER BY created_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':user_id' => $userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Erreur lors de la récupération des signalements: " . $e->getMessage());
            return [];
        }
    }

    public function getSignalementsByStatus($status) {
        try {
            $sql = "SELECT s.*, u.username FROM signalement s 
                    JOIN utilisateur u ON s.user_id = u.id 
                    WHERE s.statut = :status 
                    ORDER BY s.created_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':status' => $status]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Erreur lors de la récupération des signalements par statut: " . $e->getMessage());
            return [];
        }
    }

    public function getAllSignalements() {
        try {
            $sql = "SELECT s.*, u.username FROM signalement s 
                    JOIN utilisateur u ON s.user_id = u.id 
                    ORDER BY s.created_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Erreur lors de la récupération de tous les signalements: " . $e->getMessage());
            return [];
        }
    }

    public function updateStatus($id, $status) {
        try {
            $sql = "UPDATE signalement SET statut = :status WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':id' => $id,
                ':status' => $status
            ]);
        } catch(PDOException $e) {
            error_log("Erreur lors de la mise à jour du statut: " . $e->getMessage());
            return false;
        }
    }

    public function getSignalementById($id) {
        try {
            $sql = "SELECT s.*, u.username FROM signalement s 
                    JOIN utilisateur u ON s.user_id = u.id 
                    WHERE s.id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Erreur lors de la récupération du signalement: " . $e->getMessage());
            return null;
        }
    }
}
?> 