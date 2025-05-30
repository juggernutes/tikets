<?php
include __DIR__ . '/../app/App.php';

$idTiket = $_GET['id'] ?? null;
if (!$idTiket) {
    echo "No se proporcionó un ticket.";
    exit;
}

require_once __DIR__ . '/../app/appTiket.php';

$title = "Detalles del ticket";
include __DIR__ . '/layout/header.php';
?>

<h2>Resolver Ticket #<?= htmlspecialchars($ticket['SerieFolio']) ?></h2>

<p><strong>Sistema:</strong> <?= htmlspecialchars($ticket['SISTEMA']) ?></p>
<p><strong>Descripción:</strong> <?= nl2br(htmlspecialchars($ticket['Descripcion'])) ?></p>

<!-- Aquí puedes agregar un formulario para resolver el ticket -->

<a href="asignar_tiket.php">Volver</a>

<?php include __DIR__ . '/layout/footer.php'; ?>
