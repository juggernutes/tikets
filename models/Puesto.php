<?php
class Puesto
{
    private mysqli $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function listarActivos(): array
    {
        $sql = 'SELECT ID_Puesto, Descripcion FROM puesto WHERE Active = 1 ORDER BY Descripcion';
        $rows = [];
        if ($result = $this->conn->query($sql)) {
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
            $result->free();
        }
        return $rows;
    }

    public function obtenerPorId(int $id): ?array
    {
        $sql = 'SELECT ID_Puesto, Descripcion, Active FROM puesto WHERE ID_Puesto = ?';
        if ($stmt = $this->conn->prepare($sql)) {
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $data = null;
            if ($result = $stmt->get_result()) {
                $data = $result->fetch_assoc() ?: null;
                $result->free();
            }
            $stmt->close();
            return $data;
        }
        return null;
    }

    public function crear(string $descripcion, bool $activo = true): bool
    {
        $sql = 'INSERT INTO puesto (Descripcion, Active) VALUES (?, ?)';
        $flag = $activo ? 1 : 0;
        if ($stmt = $this->conn->prepare($sql)) {
            $stmt->bind_param('si', $descripcion, $flag);
            $ok = $stmt->execute();
            $stmt->close();
            return $ok;
        }
        return false;
    }

    public function actualizar(int $id, string $descripcion, bool $activo = true): bool
    {
        $sql = 'UPDATE puesto SET Descripcion = ?, Active = ? WHERE ID_Puesto = ?';
        $flag = $activo ? 1 : 0;
        if ($stmt = $this->conn->prepare($sql)) {
            $stmt->bind_param('sii', $descripcion, $flag, $id);
            $ok = $stmt->execute();
            $stmt->close();
            return $ok;
        }
        return false;
    }

    public function cambiarEstado(int $id, bool $activo): bool
    {
        $sql = 'UPDATE puesto SET Active = ? WHERE ID_Puesto = ?';
        $flag = $activo ? 1 : 0;
        if ($stmt = $this->conn->prepare($sql)) {
            $stmt->bind_param('ii', $flag, $id);
            $ok = $stmt->execute();
            $stmt->close();
            return $ok;
        }
        return false;
    }
}