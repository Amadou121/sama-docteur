<?php
// Fichier: connexion.php
require_once 'includes/config.php';

if (estConnecte()) {
    switch($_SESSION['user_role']) {
        case 'patient':
            header('Location: index.php');
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
$success_message = '';

// Vérifier si l'utilisateur vient de s'inscrire
if (isset($_GET['inscription']) && $_GET['inscription'] == 'success') {
    $success_message = 'Inscription réussie ! Veuillez vous connecter.';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $mot_de_passe = $_POST['mot_de_passe'];
    $remember = isset($_POST['remember']) ? true : false;
    
    // Validation des champs
    if (empty($email) || empty($mot_de_passe)) {
        $erreur = 'Veuillez remplir tous les champs.';
    } else {
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
            $erreur = 'Email ou mot de passe incorrect. Veuillez réessayer.';
        }
    }
}

include 'includes/header.php';
?>

<style>
    .auth-container {
        min-height: calc(100vh - 200px);
        background: linear-gradient(135deg, #f5f7fa 0%, #e8edf2 100%);
        position: relative;
        overflow-x: hidden;
    }
    
    .auth-card {
        background: white;
        border-radius: 24px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.08), 0 4px 12px rgba(0,0,0,0.02);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        backdrop-filter: blur(10px);
    }
    
    .auth-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 30px 60px rgba(0,0,0,0.12);
    }
    
    .auth-header {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        border-radius: 24px 24px 0 0;
        padding: 2rem;
        text-align: center;
        color: white;
        position: relative;
        overflow: hidden;
    }
    
    .auth-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%);
        transform: rotate(45deg);
    }
    
    .auth-header h2 {
        font-size: 1.75rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        position: relative;
        z-index: 1;
    }
    
    .auth-body {
        padding: 2rem;
    }
    
    .form-floating label {
        color: #6c757d;
        font-weight: 500;
    }
    
    .form-floating .form-control {
        border-radius: 12px;
        border: 2px solid #e9ecef;
        transition: all 0.3s ease;
    }
    
    .form-floating .form-control:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.1);
    }
    
    .btn-auth {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        border: none;
        border-radius: 12px;
        padding: 12px 24px;
        font-weight: 600;
        font-size: 1rem;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        color: white;
    }
    
    .btn-auth:hover {
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(37, 99, 235, 0.3);
    }
    
    .btn-auth:active {
        transform: translateY(0px);
    }
    
    .btn-outline-auth {
        border-radius: 12px;
        padding: 10px 20px;
        font-weight: 600;
        border: 2px solid #e9ecef;
        transition: all 0.3s ease;
        color: #2563eb;
        background: white;
    }
    
    .btn-outline-auth:hover {
        border-color: #2563eb;
        background: #f8f9fa;
        color: #2563eb;
        transform: translateY(-2px);
    }
    
    .social-login {
        border-top: 1px solid #e9ecef;
        padding-top: 1.5rem;
    }
    
    .alert-modern {
        border-radius: 12px;
        border: none;
        padding: 1rem;
    }
    
    .alert-modern i {
        margin-right: 0.5rem;
    }
    
    .checkbox-wrapper {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        cursor: pointer;
    }
    
    .checkbox-wrapper input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
        accent-color: #2563eb;
    }
    
    .floating-shapes {
        position: absolute;
        width: 100%;
        height: 100%;
        overflow: hidden;
        z-index: 0;
    }
    
    .shape {
        position: absolute;
        opacity: 0.1;
        animation: float 20s infinite ease-in-out;
    }
    
    @keyframes float {
        0%, 100% { transform: translateY(0px) rotate(0deg); }
        50% { transform: translateY(-20px) rotate(10deg); }
    }
    
    @media (max-width: 768px) {
        .auth-body {
            padding: 1.5rem;
        }
        
        .auth-header {
            padding: 1.5rem;
        }
    }
</style>

