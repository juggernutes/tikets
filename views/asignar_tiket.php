<?php
include __DIR__ . '/../app/App.php';

$usuarioId = $_SESSION['login_id'] ?? null;
$idSoporte = $_SESSION['login_id'] ?? null;

$rol = $_SESSION['rol'] ?? '';

/*if (!in_array($rol, ['soporte', 'administrador', 'jefe de area', 'auditoria'])) {
    header("Location: ../public/index.php");
    exit;
}*/

$title = "Tickets Abiertos";
include __DIR__ . '/layout/header.php';

require_once __DIR__ . '/../app/appTiket.php';
$tiketController = new TiketController(new Tiket($conn));
$result = $tiketController->getAllTickets($idSoporte);

?>

<h2>Tickets abiertos</h2>

<?php
if ($result && $result->num_rows > 0) {
    include __DIR__ . '/../components/renderCardabierto.php';
} else {
    echo "<p>No tienes tickets registrados.</p>";
}
?>

<a href="dashboard.php">Volver al panel</a>

<?php include __DIR__ . '/layout/footer.php'; ?>