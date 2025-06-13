<?php
include __DIR__ . '/../app/appTiket.php';

$tiketController = new TiketController(new Tiket($conn));
include __DIR__ . '/layout/header.php'; 
?>
<h1>DASHBOARD</h1>

<?php
$tikets = null;
$abiertos = [];
$cerrados = [];

if ($rol === 'EMPLEADO') {
    $tikets = $tiketController->getTicketsByUserId($usuarioId);
} elseif ($rol === 'SOPORTE' || $rol === 'ADMINISTRADOR') {
    $tikets = $tiketController->getAllTickets($usuarioId);
}

if ($tikets && $tikets->num_rows > 0) {
    while ($row = $tikets->fetch_assoc()) {
        if ($rol === 'EMPLEADO') {
            // Solo mostrar ABIERTO y RESUELTO
            if ($row['ESTADO'] === 'ABIERTO' || $row['ESTADO'] === 'RESUELTO') {
                if ($row['ESTADO'] === 'ABIERTO') {
                    $abiertos[] = $row;
                } else {
                    $cerrados[] = $row;
                }
            }
        } else {
            // SOPORTE y ADMINISTRADOR ven todo
            if (in_array($row['ESTADO'], ['ABIERTO', 'EN PROCESO'])) {
                $abiertos[] = $row;
            } else {
                $cerrados[] = $row;
            }
        }
    }

    if (!empty($abiertos)) {
        include __DIR__ . '/../components/renderCardabierto.php'; 
    } else {
        echo "<p>No hay tickets abiertos o en proceso.</p>";
    }

    if (!empty($cerrados)) {
        include __DIR__ . '/../components/renderCardCerrado.php'; 
    }

} else {
    echo "<p>No tienes tickets registrados.</p>";
}

include __DIR__ . '/layout/footer.php';
?>