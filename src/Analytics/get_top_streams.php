<?php


require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../Auth/validaToken.php';

$tokenFile = __DIR__ . "/../Auth/token.json";



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
$limit = isset($_GET['limit']) && is_numeric($_GET['limit']) ? intval($_GET['limit']) : 10;

$url = "https://api.twitch.tv/helix/streams?first=$limit";

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

if ($httpCode !== 200) {
    http_response_code($httpCode);
    echo json_encode(["error" => "Failed to retrieve streams from Twitch."]);
    exit;
}

$data = json_decode($response, true);
$streams = $data['data'] ?? [];

$userIds = array_map(fn ($stream) => $stream['user_id'], $streams);
$userIdsQuery = implode("&id=", $userIds);
$urlUsers = "https://api.twitch.tv/helix/users?id=$userIdsQuery";

$ch = curl_init($urlUsers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $accessToken",
    "Client-ID: $clientId"
]);

$responseUsers = curl_exec($ch);
$httpCodeUsers = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCodeUsers !== 200) {
    http_response_code($httpCodeUsers);
    echo json_encode(["error" => "Failed to retrieve user information from Twitch."]);
    exit;
}

$userData = json_decode($responseUsers, true)['data'] ?? [];
$usersMap = [];
foreach ($userData as $user) {
    $usersMap[$user['id']] = $user;
}

$enrichedStreams = array_map(function ($stream) use ($usersMap) {
    $user = $usersMap[$stream['user_id']] ?? [];
    return [
        "stream_id" => $stream['id'],
        "user_id" => $stream['user_id'],
        "user_name" => $stream['user_name'],
        "viewer_count" => $stream['viewer_count'],
        "title" => $stream['title'],
        "user_display_name" => $user['display_name'] ?? "Unknown",
        "profile_image_url" => $user['profile_image_url'] ?? ""
    ];
}, $streams);

$finalOutput = array_map(function ($stream) {
    return [
        "stream_id" => $stream['stream_id'],
        "user_id" => $stream['user_id'],
        "user_name" => $stream['user_name'],
        "viewer_count" => $stream['viewer_count'],
        "title" => $stream['title'],
        "user_display_name" => $stream['user_display_name'],
        "profile_image_url" => $stream['profile_image_url']
    ];
}, $enrichedStreams);

usort($finalOutput, function ($a, $b) {
    return $b['viewer_count'] - $a['viewer_count'];
});

echo json_encode($streams, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
