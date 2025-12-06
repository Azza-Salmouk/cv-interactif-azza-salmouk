<?php
// generate_pdf.php

declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');


// --- Inclure la connexion DB ---
require_once __DIR__ . '/../db.php';

if (!isset($pdo) || !($pdo instanceof PDO)) {
    // Si la connexion PDO n'est pas dispo, stop avec message JSON
    http_response_code(500);
    echo "Erreur serveur : impossible de se connecter à la base de données.";
    exit;
}

// --- Dompdf autoload (assurez-vous que composer a été exécuté) ---
require_once __DIR__ . '/../vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;

// --- Récupérer les données depuis la DB ---
try {
    $profile = $pdo->query("SELECT * FROM profile ORDER BY id DESC LIMIT 1")->fetch() ?: null;
    $skills = $pdo->query("SELECT * FROM skills ORDER BY ord ASC")->fetchAll();
    $experiences = $pdo->query("SELECT * FROM experiences ORDER BY ord ASC")->fetchAll();
    $projects = $pdo->query("SELECT * FROM projects ORDER BY ord ASC")->fetchAll();
} catch (Throwable $e) {
    http_response_code(500);
    echo "Erreur serveur : " . $e->getMessage();
    exit;
}

// --- Charger le template PDF ---
ob_start();
$templateFile = __DIR__ . '/../pdf_template.php';
if (!file_exists($templateFile)) {
    http_response_code(500);
    echo "Erreur serveur : template PDF introuvable.";
    exit;
}
include $templateFile;
$html = ob_get_clean();

// --- Configurer Dompdf ---
$options = new Options();
$options->set('isRemoteEnabled', true); // permet de charger images distantes
$options->set('defaultFont', 'DejaVu Sans'); // bonne compatibilité accents

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');

try {
    $dompdf->render();
    $dompdf->stream("CV-Azza-Salmouk.pdf", ["Attachment" => true]); // téléchargement immédiat
} catch (Throwable $e) {
    http_response_code(500);
    echo "Erreur lors de la génération du PDF : " . $e->getMessage();
    exit;
}
