<?php

function current_user()
{
    return $_SESSION['user'] ?? null;
}

function is_logged_in()
{
    return !empty($_SESSION['login']) && !empty($_SESSION['user']);
}

function is_admin()
{
    return is_logged_in() && ($_SESSION['user']['role'] ?? '') === 'admin';
}

function require_login()
{
    if (!is_logged_in()) {
        redirect_to('index.php');
    }
}

function require_admin()
{
    require_login();

    if (!is_admin()) {
        set_flash('error', 'Ditolak', 'Akses ini hanya untuk admin.');
        redirect_to('views/documents/document_management.php');
    }
}

function login_user($email, $password)
{
    $email = clean_email($email);

    if (!is_valid_email($email)) {
        return false;
    }

    $users = db_select("SELECT * FROM tb_user WHERE email = ? LIMIT 1", 's', [$email]);

    if (!$users || !password_verify($password, $users[0]['password'])) {
        return false;
    }

    $_SESSION['login'] = true;
    $_SESSION['user'] = [
        'id_user' => $users[0]['id_user'],
        'email' => $users[0]['email'],
        'nama' => $users[0]['nama'],
        'role' => $users[0]['role'],
    ];

    return true;
}

function register_user($data)
{
    $id_user = make_entity_id('USR');
    $email = clean_email($data['email'] ?? '');
    $nama = clean_input_text($data['nama'] ?? '', 120);
    $tanggal_lahir = clean_input_text($data['tanggal_lahir'] ?? '', 10);
    $password = (string) ($data['password'] ?? '');

    if (!is_valid_entity_id($id_user, 'USR') || !is_valid_email($email) || $nama === '' || $password === '' || !is_valid_date_string($tanggal_lahir)) {
        return false;
    }

    if (db_select("SELECT id_user FROM tb_user WHERE email = ? LIMIT 1", 's', [$email])) {
        return -1;
    }

    $tanggal_lahir = $tanggal_lahir !== '' ? $tanggal_lahir : null;
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    return db_execute("INSERT INTO tb_user
        (id_user, email, nama, tanggal_lahir, password, role)
        VALUES (?, ?, ?, ?, ?, 'user')
    ", 'sssss', [$id_user, $email, $nama, $tanggal_lahir, $password_hash]);
}
