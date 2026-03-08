<?php
$pageTitle = "Inscription - ImmoAgence";
require_once __DIR__ . '/../../includes/config.php';

if (isLoggedIn()) { header('Location: ' . SITE_URL . '/client/pages/dashboard.php'); exit; }

$error = $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf'] ?? '')) {
        $error = "Token de sécurité invalide.";
    } else {
        $nom    = sanitize($_POST['nom'] ?? '');
        $prenom = sanitize($_POST['prenom'] ?? '');
        $email  = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
        $tel    = sanitize($_POST['telephone'] ?? '');
        $pass   = $_POST['password'] ?? '';
        $pass2  = $_POST['password2'] ?? '';

        if (!$nom || !$prenom || !$email || !$pass) {
            $error = "Veuillez remplir tous les champs obligatoires.";
        } elseif ($pass !== $pass2) {
            $error = "Les mots de passe ne correspondent pas.";
        } elseif (strlen($pass) < 8) {
            $error = "Le mot de passe doit contenir au moins 8 caractères.";
        } else {
            $check = $pdo->prepare("SELECT id FROM utilisateurs WHERE email=?");
            $check->execute([$email]);
            if ($check->fetch()) {
                $error = "Cet email est déjà utilisé.";
            } else {
                $hash = password_hash($pass, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom, prenom, email, telephone, mot_de_passe, role) VALUES (?,?,?,?,?,'client')");
                $stmt->execute([$nom, $prenom, $email, $tel, $hash]);
                $success = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
            }
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="min-vh-100 d-flex align-items-center py-5" style="background:linear-gradient(135deg,var(--dark),var(--primary));">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-6 col-lg-5">
        <div style="background:white; border-radius:var(--radius-lg); padding:2.5rem; box-shadow:var(--shadow-lg);">
          <div class="text-center mb-4">
            <div style="font-family:'Playfair Display',serif; font-size:1.8rem; font-weight:700; color:var(--primary);">
              Immo<span style="color:var(--gold)">Agence</span>
            </div>
            <h4 style="color:var(--dark); margin-top:1rem; font-size:1.2rem;">Créer un compte</h4>
          </div>

          <?php if ($success): ?>
            <div class="alert alert-success py-2" style="border-radius:var(--radius);">
              <i class="bi bi-check-circle me-2"></i><?= $success ?>
              <div class="mt-2">
                <a href="<?= SITE_URL ?>/client/login.php" class="btn-primary-main" style="font-size:0.85rem;">Se connecter</a>
              </div>
            </div>
          <?php else: ?>
            <?php if ($error): ?>
              <div class="alert alert-danger py-2" style="font-size:0.88rem; border-radius:var(--radius);">
                <i class="bi bi-exclamation-triangle me-2"></i><?= $error ?>
              </div>
            <?php endif; ?>

            <form method="POST">
              <input type="hidden" name="csrf" value="<?= generateCsrfToken() ?>">
              <div class="row g-2 mb-2">
                <div class="col-6">
                  <label class="filter-label">Prénom *</label>
                  <input type="text" name="prenom" class="form-control form-control-sm" required>
                </div>
                <div class="col-6">
                  <label class="filter-label">Nom *</label>
                  <input type="text" name="nom" class="form-control form-control-sm" required>
                </div>
              </div>
              <div class="mb-2">
                <label class="filter-label">Email *</label>
                <input type="email" name="email" class="form-control form-control-sm" required>
              </div>
              <div class="mb-2">
                <label class="filter-label">Téléphone</label>
                <input type="tel" name="telephone" class="form-control form-control-sm">
              </div>
              <div class="mb-2">
                <label class="filter-label">Mot de passe * (min. 8 car.)</label>
                <input type="password" name="password" class="form-control form-control-sm" required>
              </div>
              <div class="mb-4">
                <label class="filter-label">Confirmer le mot de passe *</label>
                <input type="password" name="password2" class="form-control form-control-sm" required>
              </div>
              <button type="submit" class="btn-primary-main">
                <i class="bi bi-person-plus me-2"></i>Créer mon compte
              </button>
            </form>

            <p class="text-center mt-3 mb-0" style="font-size:0.88rem; color:var(--gray);">
              Déjà un compte ?
              <a href="<?= SITE_URL ?>/client/login.php" style="color:var(--primary); font-weight:600;">Se connecter</a>
            </p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
