<?php

require_once 'token_utils.php';

$decodedToken = validarToken();
echo json_encode(["message" => "Token is valid", "user" => $decodedToken]);
