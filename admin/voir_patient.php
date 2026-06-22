<?php
// Fichier: admin/voir_patient.php
require_once '../includes/config.php';

if (!estConnecte() || $_SESSION['user_role'] != 'admin') {
    header('Location: ../index.php');
    exit();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    header('Location: patients.php');
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ? AND role = 'patient'");
$stmt->execute([$id]);
$patient = $stmt->fetch();

if (!$patient) {
    header('Location: patients.php');
    exit();
}

$rdv_recents = $pdo->prepare("SELECT r.*, m.nom_complet as medecin_nom, m.email as medecin_email, m.telephone as medecin_tel
    FROM rendez_vous r
    LEFT JOIN medecins m ON r.medecin_id = m.id
    WHERE r.utilisateur_id = ?
    ORDER BY r.date_rendez_vous DESC
    LIMIT 10");
$rdv_recents->execute([$id]);
$rdv_recents = $rdv_recents->fetchAll();

include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détails du patient - Sama Docteur</title>
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
        .table-card { background: white; border-radius: 12px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .table-card .card-body { padding: 20px; }
        @media (max-width: 768px) { .admin-sidebar { transform: translateX(-100%); } .admin-content { margin-left: 0; } }
    </style>
    <link rel="stylesheet" href="assets/css/admin-responsive.css">
</head>
<body>
<button class="admin-toggle-btn" aria-label="Ouvrir le menu"><i class="fas fa-bars"></i></button>
<div class="admin-overlay"></div>
<div class="admin-wrapper">
    <div class="admin-sidebar">
        <div class="logo"><h3><i class="fas fa-stethoscope"></i> Sama Docteur</h3><p>Espace Administration</p></div>
        <div class="nav-menu">
            <div class="nav-item"><a href="index.php" class="nav-link"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a></div>
            <div class="nav-item"><a href="medecins.php" class="nav-link"><i class="fas fa-user-md"></i> Médecins</a></div>
            <div class="nav-item"><a href="patients.php" class="nav-link active"><i class="fas fa-users"></i> Patients</a></div>
            <div class="nav-item"><a href="rendez-vous.php" class="nav-link"><i class="fas fa-calendar-alt"></i> Rendez-vous</a></div>
            <div class="nav-item"><a href="specialites.php" class="nav-link"><i class="fas fa-tags"></i> Spécialités</a></div>
            <div class="nav-item"><a href="../deconnexion.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></div>
        </div>
    </div>

    <div class="admin-content">
        <div class="profile-header">
            <div class="row">
                <div class="col-md-2 text-center">
                    <div class="profile-avatar"><i class="fas fa-user"></i></div>
                </div>
                <div class="col-md-6">
                    <h2><?php echo htmlspecialchars($patient['nom_complet']); ?></h2>
                    <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($patient['email']); ?></p>
                    <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($patient['telephone'] ?? 'Non renseigné'); ?></p>
                    <p><i class="fas fa-calendar-plus"></i> Inscrit le <?php echo date('d/m/Y', strtotime($patient['date_inscription'])); ?></p>
                </div>
                <div class="col-md-4 text-end">
                    <span class="badge bg-<?php echo $patient['est_actif'] ? 'success' : 'danger'; ?> p-2">
                        <i class="fas fa-<?php echo $patient['est_actif'] ? 'check-circle' : 'times-circle'; ?>"></i>
                        <?php echo $patient['est_actif'] ? 'Actif' : 'Inactif'; ?>
                    </span>
                    <div class="mt-3">
                        <a href="patients.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Retour</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-4"><div class="stat-box"><h3><?php echo htmlspecialchars($patient['id']); ?></h3><p>ID Patient</p></div></div>
            <div class="col-md-4"><div class="stat-box"><h3><?php echo htmlspecialchars($patient['telephone'] ?: '—'); ?></h3><p>Contact</p></div></div>
            <div class="col-md-4"><div class="stat-box"><h3><?php echo htmlspecialchars($patient['email']); ?></h3><p>Email</p></div></div>
        </div>

        <div class="table-card mb-4">
            <div class="card-body">
                <h5>Derniers rendez-vous</h5>
                <?php if (empty($rdv_recents)): ?>
                    <div class="alert alert-info">Aucun rendez-vous trouvé pour ce patient.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Médecin</th>
                                    <th>Contact</th>
                                    <th>Statut</th>
                                    <th>Motif</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rdv_recents as $rdv): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y H:i', strtotime($rdv['date_rendez_vous'])); ?></td>
                                    <td><?php echo htmlspecialchars($rdv['medecin_nom'] ?: 'Non renseigné'); ?></td>
                                    <td><?php echo htmlspecialchars($rdv['medecin_tel'] ?: $rdv['medecin_email'] ?: '—'); ?></td>
                                    <td><span class="badge bg-<?php echo $rdv['statut'] == 'confirme' ? 'success' : ($rdv['statut'] == 'en_attente' ? 'warning' : ($rdv['statut'] == 'termine' ? 'info' : 'danger')); ?>"><?php echo htmlspecialchars($rdv['statut']); ?></span></td>
                                    <td><?php echo htmlspecialchars(substr($rdv['motif'], 0, 50)); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<?php include '../includes/footer.php'; ?>
</body>
</html>
