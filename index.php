<?php
// ==============================
// Fichier : index.php
// Page d'accueil - Sama Docteur
// ==============================

require_once 'includes/config.php';
include 'includes/header.php';

// Récupération spécialités
$stmt = $pdo->query("SELECT * FROM specialites LIMIT 6");
$specialites = $stmt->fetchAll();

// Récupération médecins avec leurs informations complètes
$stmtMedecins = $pdo->query("
    SELECT m.*, s.nom AS specialite_nom
    FROM medecins m
    JOIN specialites s ON m.specialite_id = s.id
    WHERE m.est_disponible = 1
    ORDER BY m.id
    LIMIT 4
");
$medecins = $stmtMedecins->fetchAll();

// Fonction pour obtenir la photo du médecin
function getDoctorPhoto($medecin) {
    // Vérifier si une photo personnalisée existe dans la base de données
    if (!empty($medecin['photo']) && file_exists('assets/images/medecins/' . $medecin['photo'])) {
        return 'assets/images/medecins/' . $medecin['photo'];
    }
    
    // Photos par défaut basées sur l'ID du médecin
    $defaultPhotos = [
        1 => 'assets/images/Dr Martin Dupuis.jpg',
        2 => 'assets/images/Dr Sophie Diallo.jpg',
        3 => 'assets/images/Dr Aliou Ndiaye.jpg',
        4 => 'assets/images/Dr Fatou Sow.jpg',
        5 => 'assets/images/docteur5.jpg',
        6 => 'assets/images/docteur6.jpg',
    ];
    
    // Utiliser une photo par défaut basée sur l'ID
    if (isset($defaultPhotos[$medecin['id']]) && file_exists($defaultPhotos[$medecin['id']])) {
        return $defaultPhotos[$medecin['id']];
    }
    
    // Fallback : utiliser doc2.jpg si aucune image n'existe
    if (file_exists('assets/images/doc2.jpg')) {
        return 'assets/images/doc2.jpg';
    }
    
    // Dernier recours : retourner null pour utiliser l'avatar
    return null;
}

// Fonction pour générer un avatar avec initiales
function getDoctorAvatar($nomComplet) {
    $initials = '';
    $parts = explode(' ', $nomComplet);
    foreach($parts as $part) {
        if (!empty($part)) {
            $initials .= mb_strtoupper(mb_substr($part, 0, 1));
        }
    }
    return 'https://ui-avatars.com/api/?name=' . urlencode($initials) . '&background=007bff&color=fff&size=120&rounded=true&bold=true&length=2';
}

// Déterminer la note aléatoire pour chaque médecin (pour la démo)
function getDoctorRating($medecinId) {
    $ratings = [
        1 => ['stars' => 5, 'count' => 234],
        2 => ['stars' => 4.8, 'count' => 187],
        3 => ['stars' => 4.9, 'count' => 312],
        4 => ['stars' => 4.7, 'count' => 156],
        5 => ['stars' => 5, 'count' => 98],
        6 => ['stars' => 4.6, 'count' => 145],
    ];
    return $ratings[$medecinId] ?? ['stars' => 4.5, 'count' => rand(50, 200)];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <meta name="description" content="Sama Docteur - Prenez rendez-vous en ligne avec les meilleurs médecins spécialistes. Consultation médicale facile et rapide.">
    <meta name="keywords" content="médecin, spécialiste, rendez-vous médical, consultation, santé, Sénégal, Saint-Louis">
    <meta name="author" content="Sama Docteur">
    <title>Sama Docteur - Prenez rendez-vous avec les meilleurs spécialistes</title>
    
    <style>
        /* ========== ANIMATIONS PERSONNALISÉES ========== */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes fadeInLeft {
            from {
                opacity: 0;
                transform: translateX(-50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        @keyframes fadeInRight {
            from {
                opacity: 0;
                transform: translateX(50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
            100% { transform: translateY(0px); }
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        
        @keyframes glow {
            0% { box-shadow: 0 0 5px rgba(0,123,255,0.2); }
            50% { box-shadow: 0 0 20px rgba(0,123,255,0.6); }
            100% { box-shadow: 0 0 5px rgba(0,123,255,0.2); }
        }
        
        @keyframes shimmer {
            0% { background-position: -1000px 0; }
            100% { background-position: 1000px 0; }
        }
        
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        
        /* ========== CLASSES D'ANIMATION ========== */
        .animate-fadeInUp {
            animation: fadeInUp 0.8s ease-out;
        }
        
        .animate-fadeInLeft {
            animation: fadeInLeft 0.8s ease-out;
        }
        
        .animate-fadeInRight {
            animation: fadeInRight 0.8s ease-out;
        }
        
        .animate-float {
            animation: float 3s ease-in-out infinite;
        }
        
        .animate-pulse {
            animation: pulse 2s ease-in-out infinite;
        }
        
        .animate-bounce {
            animation: bounce 1s ease-in-out infinite;
        }
        
        /* ========== STYLES GÉNÉRAUX ========== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
        }
        
        /* ========== HERO SECTION ========== */
        .hero-section {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: float 20s ease-in-out infinite;
        }
        
        .hero-image {
            transition: all 0.5s ease;
            filter: drop-shadow(0 10px 20px rgba(0,0,0,0.1));
        }
        
        .hero-image:hover {
            transform: scale(1.05) rotate(2deg);
            filter: drop-shadow(0 20px 30px rgba(0,0,0,0.2));
        }
        
        /* ========== SEARCH BOX ========== */
        .search-box {
            transition: all 0.3s ease;
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            border-radius: 60px;
            padding: 10px 20px;
        }
        
        .search-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 50px rgba(0,0,0,0.2) !important;
        }
        
        .search-box input:focus {
            box-shadow: none;
            outline: none;
            border-color: transparent;
        }
        
        .search-box input::placeholder {
            color: #999;
            font-size: 0.95rem;
        }
        
        /* ========== SPECIALITE CARD ========== */
        .specialite-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            border-radius: 15px;
            background: white;
            padding: 1.5rem;
            text-align: center;
        }
        
        .specialite-card:hover {
            transform: translateY(-10px) scale(1.05);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        
        .specialite-card:hover .icon-box i {
            animation: shake 0.5s ease-in-out;
        }
        
        .specialite-card .icon-box i {
            font-size: 2.5rem;
            color: #007bff;
            transition: all 0.3s ease;
        }
        
        /* ========== DOCTOR CARD ========== */
        .doctor-card {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
            border-radius: 20px;
            background: white;
        }
        
        .doctor-card:hover {
            transform: translateY(-15px);
            box-shadow: 0 30px 60px rgba(0,0,0,0.15);
        }
        
        .doctor-img-wrapper {
            position: relative;
            display: inline-block;
            margin-bottom: 1rem;
        }
        
        .doctor-img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border: 3px solid #007bff;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .doctor-card:hover .doctor-img {
            transform: scale(1.1);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            border-color: #0056b3;
        }
        
        .online-badge {
            position: absolute;
            bottom: 5px;
            right: 5px;
            background-color: #28a745;
            border-radius: 50%;
            padding: 5px;
            border: 2px solid white;
        }
        
        .online-badge i {
            font-size: 10px;
        }
        
        /* ========== AVANTAGE CARD ========== */
        .advantage-card {
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            border-radius: 15px;
            background: white;
            padding: 2rem;
            text-align: center;
            height: 100%;
        }
        
        .advantage-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(0,123,255,0.1), transparent);
            transition: left 0.5s ease;
        }
        
        .advantage-card:hover::before {
            left: 100%;
        }
        
        .advantage-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }
        
        .advantage-card:hover i {
            animation: pulse 0.5s ease-in-out;
        }
        
        .advantage-card i {
            font-size: 3rem;
            color: #007bff;
            margin-bottom: 1rem;
            display: inline-block;
        }
        
        /* ========== STATS SECTION ========== */
        .stats-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: relative;
            overflow: hidden;
        }
        
        .stats-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="rgba(255,255,255,0.05)" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,154.7C960,171,1056,181,1152,165.3C1248,149,1344,107,1392,85.3L1440,64L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') no-repeat bottom;
            background-size: cover;
            opacity: 0.3;
        }
        
        .stats-section h2 {
            animation: pulse 2s ease-in-out infinite;
            transition: all 0.3s ease;
        }
        
        .stats-section h2:hover {
            transform: scale(1.2);
            text-shadow: 0 0 10px rgba(255,255,255,0.5);
        }
        
        /* ========== BUTTONS ========== */
        .btn-primary {
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            border-radius: 50px;
            font-weight: 500;
        }
        
        .btn-primary::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255,255,255,0.3);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }
        
        .btn-primary:hover::before {
            width: 300px;
            height: 300px;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,123,255,0.3);
        }
        
        .btn-outline-primary {
            border-radius: 50px;
            transition: all 0.3s ease;
        }
        
        .btn-outline-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,123,255,0.2);
        }
        
        /* ========== SCROLL REVEAL ========== */
        .reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.8s ease;
        }
        
        .reveal.active {
            opacity: 1;
            transform: translateY(0);
        }
        
        /* ========== LOADING STATE ========== */
        .image-loading {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 1000px 100%;
            animation: shimmer 1.5s infinite;
        }
        
        /* ========== RATINGS STARS ========== */
        .rating {
            display: inline-flex;
            gap: 3px;
        }
        
        .rating i {
            font-size: 14px;
        }
        
        /* ========== RESPONSIVE ========== */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 1.8rem !important;
            }
            
            .search-box {
                border-radius: 20px;
                padding: 15px;
            }
            
            .specialite-card {
                padding: 1rem;
            }
            
            .doctor-img {
                width: 100px;
                height: 100px;
            }
            
            .stats-section h2 {
                font-size: 2rem;
            }
        }
        
        @media (max-width: 576px) {
            .hero-title {
                font-size: 1.5rem !important;
            }
            
            .btn-primary, .btn-outline-primary {
                padding: 0.5rem 1rem !important;
                font-size: 0.9rem;
            }
        }
        
        /* ========== UTILITIES ========== */
        .rounded-4 {
            border-radius: 1rem;
        }
        
        .cursor-pointer {
            cursor: pointer;
        }
        
        .text-decoration-none {
            text-decoration: none;
        }
        
        .gap-3 {
            gap: 1rem;
        }
    </style>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<!-- ========== HERO SECTION ========== -->
