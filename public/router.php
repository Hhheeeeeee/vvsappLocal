<?php

error_reporting(E_ALL & ~E_DEPRECATED);


$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rtrim($uri, '/');


$protectedRoutes = [
    '/analytics/user',
    '/analytics/streams',
    '/analytics/streams/enriched',
    '/analytics/topsofthetops'
];

if (in_array($uri, $protectedRoutes)) {
    require_once dirname(__DIR__) . '/src/Auth/validaToken.php';
    validarToken(); // Solo si pasa la validación continúa
}

function routeTo($path)
{
    require_once dirname(__DIR__) . "/src/$path";
}

switch ($uri) {
    case '/analytics/user':
        routeTo('Analytics/info_streamer.php');
        break;
    case '/analytics/streams':
        routeTo('Analytics/info_live_streams.php');
        break;
    case '/analytics/streams/enriched':
        routeTo('Analytics/get_top_streams.php');
        break;
    case '/analytics/topsofthetops':
        routeTo('Analytics/topsofthetops.php');
        break;
    case '/token':
        routeTo('Auth/token2.php');
        //require_once __DIR__ . '/token2.php';
        break;
    case '/register':
        routeTo('Auth/register.php');
        //require_once __DIR__ . '/register.php';
        break;
    default:
        header("HTTP/1.0 404 Not Found");
        break;
}
