<?php
session_start();
if (!isset($_SESSION['login_id'])) {
    header("Location: ../public/index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Ticket</title>
</head>
<body>
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
</body>
</html>
