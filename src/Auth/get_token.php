<?php

require_once __DIR__ . '/../../config/config.php';

$tokenFile = __DIR__ . "/token.json";

function getNewToken()
{
    $params = [
        'client_id' => CLIENT_ID,
        'client_secret' => CLIENT_SECRET,
        'grant_type' => 'client_credentials',
    ];

    $url = "https://id.twitch.tv/oauth2/token";
    $conn = curl_init();
    curl_setopt($conn, CURLOPT_URL, $url);
    curl_setopt($conn, CURLOPT_POST, true);
    curl_setopt($conn, CURLOPT_POSTFIELDS, http_build_query($params));
    curl_setopt($conn, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($conn, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($conn, CURLOPT_HTTPHEADER, ["Content-Type: application/x-www-form-urlencoded"]);

    $response = curl_exec($conn);
    $httpCode = curl_getinfo($conn, CURLINFO_HTTP_CODE);
    $curlError = curl_error($conn);
    curl_close($conn);

    if ($httpCode === 0) {
        return [
            "error" => "Failed to connect to Twitch API.",
            "curl_error" => $curlError
        ];
    }

    if ($httpCode !== 200 || !$response) {
        return ["error" => "Failed to retrieve token from Twitch."];
    }

    $tokenData = json_decode($response, true);
    if (!isset($tokenData['access_token'])) {
        return ["error" => "Unauthorized. No access token received."];
    }

    $tokenData['expires_at'] = time() + $tokenData['expires_in'];
    file_put_contents($GLOBALS['tokenFile'], json_encode($tokenData));

    return $tokenData;
}

function getToken()
{
    global $tokenFile;

    if (file_exists($tokenFile)) {
        $tokenData = json_decode(file_get_contents($tokenFile), true);
        if (isset($tokenData['access_token'], $tokenData['expires_at']) && time() <= $tokenData['expires_at']) {
            return $tokenData;
        }
    }

    return getNewToken();
}
