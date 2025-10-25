<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../config/db_connection.php';
require_once __DIR__ . '/../controllers/LoginController.php';

$loginController = new LoginController($conn);

// POST: enviar enlace
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo'] ?? '');
    $ok     = $loginController->restablecerContrasena($correo);

    // Mensaje neutro (evita enumeración de cuentas)
    $_SESSION['flash'] = $ok
        ? "Si la cuenta existe, te enviamos el enlace de restablecimiento (revisa SPAM)."
        : "Si la cuenta existe, te enviamos el enlace de restablecimiento (revisa SPAM).";

    header("Location: ../public/index.php");
    exit;
}

$title     = "Solicitar reinicio de contraseña";
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
  <section class="auth-modal">
    <div class="auth-header">
      <img src="../img/Centro.png" alt="Centro" class="centro">
    </div>

    <div class="auth-body">
      <h1 id="authTitle" class="auth-title">¿Olvidaste tu contraseña?</h1>
      <p style="margin: -4px 0 12px; color:#5f5f5f; font-size:14px;">
        Ingresa tu correo y te enviaremos un enlace para restablecerla.
      </p>

      <form method="POST" autocomplete="off" novalidate>
        <!-- señuelo para reducir autocompletado del navegador -->
        <input type="text" name="fake_email" autocomplete="off" aria-hidden="true" style="display:none">

        <div class="field">
          <label for="correo" class="label">Correo</label>
          <input
            class="input"
            type="email"
            id="correo"
            name="correo"
            required
            placeholder="tu@empacadorarosarito.com.mx"
            autocomplete="off"
            autocapitalize="none"
            spellcheck="false"
            inputmode="email"
            autocomplete="email"
          />
        </div>

        <div class="actions">
          <a class="link" href="../public/index.php">Cancelar</a>
          <button type="submit" class="btn">Enviar enlace</button>
        </div>
      </form>
    </div>

    <div class="meta"></div>
  </section>
</div>

</body>
</html>
