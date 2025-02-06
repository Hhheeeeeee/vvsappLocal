<?php

// config.php - Uso de variables de entorno para credenciales seguras

// Cargar variables de entorno desde un archivo .env si está disponible
if (file_exists(__DIR__ . '/.env')) {
    $env = parse_ini_file(__DIR__ . '/.env');
    define('CLIENT_ID', $env['CLIENT_ID'] ?? '');
    define('CLIENT_SECRET', $env['CLIENT_SECRET'] ?? '');
} else {
    define('CLIENT_ID', getenv('CLIENT_ID'));
    define('CLIENT_SECRET', getenv('CLIENT_SECRET'));
}

if (!CLIENT_ID || !CLIENT_SECRET) {
    die("Error: CLIENT_ID o CLIENT_SECRET no están configurados. Asegúrate de definirlos en .env o variables de entorno.");
}

?>
