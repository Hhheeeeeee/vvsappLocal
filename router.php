<?php

require_once __DIR__ . '/validaToken.php'; // Incluir la validación del token

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Verificar si la ruta requiere autenticación
$protectedRoutes = [
    '/analytics/user',
    '/analytics/streams',
    '/analytics/streams/enriched'
];

// Si la ruta está protegida, validar el token
if (in_array($uri, $protectedRoutes)) {
    validarToken(); // Llamar a la función que valida el token
}

if (file_exists(__DIR__ . $uri)) {
    return false;
}

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
    case '/token': // Nueva ruta para token2.php
        require_once __DIR__ . '/token2.php';
        break;
    case '/register': // Nueva ruta para register.php
        require_once __DIR__ . '/register.php';
        break;
    default:
        header("HTTP/1.0 404 Not Found");
        echo "404 - Página no encontrada";
        break;
}
