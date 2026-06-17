<?php
// Fichier: admin/medecins.php
require_once '../includes/config.php';

// Vérifier si l'utilisateur est connecté et est admin
if (!estConnecte() || $_SESSION['user_role'] != 'admin') {
    header('Location: ../index.php');
    exit();
}

// Traitement des actions AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    header('Content-Type: application/json');
    
    $action = $_POST['action'] ?? '';
    $response = ['success' => false, 'message' => ''];
    
    try {
        switch ($action) {
            case 'ajouter':
                $response = ajouterMedecin($_POST, $_FILES);
                break;
            case 'modifier':
                $response = modifierMedecin($_POST, $_FILES);
                break;
            case 'supprimer':
                $response = supprimerMedecin($_POST['id'] ?? 0);
                break;
            case 'changer_statut':
                $response = changerStatutMedecin($_POST['id'] ?? 0, $_POST['statut'] ?? '');
                break;
            case 'ajouter_horaire':
                $response = ajouterHoraire($_POST);
                break;
            case 'supprimer_horaire':
                $response = supprimerHoraire($_POST['id'] ?? 0);
                break;
        }
    } catch (Exception $e) {
        $response['message'] = 'Erreur: ' . $e->getMessage();
    }
    
    echo json_encode($response);
    exit();
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$specialite_filter = isset($_GET['specialite']) ? (int)$_GET['specialite'] : 0;
$statut_filter = isset($_GET['statut']) ? $_GET['statut'] : '';

// Construction de la requête avec filtres
$sql = "SELECT m.*, s.nom as specialite_nom, 
        (SELECT COUNT(*) FROM rendez_vous WHERE medecin_id = m.id AND statut = 'termine') as total_consultations,
        (SELECT AVG(note) FROM avis_patients WHERE medecin_id = m.id AND est_approuve = TRUE) as note_moyenne
        FROM medecins m
        LEFT JOIN specialites s ON m.specialite_id = s.id
        WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (m.nom_complet LIKE ? OR m.email LIKE ? OR m.telephone LIKE ? OR m.numero_ordre LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
}

if ($specialite_filter > 0) {
    $sql .= " AND m.specialite_id = ?";
    $params[] = $specialite_filter;
}

if ($statut_filter !== '') {
    $sql .= " AND m.est_disponible = ?";
    $params[] = ($statut_filter === 'disponible') ? 1 : 0;
}

// Compter le total pour la pagination
$count_sql = str_replace("SELECT m.*, s.nom as specialite_nom, 
        (SELECT COUNT(*) FROM rendez_vous WHERE medecin_id = m.id AND statut = 'termine') as total_consultations,
        (SELECT AVG(note) FROM avis_patients WHERE medecin_id = m.id AND est_approuve = TRUE) as note_moyenne", "SELECT COUNT(*)", $sql);
$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);
$total_medecins = $stmt->fetchColumn();
$total_pages = ceil($total_medecins / $limit);

// Récupérer les médecins
$sql .= " ORDER BY m.date_creation DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$medecins = $stmt->fetchAll();

// Récupérer toutes les spécialités pour les filtres et formulaires
$specialites = $pdo->query("SELECT * FROM specialites ORDER BY nom")->fetchAll();

