<?php
// Fichier: creer-compte.php
require_once 'includes/config.php';

if (estConnecte()) {
    header('Location: dashboard.php');
    exit();
}

$erreur = '';
$succes = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nom_complet = $_POST['nom_complet'];
    $email = $_POST['email'];
    $telephone = $_POST['telephone'];
    $mot_de_passe = $_POST['mot_de_passe'];
    $confirm_mot_de_passe = $_POST['confirm_mot_de_passe'];
    $role = 'patient';
    
    // Validation
    if ($mot_de_passe !== $confirm_mot_de_passe) {
        $erreur = 'Les mots de passe ne correspondent pas';
    } elseif (strlen($mot_de_passe) < 6) {
        $erreur = 'Le mot de passe doit contenir au moins 6 caractères';
    } else {
        // Vérifier si l'email existe déjà
        $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $erreur = 'Cet email est déjà utilisé';
        } else {
            // Créer le compte
            $hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("
                INSERT INTO utilisateurs (nom_complet, email, telephone, mot_de_passe, role) 
                VALUES (?, ?, ?, ?, ?)
            ");
            
            if ($stmt->execute([$nom_complet, $email, $telephone, $hash, $role])) {
                $succes = 'Compte créé avec succès ! Vous pouvez maintenant vous connecter.';
                // Redirection après 2 secondes
                echo '<meta http-equiv="refresh" content="2;url=connexion.php">';
            } else {
                $erreur = 'Une erreur est survenue. Veuillez réessayer.';
            }
        }
    }
}

include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8" data-aos="fade-up">
            <div class="form-container">
                <div class="text-center mb-4">
                    <i class="fas fa-user-plus fa-3x text-primary"></i>
                    <h2 class="mt-3">Créer un compte</h2>
                    <p class="text-secondary">Rejoignez Sama Docteur et prenez soin de votre santé</p>
                </div>
                
                <?php if($erreur): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> <?php echo $erreur; ?>
                    </div>
                <?php endif; ?>
                
                <?php if($succes): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo $succes; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" onsubmit="return validerFormulaire('registerForm')" id="registerForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nom_complet" class="form-label">Nom complet *</label>
                            <input type="text" class="form-control" id="nom_complet" name="nom_complet" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="telephone" class="form-label">Téléphone *</label>
                            <input type="tel" class="form-control" id="telephone" name="telephone" placeholder="77 123 45 67" required>
                            <div class="form-text">Format: 77 123 45 67</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="role" class="form-label">Je suis</label>
                            <select class="form-control" id="role" name="role" disabled>
                                <option value="patient">Patient</option>
                            </select>
                            <div class="form-text">Pour devenir médecin, veuillez nous contacter</div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="mot_de_passe" class="form-label">Mot de passe *</label>
                            <input type="password" class="form-control" id="mot_de_passe" name="mot_de_passe" required>
                            <div class="form-text">Minimum 6 caractères</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="confirm_mot_de_passe" class="form-label">Confirmer le mot de passe *</label>
                            <input type="password" class="form-control" id="confirm_mot_de_passe" name="confirm_mot_de_passe" required>
                        </div>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="terms" required>
                        <label class="form-check-label" for="terms">
                            J'accepte les <a href="#" class="text-decoration-none">conditions d'utilisation</a> et la <a href="#" class="text-decoration-none">politique de confidentialité</a>
                        </label>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-user-plus"></i> Créer mon compte
                    </button>
                </form>
                
                <hr class="my-4">
                
                <div class="text-center">
                    <p class="mb-0">Déjà inscrit ?</p>
                    <a href="connexion.php" class="btn btn-outline-primary mt-2">
                        <i class="fas fa-sign-in-alt"></i> Se connecter
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>