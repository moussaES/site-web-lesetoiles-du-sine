<?php
require_once __DIR__ . '/../../includes/config.php';
header('Content-Type: application/json');

if (!isAdmin() && !isGestionnaire()) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$id     = (int)($data['id'] ?? 0);
$statut = $data['statut'] ?? '';

if (!$id || !in_array($statut, ['disponible', 'reserve', 'vendu'])) {
    echo json_encode(['success' => false, 'message' => 'Données invalides']);
    exit;
}

$pdo->prepare("UPDATE biens SET statut=? WHERE id=?")->execute([$statut, $id]);
echo json_encode(['success' => true, 'message' => 'Statut mis à jour : ' . ucfirst($statut)]);
