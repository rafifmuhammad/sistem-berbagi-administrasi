<?php

function get_categories()
{
    return query("SELECT c.*,
        (SELECT COUNT(*) FROM tb_dokumen d WHERE d.id_category = c.id_category) AS total_dokumen
        FROM tb_kategori c
        ORDER BY c.nama_kategori ASC
    ");
}

function next_category_id()
{
    return make_entity_id('CAT');
}

function category_code_from_name($name)
{
    $code = strtoupper(preg_replace('/[^A-Za-z0-9]+/', '', $name));

    if ($code === '') {
        $code = 'KATEGORI';
    }

    return substr($code, 0, 16);
}

function unique_category_code($name)
{
    $base = category_code_from_name($name);
    $code = $base;
    $number = 1;

    while (true) {
        $code_safe = db_escape($code);
        $rows = query("SELECT id_category FROM tb_kategori WHERE kode_kategori = '$code_safe' LIMIT 1");

        if (empty($rows)) {
            return $code;
        }

        $suffix = '-' . $number;
        $code = substr($base, 0, 20 - strlen($suffix)) . $suffix;
        $number++;
    }
}

function add_category($data)
{
    global $conn;

    $id_category = next_category_id();
    $nama = trim($data['nama_kategori'] ?? '');

    if ($nama === '') {
        return false;
    }

    $kode = db_escape(unique_category_code($nama));
    $nama = db_escape($nama);

    mysqli_query($conn, "INSERT INTO tb_kategori (id_category, kode_kategori, nama_kategori)
        VALUES ('$id_category', '$kode', '$nama')
    ");

    return mysqli_affected_rows($conn);
}

function update_category($id_category, $data)
{
    global $conn;

    $id_category = trim((string) $id_category);
    $nama = trim($data['nama_kategori'] ?? '');

    if ($id_category === '' || $nama === '') {
        return false;
    }

    $id_safe = db_escape($id_category);
    $nama = db_escape($nama);

    mysqli_query($conn, "UPDATE tb_kategori
        SET nama_kategori = '$nama'
        WHERE id_category = '$id_safe'
    ");

    return mysqli_affected_rows($conn);
}

function delete_category($id_category)
{
    global $conn;

    $id_category = trim((string) $id_category);

    if ($id_category === '') {
        return false;
    }

    $id_safe = db_escape($id_category);

    mysqli_begin_transaction($conn);

    $documents_deleted = mysqli_query($conn, "DELETE FROM tb_dokumen WHERE id_category = '$id_safe'");

    if (!$documents_deleted) {
        mysqli_rollback($conn);
        return false;
    }

    $category_deleted = mysqli_query($conn, "DELETE FROM tb_kategori WHERE id_category = '$id_safe'");

    if (!$category_deleted) {
        mysqli_rollback($conn);
        return false;
    }

    $affected = mysqli_affected_rows($conn);
    mysqli_commit($conn);

    return $affected;
}
