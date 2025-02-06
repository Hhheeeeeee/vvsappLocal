<?php
// info_live_streams.php - Caso 2: Consultar streams en vivo

require_once 'config.php';

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

// Se obtiene de la URL
$tokenFile = "token.json";

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

if (curl_errno($ch)) {
    $error_msg = curl_error($ch);
    http_response_code(500);
    echo json_encode(["error" => "cURL error: $error_msg"]);
    curl_close($ch);
    exit;
}

curl_close($ch);

if ($httpCode === 200) {

    $data = json_decode($response, true);


    if (empty($data['data'])) {
        http_response_code(404);
        echo json_encode(["error" => "User not found."]);
        exit;
    }

    //echo $response;
    //echo json_encode($data['data'][0], JSON_PRETTY_PRINT);
    //echo "<pre>" . json_encode($data['data'][0], JSON_PRETTY_PRINT) . "</pre>";

    $streams = [];
    foreach($data['data'] as $stream) {
        $streams[] = [
            "title" => $stream['title'],
            "user_name" => $stream['user_name'],
        ];
    }


    //echo json_encode($streams, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    echo "<pre>" . json_encode($streams, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "</pre>";


} elseif ($httpCode === 400) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid request."]);
} elseif ($httpCode === 401) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized. Token expired or invalid."]);
} elseif ($httpCode === 404) {
    http_response_code(404);
    echo json_encode(["error" => "User not found."]);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Internal server error."]);
}
?>



