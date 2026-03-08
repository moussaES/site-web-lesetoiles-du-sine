<?php
$pageTitle = "ImmoAgence - Votre Agence Immobilière";
require_once __DIR__ . '/client/includes/header.php';

// Biens en vedette
$stmt = $pdo->prepare("SELECT * FROM biens WHERE statut='disponible' ORDER BY date_ajout DESC LIMIT 6");
$stmt->execute();
$biens_vedette = $stmt->fetchAll();

// Stats
$total_biens  = $pdo->query("SELECT COUNT(*) FROM biens WHERE statut='disponible'")->fetchColumn();
$total_vendus = $pdo->query("SELECT COUNT(*) FROM biens WHERE statut='vendu'")->fetchColumn();
$total_villes = $pdo->query("SELECT COUNT(DISTINCT ville) FROM biens")->fetchColumn();
?>

<!-- HERO -->
<section class="hero">
  <div class="container position-relative">
    <div class="row align-items-center">
      <div class="col-lg-6 mb-4 mb-lg-0">
        <div style="animation: fadeInUp 0.7s ease both;">
          <p style="color:var(--gold); font-size:0.85rem; text-transform:uppercase; letter-spacing:2px; font-weight:600; margin-bottom:0.5rem;">
            ✦ Agence Immobilière de Confiance
          </p>
          <h1 class="hero-title">
            Trouvez le bien<br>immobilier de vos<br><span>rêves</span>
          </h1>
          <p class="hero-subtitle">
            Maisons, immeubles, terrains — à vendre ou à louer.<br>Des centaines de biens disponibles rien que pour vous.
          </p>
          <div class="d-flex gap-3">
            <a href="<?= SITE_URL ?>/client/pages/catalogue.php?transaction=vente" class="btn-search px-4">
              <i class="bi bi-house me-2"></i>Acheter
            </a>
            <a href="<?= SITE_URL ?>/client/pages/catalogue.php?transaction=location"
               style="border:2px solid rgba(255,255,255,0.3); color:white; border-radius:var(--radius); padding:0.75rem 2rem; font-weight:600; transition:all 0.3s; font-size:0.95rem;"
               onmouseover="this.style.background='rgba(255,255,255,0.1)'"
               onmouseout="this.style.background='transparent'">
              <i class="bi bi-key me-2"></i>Louer
            </a>
          </div>
        </div>
      </div>

      <div class="col-lg-6">
        <!-- Barre de recherche rapide -->
        <div class="search-box">
          <h5 style="font-family:'Playfair Display',serif; color:var(--dark); margin-bottom:1.2rem; font-size:1.1rem;">
            <i class="bi bi-search me-2" style="color:var(--gold)"></i>Recherche rapide
          </h5>
          <form action="<?= SITE_URL ?>/client/pages/catalogue.php" method="GET">
            <div class="row g-2">
              <div class="col-6">
                <label class="filter-label">Type de bien</label>
                <select name="type" class="form-select form-select-sm">
                  <option value="">Tous les types</option>
                  <option value="maison">Maison</option>
                  <option value="immeuble">Immeuble/Appart.</option>
                  <option value="terrain">Terrain</option>
                </select>
              </div>
              <div class="col-6">
                <label class="filter-label">Transaction</label>
                <select name="transaction" class="form-select form-select-sm">
                  <option value="">Vente & Location</option>
                  <option value="vente">À Vendre</option>
                  <option value="location">À Louer</option>
                </select>
              </div>
              <div class="col-12">
                <label class="filter-label">Ville</label>
                <input type="text" name="ville" class="form-control form-control-sm" placeholder="Ex: Dakar, Thiès...">
              </div>
              <div class="col-6">
                <label class="filter-label">Prix max (FCFA)</label>
                <input type="number" name="prix_max" class="form-control form-control-sm" placeholder="Ex: 100000000">
              </div>
              <div class="col-6">
                <label class="filter-label">Superficie min (m²)</label>
                <input type="number" name="superficie_min" class="form-control form-control-sm" placeholder="Ex: 100">
              </div>
              <div class="col-12 mt-1">
                <button type="submit" class="btn-search w-100">
                  <i class="bi bi-search me-2"></i>Rechercher
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

<style>
@keyframes fadeInUp {
  from { opacity:0; transform:translateY(30px); }
  to   { opacity:1; transform:translateY(0); }
}
</style>

<!-- STATS -->
<section class="stats-section">
  <div class="container">
    <div class="row text-center g-3">
      <div class="col-6 col-md-3">
        <div class="stat-item">
          <span class="stat-number" data-target="<?= $total_biens ?>" data-suffix="+"><?= $total_biens ?>+</span>
          <span class="stat-label">Biens disponibles</span>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="stat-item">
          <span class="stat-number" data-target="<?= $total_vendus ?>" data-suffix="+"><?= $total_vendus ?>+</span>
          <span class="stat-label">Biens vendus/loués</span>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="stat-item">
          <span class="stat-number" data-target="<?= $total_villes ?>" data-suffix=""><?= $total_villes ?></span>
          <span class="stat-label">Villes couvertes</span>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="stat-item">
          <span class="stat-number" data-target="10" data-suffix="+">10+</span>
          <span class="stat-label">Années d'expérience</span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- BIENS EN VEDETTE -->