// Récupérer les statistiques globales
$stats = $pdo->query("
    SELECT 
        COUNT(*) as total_medecins,
        SUM(est_disponible) as medecins_disponibles,
        AVG(annees_experience) as experience_moyenne,
        (SELECT COUNT(*) FROM rendez_vous WHERE statut = 'confirme' AND date_rendez_vous >= NOW()) as rdv_aujourdhui
    FROM medecins
")->fetch();

include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Médecins - Sama Docteur Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #00549a; /* plus sombre */
            --secondary-color: #0077b8; /* plus sombre */
            --dark-color: #162430; /* texte principal plus foncé */
            --light-bg: #eef2f4; /* léger mais contrasté */
        }

        body {
            background: var(--light-bg);
            color: var(--dark-color);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            font-size: 14px;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
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
            font-size: 22px;
            font-weight: 600;
            color: #f1fbff;
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
            background: #ffffff;
            border-radius: 12px;
            padding: 12px 20px;
            margin-bottom: 22px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.06);
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
            background: #ffffff;
            border-radius: 12px;
            padding: 18px;
            margin-bottom: 18px;
            box-shadow: 0 2px 8px rgba(6,22,34,0.06);
            transition: transform 0.22s;
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
            font-size: 28px;
            font-weight: bold;
            margin: 0;
            color: var(--dark-color);
        }

        .stat-card p {
            margin: 5px 0 0;
            color: #7f8c8d;
            font-size: 14px;
        }

        /* Table Styles */
        .medecins-table {
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(6,22,34,0.04);
        }

        .medecins-table th {
            background: #f4f6f8;
            padding: 14px;
            font-weight: 700;
            color: var(--dark-color);
        }

        .medecins-table td {
            padding: 15px;
            vertical-align: middle;
        }

        /* Avatar: force un carré (fonctionne pour <img> et pour la div placeholder) */
        .medecin-avatar {
            width: 45px;
            height: 45px;
            min-width: 45px;
            border-radius: 50%;
            overflow: hidden;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background-position: center;
            background-size: cover;
        }
        .medecin-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        /* Ajustements pour petits écrans */
        @media (max-width: 768px) {
            .medecin-avatar {
                width: 36px;
                height: 36px;
                min-width: 36px;
            }
        }

        @media (max-width: 576px) {
            .action-buttons .btn { margin: 3px 2px; padding: 4px 6px; font-size: 11px; }
            .medecins-table td { font-size: 13px; }
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }

        .status-disponible {
            background: #d4edda;
            color: #155724;
        }

        .status-indisponible {
            background: #f8d7da;
            color: #721c24;
        }

        .rating-stars {
            color: #ffc107;
            font-size: 12px;
        }

        .action-buttons .btn {
            padding: 5px 10px;
            margin: 0 3px;
            font-size: 12px;
        }

        /* Modal Styles */
        .modal-content {
            border-radius: 12px;
        }

        .modal-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 12px 12px 0 0;
        }

        .form-label {
            font-weight: 500;
            color: var(--dark-color);
        }

        /* Responsive adjustments */
        @media (max-width: 992px) {
            .admin-sidebar {
                transform: translateX(-100%);
                position: fixed;
                left: 0;
                top: 0;
                height: 100vh;
            }
            .admin-content {
                margin-left: 0;
                padding: 18px;
            }
        }

        @media (max-width: 768px) {
            .admin-sidebar { width: 220px; }
            .admin-sidebar .logo h3 { font-size: 18px; }
            .top-bar { padding: 10px 12px; }
            .medecins-table td { padding: 10px; }
            .stat-card h3 { font-size: 20px; }
        }
    </style>
