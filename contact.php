<?php
// Fichier: contact.php
require_once 'includes/config.php';
include 'includes/header.php';

$message_envoye = false;
$erreur = '';
$nom = $email = $message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nom = trim(htmlspecialchars($_POST['nom'] ?? ''));
    $email = trim(htmlspecialchars($_POST['email'] ?? ''));
    $message = trim(htmlspecialchars($_POST['message'] ?? ''));
    
    if (empty($nom) || empty($email) || empty($message)) {
        $erreur = 'Veuillez remplir tous les champs';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreur = 'Email invalide';
    } elseif (strlen($message) < 10) {
        $erreur = 'Le message doit contenir au moins 10 caractères';
    } else {
        $message_envoye = true;
        $nom = $email = $message = '';
    }
}
?>

<style>
.contact-page {
    background: linear-gradient(135deg, #f5f7fa 0%, #eef2ff 100%);
    min-height: 100vh;
}

.contact-header {
    text-align: center;
    margin-bottom: 3rem;
}

.contact-header h1 {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 1rem;
    background: linear-gradient(135deg, #2563EB 0%, #1e40af 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.contact-header p {
    color: #4b5563;
    font-size: 1.1rem;
    font-weight: 400;
}

.contact-form-wrapper {
    background: white;
    border-radius: 24px;
    box-shadow: 0 20px 40px rgba(37,99,235,0.08), 0 5px 15px rgba(37,99,235,0.05);
    padding: 2rem;
    max-width: 600px;
    margin: 0 auto;
    position: relative;
    overflow: hidden;
}

.contact-form-wrapper::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #2563EB, #60a5fa, #2563EB);
}

.form-control-custom {
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 12px 18px;
    transition: all 0.3s ease;
    font-size: 0.95rem;
    background-color: #fafbff;
}

.form-control-custom:focus {
    border-color: #2563EB;
    box-shadow: 0 0 0 4px rgba(37,99,235,0.1);
    outline: none;
    background-color: white;
}

.form-control-custom:hover:not(:focus) {
    border-color: #93c5fd;
    background-color: #ffffff;
}

label {
    color: #2563EB;
    font-weight: 600;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
    letter-spacing: 0.3px;
}

.btn-submit {
    background: linear-gradient(135deg, #2563EB 0%, #1e40af 100%);
    color: white;
    border: none;
    border-radius: 16px;
    padding: 14px;
    font-weight: 600;
    font-size: 1rem;
    width: 100%;
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 1px;
    position: relative;
    overflow: hidden;
}

.btn-submit::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s ease;
}

.btn-submit:hover::before {
    left: 100%;
}

.btn-submit:hover {
    background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(37,99,235,0.3);
}

.btn-submit:active {
    transform: translateY(0);
}

.alert {
    border-radius: 16px;
    border: none;
    padding: 1rem;
}

.alert-success {
    background-color: #d1fae5;
    color: #065f46;
    border-left: 4px solid #10b981;
}

.alert-danger {
    background-color: #fee2e2;
    color: #991b1b;
    border-left: 4px solid #ef4444;
}

.map-container {
    position: relative;
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 20px 40px rgba(37,99,235,0.1);
}

.map-container::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #2563EB, #60a5fa, #2563EB);
}

@media (max-width: 768px) {
    .contact-header h1 {
        font-size: 2rem;
    }
    
    .contact-form-wrapper {
        padding: 1.5rem;
        margin: 0 1rem;
    }
    
    .btn-submit {
        padding: 12px;
        font-size: 0.9rem;
    }
}

/* Animation d'apparition des champs */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.contact-form-wrapper .mb-4 {
    animation: fadeInUp 0.5s ease forwards;
    opacity: 0;
}

.contact-form-wrapper .mb-4:nth-child(1) { animation-delay: 0.1s; }
.contact-form-wrapper .mb-4:nth-child(2) { animation-delay: 0.2s; }
.contact-form-wrapper .mb-4:nth-child(3) { animation-delay: 0.3s; }
.contact-form-wrapper button { animation-delay: 0.4s; }

/* Icônes décoratives */
.contact-form-wrapper::after {
    content: '⚕️';
    position: absolute;
    bottom: 20px;
    right: 20px;
    font-size: 60px;
    opacity: 0.03;
    pointer-events: none;
    transform: rotate(-15deg);
}

