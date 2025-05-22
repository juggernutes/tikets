<?php
session_start();

if (!isset($_SESSION['login_id']) || !isset($_SESSION['rol'])) {
    header("Location: ../public/index.php");
    exit;
}

$title = "Panel de control";
$rol = $_SESSION['rol'];
?>

<?php include __DIR__ . '/layout/header.php'; ?>

<div style="max-width: 600px; margin: 30px auto;">
    <ul style="list-style: none; padding: 0; text-align: center;">
        <?php if ($rol === 'EMPLEADO' ): ?>
            <li style="margin: 10px;"><a href="registrar_tiket.php"><button>Registrar nuevo ticket</button></a></li>
            <li style="margin: 10px;"><a href="validar_tiket.php"><button>Validar solución (cerrar ticket)</button></a></li>
        <?php endif; ?>

        <?php if ($rol === 'SOPORTE' || $rol === 'ADMINISTRADOR'): ?>
            <li style="margin: 10px;"><a href="asignar_tiket.php"><button>Asignar tickets</button></a></li>
            <li style="margin: 10px;"><a href="resolver_tiket.php"><button>Resolver tickets</button></a></li>
            <li style="margin: 10px;"><a href="reporte_tickets.php"><button>Reporte general</button></a></li>
        <?php endif; ?>
    </ul>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>
