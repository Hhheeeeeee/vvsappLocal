<?php

require 'vendor/autoload.php';
require_once 'config.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function validarToken() {
    $secret_key = SECRET_KEY;
    $headers = getallheaders();

    if (!isset($headers["Authorization"])) {
        http_response_code(401);
        echo json_encode(["error" => "Unauthorized. Token missing."]);
        exit;
    }

    $auth_header = $headers["Authorization"];
    list($token_type, $token) = explode(" ", $auth_header, 2);

    if ($token_type !== "Bearer") {
        http_response_code(401);
        echo json_encode(["error" => "Unauthorized. Invalid token format."]);
        exit;
    }

    try {
        $decoded = JWT::decode($token, new Key($secret_key, 'HS256'));
        $decoded_array = (array) $decoded; // Convertimos el objeto a array

        if ($decoded_array['exp'] < time()) {
            http_response_code(401);
            echo json_encode(["error" => "Unauthorized. Token has expired."]);
            exit;
        }

        return $decoded_array;
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(["error" => "Unauthorized. Invalid or expired token."]);
        exit;
    }
}
