<?php
require_once __DIR__ . '/../includes/config.php';

if (isAdmin()) { header('Location: ' . SITE_URL . '/admin/dashboard.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $pass  = $_POST['password'] ?? '';

    if ($email && $pass) {
        $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email=? AND role IN ('admin','gestionnaire') AND actif=1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($pass, $user['mot_de_passe'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nom']     = $user['nom'];
            $_SESSION['prenom']  = $user['prenom'];
            $_SESSION['email']   = $user['email'];
            // conserve le rôle réel (admin ou gestionnaire)
            $_SESSION['role']    = $user['role'];
            header('Location: ' . SITE_URL . '/admin/dashboard.php');
            exit;
        }
    }
    $error = "Identifiants incorrects ou accès non autorisé.";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin - ImmoAgence</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
</head>
<body style="background:linear-gradient(135deg, var(--dark) 0%, var(--primary) 100%); min-height:100vh; display:flex; align-items:center;">
<div class="container">
  <div class="row justify-content-center">
    <div class="col-md-4 col-sm-8">
      <div style="background:white; border-radius:var(--radius-lg); padding:2.5rem; box-shadow:var(--shadow-lg);">
        <div class="text-center mb-4">
          <div style="width:60px; height:60px; background:rgba(26,60,110,0.1); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 1rem;">
            <i class="bi bi-shield-lock-fill" style="font-size:1.5rem; color:var(--primary);"></i>
          </div>
          <div style="font-family:'Playfair Display',serif; font-size:1.5rem; font-weight:700; color:var(--primary);">
            Immo<span style="color:var(--gold)">Admin</span>
          </div>
          <p style="color:var(--gray); font-size:0.85rem; margin:0.5rem 0 0;">Accès réservé aux administrateurs</p>
        </div>

        <?php if ($error): ?>
          <div class="alert alert-danger py-2" style="border-radius:var(--radius); font-size:0.88rem;">
            <i class="bi bi-exclamation-triangle me-2"></i><?= $error ?>
          </div>
        <?php endif; ?>

        <form method="POST">
          <div class="mb-3">
            <label class="filter-label">Email administrateur</label>
            <input type="email" name="email" class="form-control" required autofocus>
          </div>
          <div class="mb-4">
            <label class="filter-label">Mot de passe</label>
            <input type="password" name="password" class="form-control" required>
          </div>
          <button type="submit" class="btn-primary-main">
            <i class="bi bi-shield-check me-2"></i>Connexion Admin
          </button>
        </form>

        <div class="text-center mt-3">
          <a href="<?= SITE_URL ?>/index.php" style="color:var(--gray); font-size:0.82rem;">
            <i class="bi bi-arrow-left me-1"></i>Retour au site
          </a>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
