<?php
require __DIR__ . "/../app/appTiket.php";

$token = $_GET["token"] ?? null;


if (empty($token)) {
    echo "Token no válido.";
    exit;
} else {
    $controller = new LoginController($conn);
    $tokenData = $controller->validarToken($token);
    if (!$tokenData) {
        echo "Token no válido o expirado.";
        exit;
    } elseif (strtotime($tokenData['FechaExpiracion']) < time()) {
        echo "El token ha expirado.";
        exit;
    }
}


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nuevoPassword = $_POST['nuevo_contrasena'] ?? '';
    $confirmarPassword = $_POST['confirmar_contrasena'] ?? '';

    if ($nuevoPassword !== $confirmarPassword) {
        $mensaje = "Las contraseñas no coinciden.";
    } elseif (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $nuevoPassword)) {
        $mensaje = "La contraseña debe tener al menos 8 caracteres, incluyendo una mayúscula, un número y un carácter especial.";
    } else {
        $controller->cambiarPasswordConToken($token, $nuevoPassword);
        exit;
    }
}


$title = "Reinicio de Contraseña";
include __DIR__ . '/layout/header.php';
?>

<form method="POST" class="restablecer-form">
    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
    <label for="nuevo_contrasena">Nueva Contraseña:</label>
    <input type="password" name="nuevo_contrasena" id="nuevo_contrasena" required>
    <label for="confirmar_contrasena">Confirmar Contraseña:</label>
    <input type="password" name="confirmar_contrasena" id="confirmar_contrasena" required>
    <input type="hidden" name="accion" value="restablecerContrasena">
    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
    <button type="submit">Restablecer Contraseña</button>
</form>
<?php
include __DIR__ . '/layout/footer.php';
