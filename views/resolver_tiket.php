<?php
include __DIR__ . '/../app/appTiket.php';

$idTiket = $_GET['id'] ?? null;
if (!$idTiket) {
    echo "No se proporcionó un ticket.";
    exit;
}

$ticket = $tiketController->getTicketById($idTiket);
if (!$ticket) {
    echo "No se encontró el ticket.";
    exit;
}
$empleado = $empleadoController -> obtenerEmpleadoporNumeroC($tiket['Numero_Empleado']);

$title = "Detalles del ticket";
include __DIR__ . '/layout/header.php';
$descripcionSolucion = $ticket['DESCRIPCION_SOLUCION'] ?? '';
?>


<div class="contenedor-tiket">
    <div class="tiket-info">
        <h2>Resolver Ticket #<?= htmlspecialchars($ticket['Folio']) ?></h2>
        <p><strong>Sistema:</strong> <?= htmlspecialchars($ticket['SISTEMA']) ?></p>
        <p><strong>Fecha:</strong> <?= htmlspecialchars($ticket['FECHA']) ?></p>
        <p><strong>Estado:</strong> <?= htmlspecialchars($ticket['ESTADO']) ?></p>

        <?php
        $descripcion = $ticket['DESCRIPCION'];
        if (strpos($descripcion, ' ') === false) {
            $descripcion = wordwrap($descripcion, 40, "\n", true);
        }
        ?>
        <p><strong>Descripción:</strong><br><?= nl2br(htmlspecialchars($descripcion)) ?></p>

        <!-- BLOQUE DE DATOS DEL EMPLEADO -->
        <?php if ($empleado): ?>
            <div class="tarjeta-empleado" style="background:#f6f9ff; border:1.5px solid #b6cced; border-radius:10px; padding:14px 22px; margin:15px 0 10px 0; box-shadow:0 1px 7px #aac8e930;">
                <h3 style="color:#23478d; margin-top:0; font-size:1.2em; margin-bottom:10px;">Datos del empleado</h3>
                <p><strong>Nombre:</strong> <?= htmlspecialchars($empleado['Nombre'] ?? $ticket['EMPLEADO']) ?></p>
                <p><strong>Puesto:</strong> <?= htmlspecialchars($empleado['Puesto'] ?? $ticket['PUESTO']) ?></p>
                <p><strong>Sucursal:</strong> <?= htmlspecialchars($empleado['Sucursal'] ?? $ticket['SUCURSAL']) ?></p>
                <?php if (!empty($empleado['Correo'])): ?>
                    <p><strong>Correo:</strong> <?= htmlspecialchars($empleado['Correo']) ?></p>
                <?php endif; ?>
                <?php if (!empty($empleado['Telefono'])): ?>
                    <p><strong>Teléfono:</strong> <?= htmlspecialchars($empleado['Telefono']) ?></p>
                <?php endif; ?>
                <!-- Puedes agregar más datos si tienes en $empleado -->
            </div>
        <?php endif; ?>

        <!-- Si no tienes la info completa en $empleado, usa los datos del ticket -->
        <?php /*  
        <p><strong>Empleado:</strong> <?= htmlspecialchars($ticket['EMPLEADO']) ?></p>
        <p><strong>Puesto:</strong> <?= htmlspecialchars($ticket['PUESTO']) ?></p>
        <p><strong>Sucursal:</strong> <?= htmlspecialchars($ticket['SUCURSAL']) ?></p>
        */ ?>
    </div>


    <div class="tiket-solucion">
        <form action="../app/appTiket.php?accion=solucionar&id_tiket=<?= $ticket['ID_Tiket'] ?>" method="POST">
            <input type="hidden" name="id_tiket" value="<?= htmlspecialchars($ticket['ID_Tiket']) ?>">

            <label for="id_error">Error:</label>
            <select name="id_error" id="id_error" required>
                <option value="">Selecciona un error</option>
                <?php include __DIR__ . '/../partials/combo_errores.php'; ?>
            </select>

            <label for="id_solucion">Solución:</label>
            <select name="id_solucion" id="id_solucion" required>
                <option value="">Selecciona una solución</option>
                <?php include __DIR__ . '/../partials/combo_soluciones.php'; ?>
            </select>

            <label for="detalle">Descripción de la solución:</label>

            <textarea name="descripcion_solucion" id="descripcion_solucion" rows="6" required><?= htmlspecialchars($descripcionSolucion) ?></textarea>

            <button type="submit">Solucionar</button>
        </form>

    </div>
</div>



<?php include __DIR__ . '/layout/footer.php'; ?>