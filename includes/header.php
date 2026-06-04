<?php
// Fichier: includes/header.php
if (!isset($noHeader)) {
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - <?php echo isset($pageTitle) ? $pageTitle : 'Plateforme de prise de rendez-vous médicaux'; ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand" href="<?php echo SITE_URL; ?>">
                <img src="<?php echo SITE_URL; ?>assets/images/logo2.png" alt="" class="navbar-logo-img">
                <span>Sama Docteur</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>">
                            <i class="fas fa-home"></i> Accueil
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'apropos.php' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>apropos.php">
                            <i class="fas fa-info-circle"></i> À propos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'specialites.php' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>specialites.php">
                            <i class="fas fa-stethoscope"></i> Spécialités
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>contact.php">
                            <i class="fas fa-envelope"></i> Contact
                        </a>
                    </li>
                    
                    <?php if (estConnecte()): ?>
                        <!-- Menu pour les médecins -->
                        <?php if ($_SESSION['user_role'] == 'medecin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo SITE_URL; ?>dashboard-medecin.php">
                                <i class="fas fa-chart-line"></i> Dashboard Médecin
                            </a>
                        </li>
                        <!-- Menu pour les admins -->
                        <?php elseif ($_SESSION['user_role'] == 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo SITE_URL; ?>admin/">
                                <i class="fas fa-crown"></i> Administration
                            </a>
                        </li>
                        <!-- Menu pour les patients -->
                        <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo SITE_URL; ?>dashboard.php">
                                <i class="fas fa-user-md"></i> Mon Espace
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <!-- Lien de déconnexion (à la racine) -->
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo SITE_URL; ?>deconnexion.php">
                                <i class="fas fa-sign-out-alt"></i> Déconnexion
                            </a>
                        </li>
                    <?php else: ?>
                        <!-- Menu pour les utilisateurs non connectés -->
                        <li class="nav-item">
                            <a class="nav-link btn btn-outline-primary ms-lg-2" href="<?php echo SITE_URL; ?>connexion.php">
                                <i class="fas fa-sign-in-alt"></i> Connexion
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn btn-primary ms-lg-2" href="<?php echo SITE_URL; ?>creer-compte.php">
                                <i class="fas fa-user-plus"></i> Créer un compte
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Affichage des messages flash -->
    <?php if (isset($_SESSION['flash_message'])): ?>
    <div class="alert alert-<?php echo $_SESSION['flash_type']; ?> alert-dismissible fade show m-3" role="alert">
        <?php 
        echo $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <!-- Affichage des messages flash (nouveau système) -->
    <?php if (isset($_SESSION['flash'])): ?>
    <div class="alert alert-<?php echo $_SESSION['flash']['type']; ?> alert-dismissible fade show m-3" role="alert">
        <i class="fas <?php echo $_SESSION['flash']['type'] == 'success' ? 'fa-check-circle' : 'fa-info-circle'; ?> me-2"></i>
        <?php 
        echo $_SESSION['flash']['message'];
        unset($_SESSION['flash']);
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <main>
<?php
}
?>