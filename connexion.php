<?php
// Fichier: connexion.php
require_once 'includes/config.php';

if (estConnecte()) {
    switch($_SESSION['user_role']) {
        case 'patient':
            header('Location: dashboard.php');
            break;
        case 'medecin':
            header('Location: dashboard-medecin.php');
            break;
        case 'admin':
            header('Location: admin/');
            break;
    }
    exit();
}

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $mot_de_passe = $_POST['mot_de_passe'];
    
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ? AND est_actif = TRUE");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($mot_de_passe, $user['mot_de_passe'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_nom'] = $user['nom_complet'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        
        // Mettre à jour la dernière connexion
        $stmt = $pdo->prepare("UPDATE utilisateurs SET derniere_connexion = NOW() WHERE id = ?");
        $stmt->execute([$user['id']]);
        
        // Redirection selon le rôle
        switch($user['role']) {
            case 'patient':
                header('Location: dashboard.php');
                break;
            case 'medecin':
                header('Location: dashboard-medecin.php');
                break;
            case 'admin':
                header('Location: admin/');
                break;
        }
        exit();
    } else {
        $erreur = 'Email ou mot de passe incorrect';
    }
}

include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6" data-aos="fade-up">
            <div class="form-container">
                <div class="text-center mb-4">
                    <i class="fas fa-stethoscope fa-3x text-primary"></i>
                    <h2 class="mt-3">Connexion</h2>
                    <p class="text-secondary">Accédez à votre espace patient</p>
                </div>
                
                <?php if($erreur): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> <?php echo $erreur; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" onsubmit="return validerFormulaire('loginForm')" id="loginForm">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="mot_de_passe" class="form-label">Mot de passe</label>
                        <input type="password" class="form-control" id="mot_de_passe" name="mot_de_passe" required>
                        <div class="form-text">
                            <a href="#" class="text-decoration-none">Mot de passe oublié ?</a>
                        </div>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember">
                        <label class="form-check-label" for="remember">Se souvenir de moi</label>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-sign-in-alt"></i> Se connecter
                    </button>
                </form>
                
                <hr class="my-4">
                
                <div class="text-center">
                    <p class="mb-2">Pas encore de compte ?</p>
                    <a href="creer-compte.php" class="btn btn-outline-primary">
                        <i class="fas fa-user-plus"></i> Créer un compte
                    </p>
                </div>
                
                <div class="text-center mt-4">
                    <p class="text-secondary small">
                        <i class="fas fa-lock"></i> Connexion sécurisée
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>