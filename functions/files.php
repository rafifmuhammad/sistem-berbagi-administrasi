<?php

function upload_dir($folder)
{
    return __DIR__ . '/../documents/' . trim($folder, '/\\') . '/';
}

function set_document_file_error($message)
{
    $GLOBALS['document_file_error'] = trim((string) $message);
}

function document_file_error()
{
    return $GLOBALS['document_file_error'] ?? '';
}

function ini_size_to_bytes($size)
{
    $size = trim((string) $size);

    if ($size === '') {
        return 0;
    }

    $unit = strtolower(substr($size, -1));
    $number = (float) $size;

    if ($unit === 'g') {
        return (int) ($number * 1024 * 1024 * 1024);
    }

    if ($unit === 'm') {
        return (int) ($number * 1024 * 1024);
    }

    if ($unit === 'k') {
        return (int) ($number * 1024);
    }

    return (int) $number;
}

function format_upload_bytes($bytes)
{
    $bytes = (int) $bytes;

    if ($bytes >= 1024 * 1024) {
        return round($bytes / 1024 / 1024, 2) . ' MB';
    }

    if ($bytes >= 1024) {
        return round($bytes / 1024, 2) . ' KB';
    }

    return $bytes . ' byte';
}

function request_upload_error()
{
    $content_length = (int) ($_SERVER['CONTENT_LENGTH'] ?? 0);
    $post_max_raw = ini_get('post_max_size');
    $post_max = ini_size_to_bytes($post_max_raw);

    if ($post_max > 0 && $content_length > $post_max) {
        return 'Ukuran total upload ' . format_upload_bytes($content_length) . ' melebihi post_max_size server (' . $post_max_raw . ').';
    }

    return '';
}

function upload_error_message($error, $label = 'File')
{
    $messages = [
        UPLOAD_ERR_INI_SIZE => $label . ' melebihi batas upload server. Naikkan upload_max_filesize dan post_max_size, atau kecilkan ukuran file.',
        UPLOAD_ERR_FORM_SIZE => $label . ' melebihi batas ukuran yang diizinkan form.',
        UPLOAD_ERR_PARTIAL => $label . ' hanya terunggah sebagian. Coba unggah ulang.',
        UPLOAD_ERR_NO_FILE => $label . ' wajib dipilih.',
        UPLOAD_ERR_NO_TMP_DIR => 'Server tidak memiliki folder sementara untuk upload.',
        UPLOAD_ERR_CANT_WRITE => 'Server gagal menulis file upload ke disk.',
        UPLOAD_ERR_EXTENSION => 'Upload diblokir oleh ekstensi PHP di server.',
    ];

    return $messages[$error] ?? $label . ' gagal diunggah. Kode error: ' . (string) $error . '.';
}

function ensure_upload_dir($folder)
{
    $folder = trim($folder, '/\\');
    $target_dir = upload_dir($folder);

    if (!is_dir($target_dir) && !@mkdir($target_dir, 0775, true) && !is_dir($target_dir)) {
        set_document_file_error('Folder documents/' . $folder . ' tidak bisa dibuat di server.');
        return '';
    }

    if (!is_writable($target_dir)) {
        @chmod($target_dir, 0775);
    }

    if (!is_writable($target_dir)) {
        set_document_file_error('Folder documents/' . $folder . ' tidak bisa ditulis. Periksa permission/owner folder di server deploy.');
        return '';
    }

    return $target_dir;
}

function safe_file_name($file_name)
{
    $file_name = str_replace(['/', '\\'], '_', $file_name);
    $file_name = preg_replace('/[\x00-\x1F\x7F]+/', '', $file_name);
    $file_name = preg_replace('/\s+/', '-', trim($file_name));

    return $file_name !== '' ? $file_name : uniqid('dokumen_', true);
}

