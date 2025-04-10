<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../Utils/utils.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['email'])) {
        http_response_code(400);
        echo json_encode(["error" => "The email is mandatory"]);
        exit;
    }

    $email = $data['email'];


    // Validar formato de email
    $patron = "/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/";
    if (!preg_match($patron, $email)) {
        http_response_code(400);
        echo json_encode(["error" => "The email must be a valid email address"]);
        exit;
    }

    http_response_code(200);
    $apiKey = generaAPIkey();
    echo json_encode(["api_key" => $apiKey]);

    $conn = null;
    try {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $conn = new mysqli(SERVERNAME, USERNAME, PASSWORD, DBNAME);

        // Verificar si el usuario ya existe
        $stmt = $conn->prepare("SELECT ID FROM USUARIOS WHERE EMAIL = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            // Actualizar la API Key existente
            $stmt = $conn->prepare("UPDATE API_KEY SET API_KEY = ?, FECHA_CREACION = CURRENT_TIMESTAMP WHERE USUARIO_ID = ?");
            $stmt->bind_param("si", $apiKey, $row['ID']);
        } else {
            // Insertar nuevo usuario y API Key
            $stmt = $conn->prepare("INSERT INTO USUARIOS (EMAIL) VALUES (?)");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $userId = $stmt->insert_id;

            $stmt = $conn->prepare("INSERT INTO API_KEY (USUARIO_ID, API_KEY) VALUES (?, ?)");
            $stmt->bind_param("is", $userId, $apiKey);
        }

        $stmt->execute();
        $stmt->close();
    } catch (mysqli_sql_exception $e) {
        echo json_encode(["error" => "Database error: " . $e->getMessage()]);
    } finally {
        if ($conn instanceof mysqli) {
            $conn->close();
        }
    }

    exit;
}
