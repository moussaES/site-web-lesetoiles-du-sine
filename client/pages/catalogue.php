<?php
$pageTitle = "Catalogue des biens - ImmoAgence";
require_once __DIR__ . '/../includes/header.php';

// Filtres depuis GET
$type        = $_GET['type']        ?? '';
$transaction = $_GET['transaction'] ?? '';
$ville       = $_GET['ville']       ?? '';
$prix_min    = isset($_GET['prix_min'])    ? (float)$_GET['prix_min']    : 0;
$prix_max    = isset($_GET['prix_max'])    ? (float)$_GET['prix_max']    : 0;
$superficie_min = isset($_GET['superficie_min']) ? (float)$_GET['superficie_min'] : 0;
$tri         = $_GET['tri'] ?? 'recent';

// Pagination
$par_page = 12;
$page     = max(1, (int)($_GET['page'] ?? 1));
$offset   = ($page - 1) * $par_page;

// Construction requête dynamique
$where = ["statut != 'vendu'"]; // on montre disponible + réservé
$params = [];

if ($type)        { $where[] = "type = ?";        $params[] = $type; }
if ($transaction) { $where[] = "transaction = ?"; $params[] = $transaction; }
if ($ville)       { $where[] = "ville LIKE ?";    $params[] = "%$ville%"; }
if ($prix_min > 0){ $where[] = "prix >= ?";       $params[] = $prix_min; }
if ($prix_max > 0){ $where[] = "prix <= ?";       $params[] = $prix_max; }
if ($superficie_min > 0){ $where[] = "superficie >= ?"; $params[] = $superficie_min; }

$whereSQL = "WHERE " . implode(' AND ', $where);

$orderSQL = match($tri) {
    'prix_asc'  => "ORDER BY prix ASC",
    'prix_desc' => "ORDER BY prix DESC",
    'superficie' => "ORDER BY superficie DESC",
    default     => "ORDER BY date_ajout DESC"
};

// Compter total
$stmtCount = $pdo->prepare("SELECT COUNT(*) FROM biens $whereSQL");
$stmtCount->execute($params);
$total = (int)$stmtCount->fetchColumn();
$total_pages = ceil($total / $par_page);

// Récupérer biens
$stmt = $pdo->prepare("SELECT * FROM biens $whereSQL $orderSQL LIMIT $par_page OFFSET $offset");
$stmt->execute($params);
$biens = $stmt->fetchAll();

// Villes pour filtre
$villes_list = $pdo->query("SELECT DISTINCT ville FROM biens ORDER BY ville")->fetchAll(PDO::FETCH_COLUMN);
?>

