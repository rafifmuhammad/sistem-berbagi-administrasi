<?php

$conn = mysqli_connect('localhost', 'root', '', 'db_berbagi_dokumen');

if (!$conn) {
    die('Koneksi ke database gagal: ' . mysqli_connect_error());
}

mysqli_set_charset($conn, 'utf8mb4');
