<?php
require_once __DIR__ . '/../config/db_connection.php';
require_once __DIR__ . '/../controllers/LoginController.php';

if (!isset($_GET['id'])) {
    die("Acceso inválido.");
}

$idLogin = intval($_GET['id']);
$controller = new LoginController($conn);

// Si el formulario fue enviado
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nuevaPassword = $_POST['nueva_password'] ?? '';
    $confirmarPassword = $_POST['confirmar_password'] ?? '';

    if ($nuevaPassword !== $confirmarPassword) {
        $mensaje = "Las contraseñas no coinciden.";
    } elseif (strlen($nuevaPassword) < 6) {
        $mensaje = "La contraseña debe tener al menos 6 caracteres.";
    } else {
        $controller->cambiarPassword($idLogin, $nuevaPassword);
        exit;
    }
}

$title = "Cambiar Contraseña";
include __DIR__ . '/layout/header.php';
?>

    <h2>Cambiar contraseña</h2>
    <?php if (isset($mensaje)) echo "<p style='color:red;'>$mensaje</p>"; ?>
    <form method="POST">
        <label>Nueva contraseña:</label><br>
        <input type="password" name="nueva_password" required><br><br>

        <label>Confirmar contraseña:</label><br>
        <input type="password" name="confirmar_password" required><br><br>

        <button type="submit">Actualizar</button>
    </form>
<?php include __DIR__ . '/layout/footer.php'; ?>

// Cerrar la conexión a la base de datos