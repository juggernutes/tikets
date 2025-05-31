<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['login_id'])) {
    header("Location: ../public/index.php");
    exit;
}

require_once __DIR__ . '/../config/db_connection.php';
require_once __DIR__ . '/../models/tiket.php';
require_once __DIR__ . '/../models/empleado.php';
require_once __DIR__ . '/../controllers/empleadoController.php';
require_once __DIR__ . '/../controllers/SistemaController.php';
require_once __DIR__ . '/../controllers/tiketController.php';

$sistemaController = new SistemaController($conn);
$empleadoController = new EmpleadoController($conn);
$tiketController = new TiketController(new Tiket($conn));

$usuarioId = $_SESSION['login_id'] ?? null;

if (isset($_GET['accion'], $_GET['id_tiket']) && $_GET['accion'] === 'tomarTiket') {
    $idTiket = intval($_GET['id_tiket']);
    $idSoporte = intval($_SESSION['login_id']);

    if ($idTiket > 0 && $idSoporte > 0) {
        $tiketController->tomarControlDeTiket($idTiket, $idSoporte);
        // Redirigir a la página de resolución del ticket
        header("Location: ../views/resolver_tiket.php?id_tiket=" . $idTiket);
        exit;
    } else {
        // Manejo de error: IDs inválidos
        echo "Parámetros inválidos.";
    }
}
