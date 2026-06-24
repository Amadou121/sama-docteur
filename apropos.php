<?php
// Fichier: apropos.php
require_once 'includes/config.php';
include 'includes/header.php';
?>

<!-- Hero Section complètement repensée -->
<section class="about-hero">
    <div class="hero-background"></div>
    <div class="container">
        <div class="hero-content">
            <div class="hero-badge">
                <i class="fas fa-heartbeat"></i> Bienvenue chez Sama Docteur
            </div>
            <h1>Nous prenons soin<br>de <span class="highlight">votre santé</span></h1>
            <p>Depuis 2020, nous révolutionnons l'accès aux soins au Sénégal en connectant patients et médecins sur une plateforme simple, rapide et sécurisée.</p>
            <div class="hero-buttons">
                <a href="prendre-rendez-vous.php" class="btn-primary">
                    <i class="fas fa-calendar-check"></i> Prendre rendez-vous
                </a>
                <a href="contact.php" class="btn-secondary">
                    <i class="fas fa-headset"></i> Nous contacter
                </a>
            </div>
        </div>
    </div>
    <div class="hero-wave">
        <svg viewBox="0 0 1440 120" preserveAspectRatio="none">
            <path d="M0,64L80,69.3C160,75,320,85,480,80C640,75,800,53,960,48C1120,43,1280,53,1360,58.7L1440,64L1440,120L1360,120C1280,120,1120,120,960,120C800,120,640,120,480,120,320,120,160,120,80,120L0,120Z"></path>
        </svg>
    </div>
</section>

<!-- Section Notre histoire -->
<section class="story-section">
    <div class="container">
        <div class="story-grid">
            <div class="story-content">
                <div class="section-label">
                    <span>Notre histoire</span>
                </div>
                <h2>Une aventure humaine <span class="text-gradient">au service de tous</span></h2>
                <p>Sama Docteur est né d'un constat simple : l'accès aux soins au Sénégal devait être simplifié. Notre fondateur, le Dr Mamadou Diallo, a vécu les difficultés des patients à trouver rapidement un médecin. Aujourd'hui, nous sommes fiers d'être la plateforme de référence en matière de e-santé au Sénégal.</p>
                <div class="story-stats">
                    <div class="stat">
                        <div class="stat-number">2020</div>
                        <div class="stat-label">Année de création</div>
                    </div>
                    <div class="stat">
                        <div class="stat-number">50k+</div>
                        <div class="stat-label">Patients accompagnés</div>
                    </div>
                    <div class="stat">
                        <div class="stat-number">15k+</div>
                        <div class="stat-label">Rendez-vous / mois</div>
                    </div>
                </div>
            </div>
                <div class="story-image">
                    <div class="story-hero">
                        <img src="assets/images/Mamadou Diallo.jpg" alt="Dr Mamadou Diallo" class="doctor-image doctor-hero-image">
                        <div class="doctor-hero-overlay">
                            <h3>Dr Mamadou Diallo</h3>
                            <p>Fondateur &amp; CEO — Médecin généraliste</p>
                            <a href="prendre-rendez-vous.php" class="btn-primary">Prendre rendez-vous</a>
                        </div>
                    </div>
                </div>
        </div>
    </div>
</section>

<!-- Section Mission -->
<section class="mission-section">
    <div class="container">
        <div class="section-header">
            <div class="section-label">
                <span>Notre mission</span>
            </div>
            <h2>Pourquoi nous faisons <span class="text-gradient">ce que nous faisons</span></h2>
            <p>Une mission claire qui guide chacune de nos actions au quotidien</p>
        </div>
        <div class="mission-grid">
            <div class="mission-card">
                <div class="mission-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3>Accessibilité pour tous</h3>
                <p>Rendre les soins de santé accessibles à chaque Sénégalais, où qu'il se trouve, à tout moment.</p>
            </div>
            <div class="mission-card">
                <div class="mission-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <h3>Simplicité et rapidité</h3>
                <p>Réduire les délais d'attente et simplifier la prise de rendez-vous médical.</p>
            </div>
            <div class="mission-card">
                <div class="mission-icon">
                    <i class="fas fa-lock"></i>
                </div>
                <h3>Sécurité des données</h3>
                <p>Protéger la confidentialité des informations médicales de nos utilisateurs.</p>
            </div>
            <div class="mission-card">
                <div class="mission-icon">
                    <i class="fas fa-hand-holding-heart"></i>
                </div>
                <h3>Qualité des soins</h3>
                <p>Garantir l'excellence médicale en sélectionnant rigoureusement nos partenaires.</p>
            </div>
        </div>
    </div>
