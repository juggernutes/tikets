<?php 
session_start();
if (!isset($_SESSION['login_id'])) {
    header("Location: ../public/index.php");
    exit;
}

$title = "Registrar Ticket";
include __DIR__ . '/layout/header.php';

require_once '../config/db_connection.php';
require_once '../controllers/SistemaController.php';

$sistemaController = new SistemaController($conn);
// Verifica si el usuario tiene permisos para registrar tickets

?>

<div style="max-width: 600px; margin: 30px auto;">
    <h2>Registrar nuevo Ticket</h2>
    <form action="../controllers/guardarTiket.php" method="POST">
        <label for="id_sistema">Sistema:</label><br>
        <select name="id_sistema" id="id_sistema" required>
            <option value="">Seleccione un sistema</option>
            <?php
            $sistemas = $sistemaController->obtenerSistemas();
            foreach ($sistemas as $sistema) {
                $id = htmlspecialchars($sistema['ID_Sistema']);
                $nombre = htmlspecialchars($sistema['Nombre']);
                $descripcion = htmlspecialchars($sistema['Descripcion']);
                echo "<option value=\"$id\" data-descripcion=\"$descripcion\">$nombre</option>";
            }
            ?>
        </select>
            <p><strong>Descripción del sistema:</strong> <span id="descripcionSistema">Seleccione un sistema</span></p>
        <script>
            document.getElementById('id_sistema').addEventListener('change', function() {
                var selectedOption = this.options[this.selectedIndex];
                var descripcion = selectedOption.getAttribute('data-descripcion');
                document.getElementById('descripcionSistema').textContent = descripcion || 'Seleccione un sistema';
            });
        </script>
        <br><br>
        <label for="descripcion">Descripción:</label><br>
        <textarea name="descripcion" id="descripcion" rows="4" cols="50" required></textarea><br><br>

        <button type="submit">Guardar</button>
    </form>
 </div>

<?php include __DIR__ . '/layout/footer.php'; ?>
