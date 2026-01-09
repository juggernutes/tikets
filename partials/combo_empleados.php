<?php
include __DIR__ . '/../App.php';

$empleados = $empleadoController->obtenerEmpleados($idUsuario); // este ID viene de App.php
foreach ($empleados as $empleado) {
    $numeroEmpleado = htmlspecialchars($empleado['Numero_Empleado']);
    $nombreEmpleado = htmlspecialchars($empleado['Nombre']);
    $idSucEmp = htmlspecialchars($empleado['ID_Sucursal']);
    $puesto = htmlspecialchars($empleado['PUESTO']);
    echo "<option 
        value=\"$numeroEmpleado\" 
        data-nombre=\"$nombreEmpleado\" 
        data-sucursal=\"$idSucEmp\"
        data-puesto=\"$puesto\">
      $nombreEmpleado
      </option>";

}
?>