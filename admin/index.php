<?php
// Fichier: admin/index.php
require_once '../includes/config.php';

// Vérifier si l'utilisateur est connecté et est admin
if (!estConnecte() || $_SESSION['user_role'] != 'admin') {
    header('Location: ../index.php');
    exit();
}

// Statistiques globales
$stats = $pdo->query("
    SELECT 
        (SELECT COUNT(*) FROM utilisateurs WHERE role = 'patient') as total_patients,
        (SELECT COUNT(*) FROM medecins) as total_medecins,
        (SELECT COUNT(*) FROM rendez_vous) as total_rdv,
        (SELECT COUNT(*) FROM rendez_vous WHERE statut = 'confirme' AND date_rendez_vous >= CURDATE()) as rdv_aujourdhui,
        (SELECT COUNT(*) FROM rendez_vous WHERE statut = 'en_attente') as rdv_attente,
        (SELECT COUNT(*) FROM rendez_vous WHERE statut = 'termine' AND MONTH(date_rendez_vous) = MONTH(CURRENT_DATE())) as rdv_mois,
        (SELECT SUM(tarif_consultation) FROM medecins) as chiffre_affaires_potentiel,
        (SELECT AVG(note) FROM avis_patients WHERE est_approuve = TRUE) as note_moyenne_globale
")->fetch();

// Rendez-vous du jour
$rdv_aujourdhui = $pdo->prepare("
    SELECT r.*, u.nom_complet as patient_nom, m.nom_complet as medecin_nom, m.telephone as medecin_tel
    FROM rendez_vous r
    JOIN utilisateurs u ON r.utilisateur_id = u.id
    JOIN medecins m ON r.medecin_id = m.id
    WHERE DATE(r.date_rendez_vous) = CURDATE()
    ORDER BY r.date_rendez_vous ASC
");
$rdv_aujourdhui->execute();
$rendez_vous_jour = $rdv_aujourdhui->fetchAll();

// Statistiques par spécialité
$stats_specialites = $pdo->query("
    SELECT s.nom, COUNT(m.id) as total_medecins, COUNT(r.id) as total_consultations
    FROM specialites s
    LEFT JOIN medecins m ON s.id = m.specialite_id
    LEFT JOIN rendez_vous r ON m.id = r.medecin_id AND r.statut = 'termine'
    GROUP BY s.id
    ORDER BY total_consultations DESC
    LIMIT 5
")->fetchAll();

// Meilleurs médecins (par notes)
$top_medecins = $pdo->query("
    SELECT m.id, m.nom_complet, s.nom as specialite, AVG(a.note) as note_moyenne, COUNT(a.id) as total_avis
    FROM medecins m
    LEFT JOIN specialites s ON m.specialite_id = s.id
    LEFT JOIN avis_patients a ON m.id = a.medecin_id AND a.est_approuve = TRUE
    GROUP BY m.id
    HAVING note_moyenne IS NOT NULL
    ORDER BY note_moyenne DESC
    LIMIT 5
")->fetchAll();

// Évolution des rendez-vous (7 derniers jours)
$evolution_rdv = $pdo->query("
    SELECT DATE(date_rendez_vous) as date, COUNT(*) as total
    FROM rendez_vous
    WHERE date_rendez_vous >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY DATE(date_rendez_vous)
    ORDER BY date ASC
")->fetchAll();

// Derniers inscrits
$derniers_inscrits = $pdo->query("
    SELECT nom_complet, email, role, date_inscription
    FROM utilisateurs
    ORDER BY date_inscription DESC
    LIMIT 5
")->fetchAll();

// Distribution des statuts des rendez-vous
$stats_statuts = $pdo->query("
    SELECT statut, COUNT(*) as total
    FROM rendez_vous
    GROUP BY statut
")->fetchAll();

$noHeader = true;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord Admin - Sama Docteur</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-color: #0066cc;
            --secondary-color: #00a8ff;
            --dark-color: #2c3e50;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --info-color: #17a2b8;
        }

        body {
            background: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .admin-wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .admin-sidebar {
            width: 280px;
            background: linear-gradient(135deg, var(--dark-color) 0%, #34495e 100%);
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
        }

        .admin-sidebar .logo {
            padding: 25px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .admin-sidebar .logo h3 {
            margin: 0;
            font-size: 24px;
        }

        .admin-sidebar .logo p {
            margin: 5px 0 0;
            font-size: 12px;
            opacity: 0.8;
        }

        .admin-sidebar .nav-menu {
            padding: 20px 0;
        }

        .admin-sidebar .nav-item {
            margin-bottom: 5px;
        }

        .admin-sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 25px;
            transition: all 0.3s;
            display: flex;
            align-items: center;
        }

        .admin-sidebar .nav-link:hover,
        .admin-sidebar .nav-link.active {
            background: rgba(255,255,255,0.1);
            color: white;
            border-left: 4px solid var(--secondary-color);
        }

        .admin-sidebar .nav-link i {
            width: 25px;
            margin-right: 10px;
        }

        /* Main Content */
        .admin-content {
            flex: 1;
            margin-left: 280px;
            padding: 20px;
        }

        /* Top Bar */
        .top-bar {
            background: white;
            border-radius: 12px;
            padding: 15px 25px;
            margin-bottom: 25px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-title h2 {
            margin: 0;
            font-size: 24px;
            color: var(--dark-color);
        }

        .page-title p {
            margin: 5px 0 0;
            color: #7f8c8d;
            font-size: 14px;
        }

        /* Stats Cards */
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            transition: transform 0.3s;
            border-left: 4px solid;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .stat-card .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 15px;
        }

        .stat-card h3 {
            font-size: 32px;
            font-weight: bold;
            margin: 0;
            color: var(--dark-color);
        }

        .stat-card p {
            margin: 5px 0 0;
            color: #7f8c8d;
            font-size: 14px;
        }

        /* Card Styles */
        .dashboard-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        .dashboard-card .card-header {
            background: none;
            border-bottom: 2px solid #f0f0f0;
            padding: 0 0 15px 0;
            margin-bottom: 20px;
            font-weight: 600;
            font-size: 18px;
        }

        .rdv-item {
            padding: 12px;
            border-left: 3px solid var(--primary-color);
            background: #f8f9fa;
            margin-bottom: 10px;
            border-radius: 8px;
        }

        .rdv-time {
            font-weight: bold;
            color: var(--primary-color);
        }

        .status-badge {
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 500;
        }

        .status-confirme { background: #d4edda; color: #155724; }
        .status-en_attente { background: #fff3cd; color: #856404; }
        .status-termine { background: #d1ecf1; color: #0c5460; }
        .status-annule { background: #f8d7da; color: #721c24; }

        .rating-stars {
            color: #ffc107;
            font-size: 12px;
        }

        @media (max-width: 768px) {
            .admin-sidebar {
                transform: translateX(-100%);
            }
            .admin-content {
                margin-left: 0;
            }
        }
    </style>
    <link rel="stylesheet" href="assets/css/admin-responsive.css">
</head>
<body>
<button class="admin-toggle-btn" aria-label="Ouvrir le menu"><i class="fas fa-bars"></i></button>
<div class="admin-overlay"></div>
<div class="admin-wrapper">
    <!-- Sidebar -->
    <div class="admin-sidebar">
        <div class="logo">
            <h3><i class="fas fa-stethoscope"></i> Sama Docteur</h3>
            <p>Espace Administration</p>
        </div>
        <div class="nav-menu">
            <div class="nav-item">
                <a href="index.php" class="nav-link active">
                    <i class="fas fa-tachometer-alt"></i> Tableau de bord
                </a>
            </div>
            <div class="nav-item">
                <a href="medecins.php" class="nav-link">
                    <i class="fas fa-user-md"></i> Médecins
                </a>
            </div>
            <div class="nav-item">
                <a href="patients.php" class="nav-link">
                    <i class="fas fa-users"></i> Patients
                </a>
            </div>
            <div class="nav-item">
                <a href="rendez-vous.php" class="nav-link">
                    <i class="fas fa-calendar-alt"></i> Rendez-vous
                </a>
            </div>
            <div class="nav-item">
                <a href="specialites.php" class="nav-link">
                    <i class="fas fa-tags"></i> Spécialités
                </a>
            </div>
            <div class="nav-item">
                <a href="rapports.php" class="nav-link">
                    <i class="fas fa-chart-line"></i> Rapports
                </a>
            </div>
            <div class="nav-item">
                <a href="../deconnexion.php" class="nav-link">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="admin-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <div class="page-title">
                <h2><i class="fas fa-chart-line"></i> Tableau de bord</h2>
                <p>Bienvenue, <?php echo htmlspecialchars($_SESSION['user_nom']); ?> | Vue d'ensemble de la plateforme</p>
            </div>
            <div>
                <span class="text-muted">
                    <i class="fas fa-calendar"></i> <?php echo date('d/m/Y'); ?>
                </span>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card" style="border-left-color: var(--primary-color);">
                    <div class="stat-icon" style="background: #e3f2fd; color: #1976d2;">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3><?php echo $stats['total_patients']; ?></h3>
                    <p>Patients inscrits</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card" style="border-left-color: var(--success-color);">
                    <div class="stat-icon" style="background: #e8f5e9; color: #388e3c;">
                        <i class="fas fa-user-md"></i>
                    </div>
                    <h3><?php echo $stats['total_medecins']; ?></h3>
                    <p>Médecins</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card" style="border-left-color: var(--warning-color);">
                    <div class="stat-icon" style="background: #fff3e0; color: #f57c00;">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <h3><?php echo $stats['rdv_aujourdhui']; ?></h3>
                    <p>RDV aujourd'hui</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card" style="border-left-color: var(--info-color);">
                    <div class="stat-icon" style="background: #e0f7fa; color: #0097a7;">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3><?php echo $stats['rdv_mois']; ?></h3>
                    <p>RDV ce mois</p>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Graphique des rendez-vous -->
            <div class="col-md-8">
                <div class="dashboard-card">
                    <div class="card-header">
                        <i class="fas fa-chart-line"></i> Évolution des rendez-vous (7 derniers jours)
                    </div>
                    <canvas id="rdvChart" height="250"></canvas>
                </div>
            </div>

            <!-- Distribution des statuts -->
            <div class="col-md-4">
                <div class="dashboard-card">
                    <div class="card-header">
                        <i class="fas fa-chart-pie"></i> Distribution des rendez-vous
                    </div>
                    <canvas id="statusChart" height="250"></canvas>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Rendez-vous du jour -->
            <div class="col-md-6">
                <div class="dashboard-card">
                    <div class="card-header">
                        <i class="fas fa-calendar-day"></i> Rendez-vous du jour
                        <span class="badge bg-primary float-end"><?php echo count($rendez_vous_jour); ?></span>
                    </div>
                    <?php if (empty($rendez_vous_jour)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-times fa-3x text-muted mb-2"></i>
                            <p class="text-muted">Aucun rendez-vous programmé pour aujourd'hui</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($rendez_vous_jour as $rdv): ?>
                            <div class="rdv-item">
                                <div class="row align-items-center">
                                    <div class="col-md-4">
                                        <span class="rdv-time">
                                            <i class="fas fa-clock"></i> <?php echo date('H:i', strtotime($rdv['date_rendez_vous'])); ?>
                                        </span>
                                    </div>
                                    <div class="col-md-5">
                                        <strong><?php echo htmlspecialchars($rdv['patient_nom']); ?></strong><br>
                                        <small class="text-muted">Dr. <?php echo htmlspecialchars($rdv['medecin_nom']); ?></small>
                                    </div>
                                    <div class="col-md-3 text-end">
                                        <span class="status-badge status-<?php echo $rdv['statut']; ?>">
                                            <?php echo $rdv['statut'] == 'confirme' ? 'Confirmé' : ($rdv['statut'] == 'en_attente' ? 'En attente' : $rdv['statut']); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Top médecins -->
            <div class="col-md-6">
                <div class="dashboard-card">
                    <div class="card-header">
                        <i class="fas fa-star"></i> Meilleurs médecins (par note)
                    </div>
                    <?php if (empty($top_medecins)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-star-half-alt fa-3x text-muted mb-2"></i>
                            <p class="text-muted">Aucun avis disponible</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($top_medecins as $medecin): ?>
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                <div>
                                    <strong><?php echo htmlspecialchars($medecin['nom_complet']); ?></strong><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($medecin['specialite']); ?></small>
                                </div>
                                <div class="text-end">
                                    <div class="rating-stars">
                                        <?php 
                                        $note = round($medecin['note_moyenne']);
                                        for ($i = 1; $i <= 5; $i++):
                                        ?>
                                            <i class="fas fa-star<?php echo $i <= $note ? '' : '-o'; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <small class="text-muted"><?php echo number_format($medecin['note_moyenne'], 1); ?> (<?php echo $medecin['total_avis']; ?> avis)</small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Spécialités populaires -->
            <div class="col-md-6">
                <div class="dashboard-card">
                    <div class="card-header">
                        <i class="fas fa-chart-bar"></i> Spécialités les plus consultées
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Spécialité</th>
                                    <th>Médecins</th>
                                    <th>Consultations</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stats_specialites as $spec): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($spec['nom']); ?></td>
                                        <td><?php echo $spec['total_medecins']; ?></td>
                                        <td><?php echo $spec['total_consultations'] ?? 0; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Derniers inscrits -->
            <div class="col-md-6">
                <div class="dashboard-card">
                    <div class="card-header">
                        <i class="fas fa-user-plus"></i> Derniers inscrits
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Email</th>
                                    <th>Rôle</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($derniers_inscrits as $user): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['nom_complet']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $user['role'] == 'admin' ? 'danger' : ($user['role'] == 'medecin' ? 'info' : 'secondary'); ?>">
                                                <?php echo $user['role']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($user['date_inscription'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Graphique des rendez-vous
const ctx1 = document.getElementById('rdvChart').getContext('2d');
new Chart(ctx1, {
    type: 'line',
    data: {
        labels: <?php echo json_encode(array_column($evolution_rdv, 'date')); ?>,
        datasets: [{
            label: 'Nombre de rendez-vous',
            data: <?php echo json_encode(array_column($evolution_rdv, 'total')); ?>,
            borderColor: '#0066cc',
            backgroundColor: 'rgba(0, 102, 204, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'top',
            }
        }
    }
});

// Graphique des statuts
const ctx2 = document.getElementById('statusChart').getContext('2d');
const statutsData = <?php 
    $labels = [];
    $data = [];
    $colors = [];
    foreach ($stats_statuts as $statut) {
        $labels[] = $statut['statut'];
        $data[] = $statut['total'];
        switch($statut['statut']) {
            case 'confirme': $colors[] = '#28a745'; break;
            case 'en_attente': $colors[] = '#ffc107'; break;
            case 'termine': $colors[] = '#17a2b8'; break;
            case 'annule': $colors[] = '#dc3545'; break;
            default: $colors[] = '#6c757d';
        }
    }
    echo json_encode(['labels' => $labels, 'data' => $data, 'colors' => $colors]);
?>;

new Chart(ctx2, {
    type: 'doughnut',
    data: {
        labels: statutsData.labels,
        datasets: [{
            data: statutsData.data,
            backgroundColor: statutsData.colors,
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom',
            }
        }
    }
});
</script>

<?php include '../includes/footer.php'; ?>