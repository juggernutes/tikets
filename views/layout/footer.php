<?php
    $appVersion = defined('APP_VERSION') ? APP_VERSION : 'V 1.3.0';
    $env = defined('APP_ENV') ? APP_ENV : 'productivo';
  ?>
  <footer class="app-footer">
    <div class="app-footer__row">
      <div class="app-footer__brand">
        <span>&copy; <?php echo date('Y'); ?> Empacadora Rosarito</span>
        <span class="divider"></span>
        <span class="muted">Dep. Tecnologias</span>
      </div>

      <nav class="app-footer__links" aria-label="Enlaces del pie">
         <?php if ($rol === 'EMPLEADO' || $rol === 'ADMINISTRADOR' || $rol === 'SOPORTE'): ?>
        <a class="app-footer__link" href="../views/registrar_tiket.php">Tickets</a>
        <a class="app-footer__link" href="#reportes">Reportes</a>
        <a class="app-footer__link" href="#manuales">Manuales</a>
        <a class="app-footer__link app-btn--danger" href="#incidencia">Reportar incidencia</a>
        <?php endif; ?>
        <a class="app-footer__link app-btn--danger" href="../public/logout.php">Cerrar sesión</a>
      </nav>

      <div class="app-footer__meta">
        <span class="badge" title="Versión de la aplicación"><?php echo $appVersion; ?></span>
        <span class="badge" title="Entorno de ejecución"><?php echo ucfirst($env); ?></span>
        <span class="divider"></span>
        <span>Contacto: <a href="mailto:ti@empacadorarosarito.com.mx">ti@empacadorarosarito.com.mx</a></span>
      </div>
    </div>
  </footer>
</body>
</html>