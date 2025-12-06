<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db.php';

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

$category = $input['category'] ?? '';
$name = $input['name'] ?? '';
$value = (int)($input['value'] ?? 0);
$meta = $input['meta'] ?? '';
$ord = (int)($input['ord'] ?? 0);
$id = isset($input['id']) ? (int)$input['id'] : 0;

if ($name === '' || $category === '') {
    echo json_encode(['ok'=>false,'error'=>'name and category required']);
    exit;
}

try {
    if ($id > 0) {
        $stmt = $pdo->prepare("UPDATE skills SET category=?, name=?, value=?, meta=?, ord=? WHERE id=?");
        $ok = $stmt->execute([$category, $name, $value, $meta, $ord, $id]);
        echo json_encode(['ok'=>$ok, 'updated'=>$id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO skills (category, name, value, meta, ord) VALUES (?, ?, ?, ?, ?)");
        $ok = $stmt->execute([$category, $name, $value, $meta, $ord]);
        echo json_encode(['ok'=>$ok, 'inserted'=>$pdo->lastInsertId()]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['ok'=>false,'error'=>$e->getMessage()]);
}
