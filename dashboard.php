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
$stmt = $pdo->prepare("\n SELECT \n COUNT(*) as total_rdv,\n SUM(CASE WHEN statut = 'confirme' THEN 1 ELSE 0 END) as rdv_confirms,\n SUM(CASE WHEN statut = 'termine' THEN 1 ELSE 0 END) as rdv_termines,\n SUM(CASE WHEN statut = 'en_attente' THEN 1 ELSE 0 END) as rdv_attente\n FROM rendez_vous \n WHERE utilisateur_id = ?\n");
$stmt->execute([$user_id]);
$stats = $stmt->fetch();

// Prochains rendez-vous
$stmt = $pdo->prepare("\n SELECT r.*, m.nom_complet as medecin_nom, m.telephone as medecin_tel, s.nom as specialite_nom\n FROM rendez_vous r\n JOIN medecins m ON r.medecin_id = m.id\n JOIN specialites s ON m.specialite_id = s.id\n WHERE r.utilisateur_id = ? AND r.date_rendez_vous >= NOW() AND r.statut NOT IN ('annule', 'termine')\n ORDER BY r.date_rendez_vous ASC\n LIMIT 5\n");
$stmt->execute([$user_id]);
$prochains_rdv = $stmt->fetchAll();

// Compter les rendez-vous en attente de confirmation
$stmt = $pdo->prepare("\n SELECT COUNT(*) as nb_attente\n FROM rendez_vous \n WHERE utilisateur_id = ? AND statut = 'en_attente' AND date_rendez_vous >= NOW()\n");
$stmt->execute([$user_id]);
$rdv_attente_count = $stmt->fetch();

// Compter les rendez-vous confirmés à venir
$stmt = $pdo->prepare("\n SELECT COUNT(*) as nb_confirmes\n FROM rendez_vous \n WHERE utilisateur_id = ? AND statut = 'confirme' AND date_rendez_vous >= NOW()\n");
$stmt->execute([$user_id]);
$rdv_confirmes_count = $stmt->fetch();

// Historique des rendez-vous
$stmt = $pdo->prepare("\n SELECT r.*, m.nom_complet as medecin_nom, s.nom as specialite_nom\n FROM rendez_vous r\n JOIN medecins m ON r.medecin_id = m.id\n JOIN specialites s ON m.specialite_id = s.id\n WHERE r.utilisateur_id = ? \n ORDER BY r.date_rendez_vous DESC\n LIMIT 10\n");
$stmt->execute([$user_id]);
$historique_rdv = $stmt->fetchAll();

// Notifications non lues
$stmt = $pdo->prepare("\n SELECT COUNT(*) as non_lues \n FROM notifications \n WHERE utilisateur_id = ? AND est_lu = FALSE\n");
$stmt->execute([$user_id]);
$notifications = $stmt->fetch();

// Récupérer toutes les notifications pour l'affichage
$stmt = $pdo->prepare("\n SELECT * FROM notifications \n WHERE utilisateur_id = ? \n ORDER BY date_creation DESC \n LIMIT 20\n");
$stmt->execute([$user_id]);
$liste_notifications = $stmt->fetchAll();

include 'includes/header.php';
?>

<style>
.dashboard-wrapper {
 background: #f8f9fa;
 min-height: calc(100vh - 200px);
 padding: 30px 0;
}

.dashboard-sidebar {
 background: white;
 border-radius: 15px;
 padding: 25px;
 box-shadow: 0 2px 10px rgba(0,0,0,0.1);
 margin-bottom: 30px;
}

.user-info {
 text-align: center;
 padding-bottom: 20px;
 border-bottom: 1px solid #e0e0e0;
 margin-bottom: 20px;
}

.user-info i {
 font-size: 60px;
 color: #0066cc;
 margin-bottom: 10px;
}

.dashboard-menu {
 list-style: none;
 padding: 0;
 margin: 0;
}

.dashboard-menu li {
 margin-bottom: 10px;
 position: relative;
}

