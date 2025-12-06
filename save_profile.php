<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'db.php';

// lire JSON POST
$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
  echo json_encode(['ok'=>false,'error'=>'Données manquantes']);
  exit;
}

// sanitize & required checks
$fullname = $mysqli->real_escape_string(trim($data['fullname'] ?? ''));
$title = $mysqli->real_escape_string(trim($data['title'] ?? ''));
$email = $mysqli->real_escape_string(trim($data['email'] ?? ''));
$location = $mysqli->real_escape_string(trim($data['location'] ?? ''));
$phone = $mysqli->real_escape_string(trim($data['phone'] ?? ''));
$linkedin = $mysqli->real_escape_string(trim($data['linkedin'] ?? ''));
$about = $mysqli->real_escape_string(trim($data['about'] ?? ''));

// upsert - simple: update id = 1 (profile unique)
$sql = "UPDATE profile SET fullname=?, title=?, email=?, location=?, phone=?, linkedin=?, about=?, updated_at=NOW() WHERE id=1";
$stmt = $mysqli->prepare($sql);
if (!$stmt) {
  echo json_encode(['ok'=>false,'error'=>'Prepare failed: '.$mysqli->error]);
  exit;
}
$stmt->bind_param('sssssss', $fullname, $title, $email, $location, $phone, $linkedin, $about);
if (!$stmt->execute()) {
  echo json_encode(['ok'=>false,'error'=>'Execute failed: '.$stmt->error]);
  exit;
}
// if no row updated, try insert
if ($stmt->affected_rows === 0) {
  $stmt->close();
  $ins = $mysqli->prepare("INSERT INTO profile (id, fullname, title, email, location, phone, linkedin, about, updated_at) VALUES (1,?,?,?,?,?,?,?,NOW())");
  $ins->bind_param('sssssss', $fullname, $title, $email, $location, $phone, $linkedin, $about);
  $ins->execute();
  $ins->close();
}

echo json_encode(['ok'=>true,'message'=>'Profil enregistré']);