<section class="hero-section py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 animate-fadeInLeft">
                <span class="badge bg-light text-primary p-3 mb-3 animate-pulse" style="font-size: 0.9rem; border-radius: 30px;">
                    <i class="fas fa-heartbeat me-2"></i> Prenez soin de votre santé
                </span>
                
                <h1 class="hero-title" style="font-size: 2.8rem; margin-bottom: 1.5rem; font-weight: 800; line-height: 1.2;">
                    Prenez rendez-vous <br>
                    avec <span style="color: #007bff; position: relative; display: inline-block;">le meilleur spécialiste
                    <span style="position: absolute; bottom: -5px; left: 0; width: 100%; height: 3px; background: linear-gradient(90deg, #007bff, transparent);"></span>
                    </span>
                </h1>
                
                <p class="hero-text text-muted" style="font-size: 1.1rem; margin-bottom: 2rem; line-height: 1.6;">
                    Sama Docteur vous met en relation avec les meilleurs médecins spécialistes.
                    Prenez rendez-vous en ligne facilement et rapidement, 24h/24 et 7j/7.
                </p>
                
                <div class="d-flex gap-3 flex-wrap">
                    <a href="prendre-rendez-vous.php" class="btn btn-primary px-4 py-3">
                        <i class="fas fa-calendar-check me-2"></i>
                        Prendre rendez-vous
                    </a>
                    <a href="#" class="btn btn-outline-primary px-4 py-3">
                        <i class="fas fa-play me-2"></i>
                        Comment ça marche ?
                    </a>
                </div>
                
                <div class="mt-4 d-flex align-items-center gap-4">
                    <div>
                        <i class="fas fa-check-circle text-success"></i>
                        <span class="ms-1">Réservation gratuite</span>
                    </div>
                    <div>
                        <i class="fas fa-lock text-success"></i>
                        <span class="ms-1">Données sécurisées</span>
                    </div>
                    <div>
                        <i class="fas fa-headset text-success"></i>
                        <span class="ms-1">Support 24/7</span>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6 text-center animate-fadeInRight">
                <div class="position-relative">
                    <img src="assets/images/Dr Adja Diop.jpg"
                         class="img-fluid hero-image animate-float"
                         alt="Docteur"
                         style="max-width: 70%; height: auto; border-radius: 30px;"
                         onerror="this.src='https://via.placeholder.com/400x400?text=Docteur'">
                    <div class="position-absolute top-0 start-0 bg-white rounded-circle p-2 shadow-lg animate-bounce">
                        <i class="fas fa-check-circle text-success fa-2x"></i>
                    </div>
                    <div class="position-absolute bottom-0 end-0 bg-white rounded-circle p-2 shadow-lg">
                        <i class="fas fa-heart text-danger fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- ========== SEARCH BOX ========== -->
        <div class="search-box shadow-lg mt-5 animate-fadeInUp" style="margin-top: 2rem !important; animation-delay: 0.3s;">
            <form action="specialites.php" method="GET">
                <div class="row g-3 align-items-center">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-0">
                                <i class="fas fa-stethoscope text-primary"></i>
                            </span>
                            <input type="text"
                                   name="specialite"
                                   class="form-control border-0"
                                   placeholder="Spécialité médicale (ex: Cardiologue, Dentiste...)"
                                   autocomplete="off"
                                   list="specialites-list">
                            <datalist id="specialites-list">
                                <?php foreach($specialites as $spec): ?>
                                <option value="<?php echo htmlspecialchars($spec['nom']); ?>">
                                <?php endforeach; ?>
                            </datalist>
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
                                   placeholder="Ville ou quartier (ex: Saint-Louis...)"
                                   autocomplete="off"
                                   value="Saint-Louis"
                                   list="villes-list">
                            <datalist id="villes-list">
                                <option value="Saint-Louis">
                            </datalist>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <button class="btn btn-primary w-100 py-3" type="submit">
                            <i class="fas fa-search me-2"></i>
                            Rechercher un médecin
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- ========== SPECIALITES SECTION ========== -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5 reveal">
            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 mb-3 rounded-pill">
                <i class="fas fa-stethoscope me-2"></i> Nos spécialités
            </span>
            <h2 class="section-title" style="font-size: 2.5rem; margin-bottom: 1rem; font-weight: 700;">
                Spécialités médicales
            </h2>
            <p class="section-subtitle text-muted" style="font-size: 1.1rem; max-width: 600px; margin: 0 auto;">
                Des médecins experts dans leur domaine, rigoureusement sélectionnés
            </p>
        </div>
        
        <div class="row g-4">
            <?php foreach($specialites as $index => $specialite): ?>
            <div class="col-md-4 col-lg-2 reveal" style="animation-delay: <?php echo $index * 0.05; ?>s;">
                <div class="specialite-card">
                    <div class="icon-box mb-3">
                        <i class="<?php echo $specialite['icone']; ?>"></i>
                    </div>
                    <h5 class="mb-2" style="font-size: 1rem;"><?php echo htmlspecialchars($specialite['nom']); ?></h5>
                    <a href="specialites.php?id=<?php echo $specialite['id']; ?>" class="text-decoration-none small text-primary">
                        Voir les médecins <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-5 reveal">
            <a href="specialites.php" class="btn btn-outline-primary px-4 py-2">
                Voir toutes les spécialités <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</section>

