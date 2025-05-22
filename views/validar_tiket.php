<?php
session_start();
require_once __DIR__ . '/../config/db_connection.php';
require_once __DIR__ . '/../models/tiket.php';

if (!isset($_SESSION['login_id'])) {
    header("Location: ../public/index.php");
    exit;
}

$tiketModel = new Tiket($conn);
$tickets = $tiketModel->obtenerTodos();
$usuarioActual = $_SESSION['login_id'];

$title = "Validar Ticket";
include __DIR__ . '/layout/header.php';
?>


    <h2>Tickets resueltos por validar</h2>

    <table border="1" cellpadding="5">
        <thead>
            <tr>
                <th>ID</th>
                <th>Descripción</th>
                <th>Solución</th>
                <th>Sistema</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $tickets->fetch_assoc()): ?>
                <?php if ($row['Estatus'] === 'RESUELTO' && $row['Numero_Empleado'] == $usuarioActual): ?>
                <tr>
                    <td><?= $row['ID_Tiket'] ?></td>
                    <td><?= $row['Descripcion'] ?></td>
                    <td><?= $row['ID_Solucion'] ?></td>
                    <td><?= $row['Sistema'] ?></td>
                    <td>
                        <form action="../controllers/validarTiket.php" method="POST">
                            <input type="hidden" name="id_tiket" value="<?= $row['ID_Tiket'] ?>">
                            <button type="submit">Validar y Cerrar</button>
                        </form>
                    </td>
                </tr>
                <?php endif; ?>
            <?php endwhile; ?>
        </tbody>
    </table>

    <br>
    <a href="dashboard.php">Volver al panel</a>
<?php include __DIR__ . '/layout/footer.php'; ?>
