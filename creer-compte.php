<?php
// Fichier: creer-compte.php
require_once 'includes/config.php';

if (estConnecte()) {
    header('Location: dashboard.php');
    exit();
}

$erreur = '';
$succes = '';

// Conservation des valeurs saisies
$nom_complet_value = '';
$email_value = '';
$telephone_value = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Nettoyage des entrées
    $nom_complet = trim(htmlspecialchars($_POST['nom_complet'] ?? ''));
    $email = trim(filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL));
    $telephone = trim(preg_replace('/[^0-9]/', '', $_POST['telephone'] ?? '')); // Garder uniquement les chiffres
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';
    $confirm_mot_de_passe = $_POST['confirm_mot_de_passe'] ?? '';
    $role = 'patient';
    
    // Conservation pour réaffichage
    $nom_complet_value = $nom_complet;
    $email_value = $email;
    $telephone_value = $_POST['telephone'] ?? '';
    
    // Validation du nom
    if (empty($nom_complet)) {
        $erreur = 'Veuillez entrer votre nom complet';
    }
    // Validation de l'email
    elseif (empty($email)) {
        $erreur = 'Veuillez entrer votre adresse email';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreur = 'Adresse email invalide';
    }
    // Validation du téléphone (Sénégal)
    elseif (empty($telephone)) {
        $erreur = 'Veuillez entrer votre numéro de téléphone';
    } elseif (!preg_match('/^(77|78|76|70)[0-9]{7}$/', $telephone)) {
        $erreur = 'Numéro de téléphone invalide. Utilisez un numéro sénégalais (77, 78, 76 ou 70 suivi de 7 chiffres)';
    }
    // Validation du mot de passe
    elseif (strlen($mot_de_passe) < 6) {
        $erreur = 'Le mot de passe doit contenir au moins 6 caractères';
    } elseif ($mot_de_passe !== $confirm_mot_de_passe) {
        $erreur = 'Les mots de passe ne correspondent pas';
    } else {
        // Vérifier si l'email existe déjà
        $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $erreur = 'Cet email est déjà utilisé. <a href="connexion.php" class="alert-link">Connectez-vous</a> ou <a href="mot-passe-oublie.php" class="alert-link">réinitialisez votre mot de passe</a>.';
        } else {
            // Vérifier si le téléphone existe déjà
            $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE telephone = ?");
            $stmt->execute([$telephone]);
            if ($stmt->fetch()) {
                $erreur = 'Ce numéro de téléphone est déjà utilisé.';
            } else {
                // Créer le compte
                $hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);
                
                try {
                    $stmt = $pdo->prepare("
                        INSERT INTO utilisateurs (nom_complet, email, telephone, mot_de_passe, role, date_inscription, est_actif) 
                        VALUES (?, ?, ?, ?, ?, NOW(), 1)
                    ");
                    
                    if ($stmt->execute([$nom_complet, $email, $telephone, $hash, $role])) {
                        $user_id = $pdo->lastInsertId();
                        
                        // Créer une notification de bienvenue
                        $stmt = $pdo->prepare("
                            INSERT INTO notifications (utilisateur_id, titre, message, type, date_creation) 
                            VALUES (?, 'Bienvenue sur Sama Docteur', 'Votre compte a été créé avec succès. Connectez-vous pour prendre rendez-vous avec nos médecins.', 'info', NOW())
                        ");
                        $stmt->execute([$user_id]);
                        
                        $_SESSION['flash'] = [
                            'type' => 'success',
                            'message' => 'Compte créé avec succès ! Vous pouvez maintenant vous connecter.'
                        ];
                        
                        header('Location: connexion.php');
                        exit();
                    } else {
                        $erreur = 'Une erreur est survenue lors de la création du compte. Veuillez réessayer.';
                    }
                } catch (PDOException $e) {
                    error_log("Erreur création compte: " . $e->getMessage());
                    $erreur = 'Une erreur technique est survenue. Veuillez réessayer plus tard.';
                }
            }
        }
    }
}

