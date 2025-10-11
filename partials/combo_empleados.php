<?php
include __DIR__ . '/../app/appTiket.php';

$empleados = $empleadoController->obtenerTodosLosEmpleados();

// ancho fijo para el número (ajústalo a tu realidad)
$width = 6; // ej. “123456”
foreach ($empleados as $empleado){
    $num  = (string)$empleado['Numero_Empleado'];
    $name = (string)$empleado['Nombre'];

    // pad a la derecha para que todos queden del mismo ancho
    $numPadded = str_pad($num, $width, ' ', STR_PAD_LEFT); // o STR_PAD_RIGHT si prefieres

    // convertir espacios a NBSP para que no se colapsen en HTML
    $label = $numPadded . '  | ' . $name;
    $labelHtml = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
    $labelHtml = str_replace(' ', '&nbsp;', $labelHtml);

    $val  = htmlspecialchars($num, ENT_QUOTES, 'UTF-8');
    $nameAttr = htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); // útil para data-nombre y title

    echo "<option value=\"$val\" data-nombre=\"$nameAttr\" title=\"$num | $name\">$labelHtml</option>";
}
?>