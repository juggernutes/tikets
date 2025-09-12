<?php
require_once __DIR__ . '/../config/db_connection.php';
require_once __DIR__ . '/../controllers/LoginController.php';

if (session_status() === PHP_SESSION_NONE) session_start();

/* Validación defensiva del id */
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    header("Location: ../public/index.php");
    exit;
}
$idLogin = (int)$_GET['id'];

$controller = new LoginController($conn);

$mensaje = null;
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nuevaPassword     = $_POST['nueva_password']     ?? '';
    $confirmarPassword = $_POST['confirmar_password'] ?? '';

    if ($nuevaPassword !== $confirmarPassword) {
        $mensaje = "Las contraseñas no coinciden.";
    } elseif (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $nuevaPassword)) {
        $mensaje = "La contraseña debe tener al menos 8 caracteres, incluyendo una mayúscula, un número y un carácter especial.";
    } else {
        // Cambia y redirige donde necesites
        $controller->cambiarPassword($idLogin, $nuevaPassword);
        header("Location: ../public/index.php?pwd=updated");
        exit;
    }
}

$title     = "Cambiar contraseña";
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
      <h1 id="authTitle" class="auth-title">Cambiar contraseña</h1>

      <?php if ($mensaje): ?>
        <div class="alert-info"><?= htmlspecialchars($mensaje) ?></div>
      <?php endif; ?>

      <div class="grid-2">
        <!-- Columna: formulario -->
        <div>
          <form method="POST" autocomplete="off" novalidate>
            <div class="field">
              <label for="nueva_password" class="label">Nueva contraseña</label>
              <input class="input" type="password" id="nueva_password" name="nueva_password"
                     required autocomplete="new-password">
            </div>

            <div class="field">
              <label for="confirmar_password" class="label">Confirmar contraseña</label>
              <input class="input" type="password" id="confirmar_password" name="confirmar_password"
                     required autocomplete="new-password">
            </div>

            <div class="actions">
              <a class="link" href="../public/index.php">Cancelar</a>
              <button type="submit" class="btn">Actualizar</button>
            </div>
          </form>
        </div>

        <!-- Columna: requisitos -->
        <div class="req-box">
          <h3 class="req-title">Características de la contraseña</h3>
          <ul class="req-list">
            <li><strong>Longitud:</strong> al menos 8 caracteres</li>
            <li><strong>Mayúsculas:</strong> 1 letra mayúscula</li>
            <li><strong>Números:</strong> 1 número</li>
            <li><strong>Especial:</strong> 1 carácter (!@#$%^&*)</li>
          </ul>
          <p class="req-note">Evita reutilizar contraseñas anteriores.</p>
        </div>
      </div>
    </div>
  </section>
</div>

</body>
</html>
