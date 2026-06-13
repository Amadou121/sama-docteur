<?php
// ==============================
// Fichier : prendre-rendez-vous-etape3.php
// Étape 3: Sélection de la date
// ==============================

require_once 'includes/config.php';
require_once 'includes/fonctions.php';

$pageTitle = 'Choisir une Date';
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

// Vérifier si le médecin a été sélectionné
if (!isset($_SESSION['rendez_vous_data']['medecin_id']) || empty($_SESSION['rendez_vous_data']['medecin_id'])) {
    header('Location: ' . SITE_URL . 'prendre-rendez-vous-etape2.php');
    exit();
}

$medecin_id = $_SESSION['rendez_vous_data']['medecin_id'];

// Récupérer les informations du médecin
$medecins = getMedecinsBySpecialite($pdo, $_SESSION['rendez_vous_data']['specialite_id']);
$medecin = null;
foreach ($medecins as $m) {
    if ($m['id'] == $medecin_id) {
        $medecin = $m;
        break;
    }
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date_rendez_vous = isset($_POST['date_rendez_vous']) ? $_POST['date_rendez_vous'] : '';
    
    if (empty($date_rendez_vous)) {
        $message = 'Veuillez choisir une date.';
        $message_type = 'danger';
    } else {
        // Vérifier si la date est dans le futur
        if (strtotime($date_rendez_vous) <= time()) {
            $message = 'La date du rendez-vous doit être dans le futur.';
            $message_type = 'danger';
        } else {
            // Stocker dans la session
            $_SESSION['rendez_vous_data']['date_rendez_vous'] = $date_rendez_vous;
            
            // Rediriger vers l'étape 4
            header('Location: ' . SITE_URL . 'prendre-rendez-vous-etape4.php');
            exit();
        }
    }
}
?>

<style>
    .date-container {
        max-width: 600px;
        margin: 40px auto;
        padding: 30px;
    }

    .date-card {
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
        background: linear-gradient(90deg, #3498db, #2980b9);
        height: 100%;
        border-radius: 10px;
        width: 50%;
    }

    .btn-back {
        margin-bottom: 20px;
    }

    .form-control-lg {
        padding: 15px;
        font-size: 1.1em;
        border-radius: 10px;
    }

    .medecin-info {
        background: #f8fbff;
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 30px;
    }
</style>

<div class="date-container" data-aos="fade-up">
    <div class="page-header">
        <h1><i class="fas fa-calendar-alt"></i> Choisissez une Date</h1>
        <p class="text-muted">Étape 3/5 - Sélectionnez la date de votre rendez-vous</p>
    </div>

    <div class="progress-bar-custom">
        <div class="progress-fill"></div>
    </div>

    <a href="<?php echo SITE_URL; ?>prendre-rendez-vous-etape2.php" class="btn btn-outline-secondary btn-back">
        <i class="fas fa-arrow-left"></i> Retour
    </a>

    <?php if (!empty($message)): ?>
    <div class="alert alert-<?php echo $message_type; ?>">
        <i class="fas <?php echo $message_type == 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> me-2"></i>
        <?php echo $message; ?>
    </div>
    <?php endif; ?>

    <?php if ($medecin): ?>
    <div class="medecin-info">
        <h5><i class="fas fa-user-md"></i> Médecin sélectionné</h5>
        <p><strong><?php echo htmlspecialchars($medecin['nom_complet']); ?></strong></p>
        <p class="text-muted mb-0"><?php echo htmlspecialchars($medecin['specialite_nom']); ?></p>
    </div>
    <?php endif; ?>

    <div class="date-card">
        <form method="POST" action="">
            <div class="mb-4">
                <label for="date_rendez_vous" class="form-label fw-bold">Date du rendez-vous</label>
                <input type="date" 
                       class="form-control form-control-lg" 
                       id="date_rendez_vous" 
                       name="date_rendez_vous" 
                       value="<?php echo isset($_POST['date_rendez_vous']) ? htmlspecialchars($_POST['date_rendez_vous']) : ''; ?>"
                       min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>"
                       required>
                <small class="text-muted">Les rendez-vous doivent être pris au moins 24h à l'avance</small>
            </div>

            <button type="submit" class="btn btn-primary btn-lg w-100">
                <i class="fas fa-arrow-right"></i> Continuer
            </button>
        </form>
    </div>
</div>

<?php
include 'includes/footer.php';
?>
