<?php
// Fichier: dashboard.php
require_once 'includes/config.php';
redirigerSiNonConnecte();

if ($_SESSION['user_role'] != 'patient') {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Statistiques
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_rdv,
        SUM(CASE WHEN statut = 'confirme' THEN 1 ELSE 0 END) as rdv_confirms,
        SUM(CASE WHEN statut = 'termine' THEN 1 ELSE 0 END) as rdv_termines,
        SUM(CASE WHEN statut = 'en_attente' THEN 1 ELSE 0 END) as rdv_attente
    FROM rendez_vous 
    WHERE utilisateur_id = ?
");
$stmt->execute([$user_id]);
$stats = $stmt->fetch();

// Prochains rendez-vous
$stmt = $pdo->prepare("
    SELECT r.*, m.nom_complet as medecin_nom, m.telephone as medecin_tel, s.nom as specialite_nom
    FROM rendez_vous r
    JOIN medecins m ON r.medecin_id = m.id
    JOIN specialites s ON m.specialite_id = s.id
    WHERE r.utilisateur_id = ? AND r.date_rendez_vous >= NOW() AND r.statut NOT IN ('annule', 'termine')
    ORDER BY r.date_rendez_vous ASC
    LIMIT 5
");
$stmt->execute([$user_id]);
$prochains_rdv = $stmt->fetchAll();

// Compter les rendez-vous en attente de confirmation
$stmt = $pdo->prepare("
    SELECT COUNT(*) as nb_attente
    FROM rendez_vous 
    WHERE utilisateur_id = ? AND statut = 'en_attente' AND date_rendez_vous >= NOW()
");
$stmt->execute([$user_id]);
$rdv_attente_count = $stmt->fetch();

// Compter les rendez-vous confirmés à venir
$stmt = $pdo->prepare("
    SELECT COUNT(*) as nb_confirmes
    FROM rendez_vous 
    WHERE utilisateur_id = ? AND statut = 'confirme' AND date_rendez_vous >= NOW()
");
$stmt->execute([$user_id]);
$rdv_confirmes_count = $stmt->fetch();

// Historique des rendez-vous
$stmt = $pdo->prepare("
    SELECT r.*, m.nom_complet as medecin_nom, s.nom as specialite_nom
    FROM rendez_vous r
    JOIN medecins m ON r.medecin_id = m.id
    JOIN specialites s ON m.specialite_id = s.id
    WHERE r.utilisateur_id = ? 
    ORDER BY r.date_rendez_vous DESC
    LIMIT 10
");
$stmt->execute([$user_id]);
$historique_rdv = $stmt->fetchAll();

// Notifications non lues
$stmt = $pdo->prepare("
    SELECT COUNT(*) as non_lues 
    FROM notifications 
    WHERE utilisateur_id = ? AND est_lu = FALSE
");
$stmt->execute([$user_id]);
$notifications = $stmt->fetch();

// Récupérer toutes les notifications pour l'affichage
$stmt = $pdo->prepare("
    SELECT * FROM notifications 
    WHERE utilisateur_id = ? 
    ORDER BY date_creation DESC 
    LIMIT 20
");
$stmt->execute([$user_id]);
$liste_notifications = $stmt->fetchAll();

// Récupérer les spécialités pour le formulaire de prise de RDV
$stmt = $pdo->prepare("SELECT * FROM specialites ORDER BY nom");
$stmt->execute();
$specialites = $stmt->fetchAll();

include 'includes/header.php';
?>

<style>
.dashboard-wrapper {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: calc(100vh - 150px);
    padding: 30px 0;
    overflow-x: hidden;
}

.dashboard-wrapper .container {
    max-width: 1260px;
}

.dashboard-sidebar {
    background: rgba(255,255,255,0.97);
    border-radius: 24px;
    padding: 28px;
    box-shadow: 0 18px 50px rgba(32, 49, 84, 0.12);
    margin-bottom: 30px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border: 1px solid rgba(255,255,255,0.8);
}

.dashboard-card {
    background: rgba(255,255,255,0.95);
    border-radius: 24px;
    padding: 28px;
    box-shadow: 0 16px 40px rgba(32, 49, 84, 0.1);
    margin-bottom: 30px;
    border: 1px solid rgba(226, 232, 240, 0.9);
    width: 100%;
    overflow: hidden;
}

.dashboard-card h4 {
    margin-bottom: 1rem;
}

.dashboard-card .table-responsive {
    overflow-x: auto;
}

.dashboard-card .appointment-card,
.dashboard-card .notification-item,
.dashboard-card .confirmation-card,
.dashboard-card .booking-step {
    max-width: 100%;
    word-break: break-word;
}

.dashboard-sidebar:hover {
    transform: translateY(-4px);
    box-shadow: 0 25px 60px rgba(32, 49, 84, 0.15);
}

.user-info {
    text-align: center;
    padding: 22px 18px;
    border-radius: 20px;
    background: linear-gradient(180deg, rgba(102,126,234,0.1), rgba(255,255,255,0.95));
    border: 1px solid rgba(102,126,234,0.16);
    margin-bottom: 24px;
}

.user-avatar {
    width: 82px;
    height: 82px;
    margin: 0 auto 16px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 34px;
    font-weight: 700;
    text-transform: uppercase;
}

.dashboard-menu {
    list-style: none;
    padding: 0;
    margin: 0;
}

.dashboard-menu li {
    margin-bottom: 12px;
    position: relative;
}

.dashboard-menu a {
    display: grid;
    grid-template-columns: auto 1fr;
    align-items: center;
    gap: 12px;
    padding: 14px 18px;
    color: #303841;
    text-decoration: none;
    border-radius: 16px;
    transition: all 0.3s ease;
    position: relative;
    font-weight: 600;
    background: rgba(255,255,255,0.88);
    border: 1px solid rgba(220, 227, 241, 0.8);
    box-shadow: 0 8px 20px rgba(70, 96, 187, 0.08);
}

.dashboard-menu a:hover {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    transform: translateX(4px);
    box-shadow: 0 12px 28px rgba(102,126,234,0.25);
}

.dashboard-menu a.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    box-shadow: 0 12px 28px rgba(102,126,234,0.25);
}

.menu-icon {
    width: 42px;
    height: 42px;
    border-radius: 14px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(102,126,234,0.12);
    color: #667eea;
    transition: background 0.3s ease, transform 0.3s ease;
}

.dashboard-menu a:hover .menu-icon {
    background: rgba(255,255,255,0.25);
    transform: translateX(2px);
}

.dashboard-menu a.active .menu-icon {
    background: rgba(255,255,255,0.25);
}

.dashboard-menu a i {
    width: auto;
    margin: 0;
    font-size: 16px;
}

/* ⭐ STYLE POUR LE LIEN CONTACT DANS LE MENU */
.dashboard-menu a.contact-link {
    background: linear-gradient(135deg, #2563EB, #1e40af);
    color: white !important;
    margin-top: 15px;
    border: none;
}

.dashboard-menu a.contact-link:hover {
    background: linear-gradient(135deg, #1e40af, #1e3a8a);
    transform: translateX(5px);
    box-shadow: 0 4px 15px rgba(37,99,235,0.3);
}

.dashboard-menu a.contact-link i {
    color: white;
}

.menu-badge {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
    border-radius: 20px;
    padding: 3px 10px;
    font-size: 11px;
    font-weight: bold;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: translateY(-50%) scale(1); }
    50% { transform: translateY(-50%) scale(1.05); }
    100% { transform: translateY(-50%) scale(1); }
}

.stat-card {
    display: none;
}

.stats-bar {
    background: #ffffff;
    border-radius: 20px;
    padding: 32px 24px;
    box-shadow: 0 16px 36px rgba(32, 49, 84, 0.09);
    border: 1px solid rgba(102,126,234,0.12);
    margin-bottom: 24px;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 24px;
}

.stat-item {
    text-align: center;
    padding: 22px 16px;
    border-radius: 24px;
    background: rgba(255,255,255,0.95);
    transition: all 0.35s ease;
    position: relative;
    overflow: hidden;
    border: 1px solid rgba(102,126,234,0.12);
}

.stat-item::before {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at top right, rgba(102,126,234,0.18), transparent 42%);
    opacity: 0;
    transition: opacity 0.35s ease;
}

.stat-item::after {
    content: '';
    position: absolute;
    width: 98px;
    height: 98px;
    border-radius: 50%;
    top: -34px;
    right: -34px;
    background: rgba(118,75,162,0.15);
    pointer-events: none;
    transition: transform 0.35s ease;
}

.stat-item:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 50px rgba(32, 49, 84, 0.12);
}

.stat-item:hover::before,
.stat-item:hover::after {
    opacity: 1;
    transform: translateY(-2px);
}

.stat-gradient-blue {
    background: linear-gradient(180deg, #eef4ff 0%, #f6f8ff 100%);
}

.stat-gradient-green {
    background: linear-gradient(180deg, #e9fbf1 0%, #f4f8f5 100%);
}

.stat-gradient-orange {
    background: linear-gradient(180deg, #fff6e5 0%, #fffaf2 100%);
}

.stat-gradient-purple {
    background: linear-gradient(180deg, #f4ecff 0%, #faf6ff 100%);
}

.stat-item .stat-number {
    font-size: 46px;
    font-weight: 900;
    color: #2d3a62;
    margin: 0;
    line-height: 1;
}

.stat-item .stat-label {
    font-size: 13px;
    font-weight: 800;
    color: #4e5d79;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    margin-top: 16px;
}

.stat-item .stat-desc {
    font-size: 13px;
    color: #667789;
    margin-top: 8px;
    line-height: 1.5;
}

.stat-item.stat-gradient-blue .stat-number {
    color: #3b5bff;
}

.stat-item.stat-gradient-green .stat-number {
    color: #1f8e5e;
}

.stat-item.stat-gradient-orange .stat-number {
    color: #d46c08;
}

.stat-item.stat-gradient-purple .stat-number {
    color: #6b4acb;
}

.stat-card h3 {
    font-size: 36px;
    font-weight: 700;
    margin: 10px 0 8px;
    color: #28354b;
}

.stat-card p {
    margin: 0;
    color: #58657f;
    font-size: 14px;
}

/* Banner greeting styles */
.dashboard-greeting {
    background: #ffffff;
    padding: 24px 20px;
    border-radius: 18px;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    box-shadow: 0 14px 40px rgba(102, 126, 234, 0.12);
    border: 1px solid rgba(102,126,234,0.1);
    margin-bottom: 20px;
    max-width: 100%;
    animation: floatIn 0.8s ease-out both;
}

.dashboard-greeting h2 {
    margin: 0;
    font-size: 28px;
    color: #273c75;
    letter-spacing: 0.3px;
    animation: fadeInUp 0.9s ease-out 0.1s both;
}

.dashboard-greeting p {
    margin: 10px 0 0;
    color: #546e7a;
    font-size: 15px;
    animation: fadeInUp 1s ease-out 0.2s both;
}

.dashboard-greeting::after {
    content: '';
    display: block;
    width: 80px;
    height: 4px;
    border-radius: 50px;
    background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
    margin-top: 16px;
    animation: pulseLine 2s ease-in-out infinite;
}

@keyframes floatIn {
    from {
        opacity: 0;
        transform: translateY(20px) scale(0.98);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(12px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes pulseLine {
    0%, 100% { transform: scaleX(1); opacity: 1; }
    50% { transform: scaleX(1.08); opacity: 0.85; }
}

.appointment-card {
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    border-radius: 15px;
    padding: 20px;
    margin-bottom: 15px;
    border-left: 5px solid rgba(102,126,234,0.25);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.appointment-card:hover {
    transform: translateX(6px);
    box-shadow: 0 8px 24px rgba(32, 49, 84, 0.12);
}

.appointment-card.confirme {
    border-left-color: #28a745;
}

.appointment-card.en_attente {
    border-left-color: #ffc107;
}

.status {
    display: inline-flex;
    align-items: center;
    padding: 6px 14px;
    border-radius: 25px;
    font-size: 12px;
    font-weight: 600;
    gap: 5px;
}

.status-confirme {
    background: linear-gradient(135deg, #a8e6cf 0%, #d4edda 100%);
    color: #155724;
    box-shadow: 0 2px 8px rgba(40,167,69,0.2);
}

.status-en_attente {
    background: linear-gradient(135deg, #ffeaa7 0%, #fff3cd 100%);
    color: #856404;
    box-shadow: 0 2px 8px rgba(255,193,7,0.2);
}

.status-termine {
    background: linear-gradient(135deg, #b8e1fc 0%, #d1ecf1 100%);
    color: #0c5460;
    box-shadow: 0 2px 8px rgba(12,84,96,0.2);
}

.status-annule {
    background: linear-gradient(135deg, #f8d7da 0%, #f8d7da 100%);
    color: #721c24;
    box-shadow: 0 2px 8px rgba(114,28,36,0.2);
}

/* Styles pour la prise de rendez-vous interactive */
.booking-container {
    max-width: 900px;
    margin: 0 auto;
}

.booking-step {
    background: white;
    border-radius: 20px;
    padding: 35px;
    margin-bottom: 20px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    animation: slideIn 0.5s ease-out;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateX(30px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.step-indicator {
    display: flex;
    justify-content: space-between;
    margin-bottom: 40px;
    position: relative;
}

.step {
    flex: 1;
    text-align: center;
    position: relative;
    z-index: 1;
    cursor: pointer;
    transition: all 0.3s;
}

.step .step-number {
    width: 45px;
    height: 45px;
    background: #e0e0e0;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin-bottom: 10px;
    transition: all 0.3s;
    font-size: 18px;
}

.step.active .step-number {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    transform: scale(1.15);
    box-shadow: 0 5px 15px rgba(102,126,234,0.4);
}

.step.completed .step-number {
    background: #28a745;
    color: white;
    transform: scale(1.05);
}

.step.completed .step-number::after {
    content: '✓';
}

.step .step-title {
    font-size: 14px;
    color: #666;
    font-weight: 500;
}

.step.active .step-title {
    color: #667eea;
    font-weight: bold;
}

.step.completed .step-title {
    color: #28a745;
}

.step-indicator::before {
    content: '';
    position: absolute;
    top: 22px;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, #e0e0e0, #e0e0e0);
    z-index: 0;
}

.specialty-card, .doctor-card {
    cursor: pointer;
    transition: all 0.3s;
    border: 2px solid #e0e0e0;
    border-radius: 15px;
    padding: 20px;
    margin-bottom: 20px;
    background: white;
    position: relative;
    overflow: hidden;
}

.specialty-card::before, .doctor-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(102,126,234,0.1), transparent);
    transition: left 0.5s;
}

.specialty-card:hover::before, .doctor-card:hover::before {
    left: 100%;
}

.specialty-card:hover, .doctor-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    border-color: #667eea;
}

.specialty-card.selected, .doctor-card.selected {
    border-color: #667eea;
    background: linear-gradient(135deg, rgba(102,126,234,0.05) 0%, rgba(118,75,162,0.05) 100%);
    box-shadow: 0 5px 20px rgba(102,126,234,0.2);
    transform: scale(1.02);
}

.doctor-avatar {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 28px;
    transition: all 0.3s;
}

.doctor-card:hover .doctor-avatar {
    transform: scale(1.1);
}

.time-slots-container {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-top: 15px;
}

.time-slot {
    flex: 0 0 calc(20% - 12px);
    background: #f8f9fa;
    border: 2px solid #e0e0e0;
    border-radius: 12px;
    padding: 12px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s;
    font-weight: 500;
}

.time-slot:hover:not(.unavailable) {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    border-color: #667eea;
    background: white;
}

.time-slot.selected {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-color: #667eea;
    transform: scale(1.05);
}

.time-slot.unavailable {
    opacity: 0.4;
    cursor: not-allowed;
    background: #f8f9fa;
    text-decoration: line-through;
}

.badge-gradient {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
}

.notification-item {
    padding: 15px;
    border-bottom: 1px solid #e0e0e0;
    cursor: pointer;
    transition: all 0.2s;
    border-radius: 10px;
    margin-bottom: 8px;
}

.notification-item:hover {
    background: #f8f9fa;
    transform: translateX(5px);
}

.notification-item.non-lue {
    background: linear-gradient(135deg, rgba(102,126,234,0.05) 0%, rgba(118,75,162,0.05) 100%);
    border-left: 4px solid #667eea;
}

.btn-gradient {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 12px 35px;
    border-radius: 30px;
    font-weight: 600;
    transition: all 0.3s;
    font-size: 16px;
}

.btn-gradient:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(102,126,234,0.4);
    color: white;
}

.btn-gradient:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn-outline-gradient {
    background: transparent;
    border: 2px solid #667eea;
    color: #667eea;
    padding: 8px 25px;
    border-radius: 25px;
    font-weight: 600;
    transition: all 0.3s;
}

.btn-outline-gradient:hover {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    transform: translateY(-2px);
}

.fade-in {
    animation: fadeIn 0.5s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.loading-spinner {
    display: inline-block;
    width: 40px;
    height: 40px;
    border: 3px solid rgba(102,126,234,0.3);
    border-radius: 50%;
    border-top-color: #667eea;
    animation: spin 1s ease-in-out infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.confirmation-card {
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 20px;
}

.confirmation-card h5 {
    color: #667eea;
    margin-bottom: 20px;
}

.confirmation-detail {
    display: flex;
    align-items: center;
    padding: 12px;
    border-bottom: 1px solid rgba(0,0,0,0.1);
}

.confirmation-detail i {
    width: 30px;
    color: #667eea;
    font-size: 18px;
}

.confirmation-detail strong {
    width: 100px;
    color: #333;
}

.success-message {
    text-align: center;
    padding: 40px;
}

.success-message i {
    font-size: 70px;
    color: #28a745;
    margin-bottom: 20px;
}

/* ⭐ BANNIÈRE DE LIEN VERS CONTACT */
.contact-banner {
    background: linear-gradient(135deg, #2563EB, #1e40af);
    border-radius: 15px;
    padding: 20px 30px;
    margin-bottom: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
    color: white;
    box-shadow: 0 4px 15px rgba(37,99,235,0.2);
}

.contact-banner .banner-content {
    display: flex;
    align-items: center;
    gap: 15px;
}

.contact-banner .banner-content i {
    font-size: 2rem;
}

.contact-banner .banner-content h5 {
    margin-bottom: 0;
    font-weight: 600;
}

.contact-banner .banner-content p {
    margin-bottom: 0;
    opacity: 0.8;
    font-size: 0.9rem;
}

.contact-banner .btn-banner {
    background: white;
    color: #2563EB;
    padding: 10px 25px;
    border-radius: 30px;
    border: none;
    font-weight: 600;
    transition: all 0.3s ease;
    text-decoration: none;
}

.contact-banner .btn-banner:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    color: #1e40af;
}

.contact-btn-top {
    background: transparent;
    border: 2px solid #0066cc;
    color: #0066cc;
    padding: 6px 18px;
    border-radius: 30px;
    transition: all 0.3s ease;
    font-size: 0.9rem;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.contact-btn-top:hover {
    background: #0066cc;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,102,204,0.3);
}

@media (max-width: 768px) {
    .contact-banner {
        flex-direction: column;
        text-align: center;
        padding: 20px;
    }
    
    .contact-banner .banner-content {
        flex-direction: column;
    }
}
</style>

<div class="dashboard-wrapper">
    <div class="container">
        <div class="row">
            <div class="col-lg-3" data-aos="fade-right">
                <div class="dashboard-sidebar">
                    <div class="user-info">
                        <div class="user-avatar">
                            <?php echo strtoupper(substr(htmlspecialchars($_SESSION['user_nom']), 0, 1)); ?>
                        </div>
                        <h5><?php echo htmlspecialchars($_SESSION['user_nom']); ?></h5>
                        <p class="text-muted">Patient</p>
                        <p class="small text-secondary">
                            <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($_SESSION['user_email']); ?>
                        </p>
                    </div>
                    <ul class="dashboard-menu">
                        <li><a href="#" class="active" data-page="dashboard"><span class="menu-icon"><i class="fas fa-tachometer-alt"></i></span> Tableau de bord</a></li>
                        <li><a href="#" data-page="booking"><span class="menu-icon"><i class="fas fa-calendar-plus"></i></span> Prendre RDV</a></li>
                        <li><a href="#" data-page="appointments">
                            <span class="menu-icon"><i class="fas fa-calendar-alt"></i></span> Mes rendez-vous
                            <?php 
                            $total_rdv_encours = $rdv_attente_count['nb_attente'] + $rdv_confirmes_count['nb_confirmes'];
                            if($total_rdv_encours > 0): 
                            ?>
                                <span class="menu-badge"><?php echo $total_rdv_encours; ?></span>
                            <?php endif; ?>
                        </a></li>
                        <li><a href="#" data-page="history"><span class="menu-icon"><i class="fas fa-history"></i></span> Historique</a></li>
                        <li><a href="#" data-page="profile"><span class="menu-icon"><i class="fas fa-user"></i></span> Mon profil</a></li>
                        <li><a href="#" data-page="notifications">
                            <span class="menu-icon"><i class="fas fa-bell"></i></span> Notifications 
                            <?php if($notifications['non_lues'] > 0): ?>
                                <span class="menu-badge"><?php echo $notifications['non_lues']; ?></span>
                            <?php endif; ?>
                        </a></li>
                        <li><a href="contact.php" class="contact-link">
                            <i class="fas fa-envelope"></i> Nous contacter
                            <i class="fas fa-arrow-right ms-auto"></i>
                        </a></li>
                    </ul>
                </div>
            </div>
            
            <div class="col-lg-9" data-aos="fade-left">
                <!-- ⭐ BANNIÈRE CONTACT -->
                <div class="contact-banner">
                    <div class="banner-content">
                        <i class="fas fa-comment-medical"></i>
                        <div>
                            <h5>Une question médicale ?</h5>
                            <p>Notre équipe est à votre écoute 24/7</p>
                        </div>
                    </div>
                    <a href="contact.php" class="btn-banner">
                        <i class="fas fa-paper-plane me-2"></i> Nous contacter
                    </a>
                </div>
                
                <!-- Section Tableau de bord -->
                <div id="dashboardSection">
                    <div class="dashboard-greeting">
                        <div class="greet-text">
                            <h2>Bonjour, <?php echo htmlspecialchars($_SESSION['user_nom']); ?></h2>
                            <p>Bienvenue sur votre tableau de bord</p>
                        </div>
                    </div>
                    
                    <div class="stats-bar">
                        <div class="stat-item stat-gradient-blue">
                            <p class="stat-number"><?php echo $stats['total_rdv']; ?></p>
                            <p class="stat-label">Total RDV</p>
                            <p class="stat-desc">Tous les rendez-vous planifiés</p>
                        </div>
                        <div class="stat-item stat-gradient-green">
                            <p class="stat-number"><?php echo $stats['rdv_confirms']; ?></p>
                            <p class="stat-label">Confirmés</p>
                            <p class="stat-desc">Rendez-vous validés</p>
                        </div>
                        <div class="stat-item stat-gradient-orange">
                            <p class="stat-number"><?php echo $stats['rdv_attente']; ?></p>
                            <p class="stat-label">En attente</p>
                            <p class="stat-desc">Demandes en attente</p>
                        </div>
                        <div class="stat-item stat-gradient-purple">
                            <p class="stat-number"><?php echo $stats['rdv_termines']; ?></p>
                            <p class="stat-label">Terminés</p>
                            <p class="stat-desc">Rendez-vous clos</p>
                        </div>
                    </div>
                    
                    <div class="dashboard-card mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="mb-0">
                                <i class="fas fa-calendar-alt"></i> Prochains rendez-vous
                                <?php if($total_rdv_encours > 0): ?>
                                    <span class="badge-gradient"><?php echo $total_rdv_encours; ?> à venir</span>
                                <?php endif; ?>
                            </h4>
                            <a href="contact.php" class="contact-btn-top">
                                <i class="fas fa-envelope"></i> Contacter
                            </a>
                        </div>
                        
                        <?php if(empty($prochains_rdv)): ?>
                            <div class="alert alert-info fade-in">
                                <i class="fas fa-info-circle"></i> Aucun rendez-vous à venir.
                                <a href="#" onclick="document.querySelector('[data-page=\'booking\']').click(); return false;" class="alert-link">Prendre un rendez-vous</a>
                            </div>
                        <?php else: ?>
                            <?php foreach($prochains_rdv as $rdv): ?>
                            <div class="appointment-card <?php echo $rdv['statut']; ?> fade-in">
                                <div class="row align-items-center">
                                    <div class="col-md-7">
                                        <h5><?php echo htmlspecialchars($rdv['medecin_nom']); ?></h5>
                                        <p class="text-primary mb-1"><?php echo htmlspecialchars($rdv['specialite_nom']); ?></p>
                                        <p class="small text-secondary mb-0">
                                            <i class="fas fa-calendar-day"></i> <?php echo date('d/m/Y', strtotime($rdv['date_rendez_vous'])); ?>
                                            à <?php echo date('H:i', strtotime($rdv['date_rendez_vous'])); ?>
                                        </p>
                                        <p class="small text-secondary">
                                            <i class="fas fa-phone-alt"></i> <?php echo htmlspecialchars($rdv['medecin_tel']); ?>
                                        </p>
                                    </div>
                                    <div class="col-md-5 text-md-end">
                                        <span class="status status-<?php echo $rdv['statut']; ?>">
                                            <?php 
                                            switch($rdv['statut']) {
                                                case 'confirme': echo '<i class="fas fa-check-circle"></i> Confirmé'; break;
                                                case 'en_attente': echo '<i class="fas fa-clock"></i> En attente'; break;
                                                case 'termine': echo '<i class="fas fa-check-double"></i> Terminé'; break;
                                                case 'annule': echo '<i class="fas fa-times-circle"></i> Annulé'; break;
                                            }
                                            ?>
                                        </span>
                                        <div class="mt-2">
                                            <button class="btn btn-sm btn-outline-gradient" onclick="voirDetailsRendezVous(<?php echo $rdv['id']; ?>)">
                                                <i class="fas fa-info-circle"></i> Détails
                                            </button>
                                            <?php if($rdv['statut'] != 'annule'): ?>
                                            <button class="btn btn-sm btn-outline-danger" onclick="annulerRendezVous(<?php echo $rdv['id']; ?>)">
                                                <i class="fas fa-times"></i> Annuler
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Section Prise de rendez-vous interactive -->
                <div id="bookingSection" style="display: none;">
                    <div class="booking-container">
                        <div class="step-indicator">
                            <div class="step completed" data-step="1">
                                <div class="step-number">1</div>
                                <div class="step-title">Spécialité</div>
                            </div>
                            <div class="step" data-step="2">
                                <div class="step-number">2</div>
                                <div class="step-title">Médecin</div>
                            </div>
                            <div class="step" data-step="3">
                                <div class="step-number">3</div>
                                <div class="step-title">Date & Heure</div>
                            </div>
                            <div class="step" data-step="4">
                                <div class="step-number">4</div>
                                <div class="step-title">Confirmation</div>
                            </div>
                        </div>
                        
                        <!-- Étape 1: Sélection spécialité -->
                        <div id="step1" class="booking-step">
                            <h4 class="mb-4 text-center">
                                <i class="fas fa-stethoscope"></i> Choisissez une spécialité
                            </h4>
                            <div class="row" id="specialtiesList">
                                <?php foreach($specialites as $specialite): ?>
                                <div class="col-md-6">
                                    <div class="specialty-card" data-specialty-id="<?php echo $specialite['id']; ?>" data-specialty-name="<?php echo htmlspecialchars($specialite['nom']); ?>">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3">
                                                <i class="fas fa-<?php echo $specialite['icone'] ?? 'stethoscope'; ?> fa-3x" style="color: #667eea;"></i>
                                            </div>
                                            <div>
                                                <h5 class="mb-1"><?php echo htmlspecialchars($specialite['nom']); ?></h5>
                                                <p class="text-muted mb-0 small"><?php echo $specialite['description'] ?? 'Consultez nos spécialistes'; ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <!-- Étape 2: Sélection médecin -->
                        <div id="step2" class="booking-step" style="display: none;">
                            <h4 class="mb-4 text-center">
                                <i class="fas fa-user-md"></i> Choisissez un médecin
                            </h4>
                            <div id="doctorsList" class="row">
                                <div class="text-center">
                                    <div class="loading-spinner"></div>
                                    <p class="mt-2">Chargement des médecins...</p>
                                </div>
                            </div>
                            <div class="mt-4 text-center">
                                <button class="btn btn-outline-gradient" onclick="previousStep()">
                                    <i class="fas fa-arrow-left"></i> Retour
                                </button>
                            </div>
                        </div>
                        
                        <!-- Étape 3: Sélection date et heure -->
                        <div id="step3" class="booking-step" style="display: none;">
                            <h4 class="mb-4 text-center">
                                <i class="fas fa-calendar-day"></i> Choisissez la date et l'heure
                            </h4>
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Date du rendez-vous</label>
                                    <input type="date" id="rdvDate" class="form-control" min="<?php echo date('Y-m-d'); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Heure disponible</label>
                                    <div id="timeSlots" class="time-slots-container"></div>
                                </div>
                            </div>
                            <div class="mt-4 text-center">
                                <button class="btn btn-outline-gradient" onclick="previousStep()">
                                    <i class="fas fa-arrow-left"></i> Retour
                                </button>
                                <button id="nextToConfirmationBtn" class="btn btn-gradient" onclick="goToConfirmation()" disabled>
                                    Continuer <i class="fas fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Étape 4: Confirmation -->
                        <div id="step4" class="booking-step" style="display: none;">
                            <div id="confirmationContent">
                                <h4 class="mb-4 text-center">
                                    <i class="fas fa-clipboard-list"></i> Confirmation du rendez-vous
                                </h4>
                                <div id="confirmationDetails" class="confirmation-card"></div>
                                <div class="text-center">
                                    <button class="btn btn-outline-gradient" onclick="previousStep()">
                                        <i class="fas fa-arrow-left"></i> Retour
                                    </button>
                                    <button class="btn btn-gradient" onclick="confirmerRendezVous()">
                                        <i class="fas fa-check-circle"></i> Confirmer le rendez-vous
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Section Mes rendez-vous (liste complète) -->
                <div id="appointmentsSection" style="display: none;">
                    <div class="dashboard-card">
                        <h4 class="mb-3">
                            <i class="fas fa-calendar-alt"></i> Tous mes rendez-vous
                            <?php if($total_rdv_encours > 0): ?>
                                <span class="badge-gradient"><?php echo $total_rdv_encours; ?> actifs</span>
                            <?php endif; ?>
                        </h4>
                        
                        <ul class="nav nav-tabs mb-3" id="appointmentTab" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab">
                                    Tous <span class="badge bg-secondary"><?php echo count($prochains_rdv); ?></span>
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab">
                                    En attente 
                                    <?php if($rdv_attente_count['nb_attente'] > 0): ?>
                                        <span class="badge bg-warning"><?php echo $rdv_attente_count['nb_attente']; ?></span>
                                    <?php endif; ?>
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#confirmed" type="button" role="tab">
                                    Confirmés
                                    <?php if($rdv_confirmes_count['nb_confirmes'] > 0): ?>
                                        <span class="badge bg-success"><?php echo $rdv_confirmes_count['nb_confirmes']; ?></span>
                                    <?php endif; ?>
                                </button>
                            </li>
                        </ul>
                        
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="all" role="tabpanel">
                                <?php if(empty($prochains_rdv)): ?>
                                    <div class="alert alert-info">Aucun rendez-vous programmé</div>
                                <?php else: ?>
                                    <?php foreach($prochains_rdv as $rdv): ?>
                                    <div class="appointment-card <?php echo $rdv['statut']; ?>">
                                        <div class="row align-items-center">
                                            <div class="col-md-8">
                                                <h5><?php echo htmlspecialchars($rdv['medecin_nom']); ?></h5>
                                                <p class="text-primary mb-1"><?php echo htmlspecialchars($rdv['specialite_nom']); ?></p>
                                                <p class="small text-secondary mb-0">
                                                    <i class="fas fa-calendar-day"></i> <?php echo date('d/m/Y', strtotime($rdv['date_rendez_vous'])); ?>
                                                    à <?php echo date('H:i', strtotime($rdv['date_rendez_vous'])); ?>
                                                </p>
                                            </div>
                                            <div class="col-md-4 text-md-end">
                                                <span class="status status-<?php echo $rdv['statut']; ?>">
                                                    <?php echo $rdv['statut'] == 'confirme' ? '<i class="fas fa-check-circle"></i> Confirmé' : '<i class="fas fa-clock"></i> En attente'; ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            
                            <div class="tab-pane fade" id="pending" role="tabpanel">
                                <?php 
                                $pending_rdv = array_filter($prochains_rdv, function($rdv) {
                                    return $rdv['statut'] == 'en_attente';
                                });
                                if(empty($pending_rdv)): ?>
                                    <div class="alert alert-info">Aucun rendez-vous en attente</div>
                                <?php else: ?>
                                    <?php foreach($pending_rdv as $rdv): ?>
                                    <div class="appointment-card en_attente">
                                        <div class="row align-items-center">
                                            <div class="col-md-8">
                                                <h5><?php echo htmlspecialchars($rdv['medecin_nom']); ?></h5>
                                                <p class="text-primary mb-1"><?php echo htmlspecialchars($rdv['specialite_nom']); ?></p>
                                                <p class="small text-secondary">
                                                    <i class="fas fa-calendar-day"></i> <?php echo date('d/m/Y H:i', strtotime($rdv['date_rendez_vous'])); ?>
                                                </p>
                                            </div>
                                            <div class="col-md-4 text-md-end">
                                                <span class="status status-en_attente"><i class="fas fa-clock"></i> En attente de confirmation</span>
                                                <div class="mt-2">
                                                    <button class="btn btn-sm btn-outline-danger" onclick="annulerRendezVous(<?php echo $rdv['id']; ?>)">
                                                        <i class="fas fa-times"></i> Annuler
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            
                            <div class="tab-pane fade" id="confirmed" role="tabpanel">
                                <?php 
                                $confirmed_rdv = array_filter($prochains_rdv, function($rdv) {
                                    return $rdv['statut'] == 'confirme';
                                });
                                if(empty($confirmed_rdv)): ?>
                                    <div class="alert alert-info">Aucun rendez-vous confirmé</div>
                                <?php else: ?>
                                    <?php foreach($confirmed_rdv as $rdv): ?>
                                    <div class="appointment-card confirme">
                                        <div class="row align-items-center">
                                            <div class="col-md-8">
                                                <h5><?php echo htmlspecialchars($rdv['medecin_nom']); ?></h5>
                                                <p class="text-primary mb-1"><?php echo htmlspecialchars($rdv['specialite_nom']); ?></p>
                                                <p class="small text-secondary">
                                                    <i class="fas fa-calendar-day"></i> <?php echo date('d/m/Y H:i', strtotime($rdv['date_rendez_vous'])); ?>
                                                </p>
                                            </div>
                                            <div class="col-md-4 text-md-end">
                                                <span class="status status-confirme"><i class="fas fa-check-circle"></i> Confirmé</span>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Section Historique -->
                <div id="historySection" style="display: none;">
                    <div class="dashboard-card">
                        <h4 class="mb-3"><i class="fas fa-history"></i> Historique des rendez-vous</h4>
                        <?php if(empty($historique_rdv)): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> Aucun historique de rendez-vous.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Médecin</th>
                                            <th>Spécialité</th>
                                            <th>Statut</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($historique_rdv as $rdv): ?>
                                        <tr class="fade-in">
                                            <td><?php echo date('d/m/Y H:i', strtotime($rdv['date_rendez_vous'])); ?></td>
                                            <td><?php echo htmlspecialchars($rdv['medecin_nom']); ?></td>
                                            <td><?php echo htmlspecialchars($rdv['specialite_nom']); ?></td>
                                            <td>
                                                <span class="status status-<?php echo $rdv['statut']; ?>">
                                                    <?php 
                                                    switch($rdv['statut']) {
                                                        case 'confirme': echo '<i class="fas fa-check-circle"></i> Confirmé'; break;
                                                        case 'en_attente': echo '<i class="fas fa-clock"></i> En attente'; break;
                                                        case 'termine': echo '<i class="fas fa-check-double"></i> Terminé'; break;
                                                        case 'annule': echo '<i class="fas fa-times-circle"></i> Annulé'; break;
                                                    }
                                                    ?>
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-info" onclick="voirDetails(<?php echo $rdv['id']; ?>)">
                                                    <i class="fas fa-eye"></i> Détails
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Section Profil -->
                <div id="profileSection" style="display: none;">
                    <div class="dashboard-card">
                        <h4 class="mb-3"><i class="fas fa-user"></i> Mon profil</h4>
                        <form id="profileForm">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nom complet</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($_SESSION['user_nom']); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" value="<?php echo htmlspecialchars($_SESSION['user_email']); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Telephone</label>
                                    <input type="tel" class="form-control" placeholder="77 123 45 67">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Adresse</label>
                                    <input type="text" class="form-control" placeholder="Votre adresse">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-gradient">Mettre à jour</button>
                        </form>
                        
                        <hr class="my-4">
                        
                        <h5>Changer le mot de passe</h5>
                        <form>
                            <div class="mb-3">
                                <label class="form-label">Mot de passe actuel</label>
                                <input type="password" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nouveau mot de passe</label>
                                <input type="password" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Confirmer le mot de passe</label>
                                <input type="password" class="form-control">
                            </div>
                            <button type="submit" class="btn btn-gradient">Changer le mot de passe</button>
                        </form>
                    </div>
                </div>
                
                <!-- Section Notifications -->
                <div id="notificationsSection" style="display: none;">
                    <div class="dashboard-card">
                        <h4 class="mb-3">
                            <i class="fas fa-bell"></i> Mes notifications
                            <?php if($notifications['non_lues'] > 0): ?>
                                <span class="badge-gradient"><?php echo $notifications['non_lues']; ?> non lues</span>
                            <?php endif; ?>
                        </h4>
                        <?php if(empty($liste_notifications)): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> Aucune notification
                            </div>
                        <?php else: ?>
                            <div class="notifications-list">
                                <?php foreach($liste_notifications as $notif): ?>
                                <div class="notification-item <?php echo !$notif['est_lu'] ? 'non-lue' : ''; ?> fade-in">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <p class="mb-1"><?php echo htmlspecialchars($notif['message']); ?></p>
                                            <small class="notification-time">
                                                <i class="far fa-clock"></i> <?php echo date('d/m/Y H:i', strtotime($notif['date_creation'])); ?>
                                            </small>
                                        </div>
                                        <?php if(!$notif['est_lu']): ?>
                                            <span class="badge-gradient">Nouveau</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php if($notifications['non_lues'] > 0): ?>
                            <div class="mt-3 text-center">
                                <button class="btn btn-sm btn-gradient" onclick="marquerToutLu()">
                                    <i class="fas fa-check-double"></i> Tout marquer comme lu
                                </button>
                            </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let bookingData = {
    specialty_id: null,
    specialty_name: null,
    doctor_id: null,
    doctor_name: null,
    doctor_phone: null,
    date: null,
    time: null
};

// Navigation dans le dashboard
document.querySelectorAll('.dashboard-menu a').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        const page = this.dataset.page;
        
        // Cacher toutes les sections
        document.getElementById('dashboardSection').style.display = 'none';
        document.getElementById('bookingSection').style.display = 'none';
        document.getElementById('appointmentsSection').style.display = 'none';
        document.getElementById('historySection').style.display = 'none';
        document.getElementById('profileSection').style.display = 'none';
        document.getElementById('notificationsSection').style.display = 'none';
        
        // Afficher la section correspondante
        if(page === 'dashboard') {
            document.getElementById('dashboardSection').style.display = 'block';
        } else if(page === 'booking') {
            document.getElementById('bookingSection').style.display = 'block';
            resetBooking();
        } else if(page === 'appointments') {
            document.getElementById('appointmentsSection').style.display = 'block';
        } else if(page === 'history') {
            document.getElementById('historySection').style.display = 'block';
        } else if(page === 'profile') {
            document.getElementById('profileSection').style.display = 'block';
        } else if(page === 'notifications') {
            document.getElementById('notificationsSection').style.display = 'block';
        }
        
        // Mettre à jour la classe active
        document.querySelectorAll('.dashboard-menu a').forEach(a => a.classList.remove('active'));
        this.classList.add('active');
    });
});

// Gestion automatique de la prise de rendez-vous
document.querySelectorAll('.specialty-card').forEach(card => {
    card.addEventListener('click', function() {
        // Sélection visuelle
        document.querySelectorAll('.specialty-card').forEach(c => c.classList.remove('selected'));
        this.classList.add('selected');
        
        // Stocker les données
        bookingData.specialty_id = this.dataset.specialtyId;
        bookingData.specialty_name = this.dataset.specialtyName;
        
        // Animation de transition
        this.style.transform = 'scale(0.98)';
        setTimeout(() => {
            this.style.transform = '';
            // Passer automatiquement à l'étape suivante
            goToStep2();
        }, 200);
    });
});

function goToStep2() {
    // Mettre à jour l'indicateur d'étape
    updateStepIndicator(2);
    
    // Cacher l'étape 1 et montrer l'étape 2
    document.getElementById('step1').style.display = 'none';
    document.getElementById('step2').style.display = 'block';
    
    // Charger les médecins pour la spécialité sélectionnée
    loadDoctors(bookingData.specialty_id);
}

function loadDoctors(specialtyId) {
    const doctorsList = document.getElementById('doctorsList');
    
    // Simulation de chargement des médecins - À remplacer par appel AJAX réel
    doctorsList.innerHTML = `
        <div class="text-center">
            <div class="loading-spinner"></div>
            <p class="mt-2">Chargement des médecins...</p>
        </div>
    `;
    
    // Simulation de données (à remplacer par votre API)
    setTimeout(() => {
        const doctors = [
            { id: 1, name: 'Dr. Sophie Martin', experience: '15 ans', rating: 4.8, reviews: 128, phone: '77 123 45 67', available: true },
            { id: 2, name: 'Dr. Jean Dupont', experience: '10 ans', rating: 4.6, reviews: 95, phone: '77 234 56 78', available: true },
            { id: 3, name: 'Dr. Marie Lambert', experience: '12 ans', rating: 4.9, reviews: 156, phone: '77 345 67 89', available: true },
            { id: 4, name: 'Dr. Pierre Dubois', experience: '8 ans', rating: 4.5, reviews: 67, phone: '77 456 78 90', available: false }
        ];
        
        doctorsList.innerHTML = '';
        doctors.forEach(doctor => {
            if (doctor.available) {
                const doctorCard = document.createElement('div');
                doctorCard.className = 'col-md-6';
                doctorCard.innerHTML = `
                    <div class="doctor-card" data-doctor-id="${doctor.id}" data-doctor-name="${doctor.name}" data-doctor-phone="${doctor.phone}">
                        <div class="d-flex align-items-center">
                            <div class="doctor-avatar me-3">
                                <i class="fas fa-user-md"></i>
                            </div>
                            <div>
                                <h5 class="mb-1">${doctor.name}</h5>
                                <p class="text-muted mb-0 small"><i class="fas fa-briefcase"></i> Expérience: ${doctor.experience}</p>
                                <p class="text-muted mb-0 small"><i class="fas fa-star text-warning"></i> ${doctor.rating} (${doctor.reviews} avis)</p>
                                <p class="text-muted mb-0 small"><i class="fas fa-phone"></i> ${doctor.phone}</p>
                            </div>
                        </div>
                    </div>
                `;
                doctorsList.appendChild(doctorCard);
            }
        });
        
        // Ajouter les événements de clic sur les médecins
        document.querySelectorAll('.doctor-card').forEach(card => {
            card.addEventListener('click', function() {
                document.querySelectorAll('.doctor-card').forEach(c => c.classList.remove('selected'));
                this.classList.add('selected');
                
                bookingData.doctor_id = this.dataset.doctorId;
                bookingData.doctor_name = this.dataset.doctorName;
                bookingData.doctor_phone = this.dataset.doctorPhone;
                
                // Animation et passage à l'étape suivante
                this.style.transform = 'scale(0.98)';
                setTimeout(() => {
                    this.style.transform = '';
                    goToStep3();
                }, 200);
            });
        });
    }, 800);
}

function goToStep3() {
    updateStepIndicator(3);
    document.getElementById('step2').style.display = 'none';
    document.getElementById('step3').style.display = 'block';
}

// Gestion de la date
document.getElementById('rdvDate').addEventListener('change', function() {
    if(this.value) {
        bookingData.date = this.value;
        loadTimeSlots(this.value);
    }
});

function loadTimeSlots(date) {
    const timeSlots = document.getElementById('timeSlots');
    
    // Simulation de créneaux horaires disponibles
    const allSlots = ['09:00', '10:00', '11:00', '14:00', '15:00', '16:00', '17:00'];
    const bookedSlots = ['11:00', '15:00']; // Simulation de créneaux déjà réservés
    
    timeSlots.innerHTML = '';
    allSlots.forEach(slot => {
        const isBooked = bookedSlots.includes(slot);
        const slotDiv = document.createElement('div');
        slotDiv.className = `time-slot ${isBooked ? 'unavailable' : ''}`;
        slotDiv.dataset.time = slot;
        slotDiv.textContent = slot;
        
        if (!isBooked) {
            slotDiv.addEventListener('click', function() {
                document.querySelectorAll('.time-slot').forEach(s => s.classList.remove('selected'));
                this.classList.add('selected');
                bookingData.time = this.dataset.time;
                
                // Activer le bouton de confirmation
                document.getElementById('nextToConfirmationBtn').disabled = false;
            });
        }
        
        timeSlots.appendChild(slotDiv);
    });
}

function goToConfirmation() {
    if (!bookingData.date || !bookingData.time) {
        alert('Veuillez sélectionner une date et une heure');
        return;
    }
    
    updateStepIndicator(4);
    document.getElementById('step3').style.display = 'none';
    document.getElementById('step4').style.display = 'block';
    
    // Afficher les détails de confirmation
    const confirmationDiv = document.getElementById('confirmationDetails');
    confirmationDiv.innerHTML = `
        <div class="confirmation-detail">
            <i class="fas fa-stethoscope"></i>
            <strong>Spécialité:</strong>
            <span>${bookingData.specialty_name}</span>
        </div>
        <div class="confirmation-detail">
            <i class="fas fa-user-md"></i>
            <strong>Médecin:</strong>
            <span>${bookingData.doctor_name}</span>
        </div>
        <div class="confirmation-detail">
            <i class="fas fa-phone"></i>
            <strong>Téléphone:</strong>
            <span>${bookingData.doctor_phone}</span>
        </div>
        <div class="confirmation-detail">
            <i class="fas fa-calendar-day"></i>
            <strong>Date:</strong>
            <span>${new Date(bookingData.date).toLocaleDateString('fr-FR', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}</span>
        </div>
        <div class="confirmation-detail">
            <i class="fas fa-clock"></i>
            <strong>Heure:</strong>
            <span>${bookingData.time}</span>
        </div>
    `;
}

function confirmerRendezVous() {
    if (confirm('Confirmez-vous la prise de rendez-vous ?')) {
        // Afficher un indicateur de chargement
        const confirmationDiv = document.getElementById('confirmationContent');
        confirmationDiv.innerHTML = `
            <div class="text-center">
                <div class="loading-spinner"></div>
                <p class="mt-3">Prise de rendez-vous en cours...</p>
            </div>
        `;
        
        // Simulation d'envoi au serveur (à remplacer par appel AJAX réel)
        setTimeout(() => {
            // Succès du rendez-vous
            confirmationDiv.innerHTML = `
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <h3>Rendez-vous confirmé !</h3>
                    <p>Votre rendez-vous a été pris avec succès.</p>
                    <p>Un email de confirmation vous a été envoyé.</p>
                    <button class="btn btn-gradient mt-3" onclick="retourDashboard()">
                        <i class="fas fa-tachometer-alt"></i> Retour au tableau de bord
                    </button>
                </div>
            `;
            
            // Réinitialiser les données après 3 secondes et retourner au dashboard
            setTimeout(() => {
                retourDashboard();
            }, 3000);
        }, 1500);
    }
}

function retourDashboard() {
    resetBooking();
    document.querySelector('[data-page="dashboard"]').click();
}

function previousStep() {
    const currentStep = getCurrentStep();
    if(currentStep > 1) {
        updateStepIndicator(currentStep - 1);
        document.getElementById(`step${currentStep}`).style.display = 'none';
        document.getElementById(`step${currentStep-1}`).style.display = 'block';
    }
}

function getCurrentStep() {
    if(document.getElementById('step1').style.display !== 'none') return 1;
    if(document.getElementById('step2').style.display !== 'none') return 2;
    if(document.getElementById('step3').style.display !== 'none') return 3;
    if(document.getElementById('step4').style.display !== 'none') return 4;
    return 1;
}

function updateStepIndicator(step) {
    document.querySelectorAll('.step').forEach((s, index) => {
        const stepNum = index + 1;
        if(stepNum < step) {
            s.classList.add('completed');
            s.classList.remove('active');
        } else if(stepNum === step) {
            s.classList.add('active');
            s.classList.remove('completed');
        } else {
            s.classList.remove('active', 'completed');
        }
    });
}

function resetBooking() {
    bookingData = {
        specialty_id: null,
        specialty_name: null,
        doctor_id: null,
        doctor_name: null,
        doctor_phone: null,
        date: null,
        time: null
    };
    
    document.getElementById('step1').style.display = 'block';
    document.getElementById('step2').style.display = 'none';
    document.getElementById('step3').style.display = 'none';
    document.getElementById('step4').style.display = 'none';
    
    updateStepIndicator(1);
    
    document.querySelectorAll('.specialty-card').forEach(c => c.classList.remove('selected'));
    document.getElementById('rdvDate').value = '';
    document.getElementById('nextToConfirmationBtn').disabled = true;
}

function annulerRendezVous(id) {
    if(confirm('Voulez-vous vraiment annuler ce rendez-vous ?')) {
        alert('Rendez-vous annulé avec succès');
        location.reload();
    }
}

function voirDetails(id) {
    alert('Affichage des détails du rendez-vous #' + id);
}

function voirDetailsRendezVous(id) {
    alert('Détails complets du rendez-vous #' + id);
}

function marquerToutLu() {
    alert('Toutes les notifications ont été marquées comme lues');
    location.reload();
}
</script>

<?php include 'includes/footer.php'; ?>