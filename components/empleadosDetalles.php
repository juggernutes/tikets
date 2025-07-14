<?php
session_start();
require_once __DIR__ . '/../app/appTiket.php';
if (!isset($_SESSION['login_id'])) {
    header("Location: ../public/index.php");
    exit;
}
$empleadoController = new EmpleadoController($conn);
$numeroEmpleado = $_GET['id'] ?? null;

$empleado = $empleadoController->obtenerEmpleadoporNumeroC($numeroEmpleado);
$equipos = $equipoController->obtenerEquiposporEmpleado($numeroEmpleado);
if (!$empleado) {
    http_response_code(404);
    include __DIR__ . '/../views/404.php';
    exit;
}

$title = "DETALLES DE EMPLEADO";

include __DIR__ . '/../views/layout/header.php';
?>
<div class="ticket-details">
    <h2>DETALLES DE EMPLEADO</h2>
    <p><strong>Usuario:</strong> <?= htmlspecialchars($empleado['USUARIO']) ?></p>
    <p><strong>Numero de empleado:</strong> <?= htmlspecialchars($empleado['Numero_Empleado']) ?></p>
    <p><strong>Nombre:</strong> <?= htmlspecialchars($empleado['Nombre']) ?></p>
    <p><strong>Puesto:</strong> <?= htmlspecialchars($empleado['PUESTO']) ?></p>
    <p><strong>Sucursal:</strong> <?= htmlspecialchars($empleado['SUCURSAL']) ?></p>
    <p><strong>Area:</strong> <?= htmlspecialchars($empleado['AREA']) ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($empleado['Correo']) ?></p>
    <p><strong>Password Correo:</strong> <?= htmlspecialchars($empleado['ClaveCorreo']) ?></p>
    <p><strong>Teléfono:</strong> <?= htmlspecialchars($empleado['Telefono']) ?></p>
    <p><strong>Extensión:</strong> <?= htmlspecialchars($empleado['Extencion']) ?></p>
    <p><strong>AnyDesk:</strong> <?= htmlspecialchars($empleado['UsuarioAnyDesk']) ?></p>
    <p><strong>Password AnyDesk:</strong> <?= htmlspecialchars($empleado['ClaveAnyDesk']) ?></p>
    <p><strong>Usuario SAP:</strong> <?= htmlspecialchars($empleado['UsuarioSAP']) ?></p>
    <p><strong>Password SAP:</strong> <?= htmlspecialchars($empleado['ClaveSAP']) ?></p>
    <a href="../views/dashboard.php"><button>Volver al Dashboard</button></a>
</div>
<table border="1" cellpadding="5" class="tickets-table">
    <thead>
        <tr>
            <th>EQUIPO</th>
            <th>NUMERO DE ACTIVO FIJO</th>
            <th>FECHA DE ASIGNACION</th>
            <th>DIRECCION IP</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($equipos = $equipo->fetch_assoc()): ?>
            <tr class="ticket-row" data-tiket="<?= htmlspecialchars($row['ID_EQUIPO']) ?>">
                <td><?= htmlspecialchars($row['TIPO_EQUIPO']) ?></td>
                <td><?= htmlspecialchars($row['NUMERO_ACTIVO_FIJO']) ?></td>
                <td><?= htmlspecialchars($row['FECHA_ASIGNACION']) ?></td>
                <td><?= htmlspecialchars($row['DIRECCION_IP']) ?></td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<?php
include __DIR__ . '/../views/layout/footer.php';
?>