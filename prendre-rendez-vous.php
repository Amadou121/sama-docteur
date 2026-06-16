<?php
// ==============================
// Fichier : prendre-rendez-vous.php
// Redirection vers la nouvelle version du système de prise de rendez-vous
// ==============================

require_once 'includes/config.php';

// Rediriger vers la nouvelle version
header('Location: ' . SITE_URL . 'prendre-rendez-vous-etape1.php');
exit();

