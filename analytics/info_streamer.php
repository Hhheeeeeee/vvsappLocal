<?php

// info_streamer.php - Consultar información de un streamer

require_once 'config.php';

// Establecer encabezado JSON antes de imprimir cualquier salida
header('Content-Type: application/json; charset=utf-8');

// Permitir parámetros desde CLI (línea de comandos)
if (php_sapi_name() == "cli") {
    parse_str(implode('&', array_slice($argv, 1)), $_GET);
}

// Verificar si el parámetro 'id' está presente
if (!isset($_GET['id']) || empty($_GET['id'])) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid or missing 'id' parameter."]);
    exit;
}

$userId = htmlspecialchars($_GET['id']);
$tokenFile = "token.json";

// Verificar si el archivo del token existe
if (!file_exists($tokenFile)) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized. No valid token found."]);
    exit;
}

$tokenData = json_decode(file_get_contents($tokenFile), true);
if (!isset($tokenData['access_token']) || time() >= $tokenData['expires_at']) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized. Twitch access token is invalid or has expired."]);
    exit;
}

$accessToken = $tokenData['access_token'];
$clientId = CLIENT_ID;
$url = "https://api.twitch.tv/helix/users?id=$userId";

// Configuración de la solicitud cURL
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $accessToken",
    "Client-ID: $clientId"
]);

// Ejecutar la solicitud a la API de Twitch
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Procesar la respuesta de la API
if ($httpCode === 200) {
    $data = json_decode($response, true);
    if (empty($data['data'])) {
        http_response_code(404);
        echo json_encode(["error" => "User not found."]);
        exit;
    }

    // Formatear la respuesta correctamente sin barras escapadas
    $userData = $data['data'][0];

    // Reemplazar valores vacíos con "N/A"
    foreach ($userData as $key => $value) {
        if (empty($value)) {
            $userData[$key] = "N/A";
        }
    }

    echo json_encode($userData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES| JSON_UNESCAPED_UNICODE);
} else {
    http_response_code($httpCode);
    echo json_encode(["error" => "Twitch API request failed."]);
}


