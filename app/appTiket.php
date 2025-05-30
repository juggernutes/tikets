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
require_once __DIR__ . '/../controllers/EmpleadoController.php';
require_once __DIR__ . '/../controllers/tiketController.php';

$sistemaController = new SistemaController($conn);
$empleadoController = new EmpleadoController($conn);
$tiketController = new TiketController(new Tiket($conn));

$usuarioId = $_SESSION['login_id'] ?? null;

