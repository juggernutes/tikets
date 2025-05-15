<?php
session_start();

// Validar sesión activa
if (!isset($_SESSION['login_id'])) {
    header("Location: ../public/index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
</head>
<body>
    <h2>Bienvenido al sistema</h2>
    <p>Has iniciado sesión correctamente.</p>

    <a href="../public/logout.php">Cerrar sesión</a>
</body>
</html>
