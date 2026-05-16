<?php
// ==============================
// Fichier : index.php
// ==============================

require_once 'includes/config.php';
include 'includes/header.php';

// Récupération spécialités
$stmt = $pdo->query("SELECT * FROM specialites LIMIT 6");
$specialites = $stmt->fetchAll();

// Récupération médecins
$stmtMedecins = $pdo->query("
    SELECT m.*, s.nom AS specialite_nom
    FROM medecins m
    JOIN specialites s ON m.specialite_id = s.id
    WHERE m.est_disponible = 1
    LIMIT 4
");
$medecins = $stmtMedecins->fetchAll();
?>

<!-- HERO - Version réduite -->
<section class="hero-section py-3">
    <div class="container">
        <div class="row align-items-center">

            <div class="col-lg-6">
                <span class="badge bg-light text-primary p-2 mb-2" style="font-size: 0.8rem;">
                    <i class="fas fa-heartbeat"></i> Prenez soin de votre santé
                </span>

                <h1 class="hero-title" style="font-size: 2.2rem; margin-bottom: 1rem;">
                    Prenez rendez-vous <br>
                    avec le meilleur spécialiste
                </h1>

                <p class="hero-text" style="font-size: 1rem; margin-bottom: 1.5rem;">
                    Sama Docteur vous met en relation avec les meilleurs médecins spécialistes.
                    Prenez rendez-vous en ligne facilement et rapidement.
                </p>

                <a href="specialites.php" class="btn btn-primary px-4 py-2">
                    <i class="fas fa-calendar-check"></i>
                    Prendre rendez-vous
                </a>
            </div>

            <div class="col-lg-6 text-center">
                <img src="assets/images/docff.png"
                     class="img-fluid hero-image"
                     alt="Docteur"
                     style="max-width: 60%; height: auto;">
            </div>

        </div>

        <!-- SEARCH -->
        <div class="search-box shadow-lg mt-3" style="margin-top: 1rem !important;">
            <form action="specialites.php" method="GET">
                <div class="row g-2 align-items-center">

                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-0">
                                <i class="fas fa-stethoscope text-primary"></i>
                            </span>

                            <input type="text"
                                   name="specialite"
                                   class="form-control border-0"
                                   placeholder="Spécialité médicale">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-0">
                                <i class="fas fa-map-marker-alt text-primary"></i>
                            </span>

                            <input type="text"
                                   name="ville"
                                   class="form-control border-0"
                                   placeholder="Ville ou quartier">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <button class="btn btn-primary w-100 py-2">
                            <i class="fas fa-search"></i>
                            Rechercher
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>
</section>

<!-- SPECIALITES -->
<section class="py-5">
    <div class="container">

        <div class="text-center mb-5">
            <h2 class="section-title">
                Nos spécialités médicales
            </h2>

            <p class="section-subtitle">
                Des médecins experts dans leur domaine
            </p>
        </div>

        <div class="row g-4">

            <?php foreach($specialites as $specialite): ?>

            <div class="col-md-4 col-lg-2">

                <div class="specialite-card">

                    <div class="icon-box">
                        <i class="<?php echo $specialite['icone']; ?>"></i>
                    </div>

                    <h5>
                        <?php echo htmlspecialchars($specialite['nom']); ?>
                    </h5>

                    <a href="specialites.php?id=<?php echo $specialite['id']; ?>">
                        Voir les médecins
                    </a>

                </div>

            </div>

            <?php endforeach; ?>

        </div>

    </div>
</section>

<!-- MEDECINS -->
<section class="py-5 bg-light">
    <div class="container">

        <div class="text-center mb-5">

            <h2 class="section-title">
                Nos médecins
            </h2>

            <p class="section-subtitle">
                Des professionnels à votre écoute
            </p>

        </div>

        <div class="row g-4">

            <?php foreach($medecins as $medecin): ?>

            <div class="col-md-6 col-lg-3">

                <div class="doctor-card">

                    <img src="assets/images/doctor-placeholder.jpg"
                         alt="Doctor"
                         style="width: 110px; height: 110px; object-fit: cover; margin-bottom: 15px; border-radius: 50%;">

                    <h5>
                        <?php echo htmlspecialchars($medecin['nom_complet']); ?>
                    </h5>

                    <span class="specialite">
                        <?php echo htmlspecialchars($medecin['specialite_nom']); ?>
                    </span>

                    <p class="ville">
                        <i class="fas fa-map-marker-alt"></i>
                        <?php echo htmlspecialchars($medecin['ville']); ?>
                    </p>

                    <div class="price">
                        <?php echo number_format($medecin['tarif_consultation'],0,',',' '); ?> FCFA
                    </div>

                    <a href="prendre-rendez-vous.php?id=<?php echo $medecin['id']; ?>"
                       class="btn btn-primary w-100">

                        <i class="fas fa-calendar-check"></i>
                        Prendre RDV

                    </a>

                </div>

            </div>

            <?php endforeach; ?>

        </div>

    </div>
</section>

<!-- AVANTAGES -->
<section class="py-5">
    <div class="container">

        <div class="text-center mb-5">

            <h2 class="section-title">
                Pourquoi choisir Sama Docteur ?
            </h2>

            <p class="section-subtitle">
                Une plateforme moderne pour votre santé
            </p>

        </div>

        <div class="row g-4">

            <div class="col-md-4">

                <div class="advantage-card">

                    <i class="fas fa-clock"></i>

                    <h5>Prise de RDV rapide</h5>

                    <p>
                        Trouvez et réservez votre rendez-vous
                        en quelques clics.
                    </p>

                </div>

            </div>

            <div class="col-md-4">

                <div class="advantage-card">

                    <i class="fas fa-user-md"></i>

                    <h5>Médecins qualifiés</h5>

                    <p>
                        Des spécialistes vérifiés et expérimentés.
                    </p>

                </div>

            </div>

            <div class="col-md-4">

                <div class="advantage-card">

                    <i class="fas fa-shield-alt"></i>

                    <h5>Données sécurisées</h5>

                    <p>
                        Vos informations médicales sont protégées.
                    </p>

                </div>

            </div>

        </div>

    </div>
</section>

<!-- STATS -->
<section class="stats-section py-5">

    <div class="container">

        <div class="row text-center">

            <div class="col-md-3">
                <h2>50+</h2>
                <p>Médecins experts</p>
            </div>

            <div class="col-md-3">
                <h2>12+</h2>
                <p>Spécialités médicales</p>
            </div>

            <div class="col-md-3">
                <h2>1000+</h2>
                <p>Patients satisfaits</p>
            </div>

            <div class="col-md-3">
                <h2>24/7</h2>
                <p>Service disponible</p>
            </div>

        </div>

    </div>

</section>

<?php include 'includes/footer.php'; ?>