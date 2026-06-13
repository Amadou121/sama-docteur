<?php
// ==============================
// Fichier : prendre-rendez-vous-etape1.php
// Étape 1: Sélection de la spécialité
// ==============================

require_once 'includes/config.php';
require_once 'includes/fonctions.php';

$pageTitle = 'Choisir une Spécialité';
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

// Réinitialiser les données de session pour ce formulaire
unset($_SESSION['rendez_vous_data']);

// Récupérer les spécialités
$specialites = getSpecialites($pdo);

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $specialite_id = isset($_POST['specialite_id']) ? intval($_POST['specialite_id']) : 0;
    
    if ($specialite_id === 0) {
        $message = 'Veuillez choisir une spécialité.';
        $message_type = 'danger';
    } else {
        // Stocker dans la session
        $_SESSION['rendez_vous_data']['specialite_id'] = $specialite_id;
        
        // Rediriger vers l'étape 2
        header('Location: ' . SITE_URL . 'prendre-rendez-vous-etape2.php');
        exit();
    }
}
?>

<style>
    .specialite-container {
        max-width: 1000px;
        margin: 40px auto;
        padding: 30px;
    }

    .specialite-card {
        border: 2px solid #ecf0f1;
        border-radius: 15px;
        padding: 30px;
        cursor: pointer;
        transition: all 0.3s ease;
        text-align: center;
        height: 100%;
    }

    .specialite-card:hover {
        border-color: #3498db;
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(52, 152, 219, 0.2);
    }

    .specialite-card i {
        font-size: 3em;
        color: #3498db;
        margin-bottom: 15px;
    }

    .specialite-card h4 {
        color: #2c3e50;
        font-weight: 600;
        margin-bottom: 10px;
    }

    .specialite-card p {
        color: #7f8c8d;
        font-size: 0.9em;
        margin: 0;
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
        width: 16.66%;
    }
</style>

<div class="specialite-container" data-aos="fade-up">
    <div class="page-header">
        <h1><i class="fas fa-stethoscope"></i> Choisissez une Spécialité</h1>
        <p class="text-muted">Étape 1/5 - Sélectionnez la spécialité médicale appropriée</p>
    </div>

    <div class="progress-bar-custom">
        <div class="progress-fill"></div>
    </div>

    <?php if (!empty($message)): ?>
    <div class="alert alert-<?php echo $message_type; ?>">
        <i class="fas <?php echo $message_type == 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> me-2"></i>
        <?php echo $message; ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="row">
            <?php foreach ($specialites as $specialite): ?>
            <div class="col-md-4 col-sm-6 mb-4">
                <div class="specialite-card" onclick="selectSpecialite(<?php echo $specialite['id']; ?>)">
                    <i class="<?php echo $specialite['icone']; ?>"></i>
                    <h4><?php echo htmlspecialchars($specialite['nom']); ?></h4>
                    <p><?php echo htmlspecialchars(substr($specialite['description'], 0, 80)) . '...'; ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <input type="hidden" name="specialite_id" id="specialite_id" value="">
    </form>
</div>

<script>
function selectSpecialite(id) {
    document.getElementById('specialite_id').value = id;
    document.querySelector('form').submit();
}
</script>

<?php
include 'includes/footer.php';
?>
