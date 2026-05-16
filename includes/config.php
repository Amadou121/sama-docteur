<?php
// Fichier: includes/config.php
session_start();

// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'sama_docteur');
define('DB_USER', 'root');
define('DB_PASS', '');

// Configuration du site - Détection automatique de l'URL
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
$uri = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');

// Définir SITE_URL de manière dynamique
define('SITE_URL', $protocol . $host . $uri . '/');

// Définir le chemin absolu du dossier racine
define('BASE_PATH', dirname(__DIR__) . '/');

define('SITE_NAME', 'Sama Docteur');

// Connexion à la base de données
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch(PDOException $e) {
    die("Erreur de connexion à la base de données: " . $e->getMessage());
}

// Fonctions utilitaires
function estConnecte() {
    return isset($_SESSION['user_id']);
}

function verifierRole($role) {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] == $role;
}

function redirigerSiNonConnecte() {
    if (!estConnecte()) {
        header('Location: ' . SITE_URL . 'connexion.php');
        exit();
    }
}
?>