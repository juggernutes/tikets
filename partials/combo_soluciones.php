<?php
include __DIR__ . '/../app/appTiket.php';

$soluciones = $solucionController->getAllSoluciones();
usort($soluciones, function($a, $b) {
    return strcasecmp($a['Descripcion'], $b['Descripcion']);
});
foreach ($soluciones as $solucion) {
    $id = htmlspecialchars($solucion['ID_Solucion']);
    $descripcion = htmlspecialchars($solucion['Descripcion']);
    echo "<option value=\"$id\">$descripcion</option>";
}