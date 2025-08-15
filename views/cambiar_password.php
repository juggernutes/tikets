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
    } elseif (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $nuevaPassword)) {
        $mensaje = "La contraseña debe tener al menos 8 caracteres, incluyendo una mayúscula, un número y un carácter especial.";
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
<div class="contrasena-wrapper">
    <div class="contrasena-box">
        <h3>Características</h3>
        <ul>
            <li><strong>Longitud:</strong> Al menos 8 caracteres</li>
            <li><strong>Mayúsculas:</strong> Una letra mayúscula</li>
            <li><strong>Números:</strong> Un número</li>
            <li><strong>Especial:</strong> Un carácter especial</li>
        </ul>
    </div>

    <div class="contrasena-box">
        <form method="POST">
            <label for="nueva_password">Nueva contraseña:</label>
            <input type="password" id="nueva_password" name="nueva_password" required>

            <label for="confirmar_password">Confirmar contraseña:</label>
            <input type="password" id="confirmar_password" name="confirmar_password" required>

            <button type="submit">Actualizar</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>
