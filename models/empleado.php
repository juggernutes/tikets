<?php

class Empleado {

     private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }    

    public function obtenerEmpleados($userId) {
        $empleados = [];

        if ($stmt = $this->conn->prepare("CALL sp_empleados(?, ?, null)")) {
            $op = 1;
            $stmt->bind_param("ii", $op, $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $empleados[] = $row;
            }
            $stmt->close();
            while ($this->conn->more_results() && $this->conn->next_result()) {
                $this->conn->use_result();
            }
        }

        return $empleados;
    }

    public function obtenerEmpleadoPorId($numeroEmpleado) {
        $sql = "CALL sp_empleados(2, ?, 0)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $numeroEmpleado);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }


}
