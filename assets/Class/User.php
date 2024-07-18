<?php

require_once '../../Database.php';

class User {
    private $conn;
    private $table_name = "user";

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    // Méthode pour créer un nouvel utilisateur
    public function createUser($firstName, $name, $mail, $password, $profilPicture, $adminRankId) {
        $query = "INSERT INTO " . $this->table_name . " (first_name, name, mail, password, profil_picture, admin_rank_id) VALUES (:first_name, :name, :mail, :password, :profil_picture, :admin_rank_id)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':first_name', $firstName);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':mail', $mail);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':profil_picture', $profilPicture, PDO::PARAM_LOB);
        $stmt->bindParam(':admin_rank_id', $adminRankId);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Méthode pour récupérer un utilisateur par son ID
    public function getUserById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Méthode pour récupérer un utilisateur par son email
    public function getUserByEmail($email) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE mail = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Méthode pour mettre à jour un utilisateur
    public function updateUser($id, $firstName, $name, $mail, $password, $profilPicture, $adminRankId) {
        $query = "UPDATE " . $this->table_name . " SET first_name = :first_name, name = :name, mail = :mail, password = :password, profil_picture = :profil_picture, admin_rank_id = :admin_rank_id WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':first_name', $firstName);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':mail', $mail);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':profil_picture', $profilPicture, PDO::PARAM_LOB);
        $stmt->bindParam(':admin_rank_id', $adminRankId);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Méthode pour supprimer un utilisateur
    public function deleteUser($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }
}

?>
