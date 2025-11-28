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
        $stmt = $this->conn->prepare("CALL sp_proveedores(?, NULL, NULL, NULL)");
        $stmt->bind_param("i", $op);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $result->free();
        $stmt->close();
        return $rows;
    }

}