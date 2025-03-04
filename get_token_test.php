<?php

require_once 'get_token.php';

header('Content-Type: application/json');

$tokenData = getToken();

echo json_encode($tokenData);