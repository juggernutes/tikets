<?php
// tickets_dashboard_content.php
include __DIR__ . '/../app/appTiket.php';

$rol = $_SESSION['rol'] ?? null;
$usuarioId = $_SESSION['usuarioId'] ?? null;

$tiketController = new TiketController(new Tiket($conn));
$tikets = null;
$abiertos = [];
$cerrados = [];

if ($rol === 'EMPLEADO') {
    $tikets = $tiketController->getTicketsByUserId($usuarioId);
} elseif ($rol === 'SOPORTE' || $rol === 'ADMINISTRADOR') {
    $tikets = $tiketController->getAllTickets($usuarioId);
}

if ($tikets && $tikets->num_rows > 0) {
    $abiertos = [];
    $cerrados = [];
    while ($row = $tikets->fetch_assoc()) {
        if (in_array($row['ESTADO'], ['ABIERTO', 'EN PROCESO'])) {
            $abiertos[] = $row;
        } else {
            $cerrados[] = $row;
        }
    }

    if (!empty($abiertos)) {
        include __DIR__ . '/../components/renderCardAbierto.php';
    } else {
        echo "<p>No hay tickets abiertos o en proceso.</p>";
    }

    if (!empty($cerrados)) {
        include __DIR__ . '/../components/renderCardCerrardo.php';
    } else {
        echo "<p>No hay tickets cerrados.</p>";
    }
} else {
    echo "<p>No tienes tickets registrados.</p>";
}
?>
