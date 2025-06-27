<?php

require_once __DIR__ . '/../app/appTiket.php';
if (!isset($_SESSION['login_id'])) {
    header("Location: ../public/index.php");
    exit;
}

$IdTiket = $_GET['ID_Tiket'] ?? null;
if ($IdTiket) {
    $ticket = $tiketController->getTicketById($IdTiket);
}
$encuesta = $encuestaController->getEncuestaByIdTiket($IdTiket);

$descripcion = wordwrap($ticket['DESCRIPCION'] ?? '', 40, "\n", true);
$descripcionSolucion = wordwrap($ticket['DESCRIPCION_SOLUCION'] ?? '', 40, "\n", true);
$comentarios = wordwrap($encuesta['COMENTARIOS'] ?? '', 40, "\n", true);

function renderStars($calificacion)
{
    $calificacion = (int)$calificacion;
    $out = '';
    for ($i = 1; $i <= 5; $i++) {
        $out .= $i <= $calificacion
            ? '<span style="color:gold; font-size:40px;">&#9733;</span>' // estrella llena
            : '<span style="color:#ccc; font-size:40px;">&#9733;</span>'; // estrella vacía (gris)
    }
    return $out;
}


$title = "DETALLES DEL TICKET";
include __DIR__ . '/layout/header.php';
?>

<div class="ticket-details">
    <h2><?= htmlspecialchars($ticket['Folio']) ?></h2>
    <p>
        <?= ($encuesta && isset($encuesta['CALIFICACION']))
            ? renderStars($encuesta['CALIFICACION'])
            : "No especificada"; ?>
    </p>
    <p><strong>Descripción:</strong> <?= nl2br(htmlspecialchars($descripcion, ENT_QUOTES, 'UTF-8')) ?></p>
    <p><strong>Estado:</strong> <?= htmlspecialchars($ticket['ESTADO']) ?></p>
    <p><strong>Fecha de Creación:</strong> <?= htmlspecialchars($ticket['FECHA']) ?></p>
    <p><strong>Fecha de Resolución:</strong> <?= htmlspecialchars($ticket['FECHA_SOLUCION'] ? $ticket['FECHA_SOLUCION'] : 'No resuelto') ?></p>
    <p><strong>Empleado:</strong> <?= htmlspecialchars($ticket['EMPLEADO']) ?></p>
    <p><strong>Puesto:</strong> <?= htmlspecialchars($ticket['PUESTO']) ?></p>
    <p><strong>Sucursal:</strong> <?= htmlspecialchars($ticket['SUCURSAL']) ?></p>
    <p><strong>Soporte:</strong> <?= htmlspecialchars($ticket['NOMBRE_SOPORTE']) ?? 'No asignado' ?></p>
    <p><strong>Error:</strong> <?= htmlspecialchars($ticket['ERROR']) ?? 'No especificado' ?></p>
    <p><strong>Solución:</strong> <?= htmlspecialchars($ticket['SOLUCION']) ?? 'No especificada' ?></p>
    <p><strong>Descripción de la Solución:</strong> <?= nl2br(htmlspecialchars($descripcionSolucion, ENT_QUOTES, 'UTF-8')) ?></p>
    
    <p><strong>Comentarios de Encuesta:</strong>
        <?php
        if ($encuesta && isset($encuesta['COMENTARIOS']) && $encuesta['COMENTARIOS'] !== "") {
            echo nl2br(htmlspecialchars($comentarios, ENT_QUOTES, 'UTF-8'));
        } else {
            echo "No hay comentarios de encuesta disponibles";
        }
        ?>
    </p>
    <a href="../views/cerrado_tiket.php"><button>Volver a Tickets Cerrados</button></a>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>