<?php
class Tiket {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Método reutilizable para ejecutar cualquier operación del SP
    private function ejecutarSP($op, $parametros = []) {
        $params = array_merge([$op], $parametros);
        $placeholders = implode(',', array_fill(0, count($params), '?'));

        $types = '';
        foreach ($params as $p) {
            if (is_int($p)) {
                $types .= 'i';
            } elseif (is_string($p)) {
                $types .= 's';
            } else {
                $types .= 's';
            }
        }

        $stmt = $this->conn->prepare("CALL sp_tiket($placeholders)");
        if (!$stmt) {
            throw new Exception("Error al preparar SP: " . $this->conn->error);
        }

        $stmt->bind_param($types, ...$params);
        $stmt->execute();

        while ($this->conn->more_results() && $this->conn->next_result()) {
            $this->conn->use_result();
        }

        return $stmt->get_result();
    }

    // Operación 1: Crear nuevo ticket
    public function crearTiket($numEmpleado, $idSistema, $descripcion) {
        $params = [ null, $numEmpleado, $idSistema, 29, $descripcion, null, null, null, null];
        $op = 1;
        $stmt = $this->conn->prepare("CALL sp_tiket(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('iiiiisiisi', $op, ...$params);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }

    // Operación personalizada: soporte toma un ticket
    public function tomarTiket($idTiket, $idSoporte) {
        $op = 3;
        $params = [$idTiket, null, null, null, null, $idSoporte, null, null, null];
        
        $stmt = $this->conn->prepare("CALL sp_tiket(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('iiiiisiisi', $op, ...$params);
        $stmt->execute();

        return $stmt->affected_rows > 0;
    }

        // Operación 4: Resolver ticket asignando error y solución
    public function resolver($idTiket, $idSoporte, $idError, $idSolucion, $descripcionSolucion) {
        $op= 4;
        $params = [
            $idTiket, null, null, $idError, null, $idSoporte, $idSolucion, $descripcionSolucion, null
        ];
        $stmt = $this->conn->prepare("CALL sp_tiket(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('iiiiisiisi', $op, ...$params);
        $stmt->execute();

        return $stmt->affected_rows > 0;
    }

     // Operación 5: cerrar ticket
    public function cerrar($idTiket, $idUsuario) {
        $op = 5;
        $params = [
            $idTiket, null, null, null, null, null, null, null, $idUsuario
        ];
        $stmt = $this->conn->prepare("CALL sp_tiket(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('iiiiisiisi', $op, ...$params);
        $stmt->execute();

        return $stmt->affected_rows > 0;
    }

    //                                Operación 2: Obtener tickets por usuario
    public function getTiketsByUser($idUsuario) {
        return $this->ejecutarSP(2, [
            null, null, null, null, null, null, null, null, $idUsuario
        ]);
    }

    //                                  Operación 6: Obtener todos los tickets
    public function obtenerTodosTikets($idSoporte) {
        return $this->ejecutarSP(6, [
            null, null, null, null, null, $idSoporte, null, null, null
        ]);
    }

    public function getTiketById($idTiket) {
        $result = $this->ejecutarSP(7, [
            $idTiket, null, null, null, null, null, null, null, null
        ]);
        return $result->fetch_assoc();
    }

    
    // Obtener un ticket específico
    /*public function getTiket($idTiket) {
        $result = $this->ejecutarSP(2, [
            $idTiket,
            null, null, null, null, null, null, null, null
        ]);
        return $result->fetch_assoc();
    }*/

    // Operación 3: Asignar soporte y marcar como EN PROCESO
    public function asignarSoporte($idTiket, $idSoporte) {
        return $this->ejecutarSP(3, [
            $idTiket,
            null, null, null, null,
            null,
            $idSoporte,
            null, null
        ]) !== false;


    }
}
?>
