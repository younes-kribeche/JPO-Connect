<?php
class User {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function getAllUser() {
        $stmt = $this->conn->query("SELECT * FROM user");
        return $stmt->fetchAll();
    }

    public function getByIdUser($id) {
        $stmt = $this->conn->prepare("SELECT * FROM user WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function createUser($firstName, $name, $mail, $password, $profilPicture, $adminRankId) {
        $stmt = $this->conn->prepare("INSERT INTO user (first_name, name, mail, password, profil_picture, admin_rank_id) VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$firstName, $name, $mail, $password, $profilPicture, $adminRankId]);
    }

    public function updateUser($id, $firstName, $name, $mail, $password, $profilPicture, $adminRankId) {
        $stmt = $this->conn->prepare("UPDATE user SET first_name = ?, name = ?, mail = ?, password = ?, profil_picture = ?, admin_rank_id = ? WHERE id = ?");
        return $stmt->execute([$firstName, $name, $mail, $password, $profilPicture, $adminRankId, $id]);
    }

    public function deleteUser($id) {
        $stmt = $this->conn->prepare("DELETE FROM user WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
?>
