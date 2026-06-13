<?php
// Fichier: apropos.php
require_once 'includes/config.php';
include 'includes/header.php';
?>

<!-- Hero Section avec overlay gradient -->
<section class="about-hero position-relative text-white" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); min-height: 62vh;">
    <div class="container h-100 py-5">
        <div class="row h-100 align-items-center">
            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                <span class="badge bg-light text-primary px-3 py-2 mb-3 fs-6">Notre histoire</span>
                <h1 class="display-3 fw-bold mb-4">Sama Docteur</h1>
                <p class="lead fs-4 mb-4 opacity-90">Révolutionner l'accès aux soins de santé au Sénégal</p>
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <span class="px-3 py-2 bg-white bg-opacity-10 rounded-pill">✓ +10 000 patients</span>
                    <span class="px-3 py-2 bg-white bg-opacity-10 rounded-pill">✓ +100 médecins</span>
                    <span class="px-3 py-2 bg-white bg-opacity-10 rounded-pill">✓ 4.9 ★ (avis)</span>
                </div>
            </div>
        </div>
    </div>
    <div class="wave-bottom">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 120">
            <path fill="#ffffff" fill-opacity="1" d="M0,64L80,69.3C160,75,320,85,480,80C640,75,800,53,960,48C1120,43,1280,53,1360,58.7L1440,64L1440,120L1360,120C1280,120,1120,120,960,120C800,120,640,120,480,120,320,120,160,120,80,120L0,120Z"></path>
        </svg>
    </div>
</section>

