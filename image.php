<?php
require_once 'config.php';
require_once 'Database.php';
require_once 'vendor/autoload.php';

if (isset($_GET['image_id'])) {
    $imageId = intval($_GET['image_id']); // Sécuriser l'ID pour éviter les injections SQL

    $conn = Database::getInstance()->getConnection();
    
    $stmt = $conn->prepare("SELECT picture FROM jpo WHERE id = ?");
    $stmt->execute([$imageId]);
    $image = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($image) {
        header('Content-Type: image/jpeg'); // Dépend du type d'image, ajustez si nécessaire
        echo $image['picture'];
    } else {
        header("HTTP/1.0 404 Not Found");
        echo "Image non trouvée.";
    }
} else {
    header("HTTP/1.0 400 Bad Request");
}
?>
