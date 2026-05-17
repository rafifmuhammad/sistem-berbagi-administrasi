<?php

function upload_dir($folder)
{
    return __DIR__ . '/../documents/' . trim($folder, '/\\') . '/';
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
    if (empty($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return '';
    }

    $original_name = $file['name'] ?? '';
    $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
    $allowed = ['pdf', 'doc', 'docx'];

    if (!in_array($extension, $allowed, true)) {
        return '';
    }

    $target_dir = upload_dir('files');

    if (!is_dir($target_dir)) {
        @mkdir($target_dir, 0755, true);
    }

    $base_name = pathinfo($original_name, PATHINFO_FILENAME);
    $file_name = safe_file_name($document_id . '_' . $base_name . '_' . time() . '.' . $extension);
    $target_path = $target_dir . $file_name;

    if (!move_uploaded_file($file['tmp_name'], $target_path)) {
        return '';
    }

    return 'documents/files/' . $file_name;
}

function has_uploaded_file($file)
{
    return !empty($file) && ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK;
}

function move_preview_file($file, $document_id)
{
    if (!has_uploaded_file($file)) {
        return '';
    }

    $original_name = $file['name'] ?? '';
    $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));

    if ($extension !== 'pdf') {
        return '';
    }

    $target_dir = upload_dir('previews');

    if (!is_dir($target_dir)) {
        @mkdir($target_dir, 0755, true);
    }

    $base_name = pathinfo($original_name, PATHINFO_FILENAME);
    $file_name = safe_file_name($document_id . '_preview_' . $base_name . '_' . time() . '.pdf');
    $target_path = $target_dir . $file_name;

    if (!move_uploaded_file($file['tmp_name'], $target_path)) {
        return '';
    }

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
    return 'files/preview.php?id=' . rawurlencode($document['id_document'] ?? '');
}

function document_preview_path($document)
{
    $preview_file = $document['preview_file'] ?? '';

    if (make_preview_file($preview_file) !== '') {
        return $preview_file;
    }

    $file = $document['file'] ?? '';

    return make_preview_file($file);
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
