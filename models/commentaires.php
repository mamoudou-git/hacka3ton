<?php
require_once __DIR__ . '/../config/config.php';

class Commentaire {
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

    public function addComment($signalementId, $userId, $commentaire) {
        try {
            $stmt = $this->db->prepare("INSERT INTO commentaire (id_signalement, id_utilisateur, commentaire) 
                                      VALUES (:id_signalement, :id_utilisateur, :commentaire)");
            
            return $stmt->execute([
                ':id_signalement' => $signalementId,
                ':id_utilisateur' => $userId,
                ':commentaire' => $commentaire
            ]);
        } catch(PDOException $e) {
            $this->error = "Erreur lors de l'ajout du commentaire: " . $e->getMessage();
            error_log($this->error);
            return false;
        }
    }

    public function getCommentsBySignalement($signalementId) {
        try {
            $stmt = $this->db->prepare("
                SELECT c.*, u.nom, u.prenom, u.role 
                FROM commentaire c 
                JOIN utilisateur u ON c.id_utilisateur = u.id 
                WHERE c.id_signalement = :id_signalement 
                ORDER BY c.date_commentaire DESC
            ");
            
            $stmt->execute([':id_signalement' => $signalementId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            $this->error = "Erreur lors de la récupération des commentaires: " . $e->getMessage();
            error_log($this->error);
            return [];
        }
    }

    public function deleteComment($commentId, $userId) {
        try {
            // Vérifier si l'utilisateur est l'auteur du commentaire
            $stmt = $this->db->prepare("SELECT id_utilisateur FROM commentaire WHERE id = :id");
            $stmt->execute([':id' => $commentId]);
            $comment = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$comment || $comment['id_utilisateur'] != $userId) {
                $this->error = "Non autorisé à supprimer ce commentaire";
                return false;
            }

            $stmt = $this->db->prepare("DELETE FROM commentaire WHERE id = :id");
            return $stmt->execute([':id' => $commentId]);
        } catch(PDOException $e) {
            $this->error = "Erreur lors de la suppression du commentaire: " . $e->getMessage();
            error_log($this->error);
            return false;
        }
    }

    public function updateComment($commentId, $userId, $newCommentaire) {
        try {
            // Vérifier si l'utilisateur est l'auteur du commentaire
            $stmt = $this->db->prepare("SELECT id_utilisateur FROM commentaire WHERE id = :id");
            $stmt->execute([':id' => $commentId]);
            $comment = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$comment || $comment['id_utilisateur'] != $userId) {
                $this->error = "Non autorisé à modifier ce commentaire";
                return false;
            }

            $stmt = $this->db->prepare("
                UPDATE commentaire 
                SET commentaire = :commentaire 
                WHERE id = :id
            ");
            
            return $stmt->execute([
                ':id' => $commentId,
                ':commentaire' => $newCommentaire
            ]);
        } catch(PDOException $e) {
            $this->error = "Erreur lors de la modification du commentaire: " . $e->getMessage();
            error_log($this->error);
            return false;
        }
    }

    public function getCommentCount($signalementId) {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count 
                FROM commentaire 
                WHERE id_signalement = :id_signalement
            ");
            
            $stmt->execute([':id_signalement' => $signalementId]);
            return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        } catch(PDOException $e) {
            $this->error = "Erreur lors du comptage des commentaires: " . $e->getMessage();
            error_log($this->error);
            return 0;
        }
    }

    public function getError() {
        return $this->error;
    }
}
?>
