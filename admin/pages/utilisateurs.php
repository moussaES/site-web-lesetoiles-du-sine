<?php
$pageTitle = "Gestion des utilisateurs";
require_once __DIR__ . '/../../includes/config.php';
requireAdmin();

// Création d'un nouvel utilisateur (admin uniquement)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom       = sanitize($_POST['nom'] ?? '');
    $prenom    = sanitize($_POST['prenom'] ?? '');
    $email     = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $password  = $_POST['password'] ?? '';
    $telephone = sanitize($_POST['telephone'] ?? '');
    $role      = in_array($_POST['role'] ?? '', ['client','gestionnaire']) ? $_POST['role'] : 'client';

    if ($nom && $prenom && $email && $password) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        try {
            $pdo->prepare("INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, telephone, role) VALUES (?,?,?,?,?,?)")
                ->execute([$nom, $prenom, $email, $hash, $telephone ?: null, $role]);
            $_SESSION['flash'] = ['type'=>'success', 'msg'=>'Utilisateur créé.'];
        } catch (PDOException $e) {
            $_SESSION['flash'] = ['type'=>'danger', 'msg'=>'Erreur : ' . $e->getMessage()];
        }
    } else {
        $_SESSION['flash'] = ['type'=>'danger', 'msg'=>'Tous les champs obligatoires doivent être remplis.'];
    }
    header('Location: ' . SITE_URL . '/admin/pages/utilisateurs.php');
    exit;
}

// Activer/désactiver
if (isset($_GET['toggle'], $_GET['id'])) {
    $user = $pdo->prepare("SELECT actif FROM utilisateurs WHERE id=? AND role!='admin'");
    $user->execute([(int)$_GET['id']]);
    $u = $user->fetch();
    if ($u) {
        $pdo->prepare("UPDATE utilisateurs SET actif=? WHERE id=?")->execute([$u['actif']?0:1, (int)$_GET['id']]);
        $_SESSION['flash'] = ['type'=>'success', 'msg'=>'Statut du compte mis à jour.'];
    }
    header('Location: ' . SITE_URL . '/admin/pages/utilisateurs.php');
    exit;
}

// Supprimer
if (isset($_GET['delete'], $_GET['id'])) {
    $pdo->prepare("DELETE FROM utilisateurs WHERE id=? AND role!='admin'")->execute([(int)$_GET['id']]);
    $_SESSION['flash'] = ['type'=>'success', 'msg'=>'Utilisateur supprimé.'];
    header('Location: ' . SITE_URL . '/admin/pages/utilisateurs.php');
    exit;
}

$search = sanitize($_GET['q'] ?? '');
$where  = "WHERE role!='admin'" . ($search ? " AND (nom LIKE ? OR prenom LIKE ? OR email LIKE ?)" : '');
$params = $search ? ["%$search%","%$search%","%$search%"] : [];

