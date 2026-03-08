<?php
$pageTitle = "Tableau de bord";
require_once __DIR__ . '/includes/header.php';

// Stats
$total_biens      = $pdo->query("SELECT COUNT(*) FROM biens")->fetchColumn();
$biens_dispo      = $pdo->query("SELECT COUNT(*) FROM biens WHERE statut='disponible'")->fetchColumn();
$biens_vendus     = $pdo->query("SELECT COUNT(*) FROM biens WHERE statut='vendu'")->fetchColumn();
$total_clients    = $pdo->query("SELECT COUNT(*) FROM utilisateurs WHERE role='client'")->fetchColumn();
$nouvelles_dem    = $pdo->query("SELECT COUNT(*) FROM demandes WHERE statut='nouvelle'")->fetchColumn();
$total_demandes   = $pdo->query("SELECT COUNT(*) FROM demandes")->fetchColumn();

// Dernières demandes
$dernieres_dem = $pdo->query("
    SELECT d.*, b.titre AS bien_titre
    FROM demandes d JOIN biens b ON b.id=d.bien_id
    ORDER BY d.date_envoi DESC LIMIT 5
")->fetchAll();

// Derniers biens
$derniers_biens = $pdo->query("SELECT * FROM biens ORDER BY date_ajout DESC LIMIT 5")->fetchAll();

// Biens par type
$par_type = $pdo->query("SELECT type, COUNT(*) as nb FROM biens GROUP BY type")->fetchAll();
?>

<!-- STATS CARDS -->
<div class="row g-3 mb-4">
  <div class="col-6 col-xl-3">
    <div class="stat-card">
      <div class="stat-card-icon blue"><i class="bi bi-houses-fill"></i></div>
      <div>
        <div class="stat-card-value"><?= $total_biens ?></div>
        <div class="stat-card-label">Total biens</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-xl-3">
    <div class="stat-card">
      <div class="stat-card-icon green"><i class="bi bi-check-circle-fill"></i></div>
      <div>
        <div class="stat-card-value"><?= $biens_dispo ?></div>
        <div class="stat-card-label">Disponibles</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-xl-3">
    <div class="stat-card">
      <div class="stat-card-icon gold"><i class="bi bi-trophy-fill"></i></div>
      <div>
        <div class="stat-card-value"><?= $biens_vendus ?></div>
        <div class="stat-card-label">Vendus/Loués</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-xl-3">
    <div class="stat-card">
      <div class="stat-card-icon red"><i class="bi bi-envelope-exclamation-fill"></i></div>
      <div>
        <div class="stat-card-value"><?= $nouvelles_dem ?></div>
        <div class="stat-card-label">Nouvelles demandes</div>
      </div>
    </div>
  </div>
</div>

<div class="row g-4">
  <!-- DERNIÈRES DEMANDES -->
  <div class="col-lg-7">
    <div class="admin-table">
      <div class="d-flex align-items-center justify-content-between p-3 pb-0">
        <h6 style="font-family:'Playfair Display',serif; margin:0;">Dernières demandes</h6>
        <a href="<?= SITE_URL ?>/admin/pages/demandes.php" style="font-size:0.82rem; color:var(--primary);">Voir tout</a>
      </div>
      <table class="table">
        <thead>
          <tr>
            <th>Client</th>
            <th>Bien</th>
            <th>Date</th>
            <th>Statut</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($dernieres_dem as $d): ?>
            <tr>
              <td>
                <div style="font-weight:500;"><?= sanitize($d['nom_visiteur']) ?></div>
                <div style="font-size:0.78rem; color:var(--gray);"><?= sanitize($d['email_visiteur']) ?></div>
              </td>
              <td style="font-size:0.85rem;"><?= sanitize($d['bien_titre']) ?></td>
              <td style="font-size:0.82rem; color:var(--gray);"><?= date('d/m/Y', strtotime($d['date_envoi'])) ?></td>
              <td>
                <span class="px-2 py-1 rounded-pill" style="font-size:0.72rem; font-weight:600;
                  background:<?= $d['statut']==='nouvelle'?'rgba(245,158,11,0.12)':($d['statut']==='traitee'?'rgba(16,185,129,0.12)':'rgba(107,114,128,0.1)') ?>;
                  color:<?= $d['statut']==='nouvelle'?'#D97706':($d['statut']==='traitee'?'#059669':'#6B7280') ?>;">
                  <?= $d['statut']==='nouvelle'?'Nouvelle':ucfirst($d['statut']) ?>
                </span>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- RÉSUMÉ + ACTIONS RAPIDES -->
  <div class="col-lg-5">
    <!-- Répartition par type -->
    <div style="background:white; border-radius:var(--radius-lg); padding:1.5rem; box-shadow:var(--shadow); margin-bottom:1.5rem;">
      <h6 style="font-family:'Playfair Display',serif; margin-bottom:1.25rem;">Répartition des biens</h6>
      <?php
      $icons = ['maison'=>'🏠','immeuble'=>'🏢','terrain'=>'🗺'];
      foreach ($par_type as $pt):
        $pct = $total_biens > 0 ? round(($pt['nb']/$total_biens)*100) : 0;
      ?>
        <div class="mb-3">
          <div class="d-flex justify-content-between mb-1">
            <span style="font-size:0.85rem;"><?= $icons[$pt['type']] ?> <?= ucfirst($pt['type']) ?></span>
            <span style="font-size:0.82rem; font-weight:600; color:var(--primary);"><?= $pt['nb'] ?> (<?= $pct ?>%)</span>
          </div>
          <div style="background:#F0F1F3; border-radius:4px; height:8px; overflow:hidden;">
            <div style="background:var(--gold); height:100%; width:<?= $pct ?>%; border-radius:4px; transition:width 1s ease;"></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Actions rapides -->
    <div style="background:white; border-radius:var(--radius-lg); padding:1.5rem; box-shadow:var(--shadow);">
      <h6 style="font-family:'Playfair Display',serif; margin-bottom:1rem;">Actions rapides</h6>
      <div class="d-grid gap-2">
        <a href="<?= SITE_URL ?>/admin/pages/ajouter_bien.php"
           style="background:var(--primary); color:white; border-radius:var(--radius); padding:0.75rem; text-align:center; font-weight:600; font-size:0.88rem; transition:all 0.2s;"
           onmouseover="this.style.background='var(--primary-light)'"
           onmouseout="this.style.background='var(--primary)'">
          <i class="bi bi-plus-circle me-2"></i>Ajouter un bien
        </a>
        <a href="<?= SITE_URL ?>/admin/pages/demandes.php?statut=nouvelle"
           style="background:rgba(239,68,68,0.08); color:var(--danger); border-radius:var(--radius); padding:0.75rem; text-align:center; font-weight:600; font-size:0.88rem;">
          <i class="bi bi-envelope-fill me-2"></i>Voir nouvelles demandes (<?= $nouvelles_dem ?>)
        </a>
        <a href="<?= SITE_URL ?>/admin/pages/utilisateurs.php"
           style="background:rgba(26,60,110,0.06); color:var(--primary); border-radius:var(--radius); padding:0.75rem; text-align:center; font-weight:600; font-size:0.88rem;">
          <i class="bi bi-people-fill me-2"></i>Gérer les clients (<?= $total_clients ?>)
        </a>
      </div>
    </div>
  </div>
</div>

<!-- DERNIERS BIENS -->
<div class="admin-table mt-4">
  <div class="d-flex align-items-center justify-content-between p-3 pb-0">
    <h6 style="font-family:'Playfair Display',serif; margin:0;">Derniers biens ajoutés</h6>
    <a href="<?= SITE_URL ?>/admin/pages/biens.php" style="font-size:0.82rem; color:var(--primary);">Voir tout</a>
  </div>
  <table class="table">
    <thead>
      <tr><th>Bien</th><th>Type</th><th>Prix</th><th>Statut</th><th>Date</th><th>Actions</th></tr>
    </thead>
    <tbody>
      <?php foreach ($derniers_biens as $bien): ?>
        <?php $photos = getPhotos($bien); $mp = !empty($photos)?getPhotoUrl($photos[0]):getDefaultPhoto(); ?>
        <tr>
          <td>
            <div class="d-flex align-items-center gap-2">
              <img src="<?= $mp ?>" style="width:40px;height:40px;object-fit:cover;border-radius:8px;"
                   onerror="this.style.display='none'">
              <div>
                <div style="font-weight:500; font-size:0.88rem;"><?= sanitize($bien['titre']) ?></div>
                <div style="font-size:0.75rem; color:var(--gray);"><?= sanitize($bien['ville']) ?></div>
              </div>
            </div>
          </td>
          <td><span style="font-size:0.82rem;"><?= ucfirst($bien['type']) ?></span></td>
          <td style="font-weight:600; font-size:0.85rem; color:var(--primary);"><?= formatPrix($bien['prix']) ?></td>
          <td>
            <select class="select-statut form-select form-select-sm" data-id="<?= $bien['id'] ?>"
                    style="border-radius:8px; font-size:0.78rem; width:auto; border-color:#E5E7EB;">
              <option value="disponible" <?= $bien['statut']==='disponible'?'selected':'' ?>>Disponible</option>
              <option value="reserve"    <?= $bien['statut']==='reserve'?'selected':'' ?>>Réservé</option>
              <option value="vendu"      <?= $bien['statut']==='vendu'?'selected':'' ?>>Vendu</option>
            </select>
          </td>
          <td style="font-size:0.82rem; color:var(--gray);"><?= date('d/m/Y', strtotime($bien['date_ajout'])) ?></td>
          <td>
            <div class="d-flex gap-1">
              <a href="<?= SITE_URL ?>/admin/pages/modifier_bien.php?id=<?= $bien['id'] ?>" class="action-btn action-btn-edit"><i class="bi bi-pencil"></i></a>
              <a href="<?= SITE_URL ?>/client/pages/bien.php?id=<?= $bien['id'] ?>" class="action-btn action-btn-view" target="_blank"><i class="bi bi-eye"></i></a>
              <a href="<?= SITE_URL ?>/admin/pages/biens.php?delete=<?= $bien['id'] ?>" class="action-btn action-btn-delete confirm-delete"><i class="bi bi-trash"></i></a>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
