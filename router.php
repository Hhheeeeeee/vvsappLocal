<?php


$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rtrim($uri, '/'); 


$protectedRoutes = [
    '/analytics/user',
    '/analytics/streams',
    '/analytics/streams/enriched',
    '/analytics/topsofthetops'
];

if ($uri !== '/' && $uri !== '/register' && in_array($uri, $protectedRoutes)) {
    require_once __DIR__ . '/validaToken.php'; // Incluir la validación del token solo si la ruta lo requiere
    echo "Validando token para: $uri<br>"; // Solo se ejecutará para rutas protegidas
    validarToken();
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
    case '/analytics/topsofthetops':
        require_once __DIR__ . '/analytics/topsofthetops.php';
        break;
    case '/token':
        require_once __DIR__ . '/token2.php';
        break;
    case '/register':
        echo "Cargando register.php...<br>";
        require_once __DIR__ . '/register.php';
        break;
    default:
        header("HTTP/1.0 404 Not Found");
        echo "404 - Página no encontrada";
        break;
}
