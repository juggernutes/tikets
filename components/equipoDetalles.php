<?php
require_once __DIR__ . '/../app/appTiket.php';

$numEmpleado = $_GET['id'] ?? null;
if (!$numEmpleado) {
    http_response_code(400);
    echo "Falta id de empleado.";
    exit;
}

$equipo = $equipoController->obtenerEquiposporEmpleado($numEmpleado);

// Si el método devuelve un array de equipos, puedes mostrar solo el primero o listarlos
if (!$equipo || !is_array($equipo)) {
    echo "<p>No se encontró equipo asignado a este empleado.</p>";
    exit;
}

// Si devuelve uno solo:
$descripcioEquipo = wordwrap($equipo['DESCRIPCION'] ?? '', 60, "\n", true);

function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>
<div class="equipo-card">
  <h2>Detalles del equipo</h2>
  <p><strong>Equipo:</strong> <?= e($equipo['EQUIPO'] ?? '') ?></p>
  <p><strong>Tipo de equipo:</strong> <?= e($equipo['TIPO_EQUIPO'] ?? '') ?></p>
  <p><strong>Número de serie:</strong> <?= e($equipo['NUMERO_DE_SERIE'] ?? '') ?></p>
  <p><strong>Dirección IP:</strong> <?= e($equipo['DIRECCION_IP'] ?? '') ?></p>
  <p><strong>Dirección MAC:</strong> <?= e($equipo['DIRECCION_MAC'] ?? '') ?></p>
  <p><strong>Número de activo fijo:</strong> <?= e($equipo['NUMERO_ACTIVO_FIJO'] ?? '') ?></p>
  <p><strong>Sistema operativo:</strong> <?= e($equipo['SISTEMA_OPERATIVO'] ?? '') ?></p>
  <p><strong>Clave de Windows:</strong> <?= e($equipo['CLAVE_WINDOWS'] ?? '') ?></p>
  <p><strong>Descripción:</strong><br><?= nl2br(e($descripcioEquipo)) ?></p>
  <p><strong>Fecha de compra:</strong> <?= e($equipo['FECHA_COMPRA'] ?? '') ?></p>
  <p><strong>Fecha de asignación:</strong> <?= e($equipo['FECHA_ASIGNACION'] ?? '') ?></p>
</div>
