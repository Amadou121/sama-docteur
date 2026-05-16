<?php
// Fichier: dashboard.php
require_once 'includes/config.php';
redirigerSiNonConnecte();

if ($_SESSION['user_role'] != 'patient') {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Statistiques
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_rdv,
        SUM(CASE WHEN statut = 'confirme' THEN 1 ELSE 0 END) as rdv_confirms,
        SUM(CASE WHEN statut = 'termine' THEN 1 ELSE 0 END) as rdv_termines,
        SUM(CASE WHEN statut = 'en_attente' THEN 1 ELSE 0 END) as rdv_attente
    FROM rendez_vous 
    WHERE utilisateur_id = ?
");
$stmt->execute([$user_id]);
$stats = $stmt->fetch();

// Prochains rendez-vous
$stmt = $pdo->prepare("
    SELECT r.*, m.nom_complet as medecin_nom, m.telephone as medecin_tel, s.nom as specialite_nom
    FROM rendez_vous r
    JOIN medecins m ON r.medecin_id = m.id
    JOIN specialites s ON m.specialite_id = s.id
    WHERE r.utilisateur_id = ? AND r.date_rendez_vous >= NOW() AND r.statut NOT IN ('annule', 'termine')
    ORDER BY r.date_rendez_vous ASC
    LIMIT 5
");
$stmt->execute([$user_id]);
$prochains_rdv = $stmt->fetchAll();

// Historique des rendez-vous
$stmt = $pdo->prepare("
    SELECT r.*, m.nom_complet as medecin_nom, s.nom as specialite_nom
    FROM rendez_vous r
    JOIN medecins m ON r.medecin_id = m.id
    JOIN specialites s ON m.specialite_id = s.id
    WHERE r.utilisateur_id = ? 
    ORDER BY r.date_rendez_vous DESC
    LIMIT 10
");
$stmt->execute([$user_id]);
$historique_rdv = $stmt->fetchAll();

// Notifications non lues
$stmt = $pdo->prepare("
    SELECT COUNT(*) as non_lues 
    FROM notifications 
    WHERE utilisateur_id = ? AND est_lu = FALSE
");
$stmt->execute([$user_id]);
$notifications = $stmt->fetch();

include 'includes/header.php';
?>

<div class="dashboard-wrapper">
    <div class="container">
        <div class="row">
            <div class="col-lg-3" data-aos="fade-right">
                <div class="dashboard-sidebar">
                    <div class="user-info">
                        <i class="fas fa-user-circle"></i>
                        <h5><?php echo htmlspecialchars($_SESSION['user_nom']); ?></h5>
                        <p>Patient</p>
                        <p class="small text-secondary">
                            <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($_SESSION['user_email']); ?>
                        </p>
                    </div>
                    <ul class="dashboard-menu">
                        <li><a href="#" class="active" data-page="dashboard"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a></li>
                        <li><a href="#" data-page="appointments"><i class="fas fa-calendar-alt"></i> Mes rendez-vous</a></li>
                        <li><a href="#" data-page="history"><i class="fas fa-history"></i> Historique</a></li>
                        <li><a href="#" data-page="profile"><i class="fas fa-user"></i> Mon profil</a></li>
                        <li><a href="#" data-page="notifications">
                            <i class="fas fa-bell"></i> Notifications 
                            <?php if($notifications['non_lues'] > 0): ?>
                                <span class="badge bg-danger"><?php echo $notifications['non_lues']; ?></span>
                            <?php endif; ?>
                        </a></li>
                    </ul>
                </div>
            </div>
            
            <div class="col-lg-9" data-aos="fade-left">
                <!-- Section Tableau de bord -->
                <div id="dashboardSection">
                    <div class="row g-4 mb-4">
                        <div class="col-md-3">
                            <div class="stat-card">
                                <i class="fas fa-calendar-check"></i>
                                <h3><?php echo $stats['total_rdv']; ?></h3>
                                <p>Total RDV</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card">
                                <i class="fas fa-check-circle"></i>
                                <h3><?php echo $stats['rdv_confirms']; ?></h3>
                                <p>Confirmés</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card">
                                <i class="fas fa-clock"></i>
                                <h3><?php echo $stats['rdv_attente']; ?></h3>
                                <p>En attente</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card">
                                <i class="fas fa-check-double"></i>
                                <h3><?php echo $stats['rdv_termines']; ?></h3>
                                <p>Terminés</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="dashboard-sidebar mb-4">
                        <h4 class="mb-3"><i class="fas fa-calendar-alt"></i> Prochains rendez-vous</h4>
                        <?php if(empty($prochains_rdv)): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> Aucun rendez-vous à venir.
                                <a href="specialites.php" class="alert-link">Prendre un rendez-vous</a>
                            </div>
                        <?php else: ?>
                            <?php foreach($prochains_rdv as $rdv): ?>
                            <div class="appointment-card">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h5><?php echo htmlspecialchars($rdv['medecin_nom']); ?></h5>
                                        <p class="text-primary mb-1"><?php echo htmlspecialchars($rdv['specialite_nom']); ?></p>
                                        <p class="small text-secondary mb-0">
                                            <i class="fas fa-calendar-day"></i> <?php echo date('d/m/Y', strtotime($rdv['date_rendez_vous'])); ?>
                                            à <?php echo date('H:i', strtotime($rdv['date_rendez_vous'])); ?>
                                        </p>
                                        <p class="small text-secondary">
                                            <i class="fas fa-phone-alt"></i> <?php echo htmlspecialchars($rdv['medecin_tel']); ?>
                                        </p>
                                    </div>
                                    <div class="col-md-4 text-md-end">
                                        <span class="status status-<?php echo $rdv['statut']; ?>">
                                            <?php 
                                            switch($rdv['statut']) {
                                                case 'confirme': echo 'Confirmé'; break;
                                                case 'en_attente': echo 'En attente'; break;
                                                case 'termine': echo 'Terminé'; break;
                                                case 'annule': echo 'Annulé'; break;
                                            }
                                            ?>
                                        </span>
                                        <div class="mt-2">
                                            <button class="btn btn-sm btn-outline-danger" onclick="annulerRendezVous(<?php echo $rdv['id']; ?>)">
                                                <i class="fas fa-times"></i> Annuler
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Section Historique -->
                <div id="historySection" style="display: none;">
                    <div class="dashboard-sidebar">
                        <h4 class="mb-3"><i class="fas fa-history"></i> Historique des rendez-vous</h4>
                        <?php if(empty($historique_rdv)): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> Aucun historique de rendez-vous.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Médecin</th>
                                            <th>Spécialité</th>
                                            <th>Statut</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($historique_rdv as $rdv): ?>
                                        <tr>
                                            <td><?php echo date('d/m/Y H:i', strtotime($rdv['date_rendez_vous'])); ?></td>
                                            <td><?php echo htmlspecialchars($rdv['medecin_nom']); ?></td>
                                            <td><?php echo htmlspecialchars($rdv['specialite_nom']); ?></td>
                                            <td>
                                                <span class="status status-<?php echo $rdv['statut']; ?>">
                                                    <?php 
                                                    switch($rdv['statut']) {
                                                        case 'confirme': echo 'Confirmé'; break;
                                                        case 'en_attente': echo 'En attente'; break;
                                                        case 'termine': echo 'Terminé'; break;
                                                        case 'annule': echo 'Annulé'; break;
                                                    }
                                                    ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if($rdv['statut'] == 'termine'): ?>
                                                    <button class="btn btn-sm btn-primary" onclick="voirDetails(<?php echo $rdv['id']; ?>)">
                                                        <i class="fas fa-eye"></i> Détails
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Section Profil -->
                <div id="profileSection" style="display: none;">
                    <div class="dashboard-sidebar">
                        <h4 class="mb-3"><i class="fas fa-user"></i> Mon profil</h4>
                        <form id="profileForm">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nom complet</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($_SESSION['user_nom']); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" value="<?php echo htmlspecialchars($_SESSION['user_email']); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Téléphone</label>
                                    <input type="tel" class="form-control" placeholder="77 123 45 67">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Adresse</label>
                                    <input type="text" class="form-control" placeholder="Votre adresse">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Mettre à jour</button>
                        </form>
                        
                        <hr class="my-4">
                        
                        <h5>Changer le mot de passe</h5>
                        <form>
                            <div class="mb-3">
                                <label class="form-label">Mot de passe actuel</label>
                                <input type="password" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nouveau mot de passe</label>
                                <input type="password" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Confirmer le mot de passe</label>
                                <input type="password" class="form-control">
                            </div>
                            <button type="submit" class="btn btn-primary">Changer le mot de passe</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function annulerRendezVous(id) {
    if(confirmSuppression('Voulez-vous vraiment annuler ce rendez-vous ?')) {
        showNotification('Rendez-vous annulé avec succès', 'success');
    }
}

function voirDetails(id) {
    showNotification('Détails du rendez-vous', 'info');
}

// Navigation dans le dashboard
document.querySelectorAll('.dashboard-menu a').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        const page = this.dataset.page;
        
        // Cacher toutes les sections
        document.getElementById('dashboardSection').style.display = 'none';
        document.getElementById('historySection').style.display = 'none';
        document.getElementById('profileSection').style.display = 'none';
        
        // Afficher la section correspondante
        if(page === 'dashboard') {
            document.getElementById('dashboardSection').style.display = 'block';
        } else if(page === 'history') {
            document.getElementById('historySection').style.display = 'block';
        } else if(page === 'profile') {
            document.getElementById('profileSection').style.display = 'block';
        }
        
        // Mettre à jour la classe active
        document.querySelectorAll('.dashboard-menu a').forEach(a => a.classList.remove('active'));
        this.classList.add('active');
    });
});
</script>

<?php include 'includes/footer.php'; ?>