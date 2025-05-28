<?php
include __DIR__ . '/../App.php';

$empleados = $empleadoController->obtenerEmpleados($idUsuario); // este ID viene de App.php
            foreach ($empleados as $empleado){
                $numeroEmpleado = htmlspecialchars($empleado['Numero_Empleado']);
                $nombreEmpleado = htmlspecialchars($empleado['Nombre']);
                echo "<option value=\"$numeroEmpleado\" data-nombre=\"$nombreEmpleado\">$nombreEmpleado</option>";
            } 
?>
