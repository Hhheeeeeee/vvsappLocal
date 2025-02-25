<?php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../validaToken.php';
require_once __DIR__ . '/../get_token_test.php';

$tokenFile = __DIR__ . "/token.json";

// Verificar el token antes de ejecutar cualquier código
$usuario = validarToken(); // Obtener los datos del usuario autenticado

if (php_sapi_name() == "cli") {
    parse_str(implode('&', array_slice($argv, 1)), $_GET);
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid or missing 'id' parameter."]);
    exit;
}

$userId = htmlspecialchars($_GET['id']);
//$tokenFile = "token.json";

// Verificar si el token de Twitch es válido
if (!file_exists($tokenFile)) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized. No valid token found."]);
    $obtener_Token = getNewToken();
}

$tokenData = json_decode(file_get_contents($tokenFile), true);
if (!isset($tokenData['access_token']) || time() >= $tokenData['expires_at']) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized. Twitch access token is invalid or has expired."]);
    $obtener_Token = getNewToken();
}

$accessToken = $tokenData['access_token'];
$clientId = CLIENT_ID;
$url = "https://api.twitch.tv/helix/users?id=$userId";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $accessToken",
    "Client-ID: $clientId"
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    $data = json_decode($response, true);
    if (empty($data['data'])) {
        http_response_code(404);
        echo json_encode(["error" => "User not found."]);
        exit;
    }

    $userData = $data['data'];

    $formattedData = [
        "id" => $userData['id'] ?? "N/A",
        "login" => $userData['login'] ?? "N/A",
        "display_name" => $userData['display_name'] ?? "N/A",
        "type" => $userData['type'] ?? "",
        "broadcaster_type" => $userData['broadcaster_type'] ?? "N/A",
        "description" => $userData['description'] ?? "N/A",
        "profile_image_url" => $userData['profile_image_url'] ?? "N/A",
        "offline_image_url" => $userData['offline_image_url'] ?? "N/A",
        "view_count" => $userData['view_count'] ?? 0,
        "created_at" => $userData['created_at'] ?? "N/A",
    ];

    header('Content-Type: application/json');
    http_response_code($httpCode);
    echo json_encode($userData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} else {
    http_response_code($httpCode);
    echo json_encode(["error" => "Twitch API request failed."]);
}
