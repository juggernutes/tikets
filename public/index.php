<?php
require_once __DIR__ . '/../config/db_connection.php';
require_once __DIR__ . '/../controllers/LoginController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $cuenta = $_POST['cuenta'] ?? '';
    $password = $_POST['password'] ?? '';

    $controller = new LoginController($conn);
    $controller->login($cuenta, $password);
} else {
    if (isset($_SESSION['login_id']) && isset($_SESSION['rol'])) {
        header("Location: ../views/dashboard.php");
        exit;
    }

    include __DIR__ . '/../views/login_form.php';
}

$conn->close();