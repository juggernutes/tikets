<?php
session_start();
if (!isset($_SESSION['login_id']) || !isset($_SESSION['rol'])) {
    header("Location: ../public/index.php");
    exit;
}

$rol = $_SESSION['rol'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Control</title>
</head>
<body>
    <h2>Bienvenido al sistema de tickets</h2>
    <p><strong>Tu rol:</strong> <?= htmlspecialchars($rol) ?></p>

    <ul>
        <?php if ($rol === 'USUARIO' || $rol === 'EMPLEADO'): ?>
            <li><a href="registrar_tiket.php">Registrar nuevo ticket</a></li>
            <li><a href="validar_tiket.php">Validar solución (cerrar ticket)</a></li>
        <?php endif; ?>

        <?php if ($rol === 'SOPORTE' || $rol === 'ADMINISTRADOR'): ?>
            <li><a href="asignar_tiket.php">Asignar tickets (en proceso)</a></li>
            <li><a href="resolver_tiket.php">Resolver tickets asignados</a></li>
        <?php endif; ?>

        <li><a href="../public/logout.php">Cerrar sesión</a></li>
    </ul>
</body>
</html>
