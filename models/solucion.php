<?php

class Solucion{
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function obtenerTodasSoluciones() {
        $soluciones = [];
        $op = 1;
        $id = null;
        $descripcion=null;
        $active = null;
        if ($stmt = $this->conn->prepare("CALL sp_soluciones(?, ?, ?, ?)")) {
            $stmt->bind_param("iisi", $op, $id, $descripcion, $active);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $soluciones[] = $row;
            }
            $stmt->close();
            // Limpieza del result set (para múltiples consultas)
            while ($this->conn->more_results() && $this->conn->next_result()) {
                $this->conn->use_result();
            }
        }
        return $soluciones;
    }

    public function obtenerSolucionPorId($id) {
        $op = 2;
        $descripcion=null;
        $active = null;

        if ($stmt = $this->conn->prepare("CALL sp_soluciones(?, ?, ?, ?)")) {
            $stmt->bind_param("iisi", $op, $id, $descripcion, $active);
            $stmt->execute();
            return $stmt->get_result()->fetch_assoc();
        }
        return null;
    }
}