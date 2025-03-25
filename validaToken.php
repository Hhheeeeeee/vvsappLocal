<?php

require_once 'token_utils.php';

header('Content-Type: application/json'); // Asegurar formato JSON

$decodedToken = validarToken();

if (isset($decodedToken["error"])) {
    echo json_encode($decodedToken); // Si hay error, solo imprimimos el error
    exit;
}

//echo json_encode([
//    "message" => "Token is valid",
//    "user" => $decodedToken
//], JSON_PRETTY_PRINT);
