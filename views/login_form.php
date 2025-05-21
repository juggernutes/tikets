<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['login_id']) && isset($_SESSION['rol'])) {
    header("Location: dashboard.php");
    exit;
}

$title = "Iniciar sesión";
include __DIR__ . '/layout/header.php';
?>

<div style="max-width: 400px; margin: 40px auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
    <h3 style="text-align:center;">Acceso al sistema</h3>
    <form method="POST" action="../public/index.php">
        <label for="cuenta">Usuario:</label><br>
        <input type="text" name="cuenta" value="<?= htmlspecialchars($_POST['cuenta'] ?? '') ?>" required style="width:100%; padding:8px; margin-bottom:10px;"><br>

        <label for="password">Contraseña:</label><br>
        <input type="password" name="password" required style="width:100%; padding:8px; margin-bottom:20px;"><br>

        <button type="submit" style="width:100%;">Iniciar sesión</button>
    </form>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>

