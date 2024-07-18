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

    public function getUserJposByUserId($userId) {
        $stmt = $this->conn->prepare("
            SELECT jpo.id, jpo.name, jpo.city, jpo.date, jpo.about 
            FROM jpo 
            JOIN user_jpo ON jpo.id = user_jpo.jpo_id 
            WHERE user_jpo.user_id = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Utilisation de FETCH_ASSOC pour un tableau associatif
    }
    
    public function deleteUserJpoByJpoId($userId, $jpoId) {
        $stmt = $this->conn->prepare("DELETE FROM user_jpo WHERE user_id = ? AND jpo_id = ?");
        return $stmt->execute([$userId, $jpoId]);
    }

    public function isUserRegisteredToJpo($userId, $jpoId) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM user_jpo WHERE user_id = ? AND jpo_id = ?");
        $stmt->execute([$userId, $jpoId]);
        $count = $stmt->fetchColumn();
        return $count > 0;
    }

    public function createUserJpos($userId, $jpoIds) {
        // Préparer la requête d'insertion
        $sql = "INSERT INTO user_jpo (user_id, jpo_id) VALUES ";
        $params = [];
        $placeholders = [];

        // Construire les paramètres et les placeholders
        foreach ($jpoIds as $jpoId) {
            if (!$this->isUserRegisteredToJpo($userId, $jpoId)) { // Vérifier si déjà inscrit
                $placeholders[] = "(?, ?)";
                $params[] = $userId;
                $params[] = $jpoId;
            }
        }

        // S'il y a des JPOs à inscrire
        if (!empty($placeholders)) {
            // Combiner la requête avec les placeholders
            $sql .= implode(", ", $placeholders);

            // Préparer et exécuter la requête
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute($params);
        }

        return true; // Aucune nouvelle inscription à effectuer
    }
    public function getUsersByJpoId($jpoId) {
        $stmt = $this->conn->prepare("SELECT u.username, u.email FROM user_jpo uj JOIN users u ON uj.user_id = u.id WHERE uj.jpo_id = ?");
        $stmt->execute([$jpoId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
