<?php 
    $title = "Registrar Ticket";
    include __DIR__ . '/layout/header.php';
    include __DIR__ . '/../App.php';

    $idUsuario = $_SESSION['login_id'];
    
    

?>

<div style="max-width: 1500px; margin: 20px auto;">
    <h2>Crear Ticket</h2>
    <form action="../controllers/guardarTiket.php" method="POST">
        <label for="id_empleado">Empleado:</label><br>
        <select name="id_empleado" id="id_empleado" required>
            

            <option value="">Seleccione un empleado</option>
            <?php
            $empleados = $empleadoController->obtenerEmpleados($idUsuario); // este ID viene de App.php
            foreach ($empleados as $empleado){
                $numeroEmpleado = htmlspecialchars($empleado['Numero_Empleado']);
                $nombreEmpleado = htmlspecialchars($empleado['Nombre']);
                echo "<option value=\"$numeroEmpleado\" data-nombre=\"$nombreEmpleado\">$nombreEmpleado</option>";
            } 
            ?>
        </select>
        <br><br>

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
