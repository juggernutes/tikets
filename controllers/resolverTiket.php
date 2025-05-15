<?php
require_once __DIR__ . '/../config/db_connection.php';
require_once __DIR__ . '/../models/tiket.php';

session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $idTiket = intval($_POST['id_tiket']);
    $idError = intval($_POST['id_error']);
    $idSolucion = trim($_POST['id_solucion']);

    $modelo = new Tiket($conn);
    $ok = $modelo->resolver($idTiket, $idError, $idSolucion);

    if ($ok) {
        header("Location: ../views/resolver_tiket.php?resuelto=1");
    } else {
        echo "Error al resolver el ticket.";
    }
}
