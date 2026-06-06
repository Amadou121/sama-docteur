<?php
// Fichier: contact.php
require_once 'includes/config.php';
include 'includes/header.php';

$message_envoye = false;
$erreur = '';
$nom = $email = $sujet = $message = ''; // Pour réafficher les valeurs en cas d'erreur

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Nettoyage et sécurisation des entrées
    $nom = trim(htmlspecialchars($_POST['nom'] ?? ''));
    $email = trim(htmlspecialchars($_POST['email'] ?? ''));
    $sujet = trim(htmlspecialchars($_POST['sujet'] ?? ''));
    $message = trim(htmlspecialchars($_POST['message'] ?? ''));
    
    if (empty($nom) || empty($email) || empty($sujet) || empty($message)) {
        $erreur = 'Veuillez remplir tous les champs';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreur = 'Email invalide';
    } elseif (strlen($message) < 10) {
        $erreur = 'Le message doit contenir au moins 10 caractères';
    } else {
        // Ici vous pouvez ajouter l'envoi réel du message (mail, base de données, etc.)
        // Exemple simple d'envoi d'email (à configurer avec votre serveur)
        /*
        $to = "contact@samadocteur.sn";
        $headers = "From: " . $email . "\r\n";
        $headers .= "Reply-To: " . $email . "\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $corps = "Nom: $nom\nEmail: $email\nSujet: $sujet\n\nMessage:\n$message";
        if (mail($to, $sujet, $corps, $headers)) {
            $message_envoye = true;
            $nom = $email = $sujet = $message = ''; // Réinitialiser le formulaire
        } else {
            $erreur = "Une erreur technique est survenue. Veuillez réessayer plus tard.";
        }
        */
        
        // Pour la démo, on simule l'envoi réussi
        $message_envoye = true;
        $nom = $email = $sujet = $message = ''; // Réinitialiser après succès
    }
}
?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-6 mb-4" data-aos="fade-right">
            <h1 class="display-5 mb-4">Contactez-nous</h1>
            <p class="lead">Nous sommes à votre écoute pour toute question ou suggestion.</p>
            <p>Notre équipe se tient à votre disposition pour vous assister dans l'utilisation de notre plateforme.</p>
            
            <div class="contact-info mt-5">
                <div class="info-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <div>
                        <h6>Notre adresse</h6>
                        <p>Immeuble Sama Tower, 5ème étage<br>Dakar, Sénégal</p>
                    </div>
                </div>
                <div class="info-item">
                    <i class="fas fa-phone-alt"></i>
                    <div>
                        <h6>Téléphone</h6>
                        <p>+221 77 000 00 00<br>+221 78 000 00 00</p>
                    </div>
                </div>
                <div class="info-item">
                    <i class="fas fa-envelope"></i>
                    <div>
                        <h6>Email</h6>
                        <p>contact@samadocteur.sn<br>support@samadocteur.sn</p>
                    </div>
                </div>
                <div class="info-item">
                    <i class="fas fa-clock"></i>
                    <div>
                        <h6>Horaires d'ouverture</h6>
                        <p>Lundi - Vendredi: 8h00 - 18h00<br>Samedi: 9h00 - 13h00</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6" data-aos="fade-left">
            <div class="form-container">
                <h3 class="mb-4">Envoyez-nous un message</h3>
                
                <?php if ($message_envoye): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i> Votre message a été envoyé avec succès. Nous vous répondrons dans les plus brefs délais.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                    </div>
                <?php endif; ?>
                
                <?php if ($erreur): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($erreur); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" id="contactForm" novalidate>
                    <div class="mb-3">
                        <label for="nom" class="form-label">Nom complet <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nom" name="nom" value="<?php echo htmlspecialchars($nom); ?>" required>
                        <div class="invalid-feedback">Veuillez entrer votre nom complet.</div>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                        <div class="invalid-feedback">Veuillez entrer une adresse email valide.</div>
                    </div>
                    <div class="mb-3">
                        <label for="sujet" class="form-label">Sujet <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="sujet" name="sujet" value="<?php echo htmlspecialchars($sujet); ?>" required>
                        <div class="invalid-feedback">Veuillez indiquer le sujet de votre message.</div>
                    </div>
                    <div class="mb-3">
                        <label for="message" class="form-label">Message <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="message" name="message" rows="5" required minlength="10"><?php echo htmlspecialchars($message); ?></textarea>
                        <div class="invalid-feedback">Le message doit contenir au moins 10 caractères.</div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100" id="submitBtn">
                        <i class="fas fa-paper-plane"></i> Envoyer le message
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Google Maps -->
    <div class="mt-5" data-aos="fade-up">
        <div class="map-container">
            <iframe 
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d123530.61284222815!2d-17.50075575!3d14.69370495!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xec1749a1efdabdf%3A0x93d787a19a49e1d!2sDakar%2C%20S%C3%A9n%C3%A9gal!5e0!3m2!1sfr!2sfr!4v1700000000000!5m2!1sfr!2sfr" 
                width="100%" 
                height="400" 
                style="border:0; border-radius: 12px;" 
                allowfullscreen="" 
                loading="lazy"
                title="Carte Google Maps de Dakar">
            </iframe>
        </div>
    </div>
</div>

<script>
(function() {
    // Validation améliorée avec JavaScript
    const form = document.getElementById('contactForm');
    if (form) {
        form.addEventListener('submit', function(event) {
            let isValid = true;
            const nom = document.getElementById('nom');
            const email = document.getElementById('email');
            const sujet = document.getElementById('sujet');
            const message = document.getElementById('message');
            
            // Réinitialiser les styles d'erreur
            [nom, email, sujet, message].forEach(field => {
                field.classList.remove('is-invalid');
            });
            
            // Validation Nom
            if (!nom.value.trim()) {
                nom.classList.add('is-invalid');
                isValid = false;
            }
            
            // Validation Email
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!email.value.trim() || !emailRegex.test(email.value)) {
                email.classList.add('is-invalid');
                isValid = false;
            }
            
            // Validation Sujet
            if (!sujet.value.trim()) {
                sujet.classList.add('is-invalid');
                isValid = false;
            }
            
            // Validation Message (min 10 caractères)
            if (!message.value.trim() || message.value.trim().length < 10) {
                message.classList.add('is-invalid');
                isValid = false;
            }
            
            if (!isValid) {
                event.preventDefault();
                // Scroll vers le premier champ en erreur
                const firstInvalid = form.querySelector('.is-invalid');
                if (firstInvalid) {
                    firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstInvalid.focus();
                }
            } else {
                // Optionnel : Désactiver le bouton pour éviter double soumission
                const submitBtn = document.getElementById('submitBtn');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi en cours...';
                }
            }
        });
        
        // Supprimer le style d'erreur lors de la saisie
        ['nom', 'email', 'sujet', 'message'].forEach(id => {
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