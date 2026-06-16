<?php
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    exit('Non autorisé');
}

$id = $_POST['id'] ?? 0;

$stmt = $pdo->prepare("SELECT * FROM medecins WHERE id = ?");
$stmt->execute([$id]);
$medecin = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode($medecin);
?>