<!-- ========== MEDECINS SECTION ========== -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5 reveal">
            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 mb-3 rounded-pill">
                <i class="fas fa-user-md me-2"></i> Nos praticiens
            </span>
            <h2 class="section-title" style="font-size: 2.5rem; margin-bottom: 1rem; font-weight: 700;">
                Médecins disponibles
            </h2>
            <p class="section-subtitle text-muted" style="font-size: 1.1rem; max-width: 600px; margin: 0 auto;">
                Des professionnels à votre écoute, prenez rendez-vous en quelques clics
            </p>
        </div>
        
        <div class="row g-4">
            <?php foreach($medecins as $index => $medecin): 
                $rating = getDoctorRating($medecin['id']);
                $fullStars = floor($rating['stars']);
                $halfStar = ($rating['stars'] - $fullStars) >= 0.5;
                $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);
                $photoPath = getDoctorPhoto($medecin);
                // Forcer la ville à Saint-Louis
                $medecin['ville'] = 'Saint-Louis';
            ?>
            <div class="col-md-6 col-lg-3 reveal" style="animation-delay: <?php echo $index * 0.1; ?>s;">
                <div class="doctor-card text-center p-4 shadow-sm h-100">
                    <div class="doctor-img-wrapper">
                        <?php if($photoPath && file_exists($photoPath)): ?>
                            <img src="<?php echo $photoPath; ?>"
                                 alt="<?php echo htmlspecialchars($medecin['nom_complet']); ?>"
                                 class="doctor-img rounded-circle"
                                 loading="lazy"
                                 onerror="this.src='<?php echo getDoctorAvatar($medecin['nom_complet']); ?>'">
                        <?php else: ?>
                            <img src="<?php echo getDoctorAvatar($medecin['nom_complet']); ?>"
                                 alt="<?php echo htmlspecialchars($medecin['nom_complet']); ?>"
                                 class="doctor-img rounded-circle"
                                 loading="lazy">
                        <?php endif; ?>
                        <span class="online-badge">
                            <i class="fas fa-check-circle text-white"></i>
                        </span>
                    </div>
                    
                    <h5 class="mb-1 fw-bold"><?php echo htmlspecialchars($medecin['nom_complet']); ?></h5>
                    <span class="badge bg-primary mb-2 px-3 py-2 rounded-pill">
                        <?php echo htmlspecialchars($medecin['specialite_nom']); ?>
                    </span>
                    
                    <p class="ville text-muted small mb-2">
                        <i class="fas fa-map-marker-alt me-1"></i>
                        Saint-Louis
                    </p>
                    
                    <div class="rating mb-3">
                        <?php for($i = 1; $i <= $fullStars; $i++): ?>
                            <i class="fas fa-star text-warning"></i>
                        <?php endfor; ?>
                        <?php if($halfStar): ?>
                            <i class="fas fa-star-half-alt text-warning"></i>
                        <?php endif; ?>
                        <?php for($i = 1; $i <= $emptyStars; $i++): ?>
                            <i class="far fa-star text-warning"></i>
                        <?php endfor; ?>
                        <span class="text-muted ms-1 small">(<?php echo $rating['count']; ?> avis)</span>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <a href="prendre-rendez-vous-etape1.php?id=<?php echo $medecin['id']; ?>"
                           class="btn btn-primary flex-grow-1">
                            <i class="fas fa-calendar-check me-1"></i>
                            Prendre votre rendez-vous
                        </a>
                        <button class="btn btn-outline-primary" onclick="showDoctorDetails(<?php echo $medecin['id']; ?>)">
                            <i class="fas fa-info-circle"></i>
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-5 reveal">
            <a href="specialites.php" class="btn btn-outline-primary px-4 py-2">
                Voir tous les médecins <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</section>