/* Style pour le champ invalide */
.was-validated .form-control-custom:invalid,
.form-control-custom.is-invalid {
    border-color: #ef4444;
    box-shadow: 0 0 0 3px rgba(239,68,68,0.1);
}

.invalid-feedback {
    color: #ef4444;
    font-size: 0.8rem;
    margin-top: 0.25rem;
}
</style>

<div class="contact-page">
    <div class="container py-5">
        <!-- En-tête -->
        <div class="contact-header" data-aos="fade-up">
            <h1>Contactez-nous</h1>
            <p>Une question ? Un besoin médical ? Notre équipe vous répond sous 24h</p>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-lg-6" data-aos="fade-up">
                <div class="contact-form-wrapper">
                    <?php if ($message_envoye): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i> Votre message a été envoyé avec succès. Notre équipe médicale vous répondra dans les plus brefs délais.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($erreur): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i> <?php echo htmlspecialchars($erreur); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" id="contactForm" novalidate>
                        <div class="mb-4">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope me-2"></i> Adresse Email
                            </label>
                            <input type="email" class="form-control form-control-custom" id="email" name="email" placeholder="exemple@email.com" value="<?php echo htmlspecialchars($email); ?>" required>
                            <div class="invalid-feedback">Veuillez entrer une adresse email valide.</div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="nom" class="form-label">
                                <i class="fas fa-user me-2"></i> Nom complet
                            </label>
                            <input type="text" class="form-control form-control-custom" id="nom" name="nom" placeholder="Docteur ou Patient" value="<?php echo htmlspecialchars($nom); ?>" required>
                            <div class="invalid-feedback">Veuillez entrer votre nom.</div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="message" class="form-label">
                                <i class="fas fa-comment-medical me-2"></i> Votre message
                            </label>
                            <textarea class="form-control form-control-custom" id="message" name="message" rows="5" placeholder="Décrivez votre demande médicale..." required minlength="10"><?php echo htmlspecialchars($message); ?></textarea>
                            <div class="invalid-feedback">Le message doit contenir au moins 10 caractères.</div>
                        </div>
                        
                        <button type="submit" class="btn-submit" id="submitBtn">
                            <i class="fas fa-paper-plane me-2"></i> ENVOYER MA DEMANDE
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Google Maps -->
        <div class="mt-5" data-aos="fade-up">
            <div class="map-container" style="max-width: 800px; margin: 0 auto;">
                <iframe 
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d61429.47673229938!2d-16.518694990382948!3d16.01769551241829!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xec256c9b9dfa4b3%3A0xead10ba233d5e2f!2sSaint-Louis%2C%20S%C3%A9n%C3%A9gal!5e0!3m2!1sfr!2sfr!4v1700000000000!5m2!1sfr!2sfr" 
                    width="100%" 
                    height="350" 
                    style="border:0; border-radius: 20px;" 
                    allowfullscreen="" 
                    loading="lazy"
                    title="Carte Google Maps de Saint-Louis du Sénégal">
                </iframe>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    const form = document.getElementById('contactForm');
    if (form) {
        form.addEventListener('submit', function(event) {
            let isValid = true;
            const nom = document.getElementById('nom');
            const email = document.getElementById('email');
            const message = document.getElementById('message');
            
            [nom, email, message].forEach(field => {
                field.classList.remove('is-invalid');
            });
            
            if (!nom.value.trim()) {
                nom.classList.add('is-invalid');
                isValid = false;
            }
            
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!email.value.trim() || !emailRegex.test(email.value)) {
                email.classList.add('is-invalid');
                isValid = false;
            }
            
            if (!message.value.trim() || message.value.trim().length < 10) {
                message.classList.add('is-invalid');
                isValid = false;
            }
            
            if (!isValid) {
                event.preventDefault();
                const firstInvalid = form.querySelector('.is-invalid');
                if (firstInvalid) {
                    firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstInvalid.focus();
                }
            } else {
                const submitBtn = document.getElementById('submitBtn');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> ENVOI EN COURS...';
                }
            }
        });
        
        ['nom', 'email', 'message'].forEach(id => {
            const field = document.getElementById(id);
            if (field) {
                field.addEventListener('input', function() {
                    this.classList.remove('is-invalid');
                });
            }
        });
    }
})();
</script>

<?php include 'includes/footer.php'; ?>