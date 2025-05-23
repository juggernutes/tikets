<?php
class Sistema {
     private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }
    // Usando SP para obtener todos los sistemas
   public function obtenerTodos() {
        $sistemas = [];

        if ($stmt = $this->conn->prepare("CALL sp_sistemas(1, 0)")) {
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

        return $sistemas;
    }

    public function obtenerPorId($id) {
        $sql = "CALL sp_sistemas(2, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

}

// Usando SP para crear un nuevo sistema
?>