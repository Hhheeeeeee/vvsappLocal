<?php
// get_streams.php - Consultar streams en vivo

require_once 'config.php';

$tokenFile = "token.json";

//Verificar si el token de acceso existe y es válido
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
$url = "https://api.twitch.tv/helix/streams";

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
    
    // Extraer solo los campos "title" y "user_name"
    $streams = [];
    foreach ($data['data'] as $stream) {
        $streams[] = [
            'title' => $stream['title'],
            'user_name' => $stream['user_name']
        ];
    }

    echo json_encode($streams);
} else {
    http_response_code($httpCode);
    echo json_encode(["error" => "Twitch API request failed."]);
}
?>
