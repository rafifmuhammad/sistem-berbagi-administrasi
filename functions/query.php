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

function db_bind_params($statement, $types, &$params)
{
    if ($types === '') {
        return true;
    }

    if (strlen($types) !== count($params)) {
        return false;
    }

    $bind = [$types];

    foreach ($params as $key => $value) {
        $bind[] = &$params[$key];
    }

    return call_user_func_array([$statement, 'bind_param'], $bind);
}

function db_select($sql, $types = '', $params = [])
{
    global $conn;

    if ($types === '' && empty($params)) {
        return query($sql);
    }

    $statement = mysqli_prepare($conn, $sql);

    if (!$statement) {
        return [];
    }

    if (!db_bind_params($statement, $types, $params) || !mysqli_stmt_execute($statement)) {
        mysqli_stmt_close($statement);
        return [];
    }

    $result = mysqli_stmt_get_result($statement);

    if ($result === false) {
        mysqli_stmt_close($statement);
        return [];
    }

    $rows = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }

    mysqli_stmt_close($statement);

    return $rows;
}

function db_execute($sql, $types = '', $params = [])
{
    global $conn;

    $statement = mysqli_prepare($conn, $sql);

    if (!$statement) {
        return false;
    }

    if (!db_bind_params($statement, $types, $params) || !mysqli_stmt_execute($statement)) {
        mysqli_stmt_close($statement);
        return false;
    }

    $affected = mysqli_stmt_affected_rows($statement);
    mysqli_stmt_close($statement);

    return $affected;
}
