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

if (is_logged_in() && !is_admin() && $document['status'] !== 'disetujui' && (string) ($document['id_user'] ?? '') !== current_user_id()) {
    http_response_code(403);
    echo 'Dokumen tidak tersedia untuk akun ini.';
    exit;
}

$file_path = document_storage_path($document['file'] ?? '', ['documents/files']);

if ($file_path === '') {
    http_response_code(404);
    echo 'File tidak ditemukan.';
    exit;
}

$download_name = basename($file_path);

if (($_GET['preview'] ?? '') !== '1') {
    increment_document_download_count($document['id_document']);
}

header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . str_replace(['"', "\r", "\n"], '', $download_name) . '"');
header('Content-Length: ' . filesize($file_path));
header('Cache-Control: no-store, no-cache, must-revalidate');
readfile($file_path);
exit;
