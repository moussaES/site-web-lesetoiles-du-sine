<?php
$pageTitle = "Gestion des biens";
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/upload.php';
requireAdminOrManager();

// Suppression
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $bien = $pdo->prepare("SELECT * FROM biens WHERE id=?");
    $bien->execute([$id]);
    $b = $bien->fetch();
    if ($b) {
        // Supprimer toutes les photos
        for ($i=1; $i<=10; $i++) {
            if (!empty($b['photo'.$i])) deletePhoto($b['photo'.$i]);
        }
        $pdo->prepare("DELETE FROM biens WHERE id=?")->execute([$id]);
        $_SESSION['flash'] = ['type'=>'success', 'msg'=>'Bien supprimé avec succès.'];
    }
    header('Location: ' . SITE_URL . '/admin/pages/biens.php');
    exit;
}

// Filtres
$search      = sanitize($_GET['q'] ?? '');
$type        = $_GET['type'] ?? '';
$transaction = $_GET['transaction'] ?? '';
$statut      = $_GET['statut'] ?? '';

$where = ["1=1"];
$params = [];
if ($search)      { $where[] = "(titre LIKE ? OR ville LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
if ($type)        { $where[] = "type=?";        $params[] = $type; }
if ($transaction) { $where[] = "transaction=?"; $params[] = $transaction; }
if ($statut)      { $where[] = "statut=?";      $params[] = $statut; }

$whereSQL = "WHERE " . implode(' AND ', $where);

$par_page = 12;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page-1) * $par_page;

$total = $pdo->prepare("SELECT COUNT(*) FROM biens $whereSQL");
$total->execute($params);
$total = (int)$total->fetchColumn();
$total_pages = ceil($total / $par_page);

$stmt = $pdo->prepare("SELECT * FROM biens $whereSQL ORDER BY date_ajout DESC LIMIT $par_page OFFSET $offset");
$stmt->execute($params);
$biens = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <p style="color:var(--gray); font-size:0.88rem; margin:0;"><?= $total ?> bien<?= $total>1?'s':'' ?> trouvé<?= $total>1?'s':'' ?></p>
  </div>
  <a href="<?= SITE_URL ?>/admin/pages/ajouter_bien.php"
     style="background:var(--primary); color:white; border-radius:var(--radius); padding:0.65rem 1.5rem; font-weight:600; font-size:0.88rem; display:inline-flex; align-items:center; gap:0.5rem;">
    <i class="bi bi-plus-circle"></i>Ajouter un bien
  </a>
</div>

<!-- Filtres -->
<div class="filters-section mb-4">
  <form method="GET" id="searchForm">
    <div class="row g-2 align-items-end">
      <div class="col-md-3">
        <div class="filter-label">Recherche</div>
        <input type="text" name="q" class="form-control form-control-sm" placeholder="Titre, ville..."
               value="<?= sanitize($search) ?>">
      </div>
      <div class="col-md-2">
        <div class="filter-label">Type</div>
        <select name="type" class="form-select form-select-sm">
          <option value="">Tous</option>
          <option value="maison"   <?= $type==='maison'?'selected':'' ?>>Maison</option>
          <option value="immeuble" <?= $type==='immeuble'?'selected':'' ?>>Immeuble</option>
          <option value="terrain"  <?= $type==='terrain'?'selected':'' ?>>Terrain</option>
        </select>
      </div>
      <div class="col-md-2">
        <div class="filter-label">Transaction</div>
        <select name="transaction" class="form-select form-select-sm">
          <option value="">Tous</option>
          <option value="vente"    <?= $transaction==='vente'?'selected':'' ?>>Vente</option>
          <option value="location" <?= $transaction==='location'?'selected':'' ?>>Location</option>
        </select>
      </div>
      <div class="col-md-2">
        <div class="filter-label">Statut</div>
        <select name="statut" class="form-select form-select-sm">
          <option value="">Tous</option>
          <option value="disponible" <?= $statut==='disponible'?'selected':'' ?>>Disponible</option>
          <option value="reserve"    <?= $statut==='reserve'?'selected':'' ?>>Réservé</option>
          <option value="vendu"      <?= $statut==='vendu'?'selected':'' ?>>Vendu</option>
        </select>
      </div>
      <div class="col-md-2">
        <button type="submit" class="btn-search w-100" style="padding:0.5rem;">
          <i class="bi bi-search me-1"></i>Filtrer
        </button>
      </div>
      <div class="col-md-1">
        <a href="<?= SITE_URL ?>/admin/pages/biens.php" style="color:var(--gray); font-size:0.82rem; display:block; text-align:center; padding:0.5rem;">
          Réinitialiser
        </a>
      </div>
    </div>
  </form>
