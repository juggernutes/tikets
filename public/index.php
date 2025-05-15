<?php
require_once __DIR__ . '/../config/db_connection.php';
require_once __DIR__ . '/../controllers/LoginController.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $cuenta = $_POST['cuenta'] ?? '';
    $password = $_POST['password'] ?? '';

    $controller = new LoginController($conn);
    $controller->login($cuenta, $password);
} else {
    include __DIR__ . '/../views/login_form.php';
}