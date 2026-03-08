<?php
$pageTitle = "Connexion - ImmoAgence";
require_once __DIR__ . '/../includes/config.php';

if (isLoggedIn()) { header('Location: ' . SITE_URL . '/client/pages/dashboard.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf'] ?? '')) {
        $error = "Token de sécurité invalide.";
    } else {
        $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
        $pass  = $_POST['password'] ?? '';

        if (!$email || !$pass) {
            $error = "Veuillez remplir tous les champs.";
        } else {
            $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email=? AND actif=1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($pass, $user['mot_de_passe'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['nom']     = $user['nom'];
                $_SESSION['prenom']  = $user['prenom'];
                $_SESSION['email']   = $user['email'];
                $_SESSION['role']    = $user['role'];

                if ($user['role'] === 'admin' || $user['role'] === 'gestionnaire') {
                    header('Location: ' . SITE_URL . '/admin/dashboard.php');
                } else {
                    $redirect = $_GET['redirect'] ?? SITE_URL . '/client/pages/dashboard.php';
                    header('Location: ' . $redirect);
                }
                exit;
            } else {
                $error = "Email ou mot de passe incorrect.";
            }
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="min-vh-100 d-flex align-items-center py-5" style="background:linear-gradient(135deg,var(--dark),var(--primary));">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-5 col-lg-4">
        <div style="background:white; border-radius:var(--radius-lg); padding:2.5rem; box-shadow:var(--shadow-lg);">
          <div class="text-center mb-4">
            <div style="font-family:'Playfair Display',serif; font-size:1.8rem; font-weight:700; color:var(--primary);">
              Immo<span style="color:var(--gold)">Agence</span>
            </div>
            <h4 style="color:var(--dark); margin-top:1rem; font-size:1.2rem;">Connexion</h4>
          </div>

          <?php if ($error): ?>
            <div class="alert alert-danger py-2" style="font-size:0.88rem; border-radius:var(--radius);">
              <i class="bi bi-exclamation-triangle me-2"></i><?= $error ?>
            </div>
          <?php endif; ?>

          <form method="POST">
            <input type="hidden" name="csrf" value="<?= generateCsrfToken() ?>">
            <div class="mb-3">
              <label class="filter-label">Email *</label>
              <div class="input-group">
                <span class="input-group-text" style="border-radius:var(--radius) 0 0 var(--radius); border:1.5px solid #E5E7EB; border-right:none; background:#F9FAFB;">
                  <i class="bi bi-envelope" style="color:var(--gray);"></i>
                </span>
                <input type="email" name="email" class="form-control" placeholder="votre@email.com"
                       style="border-radius:0 var(--radius) var(--radius) 0;" required>
              </div>
            </div>
            <div class="mb-4">
              <label class="filter-label">Mot de passe *</label>
              <div class="input-group">
                <span class="input-group-text" style="border-radius:var(--radius) 0 0 var(--radius); border:1.5px solid #E5E7EB; border-right:none; background:#F9FAFB;">
                  <i class="bi bi-lock" style="color:var(--gray);"></i>
                </span>
                <input type="password" name="password" class="form-control"
                       style="border-radius:0 var(--radius) var(--radius) 0;" required>
              </div>
            </div>
            <button type="submit" class="btn-primary-main">
              <i class="bi bi-box-arrow-in-right me-2"></i>Se connecter
            </button>
          </form>

          <p class="text-center mt-3 mb-0" style="font-size:0.88rem; color:var(--gray);">
            Pas encore de compte ?
            <a href="<?= SITE_URL ?>/client/pages/register.php" style="color:var(--primary); font-weight:600;">S'inscrire</a>
          </p>

          <div class="mt-3 p-2 rounded" style="background:#F8F9FB; font-size:0.78rem; color:var(--gray); text-align:center;">
            <strong>Admin test :</strong> admin@agence.com / Admin@1234
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
