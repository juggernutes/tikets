<div class="contenedor-tickets">
<?php foreach ($cerrados as $row): ?>
    <?php $estadoClass = strtolower(str_replace(' ', '-', $row['ESTADO'])); ?>
    <div class="tiket <?= $estadoClass ?>">
        <h4><?= htmlspecialchars($row['Folio']) ?> - <?= htmlspecialchars($row['SISTEMA']) ?></h4>
        <p><strong>Fecha:</strong> <?= htmlspecialchars($row['FECHA']) ?></p>
        <p><strong>Estado:</strong> <?= htmlspecialchars($row['ESTADO']) ?></p>
        <p><strong>Descripción:</strong><br><?= nl2br(htmlspecialchars($row['DESCRIPCION'], 30, "\n")) ?></p>
        <p><strong>Empleado:</strong> <?= htmlspecialchars($row['EMPLEADO']) ?></p>
        <p><strong>Puesto:</strong> <?= htmlspecialchars($row['PUESTO']) ?></p>
        <p><strong>Sucursal:</strong> <?= htmlspecialchars($row['SUCURSAL']) ?></p>
        <p><strong>Soporte:</strong> <?= htmlspecialchars($row['NOMBRE']) ?></p>
        <p><strong>Fecha de resolución:</strong> <?= htmlspecialchars($row['FECHA_RESOLUCION']) ?></p>
        <p><strong>Error:</strong> <?= htmlspecialchars($row['ERROR']) ?></p>
        <p><strong>Solución:</strong> <?= htmlspecialchars($row['SOLUCION']) ?></p>
        <p><strong>Descripción de la solución:</strong> <?= htmlspecialchars($row['DESCRIPCION_SOLUCION'], 30, "\n") ?></p>
        <a href="../app/appTiket.php?accion=cerrarTiket&id_tiket=<?= $row['ID_Tiket'] ?>">Cerrar</a>
    </div>
<?php endforeach; ?>
</div>