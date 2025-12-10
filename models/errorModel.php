<?php
class ErrorModel{

    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function obtenerErrores() {
        $error = [];

        if ($stmt = $this->conn->prepare("CALL sp_errores(?, ?, ?, ?, ?)")) {
            $op = 1;
            $idError = null;
            $descripcion = null;
            $active = null;
            $tipoError = null;
            $stmt->bind_param("iisii", $op, $idError, $descripcion, $active, $tipoError);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $error[] = $row;
            }
            $stmt->close();
            while ($this->conn->more_results() && $this->conn->next_result()) {
                $this->conn->use_result();
            }
        }

        return $error;        
    }
    public function insertarError($descripcion) {
        $op=2;
        $sql = "CALL sp_errores(?, null, ?, null, 2)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("is", $op, $descripcion);
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function actualizarError($idError, $descripcion, $tipoError) {
        $op=3;
        $active = null;
        $sql = "CALL sp_errores(?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iisii", $op, $idError, $descripcion, $active, $tipoError);
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function eliminarError($idError) {
        $op = 4;
        $descripcion = null;
        $active = 0; // Para eliminar, se marca como inactivo
        $tipoError = null; // No se requiere tipo de error para eliminar
        $sql = "CALL sp_errores(?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iisii", $op, $idError, $descripcion, $active, $tipoError);
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function obtenerErrorPorId($idError) {
        $op = 5;
        $descripcion = null;
        $active = null;
        $tipoError = null;
        $sql = "CALL sp_errores(?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iisii", $op, $idError, $descripcion, $active, $tipoError);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
}