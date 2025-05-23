<?php
$sistemas = $sistemaController->obtenerSistemas();
foreach ($sistemas as $sistema) {
    $id = htmlspecialchars($sistema['ID_Sistema']);
    $nombre = htmlspecialchars($sistema['Nombre']);
    $descripcion = htmlspecialchars($sistema['Descripcion']);
    echo "<option value=\"$id\" data-descripcion=\"$descripcion\">$nombre</option>";
}
?>
