<?php

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Si existe un archivo físico que coincida con la solicitud, se sirve directamente.
if (file_exists(__DIR__ . $uri)) {
    return false;
}

// Enrutamiento según la ruta solicitada
switch ($uri) {
    case '/analytics/user':
        require_once __DIR__ . '/analytics/info_streamer.php';
        break;
    case '/analytics/streams':
        require_once __DIR__ . '/analytics/info_live_streams.php';
        break;
    case '/analytics/streams/enriched':
        require_once __DIR__ . '/analytics/get_top_streams.php';
        break;
    default:
        header("HTTP/1.0 404 Not Found");
        echo "404 - Página no encontrada";
        break;
}
