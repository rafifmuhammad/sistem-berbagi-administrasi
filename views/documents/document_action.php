<?php
require_once __DIR__ . '/../../functions/function.php';
require_login();

$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? ($_POST['id'] ?? '');

if ($id === '') {
    set_flash('error', 'Gagal', 'Dokumen tidak ditemukan.');
    redirect_to('views/documents/document_management.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'status') {
    if (!is_admin()) {
        set_flash('error', 'Ditolak', 'Hanya admin yang dapat mengubah status dokumen.');
        redirect_to('views/documents/document_management.php');
    }

    $status = $_POST['status'] ?? '';
    $result = update_document_status($id, $status);
    set_flash(
        $result !== false ? 'success' : 'error',
        $result !== false ? 'Berhasil' : 'Gagal',
        $result !== false ? 'Status dokumen diperbarui.' : 'Status dokumen tidak valid.'
    );
    redirect_to('views/documents/document_management.php');
}

if ($action === 'approve') {
    if (!is_admin()) {
        set_flash('error', 'Ditolak', 'Hanya admin yang dapat menyetujui dokumen.');
        redirect_to('views/documents/document_management.php');
    }

    $result = approve_document($id);
    set_flash($result > 0 ? 'success' : 'info', $result > 0 ? 'Disetujui' : 'Tidak Berubah', $result > 0 ? 'Dokumen tampil di halaman publik.' : 'Status dokumen tidak berubah.');
    redirect_to('views/documents/document_management.php');
}

if ($action === 'reject') {
    if (!is_admin()) {
        set_flash('error', 'Ditolak', 'Hanya admin yang dapat menolak dokumen.');
        redirect_to('views/documents/document_management.php');
    }

    $result = reject_document($id);
    set_flash($result > 0 ? 'success' : 'info', $result > 0 ? 'Ditolak' : 'Tidak Berubah', $result > 0 ? 'Dokumen tidak tampil di halaman publik.' : 'Status dokumen tidak berubah.');
    redirect_to('views/documents/document_management.php');
}

if ($action === 'delete') {
    $result = delete_document($id);
    set_flash($result > 0 ? 'success' : 'error', $result > 0 ? 'Berhasil' : 'Gagal', $result > 0 ? 'Dokumen berhasil dihapus.' : 'Dokumen tidak ditemukan.');
    redirect_to('views/documents/document_management.php');
}

set_flash('error', 'Gagal', 'Aksi tidak dikenali.');
redirect_to('views/documents/document_management.php');
