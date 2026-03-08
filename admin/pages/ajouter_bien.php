<?php
$pageTitle = "Ajouter un bien";
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/upload.php';
requireAdminOrManager();

$error = '';
$bien = [
    'titre'=>'','type'=>'maison','transaction'=>'vente','prix'=>'',
    'superficie'=>'','nb_pieces'=>'','description'=>'','localisation'=>'',
    'ville'=>'','statut'=>'disponible'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf'] ?? '')) {
        $error = "Token de sécurité invalide.";
    } else {
        $bien['titre']       = sanitize($_POST['titre'] ?? '');
        $bien['type']        = in_array($_POST['type']??'', ['maison','immeuble','terrain']) ? $_POST['type'] : 'maison';
        $bien['transaction'] = in_array($_POST['transaction']??'', ['vente','location']) ? $_POST['transaction'] : 'vente';
        $bien['prix']        = (float)($_POST['prix'] ?? 0);
        $bien['superficie']  = (float)($_POST['superficie'] ?? 0) ?: null;
        $bien['nb_pieces']   = (int)($_POST['nb_pieces'] ?? 0) ?: null;
        $bien['description'] = sanitize($_POST['description'] ?? '');
        $bien['localisation']= sanitize($_POST['localisation'] ?? '');
        $bien['ville']       = sanitize($_POST['ville'] ?? '');
        $bien['statut']      = in_array($_POST['statut']??'', ['disponible','reserve','vendu']) ? $_POST['statut'] : 'disponible';

        if (!$bien['titre'] || !$bien['prix']) {
            $error = "Le titre et le prix sont obligatoires.";
        } else {
            // Insérer le bien d'abord pour avoir l'ID
            $stmt = $pdo->prepare("
                INSERT INTO biens (titre,type,transaction,prix,superficie,nb_pieces,description,localisation,ville,statut,admin_id)
                VALUES (?,?,?,?,?,?,?,?,?,?,?)
            ");
            $stmt->execute([
                $bien['titre'], $bien['type'], $bien['transaction'], $bien['prix'],
                $bien['superficie'], $bien['nb_pieces'], $bien['description'],
                $bien['localisation'], $bien['ville'], $bien['statut'], $_SESSION['user_id']
            ]);
            $bienId = $pdo->lastInsertId();

            // Upload photos
            $photos = [];
            for ($i=1; $i<=10; $i++) {
                $key = 'photo'.$i;
                if (isset($_FILES[$key]) && $_FILES[$key]['error'] === UPLOAD_ERR_OK) {
                    $uploaded = uploadPhoto($_FILES[$key], 'bien'.$bienId.'_'.$i);
                    $photos[$key] = $uploaded ?: null;
                } else {
                    $photos[$key] = null;
                }
            }

            // Update avec photos
            $pdo->prepare("
                UPDATE biens SET photo1=?,photo2=?,photo3=?,photo4=?,photo5=?,
                photo6=?,photo7=?,photo8=?,photo9=?,photo10=? WHERE id=?
            ")->execute([
                $photos['photo1'],$photos['photo2'],$photos['photo3'],$photos['photo4'],$photos['photo5'],
                $photos['photo6'],$photos['photo7'],$photos['photo8'],$photos['photo9'],$photos['photo10'],
                $bienId
            ]);

            $_SESSION['flash'] = ['type'=>'success', 'msg'=>'Bien ajouté avec succès !'];
            header('Location: ' . SITE_URL . '/admin/pages/biens.php');
            exit;
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
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
        <!-- Infos principales -->
        <div class="col-lg-8">
          <div style="background:white; border-radius:var(--radius-lg); padding:2rem; box-shadow:var(--shadow);">
            <h5 style="font-family:'Playfair Display',serif; margin-bottom:1.5rem; padding-bottom:0.75rem; border-bottom:2px solid var(--gray-light);">
              <i class="bi bi-info-circle me-2" style="color:var(--gold);"></i>Informations du bien
            </h5>

            <div class="mb-3">
              <label class="filter-label">Titre du bien *</label>
              <input type="text" name="titre" class="form-control" required
                     value="<?= sanitize($bien['titre']) ?>" placeholder="Ex: Villa moderne 4 chambres à Almadies">
            </div>

            <div class="row g-3 mb-3">
              <div class="col-md-4">
                <label class="filter-label">Type de bien *</label>
                <select name="type" class="form-select">
                  <option value="maison"   <?= $bien['type']==='maison'?'selected':'' ?>>🏠 Maison</option>
                  <option value="immeuble" <?= $bien['type']==='immeuble'?'selected':'' ?>>🏢 Immeuble/Appart.</option>
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
                  <option value="vendu"      <?= $bien['statut']==='vendu'?'selected':'' ?>>🔴 Vendu/Loué</option>
                </select>
              </div>
            </div>

            <div class="row g-3 mb-3">
              <div class="col-md-4">
                <label class="filter-label">Prix (FCFA) *</label>
                <input type="number" name="prix" class="form-control" required min="0"
                       value="<?= $bien['prix'] ?>" placeholder="Ex: 85000000">
              </div>
              <div class="col-md-4">
                <label class="filter-label">Superficie (m²)</label>
                <input type="number" name="superficie" class="form-control" min="0" step="0.01"
                       value="<?= $bien['superficie'] ?>" placeholder="Ex: 150">
              </div>
              <div class="col-md-4">
                <label class="filter-label">Nombre de pièces</label>
                <input type="number" name="nb_pieces" class="form-control" min="0"
                       value="<?= $bien['nb_pieces'] ?>" placeholder="Ex: 5">
              </div>
            </div>

            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <label class="filter-label">Localisation précise</label>
                <input type="text" name="localisation" class="form-control"
                       value="<?= sanitize($bien['localisation']) ?>" placeholder="Ex: Almadies, Route de la Corniche">
              </div>
              <div class="col-md-6">
                <label class="filter-label">Ville</label>
                <input type="text" name="ville" class="form-control"
                       value="<?= sanitize($bien['ville']) ?>" placeholder="Ex: Dakar">
              </div>
            </div>

            <div class="mb-0">
              <label class="filter-label">Description</label>
              <textarea name="description" class="form-control" rows="5"
                        placeholder="Décrivez le bien en détail : caractéristiques, équipements, environnement..."><?= sanitize($bien['description']) ?></textarea>
            </div>
          </div>
        </div>

        <!-- Colonne droite : Photos + Submit -->
        <div class="col-lg-4">
          <div style="background:white; border-radius:var(--radius-lg); padding:2rem; box-shadow:var(--shadow); margin-bottom:1.5rem;">
            <h5 style="font-family:'Playfair Display',serif; margin-bottom:1.5rem; padding-bottom:0.75rem; border-bottom:2px solid var(--gray-light);">
              <i class="bi bi-images me-2" style="color:var(--gold);"></i>Photos (max 10)
            </h5>
            <p style="font-size:0.8rem; color:var(--gray); margin-bottom:1rem;">
              La première photo sera la photo principale. Formats acceptés : JPG, PNG, WebP. Max 5MB par photo.
            </p>

            <?php for ($i=1; $i<=10; $i++): ?>
              <div class="mb-3 p-2 rounded" style="border:1.5px dashed #E5E7EB; background:#FAFAFA;">
                <label class="filter-label" style="color:<?= $i===1?'var(--gold)':'var(--gray)' ?>;">
                  <?= $i===1 ? '⭐ Photo principale' : 'Photo '.$i ?> <?= $i<=3?'(recommandée)':'' ?>
                </label>
                <div class="d-flex align-items-center gap-2">
                  <input type="file" id="photo<?= $i ?>" name="photo<?= $i ?>" accept="image/jpeg,image/png,image/webp"
                         class="form-control form-control-sm" style="font-size:0.78rem;">
                  <img id="preview_photo<?= $i ?>" src="" alt="" style="display:none; width:40px; height:40px; object-fit:cover; border-radius:6px;">
                </div>
              </div>
            <?php endfor; ?>
          </div>

          <div style="background:white; border-radius:var(--radius-lg); padding:1.5rem; box-shadow:var(--shadow);">
            <button type="submit" class="btn-primary-main">
              <i class="bi bi-plus-circle me-2"></i>Publier le bien
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
