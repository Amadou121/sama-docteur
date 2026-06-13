<?php
// Fichier: admin/specialites.php
require_once '../includes/config.php';

if (!estConnecte() || $_SESSION['user_role'] != 'admin') {
    header('Location: ../index.php');
    exit();
}

// Traitement AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    header('Content-Type: application/json');
    $action = $_POST['action'] ?? '';
    $response = ['success' => false, 'message' => ''];
    
    try {
        switch ($action) {
            case 'ajouter':
                $stmt = $pdo->prepare("INSERT INTO specialites (nom, description, icone) VALUES (?, ?, ?)");
                $stmt->execute([$_POST['nom'], $_POST['description'], $_POST['icone']]);
                $response = ['success' => true, 'message' => 'Spécialité ajoutée'];
                break;
            case 'modifier':
                $stmt = $pdo->prepare("UPDATE specialites SET nom=?, description=?, icone=? WHERE id=?");
                $stmt->execute([$_POST['nom'], $_POST['description'], $_POST['icone'], $_POST['id']]);
                $response = ['success' => true, 'message' => 'Spécialité modifiée'];
                break;
            case 'supprimer':
                $stmt = $pdo->prepare("DELETE FROM specialites WHERE id=?");
                $stmt->execute([$_POST['id']]);
                $response = ['success' => true, 'message' => 'Spécialité supprimée'];
                break;
        }
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }
    echo json_encode($response);
    exit();
}

$specialites = $pdo->query("SELECT s.*, COUNT(m.id) as nb_medecins FROM specialites s LEFT JOIN medecins m ON s.id = m.specialite_id GROUP BY s.id ORDER BY s.nom")->fetchAll();
include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Spécialités - Sama Docteur Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f4f6f9; }
        .admin-wrapper { display: flex; min-height: 100vh; }
        .admin-sidebar { width: 280px; background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%); color: white; position: fixed; height: 100vh; overflow-y: auto; }
        .admin-sidebar .logo { padding: 25px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .admin-sidebar .nav-link { color: rgba(255,255,255,0.8); padding: 12px 25px; display: flex; align-items: center; }
        .admin-sidebar .nav-link:hover, .admin-sidebar .nav-link.active { background: rgba(255,255,255,0.1); border-left: 4px solid #00a8ff; }
        .admin-sidebar .nav-link i { width: 25px; margin-right: 10px; }
        .admin-content { flex: 1; margin-left: 280px; padding: 20px; }
        .top-bar { background: white; border-radius: 12px; padding: 15px 25px; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; }
        .specialite-card { background: white; border-radius: 12px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); transition: transform 0.3s; cursor: pointer; }
        .specialite-card:hover { transform: translateY(-5px); box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
        .specialite-icon { width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 28px; margin-bottom: 15px; }
        @media (max-width: 768px) { .admin-sidebar { transform: translateX(-100%); } .admin-content { margin-left: 0; } }
    </style>
</head>
<body>
<div class="admin-wrapper">
    <div class="admin-sidebar">
        <div class="logo"><h3><i class="fas fa-stethoscope"></i> Sama Docteur</h3><p>Espace Administration</p></div>
        <div class="nav-menu">
            <div class="nav-item"><a href="index.php" class="nav-link"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a></div>
            <div class="nav-item"><a href="medecins.php" class="nav-link"><i class="fas fa-user-md"></i> Médecins</a></div>
            <div class="nav-item"><a href="patients.php" class="nav-link"><i class="fas fa-users"></i> Patients</a></div>
            <div class="nav-item"><a href="rendez-vous.php" class="nav-link"><i class="fas fa-calendar-alt"></i> Rendez-vous</a></div>
            <div class="nav-item"><a href="specialites.php" class="nav-link active"><i class="fas fa-tags"></i> Spécialités</a></div>
            <div class="nav-item"><a href="../logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></div>
        </div>
    </div>

    <div class="admin-content">
        <div class="top-bar">
            <div class="page-title"><h2><i class="fas fa-tags"></i> Gestion des Spécialités</h2></div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#specialiteModal" onclick="resetForm()"><i class="fas fa-plus"></i> Nouvelle spécialité</button>
        </div>

        <div class="row">
            <?php foreach ($specialites as $spec): ?>
            <div class="col-md-4">
                <div class="specialite-card" onclick="modifierSpecialite(<?php echo htmlspecialchars(json_encode($spec)); ?>)">
                    <div class="specialite-icon" style="background: <?php echo $spec['id'] % 2 == 0 ? '#e3f2fd' : '#e8f5e9'; ?>; color: <?php echo $spec['id'] % 2 == 0 ? '#1976d2' : '#388e3c'; ?>;">
                        <i class="<?php echo $spec['icone']; ?>"></i>
                    </div>
                    <h5><?php echo htmlspecialchars($spec['nom']); ?></h5>
                    <p class="text-muted small"><?php echo htmlspecialchars(substr($spec['description'], 0, 80)); ?>...</p>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <span class="badge bg-primary"><?php echo $spec['nb_medecins']; ?> médecins</span>
                        <div>
                            <button class="btn btn-sm btn-warning" onclick="event.stopPropagation(); modifierSpecialite(<?php echo htmlspecialchars(json_encode($spec)); ?>)"><i class="fas fa-edit"></i></button>
                            <button class="btn btn-sm btn-danger" onclick="event.stopPropagation(); supprimerSpecialite(<?php echo $spec['id']; ?>, '<?php echo htmlspecialchars($spec['nom']); ?>')"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="specialiteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white"><h5 class="modal-title" id="modalTitle">Ajouter une spécialité</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <form id="specialiteForm">
                <input type="hidden" name="id" id="specialite_id">
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Nom *</label><input type="text" class="form-control" name="nom" id="nom" required></div>
                    <div class="mb-3"><label class="form-label">Description</label><textarea class="form-control" name="description" id="description" rows="3"></textarea></div>
                    <div class="mb-3"><label class="form-label">Icône (FontAwesome)</label><input type="text" class="form-control" name="icone" id="icone" placeholder="fas fa-stethoscope" value="fas fa-stethoscope"></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button><button type="submit" class="btn btn-primary">Enregistrer</button></div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
let mode = 'ajouter';
function resetForm() { mode = 'ajouter'; $('#modalTitle').text('Ajouter une spécialité'); $('#specialiteForm')[0].reset(); $('#specialite_id').val(''); }
function modifierSpecialite(spec) { mode = 'modifier'; $('#modalTitle').text('Modifier la spécialité'); $('#specialite_id').val(spec.id); $('#nom').val(spec.nom); $('#description').val(spec.description); $('#icone').val(spec.icone); $('#specialiteModal').modal('show'); }
$('#specialiteForm').on('submit', function(e) {
    e.preventDefault();
    let formData = $(this).serialize();
    formData += '&action=' + mode;
    $.ajax({ url: 'specialites.php', type: 'POST', data: formData, dataType: 'json', success: function(r) {
        if (r.success) Swal.fire('Succès!', r.message, 'success').then(() => location.reload());
        else Swal.fire('Erreur!', r.message, 'error');
    }});
});
function supprimerSpecialite(id, nom) {
    Swal.fire({ title: 'Supprimer', text: `Supprimer "${nom}" ?`, icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Oui, supprimer' }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({ url: 'specialites.php', type: 'POST', data: {action: 'supprimer', id: id}, dataType: 'json', success: function(r) {
                if (r.success) Swal.fire('Supprimé!', r.message, 'success').then(() => location.reload());
                else Swal.fire('Erreur!', r.message, 'error');
            }});
        }
    });
}
</script>

<?php include '../includes/footer.php'; ?>