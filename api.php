<?php
// api.php
header('Content-Type: application/json; charset=utf-8');
require_once 'db.php';

$action = $_REQUEST['action'] ?? 'get_cv';
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($action === 'get_cv') {
        $profile = $pdo->query("SELECT * FROM profile ORDER BY id DESC LIMIT 1")->fetch() ?: null;
        $skills = $pdo->query("SELECT * FROM skills ORDER BY ord ASC, id ASC")->fetchAll();
        $experiences = $pdo->query("SELECT * FROM experiences ORDER BY ord ASC, id ASC")->fetchAll();
        $formations = $pdo->query("SELECT * FROM formations ORDER BY ord ASC, id ASC")->fetchAll();
        $projects = $pdo->query("SELECT * FROM projects ORDER BY ord ASC, id ASC")->fetchAll();

        echo json_encode(['ok' => true, 'profile' => $profile, 'skills' => $skills, 'experiences' => $experiences, 'formations' => $formations, 'projects' => $projects]);
        exit;
    }

    if ($action === 'save_profile' && $method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) throw new Exception('Données manquantes');

        // Use REPLACE INTO to insert or update (if id present)
        $stmt = $pdo->prepare("REPLACE INTO profile (id, fullname, title, email, location, phone, linkedin, about) VALUES (:id,:fullname,:title,:email,:location,:phone,:linkedin,:about)");
        $stmt->execute([
            ':id' => $data['id'] ?? null,
            ':fullname' => $data['fullname'] ?? '',
            ':title' => $data['title'] ?? '',
            ':email' => $data['email'] ?? '',
            ':location' => $data['location'] ?? '',
            ':phone' => $data['phone'] ?? '',
            ':linkedin' => $data['linkedin'] ?? '',
            ':about' => $data['about'] ?? '',
        ]);
        echo json_encode(['ok' => true]);
        exit;
    }

    // Add skill
    if ($action === 'add_skill' && $method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) throw new Exception('Données manquantes');

        $stmt = $pdo->prepare("INSERT INTO skills (category,name,value,meta,ord) VALUES (:category,:name,:value,:meta,:ord)");
        $stmt->execute([
            ':category' => $data['category'] ?? '',
            ':name' => $data['name'] ?? '',
            ':value' => intval($data['value'] ?? 0),
            ':meta' => $data['meta'] ?? '',
            ':ord' => intval($data['ord'] ?? 0),
        ]);
        echo json_encode(['ok' => true, 'id' => $pdo->lastInsertId()]);
        exit;
    }

    // Update skill
    if ($action === 'update_skill' && $method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data || empty($data['id'])) throw new Exception('id manquant');

        $stmt = $pdo->prepare("UPDATE skills SET category=:category,name=:name,value=:value,meta=:meta,ord=:ord WHERE id=:id");
        $stmt->execute([
            ':category' => $data['category'] ?? '',
            ':name' => $data['name'] ?? '',
            ':value' => intval($data['value'] ?? 0),
            ':meta' => $data['meta'] ?? '',
            ':ord' => intval($data['ord'] ?? 0),
            ':id' => intval($data['id']),
        ]);
        echo json_encode(['ok' => true]);
        exit;
    }

    // Delete skill
    if ($action === 'delete_skill' && $method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data || empty($data['id'])) throw new Exception('id manquant');

        $stmt = $pdo->prepare("DELETE FROM skills WHERE id=:id");
        $stmt->execute([':id' => intval($data['id'])]);
        echo json_encode(['ok' => true]);
        exit;
    }

    echo json_encode(['ok' => false, 'error' => 'Action inconnue']);
} catch (Exception $e) {
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
