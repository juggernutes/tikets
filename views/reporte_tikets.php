<?php
session_start();
require_once __DIR__ . '/../config/db_connection.php';
require_once __DIR__ . '/../models/tiket.php';

if (!isset($_SESSION['login_id']) || ($_SESSION['rol'] !== 'ADMINISTRADOR' && $_SESSION['rol'] !== 'SOPORTE')) {
    header("Location: ../public/index.php");
    exit;
}

$tiketModel = new Tiket($conn);
$tickets = $tiketModel->obtenerTodosTikets();
$title = "Reporte de Tickets";
include __DIR__ . '/layout/header.php';
?>

    <h2>Reporte general de tickets</h2>

    <table border="1" cellpadding="5">
        <thead>
            <tr>
                <th>ID</th>
                <th>Fecha</th>
                <th>Empleado</th>
                <th>Soporte</th>
                <th>Sistema</th>
                <th>Error</th>
                <th>Solución</th>
                <th>Estatus</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $tickets->fetch_assoc()): ?>
            <tr>
                <td><?= $row['ID_Tiket'] ?></td>
                <td><?= $row['FechaReporte'] ?></td>
                <td><?= $row['Empleado'] ?></td>
                <td><?= $row['Soporte'] ?? '—' ?></td>
                <td><?= $row['Sistema'] ?></td>
                <td><?= $row['TipoError'] ?? '—' ?></td>
                <td><?= $row['ID_Solucion'] ?? '—' ?></td>
                <td><?= $row['Estatus'] ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <br>
    <a href="dashboard.php">Volver al panel</a>
<?php include __DIR__ . '/layout/footer.php'; ?>
