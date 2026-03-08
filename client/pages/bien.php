<?php
require_once __DIR__ . '/../../includes/config.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: ' . SITE_URL . '/client/pages/catalogue.php'); exit; }

$stmt = $pdo->prepare("SELECT * FROM biens WHERE id = ?");
$stmt->execute([$id]);
$bien = $stmt->fetch();
if (!$bien) { header('Location: ' . SITE_URL . '/client/pages/catalogue.php'); exit; }

$pageTitle = sanitize($bien['titre']) . " - ImmoAgence";
$photos = getPhotos($bien);
$mainPhoto = !empty($photos) ? getPhotoUrl($photos[0]) : getDefaultPhoto();

// Biens similaires
$stmtSim = $pdo->prepare("SELECT * FROM biens WHERE type=? AND id!=? AND statut='disponible' LIMIT 3");
$stmtSim->execute([$bien['type'], $id]);
$similaires = $stmtSim->fetchAll();

// Traitement formulaire contact
$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['envoyer'])) {
    if (!verifyCsrfToken($_POST['csrf'] ?? '')) {
        $error = "Token de sécurité invalide.";
    } else {
        $nom   = sanitize($_POST['nom'] ?? '');
        $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
        $tel   = sanitize($_POST['telephone'] ?? '');
        $msg   = sanitize($_POST['message'] ?? '');

        if (!$nom || !$email || !$msg) {
            $error = "Veuillez remplir tous les champs obligatoires.";
        } else {
            $stmtD = $pdo->prepare("INSERT INTO demandes (bien_id, client_id, nom_visiteur, email_visiteur, telephone, message) VALUES (?,?,?,?,?,?)");
            $stmtD->execute([$id, $_SESSION['user_id'] ?? null, $nom, $email, $tel, $msg]);
            $success = "Votre demande a bien été envoyée ! Nous vous contacterons très prochainement.";
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
  <!-- Breadcrumb -->
  <nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb" style="font-size:0.85rem;">
      <li class="breadcrumb-item"><a href="<?= SITE_URL ?>/index.php" style="color:var(--primary);">Accueil</a></li>
      <li class="breadcrumb-item"><a href="<?= SITE_URL ?>/client/pages/catalogue.php" style="color:var(--primary);">Biens</a></li>
      <li class="breadcrumb-item active"><?= sanitize($bien['titre']) ?></li>
    </ol>
  </nav>

  <div class="row g-4">
    <!-- Colonne gauche : Galerie + infos -->
    <div class="col-lg-8">
      <!-- Galerie -->
      <div class="detail-gallery mb-4">
        <img id="mainPhoto" src="<?= $mainPhoto ?>" alt="<?= sanitize($bien['titre']) ?>"
             class="main-photo" onerror="this.src='<?= getDefaultPhoto() ?>'">
        <?php if (count($photos) > 1): ?>
          <div class="thumb-photos p-2" style="background:#F8F9FB;">
            <?php foreach ($photos as $i => $photo): ?>
              <img src="<?= getPhotoUrl($photo) ?>" class="thumb-photo <?= $i===0?'active':'' ?>"
                   alt="Photo <?= $i+1 ?>"
                   onerror="this.style.display='none'">
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- Badges -->
      <div class="d-flex gap-2 flex-wrap mb-3">
        <span class="badge-transaction badge-<?= $bien['transaction'] ?> px-3 py-2" style="border-radius:50px; font-size:0.85rem;">
          <?= $bien['transaction']==='vente' ? '🏷 À Vendre' : '🔑 À Louer' ?>
        </span>
        <span class="badge-statut statut-<?= $bien['statut'] ?> px-3 py-2" style="border-radius:50px; font-size:0.85rem;">
          <?= ucfirst($bien['statut']) ?>
        </span>
        <span style="background:rgba(26,60,110,0.08); color:var(--primary); border-radius:50px; padding:0.35rem 1rem; font-size:0.85rem;">
          <i class="bi bi-building me-1"></i><?= ucfirst($bien['type']) ?>
        </span>
      </div>

      <h1 class="section-title mb-2"><?= sanitize($bien['titre']) ?></h1>
      <p style="color:var(--gray); font-size:0.95rem; margin-bottom:1.5rem;">
        <i class="bi bi-geo-alt-fill" style="color:var(--gold);"></i>
        <?= sanitize($bien['localisation'] ?? '') ?><?= $bien['ville'] ? ', ' . sanitize($bien['ville']) : '' ?>
      </p>

      <!-- Caractéristiques -->
      <div class="detail-features mb-4">
        <?php if ($bien['superficie']): ?>
        <div class="feature-item">
          <div class="feature-icon">📐</div>
          <div class="feature-value"><?= $bien['superficie'] ?> m²</div>
          <div class="feature-label">Superficie</div>
        </div>
        <?php endif; ?>
        <?php if ($bien['nb_pieces']): ?>
        <div class="feature-item">
          <div class="feature-icon">🚪</div>
          <div class="feature-value"><?= $bien['nb_pieces'] ?></div>
          <div class="feature-label">Pièces</div>
        </div>
        <?php endif; ?>
        <div class="feature-item">
          <div class="feature-icon">🏙</div>
          <div class="feature-value"><?= sanitize($bien['ville']) ?></div>
          <div class="feature-label">Ville</div>
        </div>
        <div class="feature-item">
          <div class="feature-icon">📅</div>
          <div class="feature-value"><?= date('d/m/Y', strtotime($bien['date_ajout'])) ?></div>
          <div class="feature-label">Publié le</div>
        </div>
      </div>

      <!-- Description -->
      <?php if ($bien['description']): ?>
      <div class="contact-form mb-4">
        <h4 style="font-family:'Playfair Display',serif; margin-bottom:1rem;">Description</h4>
        <p style="color:#374151; line-height:1.8; font-size:0.95rem;"><?= nl2br(sanitize($bien['description'])) ?></p>
      </div>
      <?php endif; ?>
    </div>

    <!-- Colonne droite : Prix + Formulaire -->
    <div class="col-lg-4">
      <div class="detail-info-card">
        <div class="detail-prix mb-1"><?= formatPrix($bien['prix']) ?></div>
        <?php if ($bien['transaction']==='location'): ?>
          <p class="text-muted mb-3" style="font-size:0.85rem;">par mois</p>
        <?php endif; ?>

        <hr style="border-color:#F0F1F3;">

        <h5 style="font-family:'Playfair Display',serif; margin-bottom:1rem;">
          <i class="bi bi-envelope-paper me-2" style="color:var(--gold);"></i>Demande d'information
        </h5>

        <?php if ($success): ?>
          <div class="alert alert-success py-2 px-3" style="font-size:0.88rem; border-radius:var(--radius);">
            <i class="bi bi-check-circle me-2"></i><?= $success ?>
          </div>
        <?php elseif ($error): ?>
          <div class="alert alert-danger py-2 px-3" style="font-size:0.88rem; border-radius:var(--radius);">
            <i class="bi bi-exclamation-triangle me-2"></i><?= $error ?>
          </div>
        <?php endif; ?>

        <form method="POST">
          <input type="hidden" name="csrf" value="<?= generateCsrfToken() ?>">
          <input type="hidden" name="envoyer" value="1">
          <div class="mb-2">
            <input type="text" name="nom" class="form-control form-control-sm" placeholder="Votre nom *"
                   value="<?= isLoggedIn() ? sanitize($_SESSION['prenom'] . ' ' . $_SESSION['nom']) : '' ?>" required>
          </div>
          <div class="mb-2">
            <input type="email" name="email" class="form-control form-control-sm" placeholder="Email *"
                   value="<?= isLoggedIn() ? sanitize($_SESSION['email']) : '' ?>" required>
          </div>
          <div class="mb-2">
            <input type="tel" name="telephone" class="form-control form-control-sm" placeholder="Téléphone">
          </div>
          <div class="mb-3">
            <textarea name="message" class="form-control form-control-sm" rows="4"
                      placeholder="Votre message... *" required>Bonjour, je suis intéressé(e) par ce bien : <?= sanitize($bien['titre']) ?>. Pouvez-vous me contacter ?</textarea>
          </div>
          <button type="submit" class="btn-primary-main">
            <i class="bi bi-send me-2"></i>Envoyer ma demande
          </button>
        </form>

        <div class="mt-3 pt-3 border-top text-center" style="border-color:#F0F1F3 !important;">
          <p style="font-size:0.8rem; color:var(--gray); margin:0;">
            Ou appelez-nous directement<br>
            <a href="tel:+221770000000" style="color:var(--primary); font-weight:700; font-size:1rem;">
              <i class="bi bi-telephone-fill me-1"></i>+221 77 000 00 00
            </a>
          </p>
        </div>
      </div>
    </div>
  </div>

  <!-- BIENS SIMILAIRES -->
  <?php if (!empty($similaires)): ?>
  <div class="mt-5">
    <h3 class="section-title mb-2">Biens similaires</h3>
    <div class="gold-line"></div>
    <div class="row g-4 mt-1">
      <?php foreach ($similaires as $sim): ?>
        <?php
          $simPhotos = getPhotos($sim);
          $simPhoto  = !empty($simPhotos) ? getPhotoUrl($simPhotos[0]) : getDefaultPhoto();
        ?>
        <div class="col-sm-6 col-lg-4">
          <div class="bien-card">
            <div class="bien-card-img">
              <img src="<?= $simPhoto ?>" alt="<?= sanitize($sim['titre']) ?>" loading="lazy">
              <span class="badge-transaction badge-<?= $sim['transaction'] ?>">
                <?= $sim['transaction']==='vente'?'À Vendre':'À Louer' ?>
              </span>
            </div>
            <div class="bien-card-body">
              <div class="bien-titre"><?= sanitize($sim['titre']) ?></div>
              <div class="bien-ville"><i class="bi bi-geo-alt-fill"></i> <?= sanitize($sim['ville']) ?></div>
              <div class="bien-prix"><?= formatPrix($sim['prix']) ?></div>
              <a href="<?= SITE_URL ?>/client/pages/bien.php?id=<?= $sim['id'] ?>" class="btn-voir">Voir le détail</a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
