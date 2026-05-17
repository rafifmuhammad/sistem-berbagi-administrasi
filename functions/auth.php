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
    $email = db_escape($email);
    $users = query("SELECT * FROM tb_user WHERE email = '$email' LIMIT 1");

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
    global $conn;

    $id_user = make_entity_id('USR');
    $email = trim($data['email'] ?? '');
    $nama = trim($data['nama'] ?? '');
    $tanggal_lahir = trim($data['tanggal_lahir'] ?? '');
    $password = (string) ($data['password'] ?? '');

    if ($email === '' || $nama === '' || $password === '') {
        return false;
    }

    $email_safe = db_escape($email);

    if (query("SELECT id_user FROM tb_user WHERE email = '$email_safe' LIMIT 1")) {
        return -1;
    }

    $nama_safe = db_escape($nama);
    $tanggal_safe = $tanggal_lahir !== '' ? "'" . db_escape($tanggal_lahir) . "'" : 'NULL';
    $password_hash = db_escape(password_hash($password, PASSWORD_DEFAULT));

    mysqli_query($conn, "INSERT INTO tb_user
        (id_user, email, nama, tanggal_lahir, password, role)
        VALUES ('$id_user', '$email_safe', '$nama_safe', $tanggal_safe, '$password_hash', 'user')
    ");

    return mysqli_affected_rows($conn);
}
