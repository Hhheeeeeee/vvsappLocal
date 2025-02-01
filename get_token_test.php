<?php

require_once 'config.php';

$tokenFile = "token.json";

// Verificar si existe un token válido en el archivo
if (file_exists($tokenFile)) {
    $tokenData = json_decode(file_get_contents($tokenFile), true);
    
    if (isset($tokenData['access_token'], $tokenData['expires_at']) && time() <= $tokenData['expires_at']) {
        http_response_code(200);
        echo json_encode($tokenData);
        exit;
    }
}

// Generar un nuevo token si el anterior no es válido
$params = [
    'client_id' => CLIENT_ID,
    'client_secret' => CLIENT_SECRET,
    'grant_type' => 'client_credentials',
];

$url = "https://id.twitch.tv/oauth2/token?" . http_build_query($params);
$ch = curl_init($url);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/x-www-form-urlencoded"]);

$respuesta = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200 || !$respuesta) {
    http_response_code(500);
    echo json_encode(["error" => "Failed to retrieve token from Twitch."]);
    exit;
}

$tokenData = json_decode($respuesta, true);

if (!isset($tokenData['access_token'])) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized. No access token received."]);
    exit;
}

// Guardar el token en un archivo con fecha de expiración
$tokenData['expires_at'] = time() + $tokenData['expires_in'];
file_put_contents($tokenFile, json_encode($tokenData));

http_response_code(200);
echo json_encode($tokenData);
?>
