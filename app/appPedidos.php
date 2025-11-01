<?php
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($ALLOW_PUBLIC) && !isset($_SESSION['login_id'])) {
    header("Location: ../public/index.php");
    exit;
}

//cargar la conexion a la base de datos
require_once __DIR__ . '/../config/db_connection.php';

//cargar los modelos
require_once __DIR__ . '/../modelsPedido/articuloModel.php';
require_once __DIR__ . '/../modelsPedido/pedidoModel.php';
require_once __DIR__ . '/../modelsPedido/unidadOperacionalModel.php';

//cargar los controladores
require_once __DIR__ . '/../controllerPedido/articuloController.php';
require_once __DIR__ . '/../controllerPedido/pedidoController.php';
require_once __DIR__ . '/../controllerPedido/unidadOperacionalController.php';

//crear instancias de los controladores
$articuloController = new ArticuloController($conn);
$pedidoController = new PedidoController($conn);
$unidadOperacionalController = new UnidadOperacionalController($conn);

