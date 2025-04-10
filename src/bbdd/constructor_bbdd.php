<?php

require_once __DIR__ . '/../../config/config.php';

$conn = null;
try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    $conn = new mysqli(SERVERNAME, USERNAME, PASSWORD, DBNAME);
    echo "Conexión exitosa a la base de datos.\n";
    $sql = file_get_contents('init.sql');
    if (!$sql) {
        die("Error en la base de datos: " . mysqli_connect_error() . "\n");
    }
    if ($conn -> multi_query($sql)) {
        echo "Tablas creadas correctamente" . "\n";
    } else {
        echo "Error en la base de datos: " . mysqli_error($conn) . "\n";
    }
} catch (mysqli_sql_exception $e) {
    echo "Error en la conexión: " . $e->getCode() . " - " . $e->getMessage();
} finally {
    if ($conn instanceof mysqli) {
        $conn->close();
        echo " Conexión cerrada" . "\n";
    }
}
