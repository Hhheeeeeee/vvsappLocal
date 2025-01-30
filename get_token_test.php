<?php

$clientId = "mla4dss4njahiev0xb4x0d4rj03w9h";
$clientSecret = "ksqqch25jwyn4aw0ejcxkh33yoy3f3";
$tokenFile = "token.json";

if (file_exists($tokenFile)){
    $tokenData = json_decode(file_get_contents($tokenFile), true);
    if (isset($tokenData['access_token']) && time()<=$tokenData['expires_at']){
        #$token = $tokenData['access_token'];
        http_response_code(200);
        echo json_encode($tokenData);
    }
    elseif (time()>=$tokenData['expires_at']){
        http_response_code(401);
        echo json_encode(["error" => "Unauthorized. Twitch access token is invalid
    or has expired."]);
    }
    exit;
}

// Si no existe un token, no tiene access_token o este ha exiprado, generamos otro

// $url = "https://id.twitch.tv/oauth2/token?client_id=$clientId&client_secret=$clientSecret&grant_type=client_credentials";
// $respuesta = file_get_contents($url);

$params = [
    'client_id' => $clientId,
    'client_secret' => $clientSecret,
    'grant_type' => 'client_credentials',
];
$url = "https://id.twitch.tv/oauth2/token?" . http_build_query($params);
$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //esta es una solucion rápida, pero no segura

$respuesta = curl_exec($ch);
curl_close($ch);


$tokenData = json_decode($respuesta, true);

if (!isset($tokenData['access_token'])){
    #$token = $tokenData['access_token'];
    http_response_code(401); //Unauthorized
    echo json_encode(["error" => "Unauthorized. Twitch access token is invalid
    or has expired."]);
    exit;
}

$tokenData['expires_at'] = time() + $tokenData['expires_in']; //Apuntamos cuando se expira el token
file_put_contents($tokenFile, json_encode($tokenData));
echo json_encode($tokenData);

?>