</section>

<!-- Section Timeline avec design vertical -->
<section class="timeline-section">
    <div class="container">
        <div class="section-header">
            <div class="section-label">
                <span>Notre parcours</span>
            </div>
            <h2>Les étapes clés de <span class="text-gradient">notre aventure</span></h2>
        </div>
        <div class="timeline">
            <div class="timeline-item">
                <div class="timeline-dot"></div>
                <div class="timeline-content">
                    <div class="timeline-year">2020</div>
                    <h3>La naissance de Sama Docteur</h3>
                    <p>Création de la plateforme avec 50 médecins partenaires à Dakar. Première version de notre application de prise de rendez-vous.</p>
                </div>
            </div>
            <div class="timeline-item">
                <div class="timeline-dot"></div>
                <div class="timeline-content">
                    <div class="timeline-year">2021</div>
                    <h3>Expansion à toute la région</h3>
                    <p>Extension à Thiès, Mbour et Saint-Louis. Lancement de notre système de téléconsultation.</p>
                </div>
            </div>
            <div class="timeline-item">
                <div class="timeline-dot"></div>
                <div class="timeline-content">
                    <div class="timeline-year">2022</div>
                    <h3>Reconnaissance nationale</h3>
                    <p>Partenariat avec l'Ordre des Médecins du Sénégal. Plus de 200 médecins nous font confiance.</p>
                </div>
            </div>
            <div class="timeline-item">
                <div class="timeline-dot"></div>
                <div class="timeline-content">
                    <div class="timeline-year">2024</div>
                    <h3>Leader de l'e-santé</h3>
                    <p>Reconnue meilleure plateforme e-santé d'Afrique de l'Ouest. Plus de 50 000 utilisateurs actifs.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Section Équipe moderne -->
<section class="team-section">
    <div class="container">
        <div class="section-header">
            <div class="section-label">
                <span>Notre équipe</span>
            </div>
            <h2>Des experts passionnés <span class="text-gradient">à votre service</span></h2>
            <p>Une équipe multidisciplinaire dédiée à votre bien-être</p>
        </div>
        <div class="team-grid">
            <div class="team-member">
                <div class="member-image">
                    <div class="member-avatar">
                        <img src="assets/images/Mamadou Diallo.jpg" alt="Dr Mamadou Diallo">
                    </div>
                    <div class="member-social">
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
                <div class="member-info">
                    <h3>Dr Mamadou Diallo</h3>
                    <span class="member-role">Fondateur & CEO</span>
                    <p>Médecin généraliste, diplômé de l'UCAD, passionné par l'innovation médicale et la transformation numérique de la santé.</p>
                </div>
            </div>
            <div class="team-member">
                <div class="member-image">
                    <div class="member-avatar">
                        <img src="assets/images/Aminata Sow.jpg" alt="Aminata Sow">
                    </div>
                    <div class="member-social">
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
                <div class="member-info">
                    <h3>Aminata Sow</h3>
                    <span class="member-role">Directrice Médicale</span>
                    <p>Ancienne médecin chef au CHNU de Fann, expertise en santé publique et en organisation des soins.</p>
                </div>
            </div>
            <div class="team-member">
                <div class="member-image">
                    <div class="member-avatar">
                        <img src="assets/images/Oumar Ndiaye.jpg" alt="Oumar Ndiaye">
                    </div>
                    <div class="member-social">
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
                <div class="member-info">
                    <h3>Oumar Ndiaye</h3>
                    <span class="member-role">Directeur Technique</span>
                    <p>Expert en IA et e-santé, ancien lead tech chez JokkoLabs, passionné par les technologies au service de la santé.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Section Valeurs avec design carte -->
<section class="values-section">
    <div class="container">
        <div class="section-header">
            <div class="section-label">
                <span>Nos valeurs</span>
            </div>
            <h2>Ce qui nous <span class="text-gradient">anime au quotidien</span></h2>
        </div>
        <div class="values-grid">
            <div class="value-card">
                <div class="value-icon">
                    <i class="fas fa-heart"></i>
                </div>
                <h3>Empathie</h3>
                <p>À l'écoute des patients et professionnels pour une expérience humaine unique.</p>
                <div class="value-number">01</div>
            </div>
            <div class="value-card">
                <div class="value-icon">
                    <i class="fas fa-handshake"></i>
                </div>
                <h3>Confiance</h3>
                <p>Transparence totale et données médicales ultra-sécurisées.</p>
                <div class="value-number">02</div>
            </div>
            <div class="value-card">
                <div class="value-icon">
                    <i class="fas fa-microscope"></i>
                </div>
                <h3>Excellence</h3>
                <p>Sélection rigoureuse des meilleurs professionnels de santé.</p>
                <div class="value-number">03</div>
            </div>
            <div class="value-card">
                <div class="value-icon">
                    <i class="fas fa-globe"></i>
                </div>
                <h3>Innovation</h3>
                <p>Solutions technologiques adaptées au contexte sénégalais.</p>
                <div class="value-number">04</div>
            </div>
        </div>
    </div>
