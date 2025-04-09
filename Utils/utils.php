<?php

function generaAPIkey(): string
{
    return bin2hex(random_bytes(64));
}
