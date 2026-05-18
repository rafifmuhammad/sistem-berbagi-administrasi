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

function document_link_url($document)
{
    return clean_http_url($document['link_url'] ?? '');
}

function document_has_link($document)
{
    return document_link_url($document) !== '';
}

function document_file_available($document)
{
    return document_storage_path($document['file'] ?? '', ['documents/files']) !== '';
}

function document_download_count($document)
{
    return max(0, (int) ($document['download_count'] ?? 0));
}

function current_user_id()
{
    return clean_entity_id($_SESSION['user']['id_user'] ?? '', 'USR');
}

function get_documents($approved_only = false, $owner_id = null)
{
    $conditions = [];
    $types = '';
    $params = [];

    if ($approved_only) {
        $conditions[] = "d.status = 'disetujui'";
    }

    if ($owner_id !== null && trim((string) $owner_id) !== '') {
        $owner_id = clean_entity_id($owner_id, 'USR');

        if ($owner_id === '') {
            return [];
        }

        $conditions[] = 'd.id_user = ?';
        $types .= 's';
        $params[] = $owner_id;
    }

    $where = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

    return db_select("SELECT d.*, c.nama_kategori, u.nama AS uploader, a.nama AS approver
        FROM tb_dokumen d
        JOIN tb_kategori c ON c.id_category = d.id_category
        LEFT JOIN tb_user u ON u.id_user = d.id_user
        LEFT JOIN tb_user a ON a.id_user = d.approved_by
        $where
        ORDER BY d.created_at DESC
    ", $types, $params);
}

function get_dashboard_documents($limit = 8)
{
    $limit = max(1, (int) $limit);

    return query("SELECT d.*, c.nama_kategori, u.nama AS uploader, a.nama AS approver
        FROM tb_dokumen d
        JOIN tb_kategori c ON c.id_category = d.id_category
        LEFT JOIN tb_user u ON u.id_user = d.id_user
        LEFT JOIN tb_user a ON a.id_user = d.approved_by
        ORDER BY
            CASE WHEN d.status = 'menunggu' THEN 0 ELSE 1 END,
            d.created_at DESC
        LIMIT $limit
    ");
}

function get_document($id_document)
{
    $id_document = clean_entity_id($id_document, 'DOC');

    if ($id_document === '') {
        return null;
    }

    $rows = db_select("SELECT d.*, c.nama_kategori, u.nama AS uploader, a.nama AS approver
        FROM tb_dokumen d
        JOIN tb_kategori c ON c.id_category = d.id_category
        LEFT JOIN tb_user u ON u.id_user = d.id_user
        LEFT JOIN tb_user a ON a.id_user = d.approved_by
        WHERE d.id_document = ?
        LIMIT 1
    ", 's', [$id_document]);

    return $rows[0] ?? null;
}

function next_document_id()
{
    return make_entity_id('DOC');
}

function add_document($data, $file, $preview_file_upload = [])
{
    $id_document = next_document_id();
    $id_category = clean_entity_id($data['id_category'] ?? '', 'CAT');
    $id_user = current_user_id();
    $nama = clean_input_text($data['nama_dokumen'] ?? '', 180);
    $keterangan = clean_input_text($data['keterangan'] ?? '', 2000);
    $link_url_raw = clean_input_text($data['link'] ?? ($data['link_url'] ?? ''), 2048);
    $link_url = clean_http_url($link_url_raw);
    $tanggal_upload = date('Y-m-d');
    $has_file_upload = uploaded_file_was_submitted($file);
    $has_preview_upload = uploaded_file_was_submitted($preview_file_upload);

    if (!is_valid_entity_id($id_document, 'DOC') || $id_category === '' || $id_user === '' || $nama === '') {
        return false;
    }

    if ($link_url_raw !== '' && $link_url === '') {
        set_document_file_error('Link dokumen harus berupa URL http atau https yang valid.');
        return false;
    }

    if (!$has_file_upload && $link_url === '') {
        set_document_file_error('Isi file dokumen atau link dokumen.');
        return false;
    }

    if (!$has_file_upload && $has_preview_upload) {
        set_document_file_error('File preview PDF hanya bisa diisi jika ada file dokumen.');
        return false;
    }

    $file_path = null;
    $preview_file = null;

    if ($has_file_upload) {
        $file_path = move_document_file($file, $id_document);

        if ($file_path === '') {
            return false;
        }

        $preview_file = $has_preview_upload
            ? move_preview_file($preview_file_upload, $id_document)
            : make_preview_file($file_path);
    }

    if ($has_preview_upload && $preview_file === '') {
        return false;
    }

    $preview_file = $preview_file !== '' ? $preview_file : null;
    $link_url = $link_url !== '' ? $link_url : null;

    return db_execute("INSERT INTO tb_dokumen
        (id_document, id_category, id_user, nama_dokumen, keterangan, tanggal_upload, file, preview_file, link_url, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'menunggu')
    ", 'sssssssss', [$id_document, $id_category, $id_user, $nama, $keterangan, $tanggal_upload, $file_path, $preview_file, $link_url]);
}

function update_document($id_document, $data, $file, $preview_file_upload = [])
{
    $id_document = clean_entity_id($id_document, 'DOC');
    $document = get_document($id_document);

    if (!$document) {
        return false;
    }

    $id_category = clean_entity_id($data['id_category'] ?? '', 'CAT');
    $nama = clean_input_text($data['nama_dokumen'] ?? '', 180);
    $keterangan = clean_input_text($data['keterangan'] ?? '', 2000);
    $link_url_raw = clean_input_text($data['link'] ?? ($data['link_url'] ?? ''), 2048);
    $link_url = clean_http_url($link_url_raw);
    $tanggal_upload = clean_input_text($data['tanggal_upload'] ?? ($document['tanggal_upload'] ?? date('Y-m-d')), 10);
    $has_file_upload = uploaded_file_was_submitted($file);
    $has_preview_upload = uploaded_file_was_submitted($preview_file_upload);

    if ($id_category === '' || $nama === '' || !is_valid_date_string($tanggal_upload)) {
        return false;
    }

    if ($link_url_raw !== '' && $link_url === '') {
        set_document_file_error('Link dokumen harus berupa URL http atau https yang valid.');
        return false;
    }

    if (!$has_file_upload && !document_file_available($document) && $link_url === '') {
        set_document_file_error('Isi file dokumen atau link dokumen.');
        return false;
    }

    if (!$has_file_upload && !document_file_available($document) && $has_preview_upload) {
        set_document_file_error('File preview PDF hanya bisa diisi jika ada file dokumen.');
        return false;
    }

    $sets = [
        'id_category = ?',
        'nama_dokumen = ?',
        'keterangan = ?',
        'tanggal_upload = ?',
        'link_url = ?',
        "status = 'menunggu'",
        'rejection_reason = NULL',
        'approved_by = NULL',
        'approved_at = NULL',
    ];
    $types = 'sssss';
    $link_url = $link_url !== '' ? $link_url : null;
    $params = [$id_category, $nama, $keterangan, $tanggal_upload, $link_url];
    $preview_param_index = null;

    if ($has_file_upload) {
        $file_path = move_document_file($file, $id_document);

        if ($file_path === '') {
            return false;
        }

        $preview_file = make_preview_file($file_path);
        $preview_file = $preview_file !== '' ? $preview_file : null;

        $sets[] = 'file = ?';
        $sets[] = 'preview_file = ?';
        $types .= 'ss';
        $params[] = $file_path;
        $params[] = $preview_file;
        $preview_param_index = count($params) - 1;
    }

    if ($has_preview_upload) {
        $preview_file = move_preview_file($preview_file_upload, $id_document);

        if ($preview_file === '') {
            return false;
        }

        if ($preview_param_index !== null) {
            $params[$preview_param_index] = $preview_file;
        } else {
            $sets[] = 'preview_file = ?';
            $types .= 's';
            $params[] = $preview_file;
        }
    }

    $types .= 's';
    $params[] = $id_document;

    return db_execute('UPDATE tb_dokumen SET ' . implode(', ', $sets) . ' WHERE id_document = ?', $types, $params);
}

function increment_document_download_count($id_document)
{
    $id_document = clean_entity_id($id_document, 'DOC');

    if ($id_document === '') {
        return false;
    }

    return db_execute("UPDATE tb_dokumen
        SET download_count = download_count + 1
        WHERE id_document = ? AND file IS NOT NULL AND file <> ''
    ", 's', [$id_document]);
}

function approve_document($id_document)
{
    $id_document = clean_entity_id($id_document, 'DOC');
    $id_user = current_user_id();

    if ($id_document === '' || $id_user === '') {
        return false;
    }

    return db_execute("UPDATE tb_dokumen
        SET status = 'disetujui',
            rejection_reason = NULL,
            approved_by = ?,
            approved_at = NOW()
        WHERE id_document = ?
    ", 'ss', [$id_user, $id_document]);
}

function update_document_status($id_document, $status, $reason = '')
{
    $allowed = ['menunggu', 'disetujui', 'ditolak'];
    $id_document = clean_entity_id($id_document, 'DOC');

    if ($id_document === '' || !in_array($status, $allowed, true)) {
        return false;
    }

    $id_user = current_user_id();
    $reason = clean_input_text($reason, 1000);

    if ($status === 'menunggu') {
        return db_execute("UPDATE tb_dokumen
            SET status = 'menunggu',
                rejection_reason = NULL,
                approved_by = NULL,
                approved_at = NULL
            WHERE id_document = ?
        ", 's', [$id_document]);
    }

    if ($status === 'ditolak') {
        if ($reason === '' || $id_user === '') {
            return false;
        }

        return db_execute("UPDATE tb_dokumen
            SET status = 'ditolak',
                rejection_reason = ?,
                approved_by = ?,
                approved_at = NOW()
            WHERE id_document = ?
        ", 'sss', [$reason, $id_user, $id_document]);
    }

    if ($id_user === '') {
        return false;
    }

    return db_execute("UPDATE tb_dokumen
        SET status = ?,
            rejection_reason = NULL,
            approved_by = ?,
            approved_at = NOW()
        WHERE id_document = ?
    ", 'sss', [$status, $id_user, $id_document]);
}

function reject_document($id_document, $reason = '')
{
    $id_document = clean_entity_id($id_document, 'DOC');
    $id_user = current_user_id();
    $reason = clean_input_text($reason, 1000);

    if ($id_document === '' || $id_user === '' || $reason === '') {
        return false;
    }

    return db_execute("UPDATE tb_dokumen
        SET status = 'ditolak',
            rejection_reason = ?,
            approved_by = ?,
            approved_at = NOW()
        WHERE id_document = ?
    ", 'sss', [$reason, $id_user, $id_document]);
}

function delete_document($id_document)
{
    $id_document = clean_entity_id($id_document, 'DOC');

    $document = get_document($id_document);

    if (!$document) {
        return false;
    }

    if (!is_admin() && (string) ($document['id_user'] ?? '') !== current_user_id()) {
        return false;
    }

    return db_execute("DELETE FROM tb_dokumen WHERE id_document = ?", 's', [$id_document]);
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