</section>

<!-- Section Témoignages -->
<section class="testimonials-section">
    <div class="container">
        <div class="section-header">
            <div class="section-label">
                <span>Témoignages</span>
            </div>
            <h2>Ils nous <span class="text-gradient">font confiance</span></h2>
        </div>
        <div class="testimonials-grid">
            <div class="testimonial-card">
                <div class="testimonial-quote">
                    <i class="fas fa-quote-left"></i>
                </div>
                <p>"Grâce à Sama Docteur, j'ai trouvé un spécialiste en moins de 24h. Un service exceptionnel !"</p>
                <div class="testimonial-author">
                    <div class="author-avatar">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <div class="author-info">
                        <h4>Fatou Diop</h4>
                        <span>Patiente à Dakar</span>
                    </div>
                </div>
            </div>
            <div class="testimonial-card">
                <div class="testimonial-quote">
                    <i class="fas fa-quote-left"></i>
                </div>
                <p>"La plateforme a transformé ma pratique médicale. Je gagne un temps précieux au quotidien."</p>
                <div class="testimonial-author">
                    <div class="author-avatar">
                        <i class="fas fa-user-md"></i>
                    </div>
                    <div class="author-info">
                        <h4>Dr Cheikh Diouf</h4>
                        <span>Médecin généraliste</span>
                    </div>
                </div>
            </div>
            <div class="testimonial-card">
                <div class="testimonial-quote">
                    <i class="fas fa-quote-left"></i>
                </div>
                <p>"Une équipe à l'écoute et professionnelle. Je recommande vivement Sama Docteur !"</p>
                <div class="testimonial-author">
                    <div class="author-avatar">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <div class="author-info">
                        <h4>Moussa Diallo</h4>
                        <span>Patient à Thiès</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Section Call to Action -->
<section class="cta-section">
    <div class="container">
        <div class="cta-wrapper">
            <div class="cta-content">
                <h2>Prêt à prendre soin de votre santé ?</h2>
                <p>Rejoignez les milliers de patients qui nous font confiance chaque jour</p>
                <div class="cta-buttons">
                    <a href="prendre-rendezvous.php" class="btn-primary">
                        <i class="fas fa-calendar-plus"></i> Prendre rendez-vous
                    </a>
                    <a href="specialites.php" class="btn-outline-light">
                        <i class="fas fa-search"></i> Trouver un médecin
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* Reset et styles de base */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

/* Hero Section */
.about-hero {
    position: relative;
    min-height: 70vh;
    display: flex;
    align-items: center;
    overflow: hidden;
}

.hero-background {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 50%, #3b82f6 100%);
    z-index: 0;
}

.hero-background::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="rgba(255,255,255,0.05)" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,154.7C960,171,1056,181,1152,165.3C1248,149,1344,107,1392,85.3L1440,64L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>');
    background-repeat: no-repeat;
    background-position: bottom;
    background-size: cover;
    opacity: 0.3;
}

.container {
    position: relative;
    z-index: 1;
    max-width: 1280px;
    margin: 0 auto;
    padding: 0 24px;
}

.hero-content {
    text-align: center;
    color: white;
    max-width: 800px;
    margin: 0 auto;
}

.hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(10px);
    padding: 8px 20px;
    border-radius: 50px;
    font-size: 14px;
    font-weight: 500;
    margin-bottom: 24px;
}

.hero-content h1 {
    font-size: 56px;
    font-weight: 800;
    line-height: 1.2;
    margin-bottom: 24px;
}

