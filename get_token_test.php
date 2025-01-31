<?php

$clientId = "mla4dss4njahiev0xb4x0d4rj03w9h";
$clientSecret = "ksqqch25jwyn4aw0ejcxkh33yoy3f3";
$tokenFile = "token.json";

if (file_exists($tokenFile)){
    $tokenData = json_decode(file_get_contents($tokenFile), true);
    if (isset($tokenData['access_token'], $tokenData['expires_at'], $tokenData['status_code']) && time() <= $tokenData['expires_at']){
        //http_response_code(200);
        echo json_encode($tokenData);
    }
    elseif (time()>=$tokenData['expires_at']){
        //http_response_code(401);
        $tokenData['status_code'] =401;
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
$ch = curl_init($url);

//curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //esta es una solucion rápida, pero no segura

$respuesta = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
//http_response_code($httpCode);
curl_close($ch);

$tokenData = json_decode($respuesta, true);
$tokenData['status_code'] = $httpCode;

if ($respuesta === false || $httpCode !== 200) {
    echo json_encode(["error" => "Failed to retrieve token. HTTP Code: $httpCode"]);
    exit;
}

//si httpCode es 200 y se recibe una respuesta válida, rellenamos los campos de tokenFile

$tokenData['expires_at'] = time() + $tokenData['expires_in']; //Apuntamos cuando se expira el token
file_put_contents($tokenFile, json_encode($tokenData));
echo json_encode($tokenData);

/*if (!isset($tokenData['access_token'])){
    #$token = $tokenData['access_token'];
    http_response_code(401); //Unauthorized
    echo json_encode(["error" => "Unauthorized. Twitch access token is invalid
    or has expired."]);
    exit;
}*/



?>
