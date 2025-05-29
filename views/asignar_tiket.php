<?php
include __DIR__ . '/../app/App.php';

$usuarioId = $_SESSION['login_id'] ?? null;

$title = "Tickets Abiertos";
include __DIR__ . '/layout/header.php';
?>
<div style="max-width: 1500px; margin: 20px auto;">
<h2>Tickets abiertos</h2>

    <?php
    if ($result && $result->num_rows > 0) {
        include __DIR__ . '/../components/renderCard.php';
    } else {
        echo "<p>No tienes tickets registrados.</p>";
    }
    ?>


<a href="dashboard.php">Volver al panel</a>

<?php include __DIR__ . '/layout/footer.php'; ?>
