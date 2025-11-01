<?php 

include __DIR__ . '/../app/appPedidos.php';

$articulos = $articuloController->getAllArticulos();

foreach ($articulos as $articulo) {
    $id = htmlspecialchars($articulo['ID_ARTICULO']);
    $linea = htmlspecialchars($articulo['LINEA']);
    $nombre = htmlspecialchars($articulo['ARTICULO']);
    $peso = htmlspecialchars($articulo['PESO']);
    echo "<option value=\"$id\" data-linea=\"$linea\" data-peso=\"$peso\">$id - $nombre</option>";
}