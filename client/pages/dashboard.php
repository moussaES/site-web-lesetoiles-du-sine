<?php
$pageTitle = "Mon espace - ImmoAgence";
require_once __DIR__ . '/../../includes/config.php';
requireClient();

// Favoris
$stmtFav = $pdo->prepare("
    SELECT b.* FROM biens b
    JOIN favoris f ON f.bien_id = b.id
    WHERE f.client_id = ?
    ORDER BY f.date_ajout DESC
");
$stmtFav->execute([$_SESSION['user_id']]);
$favoris = $stmtFav->fetchAll();

// Demandes
$stmtDem = $pdo->prepare("
    SELECT d.*, b.titre AS bien_titre, b.photo1
    FROM demandes d
    JOIN biens b ON b.id = d.bien_id
    WHERE d.client_id = ?
    ORDER BY d.date_envoi DESC
");
$stmtDem->execute([$_SESSION['user_id']]);
$demandes = $stmtDem->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
  <div class="row mb-4">
    <div class="col">
      <h1 class="section-title">Bonjour, <?= sanitize($_SESSION['prenom']) ?> 👋</h1>
      <div class="gold-line"></div>
    </div>
    <div class="col-auto">
      <a href="<?= SITE_URL ?>/client/logout.php"
         style="color:var(--danger); font-size:0.88rem; font-weight:500;">
        <i class="bi bi-box-arrow-right me-1"></i>Déconnexion
      </a>
    </div>
  </div>

  <!-- PROFIL CLIENT -->
  <div class="row mb-5">
    <div class="col-lg-4">
      <div style="background:white; border-radius:var(--radius-lg); padding:2rem; box-shadow:var(--shadow);">
        <div class="text-center mb-3">
          <div style="width:80px; height:80px; background:linear-gradient(135deg, var(--primary), var(--gold)); border-radius:50%; margin:0 auto 1rem; display:flex; align-items:center; justify-content:center;">
            <i class="bi bi-person-fill" style="font-size:2.5rem; color:white;"></i>
          </div>
          <h5 style="font-weight:700; margin-bottom:0.25rem;"><?= sanitize($_SESSION['prenom'] . ' ' . $_SESSION['nom']) ?></h5>
          <p style="font-size:0.85rem; color:var(--gray); margin-bottom:1rem;"><?= sanitize($_SESSION['email']) ?></p>
        </div>
        <a href="#" class="btn btn-outline-primary btn-sm w-100" data-bs-toggle="modal" data-bs-target="#editProfileModal">
          <i class="bi bi-pencil me-1"></i>Modifier profil
        </a>
      </div>
    </div>
    <div class="col-lg-8">
      <div style="background:white; border-radius:var(--radius-lg); padding:2rem; box-shadow:var(--shadow);">
        <h6 style="font-family:'Playfair Display',serif; margin-bottom:1.5rem;">Informations personnelles</h6>
        <div class="row g-3">
          <div class="col-md-6">
            <label style="font-size:0.8rem; color:var(--gray); font-weight:600; text-transform:uppercase;">Prénom</label>
            <p style="font-weight:500; margin:0.3rem 0 0 0;"><?= sanitize($_SESSION['prenom']) ?></p>
          </div>
          <div class="col-md-6">
            <label style="font-size:0.8rem; color:var(--gray); font-weight:600; text-transform:uppercase;">Nom</label>
            <p style="font-weight:500; margin:0.3rem 0 0 0;"><?= sanitize($_SESSION['nom']) ?></p>
          </div>
          <div class="col-md-6">
            <label style="font-size:0.8rem; color:var(--gray); font-weight:600; text-transform:uppercase;">Email</label>
            <p style="font-weight:500; margin:0.3rem 0 0 0;"><?= sanitize($_SESSION['email']) ?></p>
          </div>
          <div class="col-md-6">
            <label style="font-size:0.8rem; color:var(--gray); font-weight:600; text-transform:uppercase;">Rôle</label>
            <p style="font-weight:500; margin:0.3rem 0 0 0;">
              <span style="background:rgba(16,185,129,0.12); color:#059669; padding:0.3rem 0.6rem; border-radius:4px; font-size:0.85rem;">
                Client
              </span>
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Stats rapides -->
  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
      <div class="stat-card">
        <div class="stat-card-icon gold"><i class="bi bi-heart-fill"></i></div>
        <div>
          <div class="stat-card-value"><?= count($favoris) ?></div>
          <div class="stat-card-label">Favoris</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="stat-card">
        <div class="stat-card-icon blue"><i class="bi bi-envelope-fill"></i></div>
        <div>
          <div class="stat-card-value"><?= count($demandes) ?></div>
          <div class="stat-card-label">Demandes</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="stat-card">
        <div class="stat-card-icon green">
          <i class="bi bi-check-circle-fill"></i>
        </div>
        <div>
          <div class="stat-card-value">
            <?= count(array_filter($demandes, fn($d) => $d['statut']==='traitee')) ?>
          </div>
          <div class="stat-card-label">Traitées</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="stat-card">
        <div class="stat-card-icon red"><i class="bi bi-clock-fill"></i></div>
        <div>
          <div class="stat-card-value">
            <?= count(array_filter($demandes, fn($d) => $d['statut']==='nouvelle')) ?>
          </div>
          <div class="stat-card-label">En attente</div>
        </div>
      </div>
    </div>
  </div>

  <!-- MES FAVORIS -->
  <div class="mb-5">
    <h3 class="section-title mb-2">❤️ Mes favoris</h3>
    <div class="gold-line"></div>
    <?php if (empty($favoris)): ?>
      <div class="text-center py-4" style="background:white; border-radius:var(--radius-lg);">
        <i class="bi bi-heart" style="font-size:3rem; color:#D1D5DB;"></i>
        <p class="text-muted mt-2">Aucun favori enregistré</p>
        <a href="<?= SITE_URL ?>/client/pages/catalogue.php" class="btn-voir px-4" style="display:inline-block;">
          Parcourir les biens
        </a>
      </div>
    <?php else: ?>
      <div class="row g-3">
        <?php foreach ($favoris as $bien): ?>
          <?php $photos = getPhotos($bien); $mp = !empty($photos)?getPhotoUrl($photos[0]):getDefaultPhoto(); ?>
          <div class="col-sm-6 col-lg-4">
            <div class="bien-card">
              <div class="bien-card-img" style="height:160px;">
                <img src="<?= $mp ?>" alt="<?= sanitize($bien['titre']) ?>">
                <span class="badge-transaction badge-<?= $bien['transaction'] ?>">
                  <?= $bien['transaction']==='vente'?'Vente':'Location' ?>
                </span>
              </div>
              <div class="bien-card-body">
                <div class="bien-titre"><?= sanitize($bien['titre']) ?></div>
                <div class="bien-ville"><i class="bi bi-geo-alt-fill"></i> <?= sanitize($bien['ville']) ?></div>
                <div class="bien-prix"><?= formatPrix($bien['prix']) ?></div>
                <a href="<?= SITE_URL ?>/client/pages/bien.php?id=<?= $bien['id'] ?>" class="btn-voir">Voir</a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- MES DEMANDES -->
  <div>
    <h3 class="section-title mb-2">📬 Mes demandes</h3>
    <div class="gold-line"></div>
    <?php if (empty($demandes)): ?>
      <div class="text-center py-4" style="background:white; border-radius:var(--radius-lg);">
        <i class="bi bi-envelope" style="font-size:3rem; color:#D1D5DB;"></i>
        <p class="text-muted mt-2">Aucune demande envoyée</p>
      </div>
    <?php else: ?>
      <div class="admin-table">
        <table class="table">
          <thead>
            <tr>
              <th>Bien</th>
              <th>Date</th>
              <th>Statut</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($demandes as $d): ?>
              <tr>
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <?php if ($d['photo1']): ?>
                      <img src="<?= getPhotoUrl($d['photo1']) ?>" style="width:40px;height:40px;object-fit:cover;border-radius:8px;">
                    <?php endif; ?>
                    <span style="font-weight:500;"><?= sanitize($d['bien_titre']) ?></span>
                  </div>
                </td>
                <td><?= date('d/m/Y', strtotime($d['date_envoi'])) ?></td>
                <td>
                  <span class="badge-statut statut-<?= $d['statut']==='nouvelle'?'disponible':($d['statut']==='traitee'?'reserve':'vendu') ?> px-3 py-1" style="border-radius:50px; font-size:0.75rem;">
                    <?= $d['statut']==='nouvelle'?'🟡 En attente':($d['statut']==='traitee'?'✅ Traitée':'🗃 Archivée') ?>
                  </span>
                </td>
                <td>
                  <a href="<?= SITE_URL ?>/client/pages/bien.php?id=<?= $d['bien_id'] ?>" class="action-btn action-btn-view">
                    <i class="bi bi-eye"></i>
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- MODAL ÉDITION PROFIL -->
<div class="modal fade" id="editProfileModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Modifier mon profil</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <form method="POST" action="<?= SITE_URL ?>/client/pages/update_profile.php">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Prénom</label>
            <input type="text" name="prenom" class="form-control" value="<?= sanitize($_SESSION['prenom']) ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Nom</label>
            <input type="text" name="nom" class="form-control" value="<?= sanitize($_SESSION['nom']) ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?= sanitize($_SESSION['email']) ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Nouveau mot de passe (laissez vide pour ne pas changer)</label>
            <input type="password" name="password" class="form-control" placeholder="••••••••">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
        </div>
      </form>
    </div>
  </div>
</div>

