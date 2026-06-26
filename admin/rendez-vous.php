<?php
// Fichier: admin/rendez-vous.php
require_once '../includes/config.php';

if (!estConnecte() || $_SESSION['user_role'] != 'admin') {
    header('Location: ../index.php');
    exit();
}

// Filtres
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$statut = isset($_GET['statut']) ? $_GET['statut'] : '';
$date_debut = isset($_GET['date_debut']) ? $_GET['date_debut'] : '';
$date_fin = isset($_GET['date_fin']) ? $_GET['date_fin'] : '';

// Requête principale
$sql = "
    SELECT r.*, 
           u.nom_complet as patient_nom, u.email as patient_email, u.telephone as patient_tel,
           m.nom_complet as medecin_nom, m.telephone as medecin_tel,
           s.nom as specialite_nom
    FROM rendez_vous r
    JOIN utilisateurs u ON r.utilisateur_id = u.id
    JOIN medecins m ON r.medecin_id = m.id
    LEFT JOIN specialites s ON m.specialite_id = s.id
    WHERE 1=1
";
$params = [];

if (!empty($search)) {
    $sql .= " AND (u.nom_complet LIKE ? OR m.nom_complet LIKE ? OR r.motif LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param]);
}
if (!empty($statut)) {
    $sql .= " AND r.statut = ?";
    $params[] = $statut;
}
if (!empty($date_debut)) {
    $sql .= " AND DATE(r.date_rendez_vous) >= ?";
    $params[] = $date_debut;
}
if (!empty($date_fin)) {
    $sql .= " AND DATE(r.date_rendez_vous) <= ?";
    $params[] = $date_fin;
}

// Total
$count_sql = preg_replace('/SELECT.*?FROM/', 'SELECT COUNT(*) FROM', $sql, 1);
$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);
$total = $stmt->fetchColumn();
$total_pages = ceil($total / $limit);

// Données
$sql .= " ORDER BY r.date_rendez_vous DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rendez_vous = $stmt->fetchAll();

// Statistiques
$stats = $pdo->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN statut = 'confirme' THEN 1 ELSE 0 END) as confirmes,
        SUM(CASE WHEN statut = 'en_attente' THEN 1 ELSE 0 END) as en_attente,
        SUM(CASE WHEN statut = 'termine' THEN 1 ELSE 0 END) as termines,
        SUM(CASE WHEN statut = 'annule' THEN 1 ELSE 0 END) as annules
    FROM rendez_vous
")->fetch();

