<?php
session_start();
require_once __DIR__ . '/../app/appTiket.php';
if (!isset($_SESSION['login_id'])) {
    header("Location: ../public/index.php");
    exit;
}

$IdUser = $_SESSION['login_id'];
$rol = $_SESSION['rol'];

$tickets = $tiketController->getTicketsCerrados($IdUser, $rol);

$title = "TIKETS CERRADOS";
include __DIR__ . '/layout/header.php';
?>

<h2>TIKETS CERRADOS</h2>

<table border="1" cellpadding="5" class="tickets-table">
    <thead>
        <tr>
            <th>FOLIO</th>
            <th>FECHA DE CREACION</th>
            <th>FECHA DE RESOLUCION</th>
            <th>SISTEMA</th>
            <th>DESCRIPCION DEL PROBLEMA</th>
            <th>EMPLEADO</th>
            <th>PUESTO</th>
            <th>SUCURSAL</th>
            <th>SOPORTE</th>
            <th>ERROR</th>
            <th>SOLUCION</th>
            <th>DESCRIPCION SOLUCION</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $tickets->fetch_assoc()): ?>
            <tr class="ticket-row" data-tiket="<?= htmlspecialchars($row['ID_Tiket']) ?>">
                <td><?= htmlspecialchars($row['Folio']) ?></td>
                <td><?= htmlspecialchars($row['FECHA']) ?></td>
                <td><?= htmlspecialchars($row['FECHA_SOLUCION']) ?></td>
                <td><?= htmlspecialchars($row['SISTEMA']) ?></td>
                <td><?= htmlspecialchars($row['DESCRIPCION']) ?></td>
                <td><?= htmlspecialchars($row['EMPLEADO']) ?></td>
                <td><?= htmlspecialchars($row['PUESTO']) ?></td>
                <td><?= htmlspecialchars($row['SUCURSAL']) ?></td>
                <td><?= htmlspecialchars($row['NOMBRE_SOPORTE']) ?></td>
                <td><?= htmlspecialchars($row['ERROR']) ?></td>
                <td><?= htmlspecialchars($row['SOLUCION']) ?></td>
                <td><?= htmlspecialchars($row['DESCRIPCION_SOLUCION']) ?></td>
            </tr>

        <?php endwhile; ?>
    </tbody>
</table>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.ticket-row').forEach(function(row) {
            row.style.cursor = 'pointer'; // Opcional, muestra el cursor de enlace
            row.addEventListener('click', function() {
                var tiket = this.getAttribute('data-tiket');
                window.location.href = 'detalles_tiket.php?ID_Tiket=' + encodeURIComponent(tiket);
            });
        });
    });
</script>

<?php include __DIR__ . '/layout/footer.php'; ?>