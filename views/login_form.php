<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inicio de sesión</title>
</head>
<body>
    <h2>Iniciar sesión</h2>

    <?php
    if (isset($_GET['error'])) {
        echo "<p style='color:red;'>Usuario o contraseña incorrectos</p>";
    }
    ?>

    <form method="POST" action="../public/index.php">
        <label for="cuenta">Usuario:</label><br>
        <input type="text" name="cuenta" id="cuenta" required><br><br>

        <label for="password">Contraseña:</label><br>
        <input type="password" name="password" id="password" required><br><br>

        <button type="submit">Ingresar</button>
    </form>
</body>
</html>
