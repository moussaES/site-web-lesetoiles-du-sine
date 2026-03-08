<?php
require_once __DIR__ . '/../../includes/config.php';
requireAdminOrManager();
$csrf = generateCsrfToken();
$currentPage = basename($_SERVER['PHP_SELF']);

// Compter nouvelles demandes
$nouvelles = $pdo->query("SELECT COUNT(*) FROM demandes WHERE statut='nouvelle'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf" content="<?= $csrf ?>">
  <title><?= $pageTitle ?? 'Admin - ImmoAgence' ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
</head>
<body>

<!-- SIDEBAR -->
<aside class="admin-sidebar">
  <div class="sidebar-brand">
    <div style="font-family:'Playfair Display',serif; font-size:1.3rem; font-weight:700; color:white;">
      Immo<span style="color:var(--gold)">Agence</span>
    </div>
    <div style="font-size:0.72rem; color:rgba(255,255,255,0.4); text-transform:uppercase; letter-spacing:1px; margin-top:0.2rem;">
      Administration
    </div>
  </div>

  <nav style="padding:1rem 0;">
    <!-- Profil utilisateur connecté -->
    <div style="padding:0.75rem 1.5rem; background:rgba(255,255,255,0.05); border-radius:var(--radius); margin:0 1rem 1rem;">
      <div style="display:flex; align-items:center; gap:0.75rem;">
        <div style="width:40px; height:40px; background:rgba(201,168,76,0.2); border-radius:50%; display:flex; align-items:center; justify-content:center; color:var(--gold); font-weight:700; font-size:0.9rem;">
          <?= strtoupper(substr($_SESSION['prenom'],0,1).substr($_SESSION['nom'],0,1)) ?>
        </div>
        <div style="flex:1;">
          <div style="font-size:0.8rem; font-weight:600; color:white;">
            <?= sanitize($_SESSION['prenom'].' '.$_SESSION['nom']) ?>
          </div>
          <div style="font-size:0.7rem; color:rgba(201,168,76,1); text-transform:uppercase;">
            <?= $_SESSION['role']==='gestionnaire'?'Gestionnaire':ucfirst($_SESSION['role']) ?>
          </div>
        </div>
      </div>
    </div>

    <div style="padding:0.5rem 1.5rem; font-size:0.7rem; color:rgba(255,255,255,0.3); text-transform:uppercase; letter-spacing:1.5px; margin-bottom:0.25rem;">
      Principal
    </div>
    <a href="<?= SITE_URL ?>/admin/dashboard.php"
       class="sidebar-nav-link <?= $currentPage==='dashboard.php'?'active':'' ?>">
      <i class="bi bi-grid-fill"></i> Tableau de bord
    </a>
    <a href="<?= SITE_URL ?>/admin/pages/biens.php"
       class="sidebar-nav-link <?= in_array($currentPage,['biens.php','ajouter_bien.php','modifier_bien.php'])?'active':'' ?>">
      <i class="bi bi-houses-fill"></i> Gestion des biens
    </a>
    <a href="<?= SITE_URL ?>/admin/pages/demandes.php"
       class="sidebar-nav-link <?= $currentPage==='demandes.php'?'active':'' ?>">
      <i class="bi bi-envelope-fill"></i> Demandes
      <?php if ($nouvelles > 0): ?>
        <span style="background:var(--danger); color:white; border-radius:50px; padding:0.1rem 0.5rem; font-size:0.7rem; margin-left:auto;"><?= $nouvelles ?></span>
      <?php endif; ?>
    </a>

    <div style="padding:0.5rem 1.5rem; font-size:0.7rem; color:rgba(255,255,255,0.3); text-transform:uppercase; letter-spacing:1.5px; margin:0.75rem 0 0.25rem;">
      Gestion
    </div>
    <?php if (isAdmin()): ?>
    <a href="<?= SITE_URL ?>/admin/pages/utilisateurs.php"
       class="sidebar-nav-link <?= $currentPage==='utilisateurs.php'?'active':'' ?>">
      <i class="bi bi-people-fill"></i> Utilisateurs
    </a>
    <?php endif; ?>

    <div style="border-top:1px solid rgba(255,255,255,0.08); margin:1rem 0;"></div>
    <a href="<?= SITE_URL ?>/index.php" class="sidebar-nav-link" target="_blank">
      <i class="bi bi-box-arrow-up-right"></i> Voir le site
    </a>
    <a href="<?= SITE_URL ?>/client/logout.php" class="sidebar-nav-link" style="color:rgba(239,68,68,0.7);">
      <i class="bi bi-box-arrow-right"></i> Déconnexion
    </a>
  </nav>
</aside>

<!-- CONTENU ADMIN -->
<div class="admin-content">
  <!-- Topbar -->
  <div class="admin-topbar">
    <div class="d-flex align-items-center gap-3">
      <button id="sidebarToggle" class="btn d-lg-none p-1">
        <i class="bi bi-list fs-4"></i>
      </button>
      <h6 style="margin:0; font-weight:600; color:var(--dark);"><?= $pageTitle ?? 'Dashboard' ?></h6>
    </div>
    <div class="d-flex align-items-center gap-3">
      <?php if ($nouvelles > 0): ?>
        <a href="<?= SITE_URL ?>/admin/pages/demandes.php" style="position:relative; color:var(--gray);">
          <i class="bi bi-bell fs-5"></i>
          <span style="position:absolute; top:-4px; right:-4px; background:var(--danger); color:white; border-radius:50%; width:16px; height:16px; font-size:0.65rem; display:flex; align-items:center; justify-content:center;"><?= $nouvelles ?></span>
        </a>
      <?php endif; ?>
      <div style="font-size:0.88rem; color:var(--gray);">
        <i class="bi bi-person-circle me-1"></i><?= sanitize($_SESSION['prenom'] . ' ' . $_SESSION['nom']) ?>
      </div>
    </div>
  </div>

  <div class="admin-page-content">

<?php if (isset($_SESSION['flash'])): ?>
  <div class="alert alert-<?= $_SESSION['flash']['type'] ?> alert-dismissible fade show" style="border-radius:var(--radius);">
    <?= sanitize($_SESSION['flash']['msg']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php unset($_SESSION['flash']); ?>
<?php endif; ?>