<div class="container py-5">
  <!-- Titre -->
  <div class="mb-4">
    <h1 class="section-title">
      <?php if ($transaction === 'vente'): ?>Biens à vendre
      <?php elseif ($transaction === 'location'): ?>Biens à louer
      <?php else: ?>Tous nos biens
      <?php endif; ?>
    </h1>
    <div class="gold-line"></div>
    <p class="text-muted"><strong><?= $total ?></strong> bien<?= $total > 1 ? 's' : '' ?> trouvé<?= $total > 1 ? 's' : '' ?></p>
  </div>

  <!-- FILTRES -->
  <div class="filters-section mb-4">
    <form id="searchForm" action="" method="GET">
      <div class="row g-3 align-items-end">
        <div class="col-6 col-md-2">
          <div class="filter-label">Type</div>
          <select name="type" class="form-select form-select-sm">
            <option value="">Tous</option>
            <option value="maison"   <?= $type==='maison'  ?'selected':'' ?>>Maison</option>
            <option value="immeuble" <?= $type==='immeuble'?'selected':'' ?>>Immeuble</option>
            <option value="terrain"  <?= $type==='terrain' ?'selected':'' ?>>Terrain</option>
          </select>
        </div>
        <div class="col-6 col-md-2">
          <div class="filter-label">Transaction</div>
          <select name="transaction" class="form-select form-select-sm">
            <option value="">Tous</option>
            <option value="vente"    <?= $transaction==='vente'   ?'selected':'' ?>>Vente</option>
            <option value="location" <?= $transaction==='location'?'selected':'' ?>>Location</option>
          </select>
        </div>
        <div class="col-6 col-md-2">
          <div class="filter-label">Ville</div>
          <select name="ville" class="form-select form-select-sm">
            <option value="">Toutes les villes</option>
            <?php foreach ($villes_list as $v): ?>
              <option value="<?= sanitize($v) ?>" <?= $ville===$v?'selected':'' ?>><?= sanitize($v) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-6 col-md-2">
          <div class="filter-label">Prix max (FCFA)</div>
          <input type="number" name="prix_max" class="form-control form-control-sm"
                 placeholder="Illimité" value="<?= $prix_max ?: '' ?>">
        </div>
        <div class="col-6 col-md-2">
          <div class="filter-label">Sup. min (m²)</div>
          <input type="number" name="superficie_min" class="form-control form-control-sm"
                 placeholder="0" value="<?= $superficie_min ?: '' ?>">
        </div>
        <div class="col-6 col-md-1">
          <div class="filter-label">Trier</div>
          <select name="tri" class="form-select form-select-sm">
            <option value="recent"    <?= $tri==='recent'   ?'selected':'' ?>>Récent</option>
            <option value="prix_asc"  <?= $tri==='prix_asc' ?'selected':'' ?>>Prix ↑</option>
            <option value="prix_desc" <?= $tri==='prix_desc'?'selected':'' ?>>Prix ↓</option>
          </select>
        </div>
        <div class="col-6 col-md-1">
          <button type="submit" class="btn-search w-100" style="padding:0.5rem;">
            <i class="bi bi-search"></i>
          </button>
        </div>
      </div>
    </form>
  </div>

  <!-- LISTE BIENS -->
  <?php if (empty($biens)): ?>
    <div class="text-center py-5">
      <i class="bi bi-house-x" style="font-size:4rem; color:#D1D5DB;"></i>
      <h4 class="mt-3" style="color:var(--gray);">Aucun bien trouvé</h4>
      <p class="text-muted">Essayez de modifier vos filtres de recherche.</p>
      <a href="<?= SITE_URL ?>/client/pages/catalogue.php" class="btn-voir px-4" style="display:inline-block;">
        Réinitialiser les filtres
      </a>
    </div>
  <?php else: ?>
    <div class="row g-4">
      <?php foreach ($biens as $bien): ?>
        <?php
          $photos = getPhotos($bien);
          $mainPhoto = !empty($photos) ? getPhotoUrl($photos[0]) : getDefaultPhoto();
        ?>
        <div class="col-sm-6 col-lg-3">
          <div class="bien-card">
            <div class="bien-card-img">
              <img src="<?= $mainPhoto ?>" alt="<?= sanitize($bien['titre']) ?>" loading="lazy"
                   onerror="this.src='<?= SITE_URL ?>/assets/images/no-photo.jpg'">
              <span class="badge-transaction badge-<?= $bien['transaction'] ?>">
                <?= $bien['transaction'] === 'vente' ? 'À Vendre' : 'À Louer' ?>
              </span>
              <span class="badge-statut statut-<?= $bien['statut'] ?>"><?= ucfirst($bien['statut']) ?></span>
              <button class="btn-favori" data-id="<?= $bien['id'] ?>">🤍</button>
            </div>
            <div class="bien-card-body">
              <div class="bien-type-icon">
                <i class="bi bi-<?= $bien['type']==='terrain'?'map':($bien['type']==='immeuble'?'building':'house') ?> me-1"></i>
                <?= ucfirst($bien['type']) ?>
              </div>
              <div class="bien-titre"><?= sanitize($bien['titre']) ?></div>
              <div class="bien-ville"><i class="bi bi-geo-alt-fill"></i> <?= sanitize($bien['localisation'] ?? $bien['ville']) ?></div>
              <div class="bien-details">
                <?php if ($bien['superficie']): ?>
                  <span class="bien-detail-item"><i class="bi bi-rulers"></i><?= $bien['superficie'] ?> m²</span>
                <?php endif; ?>
                <?php if ($bien['nb_pieces']): ?>
                  <span class="bien-detail-item"><i class="bi bi-door-open"></i><?= $bien['nb_pieces'] ?> pces</span>
                <?php endif; ?>
              </div>
              <div class="d-flex align-items-center justify-content-between">
                <div class="bien-prix"><?= formatPrix($bien['prix']) ?></div>
                <?php if ($bien['transaction']==='location'): ?><small class="text-muted">/mois</small><?php endif; ?>
              </div>
              <a href="<?= SITE_URL ?>/client/pages/bien.php?id=<?= $bien['id'] ?>" class="btn-voir">
                Voir le détail <i class="bi bi-arrow-right ms-1"></i>
              </a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- PAGINATION -->
    <?php if ($total_pages > 1): ?>
      <nav class="mt-5 d-flex justify-content-center">
        <ul class="pagination">
          <?php if ($page > 1): ?>
            <li class="page-item">
              <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page-1])) ?>">
                <i class="bi bi-chevron-left"></i>
              </a>
            </li>
          <?php endif; ?>
          <?php for ($i = max(1, $page-2); $i <= min($total_pages, $page+2); $i++): ?>
            <li class="page-item <?= $i===$page?'active':'' ?>">
              <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
            </li>
          <?php endfor; ?>
          <?php if ($page < $total_pages): ?>
            <li class="page-item">
              <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page+1])) ?>">
                <i class="bi bi-chevron-right"></i>
              </a>
            </li>
          <?php endif; ?>
        </ul>
      </nav>
    <?php endif; ?>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
