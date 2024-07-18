<?php
session_start();
require_once '../../config.php';
require_once '../../vendor/autoload.php';
require_once '../Class/User.php';
require_once '../Class/Jpo.php';
require_once '../Class/User_jpo.php';
require_once '../../Database.php';

// Vérifier si l'utilisateur est connecté et s'il est admin
if (!isset($_SESSION['user_token']) || !isset($_SESSION['admin_rank_id']) || $_SESSION['admin_rank_id'] <= 1) {
    header("Location: ../../index.php");
    exit;
}

// Créer une instance des classes Jpo et UserJpo
$jpoObj = new Jpo();
$userJpoObj = new UserJpo();

// Déclaration de variables pour le formulaire
$jpoId = $name = $city = $date = $about = '';
$updateMode = false; // Mode ajout par défaut

// Traitement du formulaire pour ajouter, modifier ou supprimer une JPO
$message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['create'])) {
        // Ajouter une nouvelle JPO
        $name = $_POST['name'];
        $city = $_POST['city'];
        $date = $_POST['date'];
        $about = $_POST['about'];
        
        $jpoObj->createJpo($name, $city, $date, $about);
        $message = "La JPO a été créée avec succès.";
    } elseif (isset($_POST['edit'])) {
        // Préparer la modification d'une JPO existante
        $jpoId = $_POST['jpo_id'];
        $jpo = $jpoObj->getByIdJpo($jpoId);
        if ($jpo) {
            $name = $jpo['name'];
            $city = $jpo['city'];
            $date = $jpo['date'];
            $about = $jpo['about'];
            $updateMode = true;
        } else {
            $message = "La JPO sélectionnée n'existe pas.";
        }
    } elseif (isset($_POST['update'])) {
        // Modifier une JPO existante
        $jpoId = $_POST['jpo_id'];
        $name = $_POST['name'];
        $city = $_POST['city'];
        $date = $_POST['date'];
        $about = $_POST['about'];
        
        $jpoObj->updateJpo($jpoId, $name, $city, $date, $about);
        $message = "La JPO a été modifiée avec succès.";
        $updateMode = false; // Sortir du mode de mise à jour après modification
    } elseif (isset($_POST['delete'])) {
        // Supprimer une JPO
        $jpoId = $_POST['jpo_id'];
        $jpoObj->deleteJpo($jpoId);
        $message = "La JPO a été supprimée avec succès.";
    }
}

// Récupérer toutes les JPOs
$jpos = $jpoObj->getAllJpo();

