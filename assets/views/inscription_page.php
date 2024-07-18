    <?php
    require_once '../../config.php';
    require_once '../../Database.php';
    require_once '../Class/User.php';

    // Inclure l'autoload de Composer
    require_once '../../vendor/autoload.php';

    use Google\Client as GoogleClient;
    use Facebook\Facebook;

    // Charger les variables d'environnement depuis le fichier .env (si applicable)
    $dotenv = Dotenv\Dotenv::createImmutable('../../');
    $dotenv->load();

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
    </head>
    <body>
        <h2>Formulaire d'Inscription</h2>
        <?php if ($message) { echo "<p>$message</p>"; } ?>
        <form action="" method="POST" enctype="multipart/form-data">
            <label for="first_name">Prénom :</label>
            <input type="text" id="first_name" name="first_name" required><br><br>

            <label for="name">Nom :</label>
            <input type="text" id="name" name="name" required><br><br>

            <label for="mail">Email :</label>
            <input type="email" id="mail" name="mail" required><br><br>

            <label for="password">Mot de passe :</label>
            <input type="password" id="password" name="password" required><br><br>

            <label for="profil_picture">Photo de profil :</label>
            <input type="file" id="profil_picture" name="profil_picture" accept="image/*" required><br><br>

            <input type="submit" value="S'inscrire">
        </form>

        <h3>Ou inscrivez-vous avec :</h3>
        <a href="<?php echo $googleLoginUrl; ?>">Se connecter avec Google</a><br><br>
    </body>
    </html>
