<?php
// get_image.php
require 'includes/config.php';

// 1) Validate product ID
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    http_response_code(400);
    exit('Invalid product ID');
}

// 2) Fetch the image field from DB
$stmt = $connexion->prepare('SELECT image FROM products WHERE id = ?');
$stmt->execute([$id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row || empty($row['image'])) {
    http_response_code(404);
    exit('Image not found');
}

$imageField = $row['image'];

// 3) If it's a filename, serve that file
$diskPath = __DIR__ . '/img/products/' . $imageField;
if (is_file($diskPath)) {
    $mime = mime_content_type($diskPath) ?: 'application/octet-stream';
    header('Content-Type: ' . $mime);
    header('Content-Length: ' . filesize($diskPath));
    readfile($diskPath);
    exit;
}

// 4) Otherwise assume it's raw BLOB data
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime  = $finfo->buffer($imageField) ?: 'application/octet-stream';
header('Content-Type: ' . $mime);
header('Content-Length: ' . strlen($imageField));
echo $imageField;
exit;
