<?php

require_once __DIR__ . '/../../config.php';


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (!isset($_POST['email']) || empty($_POST['email'])) {
        http_response_code(400);
        echo json_encode(["error" => "The email is mandatory"]);
        exit;
    }

    if (!isset($_POST['api_key']) || empty($_POST['api_key'])) {
        http_response_code(400);
        echo json_encode(["error" => "The api_key is mandatory"]);
        exit;
    }

    $email = $_POST['email'];
    $apiKey = $_POST['api_key'];
    //Validar el formato del enail
    $patron = "/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+(\.[a-zA-Z]{2,})?$/";
    if (!preg_match($patron, $email)) {
        http_response_code(400);
        echo json_encode(["error" => "The email must be a valid email address"]);
        exit;
    }

    $conn = null;

    try {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $conn = new mysqli(SERVERNAME, USERNAME, PASSWORD, DBNAME);

        // Verificar si el email y la API Key son válidos
        $stmt = $conn->prepare("SELECT U.ID FROM USUARIOS U JOIN API_KEY A ON U.ID = A.USUARIO_ID WHERE U.EMAIL = ? AND A.API_KEY = ?");
        $stmt->bind_param("ss", $email, $apiKey);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            // Generar token de sesión con expiración de 3 días
            $token = generaToken();
            $expiracion = time() + (3 * 24 * 60 * 60); // 3 días en segundos

            // Guardar el token en la base de datos
            $stmt = $conn->prepare("INSERT INTO SESSION_TOKENS (USUARIO_ID, TOKEN, EXPIRATION) VALUES (?, ?, FROM_UNIXTIME(?))");
            $stmt->bind_param("isi", $row['ID'], $token, $expiracion);
            $stmt->execute();
            $stmt->close();

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

    exit;
}

// Función para generar un token aleatorio seguro
function generaToken() {
    return bin2hex(random_bytes(32)); // Token de 64 caracteres
}

?>
