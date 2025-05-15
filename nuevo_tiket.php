<?php
session_start();

if (!isset($_SESSION['id_empleado'])) {
    header("Location: index.php?error=Acceso no autorizado");
    exit();
}

require_once __DIR__ . '/config/db_connection.php';

// Obtener datos para los select
$branches = $conn->query("SELECT BranchID, BranchName FROM company_branch WHERE Status = 1");
$systems = $conn->query("SELECT SystemID, SystemName FROM it_system WHERE Status = 1");
$priorities = $conn->query("SELECT PriorityID, PriorityName FROM priority WHERE Status = 1");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nuevo Ticket</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-5">
    <h2 class="mb-4">Captura de nuevo ticket</h2>
    <form action="guardarTiket.php" method="POST">
        <div class="mb-3">
            <label for="descripcion" class="form-label">Descripción del problema</label>
            <textarea name="descripcion" id="descripcion" class="form-control" required></textarea>
        </div>
        <div class="mb-3">
            <label for="branch" class="form-label">Sucursal</label>
            <select name="branch" id="branch" class="form-select" required>
                <option value="">Seleccione una sucursal</option>
                <?php while ($b = $branches->fetch_assoc()): ?>
                    <option value="<?= $b['BranchID'] ?>"><?= $b['BranchName'] ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="system" class="form-label">Sistema afectado</label>
            <select name="system" id="system" class="form-select" required>
                <option value="">Seleccione un sistema</option>
                <?php while ($s = $systems->fetch_assoc()): ?>
                    <option value="<?= $s['SystemID'] ?>"><?= $s['SystemName'] ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="priority" class="form-label">Prioridad</label>
            <select name="priority" id="priority" class="form-select" required>
                <option value="">Seleccione prioridad</option>
                <?php while ($p = $priorities->fetch_assoc()): ?>
                    <option value="<?= $p['PriorityID'] ?>"><?= $p['PriorityName'] ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <input type="hidden" name="empleado" value="<?= $_SESSION['id_empleado'] ?>">
        <button type="submit" class="btn btn-primary">Guardar Ticket</button>
    </form>
</body>
</html>
