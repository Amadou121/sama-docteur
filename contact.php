<?php
// Fichier: contact.php
require_once 'includes/config.php';
include 'includes/header.php';

$message_envoye = false;
$erreur = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nom = htmlspecialchars($_POST['nom']);
    $email = htmlspecialchars($_POST['email']);
    $sujet = htmlspecialchars($_POST['sujet']);
    $message = htmlspecialchars($_POST['message']);
    
    if(empty($nom) || empty($email) || empty($sujet) || empty($message)) {
        $erreur = 'Veuillez remplir tous les champs';
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreur = 'Email invalide';
    } else {
        $message_envoye = true;
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
                
                <?php if($message_envoye): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> Votre message a été envoyé avec succès. Nous vous répondrons dans les plus brefs délais.
                    </div>
                <?php endif; ?>
                
                <?php if($erreur): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> <?php echo $erreur; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" onsubmit="return validerFormulaire('contactForm')" id="contactForm">
                    <div class="mb-3">
                        <label for="nom" class="form-label">Nom complet *</label>
                        <input type="text" class="form-control" id="nom" name="nom" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email *</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="sujet" class="form-label">Sujet *</label>
                        <input type="text" class="form-control" id="sujet" name="sujet" required>
                    </div>
                    <div class="mb-3">
                        <label for="message" class="form-label">Message *</label>
                        <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
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
                loading="lazy">
            </iframe>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>