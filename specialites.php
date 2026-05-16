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
                            <div class="card-img-top text-center pt-4">
                                <i class="fas fa-user-md fa-5x text-primary"></i>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($medecin['nom_complet']); ?></h5>
                                <p class="specialite"><?php echo htmlspecialchars($medecin['specialite_nom']); ?></p>
                                <p class="info"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($medecin['ville']); ?></p>
                                <p class="info"><i class="fas fa-phone-alt"></i> <?php echo htmlspecialchars($medecin['telephone']); ?></p>
                                <p class="info"><i class="fas fa-calendar-alt"></i> <?php echo $medecin['annees_experience']; ?> ans d'expérience</p>
                                <p class="tarif"><?php echo number_format($medecin['tarif_consultation'], 0, ',', ' '); ?> FCFA</p>
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