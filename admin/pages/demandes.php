<?php
$pageTitle = "Gestion des demandes";
require_once __DIR__ . '/../../includes/config.php';
requireAdminOrManager();

// Changer statut via GET (rapide)
if (isset($_GET['statut'], $_GET['id'])) {
    $newStatut = in_array($_GET['statut'], ['nouvelle','traitee','archivee']) ? $_GET['statut'] : null;
    if ($newStatut) {
        $pdo->prepare("UPDATE demandes SET statut=? WHERE id=?")->execute([$newStatut, (int)$_GET['id']]);
        $_SESSION['flash'] = ['type'=>'success', 'msg'=>'Statut mis à jour.'];
    }
    header('Location: ' . SITE_URL . '/admin/pages/demandes.php');
    exit;
}

$filtre = $_GET['statut_filtre'] ?? '';
$where = $filtre ? "WHERE d.statut='$filtre'" : '';

$demandes = $pdo->query("
    SELECT d.*, b.titre AS bien_titre, b.type AS bien_type, b.prix AS bien_prix, b.photo1
    FROM demandes d
    JOIN biens b ON b.id = d.bien_id
    $where
    ORDER BY d.date_envoi DESC
")->fetchAll();

// Stats
$stats = $pdo->query("
    SELECT statut, COUNT(*) as nb FROM demandes GROUP BY statut
")->fetchAll(PDO::FETCH_KEY_PAIR);

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Stats -->
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="stat-card-icon gold"><i class="bi bi-envelope-fill"></i></div>
      <div>
        <div class="stat-card-value"><?= $stats['nouvelle'] ?? 0 ?></div>
        <div class="stat-card-label">Nouvelles</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="stat-card-icon green"><i class="bi bi-check-circle-fill"></i></div>
      <div>
        <div class="stat-card-value"><?= $stats['traitee'] ?? 0 ?></div>
        <div class="stat-card-label">Traitées</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="stat-card-icon blue"><i class="bi bi-archive-fill"></i></div>
      <div>
        <div class="stat-card-value"><?= $stats['archivee'] ?? 0 ?></div>
        <div class="stat-card-label">Archivées</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="stat-card-icon red"><i class="bi bi-graph-up"></i></div>
      <div>
        <div class="stat-card-value"><?= array_sum($stats) ?></div>
        <div class="stat-card-label">Total</div>
      </div>
    </div>
  </div>
</div>

<!-- Filtres -->
<div class="d-flex gap-2 mb-4 flex-wrap">
  <a href="?statut_filtre=" style="padding:0.5rem 1.25rem; border-radius:50px; font-size:0.85rem; font-weight:600; border:1.5px solid <?= !$filtre?'var(--primary)':'#E5E7EB' ?>; color:<?= !$filtre?'var(--primary)':'var(--gray)' ?>; background:<?= !$filtre?'rgba(26,60,110,0.06)':'white' ?>;">
    Toutes (<?= array_sum($stats) ?>)
  </a>
  <a href="?statut_filtre=nouvelle" style="padding:0.5rem 1.25rem; border-radius:50px; font-size:0.85rem; font-weight:600; border:1.5px solid <?= $filtre==='nouvelle'?'var(--warning)':'#E5E7EB' ?>; color:<?= $filtre==='nouvelle'?'#D97706':'var(--gray)' ?>; background:<?= $filtre==='nouvelle'?'rgba(245,158,11,0.08)':'white' ?>;">
    🟡 Nouvelles (<?= $stats['nouvelle'] ?? 0 ?>)
  </a>
  <a href="?statut_filtre=traitee" style="padding:0.5rem 1.25rem; border-radius:50px; font-size:0.85rem; font-weight:600; border:1.5px solid <?= $filtre==='traitee'?'var(--success)':'#E5E7EB' ?>; color:<?= $filtre==='traitee'?'#059669':'var(--gray)' ?>; background:<?= $filtre==='traitee'?'rgba(16,185,129,0.08)':'white' ?>;">
    ✅ Traitées (<?= $stats['traitee'] ?? 0 ?>)
  </a>
  <a href="?statut_filtre=archivee" style="padding:0.5rem 1.25rem; border-radius:50px; font-size:0.85rem; font-weight:600; border:1.5px solid <?= $filtre==='archivee'?'#9CA3AF':'#E5E7EB' ?>; color:var(--gray); background:<?= $filtre==='archivee'?'rgba(107,114,128,0.06)':'white' ?>;">
    🗃 Archivées (<?= $stats['archivee'] ?? 0 ?>)
  </a>
</div>

<!-- Table -->
<div class="admin-table">
  <table class="table">
    <thead>
      <tr>
        <th>Client</th>
        <th>Bien concerné</th>
        <th>Message</th>
        <th>Date</th>
        <th>Statut</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($demandes)): ?>
        <tr>
          <td colspan="6" class="text-center py-4 text-muted">Aucune demande trouvée</td>
        </tr>
      <?php else: ?>
        <?php foreach ($demandes as $d): ?>
          <tr style="<?= $d['statut']==='nouvelle'?'background:rgba(245,158,11,0.02)':'' ?>">
            <td>
              <div style="font-weight:600; font-size:0.88rem;"><?= sanitize($d['nom_visiteur']) ?></div>
              <div style="font-size:0.78rem; color:var(--gray);"><?= sanitize($d['email_visiteur']) ?></div>
              <?php if ($d['telephone']): ?>
                <div style="font-size:0.78rem; color:var(--primary);">
                  <i class="bi bi-telephone"></i> <?= sanitize($d['telephone']) ?>
                </div>
              <?php endif; ?>
            </td>
            <td>
              <div class="d-flex align-items-center gap-2">
                <?php if ($d['photo1']): ?>
                  <img src="<?= getPhotoUrl($d['photo1']) ?>" style="width:40px;height:40px;object-fit:cover;border-radius:6px;"
                       onerror="this.style.display='none'">
                <?php endif; ?>
                <div>
                  <div style="font-weight:500; font-size:0.85rem;"><?= sanitize($d['bien_titre']) ?></div>
                  <div style="font-size:0.75rem; color:var(--primary); font-weight:600;"><?= formatPrix($d['bien_prix']) ?></div>
                </div>
              </div>
            </td>
            <td>
              <div style="font-size:0.82rem; color:#374151; max-width:200px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"
                   title="<?= sanitize($d['message']) ?>">
                <?= sanitize($d['message']) ?>
              </div>
            </td>
            <td style="font-size:0.82rem; color:var(--gray); white-space:nowrap;">
              <?= date('d/m/Y à H:i', strtotime($d['date_envoi'])) ?>
            </td>
            <td>
              <?php
              $colors = ['nouvelle'=>['bg'=>'rgba(245,158,11,0.12)','c'=>'#D97706'], 'traitee'=>['bg'=>'rgba(16,185,129,0.12)','c'=>'#059669'], 'archivee'=>['bg'=>'rgba(107,114,128,0.1)','c'=>'#6B7280']];
              $c = $colors[$d['statut']];
              ?>
              <span style="background:<?= $c['bg'] ?>; color:<?= $c['c'] ?>; border-radius:50px; padding:0.3rem 0.85rem; font-size:0.75rem; font-weight:700;">
                <?= $d['statut']==='nouvelle'?'🟡 Nouvelle':($d['statut']==='traitee'?'✅ Traitée':'🗃 Archivée') ?>
              </span>
            </td>
            <td>
              <div class="d-flex gap-1 flex-wrap">
                <?php if ($d['statut'] !== 'traitee'): ?>
                  <a href="?id=<?= $d['id'] ?>&statut=traitee" class="action-btn action-btn-view" title="Marquer traitée">
                    <i class="bi bi-check-lg"></i>
                  </a>
                <?php endif; ?>
                <?php if ($d['statut'] !== 'archivee'): ?>
                  <a href="?id=<?= $d['id'] ?>&statut=archivee" class="action-btn" title="Archiver"
                     style="background:rgba(107,114,128,0.1); color:var(--gray);">
                    <i class="bi bi-archive"></i>
                  </a>
                <?php endif; ?>
                <a href="mailto:<?= sanitize($d['email_visiteur']) ?>?subject=Réponse à votre demande - <?= sanitize($d['bien_titre']) ?>"
                   class="action-btn action-btn-edit" title="Répondre par email">
                  <i class="bi bi-reply"></i>
                </a>
                <a href="<?= SITE_URL ?>/client/pages/bien.php?id=<?= $d['bien_id'] ?>"
                   class="action-btn" style="background:rgba(26,60,110,0.08); color:var(--primary);" target="_blank" title="Voir le bien">
                  <i class="bi bi-house"></i>
                </a>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
