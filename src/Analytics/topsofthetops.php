<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../Auth/validaToken.php';
$dbPath = __DIR__ . "/../bbdd/data.sqlite";

use Exception;

header('Content-Type: application/json');

// Verificar token
validarToken();


try {
    $db = new PDO("sqlite:" . __DIR__ . "/../bbdd/data.sqlite");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Error al conectar a la base de datos: " . $e->getMessage();
}

//Crear archivo data.sqlite (si no existe) y crear tabla top_videos (si no existe)
if (!file_exists($dbPath)) {
    touch($dbPath); // Crear el archivo vacío si no existe
}

// Crear la tabla `top_videos`


try {
    $query = "
     CREATE TABLE IF NOT EXISTS top_videos (
         id INTEGER PRIMARY KEY AUTOINCREMENT,
         game_id TEXT NOT NULL,
         game_name TEXT NOT NULL,
         user_name TEXT NOT NULL,
         total_videos INTEGER NOT NULL,
         total_views INTEGER NOT NULL,
         most_viewed_title TEXT NOT NULL,
         most_viewed_views INTEGER NOT NULL,
         most_viewed_duration TEXT NOT NULL,
         most_viewed_created_at TEXT NOT NULL,
         cached_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
     );
     ";
    $db->exec($query);
} catch (PDOException $e) {
    echo "Error al crear la tabla: " . $e->getMessage();
}


// Comprobar caché (menos de 10 minutos)
$stmt = $db->query("SELECT * FROM top_videos WHERE cached_at >= datetime('now', '-10 minutes')");
$cached = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($cached) > 0 && !isset($_GET["since"])) {
    echo json_encode($cached, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// Obtener token válido
$tokenFile = __DIR__ . "/../Auth/token.json";
if (!file_exists($tokenFile)) {
    http_response_code(500);
    echo json_encode(["error" => "Twitch token not found"]);
    exit;
}
$tokenData = json_decode(file_get_contents($tokenFile), true);
$accessToken = $tokenData["access_token"];
$clientId = CLIENT_ID;

// 1. Obtener los 3 juegos más populares
function httpRequest($url, $headers = []) {
    $conn = curl_init($url);
    curl_setopt($conn, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($conn, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($conn);
    if (curl_errno($conn)) {
        throw new Exception('Curl error: ' . curl_error($conn));
    }
    curl_close($conn);
    return $response;
}

$gamesUrl = "https://api.twitch.tv/helix/games/top?first=3";
$headers = [
    "Authorization: Bearer $accessToken",
    "Client-ID: $clientId"
];
$gamesResponse = httpRequest($gamesUrl, $headers);
try {
    $gamesResponse = httpRequest($gamesUrl, $headers);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error al obtener juegos: " . $e->getMessage()]);
    exit;
}

$gamesData = json_decode($gamesResponse, true);

if (!isset($gamesData["data"])) {
    http_response_code(500);
    echo json_encode(["error" => "Failed to fetch top games"]);
    exit;
}

$results = [];
$db->exec("DELETE FROM top_videos");

foreach ($gamesData["data"] as $game) {
    $gameId = $game["id"];
    $gameName = $game["name"];

    // 2. Obtener vídeos del juego
    $videosUrl = "https://api.twitch.tv/helix/videos?game_id=$gameId&first=40&sort=views";
    $videosResponse = httpRequest($videosUrl, $headers);
    $videosData = json_decode($videosResponse, true);

    if (isset($videosData["data"]) && count($videosData["data"]) > 0) {
        $videos = $videosData["data"];
        $totalVideos = count($videos);
        $totalViews = array_sum(array_column($videos, "view_count"));
        $mostViewed = $videos[0];

        $entry = [
            "game_id" => $gameId,
            "game_name" => $gameName,
            "user_name" => $mostViewed["user_name"],
            "total_videos" => $totalVideos,
            "total_views" => $totalViews,
            "most_viewed_title" => $mostViewed["title"],
            "most_viewed_views" => $mostViewed["view_count"],
            "most_viewed_duration" => $mostViewed["duration"],
            "most_viewed_created_at" => $mostViewed["created_at"]
        ];

        $stmt = $db->prepare("INSERT INTO top_videos (game_id, game_name, user_name, total_videos, total_views, most_viewed_title, most_viewed_views, most_viewed_duration, most_viewed_created_at)
         VALUES (:game_id, :game_name, :user_name, :total_videos, :total_views, :most_viewed_title, :most_viewed_views, :most_viewed_duration, :most_viewed_created_at)");
        $stmt->execute($entry);

        $results[] = $entry;
    }
}

echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
