<?php
session_start();
require_once 'config.php';
require_once 'Database.php';
require_once 'vendor/autoload.php';
require_once 'assets/Class/Jpo.php';
require_once 'assets/Class/User_jpo.php';

// Vérifier si l'utilisateur souhaite se déconnecter
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

// Vérifier si une requête d'image a été effectuée
if (isset($_GET['image_id'])) {
    $imageId = intval($_GET['image_id']);
    
    // Connexion à la base de données
    $conn = Database::getInstance()->getConnection();
    
    // Préparer et exécuter la requête pour obtenir l'image
    $stmt = $conn->prepare("SELECT picture FROM jpo WHERE id = ?");
    $stmt->execute([$imageId]);
    $image = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($image) {
        // Définir les en-têtes HTTP pour l'image
        header("Content-Type: image/jpeg"); // Assurez-vous que le type MIME correspond au format de l'image stockée
        echo $image['picture'];
    } else {
        header("HTTP/1.0 404 Not Found");
        echo "Image non trouvée";
    }
    exit;
}

// Récupérer toutes les JPO
$jpoObj = new Jpo();
$jpos = $jpoObj->getAllJpo();

$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$userJpoObj = new UserJpo();
$userJpos = $userJpoObj->getUserJposByUserId($userId);
$userJpoIds = array_column($userJpos, 'jpo_id');

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container {
            max-width: 1200px; /* Ajustez la largeur maximale selon vos besoins */
        }
        .jpo-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem; /* Espace entre les éléments du grid */
        }
        .jpo-item {
            position: relative;
            text-align: left;
            color: white;
            overflow: hidden;
            border-radius: 8px; /* Pour des coins arrondis */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Ombre légère pour l'effet 3D */
        }
        .jpo-item img {
            width: 100%;
            height: auto;
            display: block;
            transition: transform 0.3s ease-in-out;
        }
        .jpo-item:hover img {
            transform: scale(1.05); /* Effet de zoom au survol */
        }
        .caption {
            position: absolute;
            bottom: 10px;
            left: 10px;
            background-color: rgba(0, 0, 0, 0.5);
            padding: 5px;
            border-radius: 3px;
        }
        .jpo-item:hover .caption {
            background-color: rgba(0, 0, 0, 0.7);
        }
    </style>
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <a class="navbar-brand w-25" href="index.php"><img src="assets/img/logo-laplateforme-2024.png" alt="logo la plateforme" class="img-fluid"></a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mr-auto">
                    <?php if (isset($_SESSION['user_token'])): ?>
                        <li class="nav-item">
                            <a class="nav-link text-danger" href="index.php?logout=true">Déconnexion</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="assets/views/profil_page.php">Profil</a>
                        </li>
                        <?php if (isset($_SESSION['admin_rank_id']) && $_SESSION['admin_rank_id'] > 1): ?>
                            <li class="nav-item">
                                <a class="nav-link text-success" href="assets/views/pannel_admin_page.php">Admin</a>
                            </li>
                        <?php endif; ?>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="assets/views/connexion_page.php">Connexion</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="assets/views/inscription_page.php">Inscription</a>
                        </li>
                    <?php endif; ?>
                </ul>
                <form class="form-inline my-2 my-lg-0 ml-auto">
                    <input id="search" class="form-control mr-sm-2" type="search" placeholder="Rechercher une JPO..." aria-label="Search">
                    <div id="jpoOptions" class="dropdown-menu" aria-labelledby="searchDropdownMenu"></div>
                </form>
            </div>
        </nav>
    </header>

    <div class="container mt-5">
        <?php if (!empty($message)): ?>
            <div class="alert alert-success" role="alert">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        <h2 class="mb-4">Les prochaines Journées Portes Ouvertes:</h2>
        <div class="jpo-grid">
            <?php foreach ($jpos as $jpo): ?>
                <div class="jpo-item">
                    <a href="assets/views/jpo_details_page.php?id=<?php echo htmlspecialchars($jpo['id']); ?>">
                        <!-- Utiliser le script intégré pour servir l'image -->
                        <img src="index.php?image_id=<?php echo htmlspecialchars($jpo['id']); ?>" alt="<?php echo htmlspecialchars($jpo['name']); ?>">
                        <div class="caption">
                            <h5 class="text-light"><?php echo htmlspecialchars($jpo['name']); ?></h5>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        // Script pour la recherche en temps réel dans la barre de recherche
        $(document).ready(function() {
            $('#search').on('input', function() {
                var searchText = $(this).val();
                if (searchText.length > 0) {
                    $('#jpoOptions').addClass('show-options');
                    $.ajax({
                        url: 'search.php',
                        type: 'POST',
                        data: {search: searchText},
                        success: function(response) {
                            $('#jpoOptions').html(response);
                        }
                    });
                } else {
                    $('#jpoOptions').removeClass('show-options');
                }
            });
        });
    </script>
</body>
</html>
