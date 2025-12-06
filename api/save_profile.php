<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db.php';

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

$fullname = trim($input['fullname'] ?? '');
$title = trim($input['title'] ?? '');
$email = trim($input['email'] ?? '');
$location = trim($input['location'] ?? '');
$phone = trim($input['phone'] ?? '');
$linkedin = trim($input['linkedin'] ?? '');
$about = trim($input['about'] ?? '');

if ($fullname === '') {
    echo json_encode(['ok'=>false,'error'=>'fullname required']);
    exit;
}

try {
    // Vérifie si un profil existe déjà
    $stmt = $pdo->query("SELECT id FROM profile LIMIT 1");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        // Mise à jour
        $id = (int)$row['id'];
        $stmt = $pdo->prepare("UPDATE profile SET fullname=?, title=?, email=?, location=?, phone=?, linkedin=?, about=? WHERE id=?");
        $stmt->execute([$fullname,$title,$email,$location,$phone,$linkedin,$about,$id]);
        echo json_encode(['ok'=>true, 'updated'=>$id]);
    } else {
        // Insertion
        $stmt = $pdo->prepare("INSERT INTO profile (fullname, title, email, location, phone, linkedin, about) VALUES (?,?,?,?,?,?,?)");
        $stmt->execute([$fullname,$title,$email,$location,$phone,$linkedin,$about]);
        echo json_encode(['ok'=>true, 'inserted'=>$pdo->lastInsertId()]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['ok'=>false,'error'=>$e->getMessage()]);
}
