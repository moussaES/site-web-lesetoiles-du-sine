<?php
require_once __DIR__ . '/includes/config.php';

// Nouveau mot de passe souhaité
$newPassword = 'Admin@1234';

// Générer le hash bcrypt et mettre à jour la table
$hash = password_hash($newPassword, PASSWORD_DEFAULT);
$stmt = $pdo->prepare("UPDATE utilisateurs SET mot_de_passe = ? WHERE email = 'admin@agence.com'");
if ($stmt->execute([$hash])) {
    echo "Mot de passe admin réinitialisé avec succès. Email: admin@agence.com | Nouveau mot de passe: $newPassword";
} else {
    echo "Échec de la mise à jour du mot de passe. Vérifiez la connexion à la BDD.";
}

// Supprimez ce fichier après utilisation pour des raisons de sécurité.
?>
