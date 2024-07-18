<?php
session_start(); // Démarrer la session si ce n'est pas déjà fait
require_once 'config.php'; // Assurez-vous que le chemin vers votre fichier de configuration est correct
require_once 'vendor/autoload.php'; // Assurez-vous que le chemin vers votre autoload.php est correct
require_once 'assets/Class/Jpo.php'; // Incluez la classe Jpo

// Vérifier si l'utilisateur souhaite se déconnecter
if (isset($_GET['logout'])) {
    // Détruire toutes les données de la session
    session_destroy();
    // Rediriger vers la page index
    header("Location: index.php");
    exit;
}

// Créer une instance de la classe Jpo
$jpoObj = new Jpo();

// Récupérer toutes les JPO
$jpos = $jpoObj->getAllJpo();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Styles CSS personnalisés -->
    <style>
        .jpoOption {
            border: 1px solid #ccc;
            padding: 5px;
            margin-bottom: 5px;
        }
        .jpoOption a {
            text-decoration: none;
            color: inherit;
        }
        .jpoOption:hover {
            background-color: #f8f9fa;
        }
        /* Style pour les options de recherche */
        #jpoOptions {
            position: absolute;
            width: 100%;
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
            background-color: white;
            border: 1px solid #ccc;
            border-top: none;
            display: none; /* Caché par défaut */
        }
        .show-options {
            display: block !important; /* Afficher les options */
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
                        <!-- Bouton Déconnexion -->
                        <li class="nav-item">
                            <a class="nav-link text-danger" href="index.php?logout=true">Déconnexion</a>
                        </li>
                        <!-- Lien Profil (toujours visible si connecté) -->
                        <li class="nav-item">
                            <a class="nav-link" href="assets/views/profil_page.php">Profil</a>
                        </li>
                        <!-- Lien Admin (visible seulement si admin_rank_id > 1) -->
                        <?php if (isset($_SESSION['admin_rank_id']) && $_SESSION['admin_rank_id'] > 1): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="assets/views/pannel_admin_page.php">Admin</a>
                            </li>
                        <?php endif; ?>
                    <?php else: ?>
                        <!-- Lien Connexion -->
                        <li class="nav-item">
                            <a class="nav-link" href="assets/views/connexion_page.php">Connexion</a>
                        </li>
                        <!-- Lien Inscription -->
                        <li class="nav-item">
                            <a class="nav-link" href="assets/views/inscription_page.php">Inscription</a>
                        </li>
                    <?php endif; ?>
                </ul>
                <!-- Barre de recherche -->
                <form class="form-inline my-2 my-lg-0 ml-auto">
                    <input id="search" class="form-control mr-sm-2" type="search" placeholder="Rechercher une JPO..." aria-label="Search">
                    <div id="jpoOptions" class="dropdown-menu" aria-labelledby="searchDropdownMenu"></div>
                </form>
            </div>
        </nav>
    </header>

    <div class="container mt-5">
        <div class="row">
            <div class="col-md-12">
                <h2 class="mb-4">Les prochaines Journées Portes Ouvertes:</h2>
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">Nom</th>
                            <th scope="col">Ville</th>
                            <th scope="col">Date</th>
                            <th scope="col">Description</th>
                            <th scope="col"></th> <!-- Colonne pour le bouton -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($jpos as $jpo): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($jpo['name']); ?></td>
                                <td><?php echo htmlspecialchars($jpo['city']); ?></td>
                                <td><?php echo htmlspecialchars($jpo['date']); ?></td>
                                <td><?php echo htmlspecialchars($jpo['about']); ?></td>
                                <td>
                                    <?php if (isset($_SESSION['user_token'])): ?>
                                        <a href="inscription_jpo.php?id=<?php echo $jpo['id']; ?>" class="btn btn-primary">S'inscrire à la JPO</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var jpos = <?php echo json_encode(array_column($jpos, 'name')); ?>;
            var searchInput = document.getElementById('search');
            var jpoOptionsContainer = document.getElementById('jpoOptions');

            // Gérer la saisie dans le champ de recherche
            searchInput.addEventListener('input', function() {
                var userInput = this.value.trim().toLowerCase();
                // Vider le container des options JPO existantes
                jpoOptionsContainer.innerHTML = '';

                // Filtrer et afficher les options correspondantes
                jpos.forEach(function(jpo) {
                    if (jpo.toLowerCase().includes(userInput)) {
                        var option = document.createElement('a');
                        option.href = 'details_jpo.php?name=' + encodeURIComponent(jpo);
                        option.classList.add('dropdown-item');
                        option.textContent = jpo;
                        jpoOptionsContainer.appendChild(option);
                    }
                });

                // Afficher les options si elles existent
                if (jpoOptionsContainer.children.length > 0) {
                    jpoOptionsContainer.classList.add('show-options');
                } else {
                    jpoOptionsContainer.classList.remove('show-options');
                }
            });

            // Gérer le clic en dehors de la zone de recherche
            document.addEventListener('click', function(event) {
                var isClickInside = searchInput.contains(event.target);
                if (!isClickInside) {
                    // Cacher les options
                    jpoOptionsContainer.classList.remove('show-options');
                }
            });
        });
    </script>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
