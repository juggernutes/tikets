<?php
session_start();
require_once __DIR__ . '/../config/db_connection.php';
require_once __DIR__ . '/../models/tiket.php';

if (!isset($_SESSION['login_id']) || ($_SESSION['rol'] !== 'SOPORTE' && $_SESSION['rol'] !== 'ADMINISTRADOR')) {
    header("Location: ../public/index.php");
    exit;
}

$tiketModel = new Tiket($conn);
$tickets = $tiketModel->obtenerTodos();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Asignar Tickets</title>
</head>
<body>
    <h2>Tickets abiertos</h2>

    <table border="1" cellpadding="5">
        <thead>
            <tr>
                <th>ID</th>
                <th>Fecha</th>
                <th>Empleado</th>
                <th>Sistema</th>
                <th>Descripción</th>
                <th>Estatus</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $tickets->fetch_assoc()): ?>
                <?php if ($row['Estatus'] === 'ABIERTO'): ?>
                <tr>
                    <td><?= $row['ID_Tiket'] ?></td>
                    <td><?= $row['FechaReporte'] ?></td>
                    <td><?= $row['Empleado'] ?></td>
                    <td><?= $row['Sistema'] ?></td>
                    <td><?= $row['Descripcion'] ?></td>
                    <td><?= $row['Estatus'] ?></td>
                    <td>
                        <form action="../controllers/asignarTiket.php" method="POST">
                            <input type="hidden" name="id_tiket" value="<?= $row['ID_Tiket'] ?>">
                            <button type="submit">Tomar</button>
                        </form>
                    </td>
                </tr>
                <?php endif; ?>
            <?php endwhile; ?>
        </tbody>
    </table>

    <br>
    <a href="dashboard.php">Volver al panel</a>
</body>
</html>
