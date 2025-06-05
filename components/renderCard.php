
<div class="contenedor-tickets">
<?php while ($row = $result->fetch_assoc()): ?>
    <div class="tarjeta-ticket">
        <h4><?= htmlspecialchars($row['Folio']) ?> - <?= htmlspecialchars($row['SISTEMA']) ?></h4>
        <p><strong>Fecha:</strong> <?= htmlspecialchars($row['FECHA']) ?></p>
        <p><strong>Estado:</strong> <?= htmlspecialchars($row['ESTADO']) ?></p>
        <?php
            $descripcion = $row['DESCRIPCION'];
            if (strpos($descripcion, ' ') === false) {
                $descripcion = wordwrap($descripcion, 40, "\n", true);
            }
        ?>
        <p><strong>Descripción:</strong><span class="descripcion-limitada"><?= nl2br(htmlspecialchars($descripcion)) ?></span></p>
        <p><strong>Empleado:</strong> <?= htmlspecialchars($row['EMPLEADO']) ?></p>
        <p><strong>Puesto:</strong> <?= htmlspecialchars($row['PUESTO']) ?></p>
        <p><strong>Sucursal:</strong> <?= htmlspecialchars($row['SUCURSAL']) ?></p>
        <a href="../app/appTiket.php?accion=tomarTiket&id_tiket=<?= $row['ID_Tiket'] ?>"><button>Tomar</button></a>

    </div>
<?php endwhile; ?>
</div>
