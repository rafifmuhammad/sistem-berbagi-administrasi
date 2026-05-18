<?php

if (session_status() === PHP_SESSION_NONE) {
    $session_dir = __DIR__ . '/../storage/sessions';

    if (!is_dir($session_dir)) {
        @mkdir($session_dir, 0755, true);
    }

    if (is_dir($session_dir) && is_writable($session_dir)) {
        session_save_path($session_dir);
    }

    session_start();
}

require_once __DIR__ . '/app.php';
require_once __DIR__ . '/../database/db.php';
require_once __DIR__ . '/query.php';
require_once __DIR__ . '/flash.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/files.php';
require_once __DIR__ . '/users.php';
require_once __DIR__ . '/categories.php';
require_once __DIR__ . '/documents.php';
require_once __DIR__ . '/visits.php';

record_visit();
