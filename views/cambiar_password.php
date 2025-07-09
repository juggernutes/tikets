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
<?php if (isset($mensaje)): ?>
  <div style="background: #ffd6d6; color: #e53935; border:1px solid #e53935; border-radius:7px; padding:14px; font-weight:bold; display:flex; align-items:center; gap:12px; font-size:1.1em; box-shadow:0 1px 7px #e5393530;">
    <span style="font-size:1.7em;">&#9888;&#65039;</span>
    <?= htmlspecialchars($mensaje) ?>
  </div>
<?php endif; ?>

<form method="POST">
    <label>Nueva contraseña:</label><br>
    <input type="password" name="nueva_password" required><br><br>

    <label>Confirmar contraseña:</label><br>
    <input type="password" name="confirmar_password" required><br><br>

    <button type="submit">Actualizar</button>
</form>
<?php include __DIR__ . '/layout/footer.php'; ?>

// Cerrar la conexión a la base de datos