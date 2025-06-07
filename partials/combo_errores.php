<?php

include __DIR__ . '/../app/appTiket.php';

$errores = $errorController->getAllErrors();
usort($errores, function($a, $b) {
    return strcasecmp($a['Descripcion'], $b['Descripcion']);
});
foreach ($errores as $error) {
    $id = htmlspecialchars($error['ID_Error']);
    $descripcion = htmlspecialchars($error['Descripcion']);
    $tipoError = htmlspecialchars($error['Tipo_Error']);
    echo "<option value=\"$id\" data-tipo=\"$tipoError\">$descripcion</option>";
}