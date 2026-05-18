<?php

function visit_table_exists()
{
    return !empty(db_select("SHOW TABLES LIKE 'tb_kunjungan'"));
}

function ensure_visit_table()
{
    if (visit_table_exists()) {
        return true;
    }

    $result = db_execute("CREATE TABLE IF NOT EXISTS tb_kunjungan (
        id_visit BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        id_user VARCHAR(30) NULL,
        session_id VARCHAR(128) NULL,
        ip_address VARCHAR(45) NULL,
        user_agent TEXT NULL,
        browser VARCHAR(80) NULL,
        operating_system VARCHAR(80) NULL,
        device_type VARCHAR(30) NULL,
        page_url VARCHAR(2048) NULL,
        referrer VARCHAR(2048) NULL,
        visited_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

        INDEX idx_visit_user (id_user),
        INDEX idx_visit_session (session_id),
        INDEX idx_visit_visited_at (visited_at),
        INDEX idx_visit_device_type (device_type),

        CONSTRAINT fk_visit_user
            FOREIGN KEY (id_user)
            REFERENCES tb_user (id_user)
            ON UPDATE CASCADE
            ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    return $result !== false && visit_table_exists();
}

function visit_client_ip()
{
    $candidates = [
        $_SERVER['HTTP_CF_CONNECTING_IP'] ?? '',
        $_SERVER['HTTP_X_REAL_IP'] ?? '',
        $_SERVER['REMOTE_ADDR'] ?? '',
    ];

    $forwarded_for = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';

    if ($forwarded_for !== '') {
        foreach (explode(',', $forwarded_for) as $forwarded_ip) {
            $candidates[] = trim($forwarded_ip);
        }
    }

    foreach ($candidates as $candidate) {
        $candidate = clean_input_text($candidate, 45);

        if ($candidate !== '' && filter_var($candidate, FILTER_VALIDATE_IP) !== false) {
            return $candidate;
        }
    }

    return '';
}

function visit_page_url()
{
    return clean_input_text($_SERVER['REQUEST_URI'] ?? '', 2048);
}

function visit_referrer()
{
    return clean_http_url($_SERVER['HTTP_REFERER'] ?? '');
}

function visit_operating_system($user_agent)
{
    $checks = [
        'Windows' => 'Windows',
        'Android' => 'Android',
        'iPhone|iPad|iPod' => 'iOS',
        'Mac OS X|Macintosh' => 'macOS',
        'Linux' => 'Linux',
    ];

    foreach ($checks as $pattern => $label) {
        if (preg_match('/' . $pattern . '/i', $user_agent)) {
            return $label;
        }
    }

    return 'Tidak dikenal';
}

function visit_browser($user_agent)
{
    $checks = [
        'Edg' => 'Edge',
        'OPR|Opera' => 'Opera',
        'Chrome' => 'Chrome',
        'Firefox' => 'Firefox',
        'Safari' => 'Safari',
    ];

    foreach ($checks as $pattern => $label) {
        if (preg_match('/' . $pattern . '/i', $user_agent)) {
            return $label;
        }
    }

    return 'Tidak dikenal';
}

function visit_device_type($user_agent)
{
    if (preg_match('/tablet|ipad/i', $user_agent)) {
        return 'Tablet';
    }

    if (preg_match('/mobile|android|iphone|ipod/i', $user_agent)) {
        return 'Mobile';
    }

    return 'Desktop';
}

function should_record_visit()
{
    if (PHP_SAPI === 'cli') {
        return false;
    }

    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
        return false;
    }

    $uri = visit_page_url();
    $ignored = [
        '/files/preview/',
        '/files/download/',
        'files/preview.php',
        'files/download.php',
        '/admin/documents/action/',
        '/documents/action/',
        'document_action.php',
    ];

    foreach ($ignored as $pattern) {
        if (strpos($uri, $pattern) !== false) {
            return false;
        }
    }

    return $uri !== '' && ensure_visit_table();
}

function visit_identity_key($row)
{
    $date = substr((string) ($row['visited_at'] ?? date('Y-m-d')), 0, 10);

    return implode('|', [
        $date,
        (string) ($row['id_user'] ?? ''),
        (string) ($row['session_id'] ?? ''),
        (string) ($row['ip_address'] ?? ''),
        sha1((string) ($row['user_agent'] ?? '')),
    ]);
}