function move_document_file($file, $document_id)
{
    set_document_file_error('');

    if (empty($file)) {
        set_document_file_error(upload_error_message(UPLOAD_ERR_NO_FILE, 'File dokumen'));
        return '';
    }

    $error = $file['error'] ?? UPLOAD_ERR_NO_FILE;

    if ($error !== UPLOAD_ERR_OK) {
        set_document_file_error(upload_error_message($error, 'File dokumen'));
        return '';
    }

    $original_name = $file['name'] ?? '';
    $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
    $allowed = ['pdf', 'doc', 'docx'];

    if (!in_array($extension, $allowed, true)) {
        set_document_file_error('Format file dokumen harus PDF, DOC, atau DOCX.');
        return '';
    }

    $tmp_name = $file['tmp_name'] ?? '';

    if ($tmp_name === '' || !is_uploaded_file($tmp_name)) {
        set_document_file_error('File dokumen tidak valid sebagai file upload.');
        return '';
    }

    $target_dir = ensure_upload_dir('files');

    if ($target_dir === '') {
        return '';
    }

    $base_name = pathinfo($original_name, PATHINFO_FILENAME);
    $file_name = safe_file_name($document_id . '_' . $base_name . '_' . time() . '.' . $extension);
    $target_path = $target_dir . $file_name;

    if (!move_uploaded_file($tmp_name, $target_path)) {
        set_document_file_error('File dokumen gagal dipindahkan ke folder documents/files. Periksa permission folder server.');
        return '';
    }

    @chmod($target_path, 0644);

    return 'documents/files/' . $file_name;
}

function has_uploaded_file($file)
{
    return !empty($file) && ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK;
}

function uploaded_file_was_submitted($file)
{
    return !empty($file) && ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
}

function move_preview_file($file, $document_id)
{
    if (!uploaded_file_was_submitted($file)) {
        return '';
    }

    $error = $file['error'] ?? UPLOAD_ERR_NO_FILE;

    if ($error !== UPLOAD_ERR_OK) {
        set_document_file_error(upload_error_message($error, 'File preview PDF'));
        return '';
    }

    $original_name = $file['name'] ?? '';
    $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));

    if ($extension !== 'pdf') {
        set_document_file_error('File preview harus berformat PDF.');
        return '';
    }

    $tmp_name = $file['tmp_name'] ?? '';

    if ($tmp_name === '' || !is_uploaded_file($tmp_name)) {
        set_document_file_error('File preview PDF tidak valid sebagai file upload.');
        return '';
    }

    $target_dir = ensure_upload_dir('previews');

    if ($target_dir === '') {
        return '';
    }

    $base_name = pathinfo($original_name, PATHINFO_FILENAME);
    $file_name = safe_file_name($document_id . '_preview_' . $base_name . '_' . time() . '.pdf');
    $target_path = $target_dir . $file_name;

    if (!move_uploaded_file($tmp_name, $target_path)) {
        set_document_file_error('File preview PDF gagal dipindahkan ke folder documents/previews. Periksa permission folder server.');
        return '';
    }

    @chmod($target_path, 0644);

    return 'documents/previews/' . $file_name;
}

function make_preview_file($file_path)
{
    if ($file_path === '') {
        return '';
    }

    $absolute_file = __DIR__ . '/../' . $file_path;
    $extension = strtolower(pathinfo($absolute_file, PATHINFO_EXTENSION));

    if ($extension === 'pdf' && is_file($absolute_file)) {
        return $file_path;
    }

    return '';
}

function document_file_extension($file_path)
{
    return strtolower(pathinfo($file_path ?? '', PATHINFO_EXTENSION));
}

function document_preview_url($document)
{
    $url = 'files/preview.php?id=' . rawurlencode($document['id_document'] ?? '');
    $preview_path = document_preview_path($document);

    if ($preview_path !== '' && document_file_extension($preview_path) === 'pdf') {
        return $url . '#toolbar=0&navpanes=0&scrollbar=1&view=FitH';
    }

    return $url;
}

function document_preview_path($document)
{
    $preview_file = $document['preview_file'] ?? '';
    $document_id = (string) ($document['id_document'] ?? '');

    if (preview_file_belongs_to_document($preview_file, $document_id) && make_preview_file($preview_file) !== '') {
        return $preview_file;
    }

    $file = $document['file'] ?? '';

    return make_preview_file($file);
}

