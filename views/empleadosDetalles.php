<?php
include __DIR__ . '/layout/header.php';
?>
<div class="empleado-detalles">
    <h2>Detalles del Empleado</h2>
    <p><strong>Numero de empleado:</strong> <?= htmlspecialchars($empleado['Numero_Empleado']) ?></p>
    <p><strong>Nombre:</strong> <?= htmlspecialchars($empleado['Nombre']) ?></p>
    <p><strong>Puesto:</strong> <?= htmlspecialchars($empleado['Puesto']) ?></p>
    <p><strong>Sucursal:</strong> <?= htmlspecialchars($empleado['Sucursal']) ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($empleado['Correo']) ?></p>
    <p><strong>Password Correo:</strong> <?= htmlspecialchars($empleado['claveCorreo']) ?></p>
    <p><strong>Teléfono:</strong> <?= htmlspecialchars($empleado['Telefono']) ?></p>
    <p><strong>AnyDesk:</strong> <?= htmlspecialchars($empleado['UsuarioAnyDesk']) ?></p>
    <p><strong>Password AnyDesk:</strong> <?= htmlspecialchars($empleado['ClaveAnyDesk']) ?></p>
    <p><strong>Usuario SAP:</strong> <?= htmlspecialchars($empleado['UsuarioSAP']) ?></p>
    <p><strong>Password SAP:</strong> <?= htmlspecialchars($empleado['ClaveSAP']) ?></p>
    <p><strong>Password Administrador Windows:</strong> <?= htmlspecialchars($empleado['claveUsuarioWindows']) ?></p>
    <a href="../views/dashboard.php"><button>Volver al Dashboard</button></a>


</div>
<?php
include __DIR__ . '/layout/footer.php';
?>