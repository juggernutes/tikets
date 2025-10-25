<!-- /components/renderCardAbierto.php -->
<?php /*
<div class="ticket">
<?php $folio = $row['Folio'] ?? ('#' . ($row['ID_Tiket'] ?? '')); $desc = wordwrap($row['DESCRIPCION'] ?? '', 90, "\n", true); $css = estado_css($row['ESTADO'] ?? ''); ?>
<h4><?= e($folio) ?> — <?= e($row['SISTEMA'] ?? '') ?></h4>
<span class="chip <?= e($css) ?>"><?= e($row['ESTADO'] ?? '') ?></span>
<p class="desc"><?= nl2br(e($desc)) ?></p>
<div class="meta">
<p><strong>Fecha:</strong> <?= e($row['FECHA'] ?? '') ?></p>
<p><strong>Empleado:</strong> <?= e($row['EMPLEADO'] ?? '') ?></p>
<p><strong>Puesto:</strong> <?= e($row['PUESTO'] ?? '') ?></p>
<p><strong>Sucursal:</strong> <?= e($row['SUCURSAL'] ?? '') ?></p>
</div>
<div class="acciones">
<?php if(isset($_SESSION['rol']) && $_SESSION['rol']==='SOPORTE'): ?>
<a class="btn primary" href="../app/appTiket.php?accion=tomarTiket&id_tiket=<?= urlencode((string)($row['ID_Tiket'] ?? '')) ?>" onclick="return confirm('¿Tomar este ticket?')">Tomar</a>
<?php endif; ?>
<a class="btn" href="ticket.php?id=<?= urlencode((string)($row['ID_Tiket'] ?? '')) ?>">Ver detalle</a>
</div>
</div>
*/ ?>