<!-- ========== AVANTAGES SECTION ========== -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5 reveal">
            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 mb-3 rounded-pill">
                <i class="fas fa-star me-2"></i> Pourquoi nous choisir
            </span>
            <h2 class="section-title" style="font-size: 2.5rem; margin-bottom: 1rem; font-weight: 700;">
                Pourquoi choisir Sama Docteur ?
            </h2>
            <p class="section-subtitle text-muted" style="font-size: 1.1rem; max-width: 600px; margin: 0 auto;">
                Une plateforme moderne et fiable pour votre santé
            </p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4 reveal" style="animation-delay: 0.1s;">
                <div class="advantage-card">
                    <div class="icon-box">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h5 class="mb-3">Prise de RDV rapide</h5>
                    <p class="text-muted">
                        Trouvez et réservez votre rendez-vous en quelques clics, 24h/24 et 7j/7.
                    </p>
                    <div class="mt-3">
                        <span class="badge bg-light text-primary">Moins de 2 minutes</span>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 reveal" style="animation-delay: 0.2s;">
                <div class="advantage-card">
                    <div class="icon-box">
                        <i class="fas fa-user-md"></i>
                    </div>
                    <h5 class="mb-3">Médecins qualifiés</h5>
                    <p class="text-muted">
                        Des spécialistes vérifiés et expérimentés, rigoureusement sélectionnés.
                    </p>
                    <div class="mt-3">
                        <span class="badge bg-light text-primary">100% vérifiés</span>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 reveal" style="animation-delay: 0.3s;">
                <div class="advantage-card">
                    <div class="icon-box">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h5 class="mb-3">Données sécurisées</h5>
                    <p class="text-muted">
                        Vos informations médicales sont protégées par le chiffrement SSL.
                    </p>
                    <div class="mt-3">
                        <span class="badge bg-light text-primary">Confidentialité totale</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ========== STATS SECTION ========== -->
