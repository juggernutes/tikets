<?php
class Sistema {
     private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }
    // Usando SP para obtener todos los sistemas
   public function obtenerTodos() {
        $sistemas = [];

        if ($stmt = $this->conn->prepare("CALL sp_sistemas(1, 0, null, null)")) {
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $sistemas[] = $row;
            }
            $stmt->close();
            // Limpieza del result set (para múltiples consultas)
            while ($this->conn->more_results() && $this->conn->next_result()) {
                $this->conn->use_result();
            }
        }

        sort($sistemas, SORT_NATURAL | SORT_FLAG_CASE);
        return $sistemas;
    }

    public function obtenerPorId($id) {
        $sql = "CALL sp_sistemas(2, ?, null, null)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function crear($nombre, $descripcion) {
        $sql = "CALL sp_sistemas(3, 0, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$nombre, $descripcion]);
    }

    public function actualizar($id, $nombre, $descripcion) {
        $sql = "CALL sp_sistemas(4, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id, $nombre, $descripcion]);
    }

    public function lastInsertId() {
        $sql = "CALL sp_sistemas(5, null, null, null)";
        $result = $this->conn->prepare($sql);
        $res = $result->execute();
        
    }
}

// Usando SP para crear un nuevo sistema
?>