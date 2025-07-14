<?php
require_once __DIR__ . '/../app/appTiket.php';

$equipo = $equipoController->obtenerEquiposporEmpleado($numEmpleado);

$descripcioEquipo = wordwrap($equipo['DESCRIPCION'], 60, "\n",true);
?>
<div>
    <h2>Detalles del equipo</h2>
    <p><strong>Eqipo: </strong><?=htmlspecialchars($equipo['EQUIPO'])?></p>
    <p><strong>Tipo de equipo</strong><?=htmlspecialchars($equipo['TIPO_EQUIPO'])?></p>
    <p><strong>Numero de serie:</strong><?=htmlspecialchars($equipo['NUMERO_DE_SERIE'])?></p>
    <p><strong>Direccion Ip:</strong><?=htmlspecialchars($equipo['DIRECCION_IP'])?></p>
    <p><strong>Direccion Mac:</strong><?=htmlspecialchars($equipo['DIRECCION_MAC'])?></p>
    <p><strong>Numero de activo fijo:</strong><?=htmlspecialchars($equipo['NUMERO_ACTIVO_FIJO'])?></p>
    <p><strong>Sistema operativo:</strong><?=htmlspecialchars($equipo['SISTEMA_OPERATIVO'])?></p>
    <p><strong>Clave de Windows:</strong><?=htmlspecialchars($equipo['CLAVE_WINDOWS'])?></p>
    <p><strong>Descripcion:</strong><br><?= nl2br(htmlspecialchars($descripcioEquipo, ENT_QUOTES, "UTF-8"))?></p>
    <p><strong>Fecha de compra:</strong><?=htmlspecialchars($equipo['FECA_COMPRA'])?></p>
    <p><strong>Fecha de asignacion:</strong><?=htmlspecialchars($equipo['FECHA_ASIGNACION'])?></p>
    
</div>