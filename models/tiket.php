<?php
class Tiket {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getConnection() {
        return $this->conn;
    }

    #endregion
    public function tiket($op, $idTiket = null, $numEmpleado = null, $idSistema = null, $idError = null, $descripcion = null, $idSoporte = null, $idSolucion = null) {
        $stmt = $this->conn->prepare("CALL sp_tiket(?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iiiiisii", $op, $idTiket, $numEmpleado, $idSistema, $idError, $descripcion, $idSoporte, $idSolucion);
        $stmt->execute();
        return $stmt->get_result(); 
    }



    // Operación 1: Crear nuevo ticket (usuario)
    public function creartiket($numEmpleado, $idSistema, $descripcion) {
        $op = 1;
        $idTiket = null; 
        $idSoporte = null; 
        $idSolucion = null;
        $idError = 29;
        $detalleSolucion = null;
        $idUsuario = null;
        $stmt = $this->conn->prepare("CALL sp_tiket(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iiiiisiisi", $op, $idTiket, $numEmpleado, $idSistema, $idError, $descripcion, $idSoporte, $idSolucion, $detalleSolucion, $idUsuario);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }

    public function getTiketsByUser($idUsuario) {
        $op = 2;
        $idTiket = null;
        $idSolucion = null;
        $idError = null;
        $detalleSolucion = null;
        $numEmpleado = null;
        $idSistema = null; 
        $descripcion = null;
        $idSoporte = null;
        $stmt = $this->conn->prepare("CALL sp_tiket(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iiiiisiisi", $op, $idTiket, $numEmpleado, $idSistema, $idError, $descripcion, $idSoporte, $idSolucion, $detalleSolucion, $idUsuario);
        $stmt->execute();

        while ($this->conn->more_results() && $this->conn->next_result()) {
            $this->conn->use_result();
        }
        
        return $stmt->get_result(); 
    }

    public function getTiket($idTiket) {
        $op = 2; // Asumimos que la operación 2 es para obtener tickets
        $numEmpleado = null;
        $idSistema = null;
        $idError = 29;
        $descripcion = null;
        $idSoporte = null;
        $idSolucion = null;

        $stmt = $this->conn->prepare("CALL sp_tiket(?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iiiiisii", $op, $idTiket, $numEmpleado, $idSistema, $idError, $descripcion, $idSoporte, $idSolucion);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc(); // Retorna el ticket específico
    }

    // Operación 2: Obtener tickets activos
    public function obtenerTodosTikets() {
    $op = 2;
    $idTiket = null;
    $numEmpleado = null;    
    $idSistema = null;
    $idError = null;
    $descripcion = null;
    $idSoporte = null;
    $idSolucion = null;

    $stmt = $this->conn->prepare("CALL sp_tiket(?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiiiiiii", $op, $idTiket, $numEmpleado, $idSistema, $idError, $descripcion, $idSoporte, $idSolucion);
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
