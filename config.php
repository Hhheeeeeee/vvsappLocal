<?php

if (file_exists(__DIR__ . '/.env')) {
    $env = parse_ini_file(__DIR__ . '/.env');
    define('DBNAME', $env['DBNAME'] ?? '');
    define('USERNAME', $env['USERNAME'] ?? '');
    define('PASSWORD', $env['PASSWORD'] ?? '');
    define('SERVERNAME', $env['SERVERNAME'] ?? '');
    define('CLIENT_ID', $env['CLIENT_ID'] ?? '');
    define('CLIENT_SECRET', $env['CLIENT_SECRET'] ?? '');
} else{
    echo json_encode(["error" => "Unable to load env file."], JSON_PRETTY_PRINT);
}

if (!CLIENT_ID || !CLIENT_SECRET) {
    die("Error: CLIENT_ID o CLIENT_SECRET no están configurados. Asegúrate de definirlos en .env o variables de entorno.");
}else if (!SERVERNAME || !USERNAME || !PASSWORD || !DBNAME) {
    die("Error al obtener variables entorno para conexion a BBDD.");

}
?>