</head>
<body>
<div class="admin-wrapper">
    <!-- Sidebar -->
    <div class="admin-sidebar">
        <div class="logo">
            <h3><i class="fas fa-stethoscope"></i> Sama Docteur</h3>
            <p>Espace Administration</p>
        </div>
        <div class="nav-menu">
            <div class="nav-item">
                <a href="index.php" class="nav-link">
                    <i class="fas fa-tachometer-alt"></i> Tableau de bord
                </a>
            </div>
            <div class="nav-item">
                <a href="medecins.php" class="nav-link active">
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
                <a href="../logout.php" class="nav-link">
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
                <h2><i class="fas fa-user-md"></i> Gestion des Médecins</h2>
                <p>Gérez les médecins, leurs horaires et leurs disponibilités</p>
            </div>
            <div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMedecinModal">
                    <i class="fas fa-plus"></i> Nouveau Médecin
                </button>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #e3f2fd; color: #1976d2;">
                        <i class="fas fa-user-md"></i>
                    </div>
                    <h3><?php echo $stats['total_medecins']; ?></h3>
                    <p>Médecins total</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #e8f5e9; color: #388e3c;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h3><?php echo $stats['medecins_disponibles']; ?></h3>
                    <p>Médecins disponibles</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #fff3e0; color: #f57c00;">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3><?php echo round($stats['experience_moyenne']); ?> ans</h3>
                    <p>Expérience moyenne</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #fce4ec; color: #c2185b;">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <h3><?php echo $stats['rdv_aujourdhui']; ?></h3>
                    <p>RDV aujourd'hui</p>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <input type="text" class="form-control" name="search" placeholder="Rechercher par nom, email, téléphone..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="specialite">
                                    <option value="0">Toutes les spécialités</option>
                                    <?php foreach ($specialites as $spec): ?>
                                        <option value="<?php echo $spec['id']; ?>" <?php echo $specialite_filter == $spec['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($spec['nom']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select class="form-select" name="statut">
                                    <option value="">Tous les statuts</option>
                                    <option value="disponible" <?php echo $statut_filter == 'disponible' ? 'selected' : ''; ?>>Disponible</option>
                                    <option value="indisponible" <?php echo $statut_filter == 'indisponible' ? 'selected' : ''; ?>>Indisponible</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Filtrer
                                </button>
                                <a href="medecins.php" class="btn btn-secondary">
                                    <i class="fas fa-redo"></i> Réinitialiser
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Medecins Table -->
        <div class="medecins-table">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Médecin</th>
                            <th>Spécialité</th>
                            <th>Contact</th>
                            <th>Tarif</th>
                            <th>Consultations</th>
                            <th>Note</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($medecins)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <i class="fas fa-user-md fa-3x text-muted mb-3 d-block"></i>
                                    <p class="text-muted">Aucun médecin trouvé</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($medecins as $medecin): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php if ($medecin['photo']): ?>
                                            <img src="../uploads/medecins/<?php echo $medecin['photo']; ?>" class="medecin-avatar me-2">
                                        <?php else: ?>
                                            <div class="medecin-avatar bg-primary text-white d-flex align-items-center justify-content-center me-2">
                                                <i class="fas fa-user-md"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <strong><?php echo htmlspecialchars($medecin['nom_complet']); ?></strong><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($medecin['numero_ordre']); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?php echo htmlspecialchars($medecin['specialite_nom'] ?? 'Non spécifié'); ?></span>
                                </td>
                                <td>
                                    <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($medecin['email']); ?><br>
                                    <i class="fas fa-phone"></i> <?php echo htmlspecialchars($medecin['telephone']); ?>
                                </td>
                                <td>
                                    <strong><?php echo number_format($medecin['tarif_consultation'], 0, ',', ' '); ?> FCFA</strong>
                                </td>
                                <td>
                                    <?php echo $medecin['total_consultations'] ?? 0; ?> consultations
                                </td>
                                <td>
                                    <?php if ($medecin['note_moyenne']): ?>
                                        <div class="rating-stars">
                                            <?php 
                                            $note = round($medecin['note_moyenne']);
                                            for ($i = 1; $i <= 5; $i++):
                                            ?>
                                                <i class="fas fa-star<?php echo $i <= $note ? '' : '-o'; ?>"></i>
                                            <?php endfor; ?>
                                            <span class="text-muted">(<?php echo number_format($medecin['note_moyenne'], 1); ?>)</span>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">Aucun avis</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="status-badge <?php echo $medecin['est_disponible'] ? 'status-disponible' : 'status-indisponible'; ?>">
                                        <?php echo $medecin['est_disponible'] ? 'Disponible' : 'Indisponible'; ?>
                                    </span>
                                </td>
                                <td class="action-buttons">
                                    <button class="btn btn-sm btn-info" onclick="voirMedecin(<?php echo $medecin['id']; ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-warning" onclick="modifierMedecin(<?php echo $medecin['id']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-primary" onclick="gererHoraires(<?php echo $medecin['id']; ?>)">
                                        <i class="fas fa-clock"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="supprimerMedecin(<?php echo $medecin['id']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="d-flex justify-content-center mt-4">
            <nav>
                <ul class="pagination">
                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>&specialite=<?php echo $specialite_filter; ?>&statut=<?php echo $statut_filter; ?>">
                            Précédent
                        </a>
                    </li>
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&specialite=<?php echo $specialite_filter; ?>&statut=<?php echo $statut_filter; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>&specialite=<?php echo $specialite_filter; ?>&statut=<?php echo $statut_filter; ?>">
                            Suivant
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Ajouter Médecin -->
<div class="modal fade" id="addMedecinModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle"></i> Ajouter un nouveau médecin
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addMedecinForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nom complet *</label>
                            <input type="text" class="form-control" name="nom_complet" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Téléphone *</label>
                            <input type="text" class="form-control" name="telephone" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Spécialité *</label>
                            <select class="form-select" name="specialite_id" required>
                                <option value="">Sélectionner une spécialité</option>
                                <?php foreach ($specialites as $spec): ?>
                                    <option value="<?php echo $spec['id']; ?>"><?php echo htmlspecialchars($spec['nom']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Numéro d'ordre *</label>
                            <input type="text" class="form-control" name="numero_ordre" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tarif consultation (FCFA) *</label>
                            <input type="number" class="form-control" name="tarif_consultation" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Années d'expérience</label>
                            <input type="number" class="form-control" name="annees_experience" value="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Photo</label>
                            <input type="file" class="form-control" name="photo" accept="image/*">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Adresse</label>
                            <input type="text" class="form-control" name="adresse">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ville</label>
                            <input type="text" class="form-control" name="ville">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Biographie</label>
                            <textarea class="form-control" name="biographie" rows="3"></textarea>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Mot de passe *</label>
                            <input type="password" class="form-control" name="mot_de_passe" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Ajouter le médecin</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Modifier Médecin -->
<div class="modal fade" id="editMedecinModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-edit"></i> Modifier le médecin
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editMedecinForm" enctype="multipart/form-data">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nom complet *</label>
                            <input type="text" class="form-control" name="nom_complet" id="edit_nom_complet" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email" class="form-control" name="email" id="edit_email" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Téléphone *</label>
                            <input type="text" class="form-control" name="telephone" id="edit_telephone" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Spécialité *</label>
                            <select class="form-select" name="specialite_id" id="edit_specialite_id" required>
                                <?php foreach ($specialites as $spec): ?>
                                    <option value="<?php echo $spec['id']; ?>"><?php echo htmlspecialchars($spec['nom']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Numéro d'ordre *</label>
                            <input type="text" class="form-control" name="numero_ordre" id="edit_numero_ordre" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tarif consultation (FCFA) *</label>
                            <input type="number" class="form-control" name="tarif_consultation" id="edit_tarif_consultation" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Années d'expérience</label>
                            <input type="number" class="form-control" name="annees_experience" id="edit_annees_experience">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nouvelle photo</label>
                            <input type="file" class="form-control" name="photo" accept="image/*">
                            <small class="text-muted">Laissez vide pour garder la photo actuelle</small>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Adresse</label>
                            <input type="text" class="form-control" name="adresse" id="edit_adresse">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ville</label>
                            <input type="text" class="form-control" name="ville" id="edit_ville">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Biographie</label>
                            <textarea class="form-control" name="biographie" id="edit_biographie" rows="3"></textarea>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Nouveau mot de passe</label>
                            <input type="password" class="form-control" name="mot_de_passe" placeholder="Laissez vide pour garder l'ancien">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Horaires -->
<div class="modal fade" id="horairesModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-clock"></i> Gestion des horaires
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="horairesContent"></div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Ajouter un médecin
$('#addMedecinForm').on('submit', function(e) {
    e.preventDefault();
    var formData = new FormData(this);
    formData.append('action', 'ajouter');
    
    $.ajax({
        url: 'medecins.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                Swal.fire('Succès!', response.message, 'success').then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('Erreur!', response.message, 'error');
            }
        },
        error: function() {
            Swal.fire('Erreur!', 'Une erreur est survenue', 'error');
        }
    });
});

// Modifier un médecin
function modifierMedecin(id) {
    $.ajax({
        url: 'ajax/get_medecin.php',
        type: 'POST',
        data: {id: id},
        dataType: 'json',
        success: function(data) {
            $('#edit_id').val(data.id);
            $('#edit_nom_complet').val(data.nom_complet);
            $('#edit_email').val(data.email);
            $('#edit_telephone').val(data.telephone);
            $('#edit_specialite_id').val(data.specialite_id);
            $('#edit_numero_ordre').val(data.numero_ordre);
            $('#edit_tarif_consultation').val(data.tarif_consultation);
            $('#edit_annees_experience').val(data.annees_experience);
            $('#edit_adresse').val(data.adresse);
            $('#edit_ville').val(data.ville);
            $('#edit_biographie').val(data.biographie);
            $('#editMedecinModal').modal('show');
        }
    });
}

$('#editMedecinForm').on('submit', function(e) {
    e.preventDefault();
    var formData = new FormData(this);
    formData.append('action', 'modifier');
    
    $.ajax({
        url: 'medecins.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                Swal.fire('Succès!', response.message, 'success').then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('Erreur!', response.message, 'error');
            }
        }
    });
});

