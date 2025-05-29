<?php
require_once __DIR__ . '/../config/db_connection.php';
require_once __DIR__ . '/../models/tiket.php';



if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    
    $idUsuario = $_SESSION['login_id'] ?? null;
    if (!$idUsuario) {
        echo "No estás autenticado. <a href='../public/index.php'>Iniciar sesión</a>";
        exit;
    }
    $modelo = new Tiket($conn);
    $result = $modelo->getTiketsByUser($idUsuario);
    if ($result && $result->num_rows > 0) {
        include __DIR__ . '/../components/renderCard.php';
    } else {
        echo "No tienes tickets registrados.";
    }
}
