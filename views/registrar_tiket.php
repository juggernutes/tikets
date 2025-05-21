<?php
session_start();
if (!isset($_SESSION['login_id'])) {
    header("Location: ../public/index.php");
    exit;
}
$title = "Registrar Ticket";
include __DIR__ . '/layout/header.php';
?>

<div style="max-width: 600px; margin: 30px auto;">
    <h2>Registrar nuevo Ticket</h2>
    <form action="../controllers/guardarTiket.php" method="POST">
        <label>Sistema:</label><br>
        <select name="id_sistema" required>
            <option value="1">SAP</option>
            <option value="2">WINDOWS</option>
            <!-- Aquí idealmente cargar dinámicamente -->
        </select><br><br>

        <label>Descripción:</label><br>
        <textarea name="descripcion" required></textarea><br><br>

        <button type="submit">Guardar</button>
    </form>
</div>
<?php include __DIR__ . '/layout/footer.php'; ?>
