<?php
// Fichier: admin/voir_rendezvous.php
require_once '../includes/config.php';

if (!estConnecte() || $_SESSION['user_role'] != 'admin') {
    header('Location: ../index.php');
    exit();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) { header('Location: rendez-vous.php'); exit(); }

// Récupérer les infos du rendez-vous
$stmt = $pdo->prepare("SELECT r.*, u.nom_complet as patient_nom, u.email as patient_email, u.telephone as patient_tel,
    m.nom_complet as medecin_nom, s.nom as specialite_nom
    FROM rendez_vous r
    JOIN utilisateurs u ON r.utilisateur_id = u.id
    JOIN medecins m ON r.medecin_id = m.id
    LEFT JOIN specialites s ON m.specialite_id = s.id
    WHERE r.id = ?");
$stmt->execute([$id]);
$rdv = $stmt->fetch();

if (!$rdv) { header('Location: rendez-vous.php'); exit(); }

$noHeader = true;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détails du rendez-vous #<?php echo $rdv['id']; ?> - Sama Docteur</title>
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
        .stat-card { background: white; border-radius: 12px; padding: 20px; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .stat-card h3 { font-size: 28px; font-weight: bold; margin: 0; }
        .status-badge { padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 500; }
        .status-confirme { background: #d4edda; color: #155724; }
        .status-en_attente { background: #fff3cd; color: #856404; }
        .status-termine { background: #d1ecf1; color: #0c5460; }
        .status-annule { background: #f8d7da; color: #721c24; }
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
            <div class="nav-item"><a href="patients.php" class="nav-link"><i class="fas fa-users"></i> Patients</a></div>
            <div class="nav-item"><a href="rendez-vous.php" class="nav-link active"><i class="fas fa-calendar-alt"></i> Rendez-vous</a></div>
            <div class="nav-item"><a href="specialites.php" class="nav-link"><i class="fas fa-tags"></i> Spécialités</a></div>
            <div class="nav-item"><a href="../deconnexion.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></div>
        </div>
    </div>

    <div class="admin-content">
        <div class="top-bar">
            <div class="page-title"><h2><i class="fas fa-calendar-alt"></i> Détails du Rendez-vous</h2></div>
            <div class="actions"><a href="rendez-vous.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Retour</a></div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <h4>Rendez-vous #<?php echo $rdv['id']; ?></h4>
                        <p><strong>Date :</strong> <?php echo date('d/m/Y H:i', strtotime($rdv['date_rendez_vous'])); ?></p>
                        <p><strong>Motif :</strong><br><?php echo nl2br(htmlspecialchars($rdv['motif'])); ?></p>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card mb-3">
                            <h3><?php echo htmlspecialchars($rdv['patient_nom']); ?></h3>
                            <p>Patient</p>
                            <small><?php echo htmlspecialchars($rdv['patient_tel']); ?></small><br>
                            <small><a href="mailto:<?php echo htmlspecialchars($rdv['patient_email']); ?>"><?php echo htmlspecialchars($rdv['patient_email']); ?></a></small>
                        </div>
                        <div class="stat-card">
                            <h3>Dr. <?php echo htmlspecialchars($rdv['medecin_nom']); ?></h3>
                            <p><?php echo htmlspecialchars($rdv['specialite_nom'] ?? 'Généraliste'); ?></p>
                        </div>
                    </div>
                </div>
                <hr>
                <p><strong>Statut :</strong> <span class="status-badge status-<?php echo $rdv['statut']; ?>"><?php echo $rdv['statut']; ?></span></p>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php include '../includes/footer.php'; ?>
</body>
</html>
