<?php
// Fichier: dashboard-medecin.php
require_once 'includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'medecin') {
    header('Location: connexion.php');
    exit();
}

include 'includes/header.php';

// Données simulées pour le médecin
$medecin = [
    'nom_complet' => $_SESSION['user_nom'],
    'specialite_nom' => 'Cardiologie',
    'total_rdv' => 45,
    'rdv_confirms' => 30,
    'rdv_attente' => 5,
    'rdv_termines' => 10
];
?>

<div class="dashboard-wrapper">
    <div class="container">
        <div class="row">
            <div class="col-lg-3" data-aos="fade-right">
                <div class="dashboard-sidebar">
                    <div class="user-info">
                        <i class="fas fa-user-md"></i>
                        <h5>Dr <?php echo htmlspecialchars($medecin['nom_complet']); ?></h5>
                        <p class="text-primary"><?php echo $medecin['specialite_nom']; ?></p>
                        <p class="small text-secondary">Médecin</p>
                    </div>
                    <ul class="dashboard-menu">
                        <li><a href="#" class="active" data-page="dashboard"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a></li>
                        <li><a href="#" data-page="appointments"><i class="fas fa-calendar-alt"></i> Mes rendez-vous</a></li>
                        <li><a href="#" data-page="schedule"><i class="fas fa-clock"></i> Mes horaires</a></li>
                        <li><a href="#" data-page="patients"><i class="fas fa-users"></i> Mes patients</a></li>
                        <li><a href="#" data-page="stats"><i class="fas fa-chart-line"></i> Statistiques</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="col-lg-9" data-aos="fade-left">
                <!-- Section Tableau de bord -->
                <div id="dashboardSection">
                    <div class="dashboard-sidebar mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3>Bonjour, Dr <?php echo htmlspecialchars($medecin['nom_complet']); ?></h3>
                                <p class="text-secondary mb-0">Voici le résumé de votre activité</p>
                            </div>
                            <div>
                                <span class="status status-confirme">
                                    <i class="fas fa-check-circle"></i> Disponible
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row g-4 mb-4">
                        <div class="col-md-3">
                            <div class="stat-card">
                                <i class="fas fa-calendar-check"></i>
                                <h3><?php echo $medecin['total_rdv']; ?></h3>
                                <p>Total consultations</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card">
                                <i class="fas fa-check-circle"></i>
                                <h3><?php echo $medecin['rdv_confirms']; ?></h3>
                                <p>Confirmés</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card">
                                <i class="fas fa-clock"></i>
                                <h3><?php echo $medecin['rdv_attente']; ?></h3>
                                <p>En attente</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card">
                                <i class="fas fa-check-double"></i>
                                <h3><?php echo $medecin['rdv_termines']; ?></h3>
                                <p>Terminés</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="dashboard-sidebar mb-4">
                        <h4 class="mb-3"><i class="fas fa-calendar-day"></i> Rendez-vous du jour</h4>
                        <div class="appointment-card">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h5>Marie Dupont</h5>
                                    <p class="small text-secondary mb-1"><i class="fas fa-clock"></i> 10:30</p>
                                    <p class="small text-secondary"><i class="fas fa-phone-alt"></i> 77 111 22 33</p>
                                </div>
                                <div class="col-md-4 text-md-end">
                                    <span class="status status-confirme">Confirmé</span>
                                    <div class="mt-2">
                                        <button class="btn btn-sm btn-success" onclick="demarrerConsultation(1)">
                                            <i class="fas fa-play"></i> Démarrer
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Section Mes rendez-vous -->
                <div id="appointmentsSection" style="display: none;">
                    <div class="dashboard-sidebar">
                        <h4 class="mb-3"><i class="fas fa-calendar-alt"></i> Mes rendez-vous</h4>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Patient</th>
                                        <th>Date</th>
                                        <th>Heure</th>
                                        <th>Statut</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Marie Dupont</td>
                                        <td>15/12/2024</td>
                                        <td>10:30</td>
                                        <td><span class="status status-confirme">Confirmé</span></td>
                                        <td><button class="btn btn-sm btn-primary" onclick="voirPatient(1)"><i class="fas fa-eye"></i></button></td>
                                    </tr>
                                    <tr>
                                        <td>Jean Mendy</td>
                                        <td>16/12/2024</td>
                                        <td>14:00</td>
                                        <td><span class="status status-en_attente">En attente</span></td>
                                        <td><button class="btn btn-sm btn-primary" onclick="voirPatient(2)"><i class="fas fa-eye"></i></button></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Section Mes horaires -->
                <div id="scheduleSection" style="display: none;">
                    <div class="dashboard-sidebar">
                        <h4 class="mb-3"><i class="fas fa-clock"></i> Mes horaires</h4>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Jour</th>
                                        <th>Matin</th>
                                        <th>Après-midi</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td>Lundi</td><td>08:30 - 12:30</td><td>14:00 - 17:00</td><td><button class="btn btn-sm btn-primary">Modifier</button></td></tr>
                                    <tr><td>Mardi</td><td>08:30 - 12:30</td><td>14:00 - 17:00</td><td><button class="btn btn-sm btn-primary">Modifier</button></td></tr>
                                    <tr><td>Mercredi</td><td>08:30 - 12:30</td><td>14:00 - 17:00</td><td><button class="btn btn-sm btn-primary">Modifier</button></td></tr>
                                    <tr><td>Jeudi</td><td>08:30 - 12:30</td><td>14:00 - 17:00</td><td><button class="btn btn-sm btn-primary">Modifier</button></td></tr>
                                    <tr><td>Vendredi</td><td>08:30 - 12:30</td><td>14:00 - 17:00</td><td><button class="btn btn-sm btn-primary">Modifier</button></td></tr>
                                    <tr><td>Samedi</td><td>09:00 - 13:00</td><td>Fermé</td><td><button class="btn btn-sm btn-primary">Modifier</button></td></tr>
                                    <tr><td>Dimanche</td><td colspan="2">Fermé</td><td><button class="btn btn-sm btn-primary">Modifier</button></td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Initialiser la navigation du dashboard
document.querySelectorAll('.dashboard-menu a').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        const page = this.dataset.page;
        
        document.getElementById('dashboardSection').style.display = 'none';
        document.getElementById('appointmentsSection').style.display = 'none';
        document.getElementById('scheduleSection').style.display = 'none';
        document.getElementById('patientsSection').style.display = 'none';
        document.getElementById('statsSection').style.display = 'none';
        
        if (page === 'dashboard') document.getElementById('dashboardSection').style.display = 'block';
        else if (page === 'appointments') document.getElementById('appointmentsSection').style.display = 'block';
        else if (page === 'schedule') document.getElementById('scheduleSection').style.display = 'block';
        
        document.querySelectorAll('.dashboard-menu a').forEach(a => a.classList.remove('active'));
        this.classList.add('active');
    });
});
</script>

<?php include 'includes/footer.php'; ?>