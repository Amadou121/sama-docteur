<?php
require_once '../includes/config.php';
header('Content-Type: application/json');

if (!estConnecte() || $_SESSION['user_role'] != 'admin') {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit();
}

$id = $_POST['id'] ?? 0;
$actif = $_POST['actif'] ?? 1;

$stmt = $pdo->prepare("UPDATE utilisateurs SET est_actif = ? WHERE id = ? AND role = 'patient'");
$success = $stmt->execute([$actif, $id]);

echo json_encode(['success' => $success, 'message' => $success ? 'Statut modifié' : 'Erreur']);
?>