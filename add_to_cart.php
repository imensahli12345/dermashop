<?php
require 'includes/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

// 1) Authentification
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success'=>false,'error'=>'Non authentifié']);
    exit;
}

// 2) Lire la requête JSON
$data = json_decode(file_get_contents('php://input'), true);
if (empty($data['id']) || !ctype_digit(strval($data['id']))
  || empty($data['qty']) || !ctype_digit(strval($data['qty']))) {
    http_response_code(400);
    echo json_encode(['success'=>false,'error'=>'Données invalides']);
    exit;
}

$userId    = $_SESSION['user_id'];
$productId = (int)$data['id'];
$qty       = max(1, (int)$data['qty']);

// 3) Chercher ou créer le panier actif
$stmt = $connexion->prepare("
  SELECT id FROM paniers
  WHERE user_id = ? AND status = 'active'
");
$stmt->execute([$userId]);
$panierId = $stmt->fetchColumn();
if (!$panierId) {
    $ins = $connexion->prepare("
      INSERT INTO paniers (user_id) VALUES (?)
    ");
    $ins->execute([$userId]);
    $panierId = $connexion->lastInsertId();
}

// 4) Ajouter ou mettre à jour panier_items
$stmt2 = $connexion->prepare("
  SELECT quantity FROM panier_items
  WHERE panier_id = ? AND product_id = ?
");
$stmt2->execute([$panierId, $productId]);
$currentQty = $stmt2->fetchColumn();

if ($currentQty !== false) {
    // Mettre à jour
    $upd = $connexion->prepare("
      UPDATE panier_items
      SET quantity = quantity + ?
      WHERE panier_id = ? AND product_id = ?
    ");
    $upd->execute([$qty, $panierId, $productId]);
} else {
    // Insérer
    $ins2 = $connexion->prepare("
      INSERT INTO panier_items (panier_id, product_id, quantity)
      VALUES (?, ?, ?)
    ");
    $ins2->execute([$panierId, $productId, $qty]);
}

// 5) Nombre total d’articles dans le panier
$cnt = $connexion->prepare("
  SELECT COALESCE(SUM(quantity),0) FROM panier_items
  WHERE panier_id = ?
");
$cnt->execute([$panierId]);
$cartCount = (int)$cnt->fetchColumn();

// 6) Réponse
echo json_encode(['success'=>true, 'cartCount'=>$cartCount]);
