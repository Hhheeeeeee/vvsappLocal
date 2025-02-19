<?php
require 'vendor/autoload.php';
require 'config.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$secret_key = SECRET_KEY;

function validarToken() {
    global $secret_key;

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

    // Validar el token
    try {
        $decoded = JWT::decode($token, new Key($secret_key, 'HS256'));

        if ($decoded->exp < time()) {
            http_response_code(401);
            echo json_encode(["error" => "Unauthorized. Token has expired."]);
            exit;
        }

        // Si el token es válido, devolver los datos del usuario
        return $decoded;

    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(["error" => "Unauthorized. Invalid or expired token."]);
        exit;
    }
}
