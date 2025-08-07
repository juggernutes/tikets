<?php
require __DIR__ ."/../app/appTiket.php";

$token = $_GET["token"] ?? null;

if (!$token) {
    echo "Token no válido.";
    exit;
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$title = "Reinicio de Contraseña";
include __DIR__ . '/layout/header.php';
?>

<form action="../app/appTiket.php" method="POST">
    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
    <label for="nueva_contrasena">Nueva Contraseña:</label>
    <input type="password" name="nueva_contrasena" id="nueva_contrasena" required>
    <label for="confirmar_contrasena">Confirmar Contraseña:</label>
    <input type="password" name="confirmar_contrasena" id="confirmar_contrasena" required>
    <input type="hidden" name="accion" value="restablecerContrasena">
    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
    <button type="submit">Restablecer Contraseña</button>
</form>
<?php
include __DIR__ . '/layout/footer.php';