.dashboard-menu a {
 display: flex;
 align-items: center;
 padding: 12px 15px;
 color: #333;
 text-decoration: none;
 border-radius: 10px;
 transition: all 0.3s;
 position: relative;
}

.dashboard-menu a:hover {
 background: #f0f7ff;
 color: #0066cc;
}

.dashboard-menu a.active {
 background: #0066cc;
 color: white;
}

.dashboard-menu a i {
 width: 25px;
 margin-right: 10px;
}

.menu-badge {
 position: absolute;
 right: 15px;
 top: 50%;
 transform: translateY(-50%);
 background: #dc3545;
 color: white;
 border-radius: 20px;
 padding: 2px 8px;
 font-size: 11px;
 font-weight: bold;
}

.stat-card {
 background: white;
 border-radius: 15px;
 padding: 20px;
 text-align: center;
 box-shadow: 0 2px 10px rgba(0,0,0,0.1);
 transition: transform 0.3s;
}

.stat-card:hover {
 transform: translateY(-5px);
}

.stat-card i {
 font-size: 40px;
 color: #0066cc;
 margin-bottom: 10px;
}

.stat-card h3 {
 font-size: 28px;
 font-weight: bold;
 margin: 10px 0;
 color: #333;
}

.appointment-card {
 background: #f8f9fa;
 border-radius: 12px;
 padding: 20px;
 margin-bottom: 15px;
 border-left: 4px solid #0066cc;
 transition: all 0.3s;
}