.highlight {
    background: linear-gradient(135deg, #60a5fa, #a78bfa);
    background-clip: text;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.hero-content p {
    font-size: 18px;
    line-height: 1.6;
    margin-bottom: 40px;
    opacity: 0.9;
}

.hero-buttons {
    display: flex;
    gap: 16px;
    justify-content: center;
    flex-wrap: wrap;
}

.btn-primary, .btn-secondary {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 14px 32px;
    border-radius: 50px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
}

.btn-primary {
    background: white;
    color: #1e3a8a;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
}

.btn-secondary {
    background: rgba(255,255,255,0.15);
    color: white;
    backdrop-filter: blur(10px);
}

.btn-secondary:hover {
    background: rgba(255,255,255,0.25);
    transform: translateY(-2px);
}

.btn-outline-light {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 14px 32px;
    border-radius: 50px;
    font-weight: 600;
    text-decoration: none;
    background: transparent;
    border: 2px solid white;
    color: white;
    transition: all 0.3s ease;
}

.btn-outline-light:hover {
    background: white;
    color: #1e3a8a;
    transform: translateY(-2px);
}

.hero-wave {
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 100%;
    line-height: 0;
}

.hero-wave svg {
    width: 100%;
    height: auto;
    fill: #ffffff;
}

/* Section commune */
section {
    padding: 80px 0;
}

.section-header {
    text-align: center;
    margin-bottom: 60px;
}

.section-label {
    margin-bottom: 16px;
}

.section-label span {
    background: linear-gradient(135deg, #3b82f6, #8b5cf6);
    padding: 6px 16px;
    border-radius: 50px;
    color: white;
    font-size: 14px;
    font-weight: 600;
}

.section-header h2 {
    font-size: 48px;
    font-weight: 700;
    margin-bottom: 16px;
}

.text-gradient {
    background: linear-gradient(135deg, #3b82f6, #8b5cf6);
    background-clip: text;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.section-header p {
    font-size: 18px;
    color: #64748b;
    max-width: 600px;
    margin: 0 auto;
}

/* Story Section */
.story-section {
    background: white;
}

.story-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 60px;
    align-items: center;
}

.story-content h2 {
    font-size: 48px;
    font-weight: 700;
    margin-bottom: 24px;
}

.story-content p {
    font-size: 18px;
    line-height: 1.6;
    color: #64748b;
    margin-bottom: 40px;
}

.story-stats {
    display: flex;
    gap: 40px;
}

.stat-number {
    font-size: 32px;
    font-weight: 800;
    color: #3b82f6;
    margin-bottom: 8px;
}

.stat-label {
    color: #64748b;
    font-size: 14px;
}

.story-image {
    position: relative;
}

.image-wrapper {
    position: relative;
    min-height: 400px;
}

.image-card {
    width: 300px;
    height: 400px;
    background: linear-gradient(135deg, #3b82f6, #8b5cf6);
    border-radius: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
}

.image-card i {
    font-size: 100px;
    color: white;
    opacity: 0.5;
}

.floating-card {
    position: absolute;
    background: white;
    padding: 12px 24px;
    border-radius: 50px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
}

.floating-card i {
    color: #3b82f6;
}

.card1 {
    top: 20%;
    left: -20px;
}

.card2 {
    bottom: 20%;
    right: -20px;
}

/* Mission Section */
.mission-section {
    background: #f8fafc;
}

.mission-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 30px;
}

.mission-card {
    background: white;
    padding: 32px;
    border-radius: 20px;
    text-align: center;
    transition: all 0.3s ease;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.mission-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 30px rgba(0,0,0,0.1);
}

.mission-icon {
    width: 70px;
    height: 70px;
    background: linear-gradient(135deg, #3b82f6, #8b5cf6);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 24px;
}

.mission-icon i {
    font-size: 32px;
    color: white;
}

.mission-card h3 {
    font-size: 22px;
    font-weight: 600;
    margin-bottom: 12px;
}

.mission-card p {
    color: #64748b;
    line-height: 1.6;
}

/* Timeline Section */
.timeline-section {
    background: white;
}

.timeline {
    max-width: 800px;
    margin: 0 auto;
    position: relative;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
    width: 2px;
    height: 100%;
    background: linear-gradient(135deg, #3b82f6, #8b5cf6);
}

.timeline-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 50px;
    position: relative;
}

.timeline-item:nth-child(even) {
    flex-direction: row-reverse;
}

.timeline-dot {
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
    width: 20px;
    height: 20px;
    background: #3b82f6;
    border-radius: 50%;
    border: 4px solid white;
    box-shadow: 0 0 0 4px rgba(59,130,246,0.2);
}

.timeline-content {
    width: 45%;
    background: #f8fafc;
    padding: 24px;
    border-radius: 16px;
    transition: all 0.3s ease;
}

.timeline-content:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

.timeline-year {
    font-size: 14px;
    color: #3b82f6;
    font-weight: 600;
    margin-bottom: 8px;
}

.timeline-content h3 {
    font-size: 20px;
    font-weight: 600;
    margin-bottom: 12px;
}

.timeline-content p {
    color: #64748b;
    line-height: 1.6;
}

/* Team Section */
.team-section {
    background: #f8fafc;
}

.team-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 40px;
}

.team-member {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    transition: all 0.3s ease;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.team-member:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 30px rgba(0,0,0,0.1);
}

.member-image {
    position: relative;
    height: 300px;
    background: linear-gradient(135deg, #3b82f6, #8b5cf6);
    display: flex;
    align-items: center;
    justify-content: center;
}

.member-avatar i {
    font-size: 80px;
    color: white;
    opacity: 0.8;
}

.member-social {
    position: absolute;
    bottom: 20px;
    left: 0;
    right: 0;
    display: flex;
    justify-content: center;
    gap: 12px;
}

.member-social a {
    width: 40px;
    height: 40px;
    background: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #3b82f6;
    text-decoration: none;
    transition: all 0.3s ease;
}

.member-social a:hover {
    transform: translateY(-3px);
    background: #3b82f6;
    color: white;
}

.member-info {
    padding: 24px;
    text-align: center;
}

.member-info h3 {
    font-size: 22px;
    font-weight: 600;
    margin-bottom: 4px;
}

.member-role {
    display: inline-block;
    color: #3b82f6;
    font-weight: 500;
    margin-bottom: 12px;
}

.member-info p {
    color: #64748b;
    line-height: 1.6;
}

/* Values Section */
.values-section {
    background: white;
}

.values-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 30px;
}

.value-card {
    background: #f8fafc;
    padding: 32px;
    border-radius: 20px;
    text-align: center;
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
}

.value-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 30px rgba(0,0,0,0.1);
}

.value-icon {
    width: 70px;
    height: 70px;
    background: linear-gradient(135deg, #3b82f6, #8b5cf6);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 24px;
}

.value-icon i {
    font-size: 32px;
    color: white;
}

.value-card h3 {
    font-size: 22px;
    font-weight: 600;
    margin-bottom: 12px;
}

.value-card p {
    color: #64748b;
    line-height: 1.6;
}

.value-number {
    position: absolute;
    bottom: 16px;
    right: 16px;
    font-size: 48px;
    font-weight: 800;
    color: rgba(59,130,246,0.05);
}

/* Testimonials Section */
.testimonials-section {
    background: #f8fafc;
}

.testimonials-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 30px;
}

.testimonial-card {
    background: white;
    padding: 32px;
    border-radius: 20px;
    transition: all 0.3s ease;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.testimonial-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 30px rgba(0,0,0,0.1);
}

.testimonial-quote i {
    font-size: 40px;
    color: #3b82f6;
    opacity: 0.3;
    margin-bottom: 20px;
}

.testimonial-card p {
    font-size: 18px;
    line-height: 1.6;
    color: #1e293b;
    margin-bottom: 24px;
    font-style: italic;
}

.testimonial-author {
    display: flex;
    align-items: center;
    gap: 12px;
}

.author-avatar i {
    font-size: 48px;
    color: #94a3b8;
}

.author-info h4 {
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 4px;
}

.author-info span {
    font-size: 14px;
    color: #64748b;
}

/* CTA Section */
.cta-section {
    padding: 0 0 80px;
    background: white;
}

.cta-wrapper {
    background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 50%, #3b82f6 100%);
    border-radius: 30px;
    padding: 60px;
    text-align: center;
    color: white;
}

.cta-content h2 {
    font-size: 48px;
    font-weight: 700;
    margin-bottom: 16px;
}

.cta-content p {
    font-size: 18px;
    margin-bottom: 32px;
    opacity: 0.9;
}

.cta-buttons {
    display: flex;
    gap: 16px;
    justify-content: center;
    flex-wrap: wrap;
}

/* Responsive */
@media (max-width: 968px) {
    .hero-content h1 {
        font-size: 40px;
    }
    
    .story-grid {
        grid-template-columns: 1fr;
    }
    
    .timeline::before {
        left: 20px;
    }
    
    .timeline-item,
    .timeline-item:nth-child(even) {
        flex-direction: column;
        margin-left: 40px;
    }
    
    .timeline-dot {
        left: 10px;
    }
    
    .timeline-content {
        width: 100%;
        margin-bottom: 20px;
    }
    
    .section-header h2 {
        font-size: 32px;
    }
    
    .story-content h2 {
        font-size: 32px;
    }
    
    .cta-content h2 {
        font-size: 32px;
    }
}

@media (max-width: 768px) {
    section {
        padding: 60px 0;
    }
    
    .hero-content h1 {
        font-size: 32px;
    }
    
    .hero-buttons {
        flex-direction: column;
    }
    
    .btn-primary, .btn-secondary {
        justify-content: center;
    }
    
    .story-stats {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
}
</style>

<?php include 'includes/footer.php'; ?>