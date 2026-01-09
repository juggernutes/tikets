<?php
class Proveedor
{

    private mysqli $conn;
    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function obtenerProveedores(): array
    {
        $rows = [];
        $op = 1;
        $params = [null, null, null, null];
        $stmt = $this->conn->prepare("CALL sp_proveedores(?, ?, ?, ?, ?)");
        $stmt->bind_param("isssi", $op, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $result->free();
        $stmt->close();
        while ($this->conn->more_results() && $this->conn->next_result()) {
            $extraResult = $this->conn->use_result();
            if ($extraResult instanceof mysqli_result) {
                $extraResult->free();
            }
        }
        return $rows;
    }

    public function obtenerProveedorPorId($id)
    {
        $op = 5;
        $params = [null, null, null, $id];
        $stmt = $this->conn->prepare("CALL sp_proveedores(?, ?, ?, ?, ?)");
        $stmt->bind_param("isssi", $op, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result->fetch_assoc();
    }

    public function datosReporteSemanal($anio, $semana)
    {
        $rows = [];
        $op = 1;
        $params = [$anio, $semana];
        $stmt = $this->conn->prepare("CALL sp_reporte(?, ?, ?)");
        $stmt->bind_param("iii", $op, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $result->free();
        $stmt->close();
        while ($this->conn->more_results() && $this->conn->next_result()) {
            $extraResult = $this->conn->use_result();
            if ($extraResult instanceof mysqli_result) {
                $extraResult->free();
            }
        }
        return $rows;
    }
}