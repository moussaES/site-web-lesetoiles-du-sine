<?php
require_once __DIR__ . '/../../includes/config.php';
requireClient();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prenom = sanitize($_POST['prenom'] ?? '');
    $nom = sanitize($_POST['nom'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';

    if (!$prenom || !$nom || !$email) {
        $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Tous les champs sont requis.'];
        header('Location: ' . SITE_URL . '/client/pages/dashboard.php');
        exit;
    }

    // Vérifier si l'email est déjà utilisé (sauf si c'est le même)
    $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE email=? AND id!=?");
    $stmt->execute([$email, $_SESSION['user_id']]);
    if ($stmt->fetch()) {
        $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Cet email est déjà utilisé.'];
        header('Location: ' . SITE_URL . '/client/pages/dashboard.php');
        exit;
    }

    try {
        if ($password) {
            // Mettre à jour avec nouveau mot de passe
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("UPDATE utilisateurs SET prenom=?, nom=?, email=?, mot_de_passe=? WHERE id=?");
            $stmt->execute([$prenom, $nom, $email, $hashedPassword, $_SESSION['user_id']]);
        } else {
            // Mettre à jour sans mot de passe
            $stmt = $pdo->prepare("UPDATE utilisateurs SET prenom=?, nom=?, email=? WHERE id=?");
            $stmt->execute([$prenom, $nom, $email, $_SESSION['user_id']]);
        }

        // Mettre à jour la session
        $_SESSION['prenom'] = $prenom;
        $_SESSION['nom'] = $nom;
        $_SESSION['email'] = $email;

        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Profil mis à jour avec succès.'];
    } catch (Exception $e) {
        $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Erreur lors de la mise à jour.'];
    }

    header('Location: ' . SITE_URL . '/client/pages/dashboard.php');
    exit;
}
?>
