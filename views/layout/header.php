<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$rol = $_SESSION['rol'] ?? 'Invitado';
$usuario = $_SESSION['login_id'] ?? 'Sin sesión';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= $title ?? 'Sistema de Tickets' ?></title>
    <link rel="stylesheet" href="../tools/style.css">
</head>
<body>
    <div class="wrapper">
        <h2>Sistema de Tickets</h2>
        <div style="margin: 10px 0;">
            <span><strong>Usuario:</strong> <?= htmlspecialchars($usuario) ?></span> |
            <span><strong>Rol:</strong> <?= htmlspecialchars($rol) ?></span>
        </div>

        <nav style="margin: 10px 0;">
            <a href="../views/dashboard.php"><button>Inicio</button></a>
            <?php if ($rol === 'ADMINISTRADOR' ||$rol === 'EMPLEADO'): ?>
                <a href="../views/registrar_tiket.php"><button>Registrar Ticket</button></a>
                <a href="../views/validar_tiket.php"><button>Validar Ticket</button></a>
            <?php endif; ?>

            <?php if ($rol === 'ADMINISTRADOR' || $rol === 'SOPORTE'): ?>
                <a href="../views/registrar_tiket.php"><button>Registrar Ticket</button></a>
                <a href="../views/asignar_tiket.php"><button>Asignar Tickets</button></a>
                <a href="../views/resolver_tiket.php"><button>Resolver Tickets</button></a>
                <a href="../views/reporte_tickets.php"><button>Reporte</button></a>
            <?php endif; ?>

            <a href="../public/logout.php"><button>Cerrar sesión</button></a>
        </nav>
        <hr>
    </div>
<main>