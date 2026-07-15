<?php
require_once __DIR__ . '/config/db.php';

header('Content-Type: application/json');

$q = trim($_GET['q'] ?? '');

if ($q === '' || mb_strlen($q) < 2) {
    echo json_encode([]);
    exit;
}

$stmt = $pdo->prepare(
    'SELECT id, nama_game, genre
     FROM games
     WHERE nama_game LIKE :q
     ORDER BY nama_game ASC
     LIMIT 8'
);
$stmt->execute(['q' => '%' . $q . '%']);
$results = $stmt->fetchAll();

echo json_encode($results);
