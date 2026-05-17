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
        $path = app_clean_path($path);

        return $path === '' ? ($base === '' ? '/' : $base . '/') : $base . '/' . $path;
    }
}

if (!function_exists('app_clean_path')) {
    function app_clean_path($path)
    {
        $path = ltrim((string) $path, '/');

        if ($path === '') {
            return '';
        }

        $fragment = '';
        $fragment_pos = strpos($path, '#');

        if ($fragment_pos !== false) {
            $fragment = substr($path, $fragment_pos);
            $path = substr($path, 0, $fragment_pos);
        }

        $query = '';
        $query_pos = strpos($path, '?');

        if ($query_pos !== false) {
            $query = substr($path, $query_pos + 1);
            $path = substr($path, 0, $query_pos);
        }

        $params = [];

        if ($query !== '') {
            parse_str(str_replace('&amp;', '&', $query), $params);
        }

        $clean_path = $path;

        if ($path === 'index.php') {
            $clean_path = '';
        } elseif ($path === 'login.php') {
            $clean_path = 'login/';
        } elseif ($path === 'register.php') {
            $clean_path = 'register/';
        } elseif ($path === 'logout.php') {
            $clean_path = 'logout/';
        } elseif ($path === 'views/dashboard/dashboard.php') {
            $clean_path = 'dashboard/';
        } elseif ($path === 'views/documents/document_management.php') {
            $clean_path = clean_resource_route('documents', $params);
        } elseif ($path === 'views/documents/document_add.php') {
            $clean_path = 'documents/add/';
        } elseif ($path === 'views/documents/document_edit.php' && !empty($params['id'])) {
            $clean_path = 'documents/' . rawurlencode((string) $params['id']) . '/edit/';
            unset($params['id']);
        } elseif ($path === 'views/documents/document_detail.php' && !empty($params['id'])) {
            $clean_path = 'documents/' . rawurlencode((string) $params['id']) . '/';
            unset($params['id']);
        } elseif ($path === 'views/documents/document_action.php') {
            $clean_path = clean_document_action_route($params);
        } elseif ($path === 'views/categories/category_management.php') {
            $clean_path = clean_resource_route('categories', $params);
        } elseif ($path === 'views/users/user_management.php') {
            $clean_path = clean_resource_route('users', $params);
        } elseif ($path === 'files/preview.php' && !empty($params['id'])) {
            $clean_path = 'files/preview/' . rawurlencode((string) $params['id']) . '/';
            unset($params['id']);
        } elseif ($path === 'files/download.php' && !empty($params['id'])) {
            $clean_path = 'files/download/' . rawurlencode((string) $params['id']) . '/';
            unset($params['id']);
        }

        $query = http_build_query($params);

        if ($query !== '') {
            $clean_path .= '?' . $query;
        }

        return $clean_path . $fragment;
    }
}

if (!function_exists('clean_resource_route')) {
    function clean_resource_route($resource, &$params)
    {
        if (($params['action'] ?? '') === 'delete' && !empty($params['id'])) {
            $id = rawurlencode((string) $params['id']);
            unset($params['action'], $params['id']);

            return trim((string) $resource, '/') . '/' . $id . '/delete/';
        }

        return trim((string) $resource, '/') . '/';
    }
}

if (!function_exists('clean_document_action_route')) {
    function clean_document_action_route(&$params)
    {
        $action = (string) ($params['action'] ?? '');
        $id = (string) ($params['id'] ?? '');
        $clean_actions = ['approve', 'reject', 'delete'];

        if ($id !== '' && in_array($action, $clean_actions, true)) {
            unset($params['action'], $params['id']);

            return 'documents/' . rawurlencode($id) . '/' . $action . '/';
        }

        return 'documents/action/';
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
