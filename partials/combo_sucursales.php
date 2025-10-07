<?php
include __DIR__ . '/../app/app.php';

$sucursales = $empleadoController->obtenerSucursales();
            foreach ($sucursales as $s){
                $id = htmlspecialchars($s['id_sucursal']); 
                $nom = htmlspecialchars($s['sucursal']);
                echo "<option value=\"{$id}\" data-nombre=\"{$nom}\">{$nom}</option>";
            } 
?>