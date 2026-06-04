<?php
// Fichier: deconnexion.php
// Emplacement: Racine du projet (à côté de index.php)

// Démarrer la session si ce n'est pas déjà fait
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclure la configuration pour accéder à la base de données
require_once 'includes/config.php';

// Fonction pour journaliser la déconnexion
function logDeconnexion($pdo, $user_id, $email) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO logs_systeme (utilisateur_id, action, details, ip_address, user_agent, date_creation) 
            VALUES (?, 'deconnexion', ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $user_id,
            "Utilisateur déconnecté: " . $email,
            $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        ]);
    } catch (PDOException $e) {
        // Ne pas bloquer la déconnexion en cas d'erreur de log
        error_log("Erreur journalisation déconnexion: " . $e->getMessage());
    }
}

// Journaliser la déconnexion si l'utilisateur est connecté
if (isset($_SESSION['user_id']) && isset($_SESSION['user_email'])) {
    logDeconnexion($pdo, $_SESSION['user_id'], $_SESSION['user_email']);
    
    // Mettre à jour la dernière connexion si nécessaire
    try {
        $stmt = $pdo->prepare("
            UPDATE utilisateurs 
            SET derniere_connexion = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$_SESSION['user_id']]);
    } catch (PDOException $e) {
        // Ignorer l'erreur
    }
}

// Supprimer le cookie "Se souvenir de moi" de la base de données
if (isset($_SESSION['user_id']) && isset($pdo)) {
    try {
        $stmt = $pdo->prepare("
            UPDATE utilisateurs 
            SET remember_token = NULL, 
                token_expires = NULL 
            WHERE id = ?
        ");
        $stmt->execute([$_SESSION['user_id']]);
    } catch (PDOException $e) {
        // Ignorer l'erreur
    }
}

// Supprimer le cookie "Se souvenir de moi" du navigateur
if (isset($_COOKIE['remember_token'])) {
    setcookie(
        'remember_token', 
        '', 
        time() - 3600,  // Expiré dans le passé
        '/',            // Chemin
        '',             // Domaine
        isset($_SERVER['HTTPS']), // Sécurisé si HTTPS
        true            // HttpOnly
    );
}

// Supprimer le cookie de session
if (isset($_COOKIE[session_name()])) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 3600,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// Vider toutes les variables de session
$_SESSION = array();

// Détruire la session
session_destroy();

// Démarrer une nouvelle session pour le message flash
session_start();
$_SESSION['flash'] = [
    'type' => 'success',
    'message' => 'Vous avez été déconnecté avec succès. Merci de votre visite !'
];

// Redirection vers la page d'accueil ou de connexion
$redirect_url = 'index.php';

// Vérifier s'il y a une URL de redirection spécifique
if (isset($_GET['redirect']) && !empty($_GET['redirect'])) {
    $allowed_redirects = ['index', 'connexion', 'accueil'];
    $redirect = $_GET['redirect'];
    if (in_array($redirect, $allowed_redirects)) {
        $redirect_url = $redirect . '.php';
    }
}

// Redirection avec un délai optionnel
if (isset($_GET['delay']) && is_numeric($_GET['delay']) && $_GET['delay'] > 0) {
    header("Refresh: {$_GET['delay']}; url=$redirect_url");
    echo '<!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="refresh" content="' . intval($_GET['delay']) . ';url=' . $redirect_url . '">
        <title>Déconnexion en cours...</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .logout-card {
                background: white;
                border-radius: 20px;
                padding: 40px;
                text-align: center;
                box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                animation: fadeIn 0.5s ease-out;
            }
            @keyframes fadeIn {
                from {
                    opacity: 0;
                    transform: translateY(-20px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            .spinner {
                width: 50px;
                height: 50px;
                border: 4px solid #f3f3f3;
                border-top: 4px solid #667eea;
                border-radius: 50%;
                animation: spin 1s linear infinite;
                margin: 20px auto;
            }
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
            .checkmark {
                width: 80px;
                height: 80px;
                margin: 0 auto 20px;
            }
            .checkmark-circle {
                stroke: #28a745;
                stroke-width: 2;
                stroke-dasharray: 166;
                stroke-dashoffset: 166;
                animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
            }
            @keyframes stroke {
                100% { stroke-dashoffset: 0; }
            }
            .checkmark-check {
                stroke: #28a745;
                stroke-width: 2;
                stroke-dasharray: 48;
                stroke-dashoffset: 48;
                animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-5">
                    <div class="logout-card">
                        <div class="checkmark">
                            <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                                <circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none"/>
                                <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                            </svg>
                        </div>
                        <h3 class="text-success mb-3">Déconnexion réussie !</h3>
                        <p class="text-secondary mb-3">Vous avez été déconnecté avec succès.</p>
                        <div class="spinner"></div>
                        <p class="text-muted mt-3 small">Redirection en cours...</p>
                        <a href="' . $redirect_url . '" class="btn btn-primary mt-3">
                            <i class="fas fa-arrow-right"></i> Cliquez ici si la redirection ne fonctionne pas
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    </body>
    </html>';
    exit();
}

// Redirection normale
header("Location: $redirect_url");
exit();
?>