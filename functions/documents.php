<?php

function document_status_label($status)
{
    $labels = [
        'menunggu' => 'Menunggu',
        'disetujui' => 'Disetujui',
        'ditolak' => 'Ditolak',
    ];

    return $labels[$status] ?? 'Menunggu';
}

function document_status_badge($status, $reason = '', $clickable_reason = false)
{
    $classes = [
        'menunggu' => 'badge-soft-amber',
        'disetujui' => 'badge-soft-green',
        'ditolak' => 'badge-soft-slate',
    ];

    $class = $classes[$status] ?? 'badge-soft-amber';
    $label = h(document_status_label($status));

    if ($status === 'ditolak' && $clickable_reason) {
        $reason = trim((string) $reason);
        $reason_text = $reason !== '' ? $reason : 'Belum ada alasan penolakan.';

        return '<button type="button" class="badge status-reason-button ' . $class . ' js-rejection-reason" data-reason="' . h($reason_text) . '">' . $label . '</button>';
    }

    return '<span class="badge ' . $class . '">' . $label . '</span>';
}

function current_user_id()
{
    return trim((string) ($_SESSION['user']['id_user'] ?? ''));
}

function get_documents($approved_only = false, $owner_id = null)
{
    $conditions = [];

    if ($approved_only) {
        $conditions[] = "d.status = 'disetujui'";
    }

    if ($owner_id !== null && trim((string) $owner_id) !== '') {
        $owner_safe = db_escape($owner_id);
        $conditions[] = "d.id_user = '$owner_safe'";
    }

    $where = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

    return query("SELECT d.*, c.nama_kategori, u.nama AS uploader, a.nama AS approver
        FROM tb_dokumen d
        JOIN tb_kategori c ON c.id_category = d.id_category
        LEFT JOIN tb_user u ON u.id_user = d.id_user
        LEFT JOIN tb_user a ON a.id_user = d.approved_by
        $where
        ORDER BY d.created_at DESC
    ");
}

function get_document($id_document)
{
    $id_document = db_escape($id_document);
    $rows = query("SELECT d.*, c.nama_kategori, u.nama AS uploader, a.nama AS approver
        FROM tb_dokumen d
        JOIN tb_kategori c ON c.id_category = d.id_category
        LEFT JOIN tb_user u ON u.id_user = d.id_user
        LEFT JOIN tb_user a ON a.id_user = d.approved_by
        WHERE d.id_document = '$id_document'
        LIMIT 1
    ");

    return $rows[0] ?? null;
}

function next_document_id()
{
    return make_entity_id('DOC');
}

function current_user_id_sql()
{
    $id_user = current_user_id();

    return $id_user !== '' ? "'" . db_escape($id_user) . "'" : 'NULL';
}

function add_document($data, $file, $preview_file_upload = [])
{
    global $conn;

    $id_document = next_document_id();
    $id_category = trim((string) ($data['id_category'] ?? ''));
    $id_user_sql = current_user_id_sql();
    $nama = trim($data['nama_dokumen'] ?? '');
    $keterangan = trim($data['keterangan'] ?? '');
    $tanggal_upload = date('Y-m-d');

    if ($id_category === '' || $nama === '') {
        return false;
    }

    $file_path = move_document_file($file, $id_document);

    if ($file_path === '') {
        return false;
    }

    $preview_file = has_uploaded_file($preview_file_upload)
        ? move_preview_file($preview_file_upload, $id_document)
        : make_preview_file($file_path);

    if (has_uploaded_file($preview_file_upload) && $preview_file === '') {
        return false;
    }

    $nama_safe = db_escape($nama);
    $id_category_safe = db_escape($id_category);
    $keterangan_safe = db_escape($keterangan);
    $tanggal_safe = db_escape($tanggal_upload);
    $file_safe = db_escape($file_path);
    $preview_safe = $preview_file !== '' ? "'" . db_escape($preview_file) . "'" : 'NULL';

    mysqli_query($conn, "INSERT INTO tb_dokumen
        (id_document, id_category, id_user, nama_dokumen, keterangan, tanggal_upload, file, preview_file, status)
        VALUES ('$id_document', '$id_category_safe', $id_user_sql, '$nama_safe', '$keterangan_safe', '$tanggal_safe', '$file_safe', $preview_safe, 'menunggu')
    ");

    return mysqli_affected_rows($conn);
}

function update_document($id_document, $data, $file, $preview_file_upload = [])
{
    global $conn;

    $document = get_document($id_document);

    if (!$document) {
        return false;
    }

    $id_safe = db_escape($id_document);
    $id_category = trim((string) ($data['id_category'] ?? ''));
    $nama = trim($data['nama_dokumen'] ?? '');
    $keterangan = trim($data['keterangan'] ?? '');
    $tanggal_upload = trim($data['tanggal_upload'] ?? ($document['tanggal_upload'] ?? date('Y-m-d')));

    if ($id_category === '' || $nama === '') {
        return false;
    }

    $id_category_safe = db_escape($id_category);
    $nama_safe = db_escape($nama);
    $keterangan_safe = db_escape($keterangan);
    $tanggal_safe = db_escape($tanggal_upload);
    $file_sql = '';
    $preview_sql = '';

    if (has_uploaded_file($file)) {
        $file_path = move_document_file($file, $id_document);

        if ($file_path === '') {
            return false;
        }

        $preview_file = make_preview_file($file_path);
        $file_safe = db_escape($file_path);
        $preview_safe = $preview_file !== '' ? "'" . db_escape($preview_file) . "'" : 'NULL';
        $file_sql = ", file = '$file_safe'";
        $preview_sql = ", preview_file = $preview_safe";
    }

    if (has_uploaded_file($preview_file_upload)) {
        $preview_file = move_preview_file($preview_file_upload, $id_document);

        if ($preview_file === '') {
            return false;
        }

        $preview_safe = db_escape($preview_file);
        $preview_sql = ", preview_file = '$preview_safe'";
    }

    mysqli_query($conn, "UPDATE tb_dokumen
        SET id_category = '$id_category_safe',
            nama_dokumen = '$nama_safe',
            keterangan = '$keterangan_safe',
            tanggal_upload = '$tanggal_safe',
            status = 'menunggu',
            rejection_reason = NULL,
            approved_by = NULL,
            approved_at = NULL
            $file_sql
            $preview_sql
        WHERE id_document = '$id_safe'
    ");

    return mysqli_affected_rows($conn);
}

function approve_document($id_document)
{
    global $conn;

    $id_document = db_escape($id_document);
    $id_user_sql = current_user_id_sql();

    mysqli_query($conn, "UPDATE tb_dokumen
        SET status = 'disetujui',
            rejection_reason = NULL,
            approved_by = $id_user_sql,
            approved_at = NOW()
        WHERE id_document = '$id_document'
    ");

    return mysqli_affected_rows($conn);
}

function update_document_status($id_document, $status, $reason = '')
{
    global $conn;

    $allowed = ['menunggu', 'disetujui', 'ditolak'];

    if (!in_array($status, $allowed, true)) {
        return false;
    }

    $id_document = db_escape($id_document);
    $status = db_escape($status);
    $id_user_sql = current_user_id_sql();
    $reason = trim((string) $reason);

    if ($status === 'menunggu') {
        mysqli_query($conn, "UPDATE tb_dokumen
            SET status = 'menunggu',
                rejection_reason = NULL,
                approved_by = NULL,
                approved_at = NULL
            WHERE id_document = '$id_document'
        ");

        return mysqli_affected_rows($conn);
    }

    if ($status === 'ditolak') {
        if ($reason === '') {
            return false;
        }

        $reason_safe = db_escape($reason);

        mysqli_query($conn, "UPDATE tb_dokumen
            SET status = 'ditolak',
                rejection_reason = '$reason_safe',
                approved_by = $id_user_sql,
                approved_at = NOW()
            WHERE id_document = '$id_document'
        ");

        return mysqli_affected_rows($conn);
    }

    mysqli_query($conn, "UPDATE tb_dokumen
        SET status = '$status',
            rejection_reason = NULL,
            approved_by = $id_user_sql,
            approved_at = NOW()
        WHERE id_document = '$id_document'
    ");

    return mysqli_affected_rows($conn);
}

function reject_document($id_document, $reason = '')
{
    global $conn;

    $reason = trim((string) $reason);

    if ($reason === '') {
        return false;
    }

    $id_document = db_escape($id_document);
    $id_user_sql = current_user_id_sql();
    $reason_safe = db_escape($reason);

    mysqli_query($conn, "UPDATE tb_dokumen
        SET status = 'ditolak',
            rejection_reason = '$reason_safe',
            approved_by = $id_user_sql,
            approved_at = NOW()
        WHERE id_document = '$id_document'
    ");

    return mysqli_affected_rows($conn);
}

function delete_document($id_document)
{
    global $conn;

    $document = get_document($id_document);

    if (!$document) {
        return false;
    }

    if (!is_admin() && (string) ($document['id_user'] ?? '') !== current_user_id()) {
        return false;
    }

    $id_document = db_escape($id_document);

    mysqli_query($conn, "DELETE FROM tb_dokumen WHERE id_document = '$id_document'");

    return mysqli_affected_rows($conn);
}

function document_stats()
{
    $rows = query("SELECT
        COUNT(*) AS total,
        SUM(status = 'menunggu') AS menunggu,
        SUM(status = 'disetujui') AS disetujui,
        SUM(status = 'ditolak') AS ditolak
        FROM tb_dokumen
    ");

    return $rows[0] ?? [
        'total' => 0,
        'menunggu' => 0,
        'disetujui' => 0,
        'ditolak' => 0,
    ];
}
