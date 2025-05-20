<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= $title ?? 'Sistema de Tickets' ?></title>
    <link rel="stylesheet" href="../tools/style.css">
</head>
<body>
    <header>
        <h2>Sistema de Tickets</h2>
        <p>Usuario: <?= $_SESSION['rol'] ?? 'Invitado' ?></p>
        <nav>
            <a href="../views/dashboard.php">Inicio</a>
            <?php if (isset($_SESSION['login_id'])): ?>
                <a href="../public/logout.php">Cerrar sesión</a>
            <?php endif; ?>
        </nav>
        <hr>
    </header>
