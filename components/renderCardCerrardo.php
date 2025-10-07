<!-- /components/renderCardCerrado.php -->
<?php /*
<div class="ticket">
<?php $folio = $row['Folio'] ?? ('#' . ($row['ID_Tiket'] ?? '')); $desc = wordwrap($row['DESCRIPCION'] ?? '', 90, "\n", true); $descSol = wordwrap($row['DESCRIPCION_SOLUCION'] ?? '', 90, "\n", true); $css = estado_css($row['ESTADO'] ?? ''); ?>
<h4><?= e($folio) ?> — <?= e($row['SISTEMA'] ?? '') ?></h4>
<span class="chip <?= e($css) ?>"><?= e($row['ESTADO'] ?? '') ?></span>
<p class="desc"><strong>Descripción:</strong>\n<?= nl2br(e($desc)) ?></p>
<div class="meta">
<p><strong>Fecha:</strong> <?= e($row['FECHA'] ?? '') ?></p>
<p><strong>Empleado:</strong> <?= e($row['EMPLEADO'] ?? '') ?></p>
<p><strong>Puesto:</strong> <?= e($row['PUESTO'] ?? '') ?></p>
<p><strong>Sucursal:</strong> <?= e($row['SUCURSAL'] ?? '') ?></p>
<p><strong>Soporte:</strong> <?= e($row['NOMBRE_SOPORTE'] ?? '') ?></p>
<p><strong>Fecha de resolución:</strong> <?= e($row['FECHA_SOLUCION'] ?? '') ?></p>
<p><strong>Error:</strong> <?= e($row['ERROR'] ?? '') ?></p>
<p><strong>Solución:</strong> <?= e($row['SOLUCION'] ?? '') ?></p>
</div>
<?php if(!empty($descSol)): ?>
<p class="desc"><strong>Descripción de la solución:</strong>\n<?= nl2br(e($descSol)) ?></p>
<?php endif; ?>
<div class="acciones">
<?php if(isset($_SESSION['rol']) && $_SESSION['rol']==='SOPORTE'): ?>
<a class="btn" href="../app/appTiket.php?accion=activarTiket&id_tiket=<?= urlencode((string)($row['ID_Tiket'] ?? '')) ?>" onclick="return confirm('¿Reabrir este ticket?')">Activar</a>
<?php endif; ?>
<a class="btn primary" href="../app/appTiket.php?accion=cerrarTiket&id_tiket=<?= urlencode((string)($row['ID_Tiket'] ?? '')) ?>" onclick="return confirm('¿Confirmas cierre definitivo?')">Cerrar</a>
<a class="btn" href="ticket.php?id=<?= urlencode((string)($row['ID_Tiket'] ?? '')) ?>">Ver detalle</a>
</div>
</div>
*/ ?>