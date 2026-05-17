<?php

function query($sql)
{
    global $conn;

    $result = mysqli_query($conn, $sql);

    if ($result === false) {
        return [];
    }

    $rows = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }

    return $rows;
}

function db_escape($value)
{
    global $conn;

    return mysqli_real_escape_string($conn, (string) $value);
}
