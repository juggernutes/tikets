<?php
session_start();
require_once __DIR__ . '/../app/appTiket.php';
if (!isset($_SESSION['login_id'])) {
    header("Location: ../public/index.php");
    exit;
}
$empleadoController = new EmpleadoController($conn);
$numeroEmpleado = $_GET['id'] ?? null;

$empleado = $empleadoController->obtenerEmpleadoporNumero($numeroEmpleado);
if (!$empleado) {
    http_response_code(404);
    include __DIR__ . '/../views/404.php';
    exit;
}
include __DIR__ . '/../views/layout/header.php';
?>
<div class="empleado-detalles">
    <h2>Detalles del Empleado</h2>
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
    <p><strong>Password Administrador Windows:</strong> <?= htmlspecialchars($empleado['ClaveUsuarioWindows']) ?></p>
    <a href="../views/dashboard.php"><button>Volver al Dashboard</button></a>
</div>
<?php
include __DIR__ . '/../views/layout/footer.php';
?>
