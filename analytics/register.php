<?php

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $patron = "/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/";

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
        echo json_encode(["api_key"=>generaAPIkey()]);
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
