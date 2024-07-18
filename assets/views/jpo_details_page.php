<?php
session_start();
require_once '../../config.php';
require_once '../../image.php';
require_once '../../Database.php';
require_once '../../vendor/autoload.php';
require_once '../Class/Jpo.php';
require_once '../Class/User_jpo.php';

$jpoObj = new Jpo();
$userJpoObj = new UserJpo();

$message = '';

// Vérifier si un ID a été passé dans l'URL
if (isset($_GET['id'])) {
    $jpoId = $_GET['id'];
    $jpo = $jpoObj->getByIdJpo($jpoId);
    if (!$jpo) {
        die('JPO non trouvée.');
    }

    $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    if ($userId) {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            if (!$userJpoObj->isUserRegisteredToJpo($userId, $jpoId)) {
                $userJpoObj->createUserJpo($userId, $jpoId);
                $message = "Inscription à la JPO réussie !";
                $_SESSION['message'] = $message;
                header("Location: jpo_details_page.php?id=" . $jpoId);
                exit;
            } else {
                $message = "Vous êtes déjà inscrit à cette JPO.";
            }
        }
        $isRegistered = $userJpoObj->isUserRegisteredToJpo($userId, $jpoId);
    } else {
        $isRegistered = false;
    }
} else {
    die('ID de JPO non spécifié.');
}

// Vérifier si le formulaire a été soumis pour l'inscription
$message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['jpo_id'])) {
    $jpoId = $_POST['jpo_id'];

    if (!$userJpoObj->isUserRegisteredToJpo($userId, $jpoId)) {
        $userJpoObj->createUserJpo($userId, $jpoId);
        $message = "Inscription à la JPO réussie !";
        $_SESSION['message'] = $message;
        header("Location: index.php");
        exit;
    } else {
        $message = "Vous êtes déjà inscrit à cette JPO.";
    }
}

// Vérifier s'il y a un message à afficher depuis la session
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails de la JPO</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .jpo-image {
            width: 100%;
            height: auto;
        }
    </style>
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <a class="navbar-brand w-25" href="../../index.php"><img src="../../assets/img/logo-laplateforme-2024.png" alt="logo la plateforme" class="img-fluid"></a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mr-auto">
                    <?php if (isset($_SESSION['user_token'])): ?>
                        <li class="nav-item">
                            <a class="nav-link text-danger" href="../../index.php?logout=true">Déconnexion</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../../assets/views/profil_page.php">Profil</a>
                        </li>
                        <?php if (isset($_SESSION['admin_rank_id']) && $_SESSION['admin_rank_id'] > 1): ?>
                            <li class="nav-item">
                                <a class="nav-link text-success" href="../../assets/views/pannel_admin_page.php">Admin</a>
                            </li>
                        <?php endif; ?>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="../../assets/views/connexion_page.php">Connexion</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../../assets/views/inscription_page.php">Inscription</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </nav>
    </header>

    <div class="container mt-5">
        <?php if (!empty($message)): ?>
            <div class="alert alert-success" role="alert">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        <h2><?php echo htmlspecialchars($jpo['name']); ?></h2>
        <!-- Utiliser le script intégré pour servir l'image -->
        <img src="../../image.php?image_id=<?php echo htmlspecialchars($jpo['id']); ?>" alt="<?php echo htmlspecialchars($jpo['name']); ?>" class="jpo-image mb-4">
        <p><strong>Ville:</strong> <?php echo htmlspecialchars($jpo['city']); ?></p>
        <p><strong>Date:</strong> <?php echo htmlspecialchars($jpo['date']); ?></p>
        <p><strong>Description:</strong> <?php echo htmlspecialchars($jpo['about']); ?></p>

        <?php if ($userId && !$isRegistered): ?>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?id=<?php echo htmlspecialchars($jpoId); ?>" method="POST">
                <button type="submit" class="btn btn-primary">S'inscrire à la JPO</button>
            </form>
        <?php elseif ($userId && $isRegistered): ?>
            <p>Vous êtes déjà inscrit à cette JPO.</p>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