include 'includes/header.php';
?>

<style>
.form-container {
    background: white;
    border-radius: 20px;
    padding: 40px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.08);
}

.form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102,126,234,0.25);
}

.btn-primary {
    background: linear-gradient(135deg, #667eea, #764ba2);
    border: none;
    padding: 12px;
    font-weight: 600;
    transition: transform 0.3s, box-shadow 0.3s;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(102,126,234,0.4);
}

.btn-outline-primary {
    border-radius: 25px;
    padding: 8px 25px;
    transition: all 0.3s;
}

.btn-outline-primary:hover {
    transform: translateY(-2px);
}

.password-strength {
    margin-top: 8px;
    height: 5px;
    border-radius: 3px;
    transition: all 0.3s;
}

.password-strength-weak { background: #dc3545; width: 33%; }
.password-strength-medium { background: #ffc107; width: 66%; }
.password-strength-strong { background: #28a745; width: 100%; }

.phone-preview {
    font-size: 0.85rem;
    margin-top: 5px;
}

.phone-preview.valid {
    color: #28a745;
}

.phone-preview.invalid {
    color: #dc3545;
}

.input-group-text {
    background: #f8f9fa;
    border-right: none;
}

.input-group .form-control {
    border-left: none;
}

.input-group .form-control:focus {
    border-color: #dee2e6;
    border-left-color: #dee2e6;
}
</style>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-7" data-aos="fade-up">
            <div class="form-container">
                <div class="text-center mb-4">
                    <div class="icon-circle bg-primary bg-opacity-10 d-inline-flex p-3 rounded-circle mb-3">
                        <i class="fas fa-user-plus fa-3x text-primary"></i>
                    </div>
                    <h2 class="mt-2">Créer un compte</h2>
                    <p class="text-secondary">Rejoignez Sama Docteur et prenez soin de votre santé</p>
                </div>
                
                <?php if($erreur): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i> 
                        <?php echo $erreur; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" onsubmit="return validerFormulaireInscription(event)" id="registerForm" novalidate>
                    <!-- Nom complet -->
                    <div class="mb-3">
                        <label for="nom_complet" class="form-label fw-semibold">
                            <i class="fas fa-user text-primary me-1"></i> Nom complet <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fas fa-user text-muted"></i>
                            </span>
                            <input type="text" 
                                   class="form-control border-start-0" 
                                   id="nom_complet" 
                                   name="nom_complet" 
                                   value="<?php echo htmlspecialchars($nom_complet_value); ?>"
                                   placeholder="Mamadou Diop"
                                   required>
                        </div>
                        <div class="invalid-feedback">Veuillez entrer votre nom complet</div>
                    </div>
                    
                    <!-- Email -->
                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">
                            <i class="fas fa-envelope text-primary me-1"></i> Email <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fas fa-envelope text-muted"></i>
                            </span>
                            <input type="email" 
                                   class="form-control border-start-0" 
                                   id="email" 
                                   name="email" 
                                   value="<?php echo htmlspecialchars($email_value); ?>"
                                   placeholder="exemple@email.com"
                                   required>
                        </div>
                        <div class="form-text">Nous n'enverrons jamais de spam à votre adresse email</div>
                        <div class="invalid-feedback">Veuillez entrer une adresse email valide</div>
                    </div>
                    
                    <!-- Téléphone avec validation en temps réel -->
                    <div class="mb-3">
                        <label for="telephone" class="form-label fw-semibold">
                            <i class="fas fa-phone-alt text-primary me-1"></i> Téléphone <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fas fa-phone-alt text-muted"></i>
                            </span>
                            <input type="tel" 
                                   class="form-control border-start-0" 
                                   id="telephone" 
                                   name="telephone" 
                                   value="<?php echo htmlspecialchars($telephone_value); ?>"
                                   placeholder="77 123 45 67"
                                   required>
                        </div>
                        <div id="phoneHelp" class="form-text">
                            <i class="fas fa-info-circle"></i> Format: 77 123 45 67 (Orange, Expresso, Free, Tigo)
                        </div>
                        <div id="phonePreview" class="phone-preview"></div>
                        <div class="invalid-feedback">Numéro invalide (ex: 77 123 45 67)</div>
                    </div>
                    
                    <!-- Rôle -->
                    <div class="mb-3">
                        <label for="role" class="form-label fw-semibold">
                            <i class="fas fa-user-tag text-primary me-1"></i> Je suis
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fas fa-user text-muted"></i>
                            </span>
                            <select class="form-select border-start-0" id="role" name="role" disabled>
                                <option value="patient" selected>👤 Patient</option>
                            </select>
                        </div>
                        <div class="form-text text-primary">
                            <i class="fas fa-stethoscope"></i> 
                            Vous êtes médecin ? <a href="contact.php" class="text-decoration-none">Contactez-nous</a> pour créer un compte professionnel
                        </div>
                    </div>
                    
                    <!-- Mot de passe -->
                    <div class="mb-3">
                        <label for="mot_de_passe" class="form-label fw-semibold">
                            <i class="fas fa-lock text-primary me-1"></i> Mot de passe <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fas fa-key text-muted"></i>
                            </span>
                            <input type="password" 
                                   class="form-control border-start-0" 
                                   id="mot_de_passe" 
                                   name="mot_de_passe" 
                                   placeholder="••••••••"
                                   required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="password-strength" id="passwordStrength"></div>
                        <div id="passwordHelp" class="form-text">Minimum 6 caractères avec lettres et chiffres</div>
                        <div class="invalid-feedback">Le mot de passe doit contenir au moins 6 caractères</div>
                    </div>
                    
                    <!-- Confirmation mot de passe -->
                    <div class="mb-3">
                        <label for="confirm_mot_de_passe" class="form-label fw-semibold">
                            <i class="fas fa-check-circle text-primary me-1"></i> Confirmer le mot de passe <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fas fa-check text-muted"></i>
                            </span>
                            <input type="password" 
                                   class="form-control border-start-0" 
                                   id="confirm_mot_de_passe" 
                                   name="confirm_mot_de_passe" 
                                   placeholder="••••••••"
                                   required>
                        </div>
                        <div id="passwordMatch" class="form-text"></div>
                        <div class="invalid-feedback">Les mots de passe ne correspondent pas</div>
                    </div>
                    
                    <!-- Conditions générales -->
                    <div class="mb-4 form-check">
                        <input type="checkbox" class="form-check-input" id="terms" required>
                        <label class="form-check-label" for="terms">
                            J'accepte les <a href="conditions.php" target="_blank" class="text-decoration-none">conditions d'utilisation</a> 
                            et la <a href="confidentialite.php" target="_blank" class="text-decoration-none">politique de confidentialité</a>
                            <span class="text-danger">*</span>
                        </label>
                        <div class="invalid-feedback">Vous devez accepter les conditions</div>
                    </div>
                    
                    <!-- Newsletter optionnel -->
                    <div class="mb-4 form-check">
                        <input type="checkbox" class="form-check-input" id="newsletter" name="newsletter">
                        <label class="form-check-label" for="newsletter">
                            <i class="fas fa-newspaper text-primary"></i> Je souhaite recevoir des actualités et conseils santé
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100" id="submitBtn">
                        <i class="fas fa-user-plus me-2"></i> Créer mon compte
                    </button>
                </form>
                
                <div class="text-center mt-4">
                    <p class="text-muted mb-2">- ou -</p>
                    <p class="mb-0">Déjà inscrit ?</p>
                    <a href="connexion.php" class="btn btn-outline-primary mt-2">
                        <i class="fas fa-sign-in-alt me-2"></i> Se connecter
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Validation en temps réel du numéro de téléphone
const phoneInput = document.getElementById('telephone');
const phonePreview = document.getElementById('phonePreview');

function formatPhoneNumber(value) {
    let cleaned = value.replace(/\D/g, '');
    if (cleaned.length >= 2) {
        let formatted = cleaned.substring(0, 2);
        if (cleaned.length >= 5) {
            formatted += ' ' + cleaned.substring(2, 5);
        }
        if (cleaned.length >= 7) {
            formatted += ' ' + cleaned.substring(5, 7);
        }
        if (cleaned.length >= 9) {
            formatted += ' ' + cleaned.substring(7, 9);
        }
        return formatted;
    }
    return cleaned;
}

function validatePhoneNumber(phone) {
    const phoneClean = phone.replace(/\s/g, '');
    const phoneRegex = /^(77|78|76|70)[0-9]{7}$/;
    return phoneRegex.test(phoneClean);
}

phoneInput.addEventListener('input', function(e) {
    let cursorPos = e.target.selectionStart;
    let oldLength = this.value.length;
    let formatted = formatPhoneNumber(this.value);
    this.value = formatted;
    
    let newLength = this.value.length;
    cursorPos += newLength - oldLength;
    this.setSelectionRange(cursorPos, cursorPos);
    
    // Validation
    const isValid = validatePhoneNumber(this.value);
    if (isValid) {
        this.classList.remove('is-invalid');
        this.classList.add('is-valid');
        phonePreview.innerHTML = '<i class="fas fa-check-circle text-success"></i> Numéro valide';
        phonePreview.classList.add('valid');
        phonePreview.classList.remove('invalid');
    } else if (this.value.length > 0) {
        this.classList.add('is-invalid');
        this.classList.remove('is-valid');
        phonePreview.innerHTML = '<i class="fas fa-exclamation-circle text-danger"></i> Numéro invalide. Format attendu: 77 123 45 67';
        phonePreview.classList.add('invalid');
        phonePreview.classList.remove('valid');
    } else {
        this.classList.remove('is-invalid', 'is-valid');
        phonePreview.innerHTML = '';
    }
});

// Indicateur de force du mot de passe
const passwordInput = document.getElementById('mot_de_passe');
const passwordStrength = document.getElementById('passwordStrength');

passwordInput.addEventListener('input', function() {
    const password = this.value;
    let strength = 0;
    let strengthClass = '';
    
    if (password.length >= 6) strength++;
    if (password.length >= 8) strength++;
    if (password.match(/[a-z]/)) strength++;
    if (password.match(/[A-Z]/)) strength++;
    if (password.match(/[0-9]/)) strength++;
    if (password.match(/[^a-zA-Z0-9]/)) strength++;
    
    if (strength <= 2) {
        strengthClass = 'password-strength-weak';
        document.getElementById('passwordHelp').innerHTML = '<span class="text-danger"><i class="fas fa-exclamation-circle"></i> Mot de passe faible</span>';
    } else if (strength <= 4) {
        strengthClass = 'password-strength-medium';
        document.getElementById('passwordHelp').innerHTML = '<span class="text-warning"><i class="fas fa-chart-line"></i> Mot de passe moyen</span>';
    } else {
        strengthClass = 'password-strength-strong';
        document.getElementById('passwordHelp').innerHTML = '<span class="text-success"><i class="fas fa-check-circle"></i> Mot de passe fort</span>';
    }
    
    passwordStrength.className = 'password-strength ' + strengthClass;
    
    // Vérifier la confirmation
    checkPasswordMatch();
});

// Vérification confirmation mot de passe
const confirmPassword = document.getElementById('confirm_mot_de_passe');
const passwordMatch = document.getElementById('passwordMatch');

function checkPasswordMatch() {
    const password = passwordInput.value;
    const confirm = confirmPassword.value;
    
    if (confirm.length > 0) {
        if (password === confirm) {
            confirmPassword.classList.remove('is-invalid');
            confirmPassword.classList.add('is-valid');
            passwordMatch.innerHTML = '<i class="fas fa-check-circle text-success"></i> Les mots de passe correspondent';
            passwordMatch.classList.add('text-success');
        } else {
            confirmPassword.classList.add('is-invalid');
            confirmPassword.classList.remove('is-valid');
            passwordMatch.innerHTML = '<i class="fas fa-times-circle text-danger"></i> Les mots de passe ne correspondent pas';
            passwordMatch.classList.add('text-danger');
        }
    } else {
        confirmPassword.classList.remove('is-invalid', 'is-valid');
        passwordMatch.innerHTML = '';
    }
}

confirmPassword.addEventListener('input', checkPasswordMatch);

// Afficher/masquer mot de passe
document.getElementById('togglePassword')?.addEventListener('click', function() {
    const passwordInput = document.getElementById('mot_de_passe');
    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordInput.setAttribute('type', type);
    this.querySelector('i').classList.toggle('fa-eye');
    this.querySelector('i').classList.toggle('fa-eye-slash');
});

// Validation du formulaire complète
function validerFormulaireInscription(event) {
    const form = document.getElementById('registerForm');
    
    // Vérifier le nom
    const nom = document.getElementById('nom_complet');
    if (!nom.value.trim()) {
        nom.classList.add('is-invalid');
        showNotification('Veuillez entrer votre nom complet', 'warning');
        nom.focus();
        event.preventDefault();
        return false;
    }
    
    // Vérifier l'email
    const email = document.getElementById('email');
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!email.value || !emailRegex.test(email.value)) {
        email.classList.add('is-invalid');
        showNotification('Veuillez entrer une adresse email valide', 'warning');
        email.focus();
        event.preventDefault();
        return false;
    }
    
    // Vérifier le téléphone
    const telephone = document.getElementById('telephone');
    const phoneValid = validatePhoneNumber(telephone.value);
    if (!telephone.value || !phoneValid) {
        telephone.classList.add('is-invalid');
        showNotification('Veuillez entrer un numéro de téléphone valide (77 123 45 67)', 'warning');
        telephone.focus();
        event.preventDefault();
        return false;
    }
    
    // Vérifier le mot de passe
    const password = passwordInput.value;
    if (!password || password.length < 6) {
        passwordInput.classList.add('is-invalid');
        showNotification('Le mot de passe doit contenir au moins 6 caractères', 'warning');
        passwordInput.focus();
        event.preventDefault();
        return false;
    }
    
    // Vérifier la confirmation
    const confirm = confirmPassword.value;
    if (password !== confirm) {
        confirmPassword.classList.add('is-invalid');
        showNotification('Les mots de passe ne correspondent pas', 'warning');
        confirmPassword.focus();
        event.preventDefault();
        return false;
    }
    
    // Vérifier les conditions
    const terms = document.getElementById('terms');
    if (!terms.checked) {
        showNotification('Veuillez accepter les conditions d\'utilisation', 'warning');
        terms.focus();
        event.preventDefault();
        return false;
    }
    
    // Désactiver le bouton pour éviter double soumission
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Création en cours...';
    
    return true;
}

// Fonction de notification
function showNotification(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type === 'warning' ? 'warning' : (type === 'error' ? 'danger' : 'info')} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3`;
    alertDiv.style.zIndex = '9999';
    alertDiv.style.minWidth = '300px';
    alertDiv.innerHTML = `
        <i class="fas ${type === 'warning' ? 'fa-exclamation-triangle' : (type === 'error' ? 'fa-times-circle' : 'fa-info-circle')} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(alertDiv);
    
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}

// Conservation des valeurs après erreur
document.addEventListener('DOMContentLoaded', function() {
    // Déclencher la validation du téléphone si valeur existante
    if (phoneInput.value) {
        const event = new Event('input');
        phoneInput.dispatchEvent(event);
    }
});
</script>

<?php include 'includes/footer.php'; ?>