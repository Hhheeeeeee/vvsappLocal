<?php

$tokenFile = 'token.json';

if (is_readable($tokenFile)) {
    echo "El archivo es legible.\n";
} else {
    echo "El archivo NO es legible.\n";
}

if (is_writable($tokenFile)) {
    echo "El archivo es escribible.\n";
} else {
    echo "El archivo NO es escribible.\n";
}
