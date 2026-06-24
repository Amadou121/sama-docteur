<?php
// Fichier: messages-medecin.php
require_once 'includes/config.php';

// Vérifier que l'utilisateur est un médecin connecté
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'medecin') {
    header('Location: medecin_login.php');
    exit();
}

$medecin_id = $_SESSION['user_id'];

// Récupérer les messages du médecin
$stmt = $pdo->prepare("
    SELECT m.*, 
           DATE_FORMAT(m.date_envoi, '%d/%m/%Y à %H:%i') as date_formatee
    FROM messages m
    WHERE m.medecin_id = ? OR m.medecin_id IS NULL
    ORDER BY m.date_envoi DESC
");
$stmt->execute([$medecin_id]);
$messages = $stmt->fetchAll();

// Marquer tous les messages comme lus
$stmtUpdate = $pdo->prepare("UPDATE messages SET statut = 'lu' WHERE medecin_id = ? AND statut = 'non_lu'");
$stmtUpdate->execute([$medecin_id]);

// Compter les messages non lus
$stmtCount = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE medecin_id = ? AND statut = 'non_lu'");
$stmtCount->execute([$medecin_id]);
$unread_count = $stmtCount->fetchColumn();

include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-12">
            <!-- En-tête -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2><i class="fas fa-envelope text-primary"></i> Mes messages</h2>
                    <p class="text-muted">