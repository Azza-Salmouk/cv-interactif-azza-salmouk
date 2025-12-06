<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'db.php';
$data = json_decode(file_get_contents('php://input'), true);
if (!$data) { echo json_encode(['ok'=>false,'error'=>'No data']); exit; }

$op = $data['op'] ?? 'create'; // create | update | delete
if ($op === 'create') {
  $category = $data['category'] ?? '';
  $name = $data['name'] ?? '';
  $value = intval($data['value'] ?? 0);
  $meta = $data['meta'] ?? '';

  $stmt = $mysqli->prepare("INSERT INTO skills (category,name,value,meta,ord) VALUES (?,?,?,?,(SELECT COALESCE(MAX(ord),0)+1 FROM skills))");
  $stmt->bind_param('ssis', $category, $name, $value, $meta);
  if ($stmt->execute()) echo json_encode(['ok'=>true,'id'=>$mysqli->insert_id]);
  else echo json_encode(['ok'=>false,'error'=>$stmt->error]);
  $stmt->close();
  exit;
}

if ($op === 'update') {
  $id = intval($data['id'] ?? 0);
  $category = $data['category'] ?? '';
  $name = $data['name'] ?? '';
  $value = intval($data['value'] ?? 0);
  $meta = $data['meta'] ?? '';
  $stmt = $mysqli->prepare("UPDATE skills SET category=?,name=?,value=?,meta=? WHERE id=?");
  $stmt->bind_param('ssisi', $category, $name, $value, $meta, $id);
  if ($stmt->execute()) echo json_encode(['ok'=>true]);
  else echo json_encode(['ok'=>false,'error'=>$stmt->error]);
  $stmt->close();
  exit;
}

if ($op === 'delete') {
  $id = intval($data['id'] ?? 0);
  $stmt = $mysqli->prepare("DELETE FROM skills WHERE id=?");
  $stmt->bind_param('i', $id);
  if ($stmt->execute()) echo json_encode(['ok'=>true]);
  else echo json_encode(['ok'=>false,'error'=>$stmt->error]);
  $stmt->close();
  exit;
}

echo json_encode(['ok'=>false,'error'=>'op invalide']);
