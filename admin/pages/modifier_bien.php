<?php
$pageTitle = "Modifier un bien";
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/upload.php';
requireAdminOrManager();

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM biens WHERE id=?");
$stmt->execute([$id]);
$bien = $stmt->fetch();
if (!$bien) { header('Location: ' . SITE_URL . '/admin/pages/biens.php'); exit; }

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf'] ?? '')) {
        $error = "Token de sécurité invalide.";
    } else {
        $titre       = sanitize($_POST['titre'] ?? '');
        $type        = in_array($_POST['type']??'', ['maison','immeuble','terrain']) ? $_POST['type'] : 'maison';
        $transaction = in_array($_POST['transaction']??'', ['vente','location']) ? $_POST['transaction'] : 'vente';
        $prix        = (float)($_POST['prix'] ?? 0);
        $superficie  = (float)($_POST['superficie'] ?? 0) ?: null;
        $nb_pieces   = (int)($_POST['nb_pieces'] ?? 0) ?: null;
        $description = sanitize($_POST['description'] ?? '');
        $localisation= sanitize($_POST['localisation'] ?? '');
        $ville       = sanitize($_POST['ville'] ?? '');
        $statut      = in_array($_POST['statut']??'', ['disponible','reserve','vendu']) ? $_POST['statut'] : 'disponible';

        if (!$titre || !$prix) {
            $error = "Le titre et le prix sont obligatoires.";
        } else {
            // Gérer les photos
            $photoUpdates = [];
            for ($i=1; $i<=10; $i++) {
                $key = 'photo'.$i;
                $deleteKey = 'delete_'.$key;

                if (isset($_POST[$deleteKey]) && !empty($bien[$key])) {
                    deletePhoto($bien[$key]);
                    $photoUpdates[$key] = null;
                } elseif (isset($_FILES[$key]) && $_FILES[$key]['error'] === UPLOAD_ERR_OK) {
                    if (!empty($bien[$key])) deletePhoto($bien[$key]);
                    $uploaded = uploadPhoto($_FILES[$key], 'bien'.$id.'_'.$i);
                    $photoUpdates[$key] = $uploaded ?: $bien[$key];
                } else {
                    $photoUpdates[$key] = $bien[$key];
                }
            }

            $pdo->prepare("
                UPDATE biens SET titre=?,type=?,transaction=?,prix=?,superficie=?,nb_pieces=?,
                description=?,localisation=?,ville=?,statut=?,
                photo1=?,photo2=?,photo3=?,photo4=?,photo5=?,
                photo6=?,photo7=?,photo8=?,photo9=?,photo10=?
                WHERE id=?
            ")->execute([
                $titre,$type,$transaction,$prix,$superficie,$nb_pieces,
                $description,$localisation,$ville,$statut,
                $photoUpdates['photo1'],$photoUpdates['photo2'],$photoUpdates['photo3'],
                $photoUpdates['photo4'],$photoUpdates['photo5'],$photoUpdates['photo6'],
                $photoUpdates['photo7'],$photoUpdates['photo8'],$photoUpdates['photo9'],
                $photoUpdates['photo10'],$id
            ]);

            $_SESSION['flash'] = ['type'=>'success', 'msg'=>'Bien modifié avec succès !'];
            header('Location: ' . SITE_URL . '/admin/pages/biens.php');
            exit;
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
$existingPhotos = getPhotos($bien);
?>

<div class="row justify-content-center">
  <div class="col-xl-10">
    <?php if ($error): ?>
      <div class="alert alert-danger" style="border-radius:var(--radius);">
        <i class="bi bi-exclamation-triangle me-2"></i><?= $error ?>
      </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="csrf" value="<?= generateCsrfToken() ?>">

      <div class="row g-4">
        <div class="col-lg-8">
          <div style="background:white; border-radius:var(--radius-lg); padding:2rem; box-shadow:var(--shadow);">
            <h5 style="font-family:'Playfair Display',serif; margin-bottom:1.5rem; padding-bottom:0.75rem; border-bottom:2px solid var(--gray-light);">
              <i class="bi bi-pencil-square me-2" style="color:var(--gold);"></i>Modifier le bien
            </h5>

            <div class="mb-3">
              <label class="filter-label">Titre *</label>
              <input type="text" name="titre" class="form-control" required value="<?= sanitize($bien['titre']) ?>">
            </div>

            <div class="row g-3 mb-3">
              <div class="col-md-4">
                <label class="filter-label">Type *</label>
                <select name="type" class="form-select">
                  <option value="maison"   <?= $bien['type']==='maison'?'selected':'' ?>>🏠 Maison</option>
                  <option value="immeuble" <?= $bien['type']==='immeuble'?'selected':'' ?>>🏢 Immeuble</option>
                  <option value="terrain"  <?= $bien['type']==='terrain'?'selected':'' ?>>🗺 Terrain</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="filter-label">Transaction *</label>
                <select name="transaction" class="form-select">
                  <option value="vente"    <?= $bien['transaction']==='vente'?'selected':'' ?>>🏷 À Vendre</option>
                  <option value="location" <?= $bien['transaction']==='location'?'selected':'' ?>>🔑 À Louer</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="filter-label">Statut</label>
                <select name="statut" class="form-select">
                  <option value="disponible" <?= $bien['statut']==='disponible'?'selected':'' ?>>✅ Disponible</option>
                  <option value="reserve"    <?= $bien['statut']==='reserve'?'selected':'' ?>>🟡 Réservé</option>
                  <option value="vendu"      <?= $bien['statut']==='vendu'?'selected':'' ?>>🔴 Vendu</option>
                </select>
              </div>
            </div>

            <div class="row g-3 mb-3">
              <div class="col-md-4">
                <label class="filter-label">Prix (FCFA) *</label>
                <input type="number" name="prix" class="form-control" required value="<?= $bien['prix'] ?>">
              </div>
              <div class="col-md-4">
                <label class="filter-label">Superficie (m²)</label>
                <input type="number" name="superficie" class="form-control" step="0.01" value="<?= $bien['superficie'] ?>">
              </div>
              <div class="col-md-4">
                <label class="filter-label">Nb. pièces</label>
                <input type="number" name="nb_pieces" class="form-control" value="<?= $bien['nb_pieces'] ?>">
              </div>
            </div>

            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <label class="filter-label">Localisation</label>
                <input type="text" name="localisation" class="form-control" value="<?= sanitize($bien['localisation']) ?>">
              </div>
              <div class="col-md-6">
                <label class="filter-label">Ville</label>
                <input type="text" name="ville" class="form-control" value="<?= sanitize($bien['ville']) ?>">
              </div>
            </div>

            <div>
              <label class="filter-label">Description</label>
              <textarea name="description" class="form-control" rows="5"><?= sanitize($bien['description']) ?></textarea>
            </div>
          </div>
        </div>

        <div class="col-lg-4">
          <div style="background:white; border-radius:var(--radius-lg); padding:2rem; box-shadow:var(--shadow); margin-bottom:1.5rem;">
            <h5 style="font-family:'Playfair Display',serif; margin-bottom:1.5rem; padding-bottom:0.75rem; border-bottom:2px solid var(--gray-light);">
              <i class="bi bi-images me-2" style="color:var(--gold);"></i>Photos
            </h5>

            <?php for ($i=1; $i<=10; $i++): ?>
              <?php $key = 'photo'.$i; $existing = $bien[$key]; ?>
              <div class="mb-3 p-2 rounded" style="border:1.5px dashed #E5E7EB; background:#FAFAFA;">
                <label class="filter-label" style="color:<?= $i===1?'var(--gold)':'var(--gray)' ?>;">
                  <?= $i===1?'⭐ Photo principale':'Photo '.$i ?>
                </label>
                <?php if ($existing): ?>
                  <div class="d-flex align-items-center gap-2 mb-1">
                    <img src="<?= getPhotoUrl($existing) ?>" style="width:50px;height:50px;object-fit:cover;border-radius:6px;"
                         onerror="this.style.display='none'">
                    <label style="font-size:0.78rem; color:var(--danger); cursor:pointer; display:flex; align-items:center; gap:0.3rem;">
                      <input type="checkbox" name="delete_<?= $key ?>" value="1"> Supprimer
                    </label>
                  </div>
                <?php endif; ?>
                <input type="file" id="photo<?= $i ?>" name="<?= $key ?>" accept="image/jpeg,image/png,image/webp"
                       class="form-control form-control-sm" style="font-size:0.78rem;">
                <img id="preview_photo<?= $i ?>" src="" alt="" style="display:none; width:50px; height:50px; object-fit:cover; border-radius:6px; margin-top:4px;">
              </div>
            <?php endfor; ?>
          </div>

          <div style="background:white; border-radius:var(--radius-lg); padding:1.5rem; box-shadow:var(--shadow);">
            <button type="submit" class="btn-primary-main">
              <i class="bi bi-check-circle me-2"></i>Enregistrer les modifications
            </button>
            <a href="<?= SITE_URL ?>/admin/pages/biens.php"
               style="display:block; text-align:center; margin-top:0.75rem; color:var(--gray); font-size:0.85rem;">
              Annuler
            </a>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
