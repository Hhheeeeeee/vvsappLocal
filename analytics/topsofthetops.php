<?php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../validaToken.php';
require_once __DIR__ . '/../get_token_test.php';

$usuario = validarToken(); // Obtener los datos del usuario autenticado

//$tokenFile = "token.json";
$tokenFile = __DIR__ . "/token.json";


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

$since = isset($_GET['since']) ? intval($_GET['since']) : null;

$conn = new mysqli(SERVERNAME, USERNAME, PASSWORD, DBNAME);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Internal server error. Database connection failed."]);
    exit;
}

// Verificar si los datos en caché son válidos
$query = "SELECT * FROM cached_topsofthetops WHERE created_at > DATE_SUB(NOW(), INTERVAL 10 MINUTE)";
if ($since) {
    $query .= " AND created_at > FROM_UNIXTIME($since)";
}

$result = $conn->query($query);
if ($result && $result->num_rows > 0) {
    $cachedData = $result->fetch_assoc();
    echo $cachedData['data'];
    exit;
}

// Obtener los juegos más populares
$url = "https://api.twitch.tv/helix/games/top?first=3";
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $accessToken",
    "Client-ID: $clientId"
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    http_response_code($httpCode);
    echo json_encode(["error" => "Failed to retrieve top games from Twitch."]);
    exit;
}

$topGames = json_decode($response, true)['data'];
$topResults = [];

foreach ($topGames as $game) {
    $gameId = $game['id'];
    $gameName = $game['name'];

    // Obtener los 40 videos más vistos del juego
    $url = "https://api.twitch.tv/helix/videos?game_id=$gameId&first=40&sort=view_count";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $accessToken",
        "Client-ID: $clientId"
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        continue;
    }

    $videos = json_decode($response, true)['data'];
    if (empty($videos)) {
        continue;
    }

    $mostViewed = $videos[0];
    $totalViews = array_sum(array_column($videos, 'view_count'));

    $topResults[] = [
        "game_id" => $gameId,
        "game_name" => $gameName,
        "user_name" => $mostViewed['user_name'],
        "total_videos" => count($videos),
        "total_views" => $totalViews,
        "most_viewed_title" => $mostViewed['title'],
        "most_viewed_views" => $mostViewed['view_count'],
        "most_viewed_duration" => $mostViewed['duration'],
        "most_viewed_created_at" => $mostViewed['created_at']
    ];
}

http_response_code(200);
echo json_encode($topResults, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

// Almacenar en caché
$data = json_encode($topResults);
$stmt = $conn->prepare("INSERT INTO cached_topsofthetops (data) VALUES (?)");
$stmt->bind_param("s", $data);
$stmt->execute();
$stmt->close();
$conn->close();

?>
