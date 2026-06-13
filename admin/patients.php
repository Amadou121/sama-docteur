<?php
// Fichier: admin/patients.php
require_once '../includes/config.php';

if (!estConnecte() || $_SESSION['user_role'] != 'admin') {
    header('Location: ../index.php');
    exit();
}

// Pagination et filtres
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 15;
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$statut_filter = isset($_GET['statut']) ? $_GET['statut'] : '';

// Construction de la requête
$sql = "SELECT u.*, 
        (SELECT COUNT(*) FROM rendez_vous WHERE utilisateur_id = u.id) as total_rdv,
        (SELECT COUNT(*) FROM rendez_vous WHERE utilisateur_id = u.id AND statut = 'termine') as rdv_termines,
        (SELECT COUNT(*) FROM rendez_vous WHERE utilisateur_id = u.id AND statut = 'confirme' AND date_rendez_vous >= NOW()) as rdv_a_venir
        FROM utilisateurs u
        WHERE u.role = 'patient'";
$params = [];

if (!empty($search)) {
    $sql .= " AND (u.nom_complet LIKE ? OR u.email LIKE ? OR u.telephone LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param]);
}

if ($statut_filter !== '') {
    $sql .= " AND u.est_actif = ?";
    $params[] = ($statut_filter === 'actif') ? 1 : 0;
}

// Compter le total
$count_sql = str_replace("SELECT u.*, (SELECT COUNT(*) FROM rendez_vous WHERE utilisateur_id = u.id) as total_rdv, (SELECT COUNT(*) FROM rendez_vous WHERE utilisateur_id = u.id AND statut = 'termine') as rdv_termines, (SELECT COUNT(*) FROM rendez_vous WHERE utilisateur_id = u.id AND statut = 'confirme' AND date_rendez_vous >= NOW()) as rdv_a_venir", "SELECT COUNT(*)", $sql);
$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);
$total_patients = $stmt->fetchColumn();
$total_pages = ceil($total_patients / $limit);

// Récupérer les patients
$sql .= " ORDER BY u.date_inscription DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$patients = $stmt->fetchAll();

