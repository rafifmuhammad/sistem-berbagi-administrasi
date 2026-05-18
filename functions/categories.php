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
        $rows = db_select("SELECT id_category FROM tb_kategori WHERE kode_kategori = ? LIMIT 1", 's', [$code]);

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
    $id_category = next_category_id();
    $nama = clean_input_text($data['nama_kategori'] ?? '', 120);

    if (!is_valid_entity_id($id_category, 'CAT') || $nama === '') {
        return false;
    }

    $kode = unique_category_code($nama);

    return db_execute("INSERT INTO tb_kategori (id_category, kode_kategori, nama_kategori)
        VALUES (?, ?, ?)
    ", 'sss', [$id_category, $kode, $nama]);
}

function update_category($id_category, $data)
{
    $id_category = clean_entity_id($id_category, 'CAT');
    $nama = clean_input_text($data['nama_kategori'] ?? '', 120);

    if ($id_category === '' || $nama === '') {
        return false;
    }

    return db_execute("UPDATE tb_kategori
        SET nama_kategori = ?
        WHERE id_category = ?
    ", 'ss', [$nama, $id_category]);
}

function delete_category($id_category)
{
    global $conn;

    $id_category = clean_entity_id($id_category, 'CAT');

    if ($id_category === '') {
        return false;
    }

    mysqli_begin_transaction($conn);

    $documents_deleted = db_execute("DELETE FROM tb_dokumen WHERE id_category = ?", 's', [$id_category]);

    if ($documents_deleted === false) {
        mysqli_rollback($conn);
        return false;
    }

    $category_deleted = db_execute("DELETE FROM tb_kategori WHERE id_category = ?", 's', [$id_category]);

    if ($category_deleted === false) {
        mysqli_rollback($conn);
        return false;
    }

    $affected = $category_deleted;
    mysqli_commit($conn);

    return $affected;
}