// Supprimer un médecin
function supprimerMedecin(id) {
    Swal.fire({
        title: 'Êtes-vous sûr?',
        text: "Cette action est irréversible!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Oui, supprimer',
        cancelButtonText: 'Annuler'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'medecins.php',
                type: 'POST',
                data: {action: 'supprimer', id: id},
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Supprimé!', response.message, 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Erreur!', response.message, 'error');
                    }
                }
            });
        }
    });
}

// Voir détails médecin
function voirMedecin(id) {
    window.location.href = 'voir_medecin.php?id=' + id;
}

// Gérer les horaires
function gererHoraires(id) {
    $('#horairesModal').modal('show');
    $('#horairesContent').html('<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-3x"></i><p>Chargement...</p></div>');
    
    $.ajax({
        url: 'ajax/horaires_medecin.php',
        type: 'POST',
        data: {medecin_id: id},
        dataType: 'html',
        success: function(html) {
            $('#horairesContent').html(html);
        }
    });
}
</script>

<?php include '../includes/footer.php'; ?>

<?php
// Fonctions pour les opérations CRUD
function ajouterMedecin($data, $files) {
    global $pdo;
    
    try {
        // Vérifier si l'email existe déjà
        $stmt = $pdo->prepare("SELECT id FROM medecins WHERE email = ?");
        $stmt->execute([$data['email']]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Cet email est déjà utilisé'];
        }
        
        // Gérer l'upload de la photo
        $photo = '';
        if (isset($files['photo']) && $files['photo']['error'] == 0) {
            $upload_dir = '../uploads/medecins/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            
            $extension = pathinfo($files['photo']['name'], PATHINFO_EXTENSION);
            $photo = uniqid() . '.' . $extension;
            move_uploaded_file($files['photo']['tmp_name'], $upload_dir . $photo);
        }
        
        // Hasher le mot de passe
        $mot_de_passe = password_hash($data['mot_de_passe'], PASSWORD_DEFAULT);
        
        // Insérer le médecin
        $stmt = $pdo->prepare("
            INSERT INTO medecins (nom_complet, email, telephone, specialite_id, numero_ordre, 
            tarif_consultation, annees_experience, photo, adresse, ville, biographie, date_creation)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $data['nom_complet'],
            $data['email'],
            $data['telephone'],
            $data['specialite_id'],
            $data['numero_ordre'],
            $data['tarif_consultation'],
            $data['annees_experience'] ?? 0,
            $photo,
            $data['adresse'] ?? '',
            $data['ville'] ?? '',
            $data['biographie'] ?? ''
        ]);
        
        $medecin_id = $pdo->lastInsertId();
        
        // Créer un compte utilisateur pour le médecin
        $stmt = $pdo->prepare("
            INSERT INTO utilisateurs (nom_complet, email, mot_de_passe, telephone, role, date_inscription)
            VALUES (?, ?, ?, ?, 'medecin', NOW())
        ");
        $stmt->execute([$data['nom_complet'], $data['email'], $mot_de_passe, $data['telephone']]);
        
        return ['success' => true, 'message' => 'Médecin ajouté avec succès'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

function modifierMedecin($data, $files) {
    global $pdo;
    
    try {
        $photo = '';
        if (isset($files['photo']) && $files['photo']['error'] == 0) {
            $upload_dir = '../uploads/medecins/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            
            $extension = pathinfo($files['photo']['name'], PATHINFO_EXTENSION);
            $photo = uniqid() . '.' . $extension;
            move_uploaded_file($files['photo']['tmp_name'], $upload_dir . $photo);
            
            // Mettre à jour avec photo
            $sql = "UPDATE medecins SET nom_complet=?, email=?, telephone=?, specialite_id=?, 
                    numero_ordre=?, tarif_consultation=?, annees_experience=?, photo=?, 
                    adresse=?, ville=?, biographie=? WHERE id=?";
            $params = [
                $data['nom_complet'], $data['email'], $data['telephone'], $data['specialite_id'],
                $data['numero_ordre'], $data['tarif_consultation'], $data['annees_experience'] ?? 0,
                $photo, $data['adresse'] ?? '', $data['ville'] ?? '', $data['biographie'] ?? '',
                $data['id']
            ];
        } else {
            // Mettre à jour sans photo
            $sql = "UPDATE medecins SET nom_complet=?, email=?, telephone=?, specialite_id=?, 
                    numero_ordre=?, tarif_consultation=?, annees_experience=?, 
                    adresse=?, ville=?, biographie=? WHERE id=?";
            $params = [
                $data['nom_complet'], $data['email'], $data['telephone'], $data['specialite_id'],
                $data['numero_ordre'], $data['tarif_consultation'], $data['annees_experience'] ?? 0,
                $data['adresse'] ?? '', $data['ville'] ?? '', $data['biographie'] ?? '',
                $data['id']
            ];
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        // Mettre à jour le mot de passe si fourni
        if (!empty($data['mot_de_passe'])) {
            $mot_de_passe = password_hash($data['mot_de_passe'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE utilisateurs SET mot_de_passe = ? WHERE email = ?");
            $stmt->execute([$mot_de_passe, $data['email']]);
        }
        
        return ['success' => true, 'message' => 'Médecin modifié avec succès'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

function supprimerMedecin($id) {
    global $pdo;
    
    try {
        // Récupérer l'email avant suppression
        $stmt = $pdo->prepare("SELECT email FROM medecins WHERE id = ?");
        $stmt->execute([$id]);
        $email = $stmt->fetchColumn();
        
        // Supprimer le médecin (les horaires seront supprimés en cascade)
        $stmt = $pdo->prepare("DELETE FROM medecins WHERE id = ?");
        $stmt->execute([$id]);
        
        // Supprimer le compte utilisateur associé
        $stmt = $pdo->prepare("DELETE FROM utilisateurs WHERE email = ? AND role = 'medecin'");
        $stmt->execute([$email]);
        
        return ['success' => true, 'message' => 'Médecin supprimé avec succès'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

function changerStatutMedecin($id, $statut) {
    global $pdo;
    
    try {
        $disponible = ($statut === 'disponible') ? 1 : 0;
        $stmt = $pdo->prepare("UPDATE medecins SET est_disponible = ? WHERE id = ?");
        $stmt->execute([$disponible, $id]);
        
        return ['success' => true, 'message' => 'Statut modifié avec succès'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

function ajouterHoraire($data) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO horaires_medecins (medecin_id, jour_semaine, heure_debut, heure_fin, duree_consultation)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
            heure_debut = VALUES(heure_debut),
            heure_fin = VALUES(heure_fin),
            duree_consultation = VALUES(duree_consultation)
        ");
        
        $stmt->execute([
            $data['medecin_id'],
            $data['jour_semaine'],
            $data['heure_debut'],
            $data['heure_fin'],
            $data['duree_consultation'] ?? 30
        ]);
        
        return ['success' => true, 'message' => 'Horaire ajouté avec succès'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

function supprimerHoraire($id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("DELETE FROM horaires_medecins WHERE id = ?");
        $stmt->execute([$id]);
        
        return ['success' => true, 'message' => 'Horaire supprimé avec succès'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}
?>