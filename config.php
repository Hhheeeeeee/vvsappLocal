<?php

if (file_exists(__DIR__ . '/.env')) {
    $env = parse_ini_file(__DIR__ . '/.env');
    if (!defined('CLIENT_SECRET')) {
        define('CLIENT_SECRET', $env['CLIENT_SECRET'] ?? '');
    }
    if (!defined('CLIENT_ID')) {
        define('CLIENT_ID', $env['CLIENT_ID'] ?? '');
    }
    if (!defined('DBNAME')) {
        define('DBNAME', $env['DBNAME'] ?? '');
    }
    if (!defined('SERVERNAME')) {
        define('SERVERNAME', $env['SERVERNAME'] ?? '');
    }
    if (!defined('USERNAME')) {
        define('USERNAME', $env['USERNAME'] ?? '');
    }
    if (!defined('PASSWORD')) {
        define('PASSWORD', $env['PASSWORD'] ?? '');
    }
    if (!defined('SECRET_KEY')) {
        define('SECRET_KEY', $env['SECRET_KEY'] ?? '');
    }
} else{
    echo json_encode(["error" => "Unable to load env file."], JSON_PRETTY_PRINT);
}

if (!CLIENT_ID || !CLIENT_SECRET) {
    die("Error: CLIENT_ID o CLIENT_SECRET no están configurados. Asegúrate de definirlos en .env o variables de entorno.");
}else if (!SERVERNAME || !USERNAME || !PASSWORD || !DBNAME) {
    die("Error al obtener variables entorno para conexion a BBDD.");
} else if (!SECRET_KEY) {
    die("Error al obtener variables entorno.");
}
?>
