<?php
require_once __DIR__ . '/../functions/function.php';

$id = $_GET['id'] ?? '';
$document = get_document($id);

if (!$document) {
    http_response_code(404);
    echo 'Dokumen tidak ditemukan.';
    exit;
}

if (!is_logged_in() && $document['status'] !== 'disetujui') {
    http_response_code(403);
    echo 'Dokumen belum tersedia untuk publik.';
    exit;
}

$base_dir = realpath(__DIR__ . '/..');
$file_path = realpath(__DIR__ . '/../' . $document['file']);

if (!$file_path || strpos($file_path, $base_dir) !== 0 || !is_file($file_path)) {
    http_response_code(404);
    echo 'File tidak ditemukan.';
    exit;
}

$download_name = basename($file_path);

header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . str_replace('"', '', $download_name) . '"');
header('Content-Length: ' . filesize($file_path));
header('Cache-Control: no-store, no-cache, must-revalidate');
readfile($file_path);
exit;
