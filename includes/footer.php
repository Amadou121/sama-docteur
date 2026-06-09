<?php
// Fichier: includes/footer.php
?>
    </main>
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4 mb-md-0">
                    <div class="footer-brand">
                        <i class="fas fa-stethoscope"></i>
                        <span>Sama Docteur</span>
                    </div>
                    <p class="mt-3">Votre plateforme de prise de rendez-vous médicaux au Sénégal. Prenez soin de votre santé facilement et rapidement.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                <div class="col-md-2 mb-4 mb-md-0">
                    <h5>Liens rapides</h5>
                    <ul class="footer-links">
                        <li><a href="<?php echo SITE_URL; ?>">Accueil</a></li>
                        <li><a href="<?php echo SITE_URL; ?>apropos.php">À propos</a></li>
                        <li><a href="<?php echo SITE_URL; ?>specialites.php">Spécialités</a></li>
                        <li><a href="<?php echo SITE_URL; ?>contact.php">Contact</a></li>
                    </ul>
                </div>
                <div class="col-md-3 mb-4 mb-md-0">
                    <h5>Nos spécialités</h5>
                    <ul class="footer-links">
                        <li><a href="#">Cardiologie</a></li>
                        <li><a href="#">Dermatologie</a></li>
                        <li><a href="#">Pédiatrie</a></li>
                        <li><a href="#">Gynécologie</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Contact</h5>
                    <ul class="footer-links">
                        <li><i class="fas fa-phone-alt"></i> +221 77 000 00 00</li>
                        <li><i class="fas fa-envelope"></i> contact@samadocteur.sn</li>
                        <li><i class="fas fa-map-marker-alt"></i> Saint-Louis, Sénégal</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> Sama Docteur. Tous droits réservés. | <a href="#">Mentions légales</a> | <a href="#">Politique de confidentialité</a></p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle avec Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery (optionnel pour certaines fonctionnalités) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- AOS Animation JS -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <!-- SweetAlert2 pour les notifications -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Custom JS -->
    <script src="<?php echo SITE_URL; ?>assets/js/main.js"></script>
    
    <script>
        // Initialisation AOS
        AOS.init({
            duration: 1000,
            once: true,
            offset: 100
        });
        
        // Masquer le loader après chargement
        window.addEventListener('load', function() {
            const loader = document.getElementById('loader');
            if (loader) {
                setTimeout(function() {
                    loader.classList.add('hide');
                }, 500);
            }
        });
        
        // Fonction pour afficher les notifications
        function showNotification(message, type = 'success') {
            Swal.fire({
                title: type === 'success' ? 'Succès !' : 'Information',
                text: message,
                icon: type,
                confirmButtonColor: '#2563EB',
                timer: 3000,
                timerProgressBar: true,
                showConfirmButton: false
            });
        }
        
        // Fonction pour confirmer la suppression
        function confirmSuppression(message = 'Êtes-vous sûr de vouloir effectuer cette action ?') {
            return Swal.fire({
                title: 'Confirmation',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#EF4444',
                cancelButtonColor: '#64748B',
                confirmButtonText: 'Oui, confirmer',
                cancelButtonText: 'Annuler'
            }).then((result) => {
                return result.isConfirmed;
            });
        }
        
        // Fonction de validation de formulaire
        function validerFormulaire(formId) {
            const form = document.getElementById(formId);
            if (!form) return true;
            
            let isValid = true;
            const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
            
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    input.classList.add('is-invalid');
                    isValid = false;
                } else {
                    input.classList.remove('is-invalid');
                }
                
                // Validation email
                if (input.type === 'email' && input.value) {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(input.value)) {
                        input.classList.add('is-invalid');
                        isValid = false;
                    }
                }
                
                // Validation téléphone
                if (input.type === 'tel' && input.value) {
                    const phoneRegex = /^[0-9]{2}[0-9]{3}[0-9]{3}$/;
                    const phoneClean = input.value.replace(/\s/g, '');
                    if (!phoneRegex.test(phoneClean) && phoneClean.length > 0) {
                        input.classList.add('is-invalid');
                        isValid = false;
                    }
                }
            });
            
            if (!isValid) {
                showNotification('Veuillez remplir tous les champs correctement', 'warning');
            }
            
            return isValid;
        }
        
        // Animation au scroll pour les éléments
        document.addEventListener('DOMContentLoaded', function() {
            // Smooth scroll pour les ancres
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });
        });
        
        // Gestion des tooltips Bootstrap
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
</body>
</html>