<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: nuevo_tiket.php');
    exit();
}

require_once __DIR__ . '/config/db_connection.php';

$descripcion = trim($_POST['descripcion']);
$branch = (int)$_POST['branch'];
$system = (int)$_POST['system'];
$priority = (int)$_POST['priority'];
$empleado = (int)$_POST['empleado'];

$estatus = 'pendiente';
$fecha = date('Y-m-d H:i:s');

$stmt = $conn->prepare("INSERT INTO tiket (id_empleado, id_sucursal, id_sistema, descripcion, id_prioridad, fecha, estatus) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("iiisiss", $empleado, $branch, $system, $descripcion, $priority, $fecha, $estatus);

if ($stmt->execute()) {
    header("Location: dashboard.php?mensaje=Ticket+registrado+correctamente");
    exit();
} else {
    echo "<p>Error al guardar el ticket: " . $stmt->error . "</p>";
}

$conn->close();
?>
