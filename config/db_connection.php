<?php
// Leer archivo JSON con las credenciales
$config = json_decode(file_get_contents(__DIR__ . "/mySqlConnection.json"), true);

// Verificar si se pudo leer correctamente
if (!$config) {
    die("Error al leer el archivo de configuración.");
}

// Extraer valores
$servername = $config["server"];
$username = $config["user"];
$password = $config["password"];
$database = $config["database"];

// Crear conexión
$conn = new mysqli($servername, $username, $password, $database);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

echo "Conexión exitosa";



?>
