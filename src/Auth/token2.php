<?php

// Se debe instalar un composer.exe (desde el navegador se puede hacer)
// Ejecutar en linea de comandos:  composer require firebase/php-jwt

require __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config.php';


use Firebase\JWT\JWT;
use Firebase\JWT\Key;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL & ~E_DEPRECATED);

// Verificar si la solicitud es POST
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    return;
}

// Leer el cuerpo de la solicitud
$inputData = file_get_contents("php://input");

// Intentar decodificar el JSON recibido
$data = json_decode($inputData, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid JSON format"]);
    exit;
}

// Verificar si se han proporcionado email y api_key
if (!isset($data['email']) || empty($data['email'])) {
    http_response_code(400);
    echo json_encode(["error" => "The email is mandatory"]);
    exit;
}

if (!isset($data['api_key']) || empty($data['api_key'])) {
    http_response_code(400);
    echo json_encode(["error" => "The api_key is mandatory"]);
    exit;
}

$email = $data['email'];
$apiKey = $data['api_key'];

// Validar formato de email
$patron = "/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/";
if (!preg_match($patron, $email)) {
    http_response_code(400);
    echo json_encode(["error" => "The email must be a valid email address"]);
    exit;
}

$conn = null;

try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $conn = new mysqli(SERVERNAME, USERNAME, PASSWORD, DBNAME);

    // Validar si el email y la api_key son correctos
    $stmt = $conn->prepare("SELECT U.ID FROM USUARIOS U JOIN API_KEY A ON U.ID = A.USUARIO_ID WHERE U.EMAIL = ? AND A.API_KEY = ?");
    $stmt->bind_param("ss", $email, $apiKey);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // Generar el token JWT
        $expiration = time() + (3 * 24 * 60 * 60); // 3 días en segundos

        $payload = [
            "email" => $email,
            "exp" => $expiration // Expira en 3 días
        ];

        $token = JWT::encode($payload, SECRET_KEY, 'HS256');

        http_response_code(200);
        echo json_encode(["token" => $token]);
    } else {
        http_response_code(401);
        echo json_encode(["error" => "Unauthorized. API access token is invalid."]);
    }
} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Internal server error"]);
} finally {
    if ($conn instanceof mysqli) {
        $conn->close();
    }
}







