<?php
require_once __DIR__ . '/../config/db_connection.php';
require_once __DIR__ . '/../models/tiket.php';

session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $idTiket = intval($_POST['id_tiket']);

    $modelo = new Tiket($conn);
    $ok = $modelo->cerrar($idTiket);

    if ($ok) {
        header("Location: ../views/validar_tiket.php?cerrado=1");
    } else {
        echo "Error al cerrar el ticket.";
    }
}
