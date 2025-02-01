<?php

<<<<<<< HEAD
require_once 'config.php';
=======
require_once 'config.php'; // Se obtiene clientId y clientSecret desde un archivo externo
>>>>>>> 73d092369ffd116df8658b6670d2ed1e3cf05180

$tokenFile = "token.json";

// Verificar si existe un token válido en el archivo
if (file_exists($tokenFile)) {
    $tokenData = json_decode(file_get_contents($tokenFile), true);
    
<<<<<<< HEAD
    if (isset($tokenData['access_token'], $tokenData['expires_at']) && time() <= $tokenData['expires_at']) {
        http_response_code(200);
        echo json_encode($tokenData);
        exit;
    }
}

// Generar un nuevo token si el anterior no es válido
=======
    if (isset($tokenData['access_token'], $tokenData['expires_at'], $tokenData['status_code']) && time() <= $tokenData['expires_at']) {
        http_response_code(200);
        echo json_encode($tokenData);
        exit;
    } elseif (time() >= $tokenData['expires_at']) {
        http_response_code(401);
        echo json_encode(["error" => "Unauthorized. Twitch access token is invalid or has expired."]);
        exit;
    }
}

// Si no existe un token válido, generamos uno nuevo
>>>>>>> 73d092369ffd116df8658b6670d2ed1e3cf05180
$params = [
    'client_id' => CLIENT_ID,
    'client_secret' => CLIENT_SECRET,
    'grant_type' => 'client_credentials',
];

$url = "https://id.twitch.tv/oauth2/token?" . http_build_query($params);
$ch = curl_init($url);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
<<<<<<< HEAD
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
=======
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // Seguridad habilitada
>>>>>>> 73d092369ffd116df8658b6670d2ed1e3cf05180
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/x-www-form-urlencoded"]);

$respuesta = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

<<<<<<< HEAD
if ($httpCode !== 200 || !$respuesta) {
    http_response_code(500);
    echo json_encode(["error" => "Failed to retrieve token from Twitch."]);
=======
// Verificar si la solicitud a Twitch fue exitosa
if ($respuesta === false || $httpCode !== 200) {
    http_response_code($httpCode);
    echo json_encode(["error" => "Failed to retrieve token. HTTP Code: $httpCode"]);
>>>>>>> 73d092369ffd116df8658b6670d2ed1e3cf05180
    exit;
}

$tokenData = json_decode($respuesta, true);

<<<<<<< HEAD
if (!isset($tokenData['access_token'])) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized. No access token received."]);
    exit;
}

// Guardar el token en un archivo con fecha de expiración
$tokenData['expires_at'] = time() + $tokenData['expires_in'];
=======
// Verificar si el token fue recibido correctamente
if (!isset($tokenData['access_token'])) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized. No access token received from Twitch."]);
    exit;
}

// Guardar el token en un archivo
$tokenData['expires_at'] = time() + $tokenData['expires_in']; // Calcular fecha de expiración
$tokenData['status_code'] = 200;
>>>>>>> 73d092369ffd116df8658b6670d2ed1e3cf05180
file_put_contents($tokenFile, json_encode($tokenData));

http_response_code(200);
echo json_encode($tokenData);
<<<<<<< HEAD
=======

>>>>>>> 73d092369ffd116df8658b6670d2ed1e3cf05180
?>
