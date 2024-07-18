<?php

class Jpo {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function getAllJpo() {
        $stmt = $this->conn->query("SELECT * FROM jpo");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByIdJpo($id) {
        $stmt = $this->conn->prepare("SELECT * FROM jpo WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createJpo($name, $city, $date, $about) {
        $stmt = $this->conn->prepare("INSERT INTO jpo (name, city, date, about) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$name, $city, $date, $about]);
    }

    public function updateJpo($id, $name, $city, $date, $about) {
        $stmt = $this->conn->prepare("UPDATE jpo SET name = ?, city = ?, date = ?, about = ? WHERE id = ?");
        return $stmt->execute([$name, $city, $date, $about, $id]);
    }

    public function deleteJpo($id) {
        $stmt = $this->conn->prepare("DELETE FROM jpo WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // Nouvelle méthode pour obtenir le nombre d'utilisateurs inscrits à une JPO
    public function getUserCountByJpoId($jpoId) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS user_count FROM user_jpo WHERE jpo_id = ?");
        $stmt->execute([$jpoId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['user_count'];
    }
}

?>
