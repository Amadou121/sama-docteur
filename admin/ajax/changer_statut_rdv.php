<?php
require_once '../includes/config.php';
header('Content-Type: application/json');

if (!estConnecte() || $_SESSION['user_role'] != 'admin') {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit();
}

$id = $_POST['id'] ?? 0;
$statut = $_POST['statut'] ?? '';

$stmt = $pdo->prepare("UPDATE rendez_vous SET statut = ? WHERE id = ?");
$success = $stmt->execute([$statut, $id]);

echo json_encode(['success' => $success, 'message' => $success ? 'Statut mis à jour' : 'Erreur']);
?>