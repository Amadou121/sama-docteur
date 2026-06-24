<?php
// Fichier: contact.php
require_once 'includes/config.php';
include 'includes/header.php';

$message_envoye = false;
$erreur = '';
$prenom = $nom = $telephone = $message = '';

// Vérifier si l'utilisateur est connecté pour pré-remplir les champs
if (isset($_SESSION['user_id']) && $_SESSION['user_role'] == 'patient') {
    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("SELECT nom_complet, email, telephone FROM utilisateurs WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    if ($user) {
        $nom_complet = explode(' ', $user['nom_complet']);
        $prenom = $nom_complet[0] ?? '';
        $nom = $nom_complet[1] ?? '';
        $telephone = $user['telephone'] ?? '';
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $prenom = trim(htmlspecialchars($_POST['prenom'] ?? ''));
    $nom = trim(htmlspecialchars($_POST['nom'] ?? ''));
    $telephone = trim(htmlspecialchars($_POST['telephone'] ?? ''));
    $message = trim(htmlspecialchars($_POST['message'] ?? ''));
    
    if (empty($message)) {
        $erreur = 'Veuillez saisir votre message';
    } elseif (strlen($message) < 10) {
        $erreur = 'Le message doit contenir au moins 10 caractères';
    } else {
        $message_envoye = true;
        $prenom = $nom = $telephone = $message = '';
    }
}
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap');

.contact-page {
    background: radial-gradient(circle at 0% 0%, #f8fafc 0%, #eff6ff 100%);
    min-height: 100vh;
    position: relative;
    overflow-x: hidden;
}

/* Particules de fond */
.contact-page::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200"><circle cx="30" cy="30" r="2" fill="%232563EB" opacity="0.1"/><circle cx="170" cy="50" r="3" fill="%232563EB" opacity="0.08"/><circle cx="80" cy="180" r="2" fill="%232563EB" opacity="0.1"/><circle cx="150" cy="150" r="4" fill="%232563EB" opacity="0.05"/><circle cx="50" cy="120" r="2" fill="%232563EB" opacity="0.08"/></svg>');
    background-repeat: repeat;
    opacity: 0.5;
    pointer-events: none;
}

.contact-header {
    text-align: center;
    margin-bottom: 3rem;
    position: relative;
}

.contact-header .badge {
    display: inline-block;
    background: rgba(37,99,235,0.1);
    color: #2563EB;
    padding: 0.5rem 1rem;
    border-radius: 100px;
    font-size: 0.8rem;
    font-weight: 600;
    letter-spacing: 0.5px;
    margin-bottom: 1rem;
    backdrop-filter: blur(10px);
}

.contact-header h1 {
    font-size: 3rem;
    font-weight: 700;
    margin-bottom: 1rem;
    background: linear-gradient(135deg, #2563EB 0%, #1e40af 50%, #60a5fa 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    letter-spacing: -0.02em;
}

.contact-header p {
    color: #64748b;
    font-size: 1.125rem;
    font-weight: 400;
    max-width: 500px;
    margin: 0 auto;
}

.nav-buttons {
    display: flex;
    justify-content: center;
    gap: 15px;
    margin-top: 1.5rem;
    flex-wrap: wrap;
}

.nav-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 24px;
    border-radius: 30px;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.nav-btn-primary {
    background: linear-gradient(135deg, #2563EB, #1e40af);
    color: white;
}

.nav-btn-primary:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(37,99,235,0.3);
    color: white;
}

.nav-btn-outline {
    background: transparent;
    border-color: #2563EB;
    color: #2563EB;
}

.nav-btn-outline:hover {
    background: #2563EB;
    color: white;
    transform: translateY(-3px);
}

.contact-form-wrapper {
    background: rgba(255,255,255,0.95);
    backdrop-filter: blur(10px);
    border-radius: 32px;
    box-shadow: 0 25px 50px -12px rgba(37,99,235,0.15), 0 0 0 1px rgba(37,99,235,0.05);
    padding: 2.5rem;
    max-width: 580px;
    margin: 0 auto;
    position: relative;
    transition: all 0.3s ease;
}

.contact-form-wrapper:hover {
    box-shadow: 0 30px 60px -12px rgba(37,99,235,0.2);
    transform: translateY(-2px);
}

.form-header {
    text-align: center;
    margin-bottom: 2rem;
}

.form-header h3 {
    font-size: 1.5rem;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 0.5rem;
}

.form-header p {
    font-size: 0.875rem;
    color: #94a3b8;
}

.form-group-modern {
    margin-bottom: 1.5rem;
}

.form-group-modern label {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #334155;
    font-weight: 500;
    font-size: 0.875rem;
    margin-bottom: 0.5rem;
}

.form-group-modern label i {
    color: #2563EB;
    font-size: 0.9rem;
    width: 18px;
}

.form-group-modern label .optional {
    color: #94a3b8;
    font-size: 0.7rem;
    font-weight: 400;
    margin-left: 4px;
}

.form-control-modern {
    width: 100%;
    padding: 14px 18px;
    font-size: 0.95rem;
    font-family: 'Inter', sans-serif;
    border: 1.5px solid #e2e8f0;
    border-radius: 20px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    background: #ffffff;
}

.form-control-modern:focus {
    outline: none;
    border-color: #2563EB;
    box-shadow: 0 0 0 4px rgba(37,99,235,0.1);
}

.form-control-modern:hover:not(:focus) {
    border-color: #cbd5e1;
}

textarea.form-control-modern {
    resize: vertical;
    min-height: 120px;
}

.form-control-modern.required-field {
    border-color: #2563EB;
}

.btn-submit-modern {
    width: 100%;
    padding: 16px;
    background: linear-gradient(105deg, #2563EB 0%, #1e40af 100%);
    color: white;
    border: none;
    border-radius: 28px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    position: relative;
    overflow: hidden;
}

.btn-submit-modern::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(255,255,255,0.2);
    transform: translate(-50%, -50%);
    transition: width 0.6s, height 0.6s;
}

.btn-submit-modern:hover::before {
    width: 300px;
    height: 300px;
}

.btn-submit-modern:hover {
    transform: translateY(-2px);
    box-shadow: 0 15px 30px -10px rgba(37,99,235,0.4);
}

.btn-submit-modern:active {
    transform: translateY(0);
}

.btn-submit-modern:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

/* Alertes modernes */
.alert-modern {
    border-radius: 20px;
    padding: 1rem 1.25rem;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 12px;
    border: none;
    animation: slideIn 0.4s ease;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.alert-success-modern {
    background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
    color: #065f46;
    border-left: 4px solid #10b981;
}

.alert-danger-modern {
    background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
    color: #991b1b;
    border-left: 4px solid #ef4444;
}

/* Map container sophistiqué */
.map-container-modern {
    position: relative;
    border-radius: 32px;
    overflow: hidden;
    box-shadow: 0 25px 50px -12px rgba(0,0,0,0.15);
    transition: all 0.3s ease;
}

.map-container-modern:hover {
    transform: scale(1.01);
    box-shadow: 0 30px 60px -12px rgba(0,0,0,0.2);
}

.map-overlay {
    position: absolute;
    bottom: 20px;
    right: 20px;
    background: rgba(255,255,255,0.95);
    backdrop-filter: blur(10px);
    padding: 8px 16px;
    border-radius: 40px;
    font-size: 0.75rem;
    color: #2563EB;
    font-weight: 500;
    z-index: 1;
    pointer-events: none;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

/* ============================================
   NOUVEAU DESIGN - INFOS CONTACT EN LIGNE
   ============================================ */

.contact-info-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    max-width: 1100px;
    margin: 2.5rem auto 2rem;
    padding: 0 1rem;
}

.contact-info-card {
    background: rgba(255,255,255,0.95);
    backdrop-filter: blur(12px);
    border-radius: 24px;
    padding: 1.5rem 1rem;
    text-align: center;
    transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    box-shadow: 0 8px 25px -8px rgba(37,99,235,0.08), 0 0 0 1px rgba(37,99,235,0.04);
    position: relative;
    overflow: hidden;
}

.contact-info-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, #2563EB, #60a5fa, #2563EB);
    background-size: 200% 100%;
    animation: shimmer 3s ease-in-out infinite;
    opacity: 0;
    transition: opacity 0.3s ease;
}

@keyframes shimmer {
    0% { background-position: -200% 0; }
    100% { background-position: 200% 0; }
}

.contact-info-card:hover::before {
    opacity: 1;
}

.contact-info-card:hover {
    transform: translateY(-5px) scale(1.02);
    box-shadow: 0 20px 40px -12px rgba(37,99,235,0.2), 0 0 0 1px rgba(37,99,235,0.1);
}

/* Effet de brillance au survol */
.contact-info-card::after {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle at 30% 30%, rgba(37,99,235,0.03) 0%, transparent 70%);
    opacity: 0;
    transition: opacity 0.6s ease;
    pointer-events: none;
}

.contact-info-card:hover::after {
    opacity: 1;
}

.contact-icon-wrapper {
    width: 56px;
    height: 56px;
    margin: 0 auto 0.75rem;
    background: linear-gradient(135deg, rgba(37,99,235,0.1), rgba(96,165,250,0.05));
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.contact-info-card:hover .contact-icon-wrapper {
    background: linear-gradient(135deg, #2563EB, #1e40af);
    transform: scale(1.05) rotate(-3deg);
    box-shadow: 0 8px 20px rgba(37,99,235,0.25);
}

.contact-icon-wrapper i {
    font-size: 1.4rem;
    color: #2563EB;
    transition: all 0.3s ease;
}

.contact-info-card:hover .contact-icon-wrapper i {
    color: white;
    transform: scale(1.1);
}

.contact-card-title {
    font-size: 0.65rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1.2px;
    color: #94a3b8;
    margin-bottom: 0.5rem;
}

.contact-card-content {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
}

.contact-card-link {
    color: #1e293b;
    text-decoration: none;
    font-size: 0.85rem;
    font-weight: 500;
    transition: all 0.3s ease;
    display: inline-block;
    padding: 2px 0;
    border-bottom: 2px solid transparent;
}

.contact-card-link:hover {
    color: #2563EB;
    border-bottom-color: #2563EB;
}

.contact-card-text {
    color: #475569;
    font-size: 0.85rem;
    font-weight: 400;
    line-height: 1.4;
    margin: 0;
}

/* Badge "Urgence" dans la carte */
.contact-card-badge {
    display: inline-block;
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: white;
    padding: 0.2rem 0.8rem;
    border-radius: 30px;
    font-size: 0.6rem;
    font-weight: 700;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    animation: pulse-badge 2s ease-in-out infinite;
}

@keyframes pulse-badge {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

/* Séparateur vertical entre les cartes */
.contact-info-divider {
    display: none;
}

/* Responsive - Tablettes */
@media (max-width: 992px) {
    .contact-info-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
        padding: 0 1.5rem;
    }
    
    .contact-info-card {
        padding: 1.25rem 0.75rem;
    }
}

/* Responsive - Mobiles */
@media (max-width: 576px) {
    .contact-info-grid {
        grid-template-columns: 1fr 1fr;
        gap: 0.75rem;
        padding: 0 0.75rem;
        margin: 1.5rem auto 1rem;
    }
    
    .contact-info-card {
        padding: 1rem 0.5rem;
        border-radius: 18px;
    }
    
    .contact-icon-wrapper {
        width: 44px;
        height: 44px;
        border-radius: 14px;
    }
    
    .contact-icon-wrapper i {
        font-size: 1.1rem;
    }
    
    .contact-card-title {
        font-size: 0.55rem;
        letter-spacing: 0.8px;
    }
    
    .contact-card-link {
        font-size: 0.7rem;
    }
    
    .contact-card-text {
        font-size: 0.7rem;
    }
    
    .contact-card-badge {
        font-size: 0.5rem;
        padding: 0.15rem 0.6rem;
    }
}

/* Pour les très petits écrans */
@media (max-width: 400px) {
    .contact-info-grid {
        grid-template-columns: 1fr 1fr;
        gap: 0.5rem;
    }
    
    .contact-info-card {
        padding: 0.75rem 0.4rem;
    }
}

/* Fin du nouveau design */

/* Animations */
@keyframes fadeScale {
    from {
        opacity: 0;
        transform: scale(0.95);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

.fade-scale {
    animation: fadeScale 0.5s ease forwards;
}

/* Responsive */
@media (max-width: 768px) {
    .contact-header h1 {
        font-size: 2rem;
    }
    
    .contact-form-wrapper {
        padding: 1.5rem;
        margin: 0 1rem;
    }
    
    .form-header h3 {
        font-size: 1.25rem;
    }
    
    .btn-submit-modern {
        padding: 14px;
    }
    
    .nav-buttons {
        flex-direction: column;
        align-items: center;
    }
    
    .nav-btn {
        width: 100%;
        justify-content: center;
    }
}

/* Input avec icône flottante */
.input-icon-wrapper {
    position: relative;
}

.input-icon-wrapper i {
    position: absolute;
    left: 18px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.input-icon-wrapper .form-control-modern {
    padding-left: 46px;
}

.input-icon-wrapper:focus-within i {
    color: #2563EB;
}

/* Séparateur décoratif */
.divider {
    width: 60px;
    height: 4px;
    background: linear-gradient(90deg, #2563EB, #60a5fa);
    border-radius: 4px;
    margin: 0 auto 1.5rem auto;
}

/* Amélioration des champs invalides */
.form-control-modern.is-invalid {
    border-color: #ef4444;
    box-shadow: 0 0 0 4px rgba(239,68,68,0.1);
}

.invalid-feedback-modern {
    color: #ef4444;
    font-size: 0.75rem;
    margin-top: 0.5rem;
    display: flex;
    align-items: center;
    gap: 4px;
}

/* Indicateur de champ requis */
.required-indicator {
    color: #ef4444;
    font-size: 0.7rem;
    margin-left: 4px;
}

/* Badge utilisateur connecté */
.user-badge {
    display: inline-block;
    background: rgba(16,185,129,0.12);
    color: #065f46;
    padding: 0.4rem 1.2rem;
    border-radius: 100px;
    font-size: 0.8rem;
    font-weight: 500;
    margin-top: 0.75rem;
}

.urgent-badge {
    display: inline-block;
    background: linear-gradient(135deg, #fef2f2, #fee2e2);
    color: #dc2626;
    padding: 0.4rem 1.2rem;
    border-radius: 100px;
    font-size: 0.75rem;
    font-weight: 600;
    margin-bottom: 1rem;
    border: 1px solid rgba(220,38,38,0.15);
}

/* Hours section supprimée - intégrée dans la grille */
</style>

<div class="contact-page">
    <div class="container py-5">
        <!-- En-tête sophistiqué -->
        <div class="contact-header" data-aos="fade-up">
            <div class="urgent-badge">
                <i class="fas fa-exclamation-triangle"></i>
                Urgence médicale ? Appelez le 15
            </div>
            <div class="badge">
                <i class="fas fa-comment-medical me-2"></i> Assistance Médicale 24/7
            </div>
            <h1>Prenons soin de vous</h1>
            <div class="divider"></div>
            <p>Une question médicale ? Notre équipe de professionnels vous répond sous 24h</p>
            
            <?php if (isset($_SESSION['user_id']) && $_SESSION['user_role'] == 'patient'): ?>
                <div class="user-badge">
                    <i class="fas fa-check-circle me-1"></i> Connecté en tant que patient
                </div>
            <?php endif; ?>
            
            <div class="nav-buttons">
                <?php if (isset($_SESSION['user_id']) && $_SESSION['user_role'] == 'patient'): ?>
                    <a href="dashboard.php" class="nav-btn nav-btn-primary">
                        <i class="fas fa-tachometer-alt"></i> Mon tableau de bord
                    </a>
                <?php else: ?>
                    <a href="specialites.php" class="nav-btn nav-btn-outline">
                        <i class="fas fa-stethoscope"></i> Nos spécialités
                    </a>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- ⭐ NOUVELLE SECTION - INFOS CONTACT EN GRILLE 4 COLONNES -->
        <div class="contact-info-grid" data-aos="fade-up" data-aos-delay="100">
            <!-- Téléphone -->
            <div class="contact-info-card">
                <div class="contact-icon-wrapper">
                    <i class="fas fa-phone-alt"></i>
                </div>
                <div class="contact-card-title">Téléphone</div>
                <div class="contact-card-content">
                    <a href="tel:+221771234567" class="contact-card-link">
                        <i class="fas fa-phone me-1" style="font-size:0.6rem; color:#2563EB;"></i>
                        +221 77 123 45 67
                    </a>
                    <a href="tel:+221789876543" class="contact-card-link">
                        <i class="fas fa-phone me-1" style="font-size:0.6rem; color:#2563EB;"></i>
                        +221 78 987 65 43
                    </a>
                </div>
            </div>
            
            <!-- Email -->
            <div class="contact-info-card">
                <div class="contact-icon-wrapper">
                    <i class="fas fa-envelope"></i>
                </div>
                <div class="contact-card-title">Email</div>
                <div class="contact-card-content">
                    <a href="mailto:contact@samadocteur.sn" class="contact-card-link">
                        <i class="fas fa-envelope me-1" style="font-size:0.6rem; color:#2563EB;"></i>
                        contact@samadocteur.sn
                    </a>
                    <a href="mailto:support@samadocteur.sn" class="contact-card-link">
                        <i class="fas fa-envelope me-1" style="font-size:0.6rem; color:#2563EB;"></i>
                        support@samadocteur.sn
                    </a>
                </div>
            </div>
            
            <!-- Adresse -->
            <div class="contact-info-card">
                <div class="contact-icon-wrapper">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <div class="contact-card-title">Adresse</div>
                <div class="contact-card-content">
                    <p class="contact-card-text">
                        <i class="fas fa-location-dot me-1" style="font-size:0.6rem; color:#2563EB;"></i>
                        Quartier Sor, Saint-Louis
                    </p>
                    <p class="contact-card-text" style="font-size:0.7rem; color:#94a3b8;">
                        <i class="fas fa-flag me-1" style="font-size:0.5rem;"></i>
                        Sénégal
                    </p>
                </div>
            </div>
            
            <!-- Horaires -->
            <div class="contact-info-card">
                <div class="contact-icon-wrapper">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="contact-card-title">Horaires</div>
                <div class="contact-card-content">
                    <p class="contact-card-text" style="display:flex; justify-content:space-between; width:100%;">
                        <span style="font-weight:400; color:#64748b;">Lun - Ven</span>
                        <span style="font-weight:600; color:#1e293b;">08:00 - 18:00</span>
                    </p>
                    <p class="contact-card-text" style="display:flex; justify-content:space-between; width:100%;">
                        <span style="font-weight:400; color:#64748b;">Samedi</span>
                        <span style="font-weight:600; color:#1e293b;">09:00 - 14:00</span>
                    </p>
                    <p class="contact-card-text" style="display:flex; justify-content:space-between; width:100%; margin-top:0.15rem;">
                        <span style="font-weight:400; color:#64748b;">Dimanche</span>
                        <span style="font-weight:600; color:#ef4444; font-size:0.7rem;">Fermé</span>
                    </p>
                    <div style="margin-top:0.3rem;">
                        <span class="contact-card-badge">
                            <i class="fas fa-bolt me-1"></i> Urgences 24/7
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Formulaire de contact -->
        <div class="row justify-content-center">
            <div class="col-lg-6" data-aos="fade-up" data-aos-delay="200">
                <div class="contact-form-wrapper">
                    <div class="form-header">
                        <h3>Envoyez-nous un message</h3>
                        <p>Remplissez le formulaire ci-dessous</p>
                    </div>
                    
                    <?php if ($message_envoye): ?>
                        <div class="alert-modern alert-success-modern">
                            <i class="fas fa-check-circle fa-lg"></i>
                            <div>
                                <strong>Message envoyé !</strong><br>
                                Notre équipe médicale vous répondra dans les plus brefs délais.
                            </div>
                            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Fermer"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($erreur): ?>
                        <div class="alert-modern alert-danger-modern">
                            <i class="fas fa-exclamation-triangle fa-lg"></i>
                            <div>
                                <strong>Erreur</strong><br>
                                <?php echo htmlspecialchars($erreur); ?>
                            </div>
                            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Fermer"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" id="contactForm" novalidate>
                        <div class="form-group-modern">
                            <label for="prenom">
                                <i class="fas fa-user"></i> Prénom
                                <span class="optional">(optionnel)</span>
                            </label>
                            <div class="input-icon-wrapper">
                                <i class="fas fa-user"></i>
                                <input type="text" class="form-control-modern" id="prenom" name="prenom" placeholder="Votre prénom" value="<?php echo htmlspecialchars($prenom); ?>">
                            </div>
                        </div>
                        
                        <div class="form-group-modern">
                            <label for="nom">
                                <i class="fas fa-user-tag"></i> Nom
                                <span class="optional">(optionnel)</span>
                            </label>
                            <div class="input-icon-wrapper">
                                <i class="fas fa-user-tag"></i>
                                <input type="text" class="form-control-modern" id="nom" name="nom" placeholder="Votre nom" value="<?php echo htmlspecialchars($nom); ?>">
                            </div>
                        </div>
                        
                        <div class="form-group-modern">
                            <label for="telephone">
                                <i class="fas fa-phone-alt"></i> Numéro de téléphone
                                <span class="optional">(optionnel)</span>
                            </label>
                            <div class="input-icon-wrapper">
                                <i class="fas fa-phone-alt"></i>
                                <input type="tel" class="form-control-modern" id="telephone" name="telephone" placeholder="+221 77 000 00 00" value="<?php echo htmlspecialchars($telephone); ?>">
                            </div>
                        </div>
                        
                        <div class="form-group-modern">
                            <label for="message">
                                <i class="fas fa-comment-dots"></i> Votre message
                                <span class="required-indicator">*</span>
                            </label>
                            <textarea class="form-control-modern required-field" id="message" name="message" rows="5" placeholder="Décrivez votre demande médicale ou votre question..." required minlength="10"><?php echo htmlspecialchars($message); ?></textarea>
                            <div class="invalid-feedback-modern">
                                <i class="fas fa-circle-info"></i> Le message doit contenir au moins 10 caractères
                            </div>
                        </div>
                        
                        <button type="submit" class="btn-submit-modern" id="submitBtn">
                            <i class="fas fa-paper-plane"></i>
                            <span>Envoyer ma demande</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Google Maps -->
        <div class="map-section" data-aos="fade-up" data-aos-delay="300">
            <h3 class="map-section-title" style="text-align:center; font-size:1.25rem; font-weight:600; color:#1e293b; margin-bottom:0.5rem;">
                <i class="fas fa-map-pin" style="color:#2563EB;"></i> Nous trouver
            </h3>
            <p class="map-section-subtitle" style="text-align:center; color:#94a3b8; font-size:0.875rem; margin-bottom:1.5rem;">
                Notre centre médical est situé au cœur de Saint-Louis
            </p>
            <div class="map-container-modern" style="max-width: 800px; margin: 0 auto;">
                <div class="map-overlay">
                    <i class="fas fa-map-marker-alt me-1"></i> Saint-Louis, Sénégal
                </div>
                <iframe 
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d61429.47673229938!2d-16.518694990382948!3d16.01769551241829!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xec256c9b9dfa4b3%3A0xead10ba233d5e2f!2sSaint-Louis%2C%20S%C3%A9n%C3%A9gal!5e0!3m2!1sfr!2sfr!4v1700000000000!5m2!1sfr!2sfr" 
                    width="100%" 
                    height="350" 
                    style="border:0; border-radius: 28px;" 
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
            const message = document.getElementById('message');
            
            const clearInvalid = (field) => {
                field.classList.remove('is-invalid');
                const feedback = field.parentElement?.querySelector('.invalid-feedback-modern');
                if (feedback) feedback.style.display = 'none';
            };
            
            const showInvalid = (field) => {
                field.classList.add('is-invalid');
                const feedback = field.parentElement?.querySelector('.invalid-feedback-modern');
                if (feedback) feedback.style.display = 'flex';
            };
            
            clearInvalid(message);
            
            if (!message.value.trim() || message.value.trim().length < 10) {
                showInvalid(message);
                isValid = false;
            }
            
            if (!isValid) {
                event.preventDefault();
                message.scrollIntoView({ behavior: 'smooth', block: 'center' });
                message.focus();
            } else {
                const submitBtn = document.getElementById('submitBtn');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span> Envoi en cours...</span>';
                }
                // Redirection vers dashboard-medecin.php après soumission
                setTimeout(() => {
                    window.location.href = 'dashboard-medecin.php';
                }, 500);
            }
        });
        
        // Validation en temps réel pour le message uniquement
        const messageField = document.getElementById('message');
        
        if (messageField) {
            messageField.addEventListener('input', function() {
                if (this.value.trim().length >= 10) {
                    this.classList.remove('is-invalid');
                    const feedback = this.parentElement?.querySelector('.invalid-feedback-modern');
                    if (feedback) feedback.style.display = 'none';
                } else if (this.value.trim().length > 0) {
                    this.classList.add('is-invalid');
                    const feedback = this.parentElement?.querySelector('.invalid-feedback-modern');
                    if (feedback) feedback.style.display = 'flex';
                } else {
                    this.classList.remove('is-invalid');
                    const feedback = this.parentElement?.querySelector('.invalid-feedback-modern');
                    if (feedback) feedback.style.display = 'none';
                }
            });
        }
    }
})();
</script>

<?php include 'includes/footer.php'; ?>