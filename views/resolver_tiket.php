<?php
include __DIR__ . '/../app/App.php';

require_once __DIR__ . '/../models/tiket.php';
require_once __DIR__ . '/../controllers/tiketController.php';

$tiketController = new TiketController(new Tiket($conn));

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

$title = "Detalles del ticket";
include __DIR__ . '/layout/header.php';
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

        <p><strong>Empleado:</strong> <?= htmlspecialchars($ticket['EMPLEADO']) ?></p>
        <p><strong>Puesto:</strong> <?= htmlspecialchars($ticket['PUESTO']) ?></p>
        <p><strong>Sucursal:</strong> <?= htmlspecialchars($ticket['SUCURSAL']) ?></p>
    </div>

    <div class="tiket-solucion">
        <form action="../app/resolver_tiket_action.php" method="POST">
            <input type="hidden" name="id_tiket" value="<?= htmlspecialchars($ticket['ID_Tiket']) ?>">

            <label for="id_error">Error:</label>
            <select name="id_error" id="id_error" required>
                <option value="">Selecciona un error</option>
                <?php
                include __DIR__ . '/../partials/combo_errores.php'; // Incluye el combo de errores
                ?>
            </select>

            <label for="id_solucion">Solución:</label>
            <select name="id_solucion" id="id_solucion" required>
                <option value="">Selecciona una solución</option>
                <?php
                include __DIR__ . '/../partials/combo_soluciones.php'; // Incluye el combo de soluciones
                ?>
            </select>

            <label for="detalle">Descripción de la solución:</label>
            <textarea name="detalle" id="detalle" rows="6" required></textarea>

            <button type="submit" class="btn-guardar">Guardar solución</button>
            
        </form>
    </div>
</div>



<?php include __DIR__ . '/layout/footer.php'; ?>