<section class="py-5">
  <div class="container">
    <div class="row align-items-center mb-4">
      <div class="col">
        <p style="color:var(--gold); font-size:0.8rem; text-transform:uppercase; letter-spacing:2px; font-weight:600;">Nos annonces</p>
        <h2 class="section-title">Biens en vedette</h2>
        <div class="gold-line"></div>
      </div>
      <div class="col-auto">
        <a href="<?= SITE_URL ?>/client/pages/catalogue.php" class="btn-voir px-4" style="display:inline-block;">
          Voir tout <i class="bi bi-arrow-right ms-1"></i>
        </a>
      </div>
    </div>

    <div class="row g-4">
      <?php foreach ($biens_vedette as $bien): ?>
        <?php
          $photos = getPhotos($bien);
          $mainPhoto = !empty($photos) ? getPhotoUrl($photos[0]) : getDefaultPhoto();
        ?>
        <div class="col-sm-6 col-lg-4">
          <div class="bien-card">
            <div class="bien-card-img">
              <img src="<?= $mainPhoto ?>" alt="<?= sanitize($bien['titre']) ?>" loading="lazy"
                   onerror="this.src='<?= SITE_URL ?>/assets/images/no-photo.jpg'">
              <span class="badge-transaction badge-<?= $bien['transaction'] ?>">
                <?= $bien['transaction'] === 'vente' ? 'À Vendre' : 'À Louer' ?>
              </span>
              <span class="badge-statut statut-<?= $bien['statut'] ?>">
                <?= ucfirst($bien['statut']) ?>
              </span>
              <button class="btn-favori <?= isLoggedIn() ? '' : '' ?>" data-id="<?= $bien['id'] ?>">🤍</button>
            </div>
            <div class="bien-card-body">
              <div class="bien-type-icon">
                <i class="bi bi-<?= $bien['type']==='terrain' ? 'map' : ($bien['type']==='immeuble' ? 'building' : 'house') ?> me-1"></i>
                <?= ucfirst($bien['type']) ?>
              </div>
              <div class="bien-titre"><?= sanitize($bien['titre']) ?></div>
              <div class="bien-ville"><i class="bi bi-geo-alt-fill"></i> <?= sanitize($bien['localisation'] ?? $bien['ville']) ?></div>
              <div class="bien-details">
                <?php if ($bien['superficie']): ?>
                  <span class="bien-detail-item"><i class="bi bi-rulers"></i><?= $bien['superficie'] ?> m²</span>
                <?php endif; ?>
                <?php if ($bien['nb_pieces']): ?>
                  <span class="bien-detail-item"><i class="bi bi-door-open"></i><?= $bien['nb_pieces'] ?> pièces</span>
                <?php endif; ?>
                <span class="bien-detail-item"><i class="bi bi-calendar3"></i><?= date('M Y', strtotime($bien['date_ajout'])) ?></span>
              </div>
              <div class="d-flex align-items-center justify-content-between">
                <div class="bien-prix"><?= formatPrix($bien['prix']) ?></div>
                <?php if ($bien['transaction'] === 'location'): ?>
                  <small style="color:var(--gray); font-size:0.75rem;">/mois</small>
                <?php endif; ?>
              </div>
              <a href="<?= SITE_URL ?>/client/pages/bien.php?id=<?= $bien['id'] ?>" class="btn-voir">
                Voir le détail <i class="bi bi-arrow-right ms-1"></i>
              </a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- POURQUOI NOUS -->
<section class="py-5" style="background:var(--white);">
  <div class="container">
    <div class="text-center mb-5">
      <p style="color:var(--gold); font-size:0.8rem; text-transform:uppercase; letter-spacing:2px; font-weight:600;">Pourquoi nous choisir</p>
      <h2 class="section-title">Notre engagement</h2>
      <div class="gold-line mx-auto"></div>
    </div>
    <div class="row g-4">
      <?php
      $engagements = [
        ['bi-shield-check', 'Biens vérifiés', 'Chaque bien est vérifié et certifié par notre équipe avant publication.'],
        ['bi-headset', 'Accompagnement', 'Nos conseillers vous accompagnent de la recherche jusqu\'à la signature.'],
        ['bi-clock-history', 'Mise à jour quotidienne', 'Notre catalogue est mis à jour chaque jour avec les nouvelles annonces.'],
        ['bi-star', 'Expertise locale', 'Plus de 10 ans d\'expérience sur le marché immobilier sénégalais.'],
      ];
      foreach ($engagements as $item):
      ?>
      <div class="col-sm-6 col-lg-3">
        <div class="text-center p-4" style="border-radius:var(--radius-lg); border:1.5px solid #F0F1F3; transition:all 0.3s;"
             onmouseover="this.style.borderColor='var(--gold)'; this.style.transform='translateY(-4px)'"
             onmouseout="this.style.borderColor='#F0F1F3'; this.style.transform='translateY(0)'">
          <div style="width:60px; height:60px; background:rgba(201,168,76,0.1); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 1rem;">
            <i class="bi bi-<?= $item[0] ?>" style="font-size:1.5rem; color:var(--gold);"></i>
          </div>
          <h5 style="font-family:'Playfair Display',serif; font-size:1.05rem; margin-bottom:0.5rem;"><?= $item[1] ?></h5>
          <p style="font-size:0.85rem; color:var(--gray); margin:0;"><?= $item[2] ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="py-5" style="background: linear-gradient(135deg, var(--primary), var(--dark));">
  <div class="container text-center">
    <h2 class="section-title" style="color:white; margin-bottom:1rem;">Vous avez un bien à vendre ou à louer ?</h2>
    <p style="color:rgba(255,255,255,0.7); margin-bottom:2rem;">Confiez-nous votre bien, nous nous chargeons de tout.</p>
    <a href="<?= SITE_URL ?>/client/pages/contact.php" class="btn-search px-5 py-3">
      <i class="bi bi-envelope me-2"></i>Nous contacter
    </a>
  </div>
</section>

<?php require_once __DIR__ . '/client/includes/footer.php'; ?>
