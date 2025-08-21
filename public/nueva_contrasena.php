<?php
session_start();
$ALLOW_PUBLIC = true;

require_once __DIR__ . '/../config/db_connection.php';
require __DIR__ . "/../controllers/LoginController.php";
$error = null;
$mensaje = null;
$token = $_GET["token"] ?? '';

if ($token === '') {
    header("Location: ../public/index.php");
    exit;
}

$controller = new LoginController($conn);
$val = $controller->validarToken($token);

if (!$val['ok']) {
    $error = "Token inválido";
    echo $error;
    header("Location: ../public/index.php");
    exit;
} 


/*
// Validación básica del token: 64 hex (si usas bin2hex(random_bytes(32)))
if ($token === '' || !preg_match('/^[a-f0-9]{64}$/i', $token)) {
    $error = "Token no válido.";
} else {
    // Instanciar controller (NO estático)
    

    if (!$data || empty($data['user_id'])) {
        $error = "Token no válido o usuario no encontrado.";
    } elseif (!empty($data['used'])) {
        $error = "El token ya ha sido utilizado.";
    } else {
        // Validar fecha una vez (el SP ya filtró > NOW, esto es redundancia defensiva)
        $expira = $data['expires_at'] ?? null;
        $ts = $expira ? strtotime($expira) : false;

        if ($ts === false) {
            $error = "Fecha de expiración inválida.";
        } elseif ($ts <= time()) {
            $error = "El token ha expirado.";
        }
    }
}*/

if ($_SERVER["REQUEST_METHOD"] === "POST" && empty($error)) {
    $nuevoPassword     = $_POST['nuevo_contrasena']     ?? '';
    $confirmarPassword = $_POST['confirmar_contrasena'] ?? '';

    if ($nuevoPassword !== $confirmarPassword) {
        $mensaje = "Las contraseñas no coinciden.";
    } elseif (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $nuevoPassword)) {
        $mensaje = "La contraseña debe tener al menos 8 caracteres, con mayúscula, número y carácter especial.";
    } else {
        // Asegúrate de tener $controller instanciado
        $cuenta = (int)$val['user_id'];
        $id_token = $val['id_token'];
        $ok = $controller->cambiarPasswordConToken($token, $nuevoPassword, $cuenta);
        if ($ok) {
            header("Location: ../public/index.php?reset=ok");
            exit;
        } else {
            $mensaje = "No se pudo actualizar la contraseña. Intenta de nuevo.";
        }
    }
}

$title = "Reestablecer Contraseña";
include __DIR__ . '/../views/layout/header.php';
?>

<div class="restablecer-form">
  <h3>Reiniciar contraseña</h3>

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php else: ?>
    <?php if (!empty($mensaje)): ?>
      <div class="alert alert-warning"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>

    <div class="contrasena-wrapper">
      <!-- Formulario -->
      <div class="contrasena-box">
        <form method="POST">
          <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

          <label for="nuevo_contrasena">Nueva Contraseña</label>
          <input type="password" name="nuevo_contrasena" id="nuevo_contrasena" required>

          <label for="confirmar_contrasena">Confirmar Contraseña</label>
          <input type="password" name="confirmar_contrasena" id="confirmar_contrasena" required>

          <button type="submit">Restablecer Contraseña</button>
        </form>
      </div>

      <!-- Requerimientos -->
      <div class="contrasena-box">
        <h3>Características de la contraseña</h3>
        <ul>
          <li><strong>Longitud:</strong> Al menos 8 caracteres</li>
          <li><strong>Mayúsculas:</strong> Una letra mayúscula</li>
          <li><strong>Números:</strong> Un número</li>
          <li><strong>Especial:</strong> Un carácter especial (!@#$%^&*)</li>
        </ul>
      </div>
    </div>
  <?php endif; ?>
</div>


<?php include __DIR__ . '/../views/layout/footer.php'; ?>