<section class="stats-section py-5 text-white">
    <div class="container">
        <div class="row text-center g-4">
            <div class="col-md-3 reveal">
                <h2 class="display-3 fw-bold mb-2 stat-number" data-target="50">0</h2>
                <p class="mb-0 fs-5">Médecins experts</p>
                <small class="opacity-75">+ en croissance</small>
            </div>
            <div class="col-md-3 reveal" style="animation-delay: 0.1s;">
                <h2 class="display-3 fw-bold mb-2 stat-number" data-target="12">0</h2>
                <p class="mb-0 fs-5">Spécialités médicales</p>
                <small class="opacity-75">tous domaines</small>
            </div>
            <div class="col-md-3 reveal" style="animation-delay: 0.2s;">
                <h2 class="display-3 fw-bold mb-2 stat-number" data-target="1000">0</h2>
                <p class="mb-0 fs-5">Patients satisfaits</p>
                <small class="opacity-75">et recommandent</small>
            </div>
            <div class="col-md-3 reveal" style="animation-delay: 0.3s;">
                <h2 class="display-3 fw-bold mb-2">24/7</h2>
                <p class="mb-0 fs-5">Service disponible</p>
                <small class="opacity-75">jours et nuits</small>
            </div>
        </div>
    </div>
</section>

<!-- ========== TEMOIGNAGES SECTION ========== -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5 reveal">
            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 mb-3 rounded-pill">
                <i class="fas fa-quote-left me-2"></i> Témoignages
            </span>
            <h2 class="section-title" style="font-size: 2.5rem; margin-bottom: 1rem; font-weight: 700;">
                Ce que nos patients disent
            </h2>
            <p class="section-subtitle text-muted" style="font-size: 1.1rem; max-width: 600px; margin: 0 auto;">
                Des milliers de patients nous font confiance chaque jour
            </p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4 reveal" style="animation-delay: 0.1s;">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                        </div>
                        <p class="card-text">"Très satisfaite de la plateforme. J'ai trouvé un spécialiste rapidement et pris rendez-vous en quelques minutes. Je recommande vivement !"</p>
                        <div class="d-flex align-items-center mt-3">
                            <i class="fas fa-user-circle fa-2x text-primary me-2"></i>
                            <div>
                                <h6 class="mb-0">Marie Diouf</h6>
                                <small class="text-muted">Patient depuis 2023</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 reveal" style="animation-delay: 0.2s;">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                        </div>
                        <p class="card-text">"Service excellent ! Les médecins sont très professionnels et à l'écoute. La prise de rendez-vous est simple et rapide."</p>
                        <div class="d-flex align-items-center mt-3">
                            <i class="fas fa-user-circle fa-2x text-primary me-2"></i>
                            <div>
                                <h6 class="mb-0">Abdoulaye Ndiaye</h6>
                                <small class="text-muted">Patient depuis 2024</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 reveal" style="animation-delay: 0.3s;">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star-half-alt text-warning"></i>
                        </div>
                        <p class="card-text">"Une plateforme révolutionnaire pour la santé au Sénégal. Les rappels de RDV par SMS sont très pratiques. Je suis conquis !"</p>
                        <div class="d-flex align-items-center mt-3">
                            <i class="fas fa-user-circle fa-2x text-primary me-2"></i>
                            <div>
                                <h6 class="mb-0">Fatou Sow</h6>
                                <small class="text-muted">Patient depuis 2023</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ========== CTA SECTION ========== -->
