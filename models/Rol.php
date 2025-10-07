<?php
class Rol
{
    private mysqli $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function listarActivos(): array
    {
        $sql = 'SELECT ID_Rol, Nombre FROM rol WHERE Activo = 1 ORDER BY Nombre';
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
        $sql = 'SELECT ID_Rol, Nombre, Activo FROM rol WHERE ID_Rol = ?';
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
        $sql = 'INSERT INTO rol (Nombre, Activo) VALUES (?, ?)';
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
        $sql = 'UPDATE rol SET Nombre = ?, Activo = ? WHERE ID_Rol = ?';
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
        $sql = 'UPDATE rol SET Activo = ? WHERE ID_Rol = ?';
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