function find_today_visit($id_user, $session_id, $ip_address, $user_agent)
{
    $rows = db_select("SELECT id_visit
        FROM tb_kunjungan
        WHERE DATE(visited_at) = CURDATE()
          AND COALESCE(id_user, '') = ?
          AND COALESCE(session_id, '') = ?
          AND COALESCE(ip_address, '') = ?
          AND COALESCE(user_agent, '') = ?
        ORDER BY visited_at DESC
        LIMIT 1
    ", 'ssss', [
        $id_user ?? '',
        $session_id,
        $ip_address,
        $user_agent,
    ]);

    return (int) ($rows[0]['id_visit'] ?? 0);
}

function record_visit()
{
    if (!should_record_visit()) {
        return false;
    }

    $user_agent = clean_input_text($_SERVER['HTTP_USER_AGENT'] ?? '', 1000);
    $id_user = current_user_id();
    $id_user = $id_user !== '' ? $id_user : null;
    $session_id = clean_input_text(session_id(), 128);
    $ip_address = visit_client_ip();
    $browser = visit_browser($user_agent);
    $operating_system = visit_operating_system($user_agent);
    $device_type = visit_device_type($user_agent);
    $page_url = visit_page_url();
    $referrer = visit_referrer();
    $referrer = $referrer !== '' ? $referrer : null;
    $existing_visit_id = find_today_visit($id_user, $session_id, $ip_address, $user_agent);

    if ($existing_visit_id > 0) {
        return db_execute("UPDATE tb_kunjungan
            SET id_user = ?,
                session_id = ?,
                ip_address = ?,
                user_agent = ?,
                browser = ?,
                operating_system = ?,
                device_type = ?,
                page_url = ?,
                referrer = ?,
                visited_at = NOW()
            WHERE id_visit = ?
        ", 'sssssssssi', [$id_user, $session_id, $ip_address, $user_agent, $browser, $operating_system, $device_type, $page_url, $referrer, $existing_visit_id]);
    }

    return db_execute("INSERT INTO tb_kunjungan
        (id_user, session_id, ip_address, user_agent, browser, operating_system, device_type, page_url, referrer)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ", 'sssssssss', [$id_user, $session_id, $ip_address, $user_agent, $browser, $operating_system, $device_type, $page_url, $referrer]);
}

function visit_stats()
{
    if (!ensure_visit_table()) {
        return [
            'total' => 0,
            'today' => 0,
            'unique_sessions' => 0,
            'mobile' => 0,
        ];
    }

    $rows = db_select("SELECT
        COUNT(DISTINCT CONCAT(DATE(visited_at), '|', COALESCE(id_user, ''), '|', COALESCE(session_id, ''), '|', COALESCE(ip_address, ''), '|', SHA1(COALESCE(user_agent, '')))) AS total,
        COUNT(DISTINCT CASE WHEN DATE(visited_at) = CURDATE() THEN CONCAT(DATE(visited_at), '|', COALESCE(id_user, ''), '|', COALESCE(session_id, ''), '|', COALESCE(ip_address, ''), '|', SHA1(COALESCE(user_agent, ''))) END) AS today,
        COUNT(DISTINCT session_id) AS unique_sessions,
        COUNT(DISTINCT CASE WHEN device_type IN ('Mobile', 'Tablet') THEN CONCAT(DATE(visited_at), '|', COALESCE(id_user, ''), '|', COALESCE(session_id, ''), '|', COALESCE(ip_address, ''), '|', SHA1(COALESCE(user_agent, ''))) END) AS mobile
        FROM tb_kunjungan
    ");

    return $rows[0] ?? [
        'total' => 0,
        'today' => 0,
        'unique_sessions' => 0,
        'mobile' => 0,
    ];
}

function get_visits($limit = 500)
{
    if (!ensure_visit_table()) {
        return [];
    }

    $limit = max(1, min(1000, (int) $limit));

    $rows = db_select("SELECT k.*, u.nama AS user_name
        FROM tb_kunjungan k
        LEFT JOIN tb_user u ON u.id_user = k.id_user
        ORDER BY k.visited_at DESC
        LIMIT " . ($limit * 5) . "
    ");

    $visits = [];
    $seen = [];

    foreach ($rows as $row) {
        $key = visit_identity_key($row);

        if (isset($seen[$key])) {
            continue;
        }

        $seen[$key] = true;
        $visits[] = $row;

        if (count($visits) >= $limit) {
            break;
        }
    }

    return $visits;
}
