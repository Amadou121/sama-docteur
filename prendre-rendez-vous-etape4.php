<?php
// ==============================
// Fichier : prendre-rendez-vous-etape4.php
// Étape 4: Sélection de l'heure
// ==============================

require_once 'includes/config.php';
require_once 'includes/fonctions.php';

$pageTitle = 'Choisir une Heure';
include 'includes/header.php';

// Vérifier si l'utilisateur est connecté
if (!estConnecte()) {
    $_SESSION['flash'] = [
        'type' => 'warning',
        'message' => 'Vous devez être connecté pour prendre un rendez-vous.'
    ];
    header('Location: ' . SITE_URL . 'connexion.php');
    exit();
}

// Vérifier si la date a été sélectionnée
if (!isset($_SESSION['rendez_vous_data']['date_rendez_vous']) || empty($_SESSION['rendez_vous_data']['date_rendez_vous'])) {
    header('Location: ' . SITE_URL . 'prendre-rendez-vous-etape3.php');
    exit();
}

$medecin_id = $_SESSION['rendez_vous_data']['medecin_id'];
$date_rendez_vous = $_SESSION['rendez_vous_data']['date_rendez_vous'];

// Récupérer les horaires du médecin
$horaires = getHorairesMedecin($pdo, $medecin_id);

// Générer les créneaux disponibles
$creneaux_disponibles = [];
$jour_semaine = date('l', strtotime($date_rendez_vous));

// Traduire le jour en français
$jours_fr = [
    'Monday' => 'Lundi',
    'Tuesday' => 'Mardi',
    'Wednesday' => 'Mercredi',
    'Thursday' => 'Jeudi',
    'Friday' => 'Vendredi',
    'Saturday' => 'Samedi',
    'Sunday' => 'Dimanche'
];
$jour_fr = $jours_fr[$jour_semaine] ?? $jour_semaine;

// Trouver l'horaire correspondant
foreach ($horaires as $horaire) {
    if ($horaire['jour_semaine'] === $jour_fr && $horaire['est_disponible']) {
        $heure_debut = strtotime($horaire['heure_debut']);
        $heure_fin = strtotime($horaire['heure_fin']);
        $pause_debut = strtotime($horaire['pause_debut']);
        $pause_fin = strtotime($horaire['pause_fin']);
        $duree = $horaire['duree_consultation'];
        
        // Générer les créneaux
        $current_time = $heure_debut;
        while ($current_time < $heure_fin) {
            // Vérifier si on est dans la pause
            if ($current_time >= $pause_debut && $current_time < $pause_fin) {
                $current_time = $pause_fin;
                continue;
            }
            
            $creneau = date('H:i', $current_time);
            $creneau_datetime = $date_rendez_vous . ' ' . $creneau . ':00';
            
            // Vérifier si le créneau est déjà pris
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as count 
                FROM rendez_vous 
                WHERE medecin_id = ? AND date_rendez_vous = ? 
                AND statut NOT IN ('annule', 'termine')
            ");
            $stmt->execute([$medecin_id, $creneau_datetime]);
            $result = $stmt->fetch();
            
            if ($result['count'] == 0) {
                $creneaux_disponibles[] = $creneau;
            }
            
            $current_time += $duree * 60;
        }
        break;
    }
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $heure_rendez_vous = isset($_POST['heure_rendez_vous']) ? $_POST['heure_rendez_vous'] : '';
    
    if (empty($heure_rendez_vous)) {
        $message = 'Veuillez choisir un créneau horaire.';
        $message_type = 'danger';
    } else {
        // Stocker dans la session
        $_SESSION['rendez_vous_data']['heure_rendez_vous'] = $heure_rendez_vous;
        
        // Rediriger vers l'étape 5
        header('Location: ' . SITE_URL . 'prendre-rendez-vous-etape5.php');
        exit();
    }
}
?>

<style>
    .heure-container {
        max-width: 800px;
        margin: 40px auto;
        padding: 30px;
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        border-radius: 20px;
    }

    .heure-card {
        background: white;
        border-radius: 15px;
        padding: 40px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }

    .page-header {
        text-align: center;
        margin-bottom: 40px;
    }

    .page-header h1 {
        color: #2c3e50;
        font-weight: 700;
        margin-bottom: 10px;
    }

    .progress-bar-custom {
        background: #ecf0f1;
        border-radius: 10px;
        height: 8px;
        margin-bottom: 30px;
    }

    .progress-fill {
        background: linear-gradient(90deg, #007bff, #0056b3);
        height: 100%;
        border-radius: 10px;
        width: 66.66%;
    }

    .btn-back {
        margin-bottom: 20px;
    }

    .creneaux-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
        gap: 15px;
        margin-top: 20px;
    }

    .creneau-btn {
        padding: 20px;
        border: 2px solid #ecf0f1;
        border-radius: 10px;
        background: white;
        color: #2c3e50;
        font-weight: 600;
        font-size: 1.2em;
        cursor: pointer;
        transition: all 0.3s ease;
        text-align: center;
    }

    .creneau-btn:hover {
        border-color: #007bff;
        background: #f8fbff;
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0, 123, 255, 0.2);
    }

    .date-info {
        background: #f8fbff;
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 30px;
        text-align: center;
    }
</style>

<div class="heure-container" data-aos="fade-up">
    <div class="page-header">
        <h1><i class="fas fa-clock"></i> Choisissez un Créneau</h1>
        <p class="text-muted">Étape 4/5 - Sélectionnez l'heure de votre rendez-vous</p>
    </div>

    <div class="progress-bar-custom">
        <div class="progress-fill"></div>
    </div>

    <a href="<?php echo SITE_URL; ?>prendre-rendez-vous-etape3.php" class="btn btn-outline-secondary btn-back">
        <i class="fas fa-arrow-left"></i> Retour
    </a>

    <?php if (!empty($message)): ?>
    <div class="alert alert-<?php echo $message_type; ?>">
        <i class="fas <?php echo $message_type == 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> me-2"></i>
        <?php echo $message; ?>
    </div>
    <?php endif; ?>

    <div class="date-info">
        <h5><i class="fas fa-calendar-alt"></i> Date sélectionnée</h5>
        <p class="mb-0 fw-bold"><?php echo date('d/m/Y', strtotime($date_rendez_vous)); ?></p>
    </div>

    <div class="heure-card">
        <form method="POST" action="">
            <h5 class="mb-3">Créneaux disponibles</h5>
            
            <?php if (!empty($creneaux_disponibles)): ?>
            <div class="creneaux-grid">
                <?php foreach ($creneaux_disponibles as $creneau): ?>
                <button type="button" class="creneau-btn" onclick="selectHeure('<?php echo $creneau; ?>')">
                    <?php echo $creneau; ?>
                </button>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> Aucun créneau disponible pour cette date. Veuillez choisir une autre date.
            </div>
            <?php endif; ?>
            
            <input type="hidden" name="heure_rendez_vous" id="heure_rendez_vous" value="">
        </form>
    </div>
</div>

<script>
function selectHeure(heure) {
    document.getElementById('heure_rendez_vous').value = heure;
    document.querySelector('form').submit();
}
</script>

<?php
include 'includes/footer.php';
?>
