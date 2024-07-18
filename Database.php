<?php

class Database {
    private $host = 'localhost';
    private $db_name  = 'jpo_website';
    private $username = 'root';
    private $password = 'root';
    private $conn;
    private static $instance = null;

    // Constructeur privé pour empêcher l'instanciation directe
    private function __construct() {
        $this->connect();
    }

    // Méthode pour établir la connexion
    private function connect() {
        $this->conn = null;

        try {
            $this->conn = new PDO('mysql:host=' . $this->host . ';dbname=' . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            echo 'Erreur de connexion: ' . $e->getMessage();
        }
    }

    // Méthode pour obtenir l'instance unique de Database
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Méthode pour fermer la connexion
    public function disconnect() {
        $this->conn = null;
    }

    // Méthode pour obtenir la connexion
    public function getConnection() {
        return $this->conn;
    }
}

?>
