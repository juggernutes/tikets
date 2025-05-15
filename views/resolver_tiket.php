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
$idSoporte = $_SESSION['login_id'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resolver Tickets</title>
</head>
<body>
    <h2>Tickets en proceso asignados a mí</h2>

    <table border="1" cellpadding="5">
        <thead>
            <tr>
                <th>ID</th>
                <th>Descripción</th>
                <th>Sistema</th>
                <th>Empleado</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $tickets->fetch_assoc()): ?>
                <?php if ($row['Estatus'] === 'EN PROCESO' && $row['ID_Soporte'] == $idSoporte): ?>
                <tr>
                    <td><?= $row['ID_Tiket'] ?></td>
                    <td><?= $row['Descripcion'] ?></td>
                    <td><?= $row['Sistema'] ?></td>
                    <td><?= $row['Empleado'] ?></td>
                    <td>
                        <form action="../controllers/resolverTiket.php" method="POST">
                            <input type="hidden" name="id_tiket" value="<?= $row['ID_Tiket'] ?>">

                            <label>Error:</label>
                            <select name="id_error" required>
                                <option value="1">SAP</option>
                                <option value="2">WINDOWS</option>
                                <option value="3">LIQUIDACIÓN</option>
                                <!-- Cargar dinámicamente si deseas -->
                            </select><br>

                            <label>Solución:</label>
                            <textarea name="id_solucion" required></textarea><br>

                            <button type="submit">Marcar como resuelto</button>
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