// Gestion de la demande pour obtenir les utilisateurs inscrits
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['jpo_users'])) {
    $jpoId = $_POST['jpo_users'];
    $jpoUsers = $userJpoObj->getUsersByJpoId($jpoId);
    // Pour déboguer, nous affichons les utilisateurs récupérés dans la console PHP
    error_log(print_r($jpoUsers, true));
    // Convertir les utilisateurs en JSON pour l'utiliser dans la réponse AJAX
    header('Content-Type: application/json');
    echo json_encode($jpoUsers);
    exit;
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panneau Admin</title>
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
                        <a class="nav-link text-danger" href="../index.php?logout=true">Déconnexion</a>
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
        <h2>Pannel Admin</h2>

        <!-- Affichage des JPOs existantes -->
        <div class="card mb-4">
            <div class="card-header">
                Liste des Journées Portes Ouvertes
            </div>
            <div class="card-body">
                <?php if (count($jpos) > 0): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th scope="col">Nom</th>
                                <th scope="col">Ville</th>
                                <th scope="col">Date</th>
                                <th scope="col">Description</th>
                                <th scope="col">Inscrits</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($jpos as $jpo): ?>
                                <?php $userCount = $jpoObj->getUserCountByJpoId($jpo['id']); ?>
                                <tr>
                                    <td><a href="#" class="jpo-link" data-toggle="modal" data-target="#jpoModal" data-jpo-id="<?php echo $jpo['id']; ?>"><?php echo htmlspecialchars($jpo['name']); ?></a></td>
                                    <td><?php echo htmlspecialchars($jpo['city']); ?></td>
                                    <td><?php echo htmlspecialchars($jpo['date']); ?></td>
                                    <td><?php echo htmlspecialchars($jpo['about']); ?></td>
                                    <td><?php echo $userCount; ?></td>
                                    <td>
                                        <!-- Boutons pour modifier et supprimer -->
                                        <form method="POST" style="display:inline-block;">
                                            <input type="hidden" name="jpo_id" value="<?php echo $jpo['id']; ?>">
                                            <button type="submit" name="edit" class="btn btn-primary btn-sm">Modifier</button>
                                        </form>
                                        <form method="POST" style="display:inline-block;">
                                            <input type="hidden" name="jpo_id" value="<?php echo $jpo['id']; ?>">
                                            <button type="submit" name="delete" class="btn btn-danger btn-sm">Supprimer</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>Aucune journée portes ouvertes n'a été trouvée.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Formulaire pour ajouter/modifier une JPO -->
        <div class="card">
            <div class="card-header">
                <?php echo $updateMode ? 'Modifier la JPO' : 'Ajouter une nouvelle JPO'; ?>
            </div>
            <div class="card-body">
                <?php if ($message): ?>
                    <div class="alert alert-info"><?php echo $message; ?></div>
                <?php endif; ?>
                <form method="POST">
                    <input type="hidden" name="jpo_id" value="<?php echo $jpoId; ?>">
                    <div class="form-group">
                        <label for="name">Nom de la JPO</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="city">Ville</label>
                        <input type="text" class="form-control" id="city" name="city" value="<?php echo htmlspecialchars($city); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="date">Date</label>
                        <input type="date" class="form-control" id="date" name="date" value="<?php echo htmlspecialchars($date); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="about">Description</label>
                        <textarea class="form-control" id="about" name="about" rows="3" required><?php echo htmlspecialchars($about); ?></textarea>
                    </div>
                    <button type="submit" name="<?php echo $updateMode ? 'update' : 'create'; ?>" class="btn btn-success"><?php echo $updateMode ? 'Modifier' : 'Ajouter'; ?></button>
                </form>
            </div>
        </div>

        <!-- Modal pour afficher les utilisateurs inscrits -->
        <div class="modal fade" id="jpoModal" tabindex="-1" role="dialog" aria-labelledby="jpoModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="jpoModalLabel">Utilisateurs inscrits</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div id="jpoUsersList">
                            <!-- Liste des utilisateurs sera injectée ici via AJAX -->
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery, Popper.js, Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function () {
            // Ouvrir la modal et charger les utilisateurs inscrits
            $('#jpoModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var jpoId = button.data('jpo-id');
                var modal = $(this);

                // Charger les utilisateurs inscrits
                $.ajax({
                    url: '', // Utiliser la même page pour gérer les utilisateurs inscrits
                    type: 'POST',
                    data: {jpo_users: jpoId},
                    success: function (data) {
                        try {
                            var users = JSON.parse(data);
                            var htmlContent = '';

                            if (users.length > 0) {
                                htmlContent += '<ul class="list-group">';
                                users.forEach(function (user) {
                                    htmlContent += '<li class="list-group-item">' + user.username + ' (' + user.email + ')</li>';
                                });
                                htmlContent += '</ul>';
                            } else {
                                htmlContent += '<p>Aucun utilisateur inscrit pour cette JPO.</p>';
                            }

                            modal.find('#jpoUsersList').html(htmlContent);
                        } catch (e) {
                            modal.find('#jpoUsersList').html('<p>Erreur lors de la récupération des utilisateurs inscrits.</p>');
                            console.error("Parsing error:", e);
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        modal.find('#jpoUsersList').html('<p>Erreur lors de la récupération des utilisateurs inscrits : ' + textStatus + '</p>');
                        console.error("AJAX error:", textStatus, errorThrown);
                    }
                });
            });
        });
    </script>
</body>
</html>