<div class="container py-5">
    <!-- Mission section -->
    <div class="row align-items-center g-5 mb-5 pb-5">
        <div class="col-lg-6" data-aos="fade-right">
            <span class="text-primary text-uppercase fw-semibold mb-2 d-block">Notre mission</span>
            <h2 class="display-5 fw-bold mb-4">Pour une santé accessible à tous</h2>
            <p class="lead text-secondary mb-4">Sama Docteur connecte les patients sénégalais aux meilleurs professionnels de santé, où qu'ils se trouvent.</p>
            <div class="vstack gap-3 mb-4">
                <div class="d-flex gap-3">
                    <i class="fas fa-check-circle text-primary fa-lg mt-1"></i>
                    <div>
                        <h6 class="fw-bold">Simplification administrative</h6>
                        <p class="text-secondary mb-0">Prenez rendez-vous en quelques clics, sans paperasse ni files d'attente.</p>
                    </div>
                </div>
                <div class="d-flex gap-3">
                    <i class="fas fa-clock text-primary fa-lg mt-1"></i>
                    <div>
                        <h6 class="fw-bold">Gain de temps précieux</h6>
                        <p class="text-secondary mb-0">Économisez jusqu'à 2h par rendez-vous grâce à notre système optimisé.</p>
                    </div>
                </div>
                <div class="d-flex gap-3">
                    <i class="fas fa-chart-line text-primary fa-lg mt-1"></i>
                    <div>
                        <h6 class="fw-bold">Transparence totale</h6>
                        <p class="text-secondary mb-0">Consultez les avis, tarifs et disponibilités en temps réel.</p>
                    </div>
                </div>
            </div>
            <div class="pt-3">
                <div class="d-flex align-items-center gap-4 flex-wrap">
                    <div>
                        <div class="display-6 fw-bold text-primary">94%</div>
                        <div class="text-secondary">de satisfaction</div>
                    </div>
                    <div>
                        <div class="display-6 fw-bold text-primary">24/7</div>
                        <div class="text-secondary">disponibilité</div>
                    </div>
                    <div>
                        <div class="display-6 fw-bold text-primary">100%</div>
                        <div class="text-secondary">données sécurisées</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6" data-aos="fade-left">
            <div class="position-relative">
                <div class="bg-primary rounded-4 p-4 shadow-lg position-relative z-1" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="text-white p-4 text-center">
                        <i class="fas fa-quote-left fa-2x opacity-50 mb-3"></i>
                        <p class="fs-4 fst-italic">"Grâce à Sama Docteur, j'ai trouvé un spécialiste en moins de 24h alors que j'attendais depuis 3 mois."</p>
                        <div class="d-flex align-items-center justify-content-center gap-2 mt-3">
                            <img src="assets/images/docs.jpg" alt="Patient" class="rounded-circle" width="40" height="40" onerror="this.src='assets/images/doc3.jpg'">
                            <div>
                                <div class="fw-bold">Fatou Diop</div>
                                <small>Patiente à Dakar</small>
                            </div>
                        </div>
                    </div>
                </div>
                <img src="assets/images/hero2.jpg" alt="Sama Docteur" class="position-absolute top-0 start-0 w-100 h-100 rounded-4" style="transform: translate(15px, 15px); z-index: 0; object-fit: contain; object-position: center; background-color: #fff;" onerror="this.src='assets/images/doc4.jpg'" />
            </div>
        </div>
    </div>

    <!-- Timeline section -->
    <div class="row mt-5 pt-5">
        <div class="col-12 text-center mb-5" data-aos="fade-up">
            <span class="text-primary text-uppercase fw-semibold">Notre parcours</span>
            <h2 class="display-5 fw-bold mt-2">L'histoire de Sama Docteur</h2>
            <p class="text-secondary mx-auto" style="max-width: 600px;">Une aventure humaine et technologique au service de la santé sénégalaise</p>
        </div>
    </div>
    
    <div class="row g-4 mb-5 pb-5">
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
            <div class="card border-0 shadow-sm h-100 position-relative overflow-hidden">
                <div class="position-absolute top-0 start-0 w-100 h-2 bg-primary" style="height: 4px;"></div>
                <div class="card-body p-4 text-center">
                    <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 70px; height: 70px;">
                        <i class="fas fa-flag-checkered fa-2x text-primary"></i>
                    </div>
                    <div class="display-4 fw-bold text-primary mb-2">2020</div>
                    <h5 class="fw-bold mb-3">La naissance</h5>
                    <p class="text-secondary mb-0">Création de Sama Docteur avec la vision de digitaliser la santé au Sénégal. 50 premiers médecins inscrits.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
            <div class="card border-0 shadow-sm h-100 position-relative overflow-hidden">
                <div class="position-absolute top-0 start-0 w-100 h-2 bg-primary" style="height: 4px;"></div>
                <div class="card-body p-4 text-center">
                    <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 70px; height: 70px;">
                        <i class="fas fa-chart-line fa-2x text-primary"></i>
                    </div>
                    <div class="display-4 fw-bold text-primary mb-2">2022</div>
                    <h5 class="fw-bold mb-3">L'expansion</h5>
                    <p class="text-secondary mb-0">Expansion nationale avec +200 médecins partenaires et 5000 rendez-vous mensuels.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
            <div class="card border-0 shadow-sm h-100 position-relative overflow-hidden">
                <div class="position-absolute top-0 start-0 w-100 h-2 bg-primary" style="height: 4px;"></div>
                <div class="card-body p-4 text-center">
                    <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 70px; height: 70px;">
                        <i class="fas fa-award fa-2x text-primary"></i>
                    </div>
                    <div class="display-4 fw-bold text-primary mb-2">2024</div>
                    <h5 class="fw-bold mb-3">La consécration</h5>
                    <p class="text-secondary mb-0">Reconnue meilleure plateforme e-santé d'Afrique de l'Ouest. +50 000 utilisateurs actifs.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Team section modernisée -->
    <div class="row mt-5 pt-5">
        <div class="col-12 text-center mb-5" data-aos="fade-up">
            <span class="text-primary text-uppercase fw-semibold">Notre équipe</span>
            <h2 class="display-5 fw-bold mt-2">Des passionnés à votre service</h2>
            <p class="text-secondary mx-auto" style="max-width: 600px;">Une équipe multidisciplinaire dédiée à votre bien-être</p>
        </div>
    </div>
    
    <div class="row g-4 mb-5 pb-5">
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
            <div class="card border-0 shadow-sm h-100 text-center">
                <div class="card-body p-4">
                    <div class="bg-gradient-primary rounded-circle d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 120px; height: 120px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fas fa-user-md fa-3x text-white"></i>
                    </div>
                    <h5 class="fw-bold mb-1">Dr Mamadou Diallo</h5>
                    <p class="text-primary mb-2">Fondateur & CEO</p>
                    <p class="small text-secondary mb-3">Médecin généraliste, diplômé de l'UCAD, passionné par l'innovation médicale.</p>
                    <div class="d-flex justify-content-center gap-2">
                        <a href="#" class="text-secondary"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" class="text-secondary"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
            <div class="card border-0 shadow-sm h-100 text-center">
                <div class="card-body p-4">
                    <div class="bg-gradient-primary rounded-circle d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 120px; height: 120px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fas fa-user-circle fa-3x text-white"></i>
                    </div>
                    <h5 class="fw-bold mb-1">Aminata Sow</h5>
                    <p class="text-primary mb-2">Directrice Médicale</p>
                    <p class="small text-secondary mb-3">Ancienne médecin chef au CHNU de Fann, expertise en santé publique.</p>
                    <div class="d-flex justify-content-center gap-2">
                        <a href="#" class="text-secondary"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" class="text-secondary"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
            <div class="card border-0 shadow-sm h-100 text-center">
                <div class="card-body p-4">
                    <div class="bg-gradient-primary rounded-circle d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 120px; height: 120px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fas fa-laptop-code fa-3x text-white"></i>
                    </div>
                    <h5 class="fw-bold mb-1">Oumar Ndiaye</h5>
                    <p class="text-primary mb-2">CTO</p>
                    <p class="small text-secondary mb-3">Expert en IA et e-santé, ancien lead tech chez JokkoLabs.</p>
                    <div class="d-flex justify-content-center gap-2">
                        <a href="#" class="text-secondary"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" class="text-secondary"><i class="fab fa-github"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Values section avec icônes modernisées -->
    <div class="row mt-5 pt-5">
        <div class="col-12 text-center mb-5" data-aos="fade-up">
            <span class="text-primary text-uppercase fw-semibold">Nos valeurs</span>
            <h2 class="display-5 fw-bold mt-2">Notre philosophie</h2>
            <p class="text-secondary mx-auto" style="max-width: 600px;">Des principes forts qui guident chaque action au quotidien</p>
        </div>
    </div>
    
    <div class="row g-4">
        <div class="col-md-3" data-aos="fade-up" data-aos-delay="100">
            <div class="card border-0 shadow-sm h-100 text-center">
                <div class="card-body p-4">
                    <div class="mb-3">
                        <i class="fas fa-heart fa-3x text-primary"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Empathie</h5>
                    <p class="small text-secondary mb-0">À l'écoute des patients et professionnels pour une expérience humaine.</p>
                </div>
            </div>
        </div>
        <div class="col-md-3" data-aos="fade-up" data-aos-delay="200">
            <div class="card border-0 shadow-sm h-100 text-center">
                <div class="card-body p-4">
                    <div class="mb-3">
                        <i class="fas fa-handshake fa-3x text-primary"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Confiance</h5>
                    <p class="small text-secondary mb-0">Transparence totale et données médicales ultra-sécurisées.</p>
                </div>
            </div>
        </div>
        <div class="col-md-3" data-aos="fade-up" data-aos-delay="300">
            <div class="card border-0 shadow-sm h-100 text-center">
                <div class="card-body p-4">
                    <div class="mb-3">
                        <i class="fas fa-microscope fa-3x text-primary"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Excellence</h5>
                    <p class="small text-secondary mb-0">Sélection rigoureuse des meilleurs professionnels de santé.</p>
                </div>
            </div>
        </div>
        <div class="col-md-3" data-aos="fade-up" data-aos-delay="400">
            <div class="card border-0 shadow-sm h-100 text-center">
                <div class="card-body p-4">
                    <div class="mb-3">
                        <i class="fas fa-globe fa-3x text-primary"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Innovation</h5>
                    <p class="small text-secondary mb-0">Solutions technologiques de pointe adaptées au contexte sénégalais.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Call to action -->
    <div class="row mt-5 pt-5">
        <div class="col-12">
            <div class="bg-gradient-primary rounded-4 p-5 text-white text-center" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);" data-aos="zoom-in">
                <h3 class="display-6 fw-bold mb-3">Prêt à prendre soin de votre santé ?</h3>
                <p class="mb-4 opacity-90">Rejoignez les milliers de patients qui nous font confiance chaque jour</p>
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <a href="prendre-rendezvous.php" class="btn btn-light btn-lg px-5">Prendre RDV</a>
                    <a href="contact.php" class="btn btn-outline-light btn-lg px-5">Nous contacter</a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .about-hero {
        position: relative;
        overflow: hidden;
    }
    
    .wave-bottom {
        position: absolute;
        bottom: -2px;
        left: 0;
        width: 100%;
        line-height: 0;
    }
    
    .bg-gradient-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    .card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 1rem 3rem rgba(0,0,0,0.1) !important;
    }
    
    .btn {
        border-radius: 50px;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .btn-light:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }
    
    .btn-outline-light:hover {
        transform: translateY(-2px);
    }
    
    @media (max-width: 768px) {
        .display-3 {
            font-size: 2.5rem;
        }
        .display-5 {
            font-size: 2rem;
        }
    }
</style>

<?php include 'includes/footer.php'; ?>