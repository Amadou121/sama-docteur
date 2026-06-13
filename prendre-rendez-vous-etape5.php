<?php
// ==============================
// Fichier : prendre-rendez-vous-etape5.php
// Étape 5: Motif et Paiement
// ==============================

require_once 'includes/config.php';
require_once 'includes/fonctions.php';

$pageTitle = 'Confirmer le Rendez-vous';
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

// Vérifier si l'heure a été sélectionnée
if (!isset($_SESSION['rendez_vous_data']['heure_rendez_vous']) || empty($_SESSION['rendez_vous_data']['heure_rendez_vous'])) {
    header('Location: ' . SITE_URL . 'prendre-rendez-vous-etape4.php');
    exit();
}

$utilisateur_id = $_SESSION['user_id'];
$specialite_id = $_SESSION['rendez_vous_data']['specialite_id'];
$medecin_id = $_SESSION['rendez_vous_data']['medecin_id'];
$date_rendez_vous = $_SESSION['rendez_vous_data']['date_rendez_vous'];
$heure_rendez_vous = $_SESSION['rendez_vous_data']['heure_rendez_vous'];

// Récupérer les informations du médecin
$medecins = getMedecinsBySpecialite($pdo, $specialite_id);
$medecin = null;
foreach ($medecins as $m) {
    if ($m['id'] == $medecin_id) {
        $medecin = $m;
        break;
    }
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $motif = isset($_POST['motif']) ? trim($_POST['motif']) : '';
    $paiement_confirme = isset($_POST['paiement_confirme']) ? true : false;

    // Validation
    if (empty($motif) || !$paiement_confirme) {
        $message = 'Veuillez remplir le motif et confirmer le paiement.';
        $message_type = 'danger';
    } else {
        // Combiner date et heure
        $datetime_rendez_vous = $date_rendez_vous . ' ' . $heure_rendez_vous . ':00';

        // Créer le rendez-vous
        $resultat = creerRendezVous($pdo, $utilisateur_id, $medecin_id, $datetime_rendez_vous, $motif);

        if ($resultat['success']) {
            // Nettoyer la session
            unset($_SESSION['rendez_vous_data']);
            
            $_SESSION['flash'] = [
                'type' => 'success',
                'message' => 'Votre rendez-vous a été créé avec succès ! Vous recevrez une confirmation par email.'
            ];
            header('Location: ' . SITE_URL . 'dashboard.php');
            exit();
        } else {
            $message = $resultat['message'];
            $message_type = 'danger';
        }
    }
}
?>

<style>
    .confirmation-container {
        max-width: 800px;
        margin: 40px auto;
        padding: 30px;
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        border-radius: 20px;
    }

    .confirmation-card {
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
        width: 83.33%;
    }

    .btn-back {
        margin-bottom: 20px;
    }

    .recapitulatif {
        background: #f8fbff;
        padding: 25px;
        border-radius: 10px;
        margin-bottom: 30px;
    }

    .recapitulatif h5 {
        color: #2c3e50;
        margin-bottom: 20px;
        font-weight: 600;
    }

    .recapitulatif p {
        margin: 10px 0;
        color: #34495e;
    }

    .recapitulatif strong {
        color: #2c3e50;
    }

    .paiement-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 12px;
        padding: 25px;
        color: white;
        margin-bottom: 30px;
    }

    .paiement-section h4 {
        margin-bottom: 15px;
        font-weight: 600;
    }

    .paiement-amount {
        font-size: 2.5em;
        font-weight: 700;
        margin-bottom: 20px;
    }

    .paiement-checkbox {
        display: flex;
        align-items: center;
        gap: 12px;
        cursor: pointer;
    }

    .paiement-checkbox input[type="checkbox"] {
        width: 24px;
        height: 24px;
        cursor: pointer;
    }

    .paiement-checkbox label {
        cursor: pointer;
        margin: 0;
        font-weight: 500;
        font-size: 1.1em;
    }

    .btn-submit {
        background: linear-gradient(135deg, #007bff, #0056b3);
        color: white;
        padding: 15px 40px;
        border: none;
        border-radius: 30px;
        font-weight: 600;
        font-size: 1.1em;
        cursor: pointer;
        transition: all 0.3s ease;
        width: 100%;
    }

    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 20px rgba(0, 123, 255, 0.4);
    }
</style>

<div class="confirmation-container" data-aos="fade-up">
    <div class="page-header">
        <h1><i class="fas fa-check-circle"></i> Confirmer le Rendez-vous</h1>
        <p class="text-muted">Étape 5/5 - Vérifiez et confirmez votre rendez-vous</p>
    </div>

    <div class="progress-bar-custom">
        <div class="progress-fill"></div>
    </div>

    <a href="<?php echo SITE_URL; ?>prendre-rendez-vous-etape4.php" class="btn btn-outline-secondary btn-back">
        <i class="fas fa-arrow-left"></i> Retour
    </a>

    <?php if (!empty($message)): ?>
    <div class="alert alert-<?php echo $message_type; ?>">
        <i class="fas <?php echo $message_type == 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> me-2"></i>
        <?php echo $message; ?>
    </div>
    <?php endif; ?>

    <div class="confirmation-card">
        <?php if ($medecin): ?>
        <div class="recapitulatif">
            <h5><i class="fas fa-info-circle"></i> Récapitulatif de votre rendez-vous</h5>
            <p><strong>Médecin:</strong> <?php echo htmlspecialchars($medecin['nom_complet']); ?></p>
            <p><strong>Spécialité:</strong> <?php echo htmlspecialchars($medecin['specialite_nom']); ?></p>
            <p><strong>Date:</strong> <?php echo date('d/m/Y', strtotime($date_rendez_vous)); ?></p>
            <p><strong>Heure:</strong> <?php echo htmlspecialchars($heure_rendez_vous); ?></p>
            <p><strong>Tarif:</strong> <?php echo number_format($medecin['tarif_consultation'], 0, ',', ' '); ?> FCFA</p>
        </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-4">
                <label for="motif" class="form-label fw-bold">Motif de la consultation</label>
                <textarea class="form-control" 
                          id="motif" 
                          name="motif" 
                          rows="4" 
                          placeholder="Décrivez le motif de votre consultation..."
                          required><?php echo isset($_POST['motif']) ? htmlspecialchars($_POST['motif']) : ''; ?></textarea>
            </div>

            <div class="paiement-section">
                <h4><i class="fas fa-credit-card"></i> Paiement</h4>
                <div class="paiement-amount">10 000 FCFA</div>
                <div class="paiement-checkbox">
                    <input type="checkbox" id="paiement_confirme" name="paiement_confirme" <?php echo (isset($_POST['paiement_confirme']) && $_POST['paiement_confirme']) ? 'checked' : ''; ?>>
                    <label for="paiement_confirme">Je confirme le paiement de 10 000 FCFA</label>
                </div>
            </div>

            <button type="submit" class="btn-submit">
                <i class="fas fa-check-circle"></i> Confirmer le rendez-vous
            </button>
        </form>
    </div>
</div>

<?php
include 'includes/footer.php';
?>
