<?php
// Fichier: specialites.php
require_once 'includes/config.php';
include 'includes/header.php';

// Récupérer l'ID de la spécialité si présent
$specialite_id = isset($_GET['id']) ? intval($_GET['id']) : null;
$recherche = isset($_GET['specialite']) ? $_GET['specialite'] : '';
$ville = isset($_GET['ville']) ? $_GET['ville'] : '';

// Requête pour les spécialités
$stmtSpecialites = $pdo->query("SELECT * FROM specialites ORDER BY nom");
$specialites = $stmtSpecialites->fetchAll();

// Requête pour les médecins
$sql = "
    SELECT m.*, s.nom as specialite_nom 
    FROM medecins m 
    JOIN specialites s ON m.specialite_id = s.id 
    WHERE m.est_disponible = TRUE
";

$params = [];

if ($specialite_id) {
    $sql .= " AND m.specialite_id = ?";
    $params[] = $specialite_id;
}

if ($recherche) {
    $sql .= " AND s.nom LIKE ?";
    $params[] = "%$recherche%";
}

if ($ville) {
    $sql .= " AND m.ville LIKE ?";
    $params[] = "%$ville%";
}

$sql .= " ORDER BY m.nom_complet";

$stmtMedecins = $pdo->prepare($sql);
$stmtMedecins->execute($params);
$medecins = $stmtMedecins->fetchAll();
?>

