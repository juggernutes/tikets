<?php
// App.php

// Inicia la sesión solo si aún no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['login_id'])) {
    header("Location: ../public/index.php");
    exit;
}

require_once __DIR__ . '/../config/db_connection.php';
require_once __DIR__ . '/../controllers/SistemaController.php';
require_once __DIR__ . '/../controllers/EmpleadoController.php';
require_once __DIR__ . '/../controllers/guardarTiket.php';

$sistemaController = new SistemaController($conn);
$empleadoController = new EmpleadoController($conn);

$idUsuario = $_SESSION['login_id'];
?>