.appointment-card:hover {
 transform: translateX(5px);
 box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.status {
 display: inline-block;
 padding: 5px 12px;
 border-radius: 20px;
 font-size: 12px;
 font-weight: 500;
}

.status-confirme {
 background: #d4edda;
 color: #155724;
}

.status-en_attente {
 background: #fff3cd;
 color: #856404;
}

.status-termine {
 background: #d1ecf1;
 color: #0c5460;
}

.status-annule {
 background: #f8d7da;
 color: #721c24;
}

.notification-item {
 padding: 15px;
 border-bottom: 1px solid #e0e0e0;
 cursor: pointer;
 transition: background 0.2s;
 border-radius: 8px;
 margin-bottom: 5px;
}

.notification-item:hover {
 background: #f8f9fa;
}

.notification-item.non-lue {
 background: #f0f7ff;
 border-left: 3px solid #0066cc;
}

.notification-time {
 font-size: 11px;
 color: #999;
}

.badge-new {
 background: #dc3545;
 color: white;
 padding: 3px 8px;
 border-radius: 12px;
 font-size: 10px;
 margin-left: 5px;
}

.table th {
 background: #f8f9fa;
}

.btn-sm {
 padding: 5px 12px;
 font-size: 12px;
}
</style>

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
 <li><a href="#" data-page="appointments">
 <i class="fas fa-calendar-alt"></i> Mes rendez-vous
 <?php 
 $total_rdv_encours = (int)($rdv_attente_count['nb_attente'] ?? 0) + (int)($rdv_confirmes_count['nb_confirmes'] ?? 0);
 if($total_rdv_encours > 0): 
 ?>
 <span class="menu-badge"><?php echo $total_rdv_encours; ?></span>
 <?php endif; ?>
 </a></li>
 <li><a href="#" data-page="history"><i class="fas fa-history"></i> Historique</a></li>
 <li><a href="#" data-page="profile"><i class="fas fa-user"></i> Mon profil</a></li>
 <li><a href="#" data-page="notifications">
 <i class="fas fa-bell"></i> Notifications 
 <?php if((int)($notifications['non_lues'] ?? 0) > 0): ?>
 <span class="menu-badge"><?php echo (int)($notifications['non_lues'] ?? 0); ?></span>
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
 <h3><?php echo (int)($stats['total_rdv'] ?? 0); ?></h3>
 <p>Total RDV</p>
 </div>
 </div>
 <div class="col-md-3">
 <div class="stat-card">
 <i class="fas fa-check-circle"></i>
 <h3><?php echo (int)($stats['rdv_confirms'] ?? 0); ?></h3>
 <p>Confirmés</p>
 </div>
 </div>
 <div class="col-md-3">
 <div class="stat-card">
 <i class="fas fa-clock"></i>
 <h3><?php echo (int)($stats['rdv_attente'] ?? 0); ?></h3>
 <p>En attente</p>
 </div>
 </div>
 <div class="col-md-3">
 <div class="stat-card">
 <i class="fas fa-check-double"></i>
 <h3><?php echo (int)($stats['rdv_termines'] ?? 0); ?></h3>
 <p>Terminés</p>
 </div>
 </div>
 </div>
 
 <div class="dashboard-sidebar mb-4">
 <h4 class="mb-3">
 <i class="fas fa-calendar-alt"></i> Prochains rendez-vous
 <?php if($total_rdv_encours > 0): ?>
 <span class="badge bg-primary"><?php echo $total_rdv_encours; ?> à venir</span>
 <?php endif; ?>
 </h4>
 <?php if(empty($prochains_rdv)): ?>
 <div class="alert alert-info">
 <i class="fas fa-info-circle"></i> Aucun rendez-vous à venir.
 <a href="specialites.php" class="alert-link">Prendre un rendez-vous</a>
 </div>
 <?php else: ?>
 <?php foreach($prochains_rdv as $rdv): ?>
 <div class="appointment-card">
 <div class="row align-items-center">
 <div class="col-md-7">
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
 <div class="col-md-5 text-md-end">
 <span class="status status-<?php echo $rdv['statut']; ?>">
 <?php 
 switch($rdv['statut']) {
 case 'confirme': echo '✓ Confirmé'; break;
 case 'en_attente': echo '⏳ En attente'; break;
 case 'termine': echo '✔ Terminé'; break;
 case 'annule': echo '✗ Annulé'; break;
 }
 ?>
 </span>
 <div class="mt-2">
 <button class="btn btn-sm btn-outline-primary" onclick="voirDetailsRendezVous(<?php echo (int)$rdv['id']; ?>)">
 <i class="fas fa-info-circle"></i> Détails
 </button>
 <?php if($rdv['statut'] != 'annule'): ?>
 <button class="btn btn-sm btn-outline-danger" onclick="annulerRendezVous(<?php echo (int)$rdv['id']; ?>)">
 <i class="fas fa-times"></i> Annuler
 </button>
 <?php endif; ?>
 </div>
 </div>
 </div>
 </div>
 <?php endforeach; ?>
 <?php endif; ?>
 </div>
 </div>
 
 <!-- Section Mes rendez-vous (liste complète) -->
 <div id="appointmentsSection" style="display: none;">
 <div class="dashboard-sidebar">
 <h4 class="mb-3">
 <i class="fas fa-calendar-alt"></i> Tous mes rendez-vous
 <?php if($total_rdv_encours > 0): ?>
 <span class="badge bg-primary"><?php echo $total_rdv_encours; ?> actifs</span>
 <?php endif; ?>
 </h4>
 
 <!-- Onglets pour filtrer -->
 <ul class="nav nav-tabs mb-3" id="appointmentTab" role="tablist">
 <li class="nav-item">
 <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab">
 Tous <span class="badge bg-secondary"><?php echo count($prochains_rdv); ?></span>
 </button>
 </li>
 <li class="nav-item">
 <button class="nav-link" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab">
 En attente 
 <?php if((int)($rdv_attente_count['nb_attente'] ?? 0) > 0): ?>
 <span class="badge bg-warning"><?php echo (int)($rdv_attente_count['nb_attente'] ?? 0); ?></span>
 <?php endif; ?>
 </button>
 </li>
 <li class="nav-item">
 <button class="nav-link" data-bs-toggle="tab" data-bs-target="#confirmed" type="button" role="tab">
 Confirmés
 <?php if((int)($rdv_confirmes_count['nb_confirmes'] ?? 0) > 0): ?>
 <span class="badge bg-success"><?php echo (int)($rdv_confirmes_count['nb_confirmes'] ?? 0); ?></span>
 <?php endif; ?>
 </button>
 </li>
 </ul>
 
 <div class="tab-content">
 <div class="tab-pane fade show active" id="all" role="tabpanel">
 <?php if(empty($prochains_rdv)): ?>
 <div class="alert alert-info">Aucun rendez-vous programmé</div>
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
 </div>
 <div class="col-md-4 text-md-end">
 <span class="status status-<?php echo $rdv['statut']; ?>">
 <?php echo $rdv['statut'] == 'confirme' ? 'Confirmé' : 'En attente'; ?>
 </span>
 </div>
 </div>
 </div>
 <?php endforeach; ?>
 <?php endif; ?>
 </div>
 
 <div class="tab-pane fade" id="pending" role="tabpanel">
 <?php 
 $pending_rdv = array_filter($prochains_rdv, function($rdv) {
 return $rdv['statut'] == 'en_attente';
 });
 if(empty($pending_rdv)): ?>
 <div class="alert alert-info">Aucun rendez-vous en attente</div>
 <?php else: ?>
 <?php foreach($pending_rdv as $rdv): ?>
 <div class="appointment-card">
 <div class="row align-items-center">
 <div class="col-md-8">
 <h5><?php echo htmlspecialchars($rdv['medecin_nom']); ?></h5>
 <p class="text-primary mb-1"><?php echo htmlspecialchars($rdv['specialite_nom']); ?></p>
 <p class="small text-secondary">
 <i class="fas fa-calendar-day"></i> <?php echo date('d/m/Y H:i', strtotime($rdv['date_rendez_vous'])); ?>
 </p>
 </div>
 <div class="col-md-4 text-md-end">
 <span class="status status-en_attente">⏳ En attente de confirmation</span>
 <div class="mt-2">
 <button class="btn btn-sm btn-outline-danger" onclick="annulerRendezVous(<?php echo (int)$rdv['id']; ?>)">
 <i class="fas fa-times"></i> Annuler
 </button>
 </div>
 </div>
 </div>
 </div>
 <?php endforeach; ?>
 <?php endif; ?>
 </div>
 
 <div class="tab-pane fade" id="confirmed" role="tabpanel">
 <?php 
 $confirmed_rdv = array_filter($prochains_rdv, function($rdv) {
 return $rdv['statut'] == 'confirme';
 });
 if(empty($confirmed_rdv)): ?>
 <div class="alert alert-info">Aucun rendez-vous confirmé</div>
 <?php else: ?>
 <?php foreach($confirmed_rdv as $rdv): ?>
 <div class="appointment-card">
 <div class="row align-items-center">
 <div class="col-md-8">
 <h5><?php echo htmlspecialchars($rdv['medecin_nom']); ?></h5>
 <p class="text-primary mb-1"><?php echo htmlspecialchars($rdv['specialite_nom']); ?></p>
 <p class="small text-secondary">
 <i class="fas fa-calendar-day"></i> <?php echo date('d/m/Y H:i', strtotime($rdv['date_rendez_vous'])); ?>
 </p>
 </div>
 <div class="col-md-4 text-md-end">
 <span class="status status-confirme">✓ Confirmé</span>
 </div>
 </div>
 </div>
 <?php endforeach; ?>
 <?php endif; ?>
 </div>
 </div>
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
 <table class="table table-hover">
 <thead class="table-light">
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
 case 'confirme': echo '✓ Confirmé'; break;
 case 'en_attente': echo '⏳ En attente'; break;
 case 'termine': echo '✔ Terminé'; break;
 case 'annule': echo '✗ Annulé'; break;
 }
 ?>
 </span>
 </td>
 <td>
 <button class="btn btn-sm btn-outline-info" onclick="voirDetails(<?php echo (int)$rdv['id']; ?>)">
 <i class="fas fa-eye"></i> Détails
 </button>
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
 
 <!-- Section Notifications -->
 <div id="notificationsSection" style="display: none;">
 <div class="dashboard-sidebar">
 <h4 class="mb-3">
 <i class="fas fa-bell"></i> Mes notifications
 <?php if((int)($notifications['non_lues'] ?? 0) > 0): ?>
 <span class="badge bg-danger"><?php echo (int)($notifications['non_lues'] ?? 0); ?> non lues</span>
 <?php endif; ?>
 </h4>
 <?php if(empty($liste_notifications)): ?>
 <div class="alert alert-info">
 <i class="fas fa-info-circle"></i> Aucune notification
 </div>
 <?php else: ?>
 <div class="notifications-list">
 <?php foreach($liste_notifications as $notif): ?>
 <div class="notification-item <?php echo !$notif['est_lu'] ? 'non-lue' : ''; ?>">
 <div class="d-flex justify-content-between align-items-start">
 <div class="flex-grow-1">
 <p class="mb-1"><?php echo htmlspecialchars($notif['message']); ?></p>
 <small class="notification-time">
 <i class="far fa-clock"></i> <?php echo date('d/m/Y H:i', strtotime($notif['date_creation'])); ?>
 </small>
 </div>
 <?php if(!$notif['est_lu']): ?>
 <span class="badge-new">Nouveau</span>
 <?php endif; ?>
 </div>
 </div>
 <?php endforeach; ?>
 </div>
 <?php if((int)($notifications['non_lues'] ?? 0) > 0): ?>
 <div class="mt-3 text-center">
 <button class="btn btn-sm btn-primary" onclick="marquerToutLu()">
 <i class="fas fa-check-double"></i> Tout marquer comme lu
 </button>
 </div>
 <?php endif; ?>
 <?php endif; ?>
 </div>
 </div>
 </div>
 </div>
 </div>
