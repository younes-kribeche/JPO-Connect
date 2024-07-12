<?php
class UserJpo {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function getAllUserJpo() {
        $stmt = $this->conn->query("SELECT * FROM user_jpo");
        return $stmt->fetchAll();
    }

    public function getByIdUserJpo($id) {
        $stmt = $this->conn->prepare("SELECT * FROM user_jpo WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function createUserJpo($userId, $jpoId) {
        $stmt = $this->conn->prepare("INSERT INTO user_jpo (user_id, jpo_id) VALUES (?, ?)");
        return $stmt->execute([$userId, $jpoId]);
    }

    public function deleteUserJpo($id) {
        $stmt = $this->conn->prepare("DELETE FROM user_jpo WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
?>
