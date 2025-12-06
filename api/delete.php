<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db.php';

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

$table = $input['table'] ?? '';
$id = isset($input['id']) ? (int)$input['id'] : 0;
$allowed = ['skills','experiences','projects'];

if (!in_array($table, $allowed) || $id <= 0) {
    echo json_encode(['ok'=>false,'error'=>'invalid request']);
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM `$table` WHERE id = ?");
    $ok = $stmt->execute([$id]);
    echo json_encode(['ok'=>$ok, 'deleted'=>$ok ? $id : null]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['ok'=>false,'error'=>$e->getMessage()]);
}
