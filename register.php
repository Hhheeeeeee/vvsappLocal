<?php

require_once 'config.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {


    $patron = "/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+(\.[a-zA-Z]{2,})?$/";

    if (isset($_POST['email'])){
        $email = $_POST['email'];
        $dominio = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'Desconocido');

        if(strtolower($dominio) === "desconocido"){
            http_response_code(500);
            echo json_encode(["error" => "Internal server error"]);
            exit;
        }

        if (!preg_match($patron, $email)) {
            http_response_code(400);
            echo json_encode(["error" => "The email must be a valid email address"]);
            exit;
        }

        $correo_partes = explode('@', $email);
        $correo_dominio = $correo_partes[1];

        if($correo_dominio !== $dominio){
            http_response_code(400);
            echo json_encode(["error" => "The email must be a valid email address"]);
            exit;
        }

        http_response_code(200);
        $apiKey = generaAPIkey();
        echo json_encode(["api_key"=>$apiKey]);




        $conn = null;
        try {
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

            $conn = new mysqli(SERVERNAME, USERNAME, PASSWORD, DBNAME);

            //meter una fila

            $stmt = $conn->prepare("INSERT INTO USUARIOS (EMAIL) VALUES (?)");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $userId = $stmt->insert_id;
            $stmt->close();

            $stmt = $conn->prepare("INSERT INTO API_KEY (USUARIO_ID, API_KEY) VALUES (?, ?)");
            $stmt->bind_param("is", $userId, $apiKey);
            $stmt->execute();
            $stmt->close();


        } catch (mysqli_sql_exception $e) {
            echo "Error en la conexión: " . $e->getCode() . " - " . $e->getMessage();
        } finally {
            if ($conn instanceof mysqli) {
                $conn->close();
            }
        }

        exit;

    }
    else{
        http_response_code(400);
        echo json_encode(["error" =>"The email is mandatory"]);
        exit;
    }

}

function generaAPIkey(){
    $longitud = 64;
    $bytes = random_bytes($longitud);
    return bin2hex($bytes);
}
