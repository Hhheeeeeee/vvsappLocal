<?php

$tokenFile = __DIR__ . "/../Auth/token.json";

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../Auth/validaToken.php';

validarToken();


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
$url = "https://api.twitch.tv/helix/streams";



$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $accessToken",
    "Client-ID: $clientId"
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    $error_msg = curl_error($ch);
    http_response_code(500);
    echo json_encode(["error" => "cURL error: $error_msg"]);
    curl_close($ch);
    exit;
}

curl_close($ch);


if ($httpCode !== 200) {
    $errorDetails = curl_getinfo($ch);
    echo "<pre>" . print_r($errorDetails, true) . "</pre>";
}

if ($httpCode === 200) {

    $data = json_decode($response, true);


    if (empty($data['data'])) {
        http_response_code(404);
        echo json_encode(["error" => "User not found."]);
        exit;
    }

    $streams = [];
    foreach ($data['data'] as $stream) {
        $streams[] = [
            "title" => $stream['title'],
            "user_name" => $stream['user_name'],
        ];
    }


    echo json_encode($streams, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);


} elseif ($httpCode === 400) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid request."]);
} elseif ($httpCode === 401) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized. Token expireddddd or invalid."]);
} elseif ($httpCode === 404) {
    http_response_code(404);
    echo json_encode(["error" => "User not found."]);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Internal server error."]);
}
