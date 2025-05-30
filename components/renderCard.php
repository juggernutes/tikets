
<div class="contenedor-tickets">
<?php while ($row = $result->fetch_assoc()): ?>
    <div class="tarjeta-ticket">
        <h4><?= htmlspecialchars($row['Folio']) ?> - <?= htmlspecialchars($row['SISTEMA']) ?></h4>
        <p><strong>Fecha:</strong> <?= htmlspecialchars($row['FECHA']) ?></p>
        <?php
            $descripcion = $row['DESCRIPCION'];
            if (strpos($descripcion, ' ') === false) {
                $descripcion = wordwrap($descripcion, 40, "\n", true);
            }
        ?>
        <p><strong>Descripción:</strong><span class="descripcion-limitada"><?= nl2br(htmlspecialchars($descripcion)) ?></span></p>
        <p><strong>Estado:</strong> <?= htmlspecialchars($row['ESTADO']) ?></p>
        <p><strong>Empleado:</strong> <?= htmlspecialchars($row['EMPLEADO']) ?></p>
        <p><strong>Puesto:</strong> <?= htmlspecialchars($row['PUESTO']) ?></p>
        <p><strong>Sucursal:</strong> <?= htmlspecialchars($row['SUCURSAL']) ?></p>
        <form action="/../controllers/tiketController.php" method="POST">
            <input type="hidden" name="id_tiket" value="<?= $row['ID_Tiket'] ?>">
            <input type="hidden" name="accion" value="asignar">
            <button class="boton-ver" type="submit">Tomar</button>
        </form>

    </div>
<?php endwhile; ?>
</div>
