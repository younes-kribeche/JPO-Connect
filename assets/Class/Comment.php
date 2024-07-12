<?php
class Comment {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function getAllComment() {
        $stmt = $this->conn->query("SELECT * FROM comment");
        return $stmt->fetchAll();
    }

    public function getByIdComment($id) {
        $stmt = $this->conn->prepare("SELECT * FROM comment WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function createComment($content, $userId, $jpoId) {
        $stmt = $this->conn->prepare("INSERT INTO comment (content, user_id, jpo_id) VALUES (?, ?, ?)");
        return $stmt->execute([$content, $userId, $jpoId]);
    }

    public function updateComment($id, $content) {
        $stmt = $this->conn->prepare("UPDATE comment SET content = ? WHERE id = ?");
        return $stmt->execute([$content, $id]);
    }

    public function deleteComment($id) {
        $stmt = $this->conn->prepare("DELETE FROM comment WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
?>