<section class="py-5 bg-primary text-white">
    <div class="container text-center">
        <div class="reveal">
            <h2 class="display-5 fw-bold mb-3">Prêt à prendre soin de votre santé ?</h2>
            <p class="fs-5 mb-4 opacity-90">Rejoignez des milliers de patients qui nous font confiance chaque jour</p>
            <div class="d-flex gap-3 justify-content-center flex-wrap">
                <a href="prendre-rendez-vous.php" class="btn btn-light btn-lg px-5">
                    <i class="fas fa-calendar-check me-2"></i>
                    Prendre rendez-vous
                </a>
                <a href="contact.php" class="btn btn-outline-light btn-lg px-5">
                    <i class="fas fa-headset me-2"></i>
                    Nous contacter
                </a>
            </div>
        </div>
    </div>
</section>

<script>
// ========== SCROLL REVEAL ANIMATION ==========
function reveal() {
    var reveals = document.querySelectorAll('.reveal');
    for (var i = 0; i < reveals.length; i++) {
        var windowHeight = window.innerHeight;
        var elementTop = reveals[i].getBoundingClientRect().top;
        var elementVisible = 150;
        if (elementTop < windowHeight - elementVisible) {
            reveals[i].classList.add('active');
        }
    }
}

window.addEventListener('scroll', reveal);
reveal();

