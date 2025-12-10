<?php
include __DIR__ . '/../app/appPedidos.php';
$proveedores = $soporteController->getAllProveedores();
foreach ($proveedores as $proveedor) {
    $id = htmlspecialchars($proveedor['ID_PROVEEDOR']);
    $nombre = htmlspecialchars($proveedor['NOMBRE']);
    echo "<option value=\"$id\">$id - $nombre</option>";
}