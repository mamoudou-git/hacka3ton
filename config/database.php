<?php
class Database {
    private $host = "localhost";
    private $db_name = "gestion_signalement";
    private $username = "root";
    private $password = "";
    

    public function getConnection() {
        

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
        } catch(PDOException $e) {
            echo "Erreur de connexion: " . $e->getMessage();
        }

        return $this->conn;
    }
}
?>