<div class="auth-container">
    <div class="floating-shapes">
        <div class="shape" style="top: 10%; left: 5%; animation-delay: 0s;">
            <i class="fas fa-stethoscope fa-4x text-primary"></i>
        </div>
        <div class="shape" style="bottom: 15%; right: 8%; animation-delay: 5s;">
            <i class="fas fa-heartbeat fa-3x text-danger"></i>
        </div>
        <div class="shape" style="top: 20%; right: 15%; animation-delay: 3s;">
            <i class="fas fa-ambulance fa-3x text-info"></i>
        </div>
        <div class="shape" style="bottom: 25%; left: 10%; animation-delay: 7s;">
            <i class="fas fa-user-md fa-3x text-success"></i>
        </div>
    </div>
    
    <div class="container">
        <div class="row min-vh-100 align-items-center justify-content-center py-5">
            <div class="col-lg-6 col-md-8" data-aos="fade-up" data-aos-duration="800">
                <div class="auth-card">
                    <div class="auth-header">
                        <i class="fas fa-stethoscope fa-2x mb-3" style="position: relative; z-index: 1; display: block;"></i>
                        <h2>Bienvenue sur Sama Docteur</h2>
                        <p class="mb-0 opacity-90">Connectez-vous pour accéder à votre espace santé</p>
                    </div>
                    
                    <div class="auth-body">
                        <?php if($erreur): ?>
                            <div class="alert alert-modern alert-danger mb-4" style="background: #f8d7da; color: #721c24;">
                                <i class="fas fa-exclamation-triangle"></i> 
                                <?php echo htmlspecialchars($erreur); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if($success_message): ?>
                            <div class="alert alert-modern alert-success mb-4" style="background: #d4edda; color: #155724;">
                                <i class="fas fa-check-circle"></i> 
                                <?php echo htmlspecialchars($success_message); ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="" onsubmit="return validerFormulaire('loginForm')" id="loginForm">
                            <div class="form-floating mb-3">
                                <input type="email" class="form-control" id="email" name="email" placeholder="nom@exemple.com" required 
                                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                                <label for="email">
                                    <i class="fas fa-envelope me-2"></i>Adresse email
                                </label>
                            </div>
                            
                            <div class="form-floating mb-3 position-relative">
                                <input type="password" class="form-control" id="mot_de_passe" name="mot_de_passe" placeholder="Mot de passe" required>
                                <label for="mot_de_passe">
                                    <i class="fas fa-lock me-2"></i>Mot de passe
                                </label>
                                <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y me-3" 
                                        onclick="togglePassword('mot_de_passe')" style="z-index: 10;">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <label class="checkbox-wrapper">
                                    <input type="checkbox" name="remember" id="remember" 
                                           <?php echo isset($_POST['remember']) ? 'checked' : ''; ?>>
                                    <span class="text-secondary">Se souvenir de moi</span>
                                </label>
                                <a href="#" class="text-decoration-none small" style="color: #2563eb;">
                                    <i class="fas fa-key"></i> Mot de passe oublié ?
                                </a>
                            </div>
                            
                            <button type="submit" class="btn btn-auth w-100 mb-3">
                                <i class="fas fa-sign-in-alt me-2"></i>Se connecter
                            </button>
                        </form>
                        
                        <div class="social-login text-center">
                            <p class="text-secondary small mb-3">Ou connectez-vous avec</p>
                            <div class="d-flex justify-content-center gap-3">
                                <a href="#" class="btn btn-outline-auth">
                                    <i class="fab fa-google"></i>
                                </a>
                                <a href="#" class="btn btn-outline-auth">
                                    <i class="fab fa-facebook-f"></i>
                                </a>
                                <a href="#" class="btn btn-outline-auth">
                                    <i class="fab fa-apple"></i>
                                </a>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <div class="text-center">
                            <p class="text-secondary mb-3">Pas encore de compte ?</p>
                            <a href="creer-compte.php" class="btn btn-outline-auth w-100">
                                <i class="fas fa-user-plus me-2"></i>Créer un compte gratuitement
                            </a>
                        </div>
                        
                        <div class="text-center mt-4">
                            <p class="text-secondary small">
                                <i class="fas fa-shield-alt me-1"></i> 
                                Connexion sécurisée SSL - Vos données sont protégées
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const type = field.getAttribute('type') === 'password' ? 'text' : 'password';
    field.setAttribute('type', type);
    const icon = event.currentTarget.querySelector('i');
    icon.classList.toggle('fa-eye');
    icon.classList.toggle('fa-eye-slash');
}

function validerFormulaire(formId) {
    const form = document.getElementById(formId);
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('mot_de_passe').value.trim();
    
    if (!email || !password) {
        showNotification('Veuillez remplir tous les champs', 'error');
        return false;
    }
    
    if (!validateEmail(email)) {
        showNotification('Veuillez entrer une adresse email valide', 'error');
        return false;
    }
    
    return true;
}

function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function showNotification(message, type) {
    // Créer une notification flottante
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'error' ? 'danger' : 'success'} position-fixed top-0 start-50 translate-middle-x mt-3`;
    notification.style.zIndex = '9999';
    notification.style.minWidth = '300px';
    notification.innerHTML = `<i class="fas fa-${type === 'error' ? 'exclamation-triangle' : 'check-circle'} me-2"></i>${message}`;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Animation des champs au chargement
document.addEventListener('DOMContentLoaded', function() {
    const inputs = document.querySelectorAll('.form-floating .form-control');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        input.addEventListener('blur', function() {
            if (!this.value) {
                this.parentElement.classList.remove('focused');
            }
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>