$noHeader = true;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Rendez-vous - Sama Docteur Admin</title>
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
            <div class="nav-item"><a href="patients.php" class="nav-link"><i class="fas fa-users"></i> Patients</a></div>
            <div class="nav-item"><a href="rendez-vous.php" class="nav-link active"><i class="fas fa-calendar-alt"></i> Rendez-vous</a></div>
            <div class="nav-item"><a href="specialites.php" class="nav-link"><i class="fas fa-tags"></i> Spécialités</a></div>
            <div class="nav-item"><a href="../deconnexion.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></div>
        </div>
    </div>

    <div class="admin-content">
        <div class="top-bar">
            <div class="page-title"><h2><i class="fas fa-calendar-alt"></i> Gestion des Rendez-vous</h2></div>
        </div>

        <div class="row mb-4">
            <div class="col-md-2"><div class="stat-card"><h3><?php echo $stats['total']; ?></h3><p>Total</p></div></div>
            <div class="col-md-2"><div class="stat-card"><h3><?php echo $stats['confirmes']; ?></h3><p>Confirmés</p></div></div>
            <div class="col-md-2"><div class="stat-card"><h3><?php echo $stats['en_attente']; ?></h3><p>En attente</p></div></div>
            <div class="col-md-2"><div class="stat-card"><h3><?php echo $stats['termines']; ?></h3><p>Terminés</p></div></div>
            <div class="col-md-2"><div class="stat-card"><h3><?php echo $stats['annules']; ?></h3><p>Annulés</p></div></div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3"><input type="text" class="form-control" name="search" placeholder="Rechercher..." value="<?php echo htmlspecialchars($search); ?>"></div>
                    <div class="col-md-2">
                        <select class="form-select" name="statut">
                            <option value="">Tous les statuts</option>
                            <option value="confirme" <?php echo $statut == 'confirme' ? 'selected' : ''; ?>>Confirmé</option>
                            <option value="en_attente" <?php echo $statut == 'en_attente' ? 'selected' : ''; ?>>En attente</option>
                            <option value="termine" <?php echo $statut == 'termine' ? 'selected' : ''; ?>>Terminé</option>
                            <option value="annule" <?php echo $statut == 'annule' ? 'selected' : ''; ?>>Annulé</option>
                        </select>
                    </div>
                    <div class="col-md-2"><input type="date" class="form-control" name="date_debut" value="<?php echo $date_debut; ?>" placeholder="Date début"></div>
                    <div class="col-md-2"><input type="date" class="form-control" name="date_fin" value="<?php echo $date_fin; ?>" placeholder="Date fin"></div>
                    <div class="col-md-3"><button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filtrer</button>
                    <a href="rendez-vous.php" class="btn btn-secondary"><i class="fas fa-redo"></i> Réinitialiser</a></div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>Date</th><th>Patient</th><th>Médecin</th><th>Spécialité</th><th>Motif</th><th>Statut</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rendez_vous as $rdv): ?>
                        <tr>
                            <td><?php echo date('d/m/Y H:i', strtotime($rdv['date_rendez_vous'])); ?></td>
                            <td><strong><?php echo htmlspecialchars($rdv['patient_nom']); ?></strong><br><small><?php echo $rdv['patient_tel']; ?></small></td>
                            <td>Dr. <?php echo htmlspecialchars($rdv['medecin_nom']); ?></td>
                            <td><?php echo htmlspecialchars($rdv['specialite_nom'] ?? 'Généraliste'); ?></td>
                            <td><?php echo htmlspecialchars(substr($rdv['motif'], 0, 50)); ?></td>
                            <td><span class="status-badge status-<?php echo $rdv['statut']; ?>"><?php echo $rdv['statut']; ?></span></td>
                            <td>
                                <button class="btn btn-sm btn-info" onclick="voirRDV(<?php echo $rdv['id']; ?>)"><i class="fas fa-eye"></i></button>
                                <?php if ($rdv['statut'] == 'en_attente'): ?>
                                    <button class="btn btn-sm btn-success" onclick="confirmerRDV(<?php echo $rdv['id']; ?>)"><i class="fas fa-check"></i></button>
                                <?php endif; ?>
                                <?php if ($rdv['statut'] != 'annule' && $rdv['statut'] != 'termine'): ?>
                                    <button class="btn btn-sm btn-danger" onclick="annulerRDV(<?php echo $rdv['id']; ?>)"><i class="fas fa-times"></i></button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if ($total_pages > 1): ?>
        <div class="d-flex justify-content-center mt-4"><nav><ul class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>"><a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&statut=<?php echo $statut; ?>&date_debut=<?php echo $date_debut; ?>&date_fin=<?php echo $date_fin; ?>"><?php echo $i; ?></a></li>
            <?php endfor; ?>
        </ul></nav></div>
        <?php endif; ?>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/admin.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function voirRDV(id) { window.location.href = 'voir_rendezvous.php?id=' + id; }
function confirmerRDV(id) {
    Swal.fire({ title: 'Confirmer', text: 'Confirmer ce rendez-vous ?', icon: 'question', showCancelButton: true }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({ url: 'ajax/changer_statut_rdv.php', type: 'POST', data: {id: id, statut: 'confirme'}, dataType: 'json', success: function(r) {
                if (r.success) Swal.fire('Succès!', 'Rendez-vous confirmé', 'success').then(() => location.reload());
                else Swal.fire('Erreur!', r.message, 'error');
            }});
        }
    });
}
function annulerRDV(id) {
    Swal.fire({ title: 'Annuler', text: 'Annuler ce rendez-vous ?', icon: 'warning', showCancelButton: true }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({ url: 'ajax/changer_statut_rdv.php', type: 'POST', data: {id: id, statut: 'annule'}, dataType: 'json', success: function(r) {
                if (r.success) Swal.fire('Annulé!', 'Rendez-vous annulé', 'success').then(() => location.reload());
                else Swal.fire('Erreur!', r.message, 'error');
            }});
        }
    });
}
</script>

<?php include '../includes/footer.php'; ?>