<?php
$output = [];
exec("php get_token_test.php", $output);
print_r($output);

// Se habrá generado token.json y a partir de él podemos consultar result_code y
// hacer peticiones a API si result_code = 200;

$tokenFile = "token.json";
$tokenData = json_decode(file_get_contents($tokenFile), true);
if ($tokenData['status_code'] != 200) {
    echo json_encode(["error" => true, "message" => "There has been no success"]);
    //Quedaria manejar aqui cada error
    exit;
}

// status_code = 200


// El token de acceso
$accessToken = $tokenData['access_token'];
$userId = '1241496177';  // He utilizado mi id de Twith (hhee700)
$clientId = "mla4dss4njahiev0xb4x0d4rj03w9h";
$url = "https://api.twitch.tv/helix/users?id=$userId";

$ch = curl_init($url);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //esta es una solucion rápida, pero no segura
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $accessToken",  // Bearer token
    "Client-ID: $clientId"  // Tu client ID
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);

if ($httpCode === 200) {
    $data = json_decode($response, true);
    echo json_encode($data, JSON_PRETTY_PRINT);
} else {
    echo json_encode(["error" => true, "message" => "There a has been no success"]);
    //Quedaria manejar aqui error de usuario no encontrado o mal ingresado
}



