<?php
require_once __DIR__ . '/functions/function.php';

$_SESSION = [];
session_destroy();
redirect_to('login.php');
