<?php
require_once __DIR__ . '/../../includes/config.php';
$csrf = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf" content="<?= $csrf ?>">
  <title><?= $pageTitle ?? SITE_NAME . ' - Les etoiles du Sine Immo' ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar-main">
  <div class="container">
    <div class="d-flex align-items-center justify-content-between">
      <a href="<?= SITE_URL ?>/index.php" class="navbar-brand-text">
        Les etoiles du Sine<span>Immo</span>
      </a>

      <!-- Desktop Nav -->
      <div class="d-none d-lg-flex align-items-center gap-1">
        <a href="<?= SITE_URL ?>/index.php" class="nav-link-main <?= (basename($_SERVER['PHP_SELF'])=='index.php')?'active':'' ?>">Accueil</a>
        <a href="<?= SITE_URL ?>/client/pages/catalogue.php" class="nav-link-main <?= (basename($_SERVER['PHP_SELF'])=='catalogue.php')?'active':'' ?>">Biens</a>
        <a href="<?= SITE_URL ?>/client/pages/catalogue.php?transaction=vente" class="nav-link-main">À Vendre</a>
        <a href="<?= SITE_URL ?>/client/pages/catalogue.php?transaction=location" class="nav-link-main">À Louer</a>
        <a href="<?= SITE_URL ?>/client/pages/contact.php" class="nav-link-main">Contact</a>
      </div>

      <div class="d-none d-lg-flex align-items-center gap-2">
        <?php if (isLoggedIn()): ?>
          <a href="<?= SITE_URL ?>/client/pages/dashboard.php" class="nav-link-main">
            <i class="bi bi-person-circle me-1"></i><?= sanitize($_SESSION['prenom']) ?>
          </a>
          <a href="<?= SITE_URL ?>/client/logout.php" class="nav-link-main text-danger">
            <i class="bi bi-box-arrow-right"></i>
          </a>
        <?php else: ?>
          <a href="<?= SITE_URL ?>/client/pages/register.php" class="nav-link-main">S'inscrire</a>
          <a href="<?= SITE_URL ?>/client/login.php" class="btn-nav-login nav-link-main">Se connecter</a>
        <?php endif; ?>
      </div>

      <!-- Mobile toggle -->
      <button class="btn d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#mobileNav">
        <i class="bi bi-list fs-4"></i>
      </button>
    </div>

    <!-- Mobile Nav -->
    <div class="collapse d-lg-none" id="mobileNav">
      <div class="py-2 border-top mt-2">
        <a href="<?= SITE_URL ?>/index.php" class="d-block nav-link-main py-2">Accueil</a>
        <a href="<?= SITE_URL ?>/client/pages/catalogue.php" class="d-block nav-link-main py-2">Tous les biens</a>
        <a href="<?= SITE_URL ?>/client/pages/catalogue.php?transaction=vente" class="d-block nav-link-main py-2">À Vendre</a>
        <a href="<?= SITE_URL ?>/client/pages/catalogue.php?transaction=location" class="d-block nav-link-main py-2">À Louer</a>
        <?php if (isLoggedIn()): ?>
          <a href="<?= SITE_URL ?>/client/pages/dashboard.php" class="d-block nav-link-main py-2">Mon espace</a>
          <a href="<?= SITE_URL ?>/client/logout.php" class="d-block nav-link-main py-2 text-danger">Déconnexion</a>
        <?php else: ?>
          <a href="<?= SITE_URL ?>/client/login.php" class="d-block nav-link-main py-2">Connexion</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>

<?php if (isset($_SESSION['flash'])): ?>
  <div class="flash-msg alert alert-<?= $_SESSION['flash']['type'] ?> shadow">
    <?= sanitize($_SESSION['flash']['msg']) ?>
  </div>
  <?php unset($_SESSION['flash']); ?>
<?php endif; ?>
