<?php
require_once __DIR__ . '/../config/db_connection.php';
require_once __DIR__ . '/../models/tiket.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idSistema = intval($_POST['id_sistema']);
    $descripcion = $_POST['descripcion'];
    $numEmpleado = intval($_POST['Numero_Empleado']); // debe empatar con Número_Empleado

    $modelo = new Tiket($conn);
    $ok = $modelo->creartiket($numEmpleado, $idSistema, $descripcion);

    if ($ok) {
        echo "Ticket registrado correctamente. <a href='../views/dashboard.php'>Volver</a>";
    } else {
        echo "Error al guardar el ticket.";
    }
}