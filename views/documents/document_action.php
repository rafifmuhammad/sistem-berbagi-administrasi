<?php
require_once __DIR__ . '/../../functions/function.php';
require_login();

function document_action_return_to()
{
    $return_to = $_GET['return_to'] ?? ($_POST['return_to'] ?? 'views/documents/document_management.php');
    $allowed = [
        'views/dashboard/dashboard.php',
        'views/documents/document_management.php',
    ];

    return in_array($return_to, $allowed, true) ? $return_to : 'views/documents/document_management.php';
}

$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? ($_POST['id'] ?? '');
$return_to = document_action_return_to();

if ($id === '') {
    set_flash('error', 'Gagal', 'Dokumen tidak ditemukan.');
    redirect_to($return_to);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'status') {
    if (!is_admin()) {
        set_flash('error', 'Ditolak', 'Hanya admin yang dapat mengubah status dokumen.');
        redirect_to($return_to);
    }

    $status = $_POST['status'] ?? '';
    $reason = $_POST['rejection_reason'] ?? '';
    $result = update_document_status($id, $status, $reason);
    set_flash(
        $result !== false ? 'success' : 'error',
        $result !== false ? 'Berhasil' : 'Gagal',
        $result !== false ? 'Status dokumen diperbarui.' : 'Status dokumen tidak valid atau alasan penolakan kosong.'
    );
    redirect_to($return_to);
}

if ($action === 'approve') {
    if (!is_admin()) {
        set_flash('error', 'Ditolak', 'Hanya admin yang dapat menyetujui dokumen.');
        redirect_to($return_to);
    }

    $result = approve_document($id);
    set_flash($result > 0 ? 'success' : 'info', $result > 0 ? 'Disetujui' : 'Tidak Berubah', $result > 0 ? 'Dokumen tampil di halaman publik.' : 'Status dokumen tidak berubah.');
    redirect_to($return_to);
}

if ($action === 'reject') {
    if (!is_admin()) {
        set_flash('error', 'Ditolak', 'Hanya admin yang dapat menolak dokumen.');
        redirect_to($return_to);
    }

    $reason = $_POST['rejection_reason'] ?? '';
    $result = reject_document($id, $reason);
    if ($result === false) {
        set_flash('error', 'Gagal', 'Alasan penolakan wajib diisi.');
    } else {
        set_flash($result > 0 ? 'success' : 'info', $result > 0 ? 'Ditolak' : 'Tidak Berubah', $result > 0 ? 'Dokumen tidak tampil di halaman publik.' : 'Status dokumen tidak berubah.');
    }
    redirect_to($return_to);
}

if ($action === 'delete') {
    $result = delete_document($id);
    set_flash($result > 0 ? 'success' : 'error', $result > 0 ? 'Berhasil' : 'Gagal', $result > 0 ? 'Dokumen berhasil dihapus.' : 'Dokumen tidak ditemukan.');
    redirect_to($return_to);
}

set_flash('error', 'Gagal', 'Aksi tidak dikenali.');
redirect_to($return_to);