function preview_file_belongs_to_document($preview_file, $document_id)
{
    $preview_file = trim((string) $preview_file);
    $document_id = trim((string) $document_id);

    if ($preview_file === '' || $document_id === '') {
        return false;
    }

    $file_name = basename(str_replace('\\', '/', $preview_file));

    return strpos($file_name, $document_id . '_preview_') === 0;
}

function docx_node_text_html($node)
{
    $html = '';

    foreach ($node->childNodes as $child) {
        if ($child->nodeType !== XML_ELEMENT_NODE) {
            continue;
        }

        if ($child->localName === 't') {
            $html .= h($child->textContent);
            continue;
        }

        if ($child->localName === 'tab') {
            $html .= '&emsp;';
            continue;
        }

        if ($child->localName === 'br') {
            $html .= '<br />';
            continue;
        }

        $html .= docx_node_text_html($child);
    }

    return $html;
}

function docx_run_html($xpath, $run)
{
    $html = docx_node_text_html($run);

    if ($html === '') {
        return '';
    }

    if ($xpath->query('./w:rPr/w:b', $run)->length > 0) {
        $html = '<strong>' . $html . '</strong>';
    }

    if ($xpath->query('./w:rPr/w:i', $run)->length > 0) {
        $html = '<em>' . $html . '</em>';
    }

    if ($xpath->query('./w:rPr/w:u', $run)->length > 0) {
        $html = '<u>' . $html . '</u>';
    }

    return $html;
}

function docx_paragraph_html($xpath, $paragraph)
{
    $html = '';

    foreach ($paragraph->childNodes as $child) {
        if ($child->nodeType !== XML_ELEMENT_NODE) {
            continue;
        }

        if ($child->localName === 'r') {
            $html .= docx_run_html($xpath, $child);
        }

        if ($child->localName === 'hyperlink') {
            foreach ($xpath->query('./w:r', $child) as $run) {
                $html .= docx_run_html($xpath, $run);
            }
        }
    }

    $html = trim($html);

    if ($html === '') {
        return '';
    }

    $style = '';
    $style_node = $xpath->query('./w:pPr/w:pStyle', $paragraph)->item(0);

    if ($style_node) {
        $style = strtolower($style_node->getAttribute('w:val') ?: $style_node->getAttribute('val'));
    }

    if (strpos($style, 'title') !== false || strpos($style, 'heading1') !== false) {
        return '<h2>' . $html . '</h2>';
    }

    if (strpos($style, 'heading') !== false) {
        return '<h3>' . $html . '</h3>';
    }

    return '<p>' . $html . '</p>';
}

function docx_table_html($xpath, $table)
{
    $html = '<table>';

    foreach ($xpath->query('./w:tr', $table) as $row) {
        $html .= '<tr>';

        foreach ($xpath->query('./w:tc', $row) as $cell) {
            $cell_html = '';

            foreach ($xpath->query('./w:p', $cell) as $paragraph) {
                $cell_html .= docx_paragraph_html($xpath, $paragraph);
            }

            $html .= '<td>' . ($cell_html !== '' ? $cell_html : '&nbsp;') . '</td>';
        }

        $html .= '</tr>';
    }

    return $html . '</table>';
}

function docx_preview_html($absolute_file)
{
    if (!class_exists('ZipArchive') || !class_exists('DOMDocument')) {
        return '';
    }

    $zip = new ZipArchive();

    if ($zip->open($absolute_file) !== true) {
        return '';
    }

    $xml = $zip->getFromName('word/document.xml');
    $zip->close();

    if ($xml === false || trim($xml) === '') {
        return '';
    }

    $dom = new DOMDocument();
    $loaded = @$dom->loadXML($xml, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);

    if (!$loaded) {
        return '';
    }

    $xpath = new DOMXPath($dom);
    $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
    $body = $xpath->query('//w:body')->item(0);

    if (!$body) {
        return '';
    }

    $html = '';

    foreach ($body->childNodes as $child) {
        if ($child->nodeType !== XML_ELEMENT_NODE) {
            continue;
        }

        if ($child->localName === 'p') {
            $html .= docx_paragraph_html($xpath, $child);
        }

        if ($child->localName === 'tbl') {
            $html .= docx_table_html($xpath, $child);
        }
    }

    return $html;
}
