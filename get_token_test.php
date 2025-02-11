<?php


require_once 'config.php';

$tokenFile = "token.json";

function getNewToken() {
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
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200 || !$response) {
        http_response_code(500);
        echo json_encode(["error" => "Failed to retrieve token from Twitch."]);
        exit;
    }
    
    $tokenData = json_decode($response, true);
    if (!isset($tokenData['access_token'])) {
        http_response_code(401);
        echo json_encode(["error" => "Unauthorized. No access token received."]);
        exit;
    }
    
    $tokenData['expires_at'] = time() + $tokenData['expires_in'];
    file_put_contents($GLOBALS['tokenFile'], json_encode($tokenData));
    return $tokenData;
}

if (file_exists($tokenFile)) {
    $tokenData = json_decode(file_get_contents($tokenFile), true);
    if (isset($tokenData['access_token'], $tokenData['expires_at']) && time() <= $tokenData['expires_at']) {
        http_response_code(200);
        return $tokenData;
    }
}

$tokenData = getNewToken();

