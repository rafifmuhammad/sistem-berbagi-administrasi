<?php

if (!defined('APP_BASE_PATH')) {
    $request_uri = str_replace('\\', '/', $_SERVER['REQUEST_URI'] ?? '');
    $default_base = '/sistem-berbagi-administrasi';
    $base_path = getenv('APP_BASE_PATH') ?: $default_base;

    if ($base_path !== '' && strpos($request_uri, $base_path) === false) {
        $base_path = '';
    }

    define('APP_BASE_PATH', rtrim($base_path, '/'));
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
