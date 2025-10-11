<?php
include __DIR__ . '/../app/appTiket.php';

try {
    $sucursales = $empleadoController->obtenerSucursales();
    
    echo $sucursales === [] ? '<option disabled>No hay sucursales</option>' : '';
    foreach ($sucursales as $s){
        $id  = htmlspecialchars($s['id_sucursal']); 
        $nom = htmlspecialchars($s['sucursal']);
        echo "<option value=\"$id\" data-nombre=\"$nom\">$nom</option>";
    }
} catch (Throwable $e) {
    echo '<option disabled>Error cargando sucursales</option>';
}
?>