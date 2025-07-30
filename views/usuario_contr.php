<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$title = "Solicitar Reinicio de Contraseña";
include __DIR__ . '/layout/header.php';
?>

<div style="max-width: 400px; margin: 40px auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
    <h3 style="text-align:center;">Solicitar Reinicio de Contraseña</h3>
    <form method="POST" action="../app/appTiket.php?accion=crearTiketRestablecerContrasena&usuario=<?= htmlspecialchars($_POST['usuario'] ?? '') ?>">
        <label for="usuario">Usuario:</label><br>
        <input type="text" id="usuario" name="usuario" required style="width:100%; padding:8px; margin-bottom:20px;"><br>

        <button type="submit" style="width:100%; background-color:#003366; color:white; padding:10px; border:none; border-radius:5px;">Solicitar Reinicio</button>
    </form>

    <!-- Botón para regresar al inicio -->
    <a href="../public/index.php" style="display:block; text-align:center; margin-top:20px; background-color:#ccc; padding:10px; border-radius:5px; text-decoration:none; color:black;">
        ← Regresar al inicio
    </a>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>

