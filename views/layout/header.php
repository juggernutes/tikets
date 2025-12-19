<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$rol           = $_SESSION['rol']    ?? 'Invitado';
$usuario       = $_SESSION['login_id'] ?? 'Sin sesión';
$nombreUsuario = $_SESSION['nombre'] ?? 'Sin sesión';
if (!isset($title)) {
  $title = '';
}

$inicio = '../views/dashboard.php';

if ($rol === 'ALMACEN' || $rol === 'SUPERVISOR') {
  $inicio = '../views/dashboardPedidos.php';
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>SISTEMA DE GESTION</title>
  <link rel="icon" href="../img/favicon.ico" type="image/x-icon">
  <link rel="stylesheet" href="../tools/headerStyle.css">

  <?php if ($rol === 'SOPORTE' && $title === 'DASHBOARD'): ?>
    <meta http-equiv="refresh" content="180">
  <?php elseif ($rol === 'ALMACEN' || $rol === 'SUPERVISOR'): ?>
    <meta http-equiv="refresh" content="120">
  <?php endif; ?>

</head>

<body>
  <header class="site-header">
  <div class="container">

    <div class="brand">
      <a href="<?php echo $inicio; ?>" class="brand-logo" aria-label="Ir al inicio">
        <div class="logo-shell" aria-hidden="true">
          <span class="core"></span>
        </div>
      </a>
    </div>

    <div class="header-title">
      <h1>SISTEMA DE GESTION</h1>
    </div>

    <div class="user" title="<?php echo htmlspecialchars($rol); ?>">
      <div class="avatar">
        <?php echo strtoupper(substr($nombreUsuario, 0, 1)); ?>
      </div>
      <div>
        <strong style="font-size:13px; line-height:1.1; display:block; max-width:180px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
          <?php echo htmlspecialchars($nombreUsuario); ?>
        </strong>
        <small><?php echo htmlspecialchars($rol); ?></small>
      </div>
    </div>

    <nav aria-label="Principal">
      <ul>
        <?php if ($rol === 'ALMACEN' || $rol === 'SUPERVISOR'): ?>
          <li><a href="../views/dashboardPedidos.php">Inicio</a></li>
        <?php elseif ($rol === 'EMPLEADO' || $rol === 'ADMINISTRADOR' 
        || $rol === 'SOPORTE' || $rol === 'JEFE DE AREA' || $rol === 'PROVEEDOR'): ?>
          <li><a href="../views/dashboard.php">Inicio</a></li>
          <li><a href="../views/registrar_tiket.php">Tickets</a></li>
          <li><a href="../views/cerrado_tiket.php">Historial de tickets</a></li>

          <?php if ($rol === 'ADMINISTRADOR' || $rol === 'SOPORTE'): ?>
            <li><a href="../views/empleado.php">Empleados</a></li>
            <li><a href="../views/usuarios.php">Usuarios</a></li>
          <?php endif; ?>
        <?php endif; ?>

        <li><a href="../public/logout.php">Cerrar sesión</a></li>
      </ul>
    </nav>

  </div>
</header>
