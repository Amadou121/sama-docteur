<?php
// Fichier: admin/voir_patient.php
require_once '../includes/config.php';

if (!estConnecte() || $_SESSION['user_role'] != 'admin') {
    header('Location: ../index.php');
    exit();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) { header('Location: patients.php'); exit(); }

// Récupérer les infos du patient et statistiques
$stmt = $pdo->prepare("SELECT u.*, 
    (SELECT COUNT(*) FROM rendez_vous WHERE utilisateur_id = u.id) as total_rdv,
    (SELECT COUNT(*) FROM rendez_vous WHERE utilisateur_id = u.id AND statut = 'termine') as rdv_termines,
    (SELECT COUNT(*) FROM rendez_vous WHERE utilisateur_id = u.id AND statut = 'confirme' AND date_rendez_vous >= NOW()) as rdv_a_venir
    FROM utilisateurs u
    WHERE u.id = ? AND u.role = 'patient'");
$stmt->execute([$id]);
$patient = $stmt->fetch();

if (!$patient) { header('Location: patients.php'); exit(); }

// Derniers rendez-vous
$rdv_stmt = $pdo->prepare("SELECT r.*, m.nom_complet as medecin_nom, s.nom as specialite_nom
    FROM rendez_vous r
    LEFT JOIN medecins m ON r.medecin_id = m.id
    LEFT JOIN specialites s ON m.specialite_id = s.id
    WHERE r.utilisateur_id = ?
    ORDER BY r.date_rendez_vous DESC
    LIMIT 10");
$rdv_stmt->execute([$id]);
$rdv_recents = $rdv_stmt->fetchAll();

$noHeader = true;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient - <?php echo htmlspecialchars($patient['nom_complet']); ?> - Sama Docteur Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        body { background: #f4f6f9; }
        .admin-wrapper { display: flex; min-height: 100vh; }
        .admin-sidebar { width: 280px; background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%); color: white; position: fixed; height: 100vh; overflow-y: auto; }
        .admin-sidebar .logo { padding: 25px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .admin-sidebar .nav-link { color: rgba(255,255,255,0.8); padding: 12px 25px; display: flex; align-items: center; }
        .admin-sidebar .nav-link:hover, .admin-sidebar .nav-link.active { background: rgba(255,255,255,0.1); border-left: 4px solid #00a8ff; }
        .admin-sidebar .nav-link i { width: 25px; margin-right: 10px; }
        .admin-content { flex: 1; margin-left: 280px; padding: 20px; }
        .top-bar { background: white; border-radius: 12px; padding: 15px 25px; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; }
        .stat-card { background: white; border-radius: 12px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .stat-card h3 { font-size: 28px; font-weight: bold; margin: 0; }
        .status-badge { padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 500; display: inline-block; }
        .status-actif { background: #d4edda; color: #155724; }
        .status-inactif { background: #f8d7da; color: #721c24; }
        @media (max-width: 768px) { .admin-sidebar { transform: translateX(-100%); } .admin-content { margin-left: 0; } }
    </style>
</head>
<body>
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
        <div class="top-bar">
            <div class="page-title"><h2><i class="fas fa-user"></i> Détails du patient</h2><p><?php echo htmlspecialchars($patient['nom_complet']); ?></p></div>
            <div class="actions"><a href="patients.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Retour</a></div>
        </div>

        <div class="row mb-4">
            <div class="col-md-3"><div class="stat-card"><h3><?php echo $patient['total_rdv']; ?></h3><p>Total RDV</p></div></div>
            <div class="col-md-3"><div class="stat-card"><h3><?php echo $patient['rdv_termines']; ?></h3><p>Terminés</p></div></div>
            <div class="col-md-3"><div class="stat-card"><h3><?php echo $patient['rdv_a_venir']; ?></h3><p>À venir</p></div></div>
            <div class="col-md-3"><div class="stat-card"><h3><?php echo $patient['est_actif'] ? 'Actif' : 'Inactif'; ?></h3><p>Statut</p></div></div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <h4><?php echo htmlspecialchars($patient['nom_complet']); ?></h4>
                        <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($patient['email']); ?></p>
                        <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($patient['telephone'] ?? 'Non renseigné'); ?></p>
                        <p><strong>Inscription :</strong> <?php echo date('d/m/Y H:i', strtotime($patient['date_inscription'])); ?></p>
                        <p><strong>Dernière connexion :</strong> <?php echo $patient['derniere_connexion'] ? date('d/m/Y H:i', strtotime($patient['derniere_connexion'])) : 'Jamais'; ?></p>
                    </div>
                    <div class="col-md-4 text-end">
                        <a href="modifier_patient.php?id=<?php echo $patient['id']; ?>" class="btn btn-warning mb-2"><i class="fas fa-edit"></i> Modifier</a>
                        <button class="btn btn-<?php echo $patient['est_actif'] ? 'danger' : 'success'; ?> mb-2" onclick="toggleStatut(<?php echo $patient['id']; ?>, <?php echo $patient['est_actif'] ? '1' : '0'; ?>)"><i class="fas fa-<?php echo $patient['est_actif'] ? 'ban' : 'check'; ?>"></i> <?php echo $patient['est_actif'] ? 'Désactiver' : 'Activer'; ?></button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-white"><h5><i class="fas fa-history"></i> Derniers rendez-vous</h5></div>
            <div class="table-responsive"><table class="table table-hover">
                <thead class="table-light"><tr><th>Date</th><th>Médecin</th><th>Spécialité</th><th>Statut</th><th>Motif</th></tr></thead>
                <tbody>
                <?php if (empty($rdv_recents)): ?>
                    <tr><td colspan="5" class="text-center py-4">Aucun rendez-vous</td></tr>
                <?php else: foreach ($rdv_recents as $r): ?>
                    <tr>
                        <td><?php echo date('d/m/Y H:i', strtotime($r['date_rendez_vous'])); ?></td>
                        <td><?php echo htmlspecialchars($r['medecin_nom'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($r['specialite_nom'] ?? 'Généraliste'); ?></td>
                        <td><span class="badge bg-<?php echo $r['statut'] == 'confirme' ? 'success' : ($r['statut'] == 'en_attente' ? 'warning' : ($r['statut'] == 'termine' ? 'info' : 'danger')); ?>"><?php echo $r['statut']; ?></span></td>
                        <td><?php echo htmlspecialchars(substr($r['motif'],0,60)); ?></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table></div>
        </div>

    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function toggleStatut(id, actuel) {
    Swal.fire({ title: 'Confirmation', text: actuel ? 'Désactiver ce patient ?' : 'Activer ce patient ?', icon: 'question', showCancelButton: true }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({ url: 'ajax/toggle_patient.php', type: 'POST', data: {id: id, actif: !actuel}, dataType: 'json', success: function(response) {
                if (response.success) Swal.fire('Succès!', response.message, 'success').then(() => location.reload());
                else Swal.fire('Erreur!', response.message, 'error');
            }});
        }
    });
}
</script>

<?php include '../includes/footer.php'; ?>
</body>
</html>