$utilisateurs = $pdo->prepare("SELECT u.*, 
    (SELECT COUNT(*) FROM demandes WHERE client_id=u.id) as nb_demandes,
    (SELECT COUNT(*) FROM favoris WHERE client_id=u.id) as nb_favoris
    FROM utilisateurs u $where ORDER BY date_inscription DESC");
$utilisateurs->execute($params);
$utilisateurs = $utilisateurs->fetchAll();

$total_clients = $pdo->query("SELECT COUNT(*) FROM utilisateurs WHERE role!='admin'")->fetchColumn();
$actifs        = $pdo->query("SELECT COUNT(*) FROM utilisateurs WHERE role!='admin' AND actif=1")->fetchColumn();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row g-3 mb-4">
  <div class="col-6 col-md-4">
    <div class="stat-card">
      <div class="stat-card-icon blue"><i class="bi bi-people-fill"></i></div>
      <div>
        <div class="stat-card-value"><?= $total_clients ?></div>
        <div class="stat-card-label">Total utilisateurs</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-4">
    <div class="stat-card">
      <div class="stat-card-icon green"><i class="bi bi-person-check-fill"></i></div>
      <div>
        <div class="stat-card-value"><?= $actifs ?></div>
        <div class="stat-card-label">Comptes actifs</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-4">
    <div class="stat-card">
      <div class="stat-card-icon red"><i class="bi bi-person-x-fill"></i></div>
      <div>
        <div class="stat-card-value"><?= $total_clients - $actifs ?></div>
        <div class="stat-card-label">Désactivés</div>
      </div>
    </div>
  </div>
</div>

<?php if (isAdmin()): ?>
<div class="mb-4">
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
    <i class="bi bi-plus-circle me-2"></i>Ajouter un utilisateur
  </button>
</div>
<?php endif; ?>

<!-- Recherche -->
<div class="filters-section mb-4">
  <form method="GET" id="searchForm">
    <div class="row g-2 align-items-end">
      <div class="col-md-8">
        <div class="filter-label">Rechercher un utilisateur</div>
        <input type="text" name="q" class="form-control" placeholder="Nom, prénom ou email..."
               value="<?= sanitize($search) ?>">
      </div>
      <div class="col-md-2">
        <button type="submit" class="btn-search w-100" style="padding:0.6rem;">
          <i class="bi bi-search me-1"></i>Rechercher
        </button>
      </div>
      <div class="col-md-2">
        <a href="?" style="display:block; text-align:center; padding:0.6rem; color:var(--gray); font-size:0.85rem; border:1.5px solid #E5E7EB; border-radius:var(--radius);">
          Réinitialiser
        </a>
      </div>
    </div>
  </form>
</div>

<div class="admin-table">
  <table class="table">
    <thead>
      <tr>
        <th>Client</th>
        <th>Email</th>
        <th>Téléphone</th>
        <th>Inscription</th>
        <th>Activité</th>
        <th>Statut</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($utilisateurs)): ?>
        <tr><td colspan="7" class="text-center py-4 text-muted">Aucun utilisateur trouvé</td></tr>
      <?php else: ?>
        <?php foreach ($utilisateurs as $u): ?>
          <tr>
            <td>
              <div style="display:flex; align-items:center; gap:0.75rem;">
                <div style="width:36px; height:36px; background:<?= $u['actif']?'rgba(26,60,110,0.12)':'rgba(239,68,68,0.08)' ?>; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:700; color:<?= $u['actif']?'var(--primary)':'var(--danger)' ?>; font-size:0.85rem; flex-shrink:0;">
                  <?= strtoupper(substr($u['prenom'],0,1).substr($u['nom'],0,1)) ?>
                </div>
                <div>
                  <div style="font-weight:600; font-size:0.88rem;"><?= sanitize($u['prenom'].' '.$u['nom']) ?></div>
                  <div style="font-size:0.72rem; color:var(--gray);">ID #<?= $u['id'] ?></div>
                </div>
              </div>
            </td>
            <td style="font-size:0.85rem;"><?= sanitize($u['email']) ?></td>
            <td style="font-size:0.85rem; color:var(--gray);"><?= $u['telephone'] ? sanitize($u['telephone']) : '—' ?></td>
            <td style="font-size:0.8rem; color:var(--gray);"><?= date('d/m/Y', strtotime($u['date_inscription'])) ?></td>
            <td>
              <div style="font-size:0.8rem;">
                <span style="color:var(--primary); font-weight:600;"><?= $u['nb_demandes'] ?></span>
                <span style="color:var(--gray);"> demandes</span>
              </div>
              <div style="font-size:0.8rem;">
                <span style="color:var(--gold); font-weight:600;"><?= $u['nb_favoris'] ?></span>
                <span style="color:var(--gray);"> favoris</span>
              </div>
            </td>
            <td>
              <span style="background:<?= $u['actif']?'rgba(16,185,129,0.12)':'rgba(239,68,68,0.1)' ?>; color:<?= $u['actif']?'#059669':'var(--danger)' ?>; border-radius:50px; padding:0.3rem 0.85rem; font-size:0.75rem; font-weight:700;">
                <?= $u['actif'] ? '✅ Actif' : '🚫 Désactivé' ?>
              </span>
            </td>
            <td>
              <div class="d-flex gap-1">
                <a href="?toggle=1&id=<?= $u['id'] ?>"
                   class="action-btn <?= $u['actif']?'action-btn-delete':'action-btn-view' ?>"
                   title="<?= $u['actif']?'Désactiver':'Activer' ?>">
                  <i class="bi bi-<?= $u['actif']?'person-x':'person-check' ?>"></i>
                </a>
                <a href="mailto:<?= sanitize($u['email']) ?>" class="action-btn action-btn-edit" title="Envoyer un email">
                  <i class="bi bi-envelope"></i>
                </a>
                <a href="?delete=1&id=<?= $u['id'] ?>" class="action-btn action-btn-delete confirm-delete" title="Supprimer">
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<?php if (isAdmin()): ?>
<!-- Modal Ajouter Utilisateur -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Ajouter un utilisateur</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <form method="POST">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Nom *</label>
            <input type="text" name="nom" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Prénom *</label>
            <input type="text" name="prenom" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Email *</label>
            <input type="email" name="email" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Mot de passe *</label>
            <input type="password" name="password" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Téléphone</label>
            <input type="tel" name="telephone" class="form-control" placeholder="Ex: +221 77 123 45 67">
          </div>
          <div class="mb-3">
            <label class="form-label">Rôle *</label>
            <select name="role" class="form-select" required>
              <option value="">-- Sélectionner --</option>
              <option value="client">Client</option>
              <option value="gestionnaire">Gestionnaire</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-primary">Créer</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>
