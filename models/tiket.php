<?php
class Tiket {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Operación 1: Crear nuevo ticket (usuario)
    public function crear($numEmpleado, $idSistema, $descripcion) {
        $op = 1;
        $stmt = $this->conn->prepare("CALL sp_tiket(?, NULL, ?, ?, NULL, ?, NULL, NULL)");
        $stmt->bind_param("iiis", $op, $numEmpleado, $idSistema, $descripcion);
        return $stmt->execute();
    }

    // Operación 2: Obtener tickets activos
    public function obtenerTodos() {
    $op = 2;
    $stmt = $this->conn->prepare("CALL sp_tiket(?, NULL, NULL, NULL, NULL, NULL, NULL, NULL)");
    $stmt->bind_param("i", $op);
    $stmt->execute();
    return $stmt->get_result();
    }

    // Operación 3: Asignar soporte y pasar a EN PROCESO
    public function asignarSoporte($idTiket, $idSoporte) {
        $op = 3;
        $stmt = $this->conn->prepare("CALL sp_tiket(?, ?, NULL, NULL, NULL, NULL, ?, NULL)");
        $stmt->bind_param("iis", $op, $idTiket, $idSoporte);
        return $stmt->execute();
    }

    // Operación 4: Resolver y asignar error/solución
    public function resolver($idTiket, $idError, $idSolucion) {
        $op = 4;
        $stmt = $this->conn->prepare("CALL sp_tiket(?, ?, NULL, NULL, ?, NULL, NULL, ?)");
        $stmt->bind_param("iiis", $op, $idTiket, $idError, $idSolucion);
        return $stmt->execute();
    }

    // Operación 5: Validar y cerrar ticket
    public function cerrar($idTiket) {
        $op = 5;
        $stmt = $this->conn->prepare("CALL sp_tiket(?, ?, NULL, NULL, NULL, NULL, NULL, NULL)");
        $stmt->bind_param("ii", $op, $idTiket);
        return $stmt->execute();
    }
}
?>
