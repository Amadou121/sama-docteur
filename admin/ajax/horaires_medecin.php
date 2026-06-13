<?php
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    exit('Non autorisé');
}

$medecin_id = $_POST['medecin_id'] ?? 0;

// Récupérer les horaires existants
$stmt = $pdo->prepare("SELECT * FROM horaires_medecins WHERE medecin_id = ?");
$stmt->execute([$medecin_id]);
$horaires = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Créer un tableau associatif jour -> horaire
$horaires_par_jour = [];
foreach ($horaires as $horaire) {
    $horaires_par_jour[$horaire['jour_semaine']] = $horaire;
}

$jours = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
?>

<div class="container-fluid">
    <h5 class="mb-3">Gestion des horaires</h5>
    <form id="horairesForm">
        <input type="hidden" name="medecin_id" value="<?php echo $medecin_id; ?>">
        
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Jour</th>
                        <th>Matin (début - fin)</th>
                        <th>Après-midi (début - fin)</th>
                        <th>Durée consultation (min)</th>
                        <th>Disponible</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($jours as $jour): ?>
                        <?php 
                        $horaire = $horaires_par_jour[$jour] ?? null;
                        $heure_debut = $horaire ? substr($horaire['heure_debut'], 0, 5) : '';
                        $heure_fin = $horaire ? substr($horaire['heure_fin'], 0, 5) : '';
                        $duree = $horaire ? $horaire['duree_consultation'] : 30;
                        $disponible = $horaire ? $horaire['est_disponible'] : true;
                        ?>
                        <tr>
                            <td><strong><?php echo $jour; ?></strong></td>
                            <td>
                                <input type="time" class="form-control form-control-sm" 
                                       name="heure_debut[<?php echo $jour; ?>]" 
                                       value="<?php echo $heure_debut; ?>"
                                       placeholder="09:00">
                            </td>
                            <td>
                                <input type="time" class="form-control form-control-sm" 
                                       name="heure_fin[<?php echo $jour; ?>]" 
                                       value="<?php echo $heure_fin; ?>"
                                       placeholder="17:00">
                            </td>
                            <td>
                                <input type="number" class="form-control form-control-sm" 
                                       name="duree[<?php echo $jour; ?>]" 
                                       value="<?php echo $duree; ?>"
                                       min="15" max="120" step="15">
                            </td>
                            <td class="text-center">
                                <input type="checkbox" name="disponible[<?php echo $jour; ?>]" 
                                       value="1" <?php echo $disponible ? 'checked' : ''; ?>>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="mt-3 text-end">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Enregistrer les horaires
            </button>
        </div>
    </form>
</div>

<script>
$('#horairesForm').on('submit', function(e) {
    e.preventDefault();
    var formData = $(this).serialize();
    formData += '&action=ajouter_horaire';
    
    $.ajax({
        url: 'medecins.php',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                Swal.fire('Succès!', response.message, 'success');
            } else {
                Swal.fire('Erreur!', response.message, 'error');
            }
        }
    });
});
</script>