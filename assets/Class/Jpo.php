<?php

require_once 'Database.php';

class Jpo {
    private $conn;
    private $table_name = "jpo";

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function getAllJpo() {
        $stmt = $this->conn->query("SELECT * FROM jpo");
        return $stmt->fetchAll();
    }

    public function getByIdJpo($id) {
        $stmt = $this->conn->prepare("SELECT * FROM jpo WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
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
}
?>
