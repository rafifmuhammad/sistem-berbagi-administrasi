<?php

function get_users()
{
    return query("SELECT * FROM tb_user ORDER BY created_at DESC");
}

function add_user($data)
{
    global $conn;

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
        (email, nama, tanggal_lahir, password, role)
        VALUES ('$email_safe', '$nama_safe', $tanggal_safe, '$password_hash', 'user')
    ");

    return mysqli_affected_rows($conn);
}

function update_user($id_user, $data)
{
    global $conn;

    $id_user = (int) $id_user;
    $email = trim($data['email'] ?? '');
    $nama = trim($data['nama'] ?? '');
    $tanggal_lahir = trim($data['tanggal_lahir'] ?? '');
    $password = (string) ($data['password'] ?? '');

    if ($id_user <= 0 || $email === '' || $nama === '') {
        return false;
    }

    $email_safe = db_escape($email);
    $nama_safe = db_escape($nama);
    $tanggal_safe = $tanggal_lahir !== '' ? "'" . db_escape($tanggal_lahir) . "'" : 'NULL';

    mysqli_query($conn, "UPDATE tb_user
        SET email = '$email_safe',
            nama = '$nama_safe',
            tanggal_lahir = $tanggal_safe
        WHERE id_user = $id_user
    ");

    $affected = mysqli_affected_rows($conn);

    if ($password !== '') {
        $password_hash = db_escape(password_hash($password, PASSWORD_DEFAULT));
        mysqli_query($conn, "UPDATE tb_user SET password = '$password_hash' WHERE id_user = $id_user");
        $affected += mysqli_affected_rows($conn);
    }

    return $affected;
}

function delete_user($id_user)
{
    global $conn;

    $id_user = (int) $id_user;

    if (is_logged_in() && (int) $_SESSION['user']['id_user'] === $id_user) {
        return -1;
    }

    mysqli_query($conn, "DELETE FROM tb_user WHERE id_user = $id_user");

    return mysqli_affected_rows($conn);
}
