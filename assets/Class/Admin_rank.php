<?php
class AdminRank {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function getAllAdminRank() {
        $stmt = $this->conn->query("SELECT * FROM admin_rank");
        return $stmt->fetchAll();
    }

    public function getByIdAdminRank($id) {
        $stmt = $this->conn->prepare("SELECT * FROM admin_rank WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function createAdminRank($rank) {
        $stmt = $this->conn->prepare("INSERT INTO admin_rank (rank) VALUES (?)");
        return $stmt->execute([$rank]);
    }

    public function updateAdminRank($id, $rank) {
        $stmt = $this->conn->prepare("UPDATE admin_rank SET rank = ? WHERE id = ?");
        return $stmt->execute([$rank, $id]);
    }

    public function deleteAdminRank($id) {
        $stmt = $this->conn->prepare("DELETE FROM admin_rank WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
?>
