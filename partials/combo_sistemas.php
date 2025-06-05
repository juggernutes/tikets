<?php

include __DIR__ . '/../App.php';



$sistemas = $sistemaController->obtenerSistemas();


usort($sistemas, 
function($a, $b) {
    return strcasecmp(
        $a['Nombre'], $b['Nombre']
    );
});

            foreach ($sistemas as $sistema) {
                $id = htmlspecialchars($sistema['ID_Sistema']);
                $nombre = htmlspecialchars($sistema['Nombre']);
                $descripcion = htmlspecialchars($sistema['Descripcion']);
                echo "<option value=\"$id\" data-descripcion=\"$descripcion\">$nombre</option>";
            }         
?>
