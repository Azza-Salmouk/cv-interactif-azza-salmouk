<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db.php';

$in = json_decode(file_get_contents('php://input'), true) ?: $_POST;

$id = isset($in['id']) ? (int)$in['id'] : 0;
$start_date = $in['start_date'] ?? '';
$end_date = $in['end_date'] ?? '';
$title = $in['title'] ?? '';
$company = $in['company'] ?? '';
$summary = $in['summary'] ?? '';
$details = $in['details'] ?? '';
$ord = (int)($in['ord'] ?? 0);

if ($title === '') { echo json_encode(['ok'=>false,'error'=>'title required']); exit; }

try {
    if ($id > 0) {
        $stmt = $pdo->prepare("UPDATE experiences SET start_date=?, end_date=?, title=?, company=?, summary=?, details=?, ord=? WHERE id=?");
        $stmt->execute([$start_date,$end_date,$title,$company,$summary,$details,$ord,$id]);
        echo json_encode(['ok'=>true, 'updated'=>$id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO experiences (start_date,end_date,title,company,summary,details,ord) VALUES (?,?,?,?,?,?,?)");
        $stmt->execute([$start_date,$end_date,$title,$company,$summary,$details,$ord]);
        echo json_encode(['ok'=>true, 'inserted'=>$pdo->lastInsertId()]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['ok'=>false,'error'=>$e->getMessage()]);
}