<div class="container my-5">
    <div class="row">
        <!-- Sidebar des spécialités -->
        <div class="col-lg-3 mb-4" data-aos="fade-right">
            <div class="dashboard-sidebar">
                <h5 class="mb-3"><i class="fas fa-notes-medical"></i> Spécialités</h5>
                <ul class="dashboard-menu">
                    <li>
                        <a href="specialites.php" class="<?php echo !$specialite_id ? 'active' : ''; ?>">
                            <i class="fas fa-list"></i> Toutes les spécialités
                        </a>
                    </li>
                    <?php foreach($specialites as $spec): ?>
                    <li>
                        <a href="specialites.php?id=<?php echo $spec['id']; ?>" class="<?php echo $specialite_id == $spec['id'] ? 'active' : ''; ?>">
                            <i class="<?php echo $spec['icone']; ?>"></i> <?php echo htmlspecialchars($spec['nom']); ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <!-- Filtres -->
            <div class="dashboard-sidebar mt-4">
                <h5 class="mb-3"><i class="fas fa-filter"></i> Filtres</h5>
                <form method="GET" action="" id="filterForm">
                    <?php if($specialite_id): ?>
                        <input type="hidden" name="id" value="<?php echo $specialite_id; ?>">
                    <?php endif; ?>
                    <div class="mb-3">
                        <label class="form-label">Spécialité</label>
                        <input type="text" class="form-control" name="specialite" value="<?php echo htmlspecialchars($recherche); ?>" placeholder="Rechercher...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ville</label>
                        <select class="form-select" name="ville">
                            <option value="">Toutes les villes</option>
                            <option value="Dakar" <?php echo $ville == 'Dakar' ? 'selected' : ''; ?>>Dakar</option>
                            <option value="Thiès" <?php echo $ville == 'Thiès' ? 'selected' : ''; ?>>Thiès</option>
                            <option value="Rufisque" <?php echo $ville == 'Rufisque' ? 'selected' : ''; ?>>Rufisque</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Filtrer
                    </button>
                    <?php if($recherche || $ville): ?>
                    <a href="specialites.php<?php echo $specialite_id ? '?id='.$specialite_id : ''; ?>" class="btn btn-outline-secondary w-100 mt-2">
                        <i class="fas fa-times"></i> Réinitialiser
                    </a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
        
        <!-- Liste des médecins -->
        <div class="col-lg-9" data-aos="fade-left">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Médecins disponibles</h2>
                <span class="badge bg-primary"><?php echo count($medecins); ?> médecins trouvés</span>
            </div>
            
            <?php if(empty($medecins)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Aucun médecin trouvé pour votre recherche.
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach($medecins as $index => $medecin): ?>
                    <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="<?php echo $index * 50; ?>">
                        <div class="card-medecin card h-100">
                            <?php
                                // Photos de médecins par nom
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

                                // Images par défaut par spécialité
                                $specialiteImages = [
                                    'Cardiologie' => 'assets/images/Dr Aboubakar.jpg',
                                    'Dermatologie' => 'assets/images/doc4.jpg',
                                    'Pédiatrie' => 'assets/images/doc3.jpg',
                                    'Gynécologie' => 'assets/images/doc6.jpg',
                                    'Ophtalmologie' => 'assets/images/doc10.jpg',
                                    'Urologie' => 'assets/images/doc5.jpg',
                                    'Dentiste' => 'assets/images/doc2.png',
                                    'ORL' => 'assets/images/docc.jpg',
                                    'Généraliste' => 'assets/images/docff.png',
                                    'Orthopédie' => 'assets/images/doccrm.png',
                                    'Neurologie' => 'assets/images/Dr Mame Diarra Fall.jpg',
                                    'Psychiatrie' => 'assets/images/docter.jpg'
                                ];

                                // Liste d'avatars fallback
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
                                $doctorName = trim($medecin['nom_complet']);
                                $specialiteNom = $medecin['specialite_nom'];
                                $normalizedSpecialite = strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $specialiteNom));

                                if (isset($doctorPhotos[$doctorName]) && file_exists(BASE_PATH . $doctorPhotos[$doctorName])) {
                                    $photo = $doctorPhotos[$doctorName];
                                }

                                if (empty($photo) && !empty($medecin['photo'])) {
                                    $photo = $medecin['photo'];
                                    if (!preg_match('#^https?://#', $photo) && !file_exists(BASE_PATH . $photo)) {
                                        if (file_exists(BASE_PATH . 'assets/images/' . $photo)) {
                                            $photo = 'assets/images/' . $photo;
                                        }
                                    }
                                    if (!preg_match('#^https?://#', $photo) && !file_exists(BASE_PATH . $photo)) {
                                        $photo = '';
                                    }
                                }

                                if (empty($photo) && !empty($specialiteNom)) {
                                    foreach ($specialiteImages as $key => $defaultImage) {
                                        $normalizedKey = strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $key));
                                        if ((strpos($normalizedSpecialite, $normalizedKey) !== false || stripos($specialiteNom, $key) !== false) && file_exists(BASE_PATH . $defaultImage)) {
                                            $photo = $defaultImage;
                                            break;
                                        }
                                    }
                                }

                                if (empty($photo)) {
                                    $photo = $fallbacks[$index % count($fallbacks)];
                                }

                                // Construire l'URL publique
                                $imgSrc = preg_match('#^https?://#', $photo) ? $photo : SITE_URL . ltrim($photo, '/');
                            ?>
                            <div class="card-img-top text-center pt-4">
                                  <img src="<?php echo htmlspecialchars($imgSrc); ?>"
                                      alt="Photo de <?php echo htmlspecialchars($medecin['nom_complet']); ?>"
                                      class="photo-profil"
                                      style="width:120px;height:120px;border-radius:50%;object-fit:cover;border:4px solid #ffffff;box-shadow:0 4px 12px rgba(0,0,0,0.2);transition:transform 0.3s ease;" />
                            </div>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($medecin['nom_complet']); ?></h5>
                                <p class="specialite"><?php echo htmlspecialchars($medecin['specialite_nom']); ?></p>
                                <p class="info"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($medecin['ville']); ?></p>
                                <p class="info"><i class="fas fa-phone-alt"></i> <?php echo htmlspecialchars($medecin['telephone']); ?></p>
                                <p class="info"><i class="fas fa-calendar-alt"></i> <?php echo $medecin['annees_experience']; ?> ans d'expérience</p>
                            </div>
                            <div class="card-footer bg-transparent">
                                <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'patient'): ?>
                                    <button class="btn btn-primary w-100" onclick="prendreRendezVous(<?php echo $medecin['id']; ?>)">
                                        <i class="fas fa-calendar-check"></i> Prendre RDV
                                    </button>
                                <?php else: ?>
                                    <a href="connexion.php" class="btn btn-outline-primary w-100">
                                        <i class="fas fa-sign-in-alt"></i> Connectez-vous pour réserver
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>