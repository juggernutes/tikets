<?php
class Area
{
    private mysqli $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function listarActivas(): array
    {
        $sql = 'SELECT ID_Area, Nombre FROM area WHERE Activo = 1 ORDER BY Nombre';
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
        $sql = 'SELECT ID_Area, Nombre, Activo FROM area WHERE ID_Area = ?';
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

    public function crear(string $nombre, bool $activo = true): bool
    {
        $sql = 'INSERT INTO area (Nombre, Activo) VALUES (?, ?)';
        $flag = $activo ? 1 : 0;
        if ($stmt = $this->conn->prepare($sql)) {
            $stmt->bind_param('si', $nombre, $flag);
            $ok = $stmt->execute();
            $stmt->close();
            return $ok;
        }
        return false;
    }

    public function actualizar(int $id, string $nombre, bool $activo = true): bool
    {
        $sql = 'UPDATE area SET Nombre = ?, Activo = ? WHERE ID_Area = ?';
        $flag = $activo ? 1 : 0;
        if ($stmt = $this->conn->prepare($sql)) {
            $stmt->bind_param('sii', $nombre, $flag, $id);
            $ok = $stmt->execute();
            $stmt->close();
            return $ok;
        }
        return false;
    }

    public function cambiarEstado(int $id, bool $activo): bool
    {
        $sql = 'UPDATE area SET Activo = ? WHERE ID_Area = ?';
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