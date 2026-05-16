<?php
// Fichier: admin/index.php
require_once '../includes/config.php';
redirigerSiNonConnecte();

if ($_SESSION['user_role'] != 'admin') {
    header('Location: ../index.php');
    exit();
}

// Statistiques globales
$stmt = $pdo->query("SELECT COUNT(*) as total FROM utilisateurs WHERE role = 'patient'");
$total_patients = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM medecins");
$total_medecins = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM rendez_vous");
$total_rdv = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM rendez_vous WHERE statut = 'en_attente'");
$rdv_attente = $stmt->fetch()['total'];

// Derniers utilisateurs inscrits
$stmt = $pdo->query("
    SELECT * FROM utilisateurs 
    ORDER BY date_inscription DESC 
    LIMIT 5
");
$derniers_utilisateurs = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="dashboard-wrapper">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-2">
                <div class="dashboard-sidebar">
                    <div class="text-center mb-4">
                        <i class="fas fa-crown fa-4x text-primary"></i>
                        <h5 class="mt-2">Administrateur</h5>
                        <p class="text-secondary small"><?php echo htmlspecialchars($_SESSION['user_nom']); ?></p>
                    </div>
                    <ul class="dashboard-menu">
                        <li><a href="#" class="active"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a></li>
                        <li><a href="#"><i class="fas fa-users"></i> Utilisateurs</a></li>
                        <li><a href="#"><i class="fas fa-user-md"></i> Médecins</a></li>
                        <li><a href="#"><i class="fas fa-notes-medical"></i> Spécialités</a></li>
                        <li><a href="#"><i class="fas fa-calendar-alt"></i> Rendez-vous</a></li>
                        <li><a href="#"><i class="fas fa-chart-line"></i> Statistiques</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="col-lg-10">
                <div class="dashboard-sidebar">
                    <h3 class="mb-4">Tableau de bord administrateur</h3>
                    
                    <!-- Statistiques -->
                    <div class="row g-4 mb-4">
                        <div class="col-md-3">
                            <div class="stat-card">
                                <h3><?php echo $total_patients; ?></h3>
                                <p>Patients inscrits</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card">
                                <h3><?php echo $total_medecins; ?></h3>
                                <p>Médecins</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card">
                                <h3><?php echo $total_rdv; ?></h3>
                                <p>Rendez-vous</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card">
                                <h3><?php echo $rdv_attente; ?></h3>
                                <p>RDV en attente</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Derniers utilisateurs -->
                    <div class="mt-4">
                        <h4 class="mb-3">Derniers inscrits</h4>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Nom</th>
                                        <th>Email</th>
                                        <th>Téléphone</th>
                                        <th>Rôle</th>
                                        <th>Date d'inscription</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($derniers_utilisateurs as $user): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['nom_complet']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td><?php echo htmlspecialchars($user['telephone']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $user['role'] == 'admin' ? 'danger' : ($user['role'] == 'medecin' ? 'info' : 'success'); ?>">
                                                <?php echo ucfirst($user['role']); ?>
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
</div>

<?php include '../includes/footer.php'; ?>