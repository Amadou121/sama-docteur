<?php
// Fichier: includes/deconnexion.php
// Version simple à inclure

// Démarrer la session si nécessaire
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vider les variables de session
$_SESSION = array();

// Supprimer les cookies
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

// Détruire la session
session_destroy();

// Message flash
session_start();
$_SESSION['flash'] = [
    'type' => 'success',
    'message' => 'Déconnexion réussie. À bientôt !'
];

// Redirection
header('Location: ../index.php');
exit();
?>