<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['login_id'])) {
    header("Location: ../public/index.php");
    exit;
}

require_once __DIR__ . '/../config/db_connection.php';
require_once __DIR__ . '/../models/empleado.php';
require_once __DIR__ . '/../models/tiket.php';
require_once __DIR__ . '/../models/errorModel.php';
require_once __DIR__ . '/../models/sistema.php';
require_once __DIR__ . '/../models/solucion.php';
require_once __DIR__ . '/../controllers/solucionController.php';
require_once __DIR__ . '/../controllers/empleadoController.php';
require_once __DIR__ . '/../controllers/SistemaController.php';
require_once __DIR__ . '/../controllers/tiketController.php';
require_once __DIR__ . '/../controllers/errorModelController.php';

$sistemaController = new SistemaController($conn);
$empleadoController = new EmpleadoController($conn);
$tiketController = new TiketController(new Tiket($conn));
$errorController = new ErrorModelController(new ErrorModel($conn));
$solucionController = new SolucionController(new Solucion($conn));

$usuarioId = $_SESSION['login_id'] ?? null;

if (isset($_GET['accion'], $_GET['id_tiket']) && $_GET['accion'] === 'tomarTiket') {
    $idTiket = intval($_GET['id_tiket']);
    $idSoporte = intval($_SESSION['login_id']);

    if ($idTiket > 0 && $idSoporte > 0) {
        $tiketTomado = $tiketController->tomarControlDeTiket($idTiket, $idSoporte);
        if ($tiketTomado) {
            header("Location: ../views/resolver_tiket.php?id=$idTiket");
        } else {
            // Manejo de error: no se pudo tomar el ticket
            echo "No se pudo tomar el ticket. Inténtalo de nuevo más tarde.";
        }
        exit;
    } else {
        // Manejo de error: IDs inválidos
        echo "Parámetros inválidos.";
    }
} elseif (isset($_GET['accion'], $_GET['id_tiket']) && $_GET['accion'] === 'solucionar') {
    $idTiket = intval($_GET['id_tiket']);
    $idSoporte = intval($_SESSION['login_id']);
    $idError = intval($_POST['id_error']);
    $idSolucion = intval($_POST['id_solucion']);
    $descripcionSolucion = $_POST['descripcion_solucion'];

    if ($idTiket > 0 && $idSoporte > 0) {
        $tiketResuelto = $tiketController->resolverTiket($idTiket, $idSoporte, $idError, $idSolucion, $descripcionSolucion);
        if ($tiketResuelto) {
            header("Location: ../views/dashboard.php");
        } else {
            // Manejo de error: no se pudo resolver el ticket
            echo "No se pudo resolver el ticket. Inténtalo de nuevo más tarde.";
        }
        exit;
    } else {
        // Manejo de error: IDs inválidos
        echo "Parámetros inválidos.";
    }
}elseif (isset($_GET['accion'], $_GET['id_tiket']) && $_GET['accion'] === 'cerrarTiket') {
    $idTiket = intval($_GET['id_tiket']);
    $idUsuario = intval($_SESSION['login_id']);

echo "ID del ticket: $idTiket, ID del usuario: $idUsuario";

    if ($idTiket > 0 && $idUsuario > 0) {
        $tiketResuelto = $tiketController->closeTicket($idTiket, $idUsuario);
        echo "\nTicket cerrado: $tiketResuelto";
        if ($tiketResuelto) {
            header("Location: ../views/dashboard.php");
        } else {
            // Manejo de error: no se pudo resolver el ticket
            echo "No se pudo resolver el ticket. Inténtalo de nuevo más tarde.";
        }
        exit;
    } else {
        // Manejo de error: IDs inválidos
        echo "Parámetros inválidos.";
    }
}
