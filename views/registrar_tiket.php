<?php 
    $title = "Registrar Ticket";
    include __DIR__ . '/layout/header.php';
    include __DIR__ . '/../app/apptiket.php';

    $idUsuario = $_SESSION['login_id']; 
?>

<div style="max-width: 1500px; margin: 20px auto;">
    <h2>Crear Ticket</h2>
    <form action="../controllers/guardarTiket.php" method="POST">
        <label for="id_empleado">Empleado:</label><br>
        <select name="Numero_Empleado" id="Numero_Empleado" required>
            <option value="">Seleccione un empleado</option>
            <?php 
                include __DIR__ . '/../partials/combo_empleados.php'; // Incluye el combo de empleados            $empleados = $empleadoController->obtenerEmpleados($idUsuario); // este ID viene de App.php
            ?>
        </select>
        <br><br>
        <label for="id_sistema">Sistema:</label><br>
        <select name="id_sistema" id="id_sistema" required>
            <option value="">Seleccione un sistema</option>
            <?php
                include __DIR__ . '/../partials/combo_sistemas.php'; // Incluye el combo de sistemas
            ?>
        </select>
            <p><strong>Descripción del sistema:</strong> <span id="descripcionSistema">Seleccione un sistema</span></p>
        <script>
            // Script para mostrar la descripción del sistema seleccionado
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
