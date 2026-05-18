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

if (!defined('APP_CLEAN_URL')) {
    $clean_url = getenv('APP_CLEAN_URL');
    $disabled_values = ['0', 'false', 'off', 'no'];

    define('APP_CLEAN_URL', $clean_url === false || !in_array(strtolower((string) $clean_url), $disabled_values, true));
}

if (!function_exists('app_url')) {
    function app_url($path = '')
    {
        $base = rtrim(APP_BASE_PATH, '/');
        $path = ltrim((string) $path, '/');

        if (APP_CLEAN_URL) {
            $path = app_clean_path($path);
        }

        return $path === '' ? ($base === '' ? '/' : $base . '/') : $base . '/' . $path;
    }
}

if (!function_exists('app_clean_path')) {
    function app_clean_path($path)
    {
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
        } elseif ($path === 'views/visits/visit_management.php') {
            $clean_path = 'dashboard/visits/';
        } elseif ($path === 'views/documents/document_public_detail.php' && !empty($params['id'])) {
            $clean_path = 'public/documents/' . rawurlencode((string) $params['id']) . '/';
            unset($params['id']);
        } elseif ($path === 'views/documents/document_management.php') {
            $clean_path = clean_resource_route('admin/documents', $params);
        } elseif ($path === 'views/documents/document_add.php') {
            $clean_path = 'admin/documents/add/';
        } elseif ($path === 'views/documents/document_edit.php' && !empty($params['id'])) {
            $clean_path = 'admin/documents/' . rawurlencode((string) $params['id']) . '/edit/';
            unset($params['id']);
        } elseif ($path === 'views/documents/document_detail.php' && !empty($params['id'])) {
            $clean_path = 'admin/documents/' . rawurlencode((string) $params['id']) . '/';
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

            return 'admin/documents/' . rawurlencode($id) . '/' . $action . '/';
        }

        return 'admin/documents/action/';
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

if (!function_exists('clean_input_text')) {
    function clean_input_text($value, $max_length = 255)
    {
        $value = trim((string) $value);
        $value = str_replace("\0", '', $value);
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/', '', $value);

        if ($max_length > 0 && strlen($value) > $max_length) {
            $value = substr($value, 0, $max_length);
        }

        return $value;
    }
}

if (!function_exists('clean_email')) {
    function clean_email($value)
    {
        return strtolower(clean_input_text($value, 254));
    }
}

if (!function_exists('is_valid_email')) {
    function is_valid_email($value)
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }
}

if (!function_exists('is_valid_entity_id')) {
    function is_valid_entity_id($value, $prefix = '')
    {
        $value = trim((string) $value);

        if ($value === '') {
            return false;
        }

        $prefix = strtoupper(trim((string) $prefix));

        if ($prefix === 'USR' && preg_match('/^[0-9]{1,20}$/', $value) === 1) {
            return true;
        }

        $pattern = $prefix !== ''
            ? '/^' . preg_quote($prefix, '/') . '-[A-Z0-9]{8,64}$/'
            : '/^[A-Z]{2,10}-[A-Z0-9]{8,64}$/';

        return preg_match($pattern, strtoupper($value)) === 1;
    }
}

if (!function_exists('clean_entity_id')) {
    function clean_entity_id($value, $prefix = '')
    {
        $value = strtoupper(clean_input_text($value, 80));

        return is_valid_entity_id($value, $prefix) ? $value : '';
    }
}

if (!function_exists('is_valid_date_string')) {
    function is_valid_date_string($value)
    {
        $value = trim((string) $value);

        if ($value === '') {
            return true;
        }

        $date = DateTime::createFromFormat('!Y-m-d', $value);

        return $date && $date->format('Y-m-d') === $value;
    }
}

if (!function_exists('clean_http_url')) {
    function clean_http_url($value, $max_length = 2048)
    {
        $value = clean_input_text($value, $max_length);

        if ($value === '') {
            return '';
        }

        if (filter_var($value, FILTER_VALIDATE_URL) === false) {
            return '';
        }

        $scheme = strtolower((string) parse_url($value, PHP_URL_SCHEME));

        return in_array($scheme, ['http', 'https'], true) ? $value : '';
    }
}

if (!function_exists('path_is_inside')) {
    function path_is_inside($path, $base)
    {
        $path = realpath($path);
        $base = realpath($base);

        if (!$path || !$base) {
            return false;
        }

        $path = rtrim(str_replace('\\', '/', $path), '/');
        $base = rtrim(str_replace('\\', '/', $base), '/');

        return $path === $base || strpos($path, $base . '/') === 0;
    }
}

if (!function_exists('render_favicon_links')) {
    function render_favicon_links()
    {
        $version = '1.0';
        $links = [
            ['rel' => 'icon', 'type' => 'image/png', 'sizes' => '32x32', 'href' => 'assets/img/favicon/favicon-32x32.png'],
            ['rel' => 'icon', 'type' => 'image/png', 'sizes' => '16x16', 'href' => 'assets/img/favicon/favicon-16x16.png'],
            ['rel' => 'shortcut icon', 'href' => 'favicon.ico'],
            ['rel' => 'apple-touch-icon', 'sizes' => '180x180', 'href' => 'assets/img/favicon/apple-touch-icon.png'],
        ];

        foreach ($links as $link) {
            $attributes = [
                'rel="' . h($link['rel']) . '"',
                'href="' . h(app_url($link['href'])) . '?v=' . h($version) . '"',
            ];

            if (!empty($link['type'])) {
                $attributes[] = 'type="' . h($link['type']) . '"';
            }

            if (!empty($link['sizes'])) {
                $attributes[] = 'sizes="' . h($link['sizes']) . '"';
            }

            echo '    <link ' . implode(' ', $attributes) . ' />' . PHP_EOL;
        }

        echo '    <meta name="theme-color" content="#005878" />' . PHP_EOL;
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
