<?php
require_once __DIR__ . '/../config/db_connection.php';
require_once __DIR__ . '/../models/tiket.php';

session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['id_tiket'])) {
    $idTiket = intval($_POST['id_tiket']);
    $idSoporte = $_SESSION['login_id'];

    $modelo = new Tiket($conn);
    $resultado = $modelo->asignarSoporte($idTiket, $idSoporte);

    if ($resultado) {
        header("Location: ../views/asignar_tiket.php?success=1");
    } else {
        echo "Error al asignar el ticket.";
    }
}
