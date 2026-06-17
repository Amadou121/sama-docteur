<?php
// Fichier: includes/footer.php
?>
    </main>
    <style>
        /* Footer styles - Amélioré */
        .footer {
            background: linear-gradient(135deg, #0b1f4d 0%, #0f172a 100%);
            color: #eaf6fb;
            padding: 60px 0 20px;
            margin-top: 80px;
            position: relative;
            z-index: 100;
            width: 100%;
        }

        .footer .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        .footer h5 {
            color: #ffffff;
            font-weight: 600;
            margin-bottom: 20px;
            font-size: 1.1rem;
            position: relative;
            padding-bottom: 10px;
        }

        .footer h5::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 40px;
            height: 3px;
            background: #2563eb;
            border-radius: 2px;
        }

        .footer p {
            color: #d8f3fb;
            line-height: 1.8;
            font-size: 0.95rem;
        }

        .footer a {
            color: #9fd6ee;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .footer a:hover {
            color: #ffffff;
            text-decoration: none;
            transform: translateX(5px);
            display: inline-block;
        }

        .footer-brand {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 15px;
            color: #ffffff;
        }

        .footer-brand i {
            color: #2563eb;
            margin-right: 8px;
            font-size: 1.8rem;
        }

        .footer-brand span {
            background: linear-gradient(135deg, #2563eb, #60a5fa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Listes de liens */
        .footer-links {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .footer-links li {
            margin-bottom: 12px;
            color: #d8f3fb;
            font-size: 0.95rem;
        }

        .footer-links li i {
            width: 20px;
            margin-right: 8px;
            color: #2563eb;
        }

        .footer-links li a {
            color: #c7ecfb;
            transition: all 0.3s ease;
        }

        .footer-links li a:hover {
            color: #ffffff;
            transform: translateX(5px);
        }

        /* Social links */
        .social-links {
            margin-top: 20px;
            display: flex;
            gap: 12px;
        }

        .social-links a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            color: #2563eb;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            border: 1px solid rgba(37, 99, 235, 0.3);
        }

        .social-links a:hover {
            background: #2563eb;
            color: #ffffff;
            transform: translateY(-5px);
            border-color: #2563eb;
        }

        /* Footer bottom */
        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 25px;
            margin-top: 40px;
            text-align: center;
            color: #b0d4e8;
            font-size: 0.9rem;
        }

        .footer-bottom p {
            margin: 0;
        }

        .footer-bottom a {
            color: #9fd6ee;
            margin: 0 8px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .footer {
                padding: 40px 0 15px;
                margin-top: 60px;
            }

            .footer h5 {
                font-size: 1rem;
                margin-bottom: 15px;
            }

            .footer .col-md-4,
            .footer .col-md-2,
            .footer .col-md-3 {
                margin-bottom: 30px;
            }

            .footer-brand {
                font-size: 1.3rem;
            }

            .footer h5::after {
                left: 0;
            }
        }

        @media (max-width: 576px) {
            .footer {
                padding: 30px 0 10px;
            }

            .footer h5 {
                font-size: 0.95rem;
            }

            .footer p {
                font-size: 0.9rem;
            }

            .footer-links li {
                font-size: 0.9rem;
            }

            .social-links {
                justify-content: flex-start;
            }

            .footer-bottom {
                font-size: 0.85rem;
            }
        }
    </style>

    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4 mb-md-0">
                    <div class="footer-brand">
                        <i class="fas fa-stethoscope"></i>
                        <span>Sama Docteur</span>
                    </div>
                    <p>Votre plateforme de prise de rendez-vous médicaux au Sénégal. Prenez soin de votre santé facilement et rapidement avec nos médecins qualifiés.</p>
                    <div class="social-links">
                        <a href="#" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" title="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" title="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
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