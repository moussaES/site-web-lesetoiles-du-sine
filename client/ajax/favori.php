<?php
require_once __DIR__ . '/../../includes/config.php';
header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['redirect' => SITE_URL . '/client/login.php']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$bien_id = (int)($data['bien_id'] ?? 0);

if (!$bien_id) {
    echo json_encode(['success' => false, 'message' => 'Bien invalide']);
    exit;
}

// Vérifier si déjà en favori
$check = $pdo->prepare("SELECT id FROM favoris WHERE client_id=? AND bien_id=?");
$check->execute([$_SESSION['user_id'], $bien_id]);

if ($check->fetch()) {
    $pdo->prepare("DELETE FROM favoris WHERE client_id=? AND bien_id=?")->execute([$_SESSION['user_id'], $bien_id]);
    echo json_encode(['success' => true, 'active' => false, 'message' => 'Retiré des favoris']);
} else {
    $pdo->prepare("INSERT INTO favoris (client_id, bien_id) VALUES (?,?)")->execute([$_SESSION['user_id'], $bien_id]);
    echo json_encode(['success' => true, 'active' => true, 'message' => 'Ajouté aux favoris ❤️']);
}
