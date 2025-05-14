<?php
require_once 'config/config.php';

class Signalement {
    private $db;
    
    public function __construct() {
        try {
            $this->db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            echo "Erreur de connexion: " . $e->getMessage();
        }
    }

    public function create($data) {
        try {
            $sql = "INSERT INTO signalements (user_id, titre, description, latitude, longitude, 
                    categorie, photo, statut, created_at) 
                    VALUES (:user_id, :titre, :description, :latitude, :longitude, 
                    :categorie, :photo, :statut, NOW())";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':user_id' => $data['user_id'],
                ':titre' => $data['titre'],
                ':description' => $data['description'],
                ':latitude' => $data['latitude'],
                ':longitude' => $data['longitude'],
                ':categorie' => $data['categorie'],
                ':photo' => $data['photo'],
                ':statut' => $data['statut']
            ]);
        } catch(PDOException $e) {
            return false;
        }
    }

    public function getUserSignalements($userId) {
        try {
            $sql = "SELECT * FROM signalements WHERE user_id = :user_id ORDER BY created_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':user_id' => $userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return [];
        }
    }

    public function getSignalementsByStatus($status) {
        try {
            $sql = "SELECT s.*, u.username FROM signalements s 
                    JOIN users u ON s.user_id = u.id 
                    WHERE s.statut = :status 
                    ORDER BY s.created_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':status' => $status]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return [];
        }
    }

    public function getAllSignalements() {
        try {
            $sql = "SELECT s.*, u.username FROM signalements s 
                    JOIN users u ON s.user_id = u.id 
                    ORDER BY s.created_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return [];
        }
    }

    public function updateStatus($id, $status) {
        try {
            $sql = "UPDATE signalements SET statut = :status WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':id' => $id,
                ':status' => $status
            ]);
        } catch(PDOException $e) {
            return false;
        }
    }

    public function getSignalementById($id) {
        try {
            $sql = "SELECT s.*, u.username FROM signalements s 
                    JOIN users u ON s.user_id = u.id 
                    WHERE s.id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return null;
        }
    }
}
?> 