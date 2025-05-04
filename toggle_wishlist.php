<?php
require 'includes/config.php';    // connexion PDO
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// 1) Vérifier que l’utilisateur est loggé
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error'=>'Non authentifié']);
    exit;
}
// 2) Récupérer le JSON POST
$input = json_decode(file_get_contents('php://input'), true);
if (empty($input['id']) || !ctype_digit(strval($input['id']))) {
    http_response_code(400);
    echo json_encode(['error'=>'ID invalide']);
    exit;
}
$user_id    = $_SESSION['user_id'];
$product_id = (int)$input['id'];

// 3) Vérifier si déjà liké
$stmt = $connexion->prepare("
    SELECT id
    FROM liked_products
    WHERE user_id = ? AND product_id = ?
");
$stmt->execute([$user_id, $product_id]);
$exists = $stmt->fetchColumn();

if ($exists) {
    // 4a) Déjà liké → on supprime
    $del = $connexion->prepare("
        DELETE FROM liked_products
        WHERE user_id = ? AND product_id = ?
    ");
    $del->execute([$user_id, $product_id]);
    $liked = false;
} else {
    // 4b) Pas encore liké → on insère
    $ins = $connexion->prepare("
        INSERT INTO liked_products (user_id, product_id)
        VALUES (?, ?)
    ");
    $ins->execute([$user_id, $product_id]);
    $liked = true;
}

// 5) Réponse JSON
header('Content-Type: application/json');
echo json_encode(['liked' => $liked]);
