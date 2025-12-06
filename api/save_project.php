<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db.php';

$in = json_decode(file_get_contents('php://input'), true) ?: $_POST;

$id = isset($in['id']) ? (int)$in['id'] : 0;
$title = $in['title'] ?? '';
$description = $in['description'] ?? '';
$tags = $in['tags'] ?? '';
$ord = (int)($in['ord'] ?? 0);

if ($title === '') { echo json_encode(['ok'=>false,'error'=>'title required']); exit; }

try {
    if ($id > 0) {
        $stmt = $pdo->prepare("UPDATE projects SET title=?, description=?, tags=?, ord=? WHERE id=?");
        $stmt->execute([$title,$description,$tags,$ord,$id]);
        echo json_encode(['ok'=>true, 'updated'=>$id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO projects (title, description, tags, ord) VALUES (?,?,?,?)");
        $stmt->execute([$title,$description,$tags,$ord]);
        echo json_encode(['ok'=>true, 'inserted'=>$pdo->lastInsertId()]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['ok'=>false,'error'=>$e->getMessage()]);
}