</div>

<script>
function annulerRendezVous(id) {
 if(confirm('Voulez-vous vraiment annuler ce rendez-vous ?')) {
 // Simulation d'annulation - à remplacer par appel AJAX
 alert('Rendez-vous annulé avec succès');
 location.reload();
 }
}

function voirDetails(id) {
 alert('Affichage des détails du rendez-vous #' + id);
}

function voirDetailsRendezVous(id) {
 alert('Détails complets du rendez-vous #' + id);
}

function marquerToutLu() {
 alert('Toutes les notifications ont été marquées comme lues');
 location.reload();
}

// Navigation dans le dashboard
document.querySelectorAll('.dashboard-menu a').forEach(link => {
 link.addEventListener('click', function(e) {
 e.preventDefault();
 const page = this.dataset.page;
 
 // Cacher toutes les sections
 document.getElementById('dashboardSection').style.display = 'none';
 document.getElementById('appointmentsSection').style.display = 'none';
 document.getElementById('historySection').style.display = 'none';
 document.getElementById('profileSection').style.display = 'none';
 document.getElementById('notificationsSection').style.display = 'none';
 
 // Afficher la section correspondante
 if(page === 'dashboard') {
 document.getElementById('dashboardSection').style.display = 'block';
 } else if(page === 'appointments') {
 document.getElementById('appointmentsSection').style.display = 'block';
 } else if(page === 'history') {
 document.getElementById('historySection').style.display = 'block';
 } else if(page === 'profile') {
 document.getElementById('profileSection').style.display = 'block';
 } else if(page === 'notifications') {
 document.getElementById('notificationsSection').style.display = 'block';
 }
 
 // Mettre à jour la classe active
 document.querySelectorAll('.dashboard-menu a').forEach(a => a.classList.remove('active'));
 this.classList.add('active');
 });
});

// Rafraîchir les badges périodiquement (optionnel)
setInterval(function() {
 // Ajouter ici un appel AJAX pour mettre à jour les compteurs sans recharger la page
 console.log('Mise à jour des notifications...');
}, 30000); // Toutes les 30 secondes
</script>

<?php include 'includes/footer.php'; ?>