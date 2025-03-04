<?php

function generaAPIkey() {
    return bin2hex(random_bytes(64));
}