<?php
// ==============================
// Fichier : prendre-rendez-vous-etape2.php
// Étape 2: Sélection du médecin
// ==============================

require_once 'includes/config.php';
require_once 'includes/fonctions.php';

$pageTitle = 'Choisir un Médecin';
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

// Vérifier si la spécialité a été sélectionnée
if (!isset($_SESSION['rendez_vous_data']['specialite_id']) || empty($_SESSION['rendez_vous_data']['specialite_id'])) {
    header('Location: ' . SITE_URL . 'prendre-rendez-vous-etape1.php');
    exit();
}

$specialite_id = $_SESSION['rendez_vous_data']['specialite_id'];

// Récupérer les médecins de cette spécialité
$medecins = getMedecinsBySpecialite($pdo, $specialite_id);

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $medecin_id = isset($_POST['medecin_id']) ? intval($_POST['medecin_id']) : 0;
    
    if ($medecin_id === 0) {
        $message = 'Veuillez choisir un médecin.';
        $message_type = 'danger';
    } else {
        // Stocker dans la session
        $_SESSION['rendez_vous_data']['medecin_id'] = $medecin_id;
        
        // Rediriger vers l'étape 3
        header('Location: ' . SITE_URL . 'prendre-rendez-vous-etape3.php');
        exit();
    }
}
?>

<style>
    .medecin-container {
        max-width: 1000px;
        margin: 40px auto;
        padding: 30px;
    }

    .medecin-card {
        border: 2px solid #ecf0f1;
        border-radius: 15px;
        padding: 25px;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 20px;
        height: 100%;
    }

    .medecin-card:hover {
        border-color: #3498db;
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(52, 152, 219, 0.15);
    }

    .medecin-photo {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #3498db;
    }

    .medecin-info h4 {
        color: #2c3e50;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .medecin-info p {
        color: #7f8c8d;
        margin: 4px 0;
        font-size: 0.95em;
    }

    .medecin-info .badge {
        background: #3498db;
        color: white;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.85em;
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
        width: 33.33%;
    }

    .btn-back {
        margin-bottom: 20px;
    }
</style>

<div class="medecin-container" data-aos="fade-up">
    <div class="page-header">
        <h1><i class="fas fa-user-md"></i> Choisissez un Médecin</h1>
        <p class="text-muted">Étape 2/5 - Sélectionnez le médecin de votre choix</p>
    </div>

    <div class="progress-bar-custom">
        <div class="progress-fill"></div>
    </div>

    <a href="<?php echo SITE_URL; ?>prendre-rendez-vous-etape1.php" class="btn btn-outline-secondary btn-back">
        <i class="fas fa-arrow-left"></i> Retour
    </a>

    <?php if (!empty($message)): ?>
    <div class="alert alert-<?php echo $message_type; ?>">
        <i class="fas <?php echo $message_type == 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> me-2"></i>
        <?php echo $message; ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="row">
            <?php foreach ($medecins as $medecin): ?>
            <div class="col-md-6 mb-4">
                <div class="medecin-card" onclick="selectMedecin(<?php echo $medecin['id']; ?>)">
                    <?php 
                    $photo_path = 'assets/images/medecins/' . $medecin['photo'];
                    if (!empty($medecin['photo']) && file_exists($photo_path)) {
                        echo '<img src="' . SITE_URL . $photo_path . '" alt="' . htmlspecialchars($medecin['nom_complet']) . '" class="medecin-photo">';
                    } else {
                        echo '<div class="medecin-photo" style="background: linear-gradient(135deg, #3498db, #2980b9); display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5em; font-weight: 700;">' . substr($medecin['nom_complet'], 0, 2) . '</div>';
                    }
                    ?>
                    <div class="medecin-info">
                        <h4><?php echo htmlspecialchars($medecin['nom_complet']); ?></h4>
                        <p><span class="badge"><?php echo htmlspecialchars($medecin['specialite_nom']); ?></span></p>
                        <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($medecin['ville']); ?></p>
                        <p><i class="fas fa-clock"></i> <?php echo $medecin['annees_experience']; ?> ans d'expérience</p>
                        <p><i class="fas fa-money-bill-wave"></i> <?php echo number_format($medecin['tarif_consultation'], 0, ',', ' '); ?> FCFA</p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <input type="hidden" name="medecin_id" id="medecin_id" value="">
    </form>
</div>

<script>
function selectMedecin(id) {
    document.getElementById('medecin_id').value = id;
    document.querySelector('form').submit();
}
</script>

<?php
include 'includes/footer.php';
?>
