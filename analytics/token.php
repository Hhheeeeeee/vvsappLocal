<?php

// Simulación de almacenamiento de API Keys y tokens (debe usarse una base de datos en producción)
$usuarios_registrados = [
    "usuario@example.com" => "abcd1234efgh5678"
];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    header('Content-Type: application/json');

    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['email']) || empty($input['email'])) {
        http_response_code(400);
        echo json_encode(["error" => "The email is mandatory"]);
        exit;
    }

    if (!isset($input['api_key']) || empty($input['api_key'])) {
        http_response_code(400);
        echo json_encode(["error" => "The api_key is mandatory"]);
        exit;
    }

    $email = $input['email'];
    $api_key = $input['api_key'];

    // Validación del email
    $patron = "/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/";
    if (!preg_match($patron, $email)) {
        http_response_code(400);
        echo json_encode(["error" => "The email must be a valid email address"]);
        exit;
    }

    // Validación de la API Key
    if (!array_key_exists($email, $usuarios_registrados) || $usuarios_registrados[$email] !== $api_key) {
        http_response_code(401);
        echo json_encode(["error" => "Unauthorized. API access token is invalid."]);
        exit;
    }

    // Generar Token con expiración de 3 días (256 bits de seguridad)
    $token = bin2hex(random_bytes(32));
    $expiracion = time() + (3 * 24 * 60 * 60); // 3 días en segundos

    // Responder con el token
    http_response_code(200);
    echo json_encode([
        "token" => $token,
        "expires_at" => date("Y-m-d H:i:s", $expiracion)
    ]);
    exit;
}

// Respuesta si no es una solicitud POST
http_response_code(405);
echo json_encode(["error" => "Method Not Allowed"]);
exit;