// ========== COMPTEURS ANIMÉS POUR LES STATISTIQUES ==========
function animateCounter(element, start, end, duration) {
    let startTimestamp = null;
    const step = (timestamp) => {
        if (!startTimestamp) startTimestamp = timestamp;
        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
        const value = Math.floor(progress * (end - start) + start);
        element.textContent = value;
        if (progress < 1) {
            window.requestAnimationFrame(step);
        }
    };
    window.requestAnimationFrame(step);
}

// Observer pour les compteurs
const observerOptions = {
    threshold: 0.5
};

const statsObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const counters = entry.target.querySelectorAll('.stat-number');
            counters.forEach(counter => {
                const target = parseInt(counter.getAttribute('data-target'));
                if (!counter.classList.contains('animated')) {
                    counter.classList.add('animated');
                    animateCounter(counter, 0, target, 2000);
                }
            });
        }
    });
}, observerOptions);

// Appliquer l'observateur aux stats
const statsSection = document.querySelector('.stats-section');
if (statsSection) {
    statsObserver.observe(statsSection);
}

// ========== GESTION DES ERREURS D'IMAGES ==========
document.querySelectorAll('.doctor-img').forEach(img => {
    img.addEventListener('error', function() {
        const card = this.closest('.doctor-card');
        const name = card.querySelector('h5').innerText;
        this.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=007bff&color=fff&size=120&rounded=true&bold=true&length=2`;
    });
    
    // Ajouter une classe de chargement
    this.classList.add('image-loading');
    this.addEventListener('load', function() {
        this.classList.remove('image-loading');
    });
});

// ========== FONCTION POUR AFFICHER LES DÉTAILS DU MÉDECIN ==========
function showDoctorDetails(doctorId) {
    // Rediriger vers la page de détails ou ouvrir une modale
    window.location.href = `medecin-details.php?id=${doctorId}`;
}

// ========== SMOOTH SCROLL ==========
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

// ========== ANIMATION AU SURVOL DES CARTES ==========
document.querySelectorAll('.specialite-card').forEach(card => {
    card.addEventListener('mouseenter', function() {
        this.style.animation = 'none';
        setTimeout(() => {
            this.style.animation = '';
        }, 10);
    });
});

// ========== INITIALISATION DES TOOLTIPS (si Bootstrap JS est inclus) ==========
document.addEventListener('DOMContentLoaded', function() {
    // Activer les tooltips Bootstrap si disponibles
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
    
    // Animation de chargement des images
    console.log('Page Sama Docteur chargée avec succès !');
});

// ========== RECHERCHE EN TEMPS RÉEL (optionnel) ==========
const searchInput = document.querySelector('input[name="specialite"]');
if (searchInput) {
    searchInput.addEventListener('input', function(e) {
        // Implémenter une recherche en temps réel si nécessaire
        console.log('Recherche:', this.value);
    });
}
</script>

<?php include 'includes/footer.php'; ?>
</body>
</html>