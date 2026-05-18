<?php

function get_users()
{
    return query("SELECT * FROM tb_user ORDER BY created_at DESC");
}

function next_user_id()
{
    return make_entity_id('USR');
}

function add_user($data)
{
    $id_user = next_user_id();
    $email = clean_email($data['email'] ?? '');
    $nama = clean_input_text($data['nama'] ?? '', 120);
    $tanggal_lahir = clean_input_text($data['tanggal_lahir'] ?? '', 10);
    $password = (string) ($data['password'] ?? '');
    $role = in_array(($data['role'] ?? 'user'), ['admin', 'user'], true) ? $data['role'] : 'user';

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
        VALUES (?, ?, ?, ?, ?, ?)
    ", 'ssssss', [$id_user, $email, $nama, $tanggal_lahir, $password_hash, $role]);
}

function update_user($id_user, $data)
{
    $id_user = clean_entity_id($id_user, 'USR');
    $email = clean_email($data['email'] ?? '');
    $nama = clean_input_text($data['nama'] ?? '', 120);
    $tanggal_lahir = clean_input_text($data['tanggal_lahir'] ?? '', 10);
    $password = (string) ($data['password'] ?? '');

    if ($id_user === '' || !is_valid_email($email) || $nama === '' || !is_valid_date_string($tanggal_lahir)) {
        return false;
    }

    if (db_select("SELECT id_user FROM tb_user WHERE email = ? AND id_user <> ? LIMIT 1", 'ss', [$email, $id_user])) {
        return false;
    }

    $tanggal_lahir = $tanggal_lahir !== '' ? $tanggal_lahir : null;

    $affected = db_execute("UPDATE tb_user
        SET email = ?,
            nama = ?,
            tanggal_lahir = ?
        WHERE id_user = ?
    ", 'ssss', [$email, $nama, $tanggal_lahir, $id_user]);

    if ($affected === false) {
        return false;
    }

    if ($password !== '') {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $password_affected = db_execute("UPDATE tb_user SET password = ? WHERE id_user = ?", 'ss', [$password_hash, $id_user]);

        if ($password_affected === false) {
            return false;
        }

        $affected += $password_affected;
    }

    return $affected;
}

function delete_user($id_user)
{
    $id_user = clean_entity_id($id_user, 'USR');

    if ($id_user === '') {
        return false;
    }

    if (is_logged_in() && (string) $_SESSION['user']['id_user'] === $id_user) {
        return -1;
    }

    return db_execute("DELETE FROM tb_user WHERE id_user = ?", 's', [$id_user]);
}
