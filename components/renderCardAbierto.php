<div class="contenedor-tickets">
<?php foreach ($abiertos as $row): ?>
    <?php $estadoClass = strtolower(str_replace(' ', '-', $row['ESTADO'])); 
    $descripcion = wordwrap($row['DESCRIPCION'], 30, "\n", true);
    ?>
    <div class="tiket <?= $estadoClass ?>">
        <h4><?= htmlspecialchars($row['Folio']) ?></h4>
        <H4><?= htmlspecialchars($row['SISTEMA']) ?></H4>
        <p><strong>Fecha:</strong> <?= htmlspecialchars($row['FECHA']) ?></p>
        <p><strong>Estado:</strong> <?= htmlspecialchars($row['ESTADO']) ?></p>
        <p><strong>Descripción:</strong><br><?= nl2br(htmlspecialchars($descripcion, ENT_QUOTES, 'UTF-8')) ?></p>
        <p><strong>Empleado:</strong> <?= htmlspecialchars($row['EMPLEADO']) ?></p>
        <p><strong>Puesto:</strong> <?= htmlspecialchars($row['PUESTO']) ?></p>
        <p><strong>Sucursal:</strong> <?= htmlspecialchars($row['SUCURSAL']) ?></p>
        <?php if(isset($_SESSION['rol']) && ($_SESSION['rol'] === 'SOPORTE')): ?>
            <a href="../app/appTiket.php?accion=tomarTiket&id_tiket=<?= $row['ID_Tiket'] ?>"><button>Tomar</button></a>
        <?php endif; ?>
    </div>
<?php endforeach; ?>
</div>