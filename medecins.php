<?php
// Fichier : medecins.php
require_once 'includes/config.php';
$pageTitle = 'Médecins';
include 'includes/header.php';

$stmtMedecins = $pdo->query("SELECT m.*, s.nom AS specialite_nom FROM medecins m JOIN specialites s ON m.specialite_id = s.id WHERE m.est_disponible = TRUE ORDER BY m.nom_complet");
$medecins = $stmtMedecins->fetchAll();

function getDoctorImageUrl($medecin, $index) {
    $doctorPhotos = [
        'Dr Martin Dupuis' => 'assets/images/Dr Martin Dupuis.jpg',
        'Dr Sophie Diallo' => 'assets/images/Dr Sophie Diallo.jpg',
        'Dr Aliou Ndiaye' => 'assets/images/Dr Aliou Ndiaye.jpg',
        'Dr Fatou Sow' => 'assets/images/Dr Fatou Sow.jpg',
        'Dr Abdoulaye Kane' => 'assets/images/Dr Abdoulaye Kane.jpg',
        'Dr Adja Diop' => 'assets/images/Dr Adja Diop.jpg',
        'Dr Oumar Fall' => 'assets/images/Dr Oumar Fall.jpg',
        'Dr Aïssatou Ba' => 'assets/images/Dr Aïssatou Ba.jpg',
        'Dr Cheikh Diagne' => 'assets/images/Dr Cheikh Diagne.jpg',
        'Dr Mame Diarra Fall' => 'assets/images/Dr Mame Diarra Fall.jpg'
    ];

    $fallbacks = [
        'assets/images/doc4.jpg',
        'assets/images/docs.jpg',
        'assets/images/doc6.jpg',
        'assets/images/doc3.jpg',
        'assets/images/doc10.jpg',
        'assets/images/doc5.jpg',
        'assets/images/med.jpg'
    ];

    $photo = '';
    $nomComplet = trim($medecin['nom_complet']);

    if (!empty($medecin['photo'])) {
        $candidate = $medecin['photo'];
        if (!preg_match('#^https?://#i', $candidate)) {
            $candidate = ltrim($candidate, '/');
            if (file_exists(BASE_PATH . $candidate)) {
                $photo = $candidate;
            } elseif (file_exists(BASE_PATH . 'assets/images/' . $candidate)) {
                $photo = 'assets/images/' . $candidate;
            }
        } else {
            $photo = $candidate;
        }
    }

    if (empty($photo) && isset($doctorPhotos[$nomComplet]) && file_exists(BASE_PATH . $doctorPhotos[$nomComplet])) {
        $photo = $doctorPhotos[$nomComplet];
    }

    if (empty($photo)) {
        $photo = $fallbacks[$index % count($fallbacks)];
    }

    if (preg_match('#^https?://#i', $photo)) {
        return $photo;
    }

    return SITE_URL . ltrim($photo, '/');
}
?>

<div class="container my-5">
    <div class="text-center mb-5" data-aos="fade-up">
        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill mb-3">
            <i class="fas fa-user-md me-2"></i> Médecins
        </span>
        <h1 class="display-5 fw-bold">Tous les médecins disponibles</h1>
        <p class="text-muted mx-auto" style="max-width: 680px; font-size: 1.05rem;">
            Découvrez notre sélection de praticiens spécialistes disponibles à Saint-Louis et prenez rendez-vous en ligne en quelques clics.
        </p>
    </div>

    <?php if (empty($medecins)): ?>
        <div class="alert alert-info" role="alert" data-aos="fade-up">
            <i class="fas fa-info-circle me-2"></i> Aucun médecin disponible pour le moment. Revenez bientôt.
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($medecins as $index => $medecin): ?>
                <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="<?php echo ($index % 6) * 50; ?>">
                    <div class="card shadow-sm h-100 border-0">
                        <div class="card-body text-center p-4">
                            <img src="<?php echo htmlspecialchars(getDoctorImageUrl($medecin, $index)); ?>"
                                 alt="Photo de <?php echo htmlspecialchars($medecin['nom_complet']); ?>"
                                 class="img-fluid rounded-circle mb-3"
                                 style="width: 130px; height: 130px; object-fit: cover; border: 4px solid #007bff;">
                            <h5 class="card-title fw-bold mb-1"><?php echo htmlspecialchars($medecin['nom_complet']); ?></h5>
                            <p class="text-primary mb-2"><i class="fas fa-stethoscope me-1"></i> <?php echo htmlspecialchars($medecin['specialite_nom']); ?></p>
                            <p class="text-muted small mb-2"><i class="fas fa-map-marker-alt me-1"></i> Saint-Louis</p>
                            <p class="text-muted small mb-3"><i class="fas fa-phone-alt me-1"></i> <?php echo htmlspecialchars($medecin['telephone']); ?></p>
                            <div class="d-grid gap-2">
                                <a href="prendre-rendez-vous-etape1.php?id=<?php echo $medecin['id']; ?>" class="btn btn-primary btn-sm px-4">
                                    <i class="fas fa-calendar-check me-1"></i> Prendre RDV
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
