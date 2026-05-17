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

function document_status_badge($status)
{
    $classes = [
        'menunggu' => 'badge-soft-amber',
        'disetujui' => 'badge-soft-green',
        'ditolak' => 'badge-soft-slate',
    ];

    $class = $classes[$status] ?? 'badge-soft-amber';

    return '<span class="badge ' . $class . '">' . h(document_status_label($status)) . '</span>';
}

function get_documents($approved_only = false)
{
    $where = $approved_only ? "WHERE d.status = 'disetujui'" : '';

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
    return 'DOC-' . date('ymd') . '-' . strtoupper(substr(uniqid(), -5));
}

function add_document($data, $file, $preview_file_upload = [])
{
    global $conn;

    $id_document = next_document_id();
    $id_category = (int) ($data['id_category'] ?? 0);
    $id_user = (int) ($_SESSION['user']['id_user'] ?? 0);
    $nama = trim($data['nama_dokumen'] ?? '');
    $keterangan = trim($data['keterangan'] ?? '');
    $tanggal_upload = trim($data['tanggal_upload'] ?? date('Y-m-d'));

    if ($id_category <= 0 || $nama === '') {
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
    $keterangan_safe = db_escape($keterangan);
    $tanggal_safe = db_escape($tanggal_upload);
    $file_safe = db_escape($file_path);
    $preview_safe = $preview_file !== '' ? "'" . db_escape($preview_file) . "'" : 'NULL';

    mysqli_query($conn, "INSERT INTO tb_dokumen
        (id_document, id_category, id_user, nama_dokumen, keterangan, tanggal_upload, file, preview_file, status)
        VALUES ('$id_document', $id_category, $id_user, '$nama_safe', '$keterangan_safe', '$tanggal_safe', '$file_safe', $preview_safe, 'menunggu')
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
    $id_category = (int) ($data['id_category'] ?? 0);
    $nama = trim($data['nama_dokumen'] ?? '');
    $keterangan = trim($data['keterangan'] ?? '');
    $tanggal_upload = trim($data['tanggal_upload'] ?? date('Y-m-d'));

    if ($id_category <= 0 || $nama === '') {
        return false;
    }

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
        SET id_category = $id_category,
            nama_dokumen = '$nama_safe',
            keterangan = '$keterangan_safe',
            tanggal_upload = '$tanggal_safe',
            status = 'menunggu',
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
    $id_user = (int) ($_SESSION['user']['id_user'] ?? 0);

    mysqli_query($conn, "UPDATE tb_dokumen
        SET status = 'disetujui',
            approved_by = $id_user,
            approved_at = NOW()
        WHERE id_document = '$id_document'
    ");

    return mysqli_affected_rows($conn);
}

function update_document_status($id_document, $status)
{
    global $conn;

    $allowed = ['menunggu', 'disetujui', 'ditolak'];

    if (!in_array($status, $allowed, true)) {
        return false;
    }

    $id_document = db_escape($id_document);
    $status = db_escape($status);
    $id_user = (int) ($_SESSION['user']['id_user'] ?? 0);

    if ($status === 'menunggu') {
        mysqli_query($conn, "UPDATE tb_dokumen
            SET status = 'menunggu',
                approved_by = NULL,
                approved_at = NULL
            WHERE id_document = '$id_document'
        ");

        return mysqli_affected_rows($conn);
    }

    mysqli_query($conn, "UPDATE tb_dokumen
        SET status = '$status',
            approved_by = $id_user,
            approved_at = NOW()
        WHERE id_document = '$id_document'
    ");

    return mysqli_affected_rows($conn);
}

function reject_document($id_document)
{
    global $conn;

    $id_document = db_escape($id_document);
    $id_user = (int) ($_SESSION['user']['id_user'] ?? 0);

    mysqli_query($conn, "UPDATE tb_dokumen
        SET status = 'ditolak',
            approved_by = $id_user,
            approved_at = NOW()
        WHERE id_document = '$id_document'
    ");

    return mysqli_affected_rows($conn);
}

function delete_document($id_document)
{
    global $conn;

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
