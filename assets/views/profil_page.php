<?php
session_start();
require_once '../../config.php';
require_once '../../vendor/autoload.php';
require_once '../Class/User.php';
require_once '../Class/User_jpo.php';


// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_token'])) {
    header("Location: ../../index.php");
    exit;
}

// Récupérer l'ID de l'utilisateur depuis la session
$userId = $_SESSION['user_id'];

// Créer une instance de la classe User et UserJpo
$userObj = new User();
$userJpoObj = new UserJpo();

// Récupérer les informations de l'utilisateur
$user = $userObj->getUserById($userId);

// Récupérer les JPOs auxquelles l'utilisateur est inscrit
$userJpos = $userJpoObj->getUserJposByUserId($userId);

// Traitement du formulaire de désinscription
$message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['jpo_id']) && isset($_POST['unsubscribe'])) {
    $jpoId = $_POST['jpo_id'];

    // Vérifier si l'utilisateur est inscrit à cette JPO avant de la désinscrire
    if ($userJpoObj->isUserRegisteredToJpo($userId, $jpoId)) {
        // Désinscription de la JPO pour l'utilisateur
        $userJpoObj->deleteUserJpoByJpoId($userId, $jpoId);
        $message = "Vous vous êtes désinscrit de la JPO avec succès.";
        // Vous pouvez également rediriger ou recharger la page ici si nécessaire
        $userJpos = $userJpoObj->getUserJposByUserId($userId); // Mettre à jour la liste des JPOs après la désinscription
    } else {
        $message = "Vous n'êtes pas inscrit à cette JPO.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <a class="navbar-brand w-25" href="../../index.php"><img src="../img/logo-laplateforme-2024.png" alt="logo la plateforme" class="img-fluid"></a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="../../index.php?logout=true">Déconnexion</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="profil_page.php">Profil</a>
                    </li>
                    <?php if (isset($_SESSION['admin_rank_id']) && $_SESSION['admin_rank_id'] > 1): ?>
                        <li class="nav-item">
                            <a class="nav-link text-success" href="pannel_admin_page.php">Admin</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </nav>
    </header>

    <div class="container mt-5">
        <h2>Profil de <?php echo htmlspecialchars($user['first_name']) . ' ' . htmlspecialchars($user['name']); ?></h2>
        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-8">
                        <h5 class="card-title">Informations personnelles</h5>
                        <p class="card-text"><strong>Prénom :</strong> <?php echo htmlspecialchars($user['first_name']); ?></p>
                        <p class="card-text"><strong>Nom :</strong> <?php echo htmlspecialchars($user['name']); ?></p>
                        <p class="card-text"><strong>Email :</strong> <?php echo htmlspecialchars($user['mail']); ?></p>
                    </div>
                    <div class="col-4">
                        <?php if ($user['profil_picture']): ?>
                            <p class="card-text"><strong>Photo de profil :</strong></p>
                            <img src="data:image/jpeg;base64,<?php echo base64_encode($user['profil_picture']); ?>" alt="Photo de profil" class="img-fluid w-50">
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <!-- Afficher le message de confirmation ou d'erreur -->
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo ($message === "Vous vous êtes désinscrit de la JPO avec succès.") ? 'success' : 'danger'; ?>" role="alert">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        <h3>Mes Journées Portes Ouvertes</h3>
        <?php if (count($userJpos) > 0): ?>
            <?php foreach ($userJpos as $jpo): ?>
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($jpo['name']); ?></h5>
                        <p class="card-text"><strong>Ville :</strong> <?php echo htmlspecialchars($jpo['city']); ?></p>
                        <p class="card-text"><strong>Date :</strong> <?php echo htmlspecialchars($jpo['date']); ?></p>
                        <p class="card-text"><strong>Description :</strong> <?php echo htmlspecialchars($jpo['about']); ?></p>
                        <!-- Formulaire pour se désinscrire -->
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                            <input type="hidden" name="jpo_id" value="<?php echo $jpo['id']; ?>">
                            <button type="submit" name="unsubscribe" class="btn btn-danger">Se désinscrire</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Vous n'êtes inscrit à aucune JPO.</p>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
