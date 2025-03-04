<?php

require_once 'config.php';

header('Content-Type: application/json');

if (!CLIENT_ID || !CLIENT_SECRET) {
    die(json_encode(["error" => "CLIENT_ID o CLIENT_SECRET no están configurados. Asegúrate de definirlos en .env o variables de entorno."], JSON_PRETTY_PRINT));
}

if (!SERVERNAME || !USERNAME || !PASSWORD || !DBNAME) {
    die(json_encode(["error" => "Error al obtener variables entorno para conexión a BBDD."], JSON_PRETTY_PRINT));
}

if (!SECRET_KEY) {
    die(json_encode(["error" => "Error al obtener SECRET_KEY."], JSON_PRETTY_PRINT));
}
