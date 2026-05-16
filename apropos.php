<?php
// Fichier: apropos.php
require_once 'includes/config.php';
include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row align-items-center">
        <div class="col-lg-6 mb-4" data-aos="fade-right">
            <h1 class="display-5 mb-4">À propos de Sama Docteur</h1>
            <p class="lead">Notre mission est de faciliter l'accès aux soins de santé au Sénégal.</p>
            <p>Sama Docteur est une plateforme innovante de prise de rendez-vous médicaux en ligne. Nous mettons en relation les patients avec les meilleurs médecins spécialistes du Sénégal.</p>
            <p>Notre objectif est de simplifier la gestion des rendez-vous médicaux, réduire les temps d'attente et améliorer l'accès aux soins pour tous.</p>
            <div class="mt-4">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-check-circle fa-2x text-primary me-3"></i>
                            <div>
                                <h6 class="mb-0">Qualité garantie</h6>
                                <small class="text-secondary">Médecins vérifiés</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-clock fa-2x text-primary me-3"></i>
                            <div>
                                <h6 class="mb-0">Disponible 24/7</h6>
                                <small class="text-secondary">Prenez RDV à tout moment</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-shield-alt fa-2x text-primary me-3"></i>
                            <div>
                                <h6 class="mb-0">Données sécurisées</h6>
                                <small class="text-secondary">Confidentialité garantie</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-headset fa-2x text-primary me-3"></i>
                            <div>
                                <h6 class="mb-0">Support client</h6>
                                <small class="text-secondary">Assistance 7j/7</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6" data-aos="fade-left">
            <img src="assets/img/about-illustration.svg" alt="About Us" class="img-fluid" onerror="this.src='https://via.placeholder.com/500x400?text=Sama+Docteur'">
        </div>
    </div>
    
    <!-- Notre histoire -->
    <div class="row mt-5 pt-5">
        <div class="col-12 text-center mb-5" data-aos="fade-up">
            <h2>Notre histoire</h2>
            <p class="text-secondary">Comment tout a commencé</p>
        </div>
        <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="100">
            <div class="card text-center h-100">
                <div class="card-body">
                    <i class="fas fa-flag-checkered fa-3x text-primary mb-3"></i>
                    <h5>2020</h5>
                    <p>Création de Sama Docteur avec la vision de digitaliser la santé au Sénégal</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="200">
            <div class="card text-center h-100">
                <div class="card-body">
                    <i class="fas fa-chart-line fa-3x text-primary mb-3"></i>
                    <h5>2022</h5>
                    <p>Expansion à travers tout le Sénégal avec plus de 50 médecins partenaires</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="300">
            <div class="card text-center h-100">
                <div class="card-body">
                    <i class="fas fa-award fa-3x text-primary mb-3"></i>
                    <h5>2024</h5>
                    <p>Reconnue comme la meilleure plateforme de e-santé au Sénégal</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Notre équipe -->
    <div class="row mt-5 pt-5">
        <div class="col-12 text-center mb-5" data-aos="fade-up">
            <h2>Notre équipe</h2>
            <p class="text-secondary">Des professionnels dévoués à votre santé</p>
        </div>
        <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="100">
            <div class="card text-center h-100">
                <div class="card-body">
                    <i class="fas fa-user-md fa-4x text-primary mb-3"></i>
                    <h5>Dr Mamadou Diallo</h5>
                    <p class="text-primary">Fondateur & CEO</p>
                    <p class="small text-secondary">Médecin généraliste avec plus de 15 ans d'expérience</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="200">
            <div class="card text-center h-100">
                <div class="card-body">
                    <i class="fas fa-user-circle fa-4x text-primary mb-3"></i>
                    <h5>Aminata Sow</h5>
                    <p class="text-primary">Directrice Médicale</p>
                    <p class="small text-secondary">Spécialiste en santé publique</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="300">
            <div class="card text-center h-100">
                <div class="card-body">
                    <i class="fas fa-laptop-code fa-4x text-primary mb-3"></i>
                    <h5>Oumar Ndiaye</h5>
                    <p class="text-primary">Chef de projet IT</p>
                    <p class="small text-secondary">Expert en solutions e-santé</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Nos valeurs -->
    <div class="row mt-5 pt-5">
        <div class="col-12 text-center mb-5" data-aos="fade-up">
            <h2>Nos valeurs</h2>
            <p class="text-secondary">Ce qui nous guide au quotidien</p>
        </div>
        <div class="col-md-3 mb-4" data-aos="fade-up" data-aos-delay="100">
            <div class="card text-center h-100">
                <div class="card-body">
                    <i class="fas fa-heart fa-3x text-primary mb-3"></i>
                    <h6>Empathie</h6>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4" data-aos="fade-up" data-aos-delay="200">
            <div class="card text-center h-100">
                <div class="card-body">
                    <i class="fas fa-handshake fa-3x text-primary mb-3"></i>
                    <h6>Confiance</h6>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4" data-aos="fade-up" data-aos-delay="300">
            <div class="card text-center h-100">
                <div class="card-body">
                    <i class="fas fa-microscope fa-3x text-primary mb-3"></i>
                    <h6>Excellence</h6>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4" data-aos="fade-up" data-aos-delay="400">
            <div class="card text-center h-100">
                <div class="card-body">
                    <i class="fas fa-globe fa-3x text-primary mb-3"></i>
                    <h6>Innovation</h6>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>