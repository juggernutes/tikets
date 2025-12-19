<?php
$appVersion = defined('APP_VERSION') ? APP_VERSION : 'V 1.3.4';
$env = defined('APP_ENV') ? APP_ENV : 'productivo';
?>
<footer class="app-footer" role="contentinfo">
  <div class="app-footer__row">

    <!-- FILA SUPERIOR: contacto -->
    <div class="app-footer__top">
      <span class="app-footer__meta-text">
        Contacto TI:
        <a href="mailto:ti@empacadorarosarito.com.mx">ti@empacadorarosarito.com.mx</a>
      </span>

      <span class="app-footer__sep" aria-hidden="true"></span>

      <span class="app-footer__meta-text">
        Extensiones: <span class="app-footer__ext">1120, 1140, 1153</span>
      </span>
    </div>

    <!-- FILA INFERIOR: marca + badges -->
    <div class="app-footer__bottom">
      <div class="app-footer__brand">
        <span class="app-footer__brand-main">&copy; <?php echo date('Y'); ?> Empacadora Rosarito</span>
        <span class="app-footer__sep" aria-hidden="true"></span>
        <span class="app-footer__muted">Dep. Tecnologías</span>
        <span class="app-footer__sep" aria-hidden="true"></span>
        <span class="app-footer__badge" title="Versión"><?php echo $appVersion; ?></span>
        <span class="app-footer__badge" title="Entorno"><?php echo ucfirst($env); ?></span>
      </div>

      <nav class="app-footer__links" aria-label="Accesos rápidos">
        <?php if ($rol === 'EMPLEADO' || $rol === 'ADMINISTRADOR' || $rol === 'SOPORTE'): ?>
          <a class="app-footer__link" href="../views/registrar_tiket.php">Tickets</a>
          <a class="app-footer__link" href="../views/reportes.php">Reportes</a>
          <a class="app-footer__link" href="#manuales">Manuales</a>
          <a class="app-footer__link app-footer__link--warn" href="#incidencia">Reportar incidencia</a>
        <?php endif; ?>
        <a class="app-footer__link app-footer__link--danger" href="../public/logout.php">Cerrar sesión</a>
      </nav>
    </div>
    <div class="app-footer__legal" aria-label="Aviso legal">
      <div class="app-footer__legal-title">Aviso de confidencialidad y uso interno</div>
      <p>
        Este portal es de <strong>uso interno</strong>. La información puede incluir datos personales y/o información operativa.
        El acceso y uso están restringidos a personal autorizado de <strong>Empacadora Rosarito, S.A. de C.V.</strong>
      </p>
      <p>
        Queda prohibida su divulgación, reproducción o distribución sin autorización. El tratamiento de datos se realiza conforme
        a la <strong>LFPDPPP</strong> y demás disposiciones aplicables.
      </p>
    </div>
  </div>
  <!-- AVISO LEGAL -->


</footer>