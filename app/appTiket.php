<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['login_id'])) {
    header("Location: ../public/index.php");
    exit;
}

require_once __DIR__ . '/config/db_connection.php';
require_once __DIR__ . '/controllers/tiketController.php';
require_once __DIR__ . '/components/renderCard.php';

$usuarioId = $_SESSION['login_id'] ?? null;

