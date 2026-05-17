<?php

function get_categories()
{
    return query("SELECT c.*,
        (SELECT COUNT(*) FROM tb_dokumen d WHERE d.id_category = c.id_category) AS total_dokumen
        FROM tb_kategori c
        ORDER BY c.nama_kategori ASC
    ");
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

    $nama = trim($data['nama_kategori'] ?? '');

    if ($nama === '') {
        return false;
    }

    $kode = db_escape(unique_category_code($nama));
    $nama = db_escape($nama);

    mysqli_query($conn, "INSERT INTO tb_kategori (kode_kategori, nama_kategori)
        VALUES ('$kode', '$nama')
    ");

    return mysqli_affected_rows($conn);
}

function update_category($id_category, $data)
{
    global $conn;

    $id_category = (int) $id_category;
    $nama = trim($data['nama_kategori'] ?? '');

    if ($id_category <= 0 || $nama === '') {
        return false;
    }

    $nama = db_escape($nama);

    mysqli_query($conn, "UPDATE tb_kategori
        SET nama_kategori = '$nama'
        WHERE id_category = $id_category
    ");

    return mysqli_affected_rows($conn);
}

function delete_category($id_category)
{
    global $conn;

    $id_category = (int) $id_category;

    mysqli_query($conn, "DELETE FROM tb_kategori WHERE id_category = $id_category");

    return mysqli_affected_rows($conn);
}
