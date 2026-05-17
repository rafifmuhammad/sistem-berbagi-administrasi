<?php

if (!defined('APP_BASE_PATH')) {
    $default_base = '/sistem-berbagi-administrasi';
    $base_path = getenv('APP_BASE_PATH');

    if ($base_path === false) {
        $document_root = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
        $project_root = realpath(__DIR__ . '/../');

        if ($document_root && $project_root && strpos($project_root, $document_root) === 0) {
            $base_path = '/' . trim(str_replace('\\', '/', substr($project_root, strlen($document_root))), '/');
            if ($base_path === '/') {
                $base_path = '';
            }
        } else {
            $base_path = $default_base;
        }
    }

    if ($base_path !== '') {
        $base_path = '/' . trim((string) $base_path, '/');
    }

    define('APP_BASE_PATH', rtrim((string) $base_path, '/'));
}

if (!function_exists('app_url')) {
    function app_url($path = '')
    {
        $base = rtrim(APP_BASE_PATH, '/');
        $path = ltrim((string) $path, '/');

        return $path === '' ? ($base === '' ? '/' : $base . '/') : $base . '/' . $path;
    }
}

if (!function_exists('redirect_to')) {
    function redirect_to($path)
    {
        header('Location: ' . app_url($path));
        exit;
    }
}

if (!function_exists('h')) {
    function h($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('make_entity_id')) {
    function make_entity_id($prefix)
    {
        $seed = strtoupper(str_replace('.', '', uniqid('', true)));

        return strtoupper((string) $prefix) . '-' . $seed;
    }
}

if (!function_exists('html_id_suffix')) {
    function html_id_suffix($value)
    {
        return preg_replace('/[^A-Za-z0-9_-]/', '_', (string) $value);
    }
}