// Statistiques
$stats = $pdo->query("
    SELECT 
        COUNT(*) as total,
        SUM(est_actif) as actifs,
        (SELECT COUNT(*) FROM rendez_vous WHERE utilisateur_id IN (SELECT id FROM utilisateurs WHERE role = 'patient')) as total_rdv,
        (SELECT COUNT(DISTINCT utilisateur_id) FROM rendez_vous WHERE date_rendez_vous >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as actifs_30j
    FROM utilisateurs WHERE role = 'patient'
")->fetch();

include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Patients - Sama Docteur Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        /* Mêmes styles que medecins.php */
        body { background: #f4f6f9; }
        .admin-wrapper { display: flex; min-height: 100vh; }
        .admin-sidebar { width: 280px; background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%); color: white; position: fixed; height: 100vh; overflow-y: auto; }
        .admin-sidebar .logo { padding: 25px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .admin-sidebar .nav-link { color: rgba(255,255,255,0.8); padding: 12px 25px; display: flex; align-items: center; }
        .admin-sidebar .nav-link:hover, .admin-sidebar .nav-link.active { background: rgba(255,255,255,0.1); border-left: 4px solid #00a8ff; }
        .admin-sidebar .nav-link i { width: 25px; margin-right: 10px; }
        .admin-content { flex: 1; margin-left: 280px; padding: 20px; }
        .top-bar { background: white; border-radius: 12px; padding: 15px 25px; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; }
        .stat-card { background: white; border-radius: 12px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); transition: transform 0.3s; }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-card h3 { font-size: 32px; font-weight: bold; margin: 0; }
        .status-badge { padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 500; display: inline-block; }
        .status-actif { background: #d4edda; color: #155724; }
        .status-inactif { background: #f8d7da; color: #721c24; }
        .patient-table { background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .patient-table th { background: #f8f9fa; padding: 15px; }
        .patient-table td { padding: 15px; vertical-align: middle; }
        .avatar { width: 40px; height: 40px; border-radius: 50%; background: #0066cc; color: white; display: flex; align-items: center; justify-content: center; }
        @media (max-width: 768px) { .admin-sidebar { transform: translateX(-100%); } .admin-content { margin-left: 0; } }
    </style>
</head>
<body>
<div class="admin-wrapper">
    <div class="admin-sidebar">
        <div class="logo">
            <h3><i class="fas fa-stethoscope"></i> Sama Docteur</h3>
            <p>Espace Administration</p>
        </div>
        <div class="nav-menu">
            <div class="nav-item"><a href="dashboard.php" class="nav-link"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a></div>
            <div class="nav-item"><a href="medecins.php" class="nav-link"><i class="fas fa-user-md"></i> Médecins</a></div>
            <div class="nav-item"><a href="patients.php" class="nav-link active"><i class="fas fa-users"></i> Patients</a></div>
            <div class="nav-item"><a href="rendez-vous.php" class="nav-link"><i class="fas fa-calendar-alt"></i> Rendez-vous</a></div>
            <div class="nav-item"><a href="specialites.php" class="nav-link"><i class="fas fa-tags"></i> Spécialités</a></div>
            <div class="nav-item"><a href="../logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></div>
        </div>
    </div>

    <div class="admin-content">
        <div class="top-bar">
            <div class="page-title">
                <h2><i class="fas fa-users"></i> Gestion des Patients</h2>
                <p>Gérez les patients inscrits sur la plateforme</p>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <h3><?php echo $stats['total']; ?></h3>
                    <p>Patients inscrits</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <h3><?php echo $stats['actifs']; ?></h3>
                    <p>Comptes actifs</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <h3><?php echo $stats['total_rdv']; ?></h3>
                    <p>Total rendez-vous</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <h3><?php echo $stats['actifs_30j']; ?></h3>
                    <p>Actifs (30 jours)</p>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="search" placeholder="Rechercher par nom, email ou téléphone..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="statut">
                                    <option value="">Tous les statuts</option>
                                    <option value="actif" <?php echo $statut_filter == 'actif' ? 'selected' : ''; ?>>Actif</option>
                                    <option value="inactif" <?php echo $statut_filter == 'inactif' ? 'selected' : ''; ?>>Inactif</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filtrer</button>
                                <a href="patients.php" class="btn btn-secondary"><i class="fas fa-redo"></i> Réinitialiser</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="patient-table">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Patient</th>
                            <th>Contact</th>
                            <th>Inscription</th>
                            <th>Dernière connexion</th>
                            <th>RDV</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($patients)): ?>
                            <tr><td colspan="7" class="text-center py-5">Aucun patient trouvé</td></tr>
                        <?php else: ?>
                            <?php foreach ($patients as $patient): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar me-2"><i class="fas fa-user"></i></div>
                                        <div>
                                            <strong><?php echo htmlspecialchars($patient['nom_complet']); ?></strong>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($patient['email']); ?><br>
                                    <i class="fas fa-phone"></i> <?php echo htmlspecialchars($patient['telephone'] ?? 'Non renseigné'); ?>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($patient['date_inscription'])); ?></td>
                                <td><?php echo $patient['derniere_connexion'] ? date('d/m/Y H:i', strtotime($patient['derniere_connexion'])) : 'Jamais'; ?></td>
                                <td>
                                    <span class="badge bg-primary"><?php echo $patient['total_rdv']; ?> total</span><br>
                                    <small class="text-success"><?php echo $patient['rdv_a_venir']; ?> à venir</small>
                                </td>
                                <td>
                                    <span class="status-badge <?php echo $patient['est_actif'] ? 'status-actif' : 'status-inactif'; ?>">
                                        <?php echo $patient['est_actif'] ? 'Actif' : 'Inactif'; ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-info" onclick="voirPatient(<?php echo $patient['id']; ?>)"><i class="fas fa-eye"></i></button>
                                    <button class="btn btn-sm btn-warning" onclick="modifierPatient(<?php echo $patient['id']; ?>)"><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-sm <?php echo $patient['est_actif'] ? 'btn-danger' : 'btn-success'; ?>" onclick="toggleStatut(<?php echo $patient['id']; ?>, <?php echo $patient['est_actif']; ?>)">
                                        <i class="fas fa-<?php echo $patient['est_actif'] ? 'ban' : 'check'; ?>"></i>
                                    </button>
                                 </td>
                             </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if ($total_pages > 1): ?>
        <div class="d-flex justify-content-center mt-4">
            <nav><ul class="pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&statut=<?php echo $statut_filter; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
            </ul></nav>
        </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function voirPatient(id) { window.location.href = 'voir_patient.php?id=' + id; }
function modifierPatient(id) { window.location.href = 'modifier_patient.php?id=' + id; }
function toggleStatut(id, actuel) {
    Swal.fire({ title: 'Confirmation', text: actuel ? 'Désactiver ce patient ?' : 'Activer ce patient ?', icon: 'question', showCancelButton: true, confirmButtonText: 'Confirmer' }).then((result) => {
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