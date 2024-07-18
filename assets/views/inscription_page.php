<?php
require_once '../../config.php'; 
require_once '../../vendor/autoload.php';
require_once '../Class/User.php';

use Google\Client as GoogleClient;
use Google\Service\Oauth2 as GoogleServiceOauth2;
use Facebook\Facebook;

$dotenv = Dotenv\Dotenv::createImmutable('../../');
$dotenv->load();

// var_dump(getenv(GOOGLE_CLIENT_ID));
// var_dump(getenv(GOOGLE_CLIENT_SECRET));

// Google OAuth
$googleClient = new GoogleClient();
$googleClient->setClientId(GOOGLE_CLIENT_ID);
$googleClient->setClientSecret(GOOGLE_CLIENT_SECRET);
$googleClient->setRedirectUri('http://localhost/JPO-Connect/index.php'); // Remplacez par l'URL de redirection appropriée
$googleClient->addScope('email');
$googleClient->addScope('profile');
$googleLoginUrl = $googleClient->createAuthUrl();



$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstName = $_POST['first_name'];
    $name = $_POST['name'];
    $mail = $_POST['mail'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Hash le mot de passe

    // Gestion de l'upload de la photo de profil
    if (isset($_FILES['profil_picture']) && $_FILES['profil_picture']['error'] == 0) {
        $fileTmpPath = $_FILES['profil_picture']['tmp_name'];
        $fileName = $_FILES['profil_picture']['name'];
        $fileSize = $_FILES['profil_picture']['size'];
        $fileType = $_FILES['profil_picture']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg');
        if (in_array($fileExtension, $allowedfileExtensions)) {
            $profilPicture = file_get_contents($fileTmpPath);
        } else {
            $message = "Extension de fichier non autorisée.";
        }
    } else {
        $message = "Erreur lors du téléchargement de la photo de profil.";
    }

    if ($message === '') {
        // Création d'un nouvel utilisateur
        $user = new User();
        $success = $user->createUser($firstName, $name, $mail, $password, $profilPicture, 1); // 1 est l'admin_rank_id par défaut

        if ($success) {
            $message = "Inscription réussie!";
        } else {
            $message = "Erreur lors de l'inscription.";
        }
    }
}

// Google Callback
if (isset($_GET['code']) && !isset($_GET['state'])) {
    $token = $googleClient->fetchAccessTokenWithAuthCode($_GET['code']);
    if (isset($token['error'])) {
        $message = "Erreur lors de l'authentification avec Google.";
    } else {
        $oauth2 = new Google_Service_Oauth2($googleClient);
        $userInfo = $oauth2->userinfo->get();
        $email = $userInfo->email;
        $name = $userInfo->name;
        $profilePictureUrl = $userInfo->picture;

        // Téléchargez l'image de profil et convertissez-la en format binaire
        $profilPicture = file_get_contents($profilePictureUrl);

        // Vérifiez si l'utilisateur existe déjà
        $user = new User();
        $existingUser = $user->getUserByEmail($email);

        if ($existingUser) {
            $message = "Utilisateur déjà existant. Connectez-vous avec votre compte Google.";
        } else {
            // Créez un nouvel utilisateur
            $success = $user->createUser($name, $name, $email, '', $profilPicture, 1);

            if ($success) {
                $message = "Inscription réussie avec Google!";
            } else {
                $message = "Erreur lors de l'inscription avec Google.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulaire d'Inscription</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../template/header.php' ?>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h2 class="text-center">Formulaire d'Inscription</h2>
                    </div>
                    <div class="card-body">
                        <?php if ($message) { echo "<p class='alert alert-info'>$message</p>"; } ?>
                        <form action="" method="POST" enctype="multipart/form-data">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="first_name">Prénom :</label>
                                    <input type="text" class="form-control" id="first_name" name="first_name" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="name">Nom :</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="mail">Email :</label>
                                    <input type="email" class="form-control" id="mail" name="mail" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="password">Mot de passe :</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="profil_picture">Photo de profil :</label>
                                <input type="file" class="form-control-file" id="profil_picture" name="profil_picture" accept="image/*" required>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">S'inscrire</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                    <div class="">
                        <h2 class="text-center ">OU</h2>
                    </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <a href="<?php echo $googleLoginUrl; ?>" class="btn btn-danger btn-block">S'inscrire' avec Google</a>
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
