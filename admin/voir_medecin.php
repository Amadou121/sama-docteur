<?php
// Fichier: admin/voir_medecin.php
require_once '../includes/config.php';

if (!estConnecte() || $_SESSION['user_role'] != 'admin') {
    header('Location: ../index.php');
    exit();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) { header('Location: medecins.php'); exit(); }

// Récupérer les infos du médecin
$stmt = $pdo->prepare("
    SELECT m.*, s.nom as specialite_nom, s.icone as specialite_icone,
           (SELECT COUNT(*) FROM rendez_vous WHERE medecin_id = m.id) as total_rdv,
           (SELECT COUNT(*) FROM rendez_vous WHERE medecin_id = m.id AND statut = 'termine') as rdv_termines,
           (SELECT COUNT(*) FROM rendez_vous WHERE medecin_id = m.id AND statut = 'confirme' AND date_rendez_vous >= NOW()) as rdv_a_venir,
           (SELECT AVG(note) FROM avis_patients WHERE medecin_id = m.id AND est_approuve = TRUE) as note_moyenne,
           (SELECT COUNT(*) FROM avis_patients WHERE medecin_id = m.id AND est_approuve = TRUE) as total_avis
    FROM medecins m
    LEFT JOIN specialites s ON m.specialite_id = s.id
    WHERE m.id = ?
");
$stmt->execute([$id]);
$medecin = $stmt->fetch();

if (!$medecin) { header('Location: medecins.php'); exit(); }

// Horaires
$horaires = $pdo->prepare("SELECT * FROM horaires_medecins WHERE medecin_id = ? ORDER BY FIELD(jour_semaine, 'Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche')");
$horaires->execute([$id]);
$horaires = $horaires->fetchAll();

// Derniers rendez-vous
$rdv_recents = $pdo->prepare("
    SELECT r.*, u.nom_complet as patient_nom, u.email as patient_email, u.telephone as patient_tel
    FROM rendez_vous r
    JOIN utilisateurs u ON r.utilisateur_id = u.id
    WHERE r.medecin_id = ?
    ORDER BY r.date_rendez_vous DESC
    LIMIT 10
");
$rdv_recents->execute([$id]);
$rdv_recents = $rdv_recents->fetchAll();

// Avis
$avis = $pdo->prepare("
    SELECT a.*, u.nom_complet as patient_nom, u.email as patient_email
    FROM avis_patients a
    JOIN utilisateurs u ON a.patient_id = u.id
    WHERE a.medecin_id = ? AND a.est_approuve = TRUE
    ORDER BY a.date_creation DESC
    LIMIT 10
");
$avis->execute([$id]);
$avis = $avis->fetchAll();

$noHeader = true;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détails du Dr <?php echo htmlspecialchars($medecin['nom_complet']); ?> - Sama Docteur</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f4f6f9; }
        .admin-wrapper { display: flex; min-height: 100vh; }
        .admin-sidebar { width: 280px; background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%); color: white; position: fixed; height: 100vh; overflow-y: auto; }
        .admin-sidebar .logo { padding: 25px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .admin-sidebar .nav-link { color: rgba(255,255,255,0.8); padding: 12px 25px; display: flex; align-items: center; }
        .admin-sidebar .nav-link:hover, .admin-sidebar .nav-link.active { background: rgba(255,255,255,0.1); border-left: 4px solid #00a8ff; }
        .admin-sidebar .nav-link i { width: 25px; margin-right: 10px; }
        .admin-content { flex: 1; margin-left: 280px; padding: 20px; }
        .profile-header { background: white; border-radius: 15px; padding: 30px; margin-bottom: 25px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .profile-avatar { width: 120px; height: 120px; border-radius: 50%; background: #0066cc; color: white; display: flex; align-items: center; justify-content: center; font-size: 48px; margin: 0 auto 15px; }
        .stat-box { background: white; border-radius: 12px; padding: 15px; text-align: center; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .stat-box h3 { font-size: 24px; font-weight: bold; margin: 0; }
        .rating-stars { color: #ffc107; font-size: 14px; }
        .schedule-item { background: #f8f9fa; padding: 10px 15px; margin-bottom: 8px; border-radius: 8px; }
        @media (max-width: 768px) { .admin-sidebar { transform: translateX(-100%); } .admin-content { margin-left: 0; } }
    </style>
</head>
<body>
<div class="admin-wrapper">
    <div class="admin-sidebar">
        <div class="logo"><h3><i class="fas fa-stethoscope"></i> Sama Docteur</h3><p>Espace Administration</p></div>
        <div class="nav-menu">
            <div class="nav-item"><a href="dashboard.php" class="nav-link"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a></div>
            <div class="nav-item"><a href="medecins.php" class="nav-link active"><i class="fas fa-user-md"></i> Médecins</a></div>
            <div class="nav-item"><a href="patients.php" class="nav-link"><i class="fas fa-users"></i> Patients</a></div>
            <div class="nav-item"><a href="rendez-vous.php" class="nav-link"><i class="fas fa-calendar-alt"></i> Rendez-vous</a></div>
            <div class="nav-item"><a href="specialites.php" class="nav-link"><i class="fas fa-tags"></i> Spécialités</a></div>
            <div class="nav-item"><a href="../deconnexion.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></div>
        </div>
    </div>

    <div class="admin-content">
        <div class="profile-header">
            <div class="row">
                <div class="col-md-2 text-center">
                    <div class="profile-avatar"><i class="fas fa-user-md"></i></div>
                </div>
                <div class="col-md-6">
                    <h2>Dr. <?php echo htmlspecialchars($medecin['nom_complet']); ?></h2>
                    <p><i class="<?php echo $medecin['specialite_icone'] ?? 'fas fa-stethoscope'; ?>"></i> <strong><?php echo htmlspecialchars($medecin['specialite_nom'] ?? 'Non spécifié'); ?></strong></p>
                    <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($medecin['email']); ?> | <i class="fas fa-phone"></i> <?php echo htmlspecialchars($medecin['telephone']); ?></p>
                    <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($medecin['adresse'] ?: 'Non renseignée'); ?></p>
                    <p><i class="fas fa-id-card"></i> N° Ordre: <?php echo htmlspecialchars($medecin['numero_ordre']); ?></p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="rating-stars mb-2">
                        <?php $note = round($medecin['note_moyenne'] ?? 0); for($i=1;$i<=5;$i++): ?>
                            <i class="fas fa-star<?php echo $i <= $note ? '' : '-o'; ?>"></i>
                        <?php endfor; ?>
                        <span class="text-muted">(<?php echo number_format($medecin['note_moyenne'] ?? 0, 1); ?> / 5 - <?php echo $medecin['total_avis']; ?> avis)</span>
                    </div>
                    <span class="badge bg-<?php echo $medecin['est_disponible'] ? 'success' : 'danger'; ?> p-2 mb-2 d-inline-block">
                        <i class="fas fa-<?php echo $medecin['est_disponible'] ? 'check-circle' : 'times-circle'; ?>"></i>
                        <?php echo $medecin['est_disponible'] ? 'Disponible' : 'Indisponible'; ?>
                    </span>
                    <div class="mt-3">
                        <a href="modifier_medecin.php?id=<?php echo $medecin['id']; ?>" class="btn btn-warning"><i class="fas fa-edit"></i> Modifier</a>
                        <a href="medecins.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Retour</a>
                    </div>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12"><h6>Biographie</h6><p><?php echo nl2br(htmlspecialchars($medecin['biographie'] ?: 'Aucune biographie')); ?></p></div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3"><div class="stat-box"><h3><?php echo $medecin['total_rdv']; ?></h3><p>Total consultations</p></div></div>
            <div class="col-md-3"><div class="stat-box"><h3><?php echo $medecin['rdv_termines']; ?></h3><p>Consultations terminées</p></div></div>
            <div class="col-md-3"><div class="stat-box"><h3><?php echo $medecin['rdv_a_venir']; ?></h3><p>RDV à venir</p></div></div>
            <div class="col-md-3"><div class="stat-box"><h3><?php echo $medecin['annees_experience']; ?> ans</h3><p>Expérience</p></div></div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4"><div class="card-header bg-white"><h5><i class="fas fa-clock"></i> Horaires de consultation</h5></div>
                <div class="card-body">
                    <?php if (empty($horaires)): ?>
                        <div class="alert alert-warning">Aucun horaire défini</div>
                    <?php else: ?>
                        <?php foreach ($horaires as $horaire): ?>
                            <div class="schedule-item d-flex justify-content-between">
                                <strong><?php echo $horaire['jour_semaine']; ?></strong>
                                <span><?php echo substr($horaire['heure_debut'], 0, 5); ?> - <?php echo substr($horaire['heure_fin'], 0, 5); ?></span>
                                <span class="badge bg-info"><?php echo $horaire['duree_consultation']; ?> min</span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div></div>
            </div>
            <div class="col-md-6">
                <div class="card mb-4"><div class="card-header bg-white"><h5><i class="fas fa-chart-line"></i> Tarif</h5></div>
                <div class="card-body text-center">
                    <h2 class="text-primary"><?php echo number_format($medecin['tarif_consultation'], 0, ',', ' '); ?> FCFA</h2>
                    <p>par consultation</p>
                </div></div>
            </div>
        </div>

        <div class="card mb-4"><div class="card-header bg-white"><h5><i class="fas fa-history"></i> Derniers rendez-vous</h5></div>
        <div class="table-responsive"><table class="table table-hover">
            <thead class="table-light"><tr><th>Date</th><th>Patient</th><th>Contact</th><th>Statut</th><th>Motif</th></tr></thead>
            <tbody><?php foreach ($rdv_recents as $rdv): ?>
                <tr><td><?php echo date('d/m/Y H:i', strtotime($rdv['date_rendez_vous'])); ?></td>
                <td><?php echo htmlspecialchars($rdv['patient_nom']); ?></td>
                <td><?php echo $rdv['patient_tel']; ?></td>
                <td><span class="badge bg-<?php echo $rdv['statut'] == 'confirme' ? 'success' : ($rdv['statut'] == 'en_attente' ? 'warning' : ($rdv['statut'] == 'termine' ? 'info' : 'danger')); ?>"><?php echo $rdv['statut']; ?></span></td>
                <td><?php echo htmlspecialchars(substr($rdv['motif'], 0, 50)); ?></td></tr>
            <?php endforeach; ?></tbody>
        </table></div></div>

        <div class="card"><div class="card-header bg-white"><h5><i class="fas fa-star"></i> Avis des patients</h5></div>
        <div class="card-body">
            <?php if (empty($avis)): ?>
                <div class="alert alert-info">Aucun avis pour le moment</div>
            <?php else: ?>
                <?php foreach ($avis as $a): ?>
                    <div class="border-bottom mb-3 pb-3"><div class="d-flex justify-content-between">
                        <strong><?php echo htmlspecialchars($a['patient_nom']); ?></strong>
                        <div class="rating-stars"><?php for($i=1;$i<=5;$i++): ?><i class="fas fa-star<?php echo $i <= $a['note'] ? '' : '-o'; ?>"></i><?php endfor; ?></div>
                    </div><p class="text-muted small"><?php echo date('d/m/Y', strtotime($a['date_creation'])); ?></p>
                    <p><?php echo nl2br(htmlspecialchars($a['commentaire'] ?: 'Aucun commentaire')); ?></p></div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div></div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<?php include '../includes/footer.php'; ?>