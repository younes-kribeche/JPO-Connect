<?php
require_once __DIR__ . '/vendor/autoload.php';

// Chargement des variables d'environnement
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Définition des constantes pour les identifiants Google
define('GOOGLE_CLIENT_ID', getenv('GOOGLE_CLIENT_ID'));
define('GOOGLE_CLIENT_SECRET', getenv('GOOGLE_CLIENT_SECRET'));

var_dump(GOOGLE_CLIENT_ID);
var_dump(GOOGLE_CLIENT_SECRET);
?>