</div>

<!-- TABLE -->
<div class="admin-table">
  <table class="table">
    <thead>
      <tr>
        <th>Bien</th>
        <th>Type</th>
        <th>Transaction</th>
        <th>Prix</th>
        <th>Superficie</th>
        <th>Statut</th>
        <th>Date</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($biens)): ?>
        <tr>
          <td colspan="8" class="text-center py-4 text-muted">Aucun bien trouvé</td>
        </tr>
      <?php else: ?>
        <?php foreach ($biens as $bien): ?>
          <?php $photos = getPhotos($bien); $mp = !empty($photos)?getPhotoUrl($photos[0]):getDefaultPhoto(); ?>
          <tr>
            <td>
              <div class="d-flex align-items-center gap-2">
                <img src="<?= $mp ?>" style="width:48px;height:48px;object-fit:cover;border-radius:8px;"
                     onerror="this.style.display='none'">
                <div>
                  <div style="font-weight:600; font-size:0.88rem;"><?= sanitize($bien['titre']) ?></div>
                  <div style="font-size:0.75rem; color:var(--gray);">
                    <i class="bi bi-geo-alt" style="color:var(--gold)"></i> <?= sanitize($bien['ville']) ?>
                  </div>
                </div>
              </div>
            </td>
            <td><span style="font-size:0.82rem;"><?= ucfirst($bien['type']) ?></span></td>
            <td>
              <span class="badge-transaction badge-<?= $bien['transaction'] ?>" style="font-size:0.72rem; border-radius:50px; padding:0.25rem 0.75rem;">
                <?= $bien['transaction']==='vente'?'Vente':'Location' ?>
              </span>
            </td>
            <td style="font-weight:600; font-size:0.85rem; color:var(--primary);"><?= formatPrix($bien['prix']) ?></td>
            <td style="font-size:0.85rem;"><?= $bien['superficie'] ? $bien['superficie'].' m²' : '—' ?></td>
            <td>
              <select class="select-statut form-select form-select-sm" data-id="<?= $bien['id'] ?>"
                      style="border-radius:8px; font-size:0.78rem; width:120px; border-color:#E5E7EB;">
                <option value="disponible" <?= $bien['statut']==='disponible'?'selected':'' ?>>✅ Disponible</option>
                <option value="reserve"    <?= $bien['statut']==='reserve'?'selected':'' ?>>🟡 Réservé</option>
                <option value="vendu"      <?= $bien['statut']==='vendu'?'selected':'' ?>>🔴 Vendu</option>
              </select>
            </td>
            <td style="font-size:0.8rem; color:var(--gray);"><?= date('d/m/Y', strtotime($bien['date_ajout'])) ?></td>
            <td>
              <div class="d-flex gap-1">
                <a href="<?= SITE_URL ?>/admin/pages/modifier_bien.php?id=<?= $bien['id'] ?>" class="action-btn action-btn-edit" title="Modifier">
                  <i class="bi bi-pencil"></i>
                </a>
                <a href="<?= SITE_URL ?>/client/pages/bien.php?id=<?= $bien['id'] ?>" class="action-btn action-btn-view" title="Voir" target="_blank">
                  <i class="bi bi-eye"></i>
                </a>
                <a href="?delete=<?= $bien['id'] ?>" class="action-btn action-btn-delete confirm-delete" title="Supprimer">
                  <i class="bi bi-trash"></i>
                </a>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- PAGINATION -->
<?php if ($total_pages > 1): ?>
  <nav class="mt-4 d-flex justify-content-center">
    <ul class="pagination">
      <?php if ($page > 1): ?>
        <li class="page-item">
          <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page'=>$page-1])) ?>">
            <i class="bi bi-chevron-left"></i>
          </a>
        </li>
      <?php endif; ?>
      <?php for ($i=max(1,$page-2); $i<=min($total_pages,$page+2); $i++): ?>
        <li class="page-item <?= $i===$page?'active':'' ?>">
          <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page'=>$i])) ?>"><?= $i ?></a>
        </li>
      <?php endfor; ?>
      <?php if ($page < $total_pages): ?>
        <li class="page-item">
          <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page'=>$page+1])) ?>">
            <i class="bi bi-chevron-right"></i>
          </a>
        </li>
      <?php endif; ?>
    </ul>
  </nav>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
