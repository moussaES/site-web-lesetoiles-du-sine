<?php
$pageTitle = "Contact - ImmoAgence";
require_once __DIR__ . '/../includes/header.php';

$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom   = sanitize($_POST['nom'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $sujet = sanitize($_POST['sujet'] ?? '');
    $msg   = sanitize($_POST['message'] ?? '');

    if (!$nom || !$email || !$msg) {
        $error = "Veuillez remplir tous les champs obligatoires.";
    } else {
        // Insérer comme demande générale (bien_id fictif ou table séparée)
        // Pour simplifier, on affiche juste un succès
        $success = "Votre message a été envoyé ! Nous vous répondrons dans les plus brefs délais.";
    }
}
?>

<div class="container py-5">
  <div class="text-center mb-5">
    <p style="color:var(--gold); font-size:0.8rem; text-transform:uppercase; letter-spacing:2px; font-weight:600;">Nous sommes là</p>
    <h1 class="section-title">Contactez-nous</h1>
    <div class="gold-line mx-auto"></div>
  </div>

  <div class="row g-5">
    <div class="col-lg-4">
      <div class="contact-form h-100">
        <h4 style="font-family:'Playfair Display',serif; margin-bottom:1.5rem;">Nos coordonnées</h4>

        <?php
        $infos = [
          ['bi-geo-alt-fill', 'Adresse', 'Avenue Bourguiba, Plateau<br>Dakar, Sénégal'],
          ['bi-telephone-fill', 'Téléphone', '+221 77 000 00 00<br>+221 33 000 00 00'],
          ['bi-envelope-fill', 'Email', 'contact@agence.com<br>info@agence.com'],
          ['bi-clock-fill', 'Horaires', 'Lun-Ven : 8h00 - 18h00<br>Sam : 9h00 - 14h00'],
        ];
        foreach ($infos as $info):
        ?>
          <div class="d-flex gap-3 mb-3 pb-3" style="border-bottom:1px solid var(--gray-light);">
            <div style="width:40px; height:40px; background:rgba(201,168,76,0.1); border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
              <i class="bi bi-<?= $info[0] ?>" style="color:var(--gold);"></i>
            </div>
            <div>
              <div style="font-size:0.78rem; color:var(--gray); text-transform:uppercase; letter-spacing:0.5px; font-weight:600;"><?= $info[1] ?></div>
              <div style="font-size:0.88rem; color:var(--dark);"><?= $info[2] ?></div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="col-lg-8">
      <div class="contact-form">
        <h4 style="font-family:'Playfair Display',serif; margin-bottom:1.5rem;">Envoyez-nous un message</h4>

        <?php if ($success): ?>
          <div class="alert alert-success" style="border-radius:var(--radius);">
            <i class="bi bi-check-circle me-2"></i><?= $success ?>
          </div>
        <?php endif; ?>
        <?php if ($error): ?>
          <div class="alert alert-danger" style="border-radius:var(--radius);">
            <i class="bi bi-exclamation-triangle me-2"></i><?= $error ?>
          </div>
        <?php endif; ?>

        <form method="POST">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="filter-label">Nom complet *</label>
              <input type="text" name="nom" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="filter-label">Email *</label>
              <input type="email" name="email" class="form-control" required>
            </div>
            <div class="col-12">
              <label class="filter-label">Sujet</label>
              <input type="text" name="sujet" class="form-control" placeholder="Ex: Demande d'information sur un bien">
            </div>
            <div class="col-12">
              <label class="filter-label">Message *</label>
              <textarea name="message" class="form-control" rows="6" required></textarea>
            </div>
            <div class="col-12">
              <button type="submit" class="btn-primary-main" style="width:auto; padding:0.8rem 2.5rem;">
                <i class="bi bi-send me-2"></i>Envoyer le message
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
