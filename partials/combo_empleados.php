<?php
include __DIR__ . '/../App.php';

$empleados = $empleadoController->obtenerEmpleados($idUsuario); // este ID viene de App.php
foreach ($empleados as $empleado) {
    $id = htmlspecialchars($empleado['Numero_Empleado']);
    $nombre = htmlspecialchars($empleado['Nombre']);
    echo "<option value=\"$id\">$nombre</option>";
}
?>
