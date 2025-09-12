<?php
session_start();
$ALLOW_PUBLIC = true;

require_once __DIR__ . '/../config/db_connection.php';
require __DIR__ . "/../controllers/LoginController.php";

$error   = null;
$mensaje = null;
$token   = $_GET["token"] ?? '';

if ($token === '') {
  header("Location: ../public/index.php");
  exit;
}

$controller = new LoginController($conn);
$val = $controller->validarToken($token);
if ($val === 0) {
  header("Location: ../public/index.php");
  exit;
}

// POST: procesar cambio (tu lógica tal cual)...
if ($_SERVER["REQUEST_METHOD"] === "POST" && empty($error)) {
  $nuevoPassword     = $_POST['nuevo_contrasena']     ?? '';
  $confirmarPassword = $_POST['confirmar_contrasena'] ?? '';

  if ($nuevoPassword !== $confirmarPassword) {
    $mensaje = "Las contraseñas no coinciden.";
  } elseif (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $nuevoPassword)) {
    $mensaje = "La contraseña debe tener al menos 8 caracteres, con mayúscula, número y carácter especial.";
  } else {
    $ok = $controller->cambiarPasswordConToken($token, $nuevoPassword);
    if ($ok) {
      unset($_SESSION['login_id'], $_SESSION['rol'], $_SESSION['nombre']);
      header("Location: ../public/index.php?reset=ok");
      exit;
    } else {
      $mensaje = "No se pudo actualizar la contraseña. Intenta de nuevo.";
    }
  }
}

$title = "Restablecer contraseña";
$pageClass = 'auth-page darker';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= htmlspecialchars($title) ?></title>
  <link rel="stylesheet" href="../tools/auth.css?v=<?= time() ?>">
</head>
<body class="<?= htmlspecialchars($pageClass) ?>">

<div class="auth-overlay" role="dialog" aria-modal="true" aria-labelledby="authTitle">
  <section class="auth-modal auth-modal--wide">
    <div class="auth-header">
      <img src="../img/Centro.png" alt="Centro" class="centro">
    </div>

    <div class="auth-body">
      <h1 id="authTitle" class="auth-title">Restablecer contraseña</h1>

      <?php if (!empty($mensaje)): ?>
        <div class="alert-info"><?= htmlspecialchars($mensaje) ?></div>
      <?php endif; ?>

      <div class="grid-2">
        <!-- Columna: Formulario -->
        <div>
          <form method="POST" autocomplete="off" novalidate>
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

            <div class="field">
              <label for="nuevo_contrasena" class="label">Nueva contraseña</label>
              <input class="input" type="password" name="nuevo_contrasena" id="nuevo_contrasena" required autocomplete="new-password">
            </div>

            <div class="field">
              <label for="confirmar_contrasena" class="label">Confirmar contraseña</label>
              <input class="input" type="password" name="confirmar_contrasena" id="confirmar_contrasena" required autocomplete="new-password">
            </div>

            <div class="actions">
              <a class="link" href="../public/index.php">Volver a iniciar sesión</a>
              <button type="submit" class="btn">Guardar</button>
            </div>
          </form>
        </div>

        <!-- Columna: Requisitos -->
        <div class="req-box">
          <h3 class="req-title">Características de la contraseña</h3>
          <ul class="req-list">
            <li>Al menos <strong>8 caracteres</strong></li>
            <li>Al menos <strong>1 mayúscula</strong></li>
            <li>Al menos <strong>1 número</strong></li>
            <li>Al menos <strong>1 carácter especial</strong> (!@#$%^&*)</li>
          </ul>
          <p class="req-note">Por seguridad, evita reutilizar contraseñas anteriores.</p>
        </div>
      </div>
    </div>
  </section>
</div>

</body>
</html>
