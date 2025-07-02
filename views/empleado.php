<?php
session_start();

include __DIR__ . '/../app/appTiket.php';
if (!isset($_SESSION['login_id'])) {
    header("Location: ../public/index.php");
    exit;
}

$empleados = $empleadoController->obtenerTodosLosEmpleados();

 include __DIR__ . '/../views/layout/header.php'; 
?>
<h2>EMPLEADOS</h2>

<table border="1" cellpadding="5" class="tickets-table">
    <thead>
        <tr>
            <th>Número de Empleado</th>
            <th>Nombre</th>
            <th>Puesto</th>
            <th>Sucursal</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($empleados as $empleado): ?>
            <tr class="tickets-row" style="cursor: pointer;" 
            onclick="location.href='../components/empleadosDetalles.php?id=<?php echo $empleado['Numero_Empleado']; ?>'">
                <td><?= htmlspecialchars($empleado['Numero_Empleado']); ?></td>
                <td><?= htmlspecialchars($empleado['Nombre']); ?></td>
                <td><?= htmlspecialchars($empleado['PUESTO']); ?></td>
                <td><?= htmlspecialchars($empleado['SUCURSAL']); ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<body>
    
<?php
include __DIR__ . '/../views/layout/footer.php';
?>