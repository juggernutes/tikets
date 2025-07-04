<div class="contenedor-tickets-cerrados">
    <?php foreach ($cerrados as $row): ?>
        <?php $estadoClass = strtolower(str_replace(' ', '-', $row['ESTADO']));
        $descripcion = wordwrap($row['DESCRIPCION'] ?? '', 40, "\n", true);
        $descripcionSolucion = wordwrap($row['DESCRIPCION_SOLUCION'] ?? '', 40, "\n", true);
        ?>
        <div class="tiket <?= $estadoClass ?>">
            <h4><?= htmlspecialchars($row['Folio']) ?> - <?= htmlspecialchars($row['SISTEMA']) ?></h4>
            <p><strong>Fecha:</strong> <?= htmlspecialchars($row['FECHA']) ?></p>
            <p><strong>Estado:</strong> <?= htmlspecialchars($row['ESTADO']) ?></p>
            <p><strong>Descripción:</strong><br><?= nl2br(htmlspecialchars($descripcion, ENT_QUOTES, 'UTF-8')) ?></p>
            <p><strong>Empleado:</strong> <?= htmlspecialchars($row['EMPLEADO']) ?></p>
            <p><strong>Puesto:</strong> <?= htmlspecialchars($row['PUESTO']) ?></p>
            <p><strong>Sucursal:</strong> <?= htmlspecialchars($row['SUCURSAL']) ?></p>
            <p><strong>Soporte:</strong> <?= htmlspecialchars($row['NOMBRE_SOPORTE'] ?? '') ?></p>
            <p><strong>Fecha de resolución:</strong> <?= htmlspecialchars($row['FECHA_SOLUCION'] ?? '') ?></p>
            <p><strong>Error:</strong> <?= htmlspecialchars($row['ERROR'] ?? '') ?></p>
            <p><strong>Solución:</strong> <?= htmlspecialchars($row['SOLUCION'] ?? '') ?></p>
            <div class="acciones-botones">
                <?php if (isset($_SESSION['rol']) && ($_SESSION['rol'] === 'SOPORTE')): ?>
                    <p><strong>Descripción de la solución:</strong> <?= nl2br(htmlspecialchars($descripcionSolucion, ENT_QUOTES, 'UTF-8')) ?></p>
                    <a href="../app/appTiket.php?accion=activarTiket&id_tiket=<?= $row['ID_Tiket'] ?>"><button>Activar</button></a>
                <?php endif; ?>
                <a href="../app/appTiket.php?accion=cerrarTiket&id_tiket=<?= $row['ID_Tiket'] ?>"><button>Cerrar</button></a>
            </div>
        </div>
    <?php endforeach; ?>
</div>