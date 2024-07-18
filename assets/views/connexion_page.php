<?php
session_start(); // Démarrer la session pour gérer le token
require_once '../../config.php'; 
require_once '../../vendor/autoload.php';
require_once '../Class/User.php';

$message = '';

// Vérification de la soumission du formulaire de connexion
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mail = $_POST['mail'];
    $password = $_POST['password'];

    // Créer une instance de la classe User
    $userObj = new User();

    // Récupérer l'utilisateur depuis la base de données
    $user = $userObj->getUserByEmail($mail);

    if ($user && password_verify($password, $user['password'])) {
        // Authentification réussie
        
        // Créer un token de session (simulé ici par une chaîne aléatoire)
        $token = bin2hex(random_bytes(32));

        // Enregistrer le token et l'ID utilisateur dans la session
        $_SESSION['user_token'] = $token;
        $_SESSION['user_id'] = $user['id']; // Ajouter l'ID utilisateur à la session
        $_SESSION['admin_rank_id'] = $user['admin_rank_id']; // Ajouter le rang administrateur à la session, si nécessaire

        // Rediriger vers une page sécurisée après la connexion
        header("Location: ../../index.php");
        exit;
    } else {
        $message = "Identifiants invalides.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <?php include '../template/header.php' ?>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h2 class="text-center">Connexion</h2>
                    </div>
                    <div class="card-body">
                        <?php if ($message) { echo "<p class='alert alert-info'>$message</p>"; } ?>
                        <form action="" method="POST">
                            <div class="form-group">
                                <label for="mail">Email :</label>
                                <input type="email" class="form-control" id="mail" name="mail" required>
                            </div>
                            <div class="form-group">
                                <label for="password">Mot de passe :</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">Se connecter</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
