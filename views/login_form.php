<?php

if (session_status() === PHP_SESSION_NONE) session_start();
if (isset($_SESSION['login_id']) && isset($_SESSION['rol'])) {
    header("Location: dashboard.php");
    exit;
}
$title = "Iniciar sesión";

$pageClass = 'auth-page';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title><?= htmlspecialchars($title) ?></title>
<link rel="stylesheet" href = "../tools/auth.css?v=<?= time() ?>">

</head>
<body class="<?= htmlspecialchars($pageClass) ?>">

<div class="auth-overlay" role="dialog" aria-modal="true" aria-labelledby="authTitle">
  <section class="auth-modal">
    <div class="auth-header">
        <img src="../img/Centro.png" alt="Centro" class="centro" >         
    </div>

    <div class="auth-body">
      <h1 id="authTitle" class="auth-title">Iniciar sesión</h1>

      <form method="POST" action="../public/index.php" autocomplete="on" novalidate>
        <div class="field">
          <label for="cuenta" class="label">Correo electrónico, teléfono o usuario</label>
          <input
            class="input"
            type="text"
            id="cuenta"
            name="cuenta"
            value="<?= htmlspecialchars($_POST['cuenta'] ?? '') ?>"
            required
            autocapitalize="none"
            spellcheck="false"
            autocomplete="username"  
          />
        </div>

        <div class="field">
          <label for="password" class="label">Contraseña</label>
          <input
            class="input"
            type="password"
            id="password"
            name="password"
            required
            autocomplete="current-password"
          />
        </div>

        <div class="actions">
          <a class="link" href="../views/usuario_contr.php">¿No puede acceder a su cuenta?</a>
          <button type="submit" class="btn">Iniciar sesión</button>
        </div>
      </form>
    </div>

    <div class="meta">
      <span></span>
      <a class="link" href="#" onclick="return false;"></a>
    </div>
  </section>
</div>

</body>
</html>
