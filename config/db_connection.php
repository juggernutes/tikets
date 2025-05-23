<?php
// config/db_connection.php

if (!isset($conn)) {
    $configPath = __DIR__ . "/mySqlConnection.json";
    $config = json_decode(file_get_contents($configPath), true);

    if (!$config) {
        die("Error al leer el archivo de configuración en: $configPath");
    }

    $conn = new mysqli(
        $config["server"],
        $config["user"],
        $config["password"],
        $config["database"]
    );

    if ($conn->connect_error) {
        die("Error de conexión: " . $conn->connect_error);
    }
}

