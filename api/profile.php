<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db.php';

try {
    $stmt = $pdo->query("SELECT * FROM profile LIMIT 1");
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($profile) {
        echo json_encode(['ok'=>true, 'profile'=>$profile], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(['ok'=>true, 'profile'=>null]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['ok'=>false, 'error'=>$e->getMessage()]);
}
